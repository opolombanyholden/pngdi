<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Adherent;
use App\Models\Fondateur;
use App\Services\AdherentImportService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Models\AdherentAnomalie;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\NipValidationService;

class AdherentController extends Controller
{
    protected $importService;
    protected $fileUploadService;
    protected $notificationService;
    
    public function __construct(
        AdherentImportService $importService,
        FileUploadService $fileUploadService,
        NotificationService $notificationService
    ) {
        $this->importService = $importService;
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
    }
    
    /**
     * Liste des adhérents d'une organisation
     */
    public function index(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $adherents = $organisation->adherents()
            ->with('exclusion')
            ->paginate(20);
        
        $stats = [
            'total' => $organisation->adherents()->count(),
            'actifs' => $organisation->adherentsActifs()->count(),
            'inactifs' => $organisation->adherents()->where('is_active', false)->count(),
            'fondateurs' => $organisation->fondateurs()->count()
        ];
        
        return view('operator.adherents.index', compact('organisation', 'adherents', 'stats'));
    }
    
    /**
     * Formulaire d'ajout d'un adhérent
     */
    public function create(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que l'organisation peut ajouter des adhérents
        if (!$organisation->canAddAdherent()) {
            return redirect()->route('operator.adherents.index', $organisation)
                ->with('error', 'Cette organisation ne peut pas ajouter d\'adhérents dans son état actuel');
        }
        
        return view('operator.adherents.create', compact('organisation'));
    }
    
    /**
     * Enregistrer un nouvel adhérent
     */
    /**
     * Enregistrer un nouvel adhérent avec gestion des anomalies NIP
     * Conforme à la règle métier SGLP : enregistrement non-bloquant
     */
    public function store(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // === VALIDATION DE BASE (NON-BLOQUANTE POUR NIP) ===
        $validated = $request->validate([
            'nip' => 'nullable|string|max:20', // ✅ NIP non obligatoire
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100', 
            'date_naissance' => 'nullable|date|before:today', // ✅ Non obligatoire
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
            'nationalite' => 'nullable|string|max:100',
            'profession' => 'nullable|string|max:255', // ✅ Non obligatoire
            'adresse' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:100',
            'departement' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'fonction' => 'required|string|max:100',
            'motif_adhesion' => 'nullable|string|max:500'
        ]);
        
        try {
            // === DÉTECTION DES ANOMALIES ===
            $anomaliesResult = $this->detectAdherentAnomalies($validated, $organisation);
            
            // === DÉTERMINER LE STATUT SELON LES ANOMALIES ===
            $statutValidation = $this->determineStatutFromAnomalies($anomaliesResult['anomalies']);
            
            // === PRÉ-REMPLISSAGE DEPUIS BASE NIP (SI DISPONIBLE) ===
            if (!empty($validated['nip'])) {
                $nipValidationService = new \App\Services\NipValidationService();
                $nipValidation = $nipValidationService->validateNip(
                    $validated['nip'],
                    $validated['nom'],
                    $validated['prenom']
                );
                
                // Fusionner données NIP si disponibles (sans écraser les données saisies)
                if (isset($nipValidation['nip_data'])) {
                    $nipData = $nipValidation['nip_data'];
                    
                    // ✅ CORRECTION : Fusion intelligente sans écraser les données saisies
                    $fieldsToMerge = [
                        'date_naissance',
                        'lieu_naissance', 
                        'sexe',
                        'telephone',
                        'email'
                    ];
                    
                    foreach ($fieldsToMerge as $field) {
                        // Ne fusionner que si le champ est vide ou null dans les données saisies
                        if (empty($validated[$field]) && !empty($nipData[$field])) {
                            $validated[$field] = $nipData[$field];
                        }
                    }
                    
                    \Log::info('Fusion données NIP effectuée', [
                        'nip' => $validated['nip'],
                        'champs_fusionnes' => array_intersect_key($nipData, array_flip($fieldsToMerge))
                    ]);
                }
            }
            
            // === CRÉER L'ADHÉRENT (TOUJOURS) ===
            $adherent = Adherent::create([
                'organisation_id' => $organisation->id,
                'nip' => $validated['nip'] ?? '',
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'date_naissance' => $validated['date_naissance'],
                'lieu_naissance' => $validated['lieu_naissance'],
                'sexe' => $validated['sexe'],
                'nationalite' => $validated['nationalite'] ?? 'Gabonaise',
                'profession' => $validated['profession'],
                'adresse' => $validated['adresse'],
                'province' => $validated['province'],
                'departement' => $validated['departement'],
                'telephone' => $validated['telephone'],
                'email' => $validated['email'],
                'fonction' => $validated['fonction'],
                'motif_adhesion' => $validated['motif_adhesion'],
                'date_adhesion' => now(),
                
                // === STATUT SELON ANOMALIES ===
                'statut_validation' => $statutValidation,
                'is_active' => $statutValidation !== 'en_attente',
                'has_anomalies' => !empty($anomaliesResult['anomalies']),
                'anomalies_severity' => $anomaliesResult['severity'],
                'anomalies_data' => !empty($anomaliesResult['anomalies']) ? json_encode($anomaliesResult['anomalies']) : null,
                
                // === APPARTENANCE MULTIPLE ===
                'appartenance_multiple' => in_array('DOUBLE_APPARTENANCE_PARTI', array_column($anomaliesResult['anomalies'], 'code')),
                'organisations_precedentes' => $anomaliesResult['organisations_precedentes'] ?? null,
                
                'nip_verified_at' => now()
            ]);
            
            // === CRÉER LES ENREGISTREMENTS D'ANOMALIES ===
            if (!empty($anomaliesResult['anomalies'])) {
                $this->createAnomalieRecords($adherent, $anomaliesResult['anomalies']);
            }
            
            // === MESSAGE DE SUCCÈS ADAPTÉ ===
            $message = $this->getSuccessMessage($statutValidation, $anomaliesResult);
            
            return redirect()->route('operator.adherents.show', [$organisation, $adherent])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Erreur création adhérent', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'ajout : ' . $e->getMessage())
                ->withInput();
        }
    }


