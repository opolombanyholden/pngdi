@extends('layouts.operator')

@section('title', 'Mes Documents')

@section('page-title', 'Gestion des Documents')

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
    
    /* Couleurs spécifiques pour mise en évidence */
    .stats-card.gabon-green .metric-number {
        color: var(--gabon-yellow) !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .document-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--gabon-green);
    }
    
    .document-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        border-left-color: var(--gabon-yellow);
    }
    
    .document-card-header {
        background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%);
        border-radius: 15px 15px 0 0;
        padding: 1.2rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 2px solid var(--gabon-yellow);
    }
    
    .search-box {
        position: relative;
        margin-bottom: 1rem;
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
    
    .document-type-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .document-type-pdf {
        background: linear-gradient(135deg, var(--gabon-red) 0%, var(--gabon-red-dark) 100%);
        color: var(--text-contrast);
    }
    
    .document-type-image {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-green-dark) 100%);
        color: var(--text-contrast);
    }
    
    .document-type-word {
        background: linear-gradient(135deg, var(--gabon-blue) 0%, var(--gabon-blue-dark) 100%);
        color: var(--text-contrast);
    }
    
    .document-type-other {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: var(--text-contrast);
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
    
    .status-validated {
        background: var(--gabon-green);
        color: var(--text-contrast);
        border-color: var(--gabon-green-dark);
    }
    
    .status-pending {
        background: var(--gabon-yellow);
        color: var(--text-dark);
        border-color: var(--gabon-yellow-dark);
    }
    
    .status-rejected {
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
    
    .btn-outline-gabon.btn-red {
        color: var(--gabon-red);
        border-color: var(--gabon-red);
    }
    
    .btn-outline-gabon.btn-red:hover {
        background: var(--gabon-red);
        color: var(--text-contrast);
    }
    
    .document-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .file-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .file-icon.pdf {
        background: linear-gradient(135deg, var(--gabon-red) 0%, var(--gabon-red-dark) 100%);
        color: var(--text-contrast);
    }
    
    .file-icon.image {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-green-dark) 100%);
        color: var(--text-contrast);
    }
    
    .file-icon.word {
        background: linear-gradient(135deg, var(--gabon-blue) 0%, var(--gabon-blue-dark) 100%);
        color: var(--text-contrast);
    }
    
    .file-icon.other {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: var(--text-contrast);
    }
    
    .storage-progress {
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        height: 8px;
        margin: 1rem 0;
    }
    
    .storage-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--gabon-green), var(--gabon-yellow), var(--gabon-blue));
        border-radius: 10px;
        transition: width 1s ease;
        position: relative;
    }
    
    .storage-progress-bar::after {
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
    
    .dropdown-menu {
        border: 2px solid var(--gabon-yellow);
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }
    
    .dropdown-item {
        color: var(--text-dark);
        font-weight: 500;
        padding: 0.75rem 1.5rem;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
        color: var(--text-contrast);
    }
    
    .table-modern {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    
    .table-modern thead {
        background: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-blue) 100%);
    }
    
    .table-modern th {
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        color: var(--text-contrast);
        padding: 1rem 0.75rem;
    }
    
    .table-modern td {
        border: none;
        border-bottom: 1px solid #f1f3f4;
        padding: 1rem 0.75rem;
        vertical-align: middle;
        color: var(--text-dark);
    }
    
    .table-modern tbody tr:hover {
        background: linear-gradient(135deg, #e8f5e8 0%, #fff9e6 100%);
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
                        <div class="metric-number">{{ $totalDocuments }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Total Documents</div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-file-alt fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.2s;">
            <div class="stats-card gabon-yellow p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number">{{ $documentsValides }}</div>
                        <div class="text-uppercase font-weight-bold">Validés</div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-check-circle fa-2x" style="color: rgba(44, 62, 80, 0.8) !important;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.3s;">
            <div class="stats-card gabon-blue p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number text-white">{{ $documentsEnAttente }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">En Attente</div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-clock fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 animate-in" style="animation-delay: 0.4s;">
            <div class="stats-card gabon-red p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="metric-number text-white">{{ $documentsRejetes }}</div>
                        <div class="text-white-50 text-uppercase font-weight-bold">Rejetés</div>
                    </div>
                    <div class="ml-3">
                        <i class="fas fa-times-circle fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Espace de stockage -->
    <div class="row mb-4">
        <div class="col-12 animate-in" style="animation-delay: 0.5s;">
            <div class="filter-section">
                <h5 class="mb-3" style="color: var(--gabon-green); font-weight: 700;">
                    <i class="fas fa-hdd mr-2"></i>
                    Espace de Stockage
                </h5>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="storage-progress">
                            <div class="storage-progress-bar" style="width: {{ min($usedPercentage, 100) }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ number_format($totalSize / 1024 / 1024, 2) }} MB utilisés sur {{ number_format($maxSize / 1024 / 1024, 2) }} MB
                            ({{ $usedPercentage }}%)
                        </small>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="badge-modern status-{{ $usedPercentage > 80 ? 'rejected' : ($usedPercentage > 60 ? 'pending' : 'validated') }}">
                            {{ $usedPercentage > 80 ? 'Critique' : ($usedPercentage > 60 ? 'Attention' : 'Normal') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="filter-section animate-in" style="animation-delay: 0.6s;">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label class="form-label font-weight-bold" style="color: var(--gabon-green);">
                    <i class="fas fa-search mr-1"></i>Rechercher
                </label>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" class="form-control search-input" 
                           placeholder="Nom du document..." value="{{ request('search') }}">
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
                    <i class="fas fa-tag mr-1"></i>Type
                </label>
                <select name="type" class="form-control filter-select">
                    <option value="">Tous types</option>
                    @foreach($documentTypes as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                            {{ $type->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label font-weight-bold" style="color: var(--gabon-green);">
                    <i class="fas fa-flag mr-1"></i>Statut
                </label>
                <select name="status" class="form-control filter-select">
                    <option value="">Tous statuts</option>
                    <option value="validated" {{ request('status') == 'validated' ? 'selected' : '' }}>Validés</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetés</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-gabon-primary w-100">
                    <i class="fas fa-filter mr-1"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des documents -->
    @if($documents->count() > 0)
        @foreach($documents as $index => $document)
            <div class="document-card animate-in" style="animation-delay: {{ 0.7 + ($index * 0.1) }}s;">
                <div class="document-card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <!-- Icône du fichier -->
                                <div class="file-icon pdf">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                
                                <!-- Informations du document -->
                                <div>
                                    <h5 class="mb-1 font-weight-bold" style="color: var(--gabon-green);">
                                        {{ $document->nom_original }}
                                    </h5>
                                    <div class="mb-2">
                                        <span class="document-type-badge document-type-pdf">
                                            {{ $document->documentType->libelle ?? pathinfo($document->nom_original, PATHINFO_EXTENSION) ?: 'Document' }}
                                        </span>
                                        <span class="status-badge status-{{ $document->is_validated === true ? 'validated' : ($document->is_validated === false ? 'rejected' : 'pending') }} ml-2">
                                            <i class="fas fa-{{ $document->is_validated === true ? 'check' : ($document->is_validated === false ? 'times' : 'clock') }} mr-1"></i>
                                            {{ $document->is_validated === true ? 'Validé' : ($document->is_validated === false ? 'Rejeté' : 'En attente') }}
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ $document->dossier->organisation->nom }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $document->created_at->format('d/m/Y à H:i') }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-weight mr-1"></i>
                                        {{ number_format($document->taille / 1024, 2) }} KB
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-right">
                            <div class="document-actions">
                                @if($document->is_image || $document->is_pdf)
                                    <a href="{{ route('operator.files.preview', $document) }}" 
                                       class="btn btn-outline-gabon btn-sm" target="_blank" 
                                       data-toggle="tooltip" title="Prévisualiser">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                
                                <a href="{{ route('operator.files.download', $document) }}" 
                                   class="btn btn-outline-gabon btn-yellow btn-sm"
                                   data-toggle="tooltip" title="Télécharger">
                                    <i class="fas fa-download"></i>
                                </a>
                                
                                @if($document->dossier->canBeModified())
                                    <button type="button" class="btn btn-outline-gabon btn-sm"
                                            data-toggle="modal" data-target="#replaceModal{{ $document->id }}"
                                            data-toggle="tooltip" title="Remplacer">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-gabon btn-red btn-sm"
                                            onclick="confirmDelete({{ $document->id }})"
                                            data-toggle="tooltip" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Commentaire de validation si rejeté -->
                    @if($document->is_validated === false && $document->validation_comment)
                        <div class="alert alert-danger mt-3 mb-0">
                            <strong><i class="fas fa-exclamation-triangle mr-1"></i>Motif du rejet :</strong>
                            {{ $document->validation_comment }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $documents->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state animate-in" style="animation-delay: 0.7s;">
            <div class="empty-state-icon">
                <i class="fas fa-file-plus"></i>
            </div>
            <h3 class="text-muted mb-3">Aucun document trouvé</h3>
            <p class="text-muted mb-4">
                @if(request()->hasAny(['search', 'organisation', 'type', 'status']))
                    Aucun document ne correspond à vos critères de recherche.
                @else
                    Vous n'avez pas encore téléchargé de documents.
                @endif
            </p>
            <a href="{{ route('operator.dossiers.index') }}" class="btn btn-gabon-primary btn-lg">
                <i class="fas fa-plus mr-2"></i>Créer mon premier dossier
            </a>
        </div>
    @endif
</div>

<!-- Bouton d'action flottant -->
<div class="floating-action">
    <button class="fab" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-plus"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="{{ route('operator.dossiers.create', 'association') }}">
            <i class="fas fa-folder-plus mr-2"></i>Nouveau dossier
        </a>
        <a class="dropdown-item" href="{{ route('operator.members.import.template') }}">
            <i class="fas fa-file-import mr-2"></i>Importer des documents
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('operator.help.documents-types') }}">
            <i class="fas fa-question-circle mr-2"></i>Types de documents
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
});

// Fonction de confirmation de suppression
function confirmDelete(documentId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.')) {
        // Créer un formulaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/operator/files/${documentId}`;
        
        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        // Ajouter la méthode DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush