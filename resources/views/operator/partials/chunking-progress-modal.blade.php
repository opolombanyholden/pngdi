{{--
============================================================================
CHUNKING-PROGRESS-MODAL.BLADE.PHP - MODAL PROGRESSION CHUNKING
Partial pour confirmation.blade.php - Modal de progression temps réel
Version: 2.0 - Interface complète de monitoring chunking avec contrôles
============================================================================
--}}

<div class="modal-header">
    <div class="w-100 text-center">
        <h4 class="modal-title mb-2">
            <i class="fas fa-cogs me-2"></i>
            Import Massif en Cours
        </h4>
        <p class="modal-subtitle mb-0">
            Traitement par lots adaptatif - <span id="modal-file-name">Fichier.xlsx</span>
        </p>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" 
            id="modal-close-btn" disabled aria-label="Fermer"></button>
</div>

<div class="modal-body">
    <!-- Indicateur de phase global -->
    <div class="chunking-phase-indicator mb-4">
        <div class="d-flex align-items-center justify-content-center">
            <!-- Phase 1: Préparation -->
            <div class="phase-step completed" id="phase-preparation">
                <i class="fas fa-check"></i>
            </div>
            <div class="phase-connector active" id="connector-1"></div>
            
            <!-- Phase 2: Traitement -->
            <div class="phase-step active" id="phase-processing">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="phase-connector pending" id="connector-2"></div>
            
            <!-- Phase 3: Finalisation -->
            <div class="phase-step pending" id="phase-finalization">
                <i class="fas fa-flag-checkered"></i>
            </div>
        </div>
        <div class="phase-labels mt-2">
            <div class="row text-center">
                <div class="col-4">
                    <small class="text-muted">Préparation</small>
                </div>
                <div class="col-4">
                    <small class="text-primary fw-bold">Traitement</small>
                </div>
                <div class="col-4">
                    <small class="text-muted">Finalisation</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Progression globale -->
    <div class="chunking-progress-main mb-4">
        <div class="progress-header">
            <div class="d-flex justify-content-between align-items-center">
                <span class="progress-label fw-bold">Progression Globale</span>
                <span class="progress-percentage h4 text-primary mb-0" id="modal-global-percentage">0%</span>
            </div>
        </div>
        <div class="chunking-progress-bar mb-2">
            <div class="chunking-progress-fill" id="modal-global-progress" style="width: 0%"></div>
        </div>
        <div class="progress-text text-center">
            <span id="modal-global-status">Initialisation du traitement...</span>
        </div>
    </div>

    <!-- Statistiques temps réel -->
    <div class="chunking-stats mb-4">
        <div class="stats-grid-chunking">
            <div class="stat-item-chunking primary">
                <div class="stat-value" id="modal-stat-chunks">0</div>
                <div class="stat-label">Lots Total</div>
            </div>
            <div class="stat-item-chunking success">
                <div class="stat-value" id="modal-stat-completed">0</div>
                <div class="stat-label">Terminés</div>
            </div>
            <div class="stat-item-chunking info">
                <div class="stat-value" id="modal-stat-records">0</div>
                <div class="stat-label">Adhérents</div>
            </div>
            <div class="stat-item-chunking warning">
                <div class="stat-value" id="modal-stat-warnings">0</div>
                <div class="stat-label">Anomalies</div>
            </div>
            <div class="stat-item-chunking danger">
                <div class="stat-value" id="modal-stat-errors">0</div>
                <div class="stat-label">Erreurs</div>
            </div>
            <div class="stat-item-chunking secondary">
                <div class="stat-value" id="modal-stat-time">0s</div>
                <div class="stat-label">Durée</div>
            </div>
        </div>
    </div>

    <!-- Détails du lot en cours -->
    <div class="current-chunk-details mb-4">
        <div class="chunk-header">
            <h6 class="chunk-title">
                <i class="fas fa-layer-group me-2"></i>Lot en Cours de Traitement
            </h6>
            <span class="chunk-number" id="modal-current-chunk">Lot 1/10</span>
        </div>
        
        <div class="chunk-progress mb-3">
            <div class="chunk-progress-fill" id="modal-chunk-progress" style="width: 0%"></div>
        </div>
        
        <div class="chunk-details-grid">
            <div class="chunk-detail-item">
                <div class="chunk-detail-value" id="modal-chunk-processed">0</div>
                <div class="chunk-detail-label">Traités</div>
            </div>
            <div class="chunk-detail-item">
                <div class="chunk-detail-value" id="modal-chunk-total">100</div>
                <div class="chunk-detail-label">Total</div>
            </div>
            <div class="chunk-detail-item">
                <div class="chunk-detail-value" id="modal-chunk-speed">0/s</div>
                <div class="chunk-detail-label">Vitesse</div>
            </div>
            <div class="chunk-detail-item">
                <div class="chunk-detail-value" id="modal-chunk-eta">-</div>
                <div class="chunk-detail-label">ETA</div>
            </div>
        </div>
    </div>

    <!-- Log en temps réel -->
    <div class="chunking-log mb-4">
        <div class="log-header d-flex justify-content-between align-items-center mb-2">
            <h6 class="text-light mb-0">
                <i class="fas fa-terminal me-2"></i>Journal d'Activité
            </h6>
            <div class="log-controls">
                <button type="button" class="btn btn-sm btn-outline-light" onclick="clearLog()" title="Effacer">
                    <i class="fas fa-trash"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleAutoScroll()" 
                        id="auto-scroll-btn" title="Auto-scroll">
                    <i class="fas fa-arrow-down"></i>
                </button>
            </div>
        </div>
        <div class="log-content" id="modal-log-content">
            <div class="log-entry">
                <span class="log-timestamp">[00:00:00]</span>
                <span class="log-level-info">INFO</span>
                Initialisation du système de chunking...
            </div>
        </div>
    </div>

    <!-- Contrôles avancés -->
    <div class="chunking-controls">
        <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-chunking-pause" onclick="pauseModalChunking()" id="modal-pause-btn">
                <i class="fas fa-pause me-2"></i>Pause
            </button>
            <button type="button" class="btn btn-chunking-resume" onclick="resumeModalChunking()" 
                    id="modal-resume-btn" style="display: none;">
                <i class="fas fa-play me-2"></i>Reprendre
            </button>
            <button type="button" class="btn btn-chunking-cancel" onclick="cancelModalChunking()" id="modal-cancel-btn">
                <i class="fas fa-stop me-2"></i>Annuler
            </button>
        </div>
        
        <!-- Options supplémentaires -->
        <div class="mt-3 text-center">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="modal-verbose-mode">
                <label class="form-check-label" for="modal-verbose-mode">
                    Mode détaillé
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="modal-sound-notifications" checked>
                <label class="form-check-label" for="modal-sound-notifications">
                    Notifications sonores
                </label>
            </div>
        </div>
    </div>

    <!-- Zone d'erreurs critiques -->
    <div class="chunking-errors" id="modal-chunking-errors">
        <div class="errors-header">
            <i class="errors-icon fas fa-exclamation-triangle"></i>
            <h6 class="errors-title">Erreurs Critiques Détectées</h6>
        </div>
        <div class="errors-list" id="modal-errors-list">
            <!-- Les erreurs seront ajoutées dynamiquement -->
        </div>
    </div>
