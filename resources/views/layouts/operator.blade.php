<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Opérateur') - PNGDI</title>
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Navigation supérieure -->
    <nav class="navbar navbar-expand-lg navbar-custom navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('operator.dashboard') }}">
                <i class="fas fa-building"></i> Espace Opérateur
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarOperator">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarOperator">
                <ul class="navbar-nav ms-auto">
                    <!-- Notifications -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                            <h6 class="dropdown-header">Notifications</h6>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('operator.notifications.index') }}">
                                <small class="text-muted">Aucune nouvelle notification</small>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center" href="{{ route('operator.notifications.index') }}">
                                Voir toutes les notifications
                            </a>
                        </div>
                    </li>
                    
                    <!-- Messages -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-envelope"></i>
                            <span class="notification-badge">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                            <h6 class="dropdown-header">Messages</h6>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('operator.messages.index') }}">
                                <small class="text-muted">Aucun nouveau message</small>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center" href="{{ route('operator.messages.index') }}">
                                Voir tous les messages
                            </a>
                        </div>
                    </li>
                    
                    <!-- Profil utilisateur -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> Utilisateur Test
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('operator.profil.index') }}">Mon profil</a></li>
                            <li><a class="dropdown-item" href="{{ route('home') }}">Retour au site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.dashboard') ? 'active' : '' }}" href="{{ route('operator.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Mes organisations</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.dossiers.*') ? 'active' : '' }}" href="{{ route('operator.dossiers.index') }}">
                                <i class="fas fa-folder"></i> Mes dossiers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#newOrgMenu">
                                <i class="fas fa-plus-circle"></i> Nouvelle organisation
                            </a>
                            <div class="collapse" id="newOrgMenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('operator.dossiers.create', 'association') }}">
                                            <i class="fas fa-users"></i> Association
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('operator.dossiers.create', 'ong') }}">
                                            <i class="fas fa-hands-helping"></i> ONG
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('operator.dossiers.create', 'parti') }}">
                                            <i class="fas fa-landmark"></i> Parti politique
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('operator.dossiers.create', 'confession') }}">
                                            <i class="fas fa-pray"></i> Confession religieuse
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Obligations</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.declarations.*') ? 'active' : '' }}" href="{{ route('operator.declarations.index') }}">
                                <i class="fas fa-file-contract"></i> Déclarations annuelles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.rapports.*') ? 'active' : '' }}" href="{{ route('operator.rapports.index') }}">
                                <i class="fas fa-chart-line"></i> Rapports d'activité
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.subventions.*') ? 'active' : '' }}" href="{{ route('operator.subventions.index') }}">
                                <i class="fas fa-money-check-alt"></i> Subventions
                            </a>
                        </li>
                        
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Communication</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.messages.*') ? 'active' : '' }}" href="{{ route('operator.messages.index') }}">
                                <i class="fas fa-envelope"></i> Messages
                                <span class="badge bg-primary ms-1">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.calendrier') ? 'active' : '' }}" href="{{ route('operator.calendrier') }}">
                                <i class="fas fa-calendar-alt"></i> Calendrier
                            </a>
                        </li>
                        
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                            <span>Ressources</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.guides') ? 'active' : '' }}" href="{{ route('operator.guides') }}">
                                <i class="fas fa-book"></i> Guides
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('operator.documents-types') ? 'active' : '' }}" href="{{ route('operator.documents-types') }}">
                                <i class="fas fa-file-download"></i> Documents types
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenu principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mt-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Espace Opérateur</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>

                <!-- Titre de la page -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'Espace Opérateur')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @yield('page-actions')
                    </div>
                </div>

                <!-- Messages flash -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Contenu de la page -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Conteneur pour les alertes dynamiques -->
    <div id="alert-container"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>