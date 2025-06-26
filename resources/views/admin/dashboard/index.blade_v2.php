@extends('layouts.admin')

@section('title', 'Tableau de Bord Administration')

@section('breadcrumb')
<li class="breadcrumb-item active">Tableau de Bord</li>
@endsection

@section('content')
<div class="admin-dashboard">
    <!-- En-tête avec salutation et actions rapides -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="dashboard-title">
                    <i class="fas fa-tachometer-alt text-primary"></i>
                    Tableau de Bord Administration
                </h1>
                <p class="dashboard-subtitle text-muted">
                    Bienvenue, {{ auth()->user()->name }} • {{ now()->format('l j F Y') }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="quick-actions">
                    <button class="btn btn-gabon-green btn-sm me-2" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                    <div class="dropdown d-inline">
                        <button class="btn btn-gabon-blue dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-plus"></i> Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.users.create') }}">
                                <i class="fas fa-user-plus"></i> Nouvel Agent
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.reports.generate') }}">
                                <i class="fas fa-chart-bar"></i> Générer Rapport
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings') }}">
                                <i class="fas fa-cog"></i> Paramètres
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes statistiques principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card gabon-green">
                <div class="stats-card-body">
                    <div class="stats-content">
                        <div class="stats-number">{{ number_format($stats['total_organisations']) }}</div>
                        <div class="stats-label">Organisations Totales</div>
                        <div class="stats-trend">
                            <i class="fas fa-arrow-up"></i> +{{ $stats['approved_today'] }} aujourd'hui
                        </div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card gabon-yellow">
                <div class="stats-card-body">
                    <div class="stats-content">
                        <div class="stats-number">{{ number_format($stats['pending_review']) }}</div>
                        <div class="stats-label">En Attente</div>
                        <div class="stats-trend">
                            <i class="fas fa-clock"></i> Priorité haute
                        </div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card gabon-blue">
                <div class="stats-card-body">
                    <div class="stats-content">
                        <div class="stats-number">{{ number_format($stats['in_progress']) }}</div>
                        <div class="stats-label">En Cours</div>
                        <div class="stats-trend">
                            <i class="fas fa-spinner fa-spin"></i> Traitement actif
                        </div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card gabon-red">
                <div class="stats-card-body">
                    <div class="stats-content">
                        <div class="stats-number">{{ number_format($performanceMetrics['realise_mensuel']) }}%</div>
                        <div class="stats-label">Objectif Mensuel</div>
                        <div class="stats-trend">
                            <i class="fas fa-target"></i> {{ $performanceMetrics['objectif_mensuel'] }} visés
                        </div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métriques de performance -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gabon-green text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i>
                        Métriques de Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="metric-item">
                                <div class="metric-value text-primary">{{ round($performanceMetrics['temps_moyen_traitement'], 1) }}</div>
                                <div class="metric-label">Jours Moyen Traitement</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="metric-item">
                                <div class="metric-value text-success">{{ $performanceMetrics['taux_approbation'] }}%</div>
                                <div class="metric-label">Taux d'Approbation</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="metric-item">
                                <div class="metric-value text-info">{{ $performanceMetrics['dossiers_traites_semaine'] }}</div>
                                <div class="metric-label">Traités cette Semaine</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="metric-item">
                                <div class="metric-value text-warning">{{ $performanceMetrics['satisfaction_moyenne'] }}/5</div>
                                <div class="metric-label">Satisfaction Moyenne</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gabon-blue text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie"></i>
                        Répartition par Statut
                    </h5>
                </div>
                <div class="card-body">
                    <div class="status-distribution">
                        @foreach($statusDistribution as $statut => $count)
                        <div class="status-item d-flex justify-content-between align-items-center mb-2">
                            <div class="status-info">
                                @php
                                    $colors = [
                                        'brouillon' => 'secondary',
                                        'soumis' => 'info', 
                                        'en_validation' => 'warning',
                                        'approuve' => 'success',
                                        'rejete' => 'danger',
                                        'suspendu' => 'dark',
                                        'radie' => 'danger'
                                    ];
                                    $labels = [
                                        'brouillon' => 'Brouillon',
                                        'soumis' => 'Soumis',
                                        'en_validation' => 'En validation', 
                                        'approuve' => 'Approuvé',
                                        'rejete' => 'Rejeté',
                                        'suspendu' => 'Suspendu',
                                        'radie' => 'Radié'
                                    ];
                                @endphp
                                <span class="status-badge badge bg-{{ $colors[$statut] ?? 'secondary' }}">
                                    {{ $labels[$statut] ?? ucfirst($statut) }}
                                </span>
                            </div>
                            <div class="status-count">
                                <strong>{{ $count }}</strong>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dossiers en attente prioritaires -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gabon-yellow text-dark d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Dossiers Prioritaires ({{ $pendingDossiers->count() }})
                    </h5>
                    <a href="{{ route('admin.dossiers.pending') }}" class="btn btn-sm btn-dark">Voir Tous</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Organisation</th>
                                    <th>Type</th>
                                    <th>Opérateur</th>
                                    <th>Attente</th>
                                    <th>Priorité</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingDossiers as $dossier)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-primary me-2"></i>
                                            <div>
                                                <div class="fw-bold">{{ Str::limit($dossier['nom'], 30) }}</div>
                                                <small class="text-muted">ID: #{{ $dossier['id'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $dossier['type'] }}</span>
                                    </td>
                                    <td>{{ $dossier['operateur'] }}</td>
                                    <td>
                                        <span class="text-{{ $dossier['jours_attente'] > 7 ? 'danger' : ($dossier['jours_attente'] > 3 ? 'warning' : 'success') }}">
                                            {{ $dossier['jours_attente'] }} jour{{ $dossier['jours_attente'] > 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="priority-badge priority-{{ $dossier['priorite'] }}">
                                            {{ ucfirst($dossier['priorite']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.dossiers.show', $dossier['id']) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.dossiers.assign', $dossier['id']) }}" 
                                               class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-user-check"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                        Aucun dossier en attente prioritaire
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agents les plus actifs -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gabon-green text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users"></i>
                        Top Agents du Mois
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($topAgents as $index => $agent)
                    <div class="agent-item d-flex align-items-center mb-3">
                        <div class="agent-rank me-3">
                            <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'primary') }} rounded-pill">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        <div class="agent-avatar me-3">
                            <div class="avatar-circle bg-gabon-blue text-white">
                                {{ substr($agent['nom'], 0, 2) }}
                            </div>
                        </div>
                        <div class="agent-info flex-grow-1">
                            <div class="agent-name fw-bold">{{ $agent['nom'] }}</div>
                            <div class="agent-stats small text-muted">
                                {{ $agent['dossiers_traites'] }} dossiers traités
                                <span class="status-indicator ms-2 {{ $agent['statut'] === 'en_ligne' ? 'online' : 'offline' }}"></span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted">
                        <i class="fas fa-user-slash fa-2x mb-2"></i><br>
                        Aucune activité ce mois
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i>
                        Activité Récente
                    </h5>
                    <div class="activity-filters">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" data-filter="all">Toutes</button>
                            <button class="btn btn-outline-secondary" data-filter="organisations">Organisations</button>
                            <button class="btn btn-outline-secondary" data-filter="users">Utilisateurs</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        @forelse($recentActivity as $activity)
                        <div class="activity-item" data-type="{{ $activity['type'] }}">
                            <div class="activity-icon bg-{{ $activity['color'] }}">
                                <i class="{{ $activity['icon'] }}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-message">{{ $activity['message'] }}</div>
                                <div class="activity-time text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ $activity['date']->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            Aucune activité récente
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détails rapides -->
<div class="modal fade" id="quickDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gabon-blue text-white">
                <h5 class="modal-title">Détails Rapides</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickDetailsContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>
@endsection