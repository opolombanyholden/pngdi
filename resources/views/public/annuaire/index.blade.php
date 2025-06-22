@extends('layouts.public')

@section('title', 'Annuaire des organisations')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">Annuaire des organisations</h1>
                <p class="page-subtitle">
                    Découvrez toutes les organisations associatives, religieuses et politiques 
                    officiellement enregistrées au Gabon.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Annuaire</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-banner py-4">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="stat-card-mini">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $stats['associations'] }}</div>
                        <div class="stat-label">Associations</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-mini">
                    <div class="stat-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $stats['ong'] }}</div>
                        <div class="stat-label">ONG</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-mini">
                    <div class="stat-icon">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $stats['partis'] }}</div>
                        <div class="stat-label">Partis politiques</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-mini">
                    <div class="stat-icon">
                        <i class="fas fa-pray"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $stats['confessions'] }}</div>
                        <div class="stat-label">Confessions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search and Filters -->
<section class="search-section py-4 bg-light">
    <div class="container">
        <form method="GET" action="{{ route('annuaire.index') }}" id="filter-form">
            <div class="row g-3 align-items-end">
                <!-- Search -->
                <div class="col-lg-4">
                    <label class="form-label fw-bold">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Nom, ville, catégorie..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                
                <!-- Type Filter -->
                <div class="col-lg-2">
                    <label class="form-label fw-bold">Type</label>
                    <select class="form-select" name="type" onchange="this.form.submit()">
                        <option value="all">Tous types</option>
                        <option value="association" {{ request('type') == 'association' ? 'selected' : '' }}>
                            Associations
                        </option>
                        <option value="ong" {{ request('type') == 'ong' ? 'selected' : '' }}>
                            ONG
                        </option>
                        <option value="parti" {{ request('type') == 'parti' ? 'selected' : '' }}>
                            Partis politiques
                        </option>
                        <option value="confession" {{ request('type') == 'confession' ? 'selected' : '' }}>
                            Confessions religieuses
                        </option>
                    </select>
                </div>
                
                <!-- Province Filter -->
                <div class="col-lg-2">
                    <label class="form-label fw-bold">Province</label>
                    <select class="form-select" name="province" onchange="this.form.submit()">
                        <option value="all">Toutes provinces</option>
                        @foreach($provinces as $prov)
                        <option value="{{ $prov }}" {{ request('province') == $prov ? 'selected' : '' }}>
                            {{ $prov }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div class="col-lg-2">
                    <label class="form-label fw-bold">Catégorie</label>
                    <select class="form-select" name="categorie" onchange="this.form.submit()">
                        <option value="all">Toutes catégories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('categorie') == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Submit/Reset -->
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'type', 'province', 'categorie']))
                    <a href="{{ route('annuaire.index') }}" class="btn btn-link btn-sm w-100 mt-1">
                        Réinitialiser
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Organizations Grid -->
<section class="py-5">
    <div class="container">
        <!-- Results count -->
        @if(request()->hasAny(['search', 'type', 'province', 'categorie']))
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>{{ count($organisations) }}</strong> organisation(s) trouvée(s)
            @if(request('search'))
                pour "<strong>{{ request('search') }}</strong>"
            @endif
        </div>
        @endif
        
        <div class="row g-4">
            @forelse($organisations as $org)
            <div class="col-lg-4 col-md-6">
                <div class="org-card h-100">
                    <div class="org-card-header">
                        <div class="org-type-badge {{ $org['type'] }}">
                            @switch($org['type'])
                                @case('association')
                                    <i class="fas fa-users me-1"></i>Association
                                    @break
                                @case('ong')
                                    <i class="fas fa-hands-helping me-1"></i>ONG
                                    @break
                                @case('parti')
                                    <i class="fas fa-landmark me-1"></i>Parti politique
                                    @break
                                @case('confession')
                                    <i class="fas fa-pray me-1"></i>Confession religieuse
                                    @break
                            @endswitch
                        </div>
                        <div class="org-status {{ $org['statut'] }}">
                            <i class="fas fa-circle"></i>
                            {{ ucfirst($org['statut']) }}
                        </div>
                    </div>
                    
                    <div class="org-card-body">
                        <h4 class="org-name">
                            <a href="{{ route('annuaire.show', [$org['type'], $org['slug']]) }}">
                                {{ $org['nom'] }}
                            </a>
                        </h4>
                        
                        <p class="org-description">{{ Str::limit($org['description'], 100) }}</p>
                        
                        <div class="org-details">
                            <div class="detail-item">
                                <i class="fas fa-folder me-2"></i>
                                <span>{{ $org['categorie'] }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <span>{{ $org['ville'] }}, {{ $org['province'] }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar me-2"></i>
                                <span>Depuis {{ \Carbon\Carbon::parse($org['date_creation'])->year }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-user-friends me-2"></i>
                                <span>{{ number_format($org['membres']) }} membres</span>
                            </div>
                        </div>
                        
                        <div class="org-contact">
                            @if($org['telephone'])
                            <a href="tel:{{ $org['telephone'] }}" class="contact-link">
                                <i class="fas fa-phone"></i>
                            </a>
                            @endif
                            @if($org['email'])
                            <a href="mailto:{{ $org['email'] }}" class="contact-link">
                                <i class="fas fa-envelope"></i>
                            </a>
                            @endif
                            @if($org['site_web'])
                            <a href="http://{{ $org['site_web'] }}" target="_blank" class="contact-link">
                                <i class="fas fa-globe"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="org-card-footer">
                        <a href="{{ route('annuaire.show', [$org['type'], $org['slug']]) }}" 
                           class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-eye me-2"></i>Voir les détails
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="alert alert-warning text-center py-5">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h5>Aucune organisation trouvée</h5>
                    <p>Aucune organisation ne correspond à vos critères de recherche.</p>
                    <a href="{{ route('annuaire.index') }}" class="btn btn-primary mt-3">
                        Voir toutes les organisations
                    </a>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Votre organisation n'est pas dans l'annuaire ?</h2>
        <p class="lead mb-4">
            Inscrivez-vous sur notre plateforme pour formaliser votre organisation 
            et apparaître dans l'annuaire officiel.
        </p>
        <a href="{{ route('register') }}" class="btn btn-warning btn-lg">
            <i class="fas fa-plus-circle me-2"></i>Enregistrer mon organisation
        </a>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        color: white;
        padding: 4rem 0 3rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    /* Stats Banner */
    .stats-banner {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .stat-card-mini {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .stat-card-mini:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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
        font-size: 1.5rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-blue);
        line-height: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
    }

    /* Search Section */
    .search-section {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    /* Organization Cards */
    .org-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .org-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .org-card-header {
        padding: 1rem;
        background: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e9ecef;
    }

    .org-type-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .org-type-badge.association {
        background: rgba(76, 175, 80, 0.1);
        color: #4CAF50;
    }

    .org-type-badge.ong {
        background: rgba(33, 150, 243, 0.1);
        color: #2196F3;
    }

    .org-type-badge.parti {
        background: rgba(255, 152, 0, 0.1);
        color: #FF9800;
    }

    .org-type-badge.confession {
        background: rgba(156, 39, 176, 0.1);
        color: #9C27B0;
    }

    .org-status {
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .org-status.active {
        color: #28a745;
    }

    .org-status.active i {
        font-size: 0.5rem;
    }

    .org-card-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .org-name {
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .org-name a {
        color: var(--primary-blue);
        text-decoration: none;
        transition: color 0.3s;
    }

    .org-name a:hover {
        color: var(--dark-blue);
    }

    .org-description {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex: 1;
    }

    .org-details {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .detail-item {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        color: #6c757d;
    }

    .detail-item i {
        width: 20px;
        color: var(--primary-blue);
        opacity: 0.6;
    }

    .org-contact {
        display: flex;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 1rem;
    }

    .contact-link {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-blue);
        text-decoration: none;
        transition: all 0.3s;
    }

    .contact-link:hover {
        background: var(--primary-blue);
        color: white;
        transform: rotate(360deg);
    }

    .org-card-footer {
        padding: 1rem;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
        animation: rotate 30s linear infinite;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .org-card-body {
            padding: 1rem;
        }
        
        .org-name {
            font-size: 1.1rem;
        }
        
        .detail-item {
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-submit form on select change is already handled inline
    // Additional JS can be added here if needed
</script>
@endpush