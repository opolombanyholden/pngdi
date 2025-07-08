@extends('layouts.operator')

@section('title', 'Créer une Organisation')
@section('page-title', 'Nouvelle Organisation')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/organisation-create.css') }}">

    <!-- CSS Styles intégrés pour l'interface modernisée -->
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
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
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
                                    <li class="breadcrumb-item active text-white">Nouvelle Organisation</li>
                                </ol>
                            </nav>
                            <h2 class="mb-2">
                                <i class="fas fa-building me-2"></i>
                                Création d'une Nouvelle Organisation
                            </h2>
                            <p class="mb-0 opacity-90">Assistant de création guidée en <span id="totalSteps">9</span> étapes</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('operator.organisations.index') }}" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </a>
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

    <!-- Indicateur de sauvegarde -->
    <div class="row mb-2">
        <div class="col-12 text-end">
            <small id="save-indicator" class="text-muted"></small>
        </div>
    </div>

    <!-- Barre de progression globale -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Progression</h6>
                        <small class="text-muted">Étape <span id="currentStepNumber">1</span> sur <span id="totalStepsDisplay">9</span></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 11.11%" id="globalProgress"></div>
                    </div>
                    <div class="row mt-3 step-indicators">
                        <div class="col step-indicator active" data-step="1">
                            <i class="fas fa-list-ul step-icon"></i>
                            <small class="d-block mt-1">Type</small>
                        </div>
                        <div class="col step-indicator" data-step="2">
                            <i class="fas fa-book-open step-icon"></i>
                            <small class="d-block mt-1">Guide</small>
                        </div>
                        <div class="col step-indicator" data-step="3">
                            <i class="fas fa-user step-icon"></i>
                            <small class="d-block mt-1">Demandeur</small>
                        </div>
                        <div class="col step-indicator" data-step="4">
                            <i class="fas fa-building step-icon"></i>
                            <small class="d-block mt-1">Organisation</small>
                        </div>
                        <div class="col step-indicator" data-step="5">
                            <i class="fas fa-map-marker-alt step-icon"></i>
                            <small class="d-block mt-1">Coordonnées</small>
                        </div>
                        <div class="col step-indicator" data-step="6">
                            <i class="fas fa-users step-icon"></i>
                            <small class="d-block mt-1">Fondateurs</small>
                        </div>
                        <div class="col step-indicator" data-step="7">
                            <i class="fas fa-user-plus step-icon"></i>
                            <small class="d-block mt-1">Adhérents</small>
                        </div>
                        <div class="col step-indicator" data-step="8">
                            <i class="fas fa-file-alt step-icon"></i>
                            <small class="d-block mt-1">Documents</small>
                        </div>
                        <div class="col step-indicator" data-step="9">
                            <i class="fas fa-check-circle step-icon"></i>
                            <small class="d-block mt-1">Soumission</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal du formulaire -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="organisationForm" action="{{ route('operator.organisations.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- ✅ NOUVEAUX CHAMPS CACHÉS POUR SESSION ADHÉRENTS -->
    <input type="hidden" id="current_dossier_id" name="current_dossier_id" value="">
    <input type="hidden" id="has_session_adherents" name="has_session_adherents" value="false">
    <input type="hidden" id="session_adherents_count" name="session_adherents_count" value="0">
    <input type="hidden" id="session_key" name="session_key" value="">
                        
                        <!-- ========== ÉTAPE 1 : CHOIX DU TYPE D'ORGANISATION ========== -->
                        <div class="step-content" id="step1" style="display: block;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                                    <i class="fas fa-list-ul fa-3x text-white"></i>
                                </div>
                                <h3 class="text-success">Choisissez le type d'organisation</h3>
                                <p class="text-muted">Sélectionnez le statut juridique qui correspond à vos objectifs au Gabon</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="row">
                                        <!-- Association -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-2 organization-type-card" data-type="association">
                                                <div class="card-body text-center p-4">
                                                    <div class="org-icon mb-3" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                                                        <i class="fas fa-handshake fa-3x text-white"></i>
                                                    </div>
                                                    <h5 class="card-title text-primary">Association</h5>
                                                    <p class="card-text text-muted">
                                                        Groupement de personnes réunies autour d'un projet commun ou partageant des activités, 
                                                        sans chercher à réaliser des bénéfices.
                                                    </p>
                                                    <div class="features mb-3">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>But non lucratif
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Min. 3 fondateurs
                                                                </small>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Min. 10 adhérents
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>AG annuelle
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_organisation" value="association" id="typeAssociation">
                                                        <label class="form-check-label fw-bold" for="typeAssociation">
                                                            Choisir Association
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ONG -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-2 organization-type-card" data-type="ong">
                                                <div class="card-body text-center p-4">
                                                    <div class="org-icon mb-3" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                                                        <i class="fas fa-globe-africa fa-3x text-white"></i>
                                                    </div>
                                                    <h5 class="card-title text-info">ONG</h5>
                                                    <p class="card-text text-muted">
                                                        Organisation Non Gouvernementale à vocation humanitaire, caritative, 
                                                        éducative ou de développement social.
                                                    </p>
                                                    <div class="features mb-3">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Mission sociale
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Min. 5 fondateurs
                                                                </small>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Min. 15 adhérents
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Projet social
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_organisation" value="ong" id="typeOng">
                                                        <label class="form-check-label fw-bold" for="typeOng">
                                                            Choisir ONG
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Parti Politique -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-2 organization-type-card" data-type="parti_politique">
                                                <div class="card-body text-center p-4">
                                                    <div class="org-icon mb-3" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                                                        <i class="fas fa-vote-yea fa-3x text-dark"></i>
                                                    </div>
                                                    <h5 class="card-title text-warning">Parti Politique</h5>
                                                    <p class="card-text text-muted">
                                                        Organisation politique légalement constituée pour participer à la vie démocratique 
                                                        et aux élections au Gabon.
                                                    </p>
                                                    <div class="features mb-3">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Vocation politique
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Min. 3 fondateurs
                                                                </small>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-exclamation text-warning me-1"></i>Min. 50 adhérents
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>3 provinces min.
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_organisation" value="parti_politique" id="typeParti">
                                                        <label class="form-check-label fw-bold" for="typeParti">
                                                            Choisir Parti Politique
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Confession Religieuse -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-2 organization-type-card" data-type="confession_religieuse">
                                                <div class="card-body text-center p-4">
                                                    <div class="org-icon mb-3" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                                                        <i class="fas fa-pray fa-3x text-white"></i>
                                                    </div>
                                                    <h5 class="card-title text-purple">Confession Religieuse</h5>
                                                    <p class="card-text text-muted">
                                                        Organisation religieuse ou spirituelle reconnue pour l'exercice du culte 
                                                        et des activités religieuses au Gabon.
                                                    </p>
                                                    <div class="features mb-3">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Vocation spirituelle
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Min. 3 fondateurs
                                                                </small>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Min. 10 fidèles
                                                                </small>
                                                                <small class="d-block text-muted">
                                                                    <i class="fas fa-check text-success me-1"></i>Lieu de culte
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_organisation" value="confession_religieuse" id="typeReligion">
                                                        <label class="form-check-label fw-bold" for="typeReligion">
                                                            Choisir Confession Religieuse
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Type sélectionné -->
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-md-8">
                                            <div class="d-none" id="selectedTypeInfo">
                                                <div class="alert alert-success d-flex align-items-center border-0 shadow-sm">
                                                    <i class="fas fa-check-circle me-3 fa-2x text-success"></i>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Type sélectionné avec succès !</h6>
                                                        <strong id="selectedTypeName"></strong>
                                                        <br>
                                                        <small class="text-muted">Vous pouvez maintenant passer à l'étape suivante pour consulter le guide spécifique.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Input caché pour stocker le type -->
                                    <input type="hidden" name="organization_type" id="organizationType" required>
                                </div>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 2 : GUIDE SPÉCIFIQUE ========== -->
                        <div class="step-content" id="step2" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                                    <i class="fas fa-book-open fa-3x text-dark"></i>
                                </div>
                                <h3 class="text-warning">Guide pour <span id="selectedTypeTitle">votre organisation</span></h3>
                                <p class="text-muted">Informations légales et procédures spécifiques au Gabon</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <!-- Guide dynamique selon le type sélectionné -->
                                    <div id="guide-content">
                                        <div class="alert alert-info border-0 mb-4 shadow-sm">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle fa-3x me-3 text-info"></i>
                                                <div>
                                                    <h5 class="alert-heading mb-1">Guide spécifique à votre type d'organisation</h5>
                                                    <p class="mb-0">Le contenu s'affichera selon votre sélection à l'étape précédente</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Confirmation de lecture obligatoire -->
                                    <div class="mt-4 p-4 bg-light rounded shadow-sm">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="guideReadConfirm" name="guide_read_confirm" required>
                                            <label class="form-check-label fw-bold" for="guideReadConfirm">
                                                <i class="fas fa-check-circle me-2 text-success"></i>
                                                J'ai lu et compris les exigences légales spécifiques à mon type d'organisation au Gabon
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                            Cette confirmation est obligatoire pour passer à l'étape suivante.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 3 : INFORMATIONS DEMANDEUR - FORMULAIRE COMPLET ========== -->
                        <div class="step-content" id="step3" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                                    <i class="fas fa-user fa-3x text-white"></i>
                                </div>
                                <h3 class="text-info">Informations du demandeur</h3>
                                <p class="text-muted">Renseignez vos informations personnelles en tant que demandeur principal</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="alert alert-info border-0 mb-4">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Information importante
                                        </h6>
                                        <p class="mb-0">
                                            Vous serez le contact privilégié pour le suivi de ce dossier. Assurez-vous que toutes vos informations 
                                            sont exactes et correspondent à vos documents officiels gabonais.
                                        </p>
                                    </div>

                                    <!-- Section Identification -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-id-card me-2"></i>
                                                Identification personnelle
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- NIP - VALIDATION CORRIGÉE -->
                                                <div class="col-md-6 mb-4">
    <label for="demandeur_nip" class="form-label fw-bold required">
        <i class="fas fa-hashtag me-2 text-primary"></i>
        Numéro d'Identification Personnelle (NIP)
    </label>
    <div class="input-group">
        <input type="text" 
               class="form-control form-control-lg" 
               id="demandeur_nip" 
               name="demandeur_nip" 
               data-validate="nip"
               placeholder="A1-2345-19901225"
               maxlength="16"
               pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}"
               required>
        <span class="input-group-text">
            <i class="fas fa-spinner fa-spin d-none" id="nip-loading"></i>
            <i class="fas fa-check text-success d-none" id="nip-valid"></i>
            <i class="fas fa-times text-danger d-none" id="nip-invalid"></i>
        </span>
    </div>
    <div class="form-text d-none">
        <i class="fas fa-info me-1"></i>
        Format nouveau: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)
    </div>
    <div class="invalid-feedback" id="demandeur_nip_error"></div>
