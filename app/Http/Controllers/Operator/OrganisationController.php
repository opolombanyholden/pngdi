<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\OrganizationDraft;
use App\Models\Dossier;
use App\Models\User;
use App\Services\OrganisationValidationService;
use App\Services\OrganisationStepService;
use App\Services\WorkflowService;
use App\Services\QrCodeService;

class OrganisationController extends Controller
{
    protected $organisationValidationService;
    protected $workflowService;
    protected $qrCodeService;

    public function __construct(
        OrganisationValidationService $organisationValidationService,
        WorkflowService $workflowService,
        QrCodeService $qrCodeService
    ) {
        $this->organisationValidationService = $organisationValidationService;
        $this->workflowService = $workflowService;
        $this->qrCodeService = $qrCodeService;
    }

    // =============================================
    // NOUVELLES MÉTHODES POUR GESTION PAR ÉTAPES
    // =============================================

    /**
     * Sauvegarder une étape via AJAX
     * POST /operator/organisations/step/{step}/save
     */
    public function saveStep(Request $request, int $step)
    {
        try {
            $stepService = app(OrganisationStepService::class);
            
            $request->validate([
                'data' => 'required|array',
                'session_id' => 'nullable|string'
            ]);
            
            $result = $stepService->saveStep(
                $step,
                $request->input('data'),
                auth()->id(),
                $request->input('session_id', session()->getId())
            );
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error('Erreur sauvegarde étape via contrôleur', [
                'step' => $step,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider une étape sans sauvegarder
     * POST /operator/organisations/step/{step}/validate
     */
    public function validateStep(Request $request, int $step)
    {
        try {
            $stepService = app(OrganisationStepService::class);
            
            $request->validate([
                'data' => 'required|array'
            ]);
            
            $result = $stepService->validateStep($step, $request->input('data'));
            
            return response()->json([
                'success' => $result['valid'],
                'valid' => $result['valid'],
                'errors' => $result['errors'],
                'step' => $step
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un brouillon existant
     * GET /operator/organisations/draft/{draftId}
     */
    public function getDraft(int $draftId)
    {
        try {
            $draft = OrganizationDraft::where('id', $draftId)
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brouillon non trouvé'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'draft' => $draft,
                'statistics' => $draft->getStatistics(),
                'steps_summary' => $draft->getStepsSummary(),
                'next_step' => $draft->getNextStep()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du brouillon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les brouillons de l'utilisateur connecté
     * GET /operator/organisations/drafts
     */
    public function listDrafts(Request $request)
    {
        try {
            $query = OrganizationDraft::where('user_id', auth()->id());
            
            // Filtres
            if ($request->has('type') && $request->input('type') !== 'all') {
                $query->byType($request->input('type'));
            }
            
            if ($request->boolean('active_only', false)) {
                $query->active();
            }
            
            $drafts = $query->orderBy('last_saved_at', 'desc')
                ->limit(20)
                ->get();
            
            $draftsWithStats = $drafts->map(function ($draft) {
                return [
                    'id' => $draft->id,
                    'organization_type' => $draft->organization_type,
                    'current_step' => $draft->current_step,
                    'completion_percentage' => $draft->completion_percentage,
                    'last_saved_at' => $draft->last_saved_at,
                    'expires_at' => $draft->expires_at,
                    'is_expired' => $draft->isExpired(),
                    'statistics' => $draft->getStatistics(),
                    'can_resume' => !$draft->isExpired()
                ];
            });
            
            return response()->json([
                'success' => true,
                'drafts' => $draftsWithStats,
                'count' => $drafts->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des brouillons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau brouillon
     * POST /operator/organisations/draft/create
     */
    public function createDraft(Request $request)
    {
        try {
            $request->validate([
                'organization_type' => 'nullable|in:association,ong,parti_politique,confession_religieuse',
                'session_id' => 'nullable|string'
            ]);
            
            // Vérifier les limites d'organisations
            $type = $request->input('organization_type');
            if ($type) {
                $canCreate = $this->checkOrganisationLimits($type);
                if (!$canCreate['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $canCreate['message']
                    ], 422);
                }
            }
            
            $draft = OrganizationDraft::create([
                'user_id' => auth()->id(),
                'organization_type' => $type,
                'session_id' => $request->input('session_id', session()->getId()),
                'form_data' => [],
                'current_step' => 1,
                'completion_percentage' => 0,
                'last_saved_at' => now(),
                'expires_at' => now()->addDays(7)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Brouillon créé avec succès',
                'draft' => $draft,
                'draft_id' => $draft->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du brouillon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un brouillon
     * DELETE /operator/organisations/draft/{draftId}
     */
    public function deleteDraft(int $draftId)
    {
        try {
            $draft = OrganizationDraft::where('id', $draftId)
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brouillon non trouvé'
                ], 404);
            }
            
            $draft->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Brouillon supprimé avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finaliser un brouillon et créer l'organisation
     * POST /operator/organisations/draft/{draftId}/finalize
     */
    public function finalizeDraft(int $draftId)
    {
        try {
            $stepService = app(OrganisationStepService::class);
            
            $result = $stepService->finalizeOrganisation($draftId);
            
            if ($result['success']) {
                // Rediriger vers la page de confirmation
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'organisation_id' => $result['organisation_id'],
                    'redirect_url' => route('operator.organisations.show', $result['organisation_id'])
                ]);
            } else {
                return response()->json($result, 422);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reprendre un brouillon existant
     * GET /operator/organisations/draft/{draftId}/resume
     */
    public function resumeDraft(int $draftId)
    {
        try {
            $draft = OrganizationDraft::where('id', $draftId)
                ->where('user_id', auth()->id())
                ->first();
            
            if (!$draft) {
                return redirect()->route('operator.organisations.index')
                    ->with('error', 'Brouillon non trouvé');
            }
            
            if ($draft->isExpired()) {
                return redirect()->route('operator.organisations.index')
                    ->with('warning', 'Ce brouillon a expiré');
            }
            
            // Étendre l'expiration automatiquement
            $draft->extendExpiration(7);
            
            // Rediriger vers la page de création avec le brouillon
            return redirect()->route('operator.organisations.create')
                ->with('resume_draft_id', $draft->id)
                ->with('success', 'Brouillon restauré avec succès');
            
        } catch (\Exception $e) {
            return redirect()->route('operator.organisations.index')
                ->with('error', 'Erreur lors de la reprise du brouillon');
        }
    }

    // =============================================
    // MÉTHODES EXISTANTES CONSERVÉES
    // =============================================

    /**
     * Afficher la liste des organisations de l'opérateur
     */
    public function index()
    {
        $organisations = Organisation::where('user_id', auth()->id())
            ->with(['dossier', 'adherents'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('operator.organisations.index', compact('organisations'));
    }

    /**
     * Afficher le formulaire de création d'une organisation
     * VERSION MISE À JOUR avec support des brouillons
     */
    public function create(Request $request, $type = null)
    {
        // Vérifier les limites d'organisations
        $canCreate = $this->checkOrganisationLimits($type);
        if (!$canCreate['success']) {
            return redirect()->route('operator.dashboard')
                ->with('error', $canCreate['message']);
        }

        // Vérifier s'il faut reprendre un brouillon
        $resumeDraftId = session('resume_draft_id');
        $existingDraft = null;
        
        if ($resumeDraftId) {
            $existingDraft = OrganizationDraft::where('id', $resumeDraftId)
                ->where('user_id', auth()->id())
                ->first();
            
            if ($existingDraft && !$existingDraft->isExpired()) {
                // Nettoyer la session
                session()->forget('resume_draft_id');
            } else {
                $existingDraft = null;
            }
        }
        
        // Si pas de brouillon existant, chercher les brouillons récents
        if (!$existingDraft) {
            $recentDrafts = OrganizationDraft::where('user_id', auth()->id())
                ->active()
                ->orderBy('last_saved_at', 'desc')
                ->limit(5)
                ->get();
        } else {
            $recentDrafts = collect();
        }

        $guides = $this->getGuideContent($type);
        $documentTypes = $this->getRequiredDocuments($type);
        $provinces = $this->getProvinces();

        return view('operator.organisations.create', compact(
            'type', 
            'guides', 
            'documentTypes', 
            'provinces',
            'existingDraft',
            'recentDrafts'
        ));
    }

    /**
     * Enregistrer une nouvelle organisation
     */
    public function store(Request $request)
    {
       
        // ✅ FORCE EXTENSION TIMEOUT
        @set_time_limit(0); // Pas de limite de temps
        @ini_set('memory_limit', '1G'); // 1GB de mémoire
        
        // Log de débogage pour diagnostiquer
        \Log::info('Début soumission organisation v3', [
            'user_id' => auth()->id(),
            'request_data_keys' => array_keys($request->all()),
            'type_organisation' => $request->input('type_organisation'),
            'fondateurs_type' => gettype($request->input('fondateurs')),
            'adherents_type' => gettype($request->input('adherents'))
        ]);

        try {
            $type = $request->input('type_organisation');

            // Vérifier les limites avant création
            $canCreate = $this->checkOrganisationLimits($type);
            if (!$canCreate['success']) {
                \Log::warning('Limite organisation atteinte', $canCreate);
                
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $canCreate['message'],
                        'errors' => ['limite' => $canCreate['message']]
                    ], 422);
                }
                
                return redirect()->back()
                    ->with('error', $canCreate['message'])
                    ->withInput();
            }

            // Validation complète avec gestion d'erreurs JSON améliorée
            try {
                $validatedData = $this->validateCompleteOrganisationData($request, $type);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Erreur validation v3', [
                    'errors' => $e->errors(),
                    'user_id' => auth()->id(),
                    'json_last_error' => json_last_error_msg()
                ]);
                
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreurs de validation détectées',
                        'errors' => $e->errors()
                    ], 422);
                }
                
                throw $e;
            }

            \DB::beginTransaction();

            // ÉTAPE 1-4 : Créer l'organisation principale
            $organisation = Organisation::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'nom' => $validatedData['org_nom'],
                'sigle' => $validatedData['org_sigle'] ?? null,
                'objet' => $validatedData['org_objet'],
                'siege_social' => $validatedData['org_adresse_complete'],
                'province' => $validatedData['org_province'],
                'departement' => $validatedData['org_departement'] ?? null,
                'prefecture' => $validatedData['org_prefecture'],
                'zone_type' => $validatedData['org_zone_type'],
                'latitude' => $validatedData['org_latitude'] ?? null,
                'longitude' => $validatedData['org_longitude'] ?? null,
                'email' => $validatedData['org_email'] ?? null,
                'telephone' => $validatedData['org_telephone'],
                'site_web' => $validatedData['org_site_web'] ?? null,
                'date_creation' => $validatedData['org_date_creation'],
                'statut' => 'soumis',
                'nombre_adherents_min' => $this->getMinAdherents($type)
            ]);

            \Log::info('Organisation créée v3', ['organisation_id' => $organisation->id]);

            // Générer et assigner le numéro de récépissé
            $numeroRecepisse = $this->generateRecepisseNumber($type);
            $organisation->update(['numero_recepisse' => $numeroRecepisse]);

            // ÉTAPE 6 : Créer les fondateurs
            if (!empty($validatedData['fondateurs'])) {
                $this->createFondateurs($organisation, $validatedData['fondateurs']);
                \Log::info('Fondateurs créés v3', ['count' => count($validatedData['fondateurs'])]);
            }

            // ÉTAPE 7 : Créer les adhérents avec gestion des anomalies
            $adherentsResult = null;
            if (!empty($validatedData['adherents'])) {
                $adherentsResult = $this->createAdherents($organisation, $validatedData['adherents']);
                \Log::info('Adhérents créés v3', $adherentsResult['stats']);
            }

            // ÉTAPE 5 : Créer le dossier de traitement avec JSON sécurisé
            $donneesSupplementaires = [
                'demandeur' => [
                    'nip' => $validatedData['demandeur_nip'],
                    'nom' => $validatedData['demandeur_nom'],
                    'prenom' => $validatedData['demandeur_prenom'],
                    'email' => $validatedData['demandeur_email'],
                    'telephone' => $validatedData['demandeur_telephone'],
                    'role' => $validatedData['demandeur_role'] ?? null
                ],
                'guide_lu' => $validatedData['guide_read_confirm'] ?? false,
                'declarations' => [
                    'veracite' => $validatedData['declaration_veracite'] ?? false,
                    'conformite' => $validatedData['declaration_conformite'] ?? false,
                    'autorisation' => $validatedData['declaration_autorisation'] ?? false
                ]
            ];

            // Ajouter les anomalies seulement si elles existent
            if (!empty($adherentsResult['anomalies'])) {
                $donneesSupplementaires['adherents_anomalies'] = $adherentsResult['anomalies'];
            }

            // Nettoyer et encoder les données JSON
            $donneesSupplementairesCleaned = $this->sanitizeJsonData($donneesSupplementaires);

            $dossier = Dossier::create([
                'organisation_id' => $organisation->id,
                'type_operation' => 'creation',
                'numero_dossier' => $this->generateDossierNumber($type),
                'statut' => 'soumis',
                'submitted_at' => now(),
                // Encoder explicitement en JSON avec options sécurisées
                'donnees_supplementaires' => json_encode($donneesSupplementairesCleaned, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION)
            ]);

            \Log::info('Dossier créé v3', [
                'dossier_id' => $dossier->id,
                'donnees_supplementaires_size' => strlen(json_encode($donneesSupplementairesCleaned))
            ]);

            // ÉTAPE 8 : Traiter les documents uploadés
            if ($request->hasFile('documents')) {
                $this->handleDocumentUploads($request, $dossier);
            }

            // Initialiser le workflow FIFO
            $this->workflowService->initializeWorkflow($dossier);

            // Générer QR Code pour vérification (avec gestion d'erreur sécurisée)
            $qrCode = null;
            try {
                $qrCode = $this->qrCodeService->generateForDossier($dossier);
                if ($qrCode) {
                    \Log::info('QR Code généré avec succès v3', ['qr_code_id' => $qrCode->id]);
                } else {
                    \Log::warning('QR Code non généré mais processus continue v3', ['dossier_id' => $dossier->id]);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur QR Code non bloquante v3', [
                    'dossier_id' => $dossier->id,
                    'error' => $e->getMessage()
                ]);
                $qrCode = null; // Le processus continue même sans QR Code
            }

            // Générer accusé de réception téléchargeable pour les administrateurs
            $accuseReceptionPath = $this->generateAccuseReception($dossier, $organisation, auth()->user());

            \DB::commit();

            \Log::info('Transaction validée avec succès v3', [
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id,
                'numero_recepisse' => $numeroRecepisse
            ]);

            // ÉTAPE 9 : Préparer les données de confirmation
            $confirmationData = [
                'organisation' => $organisation,
                'dossier' => $dossier,
                'numero_recepisse' => $numeroRecepisse,
                'qr_code' => $qrCode,
                'adherents_stats' => $adherentsResult['stats'] ?? null,
                'accuse_reception_path' => $accuseReceptionPath,
                'message_confirmation' => 'Votre dossier a été soumis avec succès. Un accusé de réception sera disponible sous 72 heures ouvrées.',
                'delai_traitement' => '72 heures ouvrées'
            ];

            // Gestion des réponses AJAX vs Navigation classique
            if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
            'success' => true,
            'message' => 'Organisation créée avec succès',
        'data' => [
            'organisation_id' => $organisation->id,
            'dossier_id' => $dossier->id,
            'numero_recepisse' => $numeroRecepisse,
            'redirect_url' => route('operator.dossiers.confirmation', $dossier->id),
            // 🎯 AJOUT: URL de redirection explicite avec ID dossier
            'confirmation_url' => route('operator.dossiers.confirmation', $dossier->id),
            'confirmation_dossier_id' => $dossier->id  // 🎯 ID explicite pour JS
        ],
        'confirmation_data' => $confirmationData
         ]);
            } else {
            // 🎯 REDIRECTION CLASSIQUE: TOUJOURS AVEC DOSSIER ID
         return redirect()->route('operator.dossiers.confirmation', $dossier->id)
        ->with('success_data', $confirmationData);
        }

        } catch (\Exception $e) {
            \DB::rollback();
            
            \Log::error('Erreur création organisation complète v3', [
                'user_id' => auth()->id(),
                'type' => $type ?? 'unknown',
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'donnees_supplementaires_debug' => isset($donneesSupplementaires) ? 
                    'Taille: ' . strlen(json_encode($donneesSupplementaires ?? [])) . ' caractères' : 'non_défini',
                'json_last_error' => json_last_error_msg(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'organisation',
                    'error' => $e->getMessage(),
                    'debug' => config('app.debug') ? [
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                        'json_error' => json_last_error_msg()
                    ] : null
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'organisation. Veuillez réessayer.')
                ->withInput();
        }
    }


// =============================================
    // NOUVELLES MÉTHODES POUR SOLUTION 2 PHASES
    // =============================================

   /**
 * CORRECTION MÉTHODE storePhase1() - VERSION CORRIGÉE
 * Résout le problème HTTP 422 "Type d'organisation non reconnu"
 * 
 * POST /operator/organisations/store-phase1
 */
public function storePhase1(Request $request)
{
    // FORCE EXTENSION TIMEOUT (même protection que store())
    @set_time_limit(0);
    @ini_set('memory_limit', '1G');
    
    // 🔍 DEBUGGING AMÉLIORÉ - Log toutes les données reçues
    \Log::info('🔍 DEBUGGING Phase 1 - Données reçues complètes', [
        'user_id' => auth()->id(),
        'all_request_data' => $request->all(),
        'headers' => $request->headers->all(),
        'method' => $request->method(),
        'content_type' => $request->header('Content-Type'),
        'raw_input' => $request->getContent(),
        'version' => 'phase1_debug_v2'
    ]);

    try {
        // 🔧 EXTRACTION TYPE ORGANISATION - MULTIPLE FALLBACKS
        $type = null;
        
        // Méthode 1: Clé standard
        if ($request->has('type_organisation')) {
            $type = $request->input('type_organisation');
            \Log::info('✅ Type trouvé via type_organisation', ['type' => $type]);
        }
        // Méthode 2: Clé alternative organizationType (JavaScript)
        elseif ($request->has('organizationType')) {
            $type = $request->input('organizationType');
            \Log::info('✅ Type trouvé via organizationType', ['type' => $type]);
        }
        // Méthode 3: Dans step1
        elseif ($request->has('step1.selectedOrgType')) {
            $type = $request->input('step1.selectedOrgType');
            \Log::info('✅ Type trouvé via step1.selectedOrgType', ['type' => $type]);
        }
        // Méthode 4: Dans metadata
        elseif ($request->has('metadata.selectedOrgType')) {
            $type = $request->input('metadata.selectedOrgType');
            \Log::info('✅ Type trouvé via metadata.selectedOrgType', ['type' => $type]);
        }
        // Méthode 5: Parsing des données nested
        else {
            $allData = $request->all();
            foreach ($allData as $key => $value) {
                if (is_array($value) && isset($value['selectedOrgType'])) {
                    $type = $value['selectedOrgType'];
                    \Log::info('✅ Type trouvé via parsing nested', ['key' => $key, 'type' => $type]);
                    break;
                }
            }
        }

        // 🚨 VALIDATION TYPE OBLIGATOIRE
        if (empty($type)) {
            \Log::error('❌ ERREUR Phase 1: Type organisation manquant', [
                'received_keys' => array_keys($request->all()),
                'search_attempts' => [
                    'type_organisation' => $request->has('type_organisation'),
                    'organizationType' => $request->has('organizationType'),
                    'step1.selectedOrgType' => $request->has('step1.selectedOrgType'),
                    'metadata.selectedOrgType' => $request->has('metadata.selectedOrgType')
                ]
            ]);
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type d\'organisation manquant dans les données reçues',
                    'errors' => ['type_organisation' => 'Type d\'organisation requis'],
                    'debug' => [
                        'received_keys' => array_keys($request->all()),
                        'help' => 'Vérifiez que organizationType ou type_organisation est envoyé'
                    ]
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Type d\'organisation manquant')
                ->withInput();
        }

        // 🔧 NORMALISATION TYPE
        $typeMapping = [
            'parti' => 'parti_politique',
            'parti_politique' => 'parti_politique',
            'confession' => 'confession_religieuse',
            'confession_religieuse' => 'confession_religieuse',
            'association' => 'association',
            'ong' => 'ong'
        ];
        
        $type = $typeMapping[$type] ?? $type;
        
        \Log::info('✅ Type normalisé pour Phase 1', [
            'type_final' => $type,
            'user_id' => auth()->id(),
            'phase' => 'CREATION_SANS_ADHERENTS'
        ]);

        // Vérifier les limites avant création (avec type valide)
        $canCreate = $this->checkOrganisationLimits($type);
        if (!$canCreate['success']) {
            \Log::warning('❌ Limite organisation atteinte - Phase 1', $canCreate);
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $canCreate['message'],
                    'errors' => ['limite' => $canCreate['message']]
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', $canCreate['message'])
                ->withInput();
        }

        // 🔧 VALIDATION PHASE 1 CORRIGÉE - Données flexibles
        try {
            $validatedData = $this->validatePhase1DataCorrected($request, $type);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Erreur validation Phase 1 v2', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
                'type' => $type,
                'phase' => 'VALIDATION_PHASE1'
            ]);
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation détectées - Phase 1',
                    'errors' => $e->errors()
                ], 422);
            }
            
            throw $e;
        }

        \DB::beginTransaction();

        // ÉTAPE 1-4 : Créer l'organisation principale (IDENTIQUE à store())
        $organisation = Organisation::create([
            'user_id' => auth()->id(),
            'type' => $type,
            'nom' => $validatedData['org_nom'],
            'sigle' => $validatedData['org_sigle'] ?? null,
            'objet' => $validatedData['org_objet'],
            'siege_social' => $validatedData['org_adresse_complete'],
            'province' => $validatedData['org_province'],
            'departement' => $validatedData['org_departement'] ?? null,
            'prefecture' => $validatedData['org_prefecture'],
            'zone_type' => $validatedData['org_zone_type'],
            'latitude' => $validatedData['org_latitude'] ?? null,
            'longitude' => $validatedData['org_longitude'] ?? null,
            'email' => $validatedData['org_email'] ?? null,
            'telephone' => $validatedData['org_telephone'],
            'site_web' => $validatedData['org_site_web'] ?? null,
            'date_creation' => $validatedData['org_date_creation'],
            'statut' => 'soumis',
            'nombre_adherents_min' => $this->getMinAdherents($type)
        ]);

        \Log::info('✅ Organisation créée Phase 1 v2', ['organisation_id' => $organisation->id]);

        // Générer et assigner le numéro de récépissé
        $numeroRecepisse = $this->generateRecepisseNumber($type);
        $organisation->update(['numero_recepisse' => $numeroRecepisse]);

        // ÉTAPE 6 : Créer les fondateurs (IDENTIQUE à store())
        if (!empty($validatedData['fondateurs'])) {
            $this->createFondateurs($organisation, $validatedData['fondateurs']);
            \Log::info('✅ Fondateurs créés Phase 1 v2', ['count' => count($validatedData['fondateurs'])]);
        }

        // ÉTAPE 7 IGNORÉE EN PHASE 1 : PAS D'ADHÉRENTS
        \Log::info('ℹ️ Adhérents ignorés en Phase 1 - sera traité en Phase 2', [
            'adherents_received' => !empty($validatedData['adherents']) ? count($validatedData['adherents']) : 0
        ]);

        // ÉTAPE 5 : Créer le dossier de traitement (SANS adhérents)
        $donneesSupplementaires = [
            'demandeur' => [
                'nip' => $validatedData['demandeur_nip'],
                'nom' => $validatedData['demandeur_nom'],
                'prenom' => $validatedData['demandeur_prenom'],
                'email' => $validatedData['demandeur_email'],
                'telephone' => $validatedData['demandeur_telephone'],
                'role' => $validatedData['demandeur_role'] ?? null
            ],
            'guide_lu' => $validatedData['guide_read_confirm'] ?? false,
            'declarations' => [
                'veracite' => $validatedData['declaration_veracite'] ?? false,
                'conformite' => $validatedData['declaration_conformite'] ?? false,
                'autorisation' => $validatedData['declaration_autorisation'] ?? false
            ],
            // MARQUEUR PHASE 1
            'phase_creation' => '1_sans_adherents',
            'phase1_completed_at' => now()->toISOString(),
            'adherents_phase2_pending' => !empty($validatedData['adherents'])
        ];

        // Nettoyer et encoder les données JSON (IDENTIQUE à store())
        $donneesSupplementairesCleaned = $this->sanitizeJsonData($donneesSupplementaires);

        $dossier = Dossier::create([
            'organisation_id' => $organisation->id,
            'type_operation' => 'creation',
            'numero_dossier' => $this->generateDossierNumber($type),
            'statut' => 'soumis',
            'submitted_at' => now(),
            'donnees_supplementaires' => json_encode($donneesSupplementairesCleaned, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION)
        ]);

        \Log::info('✅ Dossier créé Phase 1 v2', [
            'dossier_id' => $dossier->id,
            'phase' => 'CREATION_SANS_ADHERENTS'
        ]);

        // ÉTAPE 8 : Traiter les documents uploadés (IDENTIQUE à store())
        if ($request->hasFile('documents')) {
            $this->handleDocumentUploads($request, $dossier);
        }

        // Initialiser le workflow FIFO (IDENTIQUE à store())
        $this->workflowService->initializeWorkflow($dossier);

        // Générer QR Code pour vérification (IDENTIQUE à store())
        $qrCode = null;
        try {
            $qrCode = $this->qrCodeService->generateForDossier($dossier);
            if ($qrCode) {
                \Log::info('✅ QR Code généré avec succès Phase 1 v2', ['qr_code_id' => $qrCode->id]);
            }
        } catch (\Exception $e) {
            \Log::error('⚠️ Erreur QR Code non bloquante Phase 1 v2', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            $qrCode = null;
        }

        // Générer accusé de réception pour Phase 1
        $accuseReceptionPath = $this->generateAccuseReceptionPhase1($dossier, $organisation, auth()->user());

        \DB::commit();

        \Log::info('🎉 Transaction Phase 1 validée avec succès v2', [
            'organisation_id' => $organisation->id,
            'dossier_id' => $dossier->id,
            'numero_recepisse' => $numeroRecepisse,
            'phase' => 'CREATION_SANS_ADHERENTS_COMPLETE'
        ]);

        // DONNÉES DE CONFIRMATION PHASE 1 SPÉCIFIQUES
        $confirmationData = [
            'organisation' => $organisation,
            'dossier' => $dossier,
            'numero_recepisse' => $numeroRecepisse,
            'qr_code' => $qrCode,
            'phase' => 1,
            'phase_message' => 'Phase 1 complétée avec succès : Organisation créée sans adhérents',
            'adherents_pending' => !empty($validatedData['adherents']),
            'next_phase_url' => route('operator.organisations.adherents-import', $dossier->id),
            'accuse_reception_path' => $accuseReceptionPath,
            'message_confirmation' => 'Phase 1 terminée avec succès. Votre organisation a été créée. Pour ajouter les adhérents, procédez à la Phase 2.',
            'delai_traitement' => '72 heures ouvrées (après ajout des adhérents)'
        ];

        // RÉPONSE PHASE 1 : REDIRECTION VERS PHASE 2 OU CONFIRMATION
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Phase 1 complétée avec succès : Organisation créée',
                'phase' => 1,
                'data' => [
                    'organisation_id' => $organisation->id,
                    'dossier_id' => $dossier->id,
                    'numero_recepisse' => $numeroRecepisse,
                    'phase2_url' => route('operator.organisations.adherents-import', $dossier->id),
                    'confirmation_url' => route('operator.dossiers.confirmation', $dossier->id)
                ],
                'confirmation_data' => $confirmationData,
                'next_action' => 'PROCEED_TO_PHASE_2'
            ]);
        } else {
            // REDIRECTION : PHASE 2 si adhérents en attente, sinon confirmation
            if (!empty($validatedData['adherents'])) {
                return redirect()->route('operator.organisations.adherents-import', $dossier->id)
                    ->with('phase1_data', $confirmationData)
                    ->with('success', 'Phase 1 complétée. Ajoutez maintenant vos adhérents en Phase 2.');
            } else {
                return redirect()->route('operator.dossiers.confirmation', $dossier->id)
                    ->with('success_data', $confirmationData);
            }
        }

    } catch (\Exception $e) {
        \DB::rollback();
        
        \Log::error('❌ Erreur création organisation Phase 1 v2', [
            'user_id' => auth()->id(),
            'type' => $type ?? 'unknown',
            'phase' => 'CREATION_SANS_ADHERENTS',
            'error_message' => $e->getMessage(),
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'organisation - Phase 1',
                'phase' => 1,
                'error' => $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ] : null
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'Erreur lors de la création de l\'organisation (Phase 1). Veuillez réessayer.')
            ->withInput();
    }
}

