{{--
============================================================================
CONFIRMATION.BLADE.PHP - REFONTE DESIGN COMPLÈTE STYLE SGLP
Version: 3.0 - Design moderne inspiré de confirmation_design.blade.php
Refonte complète de l'interface utilisateur avec animations et couleurs gabonaises
============================================================================
--}}

@extends('layouts.operator')

@section('title', 'Confirmation Finale - Dossier Soumis avec Succès')

@section('page-title', 'Confirmation Finale du Dossier')

@section('content')
<div class="container-fluid">
    {{-- ✅ Variables PHP corrigées pour la nouvelle interface --}}
    @php
    // ✅ Extraction des données depuis confirmationData
    $confirmationData = $confirmationData ?? [];

    // Dossier et organisation depuis confirmationData
    $dossier = $confirmationData['dossier'] ?? (object)[
        'id' => 0,
        'numero_dossier' => 'N/A',
        'numero_recepisse' => 'En attente'
    ];

    $organisation = $confirmationData['organisation'] ?? (object)[
        'id' => 0,
        'nom' => 'Organisation non définie',
        'type' => 'association',
        'sigle' => ''
    ];

   // Statistiques simplifiées sans chargement adhérents
$adherents_stats = [
    'total' => $organisation->adherents()->count(),
    'actifs' => $organisation->adherents()->where('is_active', true)->count(),
    'sans_anomalies' => $organisation->adherents()->whereNull('anomalies')->count(),
    'avec_anomalies' => $organisation->adherents()->whereNotNull('anomalies')->count(),
    'taux_validite' => 100
];