</div>

                                                <!-- Civilité -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_civilite" class="form-label fw-bold required">
                                                        <i class="fas fa-user-tag me-2 text-primary"></i>
                                                        Civilité
                                                    </label>
                                                    <select class="form-select form-select-lg" id="demandeur_civilite" name="demandeur_civilite" required>
                                                        <option value="">Sélectionnez votre civilité</option>
                                                        <option value="M">Monsieur</option>
                                                        <option value="Mme">Madame</option>
                                                        <option value="Mlle">Mademoiselle</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Nom -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_nom" class="form-label fw-bold required">
                                                        <i class="fas fa-user me-2 text-primary"></i>
                                                        Nom de famille
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="demandeur_nom" 
                                                           name="demandeur_nom" 
                                                           placeholder="Votre nom de famille"
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Prénom -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_prenom" class="form-label fw-bold required">
                                                        <i class="fas fa-user me-2 text-primary"></i>
                                                        Prénom(s)
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="demandeur_prenom" 
                                                           name="demandeur_prenom" 
                                                           placeholder="Vos prénoms"
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Date de naissance -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_date_naissance" class="form-label fw-bold required">
                                                        <i class="fas fa-calendar me-2 text-primary"></i>
                                                        Date de naissance
                                                    </label>
                                                    <input type="date" 
                                                           class="form-control form-control-lg" 
                                                           id="demandeur_date_naissance" 
                                                           name="demandeur_date_naissance" 
                                                           max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                                                           required>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Vous devez être majeur (18 ans minimum)
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Nationalité -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_nationalite" class="form-label fw-bold required">
                                                        <i class="fas fa-flag me-2 text-primary"></i>
                                                        Nationalité
                                                    </label>
                                                    <select class="form-select form-select-lg" id="demandeur_nationalite" name="demandeur_nationalite" required>
                                                        <option value="">Sélectionnez votre nationalité</option>
                                                        <option value="Gabonaise" selected>Gabonaise</option>
                                                        <option value="Camerounaise">Camerounaise</option>
                                                        <option value="Congolaise">Congolaise</option>
                                                        <option value="Équato-guinéenne">Équato-guinéenne</option>
                                                        <option value="Française">Française</option>
                                                        <option value="Autre">Autre</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Contact -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-address-book me-2"></i>
                                                Informations de contact
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Téléphone -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_telephone" class="form-label fw-bold required">
                                                        <i class="fas fa-phone me-2 text-success"></i>
                                                        Téléphone
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+241</span>
                                                        <input type="tel" 
                                                               class="form-control form-control-lg" 
                                                               id="demandeur_telephone" 
                                                               name="demandeur_telephone" 
                                                               placeholder="01 23 45 67"
                                                               pattern="[0-9]{8,9}"
                                                               required>
                                                    </div>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Format gabonais : 8 ou 9 chiffres (ex: 01234567)
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_email" class="form-label fw-bold required">
                                                        <i class="fas fa-envelope me-2 text-success"></i>
                                                        Adresse email
                                                    </label>
                                                    <input type="email" 
                                                           class="form-control form-control-lg" 
                                                           id="demandeur_email" 
                                                           name="demandeur_email" 
                                                           placeholder="votre.email@exemple.com"
                                                           required>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Adresse email valide pour les communications officielles
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Adresse -->
                                                <div class="col-12 mb-4">
                                                    <label for="demandeur_adresse" class="form-label fw-bold required">
                                                        <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                                        Adresse complète de résidence
                                                    </label>
                                                    <textarea class="form-control form-control-lg" 
                                                              id="demandeur_adresse" 
                                                              name="demandeur_adresse" 
                                                              rows="3"
                                                              placeholder="Adresse complète (quartier, commune, ville, province)"
                                                              required></textarea>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Profession -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_profession" class="form-label fw-bold">
                                                        <i class="fas fa-briefcase me-2 text-success"></i>
                                                        Profession
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="demandeur_profession" 
                                                           name="demandeur_profession" 
                                                           placeholder="Votre profession actuelle">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Statut dans l'organisation -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_role" class="form-label fw-bold required">
                                                        <i class="fas fa-user-tie me-2 text-success"></i>
                                                        Rôle dans l'organisation
                                                    </label>
                                                    <select class="form-select form-select-lg" id="demandeur_role" name="demandeur_role" required>
                                                        <option value="">Sélectionnez votre rôle</option>
                                                        <option value="president">Président(e)</option>
                                                        <option value="vice-president">Vice-Président(e)</option>
                                                        <option value="secretaire-general">Secrétaire Général(e)</option>
                                                        <option value="tresorier">Trésorier(ère)</option>
                                                        <option value="fondateur">Membre fondateur</option>
                                                        <option value="mandataire">Mandataire</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informations complémentaires -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-clipboard-check me-2"></i>
                                                Déclaration et engagement
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" id="demandeur_engagement" name="demandeur_engagement" required>
                                                        <label class="form-check-label fw-bold" for="demandeur_engagement">
                                                            <i class="fas fa-check-circle me-2 text-warning"></i>
                                                            Je certifie sur l'honneur que les informations fournies sont exactes et complètes
                                                        </label>
                                                    </div>
                                                    
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="demandeur_responsabilite" name="demandeur_responsabilite" required>
                                                        <label class="form-check-label fw-bold" for="demandeur_responsabilite">
                                                            <i class="fas fa-gavel me-2 text-warning"></i>
                                                            J'accepte d'être le responsable légal de cette demande et de recevoir toutes les communications officielles
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 4 : INFORMATIONS ORGANISATION ========== -->
                        <div class="step-content" id="step4" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                                    <i class="fas fa-building fa-3x text-white"></i>
                                </div>
                                <h3 class="text-primary">Informations de l'organisation</h3>
                                <p class="text-muted">Renseignez les détails de votre organisation</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <!-- Section Identité -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-id-badge me-2"></i>
                                                Identité de l'organisation
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Nom organisation -->
                                                <div class="col-md-8 mb-4">
                                                    <label for="org_nom" class="form-label fw-bold required">
                                                        <i class="fas fa-building me-2 text-primary"></i>
                                                        Nom de l'organisation
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="org_nom" 
                                                           name="org_nom" 
                                                           placeholder="Nom complet de votre organisation"
                                                           required>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Le nom exact tel qu'il apparaîtra sur les documents officiels
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Sigle -->
                                                <div class="col-md-4 mb-4">
                                                    <label for="org_sigle" class="form-label fw-bold">
                                                        <i class="fas fa-tag me-2 text-primary"></i>
                                                        Sigle (optionnel)
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="org_sigle" 
                                                           name="org_sigle" 
                                                           placeholder="Ex: AJSD, ONG-DEV"
                                                           maxlength="10">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Objet social -->
                                                <div class="col-12 mb-4">
                                                    <label for="org_objet" class="form-label fw-bold required">
                                                        <i class="fas fa-bullseye me-2 text-primary"></i>
                                                        Objet social / Mission
                                                    </label>
                                                    <textarea class="form-control form-control-lg" 
                                                              id="org_objet" 
                                                              name="org_objet" 
                                                              rows="4"
                                                              placeholder="Décrivez l'objet social et la mission principale de votre organisation..."
                                                              required></textarea>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Description claire et précise des activités et objectifs (minimum 50 caractères)
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Domaine d'activité (selon le type) -->
                                                <div class="col-md-6 mb-4" id="org_domaine_container">
                                                    <label for="org_domaine" class="form-label fw-bold">
                                                        <i class="fas fa-industry me-2 text-primary"></i>
                                                        Domaine d'activité
                                                    </label>
                                                    <select class="form-select form-select-lg" id="org_domaine" name="org_domaine">
                                                        <option value="">Sélectionnez un domaine</option>
                                                        <option value="education">Éducation et Formation</option>
                                                        <option value="sante">Santé et Social</option>
                                                        <option value="environnement">Environnement</option>
                                                        <option value="sport">Sport et Loisirs</option>
                                                        <option value="culture">Culture et Arts</option>
                                                        <option value="developpement">Développement rural/urbain</option>
                                                        <option value="droits_humains">Droits de l'Homme</option>
                                                        <option value="jeunesse">Jeunesse et Enfance</option>
                                                        <option value="femmes">Promotion de la femme</option>
                                                        <option value="autre">Autre</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Date de création prévue -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_date_creation" class="form-label fw-bold required">
                                                        <i class="fas fa-calendar me-2 text-primary"></i>
                                                        Date de création prévue
                                                    </label>
                                                    <input type="date" 
                                                           class="form-control form-control-lg" 
                                                           id="org_date_creation" 
                                                           name="org_date_creation" 
                                                           min="{{ date('Y-m-d') }}"
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Contact -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-phone me-2"></i>
                                                Informations de contact
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Téléphone principal -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_telephone" class="form-label fw-bold required">
                                                        <i class="fas fa-phone me-2 text-success"></i>
                                                        Téléphone principal
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+241</span>
                                                        <input type="tel" 
                                                               class="form-control form-control-lg" 
                                                               id="org_telephone" 
                                                               name="org_telephone" 
                                                               placeholder="01 23 45 67"
                                                               pattern="[0-9]{8,9}"
                                                               required>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_email" class="form-label fw-bold">
                                                        <i class="fas fa-envelope me-2 text-success"></i>
                                                        Adresse email
                                                    </label>
                                                    <input type="email" 
                                                           class="form-control form-control-lg" 
                                                           id="org_email" 
                                                           name="org_email" 
                                                           placeholder="contact@organisation.ga">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Site web -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_site_web" class="form-label fw-bold">
                                                        <i class="fas fa-globe me-2 text-success"></i>
                                                        Site web (optionnel)
                                                    </label>
                                                    <input type="url" 
                                                           class="form-control form-control-lg" 
                                                           id="org_site_web" 
                                                           name="org_site_web" 
                                                           placeholder="https://www.organisation.ga">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Réseaux sociaux -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_reseaux_sociaux" class="form-label fw-bold">
                                                        <i class="fab fa-facebook me-2 text-success"></i>
                                                        Réseaux sociaux (optionnel)
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="org_reseaux_sociaux" 
                                                           name="org_reseaux_sociaux" 
                                                           placeholder="Facebook, Twitter, Instagram...">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section spécifique selon le type d'organisation -->
                                    <div id="type_specific_section" class="d-none">
                                        <!-- Contenu dynamique selon le type -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 5 : COORDONNÉES ET GÉOLOCALISATION ========== -->
                        <div class="step-content" id="step5" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);">
                                    <i class="fas fa-map-marker-alt fa-3x text-white"></i>
                                </div>
                                <h3 class="text-info">Localisation et coordonnées</h3>
                                <p class="text-muted">Indiquez l'adresse du siège social de votre organisation</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <!-- Adresse du siège social -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-home me-2"></i>
                                                Siège social
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Adresse complète -->
                                                <div class="col-12 mb-4">
                                                    <label for="org_adresse_complete" class="form-label fw-bold required">
                                                        <i class="fas fa-map-marker-alt me-2 text-info"></i>
                                                        Adresse complète du siège social
                                                    </label>
                                                    <textarea class="form-control form-control-lg" 
                                                              id="org_adresse_complete" 
                                                              name="org_adresse_complete" 
                                                              rows="3"
                                                              placeholder="Numéro, rue, quartier, arrondissement..."
                                                              required></textarea>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Province -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_province" class="form-label fw-bold required">
                                                        <i class="fas fa-map me-2 text-info"></i>
                                                        Province
                                                    </label>
                                                    <select class="form-select form-select-lg" id="org_province" name="org_province" required>
                                                        <option value="">Sélectionnez une province</option>
                                                        <option value="Estuaire">Estuaire</option>
                                                        <option value="Haut-Ogooué">Haut-Ogooué</option>
                                                        <option value="Moyen-Ogooué">Moyen-Ogooué</option>
                                                        <option value="Ngounié">Ngounié</option>
                                                        <option value="Nyanga">Nyanga</option>
                                                        <option value="Ogooué-Ivindo">Ogooué-Ivindo</option>
                                                        <option value="Ogooué-Lolo">Ogooué-Lolo</option>
                                                        <option value="Ogooué-Maritime">Ogooué-Maritime</option>
                                                        <option value="Woleu-Ntem">Woleu-Ntem</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Département -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_departement" class="form-label fw-bold">
                                                        <i class="fas fa-map-pin me-2 text-info"></i>
                                                        Département
                                                    </label>
                                                    <select class="form-select form-select-lg" id="org_departement" name="org_departement">
                                                        <option value="">Sélectionnez d'abord une province</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Préfecture -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_prefecture" class="form-label fw-bold required">
                                                        <i class="fas fa-building me-2 text-info"></i>
                                                        Préfecture
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="org_prefecture" 
                                                           name="org_prefecture" 
                                                           placeholder="Nom de la préfecture"
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Zone type -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_zone_type" class="form-label fw-bold required">
                                                        <i class="fas fa-city me-2 text-info"></i>
                                                        Type de zone
                                                    </label>
                                                    <select class="form-select form-select-lg" id="org_zone_type" name="org_zone_type" required>
                                                        <option value="">Sélectionnez le type</option>
                                                        <option value="urbaine">Zone urbaine</option>
                                                        <option value="rurale">Zone rurale</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Géolocalisation optionnelle -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-satellite me-2"></i>
                                                Géolocalisation (optionnel)
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="org_latitude" class="form-label fw-bold">
                                                        <i class="fas fa-compass me-2 text-warning"></i>
                                                        Latitude
                                                    </label>
                                                    <input type="number" 
                                                           class="form-control form-control-lg" 
                                                           id="org_latitude" 
                                                           name="org_latitude" 
                                                           step="0.0000001"
                                                           min="-3.978"
                                                           max="2.318"
                                                           placeholder="Ex: 0.4162">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="org_longitude" class="form-label fw-bold">
                                                        <i class="fas fa-globe-americas me-2 text-warning"></i>
                                                        Longitude
                                                    </label>
                                                    <input type="number" 
                                                           class="form-control form-control-lg" 
                                                           id="org_longitude" 
                                                           name="org_longitude" 
                                                           step="0.0000001"
                                                           min="8.695"
                                                           max="14.502"
                                                           placeholder="Ex: 9.4673">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <div class="col-12">
                                                    <button type="button" class="btn btn-outline-warning" id="getLocationBtn">
                                                        <i class="fas fa-map-marker-alt me-2"></i>
                                                        Obtenir ma position actuelle
                                                    </button>
                                                    <div class="form-text mt-2">
                                                        <i class="fas fa-info me-1"></i>
                                                        La géolocalisation aide à localiser précisément votre organisation sur la carte du Gabon
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 6 : FONDATEURS ========== -->
                        <div class="step-content" id="step6" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                                    <i class="fas fa-users fa-3x text-white"></i>
                                </div>
                                <h3 class="text-warning">Membres fondateurs</h3>
                                <p class="text-muted">Ajoutez les membres fondateurs de votre organisation</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="alert alert-info border-0 mb-4">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Exigences selon le type d'organisation
                                        </h6>
                                        <div id="fondateurs_requirements">
                                            <p class="mb-0">Minimum requis : <span id="min_fondateurs">3</span> fondateurs majeurs</p>
                                        </div>
                                    </div>

                                    <!-- Formulaire ajout fondateur -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-user-plus me-2"></i>
                                                Ajouter un fondateur
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label for="fondateur_civilite" class="form-label fw-bold">Civilité</label>
                                                    <select class="form-select" id="fondateur_civilite">
                                                        <option value="M">Monsieur</option>
                                                        <option value="Mme">Madame</option>
                                                        <option value="Mlle">Mademoiselle</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label for="fondateur_nom" class="form-label fw-bold">Nom</label>
                                                    <input type="text" class="form-control" id="fondateur_nom" placeholder="Nom de famille">
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label for="fondateur_prenom" class="form-label fw-bold">Prénom</label>
                                                    <input type="text" class="form-control" id="fondateur_prenom" placeholder="Prénom(s)">
                                                </div>

                                                <div class="col-md-3 mb-3">
    <label for="fondateur_nip" class="form-label fw-bold">NIP</label>
    <input type="text" 
           class="form-control" 
           id="fondateur_nip" 
           data-validate="nip"
           placeholder="A1-2345-19901225" 
           maxlength="16"
           pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}">
    <small class="form-text text-muted">Format: XX-QQQQ-YYYYMMDD</small>
