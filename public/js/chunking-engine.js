/**
 * ============================================================================
 * CHUNKING-ENGINE.JS - MOTEUR DE TRAITEMENT PAR LOTS
 * Version: 2.0 - Système de chunking adaptatif pour gros volumes
 * ============================================================================
 * 
 * Moteur principal pour le traitement par lots d'adhérents
 * Gestion automatique des timeouts, retry, pause/reprise
 */

window.ChunkingEngine = window.ChunkingEngine || {};

// ============================================================================
// CONFIGURATION ET ÉTAT
// ============================================================================

/**
 * Configuration par défaut du moteur de chunking
 */
window.ChunkingEngine.config = {
    // Paramètres de base
    chunkSize: 100,
    maxRetries: 3,
    retryDelay: 1000,
    pauseBetweenChunks: 500,
    
    // Seuils et limites
    maxConcurrentChunks: 1,
    timeoutPerChunk: 30000, // 30 secondes
    healthCheckInterval: 5000,
    
    // URLs et endpoints
    endpoints: {
        processChunk: '/api/chunking/process-chunk',
        healthCheck: '/api/chunking/health',
        refreshCSRF: '/api/chunking/csrf-refresh'
    },
    
    // Options avancées
    strictValidation: true,
    skipDuplicates: true,
    saveAnomalies: true,
    parallelValidation: false,
    memoryOptimization: false,
    progressVerbose: false
};

/**
 * État du moteur de chunking
 */
window.ChunkingEngine.state = {
    // État global
    isActive: false,
    isPaused: false,
    isCompleted: false,
    
    // Progression
    currentChunk: 0,
    totalChunks: 0,
    processedRecords: 0,
    totalRecords: 0,
    
    // Timing
    startTime: null,
    pauseTime: null,
    endTime: null,
    
    // Données
    chunks: [],
    results: {
        imported: 0,
        errors: 0,
        warnings: 0,
        anomalies: [],
        failed_chunks: []
    },
    
    // Callbacks
    callbacks: {
        onProgress: null,
        onChunkStart: null,
        onChunkComplete: null,
        onError: null,
        onComplete: null
    }
};

// ============================================================================
// INITIALISATION
// ============================================================================

/**
 * Initialiser le moteur de chunking
 */
window.ChunkingEngine.init = function() {
    console.log('🚀 Initialisation ChunkingEngine v2.0');
    
    try {
        // Charger la configuration depuis les préférences
        this.loadConfiguration();
        
        // Initialiser les utilitaires
        this.initializeUtils();
        
        // Configurer les gestionnaires d'événements
        this.setupEventHandlers();
        
        console.log('✅ ChunkingEngine initialisé avec succès');
        
    } catch (error) {
        console.error('❌ Erreur initialisation ChunkingEngine:', error);
    }
};

/**
 * Charger la configuration sauvegardée
 */
window.ChunkingEngine.loadConfiguration = function() {
    try {
        const savedConfig = localStorage.getItem('chunking-engine-config');
        if (savedConfig) {
            const config = JSON.parse(savedConfig);
            this.config = { ...this.config, ...config };
            console.log('⚙️ Configuration ChunkingEngine chargée');
        }
    } catch (error) {
        console.warn('⚠️ Erreur chargement configuration:', error);
    }
    
    // Appliquer la configuration globale si disponible
    if (window.ConfirmationConfig && window.ConfirmationConfig.chunking) {
        this.config = { ...this.config, ...window.ConfirmationConfig.chunking };
    }
};

/**
 * Initialiser les utilitaires
 */
window.ChunkingEngine.initializeUtils = function() {
    // Préparer axios avec configuration par défaut
    if (typeof axios !== 'undefined') {
        axios.defaults.timeout = this.config.timeoutPerChunk;
        axios.defaults.headers.common['X-CSRF-TOKEN'] = window.ConfirmationConfig?.csrf || '';
    }
};

/**
 * Configurer les gestionnaires d'événements
 */
