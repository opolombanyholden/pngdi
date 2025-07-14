/**
 * ========================================================================
 * PNGDI - SYSTÈME DE CHUNKING POUR IMPORT ADHÉRENTS
 * Fichier: public/js/chunking-import.js
 * Compatible: Bootstrap 5 + Laravel + organisation-create.js
 * Date: 1er juillet 2025
 * Version: 1.5 - PRODUCTION AUTHENTIFICATION + NORMALISATION CORRIGÉE
 * ========================================================================
 * 
 * CORRECTIONS VERSION 1.5 :
 * - ✅ Correction calcul totalItems dans sendChunkToServer
 * - ✅ Normalisation CSV fonctionnelle et testée
 * - ✅ Logs détaillés pour diagnostic complet
 * - ✅ Gestion correcte des indices de chunk
 * - ✅ Tests validés avec fichiers réels 1000+ adhérents
 */

// ========================================
// CONFIGURATION GLOBALE DU CHUNKING
// ========================================
const ChunkingConfig = {
    // Paramètres de chunking
    chunkSize: 500,                    // Nombre d'adhérents par lot
    triggerThreshold: 501,              // Seuil de déclenchement du chunking
    pauseBetweenChunks: 3000,           // Pause en ms entre les chunks
    maxRetries: 5,                     // Nombre max de tentatives par chunk
    
    // Configuration de l'interface
    modalId: 'chunkingProgressModal',
    progressBarId: 'chunkingProgressBar',
    
    // Endpoints avec priorité WEB (solution Discussion 39)
    endpoints: {
        // ✅ INITIALISATION VIDE - sera rempli dynamiquement
        processChunk: null,
        refreshCSRF: null,
        healthCheck: null,
        authTest: '/operator/chunking/auth-test',
        
        // Routes API (FALLBACK si nécessaire)
        processChunkAPI: '/api/organisations/process-chunk',
        refreshCSRFAPI: '/api/csrf-refresh',
        healthCheckAPI: '/api/chunking/health'
    },
    
    // Messages
    messages: {
        starting: '🚀 Démarrage de l\'importation par lots...',
        processing: '⚙️ Traitement du lot {current} sur {total}...',
        retrying: '🔄 Nouvelle tentative pour le lot {chunk}...',
        completed: '✅ Importation terminée avec succès !',
        error: '❌ Erreur lors du traitement du lot {chunk}',
        cancelled: '⚠️ Importation annulée par l\'utilisateur'
    },
    
    // Configuration debug et authentification
    debug: {
        enableVerboseLogs: true,
        logAuthDetails: true,
        logRequestHeaders: true,
        logResponseDetails: true
    }
};

// ========================================
// CLASSE 1 : GESTIONNAIRE DE CHUNKS
// ========================================
class ChunkManager {
    constructor(data, chunkSize = ChunkingConfig.chunkSize) {
        this.originalData = data;
        this.chunkSize = chunkSize;
        this.chunks = [];
        this.currentChunkIndex = 0;
        this.totalChunks = 0;
        
        this.createChunks();
        console.log(`📦 ChunkManager créé: ${this.totalChunks} lots de ${chunkSize} éléments`);
    }
    
    createChunks() {
        this.chunks = [];
        
        for (let i = 0; i < this.originalData.length; i += this.chunkSize) {
            const chunk = {
                id: Math.floor(i / this.chunkSize) + 1,
                data: this.originalData.slice(i, i + this.chunkSize),
                startIndex: i,
                endIndex: Math.min(i + this.chunkSize - 1, this.originalData.length - 1),
                status: 'pending',
                attempts: 0,
                errors: [],
                processedAt: null
            };
            
            this.chunks.push(chunk);
        }
        
        this.totalChunks = this.chunks.length;
        this.currentChunkIndex = 0;
        
        console.log(`📊 ${this.totalChunks} chunks créés:`, this.chunks.map(c => ({
            id: c.id,
            size: c.data.length,
            range: `${c.startIndex}-${c.endIndex}`
        })));
    }
    
    hasNext() {
        return this.currentChunkIndex < this.totalChunks;
    }
    
    getNext() {
        if (!this.hasNext()) {
            return null;
        }
        
        const chunk = this.chunks[this.currentChunkIndex];
        this.currentChunkIndex++;
        
        console.log(`📤 Chunk ${chunk.id} récupéré:`, {
            id: chunk.id,
            dataLength: chunk.data.length,
            status: chunk.status
        });
        
        return chunk;
    }
    
    getChunkById(chunkId) {
        return this.chunks.find(c => c.id === chunkId);
    }
    
    markChunkCompleted(chunkId, processedData = null) {
        const chunk = this.getChunkById(chunkId);
        if (chunk) {
            chunk.status = 'completed';
            chunk.processedAt = new Date().toISOString();
            if (processedData) {
                chunk.processedData = processedData;
            }
            console.log(`✅ Chunk ${chunkId} marqué comme terminé`);
        }
    }
    
    markChunkError(chunkId, error) {
        const chunk = this.getChunkById(chunkId);
        if (chunk) {
            chunk.status = 'error';
            chunk.attempts++;
            chunk.errors.push({
                error: error,
                timestamp: new Date().toISOString(),
                attempt: chunk.attempts
            });
            console.error(`❌ Chunk ${chunkId} en erreur (tentative ${chunk.attempts}):`, error);
        }
    }
    
    resetChunkForRetry(chunkId) {
        const chunk = this.getChunkById(chunkId);
        if (chunk && chunk.attempts < ChunkingConfig.maxRetries) {
            chunk.status = 'pending';
            this.currentChunkIndex = Math.min(this.currentChunkIndex, chunk.id - 1);
            console.log(`🔄 Chunk ${chunkId} réinitialisé pour retry`);
            return true;
        }
        return false;
    }
    
    getStats() {
        const completed = this.chunks.filter(c => c.status === 'completed').length;
        const errors = this.chunks.filter(c => c.status === 'error').length;
        const pending = this.chunks.filter(c => c.status === 'pending').length;
        const processing = this.chunks.filter(c => c.status === 'processing').length;
        
        return {
            total: this.totalChunks,
            completed,
            errors,
            pending,
            processing,
            progressPercent: Math.round((completed / this.totalChunks) * 100),
            totalDataProcessed: this.chunks
                .filter(c => c.status === 'completed')
                .reduce((sum, c) => sum + c.data.length, 0)
        };
    }
}

// ========================================
// CLASSE 2 : SUIVI DE PROGRESSION
// ========================================
class ProgressTracker {
    constructor(totalItems, totalChunks) {
        this.totalItems = totalItems;
        this.totalChunks = totalChunks;
        this.processedItems = 0;
        this.errorItems = 0;
        this.currentChunk = 0;
        this.startTime = Date.now();
        this.modalElement = null;
        this.isPaused = false;
        this.isCancelled = false;
        
        console.log(`📊 ProgressTracker initialisé: ${totalItems} items, ${totalChunks} chunks`);
    }
    
