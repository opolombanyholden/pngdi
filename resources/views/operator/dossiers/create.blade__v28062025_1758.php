@extends('layouts.operator')

@section('title', 'Créer une Organisation')
@section('page-title', 'Nouvelle Organisation')

@section('content')
<!-- ========== SECTION A - HEADER ET NAVIGATION (Lignes 1-50) ========== -->
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
                                        <a href="{{ route('operator.dossiers.index') }}" class="text-white opacity-75">Dossiers</a>
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
                                <a href="{{ route('operator.dossiers.index') }}" class="btn btn-light">
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
                <form id="organisationForm" action="{{ route('operator.dossiers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- ========== SECTION B - ÉTAPE 1 : CHOIX DU TYPE D'ORGANISATION (Lignes 51-200) ========== -->
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

                                    <!-- Contraintes spéciales selon le type -->
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-md-10">
                                            <div class="alert alert-info border-0" id="typeConstraints" style="display: none;">
                                                <h6 class="alert-heading">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Contraintes importantes à retenir
                                                </h6>
                                                <div id="constraintContent"></div>
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

                        <!-- ========== SECTION C - ÉTAPE 2 : GUIDE SPÉCIFIQUE (Lignes 201-400) ========== -->
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
                                    
                                    <!-- Guide Association -->
                                    <div class="guide-content d-none" id="guide-association">
                                        <div class="alert alert-primary border-0 mb-4 shadow-sm">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-handshake fa-3x me-3 text-primary"></i>
                                                <div>
                                                    <h5 class="alert-heading mb-1">Guide pour créer une Association au Gabon</h5>
                                                    <p class="mb-0">Procédures légales selon la législation gabonaise en vigueur</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="accordion" id="guideAssociationAccordion">
                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#assoc-definition">
                                                        <i class="fas fa-info-circle me-2 text-primary"></i>
                                                        Définition légale de l'association
                                                    </button>
                                                </h2>
                                                <div id="assoc-definition" class="accordion-collapse collapse show" data-bs-parent="#guideAssociationAccordion">
                                                    <div class="accordion-body">
                                                        <p>Selon la loi gabonaise, une association est un groupement de personnes volontaires réunies autour d'un projet commun ou partageant des activités, sans chercher à réaliser des bénéfices à redistribuer.</p>
                                                        <div class="row mt-3">
                                                            <div class="col-md-6">
                                                                <h6 class="text-success"><i class="fas fa-check me-2"></i>Caractéristiques obligatoires</h6>
                                                                <ul class="list-unstyled">
                                                                    <li>• But exclusivement non lucratif</li>
                                                                    <li>• Minimum 3 membres fondateurs majeurs</li>
                                                                    <li>• Siège social au Gabon</li>
                                                                    <li>• Objet social licite et déterminé</li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-info"><i class="fas fa-gavel me-2"></i>Obligations légales</h6>
                                                                <ul class="list-unstyled">
                                                                    <li>• Assemblée générale annuelle obligatoire</li>
                                                                    <li>• Tenue d'une comptabilité transparente</li>
                                                                    <li>• Rapport d'activité annuel</li>
                                                                    <li>• Respect des statuts adoptés</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#assoc-conditions">
                                                        <i class="fas fa-list-check me-2 text-primary"></i>
                                                        Conditions d'éligibilité
                                                    </button>
                                                </h2>
                                                <div id="assoc-conditions" class="accordion-collapse collapse" data-bs-parent="#guideAssociationAccordion">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="text-primary">Fondateurs</h6>
                                                                <ul>
                                                                    <li><strong>Minimum 3 personnes</strong> physiques majeures</li>
                                                                    <li>Nationalité gabonaise ou titre de séjour valide</li>
                                                                    <li>Jouissance des droits civiques</li>
                                                                    <li>Casier judiciaire vierge recommandé</li>
                                                                </ul>
                                                                
                                                                <h6 class="text-primary mt-3">Adhérents</h6>
                                                                <ul>
                                                                    <li><strong>Minimum 10 adhérents</strong> à la création</li>
                                                                    <li>Liste nominative avec signatures</li>
                                                                    <li>NIP (Numéro d'Identification Personnel) obligatoire</li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-primary">Siège social</h6>
                                                                <ul>
                                                                    <li>Adresse physique au Gabon</li>
                                                                    <li>Justificatif de domiciliation</li>
                                                                    <li>Accessibilité pour les contrôles</li>
                                                                </ul>
                                                                
                                                                <h6 class="text-primary mt-3">Objet social</h6>
                                                                <ul>
                                                                    <li>Activités licites et conformes aux lois</li>
                                                                    <li>But non lucratif clairement défini</li>
                                                                    <li>Aucune atteinte à l'ordre public</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Guide ONG (Plus de contenu...) -->
                                    <!-- Guide Parti Politique (Plus de contenu...) -->
                                    <!-- Guide Confession Religieuse (Plus de contenu...) -->

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
                                            Cette confirmation est obligatoire pour passer à l'étape suivante. Elle atteste de votre connaissance des obligations légales.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION D - ÉTAPE 3 : INFORMATIONS DEMANDEUR (Lignes 401-650) ========== -->
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
                                                <!-- NIP -->
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
                                                               placeholder="13 chiffres (ex: 1234567890123)"
                                                               maxlength="13"
                                                               pattern="[0-9]{13}"
                                                               required>
                                                        <span class="input-group-text">
                                                            <i class="fas fa-spinner fa-spin d-none" id="nip-loading"></i>
                                                            <i class="fas fa-check text-success d-none" id="nip-valid"></i>
                                                            <i class="fas fa-times text-danger d-none" id="nip-invalid"></i>
                                                        </span>
                                                    </div>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Le NIP gabonais comporte exactement 13 chiffres
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

                                                <!-- Plus de champs... -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION E - ÉTAPE 4 : INFORMATIONS ORGANISATION (Lignes 651-900) ========== -->
                        <!-- ========== SECTION F - ÉTAPE 5 : COORDONNÉES GÉOLOCALISATION (Lignes 901-1200) ========== -->
                        <!-- ========== SECTION G - ÉTAPE 6 : FONDATEURS (Lignes 1201-1500) ========== -->
                        <!-- ========== SECTION H - ÉTAPE 7 : ADHÉRENTS (Lignes 1501-1800) ========== -->
                        <!-- ========== SECTION I - ÉTAPE 8 : DOCUMENTS (Lignes 1801-2100) ========== -->
                        <!-- ========== SECTION J - ÉTAPE 9 : SOUMISSION FINALE (Lignes 2101-2400) ========== -->

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

<!-- ========== SECTION N - STYLES CSS (Lignes 2401-3200) ========== -->
<style>
/* Variables CSS pour la thématique gabonaise */
:root {
    --gabon-green: #009e3f;
    --gabon-yellow: #ffcd00;
    --gabon-blue: #003f7f;
    --primary-gradient: linear-gradient(135deg, #009e3f 0%, #00b347 100%);
    --success-color: #28a745;
    --info-color: #17a2b8;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
}

/* Icônes d'étapes */
.step-icon-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Indicateurs d'étapes */
.step-indicators {
    display: flex;
    justify-content: space-between;
}

.step-indicator {
    opacity: 0.5;
    transition: all 0.3s ease;
    text-align: center;
    cursor: pointer;
}

.step-indicator.active {
    opacity: 1;
}

.step-indicator.completed {
    opacity: 1;
}

.step-indicator.completed .step-icon {
    color: var(--gabon-green) !important;
}

.step-icon {
    font-size: 1.2rem;
    color: #6c757d;
    transition: color 0.3s ease;
}

.step-indicator.active .step-icon {
    color: var(--gabon-green);
}

/* Cards type d'organisation */
.organization-type-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid #e9ecef;
}

.organization-type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 158, 63, 0.15);
}

