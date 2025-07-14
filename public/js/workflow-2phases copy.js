/**
 * MODULE WORKFLOW 2 PHASES - PNGDI
 * Fichier: public/js/workflow-2phases.js
 * 
 * Ce module étend le système existant pour supporter le workflow 2 phases
 * Sans modifier massivement organisation-create.js
 */

// =============================================
// CONFIGURATION GLOBALE
// =============================================

window.Workflow2Phases = {
    enabled: true,
    debug: true,
    
    config: {
        routes: {
            phase1: '/operator/organisations/store-phase1',
            // ✅ CORRECTION CRITIQUE : Route corrigée dossiers au lieu d'organisations
            phase2_template: '/operator/dossiers/{dossier}/adherents-import',
            //confirmation_template: '/operator/dossiers/confirmation/{dossier}'
            confirmation_template: '/operator/dossiers/{dossier}/adherents-import'
        },
        options: {
            autoRedirectPhase2: true,
            saveAdherentsForPhase2: true,
            showChoiceDialog: true
        }
    },
    
    state: {
        currentPhase: 1,
        phase1Response: null,
        savedAdherents: null
    }
};

// =============================================
// MÉTHODES PRINCIPALES
// =============================================

/**
 * Initialiser le workflow 2 phases
 * À appeler depuis organisation-create.js
 */
window.Workflow2Phases.init = function() {
    if (!this.enabled) {
        this.log('Workflow 2 phases désactivé');
        return false;
    }
    
    this.log('Initialisation workflow 2 phases');
    
    // Injecter les hooks dans l'application existante
    this.injectHooks();
    
    // Configurer les événements
    this.setupEventListeners();
    
    // Vérifier si on revient de Phase 1
    this.checkPhase1Continuation();
    
    this.log('Workflow 2 phases initialisé avec succès');
    return true;
};

/**
 * Intercepter la soumission du formulaire principal
 */
window.Workflow2Phases.interceptSubmission = function(originalSubmissionFunction) {
    this.log('Interception de la soumission pour workflow 2 phases');
    
    // Sauvegarder la fonction originale
    this.originalSubmit = originalSubmissionFunction;
    
    // Décider du workflow à utiliser
    if (this.shouldUsePhase1()) {
        return this.submitPhase1();
    } else {
        this.log('Fallback vers soumission originale');
        return this.originalSubmit();
    }
};

/**
 * Déterminer si on doit utiliser le workflow 2 phases
 */
window.Workflow2Phases.shouldUsePhase1 = function() {
    // Vérifier si activé
    if (!this.enabled) return false;
    
    // Vérifier s'il y a des adhérents (indication de gros volume)
    const adherents = this.getAdherentsFromForm();
    
    // Utiliser Phase 1 si :
    // - Plus de 50 adhérents (risque de timeout)
    // - Ou option forcée
    // - Ou détection automatique
    const adherentsCount = Array.isArray(adherents) ? adherents.length : 0;
    
    this.log(`Analyse décision workflow: ${adherentsCount} adhérents`);
    
    return adherentsCount > 50 || this.config.options.forcePhase1;
};

/**
 * ✅ SOUMISSION PHASE 1 CORRIGÉE - Gestion CSRF robuste
 */
window.Workflow2Phases.submitPhase1 = async function() {
    this.log('🚀 Début soumission Phase 1');
    
    try {
        // Afficher le loading
        this.showLoadingState('Création de votre organisation (Phase 1)...');
        
        // Préparer les données
        const formData = this.preparePhase1Data();
        
        // ✅ SOUMISSION AVEC GESTION CSRF ET RETRY
        const response = await this.submitWithCSRFRetry(formData);
        
        // Traiter le succès
        this.handlePhase1Success(response);
        
    } catch (error) {
        this.log('❌ Erreur Phase 1:', error);
        this.handlePhase1Error(error);
    }
};

/**
 * Préparer les données pour Phase 1
 */
window.Workflow2Phases.preparePhase1Data = function() {
    // Récupérer toutes les données du formulaire via l'API existante
    let formData;
    
    // Essayer différentes méthodes selon l'implémentation
    if (window.OrganisationApp && typeof window.OrganisationApp.collectAllFormData === 'function') {
        formData = window.OrganisationApp.collectAllFormData();
    } else if (window.OrganisationApp && window.OrganisationApp.formData) {
        formData = {...window.OrganisationApp.formData};
    } else {
        // Fallback: collecter manuellement
        formData = this.collectFormDataFallback();
    }
    
    // Extraire et sauvegarder les adhérents
    const adherents = formData.adherents || [];
    if (adherents.length > 0 && this.config.options.saveAdherentsForPhase2) {
        this.saveAdherentsForPhase2(adherents);
        this.log(`💾 ${adherents.length} adhérents sauvegardés pour Phase 2`);
    }
    
    // Retirer les adhérents des données Phase 1
    delete formData.adherents;
    
    // Ajouter les marqueurs
    formData._phase = 1;
    formData._workflow = '2_phases';
    formData._adherents_pending = adherents.length;
    
    return formData;
};

