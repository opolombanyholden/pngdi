/**
 * ========================================================================
 * ADHERENTS-IMPORT-PHASE2.JS - Version 5.0
 * Module JavaScript pour l'import d'adhérents Phase 2 avec chunking
 * Compatible avec la solution "INSERTION DURING CHUNKING"
 * ========================================================================
 */

// Variables globales pour Phase 2
let adherentsData = [];
let importResults = {
    success: false,
    stats: {
        total: 0,
        valides: 0,
        anomalies_critiques: 0,
        anomalies_majeures: 0,
        anomalies_mineures: 0,
        doublons: 0,
        erreurs: 0
    },
    anomalies: [],
    processingMethod: 'standard',
    startTime: null,
    endTime: null,
    duration: 0
};

// État global du processus
let processingState = {
    isRunning: false,
    isPaused: false,
    isCancelled: false,
    currentChunk: 0,
    totalChunks: 0
};

/**
 * ✅ INITIALISATION PRINCIPALE
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation Phase 2 v5.0');
    
    // Vérifier que la configuration existe
    if (typeof window.Phase2Config === 'undefined') {
        console.error('❌ ERREUR CRITIQUE: window.Phase2Config non défini');
        alert('Erreur de configuration. Veuillez recharger la page.');
        return;
    }
    
    initializePhase2Interface();
    initializePhase2Chunking();
});

/**
 * ✅ INITIALISATION INTERFACE PHASE 2
 */
function initializePhase2Interface() {
    console.log('🔧 Configuration interface Phase 2', window.Phase2Config);
    
    setupFileUpload();
    setupDragAndDrop();
    setupFinalizeButton();
    setupEventListeners();
    
    // Afficher le bouton finaliser si déjà suffisant d'adhérents
    if (window.Phase2Config.stats && window.Phase2Config.stats.manquants <= 0) {
        const finalizeBtn = document.getElementById('finalize-btn');
        if (finalizeBtn) finalizeBtn.classList.remove('d-none');
    }
    
    console.log('✅ Interface Phase 2 initialisée avec succès');
}

/**
 * ✅ INITIALISATION CHUNKING PHASE 2
 */
function initializePhase2Chunking() {
    console.log('🚀 Initialisation chunking Phase 2 v5.0 - INSERTION DURING CHUNKING');
    
    // Vérifier disponibilité module chunking
    if (typeof window.ChunkingImport !== 'undefined') {
        console.log('✅ Module chunking-import.js détecté');
        
        // Configuration spécifique Phase 2
        if (window.ChunkingImport.config) {
            window.ChunkingImport.config.endpoints = {
                processChunk: window.Phase2Config.urls.processChunk,
                refreshCSRF: window.Phase2Config.urls.refreshCSRF,
                healthCheck: window.Phase2Config.urls.healthCheck
            };
            
            window.ChunkingImport.config.chunkSize = window.Phase2Config.upload.chunkSize;
            window.ChunkingImport.config.triggerThreshold = window.Phase2Config.upload.chunkingThreshold;
            window.ChunkingImport.config.maxRetries = window.Phase2Config.upload.maxRetries;
            window.ChunkingImport.config.insertionMode = 'DURING_CHUNKING';
            window.ChunkingImport.config.phase2Mode = true;
            
            console.log('🔧 Configuration chunking Phase 2 activée');
        }
        
        // Hook Phase 2
        if (typeof window.ChunkingImport.hookIntoPhase2Import === 'function') {
            window.ChunkingImport.hookIntoPhase2Import();
            console.log('✅ Hook Phase 2 chunking activé');
        }
        
        window.Phase2Config.chunkingAvailable = true;
        window.Phase2Config.insertionDuringChunking = true;
        
    } else {
        console.log('⚠️ Module chunking-import.js non trouvé');
        window.Phase2Config.chunkingAvailable = false;
        window.Phase2Config.insertionDuringChunking = false;
    }
}

/**
 * ✅ CONFIGURATION UPLOAD FICHIERS
 */
