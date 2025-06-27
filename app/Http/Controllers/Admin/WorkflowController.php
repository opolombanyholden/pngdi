<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\User;
use App\Models\DossierValidation;
use Carbon\Carbon;

class WorkflowController extends Controller
{
    /**
     * Page des dossiers en attente
     */
    public function enAttente(Request $request)
    {
        try {
            // Récupérer les dossiers en attente avec filtres
            $query = Dossier::with(['organisation'])
                ->whereIn('statut', ['soumis', 'en_cours'])
                ->orderBy('created_at', 'asc'); // FIFO

            // Filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('organisation', function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%");
                })->orWhere('numero_dossier', 'like', "%{$search}%");
            }

            if ($request->filled('type')) {
                $query->whereHas('organisation', function($q) use ($request) {
                    $q->where('type', $request->type);
                });
            }

            if ($request->filled('priorite')) {
                // Logique de priorité basée sur l'âge et le type
                if ($request->priorite === 'haute') {
                    $query->where(function($q) {
                        $q->where('created_at', '<=', now()->subDays(10))
                          ->orWhereHas('organisation', function($org) {
                              $org->where('type', 'parti_politique');
                          });
                    });
                }
            }

            if ($request->filled('date_debut')) {
                $query->where('created_at', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->where('created_at', '<=', $request->date_fin);
            }

            $dossiersEnAttente = $query->paginate(15);

            // Enrichir les dossiers avec les données métier
            $dossiersEnAttente->getCollection()->transform(function ($dossier) {
                return $this->enrichDossierData($dossier);
            });

            // Statistiques pour les cards
            $totalEnAttente = Dossier::whereIn('statut', ['soumis', 'en_cours'])->count();
            $prioriteHaute = Dossier::whereIn('statut', ['soumis', 'en_cours'])
                ->where(function($q) {
                    $q->where('created_at', '<=', now()->subDays(10))
                      ->orWhereHas('organisation', function($org) {
                          $org->where('type', 'parti_politique');
                      });
                })->count();
            
            $delaiMoyen = Dossier::whereIn('statut', ['soumis', 'en_cours'])
                ->get()
                ->avg(function($dossier) {
                    return now()->diffInDays($dossier->created_at);
                });

            // Agents disponibles
            $agents = User::where('role', 'agent')->get();

            return view('admin.workflow.en-attente', compact(
                'dossiersEnAttente',
                'totalEnAttente',
                'prioriteHaute',
                'delaiMoyen',
                'agents'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du chargement des dossiers en attente: ' . $e->getMessage());
        }
    }

    /**
     * Page des dossiers en cours
     */
    public function enCours(Request $request)
    {
        try {
            // Récupérer les dossiers en cours avec agent assigné
            $query = Dossier::with(['organisation', 'assignedAgent'])
                ->where('statut', 'en_cours')
                ->whereNotNull('assigned_to')
                ->orderBy('updated_at', 'desc');

            // Filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('organisation', function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%");
                })->orWhere('numero_dossier', 'like', "%{$search}%");
            }

            if ($request->filled('agent')) {
                $query->where('assigned_to', $request->agent);
            }

            if ($request->filled('type')) {
                $query->whereHas('organisation', function($q) use ($request) {
                    $q->where('type', $request->type);
                });
            }

            $dossiersEnCours = $query->paginate(15);

            // Enrichir les dossiers
            $dossiersEnCours->getCollection()->transform(function ($dossier) {
                return $this->enrichDossierData($dossier);
            });

            // Statistiques
            $totalEnCours = Dossier::where('statut', 'en_cours')->whereNotNull('assigned_to')->count();
            $delaiMoyen = $dossiersEnCours->avg(function($dossier) {
                return $dossier->assigned_to ? now()->diffInDays($dossier->updated_at) : 0;
            });
            $agentsActifs = User::where('role', 'agent')
                ->whereHas('assignedDossiers', function($q) {
                    $q->where('statut', 'en_cours');
                })->count();
            $prioriteHaute = $dossiersEnCours->where('priorite', 'haute')->count();

            // Agents pour les filtres
            $agents = User::where('role', 'agent')->get();

            return view('admin.workflow.en-cours', compact(
                'dossiersEnCours',
                'totalEnCours',
                'delaiMoyen',
                'agentsActifs',
                'prioriteHaute',
                'agents'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du chargement des dossiers en cours: ' . $e->getMessage());
        }
    }

    /**
     * Page des dossiers terminés
     */
    public function termines(Request $request)
    {
        try {
            // Récupérer les dossiers terminés
            $query = Dossier::with(['organisation'])
                ->whereIn('statut', ['approuve', 'rejete'])
                ->orderBy('validated_at', 'desc');

            // Filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('organisation', function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%");
                })->orWhere('numero_dossier', 'like', "%{$search}%");
            }

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('type')) {
                $query->whereHas('organisation', function($q) use ($request) {
                    $q->where('type', $request->type);
                });
            }

            if ($request->filled('date_debut')) {
                $query->where('validated_at', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->where('validated_at', '<=', $request->date_fin);
            }

            $dossiersTermines = $query->paginate(15);

            // Enrichir les dossiers
            $dossiersTermines->getCollection()->transform(function ($dossier) {
                return $this->enrichDossierData($dossier);
            });

            // Statistiques
            $totalTermines = Dossier::whereIn('statut', ['approuve', 'rejete'])->count();
            $approuves = Dossier::where('statut', 'approuve')->count();
            $rejetes = Dossier::where('statut', 'rejete')->count();
            $tauxApprobation = $totalTermines > 0 ? round(($approuves / $totalTermines) * 100) : 0;

            return view('admin.workflow.termines', compact(
                'dossiersTermines',
                'totalTermines',
                'approuves',
                'rejetes',
                'tauxApprobation'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du chargement des dossiers terminés: ' . $e->getMessage());
        }
    }

    /**
     * Assigner un dossier à un agent
     */
    public function assign(Request $request, Dossier $dossier)
    {
        try {
            $request->validate([
                'agent_id' => 'required|exists:users,id',
                'commentaire' => 'nullable|string|max:500'
            ]);

            $agent = User::findOrFail($request->agent_id);

            $dossier->update([
                'assigned_to' => $agent->id,
                'statut' => 'en_cours'
            ]);

            // Créer ou mettre à jour la validation
            DossierValidation::updateOrCreate([
                'dossier_id' => $dossier->id,
            ], [
                'workflow_step_id' => 1, // Valeur par défaut
                'validation_entity_id' => 1, // Valeur par défaut
                'validated_by' => null,
                'decision' => 'en_attente',
                'commentaire' => $request->commentaire,
                'assigned_at' => now(),
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

    /**
     * Valider un dossier
     */
    public function validateDossier(Request $request, Dossier $dossier)
    {
        try {
            $request->validate([
                'commentaire' => 'nullable|string|max:1000'
            ]);

            $dossier->update([
                'statut' => 'approuve',
                'validated_at' => now()
            ]);

            // Mettre à jour la validation
            $validation = DossierValidation::where('dossier_id', $dossier->id)->first();
            if ($validation) {
                $validation->update([
                    'validated_by' => auth()->id(),
                    'decision' => 'approuve',
                    'commentaire' => $request->commentaire,
                    'decided_at' => now(),
                    'duree_traitement' => $validation->assigned_at ? 
                        now()->diffInHours($validation->assigned_at) : null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dossier validé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeter un dossier
     */
    public function reject(Request $request, Dossier $dossier)
    {
        try {
            $request->validate([
                'motif' => 'required|string|max:1000'
            ]);

            $dossier->update([
                'statut' => 'rejete',
                'motif_rejet' => $request->motif
            ]);

            // Mettre à jour la validation
            $validation = DossierValidation::where('dossier_id', $dossier->id)->first();
            if ($validation) {
                $validation->update([
                    'validated_by' => auth()->id(),
                    'decision' => 'rejete',
                    'commentaire' => $request->motif,
                    'decided_at' => now(),
                    'duree_traitement' => $validation->assigned_at ? 
                        now()->diffInHours($validation->assigned_at) : null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dossier rejeté avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enrichir les données d'un dossier avec la logique métier
     */
    private function enrichDossierData($dossier)
    {
        // Calcul des jours d'attente
        $dossier->jours_attente = now()->diffInDays($dossier->created_at);
        
        // Calcul de la priorité
        $priorite = $this->calculatePriorite($dossier);
        $dossier->priorite = $priorite['niveau'];
        $dossier->priorite_color = $priorite['color'];
        
        // Progression (simple pour le moment) - Compatible PHP 7.3
        switch($dossier->statut) {
            case 'soumis':
                $dossier->progression = 25;
                break;
            case 'en_cours':
                $dossier->progression = 60;
                break;
            case 'approuve':
                $dossier->progression = 100;
                break;
            case 'rejete':
                $dossier->progression = 100;
                break;
            default:
                $dossier->progression = 0;
                break;
        }

        // Actions disponibles selon le statut
        $dossier->actions_disponibles = $this->getAvailableActions($dossier);

        return $dossier;
    }

    /**
     * Calcul de priorité intelligente
     */
    private function calculatePriorite($dossier)
    {
        $joursAttente = now()->diffInDays($dossier->created_at);
        
        // Parti politique = toujours haute priorité
        if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
            return ['niveau' => 'haute', 'color' => 'red'];
        }
        
        // Ancienneté
        if ($joursAttente > 10) {
            return ['niveau' => 'haute', 'color' => 'red'];
        } elseif ($joursAttente > 5) {
            return ['niveau' => 'moyenne', 'color' => 'yellow'];
        } else {
            return ['niveau' => 'normale', 'color' => 'green'];
        }
    }

    /**
     * Actions disponibles selon le statut du dossier
     */
    private function getAvailableActions($dossier)
    {
        switch ($dossier->statut) {
            case 'soumis':
                return ['assigner'];
            case 'en_cours':
                return ['valider', 'rejeter', 'reassigner'];
            case 'approuve':
            case 'rejete':
                return ['consulter'];
            default:
                return [];
        }
    }
}