    /**
     * Détecter toutes les anomalies d'un adhérent
     */
    private function detectAdherentAnomalies(array $data, Organisation $organisation): array
    {
        $anomalies = [];
        $severity = null;
        $organisationsPrecedentes = null;
        
        $nip = $data['nip'] ?? '';
        $profession = $data['profession'] ?? '';
        $dateNaissance = $data['date_naissance'] ?? null;
        
        // === ANOMALIES CRITIQUES ===
        
        // 1. NIP absent/vide
        if (empty(trim($nip))) {
            $anomalies[] = [
                'code' => 'NIP_ABSENT',
                'type' => 'critique',
                'message' => 'NIP absent ou vide',
                'champ' => 'nip',
                'valeur' => $nip
            ];
            $severity = 'critique';
        } else {
            // 2. Format NIP incorrect (devient MAJEURE selon votre correction)
            if (!$this->validateNipFormat($nip)) {
                $anomalies[] = [
                    'code' => 'NIP_FORMAT_INVALIDE',
                    'type' => 'majeure',
                    'message' => 'Format NIP incorrect (attendu: XX-QQQQ-YYYYMMDD)',
                    'champ' => 'nip',
                    'valeur' => $nip
                ];
                if (!$severity) $severity = 'majeure';
            } else {
                // 3. Âge mineur (si NIP valide)
                $age = $this->extractAgeFromNip($nip);
                if ($age !== null && $age < 18) {
                    $anomalies[] = [
                        'code' => 'AGE_MINEUR',
                        'type' => 'critique',
                        'message' => "Personne mineure détectée (âge: {$age} ans)",
                        'champ' => 'nip',
                        'valeur' => $age
                    ];
                    $severity = 'critique';
                } elseif ($age !== null && $age > 100) {
                    $anomalies[] = [
                        'code' => 'AGE_SUSPECT',
                        'type' => 'majeure',
                        'message' => "Âge suspect détecté (âge: {$age} ans)",
                        'champ' => 'nip',
                        'valeur' => $age
                    ];
                    if (!$severity) $severity = 'majeure';
                }
            }
            
            // 4. Double appartenance parti politique
            if ($organisation->type === 'parti_politique') {
                $existingMembership = $this->checkExistingMembership($nip, $organisation->id);
                if ($existingMembership) {
                    $anomalies[] = [
                        'code' => 'DOUBLE_APPARTENANCE_PARTI',
                        'type' => 'critique',
                        'message' => 'Appartenance multiple à des partis politiques détectée',
                        'champ' => 'nip',
                        'valeur' => $existingMembership
                    ];
                    $organisationsPrecedentes = json_encode($existingMembership);
                    $severity = 'critique';
                }
            }
        }
        
        // 5. Profession interdite
        if (!empty($profession) && $this->isProfessionInterdite($profession)) {
            $anomalies[] = [
                'code' => 'PROFESSION_INTERDITE',
                'type' => 'critique',
                'message' => "Profession interdite: {$profession}",
                'champ' => 'profession',
                'valeur' => $profession
            ];
            $severity = 'critique';
        }
        
        // === ANOMALIES MAJEURES ===
        
        // 6. Date de naissance absente
        if (empty($dateNaissance)) {
            $anomalies[] = [
                'code' => 'DATE_NAISSANCE_ABSENTE',
                'type' => 'majeure',
                'message' => 'Date de naissance non renseignée',
                'champ' => 'date_naissance',
                'valeur' => null
            ];
            if (!$severity) $severity = 'majeure';
        } else {
            // 7. Date de naissance format non conforme
            if (!$this->validateDateFormat($dateNaissance)) {
                $anomalies[] = [
                    'code' => 'DATE_NAISSANCE_FORMAT_INVALIDE',
                    'type' => 'majeure',
                    'message' => 'Format de date de naissance invalide',
                    'champ' => 'date_naissance',
                    'valeur' => $dateNaissance
                ];
                if (!$severity) $severity = 'majeure';
            }
        }
        
        // === ANOMALIES MINEURES ===
        
        // 8. Téléphone invalide
        if (!empty($data['telephone']) && !$this->validatePhoneFormat($data['telephone'])) {
            $anomalies[] = [
                'code' => 'TELEPHONE_INVALIDE',
                'type' => 'mineure',
                'message' => 'Format de téléphone invalide',
                'champ' => 'telephone',
                'valeur' => $data['telephone']
            ];
            if (!$severity) $severity = 'mineure';
        }
        
        // 9. Profession manquante
        if (empty($profession)) {
            $anomalies[] = [
                'code' => 'PROFESSION_MANQUANTE',
                'type' => 'mineure',
                'message' => 'Profession non renseignée',
                'champ' => 'profession',
                'valeur' => null
            ];
            if (!$severity) $severity = 'mineure';
        }
        
        return [
            'anomalies' => $anomalies,
            'severity' => $severity,
            'organisations_precedentes' => $organisationsPrecedentes
        ];
    }


