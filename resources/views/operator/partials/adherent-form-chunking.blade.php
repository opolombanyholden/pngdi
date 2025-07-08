{{--
============================================================================
ADHERENT-FORM-CHUNKING.BLADE.PHP - FORMULAIRE GROS VOLUMES CHUNKING
Partial pour confirmation.blade.php - Section import massif
Version: 2.0 - Interface chunking avancée avec contrôles temps réel
============================================================================
--}}

<div class="chunking-form">
    <div class="form-header mb-4">
        <h5 class="text-warning">
            <i class="fas fa-layer-group me-2"></i>
            Import Gros Volumes (Chunking)
        </h5>
        <p class="text-muted mb-0">
            Traitement par lots adaptatif pour les imports massifs. 
            Idéal pour {{ number_format($upload_config['chunking_threshold'] ?? 200) }}+ adhérents avec pause/reprise.
        </p>
    </div>

    <!-- Détection automatique et configuration -->
    <div class="chunking-config mb-4">
        <div class="card border-warning">
            <div class="card-header bg-warning">
                <h6 class="mb-0 text-dark">
                    <i class="fas fa-cogs me-2"></i>Configuration Intelligente
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="config-item">
                            <label class="form-label fw-bold">
                                <i class="fas fa-users me-1"></i>Volume Détecté
                            </label>
                            <div class="config-value">
                                <span class="h4 text-warning" id="detected-volume">{{ $adherents_stats['pending_import'] ?? 0 }}</span>
                                <span class="text-muted">adhérents</span>
                            </div>
                            <small class="text-muted">Basé sur la sélection de fichier</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="config-item">
                            <label class="form-label fw-bold">
                                <i class="fas fa-microchip me-1"></i>Mode de Traitement
                            </label>
                            <div class="config-value">
                                <span class="badge badge-gabon-accent" id="processing-mode">
                                    Chunking Adaptatif
                                </span>
                            </div>
                            <small class="text-muted">Sélection automatique selon le volume</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration avancée -->
    <div class="advanced-config mb-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-sliders-h me-2"></i>Paramètres Avancés
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-light" id="toggle-advanced">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" id="advanced-settings" style="display: none;">
                <div class="row">
                    <!-- Taille des lots -->
                    <div class="col-md-4 mb-3">
                        <label for="chunk-size" class="form-label">
                            <i class="fas fa-layer-group me-1"></i>Taille des Lots
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="chunk-size" 
                                   value="{{ $upload_config['chunk_size'] ?? 100 }}" 
                                   min="50" max="500" step="50">
                            <span class="input-group-text">adhérents</span>
                        </div>
                        <small class="form-text text-muted">
                            Recommandé: 50-200 pour optimal
                        </small>
                    </div>

                    <!-- Délai entre lots -->
                    <div class="col-md-4 mb-3">
                        <label for="chunk-delay" class="form-label">
                            <i class="fas fa-clock me-1"></i>Délai entre Lots
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="chunk-delay" 
                                   value="500" min="100" max="5000" step="100">
                            <span class="input-group-text">ms</span>
                        </div>
                        <small class="form-text text-muted">
                            Pause pour éviter la surcharge serveur
                        </small>
                    </div>

                    <!-- Tentatives max -->
                    <div class="col-md-4 mb-3">
                        <label for="max-retries" class="form-label">
                            <i class="fas fa-redo me-1"></i>Tentatives Max
                        </label>
                        <input type="number" class="form-control" id="max-retries" 
                               value="3" min="1" max="10">
                        <small class="form-text text-muted">
                            Reprises automatiques en cas d'échec
                        </small>
                    </div>
                </div>

                <div class="row">
                    <!-- Options de validation -->
                    <div class="col-md-6">
                        <h6 class="text-primary">
                            <i class="fas fa-check-circle me-1"></i>Options de Validation
                        </h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="strict-validation" checked>
                            <label class="form-check-label" for="strict-validation">
                                Validation stricte des NIP
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skip-duplicates" checked>
                            <label class="form-check-label" for="skip-duplicates">
                                Ignorer les doublons automatiquement
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="save-anomalies">
                            <label class="form-check-label" for="save-anomalies">
                                Conserver les adhérents avec anomalies
                            </label>
                        </div>
                    </div>

                    <!-- Options de performance -->
                    <div class="col-md-6">
                        <h6 class="text-success">
                            <i class="fas fa-tachometer-alt me-1"></i>Optimisations
                        </h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="parallel-validation" checked>
                            <label class="form-check-label" for="parallel-validation">
                                Validation parallèle (plus rapide)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="memory-optimization">
                            <label class="form-check-label" for="memory-optimization">
                                Optimisation mémoire (gros fichiers)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="progress-verbose">
                            <label class="form-check-label" for="progress-verbose">
                                Progression détaillée
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload zone spécialisée pour gros volumes -->
    <div class="massive-upload-zone mb-4">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="fas fa-cloud-upload-alt me-2"></i>Zone d'Upload Sécurisée
                </h6>
            </div>
            <div class="card-body">
                <!-- Zone de drop améliorée -->
                <div class="massive-drop-zone" id="massive-drop-zone">
                    <div class="drop-content">
                        <div class="drop-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="drop-title">
                            Upload Fichier Volumineux
                        </div>
                        <div class="drop-subtitle">
                            Optimisé pour {{ number_format($upload_config['max_adherents'] ?? 50000) }}+ adhérents
                        </div>
                        <div class="drop-specs">
                            <div class="spec-item">
                                <i class="fas fa-file-excel text-success"></i>
                                Excel (.xlsx) jusqu'à {{ $upload_config['max_file_size'] ?? '50MB' }}
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-file-csv text-primary"></i>
                                CSV (.csv) avec encodage UTF-8
                            </div>
                        </div>
                        <button type="button" class="btn btn-gabon-primary mt-3" id="select-massive-file">
                            <i class="fas fa-folder-open me-2"></i>
                            Sélectionner Fichier Massif
                        </button>
                    </div>
                </div>
                
                <!-- Input file caché pour gros volumes -->
                <input type="file" id="massive-file-input" accept=".xlsx,.xls,.csv" style="display: none;">
                
                <!-- Informations fichier sélectionné -->
                <div class="massive-file-info" id="massive-file-info" style="display: none;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="file-preview">
                                <div class="file-icon">
                                    <i class="fas fa-file-excel text-success"></i>
                                </div>
                                <div class="file-details">
                                    <h6 class="file-name" id="massive-file-name">-</h6>
                                    <div class="file-meta">
                                        <span class="me-3">
                                            <i class="fas fa-weight me-1"></i>
                                            <span id="massive-file-size">-</span>
                                        </span>
                                        <span class="me-3">
                                            <i class="fas fa-users me-1"></i>
                                            <span id="estimated-records">Analyse...</span>
                                        </span>
                                        <span class="badge badge-gabon-warning" id="chunking-indicator">
                                            Chunking Activé
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="file-actions">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="clearMassiveFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button type="button" class="btn btn-gabon-accent" onclick="analyzeMassiveFile()" id="analyze-btn">
                                    <i class="fas fa-search me-2"></i>Analyser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prévisualisation et validation -->
    <div class="chunking-preview" id="chunking-preview" style="display: none;">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-eye me-2"></i>Prévisualisation des Données
                </h6>
            </div>
            <div class="card-body">
                <!-- Statistiques rapides -->
                <div class="preview-stats mb-3">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-preview primary">
                                <div class="stat-number" id="preview-total">0</div>
                                <div class="stat-label">Total Lignes</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-preview success">
                                <div class="stat-number" id="preview-valid">0</div>
                                <div class="stat-label">Valides</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-preview warning">
                                <div class="stat-number" id="preview-warnings">0</div>
                                <div class="stat-label">Avertissements</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-preview danger">
                                <div class="stat-number" id="preview-errors">0</div>
                                <div class="stat-label">Erreurs</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Échantillon de données -->
                <div class="data-sample">
                    <h6 class="text-primary">
                        <i class="fas fa-table me-1"></i>Échantillon (5 premières lignes)
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ligne</th>
                                    <th>Civilité</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>NIP</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody id="sample-data">
                                <!-- Sera rempli dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Actions de prévisualisation -->
                <div class="preview-actions text-center mt-3">
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="reanalyzeFile()">
                        <i class="fas fa-redo me-1"></i>Réanalyser
                    </button>
                    <button type="button" class="btn btn-gabon-primary" onclick="startChunkingProcess()" id="start-chunking-btn">
                        <i class="fas fa-rocket me-2"></i>Démarrer Import Massif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Interface de monitoring temps réel -->
    <div class="chunking-monitor" id="chunking-monitor" style="display: none;">
        <div class="card border-warning">
            <div class="card-header bg-warning">
                <h6 class="mb-0 text-dark">
                    <i class="fas fa-chart-line me-2"></i>Monitoring Temps Réel
                </h6>
            </div>
            <div class="card-body">
                <!-- Barre de progression globale -->
                <div class="global-progress mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">Progression Globale</span>
                        <span class="h5 text-primary mb-0" id="global-percentage">0%</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-gradient-primary progress-bar-striped progress-bar-animated" 
                             id="global-progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="progress-info mt-2">
                        <small class="text-muted" id="global-status">Initialisation...</small>
                    </div>
                </div>

                <!-- Détails du lot en cours -->
                <div class="current-chunk mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Lot en Cours</span>
                                <span class="badge bg-light text-primary" id="current-chunk-number">-</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="chunk-progress">
                                        <div class="progress mb-2" style="height: 15px;">
                                            <div class="progress-bar bg-success" id="chunk-progress-bar" style="width: 0%"></div>
                                        </div>
                                        <div class="chunk-details">
                                            <span id="chunk-status">En attente...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="chunk-stats">
                                        <div class="stat-line">
                                            <span class="stat-label">Traités:</span>
                                            <span class="stat-value" id="chunk-processed">0</span>
                                        </div>
                                        <div class="stat-line">
                                            <span class="stat-label">Total:</span>
                                            <span class="stat-value" id="chunk-total">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques globales -->
                <div class="global-stats mb-4">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="stat-monitor primary">
                                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                                <div class="stat-number" id="monitor-chunks-total">0</div>
                                <div class="stat-label">Lots Total</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-monitor success">
                                <div class="stat-icon"><i class="fas fa-check"></i></div>
                                <div class="stat-number" id="monitor-chunks-done">0</div>
                                <div class="stat-label">Terminés</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-monitor info">
                                <div class="stat-icon"><i class="fas fa-users"></i></div>
                                <div class="stat-number" id="monitor-records-processed">0</div>
                                <div class="stat-label">Adhérents</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-monitor warning">
                                <div class="stat-icon"><i class="fas fa-exclamation"></i></div>
                                <div class="stat-number" id="monitor-warnings">0</div>
                                <div class="stat-label">Anomalies</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-monitor danger">
                                <div class="stat-icon"><i class="fas fa-times"></i></div>
                                <div class="stat-number" id="monitor-errors">0</div>
                                <div class="stat-label">Erreurs</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="stat-monitor secondary">
                                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                                <div class="stat-number" id="monitor-time">0s</div>
                                <div class="stat-label">Durée</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contrôles de traitement -->
                <div class="chunking-controls text-center">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning" onclick="pauseChunking()" id="pause-chunking-btn">
                            <i class="fas fa-pause me-1"></i>Pause
                        </button>
                        <button type="button" class="btn btn-info" onclick="resumeChunking()" id="resume-chunking-btn" style="display: none;">
                            <i class="fas fa-play me-1"></i>Reprendre
                        </button>
                        <button type="button" class="btn btn-danger" onclick="stopChunking()" id="stop-chunking-btn">
                            <i class="fas fa-stop me-1"></i>Arrêter
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="showChunkingLog()" id="log-chunking-btn">
                            <i class="fas fa-list me-1"></i>Journal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Résultats finaux -->
    <div class="chunking-results" id="chunking-results" style="display: none;">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="fas fa-trophy me-2"></i>Import Massif Terminé
                </h6>
            </div>
            <div class="card-body">
                <!-- Résumé détaillé -->
                <div class="final-summary mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-success">
                                <i class="fas fa-check-circle me-2"></i>Import Réussi !
                            </h5>
                            <p class="mb-0">
                                L'import massif de vos adhérents s'est terminé avec succès. 
                                Consultez le résumé détaillé ci-dessous.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="completion-badge">
                                <div class="h1 text-success mb-0">
                                    <i class="fas fa-medal"></i>
                                </div>
                                <div class="badge badge-gabon-success">
                                    100% Terminé
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques finales -->
                <div class="final-stats mb-4">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="final-stat success">
                                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                                <div class="stat-number" id="final-imported">0</div>
                                <div class="stat-label">Adhérents Importés</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="final-stat primary">
                                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                                <div class="stat-number" id="final-duration">0</div>
                                <div class="stat-label">Temps Total</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="final-stat warning">
                                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                <div class="stat-number" id="final-anomalies">0</div>
                                <div class="stat-label">Anomalies Traitées</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="final-stat info">
                                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                                <div class="stat-number" id="final-chunks">0</div>
                                <div class="stat-label">Lots Traités</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions finales -->
                <div class="final-actions text-center">
                    <div class="row justify-content-center">
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="downloadChunkingReport()">
                                <i class="fas fa-file-pdf me-2"></i>
                                Rapport
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-warning w-100" onclick="reviewAnomalies()">
                                <i class="fas fa-search me-2"></i>
                                Anomalies
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-info w-100" onclick="exportFinalData()">
                                <i class="fas fa-download me-2"></i>
                                Exporter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-gabon-primary w-100" onclick="finalizeChunkingImport()">
                                <i class="fas fa-check-double me-2"></i>
                                Finaliser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Guide spécialisé gros volumes -->
    <div class="chunking-guide mt-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>Guide Import Massif
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-success">
                            <i class="fas fa-check-circle me-1"></i>Optimisations
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right me-2 text-success"></i>Chunking automatique activé</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i>Validation parallèle disponible</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i>Pause/reprise à tout moment</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i>Retry automatique sur échec</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary">
                            <i class="fas fa-cog me-1"></i>Recommandations
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Taille lot: 100-200 adhérents</li>
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Délai: 500ms entre lots</li>
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Validation stricte recommandée</li>
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Sauvegarde anomalies activée</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>Points d'attention
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>Évitez fermer l'onglet</li>
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>Connexion stable requise</li>
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>Import non annulable après 50%</li>
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>Doublons gérés automatiquement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript spécifique au chunking --}}
<script>
// Variables globales pour le chunking
let massiveFile = null;
let chunkingState = {
    isActive: false,
    isPaused: false,
    currentChunk: 0,
    totalChunks: 0,
    processedRecords: 0,
    totalRecords: 0,
    startTime: null,
    config: {
        chunkSize: {{ $upload_config['chunk_size'] ?? 100 }},
        delay: 500,
        maxRetries: 3,
        strictValidation: true,
        skipDuplicates: true,
        saveAnomalies: false,
        parallelValidation: true,
        memoryOptimization: false,
        progressVerbose: false
    }
};

