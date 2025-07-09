@extends('layouts.operator')

@section('title', 'Créer une Organisation')
@section('page-title', 'Nouvelle Organisation')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/organisation-create.css') }}">

    <!-- CSS Styles intégrés pour l'interface modernisée -->
<style>
/* ===== STYLES POUR L'INTERFACE MODERNISÉE ===== */

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

/* ===== STYLES POUR LES CARTES D'ORGANISATION ===== */
.organization-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef !important;
}

.organization-type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-color: #007bff !important;
}

.organization-type-card.selected,
.organization-type-card:has(input:checked) {
    border-color: #007bff !important;
    background: linear-gradient(135deg, rgba(0,123,255,0.05) 0%, rgba(0,123,255,0.1) 100%);
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,123,255,0.2);
}

.org-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.3s ease;
}

.organization-type-card:hover .org-icon {
    transform: scale(1.1);
}

.organization-type-card .form-check-input {
    display: none;
}

.organization-type-card .form-check-label {
    cursor: pointer;
    width: 100%;
}

/* ===== STYLES POUR LES INDICATEURS D'ÉTAPES ===== */
.step-indicator {
    text-align: center;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.step-indicator .step-icon {
    display: block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    margin: 0 auto 0.5rem;
    transition: all 0.3s ease;
}

.step-indicator.active .step-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.step-indicator.completed .step-icon {
    background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
    color: white;
}

.step-indicator.completed .step-icon::before {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.step-indicator small {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
}

.step-indicator.active small {
    color: #28a745;
    font-weight: 600;
}

.step-indicator.completed small {
    color: #007bff;
    font-weight: 600;
}

/* ===== ICÔNE D'ÉTAPE LARGE ===== */
.step-icon-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* ===== ANIMATIONS ===== */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
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
    .d-flex.gap-3 {
        gap: 0.5rem !important;
        flex-wrap: wrap;
    }
    
    .step-indicator {
        margin-bottom: 0.5rem;
    }
    
    .step-indicator .step-icon {
        width: 30px;
        height: 30px;
        line-height: 30px;
    }
    
    .organization-type-card {
        margin-bottom: 1rem;
    }
}

/* Dark theme support */
@media (prefers-color-scheme: dark) {
    .step-indicator small {
        color: #adb5bd;
    }
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

/* ===== VALIDATION STATES ===== */
.is-invalid {
    border-color: #dc3545 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.is-valid {
    border-color: #198754 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.64 1.64 3.62-3.62.94.94-4.56 4.56z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* ===== REQUIRED FIELD INDICATOR ===== */
.required::after {
    content: " *";
    color: #dc3545;
}

/* ===== STEP CONTENT DISPLAY ===== */
.step-content {
    display: none;
    animation: fadeIn 0.5s ease;
}

.step-content:first-child {
    display: block;
}


/* =====<!-- Styles workflow 2 phases intégrés -->  WORKFLOW 2 PHASES - STYLES INTÉGRÉS ===== */

.phase-banner {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
}

.phase-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="gabon-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23gabon-pattern)"/></svg>');
    opacity: 0.3;
}

.phase-content {
    position: relative;
    z-index: 2;
}

.workflow-progress-mini {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 25px;
    padding: 0.5rem 1rem;
    display: inline-block;
    margin-bottom: 0.5rem;
}

.next-phase-info {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
    border-left: 4px solid #ffc107;
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 1rem 0;
}

.adherents-preview {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 1rem 0;
    border: 2px dashed #dee2e6;
}

.phase-indicator-mini {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.phase-step-mini {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
}

.phase-step-mini.completed {
    background: #28a745;
    color: white;
}

.phase-step-mini.current {
    background: #ffc107;
    color: #333;
}

.phase-step-mini.pending {
    background: #6c757d;
    color: white;
}

.submission-choice {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.submission-choice:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.submission-choice.selected {
    border-color: #007bff;
    background: linear-gradient(135deg, rgba(0,123,255,0.05) 0%, rgba(0,123,255,0.1) 100%);
}

.submission-choice input[type="radio"] {
    display: none;
}

.choice-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
}

.btn-phase-primary {
    background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-phase-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 158, 63, 0.4);
    color: white;
}

.btn-phase-secondary {
    background: white;
    border: 2px solid #009e3f;
    color: #009e3f;
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-phase-secondary:hover {
    background: #009e3f;
    color: white;
    transform: translateY(-2px);
}

/* Animation pour modal workflow */
.modal-workflow .modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.modal-workflow .modal-header {
    background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);
    color: white;
    border: none;
    padding: 1.5rem;
}

.modal-workflow .modal-body {
    padding: 2rem;
}

.workflow-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin: 1.5rem 0;
}

.step-connector {
    width: 50px;
    height: 3px;
    background: #dee2e6;
    border-radius: 2px;
}

.step-connector.active {
    background: #28a745;
}

@media (max-width: 768px) {
    .workflow-steps {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .step-connector {
        width: 3px;
        height: 30px;
    }
    
    .submission-choice {
        margin-bottom: 1rem;
    }
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
                            <p class="mb-0 opacity-90">Assistant de création guidée en <span id="totalSteps">8</span> étapes</p>
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

    <!-- Indicateur de sauvegarde amélioré -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-end">
                <small id="save-indicator" class="text-muted me-2">
                    <i class="fas fa-circle text-muted"></i> En attente
                </small>
            </div>
        </div>
    </div>

    <!-- Barre de progression globale -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Progression</h6>
                        <small class="text-muted">Étape <span id="currentStepNumber">1</span> sur <span id="totalStepsDisplay">8</span></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 12.5%" id="globalProgress"></div>
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
                            <i class="fas fa-file-alt step-icon"></i>
                            <small class="d-block mt-1">Documents</small>
                        </div>
                        <div class="col step-indicator" data-step="8">
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

                        <!-- ========== ÉTAPE 7 : DOCUMENTS ========== -->
                        <div class="step-content" id="step7" style="display: none;">
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

                        <!-- ========== ÉTAPE 8 : SOUMISSION FINALE ========== -->
                        <!-- ========== ÉTAPE 8 : WORKFLOW 2 PHASES - SOUMISSION INTELLIGENTE ========== -->
<div class="step-content" id="step8" style="display: none;">
    <div class="text-center mb-4">
        <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <i class="fas fa-check-circle fa-3x text-white"></i>
        </div>
        <h3 class="text-success">Soumission de votre dossier</h3>
        <p class="text-muted">Choisissez le mode de soumission adapté à votre organisation</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Bannière workflow 2 phases -->
            <div class="phase-banner">
                <div class="phase-content">
                    <div class="workflow-progress-mini">
                        <i class="fas fa-rocket me-2"></i>
                        Système de soumission intelligent SGLP
                    </div>
                    <h5 class="mb-2">Workflow 2 Phases Activé</h5>
                    <p class="mb-0 opacity-90">
                        Pour optimiser le traitement de votre dossier, nous utilisons un système en 2 phases
                        qui évite les timeouts et garantit la sécurité de vos données.
                    </p>
                </div>
            </div>

            <!-- Indicateur de progression workflow -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-route me-2"></i>
                                Progression du Workflow
                            </h6>
                            <div class="workflow-steps">
                                <div class="phase-step-mini completed">1</div>
                                <div class="step-connector active"></div>
                                <div class="phase-step-mini current">2</div>
                                <div class="step-connector"></div>
                                <div class="phase-step-mini pending">✓</div>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-success fw-bold">Phase 1</small>
                                    <br><small class="text-muted">Organisation créée</small>
                                </div>
                                <div class="col-4">
                                    <small class="text-warning fw-bold">Phase 2</small>
                                    <br><small class="text-muted">Import adhérents</small>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted fw-bold">Terminé</small>
                                    <br><small class="text-muted">Soumission finale</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="phase-indicator-mini">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                                <div>
                                    <div class="fw-bold">Phase 1</div>
                                    <small class="text-muted">En cours</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                        <!-- Contenu généré dynamiquement par JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Information sur les adhérents -->
            <div class="next-phase-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users fa-2x text-warning me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="text-warning mb-1">
                            <i class="fas fa-info-circle me-1"></i>
                            Information importante sur les adhérents
                        </h6>
                        <p class="mb-2">
                            L'ajout des adhérents se fera en <strong>Phase 2</strong> après la création de votre organisation.
                            Cette approche garantit :
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="d-block">
                                    <i class="fas fa-check text-success me-1"></i>
                                    Aucun risque de timeout serveur
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-check text-success me-1"></i>
                                    Traitement par lots optimisé
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="d-block">
                                    <i class="fas fa-check text-success me-1"></i>
                                    Sécurité maximale des données
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-check text-success me-1"></i>
                                    Possibilité de reprise en cas d'interruption
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aperçu des adhérents (si présents en session) -->
            <div class="adherents-preview" id="adherents-preview-section" style="display: none;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-primary mb-1">
                            <i class="fas fa-eye me-2"></i>
                            Adhérents détectés en session
                        </h6>
                        <p class="mb-0 text-muted">
                            <span id="session-adherents-count">0</span> adhérents prêts pour l'import en Phase 2
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info" id="session-status-badge">En attente</span>
                        <br><small class="text-muted" id="session-expiry">Expire dans 2h</small>
                    </div>
                </div>
            </div>

            <!-- Choix du mode de soumission -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-route me-2"></i>
                        Mode de soumission
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="submission-choice" data-choice="phase1-only">
                                <input type="radio" name="submission_mode" value="phase1_only" id="submissionPhase1Only">
                                <label for="submissionPhase1Only" class="w-100">
                                    <div class="choice-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                        <i class="fas fa-rocket text-white"></i>
                                    </div>
                                    <h6 class="text-primary">Soumission Directe (Phase 1)</h6>
                                    <p class="text-muted small mb-3">
                                        Créer l'organisation maintenant et ajouter les adhérents en Phase 2
                                    </p>
                                    <div class="text-start">
                                        <small class="text-success d-block">
                                            <i class="fas fa-check me-1"></i> Organisation créée immédiatement
                                        </small>
                                        <small class="text-success d-block">
                                            <i class="fas fa-check me-1"></i> Numéro de récépissé généré
                                        </small>
                                        <small class="text-info d-block">
                                            <i class="fas fa-arrow-right me-1"></i> Phase 2 : Import adhérents ensuite
                                        </small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="submission-choice" data-choice="traditional">
                                <input type="radio" name="submission_mode" value="traditional" id="submissionTraditional">
                                <label for="submissionTraditional" class="w-100">
                                    <div class="choice-icon" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                                        <i class="fas fa-file-upload text-white"></i>
                                    </div>
                                    <h6 class="text-primary">Soumission Traditionnelle</h6>
                                    <p class="text-muted small mb-3">
                                        Ajouter manuellement quelques adhérents maintenant (≤ 50)
                                    </p>
                                    <div class="text-start">
                                        <small class="text-warning d-block">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Limité à 50 adhérents
                                        </small>
                                        <small class="text-warning d-block">
                                            <i class="fas fa-clock me-1"></i> Risque de timeout si nombreux
                                        </small>
                                        <small class="text-success d-block">
                                            <i class="fas fa-check me-1"></i> Tout en une seule fois
                                        </small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Message d'aide conditionnel -->
                    <div class="alert alert-info border-0 mt-3" id="submission-help">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lightbulb fa-2x me-3 text-info"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Recommandation</h6>
                                <p class="mb-0">
                                    Pour une expérience optimale, nous recommandons la <strong>Soumission Directe (Phase 1)</strong> 
                                    qui permet de créer votre organisation immédiatement et d'ajouter les adhérents en toute sécurité ensuite.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section adhérents traditionnels (masquée par défaut) -->
            <div class="card border-0 shadow-sm mb-4 d-none" id="traditional-adherents-section">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Ajout d'adhérents (Mode Traditionnel)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning border-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Mode traditionnel sélectionné
                        </h6>
                        <p class="mb-0">
                            Vous pouvez ajouter jusqu'à 50 adhérents maintenant. Pour des volumes plus importants, 
                            il est recommandé d'utiliser la Phase 2.
                        </p>
                    </div>

                    <!-- Interface simplifiée d'ajout d'adhérents -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="adherent-nom" placeholder="Nom de famille">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="adherent-prenom" placeholder="Prénom(s)">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NIP *</label>
                            <input type="text" class="form-control" id="adherent-nip" placeholder="A1-2345-19901225">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Téléphone</label>
                            <div class="input-group">
                                <span class="input-group-text">+241</span>
                                <input type="tel" class="form-control" id="adherent-telephone" placeholder="01234567">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-warning w-100" onclick="addAdherentTraditional()">
                                <i class="fas fa-plus me-2"></i>Ajouter
                            </button>
                        </div>
                    </div>

                    <!-- Liste des adhérents ajoutés -->
                    <div id="traditional-adherents-list">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <p>Aucun adhérent ajouté</p>
                        </div>
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

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="declaration_autorisation" name="declaration_autorisation" required>
                        <label class="form-check-label fw-bold" for="declaration_autorisation">
                            J'autorise l'administration à vérifier les informations fournies
                        </label>
                    </div>

                    <!-- Déclaration spécifique parti politique -->
                    <div id="declaration_parti_politique" class="d-none">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="declaration_exclusivite_parti" name="declaration_exclusivite_parti">
                            <label class="form-check-label fw-bold" for="declaration_exclusivite_parti">
                                <i class="fas fa-vote-yea me-2 text-warning"></i>
                                Je déclare que les adhérents de ce parti politique ne sont membres d'aucun autre parti politique au Gabon
                            </label>
                        </div>
                    </div>

                    <!-- Déclaration workflow 2 phases -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="declaration_workflow" name="declaration_workflow" required>
                        <label class="form-check-label fw-bold" for="declaration_workflow">
                            <i class="fas fa-route me-2 text-success"></i>
                            Je comprends et accepte le processus de création en 2 phases pour garantir la sécurité de mes données
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                    </form>
                </div>

                <!-- Boutons de navigation avec workflow 2 phases -->
<!-- Boutons de navigation avec workflow 2 phases -->
<div class="card-footer bg-light border-0">
    <div class="d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
            <i class="fas fa-arrow-left me-2"></i>Précédent
        </button>
        
        <div class="ms-auto d-flex gap-2">
            <!-- Bouton suivant (étapes 1-7) -->
            <button type="button" class="btn btn-success" id="nextBtn" onclick="changeStep(1)">
                Suivant <i class="fas fa-arrow-right ms-2"></i>
            </button>
            
            <!-- Boutons de soumission (étape 8) -->
            <button type="button" class="btn btn-primary" id="submitPhase1Btn" onclick="submitPhase1()" style="display: none;">
                <i class="fas fa-rocket me-2"></i>Créer l'Organisation (Phase 1)
            </button>
            
            <button type="submit" class="btn btn-success" id="submitTraditionalBtn" style="display: none;">
                <i class="fas fa-paper-plane me-2"></i>Soumettre Maintenant
            </button>
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
                    <li><strong>Étapes 4-8 :</strong> Complétez les informations de l'organisation</li>
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




<!-- Modal de confirmation workflow 2 phases -->
<div class="modal fade modal-workflow" id="workflowConfirmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-rocket me-2"></i>
                    Confirmation - Workflow 2 Phases
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="workflow-steps">
                        <div class="phase-step-mini current">1</div>
                        <div class="step-connector active"></div>
                        <div class="phase-step-mini pending">2</div>
                    </div>
                </div>

                <div class="alert alert-success border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3 text-success"></i>
                        <div>
                            <h6 class="alert-heading mb-2">Prêt pour la soumission Phase 1</h6>
                            <p class="mb-2">
                                Votre organisation va être créée et enregistrée dans la base de données SGLP.
                                Vous recevrez immédiatement un numéro de récépissé.
                            </p>
                            <p class="mb-0">
                                <strong>Étape suivante :</strong> Vous serez automatiquement redirigé vers l'interface
                                d'import des adhérents (Phase 2) pour compléter votre dossier.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">
                            <i class="fas fa-check me-2"></i>Phase 1 - Organisation
                        </h6>
                        <ul class="list-unstyled">
                            <li>✓ Informations organisation validées</li>
                            <li>✓ Fondateurs enregistrés</li>
                            <li>✓ Documents uploadés</li>
                            <li>✓ Numéro de récépissé généré</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">
                            <i class="fas fa-arrow-right me-2"></i>Phase 2 - Adhérents
                        </h6>
                        <ul class="list-unstyled">
                            <li>⏳ Import intelligent par lots</li>
                            <li>⏳ Traitement sécurisé</li>
                            <li>⏳ Validation automatique</li>
                            <li>⏳ Soumission finale à l'administration</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <strong>Organisation :</strong> 
                            <span id="confirm-org-name">---</span>
                        </div>
                        <div>
                            <strong>Type :</strong> 
                            <span id="confirm-org-type">---</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-phase-primary" id="confirmSubmitPhase1">
                    <i class="fas fa-rocket me-2"></i>Confirmer - Créer l'Organisation
                </button>
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
    
    <!-- MODULE WORKFLOW 2 PHASES -->
    <script src="{{ asset('js/workflow-2phases.js') }}"></script>

    <!-- Configuration JavaScript pour 8 étapes -->
    <script>
        // Configuration globale mise à jour pour 8 étapes
        window.OrganisationApp = {
            currentStep: 1,
            totalSteps: 8, // Mise à jour : 8 étapes au lieu de 9
            formData: {},
            isSubmitting: false,
            organisationType: null,
            foundateurs: [],
            validationErrors: {},
            uploadedDocuments: {}, // Pour tracker les documents uploadés
            
            // Configuration cache/localStorage
            cacheConfig: {
                enabled: true,
                keyPrefix: 'organisationForm_',
                expirationHours: 24,
                autoSaveInterval: 5000, // 5 secondes
                maxCacheSize: 5 * 1024 * 1024 // 5MB max
            },
            
            // Configuration mise à jour des étapes
            stepConfig: {
                1: { name: 'Type', icon: 'fa-list-ul', required: true },
                2: { name: 'Guide', icon: 'fa-book-open', required: true },
                3: { name: 'Demandeur', icon: 'fa-user', required: true },
                4: { name: 'Organisation', icon: 'fa-building', required: true },
                5: { name: 'Coordonnées', icon: 'fa-map-marker-alt', required: true },
                6: { name: 'Fondateurs', icon: 'fa-users', required: true },
                7: { name: 'Documents', icon: 'fa-file-alt', required: true }, // Ex-étape 8
                8: { name: 'Soumission', icon: 'fa-check-circle', required: true } // Ex-étape 9
            },
            
            // Configuration documents par type d'organisation
            documentRequirements: {
                'association': {
                    required: ['statuts', 'pv_ag', 'liste_fondateurs'],
                    optional: ['justif_siege']
                },
                'ong': {
                    required: ['statuts', 'pv_ag', 'liste_fondateurs', 'projet_social'],
                    optional: ['justif_siege', 'budget_previsionnel']
                },
                'parti_politique': {
                    required: ['statuts', 'pv_ag', 'liste_fondateurs', 'programme_politique'],
                    optional: ['justif_siege']
                },
                'confession_religieuse': {
                    required: ['statuts', 'pv_ag', 'liste_fondateurs', 'expose_doctrine'],
                    optional: ['justif_siege', 'justif_lieu_culte']
                }
            }
        };
        
        console.log('📋 Configuration mise à jour:', window.OrganisationApp);
    </script>

    <script>
// ========================================
// WORKFLOW 2 PHASES - JAVASCRIPT INTÉGRÉ
// ========================================

// Variables globales pour le workflow
window.WorkflowData = {
    submissionMode: 'phase1_only',
    traditionalAdherents: [],
    isPhase1Submitted: false
};

/**
 * Mise à jour des boutons de navigation pour l'étape 8
 */
function updateNavigationButtonsStep8() {
    const nextBtn = document.getElementById('nextBtn');
    const submitPhase1Btn = document.getElementById('submitPhase1Btn');
    const submitTraditionalBtn = document.getElementById('submitTraditionalBtn');
    const submissionInfo = document.getElementById('submission-info');
    
    if (OrganisationApp.currentStep === 8) {
        // Masquer le bouton suivant
        if (nextBtn) nextBtn.classList.add('d-none');
        
        // Afficher les informations de soumission
        if (submissionInfo) submissionInfo.classList.remove('d-none');
        
        // Afficher le bon bouton selon le mode
        if (WorkflowData.submissionMode === 'phase1_only') {
            if (submitPhase1Btn) submitPhase1Btn.classList.remove('d-none');
            if (submitTraditionalBtn) submitTraditionalBtn.classList.add('d-none');
        } else {
            if (submitPhase1Btn) submitPhase1Btn.classList.add('d-none');
            if (submitTraditionalBtn) submitTraditionalBtn.classList.remove('d-none');
        }
    } else {
        // Masquer tous les boutons de soumission
        if (submitPhase1Btn) submitPhase1Btn.classList.add('d-none');
        if (submitTraditionalBtn) submitTraditionalBtn.classList.add('d-none');
        if (submissionInfo) submissionInfo.classList.add('d-none');
        
        // Afficher le bouton suivant
        if (nextBtn) nextBtn.classList.remove('d-none');
    }
}

/**
 * Gestion du changement de mode de soumission
 */
function setupSubmissionModeHandlers() {
    const submissionChoices = document.querySelectorAll('.submission-choice');
    const traditionalSection = document.getElementById('traditional-adherents-section');
    const submissionHelp = document.getElementById('submission-help');
    
    submissionChoices.forEach(choice => {
        choice.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                // Mettre à jour la sélection visuelle
                submissionChoices.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                
                // Mettre à jour le mode global
                WorkflowData.submissionMode = radio.value;
                
                // Afficher/masquer la section traditionnelle
                if (radio.value === 'traditional') {
                    if (traditionalSection) traditionalSection.classList.remove('d-none');
                    if (submissionHelp) {
                        submissionHelp.className = 'alert alert-warning border-0 mt-3';
                        submissionHelp.innerHTML = `
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Mode Traditionnel</h6>
                                    <p class="mb-0">
                                        Attention : Ce mode est limité à 50 adhérents maximum et peut causer des timeouts 
                                        avec de gros volumes. La Phase 2 est recommandée pour plus de sécurité.
                                    </p>
                                </div>
                            </div>
                        `;
                    }
                } else {
                    if (traditionalSection) traditionalSection.classList.add('d-none');
                    if (submissionHelp) {
                        submissionHelp.className = 'alert alert-info border-0 mt-3';
                        submissionHelp.innerHTML = `
                            <div class="d-flex align-items-center">
                                <i class="fas fa-lightbulb fa-2x me-3 text-info"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Recommandation</h6>
                                    <p class="mb-0">
                                        Excellent choix ! La soumission en 2 phases garantit la sécurité de vos données 
                                        et permet de traiter un nombre illimité d'adhérents.
                                    </p>
                                </div>
                            </div>
                        `;
                    }
                }
                
                // Mettre à jour les boutons
                updateNavigationButtonsStep8();
            }
        });
    });
}

