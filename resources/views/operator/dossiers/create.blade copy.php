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
                            <p class="mb-0 opacity-90">Assistant de création guidée en <span id="totalSteps">8</span> étapes</p>
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
                        <small class="text-muted">Étape <span id="currentStepNumber">1</span> sur <span id="totalStepsDisplay">8</span></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success progress-bar-striped" role="progressbar" style="width: 12.5%" id="globalProgress"></div>
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
                            <i class="fas fa-building step-icon"></i>
                            <small class="d-block mt-1">Informations</small>
                        </div>
                        <div class="col step-indicator" data-step="4">
                            <i class="fas fa-map-marker-alt step-icon"></i>
                            <small class="d-block mt-1">Coordonnées</small>
                        </div>
                        <div class="col step-indicator" data-step="5">
                            <i class="fas fa-user-tie step-icon"></i>
                            <small class="d-block mt-1">Responsables</small>
                        </div>
                        <div class="col step-indicator" data-step="6">
                            <i class="fas fa-paperclip step-icon"></i>
                            <small class="d-block mt-1">Pièces</small>
                        </div>
                        <div class="col step-indicator" data-step="7">
                            <i class="fas fa-eye step-icon"></i>
                            <small class="d-block mt-1">Récapitulatif</small>
                        </div>
                        <div class="col step-indicator" data-step="8">
                            <i class="fas fa-paper-plane step-icon"></i>
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
                    <form id="organisationForm">
                        
                        <!-- Étape 1: Choix du Type d'Organisation -->
                        <div class="step-content" id="step1" style="display: block;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                                    <i class="fas fa-list-ul fa-3x text-white"></i>
                                </div>
                                <h3 class="text-success">Choisissez le type d'organisation</h3>
                                <p class="text-muted">Sélectionnez le statut juridique qui correspond à vos objectifs</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 shadow-sm org-type-card" data-type="association">
                                                <div class="card-body text-center p-4">
                                                    <div class="org-icon mb-3" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                                                        <i class="fas fa-users fa-3x text-white"></i>
                                                    </div>
                                                    <h5 class="card-title">Association</h5>
                                                    <p class="card-text">Organisation à but non lucratif regroupant des personnes autour d'un objectif commun d'intérêt général.</p>
                                                    <div class="features mb-3">
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Minimum 3 membres</small>
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> But non lucratif</small>
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Assemblée générale</small>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_organisation" value="association" id="typeAssociation">
                                                        <label class="form-check-label fw-bold" for="typeAssociation">Choisir ce type</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 shadow-sm org-type-card" data-type="fondation">
                                                <div class="card-body text-center p-4">
                                                    <div class="org-icon mb-3" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                                                        <i class="fas fa-heart fa-3x text-dark"></i>
                                                    </div>
                                                    <h5 class="card-title">Fondation</h5>
                                                    <p class="card-text">Organisation philanthropique dédiée à l'intérêt général avec un patrimoine affecté.</p>
                                                    <div class="features mb-3">
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Patrimoine dédié</small>
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Mission philanthropique</small>
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Conseil d'administration</small>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_organisation" value="fondation" id="typeFondation">
                                                        <label class="form-check-label fw-bold" for="typeFondation">Choisir ce type</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 shadow-sm org-type-card" data-type="ong">
                                                <div class="card-body text-center p-4">
                                                    <div class="org-icon mb-3" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                                                        <i class="fas fa-globe fa-3x text-white"></i>
                                                    </div>
                                                    <h5 class="card-title">ONG</h5>
                                                    <p class="card-text">Organisation Non Gouvernementale d'action humanitaire, sociale ou environnementale.</p>
                                                    <div class="features mb-3">
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Action humanitaire</small>
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Indépendance politique</small>
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Financement externe</small>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_organisation" value="ong" id="typeOng">
                                                        <label class="form-check-label fw-bold" for="typeOng">Choisir ce type</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100 border-0 shadow-sm org-type-card" data-type="cooperative">
                                                <div class="card-body text-center p-4">
                                                    <div class="org-icon mb-3" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                                                        <i class="fas fa-handshake fa-3x text-white"></i>
                                                    </div>
                                                    <h5 class="card-title">Coopérative</h5>
                                                    <p class="card-text">Entreprise collective basée sur la solidarité et la démocratie participative.</p>
                                                    <div class="features mb-3">
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Propriété collective</small>
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Gestion démocratique</small>
                                                        <small class="d-block text-muted"><i class="fas fa-check text-success me-1"></i> Partage des bénéfices</small>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="type_organisation" value="cooperative" id="typeCooperative">
                                                        <label class="form-check-label fw-bold" for="typeCooperative">Choisir ce type</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 2: Guide des Recommandations -->
                        <div class="step-content" id="step2" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                                    <i class="fas fa-book-open fa-3x text-dark"></i>
                                </div>
                                <h3 class="text-warning">Guide pour <span id="selectedTypeTitle">votre organisation</span></h3>
                                <p class="text-muted">Recommandations spécifiques et prérequis</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div id="guideContent">
                                        <!-- Le contenu sera généré dynamiquement selon le type choisi -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 3: Informations Générales -->
                        <div class="step-content" id="step3" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                                    <i class="fas fa-building fa-3x text-white"></i>
                                </div>
                                <h3 class="text-primary">Informations générales</h3>
                                <p class="text-muted">Décrivez votre organisation et ses objectifs</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label for="nom_organisation" class="form-label fw-bold fs-5">
                                            Nom de l'organisation <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="nom_organisation" name="nom_organisation" required>
                                        <div class="form-text">Le nom complet et officiel de votre organisation</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label for="sigle" class="form-label fw-bold">Sigle / Acronyme</label>
                                            <input type="text" class="form-control" id="sigle" name="sigle">
                                            <div class="form-text">Abréviation de votre organisation (ex: UNICEF)</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label for="secteur_activite" class="form-label fw-bold">
                                                Secteur d'activité <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="secteur_activite" name="secteur_activite" required>
                                                <option value="">Choisissez un secteur</option>
                                                <option value="education">Éducation et Formation</option>
                                                <option value="sante">Santé et Bien-être</option>
                                                <option value="environnement">Environnement et Développement Durable</option>
                                                <option value="culture">Culture et Arts</option>
                                                <option value="sport">Sport et Loisirs</option>
                                                <option value="social">Action Sociale et Humanitaire</option>
                                                <option value="economie">Développement Économique</option>
                                                <option value="droits_humains">Droits de l'Homme et Justice</option>
                                                <option value="jeunesse">Jeunesse et Enfance</option>
                                                <option value="femme">Promotion de la Femme</option>
                                                <option value="autre">Autre secteur</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-bold">
                                            Description de l'organisation <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" id="description" name="description" rows="5" required placeholder="Décrivez les objectifs, missions et activités principales de votre organisation..."></textarea>
                                        <div class="d-flex justify-content-between">
                                            <div class="form-text">Décrivez brièvement les objectifs et activités de votre organisation (minimum 50 caractères)</div>
                                            <small class="text-muted"><span id="charCount">0</span>/1000 caractères</small>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="objectifs" class="form-label fw-bold">
                                            Objectifs spécifiques <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" id="objectifs" name="objectifs" rows="4" required placeholder="Listez les objectifs principaux que votre organisation souhaite atteindre..."></textarea>
                                        <div class="form-text">Énumérez 3 à 5 objectifs principaux de votre organisation</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 4: Coordonnées et Contact -->
                        <div class="step-content" id="step4" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                                    <i class="fas fa-map-marker-alt fa-3x text-white"></i>
                                </div>
                                <h3 class="text-danger">Coordonnées et contact</h3>
                                <p class="text-muted">Renseignez l'adresse et les moyens de contact</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label for="adresse" class="form-label fw-bold">
                                            Adresse complète du siège social <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" id="adresse" name="adresse" rows="3" required placeholder="Numéro, rue, quartier, arrondissement..."></textarea>
                                        <div class="form-text">L'adresse exacte où est domiciliée votre organisation</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label for="ville" class="form-label fw-bold">
                                                Ville <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="ville" name="ville" required>
                                                <option value="">Choisissez une ville</option>
                                                <option value="libreville">Libreville</option>
                                                <option value="port_gentil">Port-Gentil</option>
                                                <option value="franceville">Franceville</option>
                                                <option value="oyem">Oyem</option>
                                                <option value="moanda">Moanda</option>
                                                <option value="lambarene">Lambaréné</option>
                                                <option value="tchibanga">Tchibanga</option>
                                                <option value="koulamoutou">Koulamoutou</option>
                                                <option value="bitam">Bitam</option>
                                                <option value="gamba">Gamba</option>
                                                <option value="autre">Autre ville</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label for="province" class="form-label fw-bold">
                                                Province <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="province" name="province" required>
                                                <option value="">Choisissez une province</option>
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
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label for="telephone" class="form-label fw-bold">
                                                Téléphone principal <span class="text-danger">*</span>
                                            </label>
                                            <input type="tel" class="form-control" id="telephone" name="telephone" required placeholder="+241 XX XX XX XX">
                                            <div class="form-text">Numéro de téléphone au format gabonais</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label for="telephone_2" class="form-label fw-bold">Téléphone secondaire</label>
                                            <input type="tel" class="form-control" id="telephone_2" name="telephone_2" placeholder="+241 XX XX XX XX">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label for="email" class="form-label fw-bold">
                                                Email principal <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email" required placeholder="contact@organisation.ga">
                                            <div class="form-text">Adresse email principale de contact</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label for="email_2" class="form-label fw-bold">Email secondaire</label>
                                            <input type="email" class="form-control" id="email_2" name="email_2" placeholder="info@organisation.ga">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label for="site_web" class="form-label fw-bold">Site Web</label>
                                            <input type="url" class="form-control" id="site_web" name="site_web" placeholder="https://www.organisation.ga">
                                            <div class="form-text">Site internet de votre organisation</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label for="reseaux_sociaux" class="form-label fw-bold">Réseaux Sociaux</label>
                                            <input type="text" class="form-control" id="reseaux_sociaux" name="reseaux_sociaux" placeholder="Facebook, Twitter, LinkedIn...">
                                            <div class="form-text">Comptes sur les réseaux sociaux</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 5: Responsables de l'Organisation -->
                        <div class="step-content" id="step5" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                                    <i class="fas fa-user-tie fa-3x text-white"></i>
                                </div>
                                <h3 class="text-primary">Responsables de l'organisation</h3>
                                <p class="text-muted">Ajoutez les dirigeants et personnes de contact (minimum 3)</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div id="responsables-container">
                                        <div class="responsable-item border rounded p-4 mb-4" style="background: rgba(0, 158, 63, 0.05);">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0 text-success">
                                                    <i class="fas fa-star me-2"></i>
                                                    Responsable Principal
                                                </h5>
                                                <span class="badge bg-success">Obligatoire</span>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Nom et prénoms <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="responsables[0][nom]" required placeholder="Nom complet">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Fonction <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="responsables[0][fonction]" required>
                                                        <option value="">Choisissez une fonction</option>
                                                        <option value="president">Président(e)</option>
                                                        <option value="vice_president">Vice-Président(e)</option>
                                                        <option value="secretaire_general">Secrétaire Général(e)</option>
                                                        <option value="directeur_executif">Directeur(trice) Exécutif(ve)</option>
                                                        <option value="coordinateur">Coordinateur(trice)</option>
                                                        <option value="autre">Autre fonction</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" name="responsables[0][email]" required placeholder="email@exemple.ga">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Téléphone <span class="text-danger">*</span></label>
                                                    <input type="tel" class="form-control" name="responsables[0][telephone]" required placeholder="+241 XX XX XX XX">
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Adresse personnelle</label>
                                                    <textarea class="form-control" name="responsables[0][adresse]" rows="2" placeholder="Adresse de résidence du responsable"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mb-4">
                                        <button type="button" class="btn btn-outline-success btn-lg" id="ajouterResponsable">
                                            <i class="fas fa-plus me-2"></i>Ajouter un responsable
                                        </button>
                                        <div class="form-text mt-2">Au minimum 3 responsables sont requis pour valider cette étape</div>
                                    </div>

                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Information importante :
                                        </h6>
                                        <p class="mb-0">Les responsables déclarés ici seront mentionnés dans les documents officiels. Assurez-vous que toutes les informations sont correctes et que ces personnes ont donné leur accord pour figurer dans le dossier.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 6: Pièces Jointes -->
                        <div class="step-content" id="step6" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                                    <i class="fas fa-paperclip fa-3x text-dark"></i>
                                </div>
                                <h3 class="text-warning">Pièces jointes</h3>
                                <p class="text-muted">Téléchargez les documents requis pour votre dossier</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="alert alert-info mb-4">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Documents requis :
                                        </h6>
                                        <p class="mb-0">Tous les documents doivent être au format PDF, JPG ou PNG et ne pas dépasser 5 MB chacun.</p>
                                    </div>

                                    <!-- Documents obligatoires -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                Documents Obligatoires
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">
                                                        Statuts de l'organisation <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="file" class="form-control" name="statuts" accept=".pdf,.jpg,.png" required>
                                                    <div class="form-text">Document officiel définissant les règles de fonctionnement</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">
                                                        Procès-verbal de l'AG constitutive <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="file" class="form-control" name="pv_constitutive" accept=".pdf,.jpg,.png" required>
                                                    <div class="form-text">PV de l'assemblée générale constitutive</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">
                                                        Liste des membres fondateurs <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="file" class="form-control" name="liste_membres" accept=".pdf,.jpg,.png" required>
                                                    <div class="form-text">Liste avec noms, prénoms et signatures</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">
                                                        Justificatif de domicile du siège <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="file" class="form-control" name="justificatif_siege" accept=".pdf,.jpg,.png" required>
                                                    <div class="form-text">Facture d'eau, électricité ou bail</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pièces d'identité des responsables -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-id-card me-2"></i>
                                                Pièces d'identité des responsables
                                            </h6>
                                        </div>
                                        <div class="card-body" id="pieces-identite-container">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">
                                                        CNI du responsable principal <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="file" class="form-control" name="cni_responsable_0" accept=".pdf,.jpg,.png" required>
                                                    <div class="form-text">Carte nationale d'identité recto-verso</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Documents optionnels -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-plus-circle me-2"></i>
                                                Documents Optionnels
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Demande d'agrément</label>
                                                    <input type="file" class="form-control" name="demande_agrement" accept=".pdf,.jpg,.png">
                                                    <div class="form-text">Si votre secteur d'activité l'exige</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Logo de l'organisation</label>
                                                    <input type="file" class="form-control" name="logo" accept=".pdf,.jpg,.png">
                                                    <div class="form-text">Logo officiel (format image recommandé)</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Plan d'activités</label>
                                                    <input type="file" class="form-control" name="plan_activites" accept=".pdf,.jpg,.png">
                                                    <div class="form-text">Programme d'activités prévisionnelles</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Budget prévisionnel</label>
                                                    <input type="file" class="form-control" name="budget" accept=".pdf,.jpg,.png">
                                                    <div class="form-text">Budget de fonctionnement prévisionnel</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-success mt-4">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-lightbulb me-2"></i>
                                            Conseils pour l'upload :
                                        </h6>
                                        <ul class="mb-0">
                                            <li>Scannez vos documents en haute qualité (300 DPI minimum)</li>
                                            <li>Assurez-vous que le texte est lisible</li>
                                            <li>Respectez la taille maximale de 5 MB par fichier</li>
                                            <li>Nommez vos fichiers de manière claire</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 7: Récapitulatif -->
                        <div class="step-content" id="step7" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                                    <i class="fas fa-eye fa-3x text-white"></i>
                                </div>
                                <h3 class="text-primary">Récapitulatif de votre dossier</h3>
                                <p class="text-muted">Vérifiez toutes les informations avant la soumission</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <!-- Récapitulatif -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="fas fa-clipboard-list me-2"></i>
                                                Récapitulatif des informations
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="recap-content">
                                                <!-- Le contenu sera généré dynamiquement -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Documents uploadés -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-warning text-dark">
                                            <h5 class="mb-0">
                                                <i class="fas fa-file-alt me-2"></i>
                                                Documents joints
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="documents-recap">
                                                <!-- Le contenu sera généré dynamiquement -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="text-center">
                                        <button type="button" class="btn btn-outline-secondary btn-lg me-3" onclick="changeStep(-1)">
                                            <i class="fas fa-edit me-2"></i>Modifier les informations
                                        </button>
                                        <button type="button" class="btn btn-success btn-lg" onclick="changeStep(1)">
                                            Tout est correct <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 8: Soumission -->
                        <div class="step-content" id="step8" style="display: none;">
                            <div class="text-center mb-4">
                                <div class="step-icon-large mx-auto mb-3" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                                    <i class="fas fa-paper-plane fa-3x text-white"></i>
                                </div>
                                <h3 class="text-success">Soumission du dossier</h3>
                                <p class="text-muted">Dernière étape : soumission ou sauvegarde</p>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <!-- Déclarations -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-warning text-dark">
                                            <h5 class="mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Déclarations et engagement
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="declaration1" required>
                                                <label class="form-check-label" for="declaration1">
                                                    <strong>Je certifie que toutes les informations fournies sont exactes et complètes.</strong>
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="declaration2" required>
                                                <label class="form-check-label" for="declaration2">
                                                    <strong>Je m'engage à respecter la législation gabonaise en vigueur.</strong>
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="declaration3" required>
                                                <label class="form-check-label" for="declaration3">
                                                    <strong>J'ai l'autorisation de tous les responsables mentionnés pour les inclure dans ce dossier.</strong>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="declaration4" required>
                                                <label class="form-check-label" for="declaration4">
                                                    <strong>J'accepte que ces informations soient traitées dans le cadre de la demande d'enregistrement.</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions finales -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-rocket me-2"></i>
                                                Actions disponibles
                                            </h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="p-4 border rounded" style="background: rgba(0, 158, 63, 0.05);">
                                                        <h6 class="text-success">Soumettre définitivement</h6>
                                                        <p class="small text-muted mb-3">Votre dossier sera transmis pour traitement officiel. Vous recevrez un accusé de réception.</p>
                                                        <button type="button" class="btn btn-success btn-lg" id="submitFinalBtn">
                                                            <i class="fas fa-paper-plane me-2"></i>
                                                            Soumettre le dossier
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="p-4 border rounded" style="background: rgba(255, 193, 7, 0.05);">
                                                        <h6 class="text-warning">Sauvegarder en brouillon</h6>
                                                        <p class="small text-muted mb-3">Conservez votre progression. Vous pourrez modifier et soumettre plus tard.</p>
                                                        <button type="button" class="btn btn-warning btn-lg" id="saveDraftBtn">
                                                            <i class="fas fa-save me-2"></i>
                                                            Enregistrer en brouillon
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Prochaines étapes -->
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-road me-2"></i>
                                            Prochaines étapes après soumission :
                                        </h6>
                                        <ol class="mb-0">
                                            <li>Accusé de réception automatique par email</li>
                                            <li>Examen du dossier par les services compétents (5-10 jours ouvrés)</li>
                                            <li>Demande de pièces complémentaires si nécessaire</li>
                                            <li>Délivrance du récépissé de déclaration (sous 30 jours)</li>
                                            <li>Publication au Journal Officiel</li>
                                            <li>Début des activités légales de votre organisation</li>
                                        </ol>
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
                                Quel type d'organisation choisir ?
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Association :</strong> Pour des activités sociales, culturelles, sportives ou éducatives sans but lucratif.</p>
                                        <p><strong>Fondation :</strong> Pour des œuvres philanthropiques avec un patrimoine dédié.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>ONG :</strong> Pour l'action humanitaire, sociale ou environnementale.</p>
                                        <p><strong>Coopérative :</strong> Pour des activités économiques en commun.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="help2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                Documents à préparer
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Documents obligatoires :</h6>
                                        <ul>
                                            <li>Statuts de l'organisation (3 exemplaires)</li>
                                            <li>Procès-verbal de l'assemblée générale constitutive</li>
                                            <li>Liste des membres fondateurs</li>
                                            <li>Copies des CNI des responsables</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Documents optionnels :</h6>
                                        <ul>
                                            <li>Demande d'agrément (si requis)</li>
                                            <li>Logo de l'organisation</li>
                                            <li>Plan d'activités</li>
                                            <li>Budget prévisionnel</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="help3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                Délais et procédures
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <p>Le traitement du dossier prend généralement 30 jours ouvrés. Un récépissé vous sera délivré pour commencer vos activités.</p>
                                <h6>Étapes après soumission :</h6>
                                <ol>
                                    <li>Accusé de réception (immédiat)</li>
                                    <li>Examen du dossier (5-10 jours)</li>
                                    <li>Demande de pièces complémentaires si nécessaire</li>
                                    <li>Délivrance du récépissé (sous 30 jours)</li>
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
        flex-basis: 25%;
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

