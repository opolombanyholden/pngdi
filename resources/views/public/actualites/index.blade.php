@extends('layouts.public')

@section('title', 'Actualités')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">Actualités</h1>
                <p class="page-subtitle">
                    Restez informé des dernières nouvelles, annonces et mises à jour concernant 
                    les organisations associatives, religieuses et politiques au Gabon.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Actualités</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Filtres et Actualités -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filtres -->
            <div class="col-lg-3 mb-4">
                <div class="filter-card">
                    <h5 class="filter-title">
                        <i class="fas fa-filter me-2"></i>Filtrer par
                    </h5>
                    
                    <!-- Barre de recherche -->
                    <div class="filter-section">
                        <form method="GET" action="{{ route('actualites.index') }}">
                            <div class="input-group mb-3">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Rechercher..." 
                                       value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            @if(request('categorie'))
                                <input type="hidden" name="categorie" value="{{ request('categorie') }}">
                            @endif
                        </form>
                    </div>
                    
                    <!-- Catégories -->
                    <div class="filter-section">
                        <h6 class="filter-section-title">Catégories</h6>
                        <div class="filter-items">
                            <a href="{{ route('actualites.index') }}" 
                               class="filter-item {{ !$categorie ? 'active' : '' }}">
                                <span>Toutes les actualités</span>
                                <span class="filter-count">{{ $total }}</span>
                            </a>
                            @foreach($categories as $cat)
                            <a href="{{ route('actualites.index', ['categorie' => $cat, 'search' => request('search')]) }}" 
                               class="filter-item {{ $categorie === $cat ? 'active' : '' }}">
                                <span>{{ $cat }}</span>
                                <span class="filter-count">{{ $categoryCounts[$cat] ?? 0 }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Archives -->
                    <div class="filter-section">
                        <h6 class="filter-section-title">Archives</h6>
                        <div class="filter-items">
                            <a href="#" class="filter-item">
                                <span>Janvier 2025</span>
                                <span class="filter-count">5</span>
                            </a>
                            <a href="#" class="filter-item">
                                <span>Décembre 2024</span>
                                <span class="filter-count">3</span>
                            </a>
                        </div>
                    </div>

                    <!-- Newsletter -->
                    <div class="newsletter-box">
                        <h6 class="mb-3">Newsletter</h6>
                        <p class="small text-white-50 mb-3">
                            Recevez les dernières actualités directement dans votre boîte mail
                        </p>
                        <form>
                            <div class="mb-2">
                                <input type="email" class="form-control" placeholder="Votre email" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-envelope me-2"></i>S'abonner
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Liste des actualités -->
            <div class="col-lg-9">
                <!-- Filtres actifs -->
                @if($categorie || request('search'))
                <div class="alert alert-info mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-info-circle me-2"></i>
                            @if($categorie)
                                Catégorie : <strong>{{ $categorie }}</strong>
                            @endif
                            @if(request('search'))
                                @if($categorie) | @endif
                                Recherche : <strong>{{ request('search') }}</strong>
                            @endif
                            <small class="ms-2">({{ $total }} résultat{{ $total > 1 ? 's' : '' }})</small>
                        </div>
                        <a href="{{ route('actualites.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-times me-1"></i> Effacer les filtres
                        </a>
                    </div>
                </div>
                @endif

                <div class="row g-4">
                    @forelse($actualitesPaginated as $actualite)
                    <div class="col-md-6">
                        <article class="news-card-vertical">
                            <div class="news-card-image">
                                @if($actualite['image'])
                                    <img src="{{ $actualite['image'] }}" alt="{{ $actualite['titre'] }}">
                                @else
                                    <div class="news-placeholder">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                @endif
                                <div class="news-card-category">
                                    {{ $actualite['categorie'] }}
                                </div>
                            </div>
                            <div class="news-card-content">
                                <div class="news-meta">
                                    <span class="news-date">
                                        <i class="far fa-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($actualite['date'])->format('d M Y') }}
                                    </span>
                                    <span class="news-author">
                                        <i class="far fa-user me-1"></i>
                                        {{ $actualite['auteur'] }}
                                    </span>
                                </div>
                                <h3 class="news-title">
                                    <a href="{{ route('actualites.show', $actualite['slug']) }}">
                                        {{ $actualite['titre'] }}
                                    </a>
                                </h3>
                                <p class="news-excerpt">
                                    {{ $actualite['extrait'] }}
                                </p>
                                <div class="news-footer">
                                    <a href="{{ route('actualites.show', $actualite['slug']) }}" 
                                       class="btn btn-link p-0">
                                        Lire la suite <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <span class="news-views">
                                        <i class="far fa-eye me-1"></i>{{ $actualite['vues'] }} vues
                                    </span>
                                </div>
                            </div>
                        </article>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center py-5">
                            <i class="fas fa-info-circle fa-3x mb-3 text-warning"></i>
                            <h5>Aucune actualité trouvée</h5>
                            @if(request('search') || $categorie)
                                <p>Il n'y a pas d'actualités correspondant à vos critères de recherche.</p>
                                <a href="{{ route('actualites.index') }}" class="btn btn-primary mt-3">
                                    Voir toutes les actualités
                                </a>
                            @else
                                <p>Aucune actualité n'est disponible pour le moment.</p>
                            @endif
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($totalPages > 1)
                <nav aria-label="Pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        {{-- Bouton Précédent --}}
                        <li class="page-item {{ $page <= 1 ? 'disabled' : '' }}">
                            <a class="page-link" 
                               href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" 
                               aria-label="Précédent">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        {{-- Numéros de page --}}
                        @php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                        @endphp
                        
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif
                        
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $page ? 'active' : '' }}">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor
                        
                        @if($end < $totalPages)
                            @if($end < $totalPages - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $totalPages]) }}">
                                    {{ $totalPages }}
                                </a>
                            </li>
                        @endif
                        
                        {{-- Bouton Suivant --}}
                        <li class="page-item {{ $page >= $totalPages ? 'disabled' : '' }}">
                            <a class="page-link" 
                               href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" 
                               aria-label="Suivant">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                @endif
            </div>
        </div>
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

    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .breadcrumb {
        background: transparent;
        margin: 0;
        padding: 0;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255,255,255,0.5);
    }

    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: color 0.3s;
    }

    .breadcrumb-item a:hover {
        color: white;
    }

    .breadcrumb-item.active {
        color: var(--secondary-gold);
    }

    /* Filter Card */
    .filter-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        position: sticky;
        top: 100px;
    }

    .filter-title {
        color: var(--primary-blue);
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .filter-section {
        margin-bottom: 2rem;
    }

    .filter-section-title {
        font-size: 1rem;
        color: #333;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .filter-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        margin-bottom: 0.5rem;
        border-radius: 8px;
        color: #666;
        text-decoration: none;
        transition: all 0.3s;
    }

    .filter-item:hover {
        background: #f8f9fa;
        color: var(--primary-blue);
        transform: translateX(5px);
    }

    .filter-item.active {
        background: var(--primary-blue);
        color: white;
    }

    .filter-count {
        background: rgba(0,0,0,0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .filter-item.active .filter-count {
        background: rgba(255,255,255,0.3);
    }

    /* Newsletter Box */
    .newsletter-box {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        color: white;
        padding: 1.5rem;
        border-radius: 10px;
        margin-top: 2rem;
    }

    .newsletter-box .form-control {
        border: none;
        background: rgba(255,255,255,0.1);
        color: white;
    }

    .newsletter-box .form-control::placeholder {
        color: rgba(255,255,255,0.6);
    }

    .newsletter-box .form-control:focus {
        background: rgba(255,255,255,0.2);
        box-shadow: 0 0 0 0.2rem rgba(255,215,0,0.25);
        color: white;
    }

    /* News Card Vertical */
    .news-card-vertical {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        height: 100%;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
    }

    .news-card-vertical:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .news-card-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .news-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .news-card-vertical:hover .news-card-image img {
        transform: scale(1.1);
    }

    .news-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #ccc;
    }

    .news-card-category {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--primary-blue);
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .news-card-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .news-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        color: #999;
    }

    .news-title {
        font-size: 1.25rem;
        margin-bottom: 1rem;
        line-height: 1.4;
    }

    .news-title a {
        color: var(--primary-blue);
        text-decoration: none;
        transition: color 0.3s;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .news-title a:hover {
        color: var(--dark-blue);
    }

    .news-excerpt {
        color: #666;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }

    .news-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    .news-views {
        color: #999;
        font-size: 0.875rem;
    }

    /* Pagination */
    .pagination {
        gap: 0.5rem;
    }

    .page-link {
        border: none;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        color: var(--primary-blue);
        background: #f8f9fa;
        transition: all 0.3s;
        min-width: 40px;
        text-align: center;
    }

    .page-link:hover {
        background: var(--primary-blue);
        color: white;
        transform: translateY(-2px);
    }

    .page-item.active .page-link {
        background: var(--primary-blue);
        color: white;
    }

    .page-item.disabled .page-link {
        background: #e9ecef;
        color: #adb5bd;
        cursor: not-allowed;
    }

    /* Alert */
    .alert-info {
        background: #e7f3ff;
        border: 1px solid #b8daff;
        color: #004085;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .filter-card {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2rem;
        }
        
        .page-subtitle {
            font-size: 1rem;
        }
    }
</style>
@endpush