/**
 * Ajouter un adhérent en mode traditionnel
 */
function addAdherentTraditional() {
    const nom = document.getElementById('adherent-nom')?.value?.trim();
    const prenom = document.getElementById('adherent-prenom')?.value?.trim();
    const nip = document.getElementById('adherent-nip')?.value?.trim();
    const telephone = document.getElementById('adherent-telephone')?.value?.trim();
    
    // Validation
    if (!nom || !prenom || !nip) {
        showNotification('Nom, prénom et NIP sont obligatoires', 'warning');
        return;
    }
    
    // Vérifier le limite
    if (WorkflowData.traditionalAdherents.length >= 50) {
        showNotification('Maximum 50 adhérents en mode traditionnel', 'warning');
        return;
    }
    
    // Vérifier les doublons
    if (WorkflowData.traditionalAdherents.some(a => a.nip === nip)) {
        showNotification('Ce NIP existe déjà', 'warning');
        return;
    }
    
    // Ajouter l'adhérent
    const adherent = {
        id: Date.now(),
        nom: nom,
        prenom: prenom,
        nip: nip,
        telephone: telephone || '',
        civilite: 'M'
    };
    
    WorkflowData.traditionalAdherents.push(adherent);
    updateTraditionalAdherentsList();
    clearTraditionalForm();
    
    showNotification(`Adhérent ${prenom} ${nom} ajouté (${WorkflowData.traditionalAdherents.length}/50)`, 'success');
}