</div>

<div class="modal-footer">
    <div class="w-100">
        <!-- Barre de statut -->
        <div class="status-bar mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="status-info">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        <span id="modal-status-message">Traitement en cours...</span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="status-details">
                        <small class="text-muted">
                            Démarré à <span id="modal-start-time">--:--</span> |
                            ETA: <span id="modal-eta-time">Calcul...</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions finales -->
        <div class="final-actions text-center" id="modal-final-actions" style="display: none;">
            <div class="alert alert-success mb-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Import terminé avec succès !</strong>
                Consultez les résultats détaillés ci-dessous.
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary" onclick="downloadModalReport()">
                    <i class="fas fa-file-pdf me-2"></i>Rapport Détaillé
                </button>
                <button type="button" class="btn btn-outline-warning" onclick="viewModalAnomalies()">
                    <i class="fas fa-exclamation-triangle me-2"></i>Voir Anomalies
                </button>
                <button type="button" class="btn btn-success" onclick="finalizeModalImport()">
                    <i class="fas fa-check-double me-2"></i>Finaliser Import
                </button>
            </div>
        </div>
        
        <!-- Actions pendant traitement -->
        <div class="processing-actions text-center" id="modal-processing-actions">
            <small class="text-muted">
                <i class="fas fa-lightbulb me-1"></i>
                Astuce: Vous pouvez minimiser cette fenêtre, le traitement continuera en arrière-plan.
            </small>
        </div>
    </div>