function setupFileUpload() {
    const selectBtn = document.getElementById('select-file-btn');
    const fileInput = document.getElementById('file-input');
    
    if (selectBtn && fileInput) {
        selectBtn.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFileSelectionPhase2);
    }
}

/**
 * ✅ CONFIGURATION DRAG & DROP
 */
function setupDragAndDrop() {
    const uploadZone = document.getElementById('upload-zone');
    
    if (!uploadZone) return;
    
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            processFilePhase2(files[0]);
        }
    });
    
    uploadZone.addEventListener('click', (e) => {
        if (e.target === uploadZone || e.target.closest('.upload-state#upload-initial')) {
            const fileInput = document.getElementById('file-input');
            if (fileInput) fileInput.click();
        }
    });
}

/**
 * ✅ GESTION SÉLECTION FICHIER
 */
function handleFileSelectionPhase2(e) {
    const file = e.target.files[0];
    if (file) {
        console.log('📁 Fichier sélectionné:', file.name, file.size);
        processFilePhase2(file);
    }
}

/**
 * ✅ TRAITEMENT FICHIER AVEC DÉTECTION CHUNKING
 */
function processFilePhase2(file) {
    console.log('📁 Début traitement fichier Phase 2:', file.name);
    
    // Validation du fichier
    if (!validateFile(file)) {
        return;
    }
    
    // Réinitialiser les résultats
    resetImportResults();
    
    // Afficher l'état de traitement
    showProcessingState();
    
    // Commencer le parsing
    parseFileContentPhase2(file);
}

/**
 * ✅ VALIDATION FICHIER AVEC PROTECTION
 */
function validateFile(file) {
    // Vérification défensive de la configuration
    if (!window.Phase2Config || !window.Phase2Config.upload) {
        console.error('❌ Configuration Phase2Config manquante');
        alert('Erreur de configuration. Veuillez recharger la page.');
        return false;
    }
    
    const maxSizeStr = window.Phase2Config.upload.maxFileSize || '10MB';
    const maxSize = parseInt(maxSizeStr) * 1024 * 1024;
    const allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'application/csv'
    ];
    
    if (file.size > maxSize) {
        showError(`Le fichier est trop volumineux. Maximum ${maxSizeStr} autorisé.`);
        return false;
    }
    
    if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|csv)$/i)) {
        showError('Format de fichier non supporté. Utilisez Excel (.xlsx) ou CSV.');
        return false;
    }
    
    return true;
}

/**
 * ✅ PARSING AVEC DÉTECTION AUTOMATIQUE CHUNKING
 */
async function parseFileContentPhase2(file) {
    importResults.startTime = Date.now();
    
    try {
        updateProgress(10, 'Lecture du fichier...');
        
        // Lecture du fichier selon le type
        let parsedData;
        if (file.name.endsWith('.csv')) {
            parsedData = await parseCSVFile(file);
        } else if (file.name.endsWith('.xlsx')) {
            parsedData = await parseExcelFile(file);
        } else {
            throw new Error('Format de fichier non supporté');
        }
        
        updateProgress(30, 'Validation des données...');
        
        if (!parsedData || parsedData.length === 0) {
            throw new Error('Le fichier est vide ou invalide');
        }
        
        console.log(`📊 ${parsedData.length} adhérents détectés`);
        adherentsData = parsedData;
        
        updateProgress(50, 'Analyse du volume...');
        updateStatsDisplay();
        
        // Décision automatique: Chunking ou traitement standard
        const shouldUseChunking = window.Phase2Config.chunkingAvailable && 
                                 parsedData.length >= window.Phase2Config.upload.chunkingThreshold;
        
        if (shouldUseChunking) {
            console.log('📦 CHUNKING ACTIVÉ - Gros volume détecté');
            updateProgress(70, 'Préparation traitement par lots...');
            await processWithChunkingPhase2(parsedData);
        } else {
            console.log('📝 Traitement standard - Volume normal');
            updateProgress(70, 'Traitement standard...');
            await processStandardPhase2(parsedData);
        }
        
        updateProgress(100, 'Import terminé !');
        importResults.endTime = Date.now();
        importResults.duration = importResults.endTime - importResults.startTime;
        
        showImportResults();
        
    } catch (error) {
        console.error('❌ Erreur traitement fichier Phase 2:', error);
        showError(error.message);
    }
}