/**
 * Mettre à jour la liste des adhérents traditionnels
 */
function updateTraditionalAdherentsList() {
    const listContainer = document.getElementById('traditional-adherents-list');
    if (!listContainer) return;
    
    if (WorkflowData.traditionalAdherents.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-users fa-2x mb-2"></i>
                <p>Aucun adhérent ajouté</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Adhérents ajoutés (${WorkflowData.traditionalAdherents.length}/50)
            </h6>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearAllTraditionalAdherents()">
                <i class="fas fa-trash"></i> Vider
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>NIP</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    WorkflowData.traditionalAdherents.forEach((adherent, index) => {
        html += `
            <tr>
                <td><strong>${adherent.nom}</strong></td>
                <td>${adherent.prenom}</td>
                <td><code>${adherent.nip}</code></td>
                <td>${adherent.telephone || '-'}</td>
                <td>
                    <button class="btn btn-outline-danger btn-sm" onclick="removeTraditionalAdherent(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    listContainer.innerHTML = html;
}

/**
 * Supprimer un adhérent traditionnel
 */
function removeTraditionalAdherent(index) {
    if (confirm('Supprimer cet adhérent ?')) {
        const adherent = WorkflowData.traditionalAdherents[index];
        WorkflowData.traditionalAdherents.splice(index, 1);
        updateTraditionalAdherentsList();
        showNotification(`Adhérent ${adherent.prenom} ${adherent.nom} supprimé`, 'info');
    }
}

/**
 * Vider tous les adhérents traditionnels
 */
function clearAllTraditionalAdherents() {
    if (confirm('Supprimer tous les adhérents ajoutés ?')) {
        WorkflowData.traditionalAdherents = [];
        updateTraditionalAdherentsList();
        showNotification('Tous les adhérents supprimés', 'info');
    }
}

/**
 * Vider le formulaire traditionnel
 */
function clearTraditionalForm() {
    const fields = ['adherent-nom', 'adherent-prenom', 'adherent-nip', 'adherent-telephone'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) field.value = '';
    });
}

/**
 * Soumission Phase 1
 */
function submitPhase1() {
    console.log('🚀 Début soumission Phase 1');
    
    // Validation finale
    if (!validateStep8()) {
        showNotification('Veuillez compléter toutes les déclarations obligatoires', 'warning');
        return;
    }
    
    // Préparer les données de confirmation
    const orgNom = document.getElementById('org_nom')?.value || 'Organisation';
    const orgType = getOrganizationTypeLabel(OrganisationApp.organisationType);
    
    document.getElementById('confirm-org-name').textContent = orgNom;
    document.getElementById('confirm-org-type').textContent = orgType;
    
    // Afficher le modal de confirmation
    const confirmModal = new bootstrap.Modal(document.getElementById('workflowConfirmModal'));
    confirmModal.show();
}

/**
 * Confirmation finale de la soumission Phase 1
 */
function confirmSubmitPhase1() {
    console.log('✅ Confirmation soumission Phase 1');
    
    // Masquer le modal
    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('workflowConfirmModal'));
    if (confirmModal) confirmModal.hide();
    
    // Afficher le loader
    showGlobalLoader('Création de votre organisation en cours...');
    
    // Collecter toutes les données
    collectAllFormData();
    
    // Préparer les données pour Phase 1
    const formData = new FormData();
    
    // Ajouter toutes les données du formulaire
    Object.keys(OrganisationApp.formData).forEach(key => {
        if (OrganisationApp.formData[key] !== null && OrganisationApp.formData[key] !== undefined) {
            formData.append(key, OrganisationApp.formData[key]);
        }
    });
    
    // Ajouter les fondateurs
    OrganisationApp.foundateurs.forEach((fondateur, index) => {
        Object.keys(fondateur).forEach(key => {
            if (key !== 'id' && key !== 'dateAjout') {
                formData.append(`fondateurs[${index}][${key}]`, fondateur[key]);
            }
        });
    });
    
    // Ajouter les documents
    Object.keys(OrganisationApp.uploadedDocuments).forEach(docType => {
        const doc = OrganisationApp.uploadedDocuments[docType];
        if (doc.file) {
            formData.append(`documents[${docType}]`, doc.file);
        }
    });
    
    // Marquer comme workflow 2 phases
    formData.append('_workflow', '2_phases');
    formData.append('_phase', '1');
    formData.append('submission_mode', 'phase1_only');
    
    // Envoyer la requête
    fetch(document.getElementById('organisationForm').action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        hideGlobalLoader();
        console.log('✅ Phase 1 réussie:', data);
        
        if (data.success && data.phase === 1) {
            // Marquer comme soumis
            WorkflowData.isPhase1Submitted = true;
            
            // Nettoyer le cache puisque Phase 1 est terminée
            clearCache();
            
            // Notification de succès
            showNotification(`Organisation créée avec succès ! N° ${data.data.numero_recepisse}`, 'success');
            
            // Redirection vers Phase 2
            setTimeout(() => {
                if (data.data.next_phase_url) {
                    window.location.href = data.data.next_phase_url;
                } else if (data.data.dossier_id) {
                    // Fallback: construire l'URL manuellement
                    window.location.href = `/operator/dossiers/${data.data.dossier_id}/adherents-import`;
                } else {
                    console.error('❌ URL Phase 2 non fournie');
                    showNotification('Organisation créée, mais erreur de redirection vers Phase 2', 'warning');
                }
            }, 2000);
        } else {
            throw new Error(data.message || 'Réponse inattendue du serveur');
        }
    })
    .catch(error => {
        hideGlobalLoader();
        console.error('❌ Erreur Phase 1:', error);
        
        let errorMessage = 'Erreur lors de la création de l\'organisation. ';
        
        if (error.message.includes('timeout')) {
            errorMessage += 'Délai d\'attente dépassé. Veuillez réessayer.';
        } else if (error.message.includes('413')) {
            errorMessage += 'Fichiers trop volumineux. Réduisez la taille des documents.';
        } else {
            errorMessage += 'Veuillez vérifier vos données et réessayer.';
        }
        
        showNotification(errorMessage, 'error');
    });
}

/**
 * Afficher le loader global
 */
function showGlobalLoader(message = 'Chargement...') {
    const loader = document.getElementById('global-loader');
    if (loader) {
        const loadingText = loader.querySelector('.visually-hidden');
        if (loadingText) loadingText.textContent = message;
        loader.classList.remove('d-none');
    }
}

/**
 * Masquer le loader global
 */
function hideGlobalLoader() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.classList.add('d-none');
    }
}

/**
 * Vérifier la session des adhérents
 */
function checkSessionAdherents() {
    // Cette fonction devrait appeler une API pour vérifier s'il y a des adhérents en session
    // Pour l'instant, simulation
    const sessionCount = 0; // À remplacer par vraie vérification
    
    const previewSection = document.getElementById('adherents-preview-section');
    const countSpan = document.getElementById('session-adherents-count');
    
    if (sessionCount > 0) {
        if (previewSection) previewSection.style.display = 'block';
        if (countSpan) countSpan.textContent = sessionCount;
    }
}

/**
 * Initialiser le workflow lors de l'arrivée à l'étape 8
 */
function initWorkflowStep8() {
    console.log('🔄 Initialisation workflow étape 8');
    
    // Sélectionner le mode par défaut
    const defaultMode = document.getElementById('submissionPhase1Only');
    if (defaultMode && !document.querySelector('input[name="submission_mode"]:checked')) {
        defaultMode.checked = true;
        defaultMode.closest('.submission-choice').classList.add('selected');
        WorkflowData.submissionMode = 'phase1_only';
    }
    
    // Configurer les gestionnaires d'événements
    setupSubmissionModeHandlers();
    
    // Vérifier les adhérents en session
    checkSessionAdherents();
    
    // Mettre à jour les boutons
    updateNavigationButtonsStep8();
    
    // Configurer le bouton de confirmation
    const confirmBtn = document.getElementById('confirmSubmitPhase1');
    if (confirmBtn) {
        confirmBtn.onclick = confirmSubmitPhase1;
    }
}

/**
 * Modifier la fonction changeStep existante pour gérer l'étape 8
 */
const originalChangeStep = window.changeStep;
window.changeStep = function(direction) {
    const result = originalChangeStep(direction);
    
    // Si on arrive à l'étape 8, initialiser le workflow
    if (OrganisationApp.currentStep === 8) {
        setTimeout(() => {
            initWorkflowStep8();
        }, 100);
    }
    
    return result;
};

/**
 * Modifier la fonction updateNavigationButtons existante
 */
const originalUpdateNavigationButtons = window.updateNavigationButtons || function() {};
window.updateNavigationButtons = function() {
    originalUpdateNavigationButtons();
    
    if (OrganisationApp.currentStep === 8) {
        updateNavigationButtonsStep8();
    }
};

// Exposer les fonctions nécessaires globalement
window.submitPhase1 = submitPhase1;
window.addAdherentTraditional = addAdherentTraditional;
window.removeTraditionalAdherent = removeTraditionalAdherent;
window.clearAllTraditionalAdherents = clearAllTraditionalAdherents;

console.log('🚀 Workflow 2 Phases intégré avec succès dans create.blade.php');
</script>
    
    <!-- Validation NIP format XX-QQQQ-YYYYMMDD -->
    <script src="{{ asset('js/nip-validation.js') }}"></script>
    
    <!-- Scripts principaux -->
    <script src="{{ asset('js/csrf-manager.js') }}"></script>
    
    <!-- Script principal adapté pour 8 étapes -->
    <script>
        // ========================================
        // FONCTIONS DE NAVIGATION CORRIGÉES
        // ========================================
        
        /**
         * Navigation entre les étapes (avec sauvegarde automatique)
         */
        function changeStep(direction) {
            console.log(`🔄 Changement d'étape: direction ${direction}, étape actuelle: ${OrganisationApp.currentStep}`);
            
            // Sauvegarder les données de l'étape actuelle avant de changer
            saveCurrentStepData();
            
            // Validation avant d'avancer
            if (direction === 1 && !validateCurrentStep()) {
                console.log('❌ Validation échouée pour l\'étape', OrganisationApp.currentStep);
                showNotification('Veuillez compléter tous les champs obligatoires avant de continuer', 'warning');
                return false;
            }
            
            // Calculer la nouvelle étape
            const newStep = OrganisationApp.currentStep + direction;
            
            if (newStep >= 1 && newStep <= OrganisationApp.totalSteps) {
                OrganisationApp.currentStep = newStep;
                updateStepDisplay();
                updateNavigationButtons();
                
                // Actions spécifiques selon l'étape
                handleStepSpecificActions(newStep);
                
                // Sauvegarde après changement d'étape
                saveToCache();
                
                scrollToTop();
                return true;
            }
            
            return false;
        }
        
        /**
         * Actions spécifiques selon l'étape (CORRIGÉ pour 8 étapes)
         */
        function handleStepSpecificActions(stepNumber) {
            switch (stepNumber) {
                case 2:
                    updateGuideContent();
                    break;
                case 4:
                    updateOrganizationRequirements();
                    break;
                case 6:
                    updateFoundersRequirements();
                    break;
                case 7: // Ex-étape 8 : Documents
                    updateDocumentsRequirements();
                    break;
                case 8: // Ex-étape 9 : Soumission
                    generateRecap();
                    // Initialiser le workflow si on arrive à l'étape 8
                    if (typeof initWorkflowStep8 === 'function') {
                        setTimeout(() => {
                            initWorkflowStep8();
                        }, 100);
                    }
                    break;
            }
        }
        
        /**
         * Validation de l'étape actuelle
         */
        function validateCurrentStep() {
            return validateStep(OrganisationApp.currentStep);
        }
        
        /**
         * Validation d'une étape spécifique (CORRIGÉ pour 8 étapes)
         */
        function validateStep(stepNumber) {
            switch (stepNumber) {
                case 1: return validateStep1();
                case 2: return validateStep2();
                case 3: return validateStep3();
                case 4: return validateStep4();
                case 5: return validateStep5();
                case 6: return validateStep6();
                case 7: return validateStep7(); // Ex-étape 8 : Documents
                case 8: return validateStep8(); // Ex-étape 9 : Soumission
                default: return true;
            }
        }
        
        /**
         * Validation étape 1 : Type d'organisation
         */
        function validateStep1() {
            const selectedType = document.querySelector('input[name="type_organisation"]:checked');
            if (!selectedType) {
                showNotification('Veuillez sélectionner un type d\'organisation', 'warning');
                return false;
            }
            
            // Stocker le type sélectionné
            OrganisationApp.organisationType = selectedType.value;
            document.getElementById('organizationType').value = selectedType.value;
            
            return true;
        }
        
        /**
         * Validation étape 2 : Guide lu
         */
        function validateStep2() {
            const guideConfirm = document.getElementById('guideReadConfirm');
            if (!guideConfirm || !guideConfirm.checked) {
                showNotification('Veuillez confirmer avoir lu et compris le guide', 'warning');
                if (guideConfirm) guideConfirm.focus();
                return false;
            }
            return true;
        }
        
        /**
         * Validation étape 3 : Demandeur
         */
        function validateStep3() {
            const requiredFields = [
                'demandeur_nip', 'demandeur_civilite', 'demandeur_nom', 
                'demandeur_prenom', 'demandeur_date_naissance', 'demandeur_nationalite',
                'demandeur_telephone', 'demandeur_email', 'demandeur_adresse', 'demandeur_role'
            ];
            
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && (!field.value || field.value.trim() === '')) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field) {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Validation checkboxes obligatoires
            const engagementCheck = document.getElementById('demandeur_engagement');
            const responsabiliteCheck = document.getElementById('demandeur_responsabilite');
            
            if (!engagementCheck || !engagementCheck.checked) {
                if (engagementCheck) engagementCheck.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!responsabiliteCheck || !responsabiliteCheck.checked) {
                if (responsabiliteCheck) responsabiliteCheck.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                showNotification('Veuillez compléter tous les champs obligatoires du demandeur', 'warning');
            }
            
            return isValid;
        }
        
        /**
         * Validation étape 4 : Organisation
         */
        function validateStep4() {
            const requiredFields = [
                'org_nom', 'org_objet', 'org_date_creation', 'org_telephone'
            ];
            
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && (!field.value || field.value.trim() === '')) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field) {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                showNotification('Veuillez compléter les informations obligatoires de l\'organisation', 'warning');
            }
            
            return isValid;
        }
        
        /**
         * Validation étape 5 : Coordonnées
         */
        function validateStep5() {
            const requiredFields = [
                'org_adresse_complete', 'org_province', 'org_prefecture', 'org_zone_type'
            ];
            
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && (!field.value || field.value.trim() === '')) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field) {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                showNotification('Veuillez compléter les informations de localisation', 'warning');
            }
            
            return isValid;
        }
        
        /**
         * Validation étape 6 : Fondateurs
         */
        function validateStep6() {
            if (OrganisationApp.foundateurs.length < 3) {
                showNotification('Minimum 3 fondateurs requis', 'warning');
                return false;
            }
            return true;
        }
        
        /**
         * Validation étape 7 : Documents (ex-étape 8)
         */
        function validateStep7() {
            if (!OrganisationApp.organisationType) {
                showNotification('Type d\'organisation non défini', 'error');
                return false;
            }
            
            const requirements = OrganisationApp.documentRequirements[OrganisationApp.organisationType];
            if (!requirements) return true;
            
            const missingDocs = [];
            
            // Vérifier les documents obligatoires
            requirements.required.forEach(docType => {
                const fileInput = document.getElementById(`doc_${docType}`);
                if (!fileInput || !fileInput.files[0]) {
                    missingDocs.push(getDocumentLabel(docType));
                }
            });
            
            if (missingDocs.length > 0) {
                showNotification(
                    `Documents obligatoires manquants :<br>• ${missingDocs.join('<br>• ')}`, 
                    'warning'
                );
                
                // Highlight des champs manquants
                requirements.required.forEach(docType => {
                    const fileInput = document.getElementById(`doc_${docType}`);
                    if (!fileInput || !fileInput.files[0]) {
                        fileInput?.classList.add('is-invalid');
                    }
                });
                
                return false;
            }
            
            // Valider les fichiers uploadés
            const invalidFiles = [];
            requirements.required.forEach(docType => {
                const fileInput = document.getElementById(`doc_${docType}`);
                if (fileInput && fileInput.files[0]) {
                    const file = fileInput.files[0];
                    
                    // Vérifier la taille
                    if (file.size > 5 * 1024 * 1024) {
                        invalidFiles.push(`${getDocumentLabel(docType)} (trop volumineux)`);
                    }
                    
                    // Vérifier le type
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        invalidFiles.push(`${getDocumentLabel(docType)} (format non autorisé)`);
                    }
                }
            });
            
            if (invalidFiles.length > 0) {
                showNotification(
                    `Fichiers invalides :<br>• ${invalidFiles.join('<br>• ')}`, 
                    'error'
                );
                return false;
            }
            
            return true;
        }
        
        /**
         * Validation étape 8 : Soumission (ex-étape 9)
         */
        function validateStep8() {
            const requiredChecks = [
                'declaration_veracite', 'declaration_conformite', 'declaration_autorisation'
            ];
            
            let isValid = true;
            
            requiredChecks.forEach(checkId => {
                const check = document.getElementById(checkId);
                if (!check || !check.checked) {
                    if (check) check.classList.add('is-invalid');
                    isValid = false;
                } else if (check) {
                    check.classList.remove('is-invalid');
                }
            });
            
            // Vérification spéciale pour parti politique
            if (OrganisationApp.organisationType === 'parti_politique') {
                const partiCheck = document.getElementById('declaration_exclusivite_parti');
                if (partiCheck && !partiCheck.checked) {
                    partiCheck.classList.add('is-invalid');
                    isValid = false;
                } else if (partiCheck) {
                    partiCheck.classList.remove('is-invalid');
                }
            }
            
            if (!isValid) {
                showNotification('Veuillez accepter toutes les déclarations obligatoires', 'warning');
            }
            
            return isValid;
        }
        
        /**
         * Mise à jour de l'affichage des étapes
         */
        function updateStepDisplay() {
            // Masquer toutes les étapes
            document.querySelectorAll('.step-content').forEach(step => {
                step.style.display = 'none';
            });
            
            // Afficher l'étape actuelle
            const currentStepElement = document.getElementById(`step${OrganisationApp.currentStep}`);
            if (currentStepElement) {
                currentStepElement.style.display = 'block';
            }
            
            // Mettre à jour les indicateurs
            updateStepIndicators();
            updateProgressBar();
        }
        
        /**
         * Mise à jour des indicateurs d'étapes
         */
        function updateStepIndicators() {
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                const stepNumber = index + 1;
                indicator.classList.remove('active', 'completed');
                
                if (stepNumber < OrganisationApp.currentStep) {
                    indicator.classList.add('completed');
                } else if (stepNumber === OrganisationApp.currentStep) {
                    indicator.classList.add('active');
                }
            });
        }
        
        /**
         * Mise à jour de la barre de progression
         */
        function updateProgressBar() {
            const progressBar = document.getElementById('globalProgress');
            const currentStepNumber = document.getElementById('currentStepNumber');
            
            if (progressBar) {
                const percentage = (OrganisationApp.currentStep / OrganisationApp.totalSteps) * 100;
                progressBar.style.width = percentage + '%';
            }
            
            if (currentStepNumber) {
                currentStepNumber.textContent = OrganisationApp.currentStep;
            }
        }
        
        /**
 * Mise à jour des boutons de navigation
 */
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const submitPhase1Btn = document.getElementById('submitPhase1Btn');
    const submitTraditionalBtn = document.getElementById('submitTraditionalBtn');
    const submissionInfo = document.getElementById('submission-info');
    
    console.log(`🔄 updateNavigationButtons - Étape: ${OrganisationApp.currentStep}/${OrganisationApp.totalSteps}`);
    
    // Bouton précédent
    if (prevBtn) {
        if (OrganisationApp.currentStep > 1) {
            prevBtn.style.display = 'inline-block';
        } else {
            prevBtn.style.display = 'none';
        }
    }
    
    // ✅ CORRECTION : Traiter l'étape 8 comme la DERNIÈRE étape (soumission)
    if (OrganisationApp.currentStep === OrganisationApp.totalSteps) {
        // DERNIÈRE ÉTAPE (8) : Soumission - Afficher boutons workflow
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'none';
        
        // Afficher les informations de soumission
        if (submissionInfo) submissionInfo.style.display = 'block';
        
        // Décider quel bouton afficher selon le workflow
        const adherentsCount = (OrganisationApp.adherents || []).length;
        console.log(`📊 Analyse volume adhérents: ${adherentsCount}`);
        
        // Par défaut, toujours afficher le bouton Phase 1 pour le workflow 2 phases
        if (submitPhase1Btn) {
            submitPhase1Btn.style.display = 'inline-block';
            console.log('✅ Bouton Phase 1 affiché');
        }
        
        // Optionnel : afficher aussi le bouton traditionnel selon les préférences
        const submissionMode = document.querySelector('input[name="submission_mode"]:checked');
        if (submissionMode && submissionMode.value === 'traditional') {
            if (submitTraditionalBtn) {
                submitTraditionalBtn.style.display = 'inline-block';
                console.log('✅ Bouton traditionnel aussi affiché');
            }
        } else {
            if (submitTraditionalBtn) submitTraditionalBtn.style.display = 'none';
        }
        
        console.log('✅ Dernière étape (8 - Soumission) - Boutons workflow affichés');
        
    } else {
        // Toutes les autres étapes (1-7) : bouton suivant visible, soumission masquée
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.style.display = 'none';
        if (submitPhase1Btn) submitPhase1Btn.style.display = 'none';
        if (submitTraditionalBtn) submitTraditionalBtn.style.display = 'none';
        if (submissionInfo) submissionInfo.style.display = 'none';
        
        console.log(`✅ Étape ${OrganisationApp.currentStep} - Bouton suivant affiché`);
    }
}
        
        
        /**
         * Sauvegarder les données de l'étape actuelle
         */
        function saveCurrentStepData() {
            const currentStepElement = document.getElementById(`step${OrganisationApp.currentStep}`);
            if (!currentStepElement) return;
            
            const inputs = currentStepElement.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                const name = input.name || input.id;
                if (!name) return;
                
                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.checked) {
                        OrganisationApp.formData[name] = input.value;
                    }
                } else if (input.type === 'file') {
                    // Pour les fichiers, stocker les métadonnées
                    if (input.files && input.files.length > 0) {
                        OrganisationApp.formData[name] = {
                            hasFile: true,
                            fileName: input.files[0].name,
                            fileSize: input.files[0].size
                        };
                    }
                } else {
                    OrganisationApp.formData[name] = input.value;
                }
            });
            
            // Déclencher la sauvegarde en cache
            saveToCache();
        }
        
        /**
         * Générer le récapitulatif pour l'étape 8 (ex-étape 9)
         */
        function generateRecap() {
            const recapContainer = document.getElementById('recap_content');
            if (!recapContainer) return;
            
            let recapHtml = '<div class="row">';
            
            // Type d'organisation
            if (OrganisationApp.organisationType) {
                recapHtml += `
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-primary"><i class="fas fa-list-ul me-2"></i>Type d'organisation</h6>
                                <p class="mb-0 fw-bold">${getOrganizationTypeLabel(OrganisationApp.organisationType)}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Nom de l'organisation
            const orgNom = document.getElementById('org_nom');
            if (orgNom && orgNom.value) {
                recapHtml += `
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-primary"><i class="fas fa-building me-2"></i>Nom de l'organisation</h6>
                                <p class="mb-0 fw-bold">${orgNom.value}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Demandeur
            const demandeurNom = document.getElementById('demandeur_nom');
            const demandeurPrenom = document.getElementById('demandeur_prenom');
            const demandeurRole = document.getElementById('demandeur_role');
            if (demandeurNom && demandeurPrenom && demandeurNom.value && demandeurPrenom.value) {
                recapHtml += `
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-primary"><i class="fas fa-user me-2"></i>Demandeur principal</h6>
                                <p class="mb-1 fw-bold">${demandeurPrenom.value} ${demandeurNom.value}</p>
                                <small class="text-muted">${demandeurRole ? demandeurRole.options[demandeurRole.selectedIndex]?.text || '' : ''}</small>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Nombre de fondateurs
            recapHtml += `
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-primary"><i class="fas fa-users me-2"></i>Fondateurs</h6>
                            <p class="mb-0 fw-bold">${OrganisationApp.foundateurs.length} fondateur(s)</p>
                            <small class="text-muted">
                                ${OrganisationApp.foundateurs.length >= 3 ? 
                                    '<i class="fas fa-check text-success me-1"></i>Minimum requis atteint' : 
                                    '<i class="fas fa-exclamation-triangle text-warning me-1"></i>Minimum requis: 3'
                                }
                            </small>
                        </div>
                    </div>
                </div>
            `;
            
            // Localisation
            const province = document.getElementById('org_province');
            const prefecture = document.getElementById('org_prefecture');
            if (province && prefecture && province.value && prefecture.value) {
                recapHtml += `
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-primary"><i class="fas fa-map-marker-alt me-2"></i>Localisation</h6>
                                <p class="mb-0 fw-bold">${prefecture.value}, ${province.value}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Documents uploadés
            const documentsUploaded = Object.keys(OrganisationApp.uploadedDocuments || {}).length;
            const requirements = OrganisationApp.documentRequirements[OrganisationApp.organisationType];
            const documentsRequired = requirements ? requirements.required.length : 0;
            
            recapHtml += `
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-primary"><i class="fas fa-file-alt me-2"></i>Documents</h6>
                            <p class="mb-0 fw-bold">${documentsUploaded} / ${documentsRequired} obligatoires</p>
                            <small class="text-muted">
                                ${documentsUploaded >= documentsRequired ? 
                                    '<i class="fas fa-check text-success me-1"></i>Documents complets' : 
                                    '<i class="fas fa-exclamation-triangle text-warning me-1"></i>Documents manquants'
                                }
                            </small>
                        </div>
                    </div>
                </div>
            `;
            
            recapHtml += '</div>';
            
            // Ajout d'un message de validation globale
            const isValid = OrganisationApp.foundateurs.length >= 3 && documentsUploaded >= documentsRequired;
            
            if (isValid) {
                recapHtml += `
                    <div class="alert alert-success border-0 mt-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Dossier prêt pour soumission</h6>
                                <p class="mb-0">Toutes les exigences minimales sont respectées. Vous pouvez procéder à la soumission.</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                recapHtml += `
                    <div class="alert alert-warning border-0 mt-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Dossier incomplet</h6>
                                <p class="mb-0">Veuillez compléter toutes les sections obligatoires avant la soumission.</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            recapContainer.innerHTML = recapHtml;
        }
        
        /**
         * Mettre à jour le contenu du guide selon le type
         */
        function updateGuideContent() {
            const guideContent = document.getElementById('guide-content');
            const selectedTypeTitle = document.getElementById('selectedTypeTitle');
            
            if (!OrganisationApp.organisationType) return;
            
            if (selectedTypeTitle) {
                selectedTypeTitle.textContent = getOrganizationTypeLabel(OrganisationApp.organisationType);
            }
            
            if (guideContent) {
                const content = getGuideContentForType(OrganisationApp.organisationType);
                guideContent.innerHTML = content;
            }
        }
        
        /**
         * Contenu du guide selon le type d'organisation
         */
        function getGuideContentForType(type) {
            const guides = {
                'association': `
                    <div class="alert alert-success border-0 mb-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-handshake fa-3x me-3 text-success"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Guide pour créer une Association au Gabon</h5>
                                <p class="mb-0">Procédures légales selon la législation gabonaise en vigueur</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-check me-2"></i>Exigences minimales</h6>
                            <ul class="list-unstyled">
                                <li>• Minimum 3 membres fondateurs majeurs</li>
                                <li>• Minimum 10 adhérents à la création</li>
                                <li>• But non lucratif clairement défini</li>
                                <li>• Siège social au Gabon</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                            <ul class="list-unstyled">
                                <li>• Statuts signés par les fondateurs</li>
                                <li>• PV de l'assemblée générale constitutive</li>
                                <li>• Liste des membres fondateurs avec NIP</li>
                                <li>• Justificatif du siège social (optionnel)</li>
                            </ul>
                        </div>
                    </div>
                `,
                'ong': `
                    <div class="alert alert-info border-0 mb-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-globe-africa fa-3x me-3 text-info"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Guide pour créer une ONG au Gabon</h5>
                                <p class="mb-0">Organisation Non Gouvernementale à vocation sociale</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-info"><i class="fas fa-check me-2"></i>Exigences minimales</h6>
                            <ul class="list-unstyled">
                                <li>• Minimum 5 membres fondateurs majeurs</li>
                                <li>• Minimum 15 adhérents à la création</li>
                                <li>• Mission sociale ou humanitaire</li>
                                <li>• Projet social détaillé</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                            <ul class="list-unstyled">
                                <li>• Statuts signés par les fondateurs</li>
                                <li>• PV de l'assemblée générale constitutive</li>
                                <li>• Liste des membres fondateurs avec NIP</li>
                                <li>• Projet social détaillé</li>
                                <li>• Budget prévisionnel (optionnel)</li>
                            </ul>
                        </div>
                    </div>
                `,
                'parti_politique': `
                    <div class="alert alert-warning border-0 mb-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-vote-yea fa-3x me-3 text-warning"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Guide pour créer un Parti Politique au Gabon</h5>
                                <p class="mb-0">Organisation politique pour la participation démocratique</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Exigences spéciales</h6>
                            <ul class="list-unstyled">
                                <li>• Minimum 3 membres fondateurs majeurs</li>
                                <li>• Minimum 50 adhérents à la création</li>
                                <li>• Présence dans au moins 3 provinces</li>
                                <li>• Programme politique détaillé</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-warning"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                            <ul class="list-unstyled">
                                <li>• Statuts signés par les fondateurs</li>
                                <li>• PV de l'assemblée générale constitutive</li>
                                <li>• Liste des membres fondateurs avec NIP</li>
                                <li>• Programme politique complet</li>
                            </ul>
                        </div>
                    </div>
                `,
                'confession_religieuse': `
                    <div class="alert alert-secondary border-0 mb-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-pray fa-3x me-3 text-secondary"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Guide pour créer une Confession Religieuse au Gabon</h5>
                                <p class="mb-0">Organisation religieuse pour l'exercice du culte</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-secondary"><i class="fas fa-check me-2"></i>Exigences minimales</h6>
                            <ul class="list-unstyled">
                                <li>• Minimum 3 membres fondateurs majeurs</li>
                                <li>• Minimum 10 fidèles à la création</li>
                                <li>• Doctrine religieuse définie</li>
                                <li>• Lieu de culte identifié</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-secondary"><i class="fas fa-file-alt me-2"></i>Documents requis</h6>
                            <ul class="list-unstyled">
                                <li>• Statuts signés par les fondateurs</li>
                                <li>• PV de l'assemblée générale constitutive</li>
                                <li>• Liste des membres fondateurs avec NIP</li>
                                <li>• Exposé de la doctrine religieuse</li>
                                <li>• Justificatif du lieu de culte (optionnel)</li>
                            </ul>
                        </div>
                    </div>
                `
            };
            
            return guides[type] || `
                <div class="alert alert-info border-0 mb-4 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-3x me-3 text-info"></i>
                        <div>
                            <h5 class="alert-heading mb-1">Guide spécifique à votre type d'organisation</h5>
                            <p class="mb-0">Le contenu s'affichera selon votre sélection à l'étape précédente</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        /**
         * Mettre à jour les exigences des fondateurs
         */
        function updateFoundersRequirements() {
            const requirementsDiv = document.getElementById('fondateurs_requirements');
            if (!requirementsDiv) return;
            
            const minimums = {
                'association': 3,
                'ong': 5,
                'parti_politique': 3,
                'confession_religieuse': 3
            };
            
            const minRequired = minimums[OrganisationApp.organisationType] || 3;
            document.getElementById('min_fondateurs').textContent = minRequired;
        }
        
        /**
         * Mettre à jour les exigences des documents
         */
        function updateDocumentsRequirements() {
            const documentsContainer = document.getElementById('documents_container');
            if (!documentsContainer || !OrganisationApp.organisationType) return;

            const requirements = OrganisationApp.documentRequirements[OrganisationApp.organisationType];
            if (!requirements) return;

            const allDocuments = [...requirements.required, ...requirements.optional];
            
            let documentsHtml = `
                <div class="alert alert-info border-0 mb-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>
                        Documents requis pour ${getOrganizationTypeLabel(OrganisationApp.organisationType)}
                    </h6>
                    <p class="mb-2">
                        <strong>Documents obligatoires :</strong> ${requirements.required.length}
                        ${requirements.optional.length > 0 ? `| <strong>Documents optionnels :</strong> ${requirements.optional.length}` : ''}
                    </p>
                    <small class="text-muted">
                        <i class="fas fa-upload me-1"></i>
                        Formats acceptés : PDF, JPG, PNG (taille max : 5MB par fichier)
                    </small>
                </div>
            `;

            allDocuments.forEach(doc => {
                const isRequired = requirements.required.includes(doc);
                const label = getDocumentLabel(doc);
                
                documentsHtml += `
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">
                                        ${isRequired ? '<span class="text-danger">*</span> ' : ''}
                                        ${label}
                                    </h6>
                                    <input type="file" 
                                           class="form-control" 
                                           id="doc_${doc}" 
                                           name="documents[${doc}]"
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           onchange="handleDocumentUpload('${doc}', this)"
                                           ${isRequired ? 'required' : ''}>
                                    <div class="form-text">
                                        <i class="fas fa-info me-1"></i>
                                        ${isRequired ? 'Document obligatoire' : 'Document optionnel'} - PDF, JPG, PNG (max 5MB)
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div id="status_${doc}" class="text-muted">
                                        <i class="fas fa-clock me-1"></i>En attente
                                    </div>
                                    <div class="progress mt-2 d-none" id="progress_container_${doc}">
                                        <div class="progress-bar" id="progress_${doc}" style="width: 0%"></div>
                                    </div>
                                    <div id="preview_${doc}" class="mt-2 d-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            documentsContainer.innerHTML = documentsHtml;
        }
        
        /**
         * Obtenir le label d'un document
         */
        function getDocumentLabel(doc) {
            const labels = {
                'statuts': 'Statuts de l\'organisation',
                'pv_ag': 'Procès-verbal de l\'assemblée générale constitutive',
                'liste_fondateurs': 'Liste des membres fondateurs',
                'justif_siege': 'Justificatif du siège social',
                'projet_social': 'Projet social détaillé',
                'budget_previsionnel': 'Budget prévisionnel',
                'programme_politique': 'Programme politique',
                'liste_50_adherents': 'Liste de 50 adhérents minimum',
                'expose_doctrine': 'Exposé de la doctrine religieuse',
                'justif_lieu_culte': 'Justificatif du lieu de culte'
            };
            return labels[doc] || doc;
        }
        
        /**
         * Gestion upload document
         */
        function handleDocumentUpload(docType, fileInput) {
            const file = fileInput.files[0];
            if (!file) return;
            
            // Validation de la taille
            if (file.size > 5 * 1024 * 1024) {
                showNotification('Le fichier ne peut pas dépasser 5MB', 'error');
                fileInput.value = '';
                return;
            }
            
            // Validation du type
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showNotification('Type de fichier non autorisé. Utilisez PDF, JPG ou PNG.', 'error');
                fileInput.value = '';
                return;
            }
            
            // Mise à jour du statut
            const statusElement = document.getElementById(`status_${docType}`);
            const progressContainer = document.getElementById(`progress_container_${docType}`);
            const progressBar = document.getElementById(`progress_${docType}`);
            const previewElement = document.getElementById(`preview_${docType}`);
            
            if (statusElement) {
                statusElement.innerHTML = '<i class="fas fa-upload me-1 text-info"></i>Fichier sélectionné';
            }
            
            // Simulation du progress (à remplacer par vraie logique d'upload)
            if (progressContainer && progressBar) {
                progressContainer.classList.remove('d-none');
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    progressBar.style.width = progress + '%';
                    
                    if (progress >= 100) {
                        clearInterval(interval);
                        setTimeout(() => {
                            progressContainer.classList.add('d-none');
                            if (statusElement) {
                                statusElement.innerHTML = '<i class="fas fa-check me-1 text-success"></i>Fichier téléchargé';
                            }
                            
                            // Aperçu pour les images
                            if (file.type.startsWith('image/') && previewElement) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    previewElement.innerHTML = `
                                        <img src="${e.target.result}" class="img-thumbnail" style="max-height: 100px;">
                                        <small class="d-block text-muted">${file.name}</small>
                                    `;
                                    previewElement.classList.remove('d-none');
                                };
                                reader.readAsDataURL(file);
                            } else if (previewElement) {
                                previewElement.innerHTML = `
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        <small class="text-muted">${file.name}</small>
                                    </div>
                                `;
                                previewElement.classList.remove('d-none');
                            }
                        }, 500);
                    }
                }, 100);
            }
            
            // Stocker le fichier dans l'application
            OrganisationApp.uploadedDocuments[docType] = {
                file: file,
                name: file.name,
                size: file.size,
                type: file.type,
                uploaded: true
            };
            
            console.log('Document uploadé:', docType, file.name);
        }
        
        /**
         * Obtenir le label d'un type d'organisation
         */
        function getOrganizationTypeLabel(type) {
            const labels = {
                'association': 'Association',
                'ong': 'Organisation Non Gouvernementale (ONG)',
                'parti_politique': 'Parti Politique',
                'confession_religieuse': 'Confession Religieuse'
            };
            return labels[type] || type;
        }
        
        /**
         * Afficher une notification
         */
        function showNotification(message, type = 'info') {
            const alertClass = {
                success: 'alert-success',
                error: 'alert-danger',
                warning: 'alert-warning',
                info: 'alert-info'
            }[type] || 'alert-info';

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        /**
         * Scroll vers le haut
         */
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // ========================================
        // GESTION DES TYPES D'ORGANISATION
        // ========================================
        
        /**
         * Initialiser les cartes de type d'organisation
         */
        function initOrganizationTypeCards() {
            const typeCards = document.querySelectorAll('.organization-type-card');
            const selectedInfo = document.getElementById('selectedTypeInfo');
            const selectedTypeName = document.getElementById('selectedTypeName');
            
            typeCards.forEach(card => {
                card.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        
                        // Mettre à jour l'affichage
                        typeCards.forEach(c => c.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        // Afficher les informations de sélection
                        if (selectedInfo && selectedTypeName) {
                            const typeLabel = this.querySelector('.card-title').textContent;
                            selectedTypeName.textContent = typeLabel;
                            selectedInfo.classList.remove('d-none');
                        }
                        
                        // Mettre à jour le type dans l'application
                        OrganisationApp.organisationType = radio.value;
                        
                        // Déclencher la validation
                        validateStep1();
                    }
                });
            });
        }
        
        // ========================================
        // GESTION DES FONDATEURS
        // ========================================
        
        /**
         * Ajouter un fondateur
         */
        function addFondateur() {
            const formData = {
                civilite: document.getElementById('fondateur_civilite')?.value || '',
                nom: document.getElementById('fondateur_nom')?.value?.trim() || '',
                prenom: document.getElementById('fondateur_prenom')?.value?.trim() || '',
                nip: document.getElementById('fondateur_nip')?.value?.trim() || '',
                fonction: document.getElementById('fondateur_fonction')?.value || '',
                telephone: document.getElementById('fondateur_telephone')?.value?.trim() || '',
                email: document.getElementById('fondateur_email')?.value?.trim() || ''
            };
            
            // Validation
            if (!formData.nom || !formData.prenom || !formData.nip) {
                showNotification('Nom, prénom et NIP sont obligatoires', 'warning');
                return;
            }
            
            // Vérifier les doublons
            if (OrganisationApp.foundateurs.some(f => f.nip === formData.nip)) {
                showNotification('Ce NIP existe déjà dans la liste des fondateurs', 'warning');
                return;
            }
            
            // Ajouter le fondateur
            const fondateur = {
                id: Date.now(),
                ...formData,
                dateAjout: new Date().toISOString()
            };
            
            OrganisationApp.foundateurs.push(fondateur);
            updateFoundersList();
            clearFoundersForm();
            
            showNotification(`Fondateur ${fondateur.prenom} ${fondateur.nom} ajouté`, 'success');
        }
        
        /**
         * Mettre à jour la liste des fondateurs
         */
        function updateFoundersList() {
            const listContainer = document.getElementById('fondateurs_list');
            const countElement = document.getElementById('fondateurs_count');
            
            if (countElement) {
                countElement.textContent = `${OrganisationApp.foundateurs.length} fondateur(s)`;
            }
            
            if (!listContainer) return;
            
            if (OrganisationApp.foundateurs.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>Aucun fondateur ajouté</p>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="table-responsive"><table class="table table-hover">';
            html += `
                <thead class="table-dark">
                    <tr>
                        <th>Civilité</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>NIP</th>
                        <th>Fonction</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
            `;
            
            OrganisationApp.foundateurs.forEach((fondateur, index) => {
                html += `
                    <tr>
                        <td>${fondateur.civilite}</td>
                        <td><strong>${fondateur.nom}</strong></td>
                        <td>${fondateur.prenom}</td>
                        <td><code>${fondateur.nip}</code></td>
                        <td>${fondateur.fonction || '-'}</td>
                        <td>${fondateur.telephone || '-'}</td>
                        <td>
                            <button class="btn btn-outline-danger btn-sm" onclick="removeFondateur(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            listContainer.innerHTML = html;
        }
        
        /**
         * Supprimer un fondateur (avec sauvegarde automatique)
         */
        function removeFondateur(index) {
            if (confirm('Supprimer ce fondateur ?')) {
                const fondateur = OrganisationApp.foundateurs[index];
                OrganisationApp.foundateurs.splice(index, 1);
                updateFoundersList();
                
                // Sauvegarder automatiquement
                saveToCache();
                
                showNotification(`Fondateur ${fondateur.prenom} ${fondateur.nom} supprimé`, 'success');
            }
        }
        
        /**
         * Vider le formulaire des fondateurs
         */
        function clearFoundersForm() {
            const inputs = ['fondateur_civilite', 'fondateur_nom', 'fondateur_prenom', 
                           'fondateur_nip', 'fondateur_fonction', 'fondateur_telephone', 'fondateur_email'];
            
            inputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input) {
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                }
            });
        }
        
        // ========================================
        // INITIALISATION
        // ========================================
        
        /**
         * Initialisation de l'application
         */
        function initApplication() {
            console.log('🚀 Initialisation OrganisationApp v2.0 (8 étapes)');
            
            // Initialiser l'affichage
            updateStepDisplay();
            updateNavigationButtons();
            
            // Initialiser les cartes de type
            initOrganizationTypeCards();
            
            // Événements des boutons
            const addFondateurBtn = document.getElementById('addFondateurBtn');
            if (addFondateurBtn) {
                addFondateurBtn.addEventListener('click', addFondateur);
            }
            
            // Événements des provinces/départements
            const provinceSelect = document.getElementById('org_province');
            if (provinceSelect) {
                provinceSelect.addEventListener('change', updateDepartements);
            }
            
            console.log('✅ Application initialisée avec succès');
        }
        
        /**
         * Mettre à jour les départements selon la province
         */
        function updateDepartements() {
            const provinceSelect = document.getElementById('org_province');
            const departementSelect = document.getElementById('org_departement');
            
            if (!provinceSelect || !departementSelect) return;
            
            const departements = {
                'Estuaire': ['Libreville', 'Ntoum', 'Komo-Mondah', 'Komo', 'Noya'],
                'Haut-Ogooué': ['Franceville', 'Bongoville', 'Bakoumba', 'Akiéni', 'Lékoko'],
                'Moyen-Ogooué': ['Lambaréné', 'Ndjolé', 'Ogooué et des Lacs', 'Abanga-Bigné'],
                'Ngounié': ['Mouila', 'Fougamou', 'Mandji', 'Tsamba-Magotsi', 'Dola'],
                'Nyanga': ['Tchibanga', 'Mayumba', 'Basse-Banio', 'Haute-Banio', 'Mongo'],
                'Ogooué-Ivindo': ['Makokou', 'Mékambo', 'Booué', 'Ivindo', 'Lopé'],
                'Ogooué-Lolo': ['Koulamoutou', 'Lastoursville', 'Mulundu', 'Offoué-Onoye', 'Lombo-Bouenguidi'],
                'Ogooué-Maritime': ['Port-Gentil', 'Omboué', 'Bendjé', 'Etimboué', 'Ndougou'],
                'Woleu-Ntem': ['Oyem', 'Bitam', 'Mitzic', 'Woleu', 'Ntem', 'Okano', 'Haut-Ntem']
            };
            
            const selectedProvince = provinceSelect.value;
            const options = departements[selectedProvince] || [];
            
            departementSelect.innerHTML = '<option value="">Sélectionnez un département</option>';
            options.forEach(dept => {
                departementSelect.innerHTML += `<option value="${dept}">${dept}</option>`;
            });
        }
        
        /**
         * Obtenir la géolocalisation
         */
        function getGeolocation() {
            const btn = document.getElementById('getLocationBtn');
            const latInput = document.getElementById('org_latitude');
            const lngInput = document.getElementById('org_longitude');
            
            if (!navigator.geolocation) {
                showNotification('La géolocalisation n\'est pas supportée par ce navigateur', 'warning');
                return;
            }
            
            // Disable button and show loading
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Localisation...';
            }
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Vérifier si on est au Gabon (approximativement)
                    if (lat >= -3.978 && lat <= 2.318 && lng >= 8.695 && lng <= 14.502) {
                        if (latInput) latInput.value = lat.toFixed(6);
                        if (lngInput) lngInput.value = lng.toFixed(6);
                        
                        showNotification('Position obtenue avec succès', 'success');
                    } else {
                        showNotification('Position détectée hors du Gabon. Veuillez vérifier.', 'warning');
                        if (latInput) latInput.value = lat.toFixed(6);
                        if (lngInput) lngInput.value = lng.toFixed(6);
                    }
                },
                function(error) {
                    let message = 'Erreur de géolocalisation: ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            message += 'Permission refusée';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message += 'Position indisponible';
                            break;
                        case error.TIMEOUT:
                            message += 'Délai dépassé';
                            break;
                        default:
                            message += 'Erreur inconnue';
                            break;
                    }
                    showNotification(message, 'error');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
            
            // Restore button state
            if (btn) {
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i>Obtenir ma position actuelle';
                }, 3000);
            }
        }
        
        /**
         * Validation avancée des champs
         */
        function validateFieldsAdvanced() {
            // Validation email
            const emailInputs = document.querySelectorAll('input[type="email"]');
            emailInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value && !isValidEmail(this.value)) {
                        this.classList.add('is-invalid');
                        showFieldError(this, 'Format email invalide');
                    } else {
                        this.classList.remove('is-invalid');
                        clearFieldError(this);
                    }
                });
            });
            
            // Validation téléphone
            const phoneInputs = document.querySelectorAll('input[type="tel"]');
            phoneInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value && !isValidGabonPhone(this.value)) {
                        this.classList.add('is-invalid');
                        showFieldError(this, 'Numéro de téléphone gabonais invalide');
                    } else {
                        this.classList.remove('is-invalid');
                        clearFieldError(this);
                    }
                });
            });
            
            // Validation dates
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.id === 'demandeur_date_naissance') {
                        const age = calculateAge(new Date(this.value));
                        if (age < 18) {
                            this.classList.add('is-invalid');
                            showFieldError(this, 'Le demandeur doit être majeur (18 ans minimum)');
                        } else {
                            this.classList.remove('is-invalid');
                            clearFieldError(this);
                        }
                    }
                });
            });
        }
        
        /**
         * Valider email
         */
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        /**
         * Valider téléphone gabonais
         */
        function isValidGabonPhone(phone) {
            // Format gabonais : 8-9 chiffres
            const phoneRegex = /^[0-9]{8,9}$/;
            return phoneRegex.test(phone.replace(/\s/g, ''));
        }
        
        /**
         * Calculer l'âge
         */
        function calculateAge(birthDate) {
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age;
        }
        
        /**
         * Afficher erreur de champ
         */
        function showFieldError(field, message) {
            if (!field) return;
            
            // Supprimer l'ancienne erreur
            clearFieldError(field);
            
            // Créer le message d'erreur
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            
            // Ajouter après le champ
            field.parentNode.insertBefore(errorDiv, field.nextSibling);
        }
        
        /**
         * Supprimer erreur de champ
         */
        function clearFieldError(field) {
            if (!field) return;
            
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
        
        // ========================================
        // SYSTÈME DE CACHE/SAUVEGARDE AUTOMATIQUE
        // ========================================
        
        /**
         * Sauvegarder les données dans le cache
         */
        function saveToCache() {
            if (!OrganisationApp.cacheConfig.enabled) return;
            
            try {
                const cacheData = {
                    currentStep: OrganisationApp.currentStep,
                    organisationType: OrganisationApp.organisationType,
                    formData: OrganisationApp.formData,
                    foundateurs: OrganisationApp.foundateurs,
                    uploadedDocuments: OrganisationApp.uploadedDocuments,
                    timestamp: new Date().toISOString(),
                    expiresAt: new Date(Date.now() + OrganisationApp.cacheConfig.expirationHours * 60 * 60 * 1000).toISOString()
                };
                
                // Collecter les données des formulaires
                collectAllFormData();
                cacheData.formData = OrganisationApp.formData;
                
                const cacheString = JSON.stringify(cacheData);
                
                // Vérifier la taille du cache
                if (cacheString.length > OrganisationApp.cacheConfig.maxCacheSize) {
                    console.warn('Cache trop volumineux, sauvegarde partielle');
                    // Sauvegarder seulement les données essentielles
                    const essentialData = {
                        currentStep: cacheData.currentStep,
                        organisationType: cacheData.organisationType,
                        formData: filterEssentialFormData(cacheData.formData),
                        foundateurs: cacheData.foundateurs,
                        timestamp: cacheData.timestamp,
                        expiresAt: cacheData.expiresAt
                    };
                    localStorage.setItem(OrganisationApp.cacheConfig.keyPrefix + 'data', JSON.stringify(essentialData));
                } else {
                    localStorage.setItem(OrganisationApp.cacheConfig.keyPrefix + 'data', cacheString);
                }
                
                // Mettre à jour l'indicateur de sauvegarde
                updateSaveIndicator('saved');
                
                console.log('💾 Données sauvegardées dans le cache', new Date().toLocaleTimeString());
                
            } catch (error) {
                console.error('❌ Erreur sauvegarde cache:', error);
                if (error.name === 'QuotaExceededError') {
                    showNotification('Espace de stockage insuffisant. Données partiellement sauvegardées.', 'warning');
                    // Essayer de nettoyer l'ancien cache
                    clearExpiredCache();
                }
            }
        }
        
        /**
         * Charger les données depuis le cache
         */
        function loadFromCache() {
            if (!OrganisationApp.cacheConfig.enabled) return false;
            
            try {
                const cacheString = localStorage.getItem(OrganisationApp.cacheConfig.keyPrefix + 'data');
                if (!cacheString) return false;
                
                const cacheData = JSON.parse(cacheString);
                
                // Vérifier l'expiration
                if (new Date(cacheData.expiresAt) < new Date()) {
                    console.log('🗑️ Cache expiré, suppression');
                    clearCache();
                    return false;
                }
                
                // Confirmer avec l'utilisateur
                if (confirm('Des données non sauvegardées ont été trouvées. Voulez-vous les restaurer ?')) {
                    // Restaurer les données
                    OrganisationApp.currentStep = cacheData.currentStep || 1;
                    OrganisationApp.organisationType = cacheData.organisationType || null;
                    OrganisationApp.formData = cacheData.formData || {};
                    OrganisationApp.foundateurs = cacheData.foundateurs || [];
                    OrganisationApp.uploadedDocuments = cacheData.uploadedDocuments || {};
                    
                    // Restaurer les champs du formulaire
                    restoreFormFields();
                    
                    // Mettre à jour l'affichage
                    updateStepDisplay();
                    updateNavigationButtons();
                    updateFoundersList();
                    
                    // Restaurer le type d'organisation si défini
                    if (OrganisationApp.organisationType) {
                        const typeRadio = document.querySelector(`input[name="type_organisation"][value="${OrganisationApp.organisationType}"]`);
                        if (typeRadio) {
                            typeRadio.checked = true;
                            typeRadio.dispatchEvent(new Event('change'));
                        }
                    }
                    
                    showNotification('Données restaurées avec succès', 'success');
                    updateSaveIndicator('restored');
                    
                    console.log('📥 Données restaurées depuis le cache', cacheData.timestamp);
                    return true;
                } else {
                    clearCache();
                    return false;
                }
                
            } catch (error) {
                console.error('❌ Erreur chargement cache:', error);
                clearCache(); // Nettoyer en cas d'erreur
                return false;
            }
        }
        
        /**
         * Collecter toutes les données du formulaire
         */
        function collectAllFormData() {
            const form = document.getElementById('organisationForm');
            if (!form) return;
            
            // Collecter tous les inputs, selects et textareas
            const elements = form.querySelectorAll('input, select, textarea');
            
            elements.forEach(element => {
                const name = element.name || element.id;
                if (!name) return;
                
                if (element.type === 'radio') {
                    if (element.checked) {
                        OrganisationApp.formData[name] = element.value;
                    }
                } else if (element.type === 'checkbox') {
                    OrganisationApp.formData[name] = element.checked;
                } else if (element.type === 'file') {
                    // Pour les fichiers, on ne peut pas les sauvegarder en localStorage
                    // On sauvegarde juste l'info qu'un fichier était sélectionné
                    if (element.files && element.files.length > 0) {
                        OrganisationApp.formData[name] = {
                            hasFile: true,
                            fileName: element.files[0].name,
                            fileSize: element.files[0].size
                        };
                    }
                } else {
                    OrganisationApp.formData[name] = element.value;
                }
            });
        }
        
        /**
         * Restaurer les champs du formulaire
         */
        function restoreFormFields() {
            const form = document.getElementById('organisationForm');
            if (!form || !OrganisationApp.formData) return;
            
            Object.keys(OrganisationApp.formData).forEach(name => {
                const element = form.querySelector(`[name="${name}"], #${name}`);
                if (!element) return;
                
                const value = OrganisationApp.formData[name];
                
                if (element.type === 'radio') {
                    if (element.value === value) {
                        element.checked = true;
                    }
                } else if (element.type === 'checkbox') {
                    element.checked = !!value;
                } else if (element.type === 'file') {
                    // On ne peut pas restaurer les fichiers, mais on peut indiquer qu'il y en avait
                    if (value && value.hasFile) {
                        const statusElement = document.getElementById(`status_${name.replace('documents[', '').replace(']', '')}`);
                        if (statusElement) {
                            statusElement.innerHTML = '<i class="fas fa-info me-1 text-info"></i>Fichier précédemment sélectionné (à re-sélectionner)';
                        }
                    }
                } else {
                    element.value = value || '';
                }
            });
        }
        
        /**
         * Filtrer les données essentielles pour économiser l'espace
         */
        function filterEssentialFormData(formData) {
            const essential = {};
            const essentialFields = [
                'type_organisation', 'demandeur_nom', 'demandeur_prenom', 'demandeur_nip',
                'demandeur_email', 'demandeur_telephone', 'org_nom', 'org_objet',
                'org_province', 'org_prefecture', 'org_adresse_complete'
            ];
            
            essentialFields.forEach(field => {
                if (formData[field]) {
                    essential[field] = formData[field];
                }
            });
            
            return essential;
        }
        
        /**
         * Nettoyer le cache
         */
        function clearCache() {
            try {
                localStorage.removeItem(OrganisationApp.cacheConfig.keyPrefix + 'data');
                console.log('🗑️ Cache nettoyé');
                updateSaveIndicator('cleared');
            } catch (error) {
                console.error('❌ Erreur nettoyage cache:', error);
            }
        }
        
        /**
         * Nettoyer les caches expirés
         */
        function clearExpiredCache() {
            try {
                // Nettoyer tous les caches de formulaire expirés
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && key.startsWith(OrganisationApp.cacheConfig.keyPrefix)) {
                        try {
                            const data = JSON.parse(localStorage.getItem(key));
                            if (data.expiresAt && new Date(data.expiresAt) < new Date()) {
                                localStorage.removeItem(key);
                                console.log('🗑️ Cache expiré supprimé:', key);
                            }
                        } catch (e) {
                            // Supprimer les caches corrompus
                            localStorage.removeItem(key);
                        }
                    }
                }
            } catch (error) {
                console.error('❌ Erreur nettoyage caches expirés:', error);
            }
        }
        
        /**
         * Mettre à jour l'indicateur de sauvegarde
         */
        function updateSaveIndicator(status) {
            const indicator = document.getElementById('save-indicator');
            if (!indicator) return;
            
            const now = new Date().toLocaleTimeString();
            
            switch (status) {
                case 'saving':
                    indicator.innerHTML = '<i class="fas fa-spinner fa-spin text-info"></i> Sauvegarde...';
                    break;
                case 'saved':
                    indicator.innerHTML = `<i class="fas fa-check-circle text-success"></i> Sauvegardé automatiquement à ${now}`;
                    break;
                case 'restored':
                    indicator.innerHTML = `<i class="fas fa-history text-info"></i> Données restaurées à ${now}`;
                    break;
                case 'error':
                    indicator.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i> Erreur de sauvegarde';
                    break;
                case 'cleared':
                    indicator.innerHTML = '<i class="fas fa-trash text-muted"></i> Cache nettoyé';
                    break;
                default:
                    indicator.innerHTML = '<i class="fas fa-circle text-muted"></i> En attente';
            }
        }
        
        /**
         * Auto-sauvegarde périodique
         */
        function setupAutoSave() {
            // Sauvegarde automatique
            setInterval(() => {
                if (OrganisationApp.currentStep > 1) {
                    updateSaveIndicator('saving');
                    setTimeout(() => {
                        saveToCache();
                    }, 100);
                }
            }, OrganisationApp.cacheConfig.autoSaveInterval);
            
            // Sauvegarde lors de changements importants
            document.addEventListener('input', debounce(() => {
                if (OrganisationApp.currentStep > 1) {
                    saveToCache();
                }
            }, 1000));
            
            // Sauvegarde avant fermeture de page
            window.addEventListener('beforeunload', (e) => {
                saveToCache();
                
                // Avertir l'utilisateur s'il y a des données non sauvegardées définitivement
                if (OrganisationApp.currentStep > 1 && !OrganisationApp.isSubmitting) {
                    e.preventDefault();
                    e.returnValue = 'Vos modifications seront sauvegardées automatiquement, mais le dossier ne sera pas soumis. Continuer ?';
                    return e.returnValue;
                }
            });
        }
        
        /**
         * Fonction debounce pour limiter les appels
         */
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        /**
         * Sauvegarder manuellement (bouton)
         */
        function saveManually() {
            updateSaveIndicator('saving');
            collectAllFormData();
            saveToCache();
            showNotification('Données sauvegardées manuellement', 'success');
        }
        
        /**
         * Nettoyer manuellement (bouton)
         */
        function clearCacheManually() {
            if (confirm('Êtes-vous sûr de vouloir supprimer toutes les données sauvegardées ?')) {
                clearCache();
                showNotification('Cache nettoyé', 'info');
            }
        }
        
        /**
         * Vérification de connectivité
         */
        function checkConnectivity() {
            if (!navigator.onLine) {
                showNotification('Connexion internet indisponible. Vos données sont sauvegardées localement.', 'warning');
            }
        }
        
        /**
         * Initialisation complète de l'application
         */
        function initApplicationComplete() {
            console.log('🚀 Initialisation complète OrganisationApp v2.0 (8 étapes)');
            
            // Nettoyer les caches expirés au démarrage
            clearExpiredCache();
            
            // Essayer de charger depuis le cache
            const cacheLoaded = loadFromCache();
            
            if (!cacheLoaded) {
                // Initialiser l'affichage par défaut
                updateStepDisplay();
                updateNavigationButtons();
            }
            
            // Initialiser les cartes de type
            initOrganizationTypeCards();
            
            // Événements des boutons fondateurs
            const addFondateurBtn = document.getElementById('addFondateurBtn');
            if (addFondateurBtn) {
                addFondateurBtn.addEventListener('click', addFondateur);
            }
            
            // Événements géolocalisation
            const getLocationBtn = document.getElementById('getLocationBtn');
            if (getLocationBtn) {
                getLocationBtn.addEventListener('click', getGeolocation);
            }
            
            // Événements des provinces/départements
            const provinceSelect = document.getElementById('org_province');
            if (provinceSelect) {
                provinceSelect.addEventListener('change', updateDepartements);
            }
            
            // Validation avancée des champs
            validateFieldsAdvanced();
            
            // Auto-sauvegarde
            setupAutoSave();
            
            // Ajouter boutons de gestion cache dans l'interface
            addCacheManagementButtons();
            
            // Vérification connectivité
            window.addEventListener('online', () => showNotification('Connexion rétablie', 'success'));
            window.addEventListener('offline', checkConnectivity);
            
            console.log('✅ Application initialisée avec succès - Toutes fonctionnalités actives + Cache');
        }
        
        /**
         * Ajouter les boutons de gestion du cache
         */
        function addCacheManagementButtons() {
            const saveIndicator = document.getElementById('save-indicator');
            if (saveIndicator && saveIndicator.parentNode) {
                const buttonsHtml = `
                    <div class="btn-group btn-group-sm ms-2" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="saveManually()" title="Sauvegarder maintenant">
                            <i class="fas fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearCacheManually()" title="Vider le cache">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                
                // Créer un conteneur pour l'indicateur et les boutons
                const container = document.createElement('div');
                container.className = 'd-flex align-items-center justify-content-end';
                container.innerHTML = `<div id="save-indicator-moved"></div>${buttonsHtml}`;
                
                // Déplacer l'indicateur dans le nouveau conteneur
                const movedIndicator = container.querySelector('#save-indicator-moved');
                movedIndicator.appendChild(saveIndicator);
                
                // Remplacer dans le DOM
                saveIndicator.parentNode.parentNode.replaceChild(container, saveIndicator.parentNode);
            }
        }
        
        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', initApplicationComplete);
        
        // Fonctions globales pour les boutons et événements
        window.changeStep = changeStep;
        window.removeFondateur = removeFondateur;
        window.handleDocumentUpload = handleDocumentUpload;
        window.saveManually = saveManually;
        window.clearCacheManually = clearCacheManually;
        
        // Fonctions utilitaires globales
        window.OrganisationUtils = {
            showNotification: showNotification,
            getOrganizationTypeLabel: getOrganizationTypeLabel,
            validateStep: validateStep,
            updateStepDisplay: updateStepDisplay,
            saveToCache: saveToCache,
            loadFromCache: loadFromCache,
            clearCache: clearCache
        };
        
        console.log('📋 Script principal chargé (8 étapes) avec gestion documents et cache complet');
    </script>
    
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
        });
    </script>

    <!-- INTÉGRATION WORKFLOW 2 PHASES -->
    <script>
    // Initialisation du workflow 2 phases
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔧 Initialisation workflow 2 phases...');
        
        // Vérifier si le module est chargé
        if (window.Workflow2Phases) {
            try {
                const workflow2PhasesInit = window.Workflow2Phases.init();
                console.log('✅ Workflow 2 phases initialisé:', workflow2PhasesInit);
            } catch (error) {
                console.error('❌ Erreur initialisation workflow 2 phases:', error);
            }
        } else {
            console.warn('⚠️ Module Workflow2Phases non trouvé');
        }
    });

    // Fonction submitPhase1 globale
    window.submitPhase1 = function() {
        console.log('🚀 submitPhase1() appelée');
        
        if (window.Workflow2Phases) {
            try {
                return window.Workflow2Phases.submitPhase1();
            } catch (error) {
                console.error('❌ Erreur submitPhase1:', error);
                showNotification('Erreur lors de la soumission Phase 1: ' + error.message, 'error');
            }
        } else {
            console.error('❌ Workflow2Phases non disponible pour submitPhase1');
            
            // Fallback : utiliser la soumission normale
            if (window.submitForm) {
                console.log('🔄 Fallback vers submitForm normale');
                return window.submitForm();
            } else {
                showNotification('Erreur: Module workflow 2 phases non chargé', 'error');
            }
        }
    };
    </script>
@endpush