/**
 * Gérer le succès de Phase 1
 */
window.Workflow2Phases.handlePhase1Success = function(response) {
    this.hideLoadingState();
    
    if (response.success && (response.phase === 1 || response.phase === "complete")) {
        this.log('🎉 Phase 1 complétée avec succès');
        
        // Sauvegarder la réponse
        this.state.phase1Response = response;
        sessionStorage.setItem('workflow_phase1_response', JSON.stringify(response));
        
        // Afficher notification
        this.showSuccessNotification('✅ Phase 1 complétée ! Organisation créée avec succès.');
        
        // Vérifier s'il y a des adhérents à traiter
        const hasAdherents = this.state.savedAdherents && this.state.savedAdherents.length > 0;

        // Si la réponse indique "confirmation", c'est qu'il n'y a pas d'adhérents
        const shouldRedirectToPhase2 = hasAdherents && response.redirect_to !== "confirmation";

        if (shouldRedirectToPhase2) {
            this.log('📋 Adhérents détectés, redirection vers Phase 2');
            if (this.config.options.autoRedirectPhase2) {
                this.showPhase2RedirectDialog(response);
            } else {
                this.redirectToPhase2(response);
                }
        } else {
            this.log('🏁 Pas d\'adhérents ou création complète, redirection vers confirmation');
            this.redirectToConfirmation(response);
        }
        
    } else {
            // Gestion spéciale si pas d'adhérents
            if (response.success && response.phase === "complete" && response.redirect_to === "confirmation") {
            this.log('🏁 Phase 1 complète sans adhérents - redirection vers confirmation');
            this.showSuccessNotification('✅ Organisation créée avec succès !');
            this.redirectToConfirmation(response);
            return;
            }
            throw new Error(response.message || 'Réponse Phase 1 invalide');
    }
};

/**
 * Afficher le dialog de choix Phase 2
 */
