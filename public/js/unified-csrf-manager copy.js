/**
 * ========================================================================
 * MODULE CSRF UNIFIÉ - SGLP
 * Centralise la gestion CSRF pour toutes les fonctionnalités
 * Compatible avec: workflow-2phases.js, chunking-import.js, organisation-create.js
 * ========================================================================
 */

window.UnifiedCSRFManager = {
    
    // Configuration
    config: {
        refreshEndpoint: '/csrf-token',
        maxRetries: 3,
        retryDelay: 1000,
        tokenMinLength: 10,
        debug: true
    },
    
    // État interne
    state: {
        lastRefresh: null,
        refreshPromise: null,
        retryCount: 0
    },
    
    /**
     * ✅ MÉTHODE PRINCIPALE : Obtenir token CSRF actuel
     */
    async getCurrentToken() {
        this.log('🔍 Récupération token CSRF unifié');
        
        // Essayer les sources locales d'abord
        let token = this.getLocalToken();
        
        if (this.isValidToken(token)) {
            this.log('✅ Token local valide trouvé');
            return token;
        }
        
        // Refresh depuis le serveur si nécessaire
        this.log('🔄 Token local invalide, refresh depuis serveur...');
        return await this.refreshFromServer();
    },
    
    /**
     * ✅ RÉCUPÉRATION TOKEN LOCAL (multi-sources)
     */
    getLocalToken() {
        // Source 1: Meta tag
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (this.isValidToken(token)) return token;
        
        // Source 2: Input caché
        token = document.querySelector('input[name="_token"]')?.value;
        if (this.isValidToken(token)) return token;
        
        // Source 3: Variable Laravel globale
        token = window.Laravel?.csrfToken;
        if (this.isValidToken(token)) return token;
        
        return null;
    },
    
    /**
     * ✅ REFRESH TOKEN DEPUIS SERVEUR
     */
    async refreshFromServer() {
        // Éviter les appels multiples simultanés
        if (this.state.refreshPromise) {
            this.log('⏳ Refresh en cours, attente...');
            return await this.state.refreshPromise;
        }
        
        this.state.refreshPromise = this._performRefresh();
        
        try {
            const token = await this.state.refreshPromise;
            this.state.refreshPromise = null;
            return token;
        } catch (error) {
            this.state.refreshPromise = null;
            throw error;
        }
    },
    
    /**
     * ✅ EXÉCUTION DU REFRESH
     */
    async _performRefresh() {
        try {
            this.log('📡 Requête refresh CSRF vers serveur...');
            
            const response = await fetch(this.config.refreshEndpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            const newToken = data.token || data.csrf_token;
            
            if (!this.isValidToken(newToken)) {
                throw new Error('Token CSRF reçu invalide du serveur');
            }
            
            // Mettre à jour tous les emplacements
            this.updateAllLocations(newToken);
            this.state.lastRefresh = Date.now();
            
            this.log('✅ Token CSRF unifié mis à jour:', newToken.substring(0, 10) + '...');
            return newToken;
            
        } catch (error) {
            this.log('❌ Erreur refresh CSRF unifié:', error.message);
            throw error;
        }
    },
    
    /**
     * ✅ MISE À JOUR DE TOUS LES EMPLACEMENTS
     */
    updateAllLocations(token) {
        // Meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', token);
        }
        
        // Inputs cachés
        document.querySelectorAll('input[name="_token"]').forEach(input => {
            input.value = token;
        });
        
        // Variable Laravel globale
        if (window.Laravel) {
            window.Laravel.csrfToken = token;
        }
        
        // Notifier les autres modules
        this.notifyModules(token);
        
        this.log('🔄 Tous les emplacements CSRF mis à jour');
    },
    
    /**
     * ✅ NOTIFICATION AUX AUTRES MODULES
     */
    notifyModules(token) {
        // Notifier workflow-2phases.js
        if (window.Workflow2Phases && typeof window.Workflow2Phases.onCSRFUpdated === 'function') {
            window.Workflow2Phases.onCSRFUpdated(token);
        }
        
        // Notifier chunking-import.js
        if (window.ChunkingImport && typeof window.ChunkingImport.onCSRFUpdated === 'function') {
            window.ChunkingImport.onCSRFUpdated(token);
        }
        
        // Émettre événement global
        window.dispatchEvent(new CustomEvent('csrf-token-updated', { 
            detail: { token, timestamp: Date.now() } 
        }));
    },
    
    /**
     * ✅ VALIDATION TOKEN
     */
    isValidToken(token) {
        return token && 
               typeof token === 'string' && 
               token.length >= this.config.tokenMinLength &&
               token !== 'undefined';
    },
    
    /**
     * ✅ SOUMISSION AVEC RETRY CSRF AUTOMATIQUE
     */
    async submitWithCSRFRetry(url, data, options = {}) {
        const maxAttempts = this.config.maxRetries;
        
        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            try {
                this.log(`🔄 Tentative ${attempt}/${maxAttempts} - Soumission avec CSRF`);
                
                // Obtenir token actuel
                const token = await this.getCurrentToken();
                
                // Préparer la requête
                const requestData = this.prepareRequestData(data, token);
                const requestOptions = this.prepareRequestOptions(options, token);
                
                // Envoyer la requête
                const response = await fetch(url, {
                    method: 'POST',
                    ...requestOptions,
                    body: this.prepareRequestBody(requestData, requestOptions)
                });
                
                // Retry automatique sur erreur 419
                if (response.status === 419 && attempt < maxAttempts) {
                    this.log('⚠️ Erreur 419 CSRF, retry avec nouveau token...');
                    await this.refreshFromServer();
                    await this.delay(this.config.retryDelay);
                    continue;
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                this.log(`✅ Soumission réussie après ${attempt} tentative(s)`);
                return await response.json();
                
            } catch (error) {
                this.log(`❌ Tentative ${attempt} échouée:`, error.message);
                
                if (attempt === maxAttempts) {
                    throw error;
                }
                
                await this.delay(this.config.retryDelay * attempt);
            }
        }
    },
    
    /**
     * ✅ PRÉPARATION DES DONNÉES DE REQUÊTE
     */
    prepareRequestData(data, token) {
        if (data instanceof FormData) {
            data.set('_token', token);
            return data;
        } else if (typeof data === 'object') {
            return { ...data, _token: token };
        } else {
            return { data, _token: token };
        }
    },
    
    /**
     * ✅ PRÉPARATION DES OPTIONS DE REQUÊTE
     */
    prepareRequestOptions(options, token) {
        return {
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            credentials: 'same-origin',
            ...options
        };
    },
    
    /**
     * ✅ PRÉPARATION DU BODY DE REQUÊTE
     */
    prepareRequestBody(data, options) {
        if (data instanceof FormData) {
            return data;
        } else if (options.headers['Content-Type']?.includes('application/json')) {
            return JSON.stringify(data);
        } else {
            return data;
        }
    },
    
    /**
     * ✅ UTILITAIRES
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },
    
    log(...args) {
        if (this.config.debug) {
            console.log('[UnifiedCSRF]', ...args);
        }
    },
    
    /**
     * ✅ DIAGNOSTIC COMPLET
     */
    diagnose() {
        const context = {
            localToken: this.getLocalToken(),
            validToken: this.isValidToken(this.getLocalToken()),
            lastRefresh: this.state.lastRefresh,
            refreshAge: this.state.lastRefresh ? (Date.now() - this.state.lastRefresh) / 1000 : null,
            refreshEndpoint: this.config.refreshEndpoint,
            metaExists: !!document.querySelector('meta[name="csrf-token"]'),
            inputExists: !!document.querySelector('input[name="_token"]'),
            laravelExists: !!window.Laravel?.csrfToken
        };
        
        this.log('🔍 Diagnostic CSRF unifié:', context);
        return context;
    }
};

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    window.UnifiedCSRFManager.log('🚀 Module CSRF unifié initialisé');
    
    // Diagnostic initial
    setTimeout(() => {
        window.UnifiedCSRFManager.diagnose();
    }, 1000);
});