</div>

                                                <div class="col-md-4 mb-3">
                                                    <label for="fondateur_fonction" class="form-label fw-bold">Fonction</label>
                                                    <select class="form-select" id="fondateur_fonction">
                                                        <option value="">Sélectionnez</option>
                                                        <option value="president">Président(e)</option>
                                                        <option value="vice-president">Vice-Président(e)</option>
                                                        <option value="secretaire-general">Secrétaire Général(e)</option>
                                                        <option value="tresorier">Trésorier(ère)</option>
                                                        <option value="membre">Membre fondateur</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label for="fondateur_telephone" class="form-label fw-bold">Téléphone</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+241</span>
                                                        <input type="tel" class="form-control" id="fondateur_telephone" placeholder="01234567">
                                                    </div>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label for="fondateur_email" class="form-label fw-bold">Email</label>
                                                    <input type="email" class="form-control" id="fondateur_email" placeholder="email@exemple.com">
                                                </div>

                                                <div class="col-12">
                                                    <button type="button" class="btn btn-warning" id="addFondateurBtn">
                                                        <i class="fas fa-plus me-2"></i>Ajouter ce fondateur
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Liste des fondateurs -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-list me-2"></i>
                                                    Liste des fondateurs
                                                </h6>
                                                <span class="badge bg-light text-dark" id="fondateurs_count">0 fondateur(s)</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div id="fondateurs_list">
                                                <div class="text-center py-4 text-muted">
                                                    <i class="fas fa-users fa-3x mb-3"></i>
                                                    <p>Aucun fondateur ajouté</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 7 : ADHÉRENTS ========== -->
