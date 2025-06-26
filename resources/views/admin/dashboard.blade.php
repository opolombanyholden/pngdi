@extends('layouts.admin')

@section('title', 'Dashboard Administration')

@section('content')
<div class="container-fluid px-4 py-3">
    <h1>
        <i class="fas fa-tachometer-alt mr-3 text-success"></i>
        Tableau de Bord Administration PNGDI
    </h1>
    <p class="text-muted mb-4">Supervision et gestion des organisations</p>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #009e3f;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h3 mb-1 text-success">247</div>
                            <div class="text-muted small">TOTAL ORGANISATIONS</div>
                        </div>
                        <div class="p-3 rounded" style="background-color: rgba(0, 158, 63, 0.1);">
                            <i class="fas fa-building fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffcd00;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h3 mb-1 text-warning">23</div>
                            <div class="text-muted small">EN VALIDATION</div>
                        </div>
                        <div class="p-3 rounded" style="background-color: rgba(255, 205, 0, 0.1);">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #003f7f;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h3 mb-1 text-primary">15</div>
                            <div class="text-muted small">EN COURS</div>
                        </div>
                        <div class="p-3 rounded" style="background-color: rgba(0, 63, 127, 0.1);">
                            <i class="fas fa-sync-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #8b1538;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h3 mb-1 text-danger">189</div>
                            <div class="text-muted small">APPROUVÉES</div>
                        </div>
                        <div class="p-3 rounded" style="background-color: rgba(139, 21, 56, 0.1);">
                            <i class="fas fa-check-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: rgba(0, 158, 63, 0.1);">
                        <i class="fas fa-plus fa-lg text-success"></i>
                    </div>
                    <h6>Nouveau Dossier</h6>
                    <p class="small text-muted">Créer un nouveau dossier</p>
                    <button class="btn btn-outline-success btn-sm">Créer</button>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: rgba(0, 63, 127, 0.1);">
                        <i class="fas fa-users fa-lg text-primary"></i>
                    </div>
                    <h6>Gestion Agents</h6>
                    <p class="small text-muted">Gérer les agents</p>
                    <button class="btn btn-outline-primary btn-sm">Gérer</button>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: rgba(255, 205, 0, 0.1);">
                        <i class="fas fa-chart-bar fa-lg text-warning"></i>
                    </div>
                    <h6>Rapports</h6>
                    <p class="small text-muted">Voir les statistiques</p>
                    <button class="btn btn-outline-warning btn-sm">Voir</button>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: rgba(23, 162, 184, 0.1);">
                        <i class="fas fa-cog fa-lg text-info"></i>
                    </div>
                    <h6>Paramètres</h6>
                    <p class="small text-muted">Configuration</p>
                    <button class="btn btn-outline-info btn-sm">Config</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area mr-2 text-success"></i>
                        Évolution Mensuelle
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-line fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h6 class="text-muted">Graphique des soumissions</h6>
                    <small class="text-muted">Chart.js à intégrer</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bell mr-2 text-primary"></i>
                        Activité Récente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 35px; height: 35px;">
                            <i class="fas fa-plus text-white fa-sm"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold small">Nouvelle organisation</div>
                            <div class="text-muted small">Association Jeunesse</div>
                            <div class="text-muted small">5 min</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 35px; height: 35px;">
                            <i class="fas fa-check text-white fa-sm"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold small">Dossier approuvé</div>
                            <div class="text-muted small">ONG Environnement</div>
                            <div class="text-muted small">15 min</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 35px; height: 35px;">
                            <i class="fas fa-user text-dark fa-sm"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold small">Agent connecté</div>
                            <div class="text-muted small">Marie NZENG</div>
                            <div class="text-muted small">30 min</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="#" class="btn btn-outline-primary btn-sm">Voir tout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dossiers prioritaires -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open mr-2 text-warning"></i>
                        Dossiers Prioritaires
                    </h5>
                    <a href="#" class="btn btn-outline-success btn-sm">Voir tous</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th class="border-0">Organisation</th>
                                    <th class="border-0">Type</th>
                                    <th class="border-0">Statut</th>
                                    <th class="border-0">Priorité</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>
                                            <div class="font-weight-bold">Association Jeunesse Libreville</div>
                                            <small class="text-muted">ASSOC-2025-001</small>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-info">Association</span></td>
                                    <td><span class="badge badge-warning">En Attente</span></td>
                                    <td><span class="text-danger"><i class="fas fa-circle mr-1"></i>Haute</span></td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm mr-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <div class="font-weight-bold">ONG Environnement</div>
                                            <small class="text-muted">ONG-2025-003</small>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-success">ONG</span></td>
                                    <td><span class="badge badge-primary">En Cours</span></td>
                                    <td><span class="text-warning"><i class="fas fa-circle mr-1"></i>Moyenne</span></td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm mr-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endsection