/* Icônes d'étapes grandes */
.step-icon-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* Cartes de type d'organisation */
.org-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.org-type-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.org-type-card.active {
    border: 3px solid #009e3f !important;
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0, 158, 63, 0.2) !important;
}

.org-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

/* Formulaire */
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

/* Items responsables */
.responsable-item {
    position: relative;
    transition: all 0.3s ease;
}

.responsable-item .btn-remove {
    position: absolute;
    top: 15px;
    right: 15px;
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

/* Progress bar */
.progress-bar {
    transition: width 0.5s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .step-icon-large {
        width: 80px;
        height: 80px;
    }
    
    .step-icon-large i {
        font-size: 2rem !important;
    }
    
    .org-icon {
        width: 80px;
        height: 80px;
    }
    
    .org-icon i {
        font-size: 2rem !important;
    }
}

/* Character counter */
#charCount {
    font-weight: bold;
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

/* Features list */
.features small {
    line-height: 1.6;
}

/* File upload */
input[type="file"] {
    padding: 8px;
}

input[type="file"]:focus {
    border-color: #009e3f;
    box-shadow: 0 0 0 0.2rem rgba(0, 158, 63, 0.25);
}

/* Upload progress */
.upload-progress {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 5px;
}

.upload-progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);
    width: 0%;
    transition: width 0.3s ease;
}

