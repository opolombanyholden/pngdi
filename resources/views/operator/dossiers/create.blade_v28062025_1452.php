@extends('layouts.operator')

@section('title', 'Créer une Organisation')
@section('page-title', 'Nouvelle Organisation')

@section('content')
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
                        <div class="progress-bar bg-success progress-bar-striped" role="progressbar" style="width: 11.11%" id="globalProgress"></div>
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

    <!-- Contenu des étapes -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="organisationForm" action="{{ route('organizations.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Les étapes seront ajoutées ici dans les sections suivantes -->
                        
                        <!-- ========== DÉBUT SECTION B - ÉTAPE 1 : CHOIX DU TYPE D'ORGANISATION ========== -->
                        <!-- ÉTAPE 1: Choix du Type d'Organisation -->
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
                        <!-- ========== FIN SECTION B - ÉTAPE 1 : CHOIX DU TYPE D'ORGANISATION ========== -->

                        <!-- ========== DÉBUT SECTION C - ÉTAPE 2 : GUIDE SPÉCIFIQUE ========== -->
                        <!-- ÉTAPE 2: Guide spécifique -->
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

                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#assoc-documents">
                                                        <i class="fas fa-file-alt me-2 text-primary"></i>
                                                        Documents requis
                                                    </button>
                                                </h2>
                                                <div id="assoc-documents" class="accordion-collapse collapse" data-bs-parent="#guideAssociationAccordion">
                                                    <div class="accordion-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead class="table-primary">
                                                                    <tr>
                                                                        <th>Document</th>
                                                                        <th>Obligatoire</th>
                                                                        <th>Observations</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><i class="fas fa-file-contract me-2"></i>Statuts de l'association</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>3 exemplaires signés et paraphés</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-gavel me-2"></i>Règlement intérieur</td>
                                                                        <td><span class="badge bg-warning">Recommandé</span></td>
                                                                        <td>Complète les statuts</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-file-invoice me-2"></i>PV de l'AG constitutive</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Adoption des statuts et élection bureau</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-users me-2"></i>Liste des fondateurs</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Avec signatures légalisées</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-id-card me-2"></i>CNI des fondateurs</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Copies certifiées conformes</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-home me-2"></i>Justificatif siège social</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Bail, facture ou attestation</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 shadow-sm">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#assoc-process">
                                                        <i class="fas fa-route me-2 text-primary"></i>
                                                        Processus de création
                                                    </button>
                                                </h2>
                                                <div id="assoc-process" class="accordion-collapse collapse" data-bs-parent="#guideAssociationAccordion">
                                                    <div class="accordion-body">
                                                        <div class="timeline">
                                                            <div class="timeline-item">
                                                                <div class="timeline-marker bg-primary text-white">1</div>
                                                                <div class="timeline-content">
                                                                    <h6>Préparation</h6>
                                                                    <p>Rédaction des statuts et définition de l'objet social</p>
                                                                </div>
                                                            </div>
                                                            <div class="timeline-item">
                                                                <div class="timeline-marker bg-primary text-white">2</div>
                                                                <div class="timeline-content">
                                                                    <h6>Assemblée générale constitutive</h6>
                                                                    <p>Adoption des statuts et élection des dirigeants</p>
                                                                </div>
                                                            </div>
                                                            <div class="timeline-item">
                                                                <div class="timeline-marker bg-warning text-dark">3</div>
                                                                <div class="timeline-content">
                                                                    <h6>Dépôt de dossier</h6>
                                                                    <p>Soumission via cette plateforme numérique</p>
                                                                </div>
                                                            </div>
                                                            <div class="timeline-item">
                                                                <div class="timeline-marker bg-success text-white">4</div>
                                                                <div class="timeline-content">
                                                                    <h6>Validation et récépissé</h6>
                                                                    <p>Examen et délivrance du récépissé définitif</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Guide ONG -->
                                    <div class="guide-content d-none" id="guide-ong">
                                        <div class="alert alert-info border-0 mb-4 shadow-sm">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-globe-africa fa-3x me-3 text-info"></i>
                                                <div>
                                                    <h5 class="alert-heading mb-1">Guide pour créer une ONG au Gabon</h5>
                                                    <p class="mb-0">Procédures spécifiques aux Organisations Non Gouvernementales</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="accordion" id="guideOngAccordion">
                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ong-definition">
                                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                                        Qu'est-ce qu'une ONG au Gabon ?
                                                    </button>
                                                </h2>
                                                <div id="ong-definition" class="accordion-collapse collapse show" data-bs-parent="#guideOngAccordion">
                                                    <div class="accordion-body">
                                                        <p>Une ONG est une organisation à but non lucratif, indépendante des gouvernements, qui se consacre à des causes humanitaires, environnementales, éducatives ou de développement social au Gabon.</p>
                                                        <div class="row mt-3">
                                                            <div class="col-md-12">
                                                                <h6 class="text-info"><i class="fas fa-target me-2"></i>Domaines d'intervention privilégiés</h6>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <ul class="list-unstyled">
                                                                            <li>• Développement communautaire rural</li>
                                                                            <li>• Santé publique et prévention</li>
                                                                            <li>• Éducation et alphabétisation</li>
                                                                            <li>• Protection de l'environnement</li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <ul class="list-unstyled">
                                                                            <li>• Droits humains et genre</li>
                                                                            <li>• Aide humanitaire d'urgence</li>
                                                                            <li>• Autonomisation des femmes</li>
                                                                            <li>• Protection de l'enfance</li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ong-conditions">
                                                        <i class="fas fa-list-check me-2 text-info"></i>
                                                        Exigences renforcées pour les ONG
                                                    </button>
                                                </h2>
                                                <div id="ong-conditions" class="accordion-collapse collapse" data-bs-parent="#guideOngAccordion">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="text-info">Critères humains</h6>
                                                                <ul>
                                                                    <li><strong>Minimum 5 fondateurs</strong> qualifiés</li>
                                                                    <li><strong>Minimum 15 adhérents</strong> à la création</li>
                                                                    <li>Expérience dans le domaine social requise</li>
                                                                    <li>CV détaillés des responsables</li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-info">Critères techniques</h6>
                                                                <ul>
                                                                    <li>Projet social clairement défini</li>
                                                                    <li>Budget prévisionnel sur 3 ans</li>
                                                                    <li>Sources de financement identifiées</li>
                                                                    <li>Plan d'activités détaillé</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 shadow-sm">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ong-documents">
                                                        <i class="fas fa-file-alt me-2 text-info"></i>
                                                        Documents spécifiques ONG
                                                    </button>
                                                </h2>
                                                <div id="ong-documents" class="accordion-collapse collapse" data-bs-parent="#guideOngAccordion">
                                                    <div class="accordion-body">
                                                        <div class="alert alert-warning">
                                                            <strong>En plus</strong> des documents communs à toute association :
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead class="table-info">
                                                                    <tr>
                                                                        <th>Document supplémentaire</th>
                                                                        <th>Obligatoire</th>
                                                                        <th>Spécifications</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><i class="fas fa-project-diagram me-2"></i>Projet social détaillé</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Objectifs, méthodologie, impacts attendus</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-chart-line me-2"></i>Budget prévisionnel</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>3 premières années minimum</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-graduation-cap me-2"></i>CV des fondateurs</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Expérience dans le domaine social</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-handshake me-2"></i>Lettres de soutien</td>
                                                                        <td><span class="badge bg-warning">Recommandé</span></td>
                                                                        <td>Partenaires, autorités locales</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Guide Parti Politique -->
                                    <div class="guide-content d-none" id="guide-parti_politique">
                                        <div class="alert alert-warning border-0 mb-4 shadow-sm">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-vote-yea fa-3x me-3 text-warning"></i>
                                                <div>
                                                    <h5 class="alert-heading mb-1">Guide pour créer un Parti Politique au Gabon</h5>
                                                    <p class="mb-0">Exigences strictes selon la loi électorale gabonaise</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="accordion" id="guidePartiAccordion">
                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#parti-definition">
                                                        <i class="fas fa-info-circle me-2 text-warning"></i>
                                                        Définition légale du parti politique
                                                    </button>
                                                </h2>
                                                <div id="parti-definition" class="accordion-collapse collapse show" data-bs-parent="#guidePartiAccordion">
                                                    <div class="accordion-body">
                                                        <p>Un parti politique est une organisation qui rassemble des citoyens gabonais autour d'un projet politique commun, dans le but de participer aux élections et d'exercer le pouvoir politique.</p>
                                                        <div class="alert alert-danger mt-3">
                                                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Contrainte d'exclusivité</h6>
                                                            <p class="mb-0">Un citoyen gabonais ne peut être membre que d'un seul parti politique à la fois. Cette règle est strictement contrôlée via le NIP.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#parti-conditions">
                                                        <i class="fas fa-list-check me-2 text-warning"></i>
                                                        Conditions strictes
                                                    </button>
                                                </h2>
                                                <div id="parti-conditions" class="accordion-collapse collapse" data-bs-parent="#guidePartiAccordion">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="text-warning">Exigences numériques</h6>
                                                                <ul>
                                                                    <li><strong>Minimum 50 adhérents</strong> à la création</li>
                                                                    <li><strong>Présence dans au moins 3 provinces</strong></li>
                                                                    <li>Répartition géographique équilibrée</li>
                                                                    <li>Adhérents majeurs exclusivement</li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-warning">Exigences politiques</h6>
                                                                <ul>
                                                                    <li>Programme politique détaillé</li>
                                                                    <li>Respect des valeurs républicaines</li>
                                                                    <li>Non-discrimination ethnique/régionale</li>
                                                                    <li>Financement transparent</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 shadow-sm">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#parti-documents">
                                                        <i class="fas fa-file-alt me-2 text-warning"></i>
                                                        Documents obligatoires spécifiques
                                                    </button>
                                                </h2>
                                                <div id="parti-documents" class="accordion-collapse collapse" data-bs-parent="#guidePartiAccordion">
                                                    <div class="accordion-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead class="table-warning">
                                                                    <tr>
                                                                        <th>Document politique</th>
                                                                        <th>Obligatoire</th>
                                                                        <th>Spécifications</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><i class="fas fa-scroll me-2"></i>Programme politique</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Vision, objectifs, propositions concrètes</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-users me-2"></i>Liste complète des 50 adhérents</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>NIP obligatoires + signatures légalisées</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-map-marked me-2"></i>Répartition géographique</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Preuve d'implantation dans 3 provinces</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-euro-sign me-2"></i>Sources de financement</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Déclaration des financements</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Guide Confession Religieuse -->
                                    <div class="guide-content d-none" id="guide-confession_religieuse">
                                        <div class="alert alert-secondary border-0 mb-4 shadow-sm" style="background: linear-gradient(135deg, #6f42c1, #e83e8c); color: white;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-pray fa-3x me-3 text-white"></i>
                                                <div>
                                                    <h5 class="alert-heading mb-1 text-white">Guide pour créer une Confession Religieuse au Gabon</h5>
                                                    <p class="mb-0 text-white opacity-75">Procédures pour l'enregistrement d'organisations religieuses</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="accordion" id="guideReligionAccordion">
                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#religion-definition">
                                                        <i class="fas fa-info-circle me-2 text-purple"></i>
                                                        Qu'est-ce qu'une confession religieuse ?
                                                    </button>
                                                </h2>
                                                <div id="religion-definition" class="accordion-collapse collapse show" data-bs-parent="#guideReligionAccordion">
                                                    <div class="accordion-body">
                                                        <p>Une confession religieuse est une organisation qui rassemble des fidèles autour d'une doctrine spirituelle commune pour l'exercice du culte et des activités religieuses au Gabon.</p>
                                                        <div class="row mt-3">
                                                            <div class="col-md-6">
                                                                <h6 class="text-purple"><i class="fas fa-star me-2"></i>Libertés garanties</h6>
                                                                <ul class="list-unstyled">
                                                                    <li>• Liberté de culte constitutionnelle</li>
                                                                    <li>• Liberté de conscience</li>
                                                                    <li>• Autonomie doctrinale</li>
                                                                    <li>• Organisation interne libre</li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-purple"><i class="fas fa-balance-scale me-2"></i>Obligations légales</h6>
                                                                <ul class="list-unstyled">
                                                                    <li>• Respect de l'ordre public</li>
                                                                    <li>• Non-discrimination</li>
                                                                    <li>• Transparence financière</li>
                                                                    <li>• Respect des lois gabonaises</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 shadow-sm mb-3">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#religion-conditions">
                                                        <i class="fas fa-list-check me-2 text-purple"></i>
                                                        Conditions requises
                                                    </button>
                                                </h2>
                                                <div id="religion-conditions" class="accordion-collapse collapse" data-bs-parent="#guideReligionAccordion">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="text-purple">Exigences de base</h6>
                                                                <ul>
                                                                    <li><strong>Minimum 10 fidèles</strong> majeurs</li>
                                                                    <li>Lieu de culte identifié et accessible</li>
                                                                    <li>Responsable religieux qualifié</li>
                                                                    <li>Doctrine clairement exposée</li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-purple">Organisation interne</h6>
                                                                <ul>
                                                                    <li>Statuts adaptés au culte</li>
                                                                    <li>Règles de fonctionnement spirituel</li>
                                                                    <li>Mode de désignation des dirigeants</li>
                                                                    <li>Gestion des biens religieux</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 shadow-sm">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#religion-documents">
                                                        <i class="fas fa-file-alt me-2 text-purple"></i>
                                                        Documents religieux spécifiques
                                                    </button>
                                                </h2>
                                                <div id="religion-documents" class="accordion-collapse collapse" data-bs-parent="#guideReligionAccordion">
                                                    <div class="accordion-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead style="background: linear-gradient(135deg, #6f42c1, #e83e8c); color: white;">
                                                                    <tr>
                                                                        <th>Document religieux</th>
                                                                        <th>Obligatoire</th>
                                                                        <th>Contenu attendu</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><i class="fas fa-book me-2"></i>Exposé de la doctrine</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Croyances, pratiques, traditions</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-home me-2"></i>Justificatif du lieu de culte</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Bail, titre de propriété, autorisation</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-certificate me-2"></i>Attestation du responsable religieux</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>Formation religieuse, expérience</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><i class="fas fa-users me-2"></i>Liste des 10 fidèles minimum</td>
                                                                        <td><span class="badge bg-danger">Oui</span></td>
                                                                        <td>NIP + déclaration d'adhésion</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
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
                                            Cette confirmation est obligatoire pour passer à l'étape suivante. Elle atteste de votre connaissance des obligations légales.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ========== FIN SECTION C - ÉTAPE 2 : GUIDE SPÉCIFIQUE ========== -->

                        <!-- ========== DÉBUT SECTION D - ÉTAPE 3 : INFORMATIONS DEMANDEUR ========== -->
                        <!-- ÉTAPE 3: Informations du demandeur -->
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

                                                <!-- Nom de famille -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_nom" class="form-label fw-bold required">
                                                        <i class="fas fa-signature me-2 text-primary"></i>
                                                        Nom de famille
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="demandeur_nom" 
                                                           name="demandeur_nom" 
                                                           placeholder="Votre nom de famille"
                                                           pattern="[A-Za-zÀ-ÿ\s\-']+"
                                                           style="text-transform: uppercase;"
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Prénom(s) -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_prenoms" class="form-label fw-bold required">
                                                        <i class="fas fa-user me-2 text-primary"></i>
                                                        Prénom(s)
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="demandeur_prenoms" 
                                                           name="demandeur_prenoms" 
                                                           placeholder="Vos prénoms complets"
                                                           pattern="[A-Za-zÀ-ÿ\s\-']+"
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Données civiles -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-file-alt me-2"></i>
                                                Données civiles
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Date de naissance -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_date_naissance" class="form-label fw-bold required">
                                                        <i class="fas fa-calendar-alt me-2 text-info"></i>
                                                        Date de naissance
                                                    </label>
                                                    <input type="date" 
                                                           class="form-control" 
                                                           id="demandeur_date_naissance" 
                                                           name="demandeur_date_naissance" 
                                                           required>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Vous devez être majeur (18 ans minimum)
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Lieu de naissance -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_lieu_naissance" class="form-label fw-bold required">
                                                        <i class="fas fa-map-marker-alt me-2 text-info"></i>
                                                        Lieu de naissance
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="demandeur_lieu_naissance" 
                                                           name="demandeur_lieu_naissance" 
                                                           placeholder="Ville, Pays (ex: Libreville, Gabon)"
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Sexe -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_sexe" class="form-label fw-bold required">
                                                        <i class="fas fa-venus-mars me-2 text-info"></i>
                                                        Sexe
                                                    </label>
                                                    <select class="form-select" id="demandeur_sexe" name="demandeur_sexe" required>
                                                        <option value="">Sélectionnez</option>
                                                        <option value="M">Masculin</option>
                                                        <option value="F">Féminin</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Nationalité -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_nationalite" class="form-label fw-bold required">
                                                        <i class="fas fa-flag me-2 text-info"></i>
                                                        Nationalité
                                                    </label>
                                                    <select class="form-select" id="demandeur_nationalite" name="demandeur_nationalite" required>
                                                        <option value="">Sélectionnez votre nationalité</option>
                                                        <option value="Gabonaise" selected>Gabonaise</option>
                                                        <option value="Française">Française</option>
                                                        <option value="Camerounaise">Camerounaise</option>
                                                        <option value="Équato-guinéenne">Équato-guinéenne</option>
                                                        <option value="Congolaise (Brazzaville)">Congolaise (Brazzaville)</option>
                                                        <option value="Congolaise (RDC)">Congolaise (RDC)</option>
                                                        <option value="Tchadienne">Tchadienne</option>
                                                        <option value="Centrafricaine">Centrafricaine</option>
                                                        <option value="Sao-toméenne">Sao-toméenne</option>
                                                        <option value="Autre">Autre</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Profession -->
                                                <div class="col-md-12 mb-4">
                                                    <label for="demandeur_profession" class="form-label fw-bold required">
                                                        <i class="fas fa-briefcase me-2 text-info"></i>
                                                        Profession ou activité principale
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="demandeur_profession" 
                                                           name="demandeur_profession" 
                                                           placeholder="Votre profession actuelle"
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
                                                Coordonnées de contact
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Email -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_email" class="form-label fw-bold required">
                                                        <i class="fas fa-envelope me-2 text-success"></i>
                                                        Adresse email
                                                    </label>
                                                    <input type="email" 
                                                           class="form-control" 
                                                           id="demandeur_email" 
                                                           name="demandeur_email" 
                                                           placeholder="votre.email@exemple.com"
                                                           required>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Cette adresse sera utilisée pour les communications officielles
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Téléphone principal -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_telephone" class="form-label fw-bold required">
                                                        <i class="fas fa-phone me-2 text-success"></i>
                                                        Numéro de téléphone principal
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-success text-white">+241</span>
                                                        <input type="tel" 
                                                               class="form-control" 
                                                               id="demandeur_telephone" 
                                                               name="demandeur_telephone" 
                                                               placeholder="XX XX XX XX"
                                                               pattern="[0-9\s]{8,12}"
                                                               maxlength="12"
                                                               required>
                                                    </div>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Format gabonais : +241 XX XX XX XX
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Téléphone secondaire -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_telephone_2" class="form-label fw-bold">
                                                        <i class="fas fa-mobile-alt me-2 text-success"></i>
                                                        Téléphone secondaire <span class="text-muted">(optionnel)</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+241</span>
                                                        <input type="tel" 
                                                               class="form-control" 
                                                               id="demandeur_telephone_2" 
                                                               name="demandeur_telephone_2" 
                                                               placeholder="XX XX XX XX"
                                                               pattern="[0-9\s]{8,12}"
                                                               maxlength="12">
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Adresse complète -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="demandeur_adresse" class="form-label fw-bold required">
                                                        <i class="fas fa-home me-2 text-success"></i>
                                                        Adresse complète de résidence
                                                    </label>
                                                    <textarea class="form-control" 
                                                              id="demandeur_adresse" 
                                                              name="demandeur_adresse" 
                                                              rows="3" 
                                                              placeholder="Numéro, rue, quartier, arrondissement, ville, province"
                                                              required></textarea>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section CNI -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-id-card me-2"></i>
                                                Informations de la Carte Nationale d'Identité
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Numéro CNI -->
                                                <div class="col-md-6 mb-3">
                                                    <label for="demandeur_cni_numero" class="form-label fw-bold required">
                                                        <i class="fas fa-hashtag me-2 text-warning"></i>
                                                        Numéro de la CNI
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="demandeur_cni_numero" 
                                                           name="demandeur_cni_numero" 
                                                           placeholder="Numéro inscrit sur votre CNI"
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Date d'établissement CNI -->
                                                <div class="col-md-6 mb-3">
                                                    <label for="demandeur_cni_date_etablissement" class="form-label fw-bold required">
                                                        <i class="fas fa-calendar-check me-2 text-warning"></i>
                                                        Date d'établissement
                                                    </label>
                                                    <input type="date" 
                                                           class="form-control" 
                                                           id="demandeur_cni_date_etablissement" 
                                                           name="demandeur_cni_date_etablissement" 
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Date d'expiration CNI -->
                                                <div class="col-md-6 mb-3">
                                                    <label for="demandeur_cni_date_expiration" class="form-label fw-bold required">
                                                        <i class="fas fa-calendar-times me-2 text-warning"></i>
                                                        Date d'expiration
                                                    </label>
                                                    <input type="date" 
                                                           class="form-control" 
                                                           id="demandeur_cni_date_expiration" 
                                                           name="demandeur_cni_date_expiration" 
                                                           required>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Lieu d'établissement CNI -->
                                                <div class="col-md-6 mb-3">
                                                    <label for="demandeur_cni_lieu_etablissement" class="form-label fw-bold required">
                                                        <i class="fas fa-building me-2 text-warning"></i>
                                                        Lieu d'établissement
                                                    </label>
                                                    <select class="form-select" id="demandeur_cni_lieu_etablissement" name="demandeur_cni_lieu_etablissement" required>
                                                        <option value="">Sélectionnez le lieu</option>
                                                        <option value="Libreville">Libreville</option>
                                                        <option value="Port-Gentil">Port-Gentil</option>
                                                        <option value="Franceville">Franceville</option>
                                                        <option value="Oyem">Oyem</option>
                                                        <option value="Moanda">Moanda</option>
                                                        <option value="Lambaréné">Lambaréné</option>
                                                        <option value="Tchibanga">Tchibanga</option>
                                                        <option value="Koulamoutou">Koulamoutou</option>
                                                        <option value="Bitam">Bitam</option>
                                                        <option value="Makokou">Makokou</option>
                                                        <option value="Autre">Autre ville</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>

                                            <!-- Vérification validité CNI -->
                                            <div class="mt-3" id="cni-validity-check">
                                                <div class="alert alert-light border">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-clock text-muted me-2"></i>
                                                        <span class="text-muted">La validité de votre CNI sera vérifiée automatiquement</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Validation -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-dark text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-check-circle me-2"></i>
                                                Vérification et validation des informations
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="demandeur_info_exactes" name="demandeur_info_exactes" required>
                                                <label class="form-check-label fw-bold" for="demandeur_info_exactes">
                                                    <i class="fas fa-certificate me-2 text-primary"></i>
                                                    Je certifie que toutes les informations fournies sont exactes et conformes à mes documents officiels gabonais
                                                </label>
                                            </div>

                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="demandeur_contact_autorise" name="demandeur_contact_autorise" required>
                                                <label class="form-check-label fw-bold" for="demandeur_contact_autorise">
                                                    <i class="fas fa-phone-alt me-2 text-success"></i>
                                                    J'autorise le contact via les coordonnées fournies pour le suivi de ce dossier de création d'organisation
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="demandeur_responsabilite" name="demandeur_responsabilite" required>
                                                <label class="form-check-label fw-bold" for="demandeur_responsabilite">
                                                    <i class="fas fa-user-shield me-2 text-warning"></i>
                                                    Je m'engage à être le responsable légal de cette demande et à fournir tout complément d'information si nécessaire
                                                </label>
                                            </div>

                                            <div class="mt-4 p-3 bg-light rounded">
                                                <small class="text-muted">
                                                    <i class="fas fa-shield-alt me-1 text-primary"></i>
                                                    <strong>Protection des données :</strong> Vos données personnelles sont protégées conformément à la législation gabonaise 
                                                    et ne sont utilisées que dans le cadre du traitement de votre demande de création d'organisation. 
                                                    Elles pourront être transmises aux services compétents pour validation.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ========== FIN SECTION D - ÉTAPE 3 : INFORMATIONS DEMANDEUR ========== -->

                        <!-- ========== DÉBUT SECTION E - ÉTAPE 4 : INFORMATIONS ORGANISATION ========== -->
                        <!-- ÉTAPE 4: Informations de l'organisation -->
                        <div class="step-content" id="step4" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                                    <i class="fas fa-building fa-3x text-white"></i>
                                </div>
                                <h3 class="text-purple">Informations de l'organisation</h3>
                                <p class="text-muted">Définissez l'identité et les caractéristiques principales de votre organisation</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    
                                    <!-- Section Identité -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); color: white;">
                                            <h6 class="mb-0">
                                                <i class="fas fa-signature me-2"></i>
                                                Identité de l'organisation
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Dénomination complète -->
                                                <div class="col-12 mb-4">
                                                    <label for="org_nom" class="form-label fw-bold required">
                                                        <i class="fas fa-signature me-2 text-purple"></i>
                                                        Dénomination complète de l'organisation
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control form-control-lg" 
                                                           id="org_nom" 
                                                           name="org_nom" 
                                                           placeholder="Nom complet et officiel de votre organisation"
                                                           maxlength="150"
                                                           required>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Le nom doit être unique et ne pas porter atteinte aux droits de tiers
                                                        <span class="float-end"><span id="nom-counter">0</span>/150 caractères</span>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Sigle/Acronyme -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_sigle" class="form-label fw-bold">
                                                        <i class="fas fa-font me-2 text-purple"></i>
                                                        Sigle ou Acronyme <span class="text-muted">(optionnel)</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_sigle" 
                                                           name="org_sigle" 
                                                           placeholder="Ex: AGDS, ACDH, etc."
                                                           maxlength="10"
                                                           style="text-transform: uppercase;">
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Version courte du nom pour faciliter l'identification
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Slogan/Devise -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_slogan" class="form-label fw-bold">
                                                        <i class="fas fa-quote-right me-2 text-purple"></i>
                                                        Slogan ou Devise <span class="text-muted">(optionnel)</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_slogan" 
                                                           name="org_slogan" 
                                                           placeholder="Votre slogan ou devise"
                                                           maxlength="100">
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Phrase qui résume votre mission ou vos valeurs
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Mission et Objectifs -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-target me-2"></i>
                                                Mission et objectifs
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Objet social -->
                                            <div class="mb-4">
                                                <label for="org_objet" class="form-label fw-bold required">
                                                    <i class="fas fa-bullseye me-2 text-primary"></i>
                                                    Objet social de l'organisation
                                                </label>
                                                <textarea class="form-control" 
                                                          id="org_objet" 
                                                          name="org_objet" 
                                                          rows="4" 
                                                          placeholder="Décrivez précisément les activités et objectifs de votre organisation..."
                                                          maxlength="1000"
                                                          required></textarea>
                                                <div class="form-text">
                                                    <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                                    <strong>Important :</strong> Cet objet déterminera le champ d'action légal de votre organisation
                                                    <span class="float-end"><span id="objet-counter">0</span>/1000 caractères</span>
                                                </div>
                                                <div class="invalid-feedback"></div>
                                            </div>

                                            <!-- Objectifs spécifiques -->
                                            <div class="mb-4">
                                                <label for="org_objectifs" class="form-label fw-bold required">
                                                    <i class="fas fa-list-ol me-2 text-primary"></i>
                                                    Objectifs spécifiques (3 à 5 objectifs principaux)
                                                </label>
                                                <textarea class="form-control" 
                                                          id="org_objectifs" 
                                                          name="org_objectifs" 
                                                          rows="4" 
                                                          placeholder="1. Premier objectif principal&#10;2. Deuxième objectif principal&#10;3. Troisième objectif principal&#10;..."
                                                          maxlength="800"
                                                          required></textarea>
                                                <div class="form-text">
                                                    <i class="fas fa-info me-1"></i>
                                                    Énumérez vos objectifs de manière claire et précise
                                                    <span class="float-end"><span id="objectifs-counter">0</span>/800 caractères</span>
                                                </div>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Classification -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-tags me-2"></i>
                                                Classification et secteur d'activité
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Secteur d'activité principal -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_secteur" class="form-label fw-bold required">
                                                        <i class="fas fa-industry me-2 text-info"></i>
                                                        Secteur d'activité principal
                                                    </label>
                                                    <select class="form-select" id="org_secteur" name="org_secteur" required>
                                                        <option value="">Sélectionnez un secteur</option>
                                                        <!-- Options dynamiques selon le type d'organisation -->
                                                        <optgroup label="Secteurs Associatifs" id="secteurs-association" class="d-none">
                                                            <option value="culture">Culture et Arts</option>
                                                            <option value="sport">Sport et Loisirs</option>
                                                            <option value="education">Éducation et Formation</option>
                                                            <option value="social">Action Sociale et Solidarité</option>
                                                            <option value="environnement">Environnement et Développement Durable</option>
                                                            <option value="jeunesse">Jeunesse et Animation</option>
                                                            <option value="sante">Santé et Bien-être</option>
                                                            <option value="droits">Droits et Libertés</option>
                                                            <option value="economie">Développement Économique Local</option>
                                                            <option value="religion">Cultuel et Spirituel</option>
                                                            <option value="autre_asso">Autre secteur associatif</option>
                                                        </optgroup>
                                                        <optgroup label="Secteurs ONG" id="secteurs-ong" class="d-none">
                                                            <option value="humanitaire">Action Humanitaire d'Urgence</option>
                                                            <option value="developpement">Développement Durable</option>
                                                            <option value="sante_publique">Santé Publique</option>
                                                            <option value="education_formation">Éducation et Formation Professionnelle</option>
                                                            <option value="droits_humains">Droits Humains et Genre</option>
                                                            <option value="environnement_ong">Protection Environnementale</option>
                                                            <option value="genre">Égalité des Sexes</option>
                                                            <option value="enfance">Protection de l'Enfance</option>
                                                            <option value="pauvrete">Lutte contre la Pauvreté</option>
                                                            <option value="governance">Gouvernance et Démocratie</option>
                                                            <option value="microfinance">Microfinance et Inclusion Financière</option>
                                                            <option value="autre_ong">Autre secteur ONG</option>
                                                        </optgroup>
                                                        <optgroup label="Secteurs Politiques" id="secteurs-parti" class="d-none">
                                                            <option value="politique_generale">Politique Générale</option>
                                                            <option value="democratie">Démocratie et Gouvernance</option>
                                                            <option value="developpement_national">Développement National</option>
                                                            <option value="justice_sociale">Justice Sociale</option>
                                                            <option value="economie_politique">Économie et Politique Sociale</option>
                                                            <option value="jeunesse_politique">Politique de la Jeunesse</option>
                                                            <option value="autre_politique">Autre orientation politique</option>
                                                        </optgroup>
                                                        <optgroup label="Secteurs Religieux" id="secteurs-religion" class="d-none">
                                                            <option value="christianisme">Christianisme</option>
                                                            <option value="islam">Islam</option>
                                                            <option value="religion_traditionnelle">Religions Traditionnelles Gabonaises</option>
                                                            <option value="bouddhisme">Bouddhisme</option>
                                                            <option value="judaisme">Judaïsme</option>
                                                            <option value="hinduisme">Hindouisme</option>
                                                            <option value="spiritualite_moderne">Spiritualité Moderne</option>
                                                            <option value="autre_religion">Autre confession</option>
                                                        </optgroup>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Zone géographique d'intervention -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_zone_intervention" class="form-label fw-bold required">
                                                        <i class="fas fa-map-marked-alt me-2 text-info"></i>
                                                        Zone géographique d'intervention
                                                    </label>
                                                    <select class="form-select" id="org_zone_intervention" name="org_zone_intervention" required>
                                                        <option value="">Sélectionnez la zone</option>
                                                        <option value="locale">Locale (ville/commune)</option>
                                                        <option value="departementale">Départementale</option>
                                                        <option value="provinciale">Provinciale</option>
                                                        <option value="regionale">Régionale (plusieurs provinces)</option>
                                                        <option value="nationale">Nationale (tout le Gabon)</option>
                                                        <option value="sous_regionale">Sous-régionale (Afrique Centrale)</option>
                                                        <option value="internationale">Internationale</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Public cible -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_public_cible" class="form-label fw-bold required">
                                                        <i class="fas fa-users me-2 text-info"></i>
                                                        Public cible principal
                                                    </label>
                                                    <select class="form-select" id="org_public_cible" name="org_public_cible" required>
                                                        <option value="">Sélectionnez le public</option>
                                                        <option value="general">Grand public (tous âges)</option>
                                                        <option value="jeunes">Jeunes (15-35 ans)</option>
                                                        <option value="femmes">Femmes</option>
                                                        <option value="enfants">Enfants et Adolescents (0-17 ans)</option>
                                                        <option value="seniors">Personnes âgées (60+ ans)</option>
                                                        <option value="handicapes">Personnes en situation de handicap</option>
                                                        <option value="professionnels">Professionnels spécialisés</option>
                                                        <option value="etudiants">Étudiants et Élèves</option>
                                                        <option value="ruraux">Populations rurales</option>
                                                        <option value="urbains">Populations urbaines</option>
                                                        <option value="vulnerable">Populations vulnérables</option>
                                                        <option value="entrepreneurs">Entrepreneurs et PME</option>
                                                        <option value="autres">Autres publics spécifiques</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Langues de travail -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_langues" class="form-label fw-bold required">
                                                        <i class="fas fa-language me-2 text-info"></i>
                                                        Langue(s) de travail
                                                    </label>
                                                    <select class="form-select" id="org_langues" name="org_langues[]" multiple required>
                                                        <option value="francais" selected>Français (officiel)</option>
                                                        <optgroup label="Langues nationales gabonaises">
                                                            <option value="fang">Fang</option>
                                                            <option value="punu">Punu</option>
                                                            <option value="nzebi">Nzebi</option>
                                                            <option value="myene">Myéné</option>
                                                            <option value="kota">Kota</option>
                                                            <option value="teke">Téké</option>
                                                            <option value="bapounou">Bapounou</option>
                                                            <option value="eschira">Eschira</option>
                                                        </optgroup>
                                                        <optgroup label="Autres langues">
                                                            <option value="anglais">Anglais</option>
                                                            <option value="espagnol">Espagnol</option>
                                                            <option value="portugais">Portugais</option>
                                                            <option value="autre">Autre langue</option>
                                                        </optgroup>
                                                    </select>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs langues
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Activités principales -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-list-ul me-2"></i>
                                                Activités principales prévues
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info border-0 mb-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Sélectionnez les activités principales que votre organisation compte mener (maximum 5)
                                            </div>
                                            
                                            <div id="activites-container">
                                                <!-- Activités pour Association -->
                                                <div id="activites-association" class="activites-type d-none">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="organisation_evenements" id="act_events">
                                                                <label class="form-check-label" for="act_events">Organisation d'événements culturels/sportifs</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="formation_education" id="act_formation">
                                                                <label class="form-check-label" for="act_formation">Formation et éducation populaire</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="sensibilisation" id="act_sensib">
                                                                <label class="form-check-label" for="act_sensib">Sensibilisation et communication</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="accompagnement_social" id="act_accomp">
                                                                <label class="form-check-label" for="act_accomp">Accompagnement social</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="collecte_fonds" id="act_collecte">
                                                                <label class="form-check-label" for="act_collecte">Collecte de fonds et solidarité</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="partenariats" id="act_partenariat">
                                                                <label class="form-check-label" for="act_partenariat">Développement de partenariats</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="recherche" id="act_recherche">
                                                                <label class="form-check-label" for="act_recherche">Recherche et études</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="plaidoyer" id="act_plaidoyer">
                                                                <label class="form-check-label" for="act_plaidoyer">Plaidoyer et représentation</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Activités pour ONG -->
                                                <div id="activites-ong" class="activites-type d-none">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="projets_developpement" id="act_dev">
                                                                <label class="form-check-label" for="act_dev">Projets de développement communautaire</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="aide_humanitaire" id="act_humanitaire">
                                                                <label class="form-check-label" for="act_humanitaire">Aide humanitaire d'urgence</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="renforcement_capacites" id="act_capacites">
                                                                <label class="form-check-label" for="act_capacites">Renforcement des capacités</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="microfinance" id="act_microfinance">
                                                                <label class="form-check-label" for="act_microfinance">Microfinance et microcrédit</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="sante_communautaire" id="act_sante">
                                                                <label class="form-check-label" for="act_sante">Santé communautaire</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="protection_environnement" id="act_env">
                                                                <label class="form-check-label" for="act_env">Protection de l'environnement</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="droits_humains_ong" id="act_droits_ong">
                                                                <label class="form-check-label" for="act_droits_ong">Défense des droits humains</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="assistance_technique" id="act_assistance">
                                                                <label class="form-check-label" for="act_assistance">Assistance technique</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Activités pour Parti Politique -->
                                                <div id="activites-parti" class="activites-type d-none">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="campagnes_electorales" id="act_campagnes">
                                                                <label class="form-check-label" for="act_campagnes">Campagnes électorales</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="formation_politique" id="act_form_pol">
                                                                <label class="form-check-label" for="act_form_pol">Formation politique des membres</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="mobilisation_citoyenne" id="act_mobilisation">
                                                                <label class="form-check-label" for="act_mobilisation">Mobilisation citoyenne</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="elaboration_programme" id="act_programme">
                                                                <label class="form-check-label" for="act_programme">Élaboration de programmes politiques</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="representation_politique" id="act_representation">
                                                                <label class="form-check-label" for="act_representation">Représentation politique</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="communication_politique" id="act_comm_pol">
                                                                <label class="form-check-label" for="act_comm_pol">Communication politique</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Activités pour Confession Religieuse -->
                                                <div id="activites-religion" class="activites-type d-none">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="ceremonies_culte" id="act_ceremonies">
                                                                <label class="form-check-label" for="act_ceremonies">Cérémonies de culte</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="enseignement_religieux" id="act_enseignement">
                                                                <label class="form-check-label" for="act_enseignement">Enseignement religieux</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="accompagnement_spirituel" id="act_accomp_spirit">
                                                                <label class="form-check-label" for="act_accomp_spirit">Accompagnement spirituel</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="action_caritative" id="act_caritative">
                                                                <label class="form-check-label" for="act_caritative">Action caritative et sociale</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="evangelisation" id="act_evangelisation">
                                                                <label class="form-check-label" for="act_evangelisation">Évangélisation/Prédication</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="dialogue_interreligieux" id="act_dialogue">
                                                                <label class="form-check-label" for="act_dialogue">Dialogue interreligieux</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="education_morale" id="act_morale">
                                                                <label class="form-check-label" for="act_morale">Éducation morale et civique</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input activite-checkbox" type="checkbox" name="activites[]" value="preservation_traditions" id="act_traditions">
                                                                <label class="form-check-label" for="act_traditions">Préservation des traditions</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 text-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <span id="activites-counter">0</span>/5 activités sélectionnées
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Validation -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-dark text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-check-circle me-2"></i>
                                                Validation des informations de l'organisation
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="org_info_valides" name="org_info_valides" required>
                                                <label class="form-check-label fw-bold" for="org_info_valides">
                                                    <i class="fas fa-certificate me-2 text-primary"></i>
                                                    Je confirme que les informations de l'organisation sont exactes et que l'objet social respecte la législation gabonaise
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="org_unicite_nom" name="org_unicite_nom" required>
                                                <label class="form-check-label fw-bold" for="org_unicite_nom">
                                                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                                    Je certifie que le nom choisi ne porte pas atteinte aux droits de tiers et qu'à ma connaissance, aucune organisation similaire n'existe déjà
                                                </label>
                                            </div>

                                            <div class="mt-4 p-3 bg-light rounded">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1 text-info"></i>
                                                    <strong>Important :</strong> Ces informations détermineront le cadre légal d'intervention de votre organisation. 
                                                    Elles ne pourront être modifiées qu'avec des procédures spéciales après l'enregistrement.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ========== FIN SECTION E - ÉTAPE 4 : INFORMATIONS ORGANISATION ========== -->

                        <!-- ========== DÉBUT SECTION F - ÉTAPE 5 : COORDONNÉES ET GÉOLOCALISATION ========== -->
                        <!-- ÉTAPE 5: Coordonnées et géolocalisation -->
                        <div class="step-content" id="step5" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%);">
                                    <i class="fas fa-map-marker-alt fa-3x text-white"></i>
                                </div>
                                <h3 class="text-danger">Coordonnées et géolocalisation</h3>
                                <p class="text-muted">Localisation du siège social et coordonnées de contact de votre organisation</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    
                                    <!-- Section Géolocalisation Administrative -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header" style="background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%); color: white;">
                                            <h6 class="mb-0">
                                                <i class="fas fa-map-marked-alt me-2"></i>
                                                Géolocalisation administrative gabonaise
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info border-0 mb-4">
                                                <h6 class="alert-heading">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Localisation du siège social
                                                </h6>
                                                <p class="mb-0">
                                                    Renseignez l'adresse officielle où sera domiciliée votre organisation. 
                                                    Cette adresse sera mentionnée dans tous les documents officiels.
                                                </p>
                                            </div>

                                            <div class="row">
                                                <!-- Province -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_province" class="form-label fw-bold required">
                                                        <i class="fas fa-flag me-2 text-danger"></i>
                                                        Province
                                                    </label>
                                                    <select class="form-select form-select-lg" id="org_province" name="org_province" required>
                                                        <option value="">Sélectionnez une province</option>
                                                        <option value="estuaire">Estuaire</option>
                                                        <option value="haut_ogooue">Haut-Ogooué</option>
                                                        <option value="moyen_ogooue">Moyen-Ogooué</option>
                                                        <option value="ngounie">Ngounié</option>
                                                        <option value="nyanga">Nyanga</option>
                                                        <option value="ogooue_ivindo">Ogooué-Ivindo</option>
                                                        <option value="ogooue_lolo">Ogooué-Lolo</option>
                                                        <option value="ogooue_maritime">Ogooué-Maritime</option>
                                                        <option value="woleu_ntem">Woleu-Ntem</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Département -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_departement" class="form-label fw-bold">
                                                        <i class="fas fa-layer-group me-2 text-danger"></i>
                                                        Département <span class="text-muted">(si applicable)</span>
                                                    </label>
                                                    <select class="form-select" id="org_departement" name="org_departement">
                                                        <option value="">Sélectionnez d'abord une province</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Préfecture -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_prefecture" class="form-label fw-bold required">
                                                        <i class="fas fa-building me-2 text-danger"></i>
                                                        Préfecture
                                                    </label>
                                                    <select class="form-select" id="org_prefecture" name="org_prefecture" required>
                                                        <option value="">Sélectionnez d'abord une province</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Sous-préfecture -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_sous_prefecture" class="form-label fw-bold">
                                                        <i class="fas fa-map-pin me-2 text-danger"></i>
                                                        Sous-préfecture <span class="text-muted">(si applicable)</span>
                                                    </label>
                                                    <select class="form-select" id="org_sous_prefecture" name="org_sous_prefecture">
                                                        <option value="">Sélectionnez d'abord une préfecture</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Canton -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_canton" class="form-label fw-bold">
                                                        <i class="fas fa-home me-2 text-danger"></i>
                                                        Canton <span class="text-muted">(si applicable)</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_canton" 
                                                           name="org_canton" 
                                                           placeholder="Nom du canton">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Regroupement -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_regroupement" class="form-label fw-bold">
                                                        <i class="fas fa-users me-2 text-danger"></i>
                                                        Regroupement <span class="text-muted">(si applicable)</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_regroupement" 
                                                           name="org_regroupement" 
                                                           placeholder="Nom du regroupement">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Zone Type et Localisation -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-city me-2"></i>
                                                Type de zone et localisation précise
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Zone Type -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_zone_type" class="form-label fw-bold required">
                                                        <i class="fas fa-location-arrow me-2 text-warning"></i>
                                                        Type de zone
                                                    </label>
                                                    <select class="form-select" id="org_zone_type" name="org_zone_type" required>
                                                        <option value="">Sélectionnez le type</option>
                                                        <option value="urbaine">Zone urbaine</option>
                                                        <option value="rurale">Zone rurale</option>
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Ville/Commune (pour zone urbaine) -->
                                                <div class="col-md-6 mb-4" id="ville_commune_container">
                                                    <label for="org_ville_commune" class="form-label fw-bold">
                                                        <i class="fas fa-city me-2 text-warning"></i>
                                                        Ville/Commune
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_ville_commune" 
                                                           name="org_ville_commune" 
                                                           placeholder="Nom de la ville ou commune">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Arrondissement (pour zone urbaine) -->
                                                <div class="col-md-6 mb-4" id="arrondissement_container">
                                                    <label for="org_arrondissement" class="form-label fw-bold">
                                                        <i class="fas fa-map me-2 text-warning"></i>
                                                        Arrondissement
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_arrondissement" 
                                                           name="org_arrondissement" 
                                                           placeholder="Nom de l'arrondissement">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Quartier (pour zone urbaine) -->
                                                <div class="col-md-6 mb-4" id="quartier_container">
                                                    <label for="org_quartier" class="form-label fw-bold">
                                                        <i class="fas fa-home me-2 text-warning"></i>
                                                        Quartier
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_quartier" 
                                                           name="org_quartier" 
                                                           placeholder="Nom du quartier">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Village (pour zone rurale) -->
                                                <div class="col-md-6 mb-4 d-none" id="village_container">
                                                    <label for="org_village" class="form-label fw-bold">
                                                        <i class="fas fa-tree me-2 text-success"></i>
                                                        Village
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_village" 
                                                           name="org_village" 
                                                           placeholder="Nom du village">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Lieu-dit (pour zone rurale) -->
                                                <div class="col-md-6 mb-4 d-none" id="lieu_dit_container">
                                                    <label for="org_lieu_dit" class="form-label fw-bold">
                                                        <i class="fas fa-map-pin me-2 text-success"></i>
                                                        Lieu-dit <span class="text-muted">(si applicable)</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_lieu_dit" 
                                                           name="org_lieu_dit" 
                                                           placeholder="Nom du lieu-dit">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Adresse Complète -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-address-book me-2"></i>
                                                Adresse complète du siège social
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Adresse détaillée -->
                                            <div class="mb-4">
                                                <label for="org_adresse_complete" class="form-label fw-bold required">
                                                    <i class="fas fa-home me-2 text-info"></i>
                                                    Adresse complète et détaillée
                                                </label>
                                                <textarea class="form-control" 
                                                          id="org_adresse_complete" 
                                                          name="org_adresse_complete" 
                                                          rows="3" 
                                                          placeholder="Numéro, rue, avenue, boulevard, précisions sur la localisation..."
                                                          maxlength="500"
                                                          required></textarea>
                                                <div class="form-text">
                                                    <i class="fas fa-info me-1"></i>
                                                    Soyez le plus précis possible pour faciliter la localisation
                                                    <span class="float-end"><span id="adresse-counter">0</span>/500 caractères</span>
                                                </div>
                                                <div class="invalid-feedback"></div>
                                            </div>

                                            <!-- Points de repère -->
                                            <div class="mb-4">
                                                <label for="org_points_repere" class="form-label fw-bold">
                                                    <i class="fas fa-landmark me-2 text-info"></i>
                                                    Points de repère <span class="text-muted">(optionnel)</span>
                                                </label>
                                                <textarea class="form-control" 
                                                          id="org_points_repere" 
                                                          name="org_points_repere" 
                                                          rows="2" 
                                                          placeholder="Près de l'école primaire X, en face du marché Y, à côté de la pharmacie Z..."
                                                          maxlength="300"></textarea>
                                                <div class="form-text">
                                                    <i class="fas fa-info me-1"></i>
                                                    Mentionnez des bâtiments, commerces ou lieux connus dans la zone
                                                    <span class="float-end"><span id="repere-counter">0</span>/300 caractères</span>
                                                </div>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Coordonnées GPS (Optionnel) -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-globe me-2"></i>
                                                Coordonnées GPS <span class="badge bg-light text-dark ms-2">Optionnel</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-success border-0 mb-3">
                                                <h6 class="alert-heading">
                                                    <i class="fas fa-satellite me-2"></i>
                                                    Géolocalisation précise
                                                </h6>
                                                <p class="mb-0">
                                                    Si vous connaissez les coordonnées GPS exactes de votre siège social, 
                                                    renseignez-les pour une localisation optimale.
                                                </p>
                                            </div>

                                            <div class="row">
                                                <!-- Latitude -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_latitude" class="form-label fw-bold">
                                                        <i class="fas fa-compass me-2 text-success"></i>
                                                        Latitude
                                                    </label>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           id="org_latitude" 
                                                           name="org_latitude" 
                                                           step="0.0000001"
                                                           min="-3"
                                                           max="3"
                                                           placeholder="Ex: 0.3901"
                                                           title="Latitude pour le Gabon (entre -3 et 3)">
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Latitude pour le Gabon (entre -3° et 3°)
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Longitude -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_longitude" class="form-label fw-bold">
                                                        <i class="fas fa-globe-americas me-2 text-success"></i>
                                                        Longitude
                                                    </label>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           id="org_longitude" 
                                                           name="org_longitude" 
                                                           step="0.0000001"
                                                           min="8"
                                                           max="15"
                                                           placeholder="Ex: 9.4540"
                                                           title="Longitude pour le Gabon (entre 8 et 15)">
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Longitude pour le Gabon (entre 8° et 15°)
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>

                                            <!-- Bouton de géolocalisation automatique -->
                                            <div class="text-center">
                                                <button type="button" class="btn btn-outline-success" id="getCurrentLocation">
                                                    <i class="fas fa-crosshairs me-2"></i>
                                                    Utiliser ma position actuelle
                                                </button>
                                                <div class="form-text mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Vous devez être physiquement au siège social pour utiliser cette fonction
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Coordonnées de Contact -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-phone me-2"></i>
                                                Coordonnées de contact de l'organisation
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Téléphone principal -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_telephone" class="form-label fw-bold required">
                                                        <i class="fas fa-phone me-2 text-primary"></i>
                                                        Téléphone principal
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-primary text-white">+241</span>
                                                        <input type="tel" 
                                                               class="form-control" 
                                                               id="org_telephone" 
                                                               name="org_telephone" 
                                                               placeholder="XX XX XX XX"
                                                               pattern="[0-9\s]{8,12}"
                                                               maxlength="12"
                                                               required>
                                                    </div>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Numéro principal de contact de l'organisation
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Téléphone secondaire -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_telephone_2" class="form-label fw-bold">
                                                        <i class="fas fa-mobile-alt me-2 text-primary"></i>
                                                        Téléphone secondaire <span class="text-muted">(optionnel)</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">+241</span>
                                                        <input type="tel" 
                                                               class="form-control" 
                                                               id="org_telephone_2" 
                                                               name="org_telephone_2" 
                                                               placeholder="XX XX XX XX"
                                                               pattern="[0-9\s]{8,12}"
                                                               maxlength="12">
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Email principal -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_email" class="form-label fw-bold required">
                                                        <i class="fas fa-envelope me-2 text-primary"></i>
                                                        Email principal
                                                    </label>
                                                    <input type="email" 
                                                           class="form-control" 
                                                           id="org_email" 
                                                           name="org_email" 
                                                           placeholder="contact@organisation.ga"
                                                           required>
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Adresse email officielle de l'organisation
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Email secondaire -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_email_2" class="form-label fw-bold">
                                                        <i class="fas fa-envelope-open me-2 text-primary"></i>
                                                        Email secondaire <span class="text-muted">(optionnel)</span>
                                                    </label>
                                                    <input type="email" 
                                                           class="form-control" 
                                                           id="org_email_2" 
                                                           name="org_email_2" 
                                                           placeholder="info@organisation.ga">
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Site web -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_site_web" class="form-label fw-bold">
                                                        <i class="fas fa-globe me-2 text-primary"></i>
                                                        Site Web <span class="text-muted">(optionnel)</span>
                                                    </label>
                                                    <input type="url" 
                                                           class="form-control" 
                                                           id="org_site_web" 
                                                           name="org_site_web" 
                                                           placeholder="https://www.organisation.ga">
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Site internet officiel (si existant)
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <!-- Réseaux sociaux -->
                                                <div class="col-md-6 mb-4">
                                                    <label for="org_reseaux_sociaux" class="form-label fw-bold">
                                                        <i class="fas fa-share-alt me-2 text-primary"></i>
                                                        Réseaux sociaux <span class="text-muted">(optionnel)</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="org_reseaux_sociaux" 
                                                           name="org_reseaux_sociaux" 
                                                           placeholder="Facebook, Instagram, Twitter...">
                                                    <div class="form-text">
                                                        <i class="fas fa-info me-1"></i>
                                                        Comptes sur les réseaux sociaux
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Validation -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-dark text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-check-circle me-2"></i>
                                                Validation des coordonnées
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="coordonnees_exactes" name="coordonnees_exactes" required>
                                                <label class="form-check-label fw-bold" for="coordonnees_exactes">
                                                    <i class="fas fa-map-marked-alt me-2 text-danger"></i>
                                                    Je certifie que l'adresse fournie est exacte et que l'organisation y sera effectivement domiciliée
                                                </label>
                                            </div>

                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="justificatif_disponible" name="justificatif_disponible" required>
                                                <label class="form-check-label fw-bold" for="justificatif_disponible">
                                                    <i class="fas fa-file-contract me-2 text-warning"></i>
                                                    Je dispose du justificatif de domiciliation (bail, facture, attestation de propriété) pour cette adresse
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="acces_autorise" name="acces_autorise" required>
                                                <label class="form-check-label fw-bold" for="acces_autorise">
                                                    <i class="fas fa-key me-2 text-success"></i>
                                                    J'autorise les services compétents à vérifier cette adresse si nécessaire dans le cadre de l'instruction du dossier
                                                </label>
                                            </div>

                                            <div class="mt-4 p-3 bg-light rounded">
                                                <small class="text-muted">
                                                    <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                                    <strong>Important :</strong> Le siège social détermine la compétence territoriale 
                                                    des autorités administratives et judiciaires. Une adresse inexacte peut entraîner 
                                                    le rejet du dossier ou des complications ultérieures.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ========== FIN SECTION F - ÉTAPE 5 : COORDONNÉES ET GÉOLOCALISATION ========== -->

                        <!-- ========== DÉBUT SECTION G - ÉTAPE 6 : FONDATEURS ========== -->
                        <!-- ÉTAPE 6: Fondateurs -->
                        <div class="step-content" id="step6" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                    <i class="fas fa-users fa-3x text-white"></i>
                                </div>
                                <h3 class="text-success">Fondateurs de l'organisation</h3>
                                <p class="text-muted">Ajoutez les personnes fondatrices de votre organisation</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    
                                    <!-- Information sur les exigences -->
                                    <div class="alert alert-info border-0 mb-4 shadow-sm">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h6 class="alert-heading mb-2">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Exigences selon le type d'organisation
                                                </h6>
                                                <div id="fondateurs-requirements">
                                                    <!-- Contenu dynamique selon le type d'organisation -->
                                                    <div id="req-association" class="requirements-info d-none">
                                                        <strong>Association :</strong> Minimum 3 fondateurs majeurs
                                                    </div>
                                                    <div id="req-ong" class="requirements-info d-none">
                                                        <strong>ONG :</strong> Minimum 5 fondateurs avec expérience dans le domaine social
                                                    </div>
                                                    <div id="req-parti_politique" class="requirements-info d-none">
                                                        <strong>Parti Politique :</strong> Minimum 3 fondateurs, chacun ne peut être fondateur que d'un seul parti
                                                    </div>
                                                    <div id="req-confession_religieuse" class="requirements-info d-none">
                                                        <strong>Confession Religieuse :</strong> Minimum 3 fondateurs avec au moins un responsable religieux qualifié
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="badge bg-success fs-6 px-3 py-2">
                                                    <i class="fas fa-users me-2"></i>
                                                    <span id="fondateurs-count">0</span> fondateur(s)
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Conteneur des fondateurs -->
                                    <div id="fondateurs-container">
                                        <!-- Le premier fondateur sera ajouté automatiquement -->
                                    </div>

                                    <!-- Bouton d'ajout -->
                                    <div class="text-center mb-4">
                                        <button type="button" class="btn btn-success btn-lg" id="ajouterFondateur">
                                            <i class="fas fa-plus me-2"></i>
                                            Ajouter un fondateur
                                        </button>
                                        <div class="form-text mt-2">
                                            <span id="fondateurs-status">Ajoutez au minimum <span id="min-required">3</span> fondateurs pour continuer</span>
                                        </div>
                                    </div>

                                    <!-- Section Vérification des doublons -->
                                    <div class="card border-0 shadow-sm mb-4" id="verification-section" style="display: none;">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Vérification des doublons
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="doublons-internes" class="mb-3" style="display: none;">
                                                <h6 class="text-danger">
                                                    <i class="fas fa-times-circle me-2"></i>
                                                    Doublons détectés dans votre liste
                                                </h6>
                                                <div id="doublons-internes-list" class="alert alert-danger">
                                                    <!-- Liste des doublons internes -->
                                                </div>
                                                <p class="text-muted">Ces doublons doivent être corrigés avant de pouvoir continuer.</p>
                                            </div>

                                            <div id="doublons-externes" class="mb-3" style="display: none;">
                                                <h6 class="text-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    NIP déjà enregistrés dans d'autres organisations
                                                </h6>
                                                <div id="doublons-externes-list" class="alert alert-warning">
                                                    <!-- Liste des doublons externes -->
                                                </div>
                                                <p class="text-muted">Ces personnes sont déjà fondateurs ou membres d'autres organisations. Des justificatifs de démission pourraient être requis.</p>
                                            </div>

                                            <div id="verification-propre" class="alert alert-success" style="display: none;">
                                                <i class="fas fa-check-circle me-2"></i>
                                                Aucun doublon détecté. Tous les NIP sont uniques et disponibles.
                                            </div>

                                            <div class="text-center">
                                                <button type="button" class="btn btn-primary" id="verifierDoublons">
                                                    <i class="fas fa-search me-2"></i>
                                                    Vérifier les doublons
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Récapitulatif -->
                                    <div class="card border-0 shadow-sm mb-4" id="recap-fondateurs" style="display: none;">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-list me-2"></i>
                                                Récapitulatif des fondateurs
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover mb-0">
                                                    <thead class="table-primary">
                                                        <tr>
                                                            <th>NIP</th>
                                                            <th>Nom complet</th>
                                                            <th>Fonction</th>
                                                            <th>Email</th>
                                                            <th>Téléphone</th>
                                                            <th>Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="recap-fondateurs-body">
                                                        <!-- Contenu généré dynamiquement -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Validation -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-dark text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-check-circle me-2"></i>
                                                Validation des fondateurs
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="fondateurs_consentement" name="fondateurs_consentement" required>
                                                <label class="form-check-label fw-bold" for="fondateurs_consentement">
                                                    <i class="fas fa-user-check me-2 text-success"></i>
                                                    Je certifie que tous les fondateurs mentionnés ont donné leur accord explicite pour figurer dans ce dossier de création d'organisation
                                                </label>
                                            </div>

                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="fondateurs_documents" name="fondateurs_documents" required>
                                                <label class="form-check-label fw-bold" for="fondateurs_documents">
                                                    <i class="fas fa-id-card me-2 text-primary"></i>
                                                    Je dispose de toutes les pièces d'identité (CNI) des fondateurs et les informations fournies sont exactes
                                                </label>
                                            </div>

                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="fondateurs_engagement" name="fondateurs_engagement" required>
                                                <label class="form-check-label fw-bold" for="fondateurs_engagement">
                                                    <i class="fas fa-handshake me-2 text-warning"></i>
                                                    Tous les fondateurs s'engagent à respecter les statuts de l'organisation et la législation gabonaise
                                                </label>
                                            </div>

                                            <div class="form-check" id="parti-politique-exclusivite" style="display: none;">
                                                <input class="form-check-input" type="checkbox" id="fondateurs_exclusivite" name="fondateurs_exclusivite">
                                                <label class="form-check-label fw-bold" for="fondateurs_exclusivite">
                                                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                                    <strong>Parti Politique :</strong> Je certifie qu'aucun des fondateurs n'est actuellement membre d'un autre parti politique
                                                </label>
                                            </div>

                                            <div class="mt-4 p-3 bg-light rounded">
                                                <small class="text-muted">
                                                    <i class="fas fa-shield-alt me-1 text-info"></i>
                                                    <strong>Responsabilité :</strong> Les fondateurs sont les responsables légaux de l'organisation 
                                                    et peuvent être tenus pour responsables de ses actions. Assurez-vous de la fiabilité 
                                                    et de l'engagement de chaque personne ajoutée comme fondateur.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Template pour un fondateur -->
                        <template id="fondateur-template">
                            <div class="card border-0 shadow-sm mb-4 fondateur-card">
                                <div class="card-header bg-success text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user me-2"></i>
                                            <span class="fondateur-title">Fondateur 1</span>
                                        </h6>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-light me-2 toggle-fondateur" title="Réduire/Développer">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-light remove-fondateur" title="Supprimer ce fondateur">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body fondateur-body">
                                    <div class="row">
                                        <!-- NIP -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-hashtag me-2 text-success"></i>
                                                NIP (Numéro d'Identification Personnel)
                                            </label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control fondateur-nip" 
                                                       name="fondateurs[INDEX][nip]" 
                                                       placeholder="13 chiffres"
                                                       maxlength="13"
                                                       pattern="[0-9]{13}"
                                                       required>
                                                <span class="input-group-text nip-status">
                                                    <i class="fas fa-clock text-muted"></i>
                                                </span>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info me-1"></i>
                                                13 chiffres exactement
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Nom -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-signature me-2 text-success"></i>
                                                Nom de famille
                                            </label>
                                            <input type="text" 
                                                   class="form-control fondateur-nom" 
                                                   name="fondateurs[INDEX][nom]" 
                                                   placeholder="Nom de famille"
                                                   style="text-transform: uppercase;"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Prénoms -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-user me-2 text-success"></i>
                                                Prénom(s)
                                            </label>
                                            <input type="text" 
                                                   class="form-control fondateur-prenoms" 
                                                   name="fondateurs[INDEX][prenoms]" 
                                                   placeholder="Prénoms complets"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Fonction -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-briefcase me-2 text-success"></i>
                                                Fonction dans l'organisation
                                            </label>
                                            <select class="form-select fondateur-fonction" name="fondateurs[INDEX][fonction]" required>
                                                <option value="">Choisissez une fonction</option>
                                                <option value="president">Président(e)</option>
                                                <option value="vice_president">Vice-Président(e)</option>
                                                <option value="secretaire_general">Secrétaire Général(e)</option>
                                                <option value="secretaire_general_adjoint">Secrétaire Général(e) Adjoint(e)</option>
                                                <option value="tresorier">Trésorier(ère)</option>
                                                <option value="tresorier_adjoint">Trésorier(ère) Adjoint(e)</option>
                                                <option value="directeur_executif">Directeur(trice) Exécutif(ve)</option>
                                                <option value="coordinateur">Coordinateur(trice)</option>
                                                <option value="responsable_programme">Responsable Programme</option>
                                                <option value="conseiller">Conseiller(ère)</option>
                                                <option value="membre_fondateur">Membre Fondateur</option>
                                                <option value="autre">Autre fonction</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Date de naissance -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-calendar-alt me-2 text-success"></i>
                                                Date de naissance
                                            </label>
                                            <input type="date" 
                                                   class="form-control fondateur-date-naissance" 
                                                   name="fondateurs[INDEX][date_naissance]" 
                                                   required>
                                            <div class="form-text">
                                                <i class="fas fa-info me-1"></i>
                                                Doit être majeur (18 ans minimum)
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Lieu de naissance -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                                Lieu de naissance
                                            </label>
                                            <input type="text" 
                                                   class="form-control fondateur-lieu-naissance" 
                                                   name="fondateurs[INDEX][lieu_naissance]" 
                                                   placeholder="Ville, Pays"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Sexe -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-venus-mars me-2 text-success"></i>
                                                Sexe
                                            </label>
                                            <select class="form-select fondateur-sexe" name="fondateurs[INDEX][sexe]" required>
                                                <option value="">Sélectionnez</option>
                                                <option value="M">Masculin</option>
                                                <option value="F">Féminin</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Nationalité -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-flag me-2 text-success"></i>
                                                Nationalité
                                            </label>
                                            <select class="form-select fondateur-nationalite" name="fondateurs[INDEX][nationalite]" required>
                                                <option value="">Sélectionnez</option>
                                                <option value="Gabonaise" selected>Gabonaise</option>
                                                <option value="Française">Française</option>
                                                <option value="Camerounaise">Camerounaise</option>
                                                <option value="Équato-guinéenne">Équato-guinéenne</option>
                                                <option value="Congolaise (Brazzaville)">Congolaise (Brazzaville)</option>
                                                <option value="Congolaise (RDC)">Congolaise (RDC)</option>
                                                <option value="Tchadienne">Tchadienne</option>
                                                <option value="Centrafricaine">Centrafricaine</option>
                                                <option value="Autre">Autre</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-envelope me-2 text-success"></i>
                                                Email
                                            </label>
                                            <input type="email" 
                                                   class="form-control fondateur-email" 
                                                   name="fondateurs[INDEX][email]" 
                                                   placeholder="email@exemple.com"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Téléphone -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-phone me-2 text-success"></i>
                                                Téléphone
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">+241</span>
                                                <input type="tel" 
                                                       class="form-control fondateur-telephone" 
                                                       name="fondateurs[INDEX][telephone]" 
                                                       placeholder="XX XX XX XX"
                                                       pattern="[0-9\s]{8,12}"
                                                       maxlength="12"
                                                       required>
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Téléphone secondaire -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-mobile-alt me-2 text-success"></i>
                                                Téléphone secondaire <span class="text-muted">(optionnel)</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">+241</span>
                                                <input type="tel" 
                                                       class="form-control fondateur-telephone-2" 
                                                       name="fondateurs[INDEX][telephone_secondaire]" 
                                                       placeholder="XX XX XX XX"
                                                       pattern="[0-9\s]{8,12}"
                                                       maxlength="12">
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- Adresse -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                <i class="fas fa-home me-2 text-success"></i>
                                                Adresse personnelle
                                            </label>
                                            <textarea class="form-control fondateur-adresse" 
                                                      name="fondateurs[INDEX][adresse]" 
                                                      rows="3" 
                                                      placeholder="Adresse complète de résidence"
                                                      required></textarea>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <!-- CNI -->
                                        <div class="col-12">
                                            <div class="border rounded p-3 bg-light">
                                                <h6 class="text-primary mb-3">
                                                    <i class="fas fa-id-card me-2"></i>
                                                    Informations CNI
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold required">Numéro CNI</label>
                                                        <input type="text" 
                                                               class="form-control fondateur-cni-numero" 
                                                               name="fondateurs[INDEX][cni_numero]" 
                                                               placeholder="Numéro de la CNI"
                                                               required>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold required">Type de pièce</label>
                                                        <select class="form-select fondateur-type-piece" name="fondateurs[INDEX][type_piece]" required>
                                                            <option value="">Sélectionnez</option>
                                                            <option value="CNI" selected>Carte Nationale d'Identité</option>
                                                            <option value="Passeport">Passeport</option>
                                                            <option value="Titre_sejour">Titre de séjour</option>
                                                        </select>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <!-- ========== FIN SECTION G - ÉTAPE 6 : FONDATEURS ========== -->

                        <!-- ========== DÉBUT SECTION H - ÉTAPE 7 : ADHÉRENTS ========== -->
<!-- ÉTAPE 7: Adhérents -->
<div class="step-content" id="step7" style="display: none;">
    <div class="text-center mb-4">
        <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
            <i class="fas fa-user-plus fa-3x text-white"></i>
        </div>
        <h3 class="text-info">Adhérents de l'organisation</h3>
        <p class="text-muted">Ajoutez les adhérents par saisie manuelle ou import de fichier</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-11">
            
            <!-- Information sur les exigences -->
            <div class="alert alert-info border-0 mb-4 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="alert-heading mb-2">
                            <i class="fas fa-info-circle me-2"></i>
                            Nombre minimum d'adhérents requis
                        </h6>
                        <div id="adherents-requirements">
                            <!-- Contenu dynamique selon le type d'organisation -->
                            <div id="req-adherents-association" class="requirements-info d-none">
                                <strong>Association :</strong> Minimum 10 adhérents
                            </div>
                            <div id="req-adherents-ong" class="requirements-info d-none">
                                <strong>ONG :</strong> Minimum 15 adhérents
                            </div>
                            <div id="req-adherents-parti" class="requirements-info d-none">
                                <strong>Parti Politique :</strong> Minimum 50 adhérents
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Chaque adhérent ne peut être membre que d'un seul parti politique
                                </div>
                            </div>
                            <div id="req-adherents-religion" class="requirements-info d-none">
                                <strong>Confession Religieuse :</strong> Minimum 10 fidèles
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end align-items-center gap-3">
                            <div class="badge bg-success fs-6 px-3 py-2">
                                <i class="fas fa-users me-2"></i>
                                <span id="adherents-count">0</span> adhérent(s)
                            </div>
                            <div class="badge bg-warning fs-6 px-3 py-2" id="adherents-status-badge">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span id="adherents-status-text">Insuffisant</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Options d'ajout -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Méthodes d'ajout des adhérents
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-primary btn-lg" onclick="showManualEntry()">
                                    <i class="fas fa-user-edit fa-2x mb-2"></i>
                                    <br>
                                    Saisie manuelle
                                    <br>
                                    <small class="text-muted">Ajouter un par un</small>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-success btn-lg" onclick="showImportSection()">
                                    <i class="fas fa-file-excel fa-2x mb-2"></i>
                                    <br>
                                    Import Excel/CSV
                                    <br>
                                    <small class="text-muted">Charger une liste complète</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Saisie Manuelle -->
            <div class="card border-0 shadow-sm mb-4" id="manual-entry-section" style="display: none;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Saisie manuelle d'un adhérent
                    </h6>
                </div>
                <div class="card-body">
                    <form id="adherent-manual-form">
                        <div class="row">
                            <!-- NIP -->
                            <div class="col-md-4 mb-3">
                                <label for="adherent_nip" class="form-label fw-bold required">
                                    <i class="fas fa-hashtag me-2 text-primary"></i>
                                    NIP (13 chiffres)
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="adherent_nip" 
                                           name="adherent_nip" 
                                           placeholder="1234567890123"
                                           maxlength="13"
                                           pattern="[0-9]{13}"
                                           required>
                                    <span class="input-group-text" id="nip-check-status">
                                        <i class="fas fa-clock text-muted"></i>
                                    </span>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Nom -->
                            <div class="col-md-4 mb-3">
                                <label for="adherent_nom" class="form-label fw-bold required">
                                    <i class="fas fa-signature me-2 text-primary"></i>
                                    Nom
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="adherent_nom" 
                                       name="adherent_nom" 
                                       placeholder="Nom de famille"
                                       style="text-transform: uppercase;"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Prénoms -->
                            <div class="col-md-4 mb-3">
                                <label for="adherent_prenom" class="form-label fw-bold required">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    Prénom(s)
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="adherent_prenom" 
                                       name="adherent_prenom" 
                                       placeholder="Prénoms"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Date de naissance -->
                            <div class="col-md-3 mb-3">
                                <label for="adherent_date_naissance" class="form-label fw-bold">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                    Date de naissance
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="adherent_date_naissance" 
                                       name="adherent_date_naissance">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Sexe -->
                            <div class="col-md-3 mb-3">
                                <label for="adherent_sexe" class="form-label fw-bold">
                                    <i class="fas fa-venus-mars me-2 text-primary"></i>
                                    Sexe
                                </label>
                                <select class="form-select" id="adherent_sexe" name="adherent_sexe">
                                    <option value="">Non précisé</option>
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-3 mb-3">
                                <label for="adherent_email" class="form-label fw-bold">
                                    <i class="fas fa-envelope me-2 text-primary"></i>
                                    Email
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="adherent_email" 
                                       name="adherent_email" 
                                       placeholder="email@exemple.com">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Téléphone -->
                            <div class="col-md-3 mb-3">
                                <label for="adherent_telephone" class="form-label fw-bold">
                                    <i class="fas fa-phone me-2 text-primary"></i>
                                    Téléphone
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">+241</span>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="adherent_telephone" 
                                           name="adherent_telephone" 
                                           placeholder="XX XX XX XX"
                                           pattern="[0-9\s]{8,12}"
                                           maxlength="12">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" onclick="hideManualEntry()">
                                <i class="fas fa-times me-2"></i>Annuler
                            </button>
                            <button type="button" class="btn btn-primary" onclick="addAdherent()">
                                <i class="fas fa-plus me-2"></i>Ajouter l'adhérent
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Section Import Excel/CSV -->
            <div class="card border-0 shadow-sm mb-4" id="import-section" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-file-excel me-2"></i>
                        Import de fichier Excel/CSV
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Instructions pour l'import
                        </h6>
                        <ol class="mb-0">
                            <li>Téléchargez le fichier modèle ci-dessous</li>
                            <li>Remplissez-le avec les informations de vos adhérents</li>
                            <li>Respectez le format des colonnes (NIP sur 13 chiffres, etc.)</li>
                            <li>Sauvegardez en format Excel (.xlsx) ou CSV (.csv)</li>
                            <li>Importez votre fichier complété</li>
                        </ol>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="/templates/adherents_template.xlsx" class="btn btn-outline-success" download>
                                    <i class="fas fa-download me-2"></i>
                                    Télécharger le modèle Excel
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="/templates/adherents_template.csv" class="btn btn-outline-info" download>
                                    <i class="fas fa-download me-2"></i>
                                    Télécharger le modèle CSV
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 p-4 border rounded bg-light">
                        <label for="adherents_file" class="form-label fw-bold">
                            <i class="fas fa-upload me-2 text-success"></i>
                            Sélectionner votre fichier d'adhérents
                        </label>
                        <input type="file" 
                               class="form-control form-control-lg" 
                               id="adherents_file" 
                               name="adherents_file" 
                               accept=".xlsx,.xls,.csv">
                        <div class="form-text">
                            Formats acceptés : Excel (.xlsx, .xls) ou CSV (.csv)
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" onclick="hideImportSection()">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-success" onclick="importAdherents()">
                            <i class="fas fa-upload me-2"></i>Importer le fichier
                        </button>
                    </div>
                </div>
            </div>

            <!-- Liste des adhérents -->
            <div class="card border-0 shadow-sm mb-4" id="adherents-list-section">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Liste des adhérents
                        </h6>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light" onclick="verifyAdherents()">
                                <i class="fas fa-check-double me-1"></i>
                                Vérifier les doublons
                            </button>
                            <button type="button" class="btn btn-sm btn-light" onclick="exportAdherents()">
                                <i class="fas fa-download me-1"></i>
                                Exporter
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres et recherche -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="adherents-search" 
                                       placeholder="Rechercher par NIP, nom ou prénom...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" id="adherents-filter">
                                <option value="">Tous les statuts</option>
                                <option value="valide">✅ Valides</option>
                                <option value="doublon">🔴 Doublons internes</option>
                                <option value="externe">🟡 Existant ailleurs</option>
                                <option value="attente">⏳ En attente</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table des adhérents -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="adherents-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" class="form-check-input" id="select-all-adherents">
                                    </th>
                                    <th>NIP</th>
                                    <th>Nom</th>
                                    <th>Prénom(s)</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Statut</th>
                                    <th>Organisation actuelle</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="adherents-tbody">
                                <!-- Les adhérents seront ajoutés dynamiquement ici -->
                                <tr id="no-adherents-row">
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-3 d-block opacity-25"></i>
                                        Aucun adhérent ajouté pour le moment
                                        <br>
                                        <small>Utilisez les boutons ci-dessus pour ajouter des adhérents</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Légende des statuts -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6 class="mb-2">Légende des statuts :</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <span class="badge bg-success me-2">✅ Valide</span>
                                <small>NIP unique et disponible</small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-danger me-2">🔴 Doublon interne</span>
                                <small>NIP déjà dans la liste</small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-warning me-2">🟡 En attente</span>
                                <small>Membre d'une autre organisation</small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-secondary me-2">⏳ À vérifier</span>
                                <small>Vérification en cours</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Gestion des conflits -->
            <div class="card border-0 shadow-sm mb-4" id="conflicts-section" style="display: none;">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Gestion des conflits d'adhésion
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Adhérents nécessitant des justificatifs
                        </h6>
                        <p>Les adhérents suivants sont déjà membres d'autres organisations. 
                           Vous devez fournir des justificatifs de démission pour continuer.</p>
                    </div>

                    <div id="conflicts-list">
                        <!-- Les conflits seront affichés dynamiquement ici -->
                    </div>
                </div>
            </div>

            <!-- Section Validation -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Validation de la liste des adhérents
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading">Récapitulatif</h6>
                                <ul class="mb-0">
                                    <li>Total adhérents : <strong id="recap-total">0</strong></li>
                                    <li>Adhérents valides : <strong id="recap-valides" class="text-success">0</strong></li>
                                    <li>En attente de validation : <strong id="recap-attente" class="text-warning">0</strong></li>
                                    <li>À corriger : <strong id="recap-erreurs" class="text-danger">0</strong></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning mb-0" id="minimum-alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Nombre minimum requis
                                </h6>
                                <p class="mb-0">
                                    Vous devez avoir au minimum <strong id="minimum-required">10</strong> adhérents valides 
                                    pour soumettre votre dossier.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="adherents_consent" name="adherents_consent" required>
                        <label class="form-check-label fw-bold" for="adherents_consent">
                            <i class="fas fa-user-check me-2 text-success"></i>
                            Je certifie que tous les adhérents listés ont donné leur consentement pour figurer dans cette organisation
                        </label>
                    </div>

                    <div class="form-check mb-3" id="parti-adherents-check" style="display: none;">
                        <input class="form-check-input" type="checkbox" id="adherents_exclusivity" name="adherents_exclusivity">
                        <label class="form-check-label fw-bold" for="adherents_exclusivity">
                            <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                            <strong>Parti Politique :</strong> Je certifie qu'aucun adhérent n'est membre d'un autre parti politique
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="adherents_documents" name="adherents_documents" required>
                        <label class="form-check-label fw-bold" for="adherents_documents">
                            <i class="fas fa-file-check me-2 text-primary"></i>
                            Je dispose de tous les documents nécessaires (justificatifs de démission le cas échéant)
                        </label>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1 text-info"></i>
                            <strong>Protection des données :</strong> Les informations personnelles des adhérents 
                            sont protégées conformément à la législation gabonaise sur la protection des données personnelles.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour upload de justificatif -->
<div class="modal fade" id="justificatifModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-file-upload me-2"></i>
                    Justificatif de démission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    L'adhérent <strong id="adherent-conflict-name"></strong> (NIP: <span id="adherent-conflict-nip"></span>) 
                    est actuellement membre de : <strong id="adherent-conflict-org"></strong>
                </div>
                
                <div class="mb-3">
                    <label for="justificatif_file" class="form-label fw-bold">
                        <i class="fas fa-file-pdf me-2"></i>
                        Document justificatif
                    </label>
                    <input type="file" 
                           class="form-control" 
                           id="justificatif_file" 
                           accept=".pdf,.jpg,.png">
                    <div class="form-text">
                        Formats acceptés : PDF, JPG, PNG (max 5MB)
                    </div>
                </div>

                <div class="mb-3">
                    <label for="justificatif_type" class="form-label fw-bold">
                        <i class="fas fa-list me-2"></i>
                        Type de justificatif
                    </label>
                    <select class="form-select" id="justificatif_type" required>
                        <option value="">Sélectionnez le type</option>
                        <option value="demission">Lettre de démission</option>
                        <option value="notification">Notification d'exclusion</option>
                        <option value="attestation">Attestation de non-appartenance</option>
                        <option value="autre">Autre document</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="justificatif_notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes complémentaires
                    </label>
                    <textarea class="form-control" 
                              id="justificatif_notes" 
                              rows="3" 
                              placeholder="Informations supplémentaires..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="uploadJustificatif()">
                    <i class="fas fa-upload me-2"></i>Envoyer le justificatif
                </button>
            </div>
        </div>
    </div>
</div>
<!-- ========== FIN SECTION H - ÉTAPE 7 : ADHÉRENTS ========== -->

<!-- ========== DÉBUT SECTION I - ÉTAPE 8 : DOCUMENTS ========== -->
<!-- ÉTAPE 8: Documents -->
<div class="step-content" id="step8" style="display: none;">
    <div class="text-center mb-4">
        <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #6c757d 0%, #343a40 100%);">
            <i class="fas fa-file-alt fa-3x text-white"></i>
        </div>
        <h3 class="text-secondary">Documents justificatifs</h3>
        <p class="text-muted">Téléchargez tous les documents requis pour votre dossier</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-11">
            
            <!-- Informations générales -->
            <div class="alert alert-info border-0 mb-4 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="alert-heading mb-2">
                            <i class="fas fa-info-circle me-2"></i>
                            Instructions pour les documents
                        </h6>
                        <ul class="mb-0">
                            <li>Formats acceptés : <strong>PDF, JPG, PNG</strong></li>
                            <li>Taille maximale par fichier : <strong>5 MB</strong></li>
                            <li>Les documents doivent être <strong>lisibles et complets</strong></li>
                            <li>Les documents obligatoires sont marqués d'un <span class="badge bg-danger">*</span></li>
                        </ul>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end align-items-center gap-3">
                            <div class="text-center">
                                <div class="fs-1 fw-bold text-primary" id="docs-uploaded-count">0</div>
                                <small class="text-muted">Documents uploadés</small>
                            </div>
                            <div class="text-center">
                                <div class="fs-1 fw-bold text-danger" id="docs-required-count">0</div>
                                <small class="text-muted">Obligatoires manquants</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Communs à tous -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-folder me-2"></i>
                        Documents communs obligatoires
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Statuts -->
                        <div class="col-md-6 mb-4">
                            <div class="document-upload-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-file-contract text-primary me-2"></i>
                                            Statuts de l'organisation
                                            <span class="badge bg-danger ms-1">*</span>
                                        </h6>
                                        <small class="text-muted">Document officiel définissant les règles de fonctionnement</small>
                                    </div>
                                    <div class="document-status" id="status-statuts">
                                        <i class="fas fa-clock text-muted"></i>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control document-input" 
                                           id="doc-statuts" 
                                           name="documents[statuts]"
                                           accept=".pdf,.jpg,.png"
                                           data-doc-type="statuts"
                                           data-required="true"
                                           onchange="handleDocumentUpload(this)">
                                    <button class="btn btn-outline-secondary btn-preview d-none" 
                                            type="button"
                                            onclick="previewDocument('statuts')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">3 exemplaires signés et paraphés requis</div>
                            </div>
                        </div>

                        <!-- PV AG Constitutive -->
                        <div class="col-md-6 mb-4">
                            <div class="document-upload-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-gavel text-primary me-2"></i>
                                            PV de l'AG constitutive
                                            <span class="badge bg-danger ms-1">*</span>
                                        </h6>
                                        <small class="text-muted">Procès-verbal de l'assemblée générale constitutive</small>
                                    </div>
                                    <div class="document-status" id="status-pv_ag">
                                        <i class="fas fa-clock text-muted"></i>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control document-input" 
                                           id="doc-pv_ag" 
                                           name="documents[pv_ag]"
                                           accept=".pdf,.jpg,.png"
                                           data-doc-type="pv_ag"
                                           data-required="true"
                                           onchange="handleDocumentUpload(this)">
                                    <button class="btn btn-outline-secondary btn-preview d-none" 
                                            type="button"
                                            onclick="previewDocument('pv_ag')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Avec adoption des statuts et élection du bureau</div>
                            </div>
                        </div>

                        <!-- Liste des fondateurs -->
                        <div class="col-md-6 mb-4">
                            <div class="document-upload-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-users text-primary me-2"></i>
                                            Liste des fondateurs signée
                                            <span class="badge bg-danger ms-1">*</span>
                                        </h6>
                                        <small class="text-muted">Liste complète avec signatures légalisées</small>
                                    </div>
                                    <div class="document-status" id="status-liste_fondateurs">
                                        <i class="fas fa-clock text-muted"></i>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control document-input" 
                                           id="doc-liste_fondateurs" 
                                           name="documents[liste_fondateurs]"
                                           accept=".pdf,.jpg,.png"
                                           data-doc-type="liste_fondateurs"
                                           data-required="true"
                                           onchange="handleDocumentUpload(this)">
                                    <button class="btn btn-outline-secondary btn-preview d-none" 
                                            type="button"
                                            onclick="previewDocument('liste_fondateurs')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Document généré automatiquement à partir de l'étape 6</div>
                            </div>
                        </div>

                        <!-- Justificatif siège social -->
                        <div class="col-md-6 mb-4">
                            <div class="document-upload-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-home text-primary me-2"></i>
                                            Justificatif du siège social
                                            <span class="badge bg-danger ms-1">*</span>
                                        </h6>
                                        <small class="text-muted">Bail, titre de propriété ou attestation d'hébergement</small>
                                    </div>
                                    <div class="document-status" id="status-justif_siege">
                                        <i class="fas fa-clock text-muted"></i>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control document-input" 
                                           id="doc-justif_siege" 
                                           name="documents[justif_siege]"
                                           accept=".pdf,.jpg,.png"
                                           data-doc-type="justif_siege"
                                           data-required="true"
                                           onchange="handleDocumentUpload(this)">
                                    <button class="btn btn-outline-secondary btn-preview d-none" 
                                            type="button"
                                            onclick="previewDocument('justif_siege')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Facture récente d'eau ou électricité acceptée</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CNI des fondateurs -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-id-card me-2"></i>
                        Pièces d'identité des fondateurs
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Vous devez fournir les CNI de tous les fondateurs déclarés à l'étape 6
                    </div>
                    <div class="row" id="cni-fondateurs-container">
                        <!-- Les champs CNI seront générés dynamiquement selon le nombre de fondateurs -->
                    </div>
                </div>
            </div>

            <!-- Documents spécifiques selon le type -->
            <div class="card border-0 shadow-sm mb-4" id="documents-specifiques">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-folder-plus me-2"></i>
                        Documents spécifiques à votre type d'organisation
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Documents pour Association -->
                    <div id="docs-association" class="documents-type d-none">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-balance-scale text-success me-2"></i>
                                                Règlement intérieur
                                                <span class="badge bg-warning ms-1">Recommandé</span>
                                            </h6>
                                            <small class="text-muted">Complète et précise les statuts</small>
                                        </div>
                                        <div class="document-status" id="status-reglement">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-reglement" 
                                               name="documents[reglement]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="reglement"
                                               data-required="false"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('reglement')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents pour ONG -->
                    <div id="docs-ong" class="documents-type d-none">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-project-diagram text-success me-2"></i>
                                                Projet social détaillé
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Objectifs, méthodologie, impacts attendus</small>
                                        </div>
                                        <div class="document-status" id="status-projet_social">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-projet_social" 
                                               name="documents[projet_social]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="projet_social"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('projet_social')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-chart-line text-success me-2"></i>
                                                Budget prévisionnel 3 ans
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Plan financier détaillé sur 3 années</small>
                                        </div>
                                        <div class="document-status" id="status-budget_previsionnel">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-budget_previsionnel" 
                                               name="documents[budget_previsionnel]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="budget_previsionnel"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('budget_previsionnel')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-graduation-cap text-success me-2"></i>
                                                CV des fondateurs
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Expérience dans le domaine social requise</small>
                                        </div>
                                        <div class="document-status" id="status-cv_fondateurs">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-cv_fondateurs" 
                                               name="documents[cv_fondateurs]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="cv_fondateurs"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('cv_fondateurs')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-handshake text-success me-2"></i>
                                                Lettres de soutien
                                                <span class="badge bg-warning ms-1">Recommandé</span>
                                            </h6>
                                            <small class="text-muted">Partenaires, autorités locales</small>
                                        </div>
                                        <div class="document-status" id="status-lettres_soutien">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-lettres_soutien" 
                                               name="documents[lettres_soutien]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="lettres_soutien"
                                               data-required="false"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('lettres_soutien')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents pour Parti Politique -->
                    <div id="docs-parti_politique" class="documents-type d-none">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-scroll text-success me-2"></i>
                                                Programme politique
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Vision, objectifs, propositions concrètes</small>
                                        </div>
                                        <div class="document-status" id="status-programme_politique">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-programme_politique" 
                                               name="documents[programme_politique]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="programme_politique"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('programme_politique')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-users text-success me-2"></i>
                                                Liste complète des 50 adhérents
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">NIP et signatures légalisées</small>
                                        </div>
                                        <div class="document-status" id="status-liste_50_adherents">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-liste_50_adherents" 
                                               name="documents[liste_50_adherents]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="liste_50_adherents"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('liste_50_adherents')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Document généré de l'étape 7 avec signatures</div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-map-marked text-success me-2"></i>
                                                Répartition géographique
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Preuve d'implantation dans 3 provinces</small>
                                        </div>
                                        <div class="document-status" id="status-repartition_geo">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-repartition_geo" 
                                               name="documents[repartition_geo]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="repartition_geo"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('repartition_geo')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-euro-sign text-success me-2"></i>
                                                Sources de financement
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Déclaration transparente des financements</small>
                                        </div>
                                        <div class="document-status" id="status-sources_financement">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-sources_financement" 
                                               name="documents[sources_financement]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="sources_financement"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('sources_financement')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents pour Confession Religieuse -->
                    <div id="docs-confession_religieuse" class="documents-type d-none">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-book text-success me-2"></i>
                                                Exposé de la doctrine
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Croyances, pratiques, traditions</small>
                                        </div>
                                        <div class="document-status" id="status-expose_doctrine">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-expose_doctrine" 
                                               name="documents[expose_doctrine]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="expose_doctrine"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('expose_doctrine')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-church text-success me-2"></i>
                                                Justificatif du lieu de culte
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Bail, titre de propriété, autorisation</small>
                                        </div>
                                        <div class="document-status" id="status-justif_lieu_culte">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-justif_lieu_culte" 
                                               name="documents[justif_lieu_culte]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="justif_lieu_culte"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('justif_lieu_culte')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-certificate text-success me-2"></i>
                                                Attestation du responsable religieux
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">Formation religieuse, expérience</small>
                                        </div>
                                        <div class="document-status" id="status-attestation_responsable">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-attestation_responsable" 
                                               name="documents[attestation_responsable]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="attestation_responsable"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('attestation_responsable')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="document-upload-card p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-users text-success me-2"></i>
                                                Liste des 10 fidèles minimum
                                                <span class="badge bg-danger ms-1">*</span>
                                            </h6>
                                            <small class="text-muted">NIP + déclaration d'adhésion</small>
                                        </div>
                                        <div class="document-status" id="status-liste_fideles">
                                            <i class="fas fa-clock text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" 
                                               class="form-control document-input" 
                                               id="doc-liste_fideles" 
                                               name="documents[liste_fideles]"
                                               accept=".pdf,.jpg,.png"
                                               data-doc-type="liste_fideles"
                                               data-required="true"
                                               onchange="handleDocumentUpload(this)">
                                        <button class="btn btn-outline-secondary btn-preview d-none" 
                                                type="button"
                                                onclick="previewDocument('liste_fideles')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Document généré de l'étape 7</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents optionnels -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>
                        Documents optionnels (tous types d'organisation)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="document-upload-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-image text-secondary me-2"></i>
                                            Logo de l'organisation
                                            <span class="badge bg-secondary ms-1">Optionnel</span>
                                        </h6>
                                        <small class="text-muted">Format image (PNG ou JPG) recommandé</small>
                                    </div>
                                    <div class="document-status" id="status-logo">
                                        <i class="fas fa-clock text-muted"></i>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control document-input" 
                                           id="doc-logo" 
                                           name="documents[logo]"
                                           accept=".png,.jpg,.jpeg"
                                           data-doc-type="logo"
                                           data-required="false"
                                           onchange="handleDocumentUpload(this)">
                                    <button class="btn btn-outline-secondary btn-preview d-none" 
                                            type="button"
                                            onclick="previewDocument('logo')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="document-upload-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-calendar-alt text-secondary me-2"></i>
                                            Plan d'activités
                                            <span class="badge bg-secondary ms-1">Optionnel</span>
                                        </h6>
                                        <small class="text-muted">Programme d'activités prévisionnelles</small>
                                    </div>
                                    <div class="document-status" id="status-plan_activites">
                                        <i class="fas fa-clock text-muted"></i>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control document-input" 
                                           id="doc-plan_activites" 
                                           name="documents[plan_activites]"
                                           accept=".pdf,.jpg,.png"
                                           data-doc-type="plan_activites"
                                           data-required="false"
                                           onchange="handleDocumentUpload(this)">
                                    <button class="btn btn-outline-secondary btn-preview d-none" 
                                            type="button"
                                            onclick="previewDocument('plan_activites')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="document-upload-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-certificate text-secondary me-2"></i>
                                            Demande d'agrément
                                            <span class="badge bg-secondary ms-1">Optionnel</span>
                                        </h6>
                                        <small class="text-muted">Si votre secteur d'activité l'exige</small>
                                    </div>
                                    <div class="document-status" id="status-demande_agrement">
                                        <i class="fas fa-clock text-muted"></i>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control document-input" 
                                           id="doc-demande_agrement" 
                                           name="documents[demande_agrement]"
                                           accept=".pdf,.jpg,.png"
                                           data-doc-type="demande_agrement"
                                           data-required="false"
                                           onchange="handleDocumentUpload(this)">
                                    <button class="btn btn-outline-secondary btn-preview d-none" 
                                            type="button"
                                            onclick="previewDocument('demande_agrement')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="document-upload-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-file-alt text-secondary me-2"></i>
                                            Autres documents
                                            <span class="badge bg-secondary ms-1">Optionnel</span>
                                        </h6>
                                        <small class="text-muted">Documents complémentaires si nécessaire</small>
                                    </div>
                                    <div class="document-status" id="status-autres_docs">
                                        <i class="fas fa-clock text-muted"></i>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control document-input" 
                                           id="doc-autres_docs" 
                                           name="documents[autres_docs]"
                                           accept=".pdf,.jpg,.png"
                                           data-doc-type="autres_docs"
                                           data-required="false"
                                           onchange="handleDocumentUpload(this)"
                                           multiple>
                                    <button class="btn btn-outline-secondary btn-preview d-none" 
                                            type="button"
                                            onclick="previewDocument('autres_docs')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Vous pouvez sélectionner plusieurs fichiers</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Récapitulatif et validation -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Validation des documents
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading">Récapitulatif des documents</h6>
                                <ul class="mb-0">
                                    <li>Documents uploadés : <strong id="recap-docs-uploaded" class="text-success">0</strong></li>
                                    <li>Documents obligatoires : <strong id="recap-docs-required">0</strong></li>
                                    <li>Documents manquants : <strong id="recap-docs-missing" class="text-danger">0</strong></li>
                                    <li>Taille totale : <strong id="recap-total-size">0 MB</strong></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning mb-0" id="missing-docs-alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Documents manquants
                                </h6>
                                <div id="missing-docs-list">
                                    <p class="mb-0">Tous les documents obligatoires doivent être fournis avant de continuer.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="documents_authenticite" name="documents_authenticite" required>
                        <label class="form-check-label fw-bold" for="documents_authenticite">
                            <i class="fas fa-certificate me-2 text-primary"></i>
                            Je certifie que tous les documents fournis sont authentiques et non falsifiés
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="documents_lisibilite" name="documents_lisibilite" required>
                        <label class="form-check-label fw-bold" for="documents_lisibilite">
                            <i class="fas fa-eye me-2 text-success"></i>
                            Je confirme que tous les documents sont lisibles et de bonne qualité
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="documents_complets" name="documents_complets" required>
                        <label class="form-check-label fw-bold" for="documents_complets">
                            <i class="fas fa-check-double me-2 text-warning"></i>
                            Je confirme avoir fourni tous les documents obligatoires requis pour mon type d'organisation
                        </label>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1 text-info"></i>
                            <strong>Important :</strong> Les documents soumis seront vérifiés par les services compétents. 
                            Toute falsification ou document non conforme entraînera le rejet du dossier et pourrait 
                            faire l'objet de poursuites judiciaires.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de prévisualisation -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    Aperçu du document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="preview-container">
                <!-- Le contenu de prévisualisation sera inséré ici -->
            </div>
        </div>
    </div>
</div>
<!-- ========== FIN SECTION I - ÉTAPE 8 : DOCUMENTS ========== -->

<!-- ========== DÉBUT SECTION J - ÉTAPE 9 : SOUMISSION FINALE ========== -->
<!-- ÉTAPE 9: Soumission finale -->
<div class="step-content" id="step9" style="display: none;">
    <div class="text-center mb-4">
        <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
            <i class="fas fa-check-circle fa-3x text-white"></i>
        </div>
        <h3 class="text-success">Soumission du dossier</h3>
        <p class="text-muted">Vérifiez une dernière fois votre dossier avant la soumission finale</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-11">
            
            <!-- État du dossier -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        État de complétude du dossier
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-info-circle fa-2x mb-2" id="icon-infos" style="color: #6c757d;"></i>
                                <h6>Informations générales</h6>
                                <div class="badge" id="badge-infos">
                                    <i class="fas fa-spinner fa-spin"></i> Vérification...
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-users fa-2x mb-2" id="icon-fondateurs" style="color: #6c757d;"></i>
                                <h6>Fondateurs</h6>
                                <div class="badge" id="badge-fondateurs">
                                    <i class="fas fa-spinner fa-spin"></i> Vérification...
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-user-plus fa-2x mb-2" id="icon-adherents" style="color: #6c757d;"></i>
                                <h6>Adhérents</h6>
                                <div class="badge" id="badge-adherents">
                                    <i class="fas fa-spinner fa-spin"></i> Vérification...
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded">
                                <i class="fas fa-file-alt fa-2x mb-2" id="icon-documents" style="color: #6c757d;"></i>
                                <h6>Documents</h6>
                                <div class="badge" id="badge-documents">
                                    <i class="fas fa-spinner fa-spin"></i> Vérification...
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="progress mt-3" style="height: 25px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 0%;" 
                             id="completion-progress">
                            <span class="fw-bold">0% Complété</span>
                        </div>
                    </div>

                    <div class="alert mt-3 d-none" id="completion-alert">
                        <!-- Message dynamique selon l'état de complétude -->
                    </div>
                </div>
            </div>

            <!-- Récapitulatif complet -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list-alt me-2"></i>
                            Récapitulatif complet du dossier
                        </h6>
                        <button type="button" class="btn btn-sm btn-light" onclick="printSummary()">
                            <i class="fas fa-print me-1"></i>
                            Imprimer
                        </button>
                    </div>
                </div>
                <div class="card-body" id="summary-content">
                    <!-- Type d'organisation -->
                    <div class="summary-section mb-4">
                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-building me-2"></i>
                            Type d'organisation
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="30%">Type :</td>
                                        <td id="summary-type">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Informations du demandeur -->
                    <div class="summary-section mb-4">
                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-user me-2"></i>
                            Informations du demandeur
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="40%">NIP :</td>
                                        <td id="summary-demandeur-nip">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Nom complet :</td>
                                        <td id="summary-demandeur-nom">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email :</td>
                                        <td id="summary-demandeur-email">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="40%">Téléphone :</td>
                                        <td id="summary-demandeur-tel">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">CNI :</td>
                                        <td id="summary-demandeur-cni">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Adresse :</td>
                                        <td id="summary-demandeur-adresse">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de l'organisation -->
                    <div class="summary-section mb-4">
                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-building me-2"></i>
                            Informations de l'organisation
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="40%">Dénomination :</td>
                                        <td id="summary-org-nom">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Sigle :</td>
                                        <td id="summary-org-sigle">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Secteur :</td>
                                        <td id="summary-org-secteur">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="40%">Zone d'intervention :</td>
                                        <td id="summary-org-zone">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Public cible :</td>
                                        <td id="summary-org-public">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Activités :</td>
                                        <td id="summary-org-activites">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="20%">Objet social :</td>
                                        <td id="summary-org-objet" class="fst-italic">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Siège social -->
                    <div class="summary-section mb-4">
                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Siège social
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="40%">Province :</td>
                                        <td id="summary-siege-province">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Préfecture :</td>
                                        <td id="summary-siege-prefecture">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Type de zone :</td>
                                        <td id="summary-siege-zone">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="40%">Téléphone :</td>
                                        <td id="summary-siege-tel">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email :</td>
                                        <td id="summary-siege-email">-</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Coordonnées GPS :</td>
                                        <td id="summary-siege-gps">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="fw-bold" width="20%">Adresse complète :</td>
                                        <td id="summary-siege-adresse">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Fondateurs et Adhérents -->
                    <div class="summary-section mb-4">
                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-users me-2"></i>
                            Fondateurs et Adhérents
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="text-success mb-0" id="summary-fondateurs-count">0</h3>
                                        <p class="mb-0">Fondateurs</p>
                                        <small class="text-muted" id="summary-fondateurs-min">Minimum requis : 3</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h3 class="text-info mb-0" id="summary-adherents-count">0</h3>
                                        <p class="mb-0">Adhérents</p>
                                        <small class="text-muted" id="summary-adherents-min">Minimum requis : 10</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="summary-section">
                        <h6 class="text-primary border-bottom pb-2">
                            <i class="fas fa-file-alt me-2"></i>
                            Documents joints
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Documents obligatoires :</strong></p>
                                <ul class="list-unstyled" id="summary-docs-required">
                                    <!-- Liste générée dynamiquement -->
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Documents optionnels :</strong></p>
                                <ul class="list-unstyled" id="summary-docs-optional">
                                    <!-- Liste générée dynamiquement -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Déclarations et engagements -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-file-signature me-2"></i>
                        Déclarations et engagements obligatoires
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important :</strong> Veuillez lire attentivement et cocher toutes les déclarations ci-dessous. 
                        Ces engagements ont une valeur légale et engagent votre responsabilité.
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="declaration_exactitude" name="declaration_exactitude" required>
                        <label class="form-check-label fw-bold" for="declaration_exactitude">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Je certifie sur l'honneur que toutes les informations fournies dans ce dossier sont exactes, 
                            complètes et conformes à la réalité
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="declaration_legalite" name="declaration_legalite" required>
                        <label class="form-check-label fw-bold" for="declaration_legalite">
                            <i class="fas fa-balance-scale me-2 text-primary"></i>
                            Je m'engage à ce que l'organisation respecte strictement la législation gabonaise en vigueur 
                            et les dispositions statutaires déclarées
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="declaration_documents" name="declaration_documents" required>
                        <label class="form-check-label fw-bold" for="declaration_documents">
                            <i class="fas fa-file-check me-2 text-info"></i>
                            J'atteste que tous les documents fournis sont authentiques et n'ont fait l'objet 
                            d'aucune falsification ou altération
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="declaration_consentement" name="declaration_consentement" required>
                        <label class="form-check-label fw-bold" for="declaration_consentement">
                            <i class="fas fa-users me-2 text-warning"></i>
                            Je confirme avoir obtenu le consentement écrit de toutes les personnes mentionnées 
                            dans ce dossier (fondateurs, adhérents, responsables)
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="declaration_responsabilite" name="declaration_responsabilite" required>
                        <label class="form-check-label fw-bold" for="declaration_responsabilite">
                            <i class="fas fa-user-shield me-2 text-danger"></i>
                            J'accepte d'assumer l'entière responsabilité de cette demande et comprends que toute 
                            fausse déclaration peut entraîner des poursuites judiciaires
                        </label>
                    </div>

                    <div class="form-check mb-3" id="declaration-parti" style="display: none;">
                        <input class="form-check-input" type="checkbox" id="declaration_exclusivite_parti" name="declaration_exclusivite_parti">
                        <label class="form-check-label fw-bold" for="declaration_exclusivite_parti">
                            <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                            <strong>Parti Politique :</strong> Je certifie qu'aucun fondateur ou adhérent n'est membre 
                            actif d'un autre parti politique au Gabon
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="declaration_donnees" name="declaration_donnees" required>
                        <label class="form-check-label fw-bold" for="declaration_donnees">
                            <i class="fas fa-shield-alt me-2 text-secondary"></i>
                            J'accepte que les données fournies soient traitées conformément à la législation 
                            gabonaise sur la protection des données personnelles
                        </label>
                    </div>
                </div>
            </div>

            <!-- Options de soumission -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i>
                        Options de soumission
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Soumettre définitivement</h5>
                                    <p class="card-text">
                                        Votre dossier sera transmis aux services compétents pour examen. 
                                        Vous recevrez un accusé de réception par email avec votre numéro de dossier.
                                    </p>
                                    <button type="button" 
                                            class="btn btn-success btn-lg" 
                                            id="submitFinalBtn"
                                            onclick="submitFinal()"
                                            disabled>
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Soumettre le dossier
                                    </button>
                                    <div class="form-text mt-2">
                                        <small>Cette action est irréversible</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-save fa-3x text-warning mb-3"></i>
                                    <h5 class="card-title">Enregistrer en brouillon</h5>
                                    <p class="card-text">
                                        Sauvegardez votre progression pour continuer plus tard. 
                                        Vous pourrez reprendre et compléter votre dossier à tout moment.
                                    </p>
                                    <button type="button" 
                                            class="btn btn-warning btn-lg"
                                            onclick="saveDraft()">
                                        <i class="fas fa-save me-2"></i>
                                        Sauvegarder le brouillon
                                    </button>
                                    <div class="form-text mt-2">
                                        <small>Modifiable jusqu'à la soumission</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prochaines étapes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-route me-2"></i>
                        Prochaines étapes après la soumission
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline-vertical">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success text-white">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">1. Accusé de réception automatique</h6>
                                <p class="text-muted mb-0">
                                    Vous recevrez immédiatement un email avec votre numéro de dossier unique
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary text-white">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">2. Vérification de complétude (1-3 jours)</h6>
                                <p class="text-muted mb-0">
                                    Les services vérifient que tous les documents requis sont présents et conformes
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning text-dark">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">3. Examen approfondi (5-10 jours)</h6>
                                <p class="text-muted mb-0">
                                    Analyse détaillée du dossier par les services compétents selon le workflow établi
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info text-white">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">4. Demande de compléments (si nécessaire)</h6>
                                <p class="text-muted mb-0">
                                    Vous serez notifié par email si des documents ou informations supplémentaires sont requis
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success text-white">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">5. Délivrance du récépissé (sous 30 jours)</h6>
                                <p class="text-muted mb-0">
                                    Récépissé définitif avec QR code sécurisé permettant de vérifier l'authenticité
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-dark text-white">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">6. Publication au Journal Officiel</h6>
                                <p class="text-muted mb-0">
                                    Publication officielle donnant existence légale à votre organisation
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Suivi en temps réel
                        </h6>
                        <p class="mb-0">
                            Vous pourrez suivre l'avancement de votre dossier à tout moment via votre espace personnel 
                            en utilisant votre numéro de dossier. Des notifications email vous tiendront informé 
                            à chaque étape importante.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de soumission -->
<div class="modal fade" id="confirmSubmitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Confirmation de soumission
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Action irréversible
                    </h6>
                    <p>Une fois soumis, votre dossier ne pourra plus être modifié. Assurez-vous que toutes les informations sont correctes.</p>
                </div>

                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Résumé de votre dossier :</h6>
                        <ul class="mb-0">
                            <li>Organisation : <strong id="confirm-org-name">-</strong></li>
                            <li>Type : <strong id="confirm-org-type">-</strong></li>
                            <li>Demandeur : <strong id="confirm-demandeur">-</strong></li>
                            <li>Email de contact : <strong id="confirm-email">-</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="final-confirm" required>
                    <label class="form-check-label fw-bold" for="final-confirm">
                        Je confirme vouloir soumettre définitivement ce dossier
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="confirmSubmission()" disabled id="final-submit-btn">
                    <i class="fas fa-paper-plane me-2"></i>Confirmer la soumission
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de succès -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                <h3 class="mb-3">Dossier soumis avec succès !</h3>
                <div class="alert alert-success">
                    <h5 class="alert-heading">Votre numéro de dossier :</h5>
                    <h2 class="mb-0" id="dossier-number">ORG-2025-XXXXX</h2>
                </div>
                <p class="mb-4">
                    Un accusé de réception détaillé a été envoyé à votre adresse email.<br>
                    Conservez précieusement votre numéro de dossier pour le suivi.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="{{ route('operator.dossiers.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-list me-2"></i>Voir mes dossiers
                    </a>
                    <button type="button" class="btn btn-success btn-lg" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimer le récapitulatif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline vertical */
.timeline-vertical {
    position: relative;
    padding-left: 40px;
}

.timeline-vertical::before {
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
    padding-bottom: 30px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

/* Summary sections */
.summary-section {
    page-break-inside: avoid;
}

@media print {
    .btn, .modal-footer, .card-header button {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .summary-section {
        page-break-inside: avoid;
    }
}
</style>
<!-- ========== FIN SECTION J - ÉTAPE 9 : SOUMISSION FINALE ========== -->

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

<!-- Modal d'aide -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>
                    Aide - Création d'Organisation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="accordion" id="helpAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="help1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                Types d'organisations au Gabon
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Association :</strong> Groupement de personnes autour d'un projet commun à but non lucratif.</p>
                                        <p><strong>ONG :</strong> Organisation non gouvernementale d'action humanitaire ou sociale.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Parti Politique :</strong> Organisation politique pour participer à la vie démocratique.</p>
                                        <p><strong>Confession Religieuse :</strong> Organisation religieuse pour l'exercice du culte.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="help2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                Documents requis selon le type
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Documents communs :</h6>
                                        <ul>
                                            <li>Statuts de l'organisation</li>
                                            <li>Procès-verbal de l'AG constitutive</li>
                                            <li>Liste des fondateurs avec signatures</li>
                                            <li>CNI des fondateurs</li>
                                            <li>Justificatif du siège social</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Documents spécifiques :</h6>
                                        <ul>
                                            <li><strong>ONG :</strong> Projet social détaillé + Budget</li>
                                            <li><strong>Parti :</strong> Programme politique + Liste 50 adhérents</li>
                                            <li><strong>Religion :</strong> Doctrine + Lieu de culte</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="help3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                Processus de validation
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <p>Le traitement du dossier suit un workflow de validation paramétrable avec plusieurs étapes.</p>
                                <h6>Étapes après soumission :</h6>
                                <ol>
                                    <li>Accusé de réception automatique</li>
                                    <li>Vérification de la complétude du dossier</li>
                                    <li>Examen par les services compétents</li>
                                    <li>Validation ou demande de pièces complémentaires</li>
                                    <li>Délivrance du récépissé définitif</li>
                                    <li>Publication au Journal Officiel</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== DÉBUT SECTION K - JAVASCRIPT PRINCIPAL ========== -->
<script>
// Variables globales
let currentStep = 1;
const totalSteps = 9;
let selectedOrgType = '';
let fondateursCount = 0;
let adherentsData = [];
let documentsUploaded = {};
let formData = new FormData();

// Configuration des types d'organisation
const orgTypeConfig = {
    association: {
        minFondateurs: 3,
        minAdherents: 10,
        label: 'Association',
        color: 'success'
    },
    ong: {
        minFondateurs: 5,
        minAdherents: 15,
        label: 'ONG',
        color: 'info'
    },
    parti_politique: {
        minFondateurs: 3,
        minAdherents: 50,
        label: 'Parti Politique',
        color: 'warning'
    },
    confession_religieuse: {
        minFondateurs: 3,
        minAdherents: 10,
        label: 'Confession Religieuse',
        color: 'purple'
    }
};

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    updateStepDisplay();
    initializeEventListeners();
    loadSavedData();
});

// Fonction de navigation entre étapes
function changeStep(direction) {
    // Sauvegarder les données actuelles
    saveCurrentStepData();
    
    if (direction === 1) {
        // Validation avant de passer à l'étape suivante
        if (!validateCurrentStep()) {
            return false;
        }
        
        if (currentStep < totalSteps) {
            currentStep++;
            
            // Actions spéciales selon l'étape
            if (currentStep === 2) {
                loadGuideContent();
            } else if (currentStep === 3) {
                // Pré-remplir avec les données du compte utilisateur si disponibles
                prefillDemandeurInfo();
            } else if (currentStep === 4) {
                // Afficher les sections selon le type d'organisation
                showOrgTypeSpecificFields();
            } else if (currentStep === 5) {
                // Initialiser la géolocalisation
                initializeGeolocation();
            } else if (currentStep === 6) {
                // Ajouter le premier fondateur
                if (fondateursCount === 0) {
                    addFondateur();
                }
            } else if (currentStep === 8) {
                // Générer les champs CNI pour les fondateurs
                generateCNIFields();
                // Afficher les documents spécifiques
                showSpecificDocuments();
            } else if (currentStep === 9) {
                // Vérifier la complétude et générer le récapitulatif
                checkCompleteness();
                generateFullSummary();
            }
        }
    } else {
        if (currentStep > 1) {
            currentStep--;
        }
    }
    
    updateStepDisplay();
    scrollToTop();
}

// Mise à jour de l'affichage
function updateStepDisplay() {
    // Masquer toutes les étapes
    document.querySelectorAll('.step-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Afficher l'étape actuelle
    const currentStepElement = document.getElementById('step' + currentStep);
    if (currentStepElement) {
        currentStepElement.style.display = 'block';
    }
    
    // Mettre à jour la barre de progression
    const progress = (currentStep / totalSteps) * 100;
    const progressBar = document.getElementById('globalProgress');
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
    }
    
    // Mettre à jour le numéro d'étape
    document.getElementById('currentStepNumber').textContent = currentStep;
    
    // Mettre à jour les indicateurs d'étapes
    updateStepIndicators();
    
    // Gérer les boutons de navigation
    updateNavigationButtons();
}

// Mise à jour des indicateurs d'étapes
function updateStepIndicators() {
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const stepNumber = index + 1;
        indicator.classList.remove('active', 'completed');
        
        if (stepNumber === currentStep) {
            indicator.classList.add('active');
        } else if (stepNumber < currentStep) {
            indicator.classList.add('completed');
            // Changer l'icône en check pour les étapes complétées
            const icon = indicator.querySelector('.step-icon');
            if (icon && !icon.classList.contains('fa-check')) {
                icon.className = 'fas fa-check step-icon text-success';
            }
        }
    });
}

