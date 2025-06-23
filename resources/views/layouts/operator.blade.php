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
    
    <style>
        /* Variables CSS */
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --header-height: 70px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-bg: #003366;
            --sidebar-hover: #004080;
            --text-muted: #b3d1ff;
            --border-color: rgba(255,255,255,0.2);
        }

        /* Reset et base */
        * {
            box-sizing: border-box;
        }

        body {
            overflow-x: hidden;
            background: #f5f7fa;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* Wrapper principal */
        .operator-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar moderne */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        /* Header sidebar */
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            display: none;
        }

        /* Profil utilisateur */
        .sidebar-profile {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255,255,255,0.03);
            margin: 0.5rem;
            border-radius: 15px;
        }

        .profile-avatar {
            position: relative;
        }

        .avatar-circle {
            width: 45px;
            height: 45px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid var(--sidebar-bg);
            border-radius: 50%;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            color: white;
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .profile-role {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Navigation */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .nav-item-custom {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            margin: 0 0.5rem;
            border-radius: 10px;
        }

        .nav-item-custom:hover {
            background: var(--sidebar-hover);
            color: white;
            transform: translateX(5px);
            text-decoration: none;
        }

        .nav-item-custom.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .nav-item-custom.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 70%;
            background: #ffd700;
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-right: 12px;
            font-size: 1rem;
        }

        .nav-item-custom.active .nav-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        .nav-text {
            flex: 1;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .nav-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .nav-badge.warning {
            background: rgba(255, 193, 7, 0.3);
            color: #fff3cd;
        }

        .nav-badge.danger {
            background: rgba(220, 53, 69, 0.3);
            color: #f8d7da;
        }

        .nav-badge.info {
            background: rgba(23, 162, 184, 0.3);
            color: #d1ecf1;
        }

        /* Sous-menu nouvelle organisation */
        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .nav-submenu.show {
            max-height: 300px;
        }

        .nav-submenu-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1.5rem 0.5rem 3.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .nav-submenu-item:hover {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.05);
        }

        /* Footer sidebar */
        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 0.5rem;
        }

        .sidebar-footer-item {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.75rem;
            background: rgba(255,255,255,0.05);
            border: none;
            border-radius: 10px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .sidebar-footer-item:hover {
            background: var(--sidebar-hover);
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }

        .sidebar-footer-item.logout:hover {
            background: rgba(255, 71, 87, 0.2);
            color: #ff4757;
        }

        /* Contenu principal */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        /* Header content */
        .content-header {
            background: white;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
            padding: 0.5rem;
            display: none;
        }

        .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1d23;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        /* Recherche header */
        .header-search {
            position: relative;
            display: flex;
            align-items: center;
            background: #f5f7fa;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            min-width: 300px;
        }

        .header-search i {
            color: var(--text-muted);
            margin-right: 0.5rem;
        }

        .search-input {
            background: none;
            border: none;
            outline: none;
            width: 100%;
            font-size: 0.9rem;
            color: #333;
        }

        /* Actions header */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .action-btn {
            position: relative;
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #333;
            font-size: 1.1rem;
        }

        .action-btn:hover {
            background: #f5f7fa;
            transform: translateY(-2px);
        }

        .action-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* User menu */
        .user-menu-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .user-menu-toggle:hover {
            background: #f5f7fa;
        }

        .user-avatar-small {
            width: 35px;
            height: 35px;
            background: var(--primary-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Content body */
        .content-body {
            padding: 0;
            flex: 1;
            overflow-y: auto;
        }

        /* Breadcrumb custom */
        .breadcrumb-custom {
            background: transparent;
            padding: 1rem 2rem;
            margin-bottom: 0;
        }

        .breadcrumb-custom .breadcrumb-item {
            font-size: 0.9rem;
        }

        .breadcrumb-custom .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }

        .breadcrumb-custom .breadcrumb-item a:hover {
            color: #667eea;
        }

        /* Overlay mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
            transition: opacity 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: block;
            }

            .menu-toggle {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .header-search {
                display: none;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .content-body {
                padding: 1rem;
            }
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .nav-item-custom {
            animation: slideIn 0.3s ease-out;
        }

        /* Styles pour les alertes */
        .alert {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        /* Ancien CSS navbar-custom pour éviter les conflits */
        .navbar-custom {
            display: none;
        }

        /* Correction pour le dropdown Bootstrap */
        .dropdown-menu-custom {
            min-width: 300px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 0.5rem 0;
        }

        .dropdown-item-custom {
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .dropdown-item-custom:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="operator-wrapper">
        <!-- Sidebar moderne -->
        <aside class="sidebar" id="sidebar">
            <!-- Logo et toggle -->
            <div class="sidebar-header">
                <a href="{{ route('operator.dashboard') }}" class="sidebar-logo">
                    <div class="logo-img">
                        <i class="fas fa-building"></i>
                    </div>
                    <span class="logo-text">PNGDI</span>
                </a>
                <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Profil utilisateur -->
            <div class="sidebar-profile">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="status-indicator"></span>
                </div>
                <div class="profile-info">
                    <h6 class="profile-name">{{ auth()->user()->name }}</h6>
                    <span class="profile-role">
                        <i class="fas fa-user-tag me-1"></i>
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">PRINCIPAL</span>
                    
                    <a href="{{ route('operator.dashboard') }}" class="nav-item-custom {{ request()->routeIs('operator.dashboard') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Tableau de bord</span>
                    </a>

                    <a href="{{ route('operator.dossiers.index') }}" class="nav-item-custom {{ request()->routeIs('operator.dossiers.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <span class="nav-text">Mes dossiers</span>
                        <span class="nav-badge">0</span>
                    </a>

                    <a href="#" class="nav-item-custom" onclick="toggleSubmenu(event, 'newOrgMenu')">
                        <div class="nav-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <span class="nav-text">Nouvelle organisation</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="nav-submenu" id="newOrgMenu">
                        <a href="{{ route('operator.dossiers.create', 'association') }}" class="nav-submenu-item">
                            <i class="fas fa-users me-2"></i> Association
                        </a>
                        <a href="{{ route('operator.dossiers.create', 'ong') }}" class="nav-submenu-item">
                            <i class="fas fa-hands-helping me-2"></i> ONG
                        </a>
                        <a href="{{ route('operator.dossiers.create', 'parti') }}" class="nav-submenu-item">
                            <i class="fas fa-landmark me-2"></i> Parti politique
                        </a>
                        <a href="{{ route('operator.dossiers.create', 'confession') }}" class="nav-submenu-item">
                            <i class="fas fa-pray me-2"></i> Confession religieuse
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">OBLIGATIONS</span>
                    
                    <a href="{{ route('operator.declarations.index') }}" class="nav-item-custom {{ request()->routeIs('operator.declarations.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <span class="nav-text">Déclarations annuelles</span>
                    </a>

                    <a href="{{ route('operator.rapports.index') }}" class="nav-item-custom {{ request()->routeIs('operator.rapports.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="nav-text">Rapports d'activité</span>
                    </a>

                    <a href="{{ route('operator.subventions.index') }}" class="nav-item-custom {{ request()->routeIs('operator.subventions.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <span class="nav-text">Subventions</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">COMMUNICATION</span>
                    
                    <a href="{{ route('operator.messages.index') }}" class="nav-item-custom {{ request()->routeIs('operator.messages.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="nav-text">Messages</span>
                        <span class="nav-badge danger">0</span>
                    </a>

                    <a href="{{ route('operator.notifications.index') }}" class="nav-item-custom {{ request()->routeIs('operator.notifications.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <span class="nav-text">Notifications</span>
                        <span class="nav-badge info">0</span>
                    </a>

                    <a href="{{ route('operator.calendrier') }}" class="nav-item-custom {{ request()->routeIs('operator.calendrier') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <span class="nav-text">Calendrier</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">RESSOURCES</span>
                    
                    <a href="{{ route('guides') }}" class="nav-item-custom" target="_blank">
                        <div class="nav-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="nav-text">Guides pratiques</span>
                        <i class="fas fa-external-link-alt ms-auto"></i>
                    </a>

                    <a href="{{ route('documents.index') }}" class="nav-item-custom" target="_blank">
                        <div class="nav-icon">
                            <i class="fas fa-file-download"></i>
                        </div>
                        <span class="nav-text">Documents types</span>
                        <i class="fas fa-external-link-alt ms-auto"></i>
                    </a>
                </div>
            </nav>

            <!-- Actions du bas -->
            <div class="sidebar-footer">
                <a href="{{ route('operator.profil.index') }}" class="sidebar-footer-item">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline flex-fill">
                    @csrf
                    <button type="submit" class="sidebar-footer-item logout w-100">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <!-- Header minimaliste -->
            <header class="content-header">
                <div class="header-left">
                    <button class="menu-toggle d-md-none" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Espace Opérateur')</h1>
                </div>

                <div class="header-right">
                    <!-- Recherche -->
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher..." class="search-input">
                    </div>

                    <!-- Actions rapides -->
                    <div class="header-actions">
                        <div class="dropdown">
                            <button class="action-btn" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="action-badge">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item dropdown-item-custom" href="{{ route('operator.notifications.index') }}">
                                    <small class="text-muted">Aucune nouvelle notification</small>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item dropdown-item-custom text-center" href="{{ route('operator.notifications.index') }}">
                                    Voir toutes les notifications
                                </a>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="action-btn" data-bs-toggle="dropdown">
                                <i class="fas fa-envelope"></i>
                                <span class="action-badge">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                <h6 class="dropdown-header">Messages</h6>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item dropdown-item-custom" href="{{ route('operator.messages.index') }}">
                                    <small class="text-muted">Aucun nouveau message</small>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item dropdown-item-custom text-center" href="{{ route('operator.messages.index') }}">
                                    Voir tous les messages
                                </a>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="user-menu-toggle" data-bs-toggle="dropdown">
                                <div class="user-avatar-small">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <i class="fas fa-chevron-down ms-2"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                <li><a class="dropdown-item dropdown-item-custom" href="{{ route('operator.profil.index') }}">
                                    <i class="fas fa-user me-2"></i> Mon profil
                                </a></li>
                                <li><a class="dropdown-item dropdown-item-custom" href="{{ route('home') }}">
                                    <i class="fas fa-home me-2"></i> Retour au site
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item dropdown-item-custom">
                                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenu de la page -->
            <div class="content-body">
                <!-- Messages flash -->
                <div class="px-4 pt-3">
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
                </div>

                <!-- Actions de page -->
                @hasSection('page-actions')
                    <div class="d-flex justify-content-end mb-3 px-4">
                        @yield('page-actions')
                    </div>
                @endif

                <!-- Contenu de la page -->
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Overlay mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Conteneur pour les alertes dynamiques -->
    <div id="alert-container"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            const submenu = document.getElementById(submenuId);
            const chevron = event.currentTarget.querySelector('.fa-chevron-down');
            
            submenu.classList.toggle('show');
            if (chevron) {
                chevron.style.transform = submenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0)';
            }
        }

        // Fermer le sidebar en cliquant sur un lien (mobile)
        document.querySelectorAll('.nav-item-custom, .nav-submenu-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768 && !item.querySelector('.fa-chevron-down')) {
                    toggleSidebar();
                }
            });
        });

        // Ouvrir automatiquement le sous-menu si une page enfant est active
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            if (currentPath.includes('/dossiers/create/')) {
                document.getElementById('newOrgMenu').classList.add('show');
                const chevron = document.querySelector('[onclick*="newOrgMenu"] .fa-chevron-down');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>