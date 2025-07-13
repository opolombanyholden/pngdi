{{--
============================================================================
CONFIRMATION.BLADE.PHP - VERSION COMPLÈTEMENT CORRIGÉE
Version: 5.0 - Correction variable $dossier undefined
============================================================================
--}}

@extends('layouts.operator')

@section('title', 'Confirmation - Dossier Soumis avec Succès')

@section('page-title', 'Confirmation de Soumission')

@section('content')
<div class="container-fluid">
    {{-- ✅ CORRECTION CRITIQUE: Gestion robuste des variables --}}
    @php
    // Vérifier et initialiser les données de confirmation
    $confirmationData = $confirmationData ?? [];
    
    // Si confirmationData n'existe pas, utiliser les variables directes (compatibilité)
    if (empty($confirmationData) && isset($dossier)) {
        $confirmationData = [
            'dossier' => $dossier,
            'organisation' => $organisation ?? $dossier->organisation ?? (object)['nom' => 'Organisation inconnue', 'type' => 'association'],
            'adherents_stats' => [
                'total' => 0,
                'valides' => 0,
                'anomalies_critiques' => 0,
                'anomalies_majeures' => 0,
                'anomalies_mineures' => 0
            ],
            'qr_code' => null,
            'accuse_reception_url' => null,
            'prochaines_etapes' => [],
            'contact_support' => [
                'email' => 'support@pngdi.ga',
                'telephone' => '+241 01 23 45 67',
                'horaires' => 'Lundi - Vendredi: 08h00 - 17h00'
            ],
            'message_legal' => 'Votre dossier a été soumis avec succès.'
        ];
    }

    // Extraction sécurisée des données
    $dossierData = $confirmationData['dossier'] ?? (object)[
        'id' => 0,
        'numero_dossier' => 'N/A'
    ];

    $organisationData = $confirmationData['organisation'] ?? (object)[
        'id' => 0,
        'nom' => 'Organisation non définie',
        'type' => 'association',
        'sigle' => ''
    ];

    // ✅ CORRECTION : Statistiques simplifiées sans requêtes problématiques
    $adherents_stats = $confirmationData['adherents_stats'] ?? [
        'total' => 0,
        'valides' => 0,
        'anomalies_critiques' => 0,
        'anomalies_majeures' => 0,
        'anomalies_mineures' => 0
    ];

    // Calculer le taux de réussite
    $taux_validite = $adherents_stats['total'] > 0 
        ? round((($adherents_stats['total'] - $adherents_stats['anomalies_critiques']) / $adherents_stats['total']) * 100, 1)
        : 100;

    // Autres données
    $qr_code = $confirmationData['qr_code'] ?? null;
    $accuse_reception_url = $confirmationData['accuse_reception_url'] ?? null;
    
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
            'description' => 'Votre dossier sera examiné selon l\'ordre d\'arrivée',
            'delai' => '72h ouvrées'
        ],
        [
            'numero' => 3,
            'titre' => 'Notification du résultat',
            'description' => 'Vous recevrez une notification par email',
            'delai' => 'Variable'
        ],
        [
            'numero' => 4,
            'titre' => 'Dépôt physique requis',
            'description' => 'Déposer le dossier physique en 3 exemplaires à la DGELP',
            'delai' => 'Dans les 7 jours'
        ]
    ];

    $contact_support = $confirmationData['contact_support'] ?? [
        'email' => 'support@pngdi.ga',
        'telephone' => '+241 01 23 45 67',
        'horaires' => 'Lundi - Vendredi: 08h00 - 17h00'
    ];

    $message_legal = $confirmationData['message_legal'] ?? 'Votre dossier numérique a été soumis avec succès. Conformément aux dispositions légales en vigueur, vous devez déposer votre dossier physique en 3 exemplaires auprès de la Direction Générale des Élections et des Libertés Publiques.';
    @endphp

    {{-- HEADER PRINCIPAL - SUCCÈS --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
                <div class="card-body text-white text-center py-5">
                    {{-- Icône de succès --}}
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-6x text-white opacity-90"></i>
                    </div>
                    
                    <h1 class="mb-3 display-6">🎉 Dossier soumis avec Succès !</h1>
                    <h2 class="h4 mb-3">
                        Dossier {{ $dossierData->numero_dossier ?? 'N/A' }}
                    </h2>
                    
                    {{-- Badge de statut --}}
                    <div class="d-flex justify-content-center mb-4">
                        <span class="badge bg-light text-success px-4 py-2 fs-6">
                            <i class="fas fa-star me-2"></i>
                            Soumission terminée avec succès
                        </span>
                    </div>
                    
                    {{-- Informations essentielles --}}
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="alert alert-light border-0 bg-rgba-white-10">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <i class="fas fa-building fa-2x mb-2 text-white"></i>
                                        <h6 class="fw-bold">{{ $organisationData->nom ?? 'Organisation non définie' }}</h6>
                                        <small class="opacity-90">{{ ucfirst(str_replace('_', ' ', $organisationData->type ?? 'association')) }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-users fa-2x mb-2 text-white"></i>
                                        <h6 class="fw-bold">{{ number_format($adherents_stats['total']) }} Adhérents</h6>
                                        <small class="opacity-90">{{ $taux_validite }}% de validité</small>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-calendar fa-2x mb-2 text-white"></i>
                                        <h6 class="fw-bold">{{ now()->format('d/m/Y') }}</h6>
                                        <small class="opacity-90">Date de soumission</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENU PRINCIPAL --}}
    <div class="row">
        {{-- Colonne Gauche - Statistiques et Étapes --}}
        <div class="col-lg-8">
            
            {{-- ✅ STATISTIQUES SIMPLIFIÉES --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-chart-pie me-2 text-white"></i>
                        Statistiques des Adhérents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-circle bg-primary text-white">
                                <h3 class="mb-0">{{ number_format($adherents_stats['total']) }}</h3>
                            </div>
                            <p class="mt-2 mb-0 text-muted">Total Adhérents</p>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-circle bg-success text-white">
                                <h3 class="mb-0">{{ number_format($adherents_stats['total'] - $adherents_stats['anomalies_critiques']) }}</h3>
                            </div>
                            <p class="mt-2 mb-0 text-muted">Valides</p>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-circle bg-warning text-white">
                                <h3 class="mb-0">{{ number_format($adherents_stats['anomalies_majeures'] + $adherents_stats['anomalies_mineures']) }}</h3>
                            </div>
                            <p class="mt-2 mb-0 text-muted">Anomalies Mineures</p>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-circle bg-danger text-white">
                                <h3 class="mb-0">{{ number_format($adherents_stats['anomalies_critiques']) }}</h3>
                            </div>
                            <p class="mt-2 mb-0 text-muted">Anomalies Critiques</p>
                        </div>
                    </div>
                    
                    {{-- Barre de progression --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Taux de Validité</strong>
                            <span class="badge bg-success">{{ $taux_validite }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $taux_validite }}%"></div>
                        </div>
                    </div>
                    
                    @if($adherents_stats['total'] > 0)
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Import réussi !</strong> Tous les adhérents ont été enregistrés dans la base de données.
                        @if($adherents_stats['anomalies_critiques'] > 0)
                        <br><small>Remarque : {{ $adherents_stats['anomalies_critiques'] }} adhérent(s) avec anomalies critiques nécessitent une correction.</small>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- PROCHAINES ÉTAPES --}}
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

        {{-- Colonne Droite - Actions et QR Code --}}
        <div class="col-lg-4">
            
            {{-- QR CODE DE VÉRIFICATION --}}
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
                            <div class="qr-display">
                                {!! $qr_code->svg_content !!}
                            </div>
                        @else
                            <div class="placeholder-qr">
                                <i class="fas fa-qrcode fa-6x text-muted"></i>
                                <p class="mt-2 text-muted">QR Code en génération</p>
                            </div>
                        @endif
                    </div>
                    <p class="small text-muted mb-2">Code: <strong>{{ $qr_code->code ?? 'En cours' }}</strong></p>
                    <div class="alert alert-info border-0 text-start">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Ce QR Code permet de vérifier l'authenticité de votre dossier.
                        </small>
                    </div>
                </div>
            </div>
            @endif

            {{-- ACTIONS ET TÉLÉCHARGEMENTS --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-download me-2 text-white"></i>
                        Documents Disponibles
                    </h5>
                </div>
                <div class="card-body">
                    
                    {{-- Téléchargement Accusé de Réception --}}
                    @if($accuse_reception_url)
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ $accuse_reception_url }}" class="btn btn-lg btn-success" target="_blank">
                            <i class="fas fa-certificate me-2"></i>
                            Télécharger l'Accusé de Réception (PDF)
                        </a>
                    </div>
                    <div class="alert alert-success border-0 mb-3">
                        <small>
                            <i class="fas fa-check-circle me-1"></i>
                            Document officiel avec QR Code de vérification.
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
                            L'accusé de réception sera disponible sous peu.
                        </small>
                    </div>
                    @endif

                    {{-- Rapport d'Anomalies --}}
                    @if($adherents_stats['anomalies_critiques'] > 0 || $adherents_stats['anomalies_majeures'] > 0 || $adherents_stats['anomalies_mineures'] > 0)
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('operator.dossiers.rapport-anomalies', $dossierData->id ?? 0) }}" class="btn btn-outline-warning btn-lg" target="_blank">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Télécharger le Rapport d'Anomalies (PDF)
                        </a>
                    </div>
                    <div class="alert alert-warning border-0 mb-3">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Détail des {{ $adherents_stats['anomalies_critiques'] + $adherents_stats['anomalies_majeures'] + $adherents_stats['anomalies_mineures'] }} anomalie(s) détectée(s).
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

            {{-- INFORMATIONS DE SUPPORT --}}
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

    {{-- MESSAGE LÉGAL --}}
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

{{-- STYLES CSS SIMPLIFIÉS --}}
<style>
/* ============================================================================
   STYLES CSS SIMPLIFIÉS POUR CONFIRMATION
   ============================================================================ */

/* Styles généraux */
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

/* Timeline simplifiée */
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
}

.timeline-item.active .timeline-marker {
    background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);
    color: #000;
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
}