document.addEventListener('DOMContentLoaded', function() {
    initializeChunkingForm();
});

/**
 * Initialiser l'interface de chunking
 */
function initializeChunkingForm() {
    console.log('🚀 Initialisation interface chunking massif');
    
    setupAdvancedToggle();
    setupMassiveUpload();
    setupConfigurationHandlers();
    loadChunkingConfiguration();
    
    console.log('✅ Interface chunking initialisée');
}

/**
 * Configurer le toggle des paramètres avancés
 */
function setupAdvancedToggle() {
    const toggleBtn = document.getElementById('toggle-advanced');
    const advancedSettings = document.getElementById('advanced-settings');
    
    if (toggleBtn && advancedSettings) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = advancedSettings.style.display !== 'none';
            
            if (isVisible) {
                advancedSettings.style.display = 'none';
                toggleBtn.innerHTML = '<i class="fas fa-chevron-down"></i>';
            } else {
                advancedSettings.style.display = 'block';
                toggleBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
            }
        });
    }
}

/**
 * Configurer l'upload massif
 */
function setupMassiveUpload() {
    const dropZone = document.getElementById('massive-drop-zone');
    const fileInput = document.getElementById('massive-file-input');
    const selectBtn = document.getElementById('select-massive-file');
    
    if (selectBtn && fileInput) {
        selectBtn.addEventListener('click', () => {
            fileInput.click();
        });
    }
    
    if (dropZone && fileInput) {
        // Clic sur la zone
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });
        
        // Drag & Drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleMassiveFileSelection(files[0]);
            }
        });
    }
    
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleMassiveFileSelection(e.target.files[0]);
            }
        });
    }
}

