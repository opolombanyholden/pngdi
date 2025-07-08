{{--
============================================================================
CONFIRMATION.BLADE.PHP - VUE MODERNISÉE IMPORT ADHÉRENTS
Transformation complète en interface d'import avec architecture modulaire
Version: 2.0 - SGLP avec détection automatique gros volumes
============================================================================
--}}

@extends('layouts.operator')

@section('title', 'Import des Adhérents - Finalisation Dossier')

@section('page-title', 'Import Adhérents & Finalisation')

{{-- Styles CSS externalisés --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/gabon-charte.css') }}">
<link rel="stylesheet" href="{{ asset('css/confirmation-import.css') }}">
<link rel="stylesheet" href="{{ asset('css/adherents-manager.css') }}">
<link rel="stylesheet" href="{{ asset('css/chunking-interface.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    {{-- Variables PHP pour les données --}}
    @php
    // Configuration de base depuis le contrôleur
    $dossier = $dossier ?? (object)[
        'id' => 1,
        'numero_dossier' => 'DOS-2025-001',
        'numero_recepisse' => 'REC-2025-001'
    ];

    $organisation = $organisation ?? (object)[
        'id' => 1,
        'nom' => 'Organisation Test',
        'type' => 'association',
        'sigle' => 'OT'
    ];

    // Statistiques adhérents
    $adherents_stats = $adherents_stats ?? [
        'existants' => 5,
        'minimum_requis' => 15,
        'manquants' => 10,
        'peut_soumettre' => false
    ];

    // Configuration upload
    $upload_config = $upload_config ?? [
        'max_file_size' => '10MB',
        'chunk_size' => 100,
        'max_adherents' => 50000,
        'chunking_threshold' => 200
    ];

    // URLs dynamiques
    $urls = $urls ?? [
        'store_adherents' => route('operator.dossiers.store-adherents', $dossier->id),
        'template_download' => route('operator.templates.adherents-excel'),
        'process_chunk' => route('api.organisations.process-chunk'),
        'confirmation' => route('operator.dossiers.confirmation', $dossier->id),
        'health_check' => '/api/chunking/health'
    ];
    @endphp

    {{--
    ============================================================================
    SECTION 1: HEADER PRINCIPAL AVEC PROGRESSION
    ============================================================================
    --}}
    <div class="confirmation-header">
        <div class="header-content">
            <!-- Indicateur de phase -->
            <div class="phase-indicator">
                <i class="fas fa-users me-2"></i>
                Finalisation: Import des Adhérents
            </div>

            <!-- Breadcrumb gabonais -->
            <nav aria-label="breadcrumb" class="breadcrumb-gabon">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('operator.dashboard') }}">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('operator.organisations.index') }}">Organisations</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('operator.organisations.show', $organisation->id ?? 1) }}">{{ $organisation->nom }}</a>
                    </li>
                    <li class="breadcrumb-item active">Import Adhérents</li>
                </ol>
            </nav>

            <!-- Titre principal avec organisation -->
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="header-title">
                        <i class="fas fa-flag me-3"></i>
                        Import des Adhérents
                    </h1>
                    <div class="header-subtitle">
                        <strong>{{ $organisation->nom }}</strong>
                        @if($organisation->sigle)
                        ({{ $organisation->sigle }})
                        @endif
                        | {{ ucfirst($organisation->type) }}
                    </div>
                    <div class="header-meta">
                        <span class="me-3">
                            <i class="fas fa-file-alt me-1"></i>
                            {{ $dossier->numero_dossier }}
                        </span>
                        <span class="me-3">
                            <i class="fas fa-receipt me-1"></i>
                            {{ $dossier->numero_recepisse }}
                        </span>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>
                            Organisation Créée
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                        <button class="btn btn-outline-light me-2" onclick="showHelp()">
                            <i class="fas fa-question-circle me-2"></i>Aide
                        </button>
                        <button class="btn btn-warning" onclick="saveDraft()">
                            <i class="fas fa-save me-2"></i>Sauvegarder
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--
    ============================================================================
    SECTION 2: DASHBOARD STATISTIQUES TEMPS RÉEL
    ============================================================================
    --}}
    <div class="stats-dashboard">
        <div class="dashboard-header">
            <h3 class="dashboard-title">
                <i class="fas fa-chart-pie me-2"></i>
                Statistiques des Adhérents
            </h3>
            <p class="dashboard-subtitle">Suivi en temps réel avec détection automatique des gros volumes</p>
        </div>

        <!-- Grid statistiques -->
        <div class="stats-grid">
            <div class="stat-card highlight" id="stat-existants">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $adherents_stats['existants'] }}</div>
                    <div class="stat-label">Existants</div>
                </div>
            </div>

            <div class="stat-card warning" id="stat-minimum">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $adherents_stats['minimum_requis'] }}</div>
                    <div class="stat-label">Minimum Requis</div>
                </div>
            </div>

            <div class="stat-card {{ $adherents_stats['manquants'] > 0 ? 'danger' : 'success' }}" id="stat-manquants">
                <div class="stat-icon">
                    <i class="fas fa-{{ $adherents_stats['manquants'] > 0 ? 'user-plus' : 'check-circle' }}"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $adherents_stats['manquants'] > 0 ? $adherents_stats['manquants'] : '✓' }}</div>
                    <div class="stat-label">{{ $adherents_stats['manquants'] > 0 ? 'Manquants' : 'Complet' }}</div>
                </div>
            </div>

            <div class="stat-card info" id="stat-importes">
                <div class="stat-icon">
                    <i class="fas fa-upload"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="import-count">0</div>
                    <div class="stat-label">Importés</div>
                </div>
            </div>
        </div>

        <!-- Indicateur de capacité -->
        <div class="capacity-indicator">
            <div class="capacity-header">
                <span class="capacity-label">Capacité d'import détectée</span>
                <span class="capacity-mode" id="capacity-mode">Standard</span>
            </div>
            <div class="capacity-bar">
                <div class="capacity-fill" id="capacity-fill" style="width: {{ min(100, ($adherents_stats['existants'] / $adherents_stats['minimum_requis']) * 100) }}%"></div>
            </div>
            <div class="capacity-info">
                <small class="text-muted">
                    Chunking automatique activé pour volumes > {{ $upload_config['chunking_threshold'] }} adhérents
                </small>
            </div>
        </div>
    </div>

    {{--
    ============================================================================
    SECTION 3: INTERFACE D'IMPORT MODERNISÉE
    ============================================================================
    --}}
    <div class="import-interface">
        <div class="import-header">
            <h4 class="import-title">
                <i class="fas fa-cloud-upload-alt me-2"></i>
                Interface d'Import Intelligente
            </h4>
            <div class="import-options">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="import-mode" id="mode-manuel" value="manuel" checked>
                    <label class="btn btn-outline-primary" for="mode-manuel">
                        <i class="fas fa-keyboard me-2"></i>Manuel
                    </label>

                    <input type="radio" class="btn-check" name="import-mode" id="mode-fichier" value="fichier">
                    <label class="btn btn-outline-success" for="mode-fichier">
                        <i class="fas fa-file-excel me-2"></i>Fichier
                    </label>

                    <input type="radio" class="btn-check" name="import-mode" id="mode-massif" value="massif">
                    <label class="btn btn-outline-warning" for="mode-massif">
                        <i class="fas fa-layer-group me-2"></i>Gros Volume
                    </label>
                </div>
            </div>
        </div>

        <!-- Section import manuel -->
        <div class="import-section" id="import-manuel">
            @include('operator.partials.adherent-form-manual')
        </div>

        <!-- Section import fichier -->
        <div class="import-section" id="import-fichier" style="display: none;">
            @include('operator.partials.adherent-form-upload')
        </div>

        <!-- Section gros volumes -->
        <div class="import-section" id="import-massif" style="display: none;">
            @include('operator.partials.adherent-form-chunking')
        </div>
    </div>

    {{--
    ============================================================================
    SECTION 4: TABLEAU ADHÉRENTS DYNAMIQUE
    ============================================================================
    --}}
    <div class="adherents-manager">
        <div class="manager-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="manager-title">
                    <i class="fas fa-table me-2"></i>
                    Gestionnaire d'Adhérents
                </h4>
                <div class="manager-actions">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportAdherents('csv')">
                            <i class="fas fa-file-csv me-1"></i>CSV
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="exportAdherents('excel')">
                            <i class="fas fa-file-excel me-1"></i>Excel
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="showAdherentsStats()">
                            <i class="fas fa-chart-bar me-1"></i>Stats
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="manager-filters">
            <div class="row">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control" id="adherents-search" placeholder="Rechercher par nom, NIP...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="filter-statut">
                        <option value="">Tous statuts</option>
                        <option value="valide">Valides</option>
                        <option value="anomalie">Anomalies</option>
                        <option value="doublon">Doublons</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="filter-source">
                        <option value="">Toutes sources</option>
                        <option value="manuel">Saisie manuelle</option>
                        <option value="import">Import fichier</option>
                        <option value="chunking">Import massif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-danger w-100" onclick="clearFilters()">
                        <i class="fas fa-times me-1"></i>Effacer
                    </button>
                </div>
                <div class="col-md-2">
                    <div class="adherents-counter">
                        <span class="counter-label">Total:</span>
                        <span class="counter-number" id="adherents-total">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions en lot -->
        <div class="bulk-actions" style="display: none;">
            <!-- Sera ajouté dynamiquement -->
        </div>

        <!-- Tableau responsive -->
        <div class="adherents-table-container">
            <table class="table adherents-table" id="adherents-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th data-sort="nip">NIP</th>
                        <th data-sort="nom_complet">Nom Complet</th>
                        <th data-sort="telephone">Téléphone</th>
                        <th data-sort="profession">Profession</th>
                        <th data-sort="source">Source</th>
                        <th data-sort="statut">Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="adherents-tbody">
                    <!-- Sera rempli dynamiquement -->
                </tbody>
            </table>

            <!-- État vide -->
            <div class="empty-state" id="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-users fa-4x"></i>
                </div>
                <h5 class="empty-title">Aucun adhérent importé</h5>
                <p class="empty-text">Commencez par importer vos adhérents via l'interface ci-dessus</p>
            </div>
        </div>

        <!-- Pagination personnalisée -->
        <div class="adherents-pagination" id="adherents-pagination">
            <!-- Sera générée dynamiquement -->
        </div>
    </div>

    {{--
    ============================================================================
    SECTION 5: ACTIONS FINALES
    ============================================================================
    --}}
    <div class="final-actions">
        <div class="actions-header">
            <h4 class="actions-title">
                <i class="fas fa-rocket me-2"></i>
                Finalisation du Dossier
            </h4>
            <p class="actions-subtitle">Vérifiez vos données et soumettez votre dossier à l'administration</p>
        </div>

        <div class="actions-grid">
            <div class="action-card" id="action-draft">
                <div class="action-icon bg-warning">
                    <i class="fas fa-save"></i>
                </div>
                <div class="action-content">
                    <h6 class="action-title">Sauvegarder en Brouillon</h6>
                    <p class="action-text">Conservez vos données pour finaliser plus tard</p>
                    <button class="btn btn-warning" onclick="saveDraft()">
                        <i class="fas fa-save me-2"></i>Sauvegarder
                    </button>
                </div>
            </div>

            <div class="action-card" id="action-validate">
                <div class="action-icon bg-info">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="action-content">
                    <h6 class="action-title">Valider les Données</h6>
                    <p class="action-text">Vérification complète avant soumission</p>
                    <button class="btn btn-info" onclick="validateAllData()">
                        <i class="fas fa-check-double me-2"></i>Valider
                    </button>
                </div>
            </div>

            <div class="action-card" id="action-submit">
                <div class="action-icon bg-success">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="action-content">
                    <h6 class="action-title">Soumettre à l'Administration</h6>
                    <p class="action-text">Envoi final pour traitement officiel</p>
                    <button class="btn btn-success" onclick="submitToAdmin()" id="submit-final-btn" disabled>
                        <i class="fas fa-paper-plane me-2"></i>Soumettre
                    </button>
                </div>
            </div>
        </div>

        <!-- Résumé avant soumission -->
        <div class="submission-summary" id="submission-summary" style="display: none;">
            <div class="summary-header">
                <h5 class="summary-title">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Résumé de Soumission
                </h5>
            </div>
            <div class="summary-content" id="summary-content">
                <!-- Sera rempli dynamiquement -->
            </div>
        </div>
    </div>
