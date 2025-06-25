@extends('layouts.operator')

@section('title', 'Notifications')

@section('page-title', 'Centre de Notifications')

@section('content')
<div class="container-fluid">
    <!-- Header avec statistiques -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-bell me-2"></i>
                                Centre de Notifications
                            </h2>
                            <p class="mb-0 opacity-90">Suivez toutes les activités et mises à jour de vos dossiers en temps réel</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-light" onclick="markAllAsRead()">
                                    <i class="fas fa-check-double me-2"></i>Tout marquer comme lu
                                </button>
                                <button class="btn btn-outline-light" onclick="refreshNotifications()">
                                    <i class="fas fa-sync-alt me-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $totalNotifications ?? 15 }}</h3>
                            <p class="mb-0 small opacity-90">Total</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-bell fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 85%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $notificationsNonLues ?? 7 }}</h3>
                            <p class="mb-0 small opacity-90">Non Lues</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-bell-slash fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $notificationsUrgentes ?? 3 }}</h3>
                            <p class="mb-0 small">Urgentes</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: 40%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $notificationsAujourdhui ?? 5 }}</h3>
                            <p class="mb-0 small opacity-90">Aujourd'hui</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 70%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher une notification..." id="searchInput">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterType">
                                <option value="">Tous les types</option>
                                <option value="dossier">Dossiers</option>
                                <option value="message">Messages</option>
                                <option value="subvention">Subventions</option>
                                <option value="declaration">Déclarations</option>
                                <option value="systeme">Système</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterStatut">
                                <option value="">Tous les statuts</option>
                                <option value="non_lue">Non lues</option>
                                <option value="lue">Lues</option>
                                <option value="archivee">Archivées</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterPriorite">
                                <option value="">Toutes priorités</option>
                                <option value="haute">Haute</option>
                                <option value="normale">Normale</option>
                                <option value="basse">Basse</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterPeriode">
                                <option value="">Toutes périodes</option>
                                <option value="today">Aujourd'hui</option>
                                <option value="week">Cette semaine</option>
                                <option value="month">Ce mois</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <div class="dropdown">
                                <button class="btn btn-outline-danger dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportNotifications()"><i class="fas fa-download me-2"></i>Exporter</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="clearAllNotifications()"><i class="fas fa-trash me-2"></i>Effacer tout</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="configureNotifications()"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline des Notifications -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-timeline me-2 text-danger"></i>
                            Timeline des Notifications
                        </h5>
                        <span class="badge bg-light text-dark">{{ $totalNotifications ?? 15 }} notifications</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="timeline" id="notificationsTimeline">
                        @if(isset($notifications) && count($notifications) > 0)
                            @foreach($notifications as $notification)
                            <div class="timeline-item {{ !($notification['is_read'] ?? true) ? 'unread' : '' }}" data-notification-id="{{ $notification['id'] ?? rand(1, 100) }}">
                                <div class="timeline-marker bg-{{ $notification['priority_color'] ?? 'primary' }}">
                                    <i class="fas fa-{{ $notification['icon'] ?? 'bell' }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="timeline-title mb-1">{{ $notification['title'] ?? 'Nouvelle notification' }}</h6>
                                                <small class="timeline-time text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ isset($notification['created_at']) ? $notification['created_at']->diffForHumans() : 'Il y a 2 heures' }}
                                                </small>
                                            </div>
                                            <div class="timeline-actions">
                                                <button class="btn btn-sm btn-outline-success" onclick="markAsRead({{ $notification['id'] ?? rand(1, 100) }})" title="Marquer comme lu">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification({{ $notification['id'] ?? rand(1, 100) }})" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="mb-2">{{ $notification['message'] ?? 'Message de notification' }}</p>
                                        @if(isset($notification['action_url']))
                                            <a href="{{ $notification['action_url'] }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                {{ $notification['action_text'] ?? 'Voir le détail' }}
                                            </a>
                                        @endif
                                        <div class="timeline-meta mt-2">
                                            <span class="badge bg-{{ $notification['type_color'] ?? 'secondary' }}">
                                                {{ $notification['type_label'] ?? 'Système' }}
                                            </span>
                                            @if(($notification['priority'] ?? 'normale') === 'haute')
                                                <span class="badge bg-danger ms-1">Priorité haute</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <!-- Notifications d'exemple -->
                            <div class="timeline-item unread" data-notification-id="1">
                                <div class="timeline-marker bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="timeline-title mb-1">Dossier approuvé</h6>
                                                <small class="timeline-time text-muted">
                                                    <i class="fas fa-clock me-1"></i>Il y a 2 heures
                                                </small>
                                            </div>
                                            <div class="timeline-actions">
                                                <button class="btn btn-sm btn-outline-success" onclick="markAsRead(1)" title="Marquer comme lu">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(1)" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="mb-2">Votre dossier de création d'association "Protection de l'Environnement" a été approuvé par l'administration.</p>
                                        <a href="#" class="btn btn-sm btn-success">
                                            <i class="fas fa-download me-1"></i>Télécharger le récépissé
                                        </a>
                                        <div class="timeline-meta mt-2">
                                            <span class="badge bg-success">Dossier</span>
                                            <span class="badge bg-primary ms-1">Approuvé</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item unread" data-notification-id="2">
                                <div class="timeline-marker bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="timeline-title mb-1">Documents manquants</h6>
                                                <small class="timeline-time text-muted">
                                                    <i class="fas fa-clock me-1"></i>Il y a 4 heures
                                                </small>
                                            </div>
                                            <div class="timeline-actions">
                                                <button class="btn btn-sm btn-outline-success" onclick="markAsRead(2)" title="Marquer comme lu">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(2)" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="mb-2">Il manque des documents pour votre demande de subvention. Veuillez compléter votre dossier.</p>
                                        <a href="#" class="btn btn-sm btn-warning">
                                            <i class="fas fa-upload me-1"></i>Compléter le dossier
                                        </a>
                                        <div class="timeline-meta mt-2">
                                            <span class="badge bg-warning">Subvention</span>
                                            <span class="badge bg-danger ms-1">Priorité haute</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item" data-notification-id="3">
                                <div class="timeline-marker bg-info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="timeline-title mb-1">Rappel - Déclaration annuelle</h6>
                                                <small class="timeline-time text-muted">
                                                    <i class="fas fa-clock me-1"></i>Hier à 16:30
                                                </small>
                                            </div>
                                            <div class="timeline-actions">
                                                <button class="btn btn-sm btn-outline-success" onclick="markAsRead(3)" title="Marquer comme lu">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(3)" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="mb-2">N'oubliez pas de soumettre votre déclaration annuelle avant le 31 mars 2025.</p>
                                        <a href="#" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-alt me-1"></i>Commencer la déclaration
                                        </a>
                                        <div class="timeline-meta mt-2">
                                            <span class="badge bg-info">Déclaration</span>
                                            <span class="badge bg-warning ms-1">Échéance proche</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item" data-notification-id="4">
                                <div class="timeline-marker bg-primary">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="timeline-title mb-1">Nouveau message reçu</h6>
                                                <small class="timeline-time text-muted">
                                                    <i class="fas fa-clock me-1"></i>Hier à 14:15
                                                </small>
                                            </div>
                                            <div class="timeline-actions">
                                                <button class="btn btn-sm btn-outline-success" onclick="markAsRead(4)" title="Marquer comme lu">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(4)" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="mb-2">Vous avez reçu un nouveau message de l'administration concernant votre organisation.</p>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-envelope-open me-1"></i>Lire le message
                                        </a>
                                        <div class="timeline-meta mt-2">
                                            <span class="badge bg-primary">Message</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item" data-notification-id="5">
                                <div class="timeline-marker bg-secondary">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="timeline-title mb-1">Mise à jour système</h6>
                                                <small class="timeline-time text-muted">
                                                    <i class="fas fa-clock me-1"></i>Il y a 2 jours
                                                </small>
                                            </div>
                                            <div class="timeline-actions">
                                                <button class="btn btn-sm btn-outline-success" onclick="markAsRead(5)" title="Marquer comme lu">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(5)" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="mb-2">Le système PNGDI a été mis à jour avec de nouvelles fonctionnalités et améliorations.</p>
                                        <div class="timeline-meta mt-2">
                                            <span class="badge bg-secondary">Système</span>
                                            <span class="badge bg-info ms-1">Mise à jour</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- État vide -->
                    <div id="emptyState" class="text-center py-5" style="display: none;">
                        <div class="mb-4">
                            <i class="fas fa-bell-slash fa-5x text-muted opacity-50"></i>
                        </div>
                        <h4 class="text-muted mb-3">Aucune notification</h4>
                        <p class="text-muted mb-4">Toutes vos notifications apparaîtront ici</p>
                        <button class="btn btn-primary" onclick="refreshNotifications()">
                            <i class="fas fa-sync-alt me-2"></i>Actualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bouton Flottant Notifications -->