/**
 * Configurer les gestionnaires de configuration
 */
function setupConfigurationHandlers() {
    // Taille des lots
    const chunkSizeInput = document.getElementById('chunk-size');
    if (chunkSizeInput) {
        chunkSizeInput.addEventListener('change', (e) => {
            chunkingState.config.chunkSize = parseInt(e.target.value);
            updateChunkingEstimation();
        });
    }
    
    // Délai entre lots
    const chunkDelayInput = document.getElementById('chunk-delay');
    if (chunkDelayInput) {
        chunkDelayInput.addEventListener('change', (e) => {
            chunkingState.config.delay = parseInt(e.target.value);
            updateChunkingEstimation();
        });
    }
    
    // Tentatives max
    const maxRetriesInput = document.getElementById('max-retries');
    if (maxRetriesInput) {
        maxRetriesInput.addEventListener('change', (e) => {
            chunkingState.config.maxRetries = parseInt(e.target.value);
        });
    }
    
    // Checkboxes de configuration
    const configCheckboxes = [
        'strict-validation', 'skip-duplicates', 'save-anomalies',
        'parallel-validation', 'memory-optimization', 'progress-verbose'
    ];
    
    configCheckboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox) {
            checkbox.addEventListener('change', (e) => {
                const configKey = id.replace(/-([a-z])/g, (g) => g[1].toUpperCase()).replace('-', '');
                chunkingState.config[configKey] = e.target.checked;
            });
        }
    });
}

