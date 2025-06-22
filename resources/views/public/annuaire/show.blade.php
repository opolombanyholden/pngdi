@extends('layouts.public')

@section('title', $organisation['nom'])

@section('content')
<!-- Header Section -->
<section class="page-header-detail">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="org-type-badge-large {{ $organisation['type'] }}">
                    @switch($organisation['type'])
                        @case('association')
                            <i class="fas fa-users me-2"></i>Association
                            @break
                        @case('ong')
                            <i class="fas fa-hands-helping me-2"></i>ONG
                            @break
                        @case('parti')
                            <i class="fas fa-landmark me-2"></i>Parti politique
                            @break
                        @case('confession')
                            <i class="fas fa-pray me-2"></i>Confession religieuse
                            @break
                    @endswitch
                </div>
                <h1 class="page-title mt-3">{{ $organisation['nom'] }}</h1>
                <p class="page-subtitle">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    {{ $organisation['ville'] }}, {{ $organisation['province'] }}
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('annuaire.index') }}">Annuaire</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($organisation['nom'], 20) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Left Column - Main Info -->
            <div class="col-lg-8">
                <!-- Description Card -->
                <div class="detail-card mb-4">
                    <h3 class="detail-card-title">
                        <i class="fas fa-info-circle me-2"></i>Présentation
                    </h3>
                    <p class="detail-text">{{ $organisation['description'] }}</p>
                    
                    <div class="info-grid mt-4">
                        <div class="info-item">
                            <span class="info-label">Catégorie</span>
                            <span class="info-value">{{ $organisation['categorie'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date de création</span>
                            <span class="info-value">{{ \Carbon\Carbon::parse($organisation['date_creation'])->format('d F Y') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Responsable</span>
                            <span class="info-value">{{ $organisation['responsable'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Nombre de membres</span>
                            <span class="info-value">{{ number_format($organisation['membres']) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Activities Card -->
                @if(isset($organisation['activites']) && count($organisation['activites']) > 0)
                <div class="detail-card mb-4">
                    <h3 class="detail-card-title">
                        <i class="fas fa-tasks me-2"></i>Activités principales
                    </h3>
                    <div class="activities-list">
                        @foreach($organisation['activites'] as $activite)
                        <div class="activity-item">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            {{ $activite }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Contact Card -->
                <div class="detail-card mb-4">
                    <h3 class="detail-card-title">
                        <i class="fas fa-address-card me-2"></i>Coordonnées
                    </h3>
                    <div class="contact-grid">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span class="contact-label">Adresse</span>
                                <span class="contact-value">{{ $organisation['adresse'] }}</span>
                            </div>
                        </div>
                        
                        @if($organisation['telephone'])
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <span class="contact-label">Téléphone</span>
                                <a href="tel:{{ $organisation['telephone'] }}" class="contact-value">
                                    {{ $organisation['telephone'] }}
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        @if($organisation['email'])
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <span class="contact-label">Email</span>
                                <a href="mailto:{{ $organisation['email'] }}" class="contact-value">
                                    {{ $organisation['email'] }}
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        @if($organisation['site_web'])
                        <div class="contact-item">
                            <i class="fas fa-globe"></i>
                            <div>
                                <span class="contact-label">Site web</span>
                                <a href="http://{{ $organisation['site_web'] }}" target="_blank" class="contact-value">
                                    {{ $organisation['site_web'] }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="status-card mb-4">
                    <div class="status-header {{ $organisation['statut'] }}">
                        <i class="fas fa-check-circle me-2"></i>
                        Organisation {{ $organisation['statut'] === 'active' ? 'Active' : 'Inactive' }}
                    </div>
                    <div class="status-body">
                        <p class="mb-2">Cette organisation est officiellement enregistrée auprès du PNGDI.</p>
                        <small class="text-muted">
                            Dernière mise à jour : {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                        </small>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="quick-stats mb-4">
                    <h5 class="mb-3">Statistiques</h5>
                    <div class="stat-row">
                        <span class="stat-icon"><i class="fas fa-history"></i></span>
                        <div>
                            <div class="stat-value">{{ \Carbon\Carbon::parse($organisation['date_creation'])->age }} ans</div>
                            <div class="stat-label">d'existence</div>
                        </div>
                    </div>
                    <div class="stat-row">
                        <span class="stat-icon"><i class="fas fa-users"></i></span>
                        <div>
                            <div class="stat-value">{{ number_format($organisation['membres']) }}</div>
                            <div class="stat-label">membres actifs</div>
                        </div>
                    </div>
                    <div class="stat-row">
                        <span class="stat-icon"><i class="fas fa-map-pin"></i></span>
                        <div>
                            <div class="stat-value">{{ $organisation['province'] }}</div>
                            <div class="stat-label">Province</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mb-4">
                    <a href="{{ route('contact') }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-flag me-2"></i>Signaler une information incorrecte
                    </a>
                    <button class="btn btn-outline-primary w-100" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimer cette fiche
                    </button>
                </div>

                <!-- Map Widget -->
                <div class="map-widget mb-4">
                    <h5 class="mb-3">Localisation</h5>
                    <div class="map-placeholder">
                        <i class="fas fa-map-marked-alt"></i>
                        <p>{{ $organisation['ville'] }}, {{ $organisation['province'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Similar Organizations -->
        @if(count($similaires) > 0)
        <div class="similar-section mt-5">
            <h3 class="section-title mb-4">
                <i class="fas fa-th me-2"></i>
                Autres organisations similaires
            </h3>
            <div class="row g-4">
                @foreach($similaires as $sim)
                <div class="col-md-4">
                    <div class="similar-card">
                        <h5 class="similar-name">
                            <a href="{{ route('annuaire.show', [$sim['type'], $sim['slug']]) }}">
                                {{ $sim['nom'] }}
                            </a>
                        </h5>
                        <p class="similar-info">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $sim['ville'] }}
                        </p>
                        <p class="similar-desc">{{ Str::limit($sim['description'], 80) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Back to List -->
<section class="py-4 bg-light">
    <div class="container text-center">
        <a href="{{ route('annuaire.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Retour à l'annuaire
        </a>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Page Header Detail */
    .page-header-detail {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        color: white;
        padding: 4rem 0 3rem;
        position: relative;
        overflow: hidden;
    }

    .page-header-detail::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    .org-type-badge-large {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
    }

    /* Detail Cards */
    .detail-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .detail-card-title {
        color: var(--primary-blue);
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .detail-text {
        color: #666;
        line-height: 1.8;
        font-size: 1.1rem;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-blue);
    }

    /* Activities */
    .activities-list {
        display: grid;
        gap: 1rem;
    }

    .activity-item {
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 10px;
        display: flex;
        align-items: center;
        font-size: 1.05rem;
    }

    /* Contact Grid */
    .contact-grid {
        display: grid;
        gap: 1.5rem;
    }

    .contact-item {
        display: flex;
        gap: 1rem;
        align-items: start;
    }

    .contact-item i {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-blue);
        flex-shrink: 0;
    }

    .contact-label {
        display: block;
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .contact-value {
        font-weight: 600;
        color: #333;
        text-decoration: none;
    }

    .contact-value:hover {
        color: var(--primary-blue);
    }

    /* Status Card */
    .status-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .status-header {
        padding: 1rem;
        background: #28a745;
        color: white;
        font-weight: 600;
        text-align: center;
    }

    .status-header.inactive {
        background: #dc3545;
    }

    .status-body {
        padding: 1.5rem;
        text-align: center;
    }

    /* Quick Stats */
    .quick-stats {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .stat-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .stat-row:last-child {
        border-bottom: none;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-blue);
        line-height: 1;
    }

    /* Map Widget */
    .map-widget {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .map-placeholder {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 3rem;
        text-align: center;
        color: #6c757d;
    }

    .map-placeholder i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
        color: var(--primary-blue);
        opacity: 0.5;
    }

    /* Similar Section */
    .similar-section {
        padding-top: 3rem;
        border-top: 2px solid #f0f0f0;
    }

    .section-title {
        color: var(--primary-blue);
        font-size: 1.75rem;
    }

    .similar-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        height: 100%;
        transition: all 0.3s;
    }

    .similar-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.12);
    }

    .similar-name {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    .similar-name a {
        color: var(--primary-blue);
        text-decoration: none;
    }

    .similar-name a:hover {
        text-decoration: underline;
    }

    .similar-info {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .similar-desc {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    /* Print Styles */
    @media print {
        .page-header-detail,
        .status-card,
        .action-buttons,
        .similar-section,
        nav {
            display: none !important;
        }
        
        .detail-card {
            box-shadow: none;
            border: 1px solid #ddd;
            break-inside: avoid;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .contact-item {
            flex-direction: column;
            text-align: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Add any interactive features here
</script>
@endpush