    showModal() {
        const modalHTML = this.createModalHTML();
        
        const existingModal = document.getElementById(ChunkingConfig.modalId);
        if (existingModal) {
            existingModal.remove();
        }
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        this.modalElement = new bootstrap.Modal(document.getElementById(ChunkingConfig.modalId), {
            backdrop: 'static',
            keyboard: false
        });
        
        this.modalElement.show();
        this.setupModalEvents();
        
        console.log('🎨 Modal de progression affichée');
    }
    
    createModalHTML() {
        return `
            <div class="modal fade" id="${ChunkingConfig.modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-cloud-upload-alt me-2"></i>
                                Importation par Lots - ${this.totalItems} Adhérents
                            </h5>
                        </div>
                        
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Progression globale</strong>
                                    <span id="chunking-progress-text">0%</span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div id="${ChunkingConfig.progressBarId}" 
                                         class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                         style="width: 0%;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-success h-100">
                                        <div class="card-body text-center">
                                            <h4 id="chunking-processed" class="text-success mb-1">0</h4>
                                            <small class="text-muted">Traités</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-danger h-100">
                                        <div class="card-body text-center">
                                            <h4 id="chunking-errors" class="text-danger mb-1">0</h4>
                                            <small class="text-muted">Erreurs</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-primary h-100">
                                        <div class="card-body text-center">
                                            <h4 id="chunking-current-chunk" class="text-primary mb-1">1</h4>
                                            <small class="text-muted">Lot actuel</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-info h-100">
                                        <div class="card-body text-center">
                                            <h4 id="chunking-eta" class="text-info mb-1">--</h4>
                                            <small class="text-muted">Temps restant</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mb-3">
                                <div class="d-flex align-items-center">
                                    <div id="chunking-status-icon" class="me-3">
                                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <strong id="chunking-status-title">Préparation...</strong>
                                        <div id="chunking-status-details" class="small text-muted">
                                            Initialisation du traitement par lots
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border-0 bg-light">
                                <div class="card-header bg-secondary text-white py-2">
                                    <small>
                                        <i class="fas fa-list me-2"></i>
                                        Journal des opérations
                                    </small>
                                </div>
                                <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                                    <div id="chunking-log" class="small">
                                        <div class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            ${new Date().toLocaleTimeString()} - Initialisation du système de chunking
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer bg-light">
                            <div class="d-flex justify-content-between w-100 align-items-center">
                                <div class="text-muted small">
                                    <i class="fas fa-clock me-1"></i>
                                    Démarré à <span id="chunking-start-time">${new Date().toLocaleTimeString()}</span>
                                </div>
                                <div>
                                    <button type="button" id="chunking-pause-btn" class="btn btn-warning me-2" disabled>
                                        <i class="fas fa-pause me-1"></i>Pause
                                    </button>
                                    <button type="button" id="chunking-cancel-btn" class="btn btn-danger">
                                        <i class="fas fa-times me-1"></i>Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    setupModalEvents() {
        const pauseBtn = document.getElementById('chunking-pause-btn');
        pauseBtn?.addEventListener('click', () => {
            this.togglePause();
        });
        
        const cancelBtn = document.getElementById('chunking-cancel-btn');
        cancelBtn?.addEventListener('click', () => {
            this.requestCancel();
        });
    }
    
    updateProgress(chunkStats, currentChunkId, status = null) {
        this.processedItems = chunkStats.totalDataProcessed;
        this.errorItems = chunkStats.errors;
        this.currentChunk = currentChunkId;
        
        const progressPercent = chunkStats.progressPercent;
        
        const progressBar = document.getElementById(ChunkingConfig.progressBarId);
        const progressText = document.getElementById('chunking-progress-text');
        
        if (progressBar) {
            progressBar.style.width = progressPercent + '%';
            progressBar.textContent = progressPercent + '%';
        }
        
        if (progressText) {
            progressText.textContent = progressPercent + '%';
        }
        
        this.updateElement('chunking-processed', this.processedItems);
        this.updateElement('chunking-errors', this.errorItems);
        this.updateElement('chunking-current-chunk', currentChunkId);
        this.updateElement('chunking-eta', this.calculateETA(progressPercent));
        
        if (status) {
            this.updateStatus(status);
        }
        
        console.log(`📊 Progression mise à jour: ${progressPercent}% (${this.processedItems}/${this.totalItems})`);
    }
    
    updateStatus(status) {
        const statusIcon = document.getElementById('chunking-status-icon');
        const statusTitle = document.getElementById('chunking-status-title');
        const statusDetails = document.getElementById('chunking-status-details');
        
        if (statusIcon && statusTitle && statusDetails) {
            switch (status.type) {
                case 'processing':
                    statusIcon.innerHTML = '<i class="fas fa-cog fa-spin fa-2x text-primary"></i>';
                    statusTitle.textContent = status.title || 'Traitement en cours...';
                    statusDetails.textContent = status.details || '';
                    break;
                    
                case 'success':
                    statusIcon.innerHTML = '<i class="fas fa-check-circle fa-2x text-success"></i>';
                    statusTitle.textContent = status.title || 'Traitement réussi';
                    statusDetails.textContent = status.details || '';
                    break;
                    
                case 'error':
                    statusIcon.innerHTML = '<i class="fas fa-exclamation-triangle fa-2x text-danger"></i>';
                    statusTitle.textContent = status.title || 'Erreur détectée';
                    statusDetails.textContent = status.details || '';
                    break;
                    
                case 'retry':
                    statusIcon.innerHTML = '<i class="fas fa-redo fa-spin fa-2x text-warning"></i>';
                    statusTitle.textContent = status.title || 'Nouvelle tentative...';
                    statusDetails.textContent = status.details || '';
                    break;
            }
        }
    }
    
    addLog(message, type = 'info') {
        const logContainer = document.getElementById('chunking-log');
        if (!logContainer) return;
        
        const timestamp = new Date().toLocaleTimeString();
        const iconMap = {
            'info': 'fa-info-circle text-info',
            'success': 'fa-check-circle text-success',
            'error': 'fa-exclamation-triangle text-danger',
            'warning': 'fa-exclamation-circle text-warning'
        };
        
        const logEntry = document.createElement('div');
        logEntry.className = 'mb-1';
        logEntry.innerHTML = `
            <i class="fas ${iconMap[type] || iconMap.info} me-1"></i>
            ${timestamp} - ${message}
        `;
        
        logContainer.appendChild(logEntry);
        logContainer.scrollTop = logContainer.scrollHeight;
        
        while (logContainer.children.length > 50) {
            logContainer.removeChild(logContainer.firstChild);
        }
    }
    
    calculateETA(progressPercent) {
        if (progressPercent <= 0) return '--';
        
        const elapsed = Date.now() - this.startTime;
        const remaining = (elapsed / progressPercent) * (100 - progressPercent);
        
        if (remaining < 60000) {
            return Math.ceil(remaining / 1000) + 's';
        } else if (remaining < 3600000) {
            return Math.ceil(remaining / 60000) + 'min';
        } else {
            return Math.ceil(remaining / 3600000) + 'h';
        }
    }
    
    togglePause() {
        this.isPaused = !this.isPaused;
        const pauseBtn = document.getElementById('chunking-pause-btn');
        
        if (this.isPaused) {
            pauseBtn.innerHTML = '<i class="fas fa-play me-1"></i>Reprendre';
            pauseBtn.className = 'btn btn-success me-2';
            this.addLog('⏸️ Traitement mis en pause', 'warning');
        } else {
            pauseBtn.innerHTML = '<i class="fas fa-pause me-1"></i>Pause';
            pauseBtn.className = 'btn btn-warning me-2';
            this.addLog('▶️ Traitement repris', 'info');
        }
    }
    
    requestCancel() {
        if (confirm('⚠️ Êtes-vous sûr de vouloir annuler l\'importation ?\n\nLes données déjà traitées seront conservées.')) {
            this.isCancelled = true;
            this.addLog('🛑 Annulation demandée par l\'utilisateur', 'error');
            
            const pauseBtn = document.getElementById('chunking-pause-btn');
            const cancelBtn = document.getElementById('chunking-cancel-btn');
            
            if (pauseBtn) pauseBtn.disabled = true;
            if (cancelBtn) {
                cancelBtn.disabled = true;
                cancelBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Annulation...';
            }
        }
    }
    
    markCompleted(finalStats) {
        const progressBar = document.getElementById(ChunkingConfig.progressBarId);
        if (progressBar) {
            progressBar.className = 'progress-bar bg-success';
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
        }
        
        this.updateStatus({
            type: 'success',
            title: '🎉 Importation terminée !',
            details: `${finalStats.totalDataProcessed} adhérents importés avec succès`
        });
        
        this.addLog(`✅ Importation terminée: ${finalStats.totalDataProcessed} adhérents traités`, 'success');
        
        const footer = document.querySelector(`#${ChunkingConfig.modalId} .modal-footer`);
        if (footer) {
            footer.innerHTML = `
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <div class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        Importation réussie !
                    </div>
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i>Fermer
                    </button>
                </div>
            `;
        }
        
        console.log('🎉 Importation marquée comme terminée');
    }
    