/**
 * Gérer la sélection d'un fichier massif
 */
function handleMassiveFileSelection(file) {
    console.log('📁 Fichier massif sélectionné:', file.name);
    
    // Validation spécialisée pour gros volumes
    if (!validateMassiveFile(file)) {
        return;
    }
    
    massiveFile = file;
    displayMassiveFileInfo(file);
    
    // Masquer la zone de drop et afficher les infos
    document.getElementById('massive-drop-zone').style.display = 'none';
    document.getElementById('massive-file-info').style.display = 'block';
    
    // Estimation rapide du nombre d'enregistrements
    estimateRecordCount(file);
}

/**
 * Valider un fichier massif
 */
function validateMassiveFile(file) {
    const maxSize = parseInt('{{ $upload_config["max_file_size_bytes"] ?? 52428800 }}'); // 50MB par défaut
    const allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv'
    ];
    
    if (!allowedTypes.includes(file.type)) {
        showChunkingError('Type de fichier non supporté pour l\'import massif');
        return false;
    }
    
    if (file.size > maxSize) {
        showChunkingError(`Fichier trop volumineux. Taille maximum: {{ $upload_config["max_file_size"] ?? "50MB" }}`);
        return false;
    }
    
    return true;
}

/**
 * Afficher les informations du fichier massif
 */
function displayMassiveFileInfo(file) {
    const fileName = document.getElementById('massive-file-name');
    const fileSize = document.getElementById('massive-file-size');
    
    if (fileName) fileName.textContent = file.name;
    if (fileSize) fileSize.textContent = formatFileSize(file.size);
}

/**
 * Estimer le nombre d'enregistrements
 */
async function estimateRecordCount(file) {
    const estimatedEl = document.getElementById('estimated-records');
    
    try {
        if (estimatedEl) estimatedEl.textContent = 'Estimation...';
        
        // Estimation basée sur la taille du fichier
        let estimatedRecords = 0;
        
        if (file.type.includes('csv')) {
            // CSV: environ 100 bytes par ligne en moyenne
            estimatedRecords = Math.floor(file.size / 100);
        } else {
            // Excel: environ 50 bytes par ligne compressée
            estimatedRecords = Math.floor(file.size / 50);
        }
        
        // Ajuster pour les en-têtes (environ 5% du fichier)
        estimatedRecords = Math.floor(estimatedRecords * 0.95);
        
        if (estimatedEl) {
            estimatedEl.textContent = `~${estimatedRecords.toLocaleString()} adhérents`;
        }
        
        // Mettre à jour le volume détecté
        const detectedVolume = document.getElementById('detected-volume');
        if (detectedVolume) {
            detectedVolume.textContent = estimatedRecords.toLocaleString();
        }
        
        // Déterminer si le chunking est nécessaire
        const chunkingThreshold = {{ $upload_config['chunking_threshold'] ?? 200 }};
        const modeIndicator = document.getElementById('processing-mode');
        
        if (estimatedRecords >= chunkingThreshold) {
            if (modeIndicator) {
                modeIndicator.textContent = 'Chunking Massif';
                modeIndicator.className = 'badge badge-gabon-warning';
            }
        } else {
            if (modeIndicator) {
                modeIndicator.textContent = 'Traitement Standard';
                modeIndicator.className = 'badge badge-gabon-primary';
            }
        }
        
        updateChunkingEstimation();
        
    } catch (error) {
        console.error('Erreur estimation:', error);
        if (estimatedEl) estimatedEl.textContent = 'Erreur estimation';
    }
}

/**
 * Analyser le fichier massif
 */