</div>

{{-- JavaScript spécifique au modal de progression --}}
<script>
// Variables globales pour le modal
let modalChunkingState = {
    isActive: false,
    isPaused: false,
    startTime: null,
    lastUpdateTime: null,
    autoScroll: true,
    soundEnabled: true,
    verboseMode: false,
    logEntries: []
};

/**
 * Initialiser le modal de chunking
 */
function initializeChunkingModal() {
    console.log('🎬 Initialisation modal chunking');
    
    const modal = document.getElementById('chunkingProgressModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', onModalShown);
        modal.addEventListener('hidden.bs.modal', onModalHidden);
    }
    
    // Initialiser les contrôles
    setupModalControls();
    
    // Désactiver la fermeture pendant le traitement
    const closeBtn = document.getElementById('modal-close-btn');
    if (closeBtn) {
        closeBtn.disabled = true;
    }
}

/**
 * Configurer les contrôles du modal
 */
function setupModalControls() {
    // Mode détaillé
    const verboseCheckbox = document.getElementById('modal-verbose-mode');
    if (verboseCheckbox) {
        verboseCheckbox.addEventListener('change', (e) => {
            modalChunkingState.verboseMode = e.target.checked;
            toggleVerboseMode(e.target.checked);
        });
    }
    
    // Notifications sonores
    const soundCheckbox = document.getElementById('modal-sound-notifications');
    if (soundCheckbox) {
        soundCheckbox.addEventListener('change', (e) => {
            modalChunkingState.soundEnabled = e.target.checked;
        });
    }
}

/**
 * Démarrer le modal de chunking
 */
function startChunkingModal(fileName, totalRecords, totalChunks) {
    const modal = new bootstrap.Modal(document.getElementById('chunkingProgressModal'), {
        backdrop: 'static',
        keyboard: false
    });
    
    // Mettre à jour les informations initiales
    const fileNameEl = document.getElementById('modal-file-name');
    if (fileNameEl) fileNameEl.textContent = fileName;
    
    updateModalStat('modal-stat-chunks', totalChunks);
    updateModalStat('modal-stat-records', 0);
    
    modalChunkingState.isActive = true;
    modalChunkingState.startTime = Date.now();
    
    // Afficher le modal
    modal.show();
    
    // Log initial
    addModalLogEntry('Démarrage de l\'import massif', 'info');
    addModalLogEntry(`Fichier: ${fileName}`, 'info');
    addModalLogEntry(`${totalRecords} adhérents à traiter en ${totalChunks} lots`, 'info');
}

/**
 * Mettre à jour la progression du modal
 */
function updateModalProgress(progress) {
    // Progression globale
    const globalProgressBar = document.getElementById('modal-global-progress');
    const globalPercentage = document.getElementById('modal-global-percentage');
    const globalStatus = document.getElementById('modal-global-status');
    
    if (globalProgressBar) {
        globalProgressBar.style.width = progress.percentage + '%';
    }
    if (globalPercentage) {
        globalPercentage.textContent = Math.round(progress.percentage) + '%';
    }
    if (globalStatus) {
        globalStatus.textContent = progress.status;
    }
    
    // Mettre à jour les phases
    updateModalPhases(progress.percentage);
    
    // Statistiques
    updateModalStat('modal-stat-completed', progress.completedChunks);
    updateModalStat('modal-stat-records', progress.processedRecords);
    updateModalStat('modal-stat-warnings', progress.warnings);
    updateModalStat('modal-stat-errors', progress.errors);
    
    // Temps écoulé
    if (modalChunkingState.startTime) {
        const elapsed = Math.floor((Date.now() - modalChunkingState.startTime) / 1000);
        updateModalStat('modal-stat-time', formatDuration(elapsed));
        
        // ETA
        if (progress.percentage > 0) {
            const totalEstimated = (elapsed / progress.percentage) * 100;
            const remaining = Math.max(0, totalEstimated - elapsed);
            updateModalETA(remaining);
        }
    }
    
    // Log de progression si mode détaillé
    if (modalChunkingState.verboseMode) {
        addModalLogEntry(`Progression: ${Math.round(progress.percentage)}% (${progress.processedRecords} adhérents)`, 'info');
    }
    
    modalChunkingState.lastUpdateTime = Date.now();
}