    markFailed(reason, finalStats) {
        const progressBar = document.getElementById(ChunkingConfig.progressBarId);
        if (progressBar) {
            progressBar.className = 'progress-bar bg-danger';
        }
        
        this.updateStatus({
            type: 'error',
            title: '❌ Importation échouée',
            details: reason
        });
        
        this.addLog(`❌ ${reason}`, 'error');
        
        const footer = document.querySelector(`#${ChunkingConfig.modalId} .modal-footer`);
        if (footer) {
            footer.innerHTML = `
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <div class="text-danger">
                        <i class="fas fa-times-circle me-1"></i>
                        Importation échouée
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            Fermer
                        </button>
                        <button type="button" class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-refresh me-1"></i>Recharger
                        </button>
                    </div>
                </div>
            `;
        }
    }
    
    close() {
        if (this.modalElement) {
            this.modalElement.hide();
            
            setTimeout(() => {
                const modalDOM = document.getElementById(ChunkingConfig.modalId);
                if (modalDOM) {
                    modalDOM.remove();
                }
            }, 500);
        }
    }
    
    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }
}

// ========================================
// CLASSE 3 : PROCESSEUR D'IMPORT - VERSION 1.5 CORRIGÉE
// ========================================
class ImportProcessor {
    constructor(chunkManager, progressTracker) {
        this.chunkManager = chunkManager;
        this.progressTracker = progressTracker;
        this.isProcessing = false;
        this.csrfToken = this.getCurrentCSRFToken();
        this.authContext = this.detectAuthContext();
        
        console.log('🔧 ImportProcessor v1.5 initialisé (NORMALISATION CORRIGÉE)', {
            csrfToken: this.csrfToken ? 'Présent' : 'Absent',
            authContext: this.authContext
        });
    }
    
    /**
     * VERSION 1.5 : Détection du contexte d'authentification
     */
    detectAuthContext() {
        const context = {
            // Tests d'authentification multi-méthodes
            hasUserMeta: !!document.querySelector('meta[name="user-id"]'),
            hasAuthUser: !!document.querySelector('meta[name="auth-user"]'),
            hasAuthenticatedClass: document.body.classList.contains('authenticated'),
            hasUserMenu: !!document.querySelector('.user-menu, .user-dropdown, #user-menu'),
            hasNavAuth: !!document.querySelector('nav .navbar-nav'),
            
            // Tests de session Laravel
            hasLaravelSession: document.cookie.includes('laravel_session'),
            hasXSRFToken: document.cookie.includes('XSRF-TOKEN'),
            hasPNGDISession: document.cookie.includes('pngdi_session'),
            
            // Tests de cookies de session
            sessionCookies: this.extractSessionCookies(),
            
            // CSRF et tokens
            csrfToken: this.getCurrentCSRFToken(),
            
            // URL et domaine
            currentDomain: window.location.hostname,
            currentPath: window.location.pathname,
            isOperatorArea: window.location.pathname.includes('/operator'),
            
            // Éléments DOM d'authentification
            authElements: this.findAuthElements()
        };
        
        // Score de confiance d'authentification
        context.authScore = this.calculateAuthScore(context);
        context.isAuthenticated = context.authScore >= 3; // Seuil de confiance
        
        if (ChunkingConfig.debug.logAuthDetails) {
            console.log('🔐 Contexte d\'authentification détecté:', context);
        }
        
        return context;
    }
    
    /**
     * VERSION 1.5 : Extraire les cookies de session détaillés
     */
    extractSessionCookies() {
        const cookies = {};
        document.cookie.split(';').forEach(cookie => {
            const [name, value] = cookie.trim().split('=');
            if (name && (name.includes('session') || name.includes('XSRF') || name.includes('csrf'))) {
                cookies[name] = value ? value.substring(0, 20) + '...' : '';
            }
        });
        return cookies;
    }
    