/**
 * 🔧 NOUVELLE MÉTHODE : Validation Phase 1 CORRIGÉE - Gestion flexible des données
 */
private function validatePhase1DataCorrected(Request $request, $type)
{
    \Log::info('🔍 Validation Phase 1 v2 - Analyse des données', [
        'keys' => array_keys($request->all()),
        'type' => $type,
        'version' => 'phase1_validation_flexible_v2'
    ]);

    // 🔧 EXTRACTION FLEXIBLE DES DONNÉES
    $extractedData = $this->extractFormDataFlexible($request);
    
    // 🔧 RÈGLES DE VALIDATION PHASE 1 ADAPTATIVES
    $rules = [
        // Type déjà validé plus haut
        'org_nom' => 'required|string|max:255',
        'org_objet' => 'required|string|min:10', // Plus souple
        'org_telephone' => 'required|string|max:255',
        'org_adresse_complete' => 'required|string|max:255',
        'org_province' => 'required|string|max:255',
        'org_prefecture' => 'required|string|max:255',
        'org_zone_type' => 'required|in:urbaine,rurale',
        
        // Demandeur - champs essentiels
        'demandeur_nip' => 'required|string|min:10',
        'demandeur_nom' => 'required|string|max:255',
        'demandeur_prenom' => 'required|string|max:255',
        'demandeur_email' => 'required|email|max:255',
        'demandeur_telephone' => 'required|string|max:20',
        
        // Fondateurs - validation souple
        'fondateurs' => 'nullable|array|min:1'
    ];

    $messages = [
        'org_nom.required' => 'Le nom de l\'organisation est obligatoire',
        'org_objet.required' => 'L\'objet de l\'organisation est obligatoire',
        'org_objet.min' => 'L\'objet doit contenir au moins 10 caractères',
        'demandeur_nip.required' => 'Le NIP du demandeur est obligatoire',
        'demandeur_email.required' => 'L\'email du demandeur est obligatoire'
    ];

    // 🔧 VALIDATION AVEC DONNÉES EXTRAITES
    $validator = \Validator::make($extractedData, $rules, $messages);
    
    if ($validator->fails()) {
        throw new \Illuminate\Validation\ValidationException($validator);
    }
    
    $validated = $validator->validated();
    
    // 🔧 COMPLÉTER AVEC VALEURS PAR DÉFAUT
    $validated['org_sigle'] = $extractedData['org_sigle'] ?? null;
    $validated['org_email'] = $extractedData['org_email'] ?? null;
    $validated['org_site_web'] = $extractedData['org_site_web'] ?? null;
    $validated['org_departement'] = $extractedData['org_departement'] ?? null;
    $validated['org_latitude'] = $extractedData['org_latitude'] ?? null;
    $validated['org_longitude'] = $extractedData['org_longitude'] ?? null;
    $validated['org_date_creation'] = $extractedData['org_date_creation'] ?? now()->format('Y-m-d');
    
    $validated['demandeur_role'] = $extractedData['demandeur_role'] ?? 'Président';
    $validated['guide_read_confirm'] = $extractedData['guide_read_confirm'] ?? true;
    $validated['declaration_veracite'] = $extractedData['declaration_veracite'] ?? true;
    $validated['declaration_conformite'] = $extractedData['declaration_conformite'] ?? true;
    $validated['declaration_autorisation'] = $extractedData['declaration_autorisation'] ?? true;
    
    // 🔧 TRAITEMENT FONDATEURS
    $validated['fondateurs'] = $extractedData['fondateurs'] ?? [];
    
    // 🔧 TRAITEMENT ADHÉRENTS (OPTIONNELS EN PHASE 1)
    $validated['adherents'] = $extractedData['adherents'] ?? [];
    
    \Log::info('✅ Validation Phase 1 v2 réussie', [
        'org_nom' => $validated['org_nom'],
        'fondateurs_count' => count($validated['fondateurs']),
        'adherents_count' => count($validated['adherents']),
        'type' => $type
    ]);
    
    return $validated;
}

