<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Accueil') - PNGDI | Portail National de Gestion des Libertés Individuelles</title>
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personnalisé -->
    <style>
        :root {
            --primary-blue: #002B7F;
            --secondary-gold: #FFD700;
            --light-blue: #E6F0FF;
            --dark-blue: #001A4D;
            --text-dark: #333;
            --text-light: #666;
            --white: #ffffff;
            --bg-light: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Navigation */
        nav.navbar-main {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }

        nav.navbar-main.scrolled {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 0.5rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-blue) !important;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .navbar-brand:hover {
            color: var(--dark-blue) !important;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            margin-right: 10px;
            background: var(--secondary-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .logo-icon::before {
            content: "PGI";
            color: var(--primary-blue);
            font-weight: bold;
            font-size: 0.9rem;
        }

        .navbar-nav .nav-link {
            color: var(--text-dark);
            font-weight: 500;
            margin: 0 0.5rem;
            position: relative;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-blue);
            transition: width 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-blue);
        }

        .btn-custom-primary {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-custom-primary:hover {
            background: var(--dark-blue);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,43,127,0.3);
            color: var(--white);
        }

        .btn-custom-warning {
            background: var(--secondary-gold);
            color: var(--primary-blue);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-custom-warning:hover {
            background: #e6c200;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,215,0,0.3);
            color: var(--primary-blue);
        }

        /* Hero Section */
        .hero {
            min-height: 90vh;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .hero h1 {
            font-size: 3.5rem;
            color: var(--white);
            margin-bottom: 1rem;
            animation: slideInLeft 1s ease;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--secondary-gold);
            margin-bottom: 2rem;
            animation: slideInLeft 1s ease 0.2s;
            animation-fill-mode: both;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Stats Section */
        .stats {
            background: var(--secondary-gold);
            padding: 4rem 0;
            position: relative;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        /* Service Cards */
        .service-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
            border: 1px solid #eee;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary-blue);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: var(--light-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-blue);
        }

        /* News Cards */
        .news-card {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .news-card:hover {
            transform: translateY(-5px);
        }

        .news-category {
            background: var(--primary-blue);
            color: var(--white);
            padding: 0.25rem 1rem;
            display: inline-block;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Footer */
        footer {
            background: var(--primary-blue);
            color: var(--white);
            padding: 3rem 0 1rem;
        }

        footer a {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: var(--secondary-gold);
        }

        /* Dropdown menu styles */
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: var(--light-blue);
            color: var(--primary-blue);
            padding-left: 2rem;
        }

        /* User menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .user-menu {
                flex-direction: column;
                width: 100%;
                gap: 0.5rem;
            }
            
            .user-menu > * {
                width: 100%;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-main fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <div class="logo-icon"></div>
                PNGDI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('actualites.*') ? 'active' : '' }}" href="{{ route('actualites.index') }}">
                            <i class="fas fa-newspaper me-1"></i>Actualités
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs(['documents.*', 'faq', 'annuaire*', 'guides', 'calendrier']) ? 'active' : '' }}" 
                           href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-th me-1"></i>Services
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('documents.*') ? 'active' : '' }}" 
                                   href="{{ route('documents.index') }}">
                                    <i class="fas fa-file-alt me-2"></i>Documents
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('faq') ? 'active' : '' }}" 
                                   href="{{ route('faq') }}">
                                    <i class="fas fa-question-circle me-2"></i>FAQ
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('annuaire*') ? 'active' : '' }}" 
                                   href="{{ route('annuaire.index') }}">
                                    <i class="fas fa-address-book me-2"></i>Annuaire
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('guides') ? 'active' : '' }}" 
                                   href="{{ route('guides') }}">
                                    <i class="fas fa-book me-2"></i>Guides
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('calendrier') ? 'active' : '' }}" 
                                   href="{{ route('calendrier') }}">
                                    <i class="fas fa-calendar-alt me-2"></i>Calendrier
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                            <i class="fas fa-info-circle me-1"></i>À propos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>
                
                <!-- Section user-menu mise à jour -->
                <div class="user-menu">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Connexion
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-custom-primary">
                            <i class="fas fa-user-plus me-1"></i>Inscription
                        </a>
                    @else
                        @if(!auth()->user()->hasVerifiedEmail())
                            <a href="{{ route('verification.notice') }}" class="btn btn-custom-warning">
                                <i class="fas fa-envelope me-1"></i>Vérifier email
                            </a>
                        @else
                            @if(auth()->user()->role === 'operator')
                                <a href="{{ route('operator.dashboard') }}" class="btn btn-custom-primary">
                                    <i class="fas fa-th-large me-1"></i>Mon espace
                                </a>
                            @elseif(in_array(auth()->user()->role, ['admin', 'agent']))
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-custom-primary">
                                    <i class="fas fa-th-large me-1"></i>Administration
                                </a>
                            @else
                                <a href="{{ route('home') }}" class="btn btn-custom-primary">
                                    <i class="fas fa-th-large me-1"></i>Accueil
                                </a>
                            @endif
                        @endif
                        
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary" title="Se déconnecter">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="d-none d-sm-inline ms-1">Déconnexion</span>
                            </button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main style="margin-top: 80px;">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle me-2"></i>À propos du PNGDI
                    </h5>
                    <p>Le Portail National de Gestion des Libertés Individuelles facilite la formalisation et la gestion des organisations associatives, religieuses et politiques du Gabon.</p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-link me-2"></i>Liens rapides
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="{{ route('actualites.index') }}">
                                <i class="fas fa-chevron-right me-1"></i>Actualités
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('documents.index') }}">
                                <i class="fas fa-chevron-right me-1"></i>Documents
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('faq') }}">
                                <i class="fas fa-chevron-right me-1"></i>FAQ
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('guides') }}">
                                <i class="fas fa-chevron-right me-1"></i>Guides pratiques
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('contact') }}">
                                <i class="fas fa-chevron-right me-1"></i>Contact
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-phone-alt me-2"></i>Contact
                    </h5>
                    <p class="mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>Libreville, Gabon
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-phone me-2"></i>+241 01 23 45 67
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-envelope me-2"></i>contact@pngdi.ga
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-clock me-2"></i>Lun - Ven: 8h00 - 17h00
                    </p>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center py-3">
                <p class="mb-0">&copy; 2025 PNGDI - Ministère de l'Intérieur et de la Sécurité. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Nav background on scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.navbar-main');
            if (window.scrollY > 100) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Fade in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });
    </script>
    @stack('scripts')
</body>
</html>