// Mise à jour des boutons de navigation
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Bouton précédent
    if (prevBtn) {
        prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
    }
    
    // Boutons suivant et soumettre
    if (currentStep === totalSteps) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.classList.remove('d-none');
    } else {
        if (nextBtn) nextBtn.style.display = 'inline-block';
        if (submitBtn) submitBtn.classList.add('d-none');
    }
}

// Initialisation des écouteurs d'événements
function initializeEventListeners() {
    // Type d'organisation (Étape 1)
    document.querySelectorAll('.organization-type-card').forEach(card => {
        card.addEventListener('click', function() {
            selectOrganizationType(this);
        });
    });
    
    // Géolocalisation (Étape 5)
    const geoBtn = document.getElementById('getCurrentLocation');
    if (geoBtn) {
        geoBtn.addEventListener('click', getCurrentLocation);
    }
    
    // Zone type change
    const zoneTypeSelect = document.getElementById('org_zone_type');
    if (zoneTypeSelect) {
        zoneTypeSelect.addEventListener('change', toggleZoneFields);
    }
    
    // Province change pour charger les départements
    const provinceSelect = document.getElementById('org_province');
    if (provinceSelect) {
        provinceSelect.addEventListener('change', loadDepartements);
    }
    
    // Ajout de fondateur
    const addFondateurBtn = document.getElementById('ajouterFondateur');
    if (addFondateurBtn) {
        addFondateurBtn.addEventListener('click', addFondateur);
    }
    
    // Compteurs de caractères
    initializeCharCounters();
    
    // Validation en temps réel
    initializeRealTimeValidation();
    
    // Auto-sauvegarde
    setInterval(autoSaveProgress, 30000); // Toutes les 30 secondes
}

