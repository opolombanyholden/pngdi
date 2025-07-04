@extends('layouts.operator')

@section('title', 'Confirmation de Soumission')

@section('content')
<div class="container-fluid px-4">
    <!-- En-tête de confirmation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle fa-5x mb-3 opacity-75"></i>
                    <h1 class="card-title h2 mb-3">🎉 Félicitations !</h1>
                    <h2 class="h4 mb-2">Votre dossier numérique a été soumis avec succès</h2>
                    <p class="lead mb-3">
                        Numéro de dossier : <strong>{{ $confirmationData['numero_recepisse'] ?? 'N/A' }}</strong>
                    </p>
                    
                    <!-- Message légal officiel -->
                    <div class="alert alert-info text-start mt-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Prochaine étape obligatoire - Dépôt physique
                        </h6>
                        <p class="mb-2">
                            <strong>Aux fins de recevoir votre accusé de réception</strong>, conformément aux dispositions de l'article 26 de la loi N° 016/2025 du 27 Juin 2025 relative aux partis politiques en République Gabonaise, vous êtes invité à <strong>déposer votre dossier physique, en 3 exemplaires</strong>, auprès des services de la Direction Générale des Élections et des Libertés Publiques du Ministère de l'Intérieur, de la Sécurité et de la Décentralisation, en application des dispositions de l'article 24 de la loi suscitée.
                        </p>
                        <div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
                            <small class="text-dark">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <strong>Important :</strong> Le dépôt numérique ne dispense pas du dépôt physique obligatoire.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations principales -->
    <div class="row">
        <!-- Détails du dossier -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-folder-open me-2"></i>
                        Détails du Dossier
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Organisation</h6>
                            <p class="fw-bold mb-3">{{ $confirmationData['organisation']->nom ?? 'N/A' }}</p>
                            
                            @if(isset($confirmationData['organisation']->sigle) && $confirmationData['organisation']->sigle)
                            <h6 class="text-muted mb-2">Sigle</h6>
                            <p class="fw-bold mb-3">{{ $confirmationData['organisation']->sigle }}</p>
                            @endif

                            <h6 class="text-muted mb-2">Type d'organisation</h6>
                            <p class="mb-3">
                                <span class="badge bg-info fs-6">
                                    {{ isset($confirmationData['organisation']->type) ? ucfirst(str_replace('_', ' ', $confirmationData['organisation']->type)) : 'N/A' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Numéro de dossier</h6>
                            <p class="fw-bold mb-3">{{ $confirmationData['dossier']->numero_dossier ?? 'N/A' }}</p>

                            <h6 class="text-muted mb-2">Date de soumission</h6>
                            <p class="mb-3">
                                @if(isset($confirmationData['dossier']->submitted_at))
                                    @php
                                        // ✅ CORRECTION : Gestion sécurisée des dates
                                        try {
                                            if (is_string($confirmationData['dossier']->submitted_at)) {
                                                $submittedDate = \Carbon\Carbon::parse($confirmationData['dossier']->submitted_at);
                                            } else {
                                                $submittedDate = $confirmationData['dossier']->submitted_at;
                                            }
                                            echo $submittedDate->format('d/m/Y à H:i');
                                        } catch (Exception $e) {
                                            echo date('d/m/Y à H:i');
                                        }
                                    @endphp
                                @else
                                    {{ date('d/m/Y à H:i') }}
                                @endif
                            </p>

                            <h6 class="text-muted mb-2">Statut</h6>
                            <p class="mb-3">
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="fas fa-clock me-1"></i>
                                    En attente de traitement
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- QR Code de vérification -->
@if(isset($confirmationData['qr_code']) && $confirmationData['qr_code'])
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-info text-center">
            <h6 class="alert-heading">
                <i class="fas fa-qrcode me-2"></i>
                Code de vérification
            </h6>
            
            <!-- Image QR Code -->
            <div class="mb-3">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($confirmationData['qr_code']) }}" 
                     alt="QR Code" 
                     class="border rounded"
                     style="background: white; padding: 10px;">
            </div>
            
            
            <small class="text-muted">
                Ce code permet de vérifier l'authenticité de votre dossier
            </small>
        </div>
    </div>
