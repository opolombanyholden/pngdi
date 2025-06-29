<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\Dossier;
use App\Models\User;
use App\Services\OrganisationValidationService;
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
     */
    public function create(Request $request, $type = null)
    {
        // Vérifier les limites d'organisations
        $canCreate = $this->checkOrganisationLimits($type);
        if (!$canCreate['success']) {
            return redirect()->route('operator.dashboard')
                ->with('error', $canCreate['message']);
        }

        $guides = $this->getGuideContent($type);
        $documentTypes = $this->getRequiredDocuments($type);
        $provinces = $this->getProvinces();

        return view('operator.organisations.create', compact(
            'type', 
            'guides', 
            'documentTypes', 
            'provinces'
        ));
    }

    /**
     * Enregistrer une nouvelle organisation
     */
    public function store(Request $request)
    {
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
                        'redirect_url' => route('operator.organisations.confirmation', $dossier->id)
                    ],
                    'confirmation_data' => $confirmationData
                ]);
            } else {
                return redirect()->route('operator.organisations.confirmation', $dossier->id)
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
                'message_confirmation' => 'Votre dossier a été soumis avec succès. Un accusé de réception sera disponible sous 72 heures ouvrées.'
            ];

            session()->forget('success_data');

            \Log::info('Page de confirmation consultée v3', [
                'user_id' => auth()->id(),
                'dossier_id' => $dossier->id,
                'organisation_nom' => $dossier->organisation->nom,
                'access_time' => now()
            ]);

            return view('operator.organisations.confirmation', compact('confirmationData'));

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
    // MÉTHODES PRIVÉES CORRIGÉES
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
     * Validation complète des données des 9 étapes - VERSION CORRIGÉE
     */
    private function validateCompleteOrganisationData(Request $request, $type)
    {
        // Log des données reçues pour debugging
        \Log::info('Validation DB v3 - Données reçues', [
            'keys' => array_keys($request->all()),
            'type' => $type,
            'org_objet_length' => strlen($request->input('org_objet', '')),
            'has_fondateurs' => $request->has('fondateurs'),
            'has_adherents' => $request->has('adherents'),
            'fondateurs_raw_type' => gettype($request->input('fondateurs')),
            'adherents_raw_type' => gettype($request->input('adherents')),
            'json_last_error_initial' => json_last_error_msg()
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
            
            // ÉTAPE 6 : Fondateurs - VALIDATION AVEC DÉCODAGE JSON SÉCURISÉ
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
                        // Mettre à jour la requête avec les données décodées
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
                    
                    // Validation détaillée des fondateurs
                    foreach ($value as $index => $fondateur) {
                        if (!is_array($fondateur)) {
                            $fail("Le fondateur ligne " . ($index + 1) . " doit être un objet valide.");
                            continue;
                        }
                        
                        if (empty($fondateur['nip']) || !preg_match('/^[0-9]{13}$/', $fondateur['nip'])) {
                            $fail("Le NIP du fondateur ligne " . ($index + 1) . " est invalide (13 chiffres requis).");
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
            
            // ÉTAPE 7 : Adhérents - VALIDATION AVEC DÉCODAGE JSON SÉCURISÉ
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
                        // Mettre à jour la requête avec les données décodées
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
                    
                    // Validation détaillée des adhérents
                    foreach ($value as $index => $adherent) {
                        if (!is_array($adherent)) {
                            $fail("L'adhérent ligne " . ($index + 1) . " doit être un objet valide.");
                            continue;
                        }
                        
                        // NIP obligatoire et format correct
                        if (empty($adherent['nip']) || !preg_match('/^[0-9]{13}$/', $adherent['nip'])) {
                            $fail("Le NIP de l'adhérent ligne " . ($index + 1) . " est invalide (13 chiffres requis).");
                        }
                        
                        // Nom et prénom obligatoires
                        if (empty($adherent['nom']) || empty($adherent['prenom'])) {
                            $fail("Le nom et prénom de l'adhérent ligne " . ($index + 1) . " sont obligatoires.");
                        }
                        
                        // Profession obligatoire (colonne ajoutée dans COLUMNS_v2)
                        if (empty($adherent['profession'])) {
                            $fail("La profession de l'adhérent ligne " . ($index + 1) . " est obligatoire.");
                        }
                        
                        // Vérification des professions exclues pour parti politique
                        if ($type === 'parti_politique' && !empty($adherent['profession'])) {
                            $professionsExclues = $this->getProfessionsExcluesParti();
                            if (in_array(strtolower($adherent['profession']), array_map('strtolower', $professionsExclues))) {
                                $fail("L'adhérent ligne " . ($index + 1) . " a une profession exclue pour les partis politiques: {$adherent['profession']}");
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

        // Règles spécifiques pour parti politique
        if ($type === 'parti_politique') {
            $rules['declaration_exclusivite_parti'] = 'required|accepted';
            $rules['adherents'][] = function ($attribute, $value, $fail) {
                // Validation supplémentaire pour minimum 50 adhérents pour parti politique
                if (is_array($value) && count($value) < 50) {
                    $fail("Un parti politique doit avoir au minimum 50 adhérents.");
                }
            };
        }

        $messages = [
            'demandeur_nip.digits' => 'Le NIP doit contenir exactement 13 chiffres.',
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
            
            // Post-traitement avec vérification de type sécurisée
            if (isset($validated['fondateurs'])) {
                if (is_string($validated['fondateurs'])) {
                    $decoded = json_decode($validated['fondateurs'], true);
                    $validated['fondateurs'] = $decoded ?? [];
                }
                if (!is_array($validated['fondateurs'])) {
                    $validated['fondateurs'] = [];
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
                
                // Assurer la fonction par défaut pour les adhérents
                foreach ($validated['adherents'] as &$adherent) {
                    if (empty($adherent['fonction'])) {
                        $adherent['fonction'] = 'Membre'; // Default selon la DB
                    }
                }
            }
            
            // Ajouter des valeurs par défaut pour les champs optionnels
            $validated['org_departement'] = $request->input('org_departement');
            $validated['declaration_veracite'] = $request->has('declaration_veracite');
            $validated['declaration_conformite'] = $request->has('declaration_conformite');
            $validated['declaration_autorisation'] = $request->has('declaration_autorisation');
            $validated['guide_read_confirm'] = $request->has('guide_read_confirm');
            
            \Log::info('Validation v3 réussie', [
                'fondateurs_count' => count($validated['fondateurs'] ?? []),
                'adherents_count' => count($validated['adherents'] ?? []),
                'type' => $type,
                'db_version' => 'COLUMNS_v2 - profession/fonction disponibles - JSON sécurisé v3'
            ]);
            
            return $validated;
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur validation v3 détaillée', [
                'errors' => $e->errors(),
                'input_keys' => array_keys($request->all()),
                'type' => $type,
                'json_last_error' => json_last_error_msg(),
                'fondateurs_type' => gettype($request->input('fondateurs')),
                'adherents_type' => gettype($request->input('adherents')),
                'db_version' => 'COLUMNS_v2',
                'profession_column_available' => true,
                'fonction_column_available' => true
            ]);
            
            throw $e;
        }
    }

    /**
     * Créer les adhérents avec système d'anomalies révolutionnaire - VERSION CORRIGÉE
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
            // Détecter les anomalies avant création
            $anomaliesDetectees = $this->detectAnomaliesAdherent($adherentData, $organisation->type);

            // S'assurer que les données historique sont correctement formatées
            $historiqueData = [
                'creation' => now()->toISOString(),
                'anomalies_detectees' => $anomaliesDetectees,
                'source' => 'creation_organisation',
                'profession_originale' => $adherentData['profession'] ?? null,
                'fonction_originale' => $adherentData['fonction'] ?? 'Membre'
            ];

            // Nettoyer les données avant insertion
            $adherentDataCleaned = [
                'organisation_id' => $organisation->id,
                'nip' => $adherentData['nip'],
                'nom' => strtoupper($adherentData['nom']),
                'prenom' => $adherentData['prenom'],
                
                // ✅ NOUVELLES COLONNES DISPONIBLES DANS COLUMNS_v2
                'profession' => $adherentData['profession'] ?? null,
                'fonction' => $adherentData['fonction'] ?? 'Membre',
                
                // Autres colonnes existantes
                'telephone' => $adherentData['telephone'] ?? null,
                'email' => $adherentData['email'] ?? null,
                'date_adhesion' => now(),
                'is_active' => empty($anomaliesDetectees['critiques']), // Actif seulement si pas d'anomalies critiques
                
                // Encoder explicitement l'historique en JSON
                'historique' => json_encode($historiqueData, JSON_UNESCAPED_UNICODE),
                
                'created_at' => now(),
                'updated_at' => now()
            ];

            $adherent = \App\Models\Adherent::create($adherentDataCleaned);
            $adherentsCreated[] = $adherent;

            // Comptabiliser les anomalies
            if (empty($anomaliesDetectees['critiques']) && empty($anomaliesDetectees['majeures']) && empty($anomaliesDetectees['mineures'])) {
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
                    'anomalies' => $anomaliesDetectees
                ];
            }
        }

        return [
            'adherents' => $adherentsCreated,
            'stats' => $stats,
            'anomalies' => $anomalies
        ];
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