<!-- ========================================================================
     INTERFACE MODERNISÉE ÉTAPE 7 - UPLOAD ET GESTION ADHÉRENTS
     Version: 2.0 - UX Optimisée avec Progress et Tableau Interactif
     ======================================================================== -->

<!-- Section dans create.blade.php - ÉTAPE 7 modernisée -->
<div class="step-content" id="step7" style="display: none;">
    <div class="text-center mb-4">
        <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);">
            <i class="fas fa-user-plus fa-3x text-white"></i>
        </div>
        <h3 class="text-primary">Adhérents de l'organisation</h3>
        <p class="text-muted">Préparez les données des adhérents pour l'importation finale</p>
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
                            Exigences d'adhésion - Étape 7 (Préparation)
                        </h6>
                        <div id="adherents_requirements" class="mb-2">
                            <p class="mb-0">
                                <strong>Minimum requis :</strong> <span id="min_adherents" class="text-primary">10</span> adhérents
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    Cette étape prépare les données en session. L'import réel se fera lors de la soumission.
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
                            Liste des adhérents préparés
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
                            <h5 class="text-muted">Aucun adhérent préparé</h5>
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
</div>

                        <!-- ========== ÉTAPE 8 : DOCUMENTS ========== -->
                        <div class="step-content" id="step8" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #17a2b8 0%, #6610f2 100%);">
                                    <i class="fas fa-file-alt fa-3x text-white"></i>
                                </div>
                                <h3 class="text-info">Documents justificatifs</h3>
                                <p class="text-muted">Uploadez les documents requis pour votre organisation</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="alert alert-info border-0 mb-4">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Documents requis
                                        </h6>
                                        <p class="mb-0">Les documents varient selon le type d'organisation sélectionné.</p>
                                    </div>

                                    <!-- Documents requis -->
                                    <div id="documents_container">
                                        <!-- Documents dynamiques selon le type -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== ÉTAPE 9 : SOUMISSION FINALE ========== -->
                        <div class="step-content" id="step9" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                    <i class="fas fa-check-circle fa-3x text-white"></i>
                                </div>
                                <h3 class="text-success">Vérification et soumission</h3>
                                <p class="text-muted">Vérifiez vos informations avant de soumettre votre dossier</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <!-- Récapitulatif -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-clipboard-check me-2"></i>
                                                Récapitulatif de votre dossier
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="recap_content">
                                                <!-- Contenu généré dynamiquement -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Déclaration finale -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-signature me-2"></i>
                                                Déclaration sur l'honneur
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="declaration_veracite" name="declaration_veracite" required>
                                                <label class="form-check-label fw-bold" for="declaration_veracite">
                                                    Je certifie sur l'honneur que toutes les informations fournies sont exactes et complètes
                                                </label>
                                            </div>
                                            
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="declaration_conformite" name="declaration_conformite" required>
                                                <label class="form-check-label fw-bold" for="declaration_conformite">
                                                    Je déclare que l'organisation respecte la législation gabonaise en vigueur
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="declaration_autorisation" name="declaration_autorisation" required>
                                                <label class="form-check-label fw-bold" for="declaration_autorisation">
                                                    J'autorise l'administration à vérifier les informations fournies
                                                </label>
                                            </div>
                                            <!-- Ajouter cette section dans l'ÉTAPE 9 du formulaire, dans la card "Déclaration sur l'honneur" -->