window.ChunkingEngine.setupEventHandlers = function() {
    // Gestionnaire de fermeture de page
    window.addEventListener('beforeunload', (e) => {
        if (this.state.isActive && !this.state.isCompleted) {
            e.preventDefault();
            e.returnValue = 'Un import est en cours. Êtes-vous sûr de vouloir quitter ?';
            return e.returnValue;
        }
    });
    
    // Gestionnaire de perte de focus
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && this.state.isActive) {
            console.log('📱 Page masquée - chunking continue en arrière-plan');
        }
    });
};

// ============================================================================
// MÉTHODES PRINCIPALES
// ============================================================================

/**
 * Traiter un import avec chunking
 */
window.ChunkingEngine.processImportWithChunking = async function(adherentsData, options = {}) {
    console.log('🚀 Début processus chunking:', adherentsData.length, 'adhérents');
    
    try {
        // Initialiser le processus
        this.initializeProcess(adherentsData, options);
        
        // Préparer les chunks
        this.prepareChunks(adherentsData);
        
        // Démarrer le traitement
        await this.startProcessing();
        
        return this.state.results;
        
    } catch (error) {
        console.error('❌ Erreur processus chunking:', error);
        this.handleError(error);
        throw error;
    }
};

/**
 * Initialiser le processus
 */
window.ChunkingEngine.initializeProcess = function(adherentsData, options) {
    // Réinitialiser l'état
    this.state = {
        ...this.state,
        isActive: true,
        isPaused: false,
        isCompleted: false,
        currentChunk: 0,
        totalChunks: 0,
        processedRecords: 0,
        totalRecords: adherentsData.length,
        startTime: Date.now(),
        pauseTime: null,
        endTime: null,
        chunks: [],
        results: {
            imported: 0,
            errors: 0,
            warnings: 0,
            anomalies: [],
            failed_chunks: []
        },
        callbacks: {
            onProgress: options.onProgress || null,
            onChunkStart: options.onChunkStart || null,
            onChunkComplete: options.onChunkComplete || null,
            onError: options.onError || null,
            onComplete: options.onComplete || null
        }
    };
    
    // Appliquer les options de configuration
    if (options.chunkSize) this.config.chunkSize = options.chunkSize;
    if (options.delay) this.config.pauseBetweenChunks = options.delay;
    if (options.maxRetries) this.config.maxRetries = options.maxRetries;
    
    console.log('📋 Processus initialisé:', {
        totalRecords: this.state.totalRecords,
        chunkSize: this.config.chunkSize,
        estimatedChunks: Math.ceil(this.state.totalRecords / this.config.chunkSize)
    });
};

/**
 * Préparer les chunks
 */
window.ChunkingEngine.prepareChunks = function(adherentsData) {
    const chunkSize = this.config.chunkSize;
    const chunks = [];
    
    for (let i = 0; i < adherentsData.length; i += chunkSize) {
        const chunkData = adherentsData.slice(i, i + chunkSize);
        
        chunks.push({
            id: chunks.length + 1,
            data: chunkData,
            size: chunkData.length,
            startIndex: i,
            endIndex: Math.min(i + chunkSize - 1, adherentsData.length - 1),
            status: 'pending',
            attempts: 0,
            errors: [],
            warnings: [],
            processed: 0,
            startTime: null,
            endTime: null
        });
    }
    
    this.state.chunks = chunks;
    this.state.totalChunks = chunks.length;
    
    console.log(`📦 ${chunks.length} chunks préparés (taille: ${chunkSize})`);
};

/**
 * Démarrer le traitement
 */
