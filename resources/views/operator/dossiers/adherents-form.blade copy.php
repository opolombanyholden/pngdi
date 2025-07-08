@extends('layouts.operator')

@section('title', 'Gestion des Adhérents')
@section('page-title', 'Ajout des Adhérents')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/organisation-create.css') }}">

    <!-- CSS Styles pour l'interface adhérents modernisée -->
<style>
/* ===== STYLES ÉTAPE 7 MODERNISÉE ===== */

.alert-gradient {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
    border-left: 4px solid #0d6efd;
}

.alert-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 50%;
    flex-shrink: 0;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
}

.mode-card {
    transition: all 0.3s ease;
}

.mode-option {
    transition: all 0.3s ease;
    cursor: pointer;
    background: #f8f9fa;
}

.mode-option:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.mode-card input:checked + label .mode-option {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(102, 16, 242, 0.05) 100%);
    border-color: #0d6efd !important;
    border-width: 2px;
}

.mode-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    flex-shrink: 0;
}

.upload-zone {
    transition: all 0.3s ease;
    cursor: pointer;
    background: rgba(25, 135, 84, 0.02);
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.upload-zone:hover {
    background: rgba(25, 135, 84, 0.05);
    border-color: #198754 !important;
    transform: scale(1.02);
}

.upload-zone.dragover {
    background: rgba(25, 135, 84, 0.1);
    border-color: #198754 !important;
    border-style: solid !important;
    transform: scale(1.05);
}

.upload-icon {
    transition: all 0.3s ease;
}

.upload-zone:hover .upload-icon {
    transform: scale(1.1);
}

.border-dashed {
    border-style: dashed !important;
}

.table-controls {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05) !important;
}

.sortable {
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}

