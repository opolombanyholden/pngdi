<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Dashboard analytics
     */
    public function index()
    {
        $data = [
            'title' => 'Analytics & Statistiques Avancées',
            'organisations_count' => Organisation::count(),
            'users_count' => User::count(),
            'pending_count' => Organisation::whereIn('statut', ['soumis', 'en_validation'])->count(),
            'approved_count' => Organisation::where('statut', 'approuve')->count(),
            'growth_rate' => $this->calculateGrowthRate(),
            'charts_data' => $this->getChartsData()
        ];

        return view('admin.analytics.index', $data);
    }

    private function calculateGrowthRate()
    {
        $thisMonth = Organisation::whereMonth('created_at', now()->month)->count();
        $lastMonth = Organisation::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) return 100;
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function getChartsData()
    {
        return [
            'monthly_registrations' => Organisation::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('month')
                ->pluck('count', 'month'),
            'status_distribution' => Organisation::selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut')
        ];
    }
}
