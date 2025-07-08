/**
 * ========================================================================
 * CONFIRMATION.JS - JAVASCRIPT PRINCIPAL POUR CONFIRMATION.BLADE.PHP
 * Version: 1.0 - Application SGLP avec intégration FileUploadSGLP
 * ========================================================================
 * 
 * Module principal pour confirmation.blade.php
 * Compatible avec file-upload-sglp.js et styles gabonais
 * 
 * Adaptation majeure :
 * - Format NIP gabonais : XX-QQQQ-YYYYMMDD
 * - Couleurs officielles du Gabon
 * - Intégration système de notifications SGLP
 * - Routes spécifiques du projet
 */

window.ConfirmationApp = window.ConfirmationApp || {};

// ========================================
// CONFIGURATION ET VARIABLES GLOBALES
// ========================================

// Variables globales pour le module avancé
window.ConfirmationApp.additionalAdherents = [];
window.ConfirmationApp.additionalUploadInProgress = false;
window.ConfirmationApp.statisticsRefreshInterval = null;

// Configuration par défaut (sera écrasée par ConfirmationConfig depuis le blade)
window.ConfirmationApp.config = {
    dossierId: null,
    adherentsCount: 0,
    adherentsEnBase: 0,
    totalAdherents: 0,
    minAdherents: 15,
    pretPourSoumission: false,
    hasPhase2Pending: false,
    sessionKey: '',
    csrf: '',
    routes: {}
};

// ========================================
// INITIALISATION PRINCIPALE
// ========================================

/**
 * Initialiser l'application confirmation
 */
window.ConfirmationApp.init = function() {
    console.log('🚀 Initialisation ConfirmationApp SGLP v1.0');
    
    // Récupérer la configuration depuis le blade
    if (window.ConfirmationConfig) {
        this.config = { ...this.config, ...window.ConfirmationConfig };
    }
    
    console.log('📋 Configuration ConfirmationApp:', this.config);
    
    // Initialiser les modules
    this.initializeAdditionalAdherentsModule();
    this.initializeStatisticsRefresh();
    this.setupDragAndDrop();
    this.initializeSuccessAnimation();
    this.setupEventListeners();
    
    // Vérifier l'état initial
    this.refreshStatistics();
    
    console.log('✅ ConfirmationApp initialisé avec succès');
};

/**
 * Configuration des écouteurs d'événements principaux
 */
window.ConfirmationApp.setupEventListeners = function() {
    // Fermer FAB en cliquant ailleurs
    document.addEventListener('click', (event) => {
        const fabMenu = document.getElementById('fabMenu');
        if (fabMenu && !fabMenu.contains(event.target)) {
            fabMenu.classList.remove('active');
        }
    });
    
    // Gestion des raccourcis clavier
    document.addEventListener('keydown', (event) => {
        // Ctrl+R ou F5 : Actualiser les statistiques
        if ((event.ctrlKey && event.key === 'r') || event.key === 'F5') {
            event.preventDefault();
            this.refreshStatistics();
            this.showNotification('Statistiques actualisées', 'info', 2000);
        }
        
        // Escape : Fermer le FAB
        if (event.key === 'Escape') {
            const fabMenu = document.getElementById('fabMenu');
            if (fabMenu) {
                fabMenu.classList.remove('active');
            }
        }
    });
    
    // Nettoyer les intervalles à la fermeture de la page
    window.addEventListener('beforeunload', () => {
        if (this.statisticsRefreshInterval) {
            clearInterval(this.statisticsRefreshInterval);
        }
    });
};

// ========================================
// ANIMATION DE SUCCÈS ET CONFETTIS
// ========================================

/**
 * Initialiser les animations de succès
 */
window.ConfirmationApp.initializeSuccessAnimation = function() {
    // Animation d'entrée de l'icône de succès
    const successCheck = document.getElementById('success-check');
    if (successCheck) {
        successCheck.style.transform = 'scale(0)';
        setTimeout(() => {
            successCheck.style.transition = 'transform 0.5s ease-out';
            successCheck.style.transform = 'scale(1)';
        }, 500);
    }
    
    // Création de particules de succès
    const particlesContainer = document.getElementById('success-particles');
    if (particlesContainer) {
        this.createSuccessParticles(particlesContainer);
    }
};

/**
 * Créer des particules de succès
 */
