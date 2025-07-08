{{-- ===================================================================
     final-confirmation.blade.php
     Vue de confirmation définitive après soumission à l'administration
     ===================================================================== --}}

@extends('layouts.operator')

@section('title', 'Dossier Soumis à l\'Administration')

@section('content')
<div class="container-fluid py-4">
    {{-- Variables PHP pour la vue --}}
    @php
        $dossier = $confirmationData['dossier'];
        $organisation = $confirmationData['organisation'];
        $numeroSoumission = $confirmationData['numero_soumission'];
        $totalAdherents = $confirmationData['total_adherents'];
        $submittedAt = $confirmationData['submitted_at'];
        $prochaines_etapes = $confirmationData['prochaines_etapes'] ?? [];
    @endphp

    {{-- Header principal avec animation de succès --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header text-white position-relative overflow-hidden" 
                     style="background: linear-gradient(135deg, #28a745 0%, #20c997 50%, #17a2b8 100%);">
                    {{-- Animation de particules de succès --}}
                    <div class="position-absolute top-0 start-0 w-100 h-100" id="success-particles"></div>
                    
                    <div class="row align-items-center position-relative">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="success-icon me-4">
                                    <i class="fas fa-check-circle fa-4x text-white" id="success-check"></i>
                                </div>
                                <div>
                                    <h2 class="mb-2 fw-bold">Dossier Soumis avec Succès !</h2>
                                    <p class="mb-0 fs-5">Votre dossier a été transmis à l'administration pour traitement final</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="bg-white bg-opacity-20 rounded p-3">
                                <div class="h4 text-white mb-1">{{ $numeroSoumission }}</div>
                                <small>Numéro de soumission</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Informations clés --}}
                <div class="card-body bg-light">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end border-2 pe-3">
                                <div class="h5 text-success mb-1">{{ $submittedAt->format('d/m/Y') }}</div>
                                <small class="text-muted">Date de soumission</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end border-2 pe-3">
                                <div class="h5 text-info mb-1">{{ $submittedAt->format('H:i') }}</div>
                                <small class="text-muted">Heure de soumission</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end border-2 pe-3">
                                <div class="h5 text-warning mb-1">{{ $totalAdherents }}</div>
                                <small class="text-muted">Adhérents traités</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="h5 text-primary mb-1">15 jours</div>
                            <small class="text-muted">Délai maximum</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Informations détaillées du dossier --}}
        <div class="col-lg-8">
            {{-- Résumé du dossier --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-folder-open me-2"></i>Résumé du Dossier
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Organisation :</td>
                                    <td>{{ $organisation->nom }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Sigle :</td>
                                    <td>{{ $organisation->sigle ?? 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Type :</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ ucfirst(str_replace('_', ' ', $organisation->type)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Province :</td>
                                    <td>{{ $organisation->province }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">N° Dossier :</td>
                                    <td><code>{{ $dossier->numero_dossier }}</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">N° Soumission :</td>
                                    <td><code>{{ $numeroSoumission }}</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Statut :</td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-paper-plane me-1"></i>Soumis à l'administration
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Total adhérents :</td>
                                    <td><span class="fw-bold text-success">{{ $totalAdherents }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Timeline des prochaines étapes --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks me-2"></i>Prochaines Étapes du Traitement
                    </h5>
                </div>
                <div class="card-body">
                    @if(!empty($prochaines_etapes))
                    <div class="timeline">
                        @foreach($prochaines_etapes as $index => $etape)
                        <div class="timeline-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="timeline-marker">
                                <div class="timeline-circle">{{ $index + 1 }}</div>
                            </div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-1">{{ $etape['titre'] }}</h6>
                                <p class="text-muted mb-2">{{ $etape['description'] }}</p>
                                <small class="badge bg-light text-dark">
                                    <i class="fas fa-clock me-1"></i>{{ $etape['delai'] }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>Les étapes de traitement seront communiquées prochainement.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Message informatif --}}
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-info-circle fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold">{{ $confirmationData['message_final'] ?? 'Information importante' }}</h6>
                            <p class="mb-2">Vous recevrez des notifications automatiques par email à chaque étape du traitement de votre dossier.</p>
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Toutes vos données sont sécurisées et traitées selon la réglementation en vigueur.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar avec actions et contacts --}}
        <div class="col-lg-4">
            {{-- Actions disponibles --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Actions Disponibles
                    </h5>
                </div>
                <div class="card-body text-center">
                    {{-- Télécharger l'accusé de réception --}}
                    @if(isset($confirmationData['accuse_reception_path']))
                    <a href="{{ route('operator.organisations.download-accuse', $confirmationData['accuse_reception_path']) }}" 
                       class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-download me-2"></i>
                        Télécharger l'Accusé de Réception
                    </a>
                    @endif
                    
                    {{-- Imprimer cette page --}}
                    <button class="btn btn-outline-primary w-100 mb-3" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimer cette Page
                    </button>
                    
                    {{-- Retour au tableau de bord --}}
                    <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-tachometer-alt me-2"></i>Retour au Tableau de Bord
                    </a>
                </div>
            </div>

            {{-- Informations de contact --}}
            @if(isset($confirmationData['contact_support']))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-headset me-2"></i>Support & Assistance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-envelope text-primary me-3"></i>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <a href="mailto:{{ $confirmationData['contact_support']['email'] }}">
                                    {{ $confirmationData['contact_support']['email'] }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-phone text-success me-3"></i>
                            <div>
                                <small class="text-muted d-block">Téléphone</small>
                                <a href="tel:{{ $confirmationData['contact_support']['telephone'] }}">
                                    {{ $confirmationData['contact_support']['telephone'] }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-info me-3"></i>
                            <div>
                                <small class="text-muted d-block">Horaires</small>
                                <span>{{ $confirmationData['contact_support']['horaires'] }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-question-circle me-1"></i>
                            Une question sur votre dossier ?<br>
                            N'hésitez pas à nous contacter en mentionnant votre numéro de soumission.
                        </small>
                    </div>
                </div>
            </div>
            @endif

            {{-- Prochaines notifications --}}
            <div class="card border-info">
                <div class="card-header bg-info bg-opacity-10 border-info">
                    <h6 class="card-title mb-0 text-info">
                        <i class="fas fa-bell me-2"></i>Notifications Automatiques
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Accusé de réception envoyé</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning me-2"></i>
                            <small>Début du traitement (J+1)</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-search text-info me-2"></i>
                            <small>Vérification administrative (J+5)</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-flag-checkered text-primary me-2"></i>
                            <small>Décision finale (J+15 max)</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Styles CSS personnalisés --}}
@push('styles')
<style>
/* Animation du check de succès */
#success-check {
    animation: successPulse 2s ease-in-out infinite;
}

@keyframes successPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Animation des particules de succès */
#success-particles {
    background: radial-gradient(circle, rgba(255,255,255,0.2) 1px, transparent 1px);
    background-size: 20px 20px;
    animation: particles 10s linear infinite;
}

@keyframes particles {
    0% { background-position: 0 0; }
    100% { background-position: 20px 20px; }
}

/* Timeline personnalisée */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    height: 100%;
    width: 2px;
    background: linear-gradient(to bottom, #28a745, #17a2b8);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
}

.timeline-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    border: 3px solid white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.timeline-item.active .timeline-circle {
    background: #28a745;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 2px 5px rgba(0,0,0,0.2), 0 0 0 0 rgba(40, 167, 69, 0.7); }
    50% { box-shadow: 0 2px 5px rgba(0,0,0,0.2), 0 0 0 10px rgba(40, 167, 69, 0); }
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #28a745;
}

.timeline-item.active .timeline-content {
    background: linear-gradient(135deg, #d4edda, #f8f9fa);
}

/* Styles pour l'impression */
@media print {
    #success-particles,
    .btn,
    .card-header.bg-primary,
    .card-header.bg-info,
    .card-header.bg-secondary,
    .card-header.bg-success {
        background: #f8f9fa !important;
        color: #333 !important;
    }
    
    .timeline-circle {
        background: #333 !important;
        color: white !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline::before {
        left: 10px;
    }
    
    .timeline-marker {
        left: -15px;
    }
    
    .timeline-circle {
        width: 25px;
        height: 25px;
        font-size: 12px;
    }
    
    .timeline-item {
        padding-left: 20px;
    }
}
</style>
@endpush

{{-- Scripts JavaScript --}}
@push('scripts')
<script>
$(document).ready(function() {
    // Animation d'entrée
    animateElements();
    
    // Confettis de célébration
    if (typeof confetti !== 'undefined') {
        setTimeout(() => {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
        }, 500);
    }
});

/**
 * Animation des éléments à l'entrée
 */
function animateElements() {
    // Animation du header
    $('.card').each(function(index) {
        $(this).hide().delay(index * 200).fadeIn(600);
    });
    
    // Animation des éléments de timeline
    $('.timeline-item').each(function(index) {
        $(this).css('opacity', '0').delay(1000 + (index * 300)).animate({
            opacity: 1
        }, 500);
    });
}

/**
 * Copier le numéro de soumission
 */
function copySubmissionNumber() {
    const number = '{{ $numeroSoumission }}';
    navigator.clipboard.writeText(number).then(() => {
        showToast('Numéro de soumission copié !', 'success');
    });
}

/**
 * Afficher un toast de notification
 */
function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast align-items-center text-white bg-${type} border-0 position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    const toastElement = new bootstrap.Toast(toast[0]);
    toastElement.show();
    
    // Supprimer après fermeture
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

/**
 * Partager les informations du dossier
 */
function shareSubmissionInfo() {
    if (navigator.share) {
        navigator.share({
            title: 'Dossier soumis avec succès',
            text: `Mon dossier d'organisation ${organisationName} a été soumis avec le numéro ${submissionNumber}`,
            url: window.location.href
        });
    } else {
        copySubmissionNumber();
    }
}
</script>
@endpush