// Sélection du type d'organisation
function selectOrganizationType(card) {
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
        selectedOrgType = radio.value;
        document.getElementById('organizationType').value = selectedOrgType;
    }
    
    // Afficher les infos de sélection
    const selectedInfo = document.getElementById('selectedTypeInfo');
    const selectedTypeName = document.getElementById('selectedTypeName');
    if (selectedInfo && selectedTypeName) {
        selectedInfo.classList.remove('d-none');
        selectedTypeName.textContent = orgTypeConfig[selectedOrgType]?.label || selectedOrgType;
    }
    
    // Afficher les contraintes
    showTypeConstraints();
}

// Afficher les contraintes du type sélectionné
function showTypeConstraints() {
    const constraintsDiv = document.getElementById('typeConstraints');
    const constraintContent = document.getElementById('constraintContent');
    
    if (!constraintsDiv || !constraintContent || !selectedOrgType) return;
    
    const config = orgTypeConfig[selectedOrgType];
    let content = `
        <ul class="mb-0">
            <li>Nombre minimum de fondateurs : <strong>${config.minFondateurs}</strong></li>
            <li>Nombre minimum d'adhérents : <strong>${config.minAdherents}</strong></li>
    `;
    
    // Contraintes spécifiques
    if (selectedOrgType === 'parti_politique') {
        content += `<li class="text-danger"><strong>Un adhérent ne peut être membre que d'un seul parti politique</strong></li>`;
        content += `<li>Présence obligatoire dans au moins <strong>3 provinces</strong></li>`;
    } else if (selectedOrgType === 'confession_religieuse') {
        content += `<li>Lieu de culte obligatoire avec justificatif</li>`;
        content += `<li>Au moins un responsable religieux qualifié parmi les fondateurs</li>`;
    } else if (selectedOrgType === 'ong') {
        content += `<li>Projet social détaillé obligatoire</li>`;
        content += `<li>Budget prévisionnel sur 3 ans requis</li>`;
    }
    
    content += `</ul>`;
    
    constraintContent.innerHTML = content;
    constraintsDiv.style.display = 'block';
}