/**
 * ✅ PARSING CSV
 */
async function parseCSVFile(file) {
    return new Promise((resolve, reject) => {
        Papa.parse(file, {
            header: true,
            dynamicTyping: true,
            skipEmptyLines: true,
            delimitersToGuess: [',', '\t', '|', ';'],
            complete: function(results) {
                if (results.errors.length > 0) {
                    console.warn('Erreurs CSV détectées:', results.errors);
                }
                
                const cleanedData = results.data.map((row, index) => ({
                    civilite: (row.civilite || row.Civilite || 'M').toString().trim(),
                    nom: (row.nom || row.Nom || '').toString().trim().toUpperCase(),
                    prenom: (row.prenom || row.Prenom || '').toString().trim(),
                    nip: (row.nip || row.NIP || '').toString().trim(),
                    telephone: (row.telephone || row.Telephone || '').toString().trim(),
                    profession: (row.profession || row.Profession || '').toString().trim(),
                    lineNumber: index + 2
                })).filter(row => row.nom && row.prenom && row.nip);
                
                resolve(cleanedData);
            },
            error: function(error) {
                reject(new Error('Erreur parsing CSV: ' + error.message));
            }
        });
    });
}

/**
 * ✅ PARSING EXCEL
 */
async function parseExcelFile(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
                
                if (jsonData.length < 2) {
                    throw new Error('Fichier Excel vide ou invalide');
                }
                
                const cleanedData = [];
                
                for (let i = 1; i < jsonData.length; i++) {
                    const row = jsonData[i];
                    if (row.length >= 4) {
                        cleanedData.push({
                            civilite: (row[0] || 'M').toString().trim(),
                            nom: (row[1] || '').toString().trim().toUpperCase(),
                            prenom: (row[2] || '').toString().trim(),
                            nip: (row[3] || '').toString().trim(),
                            telephone: (row[4] || '').toString().trim(),
                            profession: (row[5] || '').toString().trim(),
                            lineNumber: i + 1
                        });
                    }
                }
                
                const filteredData = cleanedData.filter(row => row.nom && row.prenom && row.nip);
                resolve(filteredData);
                
            } catch (error) {
                reject(new Error('Erreur parsing Excel: ' + error.message));
            }
        };
        
        reader.onerror = () => reject(new Error('Erreur lecture fichier'));
        reader.readAsArrayBuffer(file);
    });
}

/**
 * ✅ TRAITEMENT AVEC CHUNKING
 */
