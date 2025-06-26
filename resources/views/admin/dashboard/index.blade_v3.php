@extends('layouts.admin')

@section('title', 'Dashboard Administration')

@section('content')
<div class="container-fluid">
    <!-- Header avec statistiques principales -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Tableau de Bord Administration
                            </h2>
                            <p class="mb-0" style="opacity: 0.9;">Supervision et gestion des organisations - Plateforme Numérique Gabonaise</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-light btn-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Nouveau Dossier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Cards Principales -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">247</h3>
                            <p class="mb-0 small" style="opacity: 0.9;">Total Organisations</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 75%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">23</h3>
                            <p class="mb-0 small">En Validation</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: 60%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">15</h3>
                            <p class="mb-0 small" style="opacity: 0.9;">En Cours</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-sync-alt fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 85%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">189</h3>
                            <p class="mb-0 small" style="opacity: 0.9;">Approuvées</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 95%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métriques Performance -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-stopwatch fa-3x text-warning"></i>
                    </div>
                    <h4 class="mb-1">4.2 jours</h4>
                    <p class="text-muted mb-0">Temps Moyen Traitement</p>
                    <small class="text-success">
                        <i class="fas fa-arrow-down mr-1"></i>-15% ce mois
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-chart-pie fa-3x text-success"></i>
                    </div>
                    <h4 class="mb-1">87.5%</h4>
                    <p class="text-muted mb-0">Taux d'Approbation</p>
                    <small class="text-success">
                        <i class="fas fa-arrow-up mr-1"></i>+3% ce mois
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h4 class="mb-1">12</h4>
                    <p class="text-muted mb-0">Agents Actifs</p>
                    <small class="text-info">
                        <i class="fas fa-circle mr-1"></i>En ligne maintenant
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-star fa-3x text-warning"></i>
                    </div>
                    <h4 class="mb-1">4.8/5</h4>
                    <p class="text-muted mb-0">Satisfaction Moyenne</p>
                    <small class="text-success">
                        <i class="fas fa-thumbs-up mr-1"></i>Excellent
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides et Graphiques -->
    <div class="row mb-4">
        <!-- Actions Rapides -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt mr-2 text-warning"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="action-card text-center p-3 border rounded">
                                <div class="action-icon mb-2" style="background: rgba(0, 158, 63, 0.1);">
                                    <i class="fas fa-plus fa-2x text-success"></i>
                                </div>
                                <h6 class="mb-1">Nouveau Dossier</h6>
                                <small class="text-muted">Créer une organisation</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="action-card text-center p-3 border rounded">
                                <div class="action-icon mb-2" style="background: rgba(0, 63, 127, 0.1);">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                                <h6 class="mb-1">Gestion Agents</h6>
                                <small class="text-muted">Gérer les utilisateurs</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="action-card text-center p-3 border rounded">
                                <div class="action-icon mb-2" style="background: rgba(255, 205, 0, 0.1);">
                                    <i class="fas fa-chart-bar fa-2x text-warning"></i>
                                </div>
                                <h6 class="mb-1">Rapports</h6>
                                <small class="text-muted">Statistiques avancées</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="action-card text-center p-3 border rounded">
                                <div class="action-icon mb-2" style="background: rgba(139, 21, 56, 0.1);">
                                    <i class="fas fa-cog fa-2x text-danger"></i>
                                </div>
                                <h6 class="mb-1">Paramètres</h6>
                                <small class="text-muted">Configuration système</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique Evolution -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area mr-2 text-success"></i>
                            Évolution Mensuelle des Soumissions
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success active">6M</button>
                            <button class="btn btn-outline-success">1A</button>
                            <button class="btn btn-outline-success">Tout</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-placeholder text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-chart-line fa-5x text-muted" style="opacity: 0.3;"></i>
                        </div>
                        <h6 class="text-muted">Graphique des soumissions par mois</h6>
                        <small class="text-muted">Intégration Chart.js prévue</small>
                        <div class="mt-3">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="text-success">
                                        <strong>+24%</strong>
                                        <br><small>Jan</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-warning">
                                        <strong>+18%</strong>
                                        <br><small>Fév</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-primary">
                                        <strong>+31%</strong>
                                        <br><small>Mar</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-info">
                                        <strong>+15%</strong>
                                        <br><small>Avr</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité Récente et Dossiers Prioritaires -->
    <div class="row">
        <!-- Activité Récente -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-bell mr-2 text-primary"></i>
                        Activité Récente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-item d-flex align-items-center mb-3 p-2 rounded" style="background: rgba(0, 158, 63, 0.05);">
                        <div class="activity-icon mr-3">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-plus text-white fa-sm"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-weight-bold small">Nouvelle organisation</div>
                            <div class="text-muted small">Association Jeunesse Sportive</div>
                            <div class="text-muted small">Il y a 5 minutes</div>
                        </div>
                    </div>

                    <div class="activity-item d-flex align-items-center mb-3 p-2 rounded" style="background: rgba(0, 63, 127, 0.05);">
                        <div class="activity-icon mr-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-check text-white fa-sm"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-weight-bold small">Dossier approuvé</div>
                            <div class="text-muted small">ONG Développement Durable</div>
                            <div class="text-muted small">Il y a 15 minutes</div>
                        </div>
                    </div>

                    <div class="activity-item d-flex align-items-center mb-3 p-2 rounded" style="background: rgba(255, 205, 0, 0.05);">
                        <div class="activity-icon mr-3">
                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user text-dark fa-sm"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-weight-bold small">Nouvel agent connecté</div>
                            <div class="text-muted small">Marie NZENG - Estuaire</div>
                            <div class="text-muted small">Il y a 32 minutes</div>
                        </div>
                    </div>

                    <div class="activity-item d-flex align-items-center mb-3 p-2 rounded" style="background: rgba(139, 21, 56, 0.05);">
                        <div class="activity-icon mr-3">
                            <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-exclamation-triangle text-white fa-sm"></i>
                            </div>
                        </div>
                        <div>
                            <div class="font-weight-bold small">Dossier urgent</div>
                            <div class="text-muted small">Délai expiré - Action requise</div>
                            <div class="text-muted small">Il y a 1 heure</div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-outline-primary btn-sm">Voir toute l'activité</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dossiers Prioritaires -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-folder-open mr-2 text-warning"></i>
                            Dossiers Prioritaires
                        </h5>
                        <a href="#" class="btn btn-outline-success btn-sm">Voir tous</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th class="border-0">Organisation</th>
                                    <th class="border-0">Type</th>
                                    <th class="border-0">Statut</th>
                                    <th class="border-0">Agent</th>
                                    <th class="border-0">Priorité</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="dossier-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="dossier-icon mr-3">
                                                <i class="fas fa-building fa-lg text-success"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Association Jeunesse Libreville</div>
                                                <small class="text-muted">ASSOC-2025-001</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-info">Association</span></td>
                                    <td><span class="badge badge-warning">En Attente</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success rounded-circle mr-2" style="width: 8px; height: 8px;"></div>
                                            Jean MBOUROU
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-danger">
                                            <i class="fas fa-circle mr-1"></i>Haute
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Traiter">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="dossier-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="dossier-icon mr-3">
                                                <i class="fas fa-hands-helping fa-lg text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">ONG Environnement Gabon</div>
                                                <small class="text-muted">ONG-2025-003</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-success">ONG</span></td>
                                    <td><span class="badge badge-primary">En Cours</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning rounded-circle mr-2" style="width: 8px; height: 8px;"></div>
                                            Marie NZENG
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-warning">
                                            <i class="fas fa-circle mr-1"></i>Moyenne
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Traiter">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="dossier-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="dossier-icon mr-3">
                                                <i class="fas fa-landmark fa-lg text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">Parti Démocratique Progrès</div>
                                                <small class="text-muted">PARTI-2025-002</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge" style="background-color: #ffcd00; color: #000;">Parti</span></td>
                                    <td><span class="badge badge-success">Approuvé</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success rounded-circle mr-2" style="width: 8px; height: 8px;"></div>
                                            Paul ONDO
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-success">
                                            <i class="fas fa-circle mr-1"></i>Basse
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="Imprimer">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
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