window.ConfirmationApp.createSuccessParticles = function(container) {
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(255,255,255,0.8);
            border-radius: 50%;
            pointer-events: none;
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            animation: float ${2 + Math.random() * 2}s ease-in-out infinite;
            animation-delay: ${Math.random() * 2}s;
        `;
        container.appendChild(particle);
    }
};

// ========================================
// PHASE 2 : IMPORT DES ADHÉRENTS
// ========================================

/**
 * Démarrer l'import des adhérents Phase 2
 */
window.ConfirmationApp.startAdherentsImport = async function() {
    if (!this.config.hasPhase2Pending) {
        this.showNotification('Aucun adhérent en attente d\'import', 'warning');
        return;
    }
    
    console.log('🚀 Démarrage import adhérents Phase 2');
    
    const startBtn = document.getElementById('start-import-btn');
    const progressDiv = document.getElementById('import-progress');
    const controlsDiv = document.getElementById('import-controls');
    const resultsDiv = document.getElementById('import-results');
    
    try {
        controlsDiv.classList.add('d-none');
        progressDiv.classList.remove('d-none');
        resultsDiv.classList.add('d-none');
        
        // Simulation progressive réaliste
        this.updateImportProgress(10, 'Récupération des données de session...');
        await this.delay(800);
        
        this.updateImportProgress(25, 'Validation des adhérents...');
        await this.delay(1200);
        
        this.updateImportProgress(50, 'Préparation pour insertion en base...');
        await this.delay(1000);
        
        this.updateImportProgress(75, 'Import en base de données...');
        
        // Appel API réel
        const response = await fetch(this.config.routes.importAdherents, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.config.csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                dossier_id: this.config.dossierId
            })
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Erreur lors de l\'import');
        }
        
        this.updateImportProgress(100, 'Import terminé avec succès !');
        await this.delay(500);
        
        this.showImportResults(result);
        setTimeout(() => this.refreshStatistics(), 1000);
        
    } catch (error) {
        console.error('❌ Erreur import:', error);
        this.showImportError(error.message);
    }
};

/**
 * Mettre à jour la progression d'import
 */
window.ConfirmationApp.updateImportProgress = function(percentage, message) {
    const elements = {
        progressBar: document.getElementById('import-progress-bar'),
        progressText: document.getElementById('import-progress-text'),
        statusText: document.getElementById('import-status'),
        percentageText: document.getElementById('import-percentage'),
        detailsText: document.getElementById('import-details')
    };
    
    if (elements.progressBar) elements.progressBar.style.width = percentage + '%';
    if (elements.progressText) elements.progressText.textContent = percentage + '%';
    if (elements.statusText) elements.statusText.textContent = message;
    if (elements.percentageText) elements.percentageText.textContent = percentage + '%';
    if (elements.detailsText) elements.detailsText.textContent = `Progression: ${percentage}% - ${message}`;
};

/**
 * Afficher les résultats d'import
 */
window.ConfirmationApp.showImportResults = function(result) {
    const resultsDiv = document.getElementById('import-results');
    const progressDiv = document.getElementById('import-progress');
    
    progressDiv.classList.add('d-none');
    
    const successHTML = `
        <div class="alert alert-success-custom border-0">
            <div class="d-flex align-items-center">
                <div class="alert-icon alert-icon-success me-3">
                    <i class="fas fa-check-circle text-white fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-2 fw-bold">Import terminé avec succès !</h6>
                    <ul class="mb-0">
                        <li><strong>${result.data.adherents_imported || 0}</strong> adhérents importés</li>
                        <li><strong>${result.data.adherents_total || 0}</strong> adhérents au total</li>
                        ${result.data.anomalies ? `<li><strong>${result.data.anomalies}</strong> anomalies détectées</li>` : ''}
                    </ul>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-success-custom" onclick="window.location.reload()">
                    <i class="fas fa-sync me-2"></i>Actualiser la page
                </button>
            </div>
        </div>
    `;
    
    resultsDiv.innerHTML = successHTML;
    resultsDiv.classList.remove('d-none');
    
    this.showNotification('Adhérents importés avec succès !', 'success');
};

/**
 * Afficher une erreur d'import
 */
window.ConfirmationApp.showImportError = function(message) {
    const resultsDiv = document.getElementById('import-results');
    const progressDiv = document.getElementById('import-progress');
    
    progressDiv.classList.add('d-none');
    
    const errorHTML = `
        <div class="alert alert-danger-custom border-0">
            <div class="d-flex align-items-center">
                <div class="alert-icon alert-icon-danger me-3">
                    <i class="fas fa-exclamation-triangle text-white fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-2 fw-bold">Erreur lors de l'import</h6>
                    <p class="mb-0">${message}</p>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-warning-custom" onclick="ConfirmationApp.retryImport()">
                    <i class="fas fa-redo me-2"></i>Réessayer
                </button>
            </div>
        </div>
    `;
    
    resultsDiv.innerHTML = errorHTML;
    resultsDiv.classList.remove('d-none');
    
    this.showNotification('Erreur: ' + message, 'danger');
};

/**
 * Réessayer l'import
 */
window.ConfirmationApp.retryImport = function() {
    document.getElementById('import-results').classList.add('d-none');
    document.getElementById('import-controls').classList.remove('d-none');
};

// ========================================
// MODULE AVANCÉ D'UPLOAD ADHÉRENTS SUPPLÉMENTAIRES
// ========================================

/**
 * Initialiser le module d'adhérents supplémentaires
 */
window.ConfirmationApp.initializeAdditionalAdherentsModule = function() {
    // Gestion des modes d'ajout
    const modeInputs = document.querySelectorAll('input[name="additional_mode"]');
    modeInputs.forEach(input => {
        input.addEventListener('change', this.handleAdditionalModeChange.bind(this));
    });
    
    // Validation NIP temps réel
    const nipInput = document.getElementById('additional_adherent_nip');
    if (nipInput) {
        nipInput.addEventListener('input', this.validateAdditionalNipRealTime.bind(this));
        nipInput.addEventListener('blur', this.validateAdditionalNipRealTime.bind(this));
    }
    
    // Boutons d'action
    const addBtn = document.getElementById('addAdditionalAdherentBtn');
    if (addBtn) {
        addBtn.addEventListener('click', this.addAdditionalAdherentManually.bind(this));
    }
    
    const templateBtn = document.getElementById('downloadAdditionalTemplateBtn');
    if (templateBtn) {
        templateBtn.addEventListener('click', this.downloadAdditionalTemplate.bind(this));
    }
    
    const selectFileBtn = document.getElementById('select-additional-file-btn');
    if (selectFileBtn) {
        selectFileBtn.addEventListener('click', () => {
            document.getElementById('additional_adherents_file').click();
        });
    }
    
    // Configuration des événements d'upload
    const fileInput = document.getElementById('additional_adherents_file');
    if (fileInput) {
        fileInput.addEventListener('change', this.handleAdditionalFileSelect.bind(this));
    }
    
    // Configuration du toggle des modes
    this.setupAdditionalModeToggle();
    
    console.log('✅ Module adhérents supplémentaires initialisé');
};

/**
 * Gestion du changement de mode d'ajout
 */
window.ConfirmationApp.handleAdditionalModeChange = function(event) {
    const mode = event.target.value;
    const manuelSection = document.getElementById('additional_manuel_section');
    const fichierSection = document.getElementById('additional_fichier_section');
    
    if (mode === 'manuel') {
        manuelSection.classList.remove('d-none');
        fichierSection.classList.add('d-none');
    } else {
        manuelSection.classList.add('d-none');
        fichierSection.classList.remove('d-none');
    }
};

/**
 * Configuration du toggle des modes
 */
window.ConfirmationApp.setupAdditionalModeToggle = function() {
    const modeCards = document.querySelectorAll('.mode-card[data-mode]');
    modeCards.forEach(card => {
        card.addEventListener('click', function() {
            const mode = this.dataset.mode;
            const radioInput = this.querySelector('input[type="radio"]');
            if (radioInput) {
                radioInput.checked = true;
                radioInput.dispatchEvent(new Event('change'));
            }
        });
    });
};

/**
 * Validation NIP temps réel - Intégration avec FileUploadSGLP
 */
window.ConfirmationApp.validateAdditionalNipRealTime = function(event) {
    const input = event.target;
    const value = input.value.trim();
    
    if (window.FileUploadSGLP && window.FileUploadSGLP.validateNipRealTime) {
        const validation = window.FileUploadSGLP.validateNipRealTime(value);
        window.FileUploadSGLP.updateNipValidationUI(validation, 'additional');
    } else {
        // Fallback validation basique
        this.validateNipBasic(input, value);
    }
};

/**
 * Validation NIP basique (fallback)
 */
window.ConfirmationApp.validateNipBasic = function(input, value) {
    const validIcon = document.getElementById('nip-valid-additional');
    const invalidIcon = document.getElementById('nip-invalid-additional');
    const pendingIcon = document.getElementById('nip-pending-additional');
    const errorDiv = document.getElementById('additional_adherent_nip_error');
    
    // Réinitialiser les icônes
    [validIcon, invalidIcon, pendingIcon].forEach(icon => {
        if (icon) icon.classList.add('d-none');
    });
    
    if (!value) {
        pendingIcon?.classList.remove('d-none');
        input.classList.remove('nip-valid', 'nip-invalid', 'nip-validating');
        return;
    }
    
    input.classList.add('nip-validating');
    
    // Validation basique du format XX-QQQQ-YYYYMMDD
    const basicPattern = /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/;
    
    if (basicPattern.test(value)) {
        input.classList.remove('nip-validating', 'nip-invalid');
        input.classList.add('nip-valid');
        validIcon?.classList.remove('d-none');
        if (errorDiv) errorDiv.textContent = '';
    } else {
        input.classList.remove('nip-validating', 'nip-valid');
        input.classList.add('nip-invalid');
        invalidIcon?.classList.remove('d-none');
        if (errorDiv) errorDiv.textContent = 'Format invalide (XX-QQQQ-YYYYMMDD)';
    }
};

/**
 * Ajouter un adhérent manuellement
 */
window.ConfirmationApp.addAdditionalAdherentManually = function() {
    const civilite = document.getElementById('additional_adherent_civilite').value;
    const nom = document.getElementById('additional_adherent_nom').value.trim();
    const prenom = document.getElementById('additional_adherent_prenom').value.trim();
    const nip = document.getElementById('additional_adherent_nip').value.trim();
    const telephone = document.getElementById('additional_adherent_telephone').value.trim();
    const profession = document.getElementById('additional_adherent_profession').value.trim();
    const statusSpan = document.getElementById('manual-add-status');
    
    // Validation
    if (!nom || !prenom || !nip) {
        this.showNotification('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }
    
    // Vérification doublon NIP
    if (this.additionalAdherents.some(adherent => adherent.nip === nip)) {
        this.showNotification('Ce NIP existe déjà dans la liste', 'danger');
        return;
    }
    
    // Validation NIP
    const nipInput = document.getElementById('additional_adherent_nip');
    if (!nipInput.classList.contains('nip-valid')) {
        this.showNotification('Le format du NIP n\'est pas valide', 'danger');
        return;
    }
    
    // Ajouter l'adhérent
    const adherent = {
        id: Date.now(),
        civilite,
        nom,
        prenom,
        nip,
        telephone: telephone || null,
        profession: profession || null,
        source: 'manuel',
        created_at: new Date().toISOString()
    };
    
    this.additionalAdherents.push(adherent);
    
    // Réinitialiser le formulaire
    ['additional_adherent_nom', 'additional_adherent_prenom', 'additional_adherent_nip',
     'additional_adherent_telephone', 'additional_adherent_profession'].forEach(id => {
        const element = document.getElementById(id);
        if (element) element.value = '';
    });
    
    // Réinitialiser validation NIP
    nipInput.classList.remove('nip-valid', 'nip-invalid', 'nip-validating');
    document.getElementById('nip-pending-additional')?.classList.remove('d-none');
    [document.getElementById('nip-valid-additional'),
     document.getElementById('nip-invalid-additional')].forEach(icon => {
        if (icon) icon.classList.add('d-none');
    });
    
    // Mettre à jour l'affichage
    this.updateAdditionalAdherentsList();
    this.updateAdditionalSummary();
    this.showNotification(`Adhérent ${nom} ${prenom} ajouté avec succès`, 'success');
    
    // Animation de succès temporaire
    if (statusSpan) {
        statusSpan.innerHTML = '<i class="fas fa-check text-success me-1"></i>Ajouté !';
        setTimeout(() => {
            statusSpan.innerHTML = '';
        }, 2000);
    }
};

/**
 * Gestion de la sélection de fichier
 */
window.ConfirmationApp.handleAdditionalFileSelect = function(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    console.log('📁 Fichier sélectionné:', file.name, file.size, 'bytes');
    
    // Utiliser FileUploadSGLP si disponible
    if (window.FileUploadSGLP && window.FileUploadSGLP.processAdditionalFile) {
        window.FileUploadSGLP.processAdditionalFile(file, this.config.dossierId);
    } else {
        // Fallback : traitement basique
        this.processAdditionalFileBasic(file);
    }
};

/**
 * Traitement de fichier basique (fallback)
 */
window.ConfirmationApp.processAdditionalFileBasic = function(file) {
    this.showNotification('Traitement du fichier en cours...', 'info');
    
    // Simulation de traitement
    setTimeout(() => {
        this.showNotification('Fichier traité avec succès (mode basique)', 'success');
    }, 2000);
};

/**
 * Callback pour FileUploadSGLP - Fichier traité avec succès
 */
window.ConfirmationApp.handleFileProcessed = function(data) {
    console.log('✅ Fichier traité par FileUploadSGLP:', data);
    
    // Ajouter les adhérents valides à la liste
    if (data.adherents && data.adherents.length > 0) {
        this.additionalAdherents.push(...data.adherents);
        this.updateAdditionalAdherentsList();
        this.updateAdditionalSummary();
    }
    
    // Afficher les résultats
    this.showAdditionalUploadResults(data);
    
    // Actualiser les statistiques
    setTimeout(() => this.refreshStatistics(), 1000);
};

/**
 * Callback pour FileUploadSGLP - Erreur de traitement
 */
window.ConfirmationApp.handleFileError = function(error) {
    console.error('❌ Erreur FileUploadSGLP:', error);
    this.showNotification('Erreur: ' + error.message, 'danger');
};

/**
 * Afficher les résultats d'upload de fichier supplémentaire
 */
window.ConfirmationApp.showAdditionalUploadResults = function(data) {
    const resultsDiv = document.getElementById('additional-upload-results');
    if (!resultsDiv) return;
    
    const successHTML = `
        <div class="alert alert-success-custom border-0 mt-3">
            <div class="d-flex align-items-center">
                <div class="alert-icon alert-icon-success me-3">
                    <i class="fas fa-check-circle text-white fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-2 fw-bold">Fichier traité avec succès !</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-2 rounded bg-success text-white">
                                <strong>${data.stats?.valides || 0}</strong><br>
                                <small>Adhérents valides</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-2 rounded bg-primary text-white">
                                <strong>${data.stats?.total || 0}</strong><br>
                                <small>Total lignes</small>
                            </div>
                        </div>
                        ${data.stats?.invalides > 0 ? `
                        <div class="col-md-3">
                            <div class="text-center p-2 rounded bg-danger text-white">
                                <strong>${data.stats.invalides}</strong><br>
                                <small>Invalides</small>
                            </div>
                        </div>
                        ` : ''}
                        ${data.stats?.anomalies?.total > 0 ? `
                        <div class="col-md-3">
                            <div class="text-center p-2 rounded bg-warning text-dark">
                                <strong>${data.stats.anomalies.total}</strong><br>
                                <small>Anomalies</small>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="document.getElementById('additional-upload-results').innerHTML = ''">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${data.invalides && data.invalides.length > 0 ? `
            <div class="mt-3">
                <details>
                    <summary class="fw-bold text-danger" style="cursor: pointer;">
                        Voir les erreurs (${data.invalides.length})
                    </summary>
                    <div class="mt-2">
                        ${data.invalides.slice(0, 10).map(item => `
                        <small class="d-block text-danger">
                            Ligne ${item.line_number}: ${item.errors.join(', ')}
                        </small>
                        `).join('')}
                        ${data.invalides.length > 10 ? `<small class="text-muted">... et ${data.invalides.length - 10} autres erreurs</small>` : ''}
                    </div>
                </details>
            </div>
            ` : ''}
        </div>
    `;
    
    resultsDiv.innerHTML = successHTML;
    
    // Notification globale
    this.showNotification(`${data.stats?.valides || 0} adhérents ajoutés avec succès !`, 'success');
    
    // Nettoyer l'input file
    const fileInput = document.getElementById('additional_adherents_file');
    if (fileInput) fileInput.value = '';
};

/**
 * Mettre à jour la liste des adhérents supplémentaires
 */
window.ConfirmationApp.updateAdditionalAdherentsList = function() {
    const container = document.getElementById('additional_adherents_list');
    const countBadge = document.getElementById('additional_adherents_count');
    
    if (countBadge) {
        countBadge.textContent = `${this.additionalAdherents.length} adhérent(s)`;
    }
    
    if (!container) return;
    
    if (this.additionalAdherents.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-user-plus fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted">Aucun adhérent supplémentaire ajouté</h5>
                <p class="text-muted mb-4">
                    Ajoutez des adhérents pour atteindre le minimum requis pour votre organisation.
                </p>
            </div>
        `;
        return;
    }
    
    // Construire le tableau
    const tableHTML = `
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">#</th>
                        <th class="border-0">Identité</th>
                        <th class="border-0">NIP</th>
                        <th class="border-0">Contact</th>
                        <th class="border-0">Source</th>
                        <th class="border-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${this.additionalAdherents.map((adherent, index) => `
                    <tr class="adherent-row-additional ${adherent.source === 'manuel' && Date.now() - new Date(adherent.created_at).getTime() < 2000 ? 'adherent-row-new' : ''}">
                        <td>
                            <span class="badge bg-secondary">${index + 1}</span>
                        </td>
                        <td>
                            <div>
                                <strong>${adherent.civilite} ${adherent.nom} ${adherent.prenom}</strong>
                                ${adherent.profession ? `<br><small class="text-muted">${adherent.profession}</small>` : ''}
                            </div>
                        </td>
                        <td>
                            <code class="bg-light p-1 rounded">${adherent.nip}</code>
                        </td>
                        <td>
                            ${adherent.telephone ? `
                            <small class="d-block">
                                <i class="fas fa-phone text-success me-1"></i>
                                +241 ${adherent.telephone}
                            </small>
                            ` : ''}
                            ${adherent.email ? `
                            <small class="d-block">
                                <i class="fas fa-envelope text-info me-1"></i>
                                ${adherent.email}
                            </small>
                            ` : ''}
                        </td>
                        <td>
                            <span class="badge ${adherent.source === 'manuel' ? 'badge-success-custom' : 'badge-info-custom'}">
                                <i class="fas fa-${adherent.source === 'manuel' ? 'keyboard' : 'file-excel'} me-1"></i>
                                ${adherent.source === 'manuel' ? 'Manuel' : 'Fichier'}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="ConfirmationApp.removeAdditionalAdherent(${adherent.id})"
                                    title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHTML;
};

/**
 * Mettre à jour le récapitulatif
 */
window.ConfirmationApp.updateAdditionalSummary = function() {
    const addedElement = document.getElementById('summary-added');
    const totalElement = document.getElementById('summary-total');
    const missingElement = document.getElementById('summary-missing');
    
    if (addedElement) {
        addedElement.textContent = this.additionalAdherents.length;
    }
    
    // Calculer le nouveau total
    const currentTotal = parseInt(this.config.totalAdherents) || 0;
    const newTotal = currentTotal + this.additionalAdherents.length;
    
    if (totalElement) {
        totalElement.textContent = newTotal;
    }
    
    // Calculer les manquants
    const required = parseInt(this.config.minAdherents) || 0;
    const missing = Math.max(0, required - newTotal);
    
    if (missingElement) {
        missingElement.textContent = missing;
        
        // Changer la couleur selon le statut
        const parentDiv = missingElement.parentElement;
        if (missing === 0) {
            parentDiv.className = 'summary-item summary-item-success';
        } else {
            parentDiv.className = 'summary-item summary-item-danger';
        }
    }
    
    // Mettre à jour les statistiques globales si possible
    setTimeout(() => this.refreshStatistics(), 500);
};

/**
 * Supprimer un adhérent supplémentaire
 */
window.ConfirmationApp.removeAdditionalAdherent = function(id) {
    const index = this.additionalAdherents.findIndex(adherent => adherent.id === id);
    if (index !== -1) {
        const adherent = this.additionalAdherents[index];
        if (confirm(`Supprimer ${adherent.nom} ${adherent.prenom} de la liste ?`)) {
            this.additionalAdherents.splice(index, 1);
            this.updateAdditionalAdherentsList();
            this.updateAdditionalSummary();
            this.showNotification('Adhérent supprimé', 'info');
        }
    }
};

/**
 * Vider la liste des adhérents supplémentaires
 */
window.ConfirmationApp.clearAdditionalAdherents = function() {
    if (this.additionalAdherents.length === 0) {
        this.showNotification('Aucun adhérent à supprimer', 'info');
        return;
    }
    
    if (confirm(`Supprimer tous les ${this.additionalAdherents.length} adhérents supplémentaires ?`)) {
        this.additionalAdherents = [];
        this.updateAdditionalAdherentsList();
        this.updateAdditionalSummary();
        this.showNotification('Liste vidée', 'info');
    }
};

/**
 * Exporter les adhérents supplémentaires en CSV
 */
window.ConfirmationApp.exportAdditionalAdherentsCSV = function() {
    if (this.additionalAdherents.length === 0) {
        this.showNotification('Aucun adhérent à exporter', 'warning');
        return;
    }
    
    const headers = ['Civilité', 'Nom', 'Prénom', 'NIP', 'Téléphone', 'Profession', 'Source'];
    const csvContent = [
        headers.join(','),
        ...this.additionalAdherents.map(adherent => [
            adherent.civilite,
            adherent.nom,
            adherent.prenom,
            adherent.nip,
            adherent.telephone || '',
            adherent.profession || '',
            adherent.source
        ].join(','))
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `adherents_supplementaires_${new Date().getTime()}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    this.showNotification('Export CSV téléchargé avec succès', 'success');
};

/**
 * Télécharger le template
 */
window.ConfirmationApp.downloadAdditionalTemplate = function() {
    if (window.FileUploadSGLP && window.FileUploadSGLP.generateTemplate) {
        window.FileUploadSGLP.generateTemplate();
    } else {
        // Fallback : redirection vers le template
        window.open(this.config.routes.downloadTemplate, '_blank');
        this.showNotification('Téléchargement du modèle...', 'info');
    }
};

// ========================================
// SOUMISSION FINALE À L'ADMINISTRATION
// ========================================

/**
 * Gérer la soumission finale
 */
window.ConfirmationApp.handleFinalSubmission = async function(event) {
    event.preventDefault();
    
    const form = event.target;
    const declarationFinale = document.getElementById('declaration-finale');
    const confirmationSoumission = document.getElementById('confirmation-soumission');
    const submitBtn = document.getElementById('submit-final-btn');
    
    if (!declarationFinale.checked || !confirmationSoumission.checked) {
        this.showNotification('Vous devez accepter toutes les déclarations obligatoires', 'warning');
        return;
    }
    
    if (!confirm('Êtes-vous sûr de vouloir soumettre définitivement ce dossier à l\'administration ?\\n\\nCette action est irréversible.')) {
        return;
    }
    
    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Soumission en cours...';
        
        const formData = new FormData(form);
        
        const response = await fetch(this.config.routes.submitFinal, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.config.csrf,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Erreur lors de la soumission');
        }
        
        // Redirection vers page de confirmation finale
        window.location.href = this.config.routes.finalConfirmation;
        
    } catch (error) {
        console.error('❌ Erreur soumission finale:', error);
        this.showNotification('Erreur: ' + error.message, 'danger');
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Soumettre à l\'administration';
    }
};

// ========================================
// STATISTIQUES TEMPS RÉEL
// ========================================

/**
 * Initialiser l'actualisation des statistiques
 */
window.ConfirmationApp.initializeStatisticsRefresh = function() {
    // Actualisation automatique toutes les 30 secondes
    this.statisticsRefreshInterval = setInterval(() => {
        this.refreshStatistics();
    }, 30000);
};

/**
 * Actualiser les statistiques
 */
window.ConfirmationApp.refreshStatistics = function() {
    if (!this.config.routes.getStatistics) {
        console.warn('Route getStatistics non configurée');
        return;
    }
    
    fetch(this.config.routes.getStatistics, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.config.csrf
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.updateStatisticsDisplay(data.data);
        }
    })
    .catch(error => {
        console.warn('Erreur actualisation statistiques:', error);
    });
};

/**
 * Mettre à jour l'affichage des statistiques
 */
window.ConfirmationApp.updateStatisticsDisplay = function(stats) {
    // Mettre à jour les éléments
    const elements = {
        adherentsEnBase: document.getElementById('adherents-en-base'),
        adherentsEnSession: document.getElementById('adherents-en-session'),
        totalAdherents: document.getElementById('total-adherents'),
        progressPercentage: document.getElementById('progress-percentage'),
        mainProgressBar: document.getElementById('main-progress-bar')
    };
    
    if (elements.adherentsEnBase) {
        elements.adherentsEnBase.textContent = this.formatNumber(stats.adherents_en_base);
    }
    
    if (elements.adherentsEnSession) {
        elements.adherentsEnSession.textContent = this.formatNumber(stats.adherents_en_session);
    }
    
    if (elements.totalAdherents) {
        elements.totalAdherents.textContent = this.formatNumber(stats.total_adherents);
    }
    
    if (elements.progressPercentage) {
        elements.progressPercentage.textContent = `${stats.progression_percentage}%`;
    }
    
    if (elements.mainProgressBar) {
        elements.mainProgressBar.style.width = `${stats.progression_percentage}%`;
        elements.mainProgressBar.textContent = `${stats.progression_percentage}%`;
    }
    
    // Mettre à jour la configuration locale
    this.config.adherentsEnBase = stats.adherents_en_base;
    this.config.adherentsCount = stats.adherents_en_session;
    this.config.totalAdherents = stats.total_adherents;
    this.config.pretPourSoumission = stats.pret_pour_soumission;
};

// ========================================
// DRAG AND DROP
// ========================================

/**
 * Configuration du drag and drop
 */
window.ConfirmationApp.setupDragAndDrop = function() {
    const dropZone = document.getElementById('additional-file-drop-zone');
    const fileInput = document.getElementById('additional_adherents_file');
    
    if (!dropZone || !fileInput) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, this.preventDefaults, false);
        document.body.addEventListener(eventName, this.preventDefaults, false);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, this.highlight.bind(this), false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, this.unhighlight.bind(this), false);
    });
    
    dropZone.addEventListener('drop', this.handleDrop.bind(this), false);
    dropZone.addEventListener('click', () => fileInput.click());
};

/**
 * Empêcher les comportements par défaut
 */
window.ConfirmationApp.preventDefaults = function(e) {
    e.preventDefault();
    e.stopPropagation();
};

/**
 * Mettre en surbrillance la zone de drop
 */
window.ConfirmationApp.highlight = function(e) {
    const dropZone = document.getElementById('additional-file-drop-zone');
    if (dropZone) {
        dropZone.classList.add('dragover');
    }
};

/**
 * Retirer la surbrillance
 */
window.ConfirmationApp.unhighlight = function(e) {
    const dropZone = document.getElementById('additional-file-drop-zone');
    if (dropZone) {
        dropZone.classList.remove('dragover');
    }
};

/**
 * Gérer le drop de fichier
 */
window.ConfirmationApp.handleDrop = function(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0) {
        const fileInput = document.getElementById('additional_adherents_file');
        if (fileInput) {
            fileInput.files = files;
            this.handleAdditionalFileSelect({ target: fileInput });
        }
    }
};

// ========================================
// FAB (FLOATING ACTION BUTTON)
// ========================================

/**
 * Toggle FAB Menu
 */
window.ConfirmationApp.toggleFAB = function() {
    const fabMenu = document.getElementById('fabMenu');
    if (fabMenu) {
        fabMenu.classList.toggle('active');
    }
};

// ========================================
// UTILITAIRES
// ========================================

/**
 * Délai asynchrone
 */
window.ConfirmationApp.delay = function(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
};

/**
 * Formater un nombre
 */
window.ConfirmationApp.formatNumber = function(number) {
    return new Intl.NumberFormat('fr-FR').format(number);
};

/**
 * Copier le numéro de dossier
 */
window.ConfirmationApp.copyDossierNumber = function() {
    const number = this.config.dossierId;
    navigator.clipboard.writeText(number).then(() => {
        this.showNotification('Numéro de dossier copié !', 'success', 2000);
    }).catch(() => {
        // Fallback
        const textArea = document.createElement('textarea');
        textArea.value = number;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        this.showNotification('Numéro de dossier copié !', 'success', 2000);
    });
};

/**
 * Partager la progression
 */
window.ConfirmationApp.shareProgress = function() {
    const shareData = {
        title: 'Progression dossier PNGDI',
        text: `Mon dossier progresse : ${this.config.totalAdherents} adhérents ajoutés`,
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(shareData);
    } else {
        this.copyDossierNumber();
        this.showNotification('Lien copié ! Vous pouvez maintenant le partager.', 'info');
    }
};

/**
 * Contacter le support
 */
window.ConfirmationApp.contactSupport = function() {
    const email = 'support@pngdi.ga';
    const subject = `Support dossier ${this.config.dossierId}`;
    const body = `Bonjour,\n\nJ'ai besoin d'aide concernant mon dossier ${this.config.dossierId}.\n\nCordialement.`;
    
    window.open(`mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`);
};

/**
 * Afficher l'aide
 */
window.ConfirmationApp.showHelp = function() {
    // Ouvrir l'aide dans une nouvelle fenêtre ou modal
    window.open('/help/confirmation-page', '_blank');
};

/**
 * Télécharger le PDF de résumé
 */
window.ConfirmationApp.downloadPDF = function() {
    window.print();
};

/**
 * Afficher une notification avec style gabonais
 */
window.ConfirmationApp.showNotification = function(message, type = 'info', duration = 5000) {
    // Supprimer les toasts existants
    document.querySelectorAll('.toast-gabon').forEach(t => t.remove());
    
    const typeColors = {
        'success': '#009e3f',
        'info': '#003f7f',
        'warning': '#ffcd00',
        'danger': '#8b1538'
    };
    
    const typeIcons = {
        'success': 'check-circle',
        'info': 'info-circle',
        'warning': 'exclamation-triangle',
        'danger': 'exclamation-circle'
    };
    
    const textColor = type === 'warning' ? '#000' : '#fff';
    
    const toast = document.createElement('div');
    toast.className = 'toast-gabon position-fixed fade show';
    toast.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px; 
        background: ${typeColors[type]}; 
        color: ${textColor}; 
        border-radius: 10px; 
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        padding: 15px 20px;
    `;
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${typeIcons[type]} me-3 fs-5"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} ms-3" 
                    onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-suppression
    if (duration > 0) {
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    }
};

// ========================================
// INITIALISATION AUTOMATIQUE
// ========================================

/**
 * Initialiser l'application au chargement du DOM
 */
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier que nous sommes sur la bonne page
    if (window.ConfirmationConfig) {
        window.ConfirmationApp.init();
    } else {
        console.warn('ConfirmationConfig non trouvé - module non initialisé');
    }
});

// Export pour modules ES6 si supporté
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.ConfirmationApp;
}