    /**
     * Déterminer le statut selon les anomalies
     */
    private function determineStatutFromAnomalies(array $anomalies): string
    {
        foreach ($anomalies as $anomalie) {
            if ($anomalie['type'] === 'critique') {
                return 'en_attente';
            }
        }
        return 'valide';
    }


    /**
     * Vérifier si une profession est interdite
     */
    private function isProfessionInterdite(string $profession): bool
    {
        $filePath = storage_path('app/config/professions_interdites.txt');
        
        if (!file_exists($filePath)) {
            // Créer le fichier avec des professions par défaut
            $this->createDefaultProfessionsInterdites($filePath);
        }
        
        $professionsInterdites = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $professionsInterdites = array_map('trim', $professionsInterdites);
        $professionsInterdites = array_filter($professionsInterdites, function($line) {
            return !empty($line) && !str_starts_with($line, '#');
        });
        
        $professionLower = strtolower(trim($profession));
        
        foreach ($professionsInterdites as $interdite) {
            if (strtolower(trim($interdite)) === $professionLower) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Créer le fichier par défaut des professions interdites
     */
    private function createDefaultProfessionsInterdites(string $filePath): void
    {
        $defaultContent = "# Professions interdites pour adhésion aux organisations
    # Une profession par ligne, case insensitive
    magistrat
    juge
    procureur
    commissaire de police
    gendarme
    militaire actif
    fonctionnaire sécurité
    agent secret
    prefet
    sous-prefet
    gouverneur
    ministre
    directeur general
    secretaire general
    ";
        
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($filePath, $defaultContent);
    }


    /**
     * Valider le format NIP XX-QQQQ-YYYYMMDD
     */
    private function validateNipFormat(string $nip): bool
    {
        return preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', trim($nip));
    }

    /**
     * Extraire l'âge depuis un NIP valide
     */
    private function extractAgeFromNip(string $nip): ?int
    {
        if (!$this->validateNipFormat($nip)) {
            return null;
        }
        
        $datePart = substr($nip, -8); // Derniers 8 chiffres: YYYYMMDD
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        try {
            $birthDate = new \DateTime("{$year}-{$month}-{$day}");
            $now = new \DateTime();
            return $now->diff($birthDate)->y;
        } catch (\Exception $e) {
            return null;
        }
    }


    /**
     * Valider le format de date
     */
    private function validateDateFormat($date): bool
    {
        if (empty($date)) return false;
        
        try {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            return $d && $d->format('Y-m-d') === $date;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Valider le format téléphone
     */
    private function validatePhoneFormat(string $phone): bool
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($cleaned) >= 8 && strlen($cleaned) <= 15;
    }

    /**
     * Vérifier appartenance existante
     */
    private function checkExistingMembership(string $nip, int $currentOrgId): ?array
    {
        $existing = \App\Models\Adherent::where('nip', $nip)
            ->where('organisation_id', '!=', $currentOrgId)
            ->where('is_active', true)
            ->with('organisation')
            ->get();
        
        if ($existing->isEmpty()) {
            return null;
        }
        
        return $existing->map(function($adherent) {
            return [
                'id' => $adherent->organisation->id,
                'nom' => $adherent->organisation->nom,
                'type' => $adherent->organisation->type,
                'statut' => $adherent->organisation->statut
            ];
        })->toArray();
    }

    /**
     * Créer les enregistrements d'anomalies
     */
    private function createAnomalieRecords(Adherent $adherent, array $anomalies): void
    {
        foreach ($anomalies as $anomalie) {
            AdherentAnomalie::create([
                'adherent_id' => $adherent->id,
                'organisation_id' => $adherent->organisation_id,
                'type_anomalie' => $anomalie['type'], // ✅ CORRECTION : Garder le type original
                'champ_concerne' => $anomalie['champ'],
                'valeur_incorrecte' => is_array($anomalie['valeur']) ? json_encode($anomalie['valeur']) : $anomalie['valeur'],
                'description' => $anomalie['message'],
                'statut' => 'detectee',
                'detectee_le' => now()
            ]);
        }
    }


    /**
     * Générer le message de succès selon le contexte
     */
    private function getSuccessMessage(string $statut, array $anomaliesResult): string
    {
        $baseMessage = 'Adhérent ajouté avec succès.';
        
        if (empty($anomaliesResult['anomalies'])) {
            return $baseMessage . ' Aucune anomalie détectée.';
        }
        
        $count = count($anomaliesResult['anomalies']);
        $severity = $anomaliesResult['severity'];
        
        if ($statut === 'en_attente') {
            return $baseMessage . " {$count} anomalie(s) {$severity}(s) détectée(s). Validation manuelle requise.";
        } else {
            return $baseMessage . " {$count} anomalie(s) détectée(s) mais adhérent activé. Correction recommandée.";
        }
    }


    /**
     * Afficher un adhérent
     */
    public function show(Organisation $organisation, Adherent $adherent)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }
        
        $adherent->load(['histories', 'exclusion', 'imports']);
        
        return view('operator.adherents.show', compact('organisation', 'adherent'));
    }
    
    /**
     * Importer des adhérents via CSV
     */
    public function import(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        if ($request->isMethod('get')) {
            // Afficher le formulaire d'import
            $importHistory = $this->importService->getImportHistory($organisation);
            return view('operator.adherents.import', compact('organisation', 'importHistory'));
        }
        
        // Traiter l'import
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120'
        ]);
        