// Chargement du contenu du guide (Étape 2)
function loadGuideContent() {
    if (!selectedOrgType) return;
    
    // Afficher le bon guide
    document.querySelectorAll('.guide-content').forEach(guide => {
        guide.classList.add('d-none');
    });
    
    const selectedGuide = document.getElementById(`guide-${selectedOrgType}`);
    if (selectedGuide) {
        selectedGuide.classList.remove('d-none');
    }
    
    // Mettre à jour le titre
    const titleElement = document.getElementById('selectedTypeTitle');
    if (titleElement) {
        titleElement.textContent = orgTypeConfig[selectedOrgType]?.label || selectedOrgType;
    }
}

// Sauvegarde des données de l'étape actuelle
function saveCurrentStepData() {
    const stepData = {};
    const currentStepElement = document.getElementById('step' + currentStep);
    
    if (!currentStepElement) return;
    
    // Récupérer tous les champs de l'étape
    currentStepElement.querySelectorAll('input, select, textarea').forEach(field => {
        if (field.name && field.type !== 'file') {
            if (field.type === 'checkbox' || field.type === 'radio') {
                if (field.checked) {
                    stepData[field.name] = field.value;
                }
            } else {
                stepData[field.name] = field.value;
            }
        }
    });
    
    // Sauvegarder dans le localStorage
    const savedData = JSON.parse(localStorage.getItem('organizationFormData') || '{}');
    savedData['step' + currentStep] = stepData;
    savedData.currentStep = currentStep;
    savedData.selectedOrgType = selectedOrgType;
    localStorage.setItem('organizationFormData', JSON.stringify(savedData));
}