.organization-type-card.active {
    border-color: var(--gabon-green) !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 158, 63, 0.25);
}

.org-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Progress bar */
.progress-bar {
    transition: width 0.5s ease;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.step-content {
    animation: fadeInUp 0.6s ease-out;
}

/* Form styling */
.form-control:focus, .form-select:focus {
    border-color: var(--gabon-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 158, 63, 0.25);
}

.form-control.is-valid, .form-select.is-valid {
    border-color: var(--gabon-green);
}

.btn-success {
    background: var(--primary-gradient);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #00b347 0%, #009e3f 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 158, 63, 0.3);
}

/* Required field indicator */
.required::after {
    content: " *";
    color: var(--danger-color);
}

/* Responsive design */
@media (max-width: 768px) {
    .step-indicators {
        flex-wrap: wrap;
    }
    .step-indicators .col {
        flex-basis: 20%;
        margin-bottom: 10px;
        font-size: 0.8rem;
    }
    
    .step-icon {
        font-size: 1rem;
    }
    
    .step-icon-large {
        width: 60px;
        height: 60px;
    }
    
    .organization-type-card {
        margin-bottom: 20px;
    }
}

/* Modal styling */
.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

/* Toast notifications */
.toast {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Loading states */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Accordion customization */
.accordion-button:not(.collapsed) {
    background-color: rgba(0, 158, 63, 0.1);
    color: var(--gabon-green);
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(0, 158, 63, 0.25);
}

/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--gabon-green);
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #e9ecef;
}