<!-- FAB (Floating Action Button) gabonais -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-plus fab-icon"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #009e3f;" title="Nouveau Dossier">
                <i class="fas fa-plus"></i>
            </button>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Gestion Agents">
                <i class="fas fa-users"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Rapports">
                <i class="fas fa-chart-bar"></i>
            </button>
        </div>
    </div>
</div>

<style>
/* Animations pour les stats cards */
.stats-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.1);
}

/* Actions cards hover */
.action-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: #009e3f !important;
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Activity items */
.activity-item {
    transition: all 0.3s ease;
}

.activity-item:hover {
    transform: translateX(5px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

/* Dossiers table */
.dossier-row {
    transition: background-color 0.2s ease;
}

.dossier-row:hover {
    background-color: rgba(0, 158, 63, 0.05);
}

.dossier-icon {
    width: 40px;
    text-align: center;
}

/* Chart placeholder */
.chart-placeholder {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    border: 2px dashed #dee2e6;
}

/* FAB Style gabonais */
.fab-container {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
}

.fab-menu {
    position: relative;
}

.fab-main {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.fab-main:hover {
    transform: scale(1.1);
}

.fab-icon {
    color: white;
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.fab-options {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.fab-menu.active .fab-options {
    opacity: 1;
    visibility: visible;
}

.fab-menu.active .fab-icon {
    transform: rotate(45deg);
}

.fab-option {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.fab-option:hover {
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .fab-container {
        bottom: 1rem;
        right: 1rem;
    }
    
    .fab-main {
        width: 50px;
        height: 50px;
    }
    
    .fab-option {
        width: 40px;
        height: 40px;
    }
}

/* Animations d'entrée */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

/* Badge personnalisés */
.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

/* Boutons de contrôle graphique */
.btn-group .btn.active {
    background-color: #009e3f;
    border-color: #009e3f;
    color: white;
}
</style>

<script>
// Toggle FAB Menu
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.classList.toggle('active');
}

// Fermer FAB en cliquant ailleurs
document.addEventListener('click', function(event) {
    const fabMenu = document.getElementById('fabMenu');
    if (!fabMenu.contains(event.target)) {
        fabMenu.classList.remove('active');
    }
});

// Animation des cartes au chargement
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Admin PNGDI - Chargé avec succès');
    
    // Animation des nombres
    const numbers = document.querySelectorAll('.stats-card h3');
    numbers.forEach(number => {
        const finalValue = parseInt(number.textContent);
        let currentValue = 0;
        const increment = finalValue / 50;
        
        const timer = setInterval(function() {
            currentValue += increment;
            if (currentValue >= finalValue) {
                number.textContent = finalValue;
                clearInterval(timer);
            } else {
                number.textContent = Math.floor(currentValue);
            }
        }, 20);
    });

    // Gestion des boutons de graphique
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Retirer active de tous les boutons
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            // Ajouter active au bouton cliqué
            this.classList.add('active');
        });
    });

    // Animation hover sur les action cards
    document.querySelectorAll('.action-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.borderColor = '#009e3f';
        });
        card.addEventListener('mouseleave', function() {
            this.style.borderColor = '#dee2e6';
        });
    });

    // Auto-refresh simulation (optionnel)
    setInterval(function() {
        // Simulation de mise à jour des données en temps réel
        const now = new Date();
        const timeElements = document.querySelectorAll('.activity-item .text-muted.small:last-child');
        if (timeElements.length > 0 && Math.random() > 0.8) {
            // 20% de chance de simuler une nouvelle activité
            console.log('Simulation: Nouvelle activité détectée');
        }
    }, 30000); // Toutes les 30 secondes
});

// Animation au scroll
window.addEventListener('scroll', function() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        const rect = card.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }
    });
});
</script>
@endsection