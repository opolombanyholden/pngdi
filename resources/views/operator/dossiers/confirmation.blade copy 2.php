@extends('layouts.operator')

@section('title', 'Confirmation de Soumission - Phase 2')

@section('page-title', 'Confirmation de Soumission')

@section('content')
<div class="container-fluid">
    {{-- Variables PHP pour la vue --}}
    @php
        $dossier = $confirmationData['dossier'];
        $organisation = $confirmationData['organisation'];
        
        // Gestion session Phase 2
        $sessionKey = 'phase2_adherents_' . $dossier->id;
        $sessionData = session($sessionKey, []);
        
        $adherentsPhase2 = [];
        $adherentsCount = 0;
        $sessionExpiration = null;
        $hasPhase2Pending = false;
        
        if (is_array($sessionData) && !empty($sessionData)) {
            if (isset($sessionData['data']) && is_array($sessionData['data'])) {
                $adherentsPhase2 = $sessionData['data'];
                $adherentsCount = count($adherentsPhase2);
                $sessionExpiration = $sessionData['expires_at'] ?? null;
            } else {
                $adherentsPhase2 = $sessionData;
                $adherentsCount = count($adherentsPhase2);
                $sessionExpiration = session('phase2_expires_' . $dossier->id);
            }
            $hasPhase2Pending = $adherentsCount > 0;
        }
        
        // Calculs dashboard
        $adherentsEnBase = $organisation->adherents()->count();
        
        switch($organisation->type) {
            case 'association': $minAdherents = 15; break;
            case 'ong': $minAdherents = 25; break;
            case 'fondation': $minAdherents = 10; break;
            case 'cooperative': $minAdherents = 20; break;
            default: $minAdherents = 15; break;
        }
        
        $totalAdherents = $adherentsEnBase + $adherentsCount;
        $adherentsManquants = max(0, $minAdherents - $totalAdherents);
        $pretPourSoumission = $totalAdherents >= $minAdherents;
        
        $sessionExpirationFormatted = 'N/A';
        if ($sessionExpiration) {
            try {
                $sessionExpirationFormatted = \Carbon\Carbon::parse($sessionExpiration)->format('d/m/Y à H:i');
            } catch (\Exception $e) {}
        }
        
        $metadataKey = 'phase2_metadata_' . $dossier->id;
        $lotsHistory = session($metadataKey . '.lots_history', []);
        $nombreLots = count($lotsHistory);
    @endphp

    <!-- Header avec statistiques -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #006d2c 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                @if($hasPhase2Pending)
                                    Phase 1 Complétée avec Succès !
                                @else
                                    Organisation Enregistrée !
                                @endif
                            </h2>
                            <p class="mb-0 opacity-90">
                                @if($hasPhase2Pending)
                                    {{ number_format($adherentsCount) }} adhérents prêts pour l'import en base de données
                                @else
                                    Votre organisation {{ $organisation->nom }} est maintenant créée
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="bg-white bg-opacity-20 rounded p-3">
                                <div class="h4 text-white mb-1">{{ $dossier->numero_dossier }}</div>
                                <small>Numéro de dossier</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1" id="adherents-en-base">{{ number_format($adherentsEnBase) }}</h3>
                            <p class="mb-0 small opacity-90">En Base de Données</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-database fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: {{ $adherentsEnBase > 0 ? '100' : '0' }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1" id="adherents-en-session">{{ number_format($adherentsCount) }}</h3>
                            <p class="mb-0 small">En Attente (Session)</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: {{ $adherentsCount > 0 ? '75' : '0' }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1" id="total-adherents">{{ number_format($totalAdherents) }}</h3>
                            <p class="mb-0 small opacity-90">Total Adhérents</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: {{ round(($totalAdherents / $minAdherents) * 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, {{ $pretPourSoumission ? '#009e3f' : '#8b1538' }} 0%, {{ $pretPourSoumission ? '#00b347' : '#c41e3a' }} 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $minAdherents }}</h3>
                            <p class="mb-0 small opacity-90">Minimum Requis</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-{{ $pretPourSoumission ? 'check-circle' : 'exclamation-triangle' }} fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: {{ $pretPourSoumission ? '100' : '30' }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Progression vers soumission</strong>
                                <span id="progress-percentage">{{ round(($totalAdherents / $minAdherents) * 100, 1) }}%</span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $pretPourSoumission ? 'bg-success' : 'bg-warning' }}" 
                                     id="main-progress-bar" 
                                     style="width: {{ min(($totalAdherents / $minAdherents) * 100, 100) }}%">
                                    {{ round(($totalAdherents / $minAdherents) * 100, 1) }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            @if($adherentsManquants > 0)
                                <div class="alert alert-warning mb-0 py-2">
                                    <small>
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $adherentsManquants }} adhérent(s) manquant(s)
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-success mb-0 py-2">
                                    <small>
                                        <i class="fas fa-check-circle me-1"></i>
                                        Conditions remplies !
                                    </small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <button class="btn btn-outline-success" onclick="refreshStatistics()" id="refresh-stats-btn">
                                    <i class="fas fa-sync-alt me-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Phase 2 : Import des adhérents -->
    @if($hasPhase2Pending)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-cloud-upload-alt me-2 text-warning"></i>
                            Phase 2 : Import des {{ number_format($adherentsCount) }} Adhérents
                        </h5>
                        <span class="badge bg-warning text-dark">{{ number_format($adherentsCount) }} en attente</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                <strong>{{ number_format($adherentsCount) }} adhérents détectés en session</strong><br>
                                <small>Ces adhérents vont être importés en base de données et associés à votre organisation.</small>
                            </div>
                        </div>
                    </div>

                    <div id="import-controls" class="text-center">
                        <button id="start-import-btn" class="btn btn-success btn-lg px-5" onclick="startAdherentsImport()">
                            <i class="fas fa-rocket me-2"></i>
                            Démarrer l'Import des {{ number_format($adherentsCount) }} Adhérents
                        </button>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Session expire le {{ $sessionExpirationFormatted }}
                            </small>
                        </div>
                    </div>

                    <div id="import-progress" class="d-none mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <strong id="import-status">Préparation...</strong>
                            <span id="import-percentage">0%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div id="import-progress-bar" 
                                 class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                 style="width: 0%">
                                <span id="import-progress-text">0%</span>
                            </div>
                        </div>
                        <div id="import-details" class="mt-2 small text-muted">
                            Initialisation...
                        </div>
                    </div>

                    <div id="import-results" class="d-none mt-4">
                        <!-- Résultats seront injectés ici -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section upload lots supplémentaires -->
    @if($totalAdherents > 0 && !$pretPourSoumission && !$hasPhase2Pending)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2 text-info"></i>
                            Ajouter des adhérents supplémentaires
                        </h5>
                        <span class="badge bg-info">{{ $adherentsManquants }} manquant(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                <strong>Ajoutez plus d'adhérents pour atteindre le minimum requis</strong><br>
                                <small>Formats acceptés: Excel (.xlsx) ou CSV | Taille max: 10MB | Max 10 000 adhérents</small>
                            </div>
                        </div>
                    </div>

                    <!-- Zone d'upload -->
                    <div class="upload-zone border rounded p-4 text-center mb-3" id="upload-drop-zone" style="border-style: dashed !important; background: #f8f9fa;">
                        <div class="mb-3">
                            <i class="fas fa-cloud-upload-alt fa-4x text-success"></i>
                        </div>
                        <h5 class="mb-3 text-primary">Glissez votre fichier ici ou cliquez pour sélectionner</h5>
                        <p class="text-muted mb-3">
                            <i class="fas fa-file-excel me-1 text-success"></i>Excel (.xlsx) ou CSV
                            <span class="mx-2">|</span>
                            <i class="fas fa-weight-hanging me-1 text-warning"></i>Maximum 10MB
                            <span class="mx-2">|</span>
                            <i class="fas fa-users me-1 text-info"></i>Jusqu'à 10 000 adhérents
                        </p>
                        
                        <input type="file" id="additional-file-input" class="d-none" 
                               accept=".xlsx,.csv" onchange="handleAdditionalFileUpload(this)">
                        
                        <button type="button" class="btn btn-success btn-lg" onclick="document.getElementById('additional-file-input').click()">
                            <i class="fas fa-file-excel me-2"></i>
                            Sélectionner un fichier
                        </button>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1 text-success"></i>
                                Fichiers sécurisés et traités selon la réglementation gabonaise
                            </small>
                        </div>
                    </div>

                    <!-- Zone de résultats upload -->
                    <div id="additional-upload-results" class="mt-3">
                        <!-- Résultats d'upload apparaîtront ici -->
                    </div>

                    <!-- Guide format fichier -->
                    <div class="card bg-light border-0 mt-3">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="fas fa-question-circle me-2"></i>
                                Format de fichier attendu
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="small fw-bold text-success">Colonnes obligatoires :</h6>
                                    <ul class="small mb-0">
                                        <li>Civilité (M./Mme)</li>
                                        <li>Nom</li>
                                        <li>Prénom</li>
                                        <li>NIP (format XX-QQQQ-YYYYMMDD)</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="small fw-bold text-info">Colonnes optionnelles :</h6>
                                    <ul class="small mb-0">
                                        <li>Téléphone</li>
                                        <li>Email</li>
                                        <li>Profession</li>
                                        <li>Adresse</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <button class="btn btn-outline-primary btn-sm" onclick="downloadTemplate()">
                                    <i class="fas fa-download me-1"></i>
                                    Télécharger modèle Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section soumission finale -->
    @if($pretPourSoumission)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-paper-plane me-2 text-success"></i>
                            Prêt pour la Soumission Finale !
                        </h5>
                        <span class="badge bg-success">{{ number_format($totalAdherents) }} adhérents</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-success border-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-3 fs-4"></i>
                            <div>
                                <strong>Toutes les conditions sont remplies !</strong><br>
                                <small>{{ number_format($totalAdherents) }} adhérents prêts pour soumission à l'administration gabonaise</small>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline processus -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="fas fa-list-ol me-2"></i>
                            Processus de soumission finale
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold text-success">Organisation enregistrée</small><br>
                                        <small class="text-muted">Informations validées</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold text-success">Adhérents importés</small><br>
                                        <small class="text-muted">{{ number_format($totalAdherents) }} membres</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                        3
                                    </div>
                                    <div>
                                        <small class="fw-bold text-warning">Soumission finale</small><br>
                                        <small class="text-muted">En cours...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations importantes -->
                    <div class="alert alert-warning border-0 mb-4">
                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Important à savoir avant la soumission
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="small mb-0">
                                    <li><strong>La soumission est définitive</strong> et irréversible</li>
                                    <li><strong>Délai de traitement :</strong> 30-45 jours ouvrés</li>
                                    <li><strong>Accusé de réception</strong> automatique par email</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="small mb-0">
                                    <li><strong>Aucune modification possible</strong> après soumission</li>
                                    <li><strong>Dossier verrouillé</strong> automatiquement</li>
                                    <li><strong>Support disponible</strong> en cas de questions</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire soumission -->
                    <form id="final-submission-form" onsubmit="handleFinalSubmission(event)">
                        @csrf
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3 text-primary">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Déclarations obligatoires
                                </h6>
                                
                                <div class="form-check mb-3 p-3 rounded bg-white">
                                    <input class="form-check-input" type="checkbox" id="declaration-finale" required>
                                    <label class="form-check-label fw-bold" for="declaration-finale">
                                        <i class="fas fa-certificate me-2 text-success"></i>
                                        <strong>Je certifie sur l'honneur</strong> que toutes les informations fournies sont exactes et complètes
                                    </label>
                                </div>
                                
                                <div class="form-check p-3 rounded bg-white">
                                    <input class="form-check-input" type="checkbox" id="confirmation-soumission" required>
                                    <label class="form-check-label fw-bold" for="confirmation-soumission">
                                        <i class="fas fa-gavel me-2 text-primary"></i>
                                        <strong>Je confirme</strong> vouloir soumettre définitivement ce dossier à l'administration gabonaise
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5" id="submit-final-btn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Soumettre mon dossier à l'administration gabonaise
                            </button>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-lock me-1 text-success"></i>
                                    Transmission sécurisée et cryptée selon les normes gabonaises
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Informations du dossier -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open me-2 text-primary"></i>
                        Informations du dossier
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold text-muted">Numéro :</td>
                                <td><span class="badge bg-success fs-6">{{ $dossier->numero_dossier }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Organisation :</td>
                                <td>{{ $organisation->nom }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Type :</td>
                                <td><span class="badge bg-info">{{ ucfirst($organisation->type) }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Statut :</td>
                                <td>
                                    @if($pretPourSoumission)
                                        <span class="badge bg-success">Prêt pour soumission</span>
                                    @elseif($hasPhase2Pending)
                                        <span class="badge bg-warning">Import en attente</span>
                                    @else
                                        <span class="badge bg-info">En préparation</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Créé le :</td>
                                <td>{{ $dossier->created_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2 text-success"></i>
                        Actions rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-success" onclick="refreshStatistics()">
                            <i class="fas fa-sync-alt me-2"></i>Actualiser les statistiques
                        </button>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer cette page
                        </button>
                        <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-info">
                            <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                        </a>
                        <a href="{{ route('operator.organisations.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Nouvelle organisation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) tricolore -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-plus fab-icon"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #009e3f;" title="Actualiser" onclick="refreshStatistics()">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Imprimer" onclick="window.print()">
                <i class="fas fa-print"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Aide" onclick="showHelp()">
                <i class="fas fa-question-circle"></i>
            </button>
        </div>
    </div>
</div>

<style>
/* Styles identiques à index.blade.php */
.stats-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.1);
}

.upload-zone {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-zone:hover {
    background: rgba(0, 158, 63, 0.05) !important;
    border-color: #009e3f !important;
}

.upload-zone.dragover {
    background: rgba(0, 158, 63, 0.1) !important;
    border-color: #009e3f !important;
    transform: scale(1.02);
}

/* FAB Style gabonais identique */
.fab-container {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
}

.fab-menu {
    position: relative;
}

.fab-main {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.fab-main:hover {
    transform: scale(1.1);
}

.fab-icon {
    color: white;
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.fab-options {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.fab-menu.active .fab-options {
    opacity: 1;
    visibility: visible;
}

.fab-menu.active .fab-icon {
    transform: rotate(45deg);
}

.fab-option {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.fab-option:hover {
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .fab-container {
        bottom: 1rem;
        right: 1rem;
    }
    
    .fab-main {
        width: 50px;
        height: 50px;
    }
    
    .fab-option {
        width: 40px;
        height: 40px;
    }
}

/* Animations d'entrée */
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

.card {
    animation: fadeInUp 0.6s ease-out;
}
</style>

<script>
// Configuration JavaScript Phase 2 avec routes dossiers
window.Phase2Config = {
    dossierId: {{ $dossier->id }},
    adherentsCount: {{ $adherentsCount }},
    adherentsEnBase: {{ $adherentsEnBase }},
    totalAdherents: {{ $totalAdherents }},
    minAdherents: {{ $minAdherents }},
    pretPourSoumission: {{ $pretPourSoumission ? 'true' : 'false' }},
    hasPhase2Pending: {{ $hasPhase2Pending ? 'true' : 'false' }},
    csrf: '{{ csrf_token() }}',
    routes: {
        uploadAdditional: '/operator/dossiers/{{ $dossier->id }}/upload-additional-batch',
        submitFinal: '/operator/dossiers/{{ $dossier->id }}/submit-to-administration', 
        getStatistics: '/operator/dossiers/{{ $dossier->id }}/adherents-statistics',
        finalConfirmation: '/operator/dossiers/{{ $dossier->id }}/final-confirmation',
        importAdherents: '/operator/dossiers/{{ $dossier->id }}/import-session-adherents'
    }
};

// Toggle FAB Menu (identique à index.blade.php)
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.classList.toggle('active');
}

// Fermer FAB en cliquant ailleurs
document.addEventListener('click', function(event) {
    const fabMenu = document.getElementById('fabMenu');
    if (!fabMenu.contains(event.target)) {
        fabMenu.classList.remove('active');
    }
});

// Fonctions principales Phase 2
async function startAdherentsImport() {
    if (!window.Phase2Config.hasPhase2Pending) {
        showNotification('Aucun adhérent en attente d\'import', 'warning');
        return;
    }
    
    console.log('🚀 Démarrage import adhérents Phase 2');
    
    const startBtn = document.getElementById('start-import-btn');
    const progressDiv = document.getElementById('import-progress');
    const controlsDiv = document.getElementById('import-controls');
    const resultsDiv = document.getElementById('import-results');
    
    try {
        controlsDiv.classList.add('d-none');
        progressDiv.classList.remove('d-none');
        resultsDiv.classList.add('d-none');
        
        updateImportProgress(10, 'Récupération des données de session...');
        await new Promise(resolve => setTimeout(resolve, 500));
        
        updateImportProgress(25, 'Validation des adhérents...');
        await new Promise(resolve => setTimeout(resolve, 800));
        
        updateImportProgress(50, 'Préparation pour insertion en base...');
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        updateImportProgress(75, 'Import en base de données...');
        
        const response = await fetch('/operator/dossiers/' + window.Phase2Config.dossierId + '/import-session-adherents', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.Phase2Config.csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                dossier_id: window.Phase2Config.dossierId
            })
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Erreur lors de l\'import');
        }
        
        updateImportProgress(100, 'Import terminé avec succès !');
        showImportResults(result);
        setTimeout(refreshStatistics, 1000);
        
    } catch (error) {
        console.error('❌ Erreur import:', error);
        showImportError(error.message);
    }
}

function updateImportProgress(percentage, message) {
    const progressBar = document.getElementById('import-progress-bar');
    const progressText = document.getElementById('import-progress-text');
    const statusText = document.getElementById('import-status');
    const percentageText = document.getElementById('import-percentage');
    const detailsText = document.getElementById('import-details');
    
    if (progressBar) progressBar.style.width = percentage + '%';
    if (progressText) progressText.textContent = percentage + '%';
    if (statusText) statusText.textContent = message;
    if (percentageText) percentageText.textContent = percentage + '%';
    if (detailsText) detailsText.textContent = `Progression: ${percentage}% - ${message}`;
}

function showImportResults(result) {
    const resultsDiv = document.getElementById('import-results');
    const progressDiv = document.getElementById('import-progress');
    
    progressDiv.classList.add('d-none');
    
    const successHTML = `
        <div class="alert alert-success border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fs-4"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-2">Import terminé avec succès !</h6>
                    <ul class="mb-0">
                        <li><strong>${result.data.adherents_imported || 0}</strong> adhérents importés</li>
                        <li><strong>${result.data.adherents_total || 0}</strong> adhérents au total</li>
                        ${result.data.anomalies ? `<li><strong>${result.data.anomalies}</strong> anomalies détectées</li>` : ''}
                    </ul>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-success" onclick="window.location.reload()">
                    <i class="fas fa-sync me-2"></i>Actualiser la page
                </button>
            </div>
        </div>
    `;
    
    resultsDiv.innerHTML = successHTML;
    resultsDiv.classList.remove('d-none');
    
    showNotification('Adhérents importés avec succès !', 'success');
}

function showImportError(message) {
    const resultsDiv = document.getElementById('import-results');
    const progressDiv = document.getElementById('import-progress');
    const controlsDiv = document.getElementById('import-controls');
    
    progressDiv.classList.add('d-none');
    
    const errorHTML = `
        <div class="alert alert-danger border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-2">Erreur lors de l'import</h6>
                    <p class="mb-0">${message}</p>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-warning" onclick="retryImport()">
                    <i class="fas fa-redo me-2"></i>Réessayer
                </button>
            </div>
        </div>
    `;
    
    resultsDiv.innerHTML = errorHTML;
    resultsDiv.classList.remove('d-none');
    
    showNotification('Erreur: ' + message, 'danger');
}

function retryImport() {
    document.getElementById('import-results').classList.add('d-none');
    document.getElementById('import-controls').classList.remove('d-none');
}

// Upload fichier supplémentaire
function handleAdditionalFileUpload(input) {
    const file = input.files[0];
    if (!file) return;
    
    console.log('📁 Upload lot supplémentaire:', file.name);
    
    if (!validateAdditionalFile(file)) {
        input.value = '';
        return;
    }
    
    processAdditionalFile(file);
}

function validateAdditionalFile(file) {
    const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (!allowedTypes.includes(file.type) && !file.name.toLowerCase().endsWith('.csv')) {
        showNotification('Format de fichier non supporté. Utilisez Excel (.xlsx) ou CSV.', 'danger');
        return false;
    }
    
    if (file.size > maxSize) {
        showNotification('Le fichier est trop volumineux. Maximum 10MB.', 'danger');
        return false;
    }
    
    return true;
}

async function processAdditionalFile(file) {
    try {
        showNotification('Traitement du fichier en cours...', 'info');
        
        const formData = new FormData();
        formData.append('fichier_adherents', file);
        formData.append('dossier_id', window.Phase2Config.dossierId);
        
        const response = await fetch('/operator/dossiers/' + window.Phase2Config.dossierId + '/upload-additional-batch', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.Phase2Config.csrf,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Erreur lors de l\'upload');
        }
        
        showAdditionalUploadResults(result);
        setTimeout(refreshStatistics, 1000);
        
    } catch (error) {
        console.error('❌ Erreur upload:', error);
        showNotification('Erreur: ' + error.message, 'danger');
    }
}

function showAdditionalUploadResults(result) {
    const resultsDiv = document.getElementById('additional-upload-results');
    
    const successHTML = `
        <div class="alert alert-success border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fs-4"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-2">Lot supplémentaire ajouté avec succès !</h6>
                    <ul class="mb-0">
                        <li><strong>${result.data.nouveaux_adherents || 0}</strong> nouveaux adhérents</li>
                        <li><strong>${result.data.total_adherents || 0}</strong> adhérents au total</li>
                        ${result.data.doublons_supprimes ? `<li><strong>${result.data.doublons_supprimes}</strong> doublons supprimés</li>` : ''}
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    resultsDiv.innerHTML = successHTML;
    showNotification('Lot supplémentaire ajouté avec succès !', 'success');
    document.getElementById('additional-file-input').value = '';
}

// Soumission finale
async function handleFinalSubmission(event) {
    event.preventDefault();
    
    const form = event.target;
    const declarationFinale = document.getElementById('declaration-finale');
    const confirmationSoumission = document.getElementById('confirmation-soumission');
    const submitBtn = document.getElementById('submit-final-btn');
    
    if (!declarationFinale.checked || !confirmationSoumission.checked) {
        showNotification('Vous devez accepter toutes les déclarations obligatoires', 'warning');
        return;
    }
    
    if (!confirm('Êtes-vous sûr de vouloir soumettre définitivement ce dossier à l\'administration ?\n\nCette action est irréversible.')) {
        return;
    }
    
    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Soumission en cours...';
        
        const formData = new FormData(form);
        
        const response = await fetch('/operator/dossiers/' + window.Phase2Config.dossierId + '/submit-to-administration', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.Phase2Config.csrf,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Erreur lors de la soumission');
        }
        
        showNotification('Dossier soumis avec succès ! Redirection...', 'success');
        
        setTimeout(() => {
            window.location.href = '/operator/dossiers/' + window.Phase2Config.dossierId + '/final-confirmation';
        }, 2000);
        
    } catch (error) {
        console.error('❌ Erreur soumission:', error);
        showNotification('Erreur: ' + error.message, 'danger');
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Soumettre mon dossier à l\'administration gabonaise';
    }
}

// Actualiser statistiques
async function refreshStatistics() {
    try {
        const response = await fetch('/operator/dossiers/' + window.Phase2Config.dossierId + '/adherents-statistics', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.Phase2Config.csrf
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            updateStatisticsDisplay(result.data);
        }
        
    } catch (error) {
        console.warn('⚠️ Erreur actualisation statistiques:', error);
    }
}

function updateStatisticsDisplay(stats) {
    const elements = {
        'adherents-en-base': stats.adherents_en_base || 0,
        'adherents-en-session': stats.adherents_en_session || 0,
        'total-adherents': stats.total_adherents || 0
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = new Intl.NumberFormat().format(value);
        }
    });
    
    const progressBar = document.getElementById('main-progress-bar');
    const progressText = document.getElementById('progress-percentage');
    
    if (progressBar && progressText) {
        const percentage = Math.min((stats.total_adherents / window.Phase2Config.minAdherents) * 100, 100);
        progressBar.style.width = percentage + '%';
        progressBar.textContent = percentage.toFixed(1) + '%';
        progressText.textContent = percentage.toFixed(1) + '%';
        
        if (percentage >= 100) {
            progressBar.className = 'progress-bar bg-success';
        } else {
            progressBar.className = 'progress-bar bg-warning';
        }
    }
    
    if (stats.pret_pour_soumission && !window.Phase2Config.pretPourSoumission) {
        location.reload();
    }
}

// Setup drag & drop
function setupDragAndDrop() {
    const dropZone = document.getElementById('upload-drop-zone');
    const fileInput = document.getElementById('additional-file-input');
    
    if (!dropZone || !fileInput) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        dropZone.classList.add('dragover');
    }
    
    function unhighlight() {
        dropZone.classList.remove('dragover');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            handleAdditionalFileUpload(fileInput);
        }
    }
}

// Fonctions utilitaires
function downloadTemplate() {
    showNotification('Téléchargement du modèle Excel en cours...', 'info');
}

function showHelp() {
    showNotification('Aide : Contactez le support au +241 01 23 45 67', 'info');
}

function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3 fade show`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    setupDragAndDrop();
    refreshStatistics();
    
    // Actualiser toutes les 30 secondes
    setInterval(refreshStatistics, 30000);
});
</script>
@endsection