    /**
     * VERSION 1.5 : Trouver les éléments DOM d'authentification
     */
    findAuthElements() {
        const selectors = [
            'meta[name="user-id"]',
            'meta[name="auth-user"]',
            '.user-menu',
            '.user-dropdown',
            '#user-menu',
            'nav .navbar-nav',
            '[data-user-authenticated]',
            '.authenticated-user',
            '.user-profile'
        ];
        
        const found = [];
        selectors.forEach(selector => {
            const element = document.querySelector(selector);
            if (element) {
                found.push({
                    selector,
                    tagName: element.tagName,
                    id: element.id || null,
                    className: element.className || null,
                    content: element.textContent?.substring(0, 50) || null
                });
            }
        });
        
        return found;
    }
    
    /**
     * VERSION 1.5 : Calculer le score de confiance d'authentification
     */
    calculateAuthScore(context) {
        let score = 0;
        
        // Tests principaux (2 points chacun)
        if (context.hasUserMeta || context.hasAuthUser) score += 2;
        if (context.hasUserMenu || context.hasNavAuth) score += 2;
        if (context.hasLaravelSession || context.hasXSRFToken) score += 2;
        
        // Tests secondaires (1 point chacun)
        if (context.hasAuthenticatedClass) score += 1;
        if (context.csrfToken) score += 1;
        if (context.isOperatorArea) score += 1;
        if (Object.keys(context.sessionCookies).length > 0) score += 1;
        if (context.authElements.length > 0) score += 1;
        
        return score;
    }
    
    /**
     * VERSION 1.5 : Test d'authentification renforcé
     */
    async testAuthentication() {
        console.log('🔐 Test authentification v1.5 - Multi-méthodes...');
        
        // Test 1: Health check web (priorité)
        try {
            const healthResponse = await this.makeAuthTestRequest('/chunking/health');
            if (healthResponse.success && healthResponse.data.user_authenticated) {
                console.log('✅ Authentification confirmée via health check web');
                return true;
            }
        } catch (error) {
            console.warn('⚠️ Health check web échoué:', error.message);
        }
        
        // Test 2: Route API alternative
        try {
            const apiResponse = await this.makeAuthTestRequest('/api/chunking/health');
            if (apiResponse.success && apiResponse.data.user_authenticated) {
                console.log('✅ Authentification confirmée via health check API');
                return true;
            }
        } catch (error) {
            console.warn('⚠️ Health check API échoué:', error.message);
        }
        
        // Test 3: Test CSRF simple
        try {
            const csrfResponse = await this.makeAuthTestRequest('/chunking/csrf-refresh');
            if (csrfResponse.success) {
                console.log('✅ Authentification confirmée via test CSRF');
                return true;
            }
        } catch (error) {
            console.warn('⚠️ Test CSRF échoué:', error.message);
        }
        
        // Test 4: Basé sur le contexte détecté
        if (this.authContext.isAuthenticated) {
            console.log('✅ Authentification supposée basée sur le contexte DOM');
            return true;
        }
        
        console.error('❌ Tous les tests d\'authentification ont échoué');
        return false;
    }
    
    /**
     * VERSION 1.5 : Requête de test d'authentification
     */
    async makeAuthTestRequest(url) {
        const requestConfig = {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.getCurrentCSRFToken()
            },
            credentials: 'same-origin'
        };
        
        if (ChunkingConfig.debug.logRequestHeaders) {
            console.log(`📡 Test auth ${url}:`, requestConfig.headers);
        }
        
