<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') - SGLP</title>
    
    <!-- Bootstrap 4 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Variables CSS - Couleurs Gabonaises */
        :root {
            --gabon-green: #009e3f;
            --gabon-yellow: #ffcd00;
            --gabon-blue: #003f7f;
            --gabon-red: #8b1538;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        /* Reset et base */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            font-size: 14px;
        }

        /* Layout principal */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar exacte selon capture */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #0e2f5b;
            overflow-y: auto;
            z-index: 1000;
        }

        /* Header sidebar avec logos */
        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .main-logo {
            background: var(--gabon-green);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .logo-text-group {
            display: flex;
            flex-direction: column;
        }

        .sidebar-title {
            color: var(--gabon-yellow);
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .sidebar-subtitle {
            color: var(--gabon-yellow);
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }

        .settings-icon {
            background: rgba(255,255,255,0.1);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* Profil utilisateur selon capture */
        .sidebar-profile {
            padding: 1rem;
            margin: 0 1rem 1.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-avatar {
            width: 45px;
            height: 45px;
            background: var(--gabon-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            position: relative;
        }

        .profile-avatar::after {
            content: '';
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: var(--gabon-green);
            border: 2px solid #1e40af;
            border-radius: 50%;
        }

        .profile-info h6 {
            color: white;
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .profile-info small {
            color: var(--gabon-yellow);
            font-size: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Navigation sections */
        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            color: var(--gabon-yellow);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 1rem;
            margin-bottom: 0.5rem;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 0.85rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            margin: 0 1rem 0.5rem 1rem;
            border-radius: 12px;
        }

        .nav-link-custom:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            text-decoration: none;
            transform: translateX(3px);
        }

        .nav-link-custom.active {
            background: linear-gradient(135deg, var(--gabon-green) 0%, #00b347 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 158, 63, 0.3);
            position: relative;
        }

        .nav-link-custom.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--gabon-yellow);
            border-radius: 0 4px 4px 0;
        }

        .nav-icon {
            width: 40px;
            height: 40px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .nav-link-custom.active .nav-icon {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        .nav-text {
            flex: 1;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .nav-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            min-width: 24px;
            text-align: center;
        }

        .nav-badge.warning {
            background: var(--gabon-yellow);
            color: #1e3a8a;
        }

        .nav-badge.info {
            background: #3b82f6;
            color: white;
        }

        .nav-badge.success {
            background: var(--gabon-green);
            color: white;
        }

        .nav-badge.users {
            background: var(--gabon-red);
            color: white;
        }

        /* Contenu principal */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header principal */
        .main-header {
            background: white;
            height: var(--header-height);
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Barre de recherche */
        .search-container {
            position: relative;
            width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.85rem;
            background: #f9fafb;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--gabon-green);
            box-shadow: 0 0 0 3px rgba(0, 158, 63, 0.1);
            background: white;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Actions header */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .action-btn {
            position: relative;
            width: 36px;
            height: 36px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            border-color: var(--gabon-green);
            color: var(--gabon-green);
            background: rgba(0, 158, 63, 0.05);
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--gabon-red);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 5px;
            border-radius: 8px;
            min-width: 16px;
            text-align: center;
        }

        /* Menu utilisateur */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }

        .user-menu:hover {
            border-color: var(--gabon-green);
            background: rgba(0, 158, 63, 0.05);
        }

        .user-avatar-header {
            width: 32px;
            height: 32px;
            background: var(--gabon-green);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .user-info-header {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .user-role {
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* Zone de contenu */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        /* Messages d'alerte */
        .alert {
            border: none;
            border-radius: 8px;
            border-left: 4px solid;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(0, 158, 63, 0.1);
            border-left-color: var(--gabon-green);
            color: var(--gabon-green);
        }

        .alert-danger {
            background: rgba(139, 21, 56, 0.1);
            border-left-color: var(--gabon-red);
            color: var(--gabon-red);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .search-container {
                display: none;
            }

            .header-title {
                font-size: 1.2rem;
            }
        }

        /* Scrollbar personnalisée */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar exacte selon capture -->
        <aside class="sidebar" id="sidebar">
            <!-- Logo et titre -->
            <div class="sidebar-header">
                <div class="logo-section">
                    <div class="main-logo">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="logo-text-group">
                        <h5 class="sidebar-title">SGLP</h5>
                        <div class="sidebar-subtitle">Admin</div>
                    </div>
                </div>
                <div class="settings-icon">
                    <i class="fas fa-expand-arrows-alt"></i>
                </div>
            </div>

            <!-- Profil utilisateur -->
            <div class="sidebar-profile">
                <div class="profile-avatar">AD</div>
                <div class="profile-info">
                    <h6>Administrateur SGLP</h6>
                    <small><i class="fas fa-crown"></i> Administrateur</small>
                </div>
            </div>

            <!-- Navigation sections -->
            <!-- ========== REMPLACER LA SECTION NAVIGATION DANS admin.blade.php ========== -->

                <!-- Navigation sections -->
              <!-- Navigation sections -->
                <nav class="sidebar-nav">
                    <!-- Tableau de Bord -->
                    <div class="nav-section">
                        <div class="nav-section-title">TABLEAU DE BORD</div>
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-eye"></i>
                                    <span class="nav-text">Vue d'ensemble</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.analytics') }}" class="nav-link-custom {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <span class="nav-text">Analytiques</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Gestion Dossiers -->
                    <div class="nav-section">
                        <div class="nav-section-title">GESTION DOSSIERS</div>
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="{{ route('admin.dossiers.en-attente') }}" class="nav-link-custom {{ request()->routeIs('admin.dossiers.en-attente') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-clock"></i>
                                    <span class="nav-text">En Attente</span>
                                    <span class="nav-badge warning">{{ \App\Models\Dossier::whereIn('statut', ['soumis'])->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.workflow.en-cours') }}" class="nav-link-custom {{ request()->routeIs('admin.workflow.en-cours') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <span class="nav-text">En Cours</span>
                                    <span class="nav-badge info">{{ \App\Models\Dossier::where('statut', 'en_cours')->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.workflow.termines') }}" class="nav-link-custom {{ request()->routeIs('admin.workflow.termines') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-check-circle"></i>
                                    <span class="nav-text">Terminés</span>
                                    <span class="nav-badge success">{{ \App\Models\Dossier::where('statut', 'approuve')->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.organisations.index') }}" class="nav-link-custom {{ request()->routeIs('admin.organisations*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-folder-open"></i>
                                    <span class="nav-text">Toutes Organisations</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Base de Données NIP - NOUVELLE SECTION -->
                    <div class="nav-section">
                        <div class="nav-section-title">BASE DE DONNÉES</div>
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="{{ route('admin.nip-database.index') }}" class="nav-link-custom {{ request()->routeIs('admin.nip-database.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-database"></i>
                                    <span class="nav-text">Base NIP</span>
                                    <span class="nav-badge info">{{ number_format(\App\Models\NipDatabase::count()) }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.nip-database.import') }}" class="nav-link-custom {{ request()->routeIs('admin.nip-database.import') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-upload"></i>
                                    <span class="nav-text">Import NIP</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.nip-database.template') }}" class="nav-link-custom">
                                    <i class="nav-icon fas fa-download"></i>
                                    <span class="nav-text">Template Excel</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Utilisateurs -->
                    <div class="nav-section">
                        <div class="nav-section-title">UTILISATEURS</div>
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="{{ route('admin.users.operators') }}" class="nav-link-custom {{ request()->routeIs('admin.users.operators') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <span class="nav-text">Opérateurs</span>
                                    <span class="nav-badge users">{{ \App\Models\User::where('role', 'operator')->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.agents') }}" class="nav-link-custom {{ request()->routeIs('admin.users.agents') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-tie"></i>
                                    <span class="nav-text">Agents</span>
                                    <span class="nav-badge">{{ \App\Models\User::where('role', 'agent')->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.create') }}" class="nav-link-custom {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-plus"></i>
                                    <span class="nav-text">Nouvel Agent</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Configuration -->
                    <div class="nav-section">
                        <div class="nav-section-title">CONFIGURATION</div>
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="{{ route('admin.referentiels.types-organisations') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.types-organisations') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <span class="nav-text">Types Organisations</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.referentiels.document-types') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.document-types') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <span class="nav-text">Types Documents</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.referentiels.zones.index') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.zones') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-map-marker-alt"></i>
                                    <span class="nav-text">Zones Géographiques</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Rapports -->
                    <div class="nav-section">
                        <div class="nav-section-title">RAPPORTS</div>
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.index') }}" class="nav-link-custom {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <span class="nav-text">Rapports Généraux</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.exports.index') }}" class="nav-link-custom {{ request()->routeIs('admin.exports*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-download"></i>
                                    <span class="nav-text">Exports</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Système -->
                    <div class="nav-section">
                        <div class="nav-section-title">SYSTÈME</div>
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="{{ route('admin.notifications.index') }}" class="nav-link-custom {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-bell"></i>
                                    <span class="nav-text">Notifications</span>
                                    @php
                                        $unreadCount = auth()->user()->unreadNotifications->count() ?? 0;
                                    @endphp
                                    @if($unreadCount > 0)
                                        <span class="nav-badge danger">{{ $unreadCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.activity-logs.index') }}" class="nav-link-custom {{ request()->routeIs('admin.activity-logs*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-history"></i>
                                    <span class="nav-text">Logs d'Activité</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings.index') }}" class="nav-link-custom {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cog"></i>
                                    <span class="nav-text">Paramètres</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>


        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <!-- Header principal -->
            <header class="main-header">
                <div class="header-left">
                    <h1 class="header-title">
                        <i class="fas fa-shield-alt" style="color: var(--gabon-blue);"></i>
                        @yield('title', 'Administration SGLP')
                    </h1>
                </div>

                <div class="header-right">
                    <!-- Recherche -->
                    <div class="search-container">
                        <i class="search-icon fas fa-search"></i>
                        <input type="text" class="search-input" placeholder="Rechercher dans l'administration...">
                    </div>

                    <!-- Actions -->
                    <div class="header-actions">
                        <button class="action-btn" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>

                        <button class="action-btn" title="Messages">
                            <i class="fas fa-envelope"></i>
                            <span class="notification-badge">2</span>
                        </button>

                        <!-- Menu utilisateur -->
                        <div class="dropdown">
                            <div class="user-menu" data-toggle="dropdown">
                                <div class="user-avatar-header">AD</div>
                                <div class="user-info-header">
                                    <div class="user-name">Admin SGLP</div>
                                    <div class="user-role">Administrateur</div>
                                </div>
                                <i class="fas fa-chevron-down ml-2" style="color: #6b7280; font-size: 0.8rem;"></i>
                            </div>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user mr-2"></i> Mon profil
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cog mr-2"></i> Paramètres
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Zone de contenu -->
            <div class="content-area">
                <!-- Messages d'alerte -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Contenu de la page -->
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log('SGLP Admin Layout - Design final chargé');
            
            // Auto-dismiss des alertes
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);

            // Animation hover sur les liens de navigation
            $('.nav-link-custom').hover(function() {
                if (!$(this).hasClass('active')) {
                    $(this).css('transform', 'translateX(5px)');
                }
            }, function() {
                if (!$(this).hasClass('active')) {
                    $(this).css('transform', 'translateX(0)');
                }
            });

            // Gestion responsive mobile
            if (window.innerWidth <= 768) {
                $('.header-left').prepend('<button class="btn btn-link p-0 mr-3" onclick="toggleMobileSidebar()"><i class="fas fa-bars"></i></button>');
            }
        });

        function toggleMobileSidebar() {
            $('#sidebar').toggleClass('active');
        }

        // Fermer le sidebar mobile en cliquant sur un lien
        $('.nav-link-custom').on('click', function() {
            if (window.innerWidth <= 768) {
                $('#sidebar').removeClass('active');
            }
        });
    </script>
</body>
</html>