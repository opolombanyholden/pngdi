<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\Document;
use App\Models\DocumentType;
use App\Services\DossierService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\OrganisationValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class DossierController extends Controller
{
    protected $dossierService;
    protected $fileUploadService;
    protected $notificationService;
    protected $validationService;
    
    public function __construct(
        DossierService $dossierService,
        FileUploadService $fileUploadService,
        NotificationService $notificationService,
        OrganisationValidationService $validationService
    ) {
        $this->dossierService = $dossierService;
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
        $this->validationService = $validationService;
    }
    
    /**
     * Afficher la liste des dossiers
     */
    public function index(Request $request)
    {
        $query = Dossier::whereHas('organisation', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['organisation', 'currentStep']);
        
        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        if ($request->has('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }
        
        if ($request->has('type_operation')) {
            $query->where('type_operation', $request->type_operation);
        }
        
        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_dossier', 'like', "%{$search}%")
                    ->orWhereHas('organisation', function ($q2) use ($search) {
                        $q2->where('nom', 'like', "%{$search}%");
                    });
            });
        }
        
        $dossiers = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Organisations pour le filtre
        $organisations = Organisation::where('user_id', Auth::id())
            ->orderBy('nom')
            ->get();
        
        return view('operator.dossiers.index', compact('dossiers', 'organisations'));
    }

    /**
     * Afficher le formulaire de création selon le type
     */
    public function create($type)
    {
        // Mapper les types courts vers les types complets
        $typeMapping = [
            'association' => Organisation::TYPE_ASSOCIATION,
            'ong' => Organisation::TYPE_ONG,
            'parti' => Organisation::TYPE_PARTI,
            'confession' => Organisation::TYPE_CONFESSION
        ];
        
        if (!isset($typeMapping[$type])) {
            abort(404, 'Type d\'organisation non reconnu');
        }
        
        $fullType = $typeMapping[$type];
        
        // Vérifier les limites de création
        $limits = $this->checkOrganisationLimits(Auth::user(), $fullType);
        
        if (!$limits['can_create']) {
            return redirect()->route('operator.dashboard')
                ->with('error', $limits['message']);
        }
        
        // Directement afficher le formulaire de création au lieu de rediriger vers un guide
        $provinces = $this->getProvinces();
        $guides = $this->getGuideContent($fullType);
        $documentTypes = $this->getRequiredDocuments($fullType);
        
        return view('operator.dossiers.create', compact('type', 'fullType', 'provinces', 'guides', 'documentTypes'));
    }

    /**
     * Enregistrer un nouveau dossier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'type_operation' => 'required|in:creation,modification,cessation,declaration'
        ]);
        
        // Vérifier que l'organisation appartient à l'utilisateur
        $organisation = Organisation::where('id', $validated['organisation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        try {
            // Créer le dossier via le service
            $dossier = $this->dossierService->createDossier([
                'organisation_id' => $organisation->id,
                'type_operation' => $validated['type_operation']
            ]);
            
            return redirect()->route('operator.dossiers.edit', $dossier->id)
                ->with('success', 'Dossier créé avec succès. Complétez les informations requises.');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un dossier spécifique
     */
    public function show($dossier)
    {
        $dossier = Dossier::with([
            'organisation',
            'documents.documentType',
            'currentStep.validationEntity',
            'validations.validatedBy',
            'comments.user'
        ])->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Obtenir le statut détaillé
        $status = $this->dossierService->getDossierStatus($dossier);
        
        return view('operator.dossiers.show', compact('dossier', 'status'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($dossier)
    {
        $dossier = Dossier::with(['organisation', 'documents'])->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$dossier->canBeModified()) {
            return redirect()->route('operator.dossiers.show', $dossier->id)
                ->with('error', 'Ce dossier ne peut plus être modifié');
        }
        
        // Documents requis
        $requiredDocuments = DocumentType::where('type_organisation', $dossier->organisation->type)
            ->where(function ($query) use ($dossier) {
                $query->where('type_operation', $dossier->type_operation)
                    ->orWhereNull('type_operation');
            })
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();
        
        return view('operator.dossiers.edit', compact('dossier', 'requiredDocuments'));
    }

    /**
     * Mettre à jour un dossier
     */
    public function update(Request $request, $dossier)
    {
        $dossier = Dossier::findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Sauvegarder les métadonnées
        $dossier->update([
            'metadata' => array_merge($dossier->metadata ?? [], [
                'last_updated' => now()->toDateTimeString(),
                'form_data' => $request->except(['_token', '_method'])
            ])
        ]);
        
        return redirect()->route('operator.dossiers.edit', $dossier->id)
            ->with('success', 'Modifications enregistrées');
    }

    /**
     * Soumettre un dossier
     */
    public function soumettre($dossier)
    {
        $dossier = Dossier::with('organisation')->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        try {
            // Valider l'organisation
            $validation = $this->validationService->validateBeforeSubmission($dossier->organisation);
            
            if (!$validation['is_valid']) {
                return redirect()->back()
                    ->with('error', 'Validation échouée')
                    ->with('validation_errors', $validation['errors']);
            }
            
            // Soumettre le dossier
            $this->dossierService->submitDossier($dossier);
            
            return redirect()->route('operator.dossiers.show', $dossier->id)
                ->with('success', 'Dossier soumis avec succès');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la soumission : ' . $e->getMessage());
        }
    }

    /**
     * Uploader un document
     */
    public function uploadDocument(Request $request, $dossier)
    {
        $dossier = Dossier::findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'document' => 'required|file|max:10240', // 10MB max
            'document_type_id' => 'required|exists:document_types,id'
        ]);
        
        try {
            // Ajouter le document via le service
            $document = $this->dossierService->addDocument(
                $dossier,
                $request->document_type_id,
                $request->file('document')
            );
            
            return redirect()->back()
                ->with('success', 'Document téléchargé avec succès');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du téléchargement : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument($dossier, $document)
    {
        $dossier = Dossier::findOrFail($dossier);
        $document = Document::findOrFail($document);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id() || $document->dossier_id !== $dossier->id) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$dossier->canBeModified()) {
            return redirect()->back()
                ->with('error', 'Ce dossier ne peut plus être modifié');
        }
        
        // Supprimer le fichier physique
        if ($document->chemin_fichier && Storage::exists($document->chemin_fichier)) {
            Storage::delete($document->chemin_fichier);
        }
        
        // Supprimer l'enregistrement
        $document->delete();
        
        return redirect()->back()
            ->with('success', 'Document supprimé');
    }

    /**
     * Télécharger un document
     */
    public function downloadDocument($document)
    {
        $document = Document::with(['dossier.organisation'])->findOrFail($document);
        
        // Vérifier l'accès
        if ($document->dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le fichier existe
        if (!$document->fileExists()) {
            return redirect()->back()
                ->with('error', 'Fichier introuvable');
        }
        
        return Storage::download($document->chemin_fichier, $document->nom_original);
    }

    /**
     * Liste des subventions
     */
    public function subventionsIndex()
    {
        $organisations = Organisation::where('user_id', Auth::id())
            ->where('is_active', true)
            ->where('statut', Organisation::STATUT_APPROUVE)
            ->with(['declarations' => function ($query) {
                $query->where('type', 'subvention')
                    ->orderBy('created_at', 'desc');
            }])
            ->get();
        
        return view('operator.dossiers.subventions.index', compact('organisations'));
    }

    /**
     * Créer une demande de subvention
     */
    public function subventionCreate($organisation)
    {
        $organisation = Organisation::where('id', $organisation)
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->where('statut', Organisation::STATUT_APPROUVE)
            ->firstOrFail();
        
        // Vérifier qu'il n'y a pas de demande en cours
        $demandeEnCours = $organisation->declarations()
            ->where('type', 'subvention')
            ->whereIn('statut', ['brouillon', 'soumis', 'en_cours'])
            ->exists();
        
        if ($demandeEnCours) {
            return redirect()->route('operator.dossiers.subventions.index')
                ->with('error', 'Une demande de subvention est déjà en cours pour cette organisation');
        }
        
        return view('operator.dossiers.subventions.create', compact('organisation'));
    }

    /**
     * Enregistrer une demande de subvention
     */
    public function subventionStore(Request $request)
    {
        $validated = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'montant_demande' => 'required|numeric|min:0',
            'objet' => 'required|string',
            'description' => 'required|string',
            'budget_previsionnel' => 'required|file|mimes:pdf,doc,docx|max:5120'
        ]);
        
        // Vérifier l'organisation
        $organisation = Organisation::where('id', $validated['organisation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        DB::beginTransaction();
        
        try {
            // Créer la déclaration de subvention
            $declaration = $organisation->declarations()->create([
                'type' => 'subvention',
                'annee' => date('Y'),
                'data' => [
                    'montant_demande' => $validated['montant_demande'],
                    'objet' => $validated['objet'],
                    'description' => $validated['description']
                ],
                'statut' => 'brouillon'
            ]);
            
            // Upload du budget prévisionnel
            if ($request->hasFile('budget_previsionnel')) {
                $path = $request->file('budget_previsionnel')->store('subventions/' . $declaration->id);
                $declaration->update([
                    'data' => array_merge($declaration->data, [
                        'budget_previsionnel_path' => $path
                    ])
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('operator.dossiers.subventions.show', $declaration->id)
                ->with('success', 'Demande de subvention créée avec succès');
            
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher une demande de subvention
     */
    public function subventionShow($subvention)
    {
        $declaration = \App\Models\Declaration::with('organisation')
            ->where('type', 'subvention')
            ->findOrFail($subvention);
        
        // Vérifier l'accès
        if ($declaration->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('operator.dossiers.subventions.show', compact('declaration'));
    }
    
    /**
     * Vérifier les limites de création d'organisation
     */
    protected function checkOrganisationLimits($user, $type)
    {
        // Pour les partis politiques
        if ($type === Organisation::TYPE_PARTI) {
            $hasActiveParti = Organisation::where('user_id', $user->id)
                ->where('type', Organisation::TYPE_PARTI)
                ->where('is_active', true)
                ->exists();
            
            if ($hasActiveParti) {
                return [
                    'can_create' => false,
                    'message' => 'Vous avez déjà un parti politique actif. Un opérateur ne peut créer qu\'un seul parti politique.'
                ];
            }
        }
        
        // Pour les confessions religieuses
        if ($type === Organisation::TYPE_CONFESSION) {
            $hasActiveConfession = Organisation::where('user_id', $user->id)
                ->where('type', Organisation::TYPE_CONFESSION)
                ->where('is_active', true)
                ->exists();
            
            if ($hasActiveConfession) {
                return [
                    'can_create' => false,
                    'message' => 'Vous avez déjà une confession religieuse active. Un opérateur ne peut créer qu\'une seule confession religieuse.'
                ];
            }
        }
        
        return [
            'can_create' => true,
            'message' => ''
        ];
    }

    /**
     * Obtenir le contenu du guide pour un type d'organisation
     */
    private function getGuideContent($type)
    {
        $guides = [
            Organisation::TYPE_PARTI => [
                'title' => 'Création d\'un parti politique',
                'description' => 'Étapes nécessaires pour créer un parti politique au Gabon',
                'requirements' => [
                    'Minimum 1000 adhérents fondateurs',
                    'Présence dans au moins 3 provinces',
                    'Programme politique détaillé',
                    'Statuts conformes à la législation'
                ],
                'documents' => [
                    'Statuts signés et légalisés',
                    'Programme politique',
                    'Liste des fondateurs avec NIP',
                    'Procès-verbal de l\'assemblée constitutive'
                ]
            ],
            Organisation::TYPE_CONFESSION => [
                'title' => 'Création d\'une confession religieuse',
                'description' => 'Procédure pour l\'enregistrement d\'une confession religieuse',
                'requirements' => [
                    'Minimum 50 fidèles fondateurs',
                    'Doctrine religieuse clairement définie',
                    'Lieu de culte identifié',
                    'Responsables spirituels qualifiés'
                ],
                'documents' => [
                    'Statuts de la confession',
                    'Doctrine religieuse',
                    'Liste des fidèles fondateurs',
                    'Attestation du lieu de culte'
                ]
            ],
            Organisation::TYPE_ASSOCIATION => [
                'title' => 'Création d\'une association',
                'description' => 'Formalités pour créer une association au Gabon',
                'requirements' => [
                    'Minimum 7 membres fondateurs',
                    'Objet social déterminé',
                    'Siège social au Gabon',
                    'Statuts conformes'
                ],
                'documents' => [
                    'Statuts de l\'association',
                    'Liste des membres fondateurs',
                    'Procès-verbal de l\'assemblée générale constitutive',
                    'Justificatif du siège social'
                ]
            ],
            Organisation::TYPE_ONG => [
                'title' => 'Création d\'une ONG',
                'description' => 'Procédure d\'enregistrement d\'une organisation non gouvernementale',
                'requirements' => [
                    'Minimum 10 membres fondateurs',
                    'Mission d\'intérêt général',
                    'Capacité d\'intervention',
                    'Transparence financière'
                ],
                'documents' => [
                    'Statuts de l\'ONG',
                    'Plan d\'action et budget prévisionnel',
                    'CV des dirigeants',
                    'Lettres d\'engagement des partenaires'
                ]
            ]
        ];

        return $guides[$type] ?? $guides[Organisation::TYPE_ASSOCIATION];
    }

    /**
     * Obtenir les documents requis selon le type d'organisation
     */
    private function getRequiredDocuments($type)
    {
        // Cette méthode devrait interroger la table document_types
        // Pour l'instant, on retourne un tableau statique
        return [
            'statuts' => ['name' => 'Statuts', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true],
            'justificatif_siege' => ['name' => 'Justificatif siège social', 'required' => false],
        ];
    }

    /**
     * Obtenir la liste des provinces du Gabon
     */
    private function getProvinces(): array
    {
        return [
            'Estuaire' => 'Estuaire',
            'Haut-Ogooué' => 'Haut-Ogooué', 
            'Moyen-Ogooué' => 'Moyen-Ogooué',
            'Ngounié' => 'Ngounié',
            'Nyanga' => 'Nyanga',
            'Ogooué-Ivindo' => 'Ogooué-Ivindo',
            'Ogooué-Lolo' => 'Ogooué-Lolo',
            'Ogooué-Maritime' => 'Ogooué-Maritime',
            'Woleu-Ntem' => 'Woleu-Ntem'
        ];
    }

    /**
     * Méthodes additionnelles pour les fonctionnalités avancées (placeholders)
     */
    
    // Gestion des brouillons
    public function brouillons()
    {
        return redirect()->route('operator.dossiers.index', ['statut' => 'brouillon']);
    }

    public function saveDraft(Request $request, $dossier)
    {
        return redirect()->back()->with('info', 'Fonctionnalité sauvegarde brouillon en cours de développement');
    }

    public function restoreDraft(Request $request, $dossier)
    {
        return redirect()->back()->with('info', 'Fonctionnalité restauration brouillon en cours de développement');
    }

    // Historique et timeline
    public function historique($dossier)
    {
        return redirect()->route('operator.dossiers.show', $dossier)->with('info', 'Historique en cours de développement');
    }

    public function timeline($dossier)
    {
        return redirect()->route('operator.dossiers.show', $dossier)->with('info', 'Timeline en cours de développement');
    }

    // Gestion des verrous
    public function extendLock(Request $request, $dossier)
    {
        return response()->json(['message' => 'Extension verrou en cours de développement']);
    }

    public function releaseLock(Request $request, $dossier)
    {
        return response()->json(['message' => 'Libération verrou en cours de développement']);
    }

    // Templates et duplication
    public function duplicate(Request $request, $dossier)
    {
        return redirect()->back()->with('info', 'Duplication dossier en cours de développement');
    }

    public function saveAsTemplate(Request $request, $dossier)
    {
        return redirect()->back()->with('info', 'Sauvegarde comme modèle en cours de développement');
    }

    public function templates()
    {
        return view('operator.dossiers.templates-placeholder');
    }

    public function createFromTemplate(Request $request, $template)
    {
        return redirect()->route('operator.dossiers.index')->with('info', 'Création depuis modèle en cours de développement');
    }

    // Commentaires
    public function addComment(Request $request, $dossier)
    {
        return redirect()->back()->with('info', 'Ajout commentaire en cours de développement');
    }

    public function updateComment(Request $request, $comment)
    {
        return redirect()->back()->with('info', 'Modification commentaire en cours de développement');
    }

    public function deleteComment($comment)
    {
        return redirect()->back()->with('info', 'Suppression commentaire en cours de développement');
    }

    // Document avancé
    public function replaceDocument(Request $request, $dossier, $document)
    {
        return redirect()->back()->with('info', 'Remplacement document en cours de développement');
    }

    public function previewDocument($dossier, $document)
    {
        return redirect()->back()->with('info', 'Prévisualisation document en cours de développement');
    }

    // Statistiques
    public function getStats()
    {
        return response()->json([
            'total_dossiers' => 0,
            'en_cours' => 0,
            'approuves' => 0,
            'rejetes' => 0
        ]);
    }
}