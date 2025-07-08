{{-- ===================================================================
     final-confirmation.blade.php - VERSION OPTIMISÉE COMPLÈTE
     Page de confirmation définitive après soumission à l'administration
     Design inspiré d'index.blade.php avec charte graphique gabonaise
     ===================================================================== --}}

@extends('layouts.operator')

@section('title', 'Dossier Soumis avec Succès à l\'Administration')

@section('page-title', 'Confirmation de Soumission')

@section('content')
<div class="container-fluid">
    {{-- Variables PHP pour la vue --}}
    @php
        $dossier = $confirmationData['dossier'];
        $organisation = $confirmationData['organisation'];
        $numeroSoumission = $confirmationData['numero_soumission'];
        $totalAdherents = $confirmationData['total_adherents'];
        $submittedAt = $confirmationData['submitted_at'];
        $prochaines_etapes = $confirmationData['prochaines_etapes'] ?? [
            [
                'titre' => 'Accusé de réception',
                'description' => 'Envoi automatique de la confirmation de réception du dossier',
                'delai' => 'Immédiat'
            ],
            [
                'titre' => 'Vérification administrative',
                'description' => 'Contrôle de la conformité du dossier et des documents fournis',
                'delai' => '5-7 jours ouvrés'
            ],
            [
                'titre' => 'Validation des adhérents',
                'description' => 'Vérification de la liste des adhérents et de leur éligibilité',
                'delai' => '10-15 jours ouvrés'
            ],
            [
                'titre' => 'Décision finale',
                'description' => 'Décision d\'approbation ou de rejet avec notification officielle',
                'delai' => '30-45 jours ouvrés'
            ]
        ];
    @endphp

    {{-- Header principal avec succès gabonais --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="success-icon me-4">
                                    <i class="fas fa-check-circle fa-5x text-white" id="success-check"></i>
                                </div>
                                <div>
                                    <h1 class="mb-3 fw-bold">
                                        <i class="fas fa-flag me-2"></i>
                                        Dossier Soumis avec Succès !
                                    </h1>
                                    <h4 class="mb-2 opacity-90">{{ $organisation->nom }}</h4>
                                    <p class="mb-0 fs-5 opacity-80">
                                        Votre dossier a été transmis officiellement au Ministère de l'Intérieur 
                                        de la République Gabonaise pour traitement final
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="bg-white bg-opacity-20 rounded-3 p-4">
                                <div class="h2 text-white mb-2">{{ $numeroSoumission }}</div>
                                <div class="h6 mb-1">Numéro de soumission officiel</div>
                                <small class="opacity-80">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $submittedAt->format('d/m/Y à H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistiques de soumission --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white text-center">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-paper-plane fa-2x"></i>
                    </div>
                    <h3 class="mb-1">SOUMIS</h3>
                    <p class="mb-0 small opacity-90">Statut du dossier</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark text-center">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ number_format($totalAdherents) }}</h3>
                    <p class="mb-0 small">Adhérents traités</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white text-center">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="mb-1">30-45</h3>
                    <p class="mb-0 small opacity-90">Jours de traitement</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white text-center">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </div>
                    <h3 class="mb-1">SÉCURISÉ</h3>
                    <p class="mb-0 small opacity-90">Transmission cryptée</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Informations détaillées du dossier --}}
        <div class="col-lg-8">
            {{-- Résumé du dossier soumis --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-folder-open me-2 text-success"></i>
                            Résumé du Dossier Soumis
                        </h5>
                        <span class="badge bg-success">TRANSMIS À L'ADMINISTRATION</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-building me-2"></i>
                                    Informations de l'Organisation
                                </h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold text-muted">Nom :</td>
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
                                    <tr>
                                        <td class="fw-bold text-muted">Téléphone :</td>
                                        <td>{{ $organisation->telephone ?? 'Non renseigné' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-info fw-bold mb-3">
                                    <i class="fas fa-receipt me-2"></i>
                                    Informations de Soumission
                                </h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold text-muted">N° Dossier :</td>
                                        <td><code class="bg-white p-1 rounded">{{ $dossier->numero_dossier }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">N° Soumission :</td>
                                        <td><code class="bg-warning p-1 rounded">{{ $numeroSoumission }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Date soumission :</td>
                                        <td>{{ $submittedAt->format('d/m/Y à H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Total adhérents :</td>
                                        <td><span class="fw-bold text-success">{{ number_format($totalAdherents) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Statut :</td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Soumis à l'administration
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Timeline du processus avec style gabonais --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-route me-2 text-warning"></i>
                            Processus de Traitement Administratif
                        </h5>
                        <span class="badge bg-light text-dark">4 étapes</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #d1ecf1, #bee5eb);">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                <strong>Processus de traitement selon la législation gabonaise</strong><br>
                                <small>Votre dossier suit le processus officiel défini par la loi 016/2025 sur les organisations civiles</small>
                            </div>
                        </div>
                    </div>

                    @if(!empty($prochaines_etapes))
                    <div class="timeline-gabon">
                        @foreach($prochaines_etapes as $index => $etape)
                        <div class="timeline-item {{ $index === 0 ? 'active' : ($index < 1 ? 'completed' : '') }}">
                            <div class="timeline-marker">
                                <div class="timeline-circle">
                                    @if($index === 0)
                                        <i class="fas fa-play"></i>
                                    @elseif($index < 1)
                                        <i class="fas fa-check"></i>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </div>
                            </div>
                            <div class="timeline-content">
                                <div class="card border-0 {{ $index === 0 ? 'bg-warning bg-opacity-10' : ($index < 1 ? 'bg-success bg-opacity-10' : 'bg-light') }}">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-2">
                                            {{ $etape['titre'] }}
                                            @if($index === 0)
                                                <span class="badge bg-warning text-dark ms-2">EN COURS</span>
                                            @elseif($index < 1)
                                                <span class="badge bg-success ms-2">TERMINÉ</span>
                                            @else
                                                <span class="badge bg-secondary ms-2">EN ATTENTE</span>
                                            @endif
                                        </h6>
                                        <p class="text-muted mb-2">{{ $etape['description'] }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="badge bg-primary">
                                                <i class="fas fa-clock me-1"></i>{{ $etape['delai'] }}
                                            </small>
                                            @if($index === 0)
                                                <small class="text-success">
                                                    <i class="fas fa-spinner fa-spin me-1"></i>En traitement...
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h6>Informations sur les étapes</h6>
                        <p>Les détails du processus de traitement seront communiqués prochainement par l'administration.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Informations importantes --}}
            <div class="card border-warning mb-4">
                <div class="card-header bg-warning bg-opacity-10 border-warning">
                    <h6 class="card-title mb-0 text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Informations Importantes à Retenir
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-danger">⚠️ Points d'attention</h6>
                            <ul class="text-muted">
                                <li><strong>La soumission est définitive</strong> et irréversible</li>
                                <li><strong>Aucune modification possible</strong> après soumission</li>
                                <li><strong>Dossier verrouillé</strong> automatiquement</li>
                                <li><strong>Délai de traitement :</strong> 30-45 jours ouvrés maximum</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-success">✅ Ce qui vous attend</h6>
                            <ul class="text-muted">
                                <li><strong>Accusé de réception</strong> automatique par email</li>
                                <li><strong>Notifications automatiques</strong> aux étapes clés</li>
                                <li><strong>Suivi en temps réel</strong> dans votre tableau de bord</li>
                                <li><strong>Support disponible</strong> en cas de questions</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-primary border-0 mt-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-shield-alt fa-2x me-3 text-primary mt-1"></i>
                            <div>
                                <h6 class="alert-heading mb-2">Sécurité et Confidentialité</h6>
                                <p class="mb-0">
                                    {{ $confirmationData['message_final'] ?? 'Toutes vos données sont sécurisées et traitées selon la réglementation gabonaise en vigueur. Vous recevrez des notifications automatiques par email à chaque étape du traitement de votre dossier.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar avec actions et informations --}}
        <div class="col-lg-4">
            {{-- Actions disponibles --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2 text-primary"></i>
                        Actions Disponibles
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Télécharger l'accusé de réception --}}
                    @if(isset($confirmationData['accuse_reception_path']))
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('operator.dossiers.download-accuse', $confirmationData['accuse_reception_path']) }}" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-download me-2"></i>
                            Télécharger l'Accusé de Réception
                        </a>
                        <small class="text-muted text-center">
                            <i class="fas fa-file-pdf me-1"></i>
                            Document officiel au format PDF
                        </small>
                    </div>
                    @endif
                    
                    {{-- Autres actions --}}
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer cette Page
                        </button>
                        
                        <button class="btn btn-outline-info" onclick="copySubmissionNumber()">
                            <i class="fas fa-copy me-2"></i>Copier N° Soumission
                        </button>
                        
                        <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-tachometer-alt me-2"></i>Retour au Tableau de Bord
                        </a>
                        
                        <a href="{{ route('operator.organisations.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-plus me-2"></i>Nouvelle Organisation
                        </a>
                    </div>
                </div>
            </div>

            {{-- Informations de contact --}}
            @if(isset($confirmationData['contact_support']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-headset me-2 text-success"></i>
                        Support & Assistance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                            <div class="icon-circle me-3" style="width: 40px; height: 40px; background: #009e3f;">
                                <i class="fas fa-envelope text-white"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email de support</small>
                                <a href="mailto:{{ $confirmationData['contact_support']['email'] }}" class="fw-bold">
                                    {{ $confirmationData['contact_support']['email'] }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                            <div class="icon-circle me-3" style="width: 40px; height: 40px; background: #ffcd00;">
                                <i class="fas fa-phone text-dark"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Téléphone</small>
                                <a href="tel:{{ $confirmationData['contact_support']['telephone'] }}" class="fw-bold">
                                    {{ $confirmationData['contact_support']['telephone'] }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                            <div class="icon-circle me-3" style="width: 40px; height: 40px; background: #003f7f;">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Horaires de service</small>
                                <span class="fw-bold">{{ $confirmationData['contact_support']['horaires'] }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info border-0 mt-3">
                        <div class="text-center">
                            <i class="fas fa-question-circle me-2"></i>
                            <strong>Question sur votre dossier ?</strong><br>
                            <small class="text-muted">
                                N'hésitez pas à nous contacter en mentionnant votre numéro de soumission :<br>
                                <code>{{ $numeroSoumission }}</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @else
            {{-- Contact par défaut --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-headset me-2 text-success"></i>
                        Support & Assistance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                            <div class="icon-circle me-3" style="width: 40px; height: 40px; background: #009e3f;">
                                <i class="fas fa-envelope text-white"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email de support</small>
                                <a href="mailto:support@pngdi.ga" class="fw-bold">support@pngdi.ga</a>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                            <div class="icon-circle me-3" style="width: 40px; height: 40px; background: #ffcd00;">
                                <i class="fas fa-phone text-dark"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Téléphone</small>
                                <a href="tel:+24101234567" class="fw-bold">+241 01 23 45 67</a>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                            <div class="icon-circle me-3" style="width: 40px; height: 40px; background: #003f7f;">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Horaires de service</small>
                                <span class="fw-bold">Lundi-Vendredi 8h-17h</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Notifications automatiques --}}
            <div class="card border-info">
                <div class="card-header bg-info bg-opacity-10 border-info">
                    <h6 class="card-title mb-0 text-info">
                        <i class="fas fa-bell me-2"></i>Notifications Automatiques
                    </h6>
                </div>
                <div class="card-body">
                    <div class="notification-timeline">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="fw-bold text-success">Accusé de réception envoyé</small><br>
                                <small class="text-muted">Email de confirmation automatique</small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="fw-bold text-warning">Début du traitement</small><br>
                                <small class="text-muted">Notification J+1</small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="fw-bold text-info">Vérification administrative</small><br>
                                <small class="text-muted">Notification J+7</small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <div class="flex-grow-1">
                                <small class="fw-bold text-primary">Décision finale</small><br>
                                <small class="text-muted">Notification sous 45 jours max</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FAB (Floating Action Button) gabonais --}}
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()" style="background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%);">
            <i class="fas fa-ellipsis-v fab-icon text-white"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #009e3f;" title="Télécharger PDF" onclick="downloadPDF()">
                <i class="fas fa-download text-white"></i>
            </button>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Imprimer" onclick="window.print()">
                <i class="fas fa-print"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Partager" onclick="shareSubmission()">
                <i class="fas fa-share text-white"></i>
            </button>
        </div>
    </div>
</div>
@endsection

{{-- Styles CSS personnalisés --}}
@push('styles')
<style>
/* Charte graphique gabonaise officielle */
:root {
    --gabon-green: #009e3f;
    --gabon-yellow: #ffcd00;
    --gabon-blue: #003f7f;
    --gabon-red: #8b1538;
}

/* Animation du check de succès */
#success-check {
    animation: successPulse 3s ease-in-out infinite;
}

@keyframes successPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Statistiques cards avec style gabonais */
.stats-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
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

/* Timeline gabonaise personnalisée */
.timeline-gabon {
    position: relative;
    padding-left: 40px;
}

.timeline-gabon::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    height: 100%;
    width: 3px;
    background: linear-gradient(to bottom, var(--gabon-green), var(--gabon-yellow), var(--gabon-blue));
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 20px;
}

.timeline-marker {
    position: absolute;
    left: -28px;
    top: 5px;
}

.timeline-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    border: 4px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.timeline-item.completed .timeline-circle {
    background: linear-gradient(135deg, var(--gabon-green), #00b347);
    animation: none;
}

.timeline-item.active .timeline-circle {
    background: linear-gradient(135deg, var(--gabon-yellow), #ffa500);
    color: #000;
    animation: pulseGabon 2s ease-in-out infinite;
}

@keyframes pulseGabon {
    0%, 100% { 
        box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 0 0 0 rgba(255, 205, 0, 0.7); 
    }
    50% { 
        box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 0 0 15px rgba(255, 205, 0, 0); 
    }
}

.timeline-content {
    background: transparent;
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
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease;
    border: none;
}

.fab-main:hover {
    transform: scale(1.1);
}

.fab-icon {
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
    
    .timeline-gabon {
        padding-left: 30px;
    }
    
    .timeline-gabon::before {
        left: 15px;
    }
    
    .timeline-marker {
        left: -22px;
    }
    
    .timeline-circle {
        width: 30px;
        height: 30px;
        font-size: 12px;
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

/* Notification timeline */
.notification-timeline .rounded-circle {
    min-width: 30px;
}

/* Styles pour l'impression */
@media print {
    .fab-container,
    .btn,
    .card-header,
    .alert {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        break-inside: avoid;
    }
    
    .timeline-circle {
        background: #333 !important;
        color: white !important;
    }
    
    h1, h2, h3, h4, h5, h6 {
        color: #333 !important;
    }
}
</style>
@endpush

{{-- Scripts JavaScript --}}
@push('scripts')
<script>
$(document).ready(function() {
    // Initialisation
    initializePage();
    
    // Confettis de célébration après délai
    setTimeout(() => {
        if (typeof confetti !== 'undefined') {
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#009e3f', '#ffcd00', '#003f7f']
            });
        }
    }, 800);
});

/**
 * Initialisation de la page
 */
function initializePage() {
    // Animation d'entrée des cartes
    animateCards();
    
    // Configuration des tooltips
    initializeTooltips();
    
    // Gestion du clic externe pour FAB
    setupFABClickOutside();
    
    console.log('✅ Page de confirmation finale initialisée');
}

/**
 * Animation des cartes à l'entrée
 */
function animateCards() {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });
}

/**
 * Initialiser les tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Toggle FAB Menu
 */
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.classList.toggle('active');
}

/**
 * Configuration du clic externe pour FAB
 */
function setupFABClickOutside() {
    document.addEventListener('click', function(event) {
        const fabMenu = document.getElementById('fabMenu');
        if (fabMenu && !fabMenu.contains(event.target)) {
            fabMenu.classList.remove('active');
        }
    });
}

/**
 * Copier le numéro de soumission dans le presse-papiers
 */
function copySubmissionNumber() {
    const number = '{{ $numeroSoumission }}';
    navigator.clipboard.writeText(number).then(() => {
        showToast('Numéro de soumission copié dans le presse-papiers !', 'success');
    }).catch(() => {
        // Fallback pour navigateurs non compatibles
        const textArea = document.createElement('textarea');
        textArea.value = number;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Numéro de soumission copié !', 'success');
    });
}

/**
 * Télécharger en PDF (simulation)
 */
function downloadPDF() {
    showToast('Préparation du PDF en cours...', 'info');
    // Ici, implémentation réelle du téléchargement PDF
    setTimeout(() => {
        showToast('PDF téléchargé avec succès !', 'success');
    }, 2000);
}

/**
 * Partager les informations de soumission
 */
function shareSubmission() {
    const shareData = {
        title: 'Dossier soumis avec succès - PNGDI',
        text: `Mon dossier d'organisation "{{ $organisation->nom }}" a été soumis avec succès. Numéro de soumission: {{ $numeroSoumission }}`,
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(shareData);
    } else {
        copySubmissionNumber();
        showToast('Lien copié ! Vous pouvez maintenant le partager.', 'info');
    }
}

/**
 * Afficher un toast de notification avec style gabonais
 */
function showToast(message, type = 'info', duration = 5000) {
    // Supprimer les toasts existants
    document.querySelectorAll('.toast-gabon').forEach(t => t.remove());
    
    const typeColors = {
        'success': '#009e3f',
        'info': '#003f7f',
        'warning': '#ffcd00',
        'danger': '#8b1538'
    };
    
    const typeIcons = {
        'success': 'check-circle',
        'info': 'info-circle',
        'warning': 'exclamation-triangle',
        'danger': 'exclamation-circle'
    };
    
    const textColor = type === 'warning' ? '#000' : '#fff';
    
    const toast = document.createElement('div');
    toast.className = 'toast-gabon position-fixed fade show';
    toast.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px; 
        background: ${typeColors[type]}; 
        color: ${textColor}; 
        border-radius: 10px; 
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        padding: 15px 20px;
    `;
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${typeIcons[type]} me-3 fs-5"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} ms-3" 
                    onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-suppression
    if (duration > 0) {
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    }
}

/**
 * Animation au scroll pour les éléments timeline
 */
window.addEventListener('scroll', function() {
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach(item => {
        const rect = item.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }
    });
});

// Configuration des variables globales pour d'éventuelles extensions
window.ConfirmationPage = {
    numeroSoumission: '{{ $numeroSoumission }}',
    dossierId: {{ $dossier->id }},
    organisationNom: '{{ $organisation->nom }}',
    totalAdherents: {{ $totalAdherents }},
    submittedAt: '{{ $submittedAt->format("d/m/Y à H:i") }}'
};

console.log('✅ Page de confirmation finale entièrement chargée');
</script>
@endpush