        const response = await fetch(url, requestConfig);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        return {
            success: true,
            data: data,
            status: response.status
        };
    }
    
    /**
     * Traiter tous les chunks séquentiellement (VERSION 1.5)
     */
    async processAllChunks() {
        console.log('🚀 Début du traitement de tous les chunks (VERSION 1.5 CORRIGÉE)');
        
        // Test d'authentification préalable renforcé
        const authOk = await this.testAuthentication();
        if (!authOk) {
            throw new Error('Authentification échouée - impossible de continuer');
        }
        
        this.isProcessing = true;
        this.progressTracker.addLog('🚀 Démarrage du traitement par lots (v1.5)', 'info');
        
        const pauseBtn = document.getElementById('chunking-pause-btn');
        if (pauseBtn) pauseBtn.disabled = false;
        
        try {
            while (this.chunkManager.hasNext() && this.isProcessing) {
                if (this.progressTracker.isPaused) {
                    await this.waitForResume();
                }
                
                if (this.progressTracker.isCancelled) {
                    throw new Error('Importation annulée par l\'utilisateur');
                }
                
                const chunk = this.chunkManager.getNext();
                if (!chunk) break;
                
                await this.processChunk(chunk);
                
                if (ChunkingConfig.pauseBetweenChunks > 0) {
                    await this.sleep(ChunkingConfig.pauseBetweenChunks);
                }
            }
            
            const finalStats = this.chunkManager.getStats();
            this.progressTracker.markCompleted(finalStats);
            
            console.log('🎉 Tous les chunks traités avec succès (v1.5)');
            return true;
            
        } catch (error) {
            console.error('❌ Erreur lors du traitement des chunks (v1.5):', error);
            
            const finalStats = this.chunkManager.getStats();
            this.progressTracker.markFailed(error.message, finalStats);
            
            return false;
        } finally {
            this.isProcessing = false;
        }
    }
    
    /**
     * VERSION 1.5 : Traiter un chunk avec fallback intelligent
     */
    async processChunk(chunk) {
        const maxRetries = ChunkingConfig.maxRetries;
        let currentAttempt = 1;
        
        while (currentAttempt <= maxRetries) {
            try {
                console.log(`📦 Traitement chunk ${chunk.id}, tentative ${currentAttempt}/${maxRetries} (v1.5)`);
                
                chunk.status = 'processing';
                
                this.progressTracker.updateStatus({
                    type: 'processing',
                    title: `Traitement du lot ${chunk.id}/${this.chunkManager.totalChunks}`,
                    details: `${chunk.data.length} adhérents - Tentative ${currentAttempt}/${maxRetries} (v1.5)`
                });
                
                this.progressTracker.addLog(
                    `📦 Lot ${chunk.id}: traitement de ${chunk.data.length} adhérents... (v1.5)`,
                    'info'
                );
                
                if (currentAttempt > 1) {
                    console.log('🔄 Refresh CSRF avant retry...');
                    await this.refreshCSRFToken();
                }
                
                const result = await this.sendChunkToServer(chunk, currentAttempt);
                
                this.chunkManager.markChunkCompleted(chunk.id, result);
                
                this.progressTracker.addLog(
                    `✅ Lot ${chunk.id}: ${result.processed || chunk.data.length} adhérents traités (v1.5)`,
                    'success'
                );
                
                const stats = this.chunkManager.getStats();
                this.progressTracker.updateProgress(stats, chunk.id);
                
                return result;
                
            } catch (error) {
                console.error(`❌ Erreur chunk ${chunk.id}, tentative ${currentAttempt} (v1.5):`, error);
                
                this.chunkManager.markChunkError(chunk.id, error.message);
                
                this.progressTracker.addLog(
                    `❌ Lot ${chunk.id}: Erreur tentative ${currentAttempt} - ${error.message}`,
                    'error'
                );
                
                if (currentAttempt < maxRetries) {
                    this.progressTracker.updateStatus({
                        type: 'retry',
                        title: `Nouvelle tentative lot ${chunk.id}`,
                        details: `Tentative ${currentAttempt + 1}/${maxRetries} dans 2 secondes... (v1.5)`
                    });
                    
                    await this.sleep(2000);
                    currentAttempt++;
                } else {
                    throw new Error(`Chunk ${chunk.id} échoué après ${maxRetries} tentatives: ${error.message}`);
                }
            }
        }
    }
    
    /**
     * VERSION 1.5 : Normalisation des données CSV
     */
    normalizeCSVData(csvData) {
        console.log('🔧 NORMALISATION CSV v1.5 - Début traitement', csvData.length, 'éléments');
        
        return csvData.map((item, index) => {
            const normalized = {};
            
            // Mapping des colonnes CSV françaises vers format Laravel attendu
            const columnMapping = {
                'Civilité': 'civilite',
                'Nom': 'nom',
                'Prenom': 'prenom', 
                'Prénom': 'prenom',
                'NIP': 'nip',
                'Telephone': 'telephone',
                'Téléphone': 'telephone',
                'Profession': 'profession',
                'Email': 'email',
                'Date_naissance': 'date_naissance',
                'Date de naissance': 'date_naissance',
                'Lieu_naissance': 'lieu_naissance',
                'Lieu de naissance': 'lieu_naissance',
                'Adresse': 'adresse',
                'Sexe': 'sexe',
                'Nationalite': 'nationalite',
                'Nationalité': 'nationalite'
            };
            
            // Normaliser chaque propriété
            Object.keys(item).forEach(key => {
                const normalizedKey = columnMapping[key] || key.toLowerCase().replace(/[éèê]/g, 'e').replace(/[àâ]/g, 'a');
                normalized[normalizedKey] = item[key];
            });
            
            // Validation des champs obligatoires
            if (!normalized.nom || !normalized.prenom || !normalized.nip) {
                console.warn(`⚠️ Données incomplètes élément ${index}:`, normalized);
            }
            
            return normalized;
        });
    }
    
    /**
     * VERSION 1.5 : Envoi avec fallback intelligent Web → API + normalisation CSV CORRIGÉE
     */
    async sendChunkToServer(chunk, attempt = 1) {
       
    // ✅ CORRECTION : Forcer la mise à jour si URL undefined
    if (!ChunkingConfig.endpoints.processChunk || ChunkingConfig.endpoints.processChunk === 'undefined') {
        console.log('🔄 URL processChunk undefined, tentative de mise à jour...');
        this.updateEndpointsFromPhase2Config();
    }  
        // ✅ DEBUG URL AVANT ENVOI
    console.log('🔧 DEBUG URLs disponibles:', {
        'Phase2Config.urls.processChunk': window.Phase2Config?.urls?.processChunk,
        'ChunkingConfig.endpoints.processChunk': ChunkingConfig.endpoints.processChunk,
        'URL finale qui sera utilisée': ChunkingConfig.endpoints.processChunk
    });
    
    const startTime = Date.now();
    const processChunkUrl = ChunkingConfig.endpoints.processChunk;
    
    if (!processChunkUrl || processChunkUrl === 'undefined') {
        console.error('❌ URL processChunk est undefined !');
        return Promise.reject(new Error('URL processChunk non définie'));
    }
    
    console.log('📡 URL finale utilisée:', processChunkUrl);

        try {
            console.log(`📦 Traitement chunk ${chunk.id}, tentative ${attempt} (v1.5 CORRIGÉE)`);
            
            // ✅ CORRECTION : Normalisation automatique des données CSV
            const normalizedData = this.normalizeCSVData(chunk.data);
            
            console.log(`🔧 Normalisation CSV - Avant:`, chunk.data[0]);
            console.log(`🔧 Normalisation CSV - Après:`, normalizedData[0]);
            
            // ✅ CORRECTION : Calcul correct des indices avec données disponibles
            const totalItems = this.chunkManager ? this.chunkManager.originalData.length : chunk.data.length * this.chunkManager.totalChunks;
            
            const chunkData = {
                chunk_id: chunk.id,
                chunk_data: JSON.stringify(normalizedData), // Utiliser les données normalisées
                is_chunk: 'true',
                total_chunks: this.chunkManager.chunks.length,
                chunk_start_index: (chunk.id - 1) * ChunkingConfig.chunkSize,
                chunk_end_index: Math.min(chunk.id * ChunkingConfig.chunkSize - 1, totalItems - 1)
            };
            
            console.log('📊 Données chunk préparées v1.5:', {
                chunk_id: chunkData.chunk_id,
                data_count: normalizedData.length,
                start_index: chunkData.chunk_start_index,
                end_index: chunkData.chunk_end_index,
                total_chunks: chunkData.total_chunks,
                first_item: normalizedData[0]
            });
            
            const requestConfig = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCurrentCSRFToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(chunkData)
            };
            
            if (ChunkingConfig.debug.logRequestHeaders) {
                console.log(`📡 Headers v1.5 chunk ${chunk.id}:`, requestConfig.headers);
            }
            
            // PRIORITÉ 1: Route Web (selon solution Discussion 39)
            let response;
            let usedUrl = ChunkingConfig.endpoints.processChunk;
            
            try {
                console.log(`📡 Tentative PRIORITAIRE Web: ${usedUrl}`);
                response = await fetch(usedUrl, requestConfig);
                
                if (ChunkingConfig.debug.logResponseDetails) {
                    console.log(`📡 Réponse Web chunk ${chunk.id}:`, {
                        status: response.status,
                        statusText: response.statusText,
                        headers: Object.fromEntries(response.headers.entries())
                    });
                }
                
            } catch (webError) {
                console.warn(`⚠️ Erreur route Web ${usedUrl}:`, webError.message);
                
                // FALLBACK: Route API
                usedUrl = ChunkingConfig.endpoints.processChunkAPI;
                console.log(`🔄 FALLBACK vers API: ${usedUrl}`);
                
                try {
                    response = await fetch(usedUrl, requestConfig);
                    
                    if (ChunkingConfig.debug.logResponseDetails) {
                        console.log(`📡 Réponse API chunk ${chunk.id}:`, {
                            status: response.status,
                            statusText: response.statusText,
                            headers: Object.fromEntries(response.headers.entries())
                        });
                    }
                    
                } catch (apiError) {
                    console.error(`❌ Erreur route API ${usedUrl}:`, apiError.message);
                    throw new Error(`Échec des deux routes (Web + API): ${webError.message} | ${apiError.message}`);
                }
            }
            
            console.log(`📡 Réponse serveur chunk ${chunk.id} via ${usedUrl}:`, response.status, response.statusText);
            
            if (!response.ok) {
                const errorText = await response.text();
                let errorDetails;
                
                try {
                    errorDetails = JSON.parse(errorText);
                } catch (e) {
                    errorDetails = { message: errorText, status: response.status };
                }
                
                console.error(`❌ Erreur serveur chunk ${chunk.id}:`, errorDetails);
                
                // Gestion spécifique erreur authentification
                if (response.status === 401 || errorDetails.message === 'Unauthenticated.') {
                    console.warn(`🔐 Erreur authentification détectée pour chunk ${chunk.id}`);
                    
                    // Re-tester l'authentification
                    const authStillValid = await this.testAuthentication();
                    if (!authStillValid) {
                        throw new Error(`Perte d'authentification détectée pour chunk ${chunk.id}`);
                    }
                    
                    // Essayer de rafraîchir le token CSRF
                    const csrfRefreshed = await this.refreshCSRFToken();
                    if (csrfRefreshed && attempt < ChunkingConfig.maxRetries) {
                        console.log(`🔄 CSRF rafraîchi, nouvelle tentative chunk ${chunk.id}`);
                        await this.delay(1000);
                        return this.sendChunkToServer(chunk, attempt + 1);
                    }
                    
                    throw new Error(`Authentification échouée pour chunk ${chunk.id}: ${errorDetails.message}`);
                }
                
                // Gestion erreurs CSRF
                if (response.status === 419 || errorDetails.message?.includes('CSRF')) {
                    console.warn(`🔐 Erreur CSRF détectée pour chunk ${chunk.id}`);
                    
                    const csrfRefreshed = await this.refreshCSRFToken();
                    if (csrfRefreshed && attempt < ChunkingConfig.maxRetries) {
                        console.log(`🔄 CSRF rafraîchi, nouvelle tentative chunk ${chunk.id}`);
                        await this.delay(1000);
                        return this.sendChunkToServer(chunk, attempt + 1);
                    }
                }
                
                // Retry pour autres erreurs
                if (attempt < ChunkingConfig.maxRetries) {
                    console.warn(`⚠️ Tentative ${attempt + 1} pour chunk ${chunk.id} dans 2 secondes...`);
                    await this.delay(2000);
                    return this.sendChunkToServer(chunk, attempt + 1);
                }
                
                throw new Error(`Erreur serveur après ${ChunkingConfig.maxRetries} tentatives: ${errorDetails.message}`);
            }
            
            const result = await response.json();
            const processingTime = Date.now() - startTime;
            
            console.log(`✅ Chunk ${chunk.id} traité avec succès (v1.5)`, {
                processed: result.data?.processed || 0,
                errors: result.data?.errors || 0,
                time: `${processingTime}ms`,
                route: usedUrl
            });
            
            return {
                success: true,
                data: result.data,
                processingTime: processingTime,
                route: usedUrl,
                processed: result.data?.processed || chunk.data.length
            };
            
        } catch (error) {
            const processingTime = Date.now() - startTime;
            console.error(`❌ Erreur chunk ${chunk.id} (v1.5):`, error.message);
            
            throw error; // Re-throw pour que processChunk gère le retry
        }
    }
    

    /**
 * ✅ NOUVELLE MÉTHODE : Mise à jour des endpoints depuis Phase2Config
 */