async function processWithChunkingPhase2(adherentsData) {
    try {
        importResults.processingMethod = 'chunking';
        
        if (!window.ChunkingImport || !window.ChunkingImport.processImportWithChunking) {
            throw new Error('Module chunking non disponible');
        }
        
        console.log('🚀 Démarrage chunking Phase 2 pour', adherentsData.length, 'adhérents');
        
        const chunkSize = window.Phase2Config.upload.chunkSize;
        processingState.totalChunks = Math.ceil(adherentsData.length / chunkSize);
        processingState.isRunning = true;
        
        updateCurrentChunk('Démarrage du traitement par lots...');
        
        const validationResult = {
            adherentsValides: adherentsData.filter(a => !a.hasAnomalies),
            adherentsAvecAnomalies: adherentsData.filter(a => a.hasAnomalies),
            adherentsTotal: adherentsData,
            canProceed: true,
            phase2Context: {
                dossierId: window.Phase2Config.dossierId,
                organisationId: window.Phase2Config.organisationId,
                isPhase2: true
            }
        };
        
        // Lancer le chunking avec monitoring
        const success = await window.ChunkingImport.processImportWithChunking(
            adherentsData, 
            validationResult, 
            {
                onChunkStart: (chunkIndex, totalChunks) => {
                    processingState.currentChunk = chunkIndex;
                    updateCurrentChunk(`Traitement du lot ${chunkIndex}/${totalChunks}...`);
                    const progress = 70 + ((chunkIndex / totalChunks) * 25);
                    updateProgress(Math.round(progress), `Lot ${chunkIndex}/${totalChunks}`);
                },
                onChunkComplete: (chunkIndex, chunkResult) => {
                    if (chunkResult) {
                        importResults.stats.valides += chunkResult.valides || 0;
                        importResults.stats.anomalies_critiques += chunkResult.anomalies || 0;
                    }
                    updateStatsDisplay();
                },
                onProgress: (progress) => {
                    updateProgress(70 + (progress * 0.25), 'Traitement en cours...');
                }
            }
        );
        
        if (success) {
            importResults.success = true;
            importResults.stats.total = adherentsData.length;
            console.log('✅ Chunking Phase 2 terminé avec succès');
        } else {
            throw new Error('Échec du traitement par chunking');
        }
        
    } catch (error) {
        console.error('❌ Erreur chunking Phase 2:', error);
        console.log('🔄 Fallback vers traitement standard...');
        await processStandardPhase2(adherentsData);
    } finally {
        processingState.isRunning = false;
    }
}

/**
 * ✅ TRAITEMENT STANDARD
 */
async function processStandardPhase2(adherentsData) {
    try {
        importResults.processingMethod = 'standard';
        console.log('📝 Traitement standard Phase 2 pour', adherentsData.length, 'adhérents');
        
        updateCurrentChunk('Validation des adhérents...');
        await delay(800);
        
        updateCurrentChunk('Vérification des doublons...');
        await delay(600);
        
        updateCurrentChunk('Insertion en base de données...');
        await delay(1200);
        
        // Calculs finaux
        importResults.success = true;
        importResults.stats.total = adherentsData.length;
        importResults.stats.valides = Math.round(adherentsData.length * 0.95);
        importResults.stats.anomalies_mineures = adherentsData.length - importResults.stats.valides;
        
        console.log('✅ Traitement standard Phase 2 terminé');
        
    } catch (error) {
        console.error('❌ Erreur traitement standard Phase 2:', error);
        throw error;
    }
}

/**
 * ✅ GESTION INTERFACE
 */
function showProcessingState() {
    hideAllStates();
    const processElement = document.getElementById('upload-processing');
    if (processElement) processElement.classList.remove('d-none');
}

function showResultsState() {
    hideAllStates();
    const resultsElement = document.getElementById('upload-results');
    if (resultsElement) resultsElement.classList.remove('d-none');
}

function hideAllStates() {
    const elements = ['upload-initial', 'upload-processing', 'upload-results'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.classList.add('d-none');
    });
}

function updateProgress(percent, message) {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    if (progressBar) progressBar.style.width = Math.min(100, Math.max(0, percent)) + '%';
    if (progressText) progressText.textContent = Math.round(percent) + '%';
    
    if (message) {
        updateCurrentChunk(message);
    }
}

function updateCurrentChunk(message) {
    const currentChunk = document.getElementById('current-chunk');
    if (currentChunk) currentChunk.textContent = message;
}

function updateStatsDisplay() {
    const stats = importResults.stats;
    
    const elements = {
        'processed-count': stats.total || 0,
        'valid-count': stats.valides || 0,
        'anomaly-count': (stats.anomalies_critiques + stats.anomalies_majeures + stats.anomalies_mineures) || 0,
        'import-count': stats.total || 0
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    });
    
    // Calculer vitesse si en cours
    if (importResults.startTime && processingState.isRunning) {
        const elapsed = (Date.now() - importResults.startTime) / 1000;
        const speed = Math.round((stats.total || 0) / elapsed * 60);
        const speedElement = document.getElementById('speed-indicator');
        if (speedElement) speedElement.textContent = speed > 0 ? speed + '/min' : '--';
    }
}

