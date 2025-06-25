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
        $type = $request->input('type_organisation');
        
        // Vérifier les limites avant création
        $canCreate = $this->checkOrganisationLimits($type);
        if (!$canCreate['success']) {
            return redirect()->back()
                ->with('error', $canCreate['message'])
                ->withInput();
        }

        // Validation des données
        $validatedData = $this->validateOrganisationData($request, $type);

        try {
            \DB::beginTransaction();

            // Créer l'organisation
            $organisation = Organisation::create(array_merge($validatedData, [
                'user_id' => auth()->id(),
                'statut' => 'brouillon'
            ]));

            // Créer le dossier associé
            $dossier = Dossier::create([
                'organisation_id' => $organisation->id,
                'user_id' => auth()->id(),
                'type_operation' => 'creation',
                'type_organisation' => $type,
                'statut' => 'brouillon',
                'numero_dossier' => $this->generateDossierNumber($type)
            ]);

            // Initialiser le workflow
            $this->workflowService->initializeWorkflow($dossier);

            \DB::commit();

            return redirect()->route('operator.dossiers.edit', $dossier)
                ->with('success', 'Organisation créée avec succès. Veuillez compléter le dossier.');

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Erreur création organisation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'organisation')
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
        $documentTypes = $this->getRequiredDocuments($organisation->type_organisation);

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

        $validatedData = $this->validateOrganisationData($request, $organisation->type_organisation);

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

            // Vérifier que tous les documents requis sont présents
            $missingDocuments = $this->checkRequiredDocuments($organisation);
            if (!empty($missingDocuments)) {
                return redirect()->route('operator.organisations.edit', $organisation)
                    ->with('error', 'Documents manquants: ' . implode(', ', $missingDocuments));
            }

            // Mettre à jour le statut
            $organisation->update(['statut' => 'soumis']);
            $organisation->dossier->update(['statut' => 'soumis']);

            // Démarrer le workflow
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
     * Vérifier les limites d'organisations par opérateur
     */
    private function checkOrganisationLimits($type)
    {
        $userId = auth()->id();

        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $existingCount = Organisation::where('user_id', $userId)
                    ->where('type_organisation', 'parti_politique')
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
                    ->where('type_organisation', 'confession_religieuse')
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
                // Pas de limite pour les associations et ONG
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
     * Valider les données d'organisation
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
     * Obtenir le contenu du guide pour un type d'organisation
     */
    private function getGuideContent($type)
    {
        $guides = [
            'parti' => [
                'title' => 'Guide de création d\'un parti politique',
                'description' => 'Étapes nécessaires pour créer un parti politique au Gabon',
                'requirements' => [
                    'Minimum 500 adhérents fondateurs',
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
     * Vérifier les documents requis
     */
    private function checkRequiredDocuments(Organisation $organisation)
    {
        $requiredDocs = $this->getRequiredDocuments($organisation->type_organisation);
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