// Chargement des données sauvegardées
function loadSavedData() {
    const savedData = JSON.parse(localStorage.getItem('organizationFormData') || '{}');
    
    if (savedData.selectedOrgType) {
        selectedOrgType = savedData.selectedOrgType;
        // Sélectionner le type d'organisation
        const card = document.querySelector(`[data-type="${selectedOrgType}"]`);
        if (card) {
            selectOrganizationType(card);
        }
    }
    
    // Restaurer les données de chaque étape
    for (let i = 1; i <= totalSteps; i++) {
        const stepData = savedData['step' + i];
        if (stepData) {
            restoreStepData(i, stepData);
        }
    }
}

// Restauration des données d'une étape
function restoreStepData(stepNumber, data) {
    const stepElement = document.getElementById('step' + stepNumber);
    if (!stepElement) return;
    
    Object.keys(data).forEach(fieldName => {
        const field = stepElement.querySelector(`[name="${fieldName}"]`);
        if (field) {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = field.value === data[fieldName];
            } else {
                field.value = data[fieldName];
            }
        }
    });
}

// Scroll vers le haut de la page
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Auto-sauvegarde
function autoSaveProgress() {
    saveCurrentStepData();
    showNotification('Progression sauvegardée automatiquement', 'success', 2000);
}

// Afficher une notification
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, duration);
}
</script>
<!-- ========== FIN SECTION K - JAVASCRIPT PRINCIPAL ========== -->

<!-- ========== DÉBUT SECTION L - JAVASCRIPT DE VALIDATION ========== -->
<script>
// Validation de l'étape courante
function validateCurrentStep() {
    let isValid = true;
    const currentStepElement = document.getElementById('step' + currentStep);
    
    if (!currentStepElement) return false;
    
    // Réinitialiser les erreurs
    currentStepElement.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    switch(currentStep) {
        case 1:
            isValid = validateStep1();
            break;
        case 2:
            isValid = validateStep2();
            break;
        case 3:
            isValid = validateStep3();
            break;
        case 4:
            isValid = validateStep4();
            break;
        case 5:
            isValid = validateStep5();
            break;
        case 6:
            isValid = validateStep6();
            break;
        case 7:
            isValid = validateStep7();
            break;
        case 8:
            isValid = validateStep8();
            break;
        case 9:
            isValid = validateStep9();
            break;
    }
    
    if (!isValid) {
        showValidationErrors();
    }
    
    return isValid;
}

// Validation Étape 1 : Type d'organisation
function validateStep1() {
    const typeSelected = document.querySelector('input[name="type_organisation"]:checked');
    
    if (!typeSelected) {
        showNotification('Veuillez sélectionner un type d\'organisation', 'danger');
        return false;
    }
    
    selectedOrgType = typeSelected.value;
    document.getElementById('organizationType').value = selectedOrgType;
    
    return true;
}

// Validation Étape 2 : Guide
function validateStep2() {
    const guideConfirm = document.getElementById('guideReadConfirm');
    
    if (!guideConfirm || !guideConfirm.checked) {
        showNotification('Veuillez confirmer avoir lu le guide', 'danger');
        if (guideConfirm) {
            guideConfirm.classList.add('is-invalid');
        }
        return false;
    }
    
    return true;
}

