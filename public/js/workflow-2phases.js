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
            phase2_template: '/operator/dossiers/{dossier}/adherents-import',
            confirmation_template: '/operator/dossiers/confirmation/{dossier}'
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
 * Soumission Phase 1 (organisation sans adhérents)
 */
window.Workflow2Phases.submitPhase1 = function() {
    this.log('🚀 Début soumission Phase 1');
    
    try {
        // Préparer les données
        const formData = this.preparePhase1Data();
        
        // Afficher le loading
        this.showLoadingState('Création de votre organisation (Phase 1)...');
        
        // Envoyer la requête
        return fetch(this.config.routes.phase1, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCSRFToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            this.log('✅ Phase 1 réussie:', data);
            this.handlePhase1Success(data);
        })
        .catch(error => {
            this.log('❌ Erreur Phase 1:', error);
            this.handlePhase1Error(error);
        });
        
    } catch (error) {
        this.log('❌ Erreur préparation Phase 1:', error);
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
    
    if (response.success && response.phase === 1) {
        this.log('🎉 Phase 1 complétée avec succès');
        
        // Sauvegarder la réponse
        this.state.phase1Response = response;
        sessionStorage.setItem('workflow_phase1_response', JSON.stringify(response));
        
        // Afficher notification
        this.showSuccessNotification('✅ Phase 1 complétée ! Organisation créée avec succès.');
        
        // Décider de la suite
        const hasAdherents = this.state.savedAdherents && this.state.savedAdherents.length > 0;
        
        if (hasAdherents) {
            if (this.config.options.autoRedirectPhase2) {
                this.showPhase2RedirectDialog(response);
            } else {
                this.redirectToPhase2(response);
            }
        } else {
            this.redirectToConfirmation(response);
        }
        
    } else {
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
        
        this.showLoadingState('Redirection vers la confirmation...');
        
        setTimeout(() => {
            window.location.href = confirmationUrl;
        }, 1500);
    } else {
        this.log('❌ Dossier ID non fourni pour confirmation');
        this.showErrorNotification('Erreur: impossible de rediriger vers la confirmation');
    }
    
    // Nettoyer les données temporaires
    this.cleanupTemporaryData();
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
    
    let errorMessage = 'Erreur lors de la création de l\'organisation';
    
    if (error.response && error.response.errors) {
        const validationErrors = Object.values(error.response.errors).flat();
        errorMessage = validationErrors.join('<br>');
    } else if (error.message) {
        errorMessage = error.message;
    }
    
    this.showErrorNotification(errorMessage);
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
};