/* Error states */
.field-error {
    border-color: var(--danger-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Success states */
.field-success {
    border-color: var(--success-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

/* Utility classes */
.text-purple {
    color: #6f42c1 !important;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.border-gabon {
    border-color: var(--gabon-green) !important;
}

.text-gabon {
    color: var(--gabon-green) !important;
}

.bg-gabon {
    background-color: var(--gabon-green) !important;
}

/* Print styles */
@media print {
    .btn, .card-footer, .step-indicators, .modal {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .step-content {
        display: block !important;
    }
    
    body {
        background: white !important;
    }
}
</style>

<!-- À ajouter AVANT la Section O (vers la ligne 3200) -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- ========== SECTION O - CONFIGURATION BACKEND (Lignes 3201-3800) ========== -->
<!-- Meta tags pour CSRF et configuration -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="app-url" content="{{ config('app.url') }}">
<meta name="app-env" content="{{ config('app.env') }}">

<!-- Configuration JavaScript -->
<script>
// Configuration globale pour l'application
window.appConfig = {
    baseUrl: '{{ config("app.url") }}',
    csrfToken: '{{ csrf_token() }}',
    uploadMaxSize: {{ config('organization.upload_max_size', 5242880) }}, // 5MB par défaut
    allowedTypes: {!! json_encode(config('organization.allowed_file_types', ['pdf', 'jpg', 'jpeg', 'png'])) !!},
    gabonProvinces: {!! json_encode(config('gabon.provinces', [])) !!},
    organizationTypes: {!! json_encode(config('organization.types', [])) !!},
    validationRules: {!! json_encode(config('organization.validation_rules', [])) !!}
};

// Configuration des routes API
window.apiRoutes = {
    verifyNip: '{{ route("api.verify-nip") }}',
    verifyOrganizationName: '{{ route("api.verify-organization-name") }}',
    verifyMembers: '{{ route("api.verify-members") }}',
    uploadDocument: '{{ route("api.upload-document") }}',
    saveDraft: '{{ route("api.save-draft") }}',
    loadDraft: function(id) { return '{{ route("api.load-draft", ":id") }}'.replace(':id', id); },
    formAnalytics: '{{ route("api.form-analytics") }}',
    submitOrganization: '{{ route("operator.dossiers.store") }}'
};

// Configuration des types d'organisation
window.orgTypeConfig = {
    association: {
        minFondateurs: 3,
        minAdherents: 10,
        label: 'Association',
        color: 'success',
        requiredDocs: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege']
    },
    ong: {
        minFondateurs: 5,
        minAdherents: 15,
        label: 'ONG',
        color: 'info',
        requiredDocs: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'projet_social', 'budget_previsionnel', 'cv_fondateurs']
    },
    parti_politique: {
        minFondateurs: 3,
        minAdherents: 50,
        label: 'Parti Politique',
        color: 'warning',
        requiredDocs: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'programme_politique', 'liste_50_adherents', 'repartition_geo', 'sources_financement']
    },
    confession_religieuse: {
        minFondateurs: 3,
        minAdherents: 10,
        label: 'Confession Religieuse',
        color: 'purple',
        requiredDocs: ['statuts', 'pv_ag', 'liste_fondateurs', 'justif_siege', 'expose_doctrine', 'justif_lieu_culte', 'attestation_responsable', 'liste_fideles']
    }
};

// Initialisation des variables globales
window.currentStep = 1;
window.totalSteps = 9;
window.selectedOrgType = '';
window.fondateursCount = 0;
window.adherentsData = [];
window.documentsUploaded = {};

// Configuration Axios pour Laravel
if (typeof axios !== 'undefined') {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = window.appConfig.csrfToken;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['Accept'] = 'application/json';
}


// ========================================================================
// SECTION O - JAVASCRIPT COMPLET FINALISÉ (Lignes 3801-5000+)
// PNGDI - Création d'Organisation - Version Finale
// ========================================================================

// ============================================
// 1. CONFIGURATION BACKEND AVANCÉE
// ============================================

// Configuration des endpoints API basée sur l'audit des contrôleurs
const apiConfig = {
    endpoints: {
        // Vérifications en temps réel
        verifyNip: '/api/v1/verify-nip',
        verifyOrgName: '/api/v1/verify-organization-name', 
        verifyMembers: '/api/v1/verify-members',
        
        // Gestion documents
        uploadDocument: '/api/v1/upload-document',
        previewDocument: '/api/v1/preview-document',
        deleteDocument: '/api/v1/delete-document',
        
        // Système de brouillons
        saveDraft: '/api/v1/save-draft',
        loadDraft: '/api/v1/load-draft',
        deleteDraft: '/api/v1/delete-draft',
        
        // Analytics et suivi
        formAnalytics: '/api/v1/form-analytics',
        stepTracking: '/api/v1/step-tracking',
        
        // Soumission finale
        submitOrganization: '{{ route("operator.dossiers.store") }}',
        validateBeforeSubmit: '/api/v1/validate-complete-form'
    },
    
    // Configuration du retry automatique
    retry: {
        maxAttempts: 3,
        baseDelay: 1000, // 1 seconde
        maxDelay: 10000, // 10 secondes
        backoffFactor: 2
    },
    
    // Configuration timeout
    timeout: 30000, // 30 secondes
    
    // Headers par défaut
    defaultHeaders: {
        'X-CSRF-TOKEN': window.appConfig.csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
};

// ============================================
// 2. CLIENT HTTP AVEC RETRY ET GESTION D'ERREURS
// ============================================

class ApiClient {
    constructor(config = apiConfig) {
        this.config = config;
        this.loadingIndicators = new Set();
        this.requestCache = new Map();
        
        // Configuration Axios avec intercepteurs
        this.client = axios.create({
            timeout: config.timeout,
            headers: config.defaultHeaders
        });
        
        this.setupInterceptors();
    }
    
    /**
     * Configuration des intercepteurs Axios
     */
    setupInterceptors() {
        // Intercepteur de requête
        this.client.interceptors.request.use(
            (config) => {
                this.showGlobalLoading();
                return config;
            },
            (error) => {
                this.hideGlobalLoading();
                return Promise.reject(error);
            }
        );
        
        // Intercepteur de réponse
        this.client.interceptors.response.use(
            (response) => {
                this.hideGlobalLoading();
                return response;
            },
            (error) => {
                this.hideGlobalLoading();
                return this.handleErrorResponse(error);
            }
        );
    }
    
    /**
     * Méthode principale avec retry automatique
     */
    async request(method, url, data = null, options = {}) {
        const { maxAttempts, baseDelay, maxDelay, backoffFactor } = this.config.retry;
        
        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            try {
                const response = await this.client({
                    method,
                    url,
                    data,
                    ...options
                });
                
                return response.data;
                
            } catch (error) {
                console.warn(`Tentative ${attempt}/${maxAttempts} échouée:`, error.message);
                
                // Ne pas retenter pour certaines erreurs
                if (this.shouldNotRetry(error) || attempt === maxAttempts) {
                    throw error;
                }
                
                // Calcul du délai avec backoff exponentiel
                const delay = Math.min(baseDelay * Math.pow(backoffFactor, attempt - 1), maxDelay);
                
                // Ajouter du jitter pour éviter l'effet thundering herd
                const jitter = Math.random() * 0.1 * delay;
                const finalDelay = delay + jitter;
                
                console.log(`Nouvelle tentative dans ${Math.round(finalDelay)}ms...`);
                await this.sleep(finalDelay);
            }
        }
    }
    
    /**
     * Gestion spécialisée des erreurs par code
     */
    handleErrorResponse(error) {
        const status = error.response?.status;
        const data = error.response?.data;
        
        switch (status) {
            case 401:
                this.handleUnauthorized();
                break;
                
            case 403:
                showNotification('Accès refusé. Vérifiez vos permissions.', 'danger');
                break;
                
            case 422:
                this.handleValidationErrors(data);
                break;
                
            case 429:
                showNotification('Trop de requêtes. Veuillez patienter...', 'warning');
                break;
                
            case 500:
                showNotification('Erreur serveur. Nos équipes ont été notifiées.', 'danger');
                break;
                
            default:
                if (!navigator.onLine) {
                    showNotification('Connexion internet interrompue', 'warning');
                } else {
                    showNotification('Erreur de communication avec le serveur', 'danger');
                }
        }
        
        return Promise.reject(error);
    }
    
    /**
     * Détermine si une erreur ne doit pas déclencher de retry
     */
    shouldNotRetry(error) {
        const status = error.response?.status;
        const noRetryStatuses = [400, 401, 403, 404, 422];
        return noRetryStatuses.includes(status);
    }
    
    /**
     * Gestion de l'autorisation expirée
     */
    handleUnauthorized() {
        showNotification('Session expirée. Redirection...', 'warning');
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
    }
    
    /**
     * Gestion des erreurs de validation
     */
    handleValidationErrors(data) {
        if (data.errors) {
            Object.keys(data.errors).forEach(field => {
                const fieldElement = document.querySelector(`[name="${field}"]`);
                if (fieldElement) {
                    fieldElement.classList.add('is-invalid');
                    this.showFieldError(fieldElement, data.errors[field][0]);
                }
            });
        }
        
        if (data.message) {
            showNotification(data.message, 'danger');
        }
    }
    
    /**
     * Affichage d'erreur sur un champ spécifique
     */
    showFieldError(field, message) {
        let errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
    
    /**
     * Indicateurs de chargement globaux
     */
    showGlobalLoading() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.classList.remove('d-none');
        }
    }
    
    hideGlobalLoading() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.classList.add('d-none');
        }
    }
    
    /**
     * Utilitaire sleep pour les délais
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    // Méthodes raccourcis
    async get(url, options = {}) {
        return this.request('GET', url, null, options);
    }
    
    async post(url, data = null, options = {}) {
        return this.request('POST', url, data, options);
    }
    
    async put(url, data = null, options = {}) {
        return this.request('PUT', url, data, options);
    }
    
    async delete(url, options = {}) {
        return this.request('DELETE', url, null, options);
    }
}

// Instance globale du client API
const apiClient = new ApiClient();

// ============================================
// 3. SERVICES API SPÉCIALISÉS
// ============================================

/**
 * Service de vérification en temps réel
 */
const verificationService = {
    // Cache local pour éviter les requêtes répétées
    cache: new Map(),
    cacheTTL: 5 * 60 * 1000, // 5 minutes
    
    /**
     * Vérification NIP avec cache intelligent
     */
    async verifyNip(nip) {
        const cacheKey = `nip_${nip}`;
        const cached = this.cache.get(cacheKey);
        
        // Vérifier le cache
        if (cached && Date.now() - cached.timestamp < this.cacheTTL) {
            return cached.data;
        }
        
        try {
            const result = await apiClient.post(apiConfig.endpoints.verifyNip, { nip });
            
            // Mettre en cache uniquement les succès
            if (result.success) {
                this.cache.set(cacheKey, {
                    data: result,
                    timestamp: Date.now()
                });
            }
            
            return result;
        } catch (error) {
            console.error('Erreur vérification NIP:', error);
            throw error;
        }
    },
    
    /**
     * Vérification nom organisation avec suggestions
     */
    async verifyOrganizationName(name, type) {
        try {
            const result = await apiClient.post(apiConfig.endpoints.verifyOrgName, { 
                name, 
                type,
                suggest_alternatives: true 
            });
            
            // Afficher les suggestions si disponibles
            if (result.suggestions && result.suggestions.length > 0) {
                this.showNameSuggestions(result.suggestions);
            }
            
            return result;
        } catch (error) {
            console.error('Erreur vérification nom organisation:', error);
            throw error;
        }
    },
    
    /**
     * Affichage des suggestions de noms
     */
    showNameSuggestions(suggestions) {
        const container = document.getElementById('name-suggestions');
        if (!container) return;
        
        container.innerHTML = suggestions.map(suggestion => `
            <button type="button" class="btn btn-outline-primary btn-sm me-2 mb-2" 
                    onclick="selectSuggestedName('${suggestion}')">
                ${suggestion}
            </button>
        `).join('');
        
        container.classList.remove('d-none');
    },
    
    /**
     * Vérification adhérents avec détection conflits parti
     */
    async verifyMembers(members, organizationType) {
        try {
            const result = await apiClient.post(apiConfig.endpoints.verifyMembers, {
                members,
                organization_type: organizationType,
                check_party_conflicts: organizationType === 'parti_politique'
            });
            
            // Traiter les conflits détectés
            if (result.conflicts && result.conflicts.length > 0) {
                this.handleMemberConflicts(result.conflicts);
            }
            
            return result;
        } catch (error) {
            console.error('Erreur vérification adhérents:', error);
            throw error;
        }
    },
    
    /**
     * Gestion des conflits d'adhésion
     */
    handleMemberConflicts(conflicts) {
        const modalHtml = `
            <div class="modal fade" id="conflictsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Conflits d'adhésion détectés
                            </h5>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Les adhérents suivants sont déjà membres d'autres partis politiques :</p>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>NIP</th>
                                            <th>Nom Complet</th>
                                            <th>Parti Actuel</th>
                                            <th>Depuis</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${conflicts.map(conflict => `
                                            <tr>
                                                <td><code>${conflict.nip}</code></td>
                                                <td>${conflict.nom_complet}</td>
                                                <td><strong>${conflict.parti_actuel}</strong></td>
                                                <td>${conflict.date_adhesion}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="removeMemberFromList('${conflict.nip}')">
                                                        Retirer
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-3">
                                <strong>Note :</strong> Pour intégrer ces adhérents, vous devez fournir 
                                les justificatifs de leur démission des autres partis.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Ajouter et afficher la modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('conflictsModal'));
        modal.show();
        
        // Nettoyer après fermeture
        modal._element.addEventListener('hidden.bs.modal', () => {
            modal._element.remove();
        });
    }
};