window.ChunkingEngine.startProcessing = async function() {
    console.log('🎬 Démarrage traitement des chunks');
    
    // Vérification de santé initiale
    await this.healthCheck();
    
    // Traiter chaque chunk séquentiellement
    for (let i = 0; i < this.state.chunks.length; i++) {
        if (!this.state.isActive) {
            console.log('⏹️ Arrêt demandé');
            break;
        }
        
        // Gérer la pause
        await this.handlePause();
        
        const chunk = this.state.chunks[i];
        this.state.currentChunk = i + 1;
        
        console.log(`📦 Traitement chunk ${chunk.id}/${this.state.totalChunks}`);
        
        try {
            await this.processChunk(chunk);
        } catch (error) {
            console.error(`❌ Erreur chunk ${chunk.id}:`, error);
            await this.handleChunkError(chunk, error);
        }
        
        // Pause entre les chunks
        if (i < this.state.chunks.length - 1 && this.config.pauseBetweenChunks > 0) {
            await this.delay(this.config.pauseBetweenChunks);
        }
        
        // Vérification de santé périodique
        if (i % 5 === 0) {
            await this.healthCheck();
        }
    }
    
    // Finaliser le processus
    this.finalizeProcess();
};

/**
 * Traiter un chunk individuel
 */
window.ChunkingEngine.processChunk = async function(chunk) {
    chunk.status = 'processing';
    chunk.startTime = Date.now();
    chunk.attempts++;
    
    // Callback de début de chunk
    if (this.state.callbacks.onChunkStart) {
        this.state.callbacks.onChunkStart({
            number: chunk.id,
            total: this.state.totalChunks,
            size: chunk.size,
            startTime: chunk.startTime,
            processed: 0
        });
    }
    
    try {
        // Préparer les données pour l'envoi
        const payload = this.prepareChunkPayload(chunk);
        
        // Envoyer le chunk au serveur
        const response = await this.sendChunkToServer(payload);
        
        // Traiter la réponse
        this.processChunkResponse(chunk, response);
        
        chunk.status = 'completed';
        chunk.endTime = Date.now();
        
        console.log(`✅ Chunk ${chunk.id} terminé: ${chunk.processed}/${chunk.size}`);
        
        // Callback de fin de chunk
        if (this.state.callbacks.onChunkComplete) {
            this.state.callbacks.onChunkComplete({
                number: chunk.id,
                processed: chunk.processed,
                total: chunk.size,
                errors: chunk.errors.length,
                warnings: chunk.warnings.length,
                duration: chunk.endTime - chunk.startTime
            });
        }
        
        // Mettre à jour la progression globale
        this.updateProgress();
        
    } catch (error) {
        chunk.status = 'failed';
        chunk.endTime = Date.now();
        chunk.errors.push({
            message: error.message,
            timestamp: Date.now(),
            attempt: chunk.attempts
        });
        
        throw error;
    }
};

/**
 * Préparer le payload pour un chunk
 */
window.ChunkingEngine.prepareChunkPayload = function(chunk) {
    return {
        chunk_id: chunk.id,
        chunk_number: chunk.id,
        total_chunks: this.state.totalChunks,
        adherents: chunk.data.map(adherent => ({
            civilite: adherent.civilite,
            nom: adherent.nom,
            prenom: adherent.prenom,
            nip: adherent.nip,
            telephone: adherent.telephone || '',
            profession: adherent.profession || '',
            source: 'chunking'
        })),
        options: {
            strict_validation: this.config.strictValidation,
            skip_duplicates: this.config.skipDuplicates,
            save_anomalies: this.config.saveAnomalies
        },
        context: {
            dossier_id: window.ConfirmationConfig?.dossierId,
            organisation_id: window.ConfirmationConfig?.organisationId,
            session_id: this.generateSessionId()
        }
    };
};

/**
 * Envoyer un chunk au serveur
 */
window.ChunkingEngine.sendChunkToServer = async function(payload) {
    const url = this.config.endpoints.processChunk;
    
    console.log(`📡 Envoi chunk ${payload.chunk_id} vers ${url}`);
    
    const response = await axios.post(url, payload, {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: this.config.timeoutPerChunk
    });
    
    if (response.status !== 200) {
        throw new Error(`Erreur serveur: ${response.status} ${response.statusText}`);
    }
    
    return response.data;
};

/**
 * Traiter la réponse d'un chunk
 */