        try {
            $result = $this->importService->importFromCsv($organisation, $request->file('file'));
            
            // Notification
            $this->notificationService->notify(
                Auth::user(),
                'Import d\'adhérents terminé',
                sprintf(
                    'L\'import est terminé. %d adhérents importés avec succès, %d erreurs.',
                    $result['summary']['success'],
                    $result['summary']['errors']
                ),
                'info'
            );
            
            return redirect()->route('operator.adherents.import', $organisation)
                ->with('success', 'Import terminé')
                ->with('import_result', $result);
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }
    
    /**
     * Télécharger le modèle CSV
     */
    public function downloadTemplate()
    {
        $csvContent = $this->importService->generateTemplate();
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="modele_adherents.csv"'
        ]);
    }
    
    /**
     * Exporter les adhérents
     */
    public function export(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $filters = $request->only(['is_active', 'is_fondateur', 'search']);
        $csvContent = $this->importService->exportAdherents($organisation, $filters);
        
        $filename = sprintf(
            'adherents_%s_%s.csv',
            $organisation->sigle ?: 'export',
            date('Y-m-d')
        );
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    
    /**
     * Détecter les doublons
     */
    public function duplicates(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $duplicates = $this->importService->detectDuplicates($organisation);
        
        return view('operator.adherents.duplicates', compact('organisation', 'duplicates'));
    }
    
    /**
     * Exclure un adhérent
     */
    public function exclude(Request $request, Organisation $organisation, Adherent $adherent)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'motif' => 'required|string|max:500',
            'date_exclusion' => 'nullable|date|before_or_equal:today',
            'document' => 'nullable|file|mimes:pdf|max:5120'
        ]);
        
        try {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $uploadResult = $this->fileUploadService->upload(
                    $request->file('document'),
                    'adherents/exclusions'
                );
                $documentPath = $uploadResult['file_path'];
            }
            
            $adherent->exclude(
                $validated['motif'],
                $validated['date_exclusion'] ?? now(),
                $documentPath
            );
            
            return redirect()->route('operator.adherents.index', $organisation)
                ->with('success', 'Adhérent exclu avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'exclusion : ' . $e->getMessage());
        }
    }
    
    /**
     * Réactiver un adhérent
     */
    public function reactivate(Request $request, Organisation $organisation, Adherent $adherent)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }
        
        // Vérifier que l'adhérent est inactif
        if ($adherent->is_active) {
            return redirect()->back()
                ->with('error', 'Cet adhérent est déjà actif');
        }
        
        $validated = $request->validate([
            'motif' => 'required|string|max:500'
        ]);
        
        try {
            $adherent->reactivate($validated['motif']);
            
            return redirect()->route('operator.adherents.show', [$organisation, $adherent])
                ->with('success', 'Adhérent réactivé avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la réactivation : ' . $e->getMessage());
        }
    }
    
    /**
     * Gérer les fondateurs
     */
    public function fondateurs(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $fondateurs = $organisation->fondateurs()
            ->with('adherent')
            ->orderBy('ordre')
            ->get();
        
        return view('operator.adherents.fondateurs', compact('organisation', 'fondateurs'));
    }
    
    /**
     * Ajouter un fondateur
     */
    public function addFondateur(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'nip' => 'required|string|max:20',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'nationalite' => 'required|string|max:100',
            'profession' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'fonction' => 'required|string|max:100',
            'piece_identite' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);
        
        // Vérifier l'âge minimum (21 ans pour les fondateurs)
        $age = \Carbon\Carbon::parse($validated['date_naissance'])->age;
        if ($age < 21) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Un fondateur doit avoir au moins 21 ans');
        }
        
        DB::beginTransaction();
        
        try {
            // Upload de la pièce d'identité
            $uploadResult = $this->fileUploadService->upload(
                $request->file('piece_identite'),
                'fondateurs/pieces_identite'
            );
            
            // Créer le fondateur
            $fondateur = Fondateur::create(array_merge($validated, [
                'organisation_id' => $organisation->id,
                'piece_identite_path' => $uploadResult['file_path'],
                'ordre' => $organisation->fondateurs()->count() + 1,
                'is_active' => true
            ]));
            
            DB::commit();
            
            return redirect()->route('operator.adherents.fondateurs', $organisation)
                ->with('success', 'Fondateur ajouté avec succès');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout : ' . $e->getMessage());
        }
    }
    
    /**
     * Générer le lien d'auto-enregistrement
     */
    public function generateRegistrationLink(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que l'organisation est approuvée
        if (!$organisation->isApprouvee()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'organisation doit être approuvée pour générer un lien d\'enregistrement'
            ], 403);
        }
        
        try {
            $result = app(QrCodeService::class)->generateSecureRegistrationLink($organisation);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $result['url'],
                    'short_url' => $result['short_url'],
                    'expires_at' => $result['expires_at']->format('d/m/Y H:i'),
                    'qrcode' => $result['qrcode_data']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération : ' . $e->getMessage()
            ], 500);
        }
    }

   /**
 * Affiche la vue globale de tous les adhérents de l'utilisateur connecté
 * 
 * @return \Illuminate\View\View
 */