async function analyzeMassiveFile() {
    if (!massiveFile) {
        showChunkingError('Aucun fichier sélectionné');
        return;
    }
    
    console.log('🔍 Analyse du fichier massif:', massiveFile.name);
    
    const analyzeBtn = document.getElementById('analyze-btn');
    if (analyzeBtn) {
        analyzeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Analyse...';
        analyzeBtn.disabled = true;
    }
    
    try {
        // Lire et parser un échantillon du fichier
        const fileData = await readMassiveFileSample(massiveFile);
        const sampleData = await parseMassiveFileSample(fileData, massiveFile.type);
        
        // Valider l'échantillon
        const validationResults = validateMassiveSample(sampleData);
        
        // Afficher la prévisualisation
        displayChunkingPreview(sampleData, validationResults);
        
        // Activer le bouton de démarrage
        const startBtn = document.getElementById('start-chunking-btn');
        if (startBtn) {
            startBtn.disabled = false;
        }
        
    } catch (error) {
        console.error('❌ Erreur analyse fichier massif:', error);
        showChunkingError(error.message);
    } finally {
        if (analyzeBtn) {
            analyzeBtn.innerHTML = '<i class="fas fa-search me-2"></i>Analyser';
            analyzeBtn.disabled = false;
        }
    }
}

/**
 * Lire un échantillon du fichier massif
 */
function readMassiveFileSample(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            resolve(e.target.result);
        };
        
        reader.onerror = () => {
            reject(new Error('Erreur de lecture du fichier'));
        };
        
        if (file.type.includes('csv')) {
            // Pour CSV, lire les premiers 10KB pour échantillon
            const blob = file.slice(0, 10240);
            reader.readAsText(blob);
        } else {
            // Pour Excel, lire tout le fichier (nécessaire pour la structure)
            reader.readAsArrayBuffer(file);
        }
    });
}

/**
 * Parser l'échantillon du fichier massif
 */
async function parseMassiveFileSample(fileData, fileType) {
    if (fileType.includes('csv')) {
        return parseCSVSample(fileData);
    } else {
        return parseExcelSample(fileData);
    }
}

/**
 * Parser échantillon CSV
 */
function parseCSVSample(csvData) {
    if (typeof Papa !== 'undefined') {
        const results = Papa.parse(csvData, {
            header: true,
            skipEmptyLines: true,
            dynamicTyping: true,
            preview: 10 // Seulement les 10 premières lignes
        });
        
        return results.data.map(mapRowToAdherent);
    } else {
        throw new Error('Bibliothèque CSV non disponible');
    }
}

/**
 * Parser échantillon Excel
 */
function parseExcelSample(excelData) {
    if (typeof XLSX !== 'undefined') {
        const workbook = XLSX.read(excelData, { type: 'array' });
        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];
        
        const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
        
        if (jsonData.length < 2) {
            throw new Error('Le fichier Excel doit contenir au moins une ligne d\'en-têtes');
        }
        
        const headers = jsonData[0];
        const rows = jsonData.slice(1, 11); // Seulement les 10 premières lignes
        
        return rows.map(row => {
            const obj = {};
            headers.forEach((header, index) => {
                obj[header] = row[index];
            });
            return mapRowToAdherent(obj);
        }).filter(adherent => adherent.nom || adherent.prenom);
        
    } else {
        throw new Error('Bibliothèque Excel non disponible');
    }
}

/**
 * Valider l'échantillon massif
 */
function validateMassiveSample(sampleData) {
    const results = {
        valid: 0,
        warnings: 0,
        errors: 0,
        total: sampleData.length,
        issues: []
    };
    
    sampleData.forEach((adherent, index) => {
        const validation = validateSingleAdherent(adherent, index + 1);
        
        if (validation.isValid) {
            results.valid++;
        }
        
        results.warnings += validation.warnings.length;
        results.errors += validation.errors.length;
        
        if (validation.errors.length > 0) {
            results.issues.push(...validation.errors);
        }
    });
    
    return results;
}

/**
 * Afficher la prévisualisation chunking
 */
function displayChunkingPreview(sampleData, validationResults) {
    const previewDiv = document.getElementById('chunking-preview');
    
    // Mettre à jour les statistiques
    updatePreviewStat('preview-total', sampleData.length);
    updatePreviewStat('preview-valid', validationResults.valid);
    updatePreviewStat('preview-warnings', validationResults.warnings);
    updatePreviewStat('preview-errors', validationResults.errors);
    
    // Afficher l'échantillon de données
    const sampleTable = document.getElementById('sample-data');
    if (sampleTable) {
        sampleTable.innerHTML = sampleData.slice(0, 5).map((adherent, index) => {
            const hasErrors = !validateSingleAdherent(adherent, index + 1).isValid;
            const statusClass = hasErrors ? 'text-danger' : 'text-success';
            const statusIcon = hasErrors ? 'fas fa-times' : 'fas fa-check';
            
            return `
                <tr class="${hasErrors ? 'table-danger' : 'table-success'}">
                    <td>${index + 1}</td>
                    <td>${adherent.civilite || '-'}</td>
                    <td>${adherent.nom || '-'}</td>
                    <td>${adherent.prenom || '-'}</td>
                    <td><code>${adherent.nip || '-'}</code></td>
                    <td><i class="${statusIcon} ${statusClass}"></i></td>
                </tr>
            `;
        }).join('');
    }
    
    if (previewDiv) {
        previewDiv.style.display = 'block';
    }
}

/**
 * Mettre à jour une statistique de prévisualisation
 */
