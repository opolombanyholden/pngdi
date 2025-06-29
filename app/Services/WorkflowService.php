<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\WorkflowStep;
use App\Models\DossierValidation;
use App\Models\DossierOperation;
use App\Models\DossierLock;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class WorkflowService
{
    /**
     * ✅ CORRECTION PRINCIPALE - Méthode corrigée initializeWorkflow()
     */
    public function initializeWorkflow(Dossier $dossier): bool
    {
        DB::beginTransaction();
        
        try {
            // Obtenir la première étape du workflow pour ce type d'organisation et d'opération
            $firstStep = $this->getFirstWorkflowStep($dossier);
            
            if (!$firstStep) {
                // Si pas d'étapes définies, marquer le dossier comme prêt pour validation manuelle
                $dossier->update([
                    'statut' => Dossier::STATUT_SOUMIS,
                    'current_step_id' => null,
                    'submitted_at' => now()
                ]);
                
                $this->recordOperation($dossier, 'workflow_initialized', [
                    'note' => 'Aucun workflow défini - validation manuelle requise'
                ]);
            } else {
                // Démarrer le workflow à la première étape
                $dossier->update([
                    'statut' => Dossier::STATUT_EN_COURS,
                    'current_step_id' => $firstStep->id,
                    'submitted_at' => now()
                ]);
                
                // Créer la première validation en attente
                DossierValidation::create([
                    'dossier_id' => $dossier->id,
                    'workflow_step_id' => $firstStep->id,
                    'validation_entity_id' => $this->getDefaultValidationEntityId(),
                    'decision' => 'en_attente',
                    'assigned_at' => now()
                ]);
                
                $this->recordOperation($dossier, 'workflow_initialized', [
                    'first_step' => $firstStep->libelle,
                    'step_id' => $firstStep->id
                ]);
            }
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de l\'initialisation du workflow: ' . $e->getMessage(), [
                'dossier_id' => $dossier->id,
                'organisation_type' => $dossier->organisation->type ?? 'unknown',
                'operation_type' => $dossier->type_operation ?? 'unknown'
            ]);
            
            // En cas d'erreur, marquer comme soumis pour traitement manuel
            $dossier->update([
                'statut' => Dossier::STATUT_SOUMIS,
                'submitted_at' => now()
            ]);
            
            return false;
        }
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Obtenir l'ID d'entité de validation par défaut
     */
    protected function getDefaultValidationEntityId(): int
    {
        // Retourner 1 par défaut ou chercher une entité active
        return 1;
    }
    
    /**
     * ⭐ MÉTHODE CORRIGÉE - Obtenir la première étape du workflow
     */
    protected function getFirstWorkflowStep(Dossier $dossier): ?WorkflowStep
    {
        $organisation = $dossier->organisation;
        $typeOperation = $dossier->type_operation ?? 'creation';
        $typeOrganisation = $organisation->type ?? 'association';
        
        return WorkflowStep::where('type_organisation', $typeOrganisation)
            ->where('type_operation', $typeOperation)
            ->where('is_active', true)
            ->orderBy('numero_passage', 'asc')
            ->first();
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Obtenir toutes les étapes du workflow
     */
    public function getWorkflowSteps(string $typeOrganisation, string $typeOperation = 'creation'): array
    {
        return WorkflowStep::where('type_organisation', $typeOrganisation)
            ->where('type_operation', $typeOperation)
            ->where('is_active', true)
            ->orderBy('numero_passage', 'asc')
            ->get()
            ->toArray();
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Vérifier si le workflow est configuré
     */
    public function hasWorkflowConfigured(string $typeOrganisation, string $typeOperation = 'creation'): bool
    {
        return WorkflowStep::where('type_organisation', $typeOrganisation)
            ->where('type_operation', $typeOperation)
            ->where('is_active', true)
            ->exists();
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Créer un workflow par défaut si inexistant
     */
    public function createDefaultWorkflow(string $typeOrganisation, string $typeOperation = 'creation'): bool
    {
        if ($this->hasWorkflowConfigured($typeOrganisation, $typeOperation)) {
            return true; // Déjà configuré
        }
        
        try {
            // Workflow par défaut pour tous les types d'organisations
            $defaultSteps = [
                [
                    'code' => 'reception',
                    'libelle' => 'Réception et vérification',
                    'description' => 'Vérification de la complétude du dossier',
                    'numero_passage' => 1,
                    'validation_entity_id' => 1, // Service accueil
                    'delai_traitement' => 48
                ],
                [
                    'code' => 'instruction',
                    'libelle' => 'Instruction technique',
                    'description' => 'Analyse technique du dossier',
                    'numero_passage' => 2,
                    'validation_entity_id' => 2, // Service technique
                    'delai_traitement' => 72
                ],
                [
                    'code' => 'validation',
                    'libelle' => 'Validation finale',
                    'description' => 'Validation et délivrance du récépissé',
                    'numero_passage' => 3,
                    'validation_entity_id' => 3, // Direction
                    'delai_traitement' => 24
                ]
            ];
            
            foreach ($defaultSteps as $stepData) {
                WorkflowStep::create([
                    'code' => $stepData['code'] . '_' . $typeOrganisation,
                    'libelle' => $stepData['libelle'],
                    'description' => $stepData['description'],
                    'type_organisation' => $typeOrganisation,
                    'type_operation' => $typeOperation,
                    'numero_passage' => $stepData['numero_passage'],
                    'is_active' => true,
                    'permet_rejet' => true,
                    'permet_commentaire' => true,
                    'delai_traitement' => $stepData['delai_traitement']
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            \Log::error('Erreur lors de la création du workflow par défaut: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Avancer le dossier à l'étape suivante
     */
    public function moveToNextStep(Dossier $dossier, array $data = []): bool
    {
        DB::beginTransaction();
        
        try {
            // Vérifier que le dossier peut avancer
            if (!$this->canMoveForward($dossier)) {
                throw new Exception('Le dossier ne peut pas avancer à l\'étape suivante');
            }
            
            // Obtenir l'étape suivante
            $nextStep = $dossier->getNextStep();
            if (!$nextStep) {
                // Si pas d'étape suivante, le dossier est terminé
                $dossier->update([
                    'statut' => Dossier::STATUT_ACCEPTE,
                    'validated_at' => now()
                ]);
                
                // Enregistrer l'opération
                $this->recordOperation($dossier, 'workflow_completed', $data);
                
                DB::commit();
                return true;
            }
            
            // Créer la validation pour l'étape actuelle si elle existe
            if ($dossier->current_step_id) {
                DossierValidation::create([
                    'dossier_id' => $dossier->id,
                    'workflow_step_id' => $dossier->current_step_id,
                    'validation_entity_id' => $this->getDefaultValidationEntityId(),
                    'decision' => 'approuve',
                    'validated_by' => Auth::id(),
                    'decided_at' => now(),
                    'commentaire' => $data['commentaire'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'visa' => $data['visa'] ?? null
                ]);
            }
            
            // Mettre à jour le dossier
            $dossier->update([
                'current_step_id' => $nextStep->id,
                'statut' => Dossier::STATUT_EN_COURS
            ]);
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'step_forward', array_merge($data, [
                'from_step' => $dossier->currentStep->libelle ?? 'Début',
                'to_step' => $nextStep->libelle
            ]));
            
            // Déverrouiller le dossier
            $this->unlockDossier($dossier);
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Rejeter le dossier et le renvoyer à l'étape précédente ou à l'opérateur
     */
    public function rejectDossier(Dossier $dossier, string $motif, array $data = []): bool
    {
        if (empty($motif)) {
            throw new Exception('Un motif de rejet est obligatoire');
        }
        
        DB::beginTransaction();
        
        try {
            // Créer la validation de rejet
            if ($dossier->current_step_id) {
                DossierValidation::create([
                    'dossier_id' => $dossier->id,
                    'workflow_step_id' => $dossier->current_step_id,
                    'validation_entity_id' => $this->getDefaultValidationEntityId(),
                    'decision' => 'rejete',
                    'validated_by' => Auth::id(),
                    'decided_at' => now(),
                    'commentaire' => $motif,
                    'motif_rejet' => $motif
                ]);
            }
            
            // Déterminer l'étape de retour
            $previousStep = $dossier->getPreviousStep();
            
            if ($previousStep) {
                // Retour à l'étape précédente
                $dossier->update([
                    'current_step_id' => $previousStep->id,
                    'statut' => Dossier::STATUT_EN_COURS
                ]);
            } else {
                // Retour à l'opérateur
                $dossier->update([
                    'current_step_id' => null,
                    'statut' => Dossier::STATUT_REJETE,
                    'motif_rejet' => $motif
                ]);
            }
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'rejected', array_merge($data, [
                'motif' => $motif,
                'rejected_at_step' => $dossier->currentStep->libelle ?? 'Inconnu'
            ]));
            
            // Déverrouiller le dossier
            $this->unlockDossier($dossier);
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * ✅ MÉTHODE CORRIGÉE - Verrouiller un dossier pour traitement
     */
    public function lockDossier(Dossier $dossier, User $user): DossierLock
    {
        // Vérifier si le dossier est déjà verrouillé
        if ($dossier->isLocked() && !$dossier->isLockedBy($user->id)) {
            $lockedBy = $dossier->getLockedByUser();
            throw new Exception(sprintf(
                'Ce dossier est actuellement en cours de traitement par %s',
                $lockedBy ? $lockedBy->name : 'un autre utilisateur'
            ));
        }
        
        // Si déjà verrouillé par le même utilisateur, retourner le verrou existant
        if ($dossier->isLockedBy($user->id)) {
            $existingLock = $dossier->lock;
            if ($existingLock && $existingLock->is_active) {
                return $existingLock;
            }
        }
        
        // Créer un nouveau verrou
        $lock = DossierLock::create([
            'dossier_id' => $dossier->id,
            'locked_by' => $user->id,
            'workflow_step_id' => $dossier->current_step_id,
            'session_id' => session()->getId(),
            'locked_at' => now(),
            'expires_at' => now()->addMinutes(30), // Expiration après 30 minutes
            'is_active' => true,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        // Enregistrer l'opération
        $this->recordOperation($dossier, 'locked', [
            'locked_by' => $user->name
        ]);
        
        return $lock;
    }
    
    /**
     * ✅ MÉTHODE CORRIGÉE - Déverrouiller un dossier
     */
    public function unlockDossier(Dossier $dossier): bool
    {
        $lock = $dossier->lock;
        
        if ($lock && $lock->is_active) {
            $lock->update([
                'is_active' => false
            ]);
            
            // Enregistrer l'opération
            $this->recordOperation($dossier, 'unlocked');
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtenir le prochain dossier à traiter (FIFO)
     */
    public function getNextDossierToProcess(User $user): ?Dossier
    {
        // Obtenir les entités de validation de l'utilisateur
        $validationEntities = $user->validationEntities()->pluck('id');
        
        if ($validationEntities->isEmpty()) {
            return null;
        }
        
        // Chercher le prochain dossier non verrouillé
        return Dossier::whereIn('statut', [Dossier::STATUT_SOUMIS, Dossier::STATUT_EN_COURS])
            ->whereHas('currentStep', function ($query) use ($validationEntities) {
                $query->whereIn('validation_entity_id', $validationEntities);
            })
            ->whereDoesntHave('lock', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('submitted_at', 'asc')
            ->first();
    }
    
    /**
     * Attribuer un dossier spécifique à un agent (réservé aux managers)
     */
    public function assignDossierToAgent(Dossier $dossier, User $agent, User $manager): bool
    {
        // Vérifier que le manager a les droits
        if (!$manager->hasRole(['admin', 'manager'])) {
            throw new Exception('Seuls les managers peuvent attribuer des dossiers');
        }
        
        DB::beginTransaction();
        
        try {
            // Verrouiller le dossier pour l'agent
            $this->lockDossier($dossier, $agent);
            
            // Mettre à jour l'assignation
            $dossier->update([
                'assigned_to' => $agent->id
            ]);
            
            // Enregistrer l'attribution
            $this->recordOperation($dossier, 'assigned', [
                'assigned_to' => $agent->name,
                'assigned_by' => $manager->name
            ]);
            
            // Notification à l'agent
            // TODO: Implémenter la notification
            
            DB::commit();
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Vérifier si un dossier peut avancer
     */
    protected function canMoveForward(Dossier $dossier): bool
    {
        // Le dossier doit être en cours ou soumis
        if (!in_array($dossier->statut, [Dossier::STATUT_SOUMIS, Dossier::STATUT_EN_COURS])) {
            return false;
        }
        
        // Vérifier que tous les documents obligatoires sont validés
        // TODO: Implémenter hasAllRequiredDocuments si nécessaire
        // if (!$dossier->hasAllRequiredDocuments()) {
        //     return false;
        // }
        
        return true;
    }
    
    /**
     * ✅ MÉTHODE CORRIGÉE - Enregistrer une opération sur le dossier
     */
    protected function recordOperation(Dossier $dossier, string $type, array $data = []): DossierOperation
    {
        // Mapper les types personnalisés vers les types de la DB
        $typeMapping = [
            'workflow_initialized' => 'creation',
            'workflow_completed' => 'validation', 
            'step_forward' => 'validation',
            'rejected' => 'rejet',
            'locked' => 'verrouillage',
            'unlocked' => 'deverrouillage',
            'assigned' => 'assignation',
            'lock_expired' => 'deverrouillage'
        ];
        
        $dbType = $typeMapping[$type] ?? 'modification';
        
        return DossierOperation::create([
            'dossier_id' => $dossier->id,
            'user_id' => Auth::id(),
            'type_operation' => $dbType,
            'ancien_statut' => $dossier->getOriginal('statut'),
            'nouveau_statut' => $dossier->statut,
            'workflow_step_id' => $dossier->current_step_id,
            'description' => $this->getOperationDescription($type, $data),
            'donnees_avant' => $dossier->getOriginal(),
            'donnees_apres' => array_merge($dossier->getAttributes(), $data),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Générer description d'opération
     */
    protected function getOperationDescription(string $type, array $data = []): string
    {
        $descriptions = [
            'workflow_initialized' => 'Workflow initialisé',
            'workflow_completed' => 'Workflow terminé avec succès',
            'step_forward' => 'Passage à l\'étape suivante',
            'rejected' => 'Dossier rejeté : ' . ($data['motif'] ?? ''),
            'locked' => 'Dossier verrouillé pour traitement',
            'unlocked' => 'Dossier déverrouillé',
            'assigned' => 'Dossier assigné à ' . ($data['assigned_to'] ?? 'un agent'),
            'lock_expired' => 'Verrou expiré automatiquement'
        ];
        
        return $descriptions[$type] ?? "Opération: $type";
    }
    
    /**
     * Obtenir l'historique complet d'un dossier
     */
    public function getDossierHistory(Dossier $dossier): array
    {
        $history = [];
        
        // Opérations
        foreach ($dossier->operations()->with('user')->orderBy('created_at', 'desc')->get() as $operation) {
            $history[] = [
                'type' => 'operation',
                'action' => $operation->type_operation,
                'user' => $operation->user ? $operation->user->name : 'Système',
                'date' => $operation->created_at,
                'description' => $operation->description,
                'details' => $operation->donnees_apres ?? []
            ];
        }
        
        // Validations
        foreach ($dossier->validations()->with(['validatedBy', 'workflowStep'])->orderBy('decided_at', 'desc')->get() as $validation) {
            if ($validation->decided_at) {
                $history[] = [
                    'type' => 'validation',
                    'action' => $validation->decision,
                    'user' => $validation->validatedBy ? $validation->validatedBy->name : 'Inconnu',
                    'date' => $validation->decided_at,
                    'step' => $validation->workflowStep ? $validation->workflowStep->libelle : 'Inconnu',
                    'details' => [
                        'commentaire' => $validation->commentaire,
                        'motif_rejet' => $validation->motif_rejet
                    ]
                ];
            }
        }
        
        // Trier par date (les plus récents en premier)
        usort($history, function ($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });
        
        return $history;
    }
    
    /**
     * Obtenir les statistiques du workflow
     */
    public function getWorkflowStatistics(string $typeOrganisation = null, string $typeOperation = null): array
    {
        $query = Dossier::query();
        
        if ($typeOrganisation) {
            $query->whereHas('organisation', function ($q) use ($typeOrganisation) {
                $q->where('type', $typeOrganisation);
            });
        }
        
        if ($typeOperation) {
            $query->where('type_operation', $typeOperation);
        }
        
        $stats = [
            'total' => $query->count(),
            'par_statut' => [],
            'temps_moyen_traitement' => null,
            'taux_approbation' => null
        ];
        
        // Dossiers par statut
        $parStatut = (clone $query)->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();
        
        foreach ($parStatut as $stat) {
            $stats['par_statut'][$stat->statut] = $stat->total;
        }
        
        // Temps moyen de traitement (en jours)
        $dossiersTraites = (clone $query)->whereIn('statut', ['approuve', 'rejete'])
            ->whereNotNull('submitted_at')
            ->whereNotNull('validated_at')
            ->get();
        
        if ($dossiersTraites->count() > 0) {
            $totalJours = 0;
            foreach ($dossiersTraites as $dossier) {
                $totalJours += $dossier->submitted_at->diffInDays($dossier->validated_at);
            }
            $stats['temps_moyen_traitement'] = round($totalJours / $dossiersTraites->count(), 1);
        }
        
        // Taux d'approbation
        $acceptes = $stats['par_statut']['approuve'] ?? 0;
        $rejetes = $stats['par_statut']['rejete'] ?? 0;
        $total = $acceptes + $rejetes;
        
        if ($total > 0) {
            $stats['taux_approbation'] = round(($acceptes / $total) * 100, 1);
        }
        
        return $stats;
    }
    
    /**
     * Nettoyer les verrous expirés
     */
    public function cleanExpiredLocks(): int
    {
        $expired = DossierLock::where('is_active', true)
            ->where('expires_at', '<', now())
            ->get();
        
        $count = 0;
        foreach ($expired as $lock) {
            $lock->update([
                'is_active' => false
            ]);
            
            // Enregistrer l'opération
            $this->recordOperation($lock->dossier, 'lock_expired', [
                'expired_at' => $lock->expires_at->toDateTimeString()
            ]);
            
            $count++;
        }
        
        return $count;
    }
}