/**
 * Service de gestion des documents avec preview
 */
const documentService = {
    uploadQueue: [],
    maxFileSize: 5 * 1024 * 1024, // 5MB
    allowedTypes: ['application/pdf', 'image/jpeg', 'image/png'],
    
    /**
     * Upload avec progress bar en temps réel
     */
    async uploadDocument(file, documentType, organizationId = null) {
        // Validation préalable
        if (!this.validateFile(file)) {
            return Promise.reject(new Error('Fichier invalide'));
        }
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('document_type', documentType);
        if (organizationId) {
            formData.append('organization_id', organizationId);
        }
        
        try {
            const result = await apiClient.request('POST', apiConfig.endpoints.uploadDocument, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: (progressEvent) => {
                    this.updateUploadProgress(documentType, progressEvent);
                }
            });
            
            // Générer automatiquement un preview
            if (result.file_path) {
                await this.generatePreview(result.file_path, documentType);
            }
            
            return result;
        } catch (error) {
            this.handleUploadError(documentType, error);
            throw error;
        }
    },
    
    /**
     * Validation des fichiers
     */
    validateFile(file) {
        if (!file) {
            showNotification('Aucun fichier sélectionné', 'warning');
            return false;
        }
        
        if (file.size > this.maxFileSize) {
            showNotification(`Le fichier dépasse la taille maximale de ${this.maxFileSize / 1024 / 1024}MB`, 'danger');
            return false;
        }
        
        if (!this.allowedTypes.includes(file.type)) {
            showNotification('Type de fichier non autorisé. Utilisez PDF, JPG ou PNG.', 'danger');
            return false;
        }
        
        return true;
    },
    
    /**
     * Mise à jour de la progress bar
     */
    updateUploadProgress(documentType, progressEvent) {
        const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        const progressBar = document.getElementById(`progress-${documentType}`);
        
        if (progressBar) {
            progressBar.style.width = percent + '%';
            progressBar.textContent = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
        }
        
        // Animation du pourcentage
        const percentageSpan = document.getElementById(`percentage-${documentType}`);
        if (percentageSpan) {
            percentageSpan.textContent = percent + '%';
        }
    },
    
    /**
     * Génération de preview automatique
     */
    async generatePreview(filePath, documentType) {
        try {
            const result = await apiClient.post(apiConfig.endpoints.previewDocument, {
                file_path: filePath,
                type: documentType
            });
            
            if (result.preview_url) {
                this.displayPreview(documentType, result.preview_url, result.file_type);
            }
        } catch (error) {
            console.warn('Impossible de générer le preview:', error);
        }
    },
    
    /**
     * Affichage du preview
     */
    displayPreview(documentType, previewUrl, fileType) {
        const container = document.getElementById(`preview-${documentType}`);
        if (!container) return;
        
        let previewHtml = '';
        
        if (fileType === 'pdf') {
            previewHtml = `
                <div class="pdf-preview">
                    <embed src="${previewUrl}" type="application/pdf" width="100%" height="400px" />
                    <div class="text-center mt-2">
                        <a href="${previewUrl}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Ouvrir en plein écran
                        </a>
                    </div>
                </div>
            `;
        } else {
            previewHtml = `
                <div class="image-preview text-center">
                    <img src="${previewUrl}" class="img-fluid rounded shadow-sm" 
                         style="max-height: 300px; cursor: pointer;"
                         onclick="openImageModal('${previewUrl}')" />
                </div>
            `;
        }
        
        container.innerHTML = previewHtml;
        container.classList.remove('d-none');
    },
    
    /**
     * Gestion des erreurs d'upload
     */
    handleUploadError(documentType, error) {
        const progressBar = document.getElementById(`progress-${documentType}`);
        if (progressBar) {
            progressBar.classList.remove('bg-primary');
            progressBar.classList.add('bg-danger');
            progressBar.textContent = 'Erreur';
        }
        
        const statusIcon = document.getElementById(`status-${documentType}`);
        if (statusIcon) {
            statusIcon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        }
    }
};

/**
 * Service de gestion des brouillons avancé
 */
