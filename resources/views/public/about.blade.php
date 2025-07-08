@extends('layouts.public')

@section('title', 'À propos')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">À propos du SGLP</h1>
                <p class="page-subtitle">
                    Découvrez notre mission, nos valeurs et notre engagement pour la modernisation 
                    de la gestion des organisations au Gabon.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">À propos</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="about-image-wrapper">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 400'%3E%3Crect fill='%23002B7F' width='600' height='400'/%3E%3Ctext fill='%23FFD700' font-size='30' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3ESGLP%3C/text%3E%3C/svg%3E" 
                         alt="SGLP" class="img-fluid rounded-3 shadow">
                    <div class="experience-badge">
                        <span class="number">2025</span>
                        <span class="text">Année de lancement</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <span class="section-badge">NOTRE MISSION</span>
                <h2 class="display-5 fw-bold text-primary mt-3 mb-4">
                    Moderniser et simplifier la gestion des organisations
                </h2>
                <p class="lead text-muted mb-4">
                    Le Portail National de Gestion des Libertés Individuelles (SGLP) est une initiative 
                    du Ministère de l'Intérieur et de la Sécurité visant à digitaliser et optimiser 
                    les processus de formalisation des organisations au Gabon.
                </p>
                <div class="mission-points">
                    <div class="mission-item">
                        <div class="icon-box">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h5>Simplification administrative</h5>
                            <p>Réduction des délais et des démarches bureaucratiques</p>
                        </div>
                    </div>
                    <div class="mission-item">
                        <div class="icon-box">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5>Transparence totale</h5>
                            <p>Suivi en temps réel et processus clairs</p>
                        </div>
                    </div>
                    <div class="mission-item">
                        <div class="icon-box">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h5>Accessibilité pour tous</h5>
                            <p>Plateforme disponible 24/7 depuis n'importe où</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">NOS VALEURS</span>
            <h2 class="display-5 fw-bold text-primary mt-3">
                Les principes qui nous guident
            </h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h4>Intégrité</h4>
                    <p>Nous garantissons un traitement équitable et impartial de tous les dossiers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h4>Innovation</h4>
                    <p>Nous utilisons les dernières technologies pour améliorer continuellement nos services.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Collaboration</h4>
                    <p>Nous travaillons main dans la main avec les organisations pour leur réussite.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">NOTRE ÉQUIPE</span>
            <h2 class="display-5 fw-bold text-primary mt-3">
                Une équipe dédiée à votre service
            </h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h5>Direction Générale</h5>
                    <p class="text-muted">Supervision et orientation stratégique</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h5>Service Technique</h5>
                    <p class="text-muted">Développement et maintenance de la plateforme</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h5>Service Juridique</h5>
                    <p class="text-muted">Conformité légale et réglementaire</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>Support Client</h5>
                    <p class="text-muted">Assistance et accompagnement des utilisateurs</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Vous avez des questions ?</h2>
        <p class="lead mb-4">
            Notre équipe est là pour vous accompagner dans toutes vos démarches
        </p>
        <a href="{{ route('contact') }}" class="btn btn-warning btn-lg">
            <i class="fas fa-envelope me-2"></i>Contactez-nous
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

    /* Section Badge */
    .section-badge {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 1px;
        display: inline-block;
    }

    /* About Image */
    .about-image-wrapper {
        position: relative;
    }

    .experience-badge {
        position: absolute;
        bottom: -20px;
        right: -20px;
        background: var(--secondary-gold);
        color: var(--primary-blue);
        padding: 2rem;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .experience-badge .number {
        display: block;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .experience-badge .text {
        display: block;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    /* Mission Items */
    .mission-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .icon-box {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    /* Value Cards */
    .value-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .value-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .value-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, rgba(255,215,0,0.2), rgba(0,43,127,0.1));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--primary-blue);
    }

    /* Team Cards */
    .team-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .team-card:hover {
        border-color: var(--primary-blue);
        transform: translateY(-5px);
    }

    .team-avatar {
        width: 100px;
        height: 100px;
        margin: 0 auto 1rem;
        background: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
    }
</style>
@endpush