<div class="notification-fab">
    <button class="fab-notification" onclick="scrollToTop()" title="Remonter en haut">
        <i class="fas fa-chevron-up"></i>
    </button>
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

/* Timeline Styles */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #8b1538, #c41e3a, #007bff, #28a745);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 2rem;
    transition: all 0.3s ease;
}

.timeline-item.unread {
    background: linear-gradient(90deg, rgba(0, 158, 63, 0.05) 0%, transparent 100%);
    border-left: 4px solid #009e3f;
    padding: 1rem;
    border-radius: 8px;
}

.timeline-item:hover {
    transform: translateX(5px);
}

.timeline-marker {
    position: absolute;
    left: -2.5rem;
    top: 0.5rem;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 1;
}

.timeline-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1.5rem;
    transition: box-shadow 0.3s ease;
}

.timeline-content:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.timeline-title {
    font-weight: 600;
    color: #333;
}

.timeline-time {
    font-size: 0.8rem;
}

.timeline-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.timeline-item:hover .timeline-actions {
    opacity: 1;
}

.timeline-body {
    margin-top: 1rem;
}

.timeline-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Bouton flottant */
.notification-fab {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
}

.fab-notification {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);
    border: none;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fab-notification:hover {
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .timeline {
        padding-left: 1.5rem;
    }
    
    .timeline::before {
        left: 0.75rem;
    }
    
    .timeline-marker {
        left: -1.75rem;
        width: 2rem;
        height: 2rem;
        font-size: 0.8rem;
    }
    
    .timeline-item {
        padding-left: 1.5rem;
    }
    
    .timeline-content {
        padding: 1rem;
    }
}