public function indexGlobal()
{
    try {
        // Récupérer toutes les organisations de l'utilisateur connecté avec leurs adhérents
        $organisations = auth()->user()->organisations()
            ->with(['adherents' => function($query) {
                $query->latest()->limit(5); // Limiter à 5 adhérents par organisation pour l'aperçu
            }])
            ->withCount('adherents') // Compter le total des adhérents
            ->get();

        // Statistiques globales
        $totalAdherents = auth()->user()->organisations()
            ->withCount('adherents')
            ->get()
            ->sum('adherents_count');

        $adherentsActifs = 0;
        $adherentsInactifs = 0;

        foreach ($organisations as $organisation) {
            foreach ($organisation->adherents as $adherent) {
                if ($adherent->is_active ?? true) {
                    $adherentsActifs++;
                } else {
                    $adherentsInactifs++;
                }
            }
        }

        return view('operator.members.index-global', compact(
            'organisations',
            'totalAdherents', 
            'adherentsActifs',
            'adherentsInactifs'
        ));

    } catch (\Exception $e) {
        \Log::error('Erreur dans indexGlobal AdherentController: ' . $e->getMessage());
        
        return redirect()->route('operator.dashboard')
            ->with('error', 'Erreur lors du chargement des adhérents. Veuillez réessayer.');
    }
}

}