/* Recap styles */
.recap-section {
    border-left: 4px solid #009e3f;
    padding-left: 15px;
    margin-bottom: 20px;
}

.recap-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.recap-item:last-child {
    border-bottom: none;
}

.recap-label {
    font-weight: bold;
    color: #495057;
}

.recap-value {
    color: #6c757d;
    text-align: right;
    max-width: 60%;
    word-wrap: break-word;
}

/* Document list */
.document-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.document-icon {
    width: 40px;
    height: 40px;
    background: #009e3f;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.document-info {
    flex: 1;
}

.document-name {
    font-weight: bold;
    color: #495057;
}

.document-size {
    font-size: 0.875rem;
    color: #6c757d;
}

.document-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: bold;
}

.document-status.uploaded {
    background: #d4edda;
    color: #155724;
}

.document-status.missing {
    background: #f8d7da;
    color: #721c24;
}
</style>

<script>
let currentStep = 1;
const totalSteps = 8;
let responsableCount = 1;
let selectedOrgType = '';

// Guides spécifiques par type d'organisation
const guidesData = {
    association: {
        title: "Guide pour créer une Association",
        content: `
            <div class="alert alert-success">
                <h6 class="alert-heading"><i class="fas fa-users me-2"></i>Association - Recommandations spécifiques</h6>
                <p class="mb-0">Une association est un regroupement de personnes physiques ou morales qui mettent en commun leurs connaissances ou activité dans un but autre que de partager des bénéfices.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><i class="fas fa-check-circle me-2"></i>Caractéristiques principales :</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>But non lucratif obligatoire</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Minimum 3 membres fondateurs</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Assemblée générale annuelle</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Conseil d'administration</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Transparence financière</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Points d'attention :</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Pas de distribution de bénéfices</li>
                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Activités commerciales limitées</li>
                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Comptabilité obligatoire</li>
                        <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Rapport d'activité annuel</li>
                    </ul>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-lightbulb me-2"></i>Conseil pour votre association :</h6>
                <p class="mb-0">Définissez clairement vos objectifs sociaux, culturels, sportifs ou éducatifs. Préparez des statuts détaillés et assurez-vous d'avoir au moins 3 membres fondateurs engagés dans votre projet.</p>
            </div>`
    },
    fondation: {
        title: "Guide pour créer une Fondation",
        content: `
            <div class="alert alert-warning">
                <h6 class="alert-heading"><i class="fas fa-heart me-2"></i>Fondation - Recommandations spécifiques</h6>
                <p class="mb-0">Une fondation est un patrimoine affecté à une œuvre d'intérêt général à but non lucratif. Elle nécessite des moyens financiers conséquents.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><i class="fas fa-check-circle me-2"></i>Caractéristiques principales :</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Patrimoine initial conséquent</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Mission philanthropique</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Conseil d'administration</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Durée de vie illimitée</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Reconnaissance d'utilité publique possible</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Exigences spéciales :</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-euro-sign text-info me-2"></i>Capital minimum substantiel</li>
                        <li class="mb-2"><i class="fas fa-file-contract text-info me-2"></i>Acte notarié obligatoire</li>
                        <li class="mb-2"><i class="fas fa-audit text-info me-2"></i>Audit comptable annuel</li>
                        <li class="mb-2"><i class="fas fa-gavel text-info me-2"></i>Contrôle administratif renforcé</li>
                    </ul>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-lightbulb me-2"></i>Conseil pour votre fondation :</h6>
                <p class="mb-0">Assurez-vous de disposer d'un patrimoine suffisant et durable. La fondation nécessite une vision à long terme et des ressources financières stables pour accomplir sa mission philanthropique.</p>
            </div>`
    },
    ong: {
        title: "Guide pour créer une ONG",
        content: `
            <div class="alert alert-primary">
                <h6 class="alert-heading"><i class="fas fa-globe me-2"></i>ONG - Recommandations spécifiques</h6>
                <p class="mb-0">Une ONG est une organisation non gouvernementale qui intervient dans l'action humanitaire, sociale, environnementale ou de développement.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><i class="fas fa-check-circle me-2"></i>Caractéristiques principales :</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Indépendance politique</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Action humanitaire ou sociale</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Financement externe possible</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Coopération internationale</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Transparence obligatoire</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Secteurs d'intervention :</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-heart text-danger me-2"></i>Action humanitaire</li>
                        <li class="mb-2"><i class="fas fa-leaf text-success me-2"></i>Environnement</li>
                        <li class="mb-2"><i class="fas fa-graduation-cap text-primary me-2"></i>Éducation</li>
                        <li class="mb-2"><i class="fas fa-hand-holding-heart text-warning me-2"></i>Développement social</li>
                    </ul>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-lightbulb me-2"></i>Conseil pour votre ONG :</h6>
                <p class="mb-0">Définissez clairement votre mission humanitaire ou sociale. Préparez un plan d'action détaillé et identifiez vos sources de financement potentielles (bailleurs de fonds, partenaires internationaux).</p>
            </div>`
    },
    cooperative: {
        title: "Guide pour créer une Coopérative",
        content: `
            <div class="alert alert-danger">
                <h6 class="alert-heading"><i class="fas fa-handshake me-2"></i>Coopérative - Recommandations spécifiques</h6>
                <p class="mb-0">Une coopérative est une entreprise collective appartenant à ses membres, qui la contrôlent démocratiquement et partagent équitablement les bénéfices.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><i class="fas fa-check-circle me-2"></i>Principes coopératifs :</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Propriété collective</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Gestion démocratique</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Participation des membres</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Ristournes équitables</li>
                        <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Éducation coopérative</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Types de coopératives :</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-shopping-cart text-success me-2"></i>Coopérative de consommation</li>
                        <li class="mb-2"><i class="fas fa-industry text-primary me-2"></i>Coopérative de production</li>
                        <li class="mb-2"><i class="fas fa-home text-warning me-2"></i>Coopérative d'habitat</li>
                        <li class="mb-2"><i class="fas fa-seedling text-success me-2"></i>Coopérative agricole</li>
                    </ul>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-lightbulb me-2"></i>Conseil pour votre coopérative :</h6>
                <p class="mb-0">Assurez-vous que tous les membres partagent les mêmes valeurs coopératives. Définissez clairement les modalités de participation, de prise de décision et de partage des bénéfices.</p>
            </div>`
    }
};