/**
 * 🔧 MÉTHODE UTILITAIRE : Extraire les données de forme flexible
 */
private function extractFormDataFlexible(Request $request)
{
    $extracted = [];
    $allData = $request->all();
    
    // 🔍 STRATÉGIES D'EXTRACTION MULTIPLES
    
    // Stratégie 1: Données directes
    foreach ($allData as $key => $value) {
        if (strpos($key, 'org_') === 0 || strpos($key, 'demandeur_') === 0) {
            $extracted[$key] = $value;
        }
    }
    
    // Stratégie 2: Données dans des steps
    foreach (['step3', 'step4', 'step5', 'step6'] as $step) {
        if (isset($allData[$step]) && is_array($allData[$step])) {
            foreach ($allData[$step] as $key => $value) {
                if (!isset($extracted[$key])) {
                    $extracted[$key] = $value;
                }
            }
        }
    }
    
    // Stratégie 3: Parsing récursif
    $this->extractRecursive($allData, $extracted);
    
    \Log::info('🔍 Données extraites en Phase 1', [
        'extracted_keys' => array_keys($extracted),
        'strategies_used' => ['direct', 'steps', 'recursive']
    ]);
    
    return $extracted;
}

/**
 * 🔧 MÉTHODE UTILITAIRE : Extraction récursive
 */
