@extends('layouts.public')

@section('title', 'Accueil')

@section('content')
<!-- Hero Section -->
<section class="hero" id="accueil">
    <div class="container position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <h1>Simplifiez vos démarches administratives</h1>
                <p class="hero-subtitle">Système de Gestion des Libertés Publiques</p>
                <p class="text-white fs-5 mb-4">
                    Créez, gérez et formalisez vos organisations associatives, religieuses et politiques 
                    en toute simplicité grâce à notre plateforme numérique moderne et sécurisée.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-4">
                        <i class="fas fa-rocket me-2"></i>Commencer maintenant
                    </a>
                    <a href="#services" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-info-circle me-2"></i>En savoir plus
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 500 500'%3E%3Cg fill='none' stroke='%23FFD700' stroke-width='2' opacity='0.3'%3E%3Ccircle cx='250' cy='250' r='100'/%3E%3Ccircle cx='250' cy='250' r='150'/%3E%3Ccircle cx='250' cy='250' r='200'/%3E%3C/g%3E%3C/svg%3E" 
                         class="img-fluid" alt="Illustration">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section Améliorée -->
<section class="stats-section py-5">
    <div class="container">
        <div class="stats-wrapper">
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card-modern fade-in">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-target="{{ $stats['associations'] }}">0</div>
                            <div class="stat-label">Associations actives</div>
                            <div class="stat-progress">
                                <div class="progress-bar" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card-modern fade-in">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-pray"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-target="{{ $stats['confessions'] }}">0</div>
                            <div class="stat-label">Confessions religieuses</div>
                            <div class="stat-progress">
                                <div class="progress-bar" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card-modern fade-in">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-target="{{ $stats['partis'] }}">0</div>
                            <div class="stat-label">Partis politiques</div>
                            <div class="stat-progress">
                                <div class="progress-bar" style="width: 25%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card-modern fade-in">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-target="{{ $stats['ong'] }}">0</div>
                            <div class="stat-label">ONG enregistrées</div>
                            <div class="stat-progress">
                                <div class="progress-bar" style="width: 60%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section Améliorée -->
<section class="services-section py-5" id="services">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge fade-in">NOS SERVICES</span>
            <h2 class="display-5 fw-bold text-primary mt-3 fade-in">Une plateforme complète pour vous accompagner</h2>
            <p class="lead text-muted fade-in">
                Découvrez comment nous simplifions vos démarches administratives
            </p>
        </div>
        <div class="row g-4">
            @foreach($services as $index => $service)
            <div class="col-md-6 col-lg-3">
                <div class="service-card-modern fade-in" style="animation-delay: {{ $index * 0.1 }}s">
                    <div class="service-number">0{{ $index + 1 }}</div>
                    <div class="service-icon-modern">
                        <i class="{{ $service['icon'] }}"></i>
                    </div>
                    <h4 class="service-title">{{ $service['titre'] }}</h4>
                    <p class="service-description">{{ $service['description'] }}</p>
                    <a href="@if($service['titre'] == 'Documents et guides') {{ route('documents.index') }} @elseif($service['titre'] == 'Formalisation en ligne') {{ route('register') }} @else # @endif" class="service-link">
                        En savoir plus <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Actualités Section Améliorée -->
<section class="news-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge fade-in">ACTUALITÉS</span>
            <h2 class="display-5 fw-bold text-primary mt-3 fade-in">Les dernières informations</h2>
            <p class="lead text-muted fade-in">
                Restez informé des nouveautés et des mises à jour importantes
            </p>
        </div>
        <div class="row g-4">
            @foreach($actualites as $index => $actualite)
            <div class="col-md-4">
                <article class="news-card-modern fade-in" style="animation-delay: {{ $index * 0.1 }}s">
                    <div class="news-card-header">
                        <span class="news-date">
                            <i class="far fa-calendar"></i>
                            {{ \Carbon\Carbon::parse($actualite['date'])->format('d M Y') }}
                        </span>
                        <span class="news-category-badge {{ strtolower($actualite['categorie']) }}">
                            {{ $actualite['categorie'] }}
                        </span>
                    </div>
                    <div class="news-card-body">
                        <h5 class="news-title">
                            <a href="{{ route('actualites.show', $actualite['slug']) }}">{{ $actualite['titre'] }}</a>
                        </h5>
                        <p class="news-excerpt">{{ $actualite['extrait'] }}</p>
                    </div>
                    <div class="news-card-footer">
                        <a href="{{ route('actualites.show', $actualite['slug']) }}" class="read-more-link">
                            Lire la suite
                            <span class="arrow-icon">
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </span>
                        </a>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('actualites.index') }}" class="btn btn-gradient btn-lg">
                <i class="fas fa-newspaper me-2"></i>Voir toutes les actualités
            </a>
        </div>
    </div>