const draftService = {
    autoSaveInterval: 30000, // 30 secondes
    autoSaveTimer: null,
    isAutoSaving: false,
    lastSaveHash: null,
    
    /**
     * Démarrage de l'auto-sauvegarde intelligente
     */
    startAutoSave() {
        this.stopAutoSave(); // Nettoyer l'ancien timer
        
        this.autoSaveTimer = setInterval(() => {
            if (!this.isAutoSaving && !document.hidden) {
                this.saveDraftAuto();
            }
        }, this.autoSaveInterval);
        
        console.log('🔄 Auto-sauvegarde démarrée (30s)');
    },
    
    /**
     * Arrêt de l'auto-sauvegarde
     */
    stopAutoSave() {
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
            this.autoSaveTimer = null;
        }
    },
    
    /**
     * Sauvegarde automatique intelligente
     */
    async saveDraftAuto() {
        try {
            this.isAutoSaving = true;
            
            const formData = this.collectFormData();
            const currentHash = this.calculateDataHash(formData);
            
            // Ne sauvegarder que si les données ont changé
            if (currentHash === this.lastSaveHash) {
                return;
            }
            
            // Sauvegarde locale immédiate
            this.saveToLocalStorage(formData);
            
            // Sauvegarde serveur en arrière-plan
            await this.saveDraftToServer(formData);
            
            this.lastSaveHash = currentHash;
            this.updateSaveIndicator('success');
            
        } catch (error) {
            console.warn('Échec auto-sauvegarde serveur:', error);
            this.updateSaveIndicator('warning');
        } finally {
            this.isAutoSaving = false;
        }
    },
    
    /**
     * Collecte intelligente des données du formulaire
     */
    collectFormData() {
        const data = {
            metadata: {
                currentStep: window.currentStep || 1,
                selectedOrgType: window.selectedOrgType || '',
                timestamp: new Date().toISOString(),
                version: '1.0'
            },
            steps: {},
            files: {},
            progress: {}
        };
        
        // Collecter les données par étape
        for (let step = 1; step <= 9; step++) {
            const stepElement = document.getElementById(`step${step}`);
            if (stepElement) {
                data.steps[step] = this.extractStepData(stepElement);
            }
        }
        
        // Collecter les informations de progression
        data.progress = {
            completedSteps: this.getCompletedSteps(),
            validationErrors: this.getValidationErrors(),
            uploadedDocuments: Object.keys(window.documentsUploaded || {})
        };
        
        return data;
    },
    
    /**
     * Extraction des données d'une étape
     */
    extractStepData(stepElement) {
        const data = {};
        
        stepElement.querySelectorAll('input, select, textarea').forEach(field => {
            if (!field.name) return;
            
            if (field.type === 'file') {
                // Pour les fichiers, stocker uniquement les métadonnées
                if (field.files.length > 0) {
                    data[field.name] = {
                        fileName: field.files[0].name,
                        fileSize: field.files[0].size,
                        fileType: field.files[0].type,
                        lastModified: field.files[0].lastModified
                    };
                }
            } else if (field.type === 'checkbox' || field.type === 'radio') {
                if (field.checked) {
                    data[field.name] = field.value;
                }
            } else if (field.type === 'select-multiple') {
                data[field.name] = Array.from(field.selectedOptions).map(option => option.value);
            } else {
                data[field.name] = field.value;
            }
        });
        
        return data;
    },
    
    /**
     * Calcul du hash des données pour détecter les changements
     */
    calculateDataHash(data) {
        const jsonString = JSON.stringify(data, Object.keys(data).sort());
        let hash = 0;
        for (let i = 0; i < jsonString.length; i++) {
            const char = jsonString.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return hash.toString();
    },
    
    /**
     * Sauvegarde dans localStorage
     */
    saveToLocalStorage(data) {
        try {
            localStorage.setItem('organizationFormDraft', JSON.stringify(data));
            localStorage.setItem('organizationFormDraftTimestamp', Date.now().toString());
        } catch (error) {
            console.warn('Erreur sauvegarde localStorage:', error);
        }
    },
    
    /**
     * Sauvegarde sur le serveur
     */
    async saveDraftToServer(data) {
        const payload = {
            form_data: data,
            step: data.metadata.currentStep,
            organization_type: data.metadata.selectedOrgType
        };
        
        const result = await apiClient.post(apiConfig.endpoints.saveDraft, payload);
        
        if (result.draft_id) {
            localStorage.setItem('currentDraftId', result.draft_id);
        }
        
        return result;
    },
    
    /**
     * Chargement d'un brouillon depuis le serveur
     */
    async loadDraftFromServer(draftId) {
        try {
            const result = await apiClient.get(`${apiConfig.endpoints.loadDraft}/${draftId}`);
            
            if (result.form_data) {
                this.restoreFormData(result.form_data);
                showNotification('Brouillon restauré avec succès', 'success');
                return true;
            }
        } catch (error) {
            console.error('Erreur chargement brouillon:', error);
            showNotification('Impossible de charger le brouillon', 'warning');
            return false;
        }
    },
    
    /**
     * Restauration des données dans le formulaire
     */
    restoreFormData(data) {
        // Restaurer les métadonnées
        if (data.metadata) {
            window.currentStep = data.metadata.currentStep || 1;
            window.selectedOrgType = data.metadata.selectedOrgType || '';
        }
        
        // Restaurer les données par étape
        if (data.steps) {
            Object.keys(data.steps).forEach(stepNumber => {
                this.restoreStepData(stepNumber, data.steps[stepNumber]);
            });
        }
        
        // Mettre à jour l'affichage
        updateStepDisplay();
        
        // Restaurer la sélection du type d'organisation si nécessaire
        if (window.selectedOrgType) {
            const card = document.querySelector(`[data-type="${window.selectedOrgType}"]`);
            if (card) {
                selectOrganizationType(card);
            }
        }
    },
    
    /**
     * Restauration des données d'une étape
     */
    restoreStepData(stepNumber, stepData) {
        const stepElement = document.getElementById(`step${stepNumber}`);
        if (!stepElement || !stepData) return;
        
        Object.keys(stepData).forEach(fieldName => {
            const field = stepElement.querySelector(`[name="${fieldName}"]`);
            if (!field) return;
            
            const value = stepData[fieldName];
            
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = field.value === value;
            } else if (field.type === 'select-multiple') {
                const values = Array.isArray(value) ? value : [value];
                Array.from(field.options).forEach(option => {
                    option.selected = values.includes(option.value);
                });
            } else if (field.type !== 'file') {
                field.value = value;
            }
            
            // Déclencher les événements de validation
            field.dispatchEvent(new Event('change', { bubbles: true }));
        });
    },
    
    /**
     * Indicateur visuel de sauvegarde
     */
    updateSaveIndicator(status) {
        const indicator = document.getElementById('save-indicator');
        if (!indicator) return;
        
        const icons = {
            saving: '<i class="fas fa-spinner fa-spin text-primary"></i> Sauvegarde...',
            success: '<i class="fas fa-check text-success"></i> Sauvegardé',
            warning: '<i class="fas fa-exclamation-triangle text-warning"></i> Sauvegarde locale',
            error: '<i class="fas fa-times text-danger"></i> Erreur sauvegarde'
        };
        
        indicator.innerHTML = icons[status] || icons.success;
        
        // Masquer après 3 secondes pour les états success/warning
        if (status === 'success' || status === 'warning') {
            setTimeout(() => {
                indicator.innerHTML = '';
            }, 3000);
        }
    },
    
    /**
     * Méthodes utilitaires
     */
    getCompletedSteps() {
        const completed = [];
        for (let i = 1; i <= window.currentStep; i++) {
            if (validateStep(i)) {
                completed.push(i);
            }
        }
        return completed;
    },
    
    getValidationErrors() {
        const errors = [];
        document.querySelectorAll('.is-invalid').forEach(field => {
            if (field.name) {
                errors.push({
                    field: field.name,
                    step: this.findStepForField(field)
                });
            }
        });
        return errors;
    },
    
    findStepForField(field) {
        let element = field;
        while (element && element.parentNode) {
            if (element.id && element.id.startsWith('step')) {
                return parseInt(element.id.replace('step', ''));
            }
            element = element.parentNode;
        }
        return null;
    }
};

// ============================================
// 4. FONCTIONNALITÉS JAVASCRIPT AVANCÉES
// ============================================

/**
 * Gestionnaire de géolocalisation avancé
 */
const geolocationManager = {
    watchId: null,
    lastKnownPosition: null,
    
    /**
     * Obtenir la position avec gestion des erreurs avancée
     */
    async getCurrentPosition(options = {}) {
        const defaultOptions = {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 300000 // 5 minutes
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Géolocalisation non supportée'));
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.lastKnownPosition = position;
                    resolve(this.processPosition(position));
                },
                (error) => {
                    reject(this.handleGeolocationError(error));
                },
                finalOptions
            );
        });
    },
    
    /**
     * Traitement de la position obtenue
     */
    processPosition(position) {
        const { latitude, longitude } = position.coords;
        
        // Vérification des limites du Gabon
        const gabonBounds = {
            north: 2.318,
            south: -3.978,
            east: 14.502,
            west: 8.695
        };
        
        const isInGabon = latitude >= gabonBounds.south && 
                         latitude <= gabonBounds.north && 
                         longitude >= gabonBounds.west && 
                         longitude <= gabonBounds.east;
        
        return {
            latitude: latitude.toFixed(7),
            longitude: longitude.toFixed(7),
            accuracy: position.coords.accuracy,
            isInGabon,
            timestamp: new Date(position.timestamp).toISOString()
        };
    },
    
    /**
     * Gestion des erreurs de géolocalisation
     */
    handleGeolocationError(error) {
        const errorMessages = {
            1: 'Permission de géolocalisation refusée',
            2: 'Position indisponible (problème technique)',
            3: 'Délai de localisation dépassé'
        };
        
        const message = errorMessages[error.code] || 'Erreur de géolocalisation inconnue';
        return new Error(message);
    },
    
    /**
     * Surveillance continue de la position
     */
    startWatching(callback, options = {}) {
        if (this.watchId) {
            this.stopWatching();
        }
        
        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                this.lastKnownPosition = position;
                const processed = this.processPosition(position);
                callback(processed);
            },
            (error) => {
                callback(null, this.handleGeolocationError(error));
            },
            options
        );
    },
    
    /**
     * Arrêt de la surveillance
     */
    stopWatching() {
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
    }
};