function updatePreviewStat(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

/**
 * Démarrer le processus de chunking
 */
async function startChunkingProcess() {
    if (!massiveFile) {
        showChunkingError('Aucun fichier sélectionné');
        return;
    }
    
    console.log('🚀 Démarrage processus chunking massif');
    
    // Masquer la prévisualisation et afficher le monitoring
    document.getElementById('chunking-preview').style.display = 'none';
    document.getElementById('chunking-monitor').style.display = 'block';
    
    chunkingState.isActive = true;
    chunkingState.startTime = Date.now();
    
    try {
        // Initialiser le chunking avec le moteur
        if (window.ChunkingEngine && window.ChunkingEngine.processImportWithChunking) {
            
            // Lire le fichier complet
            const fileData = await readFile(massiveFile);
            const adherentsData = await parseFileData(fileData, massiveFile.type);
            
            // Configuration du chunking
            const chunkingConfig = {
                chunkSize: chunkingState.config.chunkSize,
                delay: chunkingState.config.delay,
                maxRetries: chunkingState.config.maxRetries,
                onProgress: updateChunkingProgress,
                onChunkStart: onChunkStart,
                onChunkComplete: onChunkComplete,
                onError: onChunkingError,
                onComplete: onChunkingComplete
            };
            
            // Démarrer le chunking
            await window.ChunkingEngine.processImportWithChunking(adherentsData, chunkingConfig);
            
        } else {
            throw new Error('Module ChunkingEngine non disponible');
        }
        
    } catch (error) {
        console.error('❌ Erreur processus chunking:', error);
        showChunkingError(error.message);
    }
}

/**
 * Callbacks du processus de chunking
 */
function updateChunkingProgress(progress) {
    // Mettre à jour la progression globale
    const globalProgressBar = document.getElementById('global-progress-bar');
    const globalPercentage = document.getElementById('global-percentage');
    const globalStatus = document.getElementById('global-status');
    
    if (globalProgressBar) {
        globalProgressBar.style.width = progress.percentage + '%';
    }
    if (globalPercentage) {
        globalPercentage.textContent = Math.round(progress.percentage) + '%';
    }
    if (globalStatus) {
        globalStatus.textContent = progress.status;
    }
    
    // Mettre à jour les statistiques de monitoring
    updateMonitoringStat('monitor-chunks-total', progress.totalChunks);
    updateMonitoringStat('monitor-chunks-done', progress.completedChunks);
    updateMonitoringStat('monitor-records-processed', progress.processedRecords);
    updateMonitoringStat('monitor-warnings', progress.warnings);
    updateMonitoringStat('monitor-errors', progress.errors);
    
    // Temps écoulé
    if (chunkingState.startTime) {
        const elapsed = Math.floor((Date.now() - chunkingState.startTime) / 1000);
        updateMonitoringStat('monitor-time', elapsed + 's');
    }
}

function onChunkStart(chunkInfo) {
    // Mettre à jour le numéro du chunk en cours
    const chunkNumber = document.getElementById('current-chunk-number');
    if (chunkNumber) {
        chunkNumber.textContent = `${chunkInfo.number}/${chunkInfo.total}`;
    }
    
    // Réinitialiser la progression du chunk
    const chunkProgressBar = document.getElementById('chunk-progress-bar');
    const chunkStatus = document.getElementById('chunk-status');
    
    if (chunkProgressBar) chunkProgressBar.style.width = '0%';
    if (chunkStatus) chunkStatus.textContent = 'Traitement en cours...';
    
    updateChunkStat('chunk-processed', 0);
    updateChunkStat('chunk-total', chunkInfo.size);
}

function onChunkComplete(chunkResult) {
    // Marquer le chunk comme terminé
    const chunkProgressBar = document.getElementById('chunk-progress-bar');
    const chunkStatus = document.getElementById('chunk-status');
    
    if (chunkProgressBar) chunkProgressBar.style.width = '100%';
    if (chunkStatus) {
        chunkStatus.textContent = `Terminé: ${chunkResult.processed}/${chunkResult.total}`;
    }
    
    updateChunkStat('chunk-processed', chunkResult.processed);
}

function onChunkingError(error) {
    console.error('❌ Erreur chunk:', error);
    showChunkingError(`Erreur lors du traitement: ${error.message}`);
}

function onChunkingComplete(finalResults) {
    console.log('✅ Chunking terminé:', finalResults);
    
    // Masquer le monitoring et afficher les résultats
    document.getElementById('chunking-monitor').style.display = 'none';
    document.getElementById('chunking-results').style.display = 'block';
    
    // Mettre à jour les statistiques finales
    updateFinalStat('final-imported', finalResults.imported);
    updateFinalStat('final-anomalies', finalResults.anomalies);
    updateFinalStat('final-chunks', finalResults.totalChunks);
    
    if (chunkingState.startTime) {
        const duration = Math.floor((Date.now() - chunkingState.startTime) / 1000);
        updateFinalStat('final-duration', formatDuration(duration));
    }
    
    chunkingState.isActive = false;
}

/**
 * Fonctions utilitaires de mise à jour
 */
function updateMonitoringStat(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

function updateChunkStat(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

function updateFinalStat(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

/**
 * Contrôles de chunking
 */
function pauseChunking() {
    chunkingState.isPaused = !chunkingState.isPaused;
    
    const pauseBtn = document.getElementById('pause-chunking-btn');
    const resumeBtn = document.getElementById('resume-chunking-btn');
    
    if (chunkingState.isPaused) {
        if (pauseBtn) pauseBtn.style.display = 'none';
        if (resumeBtn) resumeBtn.style.display = 'inline-block';
        
        if (window.ChunkingEngine && window.ChunkingEngine.pause) {
            window.ChunkingEngine.pause();
        }
    }
}

function resumeChunking() {
    chunkingState.isPaused = false;
    
    const pauseBtn = document.getElementById('pause-chunking-btn');
    const resumeBtn = document.getElementById('resume-chunking-btn');
    
    if (pauseBtn) pauseBtn.style.display = 'inline-block';
    if (resumeBtn) resumeBtn.style.display = 'none';
    
    if (window.ChunkingEngine && window.ChunkingEngine.resume) {
        window.ChunkingEngine.resume();
    }
}

function stopChunking() {
    if (confirm('Êtes-vous sûr de vouloir arrêter l\'import massif ?')) {
        chunkingState.isActive = false;
        chunkingState.isPaused = false;
        
        if (window.ChunkingEngine && window.ChunkingEngine.stop) {
            window.ChunkingEngine.stop();
        }
        
        // Réinitialiser l'interface
        resetChunkingInterface();
    }
}

function showChunkingLog() {
    if (window.ConfirmationApp && window.ConfirmationApp.showInfo) {
        window.ConfirmationApp.showInfo('Journal de chunking en développement');
    }
}

/**
 * Actions finales
 */
function downloadChunkingReport() {
    console.log('📄 Téléchargement rapport chunking');
    // TODO: Implémenter la génération de rapport
}

function reviewAnomalies() {
    console.log('🔍 Révision des anomalies');
    // TODO: Implémenter la révision des anomalies
}

function exportFinalData() {
    console.log('💾 Export des données finales');
    // TODO: Implémenter l'export
}

function finalizeChunkingImport() {
    console.log('🏁 Finalisation import chunking');
    
    if (window.ConfirmationApp && window.ConfirmationApp.updateStatistics) {
        window.ConfirmationApp.updateStatistics();
    }
    
    if (window.ConfirmationApp && window.ConfirmationApp.showSuccess) {
        window.ConfirmationApp.showSuccess('Import massif finalisé avec succès !');
    }
}

/**
 * Fonctions utilitaires
 */
function updateChunkingEstimation() {
    // Calculer et afficher les estimations
    const detectedVolume = document.getElementById('detected-volume');
    const chunkSizeInput = document.getElementById('chunk-size');
    
    if (detectedVolume && chunkSizeInput) {
        const totalRecords = parseInt(detectedVolume.textContent.replace(/,/g, '')) || 0;
        const chunkSize = parseInt(chunkSizeInput.value) || 100;
        
        if (totalRecords > 0) {
            const estimatedChunks = Math.ceil(totalRecords / chunkSize);
            const estimatedTime = estimatedChunks * (chunkingState.config.delay / 1000 + 2); // 2s par chunk en moyenne
            
            console.log(`📊 Estimation: ${estimatedChunks} lots, ~${Math.round(estimatedTime)}s`);
        }
    }
}

function clearMassiveFile() {
    massiveFile = null;
    document.getElementById('massive-file-info').style.display = 'none';
    document.getElementById('massive-drop-zone').style.display = 'block';
    document.getElementById('massive-file-input').value = '';
    document.getElementById('chunking-preview').style.display = 'none';
    
    // Réinitialiser les estimations
    const detectedVolume = document.getElementById('detected-volume');
    if (detectedVolume) detectedVolume.textContent = '0';
}

function reanalyzeFile() {
    if (massiveFile) {
        analyzeMassiveFile();
    }
}

function resetChunkingInterface() {
    // Masquer toutes les sections actives
    document.getElementById('chunking-monitor').style.display = 'none';
    document.getElementById('chunking-results').style.display = 'none';
    
    // Réafficher la zone d'upload
    clearMassiveFile();
    
    // Réinitialiser l'état
    chunkingState = {
        isActive: false,
        isPaused: false,
        currentChunk: 0,
        totalChunks: 0,
        processedRecords: 0,
        totalRecords: 0,
        startTime: null,
        config: chunkingState.config // Conserver la configuration
    };
}

function loadChunkingConfiguration() {
    // Charger la configuration depuis les préférences utilisateur ou défauts
    const savedConfig = localStorage.getItem('chunking-config');
    
    if (savedConfig) {
        try {
            const config = JSON.parse(savedConfig);
            chunkingState.config = { ...chunkingState.config, ...config };
            
            // Appliquer la configuration aux éléments UI
            const chunkSizeInput = document.getElementById('chunk-size');
            if (chunkSizeInput && config.chunkSize) {
                chunkSizeInput.value = config.chunkSize;
            }
            
            const chunkDelayInput = document.getElementById('chunk-delay');
            if (chunkDelayInput && config.delay) {
                chunkDelayInput.value = config.delay;
            }
            
            const maxRetriesInput = document.getElementById('max-retries');
            if (maxRetriesInput && config.maxRetries) {
                maxRetriesInput.value = config.maxRetries;
            }
            
            // Checkboxes
            ['strict-validation', 'skip-duplicates', 'save-anomalies', 
             'parallel-validation', 'memory-optimization', 'progress-verbose'].forEach(id => {
                const checkbox = document.getElementById(id);
                const configKey = id.replace(/-([a-z])/g, (g) => g[1].toUpperCase()).replace('-', '');
                if (checkbox && config[configKey] !== undefined) {
                    checkbox.checked = config[configKey];
                }
            });
            
            console.log('⚙️ Configuration chunking chargée');
        } catch (error) {
            console.warn('⚠️ Erreur chargement configuration chunking:', error);
        }
    }
}

function saveChunkingConfiguration() {
    // Sauvegarder la configuration actuelle
    try {
        localStorage.setItem('chunking-config', JSON.stringify(chunkingState.config));
        console.log('💾 Configuration chunking sauvegardée');
    } catch (error) {
        console.warn('⚠️ Erreur sauvegarde configuration chunking:', error);
    }
}

function showChunkingError(message) {
    if (window.ConfirmationApp && window.ConfirmationApp.showError) {
        window.ConfirmationApp.showError(message);
    } else {
        console.error('❌ Erreur chunking:', message);
        alert('Erreur: ' + message);
    }
}

function formatDuration(seconds) {
    if (seconds < 60) {
        return seconds + 's';
    } else if (seconds < 3600) {
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${minutes}m ${secs}s`;
    } else {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function mapRowToAdherent(row) {
    return {
        civilite: row.civilite || row.Civilite || row.CIVILITE || 'M',
        nom: (row.nom || row.Nom || row.NOM || '').toString().trim().toUpperCase(),
        prenom: (row.prenom || row.Prenom || row.PRENOM || '').toString().trim(),
        nip: (row.nip || row.NIP || row.Nip || '').toString().trim().toUpperCase(),
        telephone: (row.telephone || row.Telephone || row.TELEPHONE || '').toString().trim(),
        profession: (row.profession || row.Profession || row.PROFESSION || '').toString().trim(),
        source: 'chunking'
    };
}

function validateSingleAdherent(adherent, lineNumber) {
    const validation = {
        isValid: true,
        warnings: [],
        errors: []
    };
    
    // Validation obligatoire
    if (!adherent.nom) {
        validation.errors.push(`Ligne ${lineNumber}: Nom obligatoire`);
        validation.isValid = false;
    }
    
    if (!adherent.prenom) {
        validation.errors.push(`Ligne ${lineNumber}: Prénom obligatoire`);
        validation.isValid = false;
    }
    
    if (!adherent.nip) {
        validation.errors.push(`Ligne ${lineNumber}: NIP obligatoire`);
        validation.isValid = false;
    } else {
        // Validation format NIP
        const nipPattern = /^[A-Z]{2}-[0-9]{4}-[0-9]{8}$/;
        if (!nipPattern.test(adherent.nip)) {
            validation.errors.push(`Ligne ${lineNumber}: Format NIP invalide (${adherent.nip})`);
            validation.isValid = false;
        } else {
            // Vérifier l'âge
            const age = extractAgeFromNIP(adherent.nip);
            if (age < 18) {
                validation.errors.push(`Ligne ${lineNumber}: Adhérent mineur (${age} ans)`);
                validation.isValid = false;
            }
        }
    }
    
    // Avertissements
    if (!adherent.telephone) {
        validation.warnings.push(`Ligne ${lineNumber}: Téléphone manquant`);
    }
    
    if (!adherent.profession) {
        validation.warnings.push(`Ligne ${lineNumber}: Profession non spécifiée`);
    }
    
    return validation;
}

function extractAgeFromNIP(nip) {
    if (!nip || nip.length < 13) return 0;
    
    try {
        const datePart = nip.substring(7, 15); // YYYYMMDD
        const year = parseInt(datePart.substring(0, 4));
        const month = parseInt(datePart.substring(4, 6)) - 1;
        const day = parseInt(datePart.substring(6, 8));
        
        const birthDate = new Date(year, month, day);
        const today = new Date();
        
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    } catch (error) {
        return 0;
    }
}

// Fonctions communes pour lecture de fichier
function readFile(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            resolve(e.target.result);
        };
        
        reader.onerror = () => {
            reject(new Error('Erreur de lecture du fichier'));
        };
        
        if (file.type.includes('csv')) {
            reader.readAsText(file);
        } else {
            reader.readAsArrayBuffer(file);
        }
    });
}

function parseFileData(fileData, fileType) {
    if (fileType.includes('csv')) {
        return parseCSV(fileData);
    } else {
        return parseExcel(fileData);
    }
}

function parseCSV(csvData) {
    if (typeof Papa !== 'undefined') {
        const results = Papa.parse(csvData, {
            header: true,
            skipEmptyLines: true,
            dynamicTyping: true
        });
        
        return results.data.map(mapRowToAdherent);
    } else {
        throw new Error('Bibliothèque CSV non disponible');
    }
}

function parseExcel(excelData) {
    if (typeof XLSX !== 'undefined') {
        const workbook = XLSX.read(excelData, { type: 'array' });
        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];
        
        const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
        
        if (jsonData.length < 2) {
            throw new Error('Le fichier Excel doit contenir au moins une ligne d\'en-têtes et une ligne de données');
        }
        
        const headers = jsonData[0];
        const rows = jsonData.slice(1);
        
        return rows.map(row => {
            const obj = {};
            headers.forEach((header, index) => {
                obj[header] = row[index];
            });
            return mapRowToAdherent(obj);
        }).filter(adherent => adherent.nom || adherent.prenom);
        
    } else {
        throw new Error('Bibliothèque Excel non disponible');
    }
}

// Sauvegarder automatiquement la configuration lors des changements
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les changements de configuration pour sauvegarde automatique
    const configInputs = document.querySelectorAll('#chunk-size, #chunk-delay, #max-retries');
    configInputs.forEach(input => {
        input.addEventListener('change', saveChunkingConfiguration);
    });
    
    const configCheckboxes = document.querySelectorAll('#strict-validation, #skip-duplicates, #save-anomalies, #parallel-validation, #memory-optimization, #progress-verbose');
    configCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', saveChunkingConfiguration);
    });
});
</script>