/* Animations d'entrée */
@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.timeline-item {
    animation: slideInLeft 0.6s ease-out;
}

/* Pulse pour notifications non lues */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 158, 63, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 158, 63, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 158, 63, 0);
    }
}

.timeline-item.unread .timeline-marker {
    animation: pulse 2s infinite;
}

/* Badge personnalisé */
.badge.bg-primary { background-color: #003f7f !important; }
.badge.bg-success { background-color: #009e3f !important; }
.badge.bg-warning { background-color: #ffcd00 !important; color: #000 !important; }
.badge.bg-danger { background-color: #8b1538 !important; }
</style>

<script>
// Variables globales
let allNotifications = [];
let filteredNotifications = [];

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    
    // Gestionnaires d'événements pour les filtres
    ['filterType', 'filterStatut', 'filterPriorite', 'filterPeriode'].forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            element.addEventListener('change', applyFilters);
        }
    });

    // Recherche
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchNotifications(this.value);
        });
    }
    
    // Auto-refresh toutes les 5 minutes
    setInterval(refreshNotifications, 300000);
});

// Charger les notifications
function loadNotifications() {
    // Ici vous feriez un appel AJAX pour charger les vraies notifications
    console.log('Chargement des notifications...');
    
    // Simulation de données
    allNotifications = [
        {
            id: 1,
            title: 'Dossier approuvé',
            message: 'Votre dossier de création d\'association a été approuvé',
            type: 'dossier',
            priority: 'haute',
            is_read: false,
            created_at: new Date(Date.now() - 2 * 60 * 60 * 1000) // Il y a 2h
        },
        // ... autres notifications
    ];
    
    filteredNotifications = [...allNotifications];
    updateDisplay();
}

// Marquer une notification comme lue
function markAsRead(notificationId) {
    const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
    if (notification) {
        notification.classList.remove('unread');
        
        // Animation de succès
        const marker = notification.querySelector('.timeline-marker');
        marker.style.backgroundColor = '#28a745';
        marker.innerHTML = '<i class="fas fa-check"></i>';
        
        setTimeout(() => {
            marker.style.backgroundColor = '';
            marker.innerHTML = '<i class="fas fa-bell"></i>';
        }, 2000);
    }
    
    // Mettre à jour les statistiques
    updateStats();
    
    console.log('Notification marquée comme lue:', notificationId);
}

// Marquer toutes comme lues
function markAllAsRead() {
    if (confirm('Êtes-vous sûr de vouloir marquer toutes les notifications comme lues ?')) {
        document.querySelectorAll('.timeline-item.unread').forEach(item => {
            item.classList.remove('unread');
        });
        
        updateStats();
        
        // Animation de succès globale
        showSuccessMessage('Toutes les notifications ont été marquées comme lues');
    }
}

// Supprimer une notification
function deleteNotification(notificationId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
        const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (notification) {
            // Animation de suppression
            notification.style.transform = 'translateX(-100%)';
            notification.style.opacity = '0';
            
            setTimeout(() => {
                notification.remove();
                updateStats();
                checkEmptyState();
            }, 300);
        }
        
        console.log('Notification supprimée:', notificationId);
    }
}

// Actualiser les notifications
function refreshNotifications() {
    const button = document.querySelector('[onclick="refreshNotifications()"]');
    const icon = button.querySelector('i');
    
    // Animation de rotation
    icon.classList.add('fa-spin');
    
    setTimeout(() => {
        icon.classList.remove('fa-spin');
        showSuccessMessage('Notifications actualisées');
        loadNotifications();
    }, 1000);
}