updateEndpointsFromPhase2Config() {
    if (typeof window.Phase2Config !== 'undefined' && window.Phase2Config.urls) {
        console.log('🔧 Mise à jour forcée des endpoints depuis Phase2Config');
        
        ChunkingConfig.endpoints.processChunk = window.Phase2Config.urls.processChunk;
        ChunkingConfig.endpoints.refreshCSRF = window.Phase2Config.urls.refreshCSRF;
        ChunkingConfig.endpoints.healthCheck = window.Phase2Config.urls.healthCheck;
        
        console.log('✅ Endpoints mis à jour:', ChunkingConfig.endpoints);
        return true;
    } else {
        console.log('⚠️ Phase2Config toujours non disponible, utilisation fallback');
        
        // URLs directes en fallback
        ChunkingConfig.endpoints.processChunk = '/operator/chunking/process-chunk';
        ChunkingConfig.endpoints.refreshCSRF = '/operator/chunking/csrf-refresh';
        ChunkingConfig.endpoints.healthCheck = '/operator/chunking/health';
        
        return false;
    }
}


    /**
     * VERSION 1.5 : Refresh CSRF avec fallback
     */
    async refreshCSRFToken() {
        try {
            console.log('🔄 Rafraîchissement token CSRF (v1.5)...');
            
            const requestConfig = {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            };
            
            // PRIORITÉ: Route Web
            let response;
            let url = ChunkingConfig.endpoints.refreshCSRF;
            
            try {
                response = await fetch(url, requestConfig);
            } catch (webError) {
                console.warn(`⚠️ Erreur refresh CSRF Web:`, webError.message);
                
                // FALLBACK: Route API
                url = ChunkingConfig.endpoints.refreshCSRFAPI;
                try {
                    response = await fetch(url, requestConfig);
                } catch (apiError) {
                    console.error(`❌ Erreur refresh CSRF API:`, apiError.message);
                    return false;
                }
            }
            
            if (!response.ok) {
                console.error('❌ Erreur refresh CSRF:', response.status, response.statusText);
                return false;
            }
            
            const result = await response.json();
            
            if (result.success && result.csrf_token) {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    csrfMeta.setAttribute('content', result.csrf_token);
                }
                
                this.csrfToken = result.csrf_token;
                
                console.log('✅ Token CSRF rafraîchi avec succès (v1.5)');
                return true;
            }
            
            console.error('❌ Réponse CSRF invalide (v1.5):', result);
            return false;
            
        } catch (error) {
            console.error('❌ Erreur refresh CSRF (v1.5):', error);
            return false;
        }
    }
    
    getCurrentCSRFToken() {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        
        if (!csrfToken && ChunkingConfig.debug.logAuthDetails) {
            console.warn('⚠️ Token CSRF non trouvé dans la meta tag');
        }
        
        return csrfToken;
    }
    
    async waitForResume() {
        console.log('⏸️ En pause, attente de la reprise...');
        
        while (this.progressTracker.isPaused && !this.progressTracker.isCancelled) {
            await this.sleep(100);
        }
        
        console.log('▶️ Reprise du traitement');
    }
    
    async delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// ========================================
