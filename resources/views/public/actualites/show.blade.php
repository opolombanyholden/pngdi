@extends('layouts.public')

@section('title', $actualite['titre'])

@section('content')
<!-- Header Section -->
<section class="page-header-simple">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('actualites.index') }}">Actualités</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($actualite['titre'], 50) }}</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Article Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Article Principal -->
            <div class="col-lg-8">
                <article class="article-content">
                    <!-- Header de l'article -->
                    <header class="article-header">
                        <div class="article-meta">
                            <span class="article-category">{{ $actualite['categorie'] }}</span>
                            <span class="article-date">
                                <i class="far fa-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($actualite['date'])->format('d F Y') }}
                            </span>
                            <span class="article-author">
                                <i class="far fa-user me-1"></i>
                                {{ $actualite['auteur'] }}
                            </span>
                            <span class="article-views">
                                <i class="far fa-eye me-1"></i>
                                {{ $actualite['vues'] }} vues
                            </span>
                        </div>
                        <h1 class="article-title">{{ $actualite['titre'] }}</h1>
                    </header>

                    <!-- Image principale -->
                    @if($actualite['image'])
                    <div class="article-image">
                        <img src="{{ $actualite['image'] }}" alt="{{ $actualite['titre'] }}" class="img-fluid">
                    </div>
                    @endif

                    <!-- Contenu -->
                    <div class="article-body">
                        {!! $actualite['contenu'] !!}
                    </div>

                    <!-- Tags -->
                    @if(isset($actualite['tags']) && count($actualite['tags']) > 0)
                    <div class="article-tags">
                        <i class="fas fa-tags me-2"></i>
                        @foreach($actualite['tags'] as $tag)
                        <a href="#" class="tag">{{ $tag }}</a>
                        @endforeach
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="article-actions">
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-share-alt me-2"></i>Partager
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        <a href="{{ route('actualites.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour aux actualités
                        </a>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actualités similaires -->
                <div class="sidebar-widget">
                    <h5 class="widget-title">Articles similaires</h5>
                    <div class="similar-articles">
                        @forelse($similaires as $similaire)
                        <article class="similar-article">
                            <div class="similar-article-category">
                                {{ $similaire['categorie'] }}
                            </div>
                            <h6 class="similar-article-title">
                                <a href="{{ route('actualites.show', $similaire['slug']) }}">
                                    {{ $similaire['titre'] }}
                                </a>
                            </h6>
                            <div class="similar-article-date">
                                <i class="far fa-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($similaire['date'])->format('d M Y') }}
                            </div>
                        </article>
                        @empty
                        <p class="text-muted">Aucun article similaire</p>
                        @endforelse
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="sidebar-widget">
                    <div class="cta-box">
                        <i class="fas fa-envelope fa-3x mb-3"></i>
                        <h5>Restez informé</h5>
                        <p>Recevez nos dernières actualités directement dans votre boîte mail</p>
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-bell me-2"></i>S'abonner
                        </a>
                    </div>
                </div>

                <!-- Contact rapide -->
                <div class="sidebar-widget">
                    <h5 class="widget-title">Besoin d'aide ?</h5>
                    <div class="quick-contact">
                        <p class="mb-3">Notre équipe est à votre disposition pour répondre à vos questions.</p>
                        <div class="contact-item">
                            <i class="fas fa-phone text-primary me-2"></i>
                            +241 01 23 45 67
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            contact@pngdi.ga
                        </div>
                        <a href="{{ route('contact') }}" class="btn btn-outline-primary mt-3 w-100">
                            Nous contacter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Page Header Simple */
    .page-header-simple {
        background: #f8f9fa;
        padding: 2rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .breadcrumb {
        background: transparent;
        margin: 0;
        padding: 0;
    }

    /* Article Content */
    .article-content {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .article-header {
        padding: 2rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #666;
    }

    .article-category {
        background: var(--primary-blue);
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .article-title {
        font-size: 2.5rem;
        color: var(--primary-blue);
        font-weight: 700;
        line-height: 1.3;
        margin: 0;
    }

    .article-image {
        width: 100%;
        max-height: 500px;
        overflow: hidden;
    }

    .article-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .article-body {
        padding: 2rem;
        font-size: 1.1rem;
        line-height: 1.8;
        color: #333;
    }

    .article-body h3 {
        color: var(--primary-blue);
        margin: 2rem 0 1rem;
        font-size: 1.5rem;
    }

    .article-body ul, .article-body ol {
        margin: 1rem 0;
        padding-left: 2rem;
    }

    .article-body li {
        margin-bottom: 0.5rem;
    }

    /* Tags */
    .article-tags {
        padding: 0 2rem 2rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .tag {
        background: #f0f0f0;
        color: #666;
        padding: 0.25rem 1rem;
        border-radius: 20px;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.3s;
    }

    .tag:hover {
        background: var(--primary-blue);
        color: white;
    }

    /* Actions */
    .article-actions {
        padding: 2rem;
        background: #f8f9fa;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* Sidebar */
    .sidebar-widget {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .widget-title {
        color: var(--primary-blue);
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    /* Similar Articles */
    .similar-article {
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .similar-article:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .similar-article-category {
        background: #f0f0f0;
        color: #666;
        padding: 0.2rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        display: inline-block;
        margin-bottom: 0.5rem;
    }

    .similar-article-title {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .similar-article-title a {
        color: #333;
        text-decoration: none;
        transition: color 0.3s;
    }

    .similar-article-title a:hover {
        color: var(--primary-blue);
    }

    .similar-article-date {
        font-size: 0.875rem;
        color: #999;
    }

    /* CTA Box */
    .cta-box {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        color: white;
        padding: 2rem;
        border-radius: 10px;
        text-align: center;
    }

    .cta-box i {
        color: var(--secondary-gold);
    }

    .cta-box h5 {
        margin-bottom: 1rem;
    }

    .cta-box p {
        margin-bottom: 1.5rem;
        opacity: 0.9;
    }

    /* Quick Contact */
    .contact-item {
        padding: 0.5rem 0;
        color: #666;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .article-title {
            font-size: 2rem;
        }
        
        .article-meta {
            gap: 1rem;
            font-size: 0.85rem;
        }
        
        .article-body {
            padding: 1.5rem;
            font-size: 1rem;
        }
    }
</style>
@endpush