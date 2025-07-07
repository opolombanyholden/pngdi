<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\QrCode;
use App\Services\DossierService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\OrganisationValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
     * ✅ MÉTHODE CONFIRMATION FINALE - CORRIGÉE COMPLÈTEMENT ET RENFORCÉE
     * Afficher la page de confirmation après soumission d'organisation
     */
    public function confirmation(Request $request, $dossier)
    {
        try {
            // ✅ LOGS DE DEBUG RENFORCÉS - ENTRÉE MÉTHODE
            Log::info("=== DÉBUT CONFIRMATION METHOD DEBUG ===", [
                'dossier_param' => $dossier,
                'dossier_type' => gettype($dossier),
                'dossier_value' => is_object($dossier) ? get_class($dossier) : $dossier,
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email ?? 'Unknown',
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'timestamp' => now()->toDateTimeString(),
                'all_request_params' => $request->all()
            ]);

            // ✅ CORRECTION 1: Gérer le cas où $dossier est déjà un objet OU un ID
            if (is_object($dossier) && is_a($dossier, 'App\Models\Dossier')) {
                // $dossier est déjà un objet Dossier (binding automatique Laravel)
                $dossierObj = $dossier;
                Log::info("=== DOSSIER REÇU COMME OBJET ===", [
                    'dossier_id' => $dossierObj->id,
                    'class' => get_class($dossierObj)
                ]);
            } else {
                // $dossier est un ID, on doit le charger
                $dossierId = is_numeric($dossier) ? (int)$dossier : $dossier;
                
                Log::info("=== CHARGEMENT DOSSIER PAR ID ===", [
                    'dossier_id_param' => $dossierId,
                    'is_numeric' => is_numeric($dossier)
                ]);
                
                try {
                    $dossierObj = Dossier::with([
                        'organisation.adherents',
                        'organisation.fondateurs',
                        'documents'
                    ])->findOrFail($dossierId);
                    
                    Log::info("=== DOSSIER CHARGÉ AVEC SUCCÈS ===", [
                        'dossier_loaded_id' => $dossierObj->id
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error("=== ERREUR CHARGEMENT DOSSIER ===", [
                        'dossier_id_param' => $dossierId,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            // ✅ LOGS DÉTAILLÉS DU DOSSIER CHARGÉ
            Log::info("=== DÉTAILS DOSSIER CHARGÉ ===", [
                'dossier_id' => $dossierObj->id,
                'dossier_numero' => $dossierObj->numero_dossier,
                'organisation_id' => $dossierObj->organisation_id,
                'organisation_nom' => $dossierObj->organisation->nom ?? 'Unknown',
                'organisation_user_id' => $dossierObj->organisation->user_id ?? 'Unknown',
                'organisation_user_id_type' => gettype($dossierObj->organisation->user_id ?? null),
                'organisation_exists' => isset($dossierObj->organisation)
            ]);

            // ✅ CORRECTION 2: Vérification d'accès avec conversion de type ET logs détaillés
            $authUserId = (int)auth()->id();
            $orgUserId = (int)($dossierObj->organisation->user_id ?? 0);
            
            Log::info("=== VÉRIFICATION ACCÈS DÉTAILLÉE ===", [
                'auth_user_id' => $authUserId,
                'auth_user_id_type' => gettype($authUserId),
                'auth_user_raw' => auth()->id(),
                'org_user_id' => $orgUserId,
                'org_user_id_type' => gettype($orgUserId),
                'org_user_raw' => $dossierObj->organisation->user_id ?? null,
                'strict_comparison' => ($orgUserId === $authUserId),
                'loose_comparison' => ($orgUserId == $authUserId),
                'both_are_integers' => (is_int($authUserId) && is_int($orgUserId))
            ]);

            // ✅ VÉRIFICATION AVEC LOGS EXPLICITES
            if ($orgUserId !== $authUserId) {
                Log::warning("=== ACCÈS REFUSÉ - ANALYSE COMPLÈTE ===", [
                    'reason' => 'User ID mismatch après conversion',
                    'expected_user_id' => $orgUserId,
                    'actual_user_id' => $authUserId,
                    'dossier_id' => $dossierObj->id,
                    'organisation_nom' => $dossierObj->organisation->nom ?? 'Unknown',
                    'organisation_id' => $dossierObj->organisation_id,
                    'difference' => abs($orgUserId - $authUserId),
                    'auth_check' => auth()->check(),
                    'user_exists' => auth()->user() !== null
                ]);
                
                return redirect()->route('operator.dossiers.index')
                    ->with('error', 'Vous n\'êtes pas autorisé à consulter ce dossier (User ID: ' . $authUserId . ' ≠ Org User ID: ' . $orgUserId . ')');
            }

            Log::info("=== ACCÈS AUTORISÉ - CONSTRUCTION DONNÉES ===", [
                'user_match_confirmed' => true,
                'proceeding_to_data_construction' => true
            ]);

            // ✅ CORRECTION 3: Vérification délai avec logs (temporairement désactivée)
            if (false) { // Désactivé temporairement pour debug complet
                if ($dossierObj->submitted_at && $dossierObj->submitted_at->diffInHours(now()) > 24) {
                    Log::info("=== DÉLAI DÉPASSÉ ===", [
                        'submitted_at' => $dossierObj->submitted_at->toDateTimeString(),
                        'hours_diff' => $dossierObj->submitted_at->diffInHours(now()),
                        'limit_hours' => 24
                    ]);
                    
                    return redirect()->route('operator.dashboard')
                        ->with('warning', 'Cette page de confirmation n\'est plus disponible (délai dépassé).');
                }
            }

            // ✅ RÉCUPÉRATION/RECONSTRUCTION DES DONNÉES
            $sessionData = session('success_data');
            
            if (!$sessionData) {
                Log::info("=== RECONSTRUCTION DONNÉES SESSION ===");
                $sessionData = $this->reconstructConfirmationData($dossierObj);
                Log::info("=== DONNÉES SESSION RECONSTRUITES ===", [
                    'has_adherents_stats' => isset($sessionData['adherents_stats']),
                    'has_anomalies' => isset($sessionData['anomalies'])
                ]);
            } else {
                Log::info("=== DONNÉES SESSION TROUVÉES ===", [
                    'session_data_keys' => array_keys($sessionData)
                ]);
            }

            // ✅ CONSTRUCTION DES DONNÉES DE CONFIRMATION AVEC LOGS
            Log::info("=== DÉBUT CONSTRUCTION CONFIRMATION DATA ===");
            
            $confirmationData = [
                'organisation' => $dossierObj->organisation,
                'dossier' => $dossierObj,
                'numero_recepisse' => $dossierObj->organisation->numero_recepisse ?? 'Non attribué',
                'numero_dossier' => $dossierObj->numero_dossier ?? 'Non attribué',
                'qr_code' => $this->getQrCodeForDossier($dossierObj),
                'adherents_stats' => $sessionData['adherents_stats'] ?? $this->calculateAdherentsStats($dossierObj),
                'anomalies' => $sessionData['anomalies'] ?? $this->getAnomaliesFromDossier($dossierObj),
                'accuse_reception_path' => $this->getAccuseReceptionPath($dossierObj),
                'accuse_reception_url' => $this->getAccuseReceptionDownloadUrl($dossierObj),
                'delai_traitement' => '72 heures ouvrées',
                'message_legal' => $this->getMessageLegal(),
                'prochaines_etapes' => $this->getProchainesEtapes(),
                'contact_support' => $this->getContactSupport(),
                'submitted_at' => $dossierObj->submitted_at ?? $dossierObj->created_at ?? now(),
                'estimated_completion' => $this->calculateEstimatedCompletion($dossierObj)
            ];

            Log::info("=== CONFIRMATION DATA CONSTRUITE ===", [
                'data_keys' => array_keys($confirmationData),
                'organisation_name' => $confirmationData['organisation']->nom ?? 'Unknown',
                'dossier_numero' => $confirmationData['numero_dossier'],
                'qr_code_found' => $confirmationData['qr_code'] !== null,
                'qr_code_id' => $confirmationData['qr_code'] ? $confirmationData['qr_code']->id : null
            ]);

            // Nettoyer la session
            session()->forget('success_data');
            Log::info("=== SESSION NETTOYÉE ===");

            // ✅ LOG FINAL DE SUCCÈS
            Log::info('=== PAGE CONFIRMATION CONSULTÉE AVEC SUCCÈS ===', [
                'user_id' => auth()->id(),
                'dossier_id' => $dossierObj->id,
                'organisation_nom' => $dossierObj->organisation->nom ?? 'Unknown',
                'access_time' => now()->toDateTimeString(),
                'organisation_type' => $dossierObj->organisation->type ?? 'Unknown',
                'success' => true
            ]);

            Log::info("=== RETOUR VUE CONFIRMATION ===", [
                'view_name' => 'operator.dossiers.confirmation',
                'compact_variable' => 'confirmationData'
            ]);

            // ✅ RETOUR DE LA VUE
            return view('operator.dossiers.confirmation', compact('confirmationData'));

        } catch (\Exception $e) {
            // ✅ GESTION D'ERREUR COMPLÈTE AVEC LOGS DÉTAILLÉS
            Log::error('=== ERREUR CRITIQUE MÉTHODE CONFIRMATION ===', [
                'user_id' => auth()->id(),
                'dossier_param' => $dossier,
                'dossier_type' => gettype($dossier),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_class' => get_class($e),
                'request_url' => $request->fullUrl() ?? 'Unknown',
                'timestamp' => now()->toDateTimeString(),
                'trace_preview' => substr($e->getTraceAsString(), 0, 500)
            ]);

            return redirect()->route('operator.dashboard')
                ->with('error', 'Impossible d\'afficher la page de confirmation. Erreur: ' . $e->getMessage() . ' (Ligne: ' . $e->getLine() . ')');
        }
    }

    /**
     * ✅ MÉTHODE AUXILIAIRE - Reconstituer les données de confirmation
     */
    private function reconstructConfirmationData(Dossier $dossier)
    {
        $donneesSupplementaires = [];
        
        // Décoder les données JSON de manière sécurisée
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $decoded = json_decode($dossier->donnees_supplementaires, true);
                $donneesSupplementaires = $decoded && is_array($decoded) ? $decoded : [];
            } elseif (is_array($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = $dossier->donnees_supplementaires;
            }
        }
        
        $adherentsStats = $this->calculateAdherentsStats($dossier);
        
        return [
            'adherents_stats' => $adherentsStats,
            'anomalies' => $donneesSupplementaires['adherents_anomalies'] ?? []
        ];
    }

    /**
     * ✅ MÉTHODE AUXILIAIRE CORRIGÉE - Calculer les statistiques des adhérents  
     * CORRECTION: Gestion plus robuste des données JSON
     */
    private function calculateAdherentsStats(Dossier $dossier)
    {
        try {
            $organisation = $dossier->organisation;
            
            $totalAdherents = $organisation->adherents()->count();
            $adherentsValides = $organisation->adherents()
                ->where('is_active', true)
                ->count();
            
            // ✅ CORRECTION: Décoder les données JSON de manière plus sécurisée
            $donneesSupplementaires = [];
            
            if (!empty($dossier->donnees_supplementaires)) {
                if (is_string($dossier->donnees_supplementaires)) {
                    $decoded = json_decode($dossier->donnees_supplementaires, true);
                    $donneesSupplementaires = $decoded && is_array($decoded) ? $decoded : [];
                } elseif (is_array($dossier->donnees_supplementaires)) {
                    $donneesSupplementaires = $dossier->donnees_supplementaires;
                }
            }
            
            $anomalies = $donneesSupplementaires['adherents_anomalies'] ?? [];
            
            // Compter les anomalies par niveau
            $anomaliesCritiques = 0;
            $anomaliesMajeures = 0;
            $anomaliesMineures = 0;
            
            if (is_array($anomalies)) {
                foreach ($anomalies as $anomalie) {
                    if (isset($anomalie['anomalies']) && is_array($anomalie['anomalies'])) {
                        $anomaliesAdherent = $anomalie['anomalies'];
                        
                        if (!empty($anomaliesAdherent['critiques'])) {
                            $anomaliesCritiques++;
                        }
                        if (!empty($anomaliesAdherent['majeures'])) {
                            $anomaliesMajeures++;
                        }
                        if (!empty($anomaliesAdherent['mineures'])) {
                            $anomaliesMineures++;
                        }
                    }
                }
            }
            
            return [
                'total' => $totalAdherents,
                'valides' => $adherentsValides,
                'anomalies_critiques' => $anomaliesCritiques,
                'anomalies_majeures' => $anomaliesMajeures,
                'anomalies_mineures' => $anomaliesMineures
            ];
            
        } catch (\Exception $e) {
            Log::error('=== ERREUR CALCUL STATS ADHÉRENTS ===', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            
            // Retourner des stats par défaut en cas d'erreur
            return [
                'total' => 0,
                'valides' => 0,
                'anomalies_critiques' => 0,
                'anomalies_majeures' => 0,
                'anomalies_mineures' => 0
            ];
        }
    }

    /**
     * ✅ MÉTHODE AUXILIAIRE - Obtenir les anomalies depuis le dossier
     */
    private function getAnomaliesFromDossier(Dossier $dossier)
    {
        $donneesSupplementaires = [];
        
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $decoded = json_decode($dossier->donnees_supplementaires, true);
                $donneesSupplementaires = $decoded && is_array($decoded) ? $decoded : [];
            } elseif (is_array($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = $dossier->donnees_supplementaires;
            }
        }
        
        return $donneesSupplementaires['adherents_anomalies'] ?? [];
    }

    /**
     * ✅ MÉTHODE CORRIGÉE - Obtenir le QR Code pour le dossier
     * CORRECTION: Utiliser les bons types et champs du modèle QrCode corrigé
     */
    private function getQrCodeForDossier(Dossier $dossier)
    {
        try {
            Log::info('=== RECHERCHE QR CODE POUR DOSSIER (CORRIGÉ) ===', [
                'dossier_id' => $dossier->id,
                'using_corrected_model' => true,
                'expected_type' => QrCode::TYPE_DOSSIER
            ]);

            // ✅ CORRECTION: Utiliser les constantes et champs du modèle corrigé
            $qrCode = QrCode::where('verifiable_type', 'App\\Models\\Dossier')
                ->where('verifiable_id', $dossier->id)
                ->where('type', QrCode::TYPE_DOSSIER) // Utiliser la constante
                ->where('is_active', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            Log::info('=== QR CODE RÉSULTAT (CORRIGÉ) ===', [
                'dossier_id' => $dossier->id,
                'qr_code_found' => $qrCode !== null,
                'qr_code_id' => $qrCode ? $qrCode->id : null,
                'qr_code_code' => $qrCode ? $qrCode->code : null,
                'qr_code_type' => $qrCode ? $qrCode->type : null
            ]);

            return $qrCode;

        } catch (\Exception $e) {
            Log::error('=== ERREUR RECHERCHE QR CODE (CORRIGÉ) ===', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            // Retourner null en cas d'erreur pour ne pas bloquer la page
            return null;
        }
    }

    /**
     * ✅ MÉTHODE AUXILIAIRE - Obtenir le chemin de l'accusé de réception
     */
   private function getAccuseReceptionPath(Dossier $dossier)
{
    try {
        Log::info('=== RECHERCHE CHEMIN ACCUSÉ AVEC CORRECTION ===', [
            'dossier_id' => $dossier->id,
            'correction_applied' => 'removed_type_document_column'
        ]);

        // ✅ CORRECTION: Supprimer la condition sur 'type_document' qui n'existe pas
        $accuseDocument = $dossier->documents()
            ->where(function($query) {
                $query->where('nom_fichier', 'LIKE', 'accuse_reception_%')
                      ->orWhere('nom_fichier', 'LIKE', 'accuse_phase1_%')
                      ->orWhere('is_system_generated', 1); // Supprimer type_document
            })
            ->orderBy('created_at', 'desc')
            ->first();
        
        Log::info('=== RÉSULTAT RECHERCHE ACCUSÉ CORRIGÉ ===', [
            'dossier_id' => $dossier->id,
            'document_found' => $accuseDocument !== null,
            'document_id' => $accuseDocument ? $accuseDocument->id : null,
            'document_nom' => $accuseDocument ? $accuseDocument->nom_fichier : null
        ]);
        
        if ($accuseDocument && $accuseDocument->chemin_fichier) {
            $fullPath = storage_path('app/public/' . $accuseDocument->chemin_fichier);
            
            if (file_exists($fullPath)) {
                Log::info('=== ACCUSÉ TROUVÉ ET ACCESSIBLE ===', [
                    'path' => $accuseDocument->chemin_fichier,
                    'file_exists' => true
                ]);
                return $accuseDocument->chemin_fichier;
            } else {
                Log::warning('=== ACCUSÉ TROUVÉ MAIS FICHIER ABSENT ===', [
                    'expected_path' => $fullPath,
                    'file_exists' => false
                ]);
            }
        }
        
        Log::info('=== AUCUN ACCUSÉ TROUVÉ ===', [
            'dossier_id' => $dossier->id,
            'reason' => 'no_matching_documents'
        ]);
        
        return null;
        
    } catch (\Exception $e) {
        Log::error('=== ERREUR RECHERCHE ACCUSÉ CORRIGÉE ===', [
            'dossier_id' => $dossier->id,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'correction_note' => 'removed_type_document_reference'
        ]);
        
        // Retourner null pour ne pas bloquer la page
        return null;
    }
}

    /**
     * ✅ MÉTHODE AUXILIAIRE CORRIGÉE - Obtenir l'URL de téléchargement de l'accusé
     * CORRECTION: Meilleure recherche des documents d'accusé
     */
    /**
 * ✅ MÉTHODE AUXILIAIRE CORRIGÉE - Obtenir l'URL de téléchargement de l'accusé
 */
private function getAccuseReceptionDownloadUrl(Dossier $dossier)
{
    try {
        $accusePath = $this->getAccuseReceptionPath($dossier);
        
        if ($accusePath) {
            // Générer l'URL de téléchargement sécurisée
            return route('operator.dossiers.download-accuse', ['path' => basename($accusePath)]);
        }
        
        return null;
        
    } catch (\Exception $e) {
        Log::error('=== ERREUR GÉNÉRATION URL ACCUSÉ ===', [
            'dossier_id' => $dossier->id,
            'error' => $e->getMessage()
        ]);
        
        return null;
    }
}

    /**
     * ✅ MÉTHODE AUXILIAIRE - Message légal conforme
     */
    private function getMessageLegal()
    {
        return 'Votre dossier numérique a été soumis avec succès. Aux fins de recevoir votre accusé de réception, conformément aux dispositions de l\'article 26 de la loi No 016/2025 relative aux partis politiques en République Gabonaise, vous êtes invité à déposer votre dossier physique, en 3 exemplaires, auprès des services de la Direction Générale des Élections et des Libertés Publiques du Ministère de l\'Intérieur, de la Sécurité et de la Décentralisation, en application des dispositions de l\'article 24 de la loi suscitée.';
    }

    /**
     * ✅ MÉTHODE AUXILIAIRE - Prochaines étapes
     */
    private function getProchainesEtapes()
    {
        return [
            [
                'numero' => 1,
                'titre' => 'Assignation d\'un agent',
                'description' => 'Un agent sera assigné à votre dossier sous 48h ouvrées',
                'delai' => '48h ouvrées'
            ],
            [
                'numero' => 2,
                'titre' => 'Examen du dossier',
                'description' => 'Votre dossier sera examiné selon l\'ordre d\'arrivée (système FIFO)',
                'delai' => '72h ouvrées'
            ],
            [
                'numero' => 3,
                'titre' => 'Notification du résultat',
                'description' => 'Vous recevrez une notification par email de l\'évolution',
                'delai' => 'Variable'
            ],
            [
                'numero' => 4,
                'titre' => 'Dépôt physique requis',
                'description' => 'Déposer le dossier physique en 3 exemplaires à la DGELP',
                'delai' => 'Dans les 7 jours'
            ]
        ];
    }

    /**
     * ✅ MÉTHODE AUXILIAIRE - Contact support
     */
    private function getContactSupport()
    {
        return [
            'email' => 'support@pngdi.ga',
            'telephone' => '+241 01 23 45 67',
            'horaires' => 'Lundi - Vendredi: 08h00 - 17h00',
            'adresse' => 'Direction Générale des Élections et des Libertés Publiques, Ministère de l\'Intérieur'
        ];
    }

    /**
     * ✅ MÉTHODE AUXILIAIRE - Calculer l'estimation de completion
     */
    private function calculateEstimatedCompletion(Dossier $dossier)
    {
        $baseHours = 72; // 72h de base
        
        // Ajuster selon le type d'organisation
        switch ($dossier->organisation->type) {
            case 'parti_politique':
                $baseHours += 24; // Plus complexe
                break;
            case 'confession_religieuse':
                $baseHours += 12;
                break;
            default:
                // Association et ONG gardent le délai de base
                break;
        }
        
        // Ajouter selon le nombre d'adhérents
        $nombreAdherents = $dossier->organisation->adherents()->count();
        if ($nombreAdherents > 100) {
            $baseHours += 12;
        } elseif ($nombreAdherents > 50) {
            $baseHours += 6;
        }
        
        return now()->addHours($baseHours);
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

    /**
     * ✅ MÉTHODE MANQUANTE - Gestion des anomalies
     */
    public function anomalies(Request $request)
    {
        $query = \App\Models\Adherent::where('has_anomalies', true)
            ->whereHas('organisation', function($q) {
                $q->where('user_id', auth()->id());
            });

        if ($request->has('severity')) {
            $query->where('anomalies_severity', $request->severity);
        }

        if ($request->has('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        $anomalies = $query->with(['organisation'])->paginate(15);

        $organisations = \App\Models\Organisation::where('user_id', auth()->id())->get();

        return view('operator.dossiers.anomalies', compact('anomalies', 'organisations'));
    }

    /**
     * ✅ MÉTHODE MANQUANTE - Résoudre une anomalie
     */
    public function resolveAnomalie(Request $request, $adherentId)
    {
        $request->validate([
            'anomalie_code' => 'required|string',
            'resolution_details' => 'required|string',
            'action' => 'required|in:resolve,exclude,update'
        ]);

        try {
            $adherent = \App\Models\Adherent::findOrFail($adherentId);

            if ($adherent->organisation->user_id !== auth()->id()) {
                abort(403);
            }

            $result = $adherent->resolveAnomalie(
                $request->anomalie_code,
                [
                    'action' => $request->action,
                    'details' => $request->resolution_details,
                    'resolved_by' => auth()->id()
                ]
            );

            if ($result) {
                return redirect()->back()->with('success', 'Anomalie résolue avec succès');
            } else {
                return redirect()->back()->with('error', 'Impossible de résoudre cette anomalie');
            }

        } catch (\Exception $e) {
            Log::error('Erreur résolution anomalie: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la résolution de l\'anomalie');
        }
    }
}