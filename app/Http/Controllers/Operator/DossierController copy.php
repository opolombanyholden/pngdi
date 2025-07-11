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
use Carbon\Carbon;
use App\Models\Adherent;

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
     * ✅ MÉTHODE TEMPLATE EXCEL ADHÉRENTS
     * Résout: Route [operator.templates.adherents-excel] not defined
     */
    public function downloadTemplate()
    {
        try {
            Log::info("=== TÉLÉCHARGEMENT TEMPLATE ADHÉRENTS ===", [
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            // Créer le contenu CSV du template
            $csvContent = "NIP,Nom,Prénom,Téléphone,Profession,Adresse\n";
            $csvContent .= "A1-0001-19801225,MOUNDOUNGA,Jean,+24101234567,Ingénieur,Libreville\n";
            $csvContent .= "B2-0002-19751110,OBAME,Marie,+24101234568,Professeur,Port-Gentil\n";
            $csvContent .= "C3-0003-19900315,NGUEMA,Paul,+24101234569,Médecin,Franceville\n";

            $fileName = 'template_adherents_' . date('Y-m-d') . '.csv';

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error("=== ERREUR TÉLÉCHARGEMENT TEMPLATE ===", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors du téléchargement du template : ' . $e->getMessage());
        }
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


    /**
     * ✅ MÉTHODE MANQUANTE 1: Téléchargement accusé de réception
     * Résout: Route [operator.dossiers.download-accuse] not defined
     */
    public function downloadAccuse($dossier)
    {
        try {
            Log::info("=== DÉBUT TÉLÉCHARGEMENT ACCUSÉ ===", [
                'dossier_param' => $dossier,
                'user_id' => auth()->id()
            ]);

            // Charger le dossier avec vérification d'accès
            $dossierObj = Dossier::with(['organisation', 'documents'])
                ->where('id', $dossier)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossierObj) {
                Log::error("=== DOSSIER NON TROUVÉ POUR TÉLÉCHARGEMENT ===", [
                    'dossier_id' => $dossier,
                    'user_id' => auth()->id()
                ]);
                
                return redirect()->route('operator.dashboard')
                    ->with('error', 'Dossier non trouvé ou accès non autorisé.');
            }

            // Rechercher l'accusé de réception dans les documents
            $accuseDocument = $dossierObj->documents()
                ->where('nom_fichier', 'like', 'accuse_reception_%')
                ->orWhere('nom_original', 'like', '%Accusé%')
                ->first();

            if (!$accuseDocument) {
                Log::warning("=== ACCUSÉ NON TROUVÉ ===", [
                    'dossier_id' => $dossier,
                    'documents_count' => $dossierObj->documents()->count()
                ]);
                
                return redirect()->back()
                    ->with('error', 'Accusé de réception non trouvé.');
            }

            // Construire le chemin du fichier
            $filePath = storage_path('app/public/' . $accuseDocument->chemin_fichier);
            
            if (!file_exists($filePath)) {
                Log::error("=== FICHIER ACCUSÉ INTROUVABLE ===", [
                    'file_path' => $filePath,
                    'document_id' => $accuseDocument->id
                ]);
                
                return redirect()->back()
                    ->with('error', 'Fichier accusé de réception introuvable.');
            }

            Log::info("=== TÉLÉCHARGEMENT ACCUSÉ RÉUSSI ===", [
                'dossier_id' => $dossier,
                'file_name' => $accuseDocument->nom_fichier,
                'file_size' => filesize($filePath)
            ]);

            // Télécharger le fichier
            return response()->download($filePath, $accuseDocument->nom_original ?? $accuseDocument->nom_fichier);

        } catch (\Exception $e) {
            Log::error("=== ERREUR TÉLÉCHARGEMENT ACCUSÉ ===", [
                'dossier_id' => $dossier,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors du téléchargement : ' . $e->getMessage());
        }
    }

    /**
     * ✅ MÉTHODE MANQUANTE 2: Import adhérents Phase 2
     * Résout le workflow 2 phases
     */
    public function storeAdherents(Request $request, $dossier)
    {
        try {
            Log::info("=== DÉBUT STORE ADHERENTS PHASE 2 ===", [
                'dossier_param' => $dossier,
                'user_id' => auth()->id(),
                'request_data_keys' => array_keys($request->all())
            ]);

            // Charger le dossier avec vérification d'accès
            $dossierObj = Dossier::with(['organisation'])
                ->where('id', $dossier)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossierObj) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé ou accès non autorisé.'
                ], 403);
            }

            // Valider les données d'entrée
            $validated = $request->validate([
                'adherents' => 'required|array|min:1',
                'adherents.*.nip' => 'required|string|size:13',
                'adherents.*.nom' => 'required|string|max:100',
                'adherents.*.prenom' => 'required|string|max:100',
                'adherents.*.telephone' => 'nullable|string|max:20',
                'adherents.*.profession' => 'nullable|string|max:100',
                'adherents.*.adresse' => 'nullable|string|max:255'
            ]);

            $adherentsData = $validAdherents;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($adherentsData as $index => $adherentData) {
                try {
                    // Créer l'adhérent
                    $adherent = new \App\Models\Adherent();
                    $adherent->organisation_id = $dossierObj->organisation->id;
                    $adherent->nip = $adherentData['nip'];
                    $adherent->nom = $adherentData['nom'];
                    $adherent->prenom = $adherentData['prenom'];
                    $adherent->telephone = $adherentData['telephone'] ?? null;
                    $adherent->profession = $adherentData['profession'] ?? null;
                    $adherent->adresse = $adherentData['adresse'] ?? null;
                    $adherent->date_adhesion = now();
                    $adherent->is_active = true;
                    $adherent->save();

                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'index' => $index,
                        'nip' => $adherentData['nip'] ?? 'N/A',
                        'nom' => $adherentData['nom'] ?? 'N/A',
                        'error' => $e->getMessage()
                    ];

                    Log::warning("=== ERREUR CRÉATION ADHÉRENT ===", [
                        'index' => $index,
                        'adherent_data' => $adherentData,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            Log::info("=== STORE ADHERENTS TERMINÉ ===", [
                'dossier_id' => $dossier,
                'total_processed' => count($adherentsData),
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Import terminé : {$successCount} adhérents créés, {$errorCount} erreurs",
                'data' => [
                    'total_processed' => count($adherentsData),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error("=== ERREUR CRITIQUE STORE ADHERENTS ===", [
                'dossier_id' => $dossier,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import : ' . $e->getMessage()
            ], 500);
        }
    }


/**
 * ========================================================================
 * MÉTHODES À AJOUTER/METTRE À JOUR DANS DossierController
 * Localisation: app/Http/Controllers/Operator/DossierController.php
 * ========================================================================
 */

// AJOUTER CES MÉTHODES DANS LA CLASSE DossierController

    /**
     * Page d'import des adhérents - Phase 2
     */
    public function adherentsImport($dossierId)
    {
        try {
            // Récupérer le dossier
            $dossier = Dossier::with(['organisation', 'adherents'])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->firstOrFail();
            
            $organisation = $dossier->organisation;
            
            // Statistiques adhérents
            $adherents_stats = [
                'existants' => $dossier->adherents()->count(),
                'minimum_requis' => $this->getMinimumAdherentsRequired($organisation->type),
                'manquants' => 0,
                'peut_soumettre' => false
            ];
            
            $adherents_stats['manquants'] = max(0, $adherents_stats['minimum_requis'] - $adherents_stats['existants']);
            $adherents_stats['peut_soumettre'] = $adherents_stats['manquants'] <= 0;
            
            // Configuration upload
            $upload_config = [
                'max_file_size' => '10MB',
                'chunk_size' => 100,
                'max_adherents' => 50000,
                'chunking_threshold' => 200
            ];
            
            // URLs pour les actions
            $urls = [
                'store_adherents' => route('operator.dossiers.store-adherents', $dossier->id),
                'template_download' => route('operator.templates.adherents-excel'),
                'confirmation' => route('operator.dossiers.confirmation', $dossier->id),
                'process_chunk' => route('chunking.process-chunk'),
                'health_check' => route('chunking.health')
            ];
            
            // Stocker les informations en session pour le chunking
            session([
                'current_dossier_id' => $dossier->id,
                'current_organisation_id' => $organisation->id
            ]);
            
            Log::info('📄 PAGE IMPORT ADHÉRENTS PHASE 2', [
                'dossier_id' => $dossier->id,
                'organisation_id' => $organisation->id,
                'adherents_existants' => $adherents_stats['existants'],
                'user_id' => auth()->id()
            ]);
            
            return view('operator.dossiers.adherents-import', compact(
                'dossier',
                'organisation', 
                'adherents_stats',
                'upload_config',
                'urls'
            ));
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur page import adhérents', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('operator.dashboard')
                ->with('error', 'Erreur lors du chargement de la page d\'import');
        }
    }
    
    
    /**
     * Gérer une requête en mode chunking
     */
    private function handleChunkRequest($request, $dossier, $organisation)
    {
        // Stocker les informations en session pour le ChunkProcessorController
        session([
            'current_dossier_id' => $dossier->id,
            'current_organisation_id' => $organisation->id
        ]);
        
        // Rediriger vers le ChunkProcessorController
        $chunkController = new \App\Http\Controllers\Api\ChunkProcessorController();
        return $chunkController->processChunk($request);
    }
    
    /**
     * Gérer un upload classique (sans chunking)
     */
    private function handleClassicUpload($request, $dossier, $organisation)
    {
        // Validation du fichier ou des données
        if ($request->hasFile('adherents_file')) {
            return $this->processFileUpload($request, $dossier, $organisation);
        } elseif ($request->has('adherents_data')) {
            return $this->processJsonData($request, $dossier, $organisation);
        } else {
            throw new \Exception('Aucune donnée d\'adhérents fournie');
        }
    }
    
    /**
     * Traiter l'upload d'un fichier
     */
    private function processFileUpload($request, $dossier, $organisation)
    {
        $file = $request->file('adherents_file');
        
        // Valider le fichier
        $request->validate([
            'adherents_file' => 'required|file|mimes:xlsx,csv|max:10240' // 10MB max
        ]);
        
        // Traiter le fichier selon son type
        if ($file->getClientOriginalExtension() === 'csv') {
            $adherentsData = $this->parseCsvFile($file);
        } else {
            $adherentsData = $this->parseExcelFile($file);
        }
        
        // Traiter les données
        return $this->processAdherentsData($adherentsData, $dossier, $organisation);
    }
    
    /**
     * Traiter des données JSON
     */
    private function processJsonData($request, $dossier, $organisation)
    {
        $adherentsJson = $request->input('adherents_data');
        $adherentsData = json_decode($adherentsJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Données JSON invalides');
        }
        
        return $this->processAdherentsData($adherentsData, $dossier, $organisation);
    }
    
    /**
     * Traiter les données d'adhérents avec validation et insertion
     */
    private function processAdherentsData($adherentsData, $dossier, $organisation)
    {
        $successCount = 0;
        $errorCount = 0;
        $anomaliesCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($adherentsData as $index => $adherentData) {
                try {
                    // Validation et nettoyage
                    $cleanedData = $this->validateAndCleanAdherentData($adherentData);
                    
                    // Vérifier doublon
                    $existingAdherent = Adherent::where('nip', $cleanedData['nip'])
                        ->where('organisation_id', $organisation->id)
                        ->first();
                    
                    if ($existingAdherent) {
                        $anomaliesCount++;
                        Log::warning('Doublon détecté', [
                            'nip' => $cleanedData['nip'],
                            'nom' => $cleanedData['nom']
                        ]);
                        continue;
                    }
                    
                    // Créer l'adhérent
                    Adherent::create([
                        'organisation_id' => $organisation->id,
                        'dossier_id' => $dossier->id,
                        'nip' => $cleanedData['nip'],
                        'civilite' => $cleanedData['civilite'],
                        'nom' => $cleanedData['nom'],
                        'prenom' => $cleanedData['prenom'],
                        'telephone' => $cleanedData['telephone'],
                        'profession' => $cleanedData['profession'],
                        'adresse' => $cleanedData['adresse'],
                        'date_naissance' => $cleanedData['date_naissance'],
                        'age' => $cleanedData['age'],
                        'date_adhesion' => now(),
                        'is_active' => true,
                        'created_by' => auth()->id()
                    ]);
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'index' => $index,
                        'nip' => $adherentData['nip'] ?? 'N/A',
                        'nom' => $adherentData['nom'] ?? 'N/A',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            Log::info('✅ IMPORT TERMINÉ', [
                'dossier_id' => $dossier->id,
                'total_processed' => count($adherentsData),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'anomalies_count' => $anomaliesCount
            ]);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Import terminé : {$successCount} adhérents créés",
                    'data' => [
                        'total_processed' => count($adherentsData),
                        'success_count' => $successCount,
                        'error_count' => $errorCount,
                        'anomalies_count' => $anomaliesCount,
                        'errors' => $errors
                    ]
                ]);
            }
            
            return redirect()->route('operator.dossiers.confirmation', $dossier->id)
                ->with('success', "Import réussi : {$successCount} adhérents ajoutés");
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    /**
     * Valider et nettoyer les données d'un adhérent
     */
    private function validateAndCleanAdherentData($data)
    {
        // Validation NIP
        $nip = $data['nip'] ?? '';
        if (!preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', $nip)) {
            throw new \Exception("Format NIP invalide: {$nip}");
        }
        
        // Validation champs obligatoires
        $requiredFields = ['nom', 'prenom', 'civilite'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Champ obligatoire manquant: {$field}");
            }
        }
        
        return [
            'nip' => strtoupper(trim($nip)),
            'civilite' => trim($data['civilite']),
            'nom' => strtoupper(trim($data['nom'])),
            'prenom' => ucwords(strtolower(trim($data['prenom']))),
            'telephone' => $this->cleanPhoneNumber($data['telephone'] ?? ''),
            'profession' => trim($data['profession'] ?? ''),
            'adresse' => trim($data['adresse'] ?? ''),
            'date_naissance' => $this->extractDateFromNip($nip),
            'age' => $this->calculateAgeFromNip($nip)
        ];
    }
    
    /**
     * Nettoyer numéro de téléphone
     */
    private function cleanPhoneNumber($phone)
    {
        if (empty($phone)) return null;
        
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleaned) === 8 && !str_starts_with($cleaned, '241')) {
            $cleaned = '241' . $cleaned;
        }
        
        return $cleaned;
    }
    
    /**
     * Extraire date de naissance du NIP
     */
    private function extractDateFromNip($nip)
    {
        if (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-([0-9]{8})$/', $nip, $matches)) {
            $dateStr = $matches[1];
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);
            $day = substr($dateStr, 6, 2);
            
            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}");
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Calculer âge depuis le NIP
     */
    private function calculateAgeFromNip($nip)
    {
        $birthDate = $this->extractDateFromNip($nip);
        return $birthDate ? $birthDate->age : null;
    }
    
    /**
     * Obtenir le minimum d'adhérents requis selon le type d'organisation
     */
    private function getMinimumAdherentsRequired($type)
    {
        $minimums = [
            'association' => 10,
            'ong' => 15,
            'parti_politique' => 50,
            'confession_religieuse' => 10
        ];
        
        return $minimums[$type] ?? 10;
    }
    
    /**
     * Parser un fichier CSV
     */
    private function parseCsvFile($file)
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        if ($handle) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= count($headers)) {
                    $data[] = array_combine($headers, $row);
                }
            }
            
            fclose($handle);
        }
        
        return $data;
    }
    
    /**
     * Parser un fichier Excel (nécessite PhpSpreadsheet)
     */
    private function parseExcelFile($file)
    {
        // Implémentation basique - à adapter selon vos besoins
        // Nécessite l'installation de PhpSpreadsheet via Composer
        
        throw new \Exception('Support Excel en cours d\'implémentation');
    }


