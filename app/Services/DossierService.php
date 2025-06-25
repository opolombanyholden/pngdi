<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\Document;
use App\Models\DossierComment;
use App\Models\DossierOperation;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class DossierService
{
    protected $notificationService;
    protected $fileUploadService;
    
    public function __construct(
        NotificationService $notificationService,
        FileUploadService $fileUploadService
    ) {
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }
    
    /**
     * Créer un nouveau dossier
     */
    public function createDossier(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Vérifier qu'il n'y a pas déjà un dossier actif non traité pour l'organisation
            if (isset($data['organisation_id'])) {
                $activeDossier = Dossier::where('organisation_id', $data['organisation_id'])
                    ->whereIn('statut', [Dossier::STATUT_BROUILLON, Dossier::STATUT_SOUMIS, Dossier::STATUT_EN_COURS])
                    ->first();
                
                if ($activeDossier) {
                    throw new Exception('Un dossier est déjà en cours de traitement pour cette organisation');
                }
            }
            
            // Créer le dossier
            $dossier = Dossier::create(array_merge($data, [
                'statut' => Dossier::STATUT_BROUILLON,
                'is_active' => true
            ]));
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'created', [
                'type_operation' => $dossier->type_operation
            ]);
            
            // Notification
            if ($dossier->organisation && $dossier->organisation->user) {
                $this->notificationService->notify(
                    $dossier->organisation->user, 
                    'Nouveau dossier créé',
                    "Un nouveau dossier {$dossier->numero_dossier} a été créé avec succès.",
                    'info'
                );
            }
            
            return $dossier;
        });
    }
    
    /**
     * Mettre à jour le statut d'un dossier
     */
    public function updateStatus(Dossier $dossier, string $status, ?string $comment = null)
    {
        $oldStatus = $dossier->statut;
        
        $dossier->statut = $status;
        if ($comment) {
            $dossier->admin_comment = $comment;
        }
        
        // Si le dossier est accepté ou rejeté, mettre à jour la date de traitement
        if (in_array($status, [Dossier::STATUT_ACCEPTE, Dossier::STATUT_REJETE])) {
            $dossier->date_traitement = now();
        }
        
        $dossier->save();
        
        // Enregistrer l'opération
        $this->recordOperation($dossier, 'status_changed', [
            'old_status' => $oldStatus,
            'new_status' => $status,
            'comment' => $comment
        ]);
        
        // Notification
        if ($dossier->organisation && $dossier->organisation->user) {
            $this->notificationService->notifyDossierStatusChange($dossier, $oldStatus, $status);
        }
        
        return $dossier;
    }
    
    /**
     * Soumettre un dossier pour validation
     */
    public function submitDossier(Dossier $dossier): bool
    {
        if (!$dossier->canBeSubmitted()) {
            throw new Exception('Ce dossier ne peut pas être soumis dans son état actuel');
        }
        
        DB::beginTransaction();
        
        try {
            // Vérifier que tous les documents obligatoires sont présents
            if (!$dossier->hasAllRequiredDocuments()) {
                $missingDocs = $dossier->getMissingDocuments()->pluck('nom')->toArray();
                throw new Exception('Documents manquants : ' . implode(', ', $missingDocs));
            }
            
            // Mettre à jour le statut
            $dossier->update([
                'statut' => Dossier::STATUT_SOUMIS,
                'date_soumission' => now()
            ]);
            
            // Obtenir la première étape du workflow
            $firstStep = $dossier->getNextStep();
            if ($firstStep) {
                $dossier->update(['current_step_id' => $firstStep->id]);
            }
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'submitted');
            
            // Notifications
            if ($dossier->organisation && $dossier->organisation->user) {
                $this->notificationService->notify(
                    $dossier->organisation->user,
                    'Dossier soumis',
                    "Votre dossier {$dossier->numero_dossier} a été soumis pour validation.",
                    'success'
                );
            }
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Attacher des documents à un dossier
     */
    public function attachDocuments(Dossier $dossier, array $documents)
    {
        $results = [];
        
        foreach ($documents as $documentTypeId => $file) {
            if ($file instanceof UploadedFile) {
                $document = $this->addDocument($dossier, $documentTypeId, $file);
                $results[] = $document;
            }
        }
        
        return $results;
    }
    
    /**
     * Ajouter un document au dossier
     */
    public function addDocument(Dossier $dossier, int $documentTypeId, UploadedFile $file): Document
    {
        if (!$dossier->canBeModified()) {
            throw new Exception('Ce dossier ne peut plus être modifié');
        }
        
        DB::beginTransaction();
        
        try {
            // Upload du fichier
            $uploadResult = $this->fileUploadService->upload($file, 'dossiers/' . $dossier->id);
            
            // Créer l'enregistrement du document
            $document = Document::create([
                'dossier_id' => $dossier->id,
                'document_type_id' => $documentTypeId,
                'nom_fichier' => $uploadResult['file_name'],
                'nom_original' => $uploadResult['original_name'],
                'chemin_fichier' => $uploadResult['file_path'],
                'taille' => $uploadResult['file_size'],
                'mime_type' => $uploadResult['mime_type'],
                'hash_fichier' => hash_file('sha256', Storage::disk('public')->path($uploadResult['file_path'])),
                'uploaded_by' => Auth::id()
            ]);
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'document_added', [
                'document_type' => $document->documentType->nom ?? 'Document',
                'document_id' => $document->id
            ]);
            
            DB::commit();
            return $document;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Ajouter un commentaire au dossier
     */
    public function addComment(Dossier $dossier, string $comment, string $visibility = 'internal'): DossierComment
    {
        $comment = DossierComment::create([
            'dossier_id' => $dossier->id,
            'user_id' => Auth::id(),
            'comment' => $comment,
            'visibility' => $visibility
        ]);
        
        // Enregistrer l'opération
        $this->recordOperation($dossier, 'comment_added', [
            'visibility' => $visibility
        ]);
        
        // Notification si commentaire visible par l'opérateur
        if ($visibility === 'public' && $dossier->organisation && $dossier->organisation->user) {
            $this->notificationService->notify(
                $dossier->organisation->user,
                'Nouveau commentaire',
                'Un nouveau commentaire a été ajouté à votre dossier.',
                'info'
            );
        }
        
        return $comment;
    }
    
    /**
     * Obtenir le statut détaillé d'un dossier
     */
    public function getDossierStatus(Dossier $dossier): array
    {
        $status = [
            'dossier' => [
                'id' => $dossier->id,
                'numero' => $dossier->numero_dossier,
                'type_operation' => $dossier->type_operation,
                'statut' => $dossier->statut,
                'statut_label' => $dossier->statut_label,
                'date_soumission' => $dossier->date_soumission,
                'date_traitement' => $dossier->date_traitement,
                'progression' => $dossier->getProgressionPercentage()
            ],
            'organisation' => [
                'nom' => $dossier->organisation->nom,
                'type' => $dossier->organisation->type_label
            ],
            'workflow' => [
                'etape_actuelle' => null,
                'etapes_completees' => [],
                'prochaine_etape' => null
            ],
            'documents' => [
                'total' => $dossier->documents()->count(),
                'valides' => $dossier->documents()->validated()->count(),
                'en_attente' => $dossier->documents()->pending()->count(),
                'rejetes' => $dossier->documents()->rejected()->count()
            ],
            'is_locked' => $dossier->isLocked(),
            'locked_by' => null
        ];
        
        // Étape actuelle
        if ($dossier->currentStep) {
            $status['workflow']['etape_actuelle'] = [
                'nom' => $dossier->currentStep->nom,
                'description' => $dossier->currentStep->description,
                'entite_validation' => $dossier->currentStep->validationEntity->nom ?? 'N/A'
            ];
        }
        
        // Étapes complétées
        $completedSteps = $dossier->validations()
            ->where('decision', 'approuve')
            ->with('workflowStep')
            ->get();
        
        foreach ($completedSteps as $validation) {
            $status['workflow']['etapes_completees'][] = [
                'nom' => $validation->workflowStep->nom ?? 'Étape',
                'date_validation' => $validation->validated_at->format('d/m/Y H:i'),
                'validateur' => $validation->validatedBy->name ?? 'Inconnu'
            ];
        }
        
        // Prochaine étape
        $nextStep = $dossier->getNextStep();
        if ($nextStep) {
            $status['workflow']['prochaine_etape'] = [
                'nom' => $nextStep->nom,
                'description' => $nextStep->description
            ];
        }
        
        // Info sur le verrouillage
        if ($dossier->isLocked()) {
            $lockedBy = $dossier->getLockedByUser();
            $status['locked_by'] = $lockedBy ? $lockedBy->name : 'Utilisateur inconnu';
        }
        
        return $status;
    }
    
    /**
     * Rechercher des dossiers
     */
    public function searchDossiers(array $criteria): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Dossier::with(['organisation', 'currentStep']);
        
        // Filtrer par numéro
        if (!empty($criteria['numero'])) {
            $query->where('numero_dossier', 'like', '%' . $criteria['numero'] . '%');
        }
        
        // Filtrer par organisation
        if (!empty($criteria['organisation'])) {
            $query->whereHas('organisation', function ($q) use ($criteria) {
                $q->where('nom', 'like', '%' . $criteria['organisation'] . '%')
                    ->orWhere('sigle', 'like', '%' . $criteria['organisation'] . '%');
            });
        }
        
        // Filtrer par type d'opération
        if (!empty($criteria['type_operation'])) {
            $query->where('type_operation', $criteria['type_operation']);
        }
        
        // Filtrer par statut
        if (!empty($criteria['statut'])) {
            if (is_array($criteria['statut'])) {
                $query->whereIn('statut', $criteria['statut']);
            } else {
                $query->where('statut', $criteria['statut']);
            }
        }
        
        // Filtrer par date de soumission
        if (!empty($criteria['date_debut'])) {
            $query->where('date_soumission', '>=', $criteria['date_debut']);
        }
        
        if (!empty($criteria['date_fin'])) {
            $query->where('date_soumission', '<=', $criteria['date_fin']);
        }
        
        // Tri
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        return $query->paginate($criteria['per_page'] ?? 15);
    }
    
    /**
     * Enregistrer une opération
     */
    protected function recordOperation(Dossier $dossier, string $type, array $data = []): void
    {
        DossierOperation::create([
            'dossier_id' => $dossier->id,
            'type_operation' => $type,
            'user_id' => Auth::id(),
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}