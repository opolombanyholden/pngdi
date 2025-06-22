@extends('layouts.public')

@section('title', 'Documents et ressources')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">Documents et ressources</h1>
                <p class="page-subtitle">
                    Téléchargez tous les documents nécessaires pour vos démarches : 
                    guides, formulaires, modèles et textes réglementaires.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Documents</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-3 bg-light border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex gap-4">
                    <div class="stat-item">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        <strong>{{ $stats['total'] }}</strong> documents disponibles
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-download text-success me-2"></i>
                        <strong>{{ number_format($stats['telechargements']) }}</strong> téléchargements
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                            id="sortDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-sort me-1"></i>
                        Trier par : 
                        @switch($sort)
                            @case('populaire') Popularité @break
                            @case('nom') Nom @break
                            @default Plus récents
                        @endswitch
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                        <li><a class="dropdown-item {{ $sort == 'recent' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['sort' => 'recent']) }}">Plus récents</a></li>
                        <li><a class="dropdown-item {{ $sort == 'populaire' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['sort' => 'populaire']) }}">Plus populaires</a></li>
                        <li><a class="dropdown-item {{ $sort == 'nom' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['sort' => 'nom']) }}">Nom (A-Z)</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filtres -->
            <div class="col-lg-3 mb-4">
                <div class="filter-card">
                    <h5 class="filter-title">
                        <i class="fas fa-filter me-2"></i>Filtrer les documents
                    </h5>
                    
                    <!-- Recherche -->
                    <div class="filter-section">
                        <form method="GET" action="{{ route('documents.index') }}">
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
                            @if(request('type'))
                                <input type="hidden" name="type" value="{{ request('type') }}">
                            @endif
                        </form>
                    </div>
                    
                    <!-- Catégories -->
                    <div class="filter-section">
                        <h6 class="filter-section-title">Catégorie</h6>
                        <div class="filter-items">
                            <a href="{{ route('documents.index', array_merge(request()->except('categorie'), ['categorie' => 'all'])) }}" 
                               class="filter-item {{ !$categorie || $categorie == 'all' ? 'active' : '' }}">
                                <span>Toutes les catégories</span>
                            </a>
                            @foreach($categories as $cat)
                            <a href="{{ route('documents.index', array_merge(request()->except('categorie'), ['categorie' => $cat])) }}" 
                               class="filter-item {{ $categorie === $cat ? 'active' : '' }}">
                                <span>{{ $cat }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Types d'organisation -->
                    <div class="filter-section">
                        <h6 class="filter-section-title">Type d'organisation</h6>
                        <div class="filter-items">
                            @foreach($types as $key => $label)
                            <a href="{{ route('documents.index', array_merge(request()->except('type'), ['type' => $key])) }}" 
                               class="filter-item {{ $type === $key || (!$type && $key === 'tous') ? 'active' : '' }}">
                                <span>{{ $label }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Aide -->
                    <div class="help-box">
                        <h6 class="mb-2"><i class="fas fa-question-circle me-2"></i>Besoin d'aide ?</h6>
                        <p class="small mb-2">Vous ne trouvez pas le document recherché ?</p>
                        <a href="{{ route('contact') }}" class="btn btn-sm btn-outline-primary w-100">
                            Contactez-nous
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Liste des documents -->
            <div class="col-lg-9">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($search || ($categorie && $categorie !== 'all') || ($type && $type !== 'tous'))
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-info-circle me-2"></i>
                                Filtres actifs : 
                                @if($search)
                                    <strong>{{ $search }}</strong>
                                @endif
                                @if($categorie && $categorie !== 'all')
                                    @if($search) | @endif
                                    Catégorie : <strong>{{ $categorie }}</strong>
                                @endif
                                @if($type && $type !== 'tous')
                                    @if($search || $categorie) | @endif
                                    Type : <strong>{{ $types[$type] }}</strong>
                                @endif
                                <small class="ms-2">({{ count($documents) }} résultat{{ count($documents) > 1 ? 's' : '' }})</small>
                            </div>
                            <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-times me-1"></i> Effacer
                            </a>
                        </div>
                    </div>
                @endif
                
                <div class="row g-4">
                    @forelse($documents as $doc)
                    <div class="col-md-6">
                        <div class="document-card">
                            <div class="document-icon">
                                @switch($doc['format'])
                                    @case('PDF')
                                        <i class="far fa-file-pdf text-danger"></i>
                                        @break
                                    @case('DOCX')
                                        <i class="far fa-file-word text-primary"></i>
                                        @break
                                    @default
                                        <i class="far fa-file-alt text-secondary"></i>
                                @endswitch
                            </div>
                            <div class="document-content">
                                <div class="document-header">
                                    <h5 class="document-title">{{ $doc['titre'] }}</h5>
                                    <span class="badge bg-primary">{{ $doc['categorie'] }}</span>
                                </div>
                                <p class="document-description">{{ $doc['description'] }}</p>
                                <div class="document-meta">
                                    <span><i class="fas fa-file-alt me-1"></i>{{ $doc['format'] }}</span>
                                    <span><i class="fas fa-weight me-1"></i>{{ $doc['taille'] }}</span>
                                    <span><i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($doc['date_mise_jour'])->format('d/m/Y') }}</span>
                                    <span><i class="fas fa-download me-1"></i>{{ number_format($doc['telechargements']) }}</span>
                                </div>
                                <div class="document-tags">
                                    @foreach($doc['tags'] as $tag)
                                        <span class="tag">#{{ $tag }}</span>
                                    @endforeach
                                </div>
                                <div class="document-action">
                                    <a href="{{ route('documents.download', $doc['id']) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-download me-2"></i>Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center py-5">
                            <i class="fas fa-folder-open fa-3x mb-3 text-warning"></i>
                            <h5>Aucun document trouvé</h5>
                            <p>Aucun document ne correspond à vos critères de recherche.</p>
                            <a href="{{ route('documents.index') }}" class="btn btn-primary mt-3">
                                Voir tous les documents
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Page Header - Réutilise les styles existants */
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

    /* Stats section */
    .stat-item {
        font-size: 0.95rem;
        color: #666;
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

    /* Help Box */
    .help-box {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        margin-top: 2rem;
        text-align: center;
    }

    /* Document Card */
    .document-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        height: 100%;
        display: flex;
        gap: 1.5rem;
        transition: all 0.3s;
    }

    .document-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .document-icon {
        font-size: 3rem;
        flex-shrink: 0;
    }

    .document-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .document-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.5rem;
        gap: 1rem;
    }

    .document-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-blue);
        margin: 0;
        line-height: 1.4;
    }

    .document-description {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1rem;
        flex: 1;
    }

    .document-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.8rem;
        color: #999;
        margin-bottom: 1rem;
    }

    .document-meta span {
        display: flex;
        align-items: center;
    }

    .document-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .tag {
        font-size: 0.75rem;
        color: #666;
        background: #f0f0f0;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
    }

    .document-action {
        margin-top: auto;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .document-card {
            flex-direction: column;
            text-align: center;
        }
        
        .document-header {
            flex-direction: column;
            align-items: center;
        }
        
        .document-meta {
            justify-content: center;
        }
        
        .filter-card {
            position: relative;
            margin-bottom: 2rem;
        }
    }
</style>
@endpush