private function extractRecursive($data, &$extracted, $prefix = '')
{
    if (!is_array($data)) return;
    
    foreach ($data as $key => $value) {
        $fullKey = $prefix ? $prefix . '.' . $key : $key;
        
        if (is_array($value)) {
            $this->extractRecursive($value, $extracted, $fullKey);
        } else {
            // Chercher les clés importantes
            if (preg_match('/^(org_|demandeur_|fondateurs|adherents|declaration_|guide_)/', $key)) {
                if (!isset($extracted[$key])) {
                    $extracted[$key] = $value;
                }
            }
        }
    }
}

    /**
     * NOUVELLE MÉTHODE : Validation Phase 1 - Adhérents OPTIONNELS
     * Réutilise validateCompleteOrganisationData() en rendant adhérents optionnels
     */
    private function validatePhase1Data(Request $request, $type)
    {
        // Log des données reçues pour debugging
        \Log::info('Validation Phase 1 v1 - Adhérents optionnels', [
            'keys' => array_keys($request->all()),
            'type' => $type,
            'regle_metier' => 'Phase 1 sans adhérents obligatoires',
            'version' => 'phase1_validation_v1'
        ]);

        // RÈGLES IDENTIQUES À validateCompleteOrganisationData() SAUF ADHÉRENTS
        $rules = [
            // ÉTAPE 1 : Type
            'type_organisation' => 'required|in:association,ong,parti_politique,confession_religieuse',

            // ÉTAPE 2 : Guide
            'guide_read_confirm' => 'sometimes|accepted',
            
            // ÉTAPE 3 : Demandeur - COLONNES CONFORMES À USERS TABLE
            'demandeur_nip' => 'required|digits:13',
            'demandeur_nom' => 'required|string|max:255',
            'demandeur_prenom' => 'required|string|max:255',
            'demandeur_email' => 'required|email|max:255',
            'demandeur_telephone' => 'required|string|max:20',
            'demandeur_role' => 'sometimes|string',
            'demandeur_civilite' => 'sometimes|in:M,Mme,Mlle',
            'demandeur_date_naissance' => 'sometimes|date|before:-18 years',
            'demandeur_nationalite' => 'sometimes|string|max:255',
            'demandeur_adresse' => 'sometimes|string|max:500',
            'demandeur_profession' => 'sometimes|string|max:255',
            
            // ÉTAPE 4 : Organisation - COLONNES CONFORMES À ORGANISATIONS TABLE
            'org_nom' => 'required|string|max:255|unique:organisations,nom',
            'org_sigle' => 'nullable|string|max:255|unique:organisations,sigle',
            'org_objet' => 'required|string|min:50',
            'org_date_creation' => 'required|date',
            'org_telephone' => 'required|string|max:255',
            'org_email' => 'nullable|email|max:255',
            'org_site_web' => 'nullable|url|max:255',
            'org_domaine' => 'sometimes|string|max:255',
            
            // ÉTAPE 5 : Coordonnées - COLONNES CONFORMES À ORGANISATIONS TABLE
            'org_adresse_complete' => 'required|string|max:255',
            'org_province' => 'required|string|max:255',
            'org_departement' => 'nullable|string|max:255',
            'org_prefecture' => 'required|string|max:255',
            'org_zone_type' => 'required|in:urbaine,rurale',
            'org_latitude' => 'nullable|numeric|between:-3.978,2.318',
            'org_longitude' => 'nullable|numeric|between:8.695,14.502',
            
            // ÉTAPE 6 : Fondateurs - VALIDATION IDENTIQUE (obligatoire)
            'fondateurs' => [
                'required',
                function ($attribute, $value, $fail) use ($type) {
                    // Décoder JSON si c'est une string
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('Les données des fondateurs sont invalides (JSON malformé): ' . json_last_error_msg());
                            return;
                        }
                        $value = $decoded;
                        request()->merge(['fondateurs' => $value]);
                    }
                    
                    if (!is_array($value)) {
                        $fail('Les fondateurs doivent être un tableau.');
                        return;
                    }
                    
                    $minRequired = $this->getMinFondateurs($type);
                    if (count($value) < $minRequired) {
                        $fail("Minimum {$minRequired} fondateurs requis pour ce type d'organisation.");
                    }
                    
                    // Validation des fondateurs (identique à validateCompleteOrganisationData)
                    foreach ($value as $index => $fondateur) {
                        if (!is_array($fondateur)) {
                            $fail("Le fondateur ligne " . ($index + 1) . " doit être un objet valide.");
                            continue;
                        }
                        
                        if (empty($fondateur['nip'])) {
                            $fail("Le NIP du fondateur ligne " . ($index + 1) . " ne peut pas être vide.");
                        }
                        
                        if (empty($fondateur['nom']) || empty($fondateur['prenom'])) {
                            $fail("Le nom et prénom du fondateur ligne " . ($index + 1) . " sont obligatoires.");
                        }
                        if (empty($fondateur['fonction'])) {
                            $fail("La fonction du fondateur ligne " . ($index + 1) . " est obligatoire.");
                        }
                        if (empty($fondateur['telephone'])) {
                            $fail("Le téléphone du fondateur ligne " . ($index + 1) . " est obligatoire.");
                        }
                    }
                }
            ],
            
            // ÉTAPE 7 : Adhérents OPTIONNELS EN PHASE 1
            'adherents' => [
                'nullable', // CHANGEMENT MAJEUR : nullable au lieu de required
                function ($attribute, $value, $fail) use ($type) {
                    // Si adhérents fournis, validation légère pour stockage temporaire
                    if (!empty($value)) {
                        // Décoder JSON si c'est une string
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $fail('Les données des adhérents sont invalides (JSON malformé): ' . json_last_error_msg());
                                return;
                            }
                            $value = $decoded;
                            request()->merge(['adherents' => $value]);
                        }
                        
                        if (!is_array($value)) {
                            $fail('Les adhérents doivent être un tableau.');
                            return;
                        }
                        
                        // VALIDATION MINIMALE EN PHASE 1 (stockage temporaire)
                        foreach ($value as $index => $adherent) {
                            if (!is_array($adherent)) {
                                $fail("L'adhérent ligne " . ($index + 1) . " doit être un objet valide.");
                                continue;
                            }
                            
                            // Vérifications de base seulement
                            if (empty($adherent['nip']) || trim($adherent['nip']) === '') {
                                $fail("Le NIP de l'adhérent ligne " . ($index + 1) . " ne peut pas être vide.");
                            }
                            
                            if (empty($adherent['nom']) || empty($adherent['prenom'])) {
                                $fail("Le nom et prénom de l'adhérent ligne " . ($index + 1) . " sont obligatoires.");
                            }
                        }
                    }
                }
            ],
            
            // ÉTAPE 9 : Déclarations finales
            'declaration_veracite' => 'sometimes|accepted',
            'declaration_conformite' => 'sometimes|accepted',
            'declaration_autorisation' => 'sometimes|accepted'
        ];

        // RÈGLES SPÉCIFIQUES PARTI POLITIQUE (sans minimum adhérents)
        if ($type === 'parti_politique') {
            $rules['declaration_exclusivite_parti'] = 'required|accepted';
            // PAS DE VALIDATION MINIMUM ADHÉRENTS EN PHASE 1
        }

        $messages = [
            'demandeur_nip.digits' => 'Le NIP du demandeur doit contenir exactement 13 chiffres.',
            'demandeur_nip.required' => 'Le NIP du demandeur est obligatoire.',
            'org_nom.unique' => 'Ce nom d\'organisation est déjà utilisé.',
            'org_sigle.unique' => 'Ce sigle est déjà utilisé.',
            'org_objet.min' => 'L\'objet de l\'organisation doit contenir au moins 50 caractères.',
            'org_objet.required' => 'L\'objet de l\'organisation est obligatoire.',
            'declaration_exclusivite_parti.required' => 'La déclaration d\'exclusivité pour parti politique est obligatoire.',
            'declaration_exclusivite_parti.accepted' => 'Vous devez accepter la déclaration d\'exclusivité.',
            'fondateurs.required' => 'Les fondateurs sont obligatoires même en Phase 1.',
            'adherents.nullable' => 'Les adhérents sont optionnels en Phase 1.',
            '*.accepted' => 'Cette déclaration est obligatoire.',
            '*.required' => 'Ce champ est obligatoire.'
        ];

        try {
            $validated = $request->validate($rules, $messages);
            
            // Post-traitement avec nettoyage des données (IDENTIQUE)
            if (isset($validated['fondateurs'])) {
                if (is_string($validated['fondateurs'])) {
                    $decoded = json_decode($validated['fondateurs'], true);
                    $validated['fondateurs'] = $decoded ?? [];
                }
                if (!is_array($validated['fondateurs'])) {
                    $validated['fondateurs'] = [];
                }
                
                // Nettoyer les NIP des fondateurs
                foreach ($validated['fondateurs'] as &$fondateur) {
                    if (isset($fondateur['nip'])) {
                        $fondateur['nip'] = $this->cleanNipForStorage($fondateur['nip']);
                    }
                }
            }
            
            // TRAITEMENT SPÉCIAL ADHÉRENTS PHASE 1
            if (isset($validated['adherents']) && !empty($validated['adherents'])) {
                if (is_string($validated['adherents'])) {
                    $decoded = json_decode($validated['adherents'], true);
                    $validated['adherents'] = $decoded ?? [];
                }
                if (!is_array($validated['adherents'])) {
                    $validated['adherents'] = [];
                }
                
                // Nettoyer les NIP des adhérents (stockage temporaire)
                foreach ($validated['adherents'] as &$adherent) {
                    if (isset($adherent['nip'])) {
                        $adherent['nip'] = $this->cleanNipForStorage($adherent['nip']);
                    }
                    
                    // Assurer la fonction par défaut
                    if (empty($adherent['fonction'])) {
                        $adherent['fonction'] = 'Membre';
                    }
                }
                
                \Log::info('Adhérents reçus en Phase 1 pour stockage temporaire', [
                    'count' => count($validated['adherents']),
                    'note' => 'Seront traités en Phase 2 avec validation complète'
                ]);
            } else {
                // Pas d'adhérents fournis en Phase 1
                $validated['adherents'] = [];
                \Log::info('Aucun adhérent fourni en Phase 1 - Normal pour cette phase');
            }
            
            // Ajouter des valeurs par défaut (IDENTIQUE)
            $validated['org_departement'] = $request->input('org_departement');
            $validated['declaration_veracite'] = $request->has('declaration_veracite');
            $validated['declaration_conformite'] = $request->has('declaration_conformite');
            $validated['declaration_autorisation'] = $request->has('declaration_autorisation');
            $validated['guide_read_confirm'] = $request->has('guide_read_confirm');
            
            \Log::info('Validation Phase 1 réussie v1', [
                'fondateurs_count' => count($validated['fondateurs'] ?? []),
                'adherents_count' => count($validated['adherents'] ?? []),
                'type' => $type,
                'validation_version' => 'phase1_sans_adherents_obligatoires_v1'
            ]);
            
            return $validated;
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur validation Phase 1 v1', [
                'errors' => $e->errors(),
                'type' => $type,
                'validation_version' => 'phase1_sans_adherents_obligatoires_v1'
            ]);
            
            throw $e;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Générer accusé de réception spécifique Phase 1
     * Indique que l'organisation est créée SANS adhérents
     */
    private function generateAccuseReceptionPhase1(Dossier $dossier, Organisation $organisation, $user)
    {
        try {
            $data = [
                'dossier' => $dossier,
                'organisation' => $organisation,
                'user' => $user,
                'date_generation' => now(),
                'numero_recepisse' => $organisation->numero_recepisse,
                'phase' => 1,
                'phase_message' => 'Phase 1 complétée : Organisation créée sans adhérents'
            ];

            $filename = 'accuse_reception_phase1_' . $dossier->numero_dossier . '_' . time() . '.pdf';
            $storagePath = 'accuses_reception/' . $filename;
            $fullPath = storage_path('app/public/' . $storagePath);
            
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            $htmlContent = $this->generateAccuseReceptionPhase1HTML($data);
            file_put_contents($fullPath, $htmlContent);
            
            \App\Models\Document::create([
                'dossier_id' => $dossier->id,
                'document_type_id' => 99,
                'nom_fichier' => $filename,
                'nom_original' => 'Accusé de réception Phase 1',
                'chemin_fichier' => $storagePath,
                'type_mime' => 'application/pdf',
                'taille' => strlen($htmlContent),
                'hash_fichier' => hash('sha256', $htmlContent),
                'is_system_generated' => true,
                'metadata' => json_encode(['phase' => 1, 'type' => 'accuse_phase1']),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            \Log::info('Accusé Phase 1 généré avec succès v1', [
                'dossier_id' => $dossier->id,
                'filename' => $filename,
                'phase' => 1
            ]);
            
            return $storagePath;
            
        } catch (\Exception $e) {
            \Log::error('Erreur génération accusé Phase 1 v1: ' . $e->getMessage(), [
                'dossier_id' => $dossier->id,
                'error' => $e->getTraceAsString()
            ]);
            return null;
        }
    }


/**
 * NOUVELLE MÉTHODE : Ajouter les adhérents en Phase 2
 * Traite les adhérents sur une organisation déjà créée en Phase 1
 * 
 * POST /operator/organisations/{dossier}/store-adherents
 */
public function storeAdherentsPhase2(Request $request, $dossierId)
{
    // FORCE EXTENSION TIMEOUT pour gros volumes
    @set_time_limit(0);
    @ini_set('memory_limit', '1G');
    
    // 🔍 DEBUGGING COMPLET Phase 2
    \Log::info('🚀 DÉBUT Phase 2 - Import adhérents', [
        'user_id' => auth()->id(),
        'dossier_id' => $dossierId,
        'request_data_keys' => array_keys($request->all()),
        'content_type' => $request->header('Content-Type'),
        'method' => $request->method(),
        'version' => 'phase2_v1'
    ]);

    try {
        // 🔍 RÉCUPÉRER ET VALIDER LE DOSSIER PHASE 1
        $dossier = Dossier::with(['organisation'])
            ->where('id', $dossierId)
            ->whereHas('organisation', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->first();

        if (!$dossier) {
            \Log::error('❌ Dossier non trouvé pour Phase 2', [
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

        // 🔍 VÉRIFIER QUE C'EST BIEN UN DOSSIER PHASE 1
        $donneesSupplementaires = [];
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true) ?? [];
            } elseif (is_array($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = $dossier->donnees_supplementaires;
            }
        }

        $isPhase1Completed = isset($donneesSupplementaires['phase_creation']) && 
                             $donneesSupplementaires['phase_creation'] === '1_sans_adherents';

        if (!$isPhase1Completed) {
            \Log::warning('❌ Phase 2 tentée sur dossier non-Phase 1', [
                'dossier_id' => $dossierId,
                'phase_creation' => $donneesSupplementaires['phase_creation'] ?? 'non_définie'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ce dossier n\'est pas en attente d\'import d\'adhérents Phase 2',
                'error_code' => 'INVALID_PHASE'
            ], 422);
        }

        // 🔧 EXTRACTION DES ADHÉRENTS - MULTIPLES SOURCES
        $adherentsData = $this->extractAdherentsPhase2($request);
        
        if (empty($adherentsData)) {
            \Log::warning('❌ Aucun adhérent reçu en Phase 2', [
                'dossier_id' => $dossierId,
                'request_keys' => array_keys($request->all())
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Aucun adhérent fourni pour l\'import',
                'error_code' => 'NO_ADHERENTS_DATA'
            ], 422);
        }

        \Log::info('✅ Adhérents extraits pour Phase 2', [
            'count' => count($adherentsData),
            'dossier_id' => $dossierId,
            'organisation_type' => $organisation->type
        ]);

        // 🔧 DÉTERMINER LA MÉTHODE DE TRAITEMENT
        $processingMethod = $this->determineProcessingMethod($adherentsData, $request);
        
        \Log::info('📊 Méthode de traitement déterminée', [
            'method' => $processingMethod,
            'adherents_count' => count($adherentsData),
            'chunking_threshold' => 200
        ]);

        \DB::beginTransaction();

        // 🚀 TRAITEMENT SELON LA MÉTHODE
        $adherentsResult = null;
        
        if ($processingMethod === 'chunking') {
            \Log::info('📦 CHUNKING ACTIVÉ pour Phase 2', [
                'adherents_count' => count($adherentsData),
                'organisation_id' => $organisation->id
            ]);
            
            // Le chunking est géré côté frontend, on reçoit les données par chunks
            $adherentsResult = $this->processAdherentsChunk($organisation, $adherentsData);
        } else {
            \Log::info('📝 Traitement standard Phase 2', [
                'adherents_count' => count($adherentsData),
                'organisation_id' => $organisation->id
            ]);
            
            // Traitement direct standard
            $adherentsResult = $this->createAdherents($organisation, $adherentsData);
        }

        // 🔧 METTRE À JOUR LE DOSSIER - FINALISER PHASE 2
        $donneesSupplementaires['phase_creation'] = '2_complete';
        $donneesSupplementaires['phase2_completed_at'] = now()->toISOString();
        $donneesSupplementaires['adherents_import_method'] = $processingMethod;
        $donneesSupplementaires['adherents_stats'] = $adherentsResult['stats'];
        
        // Ajouter les anomalies si présentes
        if (!empty($adherentsResult['anomalies'])) {
            $donneesSupplementaires['adherents_anomalies'] = $adherentsResult['anomalies'];
        }

        // Nettoyer et mettre à jour
        $donneesSupplementairesCleaned = $this->sanitizeJsonData($donneesSupplementaires);
        
        $dossier->update([
            'donnees_supplementaires' => json_encode($donneesSupplementairesCleaned, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
            'updated_at' => now()
        ]);

        // 🔧 GÉNÉRER ACCUSÉ DE RÉCEPTION FINAL
        $accuseReceptionPath = $this->generateAccuseReceptionFinal($dossier, $organisation, auth()->user(), $adherentsResult);

        \DB::commit();

        \Log::info('🎉 Phase 2 complétée avec succès', [
            'organisation_id' => $organisation->id,
            'dossier_id' => $dossier->id,
            'adherents_processed' => $adherentsResult['stats']['total'],
            'processing_method' => $processingMethod
        ]);

        // 🎯 DONNÉES DE CONFIRMATION PHASE 2
        $confirmationData = [
            'organisation' => $organisation,
            'dossier' => $dossier,
            'numero_recepisse' => $organisation->numero_recepisse,
            'phase' => 2,
            'phase_message' => 'Phase 2 complétée : Adhérents ajoutés avec succès',
            'adherents_stats' => $adherentsResult['stats'],
            'anomalies' => $adherentsResult['anomalies'] ?? [],
            'processing_method' => $processingMethod,
            'accuse_reception_path' => $accuseReceptionPath,
            'message_confirmation' => 'Import des adhérents terminé avec succès. Votre dossier est maintenant complet.',
            'delai_traitement' => '72 heures ouvrées'
        ];

        // 🎯 RÉPONSE PHASE 2
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Phase 2 complétée avec succès : Adhérents importés',
                'phase' => 2,
                'data' => [
                    'organisation_id' => $organisation->id,
                    'dossier_id' => $dossier->id,
                    'adherents_processed' => $adherentsResult['stats']['total'],
                    'confirmation_url' => route('operator.dossiers.confirmation', $dossier->id)
                ],
                'stats' => $adherentsResult['stats'],
                'anomalies' => $adherentsResult['anomalies'] ?? [],
                'confirmation_data' => $confirmationData,
                'next_action' => 'WORKFLOW_COMPLETE'
            ]);
        } else {
            return redirect()->route('operator.dossiers.confirmation', $dossier->id)
                ->with('success_data', $confirmationData)
                ->with('success', 'Phase 2 complétée : Adhérents importés avec succès');
        }

    } catch (\Exception $e) {
        \DB::rollback();
        
        \Log::error('❌ Erreur Phase 2 - Import adhérents', [
            'user_id' => auth()->id(),
            'dossier_id' => $dossierId,
            'error_message' => $e->getMessage(),
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import des adhérents - Phase 2',
                'phase' => 2,
                'error' => $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ] : null
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'Erreur lors de l\'import des adhérents. Veuillez réessayer.')
            ->withInput();
    }
}

/**
 * 🔧 MÉTHODE UTILITAIRE : Extraire les adhérents des données Phase 2
 */
private function extractAdherentsPhase2(Request $request)
{
    $adherents = [];
    $allData = $request->all();
    
    \Log::info('🔍 Extraction adhérents Phase 2', [
        'data_keys' => array_keys($allData),
        'content_type' => $request->header('Content-Type')
    ]);
    
    // 🔍 STRATÉGIES D'EXTRACTION MULTIPLES
    
    // Stratégie 1: Clé directe 'adherents'
    if (isset($allData['adherents'])) {
        $adherentsRaw = $allData['adherents'];
        
        if (is_string($adherentsRaw)) {
            $decoded = json_decode($adherentsRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $adherents = $decoded;
                \Log::info('✅ Adhérents extraits via JSON string', ['count' => count($adherents)]);
            }
        } elseif (is_array($adherentsRaw)) {
            $adherents = $adherentsRaw;
            \Log::info('✅ Adhérents extraits via array direct', ['count' => count($adherents)]);
        }
    }
    
    // Stratégie 2: Données de chunking
    if (empty($adherents) && isset($allData['chunk_data'])) {
        $chunkData = $allData['chunk_data'];
        if (is_array($chunkData)) {
            $adherents = $chunkData;
            \Log::info('✅ Adhérents extraits via chunk_data', ['count' => count($adherents)]);
        }
    }
    
    // Stratégie 3: SessionStorage (workflow 2 phases)
    if (empty($adherents) && $request->has('sessionStorage_adherents')) {
        $sessionData = $request->input('sessionStorage_adherents');
        if (is_string($sessionData)) {
            $decoded = json_decode($sessionData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $adherents = $decoded;
                \Log::info('✅ Adhérents extraits via sessionStorage', ['count' => count($adherents)]);
            }
        }
    }
    
    // Stratégie 4: FormData standard
    if (empty($adherents)) {
        foreach ($allData as $key => $value) {
            if (strpos($key, 'adherent_') === 0 && is_array($value)) {
                $adherents[] = $value;
            }
        }
        if (!empty($adherents)) {
            \Log::info('✅ Adhérents extraits via FormData', ['count' => count($adherents)]);
        }
    }
    
    // 🔧 VALIDATION ET NETTOYAGE
    if (!empty($adherents)) {
        $adherents = array_filter($adherents, function($adherent) {
            return is_array($adherent) && 
                   !empty($adherent['nip']) && 
                   !empty($adherent['nom']) && 
                   !empty($adherent['prenom']);
        });
        
        \Log::info('✅ Adhérents validés Phase 2', [
            'count_final' => count($adherents),
            'sample' => !empty($adherents) ? [
                'nip' => $adherents[0]['nip'] ?? 'N/A',
                'nom' => $adherents[0]['nom'] ?? 'N/A',
                'prenom' => $adherents[0]['prenom'] ?? 'N/A'
            ] : []
        ]);
    }
    
    return $adherents;
}

/**
 * 🔧 MÉTHODE UTILITAIRE : Déterminer la méthode de traitement
 */
private function determineProcessingMethod(array $adherentsData, Request $request)
{
    $count = count($adherentsData);
    
    // Force chunking si explicitement demandé
    if ($request->has('use_chunking') && $request->boolean('use_chunking')) {
        return 'chunking';
    }
    
    // Force chunking si provient du système de chunking
    if ($request->has('processing_method') && $request->input('processing_method') === 'chunking') {
        return 'chunking';
    }
    
    // Auto-détection basée sur le volume
    if ($count >= 200) {
        return 'chunking';
    }
    
    return 'standard';
}

/**
 * 🔧 MÉTHODE UTILITAIRE : Traiter un chunk d'adhérents
 */
private function processAdherentsChunk(Organisation $organisation, array $adherentsData)
{
    \Log::info('📦 Traitement chunk adhérents', [
        'organisation_id' => $organisation->id,
        'chunk_size' => count($adherentsData)
    ]);
    
    // Utiliser la méthode existante createAdherents avec amélioration pour chunking
    $result = $this->createAdherents($organisation, $adherentsData);
    
    // Ajouter metadata chunking
    $result['processing_info'] = [
        'method' => 'chunking',
        'chunk_size' => count($adherentsData),
        'processed_at' => now()->toISOString()
    ];
    
    return $result;
}

/**
 * 🔧 NOUVELLE MÉTHODE : Générer accusé de réception final
 */
private function generateAccuseReceptionFinal(Dossier $dossier, Organisation $organisation, $user, $adherentsResult)
{
    try {
        $data = [
            'dossier' => $dossier,
            'organisation' => $organisation,
            'user' => $user,
            'date_generation' => now(),
            'numero_recepisse' => $organisation->numero_recepisse,
            'phase' => 'COMPLETE',
            'adherents_stats' => $adherentsResult['stats'],
            'anomalies' => $adherentsResult['anomalies'] ?? [],
            'phase_message' => 'Workflow 2 phases terminé : Organisation et adhérents créés'
        ];

        $filename = 'accuse_reception_final_' . $dossier->numero_dossier . '_' . time() . '.pdf';
        $storagePath = 'accuses_reception/' . $filename;
        $fullPath = storage_path('app/public/' . $storagePath);
        
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $htmlContent = $this->generateAccuseReceptionFinalHTML($data);
        file_put_contents($fullPath, $htmlContent);
        
        \App\Models\Document::create([
            'dossier_id' => $dossier->id,
            'document_type_id' => 99,
            'nom_fichier' => $filename,
            'nom_original' => 'Accusé de réception final',
            'chemin_fichier' => $storagePath,
            'type_mime' => 'application/pdf',
            'taille' => strlen($htmlContent),
            'hash_fichier' => hash('sha256', $htmlContent),
            'is_system_generated' => true,
            'metadata' => json_encode(['phase' => 'final', 'type' => 'accuse_final']),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        \Log::info('✅ Accusé final généré avec succès', [
            'dossier_id' => $dossier->id,
            'filename' => $filename,
            'phase' => 'COMPLETE'
        ]);
        
        return $storagePath;
        
    } catch (\Exception $e) {
        \Log::error('❌ Erreur génération accusé final: ' . $e->getMessage(), [
            'dossier_id' => $dossier->id,
            'error' => $e->getTraceAsString()
        ]);
        return null;
    }
}

/**
 * 🔧 NOUVELLE MÉTHODE : HTML pour accusé final
 */
private function generateAccuseReceptionFinalHTML($data)
{
    $stats = $data['adherents_stats'];
    $anomalies = $data['anomalies'];
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accusé de Réception Final - ' . $data['dossier']->numero_dossier . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #006633; padding-bottom: 20px; }
        .logo { color: #006633; font-size: 24px; font-weight: bold; }
        .title { color: #FFA500; font-size: 18px; margin-top: 10px; }
        .content { margin-top: 30px; }
        .info-box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
        .success-box { background: #d4edda; border: 2px solid #28a745; padding: 15px; margin: 20px 0; }
        .stats-box { background: #e9ecef; border: 1px solid #6c757d; padding: 15px; margin: 20px 0; }
        .warning-box { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
        .phase-title { color: #28a745; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">RÉPUBLIQUE GABONAISE</div>
        <div>Union - Travail - Justice</div>
        <div class="title">MINISTÈRE DE L\'INTÉRIEUR</div>
        <div>Direction des Organisations</div>
    </div>

    <div class="content">
        <h2 style="text-align: center; color: #006633;">ACCUSÉ DE RÉCEPTION FINAL</h2>
        
        <div class="success-box">
            <div class="phase-title">WORKFLOW 2 PHASES TERMINÉ AVEC SUCCÈS</div>
            <p><strong>Organisation et adhérents créés</strong></p>
            <p>Votre dossier a été traité avec succès selon le nouveau workflow optimisé en 2 phases.</p>
        </div>
        
        <div class="info-box">
            <h3>Informations du dossier</h3>
            <p><strong>Numéro de dossier :</strong> ' . $data['dossier']->numero_dossier . '</p>
            <p><strong>Numéro de récépissé :</strong> ' . $data['numero_recepisse'] . '</p>
            <p><strong>Date de soumission :</strong> ' . $data['dossier']->submitted_at->format('d/m/Y à H:i') . '</p>
            <p><strong>Type d\'organisation :</strong> ' . ucfirst(str_replace('_', ' ', $data['organisation']->type)) . '</p>
            <p><strong>Workflow :</strong> 2 phases (Organisation + Adhérents séparés)</p>
        </div>
        
        <div class="info-box">
            <h3>Organisation créée</h3>
            <p><strong>Nom :</strong> ' . $data['organisation']->nom . '</p>
            <p><strong>Sigle :</strong> ' . ($data['organisation']->sigle ?? 'Non renseigné') . '</p>
            <p><strong>Province :</strong> ' . $data['organisation']->province . '</p>
            <p><strong>Statut :</strong> Dossier complet, en cours de traitement</p>
        </div>
        
        <div class="stats-box">
            <h3>STATISTIQUES DES ADHÉRENTS</h3>
            <p><strong>Total traité :</strong> ' . $stats['total'] . ' adhérents</p>
            <p><strong>Valides :</strong> ' . $stats['valides'] . ' adhérents</p>';
            
    if ($stats['anomalies_critiques'] > 0 || $stats['anomalies_majeures'] > 0 || $stats['anomalies_mineures'] > 0) {
        $html .= '<p><strong>Anomalies détectées :</strong></p>
            <ul>
                <li>Critiques : ' . $stats['anomalies_critiques'] . '</li>
                <li>Majeures : ' . $stats['anomalies_majeures'] . '</li>
                <li>Mineures : ' . $stats['anomalies_mineures'] . '</li>
            </ul>';
    } else {
        $html .= '<p><strong>✅ Aucune anomalie détectée</strong></p>';
    }
            
    $html .= '</div>';
        
    if (!empty($anomalies)) {
        $html .= '<div class="warning-box">
            <h3>⚠️ ANOMALIES À CORRIGER</h3>
            <p>Certains adhérents présentent des anomalies qui devront être corrigées :</p>
            <ul>';
        
        foreach (array_slice($anomalies, 0, 5) as $anomalie) {
            $html .= '<li>' . $anomalie['nom_complet'] . ' (NIP: ' . $anomalie['nip'] . ')</li>';
        }
        
        if (count($anomalies) > 5) {
            $html .= '<li>... et ' . (count($anomalies) - 5) . ' autres</li>';
        }
        
        $html .= '</ul>
            <p><em>Détails complets disponibles dans votre espace opérateur.</em></p>
        </div>';
    }
        
    $html .= '<div class="info-box">
            <h3>Traitement et validation</h3>
            <p>1. ✅ Phase 1 : Organisation créée avec succès</p>
            <p>2. ✅ Phase 2 : Adhérents importés avec succès</p>
            <p>3. 🔄 Votre dossier sera examiné dans l\'ordre d\'arrivée (système FIFO)</p>
            <p>4. 📧 Vous serez notifié par email des étapes suivantes</p>
            <p>5. ⏱️ Délai de traitement : 72 heures ouvrées</p>
        </div>
        
        <div class="success-box">
            <h3>🎉 FÉLICITATIONS</h3>
            <p>Votre dossier de création d\'organisation est maintenant <strong>complet</strong> et prêt pour le traitement administratif.</p>
            <p>Le nouveau système 2 phases a permis de traiter votre demande de manière optimisée.</p>
        </div>
    </div>
    
    <div class="footer">
        <p>Document généré automatiquement le ' . $data['date_generation']->format('d/m/Y à H:i') . '</p>
        <p>Plateforme Numérique Gabonaise de Déclaration des Intentions (PNGDI)</p>
        <p><strong>Workflow 2 phases - Version optimisée</strong></p>
    </div>
</body>
</html>';

    return $html;
}

    /**
     * NOUVELLE MÉTHODE : HTML pour accusé Phase 1
     */
    private function generateAccuseReceptionPhase1HTML($data)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accusé de Réception Phase 1 - ' . $data['dossier']->numero_dossier . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #006633; padding-bottom: 20px; }
        .logo { color: #006633; font-size: 24px; font-weight: bold; }
        .title { color: #FFA500; font-size: 18px; margin-top: 10px; }
        .content { margin-top: 30px; }
        .info-box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
        .phase-box { background: #e8f5e8; border: 2px solid #28a745; padding: 15px; margin: 20px 0; }
        .phase-title { color: #28a745; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .next-steps { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">RÉPUBLIQUE GABONAISE</div>
        <div>Union - Travail - Justice</div>
        <div class="title">MINISTÈRE DE L\'INTÉRIEUR</div>
        <div>Direction des Organisations</div>
    </div>

    <div class="content">
        <h2 style="text-align: center; color: #006633;">ACCUSÉ DE RÉCEPTION - PHASE 1</h2>
        
        <div class="phase-box">
            <div class="phase-title">PHASE 1 COMPLÉTÉE AVEC SUCCÈS</div>
            <p><strong>Organisation créée sans adhérents</strong></p>
            <p>Votre organisation a été enregistrée avec succès. Les adhérents pourront être ajoutés en Phase 2.</p>
        </div>
        
        <div class="info-box">
            <h3>Informations du dossier</h3>
            <p><strong>Numéro de dossier :</strong> ' . $data['dossier']->numero_dossier . '</p>
            <p><strong>Numéro de récépissé :</strong> ' . $data['numero_recepisse'] . '</p>
            <p><strong>Date de soumission Phase 1 :</strong> ' . $data['dossier']->submitted_at->format('d/m/Y à H:i') . '</p>
            <p><strong>Type d\'organisation :</strong> ' . ucfirst(str_replace('_', ' ', $data['organisation']->type)) . '</p>
            <p><strong>Phase :</strong> 1/2 - Organisation créée SANS adhérents</p>
        </div>
        
        <div class="info-box">
            <h3>Organisation créée</h3>
            <p><strong>Nom :</strong> ' . $data['organisation']->nom . '</p>
            <p><strong>Sigle :</strong> ' . ($data['organisation']->sigle ?? 'Non renseigné') . '</p>
            <p><strong>Province :</strong> ' . $data['organisation']->province . '</p>
            <p><strong>Statut :</strong> Organisation créée, en attente des adhérents</p>
        </div>
        
        <div class="next-steps">
            <h3>PROCHAINES ÉTAPES - PHASE 2</h3>
            <p><strong>Étape suivante :</strong> Ajout des adhérents en Phase 2</p>
            <p><strong>Comment procéder :</strong></p>
            <ol>
                <li>Connectez-vous à la plateforme PNGDI</li>
                <li>Accédez au menu "Import des adhérents"</li>
                <li>Utilisez le numéro de dossier : <strong>' . $data['dossier']->numero_dossier . '</strong></li>
                <li>Téléchargez et complétez la liste des adhérents</li>
                <li>Soumettez les adhérents pour validation</li>
            </ol>
            <p><strong>Important :</strong> Votre dossier restera en attente tant que les adhérents ne seront pas ajoutés.</p>
        </div>
        
        <div class="info-box">
            <h3>Traitement et validation</h3>
            <p>1. Votre dossier Phase 1 sera examiné dans l\'ordre d\'arrivée (système FIFO)</p>
            <p>2. Un agent sera assigné sous 48h ouvrées</p>
            <p>3. Vous serez notifié par email des étapes suivantes</p>
            <p>4. Délai de traitement complet : 72 heures ouvrées après ajout des adhérents</p>
        </div>
    </div>
    
    <div class="footer">
        <p>Document généré automatiquement le ' . $data['date_generation']->format('d/m/Y à H:i') . '</p>
        <p>Plateforme Numérique Gabonaise de Déclaration des Intentions (PNGDI)</p>
        <p><strong>Phase 1 complétée - Phase 2 en attente</strong></p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * NOUVELLE MÉTHODE : Afficher l'interface d'import des adhérents (PHASE 2)
     * Page dédiée pour l'ajout des adhérents sur un dossier existant
     * 
     * GET /operator/organisations/{dossier}/adherents-import
     */
    public function adherentsImportPage($dossierId)
    {
        try {
            // RÉCUPÉRER ET VALIDER LE DOSSIER EXISTANT
            $dossier = Dossier::with(['organisation', 'documents'])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossier) {
                \Log::error('Dossier non trouvé pour page import adhérents', [
                    'dossier_id' => $dossierId,
                    'user_id' => auth()->id()
                ]);
                
                return redirect()->route('operator.organisations.index')
                    ->with('error', 'Dossier non trouvé ou accès non autorisé.');
            }

            $organisation = $dossier->organisation;

            // VÉRIFIER QUE C'EST BIEN UN DOSSIER PHASE 1 COMPLÉTÉ
            $donneesSupplementaires = [];
            if (!empty($dossier->donnees_supplementaires)) {
                if (is_string($dossier->donnees_supplementaires)) {
                    $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true) ?? [];
                } elseif (is_array($dossier->donnees_supplementaires)) {
                    $donneesSupplementaires = $dossier->donnees_supplementaires;
                }
            }

            $isPhase1Completed = isset($donneesSupplementaires['phase_creation']) && 
                                 $donneesSupplementaires['phase_creation'] === '1_sans_adherents';

            if (!$isPhase1Completed) {
                \Log::warning('Tentative d\'accès à la Phase 2 sans Phase 1 complétée', [
                    'dossier_id' => $dossierId,
                    'user_id' => auth()->id(),
                    'phase_creation' => $donneesSupplementaires['phase_creation'] ?? 'non_définie'
                ]);
                
                return redirect()->route('operator.organisations.show', $organisation->id)
                    ->with('warning', 'Ce dossier n\'est pas en attente d\'import d\'adhérents.');
            }

            // CALCULER LES STATISTIQUES ACTUELLES
            $adherentsExistants = $organisation->adherents()->count();
            $minAdherents = $this->getMinAdherents($organisation->type);
            $adherentsManquants = max(0, $minAdherents - $adherentsExistants);

            // PRÉPARER LES DONNÉES POUR LA VUE
            $viewData = [
                'dossier' => $dossier,
                'organisation' => $organisation,
                'phase1_data' => $donneesSupplementaires,
                'adherents_stats' => [
                    'existants' => $adherentsExistants,
                    'minimum_requis' => $minAdherents,
                    'manquants' => $adherentsManquants,
                    'peut_soumettre' => $adherentsExistants >= $minAdherents
                ],
                'upload_config' => [
                    'max_file_size' => '10MB',
                    'accepted_formats' => ['xlsx', 'csv'],
                    'chunk_size' => 100,
                    'max_adherents' => 10000
                ],
                'urls' => [
                    'store_adherents' => route('operator.organisations.store-adherents', $dossier->id),
                    'template_download' => route('operator.members.import.template'),
                    'confirmation' => route('operator.dossiers.confirmation', $dossier->id)
                ]
            ];

            \Log::info('Page import adhérents affichée', [
                'dossier_id' => $dossier->id,
                'organisation_id' => $organisation->id,
                'user_id' => auth()->id(),
                'adherents_stats' => $viewData['adherents_stats']
            ]);

            // RETOURNER LA VUE DÉDIÉE PHASE 2
            return view('operator.dossiers.adherents-import', $viewData);

        } catch (\Exception $e) {
            \Log::error('Erreur affichage page import adhérents: ' . $e->getMessage(), [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id(),
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->route('operator.organisations.index')
                ->with('error', 'Erreur lors de l\'affichage de la page d\'import des adhérents.');
        }
    }


    /**
     * Afficher les détails d'une organisation
     */
    public function show(Organisation $organisation)
    {
        $this->authorize('view', $organisation);

        $organisation->load([
            'dossier.validations.entity',
            'adherents',
            'fondateurs',
            'etablissements',
            'documents'
        ]);

        return view('operator.organisations.show', compact('organisation'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Organisation $organisation)
    {
        $this->authorize('update', $organisation);

        if (in_array($organisation->statut, ['soumis', 'en_cours', 'approuve'])) {
            return redirect()->route('operator.organisations.show', $organisation)
                ->with('warning', 'Cette organisation ne peut plus être modifiée car elle est en cours de traitement.');
        }

        $provinces = $this->getProvinces();
        $documentTypes = $this->getRequiredDocuments($organisation->type);

        return view('operator.organisations.edit', compact(
            'organisation', 
            'provinces', 
            'documentTypes'
        ));
    }

    /**
     * Mettre à jour une organisation
     */
    public function update(Request $request, Organisation $organisation)
    {
        $this->authorize('update', $organisation);

        if (in_array($organisation->statut, ['soumis', 'en_cours', 'approuve'])) {
            return redirect()->route('operator.organisations.show', $organisation)
                ->with('error', 'Cette organisation ne peut plus être modifiée.');
        }

        $validatedData = $this->validateOrganisationData($request, $organisation->type);

        try {
            $organisation->update($validatedData);

            return redirect()->route('operator.organisations.show', $organisation)
                ->with('success', 'Organisation mise à jour avec succès.');

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour organisation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour')
                ->withInput();
        }
    }

    /**
     * Valider une organisation (méthode renommée pour éviter le conflit)
     */
    public function validateOrganisation(Organisation $organisation)
    {
        $this->authorize('validate', $organisation);

        try {
            $validation = $this->organisationValidationService->validateOrganisation($organisation);

            if ($validation['success']) {
                return redirect()->route('operator.organisations.show', $organisation)
                    ->with('success', 'Organisation validée avec succès.');
            } else {
                return redirect()->route('operator.organisations.show', $organisation)
                    ->with('error', 'Validation échouée: ' . $validation['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur validation organisation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la validation');
        }
    }

    /**
     * Soumettre une organisation pour traitement
     */
    public function submit(Organisation $organisation)
    {
        $this->authorize('submit', $organisation);

        if ($organisation->statut !== 'brouillon') {
            return redirect()->route('operator.organisations.show', $organisation)
                ->with('error', 'Cette organisation a déjà été soumise.');
        }

        try {
            \DB::beginTransaction();

            $missingDocuments = $this->checkRequiredDocuments($organisation);
            if (!empty($missingDocuments)) {
                return redirect()->route('operator.organisations.edit', $organisation)
                    ->with('error', 'Documents manquants: ' . implode(', ', $missingDocuments));
            }

            $organisation->update(['statut' => 'soumis']);
            $organisation->dossier->update(['statut' => 'soumis']);

            $this->workflowService->startWorkflow($organisation->dossier);

            \DB::commit();

            return redirect()->route('operator.organisations.show', $organisation)
                ->with('success', 'Organisation soumise avec succès. Elle sera traitée selon l\'ordre d\'arrivée.');

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Erreur soumission organisation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la soumission');
        }
    }

    /**
     * Vérifier si des NIP sont déjà membres actifs d'autres organisations
     */
    public function checkExistingMembers(Request $request)
    {
        $nips = $request->input('nips', []);
        
        if (empty($nips)) {
            return response()->json(['existing_nips' => []]);
        }
        
        $existingNips = \App\Models\Adherent::whereIn('nip', $nips)
            ->where('is_active', true)
            ->pluck('nip')
            ->unique()
            ->values()
            ->toArray();
        
        return response()->json([
            'existing_nips' => $existingNips,
            'count' => count($existingNips)
        ]);
    }

    /**
     * Afficher la page de confirmation après soumission d'organisation
     */
    public function confirmation($dossierId)
    {
        try {
            $dossier = Dossier::with([
                'organisation',
                'documents'
            ])->findOrFail($dossierId);

            // Vérifier l'accès
            if ($dossier->organisation->user_id !== auth()->id()) {
                abort(403, 'Accès non autorisé à ce dossier.');
            }

            // Vérifier que le dossier vient d'être soumis (dans les dernières 24h)
            if ($dossier->submitted_at->diffInHours(now()) > 24) {
                return redirect()->route('operator.dashboard')
                    ->with('warning', 'Cette page de confirmation n\'est plus disponible.');
            }

            $sessionData = session('success_data');
            
            if (!$sessionData) {
                $sessionData = $this->reconstructConfirmationData($dossier);
            }

            $confirmationData = [
                'organisation' => $dossier->organisation,
                'dossier' => $dossier,
                'numero_recepisse' => $dossier->organisation->numero_recepisse,
                'adherents_stats' => $sessionData['adherents_stats'] ?? $this->calculateAdherentsStats($dossier),
                'accuse_reception_path' => $this->getAccuseReceptionPath($dossier),
                'delai_traitement' => '72 heures ouvrées',
                // MESSAGE CONFORME À LA LOI N° 016/2025 du 27 Juin 2025
                'message_confirmation' => 'Votre dossier numérique a été soumis avec succès. Aux fins de recevoir votre accusé de réception, conformément aux dispositions de l\'article 26 de la loi N° 016/2025 du 27 Juin 2025 relative aux partis politiques en République Gabonaise, vous êtes invité à déposer votre dossier physique, en 3 exemplaires, auprès des services de la Direction Générale des Élections et des Libertés Publiques du Ministère de l\'Intérieur, de la Sécurité et de la Décentralisation, en application des dispositions de l\'article 24 de la loi suscitée.',
                'message_legal' => [
                    'loi_reference' => 'Loi N° 016/2025 du 27 Juin 2025',
                    'article_reference' => 'Articles 24 et 26',
                    'depot_requis' => 'Dossier physique en 3 exemplaires',
                    'service_depot' => 'Direction Générale des Élections et des Libertés Publiques',
                    'ministere' => 'Ministère de l\'Intérieur, de la Sécurité et de la Décentralisation'
                ]
            ];

            session()->forget('success_data');

            \Log::info('Page de confirmation consultée v3', [
                'user_id' => auth()->id(),
                'dossier_id' => $dossier->id,
                'organisation_nom' => $dossier->organisation->nom,
                'access_time' => now(),
                'numero_dossier' => $dossier->numero_dossier
            ]);

            return view('operator.dossiers.confirmation', compact('confirmationData'));

        } catch (\Exception $e) {
            \Log::error('Erreur affichage confirmation v3: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'dossier_id' => $dossierId,
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->route('operator.dashboard')
                ->with('error', 'Impossible d\'afficher la page de confirmation.');
        }
    }

    /**
     * Télécharger l'accusé de réception
     */
    public function downloadAccuse($path)
    {
        try {
            $filename = basename($path);
            $fullPath = storage_path('app/public/accuses_reception/' . $filename);
            
            if (!file_exists($fullPath)) {
                abort(404, 'Fichier non trouvé.');
            }
            
            $document = \App\Models\Document::where('nom_fichier', $filename)
                ->whereHas('dossier.organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();
            
            if (!$document) {
                abort(403, 'Accès non autorisé à ce document.');
            }
            
            \Log::info('Téléchargement accusé de réception v3', [
                'user_id' => auth()->id(),
                'document_id' => $document->id,
                'filename' => $filename,
                'download_time' => now()
            ]);
            
            return response()->download($fullPath, $filename, [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur téléchargement accusé v3: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'path' => $path,
                'error' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Impossible de télécharger le fichier.');
        }
    }

    // =============================================================================
    // MÉTHODES PRIVÉES CONSERVÉES ET COMPLÉTÉES
    // =============================================================================

    /**
     * Vérifier les limites d'organisations par opérateur
     */
    private function checkOrganisationLimits($type)
    {
        $userId = auth()->id();

        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $existingCount = Organisation::where('user_id', $userId)
                    ->where('type', 'parti_politique')
                    ->whereIn('statut', ['brouillon', 'soumis', 'en_cours', 'approuve', 'actif'])
                    ->count();

                if ($existingCount >= 1) {
                    return [
                        'success' => false,
                        'message' => 'Vous avez déjà un parti politique actif. Un opérateur ne peut créer qu\'un seul parti politique à la fois.'
                    ];
                }
                break;

            case 'confession':
            case 'confession_religieuse':
                $existingCount = Organisation::where('user_id', $userId)
                    ->where('type', 'confession_religieuse')
                    ->whereIn('statut', ['brouillon', 'soumis', 'en_cours', 'approuve', 'actif'])
                    ->count();

                if ($existingCount >= 1) {
                    return [
                        'success' => false,
                        'message' => 'Vous avez déjà une confession religieuse active. Un opérateur ne peut créer qu\'une seule confession religieuse à la fois.'
                    ];
                }
                break;

            case 'association':
            case 'ong':
                break;

            default:
                return [
                    'success' => false,
                    'message' => 'Type d\'organisation non reconnu.'
                ];
        }

        return ['success' => true];
    }

 /**
 * Validation complète des données - VERSION CONFORME À LA RÈGLE MÉTIER NIP
 * ✅ Enregistre TOUS les adhérents, même avec des NIP invalides
 * ✅ Marque les anomalies sans bloquer le processus
 */
private function validateCompleteOrganisationData(Request $request, $type)
{
    // Log des données reçues pour debugging
    \Log::info('Validation DB v5 - Règle métier NIP appliquée', [
        'keys' => array_keys($request->all()),
        'type' => $type,
        'regle_metier' => 'Enregistrement de tous les adhérents avec détection anomalies',
        'version' => 'conforme_PNGDI_v5'
    ]);

    $rules = [
        // ÉTAPE 1 : Type
        'type_organisation' => 'required|in:association,ong,parti_politique,confession_religieuse',

        // ÉTAPE 2 : Guide
        'guide_read_confirm' => 'sometimes|accepted',
        
        // ÉTAPE 3 : Demandeur - COLONNES CONFORMES À USERS TABLE
        'demandeur_nip' => 'required|digits:13',
        'demandeur_nom' => 'required|string|max:255',
        'demandeur_prenom' => 'required|string|max:255',
        'demandeur_email' => 'required|email|max:255',
        'demandeur_telephone' => 'required|string|max:20',
        'demandeur_role' => 'sometimes|string',
        'demandeur_civilite' => 'sometimes|in:M,Mme,Mlle',
        'demandeur_date_naissance' => 'sometimes|date|before:-18 years',
        'demandeur_nationalite' => 'sometimes|string|max:255',
        'demandeur_adresse' => 'sometimes|string|max:500',
        'demandeur_profession' => 'sometimes|string|max:255',
        
        // ÉTAPE 4 : Organisation - COLONNES CONFORMES À ORGANISATIONS TABLE
        'org_nom' => 'required|string|max:255|unique:organisations,nom',
        'org_sigle' => 'nullable|string|max:255|unique:organisations,sigle',
        'org_objet' => 'required|string|min:50',
        'org_date_creation' => 'required|date',
        'org_telephone' => 'required|string|max:255',
        'org_email' => 'nullable|email|max:255',
        'org_site_web' => 'nullable|url|max:255',
        'org_domaine' => 'sometimes|string|max:255',
        
        // ÉTAPE 5 : Coordonnées - COLONNES CONFORMES À ORGANISATIONS TABLE
        'org_adresse_complete' => 'required|string|max:255',
        'org_province' => 'required|string|max:255',
        'org_departement' => 'nullable|string|max:255',
        'org_prefecture' => 'required|string|max:255',
        'org_zone_type' => 'required|in:urbaine,rurale',
        'org_latitude' => 'nullable|numeric|between:-3.978,2.318',
        'org_longitude' => 'nullable|numeric|between:8.695,14.502',
        
        // ÉTAPE 6 : Fondateurs - VALIDATION AVEC RÈGLE MÉTIER APPLIQUÉE
        'fondateurs' => [
            'required',
            function ($attribute, $value, $fail) use ($type) {
                // Décoder JSON si c'est une string
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Les données des fondateurs sont invalides (JSON malformé): ' . json_last_error_msg());
                        return;
                    }
                    $value = $decoded;
                    request()->merge(['fondateurs' => $value]);
                }
                
                if (!is_array($value)) {
                    $fail('Les fondateurs doivent être un tableau.');
                    return;
                }
                
                $minRequired = $this->getMinFondateurs($type);
                if (count($value) < $minRequired) {
                    $fail("Minimum {$minRequired} fondateurs requis pour ce type d'organisation.");
                }
                
                // ✅ VALIDATION SOUPLE POUR FONDATEURS - CONFORME RÈGLE MÉTIER
                foreach ($value as $index => $fondateur) {
                    if (!is_array($fondateur)) {
                        $fail("Le fondateur ligne " . ($index + 1) . " doit être un objet valide.");
                        continue;
                    }
                    
                    // ✅ NIP : VALIDATION NON-BLOQUANTE
                    // Les anomalies NIP seront détectées lors de la création, pas ici
                    if (empty($fondateur['nip'])) {
                        $fail("Le NIP du fondateur ligne " . ($index + 1) . " ne peut pas être vide.");
                    }
                    
                    // Autres validations obligatoires
                    if (empty($fondateur['nom']) || empty($fondateur['prenom'])) {
                        $fail("Le nom et prénom du fondateur ligne " . ($index + 1) . " sont obligatoires.");
                    }
                    if (empty($fondateur['fonction'])) {
                        $fail("La fonction du fondateur ligne " . ($index + 1) . " est obligatoire.");
                    }
                    if (empty($fondateur['telephone'])) {
                        $fail("Le téléphone du fondateur ligne " . ($index + 1) . " est obligatoire.");
                    }
                }
            }
        ],
        
        // ÉTAPE 7 : Adhérents - VALIDATION CONFORME À LA RÈGLE MÉTIER NIP
        'adherents' => [
            'required',
            function ($attribute, $value, $fail) use ($type) {
                // Décoder JSON si c'est une string
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Les données des adhérents sont invalides (JSON malformé): ' . json_last_error_msg());
                        return;
                    }
                    $value = $decoded;
                    request()->merge(['adherents' => $value]);
                }
                
                if (!is_array($value)) {
                    $fail('Les adhérents doivent être un tableau.');
                    return;
                }
                
                $minRequired = $this->getMinAdherents($type);
                if (count($value) < $minRequired) {
                    $fail("Minimum {$minRequired} adhérents requis pour ce type d'organisation.");
                }
                
                // ✅ VALIDATION CONFORME RÈGLE MÉTIER : PAS DE BLOCAGE POUR NIP
                foreach ($value as $index => $adherent) {
                    if (!is_array($adherent)) {
                        $fail("L'adhérent ligne " . ($index + 1) . " doit être un objet valide.");
                        continue;
                    }
                    
                    // ✅ NIP : VALIDATION NON-BLOQUANTE SELON RÈGLE MÉTIER
                    // Seule vérification : ne peut pas être complètement vide
                    if (empty($adherent['nip']) || trim($adherent['nip']) === '') {
                        $fail("Le NIP de l'adhérent ligne " . ($index + 1) . " ne peut pas être vide.");
                    }
                    // ✅ Les anomalies de format (13 chiffres, doublons, etc.) seront détectées 
                    // lors de la création et marquées comme anomalies sans bloquer
                    
                    // Nom et prénom obligatoires
                    if (empty($adherent['nom']) || empty($adherent['prenom'])) {
                        $fail("Le nom et prénom de l'adhérent ligne " . ($index + 1) . " sont obligatoires.");
                    }
                    
                    // Profession obligatoire
                    if (empty($adherent['profession'])) {
                        $fail("La profession de l'adhérent ligne " . ($index + 1) . " est obligatoire.");
                    }
                    
                    // ✅ PROFESSIONS EXCLUES : TRAITEMENT COMME ANOMALIE CRITIQUE NON-BLOQUANTE
                    // Conforme à la règle métier PNGDI : enregistrer avec anomalie critique
                    // La vérification des professions exclues sera faite lors de la détection d'anomalies
                }
            }
        ],
        
        // ÉTAPE 9 : Déclarations finales
        'declaration_veracite' => 'sometimes|accepted',
        'declaration_conformite' => 'sometimes|accepted',
        'declaration_autorisation' => 'sometimes|accepted'
    ];

    // Règles spécifiques pour parti politique
    if ($type === 'parti_politique') {
        $rules['declaration_exclusivite_parti'] = 'required|accepted';
        $rules['adherents'][] = function ($attribute, $value, $fail) {
            if (is_array($value) && count($value) < 50) {
                $fail("Un parti politique doit avoir au minimum 50 adhérents.");
            }
        };
    }

    $messages = [
        'demandeur_nip.digits' => 'Le NIP du demandeur doit contenir exactement 13 chiffres.',
        'demandeur_nip.required' => 'Le NIP du demandeur est obligatoire.',
        'org_nom.unique' => 'Ce nom d\'organisation est déjà utilisé.',
        'org_sigle.unique' => 'Ce sigle est déjà utilisé.',
        'org_objet.min' => 'L\'objet de l\'organisation doit contenir au moins 50 caractères.',
        'org_objet.required' => 'L\'objet de l\'organisation est obligatoire.',
        'declaration_exclusivite_parti.required' => 'La déclaration d\'exclusivité pour parti politique est obligatoire.',
        'declaration_exclusivite_parti.accepted' => 'Vous devez accepter la déclaration d\'exclusivité.',
        '*.accepted' => 'Cette déclaration est obligatoire.',
        '*.required' => 'Ce champ est obligatoire.'
    ];

    try {
        $validated = $request->validate($rules, $messages);
        
        // Post-traitement avec nettoyage des données
        if (isset($validated['fondateurs'])) {
            if (is_string($validated['fondateurs'])) {
                $decoded = json_decode($validated['fondateurs'], true);
                $validated['fondateurs'] = $decoded ?? [];
            }
            if (!is_array($validated['fondateurs'])) {
                $validated['fondateurs'] = [];
            }
            
            // ✅ NETTOYER LES NIP DES FONDATEURS
            foreach ($validated['fondateurs'] as &$fondateur) {
                if (isset($fondateur['nip'])) {
                    $fondateur['nip'] = $this->cleanNipForStorage($fondateur['nip']);
                }
            }
        }
        
        if (isset($validated['adherents'])) {
            if (is_string($validated['adherents'])) {
                $decoded = json_decode($validated['adherents'], true);
                $validated['adherents'] = $decoded ?? [];
            }
            if (!is_array($validated['adherents'])) {
                $validated['adherents'] = [];
            }
            
            // ✅ NETTOYER LES NIP DES ADHÉRENTS
            foreach ($validated['adherents'] as &$adherent) {
                if (isset($adherent['nip'])) {
                    $adherent['nip'] = $this->cleanNipForStorage($adherent['nip']);
                }
                
                // Assurer la fonction par défaut
                if (empty($adherent['fonction'])) {
                    $adherent['fonction'] = 'Membre';
                }
            }
        }
        
        // Ajouter des valeurs par défaut
        $validated['org_departement'] = $request->input('org_departement');
        $validated['declaration_veracite'] = $request->has('declaration_veracite');
        $validated['declaration_conformite'] = $request->has('declaration_conformite');
        $validated['declaration_autorisation'] = $request->has('declaration_autorisation');
        $validated['guide_read_confirm'] = $request->has('guide_read_confirm');
        
        \Log::info('Validation v5 réussie - Règle métier NIP appliquée', [
            'fondateurs_count' => count($validated['fondateurs'] ?? []),
            'adherents_count' => count($validated['adherents'] ?? []),
            'type' => $type,
            'validation_version' => 'conforme_regle_metier_PNGDI_v5',
            'nip_validation' => 'non_bloquante_avec_detection_anomalies'
        ]);
        
        return $validated;
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Erreur validation v5 avec règle métier', [
            'errors' => $e->errors(),
            'type' => $type,
            'validation_version' => 'conforme_regle_metier_PNGDI_v5'
        ]);
        
        throw $e;
    }
}


/**
 * ✅ NOUVELLE MÉTHODE : Nettoyer un NIP pour stockage
 * Conforme à la règle métier PNGDI
 */
private function cleanNipForStorage($nip)
{
    if (empty($nip)) {
        return '';
    }
    
    // Supprimer espaces, tirets et caractères non-numériques
    $cleaned = preg_replace('/[^0-9]/', '', $nip);
    
    // Log du nettoyage pour traçabilité
    if ($cleaned !== $nip) {
        \Log::info('NIP nettoyé pour stockage', [
            'original' => $nip,
            'cleaned' => $cleaned
        ]);
    }
    
    return $cleaned;
}

/**
 * ✅ MÉTHODE MISE À JOUR : Créer les adhérents avec détection d'anomalies NIP
 * Conforme à la règle métier PNGDI
 */
private function createAdherents(Organisation $organisation, array $adherentsData)
{
    $stats = [
        'total' => count($adherentsData),
        'valides' => 0,
        'anomalies_critiques' => 0,
        'anomalies_majeures' => 0,
        'anomalies_mineures' => 0
    ];

    $anomalies = [];
    $adherentsCreated = [];

    foreach ($adherentsData as $index => $adherentData) {
        // ✅ DÉTECTER LES ANOMALIES NIP SELON LA RÈGLE MÉTIER
        $anomaliesDetectees = $this->detectAndManageNipAnomalies($adherentData, $organisation->type, $organisation->id);

        // Historique conforme à la règle métier
        $historiqueData = [
            'creation' => now()->toISOString(),
            'anomalies_detectees' => $anomaliesDetectees,
            'source' => 'creation_organisation',
            'regle_metier' => 'enregistrement_avec_anomalies_PNGDI',
            'profession_originale' => $adherentData['profession'] ?? null,
            'fonction_originale' => $adherentData['fonction'] ?? 'Membre'
        ];

        // ✅ ENREGISTRER L'ADHÉRENT MÊME AVEC ANOMALIES NIP
        $adherentDataCleaned = [
            'organisation_id' => $organisation->id,
            'nip' => $adherentData['nip'], // NIP tel que fourni
            'nom' => strtoupper($adherentData['nom']),
            'prenom' => $adherentData['prenom'],
            'profession' => $adherentData['profession'] ?? null,
            'fonction' => $adherentData['fonction'] ?? 'Membre',
            'telephone' => $adherentData['telephone'] ?? null,
            'email' => $adherentData['email'] ?? null,
            'date_adhesion' => now(),
            
            // ✅ MARQUER LES ANOMALIES SELON LA RÈGLE MÉTIER
            'has_anomalies' => !empty($anomaliesDetectees),
            'anomalies_data' => json_encode($anomaliesDetectees, JSON_UNESCAPED_UNICODE),
            'anomalies_severity' => $this->resolveSeverity($anomaliesDetectees),
            
            // ✅ RESTE ACTIF MÊME AVEC ANOMALIES (sauf critiques)
            'is_active' => empty($anomaliesDetectees['critiques']),
            
            'historique' => json_encode($historiqueData, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now()
        ];

        $adherent = \App\Models\Adherent::create($adherentDataCleaned);
        $adherentsCreated[] = $adherent;

        // Comptabiliser selon les anomalies détectées
        if (empty($anomaliesDetectees)) {
            $stats['valides']++;
        } else {
            if (!empty($anomaliesDetectees['critiques'])) {
                $stats['anomalies_critiques']++;
            }
            if (!empty($anomaliesDetectees['majeures'])) {
                $stats['anomalies_majeures']++;
            }
            if (!empty($anomaliesDetectees['mineures'])) {
                $stats['anomalies_mineures']++;
            }

            $anomalies[] = [
                'adherent_id' => $adherent->id,
                'ligne' => $index + 1,
                'nip' => $adherentData['nip'],
                'nom_complet' => $adherentData['nom'] . ' ' . $adherentData['prenom'],
                'profession' => $adherentData['profession'] ?? null,
                'fonction' => $adherentData['fonction'] ?? 'Membre',
                'anomalies' => $anomaliesDetectees,
                'severity' => $this->resolveSeverity($anomaliesDetectees)
            ];
        }
    }

    \Log::info('Adhérents créés avec règle métier NIP', [
        'total_crees' => count($adherentsCreated),
        'stats' => $stats,
        'anomalies_count' => count($anomalies),
        'regle_metier' => 'PNGDI_enregistrement_avec_anomalies'
    ]);

    return [
        'adherents' => $adherentsCreated,
        'stats' => $stats,
        'anomalies' => $anomalies
    ];
}

/**
 * ✅ MÉTHODE MISE À JOUR : Détecter les anomalies selon la règle métier PNGDI
 * Inclut maintenant les professions exclues comme anomalie critique
 */
private function detectAndManageNipAnomalies(array $adherentData, string $typeOrganisation, int $organisationId)
{
    $anomalies = [
        'critiques' => [],
        'majeures' => [],
        'mineures' => []
    ];

    $nip = $adherentData['nip'] ?? '';
    $profession = $adherentData['profession'] ?? '';

    // ✅ ANOMALIE : FORMAT NIP INCORRECT
    if (!preg_match('/^[0-9]{13}$/', $nip)) {
        $anomalies['majeures'][] = [
            'code' => 'NIP_INVALID',
            'message' => 'Le NIP doit contenir exactement 13 chiffres.',
            'nip_fourni' => $nip,
            'longueur_actuelle' => strlen($nip)
        ];
    }

    // ✅ ANOMALIE : NIP DÉJÀ DANS UN AUTRE PARTI POLITIQUE
    if ($typeOrganisation === 'parti_politique') {
        $existingInOtherParty = \App\Models\Adherent::whereHas('organisation', function($query) use ($organisationId) {
            $query->where('type', 'parti_politique')
                  ->where('id', '!=', $organisationId);
        })->where('nip', $nip)->exists();

        if ($existingInOtherParty) {
            $anomalies['critiques'][] = [
                'code' => 'NIP_DUPLICATE_OTHER_PARTY',
                'message' => 'Ce NIP appartient déjà à un autre parti politique.',
                'nip' => $nip
            ];
        }
    }

    // ✅ ANOMALIE CRITIQUE : PROFESSION EXCLUE POUR PARTI POLITIQUE
    if ($typeOrganisation === 'parti_politique' && !empty($profession)) {
        $professionsExclues = $this->getProfessionsExcluesParti();
        if (in_array(strtolower($profession), array_map('strtolower', $professionsExclues))) {
            $anomalies['critiques'][] = [
                'code' => 'PROFESSION_EXCLUE_PARTI',
                'message' => 'Profession exclue pour les partis politiques: ' . $profession,
                'profession_fournie' => $profession,
                'type_organisation' => $typeOrganisation,
                'regle_legale' => 'Article 15 - Loi N° 016/2025'
            ];
        }
    }

    // ✅ ANOMALIE : DOUBLON DANS LA MÊME ORGANISATION
    $existingInSameOrg = \App\Models\Adherent::where('organisation_id', $organisationId)
        ->where('nip', $nip)
        ->exists();

    if ($existingInSameOrg) {
        $anomalies['majeures'][] = [
            'code' => 'NIP_DUPLICATE_SAME_ORG',
            'message' => 'Ce NIP apparaît plusieurs fois dans cette organisation.',
            'nip' => $nip
        ];
    }

    // ✅ ANOMALIE MINEURE : INFORMATIONS DE CONTACT MANQUANTES
    if (empty($adherentData['telephone']) && empty($adherentData['email'])) {
        $anomalies['mineures'][] = [
            'code' => 'CONTACT_INCOMPLET',
            'message' => 'Aucun moyen de contact fourni (téléphone ou email).',
            'telephone' => $adherentData['telephone'] ?? null,
            'email' => $adherentData['email'] ?? null
        ];
    }

    return $anomalies;
}

/**
 * ✅ NOUVELLE MÉTHODE : Résoudre la sévérité des anomalies
 */
private function resolveSeverity(array $anomalies)
{
    if (!empty($anomalies['critiques'])) {
        return 'critique';
    }
    if (!empty($anomalies['majeures'])) {
        return 'majeure';
    }
    if (!empty($anomalies['mineures'])) {
        return 'mineure';
    }
    return null;
}

    /**
     * Méthode d'aide pour nettoyer les données JSON
     */
    private function sanitizeJsonData($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            return $data; // Retourner la string si le décodage échoue
        }
        
        if (is_array($data)) {
            // Nettoyer récursivement les tableaux
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = $this->sanitizeJsonData($value);
            }
            return $cleaned;
        }
        
        return $data;
    }

    /**
     * Valider les données d'organisation (méthode existante simplifiée)
     */
    private function validateOrganisationData(Request $request, $type)
    {
        $rules = [
            'nom_organisation' => 'required|string|max:255',
            'sigle' => 'nullable|string|max:50',
            'description' => 'required|string|min:50',
            'adresse_siege' => 'required|string|max:255',
            'commune_siege' => 'required|string|max:100',
            'province_siege' => 'required|string|max:100',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'site_web' => 'nullable|url|max:255',
            'objectifs' => 'required|string|min:100',
            'zone_intervention' => 'required|array|min:1',
            'zone_intervention.*' => 'string|in:' . implode(',', array_keys($this->getProvinces())),
        ];

        // Règles spécifiques selon le type
        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $rules['ideologie_politique'] = 'required|string|max:255';
                $rules['programme_politique'] = 'required|string|min:200';
                break;

            case 'confession':
            case 'confession_religieuse':
                $rules['doctrine_religieuse'] = 'required|string|max:255';
                $rules['denomination'] = 'required|string|max:100';
                break;

            case 'association':
                $rules['domaine_activite'] = 'required|string|max:255';
                break;

            case 'ong':
                $rules['domaine_activite'] = 'required|string|max:255';
                $rules['mission_sociale'] = 'required|string|min:100';
                break;
        }

        return $request->validate($rules);
    }

    /**
     * Générer un numéro de dossier unique
     */
    private function generateDossierNumber($type)
    {
        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $prefix = 'PP';
                break;
            case 'confession':
            case 'confession_religieuse':
                $prefix = 'CR';
                break;
            case 'association':
                $prefix = 'AS';
                break;
            case 'ong':
                $prefix = 'ONG';
                break;
            default:
                $prefix = 'ONG';
        }

        $year = date('Y');
        $sequence = Dossier::where('numero_dossier', 'LIKE', $prefix . $year . '%')->count() + 1;

        return $prefix . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtenir le nombre minimum de fondateurs requis
     */
    private function getMinFondateurs($type)
    {
        switch ($type) {
            case 'parti_politique':
                return 3;
            case 'association':
                return 2;
            case 'confession_religieuse':
                return 2;
            case 'ong':
                return 2;
            default:
                return 2;
        }
    }

    /**
     * Obtenir le nombre minimum d'adhérents requis
     */
    private function getMinAdherents($type)
    {
        switch ($type) {
            case 'parti_politique':
                return 50;
            case 'association':
                return 7;
            case 'confession_religieuse':
                return 100;
            case 'ong':
                return 10;
            default:
                return 7;
        }
    }

    /**
     * Créer les fondateurs de l'organisation
     */
    private function createFondateurs(Organisation $organisation, array $fondateursData)
    {
        foreach ($fondateursData as $index => $fondateurData) {
            \App\Models\Fondateur::create([
                'organisation_id' => $organisation->id,
                'nip' => $fondateurData['nip'],
                'nom' => strtoupper($fondateurData['nom']),
                'prenom' => $fondateurData['prenom'],
                'fonction' => $fondateurData['fonction'],
                'telephone' => $fondateurData['telephone'],
                'email' => $fondateurData['email'] ?? null,
                'ordre' => $index + 1,
                
                // Colonnes supplémentaires de la table fondateurs si disponibles
                'date_naissance' => $fondateurData['date_naissance'] ?? null,
                'lieu_naissance' => $fondateurData['lieu_naissance'] ?? null,
                'sexe' => $fondateurData['sexe'] ?? null,
                'nationalite' => $fondateurData['nationalite'] ?? 'Gabonaise',
                'adresse_complete' => $fondateurData['adresse'] ?? null,
                'province' => $fondateurData['province'] ?? null,
                'departement' => $fondateurData['departement'] ?? null,
                'prefecture' => $fondateurData['prefecture'] ?? null,
                'zone_type' => $fondateurData['zone_type'] ?? 'urbaine',
                
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Détecter les anomalies d'un adhérent
     */
    private function detectAnomaliesAdherent(array $adherentData, string $typeOrganisation)
    {
        $anomalies = [
            'critiques' => [],
            'majeures' => [],
            'mineures' => []
        ];

        $nip = $adherentData['nip'];

        // Anomalies critiques
        if (!preg_match('/^[0-9]{13}$/', $nip)) {
            $anomalies['critiques'][] = [
                'code' => 'nip_invalide',
                'message' => 'Format NIP incorrect (doit contenir 13 chiffres)',
                'recommandation' => 'Corriger le format du NIP'
            ];
        }

        // Vérifier si déjà membre actif ailleurs
        $existantAilleurs = \App\Models\Adherent::where('nip', $nip)
            ->where('is_active', true)
            ->with('organisation')
            ->first();

        if ($existantAilleurs) {
            if ($typeOrganisation === 'parti_politique') {
                $anomalies['critiques'][] = [
                    'code' => 'membre_existant_parti',
                    'message' => 'Déjà membre actif du parti: ' . $existantAilleurs->organisation->nom,
                    'recommandation' => 'Fournir justificatif de démission ou exclure de la liste'
                ];
            } else {
                $anomalies['majeures'][] = [
                    'code' => 'membre_existant',
                    'message' => 'Déjà membre de: ' . $existantAilleurs->organisation->nom,
                    'recommandation' => 'Vérifier la compatibilité des adhésions'
                ];
            }
        }

        // Vérifier professions exclues pour parti politique
        if ($typeOrganisation === 'parti_politique' && !empty($adherentData['profession'])) {
            $professionsExclues = $this->getProfessionsExcluesParti();
            if (in_array(strtolower($adherentData['profession']), array_map('strtolower', $professionsExclues))) {
                $anomalies['critiques'][] = [
                    'code' => 'profession_exclue_parti',
                    'message' => 'Profession exclue pour les partis politiques: ' . $adherentData['profession'],
                    'recommandation' => 'Exclure cette personne ou corriger la profession'
                ];
            }
        }

        // Anomalies majeures
        if (!empty($adherentData['telephone']) && !preg_match('/^[0-9]{8,9}$/', $adherentData['telephone'])) {
            $anomalies['majeures'][] = [
                'code' => 'telephone_invalide',
                'message' => 'Format de téléphone incorrect',
                'recommandation' => 'Utiliser le format gabonais (8-9 chiffres)'
            ];
        }

        if (!empty($adherentData['email']) && !filter_var($adherentData['email'], FILTER_VALIDATE_EMAIL)) {
            $anomalies['majeures'][] = [
                'code' => 'email_invalide',
                'message' => 'Format d\'email incorrect',
                'recommandation' => 'Corriger l\'adresse email'
            ];
        }

        // Anomalies mineures
        if (empty($adherentData['telephone']) && empty($adherentData['email'])) {
            $anomalies['mineures'][] = [
                'code' => 'contact_incomplet',
                'message' => 'Aucun moyen de contact fourni',
                'recommandation' => 'Ajouter téléphone ou email'
            ];
        }

        return $anomalies;
    }

    /**
     * Générer un numéro de récépissé unique
     */
    private function generateRecepisseNumber($type)
    {
        $prefixes = [
            'parti_politique' => 'PP',
            'association' => 'AS',
            'ong' => 'ONG',
            'confession_religieuse' => 'CR'
        ];
        
        $prefix = $prefixes[$type] ?? 'ORG';
        $year = date('Y');
        
        $count = Organisation::where('type', $type)
            ->where('numero_recepisse', 'LIKE', "REC-{$prefix}-{$year}-%")
            ->count();
        
        $sequence = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        
        return "REC-{$prefix}-{$year}-{$sequence}";
    }

    /**
     * Traiter les uploads de documents
     */
    private function handleDocumentUploads(Request $request, Dossier $dossier)
    {
        $uploadedFiles = [];

        foreach ($request->allFiles() as $fieldName => $files) {
            if (strpos($fieldName, 'document_') === 0) {
                $documentType = str_replace('document_', '', $fieldName);

                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    $timestamp = time();
                    $filename = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                    
                    $path = $file->storeAs('documents/organisations', $filename, 'public');
                    $hashFichier = hash_file('sha256', $file->getPathname());

                    \App\Models\Document::create([
                        'dossier_id' => $dossier->id,
                        'document_type_id' => $this->getDocumentTypeId($documentType),
                        'nom_fichier' => $filename,
                        'nom_original' => $file->getClientOriginalName(),
                        'chemin_fichier' => $path,
                        'type_mime' => $file->getMimeType(),
                        'taille' => $file->getSize(),
                        'hash_fichier' => $hashFichier,
                        'uploaded_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $uploadedFiles[] = [
                        'nom_fichier' => $filename,
                        'nom_original' => $file->getClientOriginalName(),
                        'chemin' => $path,
                        'type' => $documentType
                    ];
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Générer l'accusé de réception PDF
     */
    private function generateAccuseReception(Dossier $dossier, Organisation $organisation, $user)
    {
        try {
            $data = [
                'dossier' => $dossier,
                'organisation' => $organisation,
                'user' => $user,
                'date_generation' => now(),
                'numero_recepisse' => $organisation->numero_recepisse
            ];

            $filename = 'accuse_reception_' . $dossier->numero_dossier . '_' . time() . '.pdf';
            $storagePath = 'accuses_reception/' . $filename;
            $fullPath = storage_path('app/public/' . $storagePath);
            
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            $htmlContent = $this->generateAccuseReceptionHTML($data);
            file_put_contents($fullPath, $htmlContent);
            
            \App\Models\Document::create([
                'dossier_id' => $dossier->id,
                'document_type_id' => 99,
                'nom_fichier' => $filename,
                'nom_original' => 'Accusé de réception',
                'chemin_fichier' => $storagePath,
                'type_mime' => 'application/pdf',
                'taille' => strlen($htmlContent),
                'hash_fichier' => hash('sha256', $htmlContent),
                'is_system_generated' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return $storagePath;
            
        } catch (\Exception $e) {
            \Log::error('Erreur génération accusé de réception v3: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reconstituer les données de confirmation depuis la base de données
     */
    private function reconstructConfirmationData(Dossier $dossier)
    {
        $donneesSupplementaires = [];
        
        // Décoder les données JSON de manière sécurisée
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true) ?? [];
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
     * Calculer les statistiques des adhérents depuis la base
     */
    private function calculateAdherentsStats(Dossier $dossier)
    {
        $organisation = $dossier->organisation;
        
        $totalAdherents = $organisation->adherents()->count();
        $adherentsValides = $organisation->adherents()->where('is_active', true)->count();
        
        $donneesSupplementaires = [];
        
        // Décoder les données JSON de manière sécurisée
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true) ?? [];
            } elseif (is_array($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = $dossier->donnees_supplementaires;
            }
        }
        
        $anomalies = $donneesSupplementaires['adherents_anomalies'] ?? [];
        
        $anomaliesCritiques = 0;
        $anomaliesMajeures = 0;
        $anomaliesMineures = 0;
        
        foreach ($anomalies as $anomalie) {
            $anomaliesAdherent = $anomalie['anomalies'] ?? [];
            
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
        
        return [
            'total' => $totalAdherents,
            'valides' => $adherentsValides,
            'anomalies_critiques' => $anomaliesCritiques,
            'anomalies_majeures' => $anomaliesMajeures,
            'anomalies_mineures' => $anomaliesMineures
        ];
    }

    /**
     * Obtenir le chemin de l'accusé de réception
     */
    private function getAccuseReceptionPath(Dossier $dossier)
    {
        $accuseDocument = $dossier->documents()
            ->where('nom_fichier', 'LIKE', 'accuse_reception_%')
            ->latest()
            ->first();
        
        if ($accuseDocument) {
            return storage_path('app/public/' . $accuseDocument->chemin_fichier);
        }
        
        return null;
    }

    /**
     * Obtenir l'ID du type de document
     */
    private function getDocumentTypeId($documentType)
    {
        $documentTypeMapping = [
            'statuts' => 1,
            'pv_ag' => 2,
            'liste_fondateurs' => 3,
            'justificatif_siege' => 4,
            'programme_politique' => 5,
            'doctrine_religieuse' => 6,
            'cv_dirigeants' => 7,
            'budget_previsionnel' => 8
        ];
        
        return $documentTypeMapping[$documentType] ?? 1;
    }

    /**
     * Liste des professions exclues pour les partis politiques
     */
    private function getProfessionsExcluesParti()
    {
        return [
            'Magistrat', 'Juge', 'Procureur',
            'Commissaire de police', 'Officier de police judiciaire',
            'Militaire en activité', 'Gendarme en activité',
            'Fonctionnaire de la sécurité d\'État',
            'Agent des services de renseignement',
            'Diplomate en mission', 'Gouverneur de province',
            'Préfet', 'Sous-préfet', 'Maire en exercice',
            'Membre du Conseil constitutionnel',
            'Membre de la Cour de cassation',
            'Membre du Conseil d\'État',
            'Contrôleur général d\'État',
            'Inspecteur général d\'État',
            'Agent comptable de l\'État',
            'Trésorier payeur général',
            'Receveur des finances'
        ];
    }

    /**
     * Générer le contenu HTML de l'accusé de réception
     */
    private function generateAccuseReceptionHTML($data)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accusé de Réception - ' . $data['dossier']->numero_dossier . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #006633; padding-bottom: 20px; }
        .logo { color: #006633; font-size: 24px; font-weight: bold; }
        .title { color: #FFA500; font-size: 18px; margin-top: 10px; }
        .content { margin-top: 30px; }
        .info-box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">RÉPUBLIQUE GABONAISE</div>
        <div>Union - Travail - Justice</div>
        <div class="title">MINISTÈRE DE L\'INTÉRIEUR</div>
        <div>Direction des Organisations</div>
    </div>

    <div class="content">
        <h2 style="text-align: center; color: #006633;">ACCUSÉ DE RÉCEPTION</h2>
        
        <div class="info-box">
            <h3>Informations du dossier</h3>
            <p><strong>Numéro de dossier:</strong> ' . $data['dossier']->numero_dossier . '</p>
            <p><strong>Numéro de récépissé:</strong> ' . $data['numero_recepisse'] . '</p>
            <p><strong>Date de soumission:</strong> ' . $data['dossier']->submitted_at->format('d/m/Y à H:i') . '</p>
            <p><strong>Type d\'organisation:</strong> ' . ucfirst(str_replace('_', ' ', $data['organisation']->type)) . '</p>
        </div>
        
        <div class="info-box">
            <h3>Organisation</h3>
            <p><strong>Nom:</strong> ' . $data['organisation']->nom . '</p>
            <p><strong>Sigle:</strong> ' . ($data['organisation']->sigle ?? 'Non renseigné') . '</p>
            <p><strong>Province:</strong> ' . $data['organisation']->province . '</p>
        </div>
        
        <div class="info-box">
            <h3>Prochaines étapes</h3>
            <p>1. Votre dossier sera examiné dans l\'ordre d\'arrivée (système FIFO)</p>
            <p>2. Un agent sera assigné sous 48h ouvrées</p>
            <p>3. Vous serez notifié de l\'évolution par email</p>
            <p>4. Délai de traitement estimé: 72 heures ouvrées</p>
        </div>
    </div>
    
    <div class="footer">
        <p>Document généré automatiquement le ' . $data['date_generation']->format('d/m/Y à H:i') . '</p>
        <p>Plateforme Numérique Gabonaise de Déclaration des Intentions (PNGDI)</p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Obtenir le contenu du guide pour un type d'organisation
     */
    private function getGuideContent($type)
    {
        $guides = [
            'parti' => [
                'title' => 'Guide de création d\'un parti politique',
                'description' => 'Étapes nécessaires pour créer un parti politique au Gabon',
                'requirements' => [
                    'Minimum 50 adhérents fondateurs',
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
            'parti_politique' => [
                'title' => 'Guide de création d\'un parti politique',
                'description' => 'Étapes nécessaires pour créer un parti politique au Gabon',
                'requirements' => [
                    'Minimum 50 adhérents fondateurs',
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
            'confession' => [
                'title' => 'Guide de création d\'une confession religieuse',
                'description' => 'Procédure pour l\'enregistrement d\'une confession religieuse',
                'requirements' => [
                    'Minimum 100 fidèles fondateurs',
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
            'confession_religieuse' => [
                'title' => 'Guide de création d\'une confession religieuse',
                'description' => 'Procédure pour l\'enregistrement d\'une confession religieuse',
                'requirements' => [
                    'Minimum 100 fidèles fondateurs',
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
            'association' => [
                'title' => 'Guide de création d\'une association',
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
            'ong' => [
                'title' => 'Guide de création d\'une ONG',
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

        return $guides[$type] ?? $guides['association'];
    }

    /**
     * Obtenir les documents requis selon le type d'organisation
     */
    private function getRequiredDocuments($type)
    {
        $baseDocuments = [
            'statuts' => ['name' => 'Statuts', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true],
            'justificatif_siege' => ['name' => 'Justificatif siège social', 'required' => false],
        ];

        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $baseDocuments['programme_politique'] = ['name' => 'Programme politique', 'required' => true];
                $baseDocuments['cv_dirigeants'] = ['name' => 'CV des dirigeants', 'required' => true];
                break;

            case 'confession':
            case 'confession_religieuse':
                $baseDocuments['doctrine_religieuse'] = ['name' => 'Doctrine religieuse', 'required' => true];
                break;

            case 'ong':
                $baseDocuments['budget_previsionnel'] = ['name' => 'Budget prévisionnel', 'required' => true];
                $baseDocuments['cv_dirigeants'] = ['name' => 'CV des dirigeants', 'required' => true];
                break;
        }

        return $baseDocuments;
    }

    /**
     * Vérifier les documents requis
     */
    private function checkRequiredDocuments(Organisation $organisation)
    {
        $requiredDocs = $this->getRequiredDocuments($organisation->type);
        $uploadedDocs = $organisation->documents->pluck('type_document')->toArray();
        
        $missing = [];
        foreach ($requiredDocs as $key => $doc) {
            if ($doc['required'] && !in_array($key, $uploadedDocs)) {
                $missing[] = $doc['name'];
            }
        }

        return $missing;
    }

    /**
     * Obtenir la liste des provinces du Gabon
     */
    private function getProvinces()
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
}