window.Workflow2Phases.showPhase2RedirectDialog = function(phase1Response) {
    const adherentsCount = this.state.savedAdherents ? this.state.savedAdherents.length : 0;
    
    // Créer le modal
    const modalHTML = `
        <div class="modal fade" id="phase2ChoiceModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle me-2"></i>
                            Organisation créée avec succès !
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle me-2"></i>
                            Votre organisation a été enregistrée avec le numéro de récépissé : 
                            <strong>${phase1Response.data.numero_recepisse || 'En cours'}</strong>
                        </div>
                        
                        <h6>Prochaine étape :</h6>
                        <p>Vous avez <strong>${adherentsCount} adhérents</strong> prêts à être importés.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h6>Ajouter maintenant</h6>
                                        <p class="small text-muted">Importez vos adhérents immédiatement</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-secondary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clock fa-2x text-secondary mb-2"></i>
                                        <h6>Plus tard</h6>
                                        <p class="small text-muted">Ajoutez les adhérents depuis votre espace</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" id="phase2-later">
                            <i class="fas fa-clock me-2"></i>
                            Plus tard
                        </button>
                        <button type="button" class="btn btn-success" id="phase2-now">
                            <i class="fas fa-users me-2"></i>
                            Ajouter maintenant
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter au DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('phase2ChoiceModal'));
    
    // Événements
    document.getElementById('phase2-now').addEventListener('click', () => {
        modal.hide();
        this.redirectToPhase2(phase1Response);
    });
    
    document.getElementById('phase2-later').addEventListener('click', () => {
        modal.hide();
        this.redirectToConfirmation(phase1Response);
    });
    
    modal.show();
};

/**
 * Redirection vers Phase 2
 */
window.Workflow2Phases.redirectToPhase2 = function(phase1Response) {
    this.log('🔄 Redirection vers Phase 2');
    
    if (phase1Response.data && phase1Response.data.dossier_id) {
        const phase2Url = this.config.routes.phase2_template.replace('{dossier}', phase1Response.data.dossier_id);
        
        this.showLoadingState('Redirection vers l\'import des adhérents...');
        
        setTimeout(() => {
            window.location.href = phase2Url;
        }, 1500);
    } else {
        this.log('❌ Dossier ID non fourni pour Phase 2');
        this.showErrorNotification('Erreur: impossible de rediriger vers Phase 2');
    }
};

/**
 * Redirection vers confirmation
 */
window.Workflow2Phases.redirectToConfirmation = function(phase1Response) {
    this.log('🏁 Redirection vers confirmation');
    
    if (phase1Response.data && phase1Response.data.dossier_id) {
    const confirmationUrl = this.config.routes.confirmation_template.replace('{dossier}', phase1Response.data.dossier_id);
    
    this.log('🏁 Redirection vers confirmation:', confirmationUrl);
    this.showLoadingState('Redirection vers la confirmation...');
    
    setTimeout(() => {
        window.location.href = confirmationUrl;
    }, 1500);
} else if (response.success && response.phase === "complete") {
    // Fallback si dossier_id pas dans data mais dans response directe
    this.log('🏁 Fallback redirection: organisation créée sans adhérents');
    this.showSuccessNotification('Organisation créée avec succès !');
    
    // Redirection simple vers la liste des organisations
    setTimeout(() => {
        window.location.href = '/operator/organisations';
    }, 2000);
    
    // Nettoyer les données temporaires
    this.cleanupTemporaryData();
}
};

// =============================================
// MÉTHODES UTILITAIRES
// =============================================

/**
 * Sauvegarder les adhérents pour Phase 2
 */
window.Workflow2Phases.saveAdherentsForPhase2 = function(adherents) {
    this.state.savedAdherents = adherents;
    sessionStorage.setItem('workflow_phase2_adherents', JSON.stringify(adherents));
};

/**
 * Récupérer les adhérents du formulaire
 */
window.Workflow2Phases.getAdherentsFromForm = function() {
    if (window.OrganisationApp && window.OrganisationApp.adherents) {
        return window.OrganisationApp.adherents;
    }
    
    // Fallback
    try {
        const adherentsField = document.querySelector('input[name="adherents"], textarea[name="adherents"]');
        if (adherentsField && adherentsField.value) {
            return JSON.parse(adherentsField.value);
        }
    } catch (e) {
        this.log('Erreur parsing adhérents:', e);
    }
    
    return [];
};

/**
 * Collecter les données du formulaire (fallback)
 */
window.Workflow2Phases.collectFormDataFallback = function() {
    const formData = {};
    const form = document.querySelector('#organisation-form, form[data-form="organisation"]');
    
    if (form) {
        const formDataObj = new FormData(form);
        for (let [key, value] of formDataObj.entries()) {
            formData[key] = value;
        }
    }
    
    return formData;
};

/**
 * Obtenir le token CSRF
 */
window.Workflow2Phases.getCSRFToken = function() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
           document.querySelector('input[name="_token"]')?.value ||
           window.Laravel?.csrfToken;
};


/**
 * ✅ MÉTHODES CSRF MANQUANTES - À ajouter après getCSRFToken
 */

/**
 * Rafraîchir le token CSRF depuis le serveur
 */
window.Workflow2Phases.refreshCSRFToken = async function() {
    this.log('🔄 Refresh token CSRF depuis serveur...');
    
    try {
        const response = await fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        const newToken = data.token || data.csrf_token;
        
        if (!newToken) {
            throw new Error('Token CSRF non reçu du serveur');
        }

        // Mettre à jour tous les emplacements
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', newToken);
        }

        const tokenInputs = document.querySelectorAll('input[name="_token"]');
        tokenInputs.forEach(input => {
            input.value = newToken;
        });

        if (window.Laravel) {
            window.Laravel.csrfToken = newToken;
        }

        this.log('✅ Token CSRF rafraîchi avec succès');
        return newToken;

    } catch (error) {
        this.log('❌ Erreur refresh CSRF:', error);
        throw error;
    }
};

/**
 * Soumission avec retry automatique en cas d'erreur CSRF
 */
window.Workflow2Phases.submitWithCSRFRetry = async function(formData, maxAttempts = 2) {
    for (let attempt = 1; attempt <= maxAttempts; attempt++) {
        try {
            this.log(`🔄 Tentative ${attempt}/${maxAttempts} - Soumission Phase 1`);
            
            // Récupérer/rafraîchir token CSRF
            let csrfToken = this.getCSRFToken();
            if (!csrfToken || csrfToken.length < 10) {
                csrfToken = await this.refreshCSRFToken();
            }

            // Préparer la requête avec token
            const requestData = {
                ...formData,
                _token: csrfToken,
                _phase: 1
            };

            // Envoyer la requête
            const response = await fetch(this.config.routes.phase1, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(requestData)
            });

            // Retry automatique en cas d'erreur 419
            if (response.status === 419 && attempt < maxAttempts) {
                this.log('⚠️ Erreur 419 CSRF, retry avec nouveau token...');
                await this.refreshCSRFToken();
                continue;
            }

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            this.log(`✅ Phase 1 réussie après ${attempt} tentative(s)`);
            return data;

        } catch (error) {
            this.log(`❌ Tentative ${attempt} échouée:`, error.message);
            
            if (attempt === maxAttempts) {
                throw error;
            }
            
            // Pause avant retry
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }
};

/**
 * Méthodes d'interface utilisateur
 */
window.Workflow2Phases.showLoadingState = function(message = 'Traitement en cours...') {
    // Essayer d'utiliser le système existant
    if (window.OrganisationApp && typeof window.OrganisationApp.showLoading === 'function') {
        window.OrganisationApp.showLoading(message);
    } else {
        this.log('🔄 Loading:', message);
        // Fallback simple
        this.showSimpleLoading(message);
    }
};

window.Workflow2Phases.hideLoadingState = function() {
    if (window.OrganisationApp && typeof window.OrganisationApp.hideLoading === 'function') {
        window.OrganisationApp.hideLoading();
    } else {
        this.hideSimpleLoading();
    }
};

window.Workflow2Phases.showSuccessNotification = function(message) {
    if (window.OrganisationApp && typeof window.OrganisationApp.showNotification === 'function') {
        window.OrganisationApp.showNotification(message, 'success');
    } else {
        this.log('✅ Success:', message);
        this.showSimpleNotification(message, 'success');
    }
};

window.Workflow2Phases.showErrorNotification = function(message) {
    if (window.OrganisationApp && typeof window.OrganisationApp.showNotification === 'function') {
        window.OrganisationApp.showNotification(message, 'error');
    } else {
        this.log('❌ Error:', message);
        this.showSimpleNotification(message, 'error');
    }
};

/**
 * Notifications simples (fallback)
 */
window.Workflow2Phases.showSimpleNotification = function(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHTML);
};

/**
 * Loading simple (fallback)
 */
window.Workflow2Phases.showSimpleLoading = function(message) {
    if (document.getElementById('workflow-loading')) return;
    
    const loadingHTML = `
        <div id="workflow-loading" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
             style="background: rgba(0,0,0,0.7); z-index: 9999;">
            <div class="card">
                <div class="card-body text-center">
                    <div class="spinner-border text-primary mb-3"></div>
                    <p class="mb-0">${message}</p>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', loadingHTML);
};

