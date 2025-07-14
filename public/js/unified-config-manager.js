/**
 * ========================================================================
 * GESTIONNAIRE DE CONFIGURATION UNIFIÉ - SGLP v2.1
 * Centralise la gestion des URLs et configurations
 * ✅ MISE À JOUR: Amélioration détection et fallbacks
 * ========================================================================
 */

window.UnifiedConfigManager = {
    
    // Configuration centralisée
    config: {
        debug: true,
        initialized: false,
        version: '2.1-MISE-A-JOUR',
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
            templateDownload: null,
            storeAdherents: null
        }
    },
    
    /**
     * ✅ INITIALISATION DEPUIS PHASE2CONFIG - AMÉLIORÉE
     */
    initializeFromPhase2Config() {
        this.log('🔧 Initialisation depuis Phase2Config v2.1...');
        
        if (typeof window.Phase2Config === 'undefined') {
            this.log('⚠️ Phase2Config non disponible, fallback...');
            this.initializeFallback();
            return false;
        }
        
        try {
            // URLs principales - AMÉLIORATION: Vérification plus robuste
            if (window.Phase2Config.urls) {
                const urls = window.Phase2Config.urls;
                
                this.config.endpoints.processChunk = urls.processChunk || urls.process_chunk;
                this.config.endpoints.refreshCSRF = urls.refreshCSRF || urls.refresh_csrf;
                this.config.endpoints.healthCheck = urls.healthCheck || urls.health_check;
                this.config.endpoints.storeAdherents = urls.storeAdherents || urls.store_adherents;
                this.config.endpoints.confirmation = urls.confirmation;
                this.config.endpoints.templateDownload = urls.templateDownload || urls.template_download;
                this.config.endpoints.storePhase1 = urls.storePhase1 || urls.store_phase1;
                
                this.log('✅ URLs Phase2Config détectées:', Object.keys(urls));
            }
            
            // Configuration chunking
            if (window.Phase2Config.upload) {
                this.config.chunking = {
                    chunkSize: window.Phase2Config.upload.chunkSize || 500,
                    threshold: window.Phase2Config.upload.chunkingThreshold || 200,
                    maxRetries: window.Phase2Config.upload.maxRetries || 3,
                    timeoutPerChunk: window.Phase2Config.upload.timeoutPerChunk || 30000
                };
                this.log('✅ Configuration chunking initialisée');
            }
            
            // Métadonnées
            this.config.dossier = {
                id: window.Phase2Config.dossierId,
                organisationId: window.Phase2Config.organisationId
            };
            
            this.config.initialized = true;
            this.log('✅ Configuration initialisée depuis Phase2Config');
            
            // Mise à jour immédiate des configs existantes
            this.updateExistingConfigs();
            
            return true;
            
        } catch (error) {
            this.log('❌ Erreur initialisation Phase2Config:', error);
            this.initializeFallback();
            return false;
        }
    },
    
    /**
     * ✅ INITIALISATION FALLBACK - AMÉLIORÉE
     */
    initializeFallback() {
        this.log('🔄 Initialisation fallback v2.1...');
        
        const currentDossierId = this.getCurrentDossierId();
        
        this.config.endpoints = {
            // Chunking
            processChunk: '/operator/chunking/process-chunk',
            refreshCSRF: '/csrf-token', // ✅ CORRECTION: URL simplifiée
            healthCheck: '/operator/chunking/health',
            
            // Workflow
            storePhase1: '/operator/organisations/store-phase1',
            storeAdherents: `/operator/dossiers/${currentDossierId}/store-adherents`,
            confirmation: `/operator/dossiers/${currentDossierId}/confirmation`,
            adherentsImport: `/operator/dossiers/${currentDossierId}/adherents-import`,
            
            // Templates
            templateDownload: '/operator/templates/adherents-excel'
        };
        
        this.config.chunking = {
            chunkSize: 500,
            threshold: 200,
            maxRetries: 3,
            timeoutPerChunk: 30000
        };
        
        this.config.dossier = {
            id: currentDossierId,
            organisationId: null
        };
        
        this.config.initialized = true;
        this.log('✅ Configuration fallback initialisée pour dossier:', currentDossierId);
    },
    
    /**
     * ✅ OBTENIR L'ID DU DOSSIER ACTUEL - AMÉLIORÉ
     */
    getCurrentDossierId() {
        // Source 1: Phase2Config
        if (window.Phase2Config?.dossierId) {
            this.log('📍 Dossier ID depuis Phase2Config:', window.Phase2Config.dossierId);
            return window.Phase2Config.dossierId;
        }
        
        // Source 2: Meta tag
        const metaDossier = document.querySelector('meta[name="dossier-id"]');
        if (metaDossier) {
            const id = metaDossier.getAttribute('content');
            this.log('📍 Dossier ID depuis meta tag:', id);
            return id;
        }
        
        // Source 3: URL actuelle - AMÉLIORATION: Patterns multiples
        const patterns = [
            /\/dossiers\/(\d+)/,
            /\/operator\/dossiers\/(\d+)/,
            /dossier[_-]?id[=:](\d+)/i
        ];
        
        for (const pattern of patterns) {
            const match = window.location.pathname.match(pattern) || 
                         window.location.search.match(pattern);
            if (match) {
                const id = match[1];
                this.log('📍 Dossier ID depuis URL:', id);
                return id;
            }
        }
        
        // Source 4: Session/Local storage
        const sessionId = sessionStorage.getItem('current_dossier_id') || 
                         localStorage.getItem('current_dossier_id');
        if (sessionId) {
            this.log('📍 Dossier ID depuis storage:', sessionId);
            return sessionId;
        }
        
        // Fallback
        this.log('⚠️ Dossier ID non trouvé, utilisation fallback: 1');
        return '1';
    },
    
    /**
     * ✅ OBTENIR UNE URL ENDPOINT - AMÉLIORÉ
     */
    getEndpoint(name) {
        if (!this.config.initialized) {
            this.initializeFromPhase2Config();
        }
        
        let endpoint = this.config.endpoints[name];
        
        if (!endpoint) {
            this.log(`⚠️ Endpoint '${name}' non trouvé dans:`, Object.keys(this.config.endpoints));
            return null;
        }
        
        // Remplacer les placeholders dynamiques
        const currentDossier = this.getCurrentDossierId();
        endpoint = endpoint.replace(/\{dossier\}/g, currentDossier)
                          .replace(/\{dossierId\}/g, currentDossier)
                          .replace(/\{id\}/g, currentDossier);
        
        this.log(`📍 Endpoint '${name}':`, endpoint);
        return endpoint;
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
     * ✅ METTRE À JOUR LES CONFIGURATIONS EXISTANTES - AMÉLIORÉ
     */
    updateExistingConfigs() {
        this.log('🔄 Mise à jour des configurations existantes v2.1...');
        
        // Mettre à jour ChunkingConfig si présent
        if (window.ChunkingConfig && window.ChunkingConfig.endpoints) {
            const oldEndpoints = { ...window.ChunkingConfig.endpoints };
            
            window.ChunkingConfig.endpoints.processChunk = this.getEndpoint('processChunk');
            window.ChunkingConfig.endpoints.refreshCSRF = this.getEndpoint('refreshCSRF');
            window.ChunkingConfig.endpoints.healthCheck = this.getEndpoint('healthCheck');
            
            this.log('✅ ChunkingConfig mis à jour:', {
                ancien: oldEndpoints,
                nouveau: window.ChunkingConfig.endpoints
            });
        }
        
        // Mettre à jour Workflow2Phases si présent
        if (window.Workflow2Phases && window.Workflow2Phases.config) {
            const oldRoutes = { ...window.Workflow2Phases.config.routes };
            
            if (this.getEndpoint('storePhase1')) {
                window.Workflow2Phases.config.routes.phase1 = this.getEndpoint('storePhase1');
            }
            if (this.getEndpoint('storeAdherents')) {
                window.Workflow2Phases.config.routes.phase2_template = this.getEndpoint('storeAdherents');
            }
            if (this.getEndpoint('confirmation')) {
                window.Workflow2Phases.config.routes.confirmation_template = this.getEndpoint('confirmation');
            }
            
            this.log('✅ Workflow2Phases mis à jour:', {
                ancien: oldRoutes,
                nouveau: window.Workflow2Phases.config.routes
            });
        }
        
        // Émettre événement global
        window.dispatchEvent(new CustomEvent('config-updated', {
            detail: { 
                endpoints: this.config.endpoints, 
                timestamp: Date.now(),
                version: this.config.version
            }
        }));
        
        this.log('📡 Événement config-updated émis');
    },
    
    /**
     * ✅ VALIDATION DE LA CONFIGURATION
     */
    validateConfig() {
        const required = ['processChunk', 'storeAdherents', 'confirmation'];
        const missing = [];
        const invalid = [];
        
        required.forEach(endpoint => {
            const url = this.getEndpoint(endpoint);
            if (!url) {
                missing.push(endpoint);
            } else if (!url.startsWith('/') && !url.startsWith('http')) {
                invalid.push({ endpoint, url });
            }
        });
        
        if (missing.length > 0) {
            this.log('❌ Endpoints manquants:', missing);
        }
        if (invalid.length > 0) {
            this.log('❌ Endpoints invalides:', invalid);
        }
        
        const isValid = missing.length === 0 && invalid.length === 0;
        
        if (isValid) {
            this.log('✅ Configuration validée');
        }
        
        return {
            valid: isValid,
            missing,
            invalid
        };
    },
    
    /**
     * ✅ MONITORING ET AUTO-CORRECTION
     */
    startMonitoring() {
        this.log('🔍 Démarrage monitoring configuration...');
        
        // Vérification périodique de Phase2Config
        const checkInterval = setInterval(() => {
            if (!this.config.initialized && window.Phase2Config) {
                this.log('🔄 Phase2Config détecté tardivement, réinitialisation...');
                this.initializeFromPhase2Config();
                clearInterval(checkInterval);
            }
        }, 2000);
        
        // Arrêt automatique après 30 secondes
        setTimeout(() => {
            clearInterval(checkInterval);
            this.log('⏹️ Monitoring configuration arrêté');
        }, 30000);
    },
    
    /**
     * ✅ DIAGNOSTIC DE LA CONFIGURATION - AMÉLIORÉ
     */
    diagnose() {
        const validation = this.validateConfig();
        
        const diagnostic = {
            version: this.config.version,
            initialized: this.config.initialized,
            phase2ConfigExists: !!window.Phase2Config,
            phase2ConfigUrls: window.Phase2Config?.urls ? Object.keys(window.Phase2Config.urls) : [],
            endpoints: this.config.endpoints,
            endpointsResolved: Object.keys(this.config.endpoints).reduce((acc, key) => {
                acc[key] = this.getEndpoint(key);
                return acc;
            }, {}),
            chunking: this.config.chunking,
            dossier: this.config.dossier,
            currentDossierId: this.getCurrentDossierId(),
            validation,
            timestamp: new Date().toISOString()
        };
        
        this.log('🔍 Diagnostic configuration v2.1:', diagnostic);
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

// Initialisation automatique avec monitoring
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation immédiate
    window.UnifiedConfigManager.initializeFromPhase2Config();
    
    // Démarrage du monitoring
    window.UnifiedConfigManager.startMonitoring();
    
    // Retry après délai si Phase2Config n'était pas prêt
    setTimeout(() => {
        if (!window.UnifiedConfigManager.config.initialized) {
            window.UnifiedConfigManager.log('🔄 Retry initialisation...');
            window.UnifiedConfigManager.initializeFromPhase2Config();
        } else {
            // Forcer mise à jour des autres configurations
            window.UnifiedConfigManager.updateExistingConfigs();
        }
        
        // Diagnostic final
        window.UnifiedConfigManager.diagnose();
    }, 1000);
});