.sortable:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.selected-actions {
    animation: slideInUp 0.3s ease;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { 
        opacity: 0; 
        transform: translateY(10px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* Progress Modal Styles */
.modal-content {
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
}

/* Badge styles */
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-controls .row > div {
        margin-bottom: 0.5rem;
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    
    .upload-zone {
        min-height: 150px;
        padding: 1rem;
    }
    
    .mode-option {
        text-align: center;
    }
    
    .d-flex.gap-3 {
        gap: 0.5rem !important;
        flex-wrap: wrap;
    }
}

/* Dark theme support */
@media (prefers-color-scheme: dark) {
    .table-controls {
        background: #212529;
        color: #fff;
    }
    
    .mode-option {
        background: #343a40;
        color: #fff;
    }
    
    .mode-option:hover {
        background: #495057;
    }
    
    .upload-zone {
        background: rgba(25, 135, 84, 0.1);
        color: #fff;
    }
}

/* Animation pour les éléments chargés dynamiquement */
.table-responsive {
    animation: fadeIn 0.4s ease;
}

/* Styles pour les anomalies */
.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.table-warning:hover {
    background-color: rgba(255, 193, 7, 0.2) !important;
}

/* Custom checkbox styles */
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Tooltip styles */
[title] {
    cursor: help;
}

/* Loading states */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

/* Card hover effects */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* Button loading states */
.btn.loading {
    pointer-events: none;
}

.btn.loading::after {
    content: '';
    display: inline-block;
    width: 1rem;
    height: 1rem;
    margin-left: 0.5rem;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@section('content')
<!-- ========== SECTION A - HEADER ET NAVIGATION ========== -->
<div class="container-fluid">
    <!-- Header avec navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <nav aria-label="breadcrumb" class="mb-2">
                                <ol class="breadcrumb text-white-50 mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('operator.dashboard') }}" class="text-white opacity-75">
                                            <i class="fas fa-home me-1"></i>Dashboard
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('operator.organisations.index') }}" class="text-white opacity-75">Organisations</a>
                                    </li>
                                    @if(isset($organisation))
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('operator.organisations.show', $organisation) }}" class="text-white opacity-75">{{ $organisation->nom }}</a>
                                    </li>
                                    @endif
                                    <li class="breadcrumb-item active text-white">Gestion des Adhérents</li>
                                </ol>
                            </nav>
                            <h2 class="mb-2">
                                <i class="fas fa-user-plus me-2"></i>
                                Gestion des Adhérents
                            </h2>
                            <p class="mb-0 opacity-90">
                                @if(isset($organisation))
                                    Organisation : <strong>{{ $organisation->nom }}</strong>
                                @else
                                    Ajout et gestion des membres adhérents
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                @if(isset($organisation))
                                    <a href="{{ route('operator.organisations.show', $organisation) }}" class="btn btn-light">
                                        <i class="fas fa-arrow-left me-2"></i>Retour
                                    </a>
                                @else
                                    <a href="{{ route('operator.organisations.index') }}" class="btn btn-light">
                                        <i class="fas fa-arrow-left me-2"></i>Retour
                                    </a>
                                @endif
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#helpModal">
                                    <i class="fas fa-question-circle me-2"></i>Aide
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateur de statut -->
    <div class="row mb-2">
        <div class="col-12 text-end">
            <small id="save-indicator" class="text-muted"></small>
        </div>
    </div>

    <!-- Contenu principal du formulaire adhérents -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="adherentsForm" action="{{ route('operator.organisations.store-adherents') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        @if(isset($organisation))
                            <input type="hidden" name="organisation_id" value="{{ $organisation->id }}">
                        @endif
                        
                        @if(isset($dossier))
                            <input type="hidden" name="dossier_id" value="{{ $dossier->id }}">
                        @endif

                        <!-- ========================================================================
                             INTERFACE MODERNISÉE GESTION ADHÉRENTS
                             Version: 2.0 - UX Optimisée avec Progress et Tableau Interactif
                             ======================================================================== -->

                        <div class="text-center mb-4">
                            <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);">
                                <i class="fas fa-user-plus fa-3x text-white"></i>
                            </div>
                            <h3 class="text-primary">Adhérents de l'organisation</h3>
                            <p class="text-muted">Gérez les données des adhérents de votre organisation</p>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-11">
                                <!-- Alerte d'exigences avec design moderne -->
                                <div class="alert alert-gradient border-0 mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="alert-icon me-3">
                                            <i class="fas fa-info-circle fa-2x text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-users me-2"></i>
                                                Exigences d'adhésion
                                            </h6>
                                            <div id="adherents_requirements" class="mb-2">
                                                <p class="mb-0">
                                                    <strong>Minimum requis :</strong> <span id="min_adherents" class="text-primary">10</span> adhérents
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-lightbulb me-1"></i>
                                                        Vous pouvez ajouter des adhérents manuellement ou importer un fichier Excel/CSV.
                                                    </small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modes d'ajout avec design moderne -->
                                <div class="card border-0 shadow-lg mb-4">
                                    <div class="card-header bg-gradient-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-cog me-2"></i>
                                            Mode d'ajout des adhérents
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="mode-card" data-mode="manuel">
                                                    <input class="form-check-input d-none" type="radio" name="adherent_mode" id="mode_manuel" value="manuel" checked>
                                                    <label class="form-check-label w-100" for="mode_manuel">
                                                        <div class="d-flex align-items-center p-3 border rounded-3 mode-option">
                                                            <div class="mode-icon me-3">
                                                                <i class="fas fa-keyboard fa-2x text-primary"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1">Saisie manuelle</h6>
                                                                <small class="text-muted">Ajouter un par un</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mode-card" data-mode="fichier">
                                                    <input class="form-check-input d-none" type="radio" name="adherent_mode" id="mode_fichier" value="fichier">
                                                    <label class="form-check-label w-100" for="mode_fichier">
                                                        <div class="d-flex align-items-center p-3 border rounded-3 mode-option">
                                                            <div class="mode-icon me-3">
                                                                <i class="fas fa-file-excel fa-2x text-success"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1">Import fichier Excel/CSV</h6>
                                                                <small class="text-muted">Charger depuis un fichier</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section saisie manuelle optimisée -->
                                <div id="adherent_manuel_section">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-user-plus me-2"></i>
                                                Ajouter un adhérent manuellement
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-2">
                                                    <label for="adherent_civilite" class="form-label fw-bold">Civilité</label>
                                                    <select class="form-select" id="adherent_civilite">
                                                        <option value="M">M.</option>
                                                        <option value="Mme">Mme</option>
                                                        <option value="Mlle">Mlle</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="adherent_nom" class="form-label fw-bold">Nom *</label>
                                                    <input type="text" class="form-control" id="adherent_nom" placeholder="Nom de famille" required>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="adherent_prenom" class="form-label fw-bold">Prénom *</label>
                                                    <input type="text" class="form-control" id="adherent_prenom" placeholder="Prénom(s)" required>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="adherent_nip" class="form-label fw-bold">NIP *</label>
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="adherent_nip" 
                                                               data-validate="nip"
                                                               placeholder="A1-2345-19901225" 
                                                               maxlength="16"
                                                               pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}"
                                                               required>
                                                        <button class="btn btn-outline-info" type="button" title="Aide format NIP">
                                                            <i class="fas fa-question-circle"></i>
                                                        </button>
                                                    </div>
                                                    <small class="form-text text-muted">Format: XX-QQQQ-YYYYMMDD</small>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="adherent_telephone" class="form-label fw-bold">Téléphone</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+241</span>
                                                        <input type="tel" class="form-control" id="adherent_telephone" placeholder="01234567">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="adherent_profession" class="form-label fw-bold">Profession</label>
                                                    <input type="text" class="form-control" id="adherent_profession" placeholder="Profession">
                                                </div>

                                                <div class="col-12">
                                                    <button type="button" class="btn btn-info btn-lg" id="addAdherentBtn">
                                                        <i class="fas fa-plus me-2"></i>Ajouter cet adhérent
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section import fichier modernisée -->
                                <div id="adherent_fichier_section" class="d-none">
                                    <div class="card border-0 shadow-lg mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-file-upload me-2"></i>
                                                Import depuis un fichier Excel/CSV
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Zone de drop modernisée -->
                                            <div class="upload-zone border-dashed border-2 border-success rounded-3 p-4 mb-4 text-center" id="file-drop-zone">
                                                <div class="upload-icon mb-3">
                                                    <i class="fas fa-cloud-upload-alt fa-4x text-success"></i>
                                                </div>
                                                <h5 class="text-success mb-2">Glissez-déposez votre fichier ici</h5>
                                                <p class="text-muted mb-3">
                                                    ou cliquez pour sélectionner un fichier
                                                    <br>
                                                    <small>
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Formats supportés: .xlsx, .xls, .csv | Taille max: 10MB
                                                    </small>
                                                </p>
                                                
                                                <button type="button" class="btn btn-success btn-lg" id="select-file-btn-manual">
                                                    <i class="fas fa-file-excel me-2"></i>
                                                    Sélectionner un fichier
                                                </button>
                                                
                                                <input type="file" class="d-none" id="adherents_file" accept=".xlsx,.xls,.csv">
                                            </div>

                                            <!-- Instructions et template -->
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <div class="alert alert-info border-0">
                                                        <h6 class="alert-heading">
                                                            <i class="fas fa-list-ul me-2"></i>
                                                            Colonnes requises dans votre fichier
                                                        </h6>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <ul class="list-unstyled mb-0">
                                                                    <li><i class="fas fa-check text-success me-1"></i> Civilité</li>
                                                                    <li><i class="fas fa-check text-success me-1"></i> Nom</li>
                                                                    <li><i class="fas fa-check text-success me-1"></i> Prénom</li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-6">
                                                                <ul class="list-unstyled mb-0">
                                                                    <li><i class="fas fa-check text-success me-1"></i> NIP</li>
                                                                    <li><i class="fas fa-check text-info me-1"></i> Téléphone</li>
                                                                    <li><i class="fas fa-check text-info me-1"></i> Profession</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-info me-1"></i>
                                                            Les colonnes avec <i class="fas fa-check text-success"></i> sont obligatoires.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center">
                                                        <h6>Besoin d'un modèle ?</h6>
                                                        <a href="#" class="btn btn-outline-success btn-lg" id="downloadTemplateBtn">
                                                            <i class="fas fa-download me-2"></i>
                                                            Télécharger le modèle
                                                        </a>
                                                        <small class="d-block text-muted mt-2">
                                                            Fichier Excel prêt à remplir
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Liste des adhérents avec interface moderne -->
                                <div class="card border-0 shadow-lg">
                                    <div class="card-header bg-dark text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-list me-2"></i>
                                                Liste des adhérents
                                            </h6>
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="badge bg-light text-dark fs-6" id="adherents_count">0 adhérent(s)</span>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-light btn-sm" onclick="exportAdherentsCSV()">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-light btn-sm" onclick="addAdherentManually()">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="adherents_list">
                                            <!-- Contenu généré dynamiquement par le JavaScript -->
                                            <div class="text-center py-5">
                                                <div class="mb-4">
                                                    <i class="fas fa-user-plus fa-4x text-muted"></i>
                                                </div>
                                                <h5 class="text-muted">Aucun adhérent ajouté</h5>
                                                <p class="text-muted mb-4">
                                                    Commencez par importer un fichier Excel/CSV ou ajoutez des adhérents manuellement.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Zone de détails d'import (cachée par défaut) -->
                                <div id="import_details" class="d-none">
                                    <!-- Contenu généré dynamiquement lors de l'import -->
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Boutons d'action -->
                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between">
                        @if(isset($organisation))
                            <a href="{{ route('operator.organisations.show', $organisation) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour à l'organisation
                            </a>
                        @else
                            <a href="{{ route('operator.organisations.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                        @endif
                        
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning" id="saveProgressBtn">
                                <i class="fas fa-save me-2"></i>Sauvegarder les modifications
                            </button>
                            <button type="submit" class="btn btn-primary" id="finalizeAdherentsBtn">
                                <i class="fas fa-check-circle me-2"></i>Finaliser les adhérents
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loader global -->
<div id="global-loader" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index: 9999;">
    <div class="d-flex align-items-center justify-content-center h-100">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>