/**
 * Mettre à jour les détails du chunk en cours
 */
function updateModalChunkDetails(chunkInfo) {
    const chunkNumber = document.getElementById('modal-current-chunk');
    const chunkProgress = document.getElementById('modal-chunk-progress');
    const chunkProcessed = document.getElementById('modal-chunk-processed');
    const chunkTotal = document.getElementById('modal-chunk-total');
    const chunkSpeed = document.getElementById('modal-chunk-speed');
    
    if (chunkNumber) {
        chunkNumber.textContent = `Lot ${chunkInfo.number}/${chunkInfo.total}`;
    }
    
    if (chunkProgress) {
        const percentage = (chunkInfo.processed / chunkInfo.size) * 100;
        chunkProgress.style.width = percentage + '%';
    }
    
    if (chunkProcessed) chunkProcessed.textContent = chunkInfo.processed;
    if (chunkTotal) chunkTotal.textContent = chunkInfo.size;
    
    // Calculer la vitesse
    if (chunkSpeed && chunkInfo.startTime) {
        const elapsed = (Date.now() - chunkInfo.startTime) / 1000;
        const speed = elapsed > 0 ? Math.round(chunkInfo.processed / elapsed) : 0;
        chunkSpeed.textContent = speed + '/s';
    }
    
    // Log détaillé du chunk
    if (modalChunkingState.verboseMode && chunkInfo.processed === 0) {
        addModalLogEntry(`Début traitement lot ${chunkInfo.number} (${chunkInfo.size} adhérents)`, 'info');
    }
}

/**
 * Finaliser le chunk en cours
 */
function completeModalChunk(chunkResult) {
    const chunkProgress = document.getElementById('modal-chunk-progress');
    if (chunkProgress) {
        chunkProgress.style.width = '100%';
    }
    
    // Log de fin de chunk
    addModalLogEntry(`Lot ${chunkResult.number} terminé: ${chunkResult.processed}/${chunkResult.total} adhérents`, 'success');
    
    if (chunkResult.errors > 0) {
        addModalLogEntry(`${chunkResult.errors} erreur(s) dans le lot ${chunkResult.number}`, 'warning');
    }
    
    // Son de notification si activé
    if (modalChunkingState.soundEnabled) {
        playNotificationSound('chunk-complete');
    }
}

/**
 * Mettre à jour les phases du processus
 */
function updateModalPhases(percentage) {
    const phasePreparation = document.getElementById('phase-preparation');
    const phaseProcessing = document.getElementById('phase-processing');
    const phaseFinalization = document.getElementById('phase-finalization');
    const connector1 = document.getElementById('connector-1');
    const connector2 = document.getElementById('connector-2');
    
    if (percentage >= 100) {
        // Phase de finalisation
        if (phaseProcessing) {
            phaseProcessing.classList.remove('active');
            phaseProcessing.classList.add('completed');
            phaseProcessing.innerHTML = '<i class="fas fa-check"></i>';
        }
        if (phaseFinalization) {
            phaseFinalization.classList.remove('pending');
            phaseFinalization.classList.add('active');
        }
        if (connector2) {
            connector2.classList.remove('pending');
            connector2.classList.add('active');
        }
    } else if (percentage > 0) {
        // Phase de traitement active
        if (connector1) {
            connector1.classList.add('active');
        }
    }
}

/**
 * Terminer le modal avec succès
 */
function completeChunkingModal(finalResults) {
    modalChunkingState.isActive = false;
    
    // Mettre à jour la progression finale
    updateModalProgress({
        percentage: 100,
        status: 'Import terminé avec succès !',
        completedChunks: finalResults.totalChunks,
        processedRecords: finalResults.imported,
        warnings: finalResults.anomalies,
        errors: finalResults.errors
    });
    
    // Finaliser les phases
    updateModalPhases(100);
    
    // Masquer les actions de traitement et afficher les actions finales
    const processingActions = document.getElementById('modal-processing-actions');
    const finalActions = document.getElementById('modal-final-actions');
    
    if (processingActions) processingActions.style.display = 'none';
    if (finalActions) finalActions.style.display = 'block';
    
    // Réactiver le bouton de fermeture
    const closeBtn = document.getElementById('modal-close-btn');
    if (closeBtn) {
        closeBtn.disabled = false;
    }
    
    // Log final
    addModalLogEntry('Import massif terminé avec succès !', 'success');
    addModalLogEntry(`Résultat final: ${finalResults.imported} adhérents importés`, 'success');
    
    if (finalResults.anomalies > 0) {
        addModalLogEntry(`${finalResults.anomalies} anomalie(s) détectée(s)`, 'warning');
    }
    
    // Notification sonore finale
    if (modalChunkingState.soundEnabled) {
        playNotificationSound('import-complete');
    }
    
    // Notification navigateur si l'onglet n'est pas actif
    if (document.hidden) {
        showBrowserNotification('Import terminé', `${finalResults.imported} adhérents importés avec succès`);
    }
}