// Rechercher dans les notifications
function searchNotifications(query) {
    const notifications = document.querySelectorAll('.timeline-item');
    
    notifications.forEach(notification => {
        const text = notification.textContent.toLowerCase();
        if (text.includes(query.toLowerCase())) {
            notification.style.display = '';
            
            // Surligner les termes trouvés
            if (query.length > 2) {
                highlightSearchTerm(notification, query);
            }
        } else {
            notification.style.display = 'none';
        }
    });
    
    checkEmptyState();
}

// Appliquer les filtres
function applyFilters() {
    const typeFilter = document.getElementById('filterType').value;
    const statutFilter = document.getElementById('filterStatut').value;
    const prioriteFilter = document.getElementById('filterPriorite').value;
    const periodeFilter = document.getElementById('filterPeriode').value;
    
    const notifications = document.querySelectorAll('.timeline-item');
    
    notifications.forEach(notification => {
        let show = true;
        
        // Filtrer par type
        if (typeFilter && !notification.textContent.toLowerCase().includes(typeFilter)) {
            show = false;
        }
        
        // Filtrer par statut
        if (statutFilter === 'non_lue' && !notification.classList.contains('unread')) {
            show = false;
        }
        if (statutFilter === 'lue' && notification.classList.contains('unread')) {
            show = false;
        }
        
        // Filtrer par priorité
        if (prioriteFilter && !notification.querySelector('.badge.bg-danger')) {
            show = false;
        }
        
        notification.style.display = show ? '' : 'none';
    });
    
    checkEmptyState();
}

// Vérifier l'état vide
function checkEmptyState() {
    const visibleNotifications = document.querySelectorAll('.timeline-item[style=""], .timeline-item:not([style])');
    const emptyState = document.getElementById('emptyState');
    const timeline = document.querySelector('.timeline');
    
    if (visibleNotifications.length === 0) {
        timeline.style.display = 'none';
        emptyState.style.display = 'block';
    } else {
        timeline.style.display = 'block';
        emptyState.style.display = 'none';
    }
}

// Mettre à jour les statistiques
function updateStats() {
    const totalNotifications = document.querySelectorAll('.timeline-item').length;
    const unreadNotifications = document.querySelectorAll('.timeline-item.unread').length;
    
    // Mettre à jour les cartes de statistiques
    document.querySelector('.stats-card h3').textContent = totalNotifications;
    document.querySelectorAll('.stats-card')[1].querySelector('h3').textContent = unreadNotifications;
}

// Exporter les notifications
function exportNotifications() {
    // Simulation d'export
    const notifications = document.querySelectorAll('.timeline-item');
    const data = [];
    
    notifications.forEach(notification => {
        const title = notification.querySelector('.timeline-title').textContent;
        const time = notification.querySelector('.timeline-time').textContent;
        const content = notification.querySelector('.timeline-body p').textContent;
        
        data.push({ title, time, content });
    });
    
    console.log('Export des notifications:', data);
    showSuccessMessage('Notifications exportées avec succès');
}

// Effacer toutes les notifications
function clearAllNotifications() {
    if (confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications ? Cette action est irréversible.')) {
        const notifications = document.querySelectorAll('.timeline-item');
        
        notifications.forEach((notification, index) => {
            setTimeout(() => {
                notification.style.transform = 'translateX(-100%)';
                notification.style.opacity = '0';
                
                setTimeout(() => {
                    notification.remove();
                    if (index === notifications.length - 1) {
                        checkEmptyState();
                        updateStats();
                    }
                }, 300);
            }, index * 100);
        });
        
        showSuccessMessage('Toutes les notifications ont été supprimées');
    }
}

// Configurer les notifications
function configureNotifications() {
    alert('Paramètres de notification - Fonctionnalité en cours de développement');
}

// Remonter en haut
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Afficher un message de succès
function showSuccessMessage(message) {
    // Créer une notification toast
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Supprimer après fermeture
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Surligner les termes de recherche
function highlightSearchTerm(element, term) {
    // Simple highlighting function
    const walker = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );
    
    const textNodes = [];
    let node;
    
    while (node = walker.nextNode()) {
        textNodes.push(node);
    }
    
    textNodes.forEach(textNode => {
        const text = textNode.textContent;
        const regex = new RegExp(`(${term})`, 'gi');
        if (regex.test(text)) {
            const highlightedText = text.replace(regex, '<mark>$1</mark>');
            const span = document.createElement('span');
            span.innerHTML = highlightedText;
            textNode.parentNode.replaceChild(span, textNode);
        }
    });
}
</script>
@endsection