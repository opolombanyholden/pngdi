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
    public function store(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Validation
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
            'province' => 'required|string|max:100',
            'departement' => 'required|string|max:100',
            'canton' => 'nullable|string|max:100',
            'prefecture' => 'nullable|string|max:100',
            'sous_prefecture' => 'nullable|string|max:100',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'date_adhesion' => 'nullable|date|before_or_equal:today',
            'photo' => 'nullable|image|max:2048',
            'piece_identite' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);
        
        // Vérifier l'âge minimum (18 ans)
        $age = \Carbon\Carbon::parse($validated['date_naissance'])->age;
        if ($age < 18) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'L\'adhérent doit avoir au moins 18 ans');
        }
        
        // Vérifier l'unicité pour les partis politiques
        if ($organisation->isPartiPolitique()) {
            $canJoin = Adherent::canJoinOrganisation($validated['nip'], $organisation->id);
            if (!$canJoin['can_join']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $canJoin['reason']);
            }
        }
        
        DB::beginTransaction();
        
        try {
            // Créer l'adhérent
            $adherentData = array_merge($validated, [
                'organisation_id' => $organisation->id,
                'date_adhesion' => $validated['date_adhesion'] ?? now(),
                'is_active' => true
            ]);
            
            // Upload de la photo
            if ($request->hasFile('photo')) {
                $adherentData['photo_path'] = $this->fileUploadService->uploadAdherentPhoto(
                    $request->file('photo'),
                    0 // ID temporaire, sera mis à jour après création
                );
            }
            
            // Upload de la pièce d'identité
            if ($request->hasFile('piece_identite')) {
                $uploadResult = $this->fileUploadService->upload(
                    $request->file('piece_identite'),
                    'adherents/pieces_identite'
                );
                $adherentData['piece_identite_path'] = $uploadResult['file_path'];
            }
            
            $adherent = Adherent::create($adherentData);
            
            // Mettre à jour le chemin de la photo avec l'ID réel
            if (isset($adherentData['photo_path'])) {
                $newPhotoPath = $this->fileUploadService->uploadAdherentPhoto(
                    $request->file('photo'),
                    $adherent->id
                );
                $adherent->update(['photo_path' => $newPhotoPath]);
            }
            
            DB::commit();
            
            return redirect()->route('operator.adherents.show', [$organisation, $adherent])
                ->with('success', 'Adhérent ajouté avec succès');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout : ' . $e->getMessage());
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