/**
 * Gérer les erreurs du modal
 */
function handleModalError(error) {
    // Afficher l'erreur dans la zone dédiée
    const errorsDiv = document.getElementById('modal-chunking-errors');
    const errorsList = document.getElementById('modal-errors-list');
    
    if (errorsList) {
        const errorItem = document.createElement('div');
        errorItem.className = 'error-item';
        errorItem.innerHTML = `
            <div class="error-chunk">Erreur Critique</div>
            <div class="error-message">${error.message}</div>
        `;
        errorsList.appendChild(errorItem);
    }
    
    if (errorsDiv) {
        errorsDiv.classList.add('show');
    }
    
    // Log de l'erreur
    addModalLogEntry(`ERREUR: ${error.message}`, 'error');
    
    // Son d'alerte
    if (modalChunkingState.soundEnabled) {
        playNotificationSound('error');
    }
}

/**
 * Ajouter une entrée au log
 */
function addModalLogEntry(message, level = 'info') {
    const logContent = document.getElementById('modal-log-content');
    if (!logContent) return;
    
    const timestamp = new Date().toLocaleTimeString();
    const entry = {
        timestamp: timestamp,
        level: level,
        message: message,
        time: Date.now()
    };
    
    modalChunkingState.logEntries.push(entry);
    
    const logEntry = document.createElement('div');
    logEntry.className = 'log-entry new';
    logEntry.innerHTML = `
        <span class="log-timestamp">[${timestamp}]</span>
        <span class="log-level-${level}">${level.toUpperCase()}</span>
        ${message}
    `;
    
    logContent.appendChild(logEntry);
    
    // Auto-scroll si activé
    if (modalChunkingState.autoScroll) {
        logContent.scrollTop = logContent.scrollHeight;
    }
    
    // Limiter le nombre d'entrées (garder seulement les 100 dernières)
    if (modalChunkingState.logEntries.length > 100) {
        modalChunkingState.logEntries.shift();
        const firstEntry = logContent.firstChild;
        if (firstEntry) {
            logContent.removeChild(firstEntry);
        }
    }
    
    // Supprimer la classe 'new' après animation
    setTimeout(() => {
        logEntry.classList.remove('new');
    }, 1000);
}

/**
 * Contrôles du modal
 */
function pauseModalChunking() {
    modalChunkingState.isPaused = true;
    
    const pauseBtn = document.getElementById('modal-pause-btn');
    const resumeBtn = document.getElementById('modal-resume-btn');
    
    if (pauseBtn) pauseBtn.style.display = 'none';
    if (resumeBtn) resumeBtn.style.display = 'inline-block';
    
    addModalLogEntry('Import mis en pause par l\'utilisateur', 'warning');
    
    // Déléguer la pause au système principal
    if (window.ChunkingEngine && window.ChunkingEngine.pause) {
        window.ChunkingEngine.pause();
    }
}

function resumeModalChunking() {
    modalChunkingState.isPaused = false;
    
    const pauseBtn = document.getElementById('modal-pause-btn');
    const resumeBtn = document.getElementById('modal-resume-btn');
    
    if (pauseBtn) pauseBtn.style.display = 'inline-block';
    if (resumeBtn) resumeBtn.style.display = 'none';
    
    addModalLogEntry('Import repris', 'info');
    
    // Déléguer la reprise au système principal
    if (window.ChunkingEngine && window.ChunkingEngine.resume) {
        window.ChunkingEngine.resume();
    }
}

