@extends('layouts.operator')

@section('title', 'Déclarations Annuelles')

@section('page-title', 'Gestion des Déclarations Annuelles')

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
        --gabon-red: #8b1538;
        --gabon-red-dark: #6d1029;
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
        background: linear-gradient(135deg, var(--gabon-red) 0%, var(--gabon-red-dark) 100%);
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
    
    /* Couleurs spécifiques pour la carte jaune */
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
    
    /* Couleurs spécifiques pour mise en évidence */
    .stats-card.gabon-green .metric-number {
        color: var(--gabon-yellow) !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .declaration-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--gabon-green);
    }
    
    .declaration-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        border-left-color: var(--gabon-yellow);
    }
    
    .declaration-card.urgent {
        border-left-color: var(--gabon-red);
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
    }
    
    .declaration-card.completed {
        border-left-color: var(--gabon-green);
        background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
    }
    
    .declaration-card-header {
        background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%);
        border-radius: 15px 15px 0 0;
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .echeance-card {
        border: 2px solid var(--gabon-yellow);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
        transition: all 0.3s ease;
    }
    
    .echeance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 205, 0, 0.2);
    }
    
    .echeance-card.urgent {
        border-color: var(--gabon-red);
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
    }
    
    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: 2px solid transparent;
    }
    
    .status-en-cours {
        background: var(--gabon-yellow);
        color: var(--text-dark);
        border-color: var(--gabon-yellow-dark);
    }
    
    .status-soumise {
        background: var(--gabon-blue);
        color: var(--text-contrast);
        border-color: var(--gabon-blue-dark);
    }
    
    .status-validee {
        background: var(--gabon-green);
        color: var(--text-contrast);
        border-color: var(--gabon-green-dark);
    }
    
    .status-rejetee {
        background: var(--gabon-red);
        color: var(--text-contrast);
        border-color: var(--gabon-red-dark);
    }
    
    .btn-gabon-primary {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        color: var(--text-contrast);
        border: 2px solid var(--gabon-green);
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    
    .btn-gabon-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 158, 63, 0.3);
        color: var(--text-contrast);
    }
    
    .btn-outline-gabon {
        background: transparent;
        color: var(--gabon-green);
        border: 2px solid var(--gabon-green);
        border-radius: 50px;
        padding: 0.4rem 1.2rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    
    .btn-outline-gabon:hover {
        background: var(--gabon-green);
        color: var(--text-contrast);
        transform: translateY(-2px);
    }
    
    .btn-outline-gabon.btn-yellow {
        color: var(--gabon-yellow-dark);
        border-color: var(--gabon-yellow);
    }
    
    .btn-outline-gabon.btn-yellow:hover {
        background: var(--gabon-yellow);
        color: var(--text-dark);
    }
    
    .btn-outline-gabon.btn-blue {
        color: var(--gabon-blue);
        border-color: var(--gabon-blue);
    }
    
    .btn-outline-gabon.btn-blue:hover {
        background: var(--gabon-blue);
        color: var(--text-contrast);
    }
    
    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 2px solid var(--gabon-yellow);
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
    
    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 1.1rem;
    }
    
    .filter-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }
    
    .filter-select:focus {
        border-color: var(--gabon-green);
        box-shadow: 0 0 0 0.1rem rgba(0, 158, 63, 0.25);
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
    
    .deadline-indicator {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .deadline-danger {
        background: var(--gabon-red);
        color: var(--text-contrast);
    }
    
    .deadline-warning {
        background: var(--gabon-yellow);
        color: var(--text-dark);
    }
    
    .deadline-info {
        background: var(--gabon-green);
        color: var(--text-contrast);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%);
        border-radius: 20px;
        border: 2px dashed var(--gabon-green);
        margin: 2rem 0;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: var(--gabon-green);
        margin-bottom: 1.5rem;
        opacity: 0.8;
    }
    
    .metric-number {
        font-size: 2.5rem;
        font-weight: 700;
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
    
    .progress-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .progress-timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--gabon-green);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.8rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--gabon-yellow);
        border: 2px solid var(--gabon-green);
    }
    
    .timeline-item.completed::before {
        background: var(--gabon-green);
    }
    
    .timeline-item.current::before {
        background: var(--gabon-blue);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistiques modernes avec couleurs gabonaises -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.1s;">
            <div class="stats-card gabon-green p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number">{{ $totalDeclarations }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Total Déclarations</div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-file-contract fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.2s;">
            <div class="stats-card gabon-yellow p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number">{{ $declarationsEnCours }}</div>
                        <div class="text-uppercase font-weight-bold">En Cours</div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-edit fa-2x" style="color: rgba(44, 62, 80, 0.8) !important;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.3s;">
            <div class="stats-card gabon-blue p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number text-white">{{ $declarationsSoumises }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Soumises</div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-paper-plane fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.4s;">
            <div class="stats-card gabon-red p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number text-white">{{ $declarationsValidees }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Validées</div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prochaines échéances -->
    @if($prochainesEcheances && $prochainesEcheances->count() > 0)
        <div class="row mb-4">
            <div class="col-12 animate-in" style="animation-delay: 0.5s;">
                <div class="filter-section">
                    <h5 class="mb-3" style="color: var(--gabon-red); font-weight: 700;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Prochaines Échéances
                    </h5>
                    
                    @foreach($prochainesEcheances->take(3) as $echeance)
                        <div class="echeance-card {{ $echeance['jours_restants'] <= 30 ? 'urgent' : '' }}">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1 font-weight-bold" style="color: var(--gabon-green);">
                                        {{ $echeance['organisation']->nom }}
                                    </h6>
                                    <p class="mb-0 text-muted">
                                        Déclaration {{ $echeance['annee'] }}
                                        @if($echeance['statut'] === 'non_commence')
                                            - <span class="text-warning">Non commencée</span>
                                        @else
                                            - <span class="text-info">En cours</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="deadline-indicator {{ $echeance['jours_restants'] <= 7 ? 'deadline-danger' : ($echeance['jours_restants'] <= 30 ? 'deadline-warning' : 'deadline-info') }}">
                                        {{ $echeance['jours_restants'] }} jour(s)
                                    </div>
                                    <small class="text-muted d-block">
                                        Limite: {{ $echeance['date_limite']->format('d/m/Y') }}
                                    </small>
                                </div>
                                <div class="col-md-3 text-right">
                                    @if($echeance['statut'] === 'non_commence')
                                        <a href="{{ route('operator.declarations.create', $echeance['organisation']) }}" 
                                           class="btn btn-gabon-primary btn-sm">
                                            <i class="fas fa-plus mr-1"></i>Commencer
                                        </a>
                                    @else
                                        <a href="{{ route('operator.declarations.show', $echeance['declaration']) }}" 
                                           class="btn btn-outline-gabon btn-blue btn-sm">
                                            <i class="fas fa-edit mr-1"></i>Continuer
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Filtres et recherche -->
    <div class="filter-section animate-in" style="animation-delay: 0.6s;">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label font-weight-bold" style="color: var(--gabon-green);">
                    <i class="fas fa-search mr-1"></i>Rechercher
                </label>
                <div class="position-relative">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" class="form-control search-input" 
                           placeholder="Titre, description..." value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="col-md-2">
                <label class="form-label font-weight-bold" style="color: var(--gabon-green);">
                    <i class="fas fa-building mr-1"></i>Organisation
                </label>
                <select name="organisation" class="form-control filter-select">
                    <option value="">Toutes</option>
                    @foreach($organisations as $org)
                        <option value="{{ $org->id }}" {{ request('organisation') == $org->id ? 'selected' : '' }}>
                            {{ $org->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label font-weight-bold" style="color: var(--gabon-green);">
                    <i class="fas fa-calendar mr-1"></i>Année
                </label>
                <select name="annee" class="form-control filter-select">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee }}" {{ request('annee') == $annee ? 'selected' : '' }}>
                            {{ $annee }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label font-weight-bold" style="color: var(--gabon-green);">
                    <i class="fas fa-flag mr-1"></i>Statut
                </label>
                <select name="statut" class="form-control filter-select">
                    <option value="">Tous statuts</option>
                    <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="soumise" {{ request('statut') == 'soumise' ? 'selected' : '' }}>Soumises</option>
                    <option value="validee" {{ request('statut') == 'validee' ? 'selected' : '' }}>Validées</option>
                    <option value="rejetee" {{ request('statut') == 'rejetee' ? 'selected' : '' }}>Rejetées</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-gabon-primary">
                    <i class="fas fa-filter mr-1"></i>Filtrer
                </button>
                <a href="{{ route('operator.declarations.index') }}" class="btn btn-outline-secondary ml-2">
                    <i class="fas fa-times mr-1"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Liste des déclarations -->
    @if($declarations->count() > 0)
        @foreach($declarations as $index => $declaration)
            <div class="declaration-card animate-in {{ $declaration->statut === 'validee' ? 'completed' : '' }}" 
                 style="animation-delay: {{ 0.7 + ($index * 0.1) }}s;">
                <div class="declaration-card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <!-- Icône de la déclaration -->
                                <div class="mr-3">
                                    <div class="d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);">
                                        <i class="fas fa-file-contract fa-lg text-white"></i>
                                    </div>
                                </div>
                                
                                <!-- Informations de la déclaration -->
                                <div>
                                    <h5 class="mb-1 font-weight-bold" style="color: var(--gabon-green);">
                                        {{ $declaration->titre ?? 'Déclaration ' . $declaration->annee }}
                                    </h5>
                                    <div class="mb-2">
                                        <span class="status-badge status-{{ $declaration->statut }}">
                                            <i class="fas fa-{{ $declaration->statut === 'en_cours' ? 'edit' : ($declaration->statut === 'soumise' ? 'paper-plane' : ($declaration->statut === 'validee' ? 'check' : 'times')) }} mr-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $declaration->statut)) }}
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ $declaration->organisation->nom }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        Année {{ $declaration->annee }}
                                        @if($declaration->date_limite)
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock mr-1"></i>
                                            Limite: {{ $declaration->date_limite->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-right">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('operator.declarations.show', $declaration) }}" 
                                   class="btn btn-outline-gabon btn-sm"
                                   data-toggle="tooltip" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($declaration->statut === 'en_cours')
                                    <a href="{{ route('operator.declarations.edit', $declaration) }}" 
                                       class="btn btn-outline-gabon btn-yellow btn-sm"
                                       data-toggle="tooltip" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('operator.declarations.soumettre', $declaration) }}" 
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-gabon btn-blue btn-sm"
                                                data-toggle="tooltip" title="Soumettre"
                                                onclick="return confirm('Confirmer la soumission de cette déclaration ?')">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($declaration->description)
                        <div class="mt-3">
                            <p class="text-muted mb-0">{{ $declaration->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $declarations->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state animate-in" style="animation-delay: 0.7s;">
            <div class="empty-state-icon">
                <i class="fas fa-file-plus"></i>
            </div>
            <h3 class="text-muted mb-3">Aucune déclaration trouvée</h3>
            <p class="text-muted mb-4">
                @if(request()->hasAny(['search', 'organisation', 'annee', 'statut']))
                    Aucune déclaration ne correspond à vos critères de recherche.
                @else
                    Vous n'avez pas encore créé de déclarations annuelles.
                @endif
            </p>
            @if($organisations->count() > 0)
                <a href="{{ route('operator.declarations.create', $organisations->first()) }}" 
                   class="btn btn-gabon-primary btn-lg">
                    <i class="fas fa-plus mr-2"></i>Créer ma première déclaration
                </a>
            @else
                <a href="{{ route('operator.dossiers.create', 'association') }}" 
                   class="btn btn-gabon-primary btn-lg">
                    <i class="fas fa-building mr-2"></i>Créer une organisation d'abord
                </a>
            @endif
        </div>
    @endif
</div>

<!-- Bouton d'action flottant -->
<div class="floating-action">
    <button class="fab" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-plus"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        @if($organisations->count() > 0)
            @foreach($organisations as $org)
                <a class="dropdown-item" href="{{ route('operator.declarations.create', $org) }}">
                    <i class="fas fa-file-plus mr-2"></i>Déclaration {{ $org->nom }}
                </a>
            @endforeach
            <div class="dropdown-divider"></div>
        @endif
        <a class="dropdown-item" href="{{ route('operator.help.guide.declaration', $organisations->first() ?? 0) }}">
            <i class="fas fa-question-circle mr-2"></i>Guide des déclarations
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
    
    // Gestion du bouton flottant
    $('.fab').hover(
        function() {
            $(this).find('i').removeClass('fa-plus').addClass('fa-magic');
        },
        function() {
            $(this).find('i').removeClass('fa-magic').addClass('fa-plus');
        }
    );
    
    // Alertes pour les échéances urgentes
    $('.echeance-card.urgent').each(function() {
        $(this).prepend('<div class="alert alert-warning alert-dismissible fade show mb-2"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Échéance proche !</strong> Pensez à finaliser cette déclaration rapidement.<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>');
    });
});
</script>
@endpush