// FONCTION PRINCIPALE D'INTERFACE
// ========================================

function shouldUseChunking(adherentsData) {
    return adherentsData && adherentsData.length >= ChunkingConfig.triggerThreshold;
}

async function processImportWithChunking(adherentsData, validationResult) {
    console.log('🚀 Démarrage import avec chunking (VERSION 1.5 CORRIGÉE):', {
        totalAdherents: adherentsData.length,
        triggerThreshold: ChunkingConfig.triggerThreshold,
        chunkSize: ChunkingConfig.chunkSize
    });
    
    try {
        const chunkManager = new ChunkManager(adherentsData, ChunkingConfig.chunkSize);
        const progressTracker = new ProgressTracker(adherentsData.length, chunkManager.totalChunks);
        
        progressTracker.showModal();
        
        const importProcessor = new ImportProcessor(chunkManager, progressTracker);
        
        const success = await importProcessor.processAllChunks();
        
        if (success) {
            console.log('🎉 Import avec chunking terminé avec succès (VERSION 1.5)');
            
            const finalStats = chunkManager.getStats();
            
            if (window.OrganisationApp) {
                const processedAdherents = chunkManager.chunks
                    .filter(c => c.status === 'completed' && c.processedData)
                    .flatMap(c => c.data);
                    
                processedAdherents.forEach(adherent => {
                    if (!window.OrganisationApp.adherents.find(a => a.nip === adherent.nip)) {
                        window.OrganisationApp.adherents.push(adherent);
                    }
                });
                
                if (typeof window.updateAdherentsList === 'function') {
                    window.updateAdherentsList();
                }
                
                if (typeof window.autoSave === 'function') {
                    window.autoSave();
                }
            }
            
            return true;
        } else {
            console.error('❌ Import avec chunking échoué (VERSION 1.5)');
            return false;
        }
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'import avec chunking (VERSION 1.5):', error);
        
        if (typeof window.showNotification === 'function') {
            window.showNotification('Erreur lors de l\'importation: ' + error.message, 'danger');
        }
        
        return false;
    }
}


/**
 * ✅ NOUVELLE MÉTHODE : Intégration spécifique Phase 2
 * Compatible avec adherents-import.blade.php
 */
function hookIntoPhase2Import() {
    console.log('🔗 Intégration avec adherents-import.blade.php Phase 2...');
    
    // Vérifier si nous sommes en Phase 2
    if (typeof window.Phase2Config !== 'undefined') {
        console.log('✅ Mode Phase 2 détecté - Configuration chunking adaptée');
        
        // Adapter la configuration aux URLs Phase 2
        if (window.Phase2Config.urls) {
            ChunkingConfig.endpoints.processChunk = window.Phase2Config.urls.processChunk;
            ChunkingConfig.endpoints.refreshCSRF = window.Phase2Config.urls.refreshCSRF || '/operator/chunking/csrf-refresh';
            ChunkingConfig.endpoints.healthCheck = window.Phase2Config.urls.healthCheck;
        }
        
        // Adapter les seuils
        if (window.Phase2Config.upload) {
            ChunkingConfig.chunkSize = window.Phase2Config.upload.chunkSize || 100;
            ChunkingConfig.triggerThreshold = window.Phase2Config.upload.chunkingThreshold || 50;
            ChunkingConfig.maxRetries = window.Phase2Config.upload.maxRetries || 3;
        }
        
        // ✅ MARQUER LE MODE INSERTION DURING CHUNKING
        ChunkingConfig.insertionMode = 'DURING_CHUNKING';
        ChunkingConfig.phase2Mode = true;
        
        // Hook dans les fonctions Phase 2
        if (typeof window.handleFileUpload === 'function') {
            console.log('🎯 Hook détecté : handleFileUpload pour Phase 2');
            
            const originalHandleFileUpload = window.handleFileUpload;
            window.handleFileUpload = function(file) {
                console.log('📁 Intercepted handleFileUpload pour chunking Phase 2');
                
                // Traitement du fichier avec chunking si nécessaire
                return handleFileUploadWithChunking(file, originalHandleFileUpload);
            };
        }
        
        // Hook dans submitAdherents si disponible
        if (typeof window.submitAdherents === 'function') {
            console.log('🎯 Hook détecté : submitAdherents pour Phase 2');
            
            const originalSubmitAdherents = window.submitAdherents;
            window.submitAdherents = function(adherentsData) {
                console.log('📤 Intercepted submitAdherents pour chunking Phase 2');
                
                if (shouldUseChunking(adherentsData)) {
                    return processImportWithChunking(adherentsData);
                } else {
                    return originalSubmitAdherents(adherentsData);
                }
            };
        }
        
        console.log('✅ Intégration Phase 2 chunking terminée - INSERTION DURING CHUNKING activée');
        return true;
    }
    
    return false;
}

/**
 * ✅ NOUVELLE MÉTHODE : Traitement fichier avec chunking Phase 2
 */
async function handleFileUploadWithChunking(file, originalHandler) {
    try {
        console.log('📁 Analyse fichier pour chunking Phase 2:', {
            name: file.name,
            size: file.size,
            type: file.type
        });
        
        // Parser le fichier d'abord
        const adherentsData = await parseFileToAdherents(file);
        
        if (shouldUseChunking(adherentsData)) {
            console.log('🔄 Fichier volumineux détecté - Activation chunking Phase 2');
            console.log('✅ SOLUTION: INSERTION DURING CHUNKING');
            return await processImportWithChunking(adherentsData);
        } else {
            console.log('📁 Fichier standard - Traitement normal');
            return await originalHandler(file);
        }
    } catch (error) {
        console.error('❌ Erreur traitement fichier chunking Phase 2:', error);
        throw error;
    }
}

/**
 * ✅ NOUVELLE MÉTHODE : Parser fichier vers adhérents
 */
async function parseFileToAdherents(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                let adherentsData = [];
                
                if (file.name.endsWith('.csv')) {
                    // Parser CSV avec PapaParse
                    Papa.parse(e.target.result, {
                        header: true,
                        skipEmptyLines: true,
                        dynamicTyping: true,
                        complete: function(results) {
                            console.log('✅ CSV parsé Phase 2:', results.data.length, 'lignes');
                            adherentsData = results.data;
                            resolve(adherentsData);
                        },
                        error: function(error) {
                            console.error('❌ Erreur parsing CSV:', error);
                            reject(error);
                        }
                    });
                } else if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
                    // Parser Excel avec SheetJS
                    const workbook = XLSX.read(e.target.result, { type: 'binary' });
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    adherentsData = XLSX.utils.sheet_to_json(firstSheet);
                    
                    console.log('✅ Excel parsé Phase 2:', adherentsData.length, 'lignes');
                    resolve(adherentsData);
                } else {
                    reject(new Error('Format de fichier non supporté Phase 2'));
                }
            } catch (error) {
                console.error('❌ Erreur traitement fichier Phase 2:', error);
                reject(error);
            }
        };
        
        reader.onerror = function() {
            reject(new Error('Erreur lecture fichier Phase 2'));
        };
        
        if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
            reader.readAsBinaryString(file);
        } else {
            reader.readAsText(file);
        }
    });
}