document.addEventListener('DOMContentLoaded', function() {
    updateStepDisplay();
    
    // Gestion des types d'organisation
    document.querySelectorAll('.org-type-card').forEach(card => {
        card.addEventListener('click', function() {
            // Retirer la classe active de toutes les cartes
            document.querySelectorAll('.org-type-card').forEach(c => c.classList.remove('active'));
            
            // Ajouter la classe active à la carte cliquée
            this.classList.add('active');
            
            // Cocher le radio button correspondant
            const radioInput = this.querySelector('input[type="radio"]');
            radioInput.checked = true;
            selectedOrgType = radioInput.value;
            
            // Mettre à jour les labels
            document.querySelectorAll('.org-type-card .form-check-label').forEach(label => {
                label.textContent = 'Choisir ce type';
                label.classList.remove('fw-bold', 'text-success');
            });
            
            const activeLabel = this.querySelector('.form-check-label');
            activeLabel.textContent = 'Type sélectionné';
            activeLabel.classList.add('fw-bold', 'text-success');
        });
    });
    
    // Compteur de caractères
    const descriptionField = document.getElementById('description');
    const charCountElement = document.getElementById('charCount');
    
    if (descriptionField && charCountElement) {
        descriptionField.addEventListener('input', function() {
            const count = this.value.length;
            charCountElement.textContent = count;
            
            if (count > 1000) {
                charCountElement.style.color = '#dc3545';
                this.value = this.value.substring(0, 1000);
                charCountElement.textContent = 1000;
            } else if (count > 800) {
                charCountElement.style.color = '#ffc107';
            } else {
                charCountElement.style.color = '#6c757d';
            }
        });
    }
    
    // Ajouter un responsable
    document.getElementById('ajouterResponsable').addEventListener('click', function() {
        const container = document.getElementById('responsables-container');
        const newResponsable = document.createElement('div');
        newResponsable.className = 'responsable-item border rounded p-4 mb-4';
        newResponsable.style.background = 'rgba(255, 205, 0, 0.05)';
        
        newResponsable.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-secondary">
                    <i class="fas fa-user me-2"></i>
                    Responsable ${responsableCount + 1}
                </h6>
                <button type="button" class="btn btn-outline-danger btn-sm btn-remove" onclick="removeResponsable(this)">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom et prénoms</label>
                    <input type="text" class="form-control" name="responsables[${responsableCount}][nom]" placeholder="Nom complet">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fonction</label>
                    <select class="form-select" name="responsables[${responsableCount}][fonction]">
                        <option value="">Choisissez une fonction</option>
                        <option value="president">Président(e)</option>
                        <option value="vice_president">Vice-Président(e)</option>
                        <option value="secretaire_general">Secrétaire Général(e)</option>
                        <option value="tresorier">Trésorier(ère)</option>
                        <option value="directeur_executif">Directeur(trice) Exécutif(ve)</option>
                        <option value="coordinateur">Coordinateur(trice)</option>
                        <option value="autre">Autre fonction</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control" name="responsables[${responsableCount}][email]" placeholder="email@exemple.ga">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Téléphone</label>
                    <input type="tel" class="form-control" name="responsables[${responsableCount}][telephone]" placeholder="+241 XX XX XX XX">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Adresse personnelle</label>
                    <textarea class="form-control" name="responsables[${responsableCount}][adresse]" rows="2" placeholder="Adresse de résidence du responsable"></textarea>
                </div>
            </div>
        `;
        
        container.appendChild(newResponsable);
        responsableCount++;
        
        // Ajouter le champ CNI correspondant
        addCNIField(responsableCount - 1);
        
        // Animation d'entrée
        newResponsable.style.opacity = '0';
        newResponsable.style.transform = 'translateY(20px)';
        setTimeout(() => {
            newResponsable.style.transition = 'all 0.5s ease';
            newResponsable.style.opacity = '1';
            newResponsable.style.transform = 'translateY(0)';
        }, 10);
    });
    
    // Validation en temps réel
    document.addEventListener('input', validateCurrentStep);
    document.addEventListener('change', validateCurrentStep);
    
    // Auto-formatage du téléphone
    document.addEventListener('input', function(e) {
        if (e.target.type === 'tel') {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('241')) {
                value = '+' + value;
            } else if (!value.startsWith('+241') && value.length > 0) {
                value = '+241' + value;
            }
            
            // Formatage avec espaces
            if (value.length > 4) {
                value = value.substring(0, 4) + ' ' + value.substring(4);
            }
            if (value.length > 7) {
                value = value.substring(0, 7) + ' ' + value.substring(7);
            }
            if (value.length > 10) {
                value = value.substring(0, 10) + ' ' + value.substring(10);
            }
            if (value.length > 13) {
                value = value.substring(0, 13) + ' ' + value.substring(13);
            }
            
            e.target.value = value.substring(0, 16);
        }
    });

    // Gestion des boutons finaux
    document.getElementById('submitFinalBtn').addEventListener('click', function() {
        if (validateAllDeclarations()) {
            submitForm('final');
        }
    });

    document.getElementById('saveDraftBtn').addEventListener('click', function() {
        submitForm('draft');
    });
});

function addCNIField(index) {
    const container = document.getElementById('pieces-identite-container').querySelector('.row');
    const newCNIField = document.createElement('div');
    newCNIField.className = 'col-md-6 mb-3';
    newCNIField.innerHTML = `
        <label class="form-label fw-bold">
            CNI du responsable ${index + 1}
        </label>
        <input type="file" class="form-control" name="cni_responsable_${index}" accept=".pdf,.jpg,.png">
        <div class="form-text">Carte nationale d'identité recto-verso</div>
    `;
    container.appendChild(newCNIField);
}

function changeStep(direction) {
    if (direction === 1) {
        // Valider l'étape actuelle avant de continuer
        if (!validateCurrentStep()) {
            return;
        }
        
        if (currentStep < totalSteps) {
            currentStep++;
            
            // Actions spéciales selon l'étape
            if (currentStep === 2) {
                loadGuideContent();
            } else if (currentStep === 7) {
                generateRecap();
            }
        }
    } else {
        if (currentStep > 1) {
            currentStep--;
        }
    }
    
    updateStepDisplay();
}

function updateStepDisplay() {
    // Masquer toutes les étapes
    document.querySelectorAll('.step-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Afficher l'étape actuelle
    document.getElementById('step' + currentStep).style.display = 'block';
    
    // Mettre à jour la progression
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('globalProgress').style.width = progress + '%';
    document.getElementById('currentStepNumber').textContent = currentStep;
    
    // Mettre à jour les indicateurs d'étapes
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const stepNumber = index + 1;
        indicator.classList.remove('active', 'completed');
        
        if (stepNumber === currentStep) {
            indicator.classList.add('active');
        } else if (stepNumber < currentStep) {
            indicator.classList.add('completed');
        }
    });
    
    // Gestion des boutons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
    
    if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
    } else {
        nextBtn.style.display = 'inline-block';
    }
}

function loadGuideContent() {
    const guideContainer = document.getElementById('guideContent');
    const titleElement = document.getElementById('selectedTypeTitle');
    
    if (selectedOrgType && guidesData[selectedOrgType]) {
        titleElement.textContent = guidesData[selectedOrgType].title.replace('Guide pour créer une ', '');
        guideContainer.innerHTML = guidesData[selectedOrgType].content;
    } else {
        guideContainer.innerHTML = `
            <div class="alert alert-warning">
                <h6>Aucun type d'organisation sélectionné</h6>
                <p class="mb-0">Veuillez retourner à l'étape précédente pour choisir un type d'organisation.</p>
            </div>
        `;
    }
}

function validateCurrentStep() {
    let isValid = true;
    const currentStepContent = document.getElementById('step' + currentStep);
    
    switch(currentStep) {
        case 1:
            // Validation du type d'organisation
            const typeSelected = document.querySelector('input[name="type_organisation"]:checked');
            if (!typeSelected) {
                alert('Veuillez choisir un type d\'organisation.');
                return false;
            }
            selectedOrgType = typeSelected.value;
            return true;
            
        case 2:
            // Étape guide - toujours valide
            return true;
            
        case 3:
            // Validation des informations générales
            const requiredFields3 = currentStepContent.querySelectorAll('input[required], select[required], textarea[required]');
            requiredFields3.forEach(field => {
                if (field.value.trim() === '') {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });
            
            // Vérifier la longueur de la description
            const description = document.getElementById('description');
            if (description.value.length < 50) {
                description.classList.add('is-invalid');
                isValid = false;
                alert('La description doit contenir au moins 50 caractères.');
            }
            
            if (!isValid) {
                alert('Veuillez remplir tous les champs obligatoires des informations générales.');
            }
            return isValid;
            
        case 4:
            // Validation des coordonnées
            const requiredFields4 = currentStepContent.querySelectorAll('input[required], select[required], textarea[required]');
            requiredFields4.forEach(field => {
                if (field.value.trim() === '') {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });
            
            // Validation email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validation téléphone
            const telephone = document.getElementById('telephone');
            const phoneRegex = /^\+241\s?\d{2}\s?\d{2}\s?\d{2}\s?\d{2}$/;
            if (!phoneRegex.test(telephone.value)) {
                telephone.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                alert('Veuillez remplir correctement tous les champs obligatoires des coordonnées.');
            }
            return isValid;
            
        case 5:
            // Validation des responsables (minimum 3)
            const responsables = document.querySelectorAll('.responsable-item');
            let responsablesValides = 0;
            
            responsables.forEach(item => {
                const requiredInputs = item.querySelectorAll('input[required], select[required]');
                let responsableValide = true;
                
                requiredInputs.forEach(input => {
                    if (input.value.trim() === '') {
                        input.classList.add('is-invalid');
                        responsableValide = false;
                    } else {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    }
                });
                
                if (responsableValide) {
                    responsablesValides++;
                }
            });
            
            if (responsablesValides < 3) {
                alert('Vous devez avoir au minimum 3 responsables avec toutes les informations obligatoires remplies.');
                return false;
            }
            
            return true;
            
        case 6:
            // Validation des pièces jointes obligatoires
            const requiredFiles = currentStepContent.querySelectorAll('input[type="file"][required]');
            requiredFiles.forEach(fileInput => {
                if (!fileInput.files.length) {
                    fileInput.classList.add('is-invalid');
                    isValid = false;
                } else {
                    fileInput.classList.remove('is-invalid');
                    fileInput.classList.add('is-valid');
                }
            });
            
            if (!isValid) {
                alert('Veuillez télécharger tous les documents obligatoires.');
            }
            return isValid;
            
        case 7:
            // Étape récapitulatif - toujours valide
            return true;
            
        case 8:
            // Validation des déclarations
            return validateAllDeclarations();
            
        default:
            return true;
    }
}

function validateAllDeclarations() {
    const declarations = document.querySelectorAll('#step8 input[type="checkbox"][required]');
    let allChecked = true;
    
    declarations.forEach(checkbox => {
        if (!checkbox.checked) {
            allChecked = false;
            checkbox.classList.add('is-invalid');
        } else {
            checkbox.classList.remove('is-invalid');
        }
    });
    
    if (!allChecked) {
        alert('Veuillez cocher toutes les déclarations obligatoires.');
    }
    
    return allChecked;
}

function generateRecap() {
    const recapContainer = document.getElementById('recap-content');
    const documentsContainer = document.getElementById('documents-recap');
    
    // Récapitulatif des informations
    const formData = new FormData(document.getElementById('organisationForm'));
    let recapHTML = '';
    
    // Type d'organisation
    recapHTML += `
        <div class="recap-section">
            <h6 class="text-primary mb-3"><i class="fas fa-list-ul me-2"></i>Type d'organisation</h6>
            <div class="recap-item">
                <span class="recap-label">Type sélectionné :</span>
                <span class="recap-value">${getTypeLabel(selectedOrgType)}</span>
            </div>
        </div>
    `;
    
    // Informations générales
    recapHTML += `
        <div class="recap-section">
            <h6 class="text-primary mb-3"><i class="fas fa-building me-2"></i>Informations générales</h6>
            <div class="recap-item">
                <span class="recap-label">Nom :</span>
                <span class="recap-value">${formData.get('nom_organisation') || 'Non renseigné'}</span>
            </div>
            <div class="recap-item">
                <span class="recap-label">Sigle :</span>
                <span class="recap-value">${formData.get('sigle') || 'Non renseigné'}</span>
            </div>
            <div class="recap-item">
                <span class="recap-label">Secteur :</span>
                <span class="recap-value">${getSecteurLabel(formData.get('secteur_activite'))}</span>
            </div>
            <div class="recap-item">
                <span class="recap-label">Description :</span>
                <span class="recap-value">${(formData.get('description') || 'Non renseigné').substring(0, 100)}${formData.get('description')?.length > 100 ? '...' : ''}</span>
            </div>
        </div>
    `;
    
    // Coordonnées
    recapHTML += `
        <div class="recap-section">
            <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Coordonnées</h6>
            <div class="recap-item">
                <span class="recap-label">Adresse :</span>
                <span class="recap-value">${formData.get('adresse') || 'Non renseigné'}</span>
            </div>
            <div class="recap-item">
                <span class="recap-label">Ville :</span>
                <span class="recap-value">${getVilleLabel(formData.get('ville'))}</span>
            </div>
            <div class="recap-item">
                <span class="recap-label">Province :</span>
                <span class="recap-value">${getProvinceLabel(formData.get('province'))}</span>
            </div>
            <div class="recap-item">
                <span class="recap-label">Téléphone :</span>
                <span class="recap-value">${formData.get('telephone') || 'Non renseigné'}</span>
            </div>
            <div class="recap-item">
                <span class="recap-label">Email :</span>
                <span class="recap-value">${formData.get('email') || 'Non renseigné'}</span>
            </div>
        </div>
    `;
    
    // Responsables
    const responsables = document.querySelectorAll('.responsable-item');
    recapHTML += `
        <div class="recap-section">
            <h6 class="text-primary mb-3"><i class="fas fa-user-tie me-2"></i>Responsables (${responsables.length})</h6>
    `;
    
    responsables.forEach((responsable, index) => {
        const nom = responsable.querySelector(`input[name="responsables[${index}][nom]"]`)?.value || 'Non renseigné';
        const fonction = responsable.querySelector(`select[name="responsables[${index}][fonction]"]`)?.value || 'Non renseigné';
        const email = responsable.querySelector(`input[name="responsables[${index}][email]"]`)?.value || 'Non renseigné';
        
        recapHTML += `
            <div class="recap-item">
                <span class="recap-label">${index === 0 ? 'Responsable principal' : `Responsable ${index + 1}`} :</span>
                <span class="recap-value">${nom} - ${getFonctionLabel(fonction)} - ${email}</span>
            </div>
        `;
    });
    
    recapHTML += '</div>';
    
    recapContainer.innerHTML = recapHTML;
    
    // Récapitulatif des documents
    generateDocumentsRecap(documentsContainer);
}

function generateDocumentsRecap(container) {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    let documentsHTML = '';
    
    const documentLabels = {
        'statuts': 'Statuts de l\'organisation',
        'pv_constitutive': 'Procès-verbal de l\'AG constitutive',
        'liste_membres': 'Liste des membres fondateurs',
        'justificatif_siege': 'Justificatif de domicile du siège',
        'demande_agrement': 'Demande d\'agrément',
        'logo': 'Logo de l\'organisation',
        'plan_activites': 'Plan d\'activités',
        'budget': 'Budget prévisionnel'
    };
    
    fileInputs.forEach(input => {
        const label = documentLabels[input.name] || input.name.replace('cni_responsable_', 'CNI du responsable ');
        const isRequired = input.hasAttribute('required');
        const hasFile = input.files.length > 0;
        
        documentsHTML += `
            <div class="document-item">
                <div class="document-icon">
                    <i class="fas fa-file-alt text-white"></i>
                </div>
                <div class="document-info">
                    <div class="document-name">${label}</div>
                    <div class="document-size">${hasFile ? formatFileSize(input.files[0].size) : 'Aucun fichier'}</div>
                </div>
                <span class="document-status ${hasFile ? 'uploaded' : 'missing'}">
                    ${hasFile ? 'Uploadé' : (isRequired ? 'Manquant' : 'Optionnel')}
                </span>
            </div>
        `;
    });
    
    container.innerHTML = documentsHTML;
}

function submitForm(type) {
    const submitBtn = type === 'final' ? document.getElementById('submitFinalBtn') : document.getElementById('saveDraftBtn');
    const originalHTML = submitBtn.innerHTML;
    
    // Animation de chargement
    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${type === 'final' ? 'Soumission...' : 'Sauvegarde...'}`;
    submitBtn.disabled = true;
    
    // Simulation de l'envoi
    setTimeout(() => {
        if (type === 'final') {
            alert('Dossier soumis avec succès ! Vous recevrez un accusé de réception par email.');
        } else {
            alert('Dossier sauvegardé en brouillon. Vous pouvez le modifier et le soumettre plus tard.');
        }
        
        // Redirection vers la liste des dossiers
        window.location.href = "{{ route('operator.dossiers.index') }}";
    }, 3000);
}

// Fonction pour supprimer un responsable
function removeResponsable(button) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce responsable ?')) {
        const responsableItem = button.closest('.responsable-item');
        responsableItem.style.transition = 'all 0.3s ease';
        responsableItem.style.opacity = '0';
        responsableItem.style.transform = 'translateX(-100%)';
        
        setTimeout(() => {
            responsableItem.remove();
            responsableCount--;
        }, 300);
    }
}

// Fonctions utilitaires
function getTypeLabel(type) {
    const types = {
        'association': 'Association',
        'fondation': 'Fondation',
        'ong': 'ONG',
        'cooperative': 'Coopérative'
    };
    return types[type] || 'Non défini';
}

function getSecteurLabel(secteur) {
    const secteurs = {
        'education': 'Éducation et Formation',
        'sante': 'Santé et Bien-être',
        'environnement': 'Environnement et Développement Durable',
        'culture': 'Culture et Arts',
        'sport': 'Sport et Loisirs',
        'social': 'Action Sociale et Humanitaire',
        'economie': 'Développement Économique',
        'droits_humains': 'Droits de l\'Homme et Justice',
        'jeunesse': 'Jeunesse et Enfance',
        'femme': 'Promotion de la Femme',
        'autre': 'Autre secteur'
    };
    return secteurs[secteur] || 'Non défini';
}

function getVilleLabel(ville) {
    const villes = {
        'libreville': 'Libreville',
        'port_gentil': 'Port-Gentil',
        'franceville': 'Franceville',
        'oyem': 'Oyem',
        'moanda': 'Moanda',
        'lambarene': 'Lambaréné',
        'tchibanga': 'Tchibanga',
        'koulamoutou': 'Koulamoutou',
        'bitam': 'Bitam',
        'gamba': 'Gamba',
        'autre': 'Autre ville'
    };
    return villes[ville] || 'Non défini';
}

function getProvinceLabel(province) {
    const provinces = {
        'estuaire': 'Estuaire',
        'haut_ogooue': 'Haut-Ogooué',
        'moyen_ogooue': 'Moyen-Ogooué',
        'ngounie': 'Ngounié',
        'nyanga': 'Nyanga',
        'ogooue_ivindo': 'Ogooué-Ivindo',
        'ogooue_lolo': 'Ogooué-Lolo',
        'ogooue_maritime': 'Ogooué-Maritime',
        'woleu_ntem': 'Woleu-Ntem'
    };
    return provinces[province] || 'Non défini';
}

function getFonctionLabel(fonction) {
    const fonctions = {
        'president': 'Président(e)',
        'vice_president': 'Vice-Président(e)',
        'secretaire_general': 'Secrétaire Général(e)',
        'tresorier': 'Trésorier(ère)',
        'directeur_executif': 'Directeur(trice) Exécutif(ve)',
        'coordinateur': 'Coordinateur(trice)',
        'autre': 'Autre fonction'
    };
    return fonctions[fonction] || 'Non défini';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endsection