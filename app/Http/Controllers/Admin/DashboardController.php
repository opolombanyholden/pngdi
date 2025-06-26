<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques principales
        $stats = [
            'total_organisations' => Organisation::count(),
            'pending_review' => Organisation::whereIn('statut', ['soumis', 'en_validation'])->count(),
            'in_progress' => Organisation::where('statut', 'en_validation')->count(),
            'approved_today' => Organisation::where('statut', 'approuve')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        // Métriques de performance
        $performanceMetrics = [
            'temps_moyen_traitement' => $this->calculateAverageProcessingTime(),
            'taux_approbation' => $this->calculateApprovalRate(),
            'dossiers_traites_semaine' => Organisation::where('statut', 'approuve')
                ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'satisfaction_moyenne' => 4.2, // Valeur temporaire
            'realise_mensuel' => $this->calculateMonthlyRealization(),
            'objectif_mensuel' => 100, // Objectif fixe temporaire
        ];

        // Répartition par statut
        $statusDistribution = Organisation::selectRaw('statut, COUNT(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut')
            ->toArray();

        // Dossiers prioritaires (simulation)
        $pendingDossiers = collect([
            [
                'id' => 1,
                'nom' => 'Association Culturelle Fang',
                'type' => 'Associative',
                'operateur' => 'Jean NGUEMA',
                'jours_attente' => 8,
                'priorite' => 'haute'
            ],
            [
                'id' => 2,
                'nom' => 'Église Évangélique du Gabon',
                'type' => 'Religieuse',
                'operateur' => 'Marie OBAME',
                'jours_attente' => 5,
                'priorite' => 'moyenne'
            ],
            [
                'id' => 3,
                'nom' => 'Parti Démocratique Gabonais Local',
                'type' => 'Politique',
                'operateur' => 'Paul NDONG',
                'jours_attente' => 12,
                'priorite' => 'haute'
            ]
        ]);

        // Top agents (simulation)
        $topAgents = [
            [
                'nom' => 'Jean NGUEMA',
                'dossiers_traites' => 28,
                'statut' => 'en_ligne'
            ],
            [
                'nom' => 'Marie OBAME',
                'dossiers_traites' => 24,
                'statut' => 'en_ligne'
            ],
            [
                'nom' => 'Paul NDONG',
                'dossiers_traites' => 19,
                'statut' => 'offline'
            ]
        ];

        // Activité récente (simulation)
        $recentActivity = collect([
            [
                'type' => 'organisation',
                'message' => 'Nouvelle organisation "Association des Jeunes Entrepreneurs" soumise',
                'date' => now()->subMinutes(15),
                'icon' => 'fas fa-plus-circle',
                'color' => 'success'
            ],
            [
                'type' => 'user',
                'message' => 'Agent Marie OBAME a approuvé 3 dossiers',
                'date' => now()->subHour(),
                'icon' => 'fas fa-check-circle',
                'color' => 'primary'
            ],
            [
                'type' => 'organisation',
                'message' => 'Organisation "Église Baptiste" a été approuvée',
                'date' => now()->subHours(2),
                'icon' => 'fas fa-building',
                'color' => 'info'
            ]
        ]);

        return view('admin.dashboard.index', compact(
            'stats', 
            'performanceMetrics', 
            'statusDistribution', 
            'pendingDossiers', 
            'topAgents', 
            'recentActivity'
        ));
    }

    public function getStatsApi()
    {
        $stats = [
            'total_organisations' => Organisation::count(),
            'pending_review' => Organisation::whereIn('statut', ['soumis', 'en_validation'])->count(),
            'in_progress' => Organisation::where('statut', 'en_validation')->count(),
            'approved_today' => Organisation::where('statut', 'approuve')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        $performance = [
            'temps_moyen_traitement' => $this->calculateAverageProcessingTime(),
            'taux_approbation' => $this->calculateApprovalRate(),
            'dossiers_traites_semaine' => Organisation::where('statut', 'approuve')
                ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'satisfaction_moyenne' => 4.2,
        ];

        $distribution = Organisation::selectRaw('statut, COUNT(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut')
            ->toArray();

        return response()->json([
            'stats' => $stats,
            'performance' => $performance,
            'distribution' => $distribution,
            'timestamp' => now()
        ]);
    }

    public function getActivityFeed()
    {
        // Simulation du feed d'activité en temps réel
        $activities = [
            [
                'type' => 'organisation',
                'message' => 'Nouvelle organisation soumise',
                'date' => now()->subMinutes(rand(1, 60)),
                'icon' => 'fas fa-plus-circle',
                'color' => 'success'
            ],
            [
                'type' => 'user',
                'message' => 'Agent en ligne',
                'date' => now()->subMinutes(rand(1, 30)),
                'icon' => 'fas fa-user-check',
                'color' => 'primary'
            ]
        ];

        return response()->json($activities);
    }

    private function calculateAverageProcessingTime()
    {
        // Calcul simplifié du temps moyen de traitement
        $approvedOrgs = Organisation::where('statut', 'approuve')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($approvedOrgs->isEmpty()) {
            return 0;
        }

        $totalDays = $approvedOrgs->sum(function ($org) {
            return $org->created_at->diffInDays($org->updated_at);
        });

        return round($totalDays / $approvedOrgs->count(), 1);
    }

    private function calculateApprovalRate()
    {
        $total = Organisation::whereIn('statut', ['approuve', 'rejete'])->count();
        
        if ($total === 0) {
            return 0;
        }

        $approved = Organisation::where('statut', 'approuve')->count();
        
        return round(($approved / $total) * 100);
    }

    private function calculateMonthlyRealization()
    {
        $monthlyTarget = 100; // Objectif mensuel fixe
        $approved = Organisation::where('statut', 'approuve')
            ->whereYear('updated_at', now()->year)
            ->whereMonth('updated_at', now()->month)
            ->count();

        return round(($approved / $monthlyTarget) * 100);
    }
}