/**
 * Gestionnaire d'analytics de formulaire
 */
const formAnalytics = {
    startTime: Date.now(),
    stepTimes: {},
    interactions: [],
    
    /**
     * Démarrage du tracking d'une étape
     */
    startStepTracking(stepNumber) {
        this.stepTimes[stepNumber] = {
            start: Date.now(),
            interactions: 0,
            errors: 0
        };
    },
    
    /**
     * Fin du tracking d'une étape
     */
    endStepTracking(stepNumber) {
        if (this.stepTimes[stepNumber]) {
            this.stepTimes[stepNumber].end = Date.now();
            this.stepTimes[stepNumber].duration = 
                this.stepTimes[stepNumber].end - this.stepTimes[stepNumber].start;
        }
    },
    
    /**
     * Enregistrement d'une interaction
     */
    trackInteraction(type, element, value = null) {
        this.interactions.push({
            type,
            element: element.name || element.id || element.tagName,
            value,
            timestamp: Date.now(),
            step: window.currentStep
        });
        
        // Incrémenter le compteur d'interactions de l'étape actuelle
        if (this.stepTimes[window.currentStep]) {
            this.stepTimes[window.currentStep].interactions++;
        }
    },
    
    /**
     * Enregistrement d'une erreur
     */
    trackError(type, field, message) {
        this.interactions.push({
            type: 'error',
            subtype: type,
            field: field.name || field.id,
            message,
            timestamp: Date.now(),
            step: window.currentStep
        });
        
        // Incrémenter le compteur d'erreurs de l'étape actuelle
        if (this.stepTimes[window.currentStep]) {
            this.stepTimes[window.currentStep].errors++;
        }
    },
    
    /**
     * Envoi des analytics au serveur
     */
    async sendAnalytics() {
        const data = {
            session_duration: Date.now() - this.startTime,
            step_times: this.stepTimes,
            interactions: this.interactions,
            user_agent: navigator.userAgent,
            screen_resolution: `${screen.width}x${screen.height}`,
            organization_type: window.selectedOrgType,
            completion_rate: this.calculateCompletionRate()
        };
        
        try {
            await apiClient.post(apiConfig.endpoints.formAnalytics, data);
        } catch (error) {
            console.warn('Impossible d\'envoyer les analytics:', error);
        }
    },
    
    /**
     * Calcul du taux de completion
     */
    calculateCompletionRate() {
        const completedSteps = Object.keys(this.stepTimes).filter(step => 
            this.stepTimes[step].end
        ).length;
        
        return (completedSteps / 9) * 100;
    }
};

/**
 * Gestionnaire de validation hybride (client + serveur)
 */
const hybridValidator = {
    rules: {},
    serverValidationCache: new Map(),
    
    /**
     * Validation côté client avec règles dynamiques
     */
    validateField(field, rules = []) {
        const value = field.value.trim();
        const fieldRules = rules.length > 0 ? rules : this.getFieldRules(field);
        
        for (const rule of fieldRules) {
            const result = this.applyRule(value, rule, field);
            if (!result.valid) {
                this.showFieldError(field, result.message);
                return false;
            }
        }
        
        this.clearFieldError(field);
        return true;
    },
    
    /**
     * Application d'une règle de validation
     */
    applyRule(value, rule, field) {
        switch (rule.type) {
            case 'required':
                return {
                    valid: value.length > 0,
                    message: rule.message || 'Ce champ est obligatoire'
                };
                
            case 'min_length':
                return {
                    valid: value.length >= rule.value,
                    message: rule.message || `Minimum ${rule.value} caractères`
                };
                
            case 'max_length':
                return {
                    valid: value.length <= rule.value,
                    message: rule.message || `Maximum ${rule.value} caractères`
                };
                
            case 'pattern':
                const regex = new RegExp(rule.pattern);
                return {
                    valid: regex.test(value),
                    message: rule.message || 'Format invalide'
                };
                
            case 'nip_gabon':
                return this.validateNIPGabon(value);
                
            case 'email':
                return this.validateEmail(value);
                
            case 'phone_gabon':
                return this.validatePhoneGabon(value);
                
            case 'custom':
                return rule.validator(value, field);
                
            default:
                return { valid: true };
        }
    },
    
    /**
     * Validation NIP gabonais
     */
    validateNIPGabon(nip) {
        // Vérification format de base
        if (!/^\d{13}$/.test(nip)) {
            return {
                valid: false,
                message: 'Le NIP doit contenir exactement 13 chiffres'
            };
        }
        
        // Vérification checksum (algorithme simplifié)
        const digits = nip.split('').map(Number);
        const checksum = digits.slice(0, 12).reduce((sum, digit, index) => 
            sum + digit * (index % 2 === 0 ? 1 : 3), 0
        );
        
        const expectedCheck = (10 - (checksum % 10)) % 10;
        /*
        if (digits[12] !== expectedCheck) {
            return {
                valid: false,
                message: 'NIP invalide (checksum incorrect)'
            };*/
            
        }
        
        return { valid: true };
    },
    
    /**
     * Validation email
     */
    validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return {
            valid: regex.test(email),
            message: 'Format d\'email invalide'
        };
    },
    
    /**
     * Validation téléphone gabonais
     */
    validatePhoneGabon(phone) {
        // Nettoyer le numéro
        const cleaned = phone.replace(/\s+/g, '').replace(/^\+241/, '');
        
        // Vérifier le format gabonais
        if (!/^[0-9]{8,9}$/.test(cleaned)) {
            return {
                valid: false,
                message: 'Format de téléphone gabonais invalide'
            };
        }
        
        // Vérifier les préfixes valides
        const validPrefixes = ['01', '02', '03', '04', '05', '06', '07'];
        const prefix = cleaned.substring(0, 2);
        
        if (!validPrefixes.includes(prefix)) {
            return {
                valid: false,
                message: 'Préfixe téléphonique gabonais invalide'
            };
        }
        
        return { valid: true };
    },
    
    /**
     * Validation asynchrone côté serveur
     */
    async validateFieldAsync(field, endpoint, additionalData = {}) {
        const value = field.value.trim();
        if (!value) return true;
        
        // Vérifier le cache
        const cacheKey = `${endpoint}_${value}`;
        if (this.serverValidationCache.has(cacheKey)) {
            const cached = this.serverValidationCache.get(cacheKey);
            if (Date.now() - cached.timestamp < 300000) { // 5 minutes
                return this.handleServerValidationResult(field, cached.result);
            }
        }
        
        try {
            const payload = { value, ...additionalData };
            const result = await apiClient.post(endpoint, payload);
            
            // Mettre en cache
            this.serverValidationCache.set(cacheKey, {
                result,
                timestamp: Date.now()
            });
            
            return this.handleServerValidationResult(field, result);
        } catch (error) {
            console.warn('Erreur validation serveur:', error);
            return true; // En cas d'erreur serveur, considérer comme valide
        }
    },
    
    /**
     * Traitement du résultat de validation serveur
     */
    handleServerValidationResult(field, result) {
        if (result.valid) {
            this.clearFieldError(field);
            field.classList.add('is-valid');
            return true;
        } else {
            this.showFieldError(field, result.message);
            field.classList.add('is-invalid');
            return false;
        }
    },
    
    /**
     * Affichage d'erreur sur un champ
     */
    showFieldError(field, message) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        let errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        
        // Analytics
        formAnalytics.trackError('validation', field, message);
    },
    
    /**
     * Suppression d'erreur sur un champ
     */
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.textContent = '';
        }
    },
    
    /**
     * Obtention des règles d'un champ
     */
    getFieldRules(field) {
        // Règles basées sur les attributs HTML5 et data-attributes
        const rules = [];
        
        if (field.hasAttribute('required')) {
            rules.push({ type: 'required' });
        }
        
        if (field.hasAttribute('minlength')) {
            rules.push({ 
                type: 'min_length', 
                value: parseInt(field.getAttribute('minlength')) 
            });
        }
        
        if (field.hasAttribute('maxlength')) {
            rules.push({ 
                type: 'max_length', 
                value: parseInt(field.getAttribute('maxlength')) 
            });
        }
        
        if (field.hasAttribute('pattern')) {
            rules.push({ 
                type: 'pattern', 
                pattern: field.getAttribute('pattern') 
            });
        }
        
        // Règles spéciales basées sur le nom/type du champ
        if (field.name && field.name.includes('nip')) {
            rules.push({ type: 'nip_gabon' });
        }
        
        if (field.type === 'email') {
            rules.push({ type: 'email' });
        }
        
        if (field.type === 'tel') {
            rules.push({ type: 'phone_gabon' });
        }
        
        return rules;
    }
};