function cancelModalChunking() {
    if (!confirm('Êtes-vous sûr de vouloir annuler l\'import ? Cette action est irréversible.')) {
        return;
    }
    
    modalChunkingState.isActive = false;
    
    addModalLogEntry('Import annulé par l\'utilisateur', 'error');
    
    // Déléguer l'annulation au système principal
    if (window.ChunkingEngine && window.ChunkingEngine.stop) {
        window.ChunkingEngine.stop();
    }
    
    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('chunkingProgressModal'));
    if (modal) {
        modal.hide();
    }
}

/**
 * Fonctions utilitaires du modal
 */
function updateModalStat(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

function updateModalETA(remainingSeconds) {
    const etaElement = document.getElementById('modal-eta-time');
    if (etaElement) {
        etaElement.textContent = formatDuration(remainingSeconds);
    }
    
    const chunkEta = document.getElementById('modal-chunk-eta');
    if (chunkEta) {
        chunkEta.textContent = formatDuration(Math.min(remainingSeconds, 60));
    }
}

function clearLog() {
    const logContent = document.getElementById('modal-log-content');
    if (logContent) {
        logContent.innerHTML = '';
        modalChunkingState.logEntries = [];
    }
}

function toggleAutoScroll() {
    modalChunkingState.autoScroll = !modalChunkingState.autoScroll;
    
    const autoScrollBtn = document.getElementById('auto-scroll-btn');
    if (autoScrollBtn) {
        if (modalChunkingState.autoScroll) {
            autoScrollBtn.classList.add('active');
            autoScrollBtn.title = 'Auto-scroll activé';
        } else {
            autoScrollBtn.classList.remove('active');
            autoScrollBtn.title = 'Auto-scroll désactivé';
        }
    }
}

function toggleVerboseMode(enabled) {
    if (enabled) {
        addModalLogEntry('Mode détaillé activé', 'info');
    } else {
        addModalLogEntry('Mode détaillé désactivé', 'info');
    }
}

/**
 * Actions finales du modal
 */
function downloadModalReport() {
    addModalLogEntry('Génération du rapport détaillé...', 'info');
    // TODO: Implémenter la génération de rapport
}

function viewModalAnomalies() {
    addModalLogEntry('Consultation des anomalies...', 'info');
    // TODO: Implémenter la consultation des anomalies
}

function finalizeModalImport() {
    addModalLogEntry('Finalisation de l\'import...', 'info');
    
    // Déléguer la finalisation
    if (window.ConfirmationApp && window.ConfirmationApp.updateStatistics) {
        window.ConfirmationApp.updateStatistics();
    }
    
    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('chunkingProgressModal'));
    if (modal) {
        modal.hide();
    }
}

/**
 * Gestionnaires d'événements du modal
 */
function onModalShown() {
    console.log('🎬 Modal chunking affiché');
    
    // Démarrer les mises à jour du timer
    if (modalChunkingState.startTime) {
        const startTimeEl = document.getElementById('modal-start-time');
        if (startTimeEl) {
            startTimeEl.textContent = new Date(modalChunkingState.startTime).toLocaleTimeString();
        }
    }
}

function onModalHidden() {
    console.log('🎬 Modal chunking masqué');
    
    // Nettoyer l'état si nécessaire
    if (!modalChunkingState.isActive) {
        modalChunkingState = {
            isActive: false,
            isPaused: false,
            startTime: null,
            lastUpdateTime: null,
            autoScroll: true,
            soundEnabled: true,
            verboseMode: false,
            logEntries: []
        };
    }
}

/**
 * Fonctions audio et notifications
 */
function playNotificationSound(type) {
    try {
        let frequency, duration;
        
        switch (type) {
            case 'chunk-complete':
                frequency = 800;
                duration = 100;
                break;
            case 'import-complete':
                frequency = 1000;
                duration = 300;
                break;
            case 'error':
                frequency = 400;
                duration = 500;
                break;
            default:
                return;
        }
        
        // Créer un son simple avec Web Audio API
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = frequency;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration / 1000);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + duration / 1000);
        
    } catch (error) {
        console.warn('Son non disponible:', error);
    }
}

function showBrowserNotification(title, body) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, {
            body: body,
            icon: '/favicon.ico'
        });
    }
}

/**
 * Fonctions utilitaires
 */
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

// Initialiser le modal au chargement
document.addEventListener('DOMContentLoaded', function() {
    initializeChunkingModal();
});
</script>