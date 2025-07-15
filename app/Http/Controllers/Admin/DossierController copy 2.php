<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\User;
use App\Models\DossierValidation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\PDFService;


class DossierController extends Controller
{
    protected $pdfService;

    public function __construct(PDFService $pdfService)
    {
        $this->middleware(['auth', 'verified', 'admin']);
        $this->pdfService = $pdfService;
    }
    
   /**
     * Liste de toutes les organisations
     * Route: /admin/organisations
     */
    public function index(Request $request)
    {
        try {
            // Query de base avec les organisations et leurs dossiers
            $query = Organisation::with(['user', 'dossiers' => function($q) {
                $q->latest()->take(1); // Dernier dossier seulement
            }])->orderBy('created_at', 'desc');

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('sigle', 'like', "%{$search}%")
                      ->orWhere('numero_recepisse', 'like', "%{$search}%");
                });
            }

            // Filtre par type d'organisation
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Filtre par statut
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            // Filtre par province
            if ($request->filled('province')) {
                $query->where('province', $request->province);
            }

            // Filtre par date de création
            if ($request->filled('date_debut')) {
                $query->where('created_at', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->where('created_at', '<=', $request->date_fin);
            }

            // Pagination
            $organisations = $query->paginate(20);

            // Enrichir chaque organisation avec des données calculées
            $organisations->getCollection()->transform(function ($organisation) {
                return $this->enrichOrganisationData($organisation);
            });

            // Statistiques pour la vue
            $stats = [
                'total_organisations' => Organisation::count(),
                'par_type' => [
                    'association' => Organisation::where('type', 'association')->count(),
                    'ong' => Organisation::where('type', 'ong')->count(),
                    'parti_politique' => Organisation::where('type', 'parti_politique')->count(),
                    'confession_religieuse' => Organisation::where('type', 'confession_religieuse')->count(),
                ],
                'par_statut' => [
                    'brouillon' => Organisation::where('statut', 'brouillon')->count(),
                    'soumis' => Organisation::where('statut', 'soumis')->count(),
                    'en_validation' => Organisation::where('statut', 'en_validation')->count(),
                    'approuve' => Organisation::where('statut', 'approuve')->count(),
                    'rejete' => Organisation::where('statut', 'rejete')->count(),
                ],
                'nouvelles_semaine' => Organisation::where('created_at', '>=', now()->subWeek())->count(),
                'approuvees_mois' => Organisation::where('statut', 'approuve')
                    ->where('updated_at', '>=', now()->subMonth())->count(),
            ];

            // Listes pour les filtres
            $types = [
                'association' => 'Association',
                'ong' => 'ONG',
                'parti_politique' => 'Parti Politique',
                'confession_religieuse' => 'Confession Religieuse'
            ];

            $statuts = [
                'brouillon' => 'Brouillon',
                'soumis' => 'Soumis',
                'en_validation' => 'En validation',
                'approuve' => 'Approuvé',
                'rejete' => 'Rejeté'
            ];

            $provinces = [
                'Estuaire', 'Haut-Ogooué', 'Moyen-Ogooué', 'Ngounié', 
                'Nyanga', 'Ogooué-Ivindo', 'Ogooué-Lolo', 'Ogooué-Maritime', 'Woleu-Ntem'
            ];

            return view('admin.organisations.index', compact(
                'organisations', 
                'stats', 
                'types', 
                'statuts', 
                'provinces'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur DossierController@index: ' . $e->getMessage());
            
            return back()->with('error', 'Erreur lors du chargement des organisations.');
        }
    }

    /**
     * Page des dossiers en attente - Compatible avec en-attente.blade.php
     */
    public function enAttente(Request $request)
{
    try {
        // Query de base avec SEULEMENT les relations confirmées
        $query = Dossier::with(['organisation']) // Organisation existe ✅
            ->whereIn('statut', ['soumis', 'en_cours'])
            ->where(function($q) {
                $q->whereNull('assigned_to')->orWhere('statut', 'soumis');
            })
            ->orderBy('created_at', 'desc');

        // Application des filtres de recherche
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('numero_dossier', 'like', "%{$search}%")
                  ->orWhereHas('organisation', function($org) use ($search) {
                      $org->where('nom', 'like', "%{$search}%")
                          ->orWhere('sigle', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par type d'organisation
        if ($request->filled('type') && $request->type !== '') {
            $query->whereHas('organisation', function($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // Filtre par priorité calculée
        if ($request->filled('priorite') && $request->priorite !== '') {
            if ($request->priorite === 'haute') {
                $query->where(function($q) {
                    $q->where('created_at', '<=', now()->subDays(7))
                      ->orWhereHas('organisation', function($org) {
                          $org->where('type', 'parti_politique');
                      });
                });
            } elseif ($request->priorite === 'normale') {
                $query->where('created_at', '>', now()->subDays(7))
                      ->whereHas('organisation', function($org) {
                          $org->where('type', '!=', 'parti_politique');
                      });
            }
        }

        // Filtre par période
        if ($request->filled('periode') && $request->periode !== '') {
            switch ($request->periode) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->where('created_at', '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->startOfMonth());
                    break;
            }
        }

        // Pagination avec 15 éléments par page
        $dossiersEnAttente = $query->paginate(15);

        // Enrichir chaque dossier avec données métier
        $dossiersEnAttente->getCollection()->transform(function ($dossier) {
            return $this->enrichDossierDataArchitecture($dossier);
        });

        // Calcul des statistiques pour les cards
        $totalEnAttente = Dossier::whereIn('statut', ['soumis', 'en_cours'])
            ->where(function($q) {
                $q->whereNull('assigned_to')->orWhere('statut', 'soumis');
            })->count();

        $prioriteHaute = $this->calculateHighPriorityCountArchitecture();
        $delaiMoyen = $this->calculateAverageWaitingTimeArchitecture();
        
        // Agents disponibles - Utiliser le modèle User correct
        $agents = User::where('role', 'agent')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Retour de la vue avec toutes les données
        return view('admin.dossiers.en-attente', compact(
            'dossiersEnAttente',
            'totalEnAttente',
            'prioriteHaute',
            'delaiMoyen',
            'agents'
        ));

    } catch (\Exception $e) {
        // Log détaillé de l'erreur
        \Log::error('Erreur DossierController@enAttente: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_params' => $request->all()
        ]);
        
        // Retour avec message d'erreur utilisateur
        return back()->with('error', 'Erreur lors du chargement des dossiers en attente. Veuillez réessayer.')
                    ->withInput();
    }
}


    /**
     * Afficher les détails d'un dossier
     */
    public function show(Request $request, $id)
    {
        try {
            // CORRECTION : Supprimer 'user' du with() car cette relation n'existe pas sur Dossier
            $dossier = Dossier::with([
                'organisation.fondateurs',
                'organisation.adherents' => function($query) {
                    $query->take(10); // Limiter pour performance
                },
                'documents.documentType',
                'assignedAgent',
                'dossierValidations.validatedBy',
                'dossierComments.user'
            ])->findOrFail($id);

            // Enrichir avec données métier
            $dossier = $this->enrichDossierData($dossier);

            // Historique des actions sur le dossier
            $historique = $this->getDossierHistory($dossier);

            // Documents disponibles avec les nouvelles méthodes
            $documentsDisponibles = $this->getAvailableActionsUpdated($dossier);

            // Statistiques du dossier
            $dossierStats = $this->getDossierStats($dossier);

            // Récupérer les informations du déclarant depuis le JSON
            $declarant = null;
            if (!empty($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true);
                $declarant = $donneesSupplementaires['demandeur'] ?? null;
            }

            return view('admin.dossiers.show', compact(
                'dossier',
                'historique',
                'documentsDisponibles',
                'dossierStats',
                'declarant'
            ));

        } catch (\ModelNotFoundException $e) {
            return redirect()->route('admin.dossiers.en-attente')
                ->with('error', 'Dossier non trouvé.');
        } catch (\Exception $e) {
            Log::error('Erreur DossierController@show: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système'
            ]);
            
            return back()->with('error', 'Erreur lors du chargement du dossier.');
        }
    }

    /**
     * Télécharger l'accusé de réception PDF
     */
    public function downloadAccuse($id)
    {
        try {
            // CORRECTION : Charger avec organisation.fondateurs au lieu de user (relation inexistante)
            $dossier = Dossier::with(['organisation.fondateurs'])->findOrFail($id);
            
            // Vérifier que le dossier a des données supplémentaires JSON
            if (empty($dossier->donnees_supplementaires)) {
                return back()->with('error', 'Impossible de générer l\'accusé : informations du déclarant manquantes.');
            }
            
            // Générer le PDF d'accusé de réception
            $pdf = $this->pdfService->generateAccuseReception($dossier);
            
            // Nom de fichier sécurisé
            $filename = $this->sanitizeFilename("accuse_reception_{$dossier->numero_dossier}") . "_" . now()->format('Ymd') . ".pdf";
            
            // Log de l'action avec informations du déclarant
            $declarant = json_decode($dossier->donnees_supplementaires, true)['demandeur'] ?? [];
            Log::info("Génération accusé PDF pour dossier {$dossier->id}", [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Inconnue',
                'declarant_nom' => ($declarant['prenom'] ?? '') . ' ' . ($declarant['nom'] ?? ''),
                'declarant_nip' => $declarant['nip'] ?? 'Non renseigné',
                'user' => auth()->user()->name
            ]);
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur génération accusé PDF: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système',
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la génération de l\'accusé de réception: ' . $e->getMessage());
        }
    }




    /**
     * Télécharger le récépissé final PDF
     */
    public function downloadRecepisse($id)
    {
        try {
            // CORRECTION : Charger avec organisation.fondateurs au lieu de user
            $dossier = Dossier::with(['organisation.fondateurs'])->findOrFail($id);
            
            // Vérifier que le dossier est approuvé
            if ($dossier->statut !== 'approuve') {
                return back()->with('error', 'Le récépissé définitif n\'est disponible que pour les dossiers approuvés.');
            }
            
            // Générer le PDF de récépissé
            $pdf = $this->pdfService->generateRecepisseDefinitif($dossier);
            
            // Nom de fichier sécurisé
            $filename = $this->sanitizeFilename("recepisse_definitif_{$dossier->organisation->nom}_{$dossier->numero_dossier}") . "_" . now()->format('Ymd') . ".pdf";
            
            // Log de l'action
            Log::info("Génération récépissé définitif PDF pour dossier {$dossier->id}", [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Inconnue',
                'numero_recepisse' => $dossier->numero_recepisse,
                'user' => auth()->user()->name
            ]);
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur génération récépissé définitif PDF: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système',
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la génération du récépissé définitif: ' . $e->getMessage());
        }
    }



    /**
     * Valider un dossier (AJAX)
     */
    public function valider(Request $request, $id)
    {
        try {
            $request->approuver([
                'numero_enregistrement' => 'nullable|string|max:100',
                'commentaire' => 'nullable|string|max:1000'
            ]);

            $dossier = Dossier::findOrFail($id);

            DB::transaction(function() use ($dossier, $request) {
                // Mettre à jour le dossier
                $dossier->update([
                    'statut' => 'approuve',
                    'validated_at' => now(),
                    'numero_recepisse' => $request->numero_enregistrement ?: $this->generateRecepisseNumber($dossier)
                ]);

                // Mettre à jour l'organisation
                if ($dossier->organisation) {
                    $dossier->organisation->update([
                        'statut' => 'approuve',
                        'numero_recepisse' => $dossier->numero_recepisse
                    ]);
                }

                // Créer/mettre à jour la validation
                DossierValidation::updateOrCreate([
                    'dossier_id' => $dossier->id,
                ], [
                    'workflow_step_id' => 1,
                    'validation_entity_id' => 1,
                    'validated_by' => auth()->id(),
                    'decision' => 'approuve',
                    'commentaire' => $request->commentaire,
                    'numero_enregistrement' => $request->numero_enregistrement,
                    'decided_at' => now(),
                    'duree_traitement' => $dossier->created_at->diffInMinutes(now())
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Dossier validé avec succès',
                'recepisse_number' => $dossier->fresh()->numero_recepisse
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur validation dossier: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeter un dossier (AJAX)
     */
    public function rejeter(Request $request, $id)
    {
        try {
            $request->approuver([
                'motif' => 'required|string|max:1000',
                'commentaire' => 'required|string|max:1000'
            ]);

            $dossier = Dossier::findOrFail($id);

            DB::transaction(function() use ($dossier, $request) {
                $dossier->update([
                    'statut' => 'rejete',
                    'motif_rejet' => $request->commentaire,
                    'validated_at' => now()
                ]);

                DossierValidation::updateOrCreate([
                    'dossier_id' => $dossier->id,
                ], [
                    'workflow_step_id' => 1,
                    'validation_entity_id' => 1,
                    'validated_by' => auth()->id(),
                    'decision' => 'rejete',
                    'motif_rejet' => $request->motif,
                    'commentaire' => $request->commentaire,
                    'decided_at' => now(),
                    'duree_traitement' => $dossier->created_at->diffInMinutes(now())
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Dossier rejeté avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur rejet dossier: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assigner un dossier à un agent (AJAX)
     */
    public function attribuer(Request $request, $id)
    {
        try {
            $request->approuver([
                'agent_id' => 'required|exists:users,id',
                'priorite' => 'nullable|in:normale,moyenne,haute',
                'commentaire' => 'nullable|string|max:500'
            ]);

            $dossier = Dossier::findOrFail($id);
            $agent = User::findOrFail($request->agent_id);

            $dossier->update([
                'assigned_to' => $agent->id,
                'statut' => 'en_cours',
                'assigned_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Dossier assigné à {$agent->name} avec succès"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTHODES PRIVÉES ==========

    /**
     * Enrichir un dossier avec des données métier calculées
     */
    private function enrichDossierData($dossier)
    {
        // Jours d'attente
        $dossier->jours_attente = now()->diffInDays($dossier->created_at);
        
        // Calcul de priorité
        $priorite = $this->calculatePriorite($dossier);
        $dossier->priorite = $priorite['niveau'];
        $dossier->priorite_color = $priorite['color'];
        
        // Progression du workflow
        $dossier->progression = $this->calculateProgression($dossier);
        
        // Actions disponibles
        $dossier->actions_disponibles = $this->getAvailableActions($dossier);

        return $dossier;
    }

    /**
     * Calculer la priorité d'un dossier
     */
    private function calculatePriorite($dossier)
    {
        $joursAttente = now()->diffInDays($dossier->created_at);
        
        // Parti politique = priorité haute automatique
        if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
            return ['niveau' => 'haute', 'color' => 'danger'];
        }
        
        // Basé sur ancienneté
        if ($joursAttente > 10) {
            return ['niveau' => 'haute', 'color' => 'danger'];
        } elseif ($joursAttente > 5) {
            return ['niveau' => 'moyenne', 'color' => 'warning'];
        } else {
            return ['niveau' => 'normale', 'color' => 'success'];
        }
    }

    /**
     * Calculer le pourcentage de progression
     */
    private function calculateProgression($dossier)
    {
        switch($dossier->statut) {
            case 'brouillon': return 10;
            case 'soumis': return 30;
            case 'en_cours': return 60;
            case 'approuve': return 100;
            case 'rejete': return 100;
            default: return 0;
        }
    }

    /**
     * Actions disponibles selon statut
     */
    private function getAvailableActions($dossier)
    {
        switch ($dossier->statut) {
            case 'soumis':
                return ['assigner', 'valider', 'rejeter'];
            case 'en_cours':
                return ['valider', 'rejeter', 'reassigner'];
            case 'approuve':
                return ['consulter', 'download_recepisse'];
            case 'rejete':
                return ['consulter'];
            default:
                return [];
        }
    }

    /**
     * Calculer nombre de dossiers haute priorité
     */
    private function calculateHighPriorityCount()
    {
        return Dossier::whereIn('statut', ['soumis', 'en_cours'])
            ->where(function($q) {
                $q->where('created_at', '<=', now()->subDays(7))
                  ->orWhereHas('organisation', function($org) {
                      $org->where('type', 'parti_politique');
                  });
            })->count();
    }

    /**
     * Calculer temps d'attente moyen
     */
    private function calculateAverageWaitingTime()
    {
        $dossiers = Dossier::whereIn('statut', ['soumis', 'en_cours'])->get();
        
        if ($dossiers->isEmpty()) {
            return 0;
        }

        $totalJours = $dossiers->sum(function($dossier) {
            return now()->diffInDays($dossier->created_at);
        });

        return round($totalJours / $dossiers->count(), 1);
    }

    /**
     * Obtenir l'historique d'un dossier
     */
    private function getDossierHistory($dossier)
    {
        // Pour l'instant, retourner un historique simulé
        // À terme, utiliser une table d'audit ou dossier_operations
        return collect([
            [
                'date' => $dossier->created_at,
                'action' => 'Création du dossier',
                'utilisateur' => $dossier->organisation->user->name ?? 'Système',
                'details' => 'Dossier soumis pour validation'
            ]
        ]);
    }

    /**
     * Documents disponibles pour téléchargement
     */
    private function getAvailableDocuments($dossier)
    {
        $documents = [];
        
        // Accusé de réception toujours disponible
        $documents[] = [
            'type' => 'accuse',
            'nom' => 'Accusé de réception',
            'url' => route('admin.dossiers.download-accuse', $dossier->id),
            'icon' => 'fas fa-file-alt'
        ];
        
        // Récépissé seulement si approuvé
        if ($dossier->statut === 'approuve') {
            $documents[] = [
                'type' => 'recepisse',
                'nom' => 'Récépissé de création',
                'url' => route('admin.dossiers.download-recepisse', $dossier->id),
                'icon' => 'fas fa-certificate'
            ];
        }

        return $documents;
    }

    /**
     * Statistiques du dossier
     */
    private function getDossierStats($dossier)
    {
        return [
            'jours_ecoules' => now()->diffInDays($dossier->created_at),
            'nb_documents' => $dossier->documents ? $dossier->documents->count() : 0,
            'nb_adherents' => $dossier->organisation && $dossier->organisation->adherents ? 
                $dossier->organisation->adherents->count() : 0,
            'progression' => $this->calculateProgression($dossier)
        ];
    }

    /**
     * Générer numéro de récépissé unique
     */
    private function generateRecepisseNumber($dossier)
    {
        $type = $dossier->organisation ? substr($dossier->organisation->type, 0, 3) : 'ORG';
        $year = now()->year;
        $sequence = str_pad(Dossier::where('statut', 'approuve')->count() + 1, 4, '0', STR_PAD_LEFT);
        
        return strtoupper($type) . '-' . $year . '-' . $sequence;
    }

    /**
     * Générer PDF accusé de réception (placeholder)
     */
    
    private function generateAccusePDF($dossier)
    {
        try {
            return $this->pdfService->generateAccuseReception($dossier);
        } catch (\Exception $e) {
            \Log::error('Erreur génération accusé PDF: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération de l\'accusé de réception: ' . $e->getMessage());
        }
    }

    /**
     * Générer PDF récépissé
     */
    private function generateRecepissePDF($dossier)
    {
        try {
            return $this->pdfService->generateRecepisseDefinitif($dossier);
        } catch (\Exception $e) {
            \Log::error('Erreur génération récépissé PDF: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération du récépissé: ' . $e->getMessage());
        }
    }
    
    /**
     * ======================================
     * MÉTHODES UTILITAIRES AJOUTÉES
     * ======================================
     */

    /**
     * Enrichit un dossier avec des données métier calculées
     */


    /**
     * Calcule la priorité d'un dossier
     */
    private function calculatePriority($dossier)
    {
        // Priorité haute si :
        // - Parti politique (toujours prioritaire)
        // - Dossier en attente depuis plus de 7 jours
        if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
            return 'haute';
        }
        
        $delai = Carbon::parse($dossier->created_at)->diffInDays(now());
        return $delai > 7 ? 'haute' : 'normale';
    }

    /**
     * Compte le nombre de dossiers à priorité haute
     */
    

    /**
     * Calcule le délai moyen d'attente
     */
    

    /**
     * ======================================
     * AUTRES MÉTHODES NÉCESSAIRES
     * ======================================
     */

    /**
     * Assigne un dossier à un agent
     */
    public function assign(Request $request, $id)
    {
        try {
            $request->approuver([
                'agent_id' => 'required|exists:users,id',
                'commentaire' => 'nullable|string|max:1000'
            ]);

            $dossier = Dossier::findOrFail($id);
            $agent = User::where('id', $request->agent_id)
                         ->where('role', 'agent')
                         ->firstOrFail();

            // Mise à jour du dossier
            $dossier->update([
                'assigned_to' => $agent->id,
                'statut' => 'en_cours',
                'assigned_at' => now()
            ]);

            // Ajout d'un commentaire si fourni
            if ($request->filled('commentaire')) {
                $dossier->operations()->create([
                    'type_operation' => 'commentaire',
                    'user_id' => auth()->id(),
                    'type' => 'assignation',
                    'contenu' => $request->commentaire,
                    'is_visible_operateur' => true
                ]);
            }

            // Log de l'activité
            activity()
                ->performedOn($dossier)
                ->causedBy(auth()->user())
                ->withProperties([
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->name
                ])
                ->log('Dossier assigné à un agent');

            return response()->json([
                'success' => true,
                'message' => "Dossier assigné à {$agent->name} avec succès"
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur DossierController@assign: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation'
            ], 500);
        }
    }

    /**
     * Ajoute un commentaire à un dossier
     */
    public function addComment(Request $request, $id)
    {
        try {
            $request->approuver([
                'comment_text' => 'required|string|max:1000'
            ]);

            $dossier = Dossier::findOrFail($id);

            $comment = $dossier->operations()->create([
                'type_operation' => 'commentaire',
                'user_id' => auth()->id(),
                'type' => 'note_admin',
                'contenu' => $request->comment_text,
                'is_visible_operateur' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Commentaire ajouté avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur DossierController@addComment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du commentaire'
            ], 500);
        }
    }


// ===============================================
// MÉTHODES À AJOUTER AU DossierController
// ===============================================

/**
 * Valide et approuve un dossier
 * Route: POST /admin/dossiers/{id}/validate
 */
public function approuver(Request $request, $id)
{
    try {
        $request->approuver([
            'numero_recepisse_final' => 'required|string|max:100|unique:organisations,numero_recepisse,' . $id,
            'date_approbation' => 'required|date',
            'validite_mois' => 'nullable|integer|min:1|max:120',
            'commentaire_approbation' => 'nullable|string|max:2000',
            'generer_recepisse' => 'boolean',
            'envoyer_email_approbation' => 'boolean',
            'publier_annuaire' => 'boolean'
        ]);

        DB::beginTransaction();

        $dossier = Dossier::with('organisation')->findOrFail($id);
        
        // Vérifier que le dossier peut être approuvé
        if (!in_array($dossier->statut, ['en_cours', 'soumis'])) {
            return response()->json([
                'success' => false,
                'message' => 'Ce dossier ne peut pas être approuvé dans son état actuel'
            ], 400);
        }

        // Mettre à jour le dossier
        $dossier->update([
            'statut' => 'approuve',
            'approved_at' => $request->date_approbation,
            'approved_by' => auth()->id()
        ]);

        // Mettre à jour l'organisation
        if ($dossier->organisation) {
            $updateData = [
                'numero_recepisse' => $request->numero_recepisse_final,
                'date_approbation' => $request->date_approbation,
                'is_approved' => true
            ];

            if ($request->validite_mois) {
                $updateData['date_expiration'] = Carbon::parse($request->date_approbation)
                    ->addMonths($request->validite_mois);
            }

            if ($request->publier_annuaire) {
                $updateData['visible_annuaire'] = true;
            }

            $dossier->organisation->update($updateData);
        }

        // Ajouter un commentaire d'approbation
        if ($request->filled('commentaire_approbation')) {
            $dossier->operations()->create([
                'type_operation' => 'commentaire',
                'user_id' => auth()->id(),
                'type' => 'approbation',
                'contenu' => $request->commentaire_approbation,
                'is_visible_operateur' => true
            ]);
        }

        // Créer une validation officielle
        $dossier->validations()->create([
            'user_id' => auth()->id(),
            'type_validation' => 'approbation',
            'statut' => 'approuve',
            'commentaire' => $request->commentaire_approbation,
            'date_validation' => $request->date_approbation,
            'numero_recepisse' => $request->numero_recepisse_final
        ]);

        // Générer le récépissé PDF si demandé
        if ($request->generer_recepisse && $this->pdfService) {
            try {
                $recepisseUrl = $this->pdfService->generateRecepisse($dossier);
                
                // Sauvegarder le document récépissé
                $dossier->documents()->create([
                    'nom_fichier' => 'recepisse_' . $dossier->numero_dossier . '.pdf',
                    'nom_original' => 'Récépissé Officiel.pdf',
                    'type_document' => 'recepisse',
                    'chemin_fichier' => $recepisseUrl,
                    'taille_fichier' => 0, // À calculer si nécessaire
                    'is_generated' => true
                ]);
            } catch (\Exception $e) {
                \Log::warning('Erreur génération récépissé: ' . $e->getMessage());
            }
        }

        // Envoyer notification email si demandé
        if ($request->envoyer_email_approbation && $dossier->user) {
            try {
                // TODO: Implémenter l'envoi d'email avec Mailable
                \Log::info('Email d\'approbation à envoyer à: ' . $dossier->user->email);
            } catch (\Exception $e) {
                \Log::warning('Erreur envoi email: ' . $e->getMessage());
            }
        }

        // Log de l'activité
        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->withProperties([
                'numero_recepisse' => $request->numero_recepisse_final,
                'date_approbation' => $request->date_approbation,
                'validite_mois' => $request->validite_mois
            ])
            ->log('Dossier approuvé');

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Dossier approuvé avec succès',
            'numero_recepisse' => $request->numero_recepisse_final
        ]);

    } catch (ValidationException $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error('Erreur DossierController@validate: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'approbation'
        ], 500);
    }
}

/**
 * Rejette un dossier
 * Route: POST /admin/dossiers/{id}/reject
 */
public function reject(Request $request, $id)
{
    try {
        $request->approuver([
            'motif_rejet' => 'required|string|max:100',
            'justification_rejet' => 'required|string|max:2000',
            'recommandations' => 'nullable|string|max:1000',
            'possibilite_recours' => 'required|in:oui,oui_avec_delai,non',
            'delai_recours' => 'nullable|integer|min:0|max:365',
            'envoyer_email_rejet' => 'boolean',
            'generer_lettre_rejet' => 'boolean',
            'archiver_dossier' => 'boolean'
        ]);

        DB::beginTransaction();

        $dossier = Dossier::with('organisation', 'user')->findOrFail($id);
        
        // Vérifier que le dossier peut être rejeté
        if (in_array($dossier->statut, ['approuve', 'rejete'])) {
            return response()->json([
                'success' => false,
                'message' => 'Ce dossier ne peut pas être rejeté dans son état actuel'
            ], 400);
        }

        // Mettre à jour le dossier
        $dossier->update([
            'statut' => 'rejete',
            'rejected_at' => now(),
            'rejected_by' => auth()->id()
        ]);

        // Créer une validation de rejet
        $dossier->validations()->create([
            'user_id' => auth()->id(),
            'type_validation' => 'rejet',
            'statut' => 'rejete',
            'motif' => $request->motif_rejet,
            'commentaire' => $request->justification_rejet,
            'recommandations' => $request->recommandations,
            'possibilite_recours' => $request->possibilite_recours,
            'delai_recours_jours' => $request->delai_recours,
            'date_validation' => now()
        ]);

        // Ajouter un commentaire de rejet
        $commentaireRejet = "**Dossier rejeté**\n\n";
        $commentaireRejet .= "**Motif:** " . $request->motif_rejet . "\n\n";
        $commentaireRejet .= "**Justification:** " . $request->justification_rejet;
        
        if ($request->filled('recommandations')) {
            $commentaireRejet .= "\n\n**Recommandations:** " . $request->recommandations;
        }

        $dossier->operations()->create([
            'type_operation' => 'commentaire',
            'user_id' => auth()->id(),
            'type' => 'rejet',
            'contenu' => $commentaireRejet,
            'is_visible_operateur' => true
        ]);

        // Générer la lettre de rejet si demandé
        if ($request->generer_lettre_rejet && $this->pdfService) {
            try {
                $lettreUrl = $this->pdfService->generateLettreRejet($dossier, [
                    'motif' => $request->motif_rejet,
                    'justification' => $request->justification_rejet,
                    'recommandations' => $request->recommandations,
                    'possibilite_recours' => $request->possibilite_recours,
                    'delai_recours' => $request->delai_recours
                ]);
                
                $dossier->documents()->create([
                    'nom_fichier' => 'lettre_rejet_' . $dossier->numero_dossier . '.pdf',
                    'nom_original' => 'Lettre de Rejet Officielle.pdf',
                    'type_document' => 'lettre_rejet',
                    'chemin_fichier' => $lettreUrl,
                    'is_generated' => true
                ]);
            } catch (\Exception $e) {
                \Log::warning('Erreur génération lettre rejet: ' . $e->getMessage());
            }
        }

        // Envoyer notification email si demandé
        if ($request->envoyer_email_rejet && $dossier->user) {
            try {
                // TODO: Implémenter l'envoi d'email de rejet
                \Log::info('Email de rejet à envoyer à: ' . $dossier->user->email);
            } catch (\Exception $e) {
                \Log::warning('Erreur envoi email rejet: ' . $e->getMessage());
            }
        }

        // Archiver si demandé
        if ($request->archiver_dossier) {
            $dossier->archives()->create([
                'archived_by' => auth()->id(),
                'archived_at' => now(),
                'motif_archivage' => 'Archivage automatique après rejet',
                'type_archive' => 'rejet'
            ]);
        }

        // Log de l'activité
        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->withProperties([
                'motif' => $request->motif_rejet,
                'possibilite_recours' => $request->possibilite_recours
            ])
            ->log('Dossier rejeté');

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Dossier rejeté avec succès'
        ]);

    } catch (ValidationException $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error('Erreur DossierController@reject: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du rejet'
        ], 500);
    }
}

/**
 * Demande des modifications à un dossier
 * Route: POST /admin/dossiers/{id}/request-modification
 */
public function requestModification(Request $request, $id)
{
    try {
        $request->approuver([
            'modifications' => 'required|array|min:1',
            'modifications.*' => 'string|max:100',
            'details_modifications' => 'required|string|max:2000',
            'delai_modification' => 'required|integer|min:1|max:365',
            'priorite_modification' => 'required|in:normale,haute,basse',
            'envoyer_email_modification' => 'boolean',
            'suspendre_traitement' => 'boolean',
            'rappel_automatique' => 'boolean'
        ]);

        DB::beginTransaction();

        $dossier = Dossier::with('organisation', 'user')->findOrFail($id);
        
        // Vérifier que le dossier peut être modifié
        if (in_array($dossier->statut, ['approuve', 'rejete'])) {
            return response()->json([
                'success' => false,
                'message' => 'Ce dossier ne peut plus être modifié'
            ], 400);
        }

        // Mettre à jour le statut si suspension demandée
        if ($request->suspendre_traitement) {
            $dossier->update([
                'statut' => 'en_attente_modification',
                'modification_requested_at' => now(),
                'modification_deadline' => now()->addDays($request->delai_modification)
            ]);
        }

        // Créer l'enregistrement de demande de modification
        $dossier->modifications()->create([
            'user_id' => auth()->id(),
            'type_modifications' => $request->modifications,
            'details' => $request->details_modifications,
            'delai_jours' => $request->delai_modification,
            'priorite' => $request->priorite_modification,
            'date_limite' => now()->addDays($request->delai_modification),
            'statut' => 'en_attente',
            'email_envoye' => $request->envoyer_email_modification,
            'rappels_actives' => $request->rappel_automatique
        ]);

        // Ajouter un commentaire détaillé
        $commentaireModification = "**Modifications demandées**\n\n";
        $commentaireModification .= "**Types de modifications:**\n";
        foreach ($request->modifications as $modification) {
            $commentaireModification .= "- " . ucfirst(str_replace('_', ' ', $modification)) . "\n";
        }
        $commentaireModification .= "\n**Détails:** " . $request->details_modifications;
        $commentaireModification .= "\n\n**Délai accordé:** " . $request->delai_modification . " jour(s)";
        $commentaireModification .= "\n**Date limite:** " . now()->addDays($request->delai_modification)->format('d/m/Y');

        $dossier->operations()->create([
            'type_operation' => 'commentaire',
            'user_id' => auth()->id(),
            'type' => 'demande_modification',
            'contenu' => $commentaireModification,
            'is_visible_operateur' => true
        ]);

        // Envoyer notification email si demandé
        if ($request->envoyer_email_modification && $dossier->user) {
            try {
                // TODO: Implémenter l'envoi d'email de demande de modification
                \Log::info('Email de demande modification à envoyer à: ' . $dossier->user->email);
            } catch (\Exception $e) {
                \Log::warning('Erreur envoi email modification: ' . $e->getMessage());
            }
        }

        // Programmer les rappels automatiques si activés
        if ($request->rappel_automatique) {
            // TODO: Programmer les tâches de rappel
            \Log::info('Rappels automatiques programmés pour le dossier: ' . $dossier->numero_dossier);
        }

        // Log de l'activité
        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->withProperties([
                'modifications' => $request->modifications,
                'delai_jours' => $request->delai_modification,
                'priorite' => $request->priorite_modification
            ])
            ->log('Modifications demandées');

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Demande de modification envoyée avec succès',
            'date_limite' => now()->addDays($request->delai_modification)->format('d/m/Y')
        ]);

    } catch (ValidationException $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error('Erreur DossierController@requestModification: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la demande de modification'
        ], 500);
    }
}

/**
 * Télécharge un document du dossier
 * Route: GET /admin/dossiers/{id}/documents/{documentId}/download
 */
public function downloadDocument($dossierId, $documentId)
{
    try {
        $dossier = Dossier::findOrFail($dossierId);
        $document = $dossier->documents()->findOrFail($documentId);
        
        $cheminComplet = storage_path('app/' . $document->chemin_fichier);
        
        if (!file_exists($cheminComplet)) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }
        
        // Log de l'activité de téléchargement
        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->log('Document téléchargé');
        
        return response()->download($cheminComplet, $document->nom_original);
        
    } catch (\Exception $e) {
        \Log::error('Erreur téléchargement document: ' . $e->getMessage());
        return response()->json(['error' => 'Erreur lors du téléchargement'], 500);
    }
}

/**
 * Prévisualise un document du dossier
 * Route: GET /admin/dossiers/{id}/documents/{documentId}/preview
 */
public function previewDocument($dossierId, $documentId)
{
    try {
        $dossier = Dossier::findOrFail($dossierId);
        $document = $dossier->documents()->findOrFail($documentId);
        
        $cheminComplet = storage_path('app/' . $document->chemin_fichier);
        
        if (!file_exists($cheminComplet)) {
            abort(404, 'Fichier introuvable');
        }
        
        // Log de l'activité de prévisualisation
        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->log('Document prévisualisé');
        
        return response()->file($cheminComplet, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->nom_original . '"'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erreur prévisualisation document: ' . $e->getMessage());
        abort(500, 'Erreur lors de la prévisualisation');
    }
}

/**
 * Génère un PDF du dossier complet
 * Route: GET /admin/dossiers/{id}/pdf
 */
public function generatePDF($id)
{
    try {
        $dossier = Dossier::with([
            'organisation',
            'user', 
            'documents',
            'operations.user',
            'validations.user'
        ])->findOrFail($id);
        
        if ($this->pdfService) {
            $pdfPath = $this->pdfService->generateDossierComplet($dossier);
            return response()->download($pdfPath, 'dossier_' . $dossier->numero_dossier . '.pdf');
        }
        
        return response()->json(['error' => 'Service PDF non disponible'], 503);
        
    } catch (\Exception $e) {
        \Log::error('Erreur génération PDF dossier: ' . $e->getMessage());
        return response()->json(['error' => 'Erreur lors de la génération PDF'], 500);
    }
}

/**
 * Obtient l'historique complet d'un dossier
 * Route: GET /admin/dossiers/{id}/history
 */
public function history($id)
{
    try {
        $dossier = Dossier::with([
            'operations.user',
            'validations.user',
            'modifications.user'
        ])->findOrFail($id);
        
        // Combiner tous les événements avec timestamps
        $events = collect();
        
        // Ajouter les commentaires
        foreach ($dossier->operations->where('type_operation', 'commentaire') as $comment) {
            $events->push([
                'type' => 'comment',
                'date' => $comment->created_at,
                'user' => $comment->user->name ?? 'Système',
                'action' => ucfirst($comment->type),
                'details' => $comment->contenu,
                'icon' => 'comment',
                'color' => 'info'
            ]);
        }
        
        // Ajouter les validations
        foreach ($dossier->validations as $validation) {
            $events->push([
                'type' => 'validation',
                'date' => $validation->created_at,
                'user' => $validation->user->name ?? 'Système',
                'action' => ucfirst($validation->type_validation),
                'details' => $validation->commentaire,
                'icon' => $validation->statut === 'approuve' ? 'check' : 'times',
                'color' => $validation->statut === 'approuve' ? 'success' : 'danger'
            ]);
        }
        
        // Trier par date décroissante
        $events = $events->sortByDesc('date')->values();
        
        return response()->json([
            'success' => true,
            'events' => $events
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erreur historique dossier: ' . $e->getMessage());
        return response()->json(['error' => 'Erreur lors du chargement de l\'historique'], 500);
    }
}

/**
 * Enrichit un dossier selon l'architecture SGLP réelle
 */
private function enrichDossierDataArchitecture($dossier)
{
    // Calcul de la priorité
    $dossier->priorite_calculee = $this->calculatePriorityArchitecture($dossier);
    
    // Calcul du délai d'attente
    $dossier->delai_attente = Carbon::parse($dossier->created_at)->diffInDays(now());
    
    // Indicateur de retard
    $dossier->en_retard = $dossier->delai_attente > 7;
    
    // Nombre de documents - Compter directement depuis la DB
    $dossier->nb_documents = DB::table('documents')
        ->where('dossier_id', $dossier->id)
        ->count();

    // Accès à l'utilisateur via l'organisation (architecture SGLP)
    if ($dossier->organisation && $dossier->organisation->user_id) {
        $dossier->user_organisation = User::find($dossier->organisation->user_id);
    }

    return $dossier;
}

/**
 * Calcule la priorité selon l'architecture SGLP
 */
private function calculatePriorityArchitecture($dossier)
{
    // Priorité haute si :
    // - Parti politique (toujours prioritaire)
    // - Dossier en attente depuis plus de 7 jours
    if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
        return 'haute';
    }
    
    $delai = Carbon::parse($dossier->created_at)->diffInDays(now());
    return $delai > 7 ? 'haute' : 'normale';
}

/**
 * Compte le nombre de dossiers à priorité haute
 */
private function calculateHighPriorityCountArchitecture()
{
    return Dossier::whereIn('statut', ['soumis', 'en_cours'])
        ->where(function($q) {
            $q->whereNull('assigned_to')->orWhere('statut', 'soumis');
        })
        ->where(function($q) {
            $q->where('created_at', '<=', now()->subDays(7))
              ->orWhereHas('organisation', function($org) {
                  $org->where('type', 'parti_politique');
              });
        })
        ->count();
}

/**
 * Calcule le délai moyen d'attente
 */
/**
     * Calcule le délai moyen d'attente
     */
    private function calculateAverageWaitingTimeArchitecture()
    {
        $dossiers = Dossier::whereIn('statut', ['soumis', 'en_cours'])
            ->where(function($q) {
                $q->whereNull('assigned_to')->orWhere('statut', 'soumis');
            })
            ->select('id', 'created_at')
            ->get();

        if ($dossiers->isEmpty()) {
            return 0;
        }

        $totalDelai = $dossiers->sum(function($dossier) {
            return Carbon::parse($dossier->created_at)->diffInDays(now());
        });

        return round($totalDelai / $dossiers->count(), 1);
    }

    /**
     * ==========================================
     * NOUVELLE MÉTHODE : RÉCÉPISSÉ PROVISOIRE
     * ==========================================
     */
    
    /**
     * Télécharger le récépissé provisoire PDF
     * 
     * Route: GET /admin/dossiers/{id}/download-recepisse-provisoire
     * 
     * @param int $id ID du dossier
     * @return \Illuminate\Http\Response
     */
   public function downloadRecepisseProvisoire($id)
    {
        try {
            // CORRECTION : Charger avec organisation.fondateurs au lieu de user
            $dossier = Dossier::with(['organisation.fondateurs'])->findOrFail($id);
            
            // Vérifier que le dossier peut générer un récépissé provisoire
            if (!$this->canGenerateRecepisseProvisoire($dossier)) {
                return back()->with('error', 'Le récépissé provisoire n\'est pas disponible pour ce dossier.');
            }
            
            // Vérifier que le dossier a des données supplémentaires JSON
            if (empty($dossier->donnees_supplementaires)) {
                return back()->with('error', 'Impossible de générer le récépissé : informations du déclarant manquantes.');
            }
            
            // Générer le PDF de récépissé provisoire
            $pdf = $this->pdfService->generateRecepisseProvisoire($dossier);
            
            // Nom de fichier sécurisé
            $filename = $this->sanitizeFilename("recepisse_provisoire_{$dossier->organisation->nom}_{$dossier->numero_dossier}") . "_" . now()->format('Ymd') . ".pdf";
            
            // Log de l'action avec informations du déclarant
            $declarant = json_decode($dossier->donnees_supplementaires, true)['demandeur'] ?? [];
            Log::info("Génération récépissé provisoire PDF pour dossier {$dossier->id}", [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Inconnue',
                'declarant_nom' => ($declarant['prenom'] ?? '') . ' ' . ($declarant['nom'] ?? ''),
                'declarant_nip' => $declarant['nip'] ?? 'Non renseigné',
                'user' => auth()->user()->name
            ]);

            // OPTIONNEL : Enregistrer l'activité si le système ActivityLog est disponible
            if (class_exists('\Spatie\Activitylog\Models\Activity')) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'action' => 'download_recepisse_provisoire',
                        'organisation' => $dossier->organisation->nom,
                        'declarant' => ($declarant['prenom'] ?? '') . ' ' . ($declarant['nom'] ?? '')
                    ])
                    ->log('Téléchargement récépissé provisoire');
            }
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur génération récépissé provisoire PDF: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système',
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la génération du récépissé provisoire: ' . $e->getMessage());
        }
    }

    /**
     * =======================================
     * MÉTHODES UTILITAIRES AJOUTÉES
     * =======================================
     */

    /**
     * Nettoyer le nom de fichier pour éviter les problèmes
     * 
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename($filename)
    {
        // Remplacer les caractères spéciaux par des underscores
        $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $filename);
        
        // Supprimer les underscores multiples
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Supprimer les underscores en début et fin
        return trim($filename, '_');
    }

    /**
     * Vérifier si un dossier peut avoir un récépissé provisoire
     * 
     * @param Dossier $dossier
     * @return bool
     */
    private function canGenerateRecepisseProvisoire(Dossier $dossier)
    {
        // Statuts autorisés pour le récépissé provisoire
        $statutsAutorises = ['soumis', 'en_cours', 'en_attente'];
        
        return in_array($dossier->statut, $statutsAutorises) && 
               $dossier->organisation &&
               !empty($dossier->donnees_supplementaires);
    }

    /**
     * Obtenir les actions disponibles pour un dossier
     * (Méthode mise à jour pour inclure le récépissé provisoire)
     * 
     * @param Dossier $dossier
     * @return array
     */
    private function getAvailableActionsUpdated($dossier)
    {
        $actions = [];
        
        // Accusé de réception - Toujours disponible
        $actions['accuse'] = [
            'disponible' => true,
            'libelle' => 'Accusé de réception',
            'description' => 'Document confirmant la réception du dossier',
            'couleur' => 'primary',
            'icone' => 'fas fa-file-alt'
        ];
        
        // Récépissé provisoire - Selon statut
        $actions['recepisse_provisoire'] = [
            'disponible' => $this->canGenerateRecepisseProvisoire($dossier),
            'libelle' => 'Récépissé provisoire',
            'description' => 'Document provisoire de déclaration',
            'couleur' => 'warning',
            'icone' => 'fas fa-file-signature'
        ];
        
        // Récépissé définitif - Seulement si approuvé
        $actions['recepisse_definitif'] = [
            'disponible' => $dossier->statut === 'approuve',
            'libelle' => 'Récépissé définitif',
            'description' => 'Document officiel d\'enregistrement',
            'couleur' => 'success',
            'icone' => 'fas fa-certificate'
        ];
        
        return $actions;
    }


}