<!-- Déclaration spécifique parti politique -->
<div id="declaration_parti_politique" class="d-none_">
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="declaration_exclusivite_parti" name="declaration_exclusivite_parti">
        <label class="form-check-label fw-bold" for="declaration_exclusivite_parti">
            <i class="fas fa-vote-yea me-2 text-warning"></i>
            Je déclare que les adhérents de ce parti politique ne sont membres d'aucun autre parti politique au Gabon
        </label>
    </div>
</div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Boutons de navigation -->
                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                            <i class="fas fa-arrow-left me-2"></i>Précédent
                        </button>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-success" id="nextBtn" onclick="changeStep(1)">
                                Suivant <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-primary d-none" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Soumettre la demande
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
                    Aide - Création d'organisation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Ce guide vous aidera à créer votre organisation étape par étape selon la législation gabonaise.</p>
                <ul>
                    <li><strong>Étape 1-2 :</strong> Choisissez le type et consultez le guide légal</li>
                    <li><strong>Étape 3 :</strong> Saisissez vos informations personnelles</li>
                    <li><strong>Étapes 4-9 :</strong> À développer selon vos besoins</li>
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

<!-- ✅ NOUVEAUX META TAGS POUR SESSION ADHÉRENTS -->
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

<!-- ========================================================================
     META TAGS À AJOUTER DANS create.blade.php
     Pour assurer la compatibilité session Étape 7 ↔ confirmation.blade.php
     ======================================================================== -->