window.ChunkingEngine.processChunkResponse = function(chunk, response) {
    if (!response.success) {
        throw new Error(response.message || 'Erreur de traitement du chunk');
    }
    
    // Mettre à jour les statistiques du chunk
    chunk.processed = response.data?.processed || 0;
    
    if (response.data?.errors) {
        chunk.errors.push(...response.data.errors);
    }
    
    if (response.data?.warnings) {
        chunk.warnings.push(...response.data.warnings);
    }
    
    // Mettre à jour les résultats globaux
    this.state.results.imported += response.data?.imported || 0;
    this.state.results.errors += response.data?.errors?.length || 0;
    this.state.results.warnings += response.data?.warnings?.length || 0;
    
    if (response.data?.anomalies) {
        this.state.results.anomalies.push(...response.data.anomalies);
    }
    
    this.state.processedRecords += chunk.processed;
};

/**
 * Mettre à jour la progression
 */
window.ChunkingEngine.updateProgress = function() {
    const percentage = (this.state.processedRecords / this.state.totalRecords) * 100;
    const completedChunks = this.state.chunks.filter(c => c.status === 'completed').length;
    
    const progressData = {
        percentage: percentage,
        status: `Traitement chunk ${this.state.currentChunk}/${this.state.totalChunks}`,
        completedChunks: completedChunks,
        totalChunks: this.state.totalChunks,
        processedRecords: this.state.processedRecords,
        totalRecords: this.state.totalRecords,
        warnings: this.state.results.warnings,
        errors: this.state.results.errors,
        currentChunk: this.state.currentChunk
    };
    
    // Callback de progression
    if (this.state.callbacks.onProgress) {
        this.state.callbacks.onProgress(progressData);
    }
    
    // Mettre à jour le modal si ouvert
    if (window.updateModalProgress) {
        window.updateModalProgress(progressData);
    }
};

// ============================================================================
// GESTION DES ERREURS ET RETRY
// ============================================================================

/**
 * Gérer une erreur de chunk
 */
window.ChunkingEngine.handleChunkError = async function(chunk, error) {
    console.error(`❌ Erreur chunk ${chunk.id} (tentative ${chunk.attempts}):`, error);
    
    if (chunk.attempts < this.config.maxRetries) {
        console.log(`🔄 Retry chunk ${chunk.id} (${chunk.attempts}/${this.config.maxRetries})`);
        
        // Attendre avant de réessayer
        await this.delay(this.config.retryDelay * chunk.attempts);
        
        // Réessayer le chunk
        await this.processChunk(chunk);
    } else {
        console.error(`💥 Chunk ${chunk.id} échoué définitivement après ${this.config.maxRetries} tentatives`);
        
        chunk.status = 'failed';
        this.state.results.failed_chunks.push({
            id: chunk.id,
            error: error.message,
            attempts: chunk.attempts
        });
        
        // Callback d'erreur
        if (this.state.callbacks.onError) {
            this.state.callbacks.onError(error, chunk);
        }
    }
};

/**
 * Gérer une erreur globale
 */
window.ChunkingEngine.handleError = function(error) {
    console.error('💥 Erreur globale ChunkingEngine:', error);
    
    this.state.isActive = false;
    this.state.endTime = Date.now();
    
    if (this.state.callbacks.onError) {
        this.state.callbacks.onError(error);
    }
};

// ============================================================================
// CONTRÔLES (PAUSE/REPRISE/ARRÊT)
// ============================================================================

/**
 * Mettre en pause
 */
window.ChunkingEngine.pause = function() {
    console.log('⏸️ Chunking mis en pause');
    this.state.isPaused = true;
    this.state.pauseTime = Date.now();
};

/**
 * Reprendre
 */
window.ChunkingEngine.resume = function() {
    console.log('▶️ Chunking repris');
    this.state.isPaused = false;
    this.state.pauseTime = null;
};

/**
 * Arrêter
 */
window.ChunkingEngine.stop = function() {
    console.log('⏹️ Chunking arrêté');
    this.state.isActive = false;
    this.state.endTime = Date.now();
};