</div>

<!-- Modal d'aide -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>
                    Aide - Gestion des adhérents
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Comment ajouter des adhérents ?</h6>
                <ul>
                    <li><strong>Saisie manuelle :</strong> Ajoutez un adhérent à la fois en remplissant le formulaire</li>
                    <li><strong>Import de fichier :</strong> Importez un fichier Excel ou CSV contenant tous vos adhérents</li>
                    <li><strong>Modèle Excel :</strong> Téléchargez notre modèle pour faciliter la préparation de vos données</li>
                </ul>
                
                <h6 class="mt-4">Informations requises :</h6>
                <ul>
                    <li>Civilité, Nom et Prénom (obligatoires)</li>
                    <li>NIP au format XX-QQQQ-YYYYMMDD (obligatoire)</li>
                    <li>Téléphone et Profession (optionnels)</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'aide NIP -->
<div class="modal fade" id="nipHelpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-hashtag me-2"></i>
                    Aide - Format NIP Gabonais
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Nouveau format NIP : XX-QQQQ-YYYYMMDD</h6>
                <div class="alert alert-info">
                    <h6 class="alert-heading">Structure du NIP :</h6>
                    <ul class="mb-0">
                        <li><strong>XX</strong> : 2 caractères alphanumériques (A-Z, 0-9)</li>
                        <li><strong>QQQQ</strong> : 4 chiffres de séquence</li>
                        <li><strong>YYYYMMDD</strong> : Date de naissance (Année-Mois-Jour)</li>
                    </ul>
                </div>
                <h6>Exemples valides :</h6>
                <div class="bg-light p-3 rounded">
                    <code>A1-2345-19901225</code> → Né le 25/12/1990<br>
                    <code>B2-0001-20000115</code> → Né le 15/01/2000<br>
                    <code>C3-9999-19850630</code> → Né le 30/06/1985
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-info" onclick="showNipExample()">
                        <i class="fas fa-magic me-2"></i>Générer un exemple
                    </button>
                </div>
                <div id="nipExampleResult" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Configuration Laravel pour JavaScript -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="app-url" content="{{ config('app.url') }}">