<!-- À ajouter dans la section <head> de create.blade.php -->

<!-- Meta tags existants -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="app-url" content="{{ config('app.url') }}">

<!-- ✅ NOUVEAUX META TAGS POUR SESSION ADHÉRENTS -->
<meta name="user-id" content="{{ auth()->id() }}">

<!-- Dossier ID sera défini si on édite un dossier existant -->
@if(isset($dossier) && $dossier)
    <meta name="dossier-id" content="{{ $dossier->id }}">
@endif

<!-- Organisation ID sera défini si on édite une organisation existante -->
@if(isset($organisation) && $organisation)
    <meta name="organisation-id" content="{{ $organisation->id }}">
@endif

<!-- ========================================================================
     CHAMP CACHÉ DANS LE FORMULAIRE
     Alternative aux meta tags pour l'ID dossier
     ======================================================================== -->

<!-- À ajouter dans le formulaire #organisationForm de create.blade.php -->
<form id="organisationForm" action="{{ route('operator.organisations.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <!-- ✅ NOUVEAU: Champ caché pour l'ID dossier (sera rempli lors de la création) -->
    <input type="hidden" id="current_dossier_id" name="current_dossier_id" value="">
    
    <!-- ✅ NOUVEAU: Indicateur session adhérents active -->
    <input type="hidden" id="has_session_adherents" name="has_session_adherents" value="false">
    <input type="hidden" id="session_adherents_count" name="session_adherents_count" value="0">
    <input type="hidden" id="session_key" name="session_key" value="">
    
    <!-- Reste du formulaire existant -->
    <!-- ... -->