/* QR Code */
.qr-code-container {
    padding: 20px;
    background: rgba(248, 249, 250, 0.9);
    border-radius: 15px;
    border: 2px dashed #009e3f;
}

.qr-display svg {
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

/* Boutons */
.btn {
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Cards */
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

/* Progress bar */
.progress {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    transition: width 1s ease-in-out;
    border-radius: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-circle {
        width: 60px;
        height: 60px;
    }
    
    .stat-circle h3 {
        font-size: 1.2rem;
    }

    .timeline-content {
        margin-left: 10px;
        padding: 10px;
    }

    .card-body {
        padding: 15px;
    }

    .display-6 {
        font-size: 1.8rem;
    }
}

/* Print */
@media print {
    .btn, .modal {
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
}

/* Alertes */
.alert {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Contact info */
.contact-info a {
    color: #009e3f;
    transition: color 0.3s ease;
}

.contact-info a:hover {
    color: #006d2c;
    text-decoration: underline !important;
}
</style>

{{-- JAVASCRIPT SIMPLIFIÉ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🇬🇦 Initialisation Page Confirmation SGLP - Version Corrigée');

    // Gestion de l'impression
    const printPageBtn = document.getElementById('printPageBtn');
    if (printPageBtn) {
        printPageBtn.addEventListener('click', function() {
            const originalTitle = document.title;
            document.title = 'Confirmation - {{ $dossierData->numero_dossier ?? "Dossier" }}';
            
            // Préparer pour impression
            const nonPrintElements = document.querySelectorAll('.btn, .modal, .no-print');
            nonPrintElements.forEach(el => el.classList.add('d-print-none'));
            
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
    
    // Animation des statistiques
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
    
    console.log('✅ Page Confirmation - Initialisée avec succès');
});

</script>
@endsection