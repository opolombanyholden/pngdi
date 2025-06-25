@extends('layouts.operator')

@section('title', 'Mes Adhérents')

@section('page-title', 'Gestion des Adhérents')

@push('styles')
<style>
    /* === COULEURS CHARTE GABONAISE === */
    :root {
        --gabon-green: #009e3f;
        --gabon-green-light: #00c851;
        --gabon-green-dark: #007a32;
        --gabon-yellow: #ffcd00;
        --gabon-yellow-light: #ffe066;
        --gabon-yellow-dark: #e6b800;
        --gabon-blue: #003f7f;
        --gabon-blue-light: #0066cc;
        --gabon-blue-dark: #002d5a;
        --text-contrast: #ffffff;
        --text-dark: #2c3e50;
        --bg-light: #f8f9fa;
    }

    .stats-card {
        border: none;
        border-radius: 15px;
        color: var(--text-contrast);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }
    
    .stats-card:hover::before {
        transform: translateX(100%);
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    /* Cartes avec couleurs gabonaises - 1 couleur dégradée par composant */
    .stats-card.gabon-green { 
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-green-dark) 100%);
    }
    .stats-card.gabon-yellow { 
        background: linear-gradient(135deg, var(--gabon-yellow) 0%, var(--gabon-yellow-dark) 100%);
    }
    .stats-card.gabon-blue { 
        background: linear-gradient(135deg, var(--gabon-blue) 0%, var(--gabon-blue-dark) 100%);
    }
    .stats-card.gabon-red { 
        background: linear-gradient(135deg, #8b1538 0%, #6d1029 100%);
    }
    
    /* Texte contrasté pour toutes les cartes statistiques */
    .stats-card .metric-number,
    .stats-card .text-white-50,
    .stats-card .text-white,
    .stats-card .text-uppercase {
        color: var(--text-contrast) !important;
    }
    
    .stats-card i {
        color: rgba(255, 255, 255, 0.7) !important;
    }
    
    /* Couleurs spécifiques pour la carte jaune (fond clair) */
    .stats-card.gabon-yellow .metric-number,
    .stats-card.gabon-yellow .text-white-50,
    .stats-card.gabon-yellow .text-white,
    .stats-card.gabon-yellow .text-uppercase {
        color: var(--text-dark) !important;
        font-weight: 700;
    }
    
    .stats-card.gabon-yellow i {
        color: rgba(44, 62, 80, 0.8) !important;
    }
    
    /* Couleurs spécifiques pour la carte verte - Total Adhérents */
    .stats-card.gabon-green .metric-number {
        color: var(--gabon-yellow) !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    /* Couleurs spécifiques pour la carte rouge - Inactifs */
    .stats-card.gabon-red .metric-number,
    .stats-card.gabon-red .text-white-50,
    .stats-card.gabon-red .text-white,
    .stats-card.gabon-red .text-uppercase {
        color: var(--text-contrast) !important;
    }
    
    /* Couleurs spécifiques pour la carte bleue - Organisations */
    .stats-card.gabon-blue .metric-number {
        color: var(--gabon-yellow) !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .org-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        border: 2px solid transparent;
    }
    
    .org-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        border-color: var(--gabon-green);
    }
    
    .org-card-header {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        color: var(--text-contrast);
        border-radius: 20px 20px 0 0;
        padding: 1.5rem;
        position: relative;
    }
    
    .org-card-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--gabon-green), var(--gabon-yellow), var(--gabon-blue));
        background-size: 200% 100%;
        animation: gabonGradientShift 3s ease infinite;
    }
    
    @keyframes gabonGradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .member-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    
    .member-table thead {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
    }
    
    .member-table th {
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        color: var(--text-contrast);
        padding: 1rem 0.75rem;
    }
    
    .member-table td {
        border: none;
        border-bottom: 1px solid #e8f5e8;
        padding: 1rem 0.75rem;
        vertical-align: middle;
        color: var(--text-dark);
    }
    
    .member-table tbody tr {
        transition: all 0.2s ease;
    }
    
    .member-table tbody tr:hover {
        background: linear-gradient(135deg, #e8f5e8 0%, #fff9e6 100%);
        transform: scale(1.01);
    }
    
    .badge-modern {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: 2px solid transparent;
    }
    
    .badge-gabon-success {
        background: var(--gabon-green);
        color: var(--text-contrast);
        border-color: var(--gabon-green-dark);
    }
    
    .badge-gabon-warning {
        background: var(--gabon-yellow);
        color: var(--text-dark);
        border-color: var(--gabon-yellow-dark);
    }
    
    .badge-gabon-info {
        background: var(--gabon-blue);
        color: var(--text-contrast);
        border-color: var(--gabon-blue-dark);
    }
    
    .btn-modern {
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }
    
    .btn-gabon-primary {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        color: var(--text-contrast);
        border-color: var(--gabon-green);
    }
    
    .btn-gabon-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 158, 63, 0.3);
        color: var(--text-contrast);
    }
    
    .btn-outline-gabon-primary {
        background: transparent;
        color: var(--gabon-green);
        border-color: var(--gabon-green);
    }
    
    .btn-outline-gabon-primary:hover {
        background: var(--gabon-green);
        color: var(--text-contrast);
        transform: translateY(-2px);
    }
    
    .floating-action {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
    }
    
    .fab {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        border: 3px solid var(--gabon-yellow);
        color: var(--text-contrast);
        font-size: 1.5rem;
        box-shadow: 0 8px 32px rgba(0, 158, 63, 0.4);
        transition: all 0.3s ease;
    }
    
    .fab:hover {
        transform: scale(1.1) rotate(180deg);
        box-shadow: 0 12px 40px rgba(0, 158, 63, 0.6);
        border-color: var(--gabon-yellow-light);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%);
        border-radius: 20px;
        border: 2px dashed var(--gabon-green);
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: var(--gabon-green);
        margin-bottom: 1.5rem;
        opacity: 0.8;
    }
    
    .search-box {
        position: relative;
        margin-bottom: 2rem;
    }
    
    .search-input {
        border-radius: 50px;
        padding: 0.75rem 1.5rem 0.75rem 3rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        background: white;
        color: var(--text-dark);
    }
    
    .search-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        transform: translateY(-2px);
    }
    
    .search-input::placeholder {
        color: #6c757d;
    }
    
    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 1.1rem;
    }
    
    .filter-chips {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .filter-chip {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        color: var(--text-contrast);
        border: 2px solid transparent;
        border-radius: 50px;
        padding: 0.4rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .filter-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0, 158, 63, 0.3);
        border-color: var(--gabon-yellow);
    }
    
    .filter-chip.active {
        background: var(--gabon-yellow);
        color: var(--text-dark);
        border-color: var(--gabon-yellow-dark);
    }
    
    .progress-modern {
        height: 8px;
        border-radius: 10px;
        background: rgba(255,255,255,0.3);
        overflow: hidden;
        margin: 1rem 0;
    }
    
    .progress-bar-modern {
        height: 100%;
        border-radius: 10px;
        background: linear-gradient(90deg, var(--gabon-yellow), var(--gabon-yellow-light));
        transition: width 1s ease;
        position: relative;
    }
    
    .progress-bar-modern::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-image: linear-gradient(45deg, rgba(255,255,255,.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.2) 50%, rgba(255,255,255,.2) 75%, transparent 75%, transparent);
        background-size: 1rem 1rem;
        animation: progressStripes 1s linear infinite;
    }
    
    @keyframes progressStripes {
        0% { background-position: 1rem 0; }
        100% { background-position: 0 0; }
    }
    
    .metric-box {
        text-align: center;
        padding: 1.5rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 2px solid var(--gabon-yellow);
    }
    
    .metric-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        border-color: var(--gabon-green);
    }
    
    .metric-number {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Amélioration de la lisibilité pour les barres de progression */
    .stats-card .progress-bar-modern {
        background: linear-gradient(90deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
    }
    
    /* Avatar avec couleurs gabonaises */
    .avatar-circle {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        border: 2px solid var(--gabon-yellow);
    }
    
    /* Amélioration des boutons d'action */
    .btn-outline-primary {
        color: var(--gabon-green);
        border-color: var(--gabon-green);
    }
    
    .btn-outline-primary:hover {
        background: var(--gabon-green);
        border-color: var(--gabon-green);
        color: var(--text-contrast);
    }
    
    .btn-outline-warning {
        color: var(--gabon-yellow-dark);
        border-color: var(--gabon-yellow);
    }
    
    .btn-outline-warning:hover {
        background: var(--gabon-yellow);
        border-color: var(--gabon-yellow);
        color: var(--text-dark);
    }
    
    .btn-outline-info {
        color: var(--gabon-blue);
        border-color: var(--gabon-blue);
    }
    
    .btn-outline-info:hover {
        background: var(--gabon-blue);
        border-color: var(--gabon-blue);
        color: var(--text-contrast);
    }
    
    /* Amélioration du contraste pour les badges */
    .badge-outline-primary {
        color: var(--gabon-green);
        border: 1px solid var(--gabon-green);
        background: rgba(0, 158, 63, 0.1);
        font-weight: 600;
    }
    
    .animate-in {
        animation: slideInUp 0.6s ease forwards;
        opacity: 0;
        transform: translateY(30px);
    }
    
    @keyframes slideInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Dropdown menu avec thème gabonais */
    .dropdown-menu {
        border: 2px solid var(--gabon-yellow);
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }
    
    .dropdown-item {
        color: var(--text-dark);
        font-weight: 500;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        color: var(--text-contrast);
    }
    
    /* Footer des cartes d'organisation */
    .card-footer {
        background: linear-gradient(135deg, #f8f9fa 0%, var(--bg-light) 100%);
        border-top: 1px solid var(--gabon-yellow);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Barre de recherche et filtres -->
    <div class="search-box animate-in" style="animation-delay: 0.1s;">
        <div class="position-relative">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control search-input" placeholder="Rechercher un adhérent, organisation..." id="searchInput">
        </div>
    </div>
    
    <div class="filter-chips animate-in" style="animation-delay: 0.2s;">
        <button class="filter-chip active" data-filter="all">
            <i class="fas fa-users mr-1"></i> Tous
        </button>
        <button class="filter-chip" data-filter="active">
            <i class="fas fa-user-check mr-1"></i> Actifs
        </button>
        <button class="filter-chip" data-filter="inactive">
            <i class="fas fa-user-times mr-1"></i> Inactifs
        </button>
        <button class="filter-chip" data-filter="recent">
            <i class="fas fa-clock mr-1"></i> Récents
        </button>
    </div>

    <!-- Statistiques modernes avec couleurs gabonaises -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.3s;">
            <div class="stats-card gabon-green p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number text-white">{{ $totalAdherents ?? 0 }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Total Adhérents</div>
                        <div class="progress-modern">
                            <div class="progress-bar-modern" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-users fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.4s;">
            <div class="stats-card gabon-yellow p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number text-white">{{ $adherentsActifs ?? 0 }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Actifs</div>
                        <div class="progress-modern">
                            <div class="progress-bar-modern" style="width: {{ $totalAdherents > 0 ? ($adherentsActifs / $totalAdherents * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-user-check fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.5s;">
            <div class="stats-card gabon-red p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number text-white">{{ $adherentsInactifs ?? 0 }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Inactifs</div>
                        <div class="progress-modern">
                            <div class="progress-bar-modern" style="width: {{ $totalAdherents > 0 ? ($adherentsInactifs / $totalAdherents * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-user-times fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.6s;">
            <div class="stats-card gabon-blue p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number text-white">{{ $organisations->count() ?? 0 }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Organisations</div>
                        <div class="progress-modern">
                            <div class="progress-bar-modern" style="width: {{ min(($organisations->count() ?? 0) * 25, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-building fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organisations et adhérents -->
    @if($organisations && $organisations->count() > 0)
        @foreach($organisations as $index => $organisation)
            <div class="org-card animate-in" style="animation-delay: {{ 0.7 + ($index * 0.1) }}s;">
                <div class="org-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 font-weight-bold">{{ $organisation->nom ?? 'Nom non défini' }}</h4>
                                <p class="mb-0 text-white-50">
                                    <i class="fas fa-tag mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $organisation->type ?? 'Non défini')) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="badge-modern badge-gabon-info mb-2">
                                {{ $organisation->statut_label ?? 'Statut inconnu' }}
                            </div>
                            <div class="metric-box bg-white text-dark d-inline-block" style="min-width: 100px;">
                                <div class="metric-number" style="font-size: 1.5rem;">{{ $organisation->adherents_count ?? 0 }}</div>
                                <div style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600; color: var(--text-dark);">Adhérents</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($organisation->adherents && $organisation->adherents->count() > 0)
                        <div class="member-table">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user mr-2"></i>Adhérent</th>
                                        <th><i class="fas fa-id-card mr-2"></i>NIP</th>
                                        <th><i class="fas fa-phone mr-2"></i>Contact</th>
                                        <th><i class="fas fa-calendar mr-2"></i>Adhésion</th>
                                        <th><i class="fas fa-signal mr-2"></i>Statut</th>
                                        <th><i class="fas fa-cogs mr-2"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organisation->adherents as $adherent)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle text-white mr-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px; border-radius: 50%; font-weight: bold;">
                                                        {{ strtoupper(substr($adherent->nom ?? 'U', 0, 1)) }}{{ strtoupper(substr($adherent->prenom ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold" style="color: var(--text-dark);">{{ $adherent->nom_complet ?? ($adherent->nom . ' ' . $adherent->prenom) ?? 'N/A' }}</div>
                                                        <div class="text-muted small">{{ $adherent->email ?? 'Email non renseigné' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-outline-primary">{{ $adherent->nip ?? 'N/A' }}</span>
                                            </td>
                                            <td style="color: var(--text-dark);">{{ $adherent->telephone ?? 'N/A' }}</td>
                                            <td>
                                                <div class="text-center">
                                                    <div class="font-weight-bold" style="color: var(--text-dark);">{{ $adherent->date_adhesion ? $adherent->date_adhesion->format('d/m/Y') : 'N/A' }}</div>
                                                    <div class="text-muted small">
                                                        {{ $adherent->date_adhesion ? $adherent->date_adhesion->diffForHumans() : '' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge-modern badge-gabon-{{ ($adherent->is_active ?? true) ? 'success' : 'warning' }}">
                                                    <i class="fas fa-{{ ($adherent->is_active ?? true) ? 'check' : 'times' }} mr-1"></i>
                                                    {{ ($adherent->is_active ?? true) ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary btn-modern" data-toggle="tooltip" title="Voir le profil">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning btn-modern" data-toggle="tooltip" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info btn-modern" data-toggle="tooltip" title="Contacter">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($organisation->adherents_count > 5)
                            <div class="text-center p-4 bg-light">
                                <p class="text-muted mb-3">
                                    <i class="fas fa-ellipsis-h mr-2"></i>
                                    {{ $organisation->adherents_count - 5 }} autre(s) adhérent(s) non affichés
                                </p>
                                <a href="{{ route('operator.members.by-organisation', $organisation->id) }}" 
                                   class="btn btn-gabon-primary btn-modern">
                                    <i class="fas fa-users mr-2"></i>
                                    Voir tous les adhérents ({{ $organisation->adherents_count }})
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="empty-state m-4">
                            <div class="empty-state-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h5 class="text-muted mb-3">Aucun adhérent</h5>
                            <p class="text-muted mb-4">Cette organisation n'a pas encore d'adhérents enregistrés.</p>
                            <a href="{{ route('operator.members.create') }}" class="btn btn-gabon-primary btn-modern">
                                <i class="fas fa-plus mr-2"></i>Ajouter le premier adhérent
                            </a>
                        </div>
                    @endif
                </div>
                
                <div class="card-footer border-0 p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <a href="{{ route('operator.members.by-organisation', $organisation->id) }}" 
                                   class="btn btn-outline-gabon-primary btn-modern">
                                    <i class="fas fa-users mr-2"></i>Gérer
                                </a>
                                <a href="{{ route('operator.members.export', $organisation->id) }}" 
                                   class="btn btn-outline-secondary btn-modern">
                                    <i class="fas fa-download mr-2"></i>Export
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-right">
                                <div class="progress-modern">
                                    <div class="progress-bar-modern" style="width: {{ min(($organisation->adherents_count ?? 0) * 10, 100) }}%"></div>
                                </div>
                                <small class="text-muted">Objectif: 10 adhérents</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state animate-in" style="animation-delay: 0.7s;">
            <div class="empty-state-icon">
                <i class="fas fa-building"></i>
            </div>
            <h3 class="text-muted mb-3">Aucune organisation</h3>
            <p class="text-muted mb-4">Vous devez d'abord créer une organisation pour pouvoir gérer des adhérents.</p>
            <a href="{{ route('operator.dossiers.create', 'association') }}" class="btn btn-gabon-primary btn-modern btn-lg">
                <i class="fas fa-plus mr-2"></i>Créer votre première organisation
            </a>
        </div>
    @endif
</div>

<!-- Bouton d'action flottant avec style gabonais -->
<div class="floating-action">
    <button class="fab" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-plus"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="{{ route('operator.members.create') }}">
            <i class="fas fa-user-plus mr-2"></i>Nouvel adhérent
        </a>
        <a class="dropdown-item" href="{{ route('operator.dossiers.create', 'association') }}">
            <i class="fas fa-building mr-2"></i>Nouvelle organisation
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('operator.members.import.template') }}">
            <i class="fas fa-file-import mr-2"></i>Import en masse
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialiser les animations
    $('.animate-in').each(function(index) {
        $(this).css('animation-delay', (index * 0.1) + 's');
    });
    
    // Initialiser les tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Filtres
    $('.filter-chip').click(function() {
        $('.filter-chip').removeClass('active');
        $(this).addClass('active');
        
        const filter = $(this).data('filter');
        filterMembers(filter);
    });
    
    // Recherche en temps réel
    $('#searchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        searchMembers(searchTerm);
    });
    
    function filterMembers(filter) {
        $('.org-card').each(function() {
            const card = $(this);
            let shouldShow = false;
            
            card.find('tbody tr').each(function() {
                const row = $(this);
                const isActive = row.find('.badge-gabon-success').length > 0;
                
                switch(filter) {
                    case 'all':
                        shouldShow = true;
                        row.show();
                        break;
                    case 'active':
                        if (isActive) {
                            shouldShow = true;
                            row.show();
                        } else {
                            row.hide();
                        }
                        break;
                    case 'inactive':
                        if (!isActive) {
                            shouldShow = true;
                            row.show();
                        } else {
                            row.hide();
                        }
                        break;
                    case 'recent':
                        // Logic for recent members (last 30 days)
                        shouldShow = true;
                        row.show();
                        break;
                }
            });
            
            if (shouldShow) {
                card.fadeIn();
            } else {
                card.fadeOut();
            }
        });
    }
    
    function searchMembers(searchTerm) {
        $('.org-card').each(function() {
            const card = $(this);
            let hasMatch = false;
            
            // Recherche dans le nom de l'organisation
            const orgName = card.find('.org-card-header h4').text().toLowerCase();
            if (orgName.includes(searchTerm)) {
                hasMatch = true;
            }
            
            // Recherche dans les adhérents
            card.find('tbody tr').each(function() {
                const row = $(this);
                const memberName = row.find('td:first .font-weight-bold').text().toLowerCase();
                const memberNip = row.find('td:nth-child(2)').text().toLowerCase();
                const memberPhone = row.find('td:nth-child(3)').text().toLowerCase();
                
                if (memberName.includes(searchTerm) || 
                    memberNip.includes(searchTerm) || 
                    memberPhone.includes(searchTerm)) {
                    hasMatch = true;
                    row.show();
                } else if (searchTerm !== '') {
                    row.hide();
                } else {
                    row.show();
                }
            });
            
            if (hasMatch || searchTerm === '') {
                card.fadeIn();
            } else {
                card.fadeOut();
            }
        });
    }
    
    // Animation au scroll
    $(window).scroll(function() {
        $('.animate-in').each(function() {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();
            
            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('visible');
            }
        });
    });
    
    // Gestion du bouton flottant
    $('.fab').hover(
        function() {
            $(this).find('i').removeClass('fa-plus').addClass('fa-magic');
        },
        function() {
            $(this).find('i').removeClass('fa-magic').addClass('fa-plus');
        }
    );
});
</script>
@endpush