</form>

<!-- ========================================================================
     SCRIPT D'INITIALISATION POUR RÉCUPÉRER L'ID DOSSIER
     À ajouter dans la section @push('scripts') de create.blade.php
     ======================================================================== -->

<script>
/**
 * Initialisation des variables globales pour la session adhérents
 */
window.currentUserId = {{ auth()->id() }};

@if(isset($dossier) && $dossier)
    window.currentDossierId = {{ $dossier->id }};
    document.getElementById('current_dossier_id').value = {{ $dossier->id }};
@endif

@if(isset($organisation) && $organisation)
    window.currentOrganisationId = {{ $organisation->id }};
@endif

/**
 * Hook pour capturer l'ID dossier lors de la création
 */
document.addEventListener('DOMContentLoaded', function() {
    // Intercepter les réponses AJAX pour capturer l'ID dossier
    const originalFetch = window.fetch;
    
    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                // Cloner la réponse pour pouvoir la lire sans affecter l'original
                const clonedResponse = response.clone();
                
                // Si c'est une réponse JSON de création d'organisation
                if (response.url.includes('/organisations') && response.ok) {
                    clonedResponse.json().then(data => {
                        if (data.success && data.data && data.data.dossier_id) {
                            const dossierId = data.data.dossier_id;
                            
                            // Mettre à jour les variables globales
                            window.currentDossierId = dossierId;
                            
                            // Mettre à jour le champ caché
                            const hiddenField = document.getElementById('current_dossier_id');
                            if (hiddenField) {
                                hiddenField.value = dossierId;
                            }
                            
                            console.log('✅ ID Dossier capturé:', dossierId);
                            
                            // Déclencher un événement personnalisé
                            window.dispatchEvent(new CustomEvent('dossierCreated', {
                                detail: { dossierId: dossierId }
                            }));
                        }
                    }).catch(e => {
                        // Erreur de parsing JSON, ignorer
                    });
                }
                
                return response;
            });
    };
});