// ============================================
// 5. INITIALISATION ET CONFIGURATION FINALE
// ============================================

/**
 * Initialisation complète de l'application
 */
function initializeCompleteApplication() {
    console.log('🚀 Initialisation complète de l\'application PNGDI');
    
    // Démarrer l'auto-sauvegarde
    draftService.startAutoSave();
    
    // Configurer les événements de validation hybride
    setupHybridValidation();
    
    // Initialiser le tracking analytics
    formAnalytics.startStepTracking(1);
    
    // Configurer les gestionnaires d'événements avancés
    setupAdvancedEventListeners();
    
    // Tenter de restaurer un brouillon existant
    tryRestoreExistingDraft();
    
    // Configurer la gestion hors ligne
    setupOfflineSupport();
    
    console.log('✅ Application entièrement initialisée');
}

/**
 * Configuration de la validation hybride
 */
function setupHybridValidation() {
    // Validation en temps réel pour tous les champs
    document.addEventListener('input', function(e) {
        if (e.target.matches('input, textarea, select')) {
            // Debounce pour éviter trop de requêtes
            clearTimeout(e.target.validationTimeout);
            e.target.validationTimeout = setTimeout(() => {
                hybridValidator.validateField(e.target);
                formAnalytics.trackInteraction('input', e.target, e.target.value);
            }, 300);
        }
    });
    
    // Validation serveur asynchrone pour les champs spéciaux
    document.addEventListener('blur', function(e) {
        if (e.target.name === 'demandeur_nip') {
            hybridValidator.validateFieldAsync(e.target, apiConfig.endpoints.verifyNip);
        } else if (e.target.name === 'org_nom') {
            hybridValidator.validateFieldAsync(e.target, apiConfig.endpoints.verifyOrgName, {
                type: window.selectedOrgType
            });
        }
    });
}

/**
 * Configuration des événements avancés
 */
function setupAdvancedEventListeners() {
    // Gestion de la visibilité de page pour la sauvegarde
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            draftService.saveDraftAuto();
        }
    });
    
    // Sauvegarde avant fermeture
    window.addEventListener('beforeunload', function(e) {
        draftService.saveDraftAuto();
        formAnalytics.sendAnalytics();
    });
    
    // Gestion des erreurs de connectivité
    window.addEventListener('online', function() {
        showNotification('Connexion rétablie', 'success');
        draftService.saveDraftAuto(); // Synchroniser les données en attente
    });
    
    window.addEventListener('offline', function() {
        showNotification('Mode hors ligne activé', 'warning');
    });
    
    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
        // Ctrl+S pour sauvegarder
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            draftService.saveDraftAuto();
            showNotification('Brouillon sauvegardé', 'success');
        }
        
        // Flèches pour navigation
        if (e.ctrlKey && e.key === 'ArrowRight') {
            e.preventDefault();
            changeStep(1);
        } else if (e.ctrlKey && e.key === 'ArrowLeft') {
            e.preventDefault();
            changeStep(-1);
        }
    });
}

/**
 * Tentative de restauration d'un brouillon existant
 */
async function tryRestoreExistingDraft() {
    // Vérifier localStorage d'abord
    const localDraft = localStorage.getItem('organizationFormDraft');
    if (localDraft) {
        try {
            const data = JSON.parse(localDraft);
            const timestamp = localStorage.getItem('organizationFormDraftTimestamp');
            
            // Vérifier si le brouillon n'est pas trop ancien (7 jours)
            if (Date.now() - parseInt(timestamp) < 7 * 24 * 60 * 60 * 1000) {
                const restore = confirm('Un brouillon a été trouvé. Voulez-vous le restaurer ?');
                if (restore) {
                    draftService.restoreFormData(data);
                    showNotification('Brouillon restauré depuis le stockage local', 'success');
                }
            }
        } catch (error) {
            console.warn('Erreur lors de la restauration du brouillon local:', error);
        }
    }
    
    // Vérifier s'il y a un brouillon serveur
    const draftId = localStorage.getItem('currentDraftId');
    if (draftId) {
        try {
            await draftService.loadDraftFromServer(draftId);
        } catch (error) {
            console.warn('Impossible de charger le brouillon serveur:', error);
        }
    }
}

/**
 * Configuration du support hors ligne
 */
function setupOfflineSupport() {
    // Détecter si l'application peut fonctionner hors ligne
    if ('serviceWorker' in navigator) {
        // Enregistrer le service worker pour le cache
        navigator.serviceWorker.register('/sw.js').catch(error => {
            console.warn('Service Worker non disponible:', error);
        });
    }
    
    // Synchronisation en arrière-plan quand la connexion revient
    if ('sync' in window.ServiceWorkerRegistration.prototype) {
        navigator.serviceWorker.ready.then(registration => {
            return registration.sync.register('draft-sync');
        }).catch(error => {
            console.warn('Background Sync non disponible:', error);
        });
    }
}

// ============================================
// 6. FONCTIONS UTILITAIRES AVANCÉES
// ============================================

/**
 * Sélection suggérée de nom d'organisation
 */
