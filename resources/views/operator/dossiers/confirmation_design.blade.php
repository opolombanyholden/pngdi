@extends('layouts.operator')

@section('title', 'Confirmation Finale - Phase 2 Terminée')

@section('page-title', 'Confirmation Finale du Dossier')

@section('content')
<div class="container-fluid">
    <!-- Header Principal - Succès Phase 2 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
                <div class="card-body text-white text-center py-5 position-relative">
                    <!-- Animation de succès -->
                    <div class="success-animation mb-4">
                        <div class="checkmark-container">
                            <i class="fas fa-check-circle fa-6x text-white opacity-90 pulse-animation"></i>
                        </div>
                    </div>
                    
                    <h1 class="mb-3 display-6">🎉 Dossier soumis avec Succès !</h1>
                    <h2 class="h4 mb-3">Dossier {{ $confirmationData['numero_dossier'] }} <br/>Information de l'organisation et liste des Adhérents enregistrées</h2>
                    
                    <!-- Badge de statut -->
                    <div class="d-flex justify-content-center mb-4">
                        <span class="badge bg-light text-success px-4 py-2 fs-6">
                            <i class="fas fa-star me-2"></i>
                            {{ $confirmationData['phase_name'] }}
                        </span>
                    </div>
                    
                    <!-- Informations essentielles -->
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="alert alert-light border-0 bg-rgba-white-10">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <i class="fas fa-building fa-2x mb-2" style="text-color:#FFFFFF"></i>
                                        <h6 class="fw-bold">{{ $confirmationData['organisation']->nom }}</h6>
                                        <small class="opacity-90">{{ ucfirst(str_replace('_', ' ', $confirmationData['organisation']->type)) }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <h6 class="fw-bold">{{ $confirmationData['adherents_stats']['total'] }} Adhérents</h6>
                                        <small class="opacity-90">{{ $confirmationData['adherents_stats']['taux_validite'] }}% de validité</small>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-calendar fa-2x mb-2"></i>
                                        <h6 class="fw-bold">{{ $confirmationData['phase2_completed_at']->format('d/m/Y') }}</h6>
                                        <small class="opacity-90">Date de finalisation</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="row">
        <!-- Colonne Gauche - Statistiques et Étapes -->
        <div class="col-lg-8">
            <!-- Statistiques des Adhérents -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-users me-2 text-white"></i>
                        Statistiques des Adhérents Importés
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Total Adhérents -->
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="stat-circle bg-primary text-white mb-2">
                                    <span class="h4 mb-0 text-white">{{ $confirmationData['adherents_stats']['total'] }}</span>
                                </div>
                                <h6 class="mb-1 text-white">Total Adhérents</h6>
                                <small class="text-warning">Importés avec succès</small>
                            </div>
                        </div>

                        <!-- Adhérents Actifs -->
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="stat-circle bg-success text-white mb-2">
                                    <span class="h4 mb-0 text-white">{{ $confirmationData['adherents_stats']['actifs'] }}</span>
                                </div>
                                <h6 class="mb-1 text-white">Actifs</h6>
                                <small class="text-warning">Comptes activés</small>
                            </div>
                        </div>

                        <!-- Sans Anomalies -->
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="stat-circle bg-info text-white mb-2">
                                    <span class="h4 mb-0 text-white">{{ $confirmationData['adherents_stats']['sans_anomalies'] }}</span>
                                </div>
                                <h6 class="mb-1 text-white">Sans Anomalies</h6>
                                <small class="text-warning">Données parfaites</small>
                            </div>
                        </div>

                        <!-- Taux de Validité -->
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="stat-circle bg-warning text-dark mb-2">
                                    <span class="h4 mb-0 text-dark">{{ $confirmationData['adherents_stats']['taux_validite'] }}%</span>
                                </div>
                                <h6 class="mb-1 text-white">Taux de Validité</h6>
                                <small class="text-warning">Qualité des données</small>
                            </div>
                        </div>
                    </div>

                    @if($confirmationData['adherents_stats']['avec_anomalies'] > 0)
                    <!-- Détails des Anomalies -->
                    <div class="mt-4">
                        <div class="alert alert-warning border-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Anomalies Détectées et Conservées
                            </h6>
                            <p class="mb-2">
                                {{ $confirmationData['adherents_stats']['avec_anomalies'] }} adhérent(s) présentent des anomalies mineures.
                                Conformément à notre politique de conservation totale, tous les adhérents ont été conservés.
                            </p>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Les anomalies peuvent être consultées et corrigées ultérieurement dans l'interface de gestion.
                                </small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Barre de progression visuelle -->
                    <div class="mt-4">
                        <h6 class="mb-2 text-white">Répartition de la Qualité des Données</h6>
                        <div class="progress" style="height: 20px;">
                            @php
                                $total = $confirmationData['adherents_stats']['total'];
                                $sansAnomalies = $confirmationData['adherents_stats']['sans_anomalies'];
                                $avecAnomalies = $confirmationData['adherents_stats']['avec_anomalies'];
                                $pourcentageSans = $total > 0 ? ($sansAnomalies / $total) * 100 : 0;
                                $pourcentageAvec = $total > 0 ? ($avecAnomalies / $total) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-success" style="width: {{ $pourcentageSans }}%">
                                {{ round($pourcentageSans) }}% parfaits
                            </div>
                            <div class="progress-bar bg-warning" style="width: {{ $pourcentageAvec }}%">
                                {{ round($pourcentageAvec) }}% avec anomalies
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prochaines Étapes -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="fas fa-route me-2 text-dark"></i>
                        Prochaines Étapes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($confirmationData['prochaines_etapes'] as $index => $etape)
                        <div class="timeline-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="timeline-marker">
                                <span class="step-number">{{ $etape['numero'] }}</span>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ $etape['titre'] }}</h6>
                                <p class="text-muted mb-1">{{ $etape['description'] }}</p>
                                <small class="text-info">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $etape['delai'] }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne Droite - Actions et Informations Complémentaires -->
        <div class="col-lg-4">
            <!-- QR Code et Vérification -->
            @if($confirmationData['qr_code'])
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-qrcode me-2 text-white"></i>
                        Code de Vérification
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="qr-code-container mb-3">
                        {!! $confirmationData['qr_code']->svg_content !!}
                    </div>
                    <p class="small text-muted mb-2">Code: {{ $confirmationData['qr_code']->code }}</p>
                    <div class="alert alert-info border-0 text-start">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Ce QR Code permet de vérifier l'authenticité de votre dossier.
                        </small>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions Disponibles -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);" class="text-white">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-download me-2 text-white"></i>
                        Documents et Rapports
                    </h5>
                </div>
                <div class="card-body">
                    

                    <!-- Téléchargement Accusé Officiel -->
                    @if($confirmationData['accuse_reception_url'])
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ $confirmationData['accuse_reception_url'] }}" class="btn btn-primary btn-lg" target="_blank">
                            <i class="fas fa-certificate me-2"></i>
                            Télécharger l'Accusé Officiel (PDF)
                        </a>
                    </div>
                    <div class="alert alert-info border-0 mb-3">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Document officiel avec QR Code de vérification à présenter à l'administration.
                        </small>
                    </div>
                    @endif

                    <!-- Télécharger Rapport des Anomalies -->
                    @if($confirmationData['adherents_stats']['avec_anomalies'] > 0)
                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-warning btn-lg" id="downloadAnomaliesBtn" 
                                data-dossier-id="{{ $confirmationData['dossier']->id }}">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Télécharger Rapport des Anomalies
                        </button>
                    </div>
                    <div class="alert alert-warning border-0 mb-3">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Rapport détaillé des {{ $confirmationData['adherents_stats']['avec_anomalies'] }} anomalie(s) détectée(s) 
                            pour correction ultérieure.
                        </small>
                    </div>
                    @endif

                    <!-- Imprimer cette page -->
                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-outline-info btn-lg" id="printPageBtn">
                            <i class="fas fa-print me-2"></i>
                            Imprimer cette Confirmation
                        </button>
                    </div>

                    <!-- Retour au Dashboard -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home me-2"></i>
                            Retour au Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations de Support -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="fas fa-headset me-2 text-dark"></i>
                        Support & Contact
                    </h5>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="mb-3">
                            <strong>Email:</strong><br>
                            <a href="mailto:{{ $confirmationData['contact_support']['email'] }}" class="text-decoration-none">
                                {{ $confirmationData['contact_support']['email'] }}
                            </a>
                        </div>
                        <div class="mb-3">
                            <strong>Téléphone:</strong><br>
                            <a href="tel:{{ $confirmationData['contact_support']['telephone'] }}" class="text-decoration-none">
                                {{ $confirmationData['contact_support']['telephone'] }}
                            </a>
                        </div>
                        <div class="mb-3">
                            <strong>Horaires:</strong><br>
                            <small class="text-muted">{{ $confirmationData['contact_support']['horaires'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Légal -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="alert alert-info border-0 mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-gavel me-2"></i>
                            Information Légale
                        </h6>
                        <p class="mb-0 small">{{ $confirmationData['message_legal'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Téléchargement Rapport Anomalies -->
<div class="modal fade" id="anomaliesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Rapport des Anomalies Détectées
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>
                        À propos des anomalies
                    </h6>
                    <p>Les anomalies détectées sont des incohérences mineures dans les données des adhérents. 
                    Conformément à notre politique de conservation totale, tous les adhérents ont été conservés.</p>
                    <p class="mb-0">Ce rapport vous permettra de corriger ces informations ultérieurement.</p>
                </div>

                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h4 class="text-warning">{{ $confirmationData['adherents_stats']['avec_anomalies'] }}</h4>
                                <small>Adhérents avec anomalies</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body">
                                <h4 class="text-success">{{ $confirmationData['adherents_stats']['sans_anomalies'] }}</h4>
                                <small>Adhérents parfaits</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body">
                                <h4 class="text-info">{{ $confirmationData['adherents_stats']['taux_validite'] }}%</h4>
                                <small>Taux de validité</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <p class="mb-3">Le rapport détaillé inclura :</p>
                    <ul class="list-unstyled text-start">
                        <li><i class="fas fa-check text-success me-2"></i>Liste complète des adhérents avec anomalies</li>
                        <li><i class="fas fa-check text-success me-2"></i>Type et description de chaque anomalie</li>
                        <li><i class="fas fa-check text-success me-2"></i>Suggestions de correction</li>
                        <li><i class="fas fa-check text-success me-2"></i>Export Excel pour faciliter les corrections</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Fermer
                </button>
                <button type="button" class="btn btn-warning" id="confirmDownloadAnomaliesBtn">
                    <i class="fas fa-download me-2"></i>
                    Télécharger le Rapport
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Animations */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse-animation {
    animation: pulse 2s infinite;
}

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

/* Styles spécifiques */
.bg-rgba-white-10 {
    background: rgba(255, 255, 255, 0.1) !important;
}

.stat-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Timeline */
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
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
}

.timeline-item.completed .timeline-marker i {
    font-size: 12px;
}

.timeline-item.active .timeline-marker {
    background: #ffc107;
    color: #000;
}

.step-number {
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.timeline-content {
    margin-left: 15px;
}

/* QR Code */
.qr-code-container svg {
    max-width: 150px;
    height: auto;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-circle {
        width: 60px;
        height: 60px;
    }
    
    .stat-circle .h4 {
        font-size: 1.1rem;
    }
}

/* Print styles pour PDF */
@media print {
    .btn, .card-header, .alert {
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .no-print {
        display: none !important;
    }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bouton de téléchargement du rapport des anomalies
    const downloadAnomaliesBtn = document.getElementById('downloadAnomaliesBtn');
    if (downloadAnomaliesBtn) {
        downloadAnomaliesBtn.addEventListener('click', function() {
            const anomaliesModal = new bootstrap.Modal(document.getElementById('anomaliesModal'));
            anomaliesModal.show();
        });
    }
    
    // Confirmation téléchargement rapport anomalies
    const confirmDownloadAnomaliesBtn = document.getElementById('confirmDownloadAnomaliesBtn');
    if (confirmDownloadAnomaliesBtn) {
        confirmDownloadAnomaliesBtn.addEventListener('click', async function() {
            const dossierId = downloadAnomaliesBtn.dataset.dossierId;
            
            try {
                // Afficher loading
                confirmDownloadAnomaliesBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Génération...';
                confirmDownloadAnomaliesBtn.disabled = true;
                
                // Construire l'URL de téléchargement
                const downloadUrl = `/operator/dossiers/${dossierId}/download-anomalies-report`;
                
                // Créer et déclencher le téléchargement
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = `rapport-anomalies-${dossierId}.xlsx`;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Fermer modal après délai
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('anomaliesModal')).hide();
                }, 1000);
                
                showSuccessAlert('Rapport des anomalies téléchargé avec succès !');
                
            } catch (error) {
                console.error('Erreur téléchargement rapport:', error);
                showErrorAlert('Erreur lors du téléchargement: ' + error.message);
            } finally {
                // Restaurer bouton
                confirmDownloadAnomaliesBtn.innerHTML = '<i class="fas fa-download me-2"></i>Télécharger le Rapport';
                confirmDownloadAnomaliesBtn.disabled = false;
            }
        });
    }
    
    // Bouton d'impression
    const printPageBtn = document.getElementById('printPageBtn');
    if (printPageBtn) {
        printPageBtn.addEventListener('click', function() {
            // Préparer la page pour l'impression
            const originalTitle = document.title;
            document.title = 'Confirmation Finale - {{ $confirmationData["numero_dossier"] }}';
            
            // Cacher les éléments non imprimables
            const nonPrintElements = document.querySelectorAll('.btn, .modal');
            nonPrintElements.forEach(el => el.classList.add('no-print'));
            
            // Imprimer
            window.print();
            
            // Restaurer après impression
            setTimeout(() => {
                document.title = originalTitle;
                nonPrintElements.forEach(el => el.classList.remove('no-print'));
            }, 1000);
        });
    }
    
    // Fonctions utilitaires pour les alertes
    function showSuccessAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
    
    function showErrorAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 8000);
    }
});
</script>
@endsection