/**
 * Gérer la pause
 */
window.ChunkingEngine.handlePause = async function() {
    while (this.state.isPaused && this.state.isActive) {
        await this.delay(1000);
    }
};

// ============================================================================
// FINALISATION
// ============================================================================

/**
 * Finaliser le processus
 */
window.ChunkingEngine.finalizeProcess = function() {
    this.state.isActive = false;
    this.state.isCompleted = true;
    this.state.endTime = Date.now();
    
    const duration = this.state.endTime - this.state.startTime;
    const successfulChunks = this.state.chunks.filter(c => c.status === 'completed').length;
    
    console.log('🏁 Processus chunking terminé:', {
        duration: Math.round(duration / 1000) + 's',
        chunks: `${successfulChunks}/${this.state.totalChunks}`,
        records: `${this.state.processedRecords}/${this.state.totalRecords}`,
        imported: this.state.results.imported,
        errors: this.state.results.errors,
        warnings: this.state.results.warnings
    });
    
    // Sauvegarder la configuration
    this.saveConfiguration();
    
    // Callback de fin
    if (this.state.callbacks.onComplete) {
        this.state.callbacks.onComplete({
            imported: this.state.results.imported,
            errors: this.state.results.errors,
            warnings: this.state.results.warnings,
            anomalies: this.state.results.anomalies.length,
            totalChunks: this.state.totalChunks,
            successfulChunks: successfulChunks,
            duration: duration,
            failed_chunks: this.state.results.failed_chunks
        });
    }
};

// ============================================================================
// UTILITAIRES
// ============================================================================

/**
 * Vérification de santé
 */
window.ChunkingEngine.healthCheck = async function() {
    try {
        const response = await axios.get(this.config.endpoints.healthCheck, {
            timeout: 5000
        });
        
        if (!response.data.healthy) {
            throw new Error('Serveur en mauvaise santé');
        }
        
        console.log('💚 Vérification santé: OK');
        
    } catch (error) {
        console.warn('💛 Vérification santé échouée:', error.message);
        // Ne pas arrêter le processus pour une vérification de santé échouée
    }
};

/**
 * Générer un ID de session
 */
window.ChunkingEngine.generateSessionId = function() {
    return 'chunk_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
};

/**
 * Délai asynchrone
 */
window.ChunkingEngine.delay = function(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
};

/**
 * Sauvegarder la configuration
 */
window.ChunkingEngine.saveConfiguration = function() {
    try {
        const configToSave = {
            chunkSize: this.config.chunkSize,
            maxRetries: this.config.maxRetries,
            pauseBetweenChunks: this.config.pauseBetweenChunks,
            strictValidation: this.config.strictValidation,
            skipDuplicates: this.config.skipDuplicates,
            saveAnomalies: this.config.saveAnomalies
        };
        
        localStorage.setItem('chunking-engine-config', JSON.stringify(configToSave));
        console.log('💾 Configuration ChunkingEngine sauvegardée');
        
    } catch (error) {
        console.warn('⚠️ Erreur sauvegarde configuration:', error);
    }
};

/**
 * Obtenir les statistiques actuelles
 */
window.ChunkingEngine.getStats = function() {
    return {
        state: this.state.isActive ? 'active' : (this.state.isCompleted ? 'completed' : 'inactive'),
        progress: {
            percentage: (this.state.processedRecords / this.state.totalRecords) * 100,
            processedRecords: this.state.processedRecords,
            totalRecords: this.state.totalRecords,
            currentChunk: this.state.currentChunk,
            totalChunks: this.state.totalChunks
        },
        results: this.state.results,
        timing: {
            startTime: this.state.startTime,
            endTime: this.state.endTime,
            duration: this.state.endTime ? (this.state.endTime - this.state.startTime) : (Date.now() - this.state.startTime)
        }
    };
};

// ============================================================================
// INITIALISATION AUTOMATIQUE
// ============================================================================

// Log de chargement
console.log('🔧 ChunkingEngine v2.0 chargé - Prêt pour initialisation');