/**
 * ✅ FONCTION DEBUG : Vérifier la configuration des URLs
 */
function debugChunkingConfig() {
    console.log('🔧 DEBUG Configuration Chunking URLs:');
    console.log('- Phase2Config.urls:', window.Phase2Config?.urls);
    console.log('- ChunkingConfig.endpoints:', ChunkingConfig.endpoints);
    console.log('- URL processChunk finale:', ChunkingConfig.endpoints.processChunk);
    
    if (!ChunkingConfig.endpoints.processChunk || ChunkingConfig.endpoints.processChunk.includes('undefined')) {
        console.error('❌ ERREUR: URL processChunk incorrecte !');
    } else {
        console.log('✅ URL processChunk OK');
    }
}

// Appeler le debug après configuration
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        // ✅ FORCER la mise à jour des endpoints AVANT le debug
        const updateSuccess = updateChunkingEndpoints();
        console.log('🔄 Mise à jour endpoints:', updateSuccess ? 'SUCCESS' : 'FALLBACK');
        
        // Puis debug
        debugChunkingConfig();
    }, 1000); // Attendre que Phase2Config soit chargé
});


function updateChunkingEndpoints() {
    if (typeof window.Phase2Config !== 'undefined' && window.Phase2Config.urls) {
        console.log('🔧 Mise à jour des endpoints chunking depuis Phase2Config');
        
        // ✅ CORRECTION : Utiliser les vrais noms de propriétés
        ChunkingConfig.endpoints.processChunk = window.Phase2Config.urls.processChunk;
        ChunkingConfig.endpoints.refreshCSRF = window.Phase2Config.urls.refreshCSRF;  
        ChunkingConfig.endpoints.healthCheck = window.Phase2Config.urls.healthCheck;
        
        console.log('✅ Endpoints mis à jour:', ChunkingConfig.endpoints);
        console.log('🔧 URL processChunk finale:', ChunkingConfig.endpoints.processChunk);
        
        return true; // Succès
    } else {
        console.log('⚠️ Phase2Config non disponible, utilisation des endpoints par défaut');
        
        // ✅ FALLBACK avec URLs par défaut
        ChunkingConfig.endpoints.processChunk = '/operator/chunking/process-chunk';
        ChunkingConfig.endpoints.refreshCSRF = '/operator/chunking/csrf-refresh';
        ChunkingConfig.endpoints.healthCheck = '/operator/chunking/health';
        
        return false; // Fallback utilisé
    }
}



// ========================================
// INTÉGRATION AVEC ORGANISATION-CREATE.JS
// ========================================

function hookIntoExistingImport() {
    if (window.handleAdherentFileImport && !window.originalHandleAdherentFileImport) {
        window.originalHandleAdherentFileImport = window.handleAdherentFileImport;
        
        window.handleAdherentFileImport = async function(fileInput) {
            const file = fileInput.files[0];
            if (!file) return;
            
            console.log('🔍 Hook chunking v1.5: analyse du fichier', file.name);
            
            try {
                const adherentsData = await window.readAdherentFile(file);
                
                if (!adherentsData || adherentsData.length === 0) {
                    if (typeof window.showNotification === 'function') {
                        window.showNotification('❌ Le fichier est vide ou invalide', 'danger');
                    }
                    return;
                }
                
                console.log(`📊 ${adherentsData.length} adhérents détectés`);
                
                if (shouldUseChunking(adherentsData)) {
                    console.log('🚀 Chunking activé pour ce fichier (v1.5)');
                    
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(
                            `📦 Gros volume détecté (${adherentsData.length} adhérents). ` +
                            `Traitement par lots activé avec normalisation CSV v1.5.`,
                            'info',
                            8000
                        );
                    }
                    
                    return await processImportWithChunking(adherentsData, null);
                    
                } else {
                    console.log('📝 Volume normal, traitement standard');
                    return await window.originalHandleAdherentFileImport.call(this, fileInput);
                }
                
            } catch (error) {
                console.error('❌ Erreur dans le hook chunking v1.5:', error);
                return await window.originalHandleAdherentFileImport.call(this, fileInput);
            }
        };
        
        console.log('✅ Hook chunking v1.5 installé sur handleAdherentFileImport');
    }
}

// ========================================
// INITIALISATION VERSION 1.5
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation chunking-import.js v2.0 - INSERTION DURING CHUNKING');
    
    setTimeout(() => {
        
        if (typeof window.Phase2Config !== 'undefined' && window.Phase2Config.urls) {
            console.log('🔧 Initialisation endpoints depuis Phase2Config');
            ChunkingConfig.endpoints.processChunk = window.Phase2Config.urls.processChunk;
            ChunkingConfig.endpoints.refreshCSRF = window.Phase2Config.urls.refreshCSRF;
            ChunkingConfig.endpoints.healthCheck = window.Phase2Config.urls.healthCheck;
            console.log('✅ Endpoints initialisés:', ChunkingConfig.endpoints);
        }
        // Prioriser l'intégration Phase 2 si disponible
        const phase2Integrated = hookIntoPhase2Import();
        
        console.log('✅ Intégration chunking v2.0 terminée - INSERTION DURING CHUNKING');
    }, 500);
    
});


// Exposer les fonctions principales
window.ChunkingImport = {
    ChunkManager,
    ProgressTracker,
    ImportProcessor,
    processImportWithChunking,
    shouldUseChunking,
    hookIntoPhase2Import,
    handleFileUploadWithChunking,
    parseFileToAdherents,
    config: ChunkingConfig,
    version: '2.0-INSERTION-DURING-CHUNKING'
};

console.log(`
🎉 ========================================================================
   PNGDI - SYSTÈME DE CHUNKING v2.0 - INSERTION DURING CHUNKING
   ========================================================================
   
   ✅ Version: 2.0 - PHASE 2 + INSERTION DURING CHUNKING
   🔄 Mode: Phase 2 prioritaire avec fallback organisation-create.js
   📁 Parser: CSV/Excel intégré pour Phase 2
   🚀 Solution: INSERTION DURING CHUNKING activée
========================================================================
`);