</div>
@endif


                </div>
            </div>

            <!-- Statistiques des adhérents -->
            @if(isset($confirmationData['adherents_stats']) && $confirmationData['adherents_stats'])
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Statistiques des Adhérents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-primary mb-1">{{ $confirmationData['adherents_stats']['total'] ?? 0 }}</h3>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-success mb-1">{{ $confirmationData['adherents_stats']['valides'] ?? 0 }}</h3>
                                <small class="text-muted">Valides</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-warning mb-1">
                                    {{ ($confirmationData['adherents_stats']['anomalies_majeures'] ?? 0) + ($confirmationData['adherents_stats']['anomalies_mineures'] ?? 0) }}
                                </h3>
                                <small class="text-muted">Anomalies</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger mb-1">{{ $confirmationData['adherents_stats']['anomalies_critiques'] ?? 0 }}</h3>
                            <small class="text-muted">Critiques</small>
                        </div>
                    </div>

                    @if(($confirmationData['adherents_stats']['anomalies_critiques'] ?? 0) > 0 || 
                        ($confirmationData['adherents_stats']['anomalies_majeures'] ?? 0) > 0 || 
                        ($confirmationData['adherents_stats']['anomalies_mineures'] ?? 0) > 0)
                    <div class="alert alert-warning mt-3">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Anomalies détectées
                        </h6>
                        <p class="mb-2">
                            Des anomalies ont été détectées dans votre liste d'adhérents. 
                            <strong>Tous les adhérents ont été conservés</strong> conformément à notre nouveau système révolutionnaire.
                        </p>
                        <p class="mb-0">
                            Un rapport détaillé des anomalies sera inclus dans l'accusé de réception que vous recevrez sous 72h ouvrées.
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Prochaines étapes -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-route me-2"></i>
                        Prochaines Étapes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item active">
                            <div class="timeline-marker bg-success">
                                <i class="fas fa-check text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Soumission</h6>
                                <small class="text-muted">
                                    Complétée le 
                                    @php
                                        try {
                                            echo \Carbon\Carbon::now()->format('d/m/Y à H:i');
                                        } catch (Exception $e) {
                                            echo date('d/m/Y à H:i');
                                        }
                                    @endphp
                                </small>
                            </div>
                        </div>
                        
                        <div class="timeline-item pending">
                            <div class="timeline-marker bg-warning">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Attribution Agent</h6>
                                <small class="text-muted">Automatique selon workflow FIFO</small>
                            </div>
                        </div>
                        
                        <div class="timeline-item pending">
                            <div class="timeline-marker bg-secondary">
                                <i class="fas fa-search text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Examen du Dossier</h6>
                                <small class="text-muted">Vérification et validation</small>
                            </div>
                        </div>
                        
                        <div class="timeline-item pending">
                            <div class="timeline-marker bg-primary">
                                <i class="fas fa-certificate text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Décision Finale</h6>
                                <small class="text-muted">Notification par email</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations importantes -->
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations Importantes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary">
                        <h6 class="alert-heading">
                            <i class="fas fa-envelope me-2"></i>
                            Accusé de Réception
                        </h6>
                        <p class="mb-0">
                            Vous recevrez un accusé de réception officiel dans un délai de 
                            <strong>{{ $confirmationData['delai_traitement'] ?? '72 heures ouvrées' }}</strong>.
                        </p>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-bell me-2"></i>
                            Notifications
                        </h6>
                        <p class="mb-0">
                            Vous serez notifié par email à chaque étape du traitement de votre dossier.
                        </p>
                    </div>

                    <div class="alert alert-success">
                        <h6 class="alert-heading">
                            <i class="fas fa-shield-alt me-2"></i>
                            Conservation des Données
                        </h6>
                        <p class="mb-0">
                            Tous vos adhérents ont été enregistrés, même ceux présentant des anomalies mineures.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions disponibles -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Actions Disponibles</h5>
                    <div class="btn-group" role="group">
                        <a href="{{ route('operator.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Retour au Dashboard
                        </a>
                        
                        @if(isset($confirmationData['accuse_reception_path']) && $confirmationData['accuse_reception_path'])
                            @php
                                // ✅ VÉRIFICATION EXISTENCE DE LA ROUTE
                                $downloadRouteExists = Route::has('operator.documents.download');
                            @endphp
                            @if($downloadRouteExists)
                                <a href="{{ route('operator.documents.download', ['path' => basename($confirmationData['accuse_reception_path'])]) }}" 
                                   class="btn btn-success" target="_blank">
                                    <i class="fas fa-download me-2"></i>
                                    Télécharger l'Accusé
                                </a>
                            @else
                                <button type="button" class="btn btn-outline-success" disabled title="Fonction de téléchargement en cours de développement">
                                    <i class="fas fa-download me-2"></i>
                                    Télécharger l'Accusé
                                </button>
                            @endif
                        @endif
                        
                        <a href="{{ route('operator.organisations.create') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-plus me-2"></i>
                            Nouvelle Organisation
                        </a>
                        
                        <button type="button" class="btn btn-info" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            Imprimer cette Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles personnalisés pour la timeline -->
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}

.timeline-item.active .timeline-content {
    border-left-color: #28a745;
    background: #d4edda;
}

.timeline-item.pending .timeline-content {
    border-left-color: #6c757d;
}

@media print {
    .btn-group {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .bg-success,
    .bg-primary,
    .bg-warning,
    .bg-info,
    .bg-secondary {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .text-white {
        color: #212529 !important;
    }
}

/* Animations d'entrée */
.card {
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive improvements */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 10px;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -15px;
        width: 25px;
        height: 25px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée pour les éléments
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Auto-scroll vers le contenu principal sur mobile
    if (window.innerWidth <= 768) {
        setTimeout(() => {
            document.querySelector('.container-fluid').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 500);
    }
    
    // Notification toast (si vous utilisez Bootstrap Toast)
    const showSuccessToast = () => {
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toastHTML = `
                <div class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3" 
                     style="z-index: 9999" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i>
                            Dossier soumis avec succès !
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                                data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', toastHTML);
            const toastElement = document.querySelector('.toast:last-child');
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Supprimer le toast après affichage
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
    };
    
    // Afficher le toast après le chargement
    setTimeout(showSuccessToast, 1000);
});
</script>
@endsection