// Validation Étape 3 : Informations demandeur
function validateStep3() {
    const requiredFields = [
        { id: 'demandeur_nip', message: 'Le NIP est obligatoire', validator: validateNIP },
        { id: 'demandeur_civilite', message: 'La civilité est obligatoire' },
        { id: 'demandeur_nom', message: 'Le nom est obligatoire' },
        { id: 'demandeur_prenoms', message: 'Les prénoms sont obligatoires' },
        { id: 'demandeur_date_naissance', message: 'La date de naissance est obligatoire', validator: validateAge },
        { id: 'demandeur_lieu_naissance', message: 'Le lieu de naissance est obligatoire' },
        { id: 'demandeur_sexe', message: 'Le sexe est obligatoire' },
        { id: 'demandeur_nationalite', message: 'La nationalité est obligatoire' },
        { id: 'demandeur_profession', message: 'La profession est obligatoire' },
        { id: 'demandeur_email', message: 'L\'email est obligatoire', validator: validateEmail },
        { id: 'demandeur_telephone', message: 'Le téléphone est obligatoire', validator: validatePhone },
        { id: 'demandeur_adresse', message: 'L\'adresse est obligatoire' },
        { id: 'demandeur_cni_numero', message: 'Le numéro de CNI est obligatoire' },
        { id: 'demandeur_cni_date_etablissement', message: 'La date d\'établissement de la CNI est obligatoire' },
        { id: 'demandeur_cni_date_expiration', message: 'La date d\'expiration de la CNI est obligatoire', validator: validateCNIExpiration },
        { id: 'demandeur_cni_lieu_etablissement', message: 'Le lieu d\'établissement de la CNI est obligatoire' }
    ];
    
    let isValid = true;
    const errors = [];
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (!element) return;
        
        const value = element.value.trim();
        
        if (!value) {
            element.classList.add('is-invalid');
            errors.push(field.message);
            isValid = false;
        } else if (field.validator && !field.validator(value, element)) {
            element.classList.add('is-invalid');
            isValid = false;
        } else {
            element.classList.remove('is-invalid');
            element.classList.add('is-valid');
        }
    });
    
    // Validation des checkboxes
    const checkboxes = ['demandeur_info_exactes', 'demandeur_contact_autorise', 'demandeur_responsabilite'];
    checkboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && !checkbox.checked) {
            checkbox.classList.add('is-invalid');
            errors.push('Toutes les déclarations doivent être cochées');
            isValid = false;
        }
    });
    
    if (!isValid && errors.length > 0) {
        showNotification(errors[0], 'danger');
    }
    
    return isValid;
}

// Validation Étape 4 : Informations organisation
function validateStep4() {
    const requiredFields = [
        { id: 'org_nom', message: 'Le nom de l\'organisation est obligatoire' },
        { id: 'org_objet', message: 'L\'objet social est obligatoire', minLength: 50 },
        { id: 'org_objectifs', message: 'Les objectifs sont obligatoires', minLength: 50 },
        { id: 'org_secteur', message: 'Le secteur d\'activité est obligatoire' },
        { id: 'org_zone_intervention', message: 'La zone d\'intervention est obligatoire' },
        { id: 'org_public_cible', message: 'Le public cible est obligatoire' }
    ];
    
    let isValid = true;
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (!element) return;
        
        const value = element.value.trim();
        
        if (!value) {
            element.classList.add('is-invalid');
            showNotification(field.message, 'danger');
            isValid = false;
        } else if (field.minLength && value.length < field.minLength) {
            element.classList.add('is-invalid');
            showNotification(`${field.message} (minimum ${field.minLength} caractères)`, 'danger');
            isValid = false;
        } else {
            element.classList.remove('is-invalid');
            element.classList.add('is-valid');
        }
    });
    
    // Validation des langues (au moins une)
    const langues = document.getElementById('org_langues');
    if (langues && langues.selectedOptions.length === 0) {
        langues.classList.add('is-invalid');
        showNotification('Sélectionnez au moins une langue de travail', 'danger');
        isValid = false;
    }
    
    // Validation des activités (maximum 5)
    const activites = document.querySelectorAll('.activite-checkbox:checked');
    if (activites.length === 0) {
        showNotification('Sélectionnez au moins une activité principale', 'danger');
        isValid = false;
    } else if (activites.length > 5) {
        showNotification('Maximum 5 activités principales autorisées', 'danger');
        isValid = false;
    }
    
    // Validation des checkboxes
    const checkboxes = ['org_info_valides', 'org_unicite_nom'];
    checkboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && !checkbox.checked) {
            checkbox.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Validation Étape 5 : Coordonnées
function validateStep5() {
    const requiredFields = [
        { id: 'org_province', message: 'La province est obligatoire' },
        { id: 'org_prefecture', message: 'La préfecture est obligatoire' },
        { id: 'org_zone_type', message: 'Le type de zone est obligatoire' },
        { id: 'org_adresse_complete', message: 'L\'adresse complète est obligatoire' },
        { id: 'org_telephone', message: 'Le téléphone est obligatoire', validator: validatePhone },
        { id: 'org_email', message: 'L\'email est obligatoire', validator: validateEmail }
    ];
    
    let isValid = true;
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (!element) return;
        
        const value = element.value.trim();
        
        if (!value) {
            element.classList.add('is-invalid');
            showNotification(field.message, 'danger');
            isValid = false;
        } else if (field.validator && !field.validator(value, element)) {
            element.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    // Validation des coordonnées GPS si fournies
    const latitude = document.getElementById('org_latitude');
    const longitude = document.getElementById('org_longitude');
    
    if (latitude && latitude.value && !validateLatitude(latitude.value)) {
        latitude.classList.add('is-invalid');
        showNotification('Latitude invalide pour le Gabon (entre -3 et 3)', 'danger');
        isValid = false;
    }
    
    if (longitude && longitude.value && !validateLongitude(longitude.value)) {
        longitude.classList.add('is-invalid');
        showNotification('Longitude invalide pour le Gabon (entre 8 et 15)', 'danger');
        isValid = false;
    }
    
    // Validation des checkboxes
    const checkboxes = ['coordonnees_exactes', 'justificatif_disponible', 'acces_autorise'];
    checkboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && !checkbox.checked) {
            checkbox.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Validation Étape 6 : Fondateurs
function validateStep6() {
    const config = orgTypeConfig[selectedOrgType];
    const minFondateurs = config ? config.minFondateurs : 3;
    
    const fondateurs = document.querySelectorAll('.fondateur-card');
    
    if (fondateurs.length < minFondateurs) {
        showNotification(`Minimum ${minFondateurs} fondateurs requis pour ${config.label}`, 'danger');
        return false;
    }
    
    let isValid = true;
    const nips = new Set();
    
    fondateurs.forEach((card, index) => {
        const requiredFields = [
            { selector: '.fondateur-nip', message: 'NIP' },
            { selector: '.fondateur-nom', message: 'Nom' },
            { selector: '.fondateur-prenoms', message: 'Prénoms' },
            { selector: '.fondateur-fonction', message: 'Fonction' },
            { selector: '.fondateur-date-naissance', message: 'Date de naissance' },
            { selector: '.fondateur-lieu-naissance', message: 'Lieu de naissance' },
            { selector: '.fondateur-sexe', message: 'Sexe' },
            { selector: '.fondateur-nationalite', message: 'Nationalité' },
            { selector: '.fondateur-email', message: 'Email' },
            { selector: '.fondateur-telephone', message: 'Téléphone' },
            { selector: '.fondateur-adresse', message: 'Adresse' },
            { selector: '.fondateur-cni-numero', message: 'Numéro CNI' },
            { selector: '.fondateur-type-piece', message: 'Type de pièce' }
        ];
        
        requiredFields.forEach(field => {
            const element = card.querySelector(field.selector);
            if (!element) return;
            
            if (!element.value.trim()) {
                element.classList.add('is-invalid');
                if (isValid) {
                    showNotification(`${field.message} manquant pour le fondateur ${index + 1}`, 'danger');
                }
                isValid = false;
            }
        });
        
        // Vérification des doublons NIP
        const nipField = card.querySelector('.fondateur-nip');
        if (nipField && nipField.value) {
            if (nips.has(nipField.value)) {
                nipField.classList.add('is-invalid');
                showNotification(`NIP en doublon détecté : ${nipField.value}`, 'danger');
                isValid = false;
            } else {
                nips.add(nipField.value);
            }
        }
    });
    
    // Validation des checkboxes
    const checkboxes = ['fondateurs_consentement', 'fondateurs_documents', 'fondateurs_engagement'];
    if (selectedOrgType === 'parti_politique') {
        checkboxes.push('fondateurs_exclusivite');
    }
    
    checkboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && !checkbox.checked) {
            checkbox.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Validation Étape 7 : Adhérents
function validateStep7() {
    const config = orgTypeConfig[selectedOrgType];
    const minAdherents = config ? config.minAdherents : 10;
    
    const adherentsValides = adherentsData.filter(a => a.statut === 'valide').length;
    
    if (adherentsValides < minAdherents) {
        showNotification(`Minimum ${minAdherents} adhérents valides requis pour ${config.label}`, 'danger');
        return false;
    }
    
    // Vérifier s'il y a des doublons internes non résolus
    const doublonsInternes = adherentsData.filter(a => a.statut === 'doublon_interne').length;
    if (doublonsInternes > 0) {
        showNotification(`${doublonsInternes} doublon(s) interne(s) à corriger`, 'danger');
        return false;
    }
    
    // Validation des checkboxes
    const checkboxes = ['adherents_consent', 'adherents_documents'];
    if (selectedOrgType === 'parti_politique') {
        checkboxes.push('adherents_exclusivity');
    }
    
    let isValid = true;
    checkboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && !checkbox.checked) {
            checkbox.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Validation Étape 8 : Documents
function validateStep8() {
    let isValid = true;
    const missingDocs = [];
    
    // Vérifier tous les documents obligatoires
    document.querySelectorAll('.document-input[data-required="true"]').forEach(input => {
        const docType = input.getAttribute('data-doc-type');
        
        if (!input.files || input.files.length === 0) {
            input.classList.add('is-invalid');
            const label = input.closest('.document-upload-card')?.querySelector('h6')?.textContent || docType;
            missingDocs.push(label);
            isValid = false;
        } else {
            // Vérifier la taille du fichier (max 5MB)
            const file = input.files[0];
            if (file.size > 5 * 1024 * 1024) {
                input.classList.add('is-invalid');
                showNotification(`${file.name} dépasse la taille maximale de 5MB`, 'danger');
                isValid = false;
            }
        }
    });
    
    if (missingDocs.length > 0) {
        showNotification(`Documents manquants : ${missingDocs[0]}`, 'danger');
    }
    
    // Validation des checkboxes
    const checkboxes = ['documents_authenticite', 'documents_lisibilite', 'documents_complets'];
    checkboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && !checkbox.checked) {
            checkbox.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Validation Étape 9 : Soumission
function validateStep9() {
    // Vérifier la complétude globale
    const completion = checkGlobalCompletion();
    
    if (completion.percentage < 100) {
        showNotification('Veuillez compléter toutes les sections avant de soumettre', 'danger');
        return false;
    }
    
    // Validation des déclarations finales
    const declarations = [
        'declaration_exactitude',
        'declaration_legalite',
        'declaration_documents',
        'declaration_consentement',
        'declaration_responsabilite',
        'declaration_donnees'
    ];
    
    if (selectedOrgType === 'parti_politique') {
        declarations.push('declaration_exclusivite_parti');
    }
    
    let isValid = true;
    declarations.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox && !checkbox.checked) {
            checkbox.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    if (!isValid) {
        showNotification('Toutes les déclarations doivent être acceptées', 'danger');
    }
    
    return isValid;
}

// Validateurs spécifiques
function validateNIP(value) {
    const nipRegex = /^[0-9]{13}$/;
    if (!nipRegex.test(value)) {
        showNotification('Le NIP doit contenir exactement 13 chiffres', 'danger');
        return false;
    }
    return true;
}

function validateEmail(value) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
        showNotification('Format d\'email invalide', 'danger');
        return false;
    }
    return true;
}

function validatePhone(value) {
    // Nettoyer le numéro
    const cleaned = value.replace(/\s+/g, '').replace(/^\+241/, '');
    const phoneRegex = /^[0-9]{8,9}$/;
    
    if (!phoneRegex.test(cleaned)) {
        showNotification('Format de téléphone invalide', 'danger');
        return false;
    }
    return true;
}

function validateAge(dateString) {
    const birthDate = new Date(dateString);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    if (age < 18) {
        showNotification('Vous devez être majeur (18 ans minimum)', 'danger');
        return false;
    }
    return true;
}

function validateCNIExpiration(dateString) {
    const expirationDate = new Date(dateString);
    const today = new Date();
    
    if (expirationDate <= today) {
        showNotification('La CNI est expirée ou expire aujourd\'hui', 'danger');
        return false;
    }
    return true;
}

function validateLatitude(value) {
    const lat = parseFloat(value);
    return !isNaN(lat) && lat >= -3 && lat <= 3;
}

function validateLongitude(value) {
    const lng = parseFloat(value);
    return !isNaN(lng) && lng >= 8 && lng <= 15;
}

// Validation en temps réel
function initializeRealTimeValidation() {
    // NIP
    document.querySelectorAll('input[name*="nip"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validateNIP(this.value)) {
                this.classList.add('is-invalid');
            } else if (this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    // Email
    document.querySelectorAll('input[type="email"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                this.classList.add('is-invalid');
            } else if (this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    // Téléphone
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('input', function() {
            formatPhoneNumber(this);
        });
        
        input.addEventListener('blur', function() {
            if (this.value && !validatePhone(this.value)) {
                this.classList.add('is-invalid');
            } else if (this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
}

// Formatage du numéro de téléphone
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    
    // Si commence par 241, c'est déjà avec l'indicatif
    if (value.startsWith('241')) {
        value = value.substring(3);
    }
    
    // Formater en XX XX XX XX
    if (value.length > 0) {
        const formatted = value.match(/.{1,2}/g)?.join(' ') || value;
        input.value = formatted.substring(0, 11); // Limite à 8 chiffres + 3 espaces
    }
}

// Afficher les erreurs de validation
function showValidationErrors() {
    const firstError = document.querySelector('.is-invalid');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
    }
}

// Vérification de la complétude globale
function checkGlobalCompletion() {
    const sections = {
        infos: checkInfosCompletion(),
        fondateurs: checkFondateursCompletion(),
        adherents: checkAdherentsCompletion(),
        documents: checkDocumentsCompletion()
    };
    
    const total = Object.values(sections).reduce((sum, val) => sum + val, 0);
    const percentage = Math.round(total / 4);
    
    return {
        percentage,
        sections,
        isComplete: percentage === 100
    };
}

function checkInfosCompletion() {
    // Vérifier étapes 1 à 5
    const savedData = JSON.parse(localStorage.getItem('organizationFormData') || '{}');
    let completed = 0;
    
    for (let i = 1; i <= 5; i++) {
        if (savedData['step' + i] && Object.keys(savedData['step' + i]).length > 0) {
            completed++;
        }
    }
    
    return (completed / 5) * 100;
}

function checkFondateursCompletion() {
    const config = orgTypeConfig[selectedOrgType];
    const minFondateurs = config ? config.minFondateurs : 3;
    const fondateurs = document.querySelectorAll('.fondateur-card');
    
    if (fondateurs.length >= minFondateurs) {
        return 100;
    }
    
    return (fondateurs.length / minFondateurs) * 100;
}

function checkAdherentsCompletion() {
    const config = orgTypeConfig[selectedOrgType];
    const minAdherents = config ? config.minAdherents : 10;
    const adherentsValides = adherentsData.filter(a => a.statut === 'valide').length;
    
    if (adherentsValides >= minAdherents) {
        return 100;
    }
    
    return (adherentsValides / minAdherents) * 100;
}

function checkDocumentsCompletion() {
    const requiredDocs = document.querySelectorAll('.document-input[data-required="true"]');
    let uploaded = 0;
    
    requiredDocs.forEach(input => {
        if (input.files && input.files.length > 0) {
            uploaded++;
        }
    });
    
    if (requiredDocs.length === 0) return 0;
    
    return (uploaded / requiredDocs.length) * 100;
}
</script>
<!-- ========== FIN SECTION L - JAVASCRIPT DE VALIDATION ========== -->

<!-- ========== DÉBUT SECTION M - JAVASCRIPT FONCTIONNALITÉS ========== -->
<script>
// ============================================
// GESTION DES UPLOADS DE DOCUMENTS
// ============================================

// Gestion de l'upload de documents
function handleDocumentUpload(input) {
    const file = input.files[0];
    const docType = input.getAttribute('data-doc-type');
    const statusIcon = document.getElementById(`status-${docType}`);
    const previewBtn = input.nextElementSibling;
    
    if (!file) {
        // Réinitialiser le statut si aucun fichier
        statusIcon.innerHTML = '<i class="fas fa-clock text-muted"></i>';
        if (previewBtn) previewBtn.classList.add('d-none');
        return;
    }
    
    // Validation du fichier
    const validTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!validTypes.includes(file.type)) {
        showNotification('Format de fichier non autorisé. Utilisez PDF, JPG ou PNG.', 'danger');
        input.value = '';
        statusIcon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        return;
    }
    
    if (file.size > maxSize) {
        showNotification('Le fichier dépasse la taille maximale de 5MB', 'danger');
        input.value = '';
        statusIcon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        return;
    }
    
    // Mise à jour du statut
    statusIcon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
    
    // Afficher le bouton de prévisualisation
    if (previewBtn) {
        previewBtn.classList.remove('d-none');
    }
    
    // Stocker le fichier pour l'upload
    documentsUploaded[docType] = file;
    
    // Mettre à jour les compteurs
    updateDocumentCounters();
    
    // Si c'est une image, créer une preview
    if (file.type.startsWith('image/')) {
        createImagePreview(file, docType);
    }
}

// Prévisualisation des documents
function previewDocument(docType) {
    const file = documentsUploaded[docType];
    if (!file) return;
    
    const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
    const container = document.getElementById('preview-container');
    
    container.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>';
    
    if (file.type === 'application/pdf') {
        // Pour les PDF, afficher un lien de téléchargement ou utiliser PDF.js
        const url = URL.createObjectURL(file);
        container.innerHTML = `
            <div class="text-center">
                <i class="fas fa-file-pdf fa-5x text-danger mb-3"></i>
                <h5>${file.name}</h5>
                <p class="text-muted">Taille : ${formatFileSize(file.size)}</p>
                <a href="${url}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-2"></i>
                    Ouvrir dans un nouvel onglet
                </a>
            </div>
        `;
    } else if (file.type.startsWith('image/')) {
        // Pour les images, afficher directement
        const reader = new FileReader();
        reader.onload = function(e) {
            container.innerHTML = `
                <img src="${e.target.result}" class="img-fluid" alt="${file.name}">
                <p class="text-center mt-3 text-muted">${file.name} - ${formatFileSize(file.size)}</p>
            `;
        };
        reader.readAsDataURL(file);
    }
    
    modal.show();
}

// Créer une miniature pour les images
function createImagePreview(file, docType) {
    const reader = new FileReader();
    reader.onload = function(e) {
        // Créer un élément de preview à côté du champ
        const uploadCard = document.querySelector(`[data-doc-type="${docType}"]`).closest('.document-upload-card');
        let previewDiv = uploadCard.querySelector('.image-preview');
        
        if (!previewDiv) {
            previewDiv = document.createElement('div');
            previewDiv.className = 'image-preview mt-2';
            uploadCard.appendChild(previewDiv);
        }
        
        previewDiv.innerHTML = `
            <img src="${e.target.result}" class="img-thumbnail" style="max-height: 100px;">
        `;
    };
    reader.readAsDataURL(file);
}

// Mise à jour des compteurs de documents
function updateDocumentCounters() {
    const requiredDocs = document.querySelectorAll('.document-input[data-required="true"]');
    const uploadedRequired = Array.from(requiredDocs).filter(input => input.files && input.files.length > 0);
    const totalUploaded = Object.keys(documentsUploaded).length;
    
    // Mettre à jour les compteurs dans l'interface
    const uploadedCountEl = document.getElementById('docs-uploaded-count');
    const requiredCountEl = document.getElementById('docs-required-count');
    
    if (uploadedCountEl) uploadedCountEl.textContent = totalUploaded;
    if (requiredCountEl) requiredCountEl.textContent = requiredDocs.length - uploadedRequired.length;
    
    // Mettre à jour le récapitulatif
    updateDocumentsSummary();
}

// ============================================
// GESTION DES ADHÉRENTS
// ============================================

// Afficher la section de saisie manuelle
function showManualEntry() {
    document.getElementById('manual-entry-section').style.display = 'block';
    document.getElementById('import-section').style.display = 'none';
    document.getElementById('adherent_nip').focus();
}

// Masquer la section de saisie manuelle
function hideManualEntry() {
    document.getElementById('manual-entry-section').style.display = 'none';
    // Réinitialiser le formulaire
    document.getElementById('adherent-manual-form').reset();
}

// Afficher la section d'import
function showImportSection() {
    document.getElementById('import-section').style.display = 'block';
    document.getElementById('manual-entry-section').style.display = 'none';
}

// Masquer la section d'import
function hideImportSection() {
    document.getElementById('import-section').style.display = 'none';
    document.getElementById('adherents_file').value = '';
}

// Ajouter un adhérent manuellement
function addAdherent() {
    const form = document.getElementById('adherent-manual-form');
    const formData = new FormData(form);
    
    // Validation des champs obligatoires
    const nip = formData.get('adherent_nip');
    const nom = formData.get('adherent_nom');
    const prenom = formData.get('adherent_prenom');
    
    if (!nip || !nom || !prenom) {
        showNotification('Veuillez remplir au minimum le NIP, nom et prénom', 'danger');
        return;
    }
    
    // Vérifier le format du NIP
    if (!validateNIP(nip)) {
        document.getElementById('adherent_nip').classList.add('is-invalid');
        return;
    }
    
    // Vérifier les doublons internes
    const existingAdherent = adherentsData.find(a => a.nip === nip);
    if (existingAdherent) {
        showNotification(`Un adhérent avec le NIP ${nip} existe déjà dans la liste`, 'danger');
        document.getElementById('adherent_nip').classList.add('is-invalid');
        return;
    }
    
    // Créer l'objet adhérent
    const adherent = {
        id: Date.now(),
        nip: nip,
        nom: nom.toUpperCase(),
        prenom: prenom,
        date_naissance: formData.get('adherent_date_naissance') || '',
        sexe: formData.get('adherent_sexe') || '',
        email: formData.get('adherent_email') || '',
        telephone: formData.get('adherent_telephone') || '',
        statut: 'valide',
        organisation_actuelle: null
    };
    
    // Ajouter à la liste
    adherentsData.push(adherent);
    
    // Mettre à jour l'affichage
    updateAdherentsDisplay();
    updateAdherentsCounters();
    
    // Réinitialiser le formulaire
    form.reset();
    hideManualEntry();
    
    showNotification('Adhérent ajouté avec succès', 'success');
    
    // Vérifier automatiquement les doublons si c'est un parti politique
    if (selectedOrgType === 'parti_politique') {
        checkAdherentInOtherOrgs(adherent);
    }
}

// Import des adhérents depuis Excel/CSV
function importAdherents() {
    const fileInput = document.getElementById('adherents_file');
    const file = fileInput.files[0];
    
    if (!file) {
        showNotification('Veuillez sélectionner un fichier', 'danger');
        return;
    }
    
    const validTypes = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv'
    ];
    
    if (!validTypes.includes(file.type)) {
        showNotification('Format de fichier non supporté. Utilisez Excel (.xlsx, .xls) ou CSV', 'danger');
        return;
    }
    
    showNotification('Import en cours...', 'info');
    
    const reader = new FileReader();
    
    if (file.type === 'text/csv') {
        reader.onload = function(e) {
            parseCSV(e.target.result);
        };
        reader.readAsText(file);
    } else {
        // Pour Excel, on devrait utiliser une bibliothèque comme SheetJS
        // Pour la démo, on simule
        showNotification('L\'import Excel nécessite une bibliothèque supplémentaire. Utilisez CSV pour le moment.', 'warning');
    }
}

// Parser le CSV
function parseCSV(csvContent) {
    const lines = csvContent.split('\n');
    const headers = lines[0].toLowerCase().split(',').map(h => h.trim());
    
    // Vérifier les colonnes requises
    const requiredColumns = ['nip', 'nom', 'prenom'];
    const missingColumns = requiredColumns.filter(col => !headers.includes(col));
    
    if (missingColumns.length > 0) {
        showNotification(`Colonnes manquantes : ${missingColumns.join(', ')}`, 'danger');
        return;
    }
    
    let importedCount = 0;
    let errorCount = 0;
    const errors = [];
    
    // Parser chaque ligne
    for (let i = 1; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!line) continue;
        
        const values = line.split(',').map(v => v.trim());
        const adherentData = {};
        
        headers.forEach((header, index) => {
            adherentData[header] = values[index] || '';
        });
        
        // Validation du NIP
        if (!validateNIP(adherentData.nip)) {
            errors.push(`Ligne ${i + 1} : NIP invalide (${adherentData.nip})`);
            errorCount++;
            continue;
        }
        
        // Vérifier les doublons
        if (adherentsData.some(a => a.nip === adherentData.nip)) {
            errors.push(`Ligne ${i + 1} : NIP en doublon (${adherentData.nip})`);
            errorCount++;
            continue;
        }
        
        // Créer l'adhérent
        const adherent = {
            id: Date.now() + i,
            nip: adherentData.nip,
            nom: (adherentData.nom || '').toUpperCase(),
            prenom: adherentData.prenom || '',
            date_naissance: adherentData.date_naissance || '',
            sexe: adherentData.sexe || '',
            email: adherentData.email || '',
            telephone: adherentData.telephone || '',
            statut: 'valide',
            organisation_actuelle: null
        };
        
        adherentsData.push(adherent);
        importedCount++;
    }
    
    // Afficher le résultat
    hideImportSection();
    updateAdherentsDisplay();
    updateAdherentsCounters();
    
    let message = `Import terminé : ${importedCount} adhérent(s) importé(s)`;
    if (errorCount > 0) {
        message += `, ${errorCount} erreur(s)`;
        console.error('Erreurs d\'import :', errors);
    }
    
    showNotification(message, importedCount > 0 ? 'success' : 'warning');
}

// Vérifier les adhérents (doublons)
function verifyAdherents() {
    if (adherentsData.length === 0) {
        showNotification('Aucun adhérent à vérifier', 'info');
        return;
    }
    
    showNotification('Vérification en cours...', 'info');
    
    // Vérifier les doublons internes
    const nips = {};
    const doublonsInternes = [];
    
    adherentsData.forEach(adherent => {
        if (nips[adherent.nip]) {
            doublonsInternes.push(adherent.nip);
            adherent.statut = 'doublon_interne';
        } else {
            nips[adherent.nip] = true;
            adherent.statut = 'valide';
        }
    });
    
    // Pour un parti politique, vérifier aussi dans d'autres organisations
    if (selectedOrgType === 'parti_politique') {
        // Simulation de vérification externe
        simulateExternalVerification();
    }
    
    updateAdherentsDisplay();
    updateAdherentsCounters();
    
    if (doublonsInternes.length > 0) {
        showNotification(`${doublonsInternes.length} doublon(s) interne(s) détecté(s)`, 'warning');
    } else {
        showNotification('Aucun doublon interne détecté', 'success');
    }
}

// Simulation de vérification dans d'autres organisations
function simulateExternalVerification() {
    // Simulation : marquer certains adhérents comme déjà membres ailleurs
    const simulatedConflicts = ['1234567890123', '9876543210987'];
    
    adherentsData.forEach(adherent => {
        if (simulatedConflicts.includes(adherent.nip)) {
            adherent.statut = 'externe';
            adherent.organisation_actuelle = 'Parti XYZ'; // Simulation
        }
    });
    
    const conflictsCount = adherentsData.filter(a => a.statut === 'externe').length;
    if (conflictsCount > 0) {
        document.getElementById('conflicts-section').style.display = 'block';
        updateConflictsList();
    }
}

// Export des adhérents
function exportAdherents() {
    if (adherentsData.length === 0) {
        showNotification('Aucun adhérent à exporter', 'info');
        return;
    }
    
    // Créer le CSV
    const headers = ['NIP', 'Nom', 'Prénom', 'Date de naissance', 'Sexe', 'Email', 'Téléphone', 'Statut'];
    const rows = [headers];
    
    adherentsData.forEach(adherent => {
        rows.push([
            adherent.nip,
            adherent.nom,
            adherent.prenom,
            adherent.date_naissance || '',
            adherent.sexe || '',
            adherent.email || '',
            adherent.telephone || '',
            adherent.statut
        ]);
    });
    
    const csvContent = rows.map(row => row.join(',')).join('\n');
    
    // Télécharger le fichier
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `adherents_${selectedOrgType}_${Date.now()}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('Export réussi', 'success');
}

// Mise à jour de l'affichage des adhérents
function updateAdherentsDisplay() {
    const tbody = document.getElementById('adherents-tbody');
    const noDataRow = document.getElementById('no-adherents-row');
    
    if (adherentsData.length === 0) {
        noDataRow.style.display = 'table-row';
        return;
    }
    
    noDataRow.style.display = 'none';
    
    // Filtrer selon la recherche et le statut
    const searchTerm = document.getElementById('adherents-search').value.toLowerCase();
    const filterStatus = document.getElementById('adherents-filter').value;
    
    const filteredAdherents = adherentsData.filter(adherent => {
        const matchesSearch = !searchTerm || 
            adherent.nip.includes(searchTerm) ||
            adherent.nom.toLowerCase().includes(searchTerm) ||
            adherent.prenom.toLowerCase().includes(searchTerm);
            
        const matchesFilter = !filterStatus || adherent.statut === filterStatus;
        
        return matchesSearch && matchesFilter;
    });
    
    // Générer les lignes
    tbody.innerHTML = filteredAdherents.map(adherent => `
        <tr>
            <td>
                <input type="checkbox" class="form-check-input adherent-checkbox" value="${adherent.id}">
            </td>
            <td>${adherent.nip}</td>
            <td>${adherent.nom}</td>
            <td>${adherent.prenom}</td>
            <td>${adherent.email || '-'}</td>
            <td>${adherent.telephone || '-'}</td>
            <td>${getStatusBadge(adherent.statut)}</td>
            <td>${adherent.organisation_actuelle || '-'}</td>
            <td>
                ${adherent.statut === 'externe' ? 
                    `<button class="btn btn-sm btn-warning" onclick="showJustificatifModal(${adherent.id})">
                        <i class="fas fa-file-upload"></i>
                    </button>` : ''
                }
                <button class="btn btn-sm btn-danger" onclick="removeAdherent(${adherent.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Badge de statut
function getStatusBadge(statut) {
    const badges = {
        'valide': '<span class="badge bg-success">✅ Valide</span>',
        'doublon_interne': '<span class="badge bg-danger">🔴 Doublon interne</span>',
        'externe': '<span class="badge bg-warning">🟡 En attente</span>',
        'attente': '<span class="badge bg-secondary">⏳ À vérifier</span>'
    };
    
    return badges[statut] || '<span class="badge bg-secondary">?</span>';
}

// Supprimer un adhérent
function removeAdherent(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?')) return;
    
    adherentsData = adherentsData.filter(a => a.id !== id);
    updateAdherentsDisplay();
    updateAdherentsCounters();
    
    showNotification('Adhérent supprimé', 'success');
}

// Mise à jour des compteurs d'adhérents
function updateAdherentsCounters() {
    const total = adherentsData.length;
    const valides = adherentsData.filter(a => a.statut === 'valide').length;
    const attente = adherentsData.filter(a => a.statut === 'externe').length;
    const erreurs = adherentsData.filter(a => a.statut === 'doublon_interne').length;
    
    // Mise à jour des badges
    document.getElementById('adherents-count').textContent = total;
    document.getElementById('recap-total').textContent = total;
    document.getElementById('recap-valides').textContent = valides;
    document.getElementById('recap-attente').textContent = attente;
    document.getElementById('recap-erreurs').textContent = erreurs;
    
    // Vérifier le minimum requis
    const config = orgTypeConfig[selectedOrgType];
    const minRequired = config ? config.minAdherents : 10;
    document.getElementById('minimum-required').textContent = minRequired;
    
    const statusBadge = document.getElementById('adherents-status-badge');
    const statusText = document.getElementById('adherents-status-text');
    
    if (valides >= minRequired) {
        statusBadge.className = 'badge bg-success fs-6 px-3 py-2';
        statusText.textContent = 'Suffisant';
    } else {
        statusBadge.className = 'badge bg-warning fs-6 px-3 py-2';
        statusText.textContent = `Manque ${minRequired - valides}`;
    }
}

// ============================================
// GESTION DES FONDATEURS
// ============================================

// Ajouter un fondateur
function addFondateur() {
    fondateursCount++;
    
    const container = document.getElementById('fondateurs-container');
    const template = document.getElementById('fondateur-template');
    const newFondateur = template.content.cloneNode(true);
    
    // Mettre à jour les index et titres
    const card = newFondateur.querySelector('.fondateur-card');
    const inputs = newFondateur.querySelectorAll('input, select, textarea');
    const title = newFondateur.querySelector('.fondateur-title');
    
    title.textContent = `Fondateur ${fondateursCount}`;
    
    inputs.forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace('INDEX', fondateursCount - 1));
        }
    });
    
    // Ajouter les événements
    const toggleBtn = newFondateur.querySelector('.toggle-fondateur');
    const removeBtn = newFondateur.querySelector('.remove-fondateur');
    const nipInput = newFondateur.querySelector('.fondateur-nip');
    
    toggleBtn.addEventListener('click', function() {
        toggleFondateurCard(this);
    });
    
    removeBtn.addEventListener('click', function() {
        removeFondateur(this);
    });
    
    nipInput.addEventListener('blur', function() {
        validateFondateurNIP(this);
    });
    
    // Ajouter au conteneur
    container.appendChild(newFondateur);
    
    // Mettre à jour l'affichage
    updateFondateursDisplay();
    
    // Focus sur le premier champ
    const firstInput = card.querySelector('input');
    if (firstInput) firstInput.focus();
}

// Basculer l'affichage d'une carte fondateur
function toggleFondateurCard(button) {
    const card = button.closest('.fondateur-card');
    const body = card.querySelector('.fondateur-body');
    const icon = button.querySelector('i');
    
    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
    } else {
        body.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
    }
}

