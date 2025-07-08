@extends('layouts.public')

@section('title', 'Guides pratiques')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">Guides pratiques</h1>
                <p class="page-subtitle">
                    Consultez nos guides détaillés pour vous accompagner pas à pas 
                    dans toutes vos démarches administratives.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Guides</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar -->
<section class="stats-bar py-3 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-item">
                    <i class="fas fa-book text-primary me-2"></i>
                    <strong>{{ count($guides) }}</strong> guides disponibles
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <i class="fas fa-download text-success me-2"></i>
                    <strong>{{ array_sum(array_column($guides, 'telechargements')) }}</strong> téléchargements
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <i class="fas fa-sync text-info me-2"></i>
                    Mis à jour régulièrement
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Guides Grid -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            @foreach($guides as $index => $guide)
            <div class="col-lg-4 col-md-6">
                <div class="guide-card h-100">
                    <div class="guide-header">
                        <div class="guide-icon">
                            @switch($guide['categorie'])
                                @case('Association')
                                    <i class="fas fa-users"></i>
                                    @break
                                @case('ONG')
                                    <i class="fas fa-hands-helping"></i>
                                    @break
                                @case('Parti politique')
                                    <i class="fas fa-landmark"></i>
                                    @break
                                @default
                                    <i class="fas fa-book"></i>
                            @endswitch
                        </div>
                        <span class="guide-category">{{ $guide['categorie'] }}</span>
                    </div>
                    
                    <div class="guide-body">
                        <h4 class="guide-title">{{ $guide['titre'] }}</h4>
                        <p class="guide-description">{{ $guide['description'] }}</p>
                        
                        <div class="guide-meta">
                            <div class="meta-item">
                                <i class="fas fa-file-alt me-1"></i>
                                {{ $guide['pages'] }} pages
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($guide['mise_a_jour'])->format('d/m/Y') }}
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-download me-1"></i>
                                {{ number_format($guide['telechargements']) }} téléchargements
                            </div>
                        </div>
                    </div>
                    
                    <div class="guide-footer">
                        <a href="{{ route('documents.index') }}" class="btn btn-primary w-100">
                            <i class="fas fa-download me-2"></i>Télécharger le guide
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Quick Start Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-primary">Guide de démarrage rapide</h2>
            <p class="lead text-muted">
                Les étapes essentielles pour créer votre organisation
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5>Inscription</h5>
                    <p>Créez votre compte sur la plateforme SGLP</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>Préparation</h5>
                    <p>Rassemblez tous les documents nécessaires</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Soumission</h5>
                    <p>Complétez et soumettez votre dossier en ligne</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h5>Suivi</h5>
                    <p>Suivez l'avancement de votre demande</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Video Tutorials -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-primary">Tutoriels vidéo</h2>
            <p class="lead text-muted">
                Apprenez à utiliser la plateforme avec nos tutoriels vidéo
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="video-card">
                    <div class="video-thumbnail">
                        <i class="fas fa-play-circle"></i>
                        <span class="video-duration">5:30</span>
                    </div>
                    <h5>Comment créer un compte</h5>
                    <p>Guide pas à pas pour votre inscription</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="video-card">
                    <div class="video-thumbnail">
                        <i class="fas fa-play-circle"></i>
                        <span class="video-duration">8:45</span>
                    </div>
                    <h5>Soumettre un dossier</h5>
                    <p>Tout sur la soumission de documents</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="video-card">
                    <div class="video-thumbnail">
                        <i class="fas fa-play-circle"></i>
                        <span class="video-duration">3:20</span>
                    </div>
                    <h5>Suivre son dossier</h5>
                    <p>Comment vérifier l'état de votre demande</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Besoin d'aide supplémentaire ?</h2>
        <p class="lead mb-4">
            Notre équipe de support est disponible pour vous accompagner
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('faq') }}" class="btn btn-warning btn-lg">
                <i class="fas fa-question-circle me-2"></i>Consulter la FAQ
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">
                <i class="fas fa-headset me-2"></i>Contacter le support
            </a>
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

    /* Stats Bar */
    .stats-bar {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .stat-item {
        font-size: 1.1rem;
        color: #666;
    }

    /* Guide Cards */
    .guide-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .guide-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .guide-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        color: white;
        padding: 2rem;
        position: relative;
        text-align: center;
    }

    .guide-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .guide-category {
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
    }

    .guide-body {
        padding: 1.5rem;
        flex: 1;
    }

    .guide-title {
        color: var(--primary-blue);
        margin-bottom: 1rem;
    }

    .guide-description {
        color: #666;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .guide-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .meta-item {
        font-size: 0.875rem;
        color: #999;
        display: flex;
        align-items: center;
    }

    .guide-footer {
        padding: 1rem;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    /* Step Cards */
    .step-card {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 15px;
        position: relative;
        transition: all 0.3s ease;
    }

    .step-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .step-number {
        width: 60px;
        height: 60px;
        background: var(--secondary-gold);
        color: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 auto 1rem;
    }

    /* Video Cards */
    .video-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .video-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .video-thumbnail {
        height: 200px;
        background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .video-thumbnail i {
        font-size: 4rem;
        color: var(--primary-blue);
        opacity: 0.8;
        transition: all 0.3s;
    }

    .video-card:hover .video-thumbnail i {
        transform: scale(1.1);
        opacity: 1;
    }

    .video-duration {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 5px;
        font-size: 0.875rem;
    }

    .video-card h5 {
        padding: 1rem;
        margin: 0;
        color: var(--primary-blue);
    }

    .video-card p {
        padding: 0 1rem 1rem;
        margin: 0;
        color: #666;
    }
</style>
@endpush