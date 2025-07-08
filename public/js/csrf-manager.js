/**
 * ========================================================================
 * SGLP - Gestionnaire CSRF Autonome v2.0
 * Fichier: public/js/csrf-manager.js
 * À charger AVANT organisation-create.js
 * ========================================================================
 */

 (function() {
    'use strict';
    
    console.log('🔐 Chargement CSRFManager autonome...');

    class CSRFManager {
        constructor() {
            this.debug = true;
            this.lastTokenRefresh = null;
            this.refreshAttempts = 0;
            this.maxRefreshAttempts = 3;
            
            console.log('🔐 CSRFManager initialisé v2.0');
        }

        /**
         * Diagnostic complet du contexte CSRF
         */
        diagnoseCSRFContext() {
            const context = {
                metaToken: this.getMetaToken(),
                inputToken: this.getInputToken(),
                laravelToken: this.getLaravelToken(),
                xsrfCookie: this.getXSRFCookie(),
                sessionCookies: this.getSessionCookies(),
                pageAge: this.getPageAge(),
                timestamp: new Date().toISOString()
            };

            if (this.debug) {
                console.log('🔍 === DIAGNOSTIC CSRF COMPLET ===');
                console.log('Meta CSRF:', context.metaToken ? context.metaToken.substring(0, 10) + '...' : 'MANQUANT');
                console.log('Input CSRF:', context.inputToken ? context.inputToken.substring(0, 10) + '...' : 'MANQUANT');
                console.log('Laravel CSRF:', context.laravelToken ? context.laravelToken.substring(0, 10) + '...' : 'MANQUANT');
                console.log('Cookie XSRF:', context.xsrfCookie ? 'PRÉSENT' : 'MANQUANT');
                console.log('Session cookies:', Object.keys(context.sessionCookies).join(', ') || 'AUCUN');
                console.log('Âge de la page:', context.pageAge, 'minutes');
            }

            return context;
        }

        /**
         * Récupération robuste du token CSRF actuel
         */
        async getCurrentToken() {
            // Méthode 1: Meta tag (priorité)
            let token = this.getMetaToken();
            
            // Méthode 2: Input caché
            if (!this.isValidToken(token)) {
                token = this.getInputToken();
            }

            // Méthode 3: Variable Laravel globale
            if (!this.isValidToken(token)) {
                token = this.getLaravelToken();
            }

            // Méthode 4: Rafraîchissement serveur
            if (!this.isValidToken(token)) {
                token = await this.refreshTokenFromServer();
            }

            return token;
        }

        /**
         * Rafraîchissement token depuis le serveur
         */
        async refreshTokenFromServer() {
            if (this.refreshAttempts >= this.maxRefreshAttempts) {
                throw new Error('Limite de rafraîchissement CSRF atteinte');
            }

            this.refreshAttempts++;
            
            try {
                console.log('🔄 Rafraîchissement token CSRF depuis serveur...');
                
                const response = await fetch('/csrf-token', {
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
                const newToken = data.csrf_token;

                if (!this.isValidToken(newToken)) {
                    throw new Error('Token CSRF reçu invalide');
                }

                // Mettre à jour tous les emplacements
                this.updateAllTokenLocations(newToken);
                this.lastTokenRefresh = Date.now();

                console.log('✅ Token CSRF rafraîchi avec succès');
                return newToken;

            } catch (error) {
                console.error('❌ Erreur rafraîchissement CSRF:', error);
                throw error;
            }
        }

        /**
         * Mise à jour de tous les emplacements du token
         */
        updateAllTokenLocations(newToken) {
            // Meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', newToken);
            }

            // Inputs cachés
            const tokenInputs = document.querySelectorAll('input[name="_token"]');
            tokenInputs.forEach(input => {
                input.value = newToken;
            });

            // Variable Laravel globale
            if (window.Laravel) {
                window.Laravel.csrfToken = newToken;
            }

            // Headers AJAX par défaut si jQuery est présent
            if (window.$ && $.ajaxSetup) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': newToken
                    }
                });
            }
        }

        /**
         * Validation token CSRF
         */
        isValidToken(token) {
            return token && 
                   typeof token === 'string' && 
                   token.length >= 40 && 
                   !/^\s*$/.test(token);
        }

        /**
         * Getters pour les différentes sources de token
         */
        getMetaToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        }

        getInputToken() {
            return document.querySelector('input[name="_token"]')?.value;
        }

        getLaravelToken() {
            return window.Laravel?.csrfToken;
        }

        getXSRFCookie() {
            return this.getCookie('XSRF-TOKEN');
        }

        getSessionCookies() {
            const cookies = {};
            document.cookie.split(';').forEach(cookie => {
                const [name, value] = cookie.trim().split('=');
                if (name && (name.includes('session') || name.includes('XSRF') || name.includes('pngdi'))) {
                    cookies[name] = value ? 'PRÉSENT' : 'VIDE';
                }
            });
            return cookies;
        }

        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        getPageAge() {
            if (performance.timing) {
                const pageLoadTime = performance.timing.navigationStart;
                return Math.floor((Date.now() - pageLoadTime) / 1000 / 60);
            }
            return 0;
        }

        /**
         * Test de connectivité session
         */
        async testSessionConnectivity() {
            try {
                const response = await fetch('/csrf-debug', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('🔐 Test session:', data);
                    return data;
                }
                return null;
            } catch (error) {
                console.error('❌ Test session échoué:', error);
                return null;
            }
        }

        /**
         * Reset du gestionnaire
         */
        reset() {
            this.refreshAttempts = 0;
            this.lastTokenRefresh = null;
        }
    }

    // ========================================================================
    // FONCTION PRINCIPALE : Soumission avec gestion CSRF
    // ========================================================================
    async function submitFormWithCSRFHandling(formData, url, options = {}) {
        const maxAttempts = 2;
        let attempt = 1;

        while (attempt <= maxAttempts) {
            try {
                console.log(`🔄 Tentative de soumission ${attempt}/${maxAttempts}`);

                // Diagnostic CSRF complet
                const csrfContext = window.CSRFManager.diagnoseCSRFContext();
                
                // Récupération token robuste
                const csrfToken = await window.CSRFManager.getCurrentToken();
                
                if (!window.CSRFManager.isValidToken(csrfToken)) {
                    throw new Error('Token CSRF invalide après récupération');
                }

                console.log('🔐 Token CSRF validé pour soumission:', csrfToken.substring(0, 10) + '...');

                // Ajouter le token aux données
                if (formData instanceof FormData) {
                    formData.set('_token', csrfToken);
                } else {
                    formData._token = csrfToken;
                }

                // Configuration requête
                const requestConfig = {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    },
                    body: formData,
                    credentials: 'same-origin',
                    ...options
                };

                // Timeout configuré
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), options.timeout || 120000);
                requestConfig.signal = controller.signal;

                const response = await fetch(url, requestConfig);
                clearTimeout(timeoutId);

                // Gestion succès
                if (response.ok) {
                    window.CSRFManager.reset();
                    return await response.json();
                }

                // Gestion erreur 419 CSRF
                if (response.status === 419) {
                    console.warn(`🔐 Erreur 419 détectée (tentative ${attempt})`);
                    
                    if (attempt < maxAttempts) {
                        console.log('🔄 Tentative de récupération CSRF...');
                        await window.CSRFManager.refreshTokenFromServer();
                        attempt++;
                        continue;
                    }
                    
                    throw new Error('Token CSRF invalide après rafraîchissement - veuillez recharger la page');
                }

                // Autres erreurs HTTP
                let errorData;
                try {
                    errorData = await response.json();
                } catch (e) {
                    errorData = { message: `Erreur HTTP ${response.status}` };
                }
                throw new Error(errorData.message || `Erreur HTTP ${response.status}`);

            } catch (error) {
                console.error(`❌ Erreur soumission tentative ${attempt}:`, error);
                
                if (error.name === 'AbortError') {
                    throw new Error('Timeout de soumission - veuillez réessayer');
                }
                
                if (attempt === maxAttempts) {
                    throw new Error(`Session expirée après ${maxAttempts} tentatives - veuillez recharger la page`);
                }
                
                attempt++;
            }
        }
    }

    // Attendre que le DOM soit prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    function initialize() {
        // Instance globale
        window.CSRFManager = new CSRFManager();
        
        // Export fonction de soumission
        window.submitFormWithCSRFHandling = submitFormWithCSRFHandling;
        
        console.log('✅ CSRFManager autonome chargé avec succès');
        
        // Test initial de connectivité (optionnel)
        if (window.CSRFManager) {
            window.CSRFManager.testSessionConnectivity().catch(err => {
                console.warn('⚠️ Test initial CSRF échoué:', err.message);
            });
        }
    }

})();