</div>

{{--
============================================================================
MODALS ET INTERFACES SECONDAIRES
============================================================================
--}}

<!-- Modal de progression chunking -->
<div class="modal fade chunking-modal" id="chunkingProgressModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            @include('operator.partials.chunking-progress-modal')
        </div>
    </div>
</div>

<!-- Modal d'aide -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>Guide d'Import des Adhérents
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-primary">Import Manuel</h6>
                        <p>Saisie individuelle idéale pour petits volumes ou corrections ponctuelles.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Validation temps réel</li>
                            <li><i class="fas fa-check text-success me-2"></i>Formatage automatique</li>
                            <li><i class="fas fa-check text-success me-2"></i>Aperçu immédiat</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-success">Import Fichier</h6>
                        <p>Upload Excel/CSV pour volumes moyens avec validation avancée.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Drag & Drop</li>
                            <li><i class="fas fa-check text-success me-2"></i>Analyse automatique</li>
                            <li><i class="fas fa-check text-success me-2"></i>Rapport d'erreurs</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-warning">Import Massif</h6>
                        <p>Chunking adaptatif pour gros volumes avec monitoring temps réel.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Traitement par lots</li>
                            <li><i class="fas fa-check text-success me-2"></i>Pause/Reprise</li>
                            <li><i class="fas fa-check text-success me-2"></i>Optimisé serveur</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de validation finale -->