// Supprimer un fondateur
function removeFondateur(button) {
    const config = orgTypeConfig[selectedOrgType];
    const minRequired = config ? config.minFondateurs : 3;
    
    if (fondateursCount <= minRequired) {
        showNotification(`Minimum ${minRequired} fondateurs requis pour ${config.label}`, 'danger');
        return;
    }
    
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce fondateur ?')) return;
    
    const card = button.closest('.fondateur-card');
    card.remove();
    fondateursCount--;
    
    // Renuméroter les fondateurs restants
    updateFondateursNumbers();
    updateFondateursDisplay();
    
    showNotification('Fondateur supprimé', 'success');
}

// Valider le NIP d'un fondateur
function validateFondateurNIP(input) {
    const nip = input.value;
    const statusIcon = input.nextElementSibling;
    
    if (!nip) {
        statusIcon.innerHTML = '<i class="fas fa-clock text-muted"></i>';
        return;
    }
    
    if (!validateNIP(nip)) {
        statusIcon.innerHTML = '<i class="fas fa-times text-danger"></i>';
        input.classList.add('is-invalid');
        return;
    }
    
    // Vérifier les doublons
    const allNips = Array.from(document.querySelectorAll('.fondateur-nip')).map(i => i.value);
    const duplicates = allNips.filter((n, i) => n === nip && allNips.indexOf(n) !== i);
    
    if (duplicates.length > 0) {
        statusIcon.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i>';
        showNotification('NIP en doublon dans la liste des fondateurs', 'warning');
    } else {
        statusIcon.innerHTML = '<i class="fas fa-check text-success"></i>';
        input.classList.remove('is-invalid');
    }
}

// Mettre à jour la numérotation des fondateurs
function updateFondateursNumbers() {
    const cards = document.querySelectorAll('.fondateur-card');
    cards.forEach((card, index) => {
        const title = card.querySelector('.fondateur-title');
        title.textContent = `Fondateur ${index + 1}`;
        
        // Mettre à jour les noms des champs
        const inputs = card.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
            }
        });
    });
}

// Mise à jour de l'affichage des fondateurs
function updateFondateursDisplay() {
    const count = document.getElementById('fondateurs-count');
    const config = orgTypeConfig[selectedOrgType];
    const minRequired = config ? config.minFondateurs : 3;
    
    count.textContent = fondateursCount;
    document.getElementById('min-required').textContent = minRequired;
    
    const statusText = document.getElementById('fondateurs-status');
    if (fondateursCount >= minRequired) {
        statusText.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Nombre de fondateurs suffisant</span>';
    } else {
        statusText.textContent = `Ajoutez au minimum ${minRequired} fondateurs pour continuer`;
    }
    
    // Afficher la section vérification si nécessaire
    if (fondateursCount > 0) {
        document.getElementById('verification-section').style.display = 'block';
        updateRecapFondateurs();
    }
}

// Mettre à jour le récapitulatif des fondateurs
function updateRecapFondateurs() {
    const tbody = document.getElementById('recap-fondateurs-body');
    const cards = document.querySelectorAll('.fondateur-card');
    
    if (cards.length === 0) {
        document.getElementById('recap-fondateurs').style.display = 'none';
        return;
    }
    
    document.getElementById('recap-fondateurs').style.display = 'block';
    
    tbody.innerHTML = Array.from(cards).map((card, index) => {
        const nip = card.querySelector('.fondateur-nip').value || '-';
        const nom = card.querySelector('.fondateur-nom').value || '-';
        const prenoms = card.querySelector('.fondateur-prenoms').value || '-';
        const fonction = card.querySelector('.fondateur-fonction').value || '-';
        const email = card.querySelector('.fondateur-email').value || '-';
        const telephone = card.querySelector('.fondateur-telephone').value || '-';
        
        return `
            <tr>
                <td>${nip}</td>
                <td>${nom} ${prenoms}</td>
                <td>${fonction}</td>
                <td>${email}</td>
                <td>${telephone}</td>
                <td><span class="badge bg-success">Valide</span></td>
            </tr>
        `;
    }).join('');
}

// Vérifier les doublons de fondateurs
function verifierDoublons() {
    const cards = document.querySelectorAll('.fondateur-card');
    const nips = [];
    const doublons = [];
    
    cards.forEach(card => {
        const nipInput = card.querySelector('.fondateur-nip');
        const nip = nipInput.value;
        
        if (nip) {
            if (nips.includes(nip)) {
                doublons.push(nip);
                nipInput.classList.add('is-invalid');
            } else {
                nips.push(nip);
                nipInput.classList.remove('is-invalid');
            }
        }
    });
    
    const doublonsDiv = document.getElementById('doublons-internes');
    const validDiv = document.getElementById('verification-propre');
    
    if (doublons.length > 0) {
        doublonsDiv.style.display = 'block';
        validDiv.style.display = 'none';
        
        document.getElementById('doublons-internes-list').innerHTML = 
            `Les NIP suivants sont en doublon : ${doublons.join(', ')}`;
            
        showNotification(`${doublons.length} doublon(s) détecté(s)`, 'warning');
    } else {
        doublonsDiv.style.display = 'none';
        validDiv.style.display = 'block';
        
        showNotification('Aucun doublon détecté', 'success');
    }
}

// ============================================
// GÉOLOCALISATION
// ============================================

// Obtenir la position actuelle
function getCurrentLocation() {
    if (!navigator.geolocation) {
        showNotification('La géolocalisation n\'est pas supportée par votre navigateur', 'danger');
        return;
    }
    
    const button = document.getElementById('getCurrentLocation');
    const originalHTML = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Localisation en cours...';
    button.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            
            // Vérifier que c'est au Gabon
            if (latitude < -3 || latitude > 3 || longitude < 8 || longitude > 15) {
                showNotification('Votre position semble être en dehors du Gabon', 'warning');
            }
            
            // Remplir les champs
            document.getElementById('org_latitude').value = latitude.toFixed(7);
            document.getElementById('org_longitude').value = longitude.toFixed(7);
            
            showNotification('Position obtenue avec succès', 'success');
            
            button.innerHTML = originalHTML;
            button.disabled = false;
        },
        (error) => {
            let message = 'Impossible d\'obtenir votre position';
            
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Vous avez refusé l\'accès à votre position';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Position indisponible';
                    break;
                case error.TIMEOUT:
                    message = 'Délai d\'attente dépassé';
                    break;
            }
            
            showNotification(message, 'danger');
            
            button.innerHTML = originalHTML;
            button.disabled = false;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Initialiser la géolocalisation
function initializeGeolocation() {
    // Charger les départements selon la province sélectionnée
    loadDepartements();
    
    // Afficher/masquer les champs selon le type de zone
    toggleZoneFields();
}

// Charger les départements d'une province
function loadDepartements() {
    const province = document.getElementById('org_province').value;
    const departementSelect = document.getElementById('org_departement');
    const prefectureSelect = document.getElementById('org_prefecture');
    
    if (!province) {
        departementSelect.innerHTML = '<option value="">Sélectionnez d\'abord une province</option>';
        prefectureSelect.innerHTML = '<option value="">Sélectionnez d\'abord une province</option>';
        return;
    }
    
    // Simulation de données départements/préfectures
    const data = {
        'estuaire': {
            departements: ['Komo', 'Komo-Mondah', 'Noya'],
            prefectures: ['Libreville', 'Akanda', 'Owendo', 'Ntoum']
        },
        'haut_ogooue': {
            departements: ['Djouori-Agnili', 'Lekabi-Lewolo', 'Lekoni-Lekori'],
            prefectures: ['Franceville', 'Moanda', 'Okondja', 'Bongoville']
        },
        // Ajouter d'autres provinces...
    };
    
    const provinceData = data[province] || { departements: [], prefectures: [] };
    
    // Remplir les départements
    departementSelect.innerHTML = '<option value="">Sélectionnez un département</option>' +
        provinceData.departements.map(d => `<option value="${d}">${d}</option>`).join('');
    
    // Remplir les préfectures
    prefectureSelect.innerHTML = '<option value="">Sélectionnez une préfecture</option>' +
        provinceData.prefectures.map(p => `<option value="${p}">${p}</option>`).join('');
}

// Basculer les champs selon le type de zone
function toggleZoneFields() {
    const zoneType = document.getElementById('org_zone_type').value;
    
    const urbainFields = ['ville_commune_container', 'arrondissement_container', 'quartier_container'];
    const ruralFields = ['village_container', 'lieu_dit_container'];
    
    if (zoneType === 'urbaine') {
        urbainFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('d-none');
        });
        ruralFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
    } else if (zoneType === 'rurale') {
        ruralFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('d-none');
        });
        urbainFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
    }
}

// ============================================
// FONCTIONS SPÉCIFIQUES PAR TYPE
// ============================================

// Afficher les champs spécifiques selon le type d'organisation
function showOrgTypeSpecificFields() {
    if (!selectedOrgType) return;
    
    // Afficher le bon secteur d'activité
    document.querySelectorAll('#org_secteur optgroup').forEach(group => {
        group.classList.add('d-none');
    });
    
    const secteurGroup = document.getElementById(`secteurs-${selectedOrgType.replace('_', '-')}`);
    if (secteurGroup) {
        secteurGroup.classList.remove('d-none');
    }
    
    // Afficher les bonnes activités
    document.querySelectorAll('.activites-type').forEach(div => {
        div.classList.add('d-none');
    });
    
    const activitesDiv = document.getElementById(`activites-${selectedOrgType.replace('_', '-')}`);
    if (activitesDiv) {
        activitesDiv.classList.remove('d-none');
    }
    
    // Mettre à jour les exigences affichées
    updateTypeRequirements();
}

// Mettre à jour les exigences selon le type
function updateTypeRequirements() {
    // Fondateurs
    document.querySelectorAll('.requirements-info').forEach(req => {
        req.classList.add('d-none');
    });
    
    const fondateursReq = document.getElementById(`req-${selectedOrgType.replace('_', '-')}`);
    if (fondateursReq) {
        fondateursReq.classList.remove('d-none');
    }
    
    // Adhérents
    const adherentsReq = document.getElementById(`req-adherents-${selectedOrgType.replace('_', '-')}`);
    if (adherentsReq) {
        adherentsReq.classList.remove('d-none');
    }
    
    // Afficher les checkboxes spécifiques pour parti politique
    if (selectedOrgType === 'parti_politique') {
        document.getElementById('parti-politique-exclusivite').style.display = 'block';
        document.getElementById('parti-adherents-check').style.display = 'block';
        document.getElementById('declaration-parti').style.display = 'block';
    }
}

// Afficher les documents spécifiques selon le type
function showSpecificDocuments() {
    if (!selectedOrgType) return;
    
    // Masquer tous les types de documents
    document.querySelectorAll('.documents-type').forEach(div => {
        div.classList.add('d-none');
    });
    
    // Afficher les documents du type sélectionné
    const docsDiv = document.getElementById(`docs-${selectedOrgType.replace('_', '-')}`);
    if (docsDiv) {
        docsDiv.classList.remove('d-none');
    }
}

// Générer les champs CNI pour les fondateurs
function generateCNIFields() {
    const container = document.getElementById('cni-fondateurs-container');
    const fondateurs = document.querySelectorAll('.fondateur-card');
    
    container.innerHTML = '';
    
    fondateurs.forEach((card, index) => {
        const nom = card.querySelector('.fondateur-nom').value || '';
        const prenoms = card.querySelector('.fondateur-prenoms').value || '';
        
        const cniField = document.createElement('div');
        cniField.className = 'col-md-6 mb-4';
        cniField.innerHTML = `
            <div class="document-upload-card p-3 border rounded">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">
                            <i class="fas fa-id-card text-warning me-2"></i>
                            CNI ${nom} ${prenoms}
                            <span class="badge bg-danger ms-1">*</span>
                        </h6>
                        <small class="text-muted">Fondateur ${index + 1}</small>
                    </div>
                    <div class="document-status" id="status-cni-fondateur-${index}">
                        <i class="fas fa-clock text-muted"></i>
                    </div>
                </div>
                <div class="input-group">
                    <input type="file" 
                           class="form-control document-input" 
                           id="doc-cni-fondateur-${index}" 
                           name="documents[cni_fondateur_${index}]"
                           accept=".pdf,.jpg,.png"
                           data-doc-type="cni-fondateur-${index}"
                           data-required="true"
                           onchange="handleDocumentUpload(this)">
                    <button class="btn btn-outline-secondary btn-preview d-none" 
                            type="button"
                            onclick="previewDocument('cni-fondateur-${index}')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="form-text">Carte d'identité recto-verso</div>
            </div>
        `;
        
        container.appendChild(cniField);
    });
}

// ============================================
// INITIALISATION DES COMPTEURS
// ============================================

// Initialiser les compteurs de caractères
function initializeCharCounters() {
    // Nom organisation
    const nomInput = document.getElementById('org_nom');
    if (nomInput) {
        nomInput.addEventListener('input', function() {
            const counter = document.getElementById('nom-counter');
            if (counter) counter.textContent = this.value.length;
        });
    }
    
    // Objet social
    const objetInput = document.getElementById('org_objet');
    if (objetInput) {
        objetInput.addEventListener('input', function() {
            const counter = document.getElementById('objet-counter');
            if (counter) counter.textContent = this.value.length;
        });
    }
    
    // Objectifs
    const objectifsInput = document.getElementById('org_objectifs');
    if (objectifsInput) {
        objectifsInput.addEventListener('input', function() {
            const counter = document.getElementById('objectifs-counter');
            if (counter) counter.textContent = this.value.length;
        });
    }
    
    // Adresse
    const adresseInput = document.getElementById('org_adresse_complete');
    if (adresseInput) {
        adresseInput.addEventListener('input', function() {
            const counter = document.getElementById('adresse-counter');
            if (counter) counter.textContent = this.value.length;
        });
    }
    
    // Points de repère
    const repereInput = document.getElementById('org_points_repere');
    if (repereInput) {
        repereInput.addEventListener('input', function() {
            const counter = document.getElementById('repere-counter');
            if (counter) counter.textContent = this.value.length;
        });
    }
    
    // Activités sélectionnées
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('activite-checkbox')) {
            const checked = document.querySelectorAll('.activite-checkbox:checked');
            const counter = document.getElementById('activites-counter');
            if (counter) counter.textContent = checked.length;
            
            // Limiter à 5
            if (checked.length > 5) {
                e.target.checked = false;
                showNotification('Maximum 5 activités autorisées', 'warning');
            }
        }
    });
}

// ============================================
// FONCTIONS ÉTAPE 9 - SOUMISSION
// ============================================

// Vérifier la complétude pour l'étape 9
function checkCompleteness() {
    const completion = checkGlobalCompletion();
    
    // Mettre à jour les icônes et badges
    updateCompletionStatus('infos', completion.sections.infos);
    updateCompletionStatus('fondateurs', completion.sections.fondateurs);
    updateCompletionStatus('adherents', completion.sections.adherents);
    updateCompletionStatus('documents', completion.sections.documents);
    
    // Mettre à jour la barre de progression
    const progressBar = document.getElementById('completion-progress');
    progressBar.style.width = completion.percentage + '%';
    progressBar.querySelector('span').textContent = completion.percentage + '% Complété';
    
    // Afficher l'alerte appropriée
    const alert = document.getElementById('completion-alert');
    alert.classList.remove('d-none', 'alert-success', 'alert-warning', 'alert-danger');
    
    if (completion.percentage === 100) {
        alert.className = 'alert alert-success';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            <strong>Excellent !</strong> Votre dossier est complet et prêt à être soumis.
        `;
        document.getElementById('submitFinalBtn').disabled = false;
    } else if (completion.percentage >= 75) {
        alert.className = 'alert alert-warning';
        alert.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Presque terminé !</strong> Il reste quelques éléments à compléter.
        `;
        document.getElementById('submitFinalBtn').disabled = true;
    } else {
        alert.className = 'alert alert-danger';
        alert.innerHTML = `
            <i class="fas fa-times-circle me-2"></i>
            <strong>Dossier incomplet.</strong> Plusieurs sections nécessitent votre attention.
        `;
        document.getElementById('submitFinalBtn').disabled = true;
    }
}

