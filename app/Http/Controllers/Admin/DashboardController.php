<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\Dossier;
use App\Models\User;
use App\Models\DossierValidation;
use App\Models\Declaration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Page principale du dashboard admin avec données dynamiques
     */
    public function index()
    {
        try {
            // Récupérer les statistiques principales
            $stats = $this->getMainStatistics();
            
            // Récupérer les métriques de performance
            $performance = $this->getPerformanceMetrics();
            
            // Récupérer les dossiers prioritaires
            $priorityDossiers = $this->getPriorityDossiers();
            
            // Récupérer les top agents
            $topAgents = $this->getTopAgents();
            
            // Récupérer l'activité récente
            $recentActivity = $this->getRecentActivity();
            
            // Récupérer les données pour graphiques
            $chartData = $this->getChartData();
            
            return view('admin.dashboard', compact(
                'stats',
                'performance', 
                'priorityDossiers',
                'topAgents',
                'recentActivity',
                'chartData'
            ));
            
        } catch (\Exception $e) {
            // En cas d'erreur, retourner des données de fallback
            return view('admin.dashboard', [
                'stats' => $this->getFallbackStats(),
                'performance' => $this->getFallbackPerformance(),
                'priorityDossiers' => collect(),
                'topAgents' => collect(),
                'recentActivity' => collect(),
                'chartData' => $this->getFallbackChartData(),
                'error' => 'Erreur lors du chargement des données: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Statistiques principales dynamiques
     */
    private function getMainStatistics()
    {
        $totalOrganisations = Organisation::count();
        $totalDossiers = Dossier::count();
        $dossiersEnAttente = Dossier::whereIn('statut', ['soumis', 'en_cours'])->count();
        $dossiersApprouves = Dossier::where('statut', 'approuve')->count();
        $newOrganisationsWeek = Organisation::where('created_at', '>=', now()->subWeek())->count();
        $totalAdherents = \DB::table('adherents')->count();
        $declarationsMonth = Declaration::where('created_at', '>=', now()->subMonth())->count();

        return (object) [
            'total_organisations' => $totalOrganisations,
            'total_dossiers' => $totalDossiers,
            'pending_review' => $dossiersEnAttente,
            'approved_today' => $dossiersApprouves,
            'new_organisations_week' => $newOrganisationsWeek,
            'total_adherents' => $totalAdherents,
            'declarations_month' => $declarationsMonth
        ];
    }

    /**
     * Métriques de performance dynamiques
     */
    private function getPerformanceMetrics()
    {
        // Calcul du temps moyen de traitement
        $avgTraitement = DossierValidation::whereNotNull('duree_traitement')
            ->avg('duree_traitement') ?? 0;
        $tempsTraitement = round($avgTraitement / 24, 1); // Convertir heures en jours

        // Calcul du taux d'approbation
        $totalTraites = Dossier::whereIn('statut', ['approuve', 'rejete'])->count();
        $approuves = Dossier::where('statut', 'approuve')->count();
        $tauxApprobation = $totalTraites > 0 ? round(($approuves / $totalTraites) * 100) : 0;

        // Dossiers traités cette semaine
        $dossiersTraitesSemaine = DossierValidation::where('decided_at', '>=', now()->subWeek())
            ->whereNotNull('decided_at')
            ->count();

        // Score de satisfaction (formule simple)
        $satisfaction = min(100, ($tauxApprobation + (100 - min($tempsTraitement * 10, 50))) / 2);

        // Agents actifs (connectés dans les 7 derniers jours)
        $agentsActifs = User::where('role', 'agent')
            ->where('last_login_at', '>=', now()->subDays(7))
            ->count();

        return (object) [
            'temps_moyen_traitement' => $tempsTraitement,
            'taux_approbation' => $tauxApprobation,
            'dossiers_traites_semaine' => $dossiersTraitesSemaine,
            'satisfaction_moyenne' => round($satisfaction),
            'realise_mensuel' => $dossiersTraitesSemaine * 4, // Estimation mensuelle
            'objectif_mensuel' => 100,
            'agents_actifs' => $agentsActifs
        ];
    }

    /**
     * Dossiers prioritaires avec enrichissement
     */
    private function getPriorityDossiers()
    {
        return Dossier::with(['organisation'])
            ->whereIn('statut', ['soumis', 'en_cours'])
            ->get()
            ->map(function ($dossier) {
                return $this->enrichDossierData($dossier);
            })
            ->sortByDesc('priorite_score')
            ->take(5);
    }

    /**
     * Top agents du mois
     */
    private function getTopAgents()
    {
        return User::where('role', 'agent')
            ->withCount([
                'dossierValidations as validations_count' => function($query) {
                    $query->where('created_at', '>=', now()->subMonth())
                          ->whereNotNull('decided_at');
                }
            ])
            ->having('validations_count', '>', 0)
            ->orderByDesc('validations_count')
            ->take(5)
            ->get()
            ->map(function ($agent) {
                $agent->performance_score = min(100, $agent->validations_count * 10);
                return $agent;
            });
    }

    /**
     * Activité récente (fusion de plusieurs sources)
     */
    private function getRecentActivity()
    {
        $activities = collect();

        // Nouvelles organisations
        $newOrgs = Organisation::where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($org) {
                return (object) [
                    'type' => 'organisation',
                    'title' => 'Nouvelle organisation',
                    'description' => $org->nom,
                    'time' => $org->created_at,
                    'icon' => 'building',
                    'color' => 'blue'
                ];
            });

        // Validations récentes
        $recentValidations = DossierValidation::with(['dossier.organisation'])
            ->whereNotNull('decided_at')
            ->where('decided_at', '>=', now()->subDays(7))
            ->latest('decided_at')
            ->take(3)
            ->get()
            ->map(function ($validation) {
                return (object) [
                    'type' => 'validation',
                    'title' => ucfirst($validation->decision),
                    'description' => 'Dossier ' . $validation->dossier->numero_dossier,
                    'time' => $validation->decided_at,
                    'icon' => $validation->decision === 'approuve' ? 'check-circle' : 'x-circle',
                    'color' => $validation->decision === 'approuve' ? 'green' : 'red'
                ];
            });

        return $activities->merge($newOrgs)->merge($recentValidations)
            ->sortByDesc('time')
            ->take(6);
    }

    /**
     * Données pour graphiques (6 derniers mois)
     */
    private function getChartData()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $organisations = Organisation::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $dossiers = Dossier::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $validations = DossierValidation::whereBetween('decided_at', [$startOfMonth, $endOfMonth])
                ->whereNotNull('decided_at')
                ->count();

            $data[] = [
                'month' => $date->format('M Y'),
                'organisations' => $organisations,
                'dossiers' => $dossiers,
                'validations' => $validations
            ];
        }

        return $data;
    }

    /**
     * Enrichissement des données dossier avec logique métier
     */
    private function enrichDossierData($dossier)
    {
        $joursAttente = now()->diffInDays($dossier->created_at);
        
        // Calcul priorité intelligente
        $priorite = $this->calculatePriorite($dossier, $joursAttente);
        
        $dossier->jours_attente = $joursAttente;
        $dossier->priorite = $priorite['niveau'];
        $dossier->priorite_score = $priorite['score'];
        $dossier->priorite_color = $priorite['color'];
        
        return $dossier;
    }

    /**
     * Calcul de priorité intelligente
     */
    private function calculatePriorite($dossier, $joursAttente)
    {
        $score = 0;
        
        // Partis politiques = priorité haute
        if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
            $score += 50;
        }
        
        // Ancienneté
        if ($joursAttente > 10) {
            $score += 30;
        } elseif ($joursAttente > 5) {
            $score += 15;
        }
        
        // Type opération
        if ($dossier->type_operation === 'creation') {
            $score += 10;
        }

        if ($score >= 50) {
            return ['niveau' => 'haute', 'score' => $score, 'color' => 'red'];
        } elseif ($score >= 25) {
            return ['niveau' => 'moyenne', 'score' => $score, 'color' => 'yellow'];
        } else {
            return ['niveau' => 'normale', 'score' => $score, 'color' => 'green'];
        }
    }

    /**
     * API - Statistiques JSON
     */
    public function getStatsApi()
    {
        try {
            $stats = $this->getMainStatistics();
            $performance = $this->getPerformanceMetrics();

            return response()->json([
                'stats' => $stats,
                'performance' => $performance,
                'distribution' => [
                    'soumis' => Dossier::where('statut', 'soumis')->count(),
                    'en_validation' => Dossier::where('statut', 'en_cours')->count(),
                    'approuve' => Dossier::where('statut', 'approuve')->count(),
                ],
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API - Feed d'activité JSON  
     */
    public function getActivityFeed()
    {
        try {
            $activity = $this->getRecentActivity();
            return response()->json($activity);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API - Données graphiques JSON
     */
    public function getChartDataApi()
    {
        try {
            $chartData = $this->getChartData();
            return response()->json($chartData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API - Statut des agents JSON
     */
    public function getAgentsStatus()
    {
        try {
            $agents = User::where('role', 'agent')
                ->withCount(['dossierValidations as charge_actuelle' => function($query) {
                    $query->where('decision', 'en_attente');
                }])
                ->get()
                ->map(function ($agent) {
                    $agent->status = $agent->last_login_at && $agent->last_login_at->gt(now()->subHours(2)) ? 'en_ligne' : 'hors_ligne';
                    $agent->disponibilite = $agent->charge_actuelle < 5 ? 'disponible' : 'charge';
                    return $agent;
                });

            return response()->json($agents);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API - Dossiers prioritaires JSON
     */
    public function getPriorityDossiersApi()
    {
        try {
            $dossiers = $this->getPriorityDossiers();
            return response()->json($dossiers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API - Métriques de performance JSON
     */
    public function getPerformanceMetricsApi()
    {
        try {
            $metrics = $this->getPerformanceMetrics();
            return response()->json($metrics);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Données de fallback en cas d'erreur
     */
    private function getFallbackStats()
    {
        return (object) [
            'total_organisations' => 0,
            'total_dossiers' => 0,
            'pending_review' => 0,
            'approved_today' => 0,
            'new_organisations_week' => 0,
            'total_adherents' => 0,
            'declarations_month' => 0
        ];
    }

    private function getFallbackPerformance()
    {
        return (object) [
            'temps_moyen_traitement' => 0,
            'taux_approbation' => 0,
            'dossiers_traites_semaine' => 0,
            'satisfaction_moyenne' => 0,
            'realise_mensuel' => 0,
            'objectif_mensuel' => 100,
            'agents_actifs' => 0
        ];
    }

    private function getFallbackChartData()
    {
        return [];
    }
}