window.Workflow2Phases.hideSimpleLoading = function() {
    const loading = document.getElementById('workflow-loading');
    if (loading) {
        loading.remove();
    }
};

/**
 * Gestion des erreurs
 */
window.Workflow2Phases.handlePhase1Error = function(error) {
    this.hideLoadingState();
    this.log('❌ Erreur Phase 1:', error);
    
    // Analyser le type d'erreur
    let errorMessage = 'Erreur lors de la création de l\'organisation';
    
    if (typeof error === 'string') {
        // Si c'est juste un message (comme dans votre cas)
        if (error.includes('Organisation créée avec succès')) {
            // Ce n'est pas vraiment une erreur, c'est un succès mal géré
            this.log('✅ Faux erreur détectée - c\'est en fait un succès');
            this.showSuccessNotification('✅ ' + error);
            
            // Redirection simple vers les organisations
            setTimeout(() => {
                window.location.href = '/operator/organisations';
            }, 2000);
            return;
        }
        errorMessage += ': ' + error;
    } else if (error.message) {
        errorMessage += ': ' + error.message;
    }
    
    // Afficher notification d'erreur seulement si c'est vraiment une erreur
    this.showErrorNotification('❌ ' + errorMessage);
};

/**
 * Logging avec debug
 */
window.Workflow2Phases.log = function(...args) {
    if (this.debug) {
        console.log('[Workflow2Phases]', ...args);
    }
};

/**
 * Nettoyer les données temporaires
 */
window.Workflow2Phases.cleanupTemporaryData = function() {
    sessionStorage.removeItem('workflow_phase1_response');
    sessionStorage.removeItem('workflow_phase2_adherents');
    this.state.phase1Response = null;
    this.state.savedAdherents = null;
};

/**
 * Hooks et intégration
 */
window.Workflow2Phases.injectHooks = function() {
    // Hook sera ajouté dans l'étape suivante
    this.log('Hooks injectés');
};

window.Workflow2Phases.setupEventListeners = function() {
    // Événements seront configurés dans l'étape suivante
    this.log('Event listeners configurés');
};

window.Workflow2Phases.checkPhase1Continuation = function() {
    const phase1Response = sessionStorage.getItem('workflow_phase1_response');
    if (phase1Response) {
        this.log('Continuation depuis Phase 1 détectée');
        this.state.phase1Response = JSON.parse(phase1Response);
    }


/**
 * Nettoyer les données temporaires
 */
window.Workflow2Phases.cleanupTemporaryData = function() {
    try {
        // Nettoyer sessionStorage
        sessionStorage.removeItem('workflow_phase1_response');
        sessionStorage.removeItem('workflow_phase2_adherents');
        
        // Réinitialiser l'état
        this.state.currentPhase = 1;
        this.state.phase1Response = null;
        this.state.savedAdherents = null;
        
        this.log('🧹 Données temporaires nettoyées');
    } catch (error) {
        this.log('❌ Erreur nettoyage:', error);
    }
};


};