// Mettre à jour le statut de complétude d'une section
function updateCompletionStatus(section, percentage) {
    const icon = document.getElementById(`icon-${section}`);
    const badge = document.getElementById(`badge-${section}`);
    
    if (percentage === 100) {
        icon.style.color = '#28a745';
        badge.className = 'badge bg-success';
        badge.innerHTML = '<i class="fas fa-check me-1"></i>Complet';
    } else if (percentage > 0) {
        icon.style.color = '#ffc107';
        badge.className = 'badge bg-warning';
        badge.innerHTML = `<i class="fas fa-hourglass-half me-1"></i>${Math.round(percentage)}%`;
    } else {
        icon.style.color = '#dc3545';
        badge.className = 'badge bg-danger';
        badge.innerHTML = '<i class="fas fa-times me-1"></i>Incomplet';
    }
}

// Générer le récapitulatif complet
function generateFullSummary() {
    const savedData = JSON.parse(localStorage.getItem('organizationFormData') || '{}');
    
    // Type d'organisation
    document.getElementById('summary-type').textContent = orgTypeConfig[selectedOrgType]?.label || selectedOrgType;
    
    // Informations demandeur
    const step3 = savedData.step3 || {};
    document.getElementById('summary-demandeur-nip').textContent = step3.demandeur_nip || '-';
    document.getElementById('summary-demandeur-nom').textContent = 
        `${step3.demandeur_civilite || ''} ${step3.demandeur_nom || ''} ${step3.demandeur_prenoms || ''}`.trim() || '-';
    document.getElementById('summary-demandeur-email').textContent = step3.demandeur_email || '-';
    document.getElementById('summary-demandeur-tel').textContent = step3.demandeur_telephone || '-';
    document.getElementById('summary-demandeur-cni').textContent = step3.demandeur_cni_numero || '-';
    document.getElementById('summary-demandeur-adresse').textContent = step3.demandeur_adresse || '-';
    
    // Informations organisation
    const step4 = savedData.step4 || {};
    document.getElementById('summary-org-nom').textContent = step4.org_nom || '-';
    document.getElementById('summary-org-sigle').textContent = step4.org_sigle || 'Aucun';
    document.getElementById('summary-org-secteur').textContent = step4.org_secteur || '-';
    document.getElementById('summary-org-zone').textContent = step4.org_zone_intervention || '-';
    document.getElementById('summary-org-public').textContent = step4.org_public_cible || '-';
    document.getElementById('summary-org-objet').textContent = step4.org_objet || '-';
    
    // Activités sélectionnées
    const activites = Array.from(document.querySelectorAll('.activite-checkbox:checked'))
        .map(cb => cb.parentElement.textContent.trim());
    document.getElementById('summary-org-activites').textContent = 
        activites.length > 0 ? activites.join(', ') : '-';
    
    // Siège social
    const step5 = savedData.step5 || {};
    document.getElementById('summary-siege-province').textContent = step5.org_province || '-';
    document.getElementById('summary-siege-prefecture').textContent = step5.org_prefecture || '-';
    document.getElementById('summary-siege-zone').textContent = step5.org_zone_type || '-';
    document.getElementById('summary-siege-tel').textContent = step5.org_telephone || '-';
    document.getElementById('summary-siege-email').textContent = step5.org_email || '-';
    document.getElementById('summary-siege-adresse').textContent = step5.org_adresse_complete || '-';
    
    // GPS
    const gps = (step5.org_latitude && step5.org_longitude) ? 
        `${step5.org_latitude}, ${step5.org_longitude}` : 'Non renseigné';
    document.getElementById('summary-siege-gps').textContent = gps;
    
    // Fondateurs et Adhérents
    const config = orgTypeConfig[selectedOrgType];
    document.getElementById('summary-fondateurs-count').textContent = fondateursCount;
    document.getElementById('summary-fondateurs-min').textContent = 
        `Minimum requis : ${config?.minFondateurs || 3}`;
    
    document.getElementById('summary-adherents-count').textContent = adherentsData.length;
    document.getElementById('summary-adherents-min').textContent = 
        `Minimum requis : ${config?.minAdherents || 10}`;
    
    // Documents
    updateDocumentsSummary();
}

// Mettre à jour le récapitulatif des documents
function updateDocumentsSummary() {
    const requiredList = document.getElementById('summary-docs-required');
    const optionalList = document.getElementById('summary-docs-optional');
    
    const requiredDocs = document.querySelectorAll('.document-input[data-required="true"]');
    const optionalDocs = document.querySelectorAll('.document-input[data-required="false"]');
    
    // Documents obligatoires
    requiredList.innerHTML = Array.from(requiredDocs).map(input => {
        const hasFile = input.files && input.files.length > 0;
        const label = input.closest('.document-upload-card')?.querySelector('h6')?.textContent.trim() || 'Document';
        const icon = hasFile ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
        return `<li>${icon} ${label}</li>`;
    }).join('');
    
    // Documents optionnels
    optionalList.innerHTML = Array.from(optionalDocs).map(input => {
        const hasFile = input.files && input.files.length > 0;
        const label = input.closest('.document-upload-card')?.querySelector('h6')?.textContent.trim() || 'Document';
        const icon = hasFile ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-minus text-muted"></i>';
        return `<li>${icon} ${label}</li>`;
    }).join('');
}

// Soumettre définitivement
function submitFinal() {
    // Vérifier toutes les validations
    if (!validateStep9()) {
        return;
    }
    
    // Préparer les données pour la modal
    const savedData = JSON.parse(localStorage.getItem('organizationFormData') || '{}');
    document.getElementById('confirm-org-name').textContent = savedData.step4?.org_nom || '-';
    document.getElementById('confirm-org-type').textContent = orgTypeConfig[selectedOrgType]?.label || '-';
    document.getElementById('confirm-demandeur').textContent = 
        `${savedData.step3?.demandeur_nom || ''} ${savedData.step3?.demandeur_prenoms || ''}`.trim() || '-';
    document.getElementById('confirm-email').textContent = savedData.step3?.demandeur_email || '-';
    
    // Afficher la modal
    const modal = new bootstrap.Modal(document.getElementById('confirmSubmitModal'));
    modal.show();
    
    // Gérer la checkbox de confirmation
    document.getElementById('final-confirm').addEventListener('change', function() {
        document.getElementById('final-submit-btn').disabled = !this.checked;
    });
}

// Confirmer la soumission
function confirmSubmission() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmSubmitModal'));
    modal.hide();
    
    // Afficher un loader
    showNotification('Soumission en cours...', 'info', 10000);
    
    // Préparer toutes les données
    prepareSubmissionData();
    
    // Simuler l'envoi (à remplacer par un vrai appel AJAX)
    setTimeout(() => {
        // Générer un numéro de dossier
        const dossierNumber = `ORG-2025-${Math.floor(Math.random() * 90000) + 10000}`;
        document.getElementById('dossier-number').textContent = dossierNumber;
        
        // Sauvegarder le numéro
        localStorage.setItem('lastDossierNumber', dossierNumber);
        
        // Nettoyer le localStorage
        localStorage.removeItem('organizationFormData');
        
        // Afficher la modal de succès
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    }, 3000);
}

// Sauvegarder en brouillon
function saveDraft() {
    saveCurrentStepData();
    
    showNotification('Sauvegarde en cours...', 'info');
    
    // Simuler la sauvegarde
    setTimeout(() => {
        showNotification('Brouillon sauvegardé avec succès', 'success');
    }, 1000);
}

// Préparer les données pour la soumission
function prepareSubmissionData() {
    const formData = new FormData();
    const savedData = JSON.parse(localStorage.getItem('organizationFormData') || '{}');
    
    // Ajouter toutes les données sauvegardées
    Object.keys(savedData).forEach(key => {
        if (typeof savedData[key] === 'object') {
            Object.keys(savedData[key]).forEach(field => {
                formData.append(field, savedData[key][field]);
            });
        } else {
            formData.append(key, savedData[key]);
        }
    });
    
    // Ajouter les fondateurs
    const fondateurs = [];
    document.querySelectorAll('.fondateur-card').forEach((card, index) => {
        const fondateur = {};
        card.querySelectorAll('input, select, textarea').forEach(field => {
            const name = field.name;
            if (name) {
                const fieldName = name.match(/\[([^\]]+)\]$/)?.[1];
                if (fieldName) {
                    fondateur[fieldName] = field.value;
                }
            }
        });
        fondateurs.push(fondateur);
    });
    formData.append('fondateurs', JSON.stringify(fondateurs));
    
    // Ajouter les adhérents
    formData.append('adherents', JSON.stringify(adherentsData));
    
    // Ajouter les fichiers
    Object.keys(documentsUploaded).forEach(docType => {
        formData.append(`document_${docType}`, documentsUploaded[docType]);
    });
    
    return formData;
}

// Imprimer le récapitulatif
function printSummary() {
    window.print();
}

// Formatage de la taille des fichiers
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Préfill des informations du demandeur (si disponibles)
function prefillDemandeurInfo() {
    // Cette fonction pourrait être utilisée pour pré-remplir avec les données
    // du compte utilisateur connecté si disponibles
}

// Mise à jour de la liste des conflits
function updateConflictsList() {
    const conflictsList = document.getElementById('conflicts-list');
    const conflicts = adherentsData.filter(a => a.statut === 'externe');
    
    conflictsList.innerHTML = conflicts.map(adherent => `
        <div class="card mb-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-1">${adherent.nom} ${adherent.prenom}</h6>
                        <p class="mb-0 text-muted">NIP : ${adherent.nip}</p>
                        <p class="mb-0 text-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Membre actuel de : <strong>${adherent.organisation_actuelle}</strong>
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-warning" onclick="showJustificatifModal(${adherent.id})">
                            <i class="fas fa-file-upload me-2"></i>
                            Fournir un justificatif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Afficher la modal de justificatif
function showJustificatifModal(adherentId) {
    const adherent = adherentsData.find(a => a.id === adherentId);
    if (!adherent) return;
    
    document.getElementById('adherent-conflict-name').textContent = `${adherent.nom} ${adherent.prenom}`;
    document.getElementById('adherent-conflict-nip').textContent = adherent.nip;
    document.getElementById('adherent-conflict-org').textContent = adherent.organisation_actuelle;
    
    const modal = new bootstrap.Modal(document.getElementById('justificatifModal'));
    modal.show();
    
    // Stocker l'ID pour l'upload
    window.currentConflictAdherentId = adherentId;
}

// Upload du justificatif
function uploadJustificatif() {
    const file = document.getElementById('justificatif_file').files[0];
    const type = document.getElementById('justificatif_type').value;
    const notes = document.getElementById('justificatif_notes').value;
    
    if (!file || !type) {
        showNotification('Veuillez fournir un fichier et sélectionner le type', 'danger');
        return;
    }
    
    // Mettre à jour le statut de l'adhérent
    const adherent = adherentsData.find(a => a.id === window.currentConflictAdherentId);
    if (adherent) {
        adherent.statut = 'valide';
        adherent.justificatif = {
            file: file.name,
            type: type,
            notes: notes
        };
    }
    
    // Fermer la modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('justificatifModal'));
    modal.hide();
    
    // Mettre à jour l'affichage
    updateAdherentsDisplay();
    updateAdherentsCounters();
    updateConflictsList();
    
    showNotification('Justificatif ajouté avec succès', 'success');
}

// Recherche et filtre des adhérents
document.addEventListener('DOMContentLoaded', function() {
    // Recherche
    const searchInput = document.getElementById('adherents-search');
    if (searchInput) {
        searchInput.addEventListener('input', updateAdherentsDisplay);
    }
    
    // Filtre
    const filterSelect = document.getElementById('adherents-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', updateAdherentsDisplay);
    }
    
    // Select all checkbox
    const selectAll = document.getElementById('select-all-adherents');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.adherent-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }
});

// Vérifier un adhérent dans d'autres organisations (simulation)
function checkAdherentInOtherOrgs(adherent) {
    // Simulation d'une vérification asynchrone
    setTimeout(() => {
        // Simuler un conflit pour certains NIP
        if (adherent.nip.endsWith('123') || adherent.nip.endsWith('987')) {
            adherent.statut = 'externe// SUITE DE LA SECTION M À PARTIR DE LA COUPURE

';
adherent.organisation_actuelle = 'Parti XYZ'; // Simulation
}
}, 1000);
}

// ============================================
// GESTION DES LISTES D'ADHÉRENTS
// ============================================

// Mise à jour des conflits d'adhérents
function updateAdherentsConflicts() {
const conflicts = adherentsData.filter(a => a.statut === 'externe');

if (conflicts.length > 0) {
document.getElementById('conflicts-section').style.display = 'block';
updateConflictsList();
} else {
document.getElementById('conflicts-section').style.display = 'none';
}
}

// Sélection multiple d'adhérents
function toggleAllAdherents(selectAll) {
const checkboxes = document.querySelectorAll('.adherent-checkbox');
checkboxes.forEach(cb => {
cb.checked = selectAll.checked;
});
}

// Actions groupées sur les adhérents
function performBulkAction(action) {
const selected = document.querySelectorAll('.adherent-checkbox:checked');
const selectedIds = Array.from(selected).map(cb => parseInt(cb.value));

if (selectedIds.length === 0) {
showNotification('Veuillez sélectionner au moins un adhérent', 'warning');
return;
}

switch(action) {
case 'delete':
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedIds.length} adhérent(s) ?`)) {
        adherentsData = adherentsData.filter(a => !selectedIds.includes(a.id));
        updateAdherentsDisplay();
        updateAdherentsCounters();
        showNotification(`${selectedIds.length} adhérent(s) supprimé(s)`, 'success');
    }
    break;
case 'validate':
    selectedIds.forEach(id => {
        const adherent = adherentsData.find(a => a.id === id);
        if (adherent && adherent.statut === 'attente') {
            adherent.statut = 'valide';
        }
    });
    updateAdherentsDisplay();
    updateAdherentsCounters();
    showNotification(`${selectedIds.length} adhérent(s) validé(s)`, 'success');
    break;
}
}

// ============================================
// GESTION DES DOCUMENTS AVANCÉE
// ============================================

// Vérification des documents manquants
function checkMissingDocuments() {
const missingDocs = [];
const requiredDocs = document.querySelectorAll('.document-input[data-required="true"]');

requiredDocs.forEach(input => {
if (!input.files || input.files.length === 0) {
    const label = input.closest('.document-upload-card')?.querySelector('h6')?.textContent.trim();
    missingDocs.push(label);
}
});

const missingAlert = document.getElementById('missing-docs-alert');
const missingList = document.getElementById('missing-docs-list');

if (missingDocs.length > 0) {
missingAlert.classList.remove('d-none');
missingList.innerHTML = `
    <p class="mb-2">Documents obligatoires manquants :</p>
    <ul class="mb-0">
        ${missingDocs.map(doc => `<li>${doc}</li>`).join('')}
    </ul>
`;
} else {
missingAlert.classList.add('d-none');
}

// Mettre à jour les compteurs
document.getElementById('recap-docs-missing').textContent = missingDocs.length;

return missingDocs.length === 0;
}

// Upload multiple de documents
function handleMultipleDocuments(input) {
const files = Array.from(input.files);
const docType = input.getAttribute('data-doc-type');

if (files.length === 0) return;

// Validation globale
let totalSize = 0;
let hasInvalidFile = false;

files.forEach(file => {
totalSize += file.size;
if (!['application/pdf', 'image/jpeg', 'image/png'].includes(file.type)) {
    hasInvalidFile = true;
}
});

if (hasInvalidFile) {
showNotification('Certains fichiers ont un format non autorisé', 'danger');
input.value = '';
return;
}

if (totalSize > 20 * 1024 * 1024) { // 20MB total
showNotification('La taille totale des fichiers dépasse 20MB', 'danger');
input.value = '';
return;
}

// Stocker les fichiers
documentsUploaded[docType] = files;

// Afficher le nombre de fichiers
const statusIcon = document.getElementById(`status-${docType}`);
statusIcon.innerHTML = `<span class="badge bg-success">${files.length} fichier(s)</span>`;

updateDocumentCounters();
}

// ============================================
// INTÉGRATION AVEC LE BACKEND LARAVEL
// ============================================

// Configuration AJAX pour Laravel
function setupAjaxDefaults() {
// Token CSRF
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Headers par défaut
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
}

// Vérification NIP en temps réel
async function verifyNIPRealTime(nip, callback) {
if (!nip || nip.length !== 13) {
callback(false, 'NIP invalide');
return;
}

try {
// Simulation d'appel API
// const response = await axios.post('/api/verify-nip', { nip });

// Pour la démo, simulation de réponse
setTimeout(() => {
    const isValid = !nip.endsWith('000'); // Simulation
    const message = isValid ? 'NIP disponible' : 'NIP déjà utilisé';
    callback(isValid, message);
}, 500);
} catch (error) {
callback(false, 'Erreur de vérification');
}
}

// Sauvegarde automatique périodique
function setupAutoSave() {
setInterval(() => {
if (document.hidden) return; // Ne pas sauvegarder si l'onglet est inactif

const currentData = collectAllFormData();
const savedData = localStorage.getItem('organizationFormData');

// Sauvegarder seulement s'il y a des changements
if (JSON.stringify(currentData) !== savedData) {
    localStorage.setItem('organizationFormData', JSON.stringify(currentData));
    showNotification('Sauvegarde automatique', 'success', 1000);
}
}, 30000); // Toutes les 30 secondes
}

// Collecter toutes les données du formulaire
function collectAllFormData() {
const data = {
type: selectedOrgType,
currentStep: currentStep,
timestamp: new Date().toISOString()
};

// Collecter les données de chaque étape
for (let i = 1; i <= currentStep; i++) {
const stepData = {};
const stepElement = document.getElementById('step' + i);

if (stepElement) {
    stepElement.querySelectorAll('input, select, textarea').forEach(field => {
        if (field.name && field.type !== 'file') {
            if (field.type === 'checkbox' || field.type === 'radio') {
                if (field.checked) {
                    stepData[field.name] = field.value;
                }
            } else {
                stepData[field.name] = field.value;
            }
        }
    });
}

data['step' + i] = stepData;
}

// Ajouter les données spéciales
data.fondateurs = collectFondateursData();
data.adherents = adherentsData;

return data;
}

// Collecter les données des fondateurs
function collectFondateursData() {
const fondateurs = [];

document.querySelectorAll('.fondateur-card').forEach((card, index) => {
const fondateur = {};

card.querySelectorAll('input, select, textarea').forEach(field => {
    const name = field.name;
    if (name) {
        const fieldName = name.match(/\[([^\]]+)\]$/)?.[1];
        if (fieldName) {
            fondateur[fieldName] = field.value;
        }
    }
});

if (Object.keys(fondateur).length > 0) {
    fondateurs.push(fondateur);
}
});

return fondateurs;
}

// ============================================
// GESTION DES ERREURS GLOBALES
// ============================================

// Gestionnaire d'erreurs global
window.addEventListener('error', function(e) {
console.error('Erreur globale:', e.error);
showNotification('Une erreur inattendue s\'est produite', 'danger');
});

// Gestion des promesses rejetées
window.addEventListener('unhandledrejection', function(e) {
console.error('Promesse rejetée:', e.reason);
showNotification('Une erreur de traitement s\'est produite', 'danger');
});

// ============================================
// FONCTIONS UTILITAIRES ADDITIONNELLES
// ============================================

// Debounce pour les validations
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

// Validation NIP avec debounce
const debouncedNIPValidation = debounce(function(input) {
const nip = input.value;
const statusIcon = input.nextElementSibling?.querySelector('i');

if (!nip) return;

if (statusIcon) {
statusIcon.className = 'fas fa-spinner fa-spin';
}

verifyNIPRealTime(nip, (isValid, message) => {
if (statusIcon) {
    statusIcon.className = isValid ? 'fas fa-check text-success' : 'fas fa-times text-danger';
}

if (!isValid) {
    input.classList.add('is-invalid');
    const feedback = input.parentElement.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
    }
} else {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
}
});
}, 500);

// ============================================
// INITIALISATION AU CHARGEMENT DE LA PAGE
// ============================================

// Initialisation complète
document.addEventListener('DOMContentLoaded', function() {
// Configuration initiale
setupAjaxDefaults();
setupAutoSave();

// Event listeners pour la validation NIP en temps réel
document.addEventListener('input', function(e) {
if (e.target.classList.contains('fondateur-nip') || 
    e.target.id === 'adherent_nip' ||
    e.target.id === 'demandeur_nip') {
    debouncedNIPValidation(e.target);
}
});

// Gestion des tooltips Bootstrap
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Initialiser les popovers
const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
popoverTriggerList.map(function (popoverTriggerEl) {
return new bootstrap.Popover(popoverTriggerEl);
});

// Gestion du bouton retour navigateur
window.addEventListener('popstate', function(e) {
if (confirm('Attention : Vous risquez de perdre vos données non sauvegardées. Voulez-vous vraiment quitter ?')) {
    return true;
} else {
    window.history.pushState(null, null, window.location.pathname);
    return false;
}
});

// Avertissement avant de quitter la page
window.addEventListener('beforeunload', function(e) {
const hasUnsavedData = localStorage.getItem('organizationFormData') !== null;

if (hasUnsavedData && currentStep > 1) {
    e.preventDefault();
    e.returnValue = 'Vous avez des données non soumises. Êtes-vous sûr de vouloir quitter ?';
    return e.returnValue;
}
});

// Gestion du responsive pour les tables
function makeTablesResponsive() {
const tables = document.querySelectorAll('table:not(.table-responsive table)');
tables.forEach(table => {
    const wrapper = document.createElement('div');
    wrapper.className = 'table-responsive';
    table.parentNode.insertBefore(wrapper, table);
    wrapper.appendChild(table);
});
}

makeTablesResponsive();

// Initialisation des éléments selon le type d'organisation
if (selectedOrgType) {
showOrgTypeSpecificFields();
updateTypeRequirements();
}

// Restaurer l'état si nécessaire
const savedStep = localStorage.getItem('currentStep');
if (savedStep) {
currentStep = parseInt(savedStep);
updateStepDisplay();
}
});

// ============================================
// EXPORT DES FONCTIONS POUR TESTS
// ============================================

// Pour faciliter les tests unitaires
if (typeof module !== 'undefined' && module.exports) {
module.exports = {
validateNIP,
validateEmail,
validatePhone,
formatPhoneNumber,
formatFileSize,
debounce
};
}
</script>
<!-- ========== FIN SECTION M - JAVASCRIPT FONCTIONNALITÉS ========== -->


<style>
/* Indicateurs d'étapes */
.step-indicators {
    display: flex;
    justify-content: space-between;
}

@media (max-width: 768px) {
    .step-indicators {
        flex-wrap: wrap;
    }
    .step-indicators .col {
        flex-basis: 20%;
        margin-bottom: 10px;
    }
}

.step-indicator {
    opacity: 0.5;
    transition: all 0.3s ease;
    text-align: center;
}

.step-indicator.active {
    opacity: 1;
}

.step-indicator.completed {
    opacity: 1;
}

.step-indicator.completed .step-icon {
    color: #009e3f !important;
}

.step-icon {
    font-size: 1.2rem;
    color: #6c757d;
    transition: color 0.3s ease;
}

.step-indicator.active .step-icon {
    color: #009e3f;
}

/* Progress bar */
.progress-bar {
    transition: width 0.5s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .step-indicators .col {
        font-size: 0.8rem;
    }
    
    .step-icon {
        font-size: 1rem;
    }
}

/* Buttons */
.btn-success {
    background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #00b347 0%, #009e3f 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 158, 63, 0.3);
}

/* Modal */
.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

/* Form controls */
.form-control:focus, .form-select:focus {
    border-color: #009e3f;
    box-shadow: 0 0 0 0.2rem rgba(0, 158, 63, 0.25);
}

.form-control.is-valid, .form-select.is-valid {
    border-color: #009e3f;
}

.form-control.is-invalid, .form-select.is-invalid {
    border-color: #dc3545;
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

/* Required field indicator */
.required::after {
    content: " *";
    color: #dc3545;
}
</style>

<script>
let currentStep = 1;
const totalSteps = 9;
let selectedOrgType = '';

document.addEventListener('DOMContentLoaded', function() {
    updateStepDisplay();
    
    // Auto-update progress when moving between steps
    const progressBar = document.getElementById('globalProgress');
    const currentStepDisplay = document.getElementById('currentStepNumber');
    
    function updateStepDisplay() {
        // Update progress bar
        const progress = (currentStep / totalSteps) * 100;
        progressBar.style.width = progress + '%';
        currentStepDisplay.textContent = currentStep;
        
        // Update step indicators
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            const stepNumber = index + 1;
            indicator.classList.remove('active', 'completed');
            
            if (stepNumber === currentStep) {
                indicator.classList.add('active');
            } else if (stepNumber < currentStep) {
                indicator.classList.add('completed');
            }
        });
        
        // Update navigation buttons
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
        
        if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.classList.remove('d-none');
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.classList.add('d-none');
        }
    }
    
    // Navigation function
    window.changeStep = function(direction) {
        if (direction === 1 && currentStep < totalSteps) {
            currentStep++;
        } else if (direction === -1 && currentStep > 1) {
            currentStep--;
        }
        updateStepDisplay();
    };
    
    // Initialize display
    updateStepDisplay();
});
</script>




@endsection