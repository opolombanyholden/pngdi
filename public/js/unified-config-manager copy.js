/**
 * ========================================================================
 * GESTIONNAIRE DE CONFIGURATION UNIFIÉ - SGLP
 * Centralise la gestion des URLs et configurations
 * ========================================================================
 */

window.UnifiedConfigManager = {
    
    // Configuration centralisée
    config: {
        debug: true,
        initialized: false,
        endpoints: {
            // Workflow
            storePhase1: null,
            adherentsImport: null,
            confirmation: null,
            
            // Chunking
            processChunk: null,
            refreshCSRF: null,
            healthCheck: null,
            
            // Templates
            templateDownload: null
        }
    },
    
    /**
     * ✅ INITIALISATION DEPUIS PHASE2CONFIG
     */
    initializeFromPhase2Config() {
        this.log('🔧 Initialisation depuis Phase2Config...');
        
        if (typeof window.Phase2Config === 'undefined') {
            this.log('⚠️ Phase2Config non disponible');
            this.initializeFallback();
            return false;
        }
        
        try {
            // URLs principales
            if (window.Phase2Config.urls) {
                this.config.endpoints.processChunk = window.Phase2Config.urls.processChunk;
                this.config.endpoints.refreshCSRF = window.Phase2Config.urls.refreshCSRF;
                this.config.endpoints.healthCheck = window.Phase2Config.urls.healthCheck;
                this.config.endpoints.storeAdherents = window.Phase2Config.urls.storeAdherents;
                this.config.endpoints.confirmation = window.Phase2Config.urls.confirmation;
                this.config.endpoints.templateDownload = window.Phase2Config.urls.templateDownload;
            }
            
            // Configuration chunking
            if (window.Phase2Config.upload) {
                this.config.chunking = {
                    chunkSize: window.Phase2Config.upload.chunkSize || 500,
                    threshold: window.Phase2Config.upload.chunkingThreshold || 200,
                    maxRetries: window.Phase2Config.upload.maxRetries || 3,
                    timeoutPerChunk: window.Phase2Config.upload.timeoutPerChunk || 30000
                };
            }
            
            // Métadonnées
            this.config.dossier = {
                id: window.Phase2Config.dossierId,
                organisationId: window.Phase2Config.organisationId
            };
            
            this.config.initialized = true;
            this.log('✅ Configuration initialisée depuis Phase2Config');
            return true;
            
        } catch (error) {
            this.log('❌ Erreur initialisation Phase2Config:', error);
            this.initializeFallback();
            return false;
        }
    },
    
    /**
     * ✅ INITIALISATION FALLBACK
     */
    initializeFallback() {
        this.log('🔄 Initialisation fallback...');
        
        this.config.endpoints = {
            processChunk: '/operator/chunking/process-chunk',
            refreshCSRF: '/operator/chunking/csrf-refresh',
            healthCheck: '/operator/chunking/health',
            storePhase1: '/operator/organisations/store-phase1',
            storeAdherents: `/operator/dossiers/${this.getCurrentDossierId()}/store-adherents`,
            confirmation: `/operator/dossiers/${this.getCurrentDossierId()}/confirmation`,
            templateDownload: '/operator/templates/adherents-excel'
        };
        
        this.config.chunking = {
            chunkSize: 500,
            threshold: 200,
            maxRetries: 3,
            timeoutPerChunk: 30000
        };
        
        this.config.initialized = true;
        this.log('✅ Configuration fallback initialisée');
    },
    
    /**
     * ✅ OBTENIR L'ID DU DOSSIER ACTUEL
     */
    getCurrentDossierId() {
        // Source 1: Phase2Config
        if (window.Phase2Config?.dossierId) {
            return window.Phase2Config.dossierId;
        }
        
        // Source 2: Meta tag
        const metaDossier = document.querySelector('meta[name="dossier-id"]');
        if (metaDossier) {
            return metaDossier.getAttribute('content');
        }
        
        // Source 3: URL actuelle
        const urlMatch = window.location.pathname.match(/\/dossiers\/(\d+)/);
        if (urlMatch) {
            return urlMatch[1];
        }
        
        // Source 4: Session storage
        return sessionStorage.getItem('current_dossier_id') || '1';
    },
    
    /**
     * ✅ OBTENIR UNE URL ENDPOINT
     */
    getEndpoint(name) {
        if (!this.config.initialized) {
            this.initializeFromPhase2Config();
        }
        
        const endpoint = this.config.endpoints[name];
        
        if (!endpoint) {
            this.log(`⚠️ Endpoint '${name}' non trouvé`);
            return null;
        }
        
        // Remplacer les placeholders dynamiques
        return endpoint.replace('{dossier}', this.getCurrentDossierId())
                      .replace('{dossierId}', this.getCurrentDossierId());
    },
    
    /**
     * ✅ OBTENIR LA CONFIGURATION CHUNKING
     */
    getChunkingConfig() {
        if (!this.config.initialized) {
            this.initializeFromPhase2Config();
        }
        
        return this.config.chunking;
    },
    
    /**
     * ✅ METTRE À JOUR LES CONFIGURATIONS EXISTANTES
     */
    updateExistingConfigs() {
        this.log('🔄 Mise à jour des configurations existantes...');
        
        // Mettre à jour ChunkingConfig si présent
        if (window.ChunkingConfig) {
            window.ChunkingConfig.endpoints = {
                processChunk: this.getEndpoint('processChunk'),
                refreshCSRF: this.getEndpoint('refreshCSRF'),
                healthCheck: this.getEndpoint('healthCheck')
            };
            this.log('✅ ChunkingConfig mis à jour');
        }
        
        // Mettre à jour Workflow2Phases si présent
        if (window.Workflow2Phases && window.Workflow2Phases.config) {
            window.Workflow2Phases.config.routes.phase1 = this.getEndpoint('storePhase1');
            window.Workflow2Phases.config.routes.phase2_template = this.getEndpoint('storeAdherents');
            window.Workflow2Phases.config.routes.confirmation_template = this.getEndpoint('confirmation');
            this.log('✅ Workflow2Phases mis à jour');
        }
        
        // Émettre événement global
        window.dispatchEvent(new CustomEvent('config-updated', {
            detail: { endpoints: this.config.endpoints, timestamp: Date.now() }
        }));
    },
    
    /**
     * ✅ VALIDATION DE LA CONFIGURATION
     */
    validateConfig() {
        const required = ['processChunk', 'storeAdherents', 'confirmation'];
        const missing = [];
        
        required.forEach(endpoint => {
            if (!this.getEndpoint(endpoint)) {
                missing.push(endpoint);
            }
        });
        
        if (missing.length > 0) {
            this.log('❌ Endpoints manquants:', missing);
            return false;
        }
        
        this.log('✅ Configuration validée');
        return true;
    },
    
    /**
     * ✅ DIAGNOSTIC DE LA CONFIGURATION
     */
    diagnose() {
        const diagnostic = {
            initialized: this.config.initialized,
            phase2ConfigExists: !!window.Phase2Config,
            endpoints: this.config.endpoints,
            chunking: this.config.chunking,
            dossier: this.config.dossier,
            currentDossierId: this.getCurrentDossierId(),
            validConfig: this.validateConfig()
        };
        
        this.log('🔍 Diagnostic configuration:', diagnostic);
        return diagnostic;
    },
    
    /**
     * ✅ LOGGING
     */
    log(...args) {
        if (this.config.debug) {
            console.log('[UnifiedConfig]', ...args);
        }
    }
};

// Initialisation automatique avec retry
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation immédiate
    window.UnifiedConfigManager.initializeFromPhase2Config();
    
    // Retry après délai si Phase2Config n'était pas prêt
    setTimeout(() => {
        if (!window.UnifiedConfigManager.config.initialized) {
            window.UnifiedConfigManager.log('🔄 Retry initialisation...');
            window.UnifiedConfigManager.initializeFromPhase2Config();
        }
        
        // Mettre à jour les autres configurations
        window.UnifiedConfigManager.updateExistingConfigs();
        
        // Diagnostic final
        window.UnifiedConfigManager.diagnose();
    }, 1000);
});