function selectSuggestedName(name) {
    const nameField = document.getElementById('org_nom');
    if (nameField) {
        nameField.value = name;
        nameField.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Masquer les suggestions
    const container = document.getElementById('name-suggestions');
    if (container) {
        container.classList.add('d-none');
    }
}

/**
 * Suppression d'un adhérent de la liste
 */
function removeMemberFromList(nip) {
    const memberRow = document.querySelector(`[data-nip="${nip}"]`);
    if (memberRow) {
        memberRow.remove();
        showNotification(`Adhérent ${nip} retiré de la liste`, 'info');
    }
}

/**
 * Ouverture d'image en modal
 */
function openImageModal(imageUrl) {
    const modalHtml = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Aperçu du document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageUrl}" class="img-fluid" style="max-height: 70vh;" />
                    </div>
                    <div class="modal-footer">
                        <a href="${imageUrl}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt me-2"></i>Ouvrir dans un nouvel onglet
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Supprimer l'ancienne modal si elle existe
    const existingModal = document.getElementById('imageModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Ajouter et afficher la nouvelle modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
    
    // Nettoyer après fermeture
    modal._element.addEventListener('hidden.bs.modal', () => {
        modal._element.remove();
    });
}

/**
 * Extension de la fonction changeStep existante
 */
const originalChangeStep = window.changeStep;
window.changeStep = function(direction) {
    // Analytics du changement d'étape
    formAnalytics.endStepTracking(window.currentStep);
    
    // Appeler la fonction originale
    const result = originalChangeStep ? originalChangeStep(direction) : changeStepOriginal(direction);
    
    // Démarrer le tracking de la nouvelle étape
    if (direction > 0 && window.currentStep <= 9) {
        formAnalytics.startStepTracking(window.currentStep);
    }
    
    return result;
};

/**
 * Extension de la fonction de soumission finale
 */
async function submitFormWithAnalytics() {
    try {
        // Sauvegarder une dernière fois
        await draftService.saveDraftAuto();
        
        // Envoyer les analytics
        await formAnalytics.sendAnalytics();
        
        // Validation finale côté serveur
        const validationResult = await apiClient.post(apiConfig.endpoints.validateBeforeSubmit, {
            form_data: draftService.collectFormData()
        });
        
        if (!validationResult.valid) {
            showNotification('Veuillez corriger les erreurs avant de soumettre', 'danger');
            return false;
        }
        
        // Soumission finale
        const form = document.getElementById('organisationForm');
        if (form) {
            form.submit();
        }
        
        // Nettoyer les brouillons après soumission réussie
        localStorage.removeItem('organizationFormDraft');
        localStorage.removeItem('currentDraftId');
        
    } catch (error) {
        console.error('Erreur lors de la soumission:', error);
        showNotification('Erreur lors de la soumission. Veuillez réessayer.', 'danger');
    }
}

// ============================================
// 7. INITIALISATION FINALE
// ============================================

// Remplacer l'initialisation de base par la version complète
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation complète
    initializeCompleteApplication();
    
    // Configuration du bouton de soumission finale
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitFormWithAnalytics();
        });
    }
    
    console.log('🎉 Application PNGDI - Création d\'organisation entièrement finalisée');
});

// Exportation pour les tests et le debug
window.pngdiApp = {
    apiClient,
    verificationService,
    documentService,
    draftService,
    geolocationManager,
    formAnalytics,
    hybridValidator,
    apiConfig
};

// ========================================================================
// FIN SECTION O - JAVASCRIPT COMPLET FINALISÉ
// ========================================================================
</script>

<script>
// ========================================
// FONCTIONS DE NAVIGATION ESSENTIELLES
// ========================================

// Variables globales
window.currentStep = 1;
window.totalSteps = 9;

/**
 * Navigation entre les étapes
 */
function changeStep(direction) {
    console.log(`🔄 Changement d'étape: direction ${direction}, étape actuelle: ${window.currentStep}`);
    
    if (direction === 1) {
        // Aller à l'étape suivante
        if (window.currentStep < window.totalSteps) {
            window.currentStep++;
        }
    } else {
        // Aller à l'étape précédente
        if (window.currentStep > 1) {
            window.currentStep--;
        }
    }
    
    updateStepDisplay();
    updateNavigationButtons();
    scrollToTop();
}

/**
 * Mise à jour de l'affichage des étapes
 */
function updateStepDisplay() {
    // Masquer toutes les étapes
    document.querySelectorAll('.step-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Afficher l'étape actuelle
    const currentStepElement = document.getElementById('step' + window.currentStep);
    if (currentStepElement) {
        currentStepElement.style.display = 'block';
        console.log('✅ Affichage étape', window.currentStep);
    } else {
        console.warn('⚠️ Élément step' + window.currentStep + ' non trouvé');
    }
    
    // Mettre à jour la barre de progression
    const progress = (window.currentStep / window.totalSteps) * 100;
    const progressBar = document.getElementById('globalProgress');
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
    }
    
    // Mettre à jour le numéro d'étape
    const currentStepNumber = document.getElementById('currentStepNumber');
    if (currentStepNumber) {
        currentStepNumber.textContent = window.currentStep;
    }
    
    // Mettre à jour les indicateurs d'étapes
    updateStepIndicators();
}

/**
 * Mise à jour des indicateurs d'étapes
 */
function updateStepIndicators() {
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const stepNumber = index + 1;
        indicator.classList.remove('active', 'completed');
        
        if (stepNumber === window.currentStep) {
            indicator.classList.add('active');
        } else if (stepNumber < window.currentStep) {
            indicator.classList.add('completed');
        }
    });
}

/**
 * Mise à jour des boutons de navigation
 */
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Bouton précédent
    if (prevBtn) {
        prevBtn.style.display = window.currentStep === 1 ? 'none' : 'inline-block';
    }
    
    // Boutons suivant et soumettre
    if (window.currentStep === window.totalSteps) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.classList.remove('d-none');
    } else {
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.classList.add('d-none');
    }
}

/**
 * Scroll vers le haut
 */
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

/**
 * Sélection du type d'organisation
 */
function selectOrganizationType(card) {
    console.log('🏢 Sélection du type d\'organisation');
    
    // Retirer la sélection précédente
    document.querySelectorAll('.organization-type-card').forEach(c => {
        c.classList.remove('active', 'border-success', 'border-3');
    });
    
    // Appliquer la nouvelle sélection
    card.classList.add('active', 'border-success', 'border-3');
    
    // Cocher le radio button
    const radio = card.querySelector('input[type="radio"]');
    if (radio) {
        radio.checked = true;
        window.selectedOrgType = radio.value;
        
        // Mettre à jour l'input caché
        const hiddenInput = document.getElementById('organizationType');
        if (hiddenInput) {
            hiddenInput.value = radio.value;
        }
    }
    
    // Afficher les infos de sélection
    const selectedInfo = document.getElementById('selectedTypeInfo');
    const selectedTypeName = document.getElementById('selectedTypeName');
    if (selectedInfo && selectedTypeName) {
        selectedInfo.classList.remove('d-none');
        selectedTypeName.textContent = radio.value;
    }
}

// ========================================
// INITIALISATION
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation PNGDI - Navigation');
    
    // Initialiser l'affichage
    updateStepDisplay();
    updateNavigationButtons();
    
    // Configurer les événements pour les cartes d'organisation
    document.querySelectorAll('.organization-type-card').forEach(card => {
        card.addEventListener('click', function() {
            selectOrganizationType(this);
        });
    });
    
    console.log('✅ Navigation initialisée avec succès');
});

console.log('📝 Fonctions de navigation chargées');
</script>

@endsection