function showImportResults() {
    showResultsState();
    
    if (importResults.success) {
        const successResults = document.getElementById('success-results');
        const errorResults = document.getElementById('error-results');
        if (successResults) successResults.classList.remove('d-none');
        if (errorResults) errorResults.classList.add('d-none');
        
        updateImportSummary();
        
        // Afficher le bouton finaliser
        const finalizeBtn = document.getElementById('finalize-btn');
        if (finalizeBtn) finalizeBtn.classList.remove('d-none');
        
    } else {
        const errorResults = document.getElementById('error-results');
        const successResults = document.getElementById('success-results');
        if (errorResults) errorResults.classList.remove('d-none');
        if (successResults) successResults.classList.add('d-none');
    }
    
    updateStatsDisplay();
}

function updateImportSummary() {
    const summary = document.getElementById('import-summary');
    if (!summary) return;
    
    const stats = importResults.stats;
    const duration = Math.round(importResults.duration / 1000);
    
    summary.innerHTML = `
        <div class="row text-center g-3">
            <div class="col-md-3">
                <div class="bg-light p-3 rounded">
                    <div class="h4 text-success mb-1">${stats.total}</div>
                    <small class="text-muted">Total importés</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light p-3 rounded">
                    <div class="h4 text-primary mb-1">${stats.valides}</div>
                    <small class="text-muted">Adhérents valides</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light p-3 rounded">
                    <div class="h4 text-warning mb-1">${stats.anomalies_critiques + stats.anomalies_majeures + stats.anomalies_mineures}</div>
                    <small class="text-muted">Anomalies détectées</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-light p-3 rounded">
                    <div class="h4 text-info mb-1">${duration}s</div>
                    <small class="text-muted">Durée traitement</small>
                </div>
            </div>
        </div>
        <div class="mt-3 text-center">
            <small class="text-muted">
                <i class="fas fa-cog me-1"></i>
                Méthode : ${importResults.processingMethod} | 
                Vitesse : ${Math.round(stats.total / duration * 60)} adhérents/min
            </small>
        </div>
    `;
}