<div class="modal fade" id="finalValidationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-double me-2"></i>Validation Finale
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="validation-results">
                    <!-- Sera rempli dynamiquement -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="confirmFinalSubmission()">
                    <i class="fas fa-paper-plane me-2"></i>Confirmer Soumission
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Formulaire de soumission caché --}}
<form id="hidden-submission-form" method="POST" action="{{ $urls['store_adherents'] }}" style="display: none;">
    @csrf
    <input type="hidden" name="adherents_data" id="adherents-data-input">
    <input type="hidden" name="submission_type" value="final">
    <input type="hidden" name="validation_passed" id="validation-passed-input">
</form>

{{-- Configuration JavaScript --}}
<script>
// Configuration globale pour les modules JS
window.ConfirmationConfig = {
    dossierId: {{ $dossier->id }},
    organisationId: {{ $organisation->id }},
    organisationNom: '{{ $organisation->nom }}',
    organisationType: '{{ $organisation->type }}',
    
    // Statistiques
    stats: {
        existants: {{ $adherents_stats['existants'] }},
        minimumRequis: {{ $adherents_stats['minimum_requis'] }},
        manquants: {{ $adherents_stats['manquants'] }},
        peutSoumettre: {{ $adherents_stats['peut_soumettre'] ? 'true' : 'false' }}
    },
    
    // Configuration upload
    upload: {
        maxFileSize: '{{ $upload_config['max_file_size'] }}',
        chunkSize: {{ $upload_config['chunk_size'] }},
        maxAdherents: {{ $upload_config['max_adherents'] }},
        chunkingThreshold: {{ $upload_config['chunking_threshold'] }}
    },
    
    // URLs API
    urls: {!! json_encode($urls) !!},
    
    // CSRF
    csrf: '{{ csrf_token() }}',
    
    // Configuration chunking
    chunking: {
        enabled: true,
        threshold: {{ $upload_config['chunking_threshold'] }},
        batchSize: {{ $upload_config['chunk_size'] }},
        maxRetries: 3,
        pauseBetweenChunks: 500
    }
};

// Initialiser les adhérents existants s'il y en a
window.AdherentsData = [];
</script>

@endsection

{{-- Scripts JavaScript externalisés --}}
@push('scripts')
{{-- Bibliothèques externes --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>

{{-- Modules principaux --}}
<script src="{{ asset('js/confirmation-app.js') }}"></script>
<script src="{{ asset('js/adherents-manager.js') }}"></script>
<script src="{{ asset('js/chunking-engine.js') }}"></script>
<script src="{{ asset('js/file-upload-sglp.js') }}"></script>
<script src="{{ asset('js/validation-engine.js') }}"></script>

{{-- Initialisation --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🇬🇦 Initialisation Interface Import Adhérents SGLP v2.0');
    
    // Initialiser l'application principale
    if (window.ConfirmationApp) {
        window.ConfirmationApp.init();
    }
    
    // Initialiser le gestionnaire d'adhérents
    if (window.AdherentsManager) {
        window.AdherentsManager.init();
    }
    
    // Initialiser le moteur de chunking
    if (window.ChunkingEngine) {
        window.ChunkingEngine.init();
    }
    
    console.log('✅ Tous les modules initialisés avec succès');
});
</script>
@endpush