<meta name="user-id" content="{{ auth()->id() }}">

@if(isset($dossier) && $dossier)
    <meta name="dossier-id" content="{{ $dossier->id }}">
@endif

@if(isset($organisation) && $organisation)
    <meta name="organisation-id" content="{{ $organisation->id }}">
@endif

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- Configuration globale pour les adhérents -->
    <script>
        window.AdherentsConfig = {
            enabled: true,
            maxAdherents: 10000,
            autoSaveInterval: 30000, // 30 secondes
            routes: {
                save: '{{ route("operator.organisations.save-adherents") }}',
                validate: '{{ route("operator.organisations.validate-adherents") }}',
                template: '{{ route("operator.organisations.download-adherents-template") }}'
            }
        };

        console.log('🔧 Configuration adhérents chargée:', window.AdherentsConfig);
    </script>

    <!-- Validation NIP format XX-QQQQ-YYYYMMDD -->
    <script src="{{ asset('js/nip-validation.js') }}"></script>

    <!-- Scripts principaux -->
    <script src="{{ asset('js/csrf-manager.js') }}"></script>
    
    <!-- Système de chunking pour gros volumes -->
    <script src="{{ asset('js/chunking-import.js') }}"></script>
    
    <!-- Script dédié aux adhérents -->
    <script src="{{ asset('js/adherents-manager.js') }}"></script>
    
    <!-- Scripts NIP Validation -->
    <script>
        function showNipExample() {
            if (window.NipValidation) {
                const example = window.NipValidation.generateExample();
                const validation = window.NipValidation.validateFormat(example);
                const resultDiv = document.getElementById('nipExampleResult');
                
                if (validation.valid && validation.extracted_info) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>Exemple généré :</strong> <code>${example}</code><br>
                            <small>Âge calculé : ${validation.extracted_info.age} ans</small>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-info">
                            <strong>Exemple généré :</strong> <code>${example}</code>
                        </div>
                    `;
                }
            }
        }

        // Ajouter bouton d'aide NIP dans les champs
        document.addEventListener('DOMContentLoaded', function() {
            const nipInputs = document.querySelectorAll('input[data-validate="nip"]');
            nipInputs.forEach(function(input) {
                const container = input.closest('.input-group');
                if (container && !container.querySelector('.btn-help-nip')) {
                    const helpBtn = document.createElement('button');
                    helpBtn.type = 'button';
                    helpBtn.className = 'btn btn-outline-info btn-help-nip';
                    helpBtn.innerHTML = '<i class="fas fa-question-circle"></i>';
                    helpBtn.title = 'Aide format NIP';
                    helpBtn.onclick = function() {
                        const modal = new bootstrap.Modal(document.getElementById('nipHelpModal'));
                        modal.show();
                    };
                    
                    // Ajouter après l'input-group-text existant
                    const inputGroupText = container.querySelector('.input-group-text');
                    if (inputGroupText) {
                        container.insertBefore(helpBtn, inputGroupText.nextSibling);
                    } else {
                        container.appendChild(helpBtn);
                    }
                }
            });

            // Initialiser les fonctionnalités d'adhérents
            if (window.AdherentsManager) {
                window.AdherentsManager.init();
            }
        });

        // Fonctions utilitaires pour l'export et l'ajout manuel
        function exportAdherentsCSV() {
            if (window.AdherentsManager && window.AdherentsManager.exportToCSV) {
                window.AdherentsManager.exportToCSV();
            } else {
                console.warn('AdherentsManager non initialisé');
            }
        }

        function addAdherentManually() {
            const modeManuel = document.getElementById('mode_manuel');
            if (modeManuel) {
                modeManuel.checked = true;
                // Déclencher l'événement de changement de mode
                modeManuel.dispatchEvent(new Event('change'));
            }
            
            // Scroll vers le formulaire d'ajout
            const formSection = document.getElementById('adherent_manuel_section');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>
@endpush