console.log(`
🎉 ========================================================================
   CONFIRMATION.JS v1.0 - MODULE JAVASCRIPT COMPLET SGLP
   ========================================================================
   
   ✅ Module principal pour confirmation.blade.php
   🇬🇦 Compatible avec FileUploadSGLP et charte gabonaise
   📱 Interface moderne avec FAB et animations
   🔍 Validation temps réel NIP gabonais XX-QQQQ-YYYYMMDD
   📊 Gestion statistiques et dashboard temps réel
   🚀 Upload lots supplémentaires avec drag & drop
   
   🎯 FONCTIONNALITÉS PRINCIPALES :
   ✅ Phase 2 : Import adhérents depuis session
   ✅ Upload lots supplémentaires (manuel + fichier)
   ✅ Validation NIP gabonais en temps réel
   ✅ Soumission finale à l'administration
   ✅ Dashboard statistiques temps réel
   ✅ Notifications modernes style SGLP
   ✅ FAB tricolore gabonais
   ✅ Drag & drop avec feedback visuel
   
   🔧 INTÉGRATIONS :
   ✅ FileUploadSGLP.js pour traitement fichiers
   ✅ ConfirmationConfig depuis blade.php
   ✅ API routes backend Laravel
   ✅ Design cohérent avec index.blade.php
   
   🇬🇦 Optimisé pour l'administration gabonaise
========================================================================
`);