/**
 * ✅ MÉTHODE À AJOUTER dans app/Http/Controllers/Operator/DossierController.php
 * 
 * INSTRUCTION : Ajouter cette méthode dans DossierController
 * Elle résout l'erreur "The given data was invalid" en Phase 2
 */
/**
 * ✅ MÉTHODE HARMONISÉE - storeAdherentsPhase2
 * Traiter l'import des adhérents en Phase 2 (compatible avec système existant)
 * 
 * CORRECTION MAJEURE: Résout l'erreur "Undefined variable: validAdherents"
 */
public function storeAdherentsPhase2(Request $request, $dossierId)
{
    // FORCE EXTENSION TIMEOUT pour gros volumes
    @set_time_limit(0);
    @ini_set('memory_limit', '1G');
    
    Log::info('=== DÉBUT STORE ADHERENTS PHASE 2 HARMONISÉ ===', [
        'dossier_param' => $dossierId,
        'user_id' => auth()->id(),
        'request_data_keys' => array_keys($request->all()),
        'version' => 'harmonized_v1.0'
    ]);

    try {
        // ✅ ÉTAPE 1: RÉCUPÉRER LE DOSSIER AVEC VÉRIFICATIONS
        $dossier = Dossier::with(['organisation'])
            ->where('id', $dossierId)
            ->whereHas('organisation', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->first();

        if (!$dossier) {
            Log::error('❌ Dossier non trouvé pour Phase 2', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Dossier non trouvé ou accès non autorisé',
                'error_code' => 'DOSSIER_NOT_FOUND'
            ], 404);
        }

        $organisation = $dossier->organisation;

        // ✅ ÉTAPE 2: EXTRACTION ET VALIDATION DES DONNÉES ADHÉRENTS
        $adherentsRaw = $request->input('adherents');
        $adherentsData = [];

        Log::info('🔍 DONNÉES BRUTES REÇUES', [
            'adherents_type' => gettype($adherentsRaw),
            'adherents_content' => is_string($adherentsRaw) ? substr($adherentsRaw, 0, 200) . '...' : 'Array',
            'all_request_keys' => array_keys($request->all())
        ]);

        // ✅ DÉCODAGE INTELLIGENT DES DONNÉES
        if (is_string($adherentsRaw)) {
            $decoded = json_decode($adherentsRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $adherentsData = $decoded;
                Log::info('✅ Données JSON décodées avec succès', ['count' => count($adherentsData)]);
            } else {
                Log::error('❌ Erreur décodage JSON', [
                    'json_error' => json_last_error_msg(),
                    'raw_data' => substr($adherentsRaw, 0, 500)
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Format de données adhérents invalide (JSON malformé)',
                    'error_code' => 'INVALID_JSON_FORMAT'
                ], 422);
            }
        } elseif (is_array($adherentsRaw)) {
            $adherentsData = $adherentsRaw;
            Log::info('✅ Données array directes', ['count' => count($adherentsData)]);
        } else {
            Log::error('❌ Format données adhérents non reconnu', [
                'type' => gettype($adherentsRaw),
                'value' => $adherentsRaw
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Format de données adhérents non reconnu',
                'error_code' => 'UNRECOGNIZED_DATA_FORMAT'
            ], 422);
        }

        // ✅ VÉRIFICATION BASIQUE
        if (empty($adherentsData) || !is_array($adherentsData)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune donnée d\'adhérents fournie',
                'error_code' => 'NO_ADHERENTS_DATA'
            ], 422);
        }

        // ✅ ÉTAPE 3: EXTRACTION DES VRAIS ADHÉRENTS (filtrer métadonnées)
        $adherentsData = $this->extractRealAdherents($adherentsData);
        
        Log::info('📊 Adhérents extraits pour traitement', [
            'count' => count($adherentsData),
            'dossier_id' => $dossierId
        ]);

        // ✅ ÉTAPE 4: CONSERVATION TOTALE - TRAITEMENT DE TOUS LES ADHÉRENTS
        $statsDetaillees = [
            'total' => 0,
            'enregistres' => 0,
            'sans_anomalies' => 0,
            'avec_anomalies' => 0,
            'anomalies_critiques' => 0,
            'anomalies_majeures' => 0,
            'anomalies_mineures' => 0,
            'erreurs_systeme' => 0
        ];

        $anomaliesDetaillees = [];
        $erreursSysteme = [];

        // Commencer transaction
        DB::beginTransaction();

        foreach ($adherentsData as $index => $adherentRaw) {
            $statsDetaillees['total']++;
            
            try {
                // ✅ PRÉPARATION DES DONNÉES (nettoyage basique SANS validation bloquante)
                $adherentData = $this->prepareAdherentDataConservation($adherentRaw, $index);
                
                // ✅ CRÉER L'ADHÉRENT (AUCUNE VALIDATION BLOQUANTE)
                $adherent = new \App\Models\Adherent();
                $adherent->organisation_id = $organisation->id;
                $adherent->nip = $adherentData['nip'];
                $adherent->nom = $adherentData['nom'];
                $adherent->prenom = $adherentData['prenom'];
                $adherent->civilite = $adherentData['civilite'] ?? 'M';
                $adherent->profession = $adherentData['profession'] ?? null;
                $adherent->telephone = $adherentData['telephone'] ?? null;
                $adherent->email = $adherentData['email'] ?? null;
                $adherent->fonction = $adherentData['fonction'] ?? 'Membre';
                $adherent->date_adhesion = now();
                $adherent->is_fondateur = false;
                
                // ✅ ENREGISTRER - Le modèle Adherent gérera automatiquement les anomalies
                $adherent->save();
                
                $statsDetaillees['enregistres']++;
                
                // ✅ COLLECTER LES STATISTIQUES D'ANOMALIES
                if ($adherent->has_anomalies) {
                    $statsDetaillees['avec_anomalies']++;
                    
                    switch ($adherent->anomalies_severity) {
                        case 'critique':
                            $statsDetaillees['anomalies_critiques']++;
                            break;
                        case 'majeure':
                            $statsDetaillees['anomalies_majeures']++;
                            break;
                        case 'mineure':
                            $statsDetaillees['anomalies_mineures']++;
                            break;
                    }
                    
                    $anomaliesDetaillees[] = [
                        'adherent_id' => $adherent->id,
                        'index' => $index,
                        'nip' => $adherent->nip,
                        'nom_complet' => $adherent->nom . ' ' . $adherent->prenom,
                        'severity' => $adherent->anomalies_severity,
                        'anomalies_details' => $adherent->anomalies_data
                    ];
                } else {
                    $statsDetaillees['sans_anomalies']++;
                }
                
            } catch (\Exception $e) {
                $statsDetaillees['erreurs_systeme']++;
                $erreursSysteme[] = [
                    'index' => $index,
                    'error_message' => $e->getMessage()
                ];
                
                Log::error("❌ Erreur système enregistrement", [
                    'index' => $index,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // ✅ ÉTAPE 5: VALIDER LA TRANSACTION
        DB::commit();

        Log::info('🎉 CONSERVATION TOTALE TERMINÉE', [
            'dossier_id' => $dossier->id,
            'stats' => $statsDetaillees,
            'taux_reussite' => $statsDetaillees['total'] > 0 
                ? round(($statsDetaillees['enregistres'] / $statsDetaillees['total']) * 100, 1) . '%'
                : '0%'
        ]);

        // ✅ ÉTAPE 6: MISE À JOUR SÉCURISÉE DU DOSSIER
        try {
            // Récupérer les données supplémentaires existantes
            $donneesSupplementaires = [];
            if (!empty($dossier->donnees_supplementaires)) {
                if (is_string($dossier->donnees_supplementaires)) {
                    $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true) ?? [];
                } elseif (is_array($dossier->donnees_supplementaires)) {
                    $donneesSupplementaires = $dossier->donnees_supplementaires;
                }
            }

            // Mise à jour avec nouvelles statistiques
            $donneesSupplementaires['adherents_stats'] = $statsDetaillees;
            $donneesSupplementaires['adherents_import_date'] = now()->toISOString();
            $donneesSupplementaires['conservation_totale'] = true;
            $donneesSupplementaires['phase_creation'] = '2_avec_adherents';
            $donneesSupplementaires['adherents_phase2_pending'] = false;

            // ✅ MISE À JOUR AVEC STATUT VALIDE
            DB::table('dossiers')
                ->where('id', $dossier->id)
                ->update([
                    'statut' => 'approuve',  // ✅ Statut valide selon ENUM de la DB
                    'donnees_supplementaires' => json_encode($donneesSupplementaires),
                    'updated_at' => now()
                ]);

            Log::info('✅ Dossier mis à jour avec succès', [
                'dossier_id' => $dossier->id,
                'statut' => 'approuve',
                'stats' => $statsDetaillees
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erreur mise à jour dossier', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            
            // Ne pas faire échouer le processus car les adhérents sont déjà enregistrés
            Log::warning('⚠️ Adhérents enregistrés malgré erreur mise à jour dossier');
        }

        // ✅ ÉTAPE 7: RÉPONSE DE SUCCÈS
        Log::info('🎉 PHASE 2 TERMINÉE AVEC SUCCÈS', [
            'dossier_id' => $dossierId,
            'organisation_id' => $organisation->id,
            'adherents_stats' => $statsDetaillees
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Import terminé avec succès - Conservation totale appliquée',
            'phase' => 2,
            'conservation_totale' => true,
            'data' => [
                'stats' => $statsDetaillees,
                'anomalies' => [
                    'total' => count($anomaliesDetaillees),
                    'details' => $anomaliesDetaillees
                ],
                'erreurs_systeme' => [
                    'total' => count($erreursSysteme),
                    'details' => $erreursSysteme
                ]
            ],
            'summary' => [
                'message' => "✅ {$statsDetaillees['enregistres']}/{$statsDetaillees['total']} adhérents enregistrés",
                'conservation' => 'Tous les adhérents sont conservés, anomalies classifiées'
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        
        Log::error('=== ERREUR CRITIQUE STORE ADHERENTS HARMONISÉ ===', [
            'dossier_id' => $dossierId,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur critique lors de l\'import des adhérents',
            'error_code' => 'CRITICAL_ERROR',
            'debug' => config('app.debug') ? [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] : null
        ], 500);
    }
}


    /**
     * ✅ MÉTHODE PRIVÉE - Préparer les données adhérent pour Phase 2
     */
    private function prepareAdherentDataForPhase2(array $rawData): array
    {
        return [
            'nip' => $this->cleanNipPhase2($rawData['nip'] ?? ''),
            'nom' => trim(strtoupper($rawData['nom'] ?? '')),
            'prenom' => trim(ucwords(strtolower($rawData['prenom'] ?? ''))),
            'profession' => trim($rawData['profession'] ?? ''),
            'telephone' => $this->cleanTelephonePhase2($rawData['telephone'] ?? ''),
            'email' => trim(strtolower($rawData['email'] ?? '')),
            'fonction' => $rawData['fonction'] ?? 'Membre',
            'civilite' => $rawData['civilite'] ?? 'M',
            'is_active' => true,
            'is_fondateur' => false
        ];
    }

    /**
     * ✅ MÉTHODE PRIVÉE - Nettoyer le NIP pour Phase 2
     */
    private function cleanNipPhase2(string $nip): string
    {
        $cleaned = strtoupper(trim($nip));
        $cleaned = preg_replace('/[^A-Z0-9\-]/', '', $cleaned);
        return $cleaned;
    }

    /**
     * ✅ MÉTHODE PRIVÉE - Nettoyer le téléphone pour Phase 2
     */
    private function cleanTelephonePhase2(string $telephone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $telephone);
        
        // Ajouter le préfixe 0 si manquant et que le numéro fait 8 chiffres
        if (strlen($cleaned) === 8 && !str_starts_with($cleaned, '0')) {
            $cleaned = '0' . $cleaned;
        }
        
        return $cleaned;
    }


/**
     * ✅ MÉTHODE HARMONISÉE - Extraire les vrais adhérents depuis structure complexe
     * Résout les problèmes de variables non définies lors de l'extraction
     */
    private function extractRealAdherents(array $data): array
    {
        $realAdherents = [];
        
        Log::info('🔍 Début extraction adhérents', [
            'input_keys' => array_keys($data),
            'input_count' => count($data)
        ]);
        
        // CAS 1: Les adhérents sont dans la clé 'adherents' (structure détectée dans les logs)
        if (isset($data['adherents']) && is_array($data['adherents'])) {
            Log::info('🔍 Structure détectée: adherents dans sous-clé', [
                'count' => count($data['adherents'])
            ]);
            return $data['adherents'];
        }
        
        // CAS 2: Les adhérents sont dans une clé 'data'
        if (isset($data['data']) && is_array($data['data'])) {
            Log::info('🔍 Structure détectée: adherents dans data', [
                'count' => count($data['data'])
            ]);
            return $data['data'];
        }
        
        // CAS 3: Structure directe - tableau d'adhérents
        foreach ($data as $key => $item) {
            // Ignorer les métadonnées connues
            $metadataKeys = [
                'stats', 'anomalies', 'processingMethod', 'duration', 'phase', 
                'version', 'import_stats', 'summary', 'errors', 'validation',
                'chunk_id', 'chunk_total', 'processing_info'
            ];
            
            if (in_array($key, $metadataKeys)) {
                Log::debug("🔍 Ignoré métadonnée: {$key}");
                continue;
            }
            
            // Vérifier que c'est un vrai adhérent
            if (is_array($item) && $this->isValidAdherentData($item)) {
                $realAdherents[] = $item;
            } else {
                Log::debug("🔍 Item ignoré (pas un adhérent valide): " . json_encode($item));
            }
        }
        
        Log::info('🔍 Extraction terminée', [
            'method' => 'structure_directe',
            'count' => count($realAdherents)
        ]);
        
        return $realAdherents;
    }

    /**
     * ✅ MÉTHODE HARMONISÉE - Vérifier si un item est un adhérent valide
     */
    private function isValidAdherentData(array $item): bool
    {
        // Vérifications basiques
        if (!isset($item['nip']) || empty(trim($item['nip']))) {
            return false;
        }
        
        if (!isset($item['nom']) || empty(trim($item['nom']))) {
            return false;
        }
        
        if (!isset($item['prenom']) || empty(trim($item['prenom']))) {
            return false;
        }
        
        return true;
    }

    /**
     * ✅ MÉTHODE HARMONISÉE - Préparer les données adhérent pour conservation totale
     * Résout les erreurs de variables non définies dans le traitement des données
     */
    private function prepareAdherentDataConservation(array $rawData, int $index): array
    {
        // ✅ NETTOYAGE BASIQUE SANS VALIDATION BLOQUANTE
        $preparedData = [
            'nip' => $this->cleanNipConservation($rawData['nip'] ?? '', $index),
            'nom' => $this->cleanNomConservation($rawData['nom'] ?? '', $index),
            'prenom' => $this->cleanPrenomConservation($rawData['prenom'] ?? '', $index),
            'civilite' => $this->cleanCiviliteConservation($rawData['civilite'] ?? ''),
            'profession' => $this->cleanProfessionConservation($rawData['profession'] ?? ''),
            'telephone' => $this->cleanTelephoneConservation($rawData['telephone'] ?? ''),
            'email' => $this->cleanEmailConservation($rawData['email'] ?? ''),
            'fonction' => $this->cleanFonctionConservation($rawData['fonction'] ?? '')
        ];
        
        Log::debug("✅ Données préparées pour index {$index}", [
            'nip' => $preparedData['nip'],
            'nom' => $preparedData['nom'],
            'prenom' => $preparedData['prenom']
        ]);
        
        return $preparedData;
    }

    /**
     * ✅ MÉTHODES DE NETTOYAGE INDIVIDUELLES - Conservation totale
     */
    private function cleanNipConservation(string $nip, int $index): string
    {
        if (empty(trim($nip))) {
            return "MISSING_NIP_{$index}_" . time();
        }
        
        // Nettoyage basique sans validation bloquante
        $cleaned = strtoupper(trim($nip));
        $cleaned = preg_replace('/[^A-Z0-9\-]/', '', $cleaned);
        
        return $cleaned ?: "INVALID_NIP_{$index}_" . time();
    }

    private function cleanNomConservation(string $nom, int $index): string
    {
        if (empty(trim($nom))) {
            return "MISSING_NOM_{$index}";
        }
        
        return strtoupper(trim($nom));
    }

    private function cleanPrenomConservation(string $prenom, int $index): string
    {
        if (empty(trim($prenom))) {
            return "MISSING_PRENOM_{$index}";
        }
        
        return ucwords(strtolower(trim($prenom)));
    }

    private function cleanCiviliteConservation(string $civilite): string
    {
        $civiliteClean = strtoupper(trim($civilite));
        $validCivilites = ['M', 'MME', 'MLLE', 'DR', 'PROF'];
        
        return in_array($civiliteClean, $validCivilites) ? $civiliteClean : 'M';
    }

    private function cleanProfessionConservation(string $profession): ?string
    {
        $cleaned = trim($profession);
        return empty($cleaned) ? null : $cleaned;
    }

    private function cleanTelephoneConservation(string $telephone): ?string
    {
        if (empty(trim($telephone))) {
            return null;
        }
        
        // Nettoyage basique - garder seulement les chiffres
        $cleaned = preg_replace('/[^0-9]/', '', $telephone);
        
        // Ajouter le préfixe 0 si manquant et que le numéro fait 8 chiffres
        if (strlen($cleaned) === 8 && !str_starts_with($cleaned, '0')) {
            $cleaned = '0' . $cleaned;
        }
        
        return empty($cleaned) ? null : $cleaned;
    }

    private function cleanEmailConservation(string $email): ?string
    {
        $cleaned = strtolower(trim($email));
        
        // Validation basique email
        if (empty($cleaned) || !filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        
        return $cleaned;
    }

    private function cleanFonctionConservation(string $fonction): string
    {
        $cleaned = trim($fonction);
        return empty($cleaned) ? 'Membre' : $cleaned;
    }

    /**
     * ✅ MÉTHODE HARMONISÉE - Calculer statistiques adhérents avec gestion d'erreurs
     * Résout les problèmes de variables non définies dans le calcul des stats
     */
    private function calculateAdherentsStatsHarmonized(Dossier $dossier): array
    {
        try {
            $organisation = $dossier->organisation;
            
            if (!$organisation) {
                Log::warning('Organisation non trouvée pour dossier', ['dossier_id' => $dossier->id]);
                return $this->getDefaultAdherentsStats();
            }
            
            $totalAdherents = $organisation->adherents()->count();
            $adherentsValides = $organisation->adherents()
                ->where('is_active', true)
                ->count();
            
            // ✅ CORRECTION: Décoder les données JSON de manière plus sécurisée
            $donneesSupplementaires = $this->extractDonneesSupplementaires($dossier);
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
            Log::error('=== ERREUR CALCUL STATS ADHÉRENTS HARMONISÉ ===', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            
            return $this->getDefaultAdherentsStats();
        }
    }

    /**
     * ✅ MÉTHODE HARMONISÉE - Extraire données supplémentaires de manière sécurisée
     */
    private function extractDonneesSupplementaires(Dossier $dossier): array
    {
        $donneesSupplementaires = [];
        
        try {
            if (!empty($dossier->donnees_supplementaires)) {
                if (is_string($dossier->donnees_supplementaires)) {
                    $decoded = json_decode($dossier->donnees_supplementaires, true);
                    $donneesSupplementaires = $decoded && is_array($decoded) ? $decoded : [];
                } elseif (is_array($dossier->donnees_supplementaires)) {
                    $donneesSupplementaires = $dossier->donnees_supplementaires;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Erreur extraction données supplémentaires', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $donneesSupplementaires;
    }

    /**
     * ✅ MÉTHODE HARMONISÉE - Statistiques par défaut en cas d'erreur
     */
    private function getDefaultAdherentsStats(): array
    {
        return [
            'total' => 0,
            'valides' => 0,
            'anomalies_critiques' => 0,
            'anomalies_majeures' => 0,
            'anomalies_mineures' => 0
        ];
    }

    /**
     * ✅ MÉTHODE HARMONISÉE - Gestion des anomalies avec variables bien définies
     */
    private function getAnomaliesFromDossierHarmonized(Dossier $dossier): array
    {
        try {
            $donneesSupplementaires = $this->extractDonneesSupplementaires($dossier);
            return $donneesSupplementaires['adherents_anomalies'] ?? [];
        } catch (\Exception $e) {
            Log::error('Erreur récupération anomalies', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    // FIN DE LA CLASSE
}