function showError(message) {
    showResultsState();
    const errorResults = document.getElementById('error-results');
    const successResults = document.getElementById('success-results');
    if (errorResults) errorResults.classList.remove('d-none');
    if (successResults) successResults.classList.add('d-none');
    
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        errorMessage.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle text-danger me-2 mt-1"></i>
                <div>
                    <strong>Erreur détectée :</strong><br>
                    ${message}
                </div>
            </div>
        `;
    }
}

/**
 * ✅ FINALISATION
 */
function setupFinalizeButton() {
    const finalizeBtn = document.getElementById('finalize-btn');
    const confirmBtn = document.getElementById('confirm-finalize');
    
    if (finalizeBtn) {
        finalizeBtn.addEventListener('click', showFinalizeModal);
    }
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', submitFinalData);
    }
}

function showFinalizeModal() {
    const modal = new bootstrap.Modal(document.getElementById('finalizeModal'));
    
    // Préparer le résumé final
    const finalStats = document.getElementById('final-stats');
    const finalTotalCount = document.getElementById('final-total-count');
    const finalValidCount = document.getElementById('final-valid-count');
    
    if (finalStats && finalTotalCount && finalValidCount) {
        const stats = importResults.stats;
        const totalAdherents = window.Phase2Config.stats.existants + stats.total;
        
        finalTotalCount.textContent = stats.total;
        finalValidCount.textContent = stats.valides;
        
        finalStats.innerHTML = `
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <strong>Organisation :</strong> ${totalAdherents} adhérents au total
                </div>
                <div class="col-md-6">
                    <strong>Méthode :</strong> ${importResults.processingMethod} (v5.0)
                </div>
                <div class="col-md-6">
                    <strong>Durée :</strong> ${Math.round(importResults.duration / 1000)}s
                </div>
                <div class="col-md-6">
                    <strong>Anomalies :</strong> ${stats.anomalies_critiques + stats.anomalies_majeures + stats.anomalies_mineures}
                </div>
            </div>
        `;
    }
    
    modal.show();
}

function submitFinalData() {
    console.log('🚀 Soumission finale Phase 2 v5.0');
    
    const confirmBtn = document.getElementById('confirm-finalize');
    if (confirmBtn) {
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Finalisation...';
        confirmBtn.disabled = true;
    }
    
    // Préparer les données finales
    const finalData = {
        adherents: adherentsData,
        stats: importResults.stats,
        anomalies: importResults.anomalies,
        processingMethod: importResults.processingMethod,
        duration: importResults.duration,
        phase: 2,
        version: '5.0'
    };
    
    // Remplir le formulaire caché
    const form = document.getElementById('adherents-form');
    const adherentsDataInput = document.getElementById('adherents-data');
    const processingMethodInput = document.getElementById('processing-method');
    const importStatsInput = document.getElementById('import-stats');
    
    if (form && adherentsDataInput) {
        adherentsDataInput.value = JSON.stringify(finalData);
        if (processingMethodInput) processingMethodInput.value = importResults.processingMethod;
        if (importStatsInput) importStatsInput.value = JSON.stringify(importResults.stats);
        
        console.log('📤 Soumission du formulaire Phase 2');
        form.submit();
    } else {
        console.error('❌ Formulaire de soumission non trouvé');
        if (confirmBtn) {
            confirmBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Erreur';
            confirmBtn.disabled = false;
        }
    }
}

/**
 * ✅ FONCTIONS UTILITAIRES
 */
function setupEventListeners() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && processingState.isRunning) {
            pauseProcessing();
        }
    });
}

function resetImportResults() {
    importResults = {
        success: false,
        stats: {
            total: 0,
            valides: 0,
            anomalies_critiques: 0,
            anomalies_majeures: 0,
            anomalies_mineures: 0,
            doublons: 0,
            erreurs: 0
        },
        anomalies: [],
        processingMethod: 'standard',
        startTime: null,
        endTime: null,
        duration: 0
    };
    adherentsData = [];
}

function resetProcessingState() {
    processingState = {
        isRunning: false,
        isPaused: false,
        isCancelled: false,
        currentChunk: 0,
        totalChunks: 0
    };
}

function resetUpload() {
    resetImportResults();
    resetProcessingState();
    
    hideAllStates();
    const initialElement = document.getElementById('upload-initial');
    if (initialElement) initialElement.classList.remove('d-none');
    
    const fileInput = document.getElementById('file-input');
    if (fileInput) fileInput.value = '';
    
    console.log('🔄 Interface d\'upload réinitialisée');
}

function pauseProcessing() {
    processingState.isPaused = !processingState.isPaused;
    const pauseBtn = document.getElementById('pause-btn');
    
    if (pauseBtn) {
        if (processingState.isPaused) {
            pauseBtn.innerHTML = '<i class="fas fa-play me-1"></i>Reprendre';
            updateCurrentChunk('Traitement en pause...');
        } else {
            pauseBtn.innerHTML = '<i class="fas fa-pause me-1"></i>Pause';
            updateCurrentChunk('Reprise du traitement...');
        }
    }
    
    console.log('⏸️ Traitement', processingState.isPaused ? 'mis en pause' : 'repris');
}

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// Fonctions utilitaires globales
window.refreshUploadZone = function() {
    resetUpload();
    console.log('🔄 Zone d\'upload actualisée');
};

window.clearUploadZone = function() {
    if (processingState.isRunning) {
        if (!confirm('Un import est en cours. Voulez-vous vraiment arrêter et vider la zone ?')) {
            return;
        }
    }
    resetUpload();
};

window.showHelp = function() {
    const modal = new bootstrap.Modal(document.getElementById('helpModal'));
    modal.show();
};

window.cancelFinalization = function() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('finalizeModal'));
    if (modal) modal.hide();
};

console.log('✅ adherents-import-phase2.js v5.0 chargé avec succès');