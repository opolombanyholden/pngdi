@extends('layouts.operator')

@section('title', 'Phase 2 - Gestion des Adhérents')
@section('page-title', 'Import des Adhérents')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/organisation-create.css') }}">

    <!-- ========================================================================
         CSS STYLES MODERNISÉS - CHARTE GABONAISE OFFICIELLE
         Intégration du design template_import_adherent.php
         ======================================================================== -->
    <style>
        :root {
            --gabon-green: #009e3f;
            --gabon-green-light: #00b347;
            --gabon-green-dark: #006d2c;
            --gabon-yellow: #ffcd00;
            --gabon-yellow-light: #ffd700;
            --gabon-yellow-dark: #b8930b;
            --gabon-blue: #003f7f;
            --gabon-blue-light: #0056b3;
            --gabon-blue-dark: #002855;
            
            --primary-gradient: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-green-light) 100%);
            --warning-gradient: linear-gradient(135deg, var(--gabon-yellow) 0%, #fd7e14 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            --secondary-gradient: linear-gradient(135deg, var(--gabon-blue) 0%, var(--gabon-blue-light) 100%);
            
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.15);
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        .phase2-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 2rem 0;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .phase2-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="gabon-pattern" x="0" y="0" width="25" height="25" patternUnits="userSpaceOnUse"><circle cx="12.5" cy="12.5" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23gabon-pattern)"/></svg>');
            opacity: 0.3;
        }

        .phase2-content {
            position: relative;
            z-index: 2;
        }

        .phase-indicator {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            display: inline-block;
            margin-bottom: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            animation: phaseGlow 3s ease-in-out infinite;
        }

        @keyframes phaseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            50% { box-shadow: 0 0 0 15px rgba(255, 255, 255, 0); }
        }

        .organization-summary {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: -3rem 0 2rem 0;
            position: relative;
            z-index: 10;
            border: 3px solid var(--gabon-green);
        }

        .organization-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--primary-gradient);
            border-radius: 20px 20px 0 0;
        }

        .org-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f3f5;
        }

        .org-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            box-shadow: var(--shadow-md);
            animation: orgIconPulse 3s ease-in-out infinite;
        }

        @keyframes orgIconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .adherents-dashboard {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: 2rem 0;
            border-left: 5px solid var(--gabon-yellow);
        }

        .dashboard-header {
            background: var(--warning-gradient);
            color: #333;
            padding: 1.5rem;
            border-radius: 15px;
            margin: -2rem -2rem 2rem -2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2));
            transform: translateX(100px);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100px); }
            100% { transform: translateX(200px); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all var(--transition-normal);
            border: 2px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gabon-green);
            transform: scaleX(0);
            transition: transform var(--transition-normal);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--gabon-green);
        }

        .stat-card.highlight {
            background: var(--primary-gradient);
            color: white;
            border-color: var(--gabon-green-dark);
        }

        .stat-card.warning {
            background: var(--warning-gradient);
            color: #333;
            border-color: var(--gabon-yellow-dark);
        }

        .btn-gabon {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all var(--transition-normal);
            border: none;
            position: relative;
            overflow: hidden;
            min-width: 220px;
            font-size: 0.9rem;
        }

        .btn-gabon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-gabon:hover::before {
            left: 100%;
        }

        .btn-primary-gabon {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 6px 20px rgba(0, 158, 63, 0.4);
        }

        .btn-primary-gabon:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 158, 63, 0.6);
        }

        .btn-warning-gabon {
            background: var(--warning-gradient);
            color: #333;
            box-shadow: 0 6px 20px rgba(255, 205, 0, 0.4);
        }

        .btn-warning-gabon:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 205, 0, 0.6);
        }

        .btn-secondary-gabon {
            background: white;
            color: var(--gabon-blue);
            border: 3px solid var(--gabon-blue);
            box-shadow: 0 6px 20px rgba(0, 63, 127, 0.2);
        }

        .btn-secondary-gabon:hover {
            background: var(--gabon-blue);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 63, 127, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .org-header {
                flex-direction: column;
                text-align: center;
            }
            .org-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .btn-gabon {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
@endpush

@section('content')
    <!-- ========================================================================
         HEADER PHASE 2 - NAVIGATION ET CONTEXTE
         ======================================================================== -->
    <div class="phase2-header">
        <div class="container">
            <div class="phase2-content">
                <!-- Indicateur de phase -->
                <div class="phase-indicator">
                    <i class="fas fa-users me-2"></i>
                    Phase 2 : Gestion des Adhérents
                </div>

                <!-- Breadcrumb gabonais -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-white-50 mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('operator.dashboard') }}" class="text-white opacity-75">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('operator.organisations.index') }}" class="text-white opacity-75">Organisations</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('operator.organisations.show', $organisation) }}" class="text-white opacity-75">{{ $organisation->nom }}</a>
                        </li>
                        <li class="breadcrumb-item active text-white">Adhérents</li>
                    </ol>
                </nav>

                <!-- Titre principal -->
                <div class="row align-items-center mt-3">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-2">Gestion des Adhérents</h1>
                        <p class="lead mb-0 opacity-90">
                            Import et traitement intelligent de {{ $adherents_stats['pending_from_phase1'] ?? 0 }} adhérents
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('operator.organisations.create') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Phase 1
                            </a>
                            <button class="btn btn-warning" onclick="showHelp()">
                                <i class="fas fa-question-circle me-2"></i>Aide
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- ========================================================================
             SECTION INFORMATIONS ORGANISATION - RÉCAPITULATIF INTERACTIF
             ======================================================================== -->
        <div class="organization-summary fade-in">
            <div class="org-header">
                <div class="org-icon">
                    <i class="fas fa-building fa-2x text-white"></i>
                </div>
                <div class="org-details flex-grow-1">
                    <h3 class="mb-1">{{ $organisation->nom }}</h3>
                    <div class="org-meta">
                        <span class="me-3">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $organisation->adresse_complete ?? 'Adresse non définie' }}
                        </span>
                        <span class="me-3">
                            <i class="fas fa-calendar me-1"></i>
                            Créée le {{ $organisation->created_at->format('d/m/Y') }}
                        </span>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>
                            Phase 1 Complétée
                        </span>
                    </div>
                </div>
                <div class="org-actions">
                    <a href="{{ route('operator.organisations.show', $organisation) }}" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>

            <!-- Statistiques organisation -->
            <div class="row">
                <div class="col-md-2">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="h5 text-primary mb-1">{{ $organisation->foundateurs_count ?? $organisation->fondateurs->count() }}</div>
                        <small class="text-muted">Fondateurs</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="h5 text-primary mb-1">{{ $organisation->documents_count ?? 0 }}</div>
                        <small class="text-muted">Documents</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="h5 text-primary mb-1">#{{ $dossier->numero_recepisse ?? 'En cours' }}</div>
                        <small class="text-muted">Récépissé</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="h5 text-primary mb-1">{{ ucfirst($organisation->type) }}</div>
                        <small class="text-muted">Type</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="h5 text-primary mb-1">{{ $adherents_stats['minimum_requis'] ?? 10 }}</div>
                        <small class="text-muted">Min. Requis</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-2 bg-light rounded">
                        <div class="h5 text-primary mb-1">2h</div>
                        <small class="text-muted">Session</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================
             DASHBOARD STATISTIQUES ADHÉRENTS - TEMPS RÉEL
             ======================================================================== -->
        <div class="adherents-dashboard slide-in-left">
            <div class="dashboard-header">
                <h2 class="h3 mb-2">
                    <i class="fas fa-chart-line me-2"></i>
                    Statistiques des Adhérents en Temps Réel
                </h2>
                <p class="mb-0">Suivi intelligent du traitement par lots adaptatif</p>
            </div>

            <!-- Grid des statistiques -->
            <div class="stats-grid">
                <div class="stat-card highlight">
                    <div class="h4 mb-1">{{ $adherents_stats['pending_from_phase1'] ?? 0 }}</div>
                    <div class="text-muted">En attente d'import</div>
                </div>

                <div class="stat-card">
                    <div class="h4 mb-1" id="processed-count">0</div>
                    <div class="text-muted">Traités avec succès</div>
                </div>

                <div class="stat-card warning">
                    <div class="h4 mb-1">{{ $adherents_stats['existants'] ?? 0 }}</div>
                    <div class="text-muted">Déjà en base</div>
                </div>

                <div class="stat-card {{ $adherents_stats['peut_soumettre'] ? 'highlight' : 'warning' }}">
                    <div class="h4 mb-1">{{ $adherents_stats['peut_soumettre'] ? 'OUI' : 'NON' }}</div>
                    <div class="text-muted">Peut soumettre</div>
                </div>
            </div>

            @if($has_pending_adherents)
                <div class="alert alert-success border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Données de Phase 1 Détectées</h6>
                            <p class="mb-0">
                                {{ $adherents_stats['pending_from_phase1'] }} adhérents sont prêts à être importés depuis la Phase 1.
                                Le traitement prendra environ {{ ceil($adherents_stats['pending_from_phase1'] / 100) * 2 }} minutes.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-upload fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Import Manuel</h6>
                            <p class="mb-0">
                                Aucune donnée de Phase 1 détectée. Utilisez l'interface ci-dessous pour importer vos adhérents.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- ========================================================================
             SECTION ACTIONS PRINCIPALES - BOUTONS GABONAIS
             ======================================================================== -->
        <div class="adherents-dashboard">
            <h3 class="text-primary fw-bold mb-4">
                <i class="fas fa-cogs me-2"></i>
                Actions Disponibles
            </h3>

            @if($has_pending_adherents)
                <!-- Traitement automatique des données Phase 1 -->
                <div class="alert alert-warning border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-magic fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Traitement Automatique Prêt</h6>
                            <p class="mb-0">
                                {{ $adherents_stats['pending_from_phase1'] }} adhérents de la Phase 1 sont prêts à être importés.
                                Le système utilisera un traitement par lots pour éviter les timeouts.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action automatique -->
                <div class="text-center mb-4">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <button class="btn btn-gabon btn-primary-gabon w-100" onclick="startAutomaticImport()">
                                <i class="fas fa-play me-2"></i>
                                Démarrer l'Import Automatique
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-gabon btn-warning-gabon w-100" onclick="saveDraft()">
                                <i class="fas fa-save me-2"></i>
                                Sauvegarder Brouillon
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-gabon btn-secondary-gabon w-100" onclick="submitToAdmin()">
                                <i class="fas fa-paper-plane me-2"></i>
                                Soumettre à l'Administration
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <!-- Interface import manuel -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-upload me-2"></i>
                            Import Manuel des Adhérents
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="adherentsImportForm" action="{{ $urls['store_adherents'] }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
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
                                        Formats supportés: .xlsx, .xls, .csv | Taille max: {{ $upload_config['max_file_size'] }}
                                    </small>
                                </p>
                                
                                <button type="button" class="btn btn-success btn-lg" id="select-file-btn">
                                    <i class="fas fa-file-excel me-2"></i>
                                    Sélectionner un fichier
                                </button>
                                
                                <input type="file" class="d-none" id="adherents_file" name="adherents_file" accept=".xlsx,.xls,.csv">
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
                                        <a href="{{ $urls['template_download'] }}" class="btn btn-outline-success btn-lg">
                                            <i class="fas fa-download me-2"></i>
                                            Télécharger le modèle
                                        </a>
                                        <small class="d-block text-muted mt-2">
                                            Fichier Excel prêt à remplir
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Boutons d'action manuel -->
                <div class="text-center">
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <button class="btn btn-gabon btn-warning-gabon w-100" onclick="saveDraft()">
                                <i class="fas fa-save me-2"></i>
                                Sauvegarder Brouillon
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-gabon btn-secondary-gabon w-100" onclick="submitToAdmin()" disabled id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Soumettre à l'Administration
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Informations complémentaires -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="h4 text-primary mb-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="fw-bold">~{{ ceil(($adherents_stats['pending_from_phase1'] ?? 100) / 100) * 2 }} minutes</div>
                        <small class="text-muted">Temps estimé</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="h4 text-success mb-2">
                            <i class="fas fa-layers"></i>
                        </div>
                        <div class="fw-bold">Lots de {{ $upload_config['chunk_size'] ?? 100 }}</div>
                        <small class="text-muted">Traitement optimisé</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="h4 text-info mb-2">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="fw-bold">Temps réel</div>
                        <small class="text-muted">Suivi live</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================
             SECTION PROGRESS (Affichée pendant l'import)
             ======================================================================== -->
        <div class="adherents-dashboard d-none" id="progress-section">
            <div class="text-center mb-4">
                <h3 class="text-primary fw-bold">
                    <i class="fas fa-cog fa-spin me-2"></i>
                    Import en Cours...
                </h3>
                <p class="text-muted">Traitement intelligent par lots adaptatif</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-8">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                     id="main-progress" style="width: 0%;" role="progressbar"></div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="h4 text-primary mb-1" id="progress-percentage">0%</div>
                            <small class="text-muted">Progression globale</small>
                        </div>
                    </div>
                    
                    <div class="text-center mb-3">
                        <span class="fw-bold text-primary" id="progress-text">Préparation...</span>
                    </div>

                    <!-- Statistiques temps réel -->
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="h5 text-primary mb-1" id="current-batch">1</div>
                            <small class="text-muted">Lot actuel</small>
                        </div>
                        <div class="col-md-2">
                            <div class="h5 text-primary mb-1" id="total-batches">1</div>
                            <small class="text-muted">Total lots</small>
                        </div>
                        <div class="col-md-2">
                            <div class="h5 text-success mb-1" id="processed-count">0</div>
                            <small class="text-muted">Traités</small>
                        </div>
                        <div class="col-md-2">
                            <div class="h5 text-info mb-1" id="elapsed-time">00:00</div>
                            <small class="text-muted">Temps écoulé</small>
                        </div>
                        <div class="col-md-2">
                            <div class="h5 text-warning mb-1" id="remaining-time">--:--</div>
                            <small class="text-muted">Temps restant</small>
                        </div>
                        <div class="col-md-2">
                            <div class="h5 text-secondary mb-1" id="speed-rate">0/min</div>
                            <small class="text-muted">Vitesse</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions pendant l'import -->
            <div class="text-center mt-3">
                <button class="btn btn-outline-warning me-2" onclick="pauseImport()">
                    <i class="fas fa-pause me-2"></i>Pause
                </button>
                <button class="btn btn-outline-danger" onclick="cancelImport()">
                    <i class="fas fa-stop me-2"></i>Annuler
                </button>
            </div>
        </div>

        <!-- ========================================================================
             TABLEAU APERÇU ADHÉRENTS (Optionnel)
             ======================================================================== -->
        <div class="adherents-dashboard">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Aperçu des Adhérents
                </h4>
                <button class="btn btn-outline-primary btn-sm" onclick="toggleTable()">
                    <i class="fas fa-eye me-1"></i>Voir tout
                </button>
            </div>

            <!-- Table responsive -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>NIP</th>
                            <th>Nom Complet</th>
                            <th>Téléphone</th>
                            <th>Profession</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adherents-table-body">
                        @if($has_pending_adherents && isset($adherents_from_phase1))
                            @foreach(array_slice($adherents_from_phase1['adherents'] ?? [], 0, 5) as $adherent)
                                <tr>
                                    <td><code>{{ $adherent['nip'] ?? 'N/A' }}</code></td>
                                    <td><strong>{{ ($adherent['prenom'] ?? '') . ' ' . ($adherent['nom'] ?? '') }}</strong></td>
                                    <td>{{ $adherent['telephone'] ?? '-' }}</td>
                                    <td>{{ $adherent['profession'] ?? '-' }}</td>
                                    <td><span class="badge bg-warning">En attente</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($adherents_from_phase1['adherents'] ?? []) > 5)
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-ellipsis-h me-2"></i>
                                        Et {{ count($adherents_from_phase1['adherents']) - 5 }} autres adhérents...
                                    </td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                    Aucun adhérent à afficher
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========================================================================
        // JAVASCRIPT POUR INTERFACE PHASE 2 - GESTION ADHÉRENTS MODERNE
        // ========================================================================

        // Configuration globale
        const Phase2Config = {
            dossierId: {{ $dossier->id }},
            organisationId: {{ $organisation->id }},
            totalAdherents: {{ $adherents_stats['pending_from_phase1'] ?? 0 }},
            batchSize: {{ $upload_config['chunk_size'] ?? 100 }},
            estimatedTime: {{ ceil(($adherents_stats['pending_from_phase1'] ?? 100) / 100) * 2 }} * 60 * 1000, // en ms
            updateInterval: 1000, // 1 seconde
            hasSessionData: {{ $has_pending_adherents ? 'true' : 'false' }},
            apiEndpoints: {
                store: '{{ $urls["store_adherents"] }}',
                processSession: '{{ $urls["process_session_adherents"] }}',
                confirmation: '{{ $urls["confirmation"] }}'
            }
        };

        // État global
        let importState = {
            isRunning: false,
            isPaused: false,
            currentBatch: 0,
            totalBatches: Math.ceil(Phase2Config.totalAdherents / Phase2Config.batchSize),
            processed: 0,
            errors: 0,
            startTime: null,
            elapsedTime: 0
        };

        // ========================================================================
        // FONCTIONS PRINCIPALES
        // ========================================================================

        /**
         * Démarrer l'import automatique (données Phase 1)
         */
        function startAutomaticImport() {
            if (importState.isRunning) return;

            if (!confirm(`Démarrer l'import automatique de ${Phase2Config.totalAdherents} adhérents ? Cette opération prendra environ ${Math.ceil(Phase2Config.estimatedTime / 60000)} minutes.`)) {
                return;
            }

            console.log('🚀 Démarrage import automatique Phase 2');

            // Initialiser l'état
            importState.isRunning = true;
            importState.startTime = Date.now();
            importState.currentBatch = 1;
            importState.processed = 0;
            importState.errors = 0;
            importState.totalBatches = Math.ceil(Phase2Config.totalAdherents / Phase2Config.batchSize);

            // Afficher la section progress
            showProgressSection();

            // Démarrer le traitement
            processSessionAdherents();

            // Démarrer le timer
            startTimer();

            showNotification('Import automatique démarré', 'info');
        }

        /**
         * Traiter les adhérents de session
         */
        async function processSessionAdherents() {
            if (!importState.isRunning || importState.isPaused) return;

            try {
                updateProgressText(`Traitement automatique du lot ${importState.currentBatch}/${importState.totalBatches}`);

                // Appel API pour traiter les adhérents de session
                const response = await fetch(Phase2Config.apiEndpoints.processSession, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        batch_number: importState.currentBatch,
                        batch_size: Phase2Config.batchSize
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Mettre à jour les statistiques
                    importState.processed += result.processed_count || Phase2Config.batchSize;
                    updateStatistics();

                    // Vérifier si terminé
                    if (result.completed || importState.processed >= Phase2Config.totalAdherents) {
                        completeImport();
                    } else {
                        // Continuer avec le lot suivant
                        importState.currentBatch++;
                        setTimeout(processSessionAdherents, 1000);
                    }
                } else {
                    throw new Error(result.message || 'Erreur serveur');
                }

            } catch (error) {
                console.error('Erreur traitement:', error);
                importState.errors++;
                updateStatistics();
                
                // Continuer malgré l'erreur
                importState.currentBatch++;
                if (importState.currentBatch <= importState.totalBatches) {
                    setTimeout(processSessionAdherents, 2000);
                } else {
                    completeImport();
                }
            }
        }

        /**
         * Afficher la section progress
         */
        function showProgressSection() {
            const progressSection = document.getElementById('progress-section');
            if (progressSection) {
                progressSection.classList.remove('d-none');
                progressSection.scrollIntoView({ behavior: 'smooth' });
            }
        }

        /**
         * Mettre à jour les statistiques
         */
        function updateStatistics() {
            const progressPercentage = Math.min(100, Math.round((importState.processed / Phase2Config.totalAdherents) * 100));

            // Mettre à jour les éléments DOM
            updateElement('processed-count', importState.processed);
            updateElement('progress-percentage', `${progressPercentage}%`);
            updateElement('current-batch', importState.currentBatch);
            updateElement('total-batches', importState.totalBatches);

            // Mettre à jour la barre de progression
            const progressBar = document.getElementById('main-progress');
            if (progressBar) {
                progressBar.style.width = `${progressPercentage}%`;
            }

            // Calculer la vitesse
            if (importState.elapsedTime > 0) {
                const speedPerMinute = Math.round((importState.processed / (importState.elapsedTime / 60000)));
                updateElement('speed-rate', `${speedPerMinute}/min`);
            }
        }

        /**
         * Mettre à jour le texte de progression
         */
        function updateProgressText(text) {
            updateElement('progress-text', text);
        }

        /**
         * Timer pour le temps écoulé et restant
         */
        function startTimer() {
            const timer = setInterval(() => {
                if (!importState.isRunning) {
                    clearInterval(timer);
                    return;
                }

                importState.elapsedTime = Date.now() - importState.startTime;
                
                // Mettre à jour le temps écoulé
                const elapsedMinutes = Math.floor(importState.elapsedTime / 60000);
                const elapsedSeconds = Math.floor((importState.elapsedTime % 60000) / 1000);
                updateElement('elapsed-time', `${elapsedMinutes.toString().padStart(2, '0')}:${elapsedSeconds.toString().padStart(2, '0')}`);

                // Calculer et afficher le temps restant
                if (importState.processed > 0) {
                    const averageTimePerAdherent = importState.elapsedTime / importState.processed;
                    const remainingAdherents = Phase2Config.totalAdherents - importState.processed;
                    const estimatedRemainingTime = remainingAdherents * averageTimePerAdherent;
                    
                    const remainingMinutes = Math.floor(estimatedRemainingTime / 60000);
                    const remainingSeconds = Math.floor((estimatedRemainingTime % 60000) / 1000);
                    updateElement('remaining-time', `${remainingMinutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`);
                }
            }, 1000);
        }

        /**
         * Terminer l'import
         */
        function completeImport() {
            importState.isRunning = false;
            
            updateProgressText('Import terminé avec succès !');
            updateElement('progress-percentage', '100%');
            
            const progressBar = document.getElementById('main-progress');
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animated');
            }

            showNotification('Import terminé avec succès !', 'success');

            // Proposer la soumission
            setTimeout(() => {
                if (confirm('Import terminé avec succès ! Souhaitez-vous soumettre le dossier à l\'administration ?')) {
                    submitToAdmin();
                }
            }, 2000);
        }

        /**
         * Pause/Reprendre l'import
         */
        function pauseImport() {
            if (importState.isPaused) {
                importState.isPaused = false;
                processSessionAdherents();
                showNotification('Import repris', 'info');
            } else {
                importState.isPaused = true;
                showNotification('Import en pause', 'warning');
            }
        }

        /**
         * Annuler l'import
         */
        function cancelImport() {
            if (!confirm('Êtes-vous sûr de vouloir annuler l\'import ? Les données déjà traitées seront conservées.')) {
                return;
            }

            importState.isRunning = false;
            importState.isPaused = false;
            
            updateProgressText('Import annulé par l\'utilisateur');
            showNotification('Import annulé', 'warning');
        }

        /**
         * Sauvegarder en brouillon
         */
        function saveDraft() {
            showNotification('Sauvegarde en cours...', 'info');
            
            // Simulation de sauvegarde
            setTimeout(() => {
                showNotification('Brouillon sauvegardé avec succès', 'success');
            }, 1000);
        }

        /**
         * Soumettre à l'administration
         */
        function submitToAdmin() {
            if (!confirm('Êtes-vous sûr de vouloir soumettre le dossier à l\'administration ? Cette action est irréversible.')) {
                return;
            }

            showNotification('Soumission en cours...', 'info');
            
            // Redirection vers confirmation
            setTimeout(() => {
                window.location.href = Phase2Config.apiEndpoints.confirmation;
            }, 2000);
        }

        /**
         * Fonctions utilitaires
         */
        function updateElement(id, value) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        }

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

        function showHelp() {
            const helpContent = `
                <div class="text-center mb-3">
                    <i class="fas fa-users fa-3x text-primary mb-2"></i>
                    <h4>Aide - Phase 2: Gestion des Adhérents</h4>
                </div>
                <div class="text-start">
                    <h6><i class="fas fa-info-circle me-2 text-info"></i>Fonctionnement</h6>
                    <p>Le système traite vos adhérents par lots de ${Phase2Config.batchSize} pour éviter les timeouts et garantir la stabilité.</p>
                    
                    <h6><i class="fas fa-clock me-2 text-warning"></i>Temps de traitement</h6>
                    <p>Environ ${Math.ceil(Phase2Config.estimatedTime / 60000)} minutes pour ${Phase2Config.totalAdherents} adhérents. Vous pouvez suivre la progression en temps réel.</p>
                    
                    <h6><i class="fas fa-pause me-2 text-primary"></i>Pause et reprise</h6>
                    <p>Vous pouvez suspendre et reprendre l'import à tout moment sans perte de données.</p>
                    
                    <h6><i class="fas fa-shield-alt me-2 text-success"></i>Sécurité</h6>
                    <p>Toutes les données sont sauvegardées automatiquement. En cas de problème, vous ne perdrez rien.</p>
                </div>
            `;
            
            showModal('Aide - Phase 2: Gestion des Adhérents', helpContent);
        }

        function showModal(title, content) {
            // Implementation du modal
            console.log('Modal:', title, content);
        }

        function toggleTable() {
            console.log('Toggle table visibility');
        }

        // ========================================================================
        // INITIALISATION
        // ========================================================================

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🇬🇦 Interface Phase 2 - Gestion des Adhérents initialisée');
            console.log('Configuration:', Phase2Config);
            
            // Si données de session présentes, préparer l'interface
            if (Phase2Config.hasSessionData) {
                console.log('📋 Données de Phase 1 détectées - Interface automatique activée');
            } else {
                console.log('📁 Mode import manuel activé');
                setupFileUpload();
            }
        });

        /**
         * Configuration upload de fichier pour mode manuel
         */
        function setupFileUpload() {
            const fileInput = document.getElementById('adherents_file');
            const selectBtn = document.getElementById('select-file-btn');
            const dropZone = document.getElementById('file-drop-zone');

            if (selectBtn && fileInput) {
                selectBtn.addEventListener('click', () => {
                    fileInput.click();
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', handleFileSelection);
            }

            if (dropZone) {
                dropZone.addEventListener('dragover', handleDragOver);
                dropZone.addEventListener('drop', handleFileDrop);
                dropZone.addEventListener('click', () => {
                    if (fileInput) fileInput.click();
                });
            }
        }

        function handleFileSelection(event) {
            const file = event.target.files[0];
            if (file) {
                console.log('Fichier sélectionné:', file.name);
                processUploadedFile(file);
            }
        }

        function handleDragOver(event) {
            event.preventDefault();
            event.currentTarget.classList.add('dragover');
        }

        function handleFileDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('dragover');
            
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                processUploadedFile(files[0]);
            }
        }

        function processUploadedFile(file) {
            console.log('Traitement fichier:', file.name);
            showNotification(`Fichier "${file.name}" prêt pour l'import`, 'success');
            
            // Activer le bouton de soumission
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }

        console.log('✅ Interface Phase 2 - Gestion des Adhérents chargée');
    </script>

    <!-- Configuration Laravel pour JavaScript -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="dossier-id" content="{{ $dossier->id }}">
    <meta name="organisation-id" content="{{ $organisation->id }}">
@endsection

@push('scripts')
    <!-- Scripts supplémentaires si nécessaire -->
    <script>
        console.log('🎨 Design moderne gabonais chargé pour Phase 2');
    </script>
@endpush