if ($adherents_stats['total'] > 0) {
    $adherents_stats['taux_validite'] = round(($adherents_stats['sans_anomalies'] / $adherents_stats['total']) * 100, 1);
}
    

    // Nouvelles variables pour le design
    $phase_name = $confirmationData['phase_name'] ?? 'Phase 2 Terminée avec Succès';
    $phase2_completed_at = now(); // Date de finalisation
    $qr_code = $confirmationData['qr_code'] ?? null;
    
    
    // Prochaines étapes depuis confirmationData ou par défaut
    $prochaines_etapes = $confirmationData['prochaines_etapes'] ?? [
        [
            'numero' => 1,
            'titre' => 'Assignation d\'un agent',
            'description' => 'Un agent sera assigné à votre dossier sous 48h ouvrées',
            'delai' => '48h ouvrées'
        ],
        [
            'numero' => 2,
            'titre' => 'Examen du dossier',
            'description' => 'Votre dossier sera examiné selon l\'ordre d\'arrivée (système FIFO)',
            'delai' => '72h ouvrées'
        ],
        [
            'numero' => 3,
            'titre' => 'Notification du résultat',
            'description' => 'Vous recevrez une notification par email de l\'évolution',
            'delai' => 'Variable'
        ],
        [
            'numero' => 4,
            'titre' => 'Dépôt physique requis',
            'description' => 'Déposer le dossier physique en 3 exemplaires à la DGELP',
            'delai' => 'Dans les 7 jours'
        ]
    ];

    // Contact support
    $contact_support = $confirmationData['contact_support'] ?? [
        'email' => 'support@pngdi.ga',
        'telephone' => '+241 01 23 45 67',
        'horaires' => 'Lundi - Vendredi: 08h00 - 17h00'
    ];

    // Message légal
    $message_legal = $confirmationData['message_legal'] ?? 'Votre dossier numérique a été soumis avec succès. Conformément aux dispositions légales en vigueur, vous devez déposer votre dossier physique en 3 exemplaires auprès de la Direction Générale des Élections et des Libertés Publiques.';

    // URLs pour téléchargements
    $accuse_reception_url = $confirmationData['accuse_reception_url'] ?? null;
    @endphp

    {{-- Header Principal - Succès Phase 2 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
                <div class="card-body text-white text-center py-5 position-relative">
                    {{-- Animation de succès --}}
                    <div class="success-animation mb-4">
                        <div class="checkmark-container">
                            <i class="fas fa-check-circle fa-6x text-white opacity-90 pulse-animation"></i>
                        </div>
                    </div>
                    
                    <h1 class="mb-3 display-6">🎉 Dossier soumis avec Succès !</h1>
                    <h2 class="h4 mb-3">
                        Dossier {{ $dossier->numero_dossier ?? 'N/A' }} <br/>
                        Informations de l'organisation et liste des Adhérents enregistrées
                    </h2>
                    
                    {{-- Badge de statut --}}
                    <div class="d-flex justify-content-center mb-4">
                        <span class="badge bg-light text-success px-4 py-2 fs-6">
                            <i class="fas fa-star me-2"></i>
                            {{ $phase_name }}
                        </span>
                    </div>
                    
                    {{-- Informations essentielles --}}
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="alert alert-light border-0 bg-rgba-white-10">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <i class="fas fa-building fa-2x mb-2 text-white"></i>
                                        <h6 class="fw-bold">{{ $organisation->nom ?? 'Organisation non définie' }}</h6>
                                        <small class="opacity-90">{{ ucfirst(str_replace('_', ' ', $organisation->type ?? 'association')) }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-users fa-2x mb-2 text-white"></i>
                                        <h6 class="fw-bold">{{ $adherents_stats['total'] }} Adhérents</h6>
                                        <small class="opacity-90">{{ $adherents_stats['taux_validite'] }}% de validité</small>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-calendar fa-2x mb-2 text-white"></i>
                                        <h6 class="fw-bold">{{ $phase2_completed_at->format('d/m/Y') }}</h6>
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

    {{-- Contenu Principal --}}
    <div class="row">
        {{-- Colonne Gauche - Statistiques et Étapes --}}
        <div class="col-lg-8">
            
        {{-- Statistiques Simplifiées --}}
<div class="card shadow-sm mb-4">
    <div class="card-header text-white" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
        <h5 class="card-title mb-0 text-white">
            <i class="fas fa-users me-2 text-white"></i>
            Statistiques de l'Import
        </h5>
    </div>
    <div class="card-body text-center">
        <div class="row">
            <div class="col-md-6">
                <h2 class="text-success">{{ number_format($adherents_stats['total']) }}</h2>
                <p class="text-muted">Adhérents Enregistrés</p>
            </div>
            <div class="col-md-6">
                <h2 class="text-primary">✅</h2>
                <p class="text-muted">Import Réussi</p>
            </div>
        </div>
        <div class="alert alert-success mt-3">
            <i class="fas fa-check-circle me-2"></i>
            Tous les adhérents ont été enregistrés avec succès dans la base de données.
        </div>
    </div>
</div>

            {{-- Prochaines Étapes --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header text-dark" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="fas fa-route me-2 text-dark"></i>
                        Prochaines Étapes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($prochaines_etapes as $index => $etape)
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

        {{-- Colonne Droite - Actions et Informations Complémentaires --}}
        <div class="col-lg-4">
            {{-- QR Code et Vérification --}}
            @if($qr_code)
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-qrcode me-2 text-white"></i>
                        Code de Vérification
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="qr-code-container mb-3">
                        @if(isset($qr_code->svg_content))
                            {!! $qr_code->svg_content !!}
                        @else
                            <div class="placeholder-qr">
                                <i class="fas fa-qrcode fa-8x text-muted"></i>
                                <p class="mt-2 text-muted">QR Code en génération</p>
                            </div>
                        @endif
                    </div>
                    <p class="small text-muted mb-2">Code: {{ $qr_code->code ?? 'En cours' }}</p>
                    <div class="alert alert-info border-0 text-start">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Ce QR Code permet de vérifier l'authenticité de votre dossier.
                        </small>
                    </div>
                </div>
            </div>
            @endif

            {{-- Actions Disponibles --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-download me-2 text-white"></i>
                        Documents et Rapports
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Téléchargement Accusé Officiel --}}
                    @if($accuse_reception_url)
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ $accuse_reception_url }}" class="btn btn-lg" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%); color: white;" target="_blank">
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
                    @else
                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-outline-success btn-lg" disabled>
                            <i class="fas fa-certificate me-2"></i>
                            Accusé en génération...
                        </button>
                    </div>
                    <div class="alert alert-warning border-0 mb-3">
                        <small>
                            <i class="fas fa-clock me-1"></i>
                            L'accusé de réception officiel sera disponible sous peu.
                        </small>
                    </div>
                    @endif

                    

                    {{-- Imprimer cette page --}}
                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-outline-info btn-lg" id="printPageBtn">
                            <i class="fas fa-print me-2"></i>
                            Imprimer cette Confirmation
                        </button>
                    </div>

                    {{-- Retour au Dashboard --}}
                    <div class="d-grid gap-2">
                        <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home me-2"></i>
                            Retour au Dashboard
                        </a>
                    </div>
                </div>
            </div>

            {{-- Informations de Support --}}
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
                            <a href="mailto:{{ $contact_support['email'] }}" class="text-decoration-none">
                                {{ $contact_support['email'] }}
                            </a>
                        </div>
                        <div class="mb-3">
                            <strong>Téléphone:</strong><br>
                            <a href="tel:{{ $contact_support['telephone'] }}" class="text-decoration-none">
                                {{ $contact_support['telephone'] }}
                            </a>
                        </div>
                        <div class="mb-3">
                            <strong>Horaires:</strong><br>
                            <small class="text-muted">{{ $contact_support['horaires'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Message Légal --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="alert alert-info border-0 mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-gavel me-2"></i>
                            Information Légale
                        </h6>
                        <p class="mb-0 small">{{ $message_legal }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- Styles CSS modernisés --}}
<style>
/* ============================================================================
   STYLES CSS MODERNISÉS AVEC COULEURS GABONAISES
   ============================================================================ */

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

@keyframes slideInFromLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.timeline-item {
    animation: slideInFromLeft 0.6s ease-out;
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
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.stat-circle:hover {
    transform: scale(1.05);
}

/* Timeline modernisée */
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
    width: 3px;
    background: linear-gradient(to bottom, #009e3f, #ffcd00, #003f7f);
    border-radius: 2px;
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
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.timeline-item.completed .timeline-marker {
    background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);
}

.timeline-item.completed .timeline-marker i {
    font-size: 12px;
}

.timeline-item.active .timeline-marker {
    background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);
    color: #000;
    transform: scale(1.1);
}

.step-number {
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.timeline-content {
    margin-left: 15px;
    padding: 15px;
    background: rgba(248, 249, 250, 0.8);
    border-radius: 8px;
    border-left: 3px solid #009e3f;
    transition: all 0.3s ease;
}

.timeline-content:hover {
    background: rgba(248, 249, 250, 1);
    transform: translateX(5px);
}

/* QR Code modernisé */
.qr-code-container {
    padding: 20px;
    background: rgba(248, 249, 250, 0.9);
    border-radius: 15px;
    border: 2px dashed #009e3f;
}

.qr-code-container svg {
    max-width: 150px;
    height: auto;
    border-radius: 8px;
}

.placeholder-qr {
    padding: 30px;
    background: rgba(248, 249, 250, 0.5);
    border-radius: 15px;
    border: 2px dashed #ccc;
}

/* Boutons modernisés */
.btn {
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Cards améliorées */
.card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border: none;
    padding: 20px;
}

/* Progress bar gabonaise */
.progress {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    transition: width 1s ease-in-out;
    border-radius: 10px;
}

/* Responsive amélioré */
@media (max-width: 768px) {
    .stat-circle {
        width: 60px;
        height: 60px;
    }
    
    .stat-circle .h4 {
        font-size: 1.1rem;
    }

    .timeline-content {
        margin-left: 10px;
        padding: 10px;
    }

    .card-body {
        padding: 15px;
    }

    .display-6 {
        font-size: 2rem;
    }
}

/* Print styles optimisés */
@media print {
    .btn, .modal, .timeline-marker {
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .no-print {
        display: none !important;
    }

    .card {
        break-inside: avoid;
        margin-bottom: 20px;
    }
    
    .pulse-animation {
        animation: none;
    }
}

/* Alertes modernisées */
.alert {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.alert-info {
    background: linear-gradient(135deg, rgba(13, 202, 240, 0.1), rgba(13, 202, 240, 0.2));
}

.alert-warning {
    background: linear-gradient(135deg, rgba(255, 205, 0, 0.1), rgba(255, 165, 0, 0.2));
}

/* Badges modernisés */
.badge {
    border-radius: 8px;
    padding: 8px 16px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Contact info stylisé */
.contact-info a {
    color: #009e3f;
    transition: color 0.3s ease;
}

.contact-info a:hover {
    color: #006d2c;
    text-decoration: underline !important;
}
</style>

{{-- Scripts JavaScript modernisés --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🇬🇦 Initialisation Page Confirmation Design SGLP v3.0');

    
    // ============================================================================
    // GESTION DE L'IMPRESSION
    // ============================================================================
    
    // Bouton d'impression modernisé
    const printPageBtn = document.getElementById('printPageBtn');
    if (printPageBtn) {
        printPageBtn.addEventListener('click', function() {
            // Préparer la page pour l'impression
            const originalTitle = document.title;
            document.title = 'Confirmation Finale - {{ $dossier->numero_dossier ?? "Dossier" }}';
            
            // Cacher les éléments non imprimables
            const nonPrintElements = document.querySelectorAll('.btn, .modal, .no-print');
            nonPrintElements.forEach(el => el.classList.add('d-print-none'));
            
            // Ajouter un message d'impression
            showInfoAlert('🖨️ Préparation de l\'impression en cours...');
            
            // Délai pour permettre le rendu
            setTimeout(() => {
                window.print();
                
                // Restaurer après impression
                setTimeout(() => {
                    document.title = originalTitle;
                    nonPrintElements.forEach(el => el.classList.remove('d-print-none'));
                }, 1000);
            }, 500);
        });
    }
    
    // ============================================================================
    // ANIMATIONS ET EFFETS VISUELS
    // ============================================================================
    
    // Animation des statistiques au chargement
    animateStatCircles();
    
    // Animation de la timeline
    animateTimelineItems();
    
    // Animation du QR Code
    animateQRCode();
    
    // ============================================================================
    // FONCTIONS UTILITAIRES
    // ============================================================================
    
    /**
     * Animer les cercles de statistiques
     */
    function animateStatCircles() {
        const statCircles = document.querySelectorAll('.stat-circle');
        statCircles.forEach((circle, index) => {
            setTimeout(() => {
                circle.style.transform = 'scale(0)';
                circle.style.transition = 'transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                
                setTimeout(() => {
                    circle.style.transform = 'scale(1)';
                }, 100);
            }, index * 200);
        });
    }
    
    /**
     * Animer les éléments de la timeline
     */
    function animateTimelineItems() {
        const timelineItems = document.querySelectorAll('.timeline-item');
        timelineItems.forEach((item, index) => {
            setTimeout(() => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-50px)';
                item.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 100);
            }, index * 300);
        });
    }
    
    /**
     * Animer le QR Code
     */
    function animateQRCode() {
        const qrContainer = document.querySelector('.qr-code-container');
        if (qrContainer) {
            setTimeout(() => {
                qrContainer.style.transform = 'scale(0.8) rotate(-5deg)';
                qrContainer.style.transition = 'transform 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                
                setTimeout(() => {
                    qrContainer.style.transform = 'scale(1) rotate(0deg)';
                }, 100);
            }, 1000);
        }
    }
    
    /**
     * Afficher une alerte de succès modernisée
     */
    function showSuccessAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = `
            top: 20px; 
            right: 20px; 
            z-index: 9999; 
            min-width: 350px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);
            color: white;
            border: none;
        `;
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Animation d'entrée
        setTimeout(() => alertDiv.classList.add('show'), 100);
        
        // Suppression automatique
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 5000);
    }
    
    /**
     * Afficher une alerte d'erreur modernisée
     */
    function showErrorAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = `
            top: 20px; 
            right: 20px; 
            z-index: 9999; 
            min-width: 350px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
        `;
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Animation d'entrée
        setTimeout(() => alertDiv.classList.add('show'), 100);
        
        // Suppression automatique
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 8000);
    }
    
    /**
     * Afficher une alerte d'information modernisée
     */
    function showInfoAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = `
            top: 20px; 
            right: 20px; 
            z-index: 9999; 
            min-width: 350px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border: none;
        `;
        alertDiv.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Animation d'entrée
        setTimeout(() => alertDiv.classList.add('show'), 100);
        
        // Suppression automatique
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 3000);
    }
    
    // ============================================================================
    // INITIALISATION FINALE
    // ============================================================================
    
    console.log('✅ Page Confirmation Design SGLP v3.0 - Initialisée avec succès');
    
    // Afficher message de bienvenue
    setTimeout(() => {
        showSuccessAlert('🎉 Félicitations ! Votre dossier a été soumis avec succès.');
    }, 1000);
});
</script>

@endsection