</section>

<!-- Section Carte Géographique -->
<section class="map-section py-5 bg-primary text-white">
    <div class="container-fluid">
        <div class="text-center mb-5">
            <span class="section-badge-white fade-in">RÉPARTITION GÉOGRAPHIQUE</span>
            <h2 class="display-5 fw-bold mt-3 fade-in">Présence sur tout le territoire national</h2>
            <p class="lead fade-in">
                Découvrez la répartition des organisations à travers les provinces du Gabon
            </p>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="map-container fade-in">
                    <div id="gabon-map" class="interactive-map">
                        <!-- SVG Map du Gabon -->
                        <svg viewBox="0 0 800 900" xmlns="http://www.w3.org/2000/svg" class="gabon-svg-map">
                            <!-- Provinces du Gabon (représentation simplifiée) -->
                            <g class="provinces">
                                <!-- Estuaire (Libreville) -->
                                <path d="M 300 150 L 400 150 L 400 250 L 300 250 Z" 
                                      class="province" 
                                      data-province="Estuaire" 
                                      data-associations="45" 
                                      data-ong="32" 
                                      data-partis="8" 
                                      data-confessions="15"/>
                                
                                <!-- Haut-Ogooué -->
                                <path d="M 500 400 L 650 400 L 650 550 L 500 550 Z" 
                                      class="province" 
                                      data-province="Haut-Ogooué" 
                                      data-associations="28" 
                                      data-ong="18" 
                                      data-partis="3" 
                                      data-confessions="8"/>
                                
                                <!-- Ogooué-Maritime -->
                                <path d="M 200 350 L 350 350 L 350 500 L 200 500 Z" 
                                      class="province" 
                                      data-province="Ogooué-Maritime" 
                                      data-associations="22" 
                                      data-ong="15" 
                                      data-partis="2" 
                                      data-confessions="7"/>
                                
                                <!-- Woleu-Ntem -->
                                <path d="M 350 50 L 500 50 L 500 200 L 350 200 Z" 
                                      class="province" 
                                      data-province="Woleu-Ntem" 
                                      data-associations="18" 
                                      data-ong="12" 
                                      data-partis="1" 
                                      data-confessions="6"/>
                                
                                <!-- Moyen-Ogooué -->
                                <path d="M 300 300 L 450 300 L 450 450 L 300 450 Z" 
                                      class="province" 
                                      data-province="Moyen-Ogooué" 
                                      data-associations="15" 
                                      data-ong="8" 
                                      data-partis="1" 
                                      data-confessions="5"/>
                                
                                <!-- Autres provinces... -->
                            </g>
                            
                            <!-- Points pour les villes principales -->
                            <g class="cities">
                                <circle cx="350" cy="200" r="8" class="city-marker capital" />
                                <text x="360" y="205" class="city-label">Libreville</text>
                                
                                <circle cx="575" cy="475" r="5" class="city-marker" />
                                <text x="585" y="480" class="city-label">Franceville</text>
                                
                                <circle cx="275" cy="425" r="5" class="city-marker" />
                                <text x="285" y="430" class="city-label">Port-Gentil</text>
                            </g>
                        </svg>
                    </div>
                    
                    <!-- Tooltip -->
                    <div id="map-tooltip" class="map-tooltip"></div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="map-stats fade-in">
                    <h4 class="mb-4">Légende & Statistiques</h4>
                    
                    <!-- Légende -->
                    <div class="legend-items">
                        <div class="legend-item">
                            <span class="legend-color associations"></span>
                            <span>Associations</span>
                            <span class="legend-count">{{ $stats['associations'] }}</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color ong"></span>
                            <span>ONG</span>
                            <span class="legend-count">{{ $stats['ong'] }}</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color partis"></span>
                            <span>Partis politiques</span>
                            <span class="legend-count">{{ $stats['partis'] }}</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color confessions"></span>
                            <span>Confessions religieuses</span>
                            <span class="legend-count">{{ $stats['confessions'] }}</span>
                        </div>
                    </div>
                    
                    <!-- Top Provinces -->
                    <div class="top-provinces mt-4">
                        <h5 class="mb-3">Provinces les plus actives</h5>
                        <div class="province-rank">
                            <div class="rank-item">
                                <span class="rank-number">1</span>
                                <span class="rank-name">Estuaire</span>
                                <span class="rank-count">100 org.</span>
                            </div>
                            <div class="rank-item">
                                <span class="rank-number">2</span>
                                <span class="rank-name">Haut-Ogooué</span>
                                <span class="rank-count">57 org.</span>
                            </div>
                            <div class="rank-item">
                                <span class="rank-number">3</span>
                                <span class="rank-name">Ogooué-Maritime</span>
                                <span class="rank-count">46 org.</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="mt-4">
                        <a href="{{ route('annuaire.index') }}" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-search me-2"></i>Explorer l'annuaire complet
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold text-primary mb-4 fade-in">Prêt à démarrer vos démarches ?</h2>
                <p class="lead text-muted mb-4 fade-in">
                    Rejoignez les centaines d'organisations qui ont simplifié leurs processus administratifs 
                    grâce à notre plateforme numérique.
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap fade-in">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>Créer un compte
                    </a>
                    <a href="{{ route('faq') }}" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-question-circle me-2"></i>Consulter la FAQ
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h2 class="display-5 fw-bold text-primary mb-4 fade-in">Pourquoi choisir SGLP ?</h2>
                <div class="d-flex mb-4 fade-in">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle p-3">
                            <i class="fas fa-check fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5>Processus 100% dématérialisé</h5>
                        <p class="text-muted">Finies les files d'attente, gérez tout depuis votre ordinateur ou smartphone.</p>
                    </div>
                </div>
                <div class="d-flex mb-4 fade-in">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle p-3">
                            <i class="fas fa-shield-alt fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5>Sécurisé et confidentiel</h5>
                        <p class="text-muted">Vos données sont protégées selon les standards de sécurité les plus élevés.</p>
                    </div>
                </div>
                <div class="d-flex fade-in">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle p-3">
                            <i class="fas fa-headset fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5>Support disponible</h5>
                        <p class="text-muted">Une équipe dédiée pour vous accompagner dans toutes vos démarches.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-white p-5 rounded shadow-lg fade-in">
                    <h4 class="mb-4">Démarrez en 3 étapes simples</h4>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning text-primary rounded-circle p-2 px-3 me-3 fw-bold">1</div>
                        <div>
                            <h6 class="mb-0">Créez votre compte</h6>
                            <small class="text-muted">Inscription gratuite en moins de 2 minutes</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning text-primary rounded-circle p-2 px-3 me-3 fw-bold">2</div>
                        <div>
                            <h6 class="mb-0">Complétez votre dossier</h6>
                            <small class="text-muted">Remplissez les formulaires et uploadez vos documents</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-primary rounded-circle p-2 px-3 me-3 fw-bold">3</div>
                        <div>
                            <h6 class="mb-0">Suivez votre demande</h6>
                            <small class="text-muted">Recevez des notifications à chaque étape</small>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-folder-open me-2"></i>Accéder aux documents utiles
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
    /* Stats Section Moderne */
    .stats-section {
        background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
        position: relative;
        overflow: hidden;
    }

    .stats-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(0,43,127,0.03) 0%, transparent 70%);
        animation: pulse 15s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .stat-card-modern {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, transparent, rgba(255,215,0,0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.6s;
        opacity: 0;
    }

    .stat-card-modern:hover::before {
        opacity: 1;
        top: -100%;
        right: -100%;
    }

    .stat-card-modern:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,43,127,0.15);
    }

    .stat-icon-wrapper {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .stat-icon-wrapper::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%) scale(0);
        transition: transform 0.6s;
    }

    .stat-card-modern:hover .stat-icon-wrapper::after {
        transform: translate(-50%, -50%) scale(2);
    }

    .stat-icon-wrapper i {
        font-size: 2rem;
        color: white;
        z-index: 1;
        position: relative;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 0.5rem;
        font-family: 'Arial', sans-serif;
    }

    .stat-progress {
        height: 6px;
        background: #e9ecef;
        border-radius: 10px;
        margin-top: 1rem;
        overflow: hidden;
        position: relative;
    }

    .stat-progress .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--secondary-gold), var(--primary-blue));
        border-radius: 10px;
        position: relative;
        animation: progressAnimation 2s ease-out;
    }

    @keyframes progressAnimation {
        from { width: 0; }
    }

    /* Services Section Moderne */
    .services-section {
        background: white;
        position: relative;
    }

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

    .service-card-modern {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        height: 100%;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 1px solid #f0f0f0;
    }

    .service-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--secondary-gold), var(--primary-blue));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s;
    }

    .service-card-modern:hover::before {
        transform: scaleX(1);
    }

    .service-card-modern:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,43,127,0.15);
        border-color: transparent;
    }

    .service-number {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 3rem;
        font-weight: 700;
        color: rgba(0,43,127,0.1);
        font-family: 'Arial', sans-serif;
    }

    .service-icon-modern {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(255,215,0,0.2), rgba(0,43,127,0.1));
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        transition: all 0.4s;
    }

    .service-card-modern:hover .service-icon-modern {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        transform: rotate(10deg);
    }

    .service-icon-modern i {
        font-size: 2.5rem;
        color: var(--primary-blue);
        transition: all 0.4s;
    }

    .service-card-modern:hover .service-icon-modern i {
        color: white;
        transform: scale(1.1);
    }

    .service-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-blue);
        margin-bottom: 1rem;
    }

    .service-description {
        color: #6c757d;
        line-height: 1.8;
        margin-bottom: 1.5rem;
    }

    .service-link {
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }

    .service-link i {
        transition: transform 0.3s;
    }

    .service-link:hover {
        color: var(--dark-blue);
        gap: 1rem;
    }

    .service-link:hover i {
        transform: translateX(5px);
    }

    /* News Section Moderne */
    .news-section {
        background: #f8f9fa;
        position: relative;
    }

    .news-card-modern {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        height: 100%;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
    }

    .news-card-modern:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,43,127,0.15);
    }

    .news-card-header {
        padding: 1.5rem 1.5rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .news-date {
        color: #6c757d;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .news-category-badge {
        padding: 0.25rem 1rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .news-category-badge.réglementation {
        background: rgba(0,123,255,0.1);
        color: #007bff;
    }

    .news-category-badge.événement {
        background: rgba(40,167,69,0.1);
        color: #28a745;
    }

    .news-category-badge.documentation {
        background: rgba(255,193,7,0.1);
        color: #ffc107;
    }

    .news-card-body {
        padding: 1.5rem;
        flex: 1;
    }

    .news-title {
        margin-bottom: 1rem;
    }

    .news-title a {
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 600;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.3s;
    }

    .news-title a:hover {
        color: var(--dark-blue);
    }

    .news-excerpt {
        color: #6c757d;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .news-card-footer {
        padding: 0 1.5rem 1.5rem;
        margin-top: auto;
    }

    .read-more-link {
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
        padding-bottom: 2px;
    }

    .read-more-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary-blue);
        transform: translateX(-100%);
        transition: transform 0.3s;
    }

    .read-more-link:hover::after {
        transform: translateX(0);
    }

    .read-more-link:hover {
        color: var(--dark-blue);
    }

    .arrow-icon {
        display: inline-flex;
        transition: transform 0.3s;
    }

    .read-more-link:hover .arrow-icon {
        transform: translateX(5px);
    }

    /* Section Carte */
    .map-section {
        position: relative;
        overflow: hidden;
    }

    .map-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.03"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
    }

    .section-badge-white {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 1px;
        display: inline-block;
        backdrop-filter: blur(10px);
    }

    .map-container {
        background: rgba(255,255,255,0.1);
        border-radius: 20px;
        padding: 2rem;
        backdrop-filter: blur(10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .interactive-map {
        position: relative;
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
    }

    .gabon-svg-map {
        width: 100%;
        height: auto;
    }

    .province {
        fill: rgba(255,255,255,0.2);
        stroke: white;
        stroke-width: 2;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .province:hover {
        fill: rgba(255,215,0,0.8);
        stroke-width: 3;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.3));
    }

    .province.active {
        fill: rgba(255,215,0,0.9);
    }

    .city-marker {
        fill: white;
        stroke: var(--primary-blue);
        stroke-width: 2;
    }

    .city-marker.capital {
        fill: var(--secondary-gold);
    }

    .city-label {
        fill: white;
        font-size: 14px;
        font-weight: 600;
    }

    /* Tooltip */
    .map-tooltip {
        position: absolute;
        background: white;
        color: var(--primary-blue);
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 1000;
        min-width: 200px;
    }

    .map-tooltip.show {
        opacity: 1;
    }

    .tooltip-header {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--primary-blue);
    }

    .tooltip-stats {
        display: grid;
        gap: 0.25rem;
    }

    .tooltip-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.25rem 0;
    }

    .tooltip-stat-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tooltip-stat-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    /* Map Stats */
    .map-stats {
        background: rgba(255,255,255,0.1);
        border-radius: 20px;
        padding: 2rem;
        backdrop-filter: blur(10px);
    }

    .legend-items {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        transition: all 0.3s;
    }

    .legend-item:hover {
        background: rgba(255,255,255,0.2);
        transform: translateX(5px);
    }

    .legend-color {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-block;
        border: 3px solid white;
    }

    .legend-color.associations {
        background: #4CAF50;
    }

    .legend-color.ong {
        background: #2196F3;
    }

    .legend-color.partis {
        background: #FF9800;
    }

    .legend-color.confessions {
        background: #9C27B0;
    }

    .legend-count {
        margin-left: auto;
        font-weight: 700;
        font-size: 1.25rem;
    }

    /* Top Provinces */
    .rank-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        margin-bottom: 0.5rem;
        transition: all 0.3s;
    }

    .rank-item:hover {
        background: rgba(255,255,255,0.2);
    }

    .rank-number {
        width: 35px;
        height: 35px;
        background: var(--secondary-gold);
        color: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .rank-name {
        flex: 1;
    }

    .rank-count {
        font-weight: 600;
        opacity: 0.9;
    }

    /* Bouton Gradient */
    .btn-gradient {
        background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .btn-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .btn-gradient:hover::before {
        left: 100%;
    }

    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,43,127,0.3);
        color: white;
    }

    /* Animations supplémentaires */
    .fade-in {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Animation pour les données */
    @keyframes dataPopIn {
        from {
            transform: scale(0);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .province[data-animation="true"] {
        animation: dataPopIn 0.6s ease-out;
    }
</style>
@endpush

@push('scripts')
<script>
    // Counter animation for stats
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;

        const animateCounter = (counter) => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(() => animateCounter(counter), 10);
            } else {
                counter.innerText = target;
            }
        };

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    entry.target.classList.add('counted');
                    animateCounter(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => {
            counterObserver.observe(counter);
        });

        // Progress bar animation
        const progressBars = document.querySelectorAll('.progress-bar');
        const progressObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                    entry.target.classList.add('animated');
                    const width = entry.target.style.width;
                    entry.target.style.width = '0';
                    setTimeout(() => {
                        entry.target.style.width = width;
                    }, 100);
                }
            });
        }, { threshold: 0.5 });

        progressBars.forEach(bar => {
            progressObserver.observe(bar);
        });

        // Carte interactive
        const provinces = document.querySelectorAll('.province');
        const tooltip = document.getElementById('map-tooltip');
        
        provinces.forEach(province => {
            province.addEventListener('mouseenter', function(e) {
                const rect = this.getBoundingClientRect();
                const mapRect = document.querySelector('.interactive-map').getBoundingClientRect();
                
                const data = {
                    name: this.dataset.province,
                    associations: this.dataset.associations,
                    ong: this.dataset.ong,
                    partis: this.dataset.partis,
                    confessions: this.dataset.confessions
                };
                
                const total = parseInt(data.associations) + parseInt(data.ong) + 
                             parseInt(data.partis) + parseInt(data.confessions);
                
                tooltip.innerHTML = `
                    <div class="tooltip-header">${data.name}</div>
                    <div class="tooltip-stats">
                        <div class="tooltip-stat">
                            <span class="tooltip-stat-label">
                                <span class="tooltip-stat-dot" style="background: #4CAF50"></span>
                                Associations
                            </span>
                            <strong>${data.associations}</strong>
                        </div>
                        <div class="tooltip-stat">
                            <span class="tooltip-stat-label">
                                <span class="tooltip-stat-dot" style="background: #2196F3"></span>
                                ONG
                            </span>
                            <strong>${data.ong}</strong>
                        </div>
                        <div class="tooltip-stat">
                            <span class="tooltip-stat-label">
                                <span class="tooltip-stat-dot" style="background: #FF9800"></span>
                                Partis politiques
                            </span>
                            <strong>${data.partis}</strong>
                        </div>
                        <div class="tooltip-stat">
                            <span class="tooltip-stat-label">
                                <span class="tooltip-stat-dot" style="background: #9C27B0"></span>
                                Confessions
                            </span>
                            <strong>${data.confessions}</strong>
                        </div>
                        <hr style="border-color: rgba(0,43,127,0.2); margin: 0.5rem 0;">
                        <div class="tooltip-stat">
                            <strong>Total</strong>
                            <strong style="color: var(--primary-blue); font-size: 1.2rem;">${total}</strong>
                        </div>
                    </div>
                `;
                
                // Position du tooltip
                const x = rect.left - mapRect.left + rect.width / 2 - tooltip.offsetWidth / 2;
                const y = rect.top - mapRect.top - tooltip.offsetHeight - 10;
                
                tooltip.style.left = x + 'px';
                tooltip.style.top = y + 'px';
                tooltip.classList.add('show');
                
                // Animation de la province
                this.setAttribute('data-animation', 'true');
            });
            
            province.addEventListener('mouseleave', function() {
                tooltip.classList.remove('show');
                this.removeAttribute('data-animation');
            });
            
            // Click pour plus de détails
            province.addEventListener('click', function() {
                const provinceName = this.dataset.province;
                // Redirection vers l'annuaire filtré par province
                window.location.href = `/annuaire?province=${encodeURIComponent(provinceName)}`;
            });
        });
        
        // Animation des provinces au scroll
        const mapObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const provinces = entry.target.querySelectorAll('.province');
                    provinces.forEach((province, index) => {
                        setTimeout(() => {
                            province.style.fill = getColorByDensity(province);
                            province.setAttribute('data-animation', 'true');
                        }, index * 100);
                    });
                }
            });
        }, { threshold: 0.5 });
        
        const mapContainer = document.querySelector('.map-container');
        if (mapContainer) {
            mapObserver.observe(mapContainer);
        }
        
        // Fonction pour colorer selon la densité
        function getColorByDensity(province) {
            const total = parseInt(province.dataset.associations || 0) + 
                         parseInt(province.dataset.ong || 0) + 
                         parseInt(province.dataset.partis || 0) + 
                         parseInt(province.dataset.confessions || 0);
            
            if (total > 80) return 'rgba(255,215,0,0.8)';
            if (total > 50) return 'rgba(255,215,0,0.6)';
            if (total > 30) return 'rgba(255,215,0,0.4)';
            return 'rgba(255,255,255,0.2)';
        }
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endpush