/**
 * Écouter l'événement de création de dossier
 */
window.addEventListener('dossierCreated', function(event) {
    const dossierId = event.detail.dossierId;
    
    console.log('📥 Événement dossierCreated reçu:', dossierId);
    
    // Vérifier s'il y a des adhérents en session à associer
    if (window.OrganisationApp && window.OrganisationApp.adherents && window.OrganisationApp.adherents.length > 0) {
        console.log('🔄 Association des adhérents session au nouveau dossier');
        
        // Mettre à jour les informations de session
        if (window.OrganisationApp.sessionInfo) {
            window.OrganisationApp.sessionInfo.dossierId = dossierId;
        }
        
        // Sauvegarder avec le bon ID dossier
        if (typeof saveAdherentsToSession === 'function' && window.OrganisationApp.adherentsMetadata) {
            const preparedData = {
                adherents: window.OrganisationApp.adherents,
                stats: window.OrganisationApp.adherentsMetadata.stats || {},
                rapport: window.OrganisationApp.adherentsMetadata.rapport || {},
                timestamp: new Date().toISOString(),
                expires_at: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString()
            };
            
            saveAdherentsToSession(preparedData);
        }
    }
});
</script>

<!-- ========================================================================
     VARIABLES DE CONFIGURATION POUR LE JAVASCRIPT
     ======================================================================== -->

<script>
// Configuration globale pour la session adhérents
window.SessionAdherentsConfig = {
    enabled: true,
    keyPrefix: 'phase2_adherents_',
    expirationPrefix: 'phase2_expires_',
    metadataPrefix: 'phase2_metadata_',
    expirationHours: 2,
    maxAdherents: 10000,
    autoSaveInterval: 30000, // 30 secondes
    routes: {
        save: '{{ route("operator.organisations.save-session-adherents") }}',
        check: '{{ route("operator.organisations.check-session-adherents") }}',
        clear: '{{ route("operator.organisations.clear-session-adherents") }}'
    }
};

console.log('🔧 Configuration session adhérents chargée:', window.SessionAdherentsConfig);
</script>


<!-- ✅ NOUVEAU : Validation NIP format XX-QQQQ-YYYYMMDD -->
    <script src="{{ asset('js/nip-validation.js') }}"></script>

    <!-- Workflow 2 phases -->
<script src="{{ asset('js/workflow-2phases.js') }}"></script>

<script src="{{ asset('js/csrf-manager.js') }}"></script>
    <script src="{{ asset('js/organisation-create.js') }}"></script>
    
    <!-- NOUVEAU : Système de chunking -->
    <script src="{{ asset('js/chunking-import.js') }}"></script>

    <!-- ✅ Scripts NIP Validation -->
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
        });
    </script>
@endpush