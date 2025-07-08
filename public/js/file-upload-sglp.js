/**
 * ========================================================================
 * FILE-UPLOAD-SGLP.JS - MODULE UPLOAD ADAPTÉ POUR SGLP
 * Version: 1.0 - Adaptation du module file-upload-common.js pour SGLP
 * ========================================================================
 * 
 * Module réutilisable pour l'upload et traitement des fichiers adhérents
 * Compatible avec confirmation.blade.php et système SGLP gabonais
 * 
 * Adaptation majeure :
 * - Format NIP gabonais : XX-QQQQ-YYYYMMDD
 * - Couleurs officielles du Gabon
 * - Intégration système de notifications SGLP
 * - Routes spécifiques du projet
 */

window.FileUploadSGLP = window.FileUploadSGLP || {};

// ========================================
// CONFIGURATION ADAPTÉE SGLP
// ========================================

window.FileUploadSGLP.config = {
    maxFileSize: 10240, // 10MB en KB
    supportedFormats: ['xlsx', 'csv'],
    progressSteps: {
        reading: 25,
        validation: 50,
        preparation: 75,
        saving: 90,
        complete: 100
    },
    validation: {
        requiredFields: ['civilite', 'nom', 'prenom', 'nip'],
        // ✅ ADAPTATION SGLP : Format NIP gabonais XX-QQQQ-YYYYMMDD
        nipFormat: /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/,
        phoneFormat: /^[0-9]{8,9}$/,
        gabonPhonePrefix: '+241'
    },
    // ✅ COULEURS GABONAISES OFFICIELLES
    colors: {
        green: '#009e3f',
        yellow: '#ffcd00', 
        blue: '#003f7f',
        red: '#8b1538'
    }
};

// ========================================
// VALIDATION FICHIER ADAPTÉE SGLP
// ========================================

/**
 * Valider le fichier sélectionné (taille, format) - Version SGLP
 */
window.FileUploadSGLP.validateFile = function(file) {
    console.log('🔍 Validation fichier SGLP:', file.name);
    
    // Vérifier la taille
    const maxSizeBytes = this.config.maxFileSize * 1024;
    if (file.size > maxSizeBytes) {
        this.showError('Le fichier est trop volumineux. Taille maximale: 10MB');
        return false;
    }
    
    // Vérifier le format
    const extension = file.name.split('.').pop().toLowerCase();
    if (!this.config.supportedFormats.includes(extension)) {
        this.showError('Format de fichier non supporté. Utilisez: ' + this.config.supportedFormats.join(', '));
        return false;
    }
    
    console.log('✅ Fichier valide pour SGLP');
    return true;
};

// ========================================
// INTERFACE PROGRESS BAR SGLP AVEC COULEURS GABONAISES
// ========================================

/**
 * Créer et afficher la modal de progression - Style SGLP
 */
window.FileUploadSGLP.showProgressModal = function(title = 'Upload en cours...', showInModal = true) {
    this.hideProgressModal(); // Nettoyer d'abord
    
    if (showInModal) {
        const modalHTML = `
            <div class="modal fade" id="fileUploadProgressModalSGLP" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); color: white;">
                            <h5 class="modal-title">
                                <i class="fas fa-file-upload me-2"></i>
                                ${title}
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <div class="spinner-border" style="color: #009e3f;" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                            
                            <div class="progress mb-3" style="height: 25px; border-radius: 15px;">
                                <div id="fileUploadProgressBarSGLP" 
                                     class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 0%; background: linear-gradient(90deg, #009e3f, #00b347);" 
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span id="fileUploadProgressTextSGLP" class="text-white fw-bold">0%</span>
                                </div>
                            </div>
                            
                            <div id="fileUploadProgressMessageSGLP" class="text-center text-muted">
                                Initialisation...
                            </div>
                            
                            <div id="fileUploadProgressDetailsSGLP" class="mt-3 small text-muted d-none">
                                <!-- Détails supplémentaires -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('fileUploadProgressModalSGLP'));
        modal.show();
    } else {
        // Mode inline pour confirmation.blade.php
        this.createInlineProgress();
    }
};

/**
 * Créer la progress bar inline - Style SGLP
 */
window.FileUploadSGLP.createInlineProgress = function() {
    const existingProgress = document.getElementById('inline-upload-progress-sglp');
    if (existingProgress) {
        existingProgress.remove();
    }
    
    const progressHTML = `
        <div id="inline-upload-progress-sglp" class="mt-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); color: white;">
                    <h6 class="mb-0">
                        <i class="fas fa-cog fa-spin me-2"></i>
                        Traitement en cours...
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <strong id="inline-upload-label-sglp">Upload en cours...</strong>
                        <span id="inline-upload-percentage-sglp" class="fw-bold" style="color: #009e3f;">0%</span>
                    </div>
                    <div class="progress" style="height: 25px; border-radius: 15px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             id="inline-upload-progress-bar-sglp" 
                             style="width: 0%; background: linear-gradient(90deg, #009e3f, #00b347);"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <span id="inline-upload-progress-text-sglp" class="text-white fw-bold">0%</span>
                        </div>
                    </div>
                    <div id="inline-upload-message-sglp" class="small text-muted mt-2">
                        Initialisation...
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Insérer après le conteneur d'upload
    const uploadContainer = document.getElementById('additional-upload-results') || 
                           document.querySelector('.upload-zone') || 
                           document.querySelector('.card-body');
    if (uploadContainer) {
        uploadContainer.insertAdjacentHTML('afterend', progressHTML);
    }
};

/**
 * Mettre à jour la progression - Style SGLP
 */
window.FileUploadSGLP.updateProgress = function(percentage, message, details = '') {
    percentage = Math.min(100, Math.max(0, percentage));
    
    // Mise à jour modal
    const modalProgressBar = document.getElementById('fileUploadProgressBarSGLP');
    const modalProgressText = document.getElementById('fileUploadProgressTextSGLP');
    const modalProgressMessage = document.getElementById('fileUploadProgressMessageSGLP');
    
    if (modalProgressBar) {
        modalProgressBar.style.width = percentage + '%';
        modalProgressBar.setAttribute('aria-valuenow', percentage);
    }
    if (modalProgressText) modalProgressText.textContent = percentage + '%';
    if (modalProgressMessage) modalProgressMessage.textContent = message;
    
    // Mise à jour inline
    const inlineProgressBar = document.getElementById('inline-upload-progress-bar-sglp');
    const inlineProgressText = document.getElementById('inline-upload-progress-text-sglp');
    const inlinePercentage = document.getElementById('inline-upload-percentage-sglp');
    const inlineLabel = document.getElementById('inline-upload-label-sglp');
    const inlineMessage = document.getElementById('inline-upload-message-sglp');
    
    if (inlineProgressBar) {
        inlineProgressBar.style.width = percentage + '%';
        inlineProgressBar.setAttribute('aria-valuenow', percentage);
    }
    if (inlineProgressText) inlineProgressText.textContent = percentage + '%';
    if (inlinePercentage) inlinePercentage.textContent = percentage + '%';
    if (inlineLabel) inlineLabel.textContent = message;
    if (inlineMessage) inlineMessage.textContent = `Progression: ${percentage}% - ${message}`;
    
    // Détails supplémentaires
    if (details) {
        const modalDetails = document.getElementById('fileUploadProgressDetailsSGLP');
        if (modalDetails) {
            modalDetails.innerHTML = details;
            modalDetails.classList.remove('d-none');
        }
    }
    
    console.log(`📊 Progress SGLP: ${percentage}% - ${message}`);
};

/**
 * Masquer la modal de progression - SGLP
 */
window.FileUploadSGLP.hideProgressModal = function() {
    const existingModal = document.getElementById('fileUploadProgressModalSGLP');
    if (existingModal) {
        const modalInstance = bootstrap.Modal.getInstance(existingModal);
        if (modalInstance) {
            modalInstance.hide();
        }
        setTimeout(() => existingModal.remove(), 300);
    }
    
    const inlineProgress = document.getElementById('inline-upload-progress-sglp');
    if (inlineProgress) {
        inlineProgress.remove();
    }
};

// ========================================
// LECTURE ET TRAITEMENT FICHIERS - SGLP
// ========================================

/**
 * Lire le fichier avec progression - Version SGLP
 */
window.FileUploadSGLP.readFile = function(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            try {
                this.updateProgress(this.config.progressSteps.reading, '📖 Lecture du fichier SGLP...');
                
                const data = e.target.result;
                let parsedData = [];
                
                if (file.name.toLowerCase().endsWith('.csv')) {
                    parsedData = this.parseCSV(data);
                } else {
                    parsedData = this.parseExcel(data);
                }
                
                console.log(`✅ Fichier SGLP lu: ${parsedData.length} lignes`);
                resolve(parsedData);
                
            } catch (error) {
                console.error('❌ Erreur lecture fichier SGLP:', error);
                reject(new Error('Impossible de lire le fichier: ' + error.message));
            }
        };
        
        reader.onerror = () => {
            reject(new Error('Erreur lors de la lecture du fichier'));
        };
        
        reader.readAsBinaryString(file);
    });
};

/**
 * Parser CSV simple - Adapté SGLP
 */
window.FileUploadSGLP.parseCSV = function(data) {
    const lines = data.split('\n').filter(line => line.trim());
    if (lines.length < 2) return [];
    
    const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
    const rows = [];
    
    // Mapping des colonnes françaises vers format SGLP
    const columnMapping = {
        'civilité': 'civilite',
        'civilite': 'civilite',
        'nom': 'nom',
        'prénom': 'prenom',
        'prenom': 'prenom',
        'nip': 'nip',
        'téléphone': 'telephone',
        'telephone': 'telephone',
        'phone': 'telephone',
        'profession': 'profession',
        'email': 'email',
        'adresse': 'adresse'
    };
    
    for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(',');
        if (values.length >= headers.length) {
            const row = {};
            headers.forEach((header, index) => {
                const mappedHeader = columnMapping[header] || header;
                row[mappedHeader] = values[index] ? values[index].trim() : '';
            });
            rows.push(row);
        }
    }
    
    return rows;
};

/**
 * Parser Excel avec XLSX - Adapté SGLP
 */
window.FileUploadSGLP.parseExcel = function(data) {
    if (typeof XLSX === 'undefined') {
        throw new Error('Librairie XLSX non disponible pour SGLP');
    }
    
    const workbook = XLSX.read(data, { type: 'binary' });
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];
    
    const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
    
    if (jsonData.length < 2) return [];
    
    const headers = jsonData[0].map(h => String(h || '').toLowerCase().trim());
    const rows = [];
    
    // Mapping pour Excel - Format SGLP
    const columnMapping = {
        'civilité': 'civilite',
        'civilite': 'civilite',
        'nom': 'nom',
        'prénom': 'prenom',
        'prenom': 'prenom', 
        'nip': 'nip',
        'téléphone': 'telephone',
        'telephone': 'telephone',
        'profession': 'profession',
        'email': 'email',
        'adresse': 'adresse'
    };
    
    for (let i = 1; i < jsonData.length; i++) {
        const rowData = jsonData[i];
        if (rowData && rowData.length > 0) {
            const row = {};
            headers.forEach((header, index) => {
                const mappedHeader = columnMapping[header] || header;
                row[mappedHeader] = rowData[index] ? String(rowData[index]).trim() : '';
            });
            
            // Ne garder que les lignes avec au moins un nom ou prénom
            if (row.nom || row.prenom) {
                rows.push(row);
            }
        }
    }
    
    return rows;
};

// ========================================
// VALIDATION DONNÉES SPÉCIFIQUE SGLP
// ========================================

/**
 * Valider les données adhérents - Version SGLP avec NIP gabonais
 */
window.FileUploadSGLP.validateData = function(data) {
    this.updateProgress(this.config.progressSteps.validation, '🔍 Validation des données SGLP...');
    
    const results = {
        valid: [],
        invalid: [],
        stats: {
            total: data.length,
            valides: 0,
            invalides: 0,
            anomalies: {
                critiques: 0,
                majeures: 0,
                mineures: 0
            }
        }
    };
    
    data.forEach((row, index) => {
        const validation = this.validateRowSGLP(row, index + 1);
        
        if (validation.isValid) {
            results.valid.push({
                ...row,
                line_number: index + 1,
                anomalies: validation.anomalies,
                source: 'fichier',
                created_at: new Date().toISOString()
            });
            results.stats.valides++;
        } else {
            results.invalid.push({
                ...row,
                line_number: index + 1,
                errors: validation.errors,
                anomalies: validation.anomalies
            });
            results.stats.invalides++;
        }
        
        // Compter les anomalies
        validation.anomalies.forEach(anomalie => {
            results.stats.anomalies[anomalie.severity]++;
        });
    });
    
    return results;
};

/**
 * Valider une ligne de données - Spécifique SGLP
 */
window.FileUploadSGLP.validateRowSGLP = function(row, lineNumber) {
    const errors = [];
    const anomalies = [];
    
    // ✅ VALIDATION NIP GABONAIS (XX-QQQQ-YYYYMMDD)
    if (!row.nip || !this.config.validation.nipFormat.test(row.nip)) {
        errors.push('NIP invalide (format attendu: XX-QQQQ-YYYYMMDD)');
        anomalies.push({
            field: 'nip',
            type: 'format_invalide',
            severity: 'critiques',
            message: `NIP invalide: ${row.nip || 'manquant'}`,
            suggestion: 'Format attendu: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)'
        });
    } else {
        // Validation supplémentaire de la date dans le NIP
        const datePart = row.nip.slice(-8); // YYYYMMDD
        if (datePart.length === 8) {
            const year = parseInt(datePart.slice(0, 4));
            const month = parseInt(datePart.slice(4, 6));
            const day = parseInt(datePart.slice(6, 8));
            
            const currentYear = new Date().getFullYear();
            if (year < 1920 || year > currentYear) {
                anomalies.push({
                    field: 'nip',
                    type: 'date_invalide',
                    severity: 'majeures',
                    message: `Année de naissance invalide dans NIP: ${year}`,
                    suggestion: `Année doit être entre 1920 et ${currentYear}`
                });
            }
            
            if (month < 1 || month > 12) {
                anomalies.push({
                    field: 'nip',
                    type: 'date_invalide', 
                    severity: 'majeures',
                    message: `Mois invalide dans NIP: ${month}`,
                    suggestion: 'Mois doit être entre 01 et 12'
                });
            }
            
            if (day < 1 || day > 31) {
                anomalies.push({
                    field: 'nip',
                    type: 'date_invalide',
                    severity: 'majeures', 
                    message: `Jour invalide dans NIP: ${day}`,
                    suggestion: 'Jour doit être entre 01 et 31'
                });
            }
        }
    }
    
    // Validation nom/prénom (obligatoires)
    if (!row.nom || row.nom.length < 2) {
        errors.push('Nom manquant ou trop court');
        anomalies.push({
            field: 'nom',
            type: 'donnee_manquante',
            severity: 'critiques',
            message: 'Nom manquant ou trop court',
            suggestion: 'Le nom doit contenir au moins 2 caractères'
        });
    }
    
    if (!row.prenom || row.prenom.length < 2) {
        errors.push('Prénom manquant ou trop court');
        anomalies.push({
            field: 'prenom',
            type: 'donnee_manquante',
            severity: 'critiques',
            message: 'Prénom manquant ou trop court',
            suggestion: 'Le prénom doit contenir au moins 2 caractères'
        });
    }
    
    // Validation civilité
    const civilites_valides = ['M', 'Mme', 'Mlle', 'M.', 'Mr'];
    if (row.civilite && !civilites_valides.includes(row.civilite)) {
        anomalies.push({
            field: 'civilite',
            type: 'valeur_invalide',
            severity: 'mineures',
            message: `Civilité non reconnue: ${row.civilite}`,
            suggestion: 'Utiliser: M, Mme, Mlle'
        });
    }
    
    // ✅ VALIDATION TÉLÉPHONE GABONAIS (+241)
    if (row.telephone && row.telephone.trim()) {
        // Nettoyer le téléphone
        const cleanPhone = row.telephone.replace(/[\s\-\+]/g, '');
        
        if (cleanPhone.startsWith('241')) {
            // Format avec indicatif
            if (!this.config.validation.phoneFormat.test(cleanPhone.slice(3))) {
                anomalies.push({
                    field: 'telephone',
                    type: 'format_invalide',
                    severity: 'mineures',
                    message: `Format téléphone invalide: ${row.telephone}`,
                    suggestion: 'Format attendu: +241 XX XXX XXX ou 0X XXX XXX'
                });
            }
        } else if (cleanPhone.startsWith('0')) {
            // Format local gabonais
            if (!this.config.validation.phoneFormat.test(cleanPhone.slice(1))) {
                anomalies.push({
                    field: 'telephone',
                    type: 'format_invalide',
                    severity: 'mineures',
                    message: `Format téléphone invalide: ${row.telephone}`,
                    suggestion: 'Format attendu: 0X XXX XXX (8-9 chiffres après le 0)'
                });
            }
        } else {
            // Format sans indicatif
            if (!this.config.validation.phoneFormat.test(cleanPhone)) {
                anomalies.push({
                    field: 'telephone',
                    type: 'format_invalide',
                    severity: 'mineures',
                    message: `Format téléphone invalide: ${row.telephone}`,
                    suggestion: 'Format attendu: XX XXX XXX (8-9 chiffres)'
                });
            }
        }
    }
    
    // Validation email si présent
    if (row.email && row.email.trim()) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(row.email)) {
            anomalies.push({
                field: 'email',
                type: 'format_invalide',
                severity: 'mineures',
                message: `Format email invalide: ${row.email}`,
                suggestion: 'Format attendu: nom@domaine.com'
            });
        }
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors,
        anomalies: anomalies
    };
};

// ========================================
// FONCTION PRINCIPALE D'UPLOAD SGLP
// ========================================

/**
 * Fonction principale pour traiter un fichier - Version SGLP
 */
window.FileUploadSGLP.processFile = function(file, options = {}) {
    const config = {
        showModal: true,
        onSuccess: null,
        onError: null,
        onProgress: null,
        saveToSession: false,
        sessionKey: null,
        dossierId: null,
        ...options
    };
    
    return new Promise(async (resolve, reject) => {
        try {
            // Validation fichier
            if (!this.validateFile(file)) {
                reject(new Error('Fichier invalide'));
                return;
            }
            
            // Afficher progression
            this.showProgressModal('Upload fichier adhérents SGLP', config.showModal);
            
            // Lecture fichier
            const rawData = await this.readFile(file);
            
            // Validation données
            const validationResult = await this.validateData(rawData);
            
            // Préparation finale
            this.updateProgress(this.config.progressSteps.preparation, '⚙️ Préparation des données SGLP...');
            
            const finalData = {
                adherents: validationResult.valid,
                invalides: validationResult.invalid,
                stats: validationResult.stats,
                metadata: {
                    filename: file.name,
                    filesize: file.size,
                    processed_at: new Date().toISOString(),
                    total_rows: rawData.length,
                    system: 'SGLP',
                    dossier_id: config.dossierId
                }
            };
            
            // Callback de progression
            if (config.onProgress) {
                config.onProgress(finalData);
            }
            
            // Sauvegarde en session si demandée
            if (config.saveToSession && config.sessionKey) {
                this.updateProgress(this.config.progressSteps.saving, '💾 Sauvegarde en session SGLP...');
                await this.saveToSessionSGLP(finalData, config.sessionKey, config.dossierId);
            }
            
            // Succès
            this.updateProgress(this.config.progressSteps.complete, '✅ Traitement SGLP terminé avec succès !');
            
            setTimeout(() => {
                this.hideProgressModal();
                if (config.onSuccess) {
                    config.onSuccess(finalData);
                }
                resolve(finalData);
            }, 1500);
            
        } catch (error) {
            console.error('❌ Erreur traitement fichier SGLP:', error);
            this.hideProgressModal();
            this.showError(error.message);
            if (config.onError) {
                config.onError(error);
            }
            reject(error);
        }
    });
};

// ========================================
// UTILITAIRES SGLP
// ========================================

/**
 * Sauvegarder en session - Version SGLP
 */
window.FileUploadSGLP.saveToSessionSGLP = function(data, sessionKey, dossierId) {
    return new Promise((resolve, reject) => {
        try {
            // Structure spécifique SGLP
            const sessionData = {
                data: data.adherents,
                total: data.adherents.length,
                created_at: new Date().toISOString(),
                expires_at: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString(), // 2h
                dossier_id: dossierId,
                metadata: data.metadata
            };
            
            // Sauvegarder en sessionStorage local (fallback)
            sessionStorage.setItem(sessionKey, JSON.stringify(sessionData));
            
            // Si disponible, utiliser aussi l'API backend SGLP
            if (window.ConfirmationConfig && window.ConfirmationConfig.routes) {
                fetch('/operator/save-session-adherents', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.ConfirmationConfig.csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        session_key: sessionKey,
                        data: sessionData
                    })
                }).then(response => response.json())
                  .then(result => {
                      console.log('✅ Session SGLP sauvegardée:', result);
                  })
                  .catch(error => {
                      console.warn('⚠️ Erreur sauvegarde session backend:', error);
                  });
            }
            
            setTimeout(resolve, 500);
        } catch (error) {
            reject(error);
        }
    });
};

/**
 * Afficher une erreur - Style SGLP
 */
window.FileUploadSGLP.showError = function(message) {
    if (typeof ConfirmationApp !== 'undefined' && ConfirmationApp.showNotification) {
        ConfirmationApp.showNotification(message, 'danger');
    } else if (typeof showNotification === 'function') {
        showNotification(message, 'danger');
    } else {
        // Fallback : notification basique avec couleurs SGLP
        this.createNotification(message, 'danger');
    }
};

/**
 * Afficher un succès - Style SGLP
 */
window.FileUploadSGLP.showSuccess = function(message) {
    if (typeof ConfirmationApp !== 'undefined' && ConfirmationApp.showNotification) {
        ConfirmationApp.showNotification(message, 'success');
    } else if (typeof showNotification === 'function') {
        showNotification(message, 'success');
    } else {
        // Fallback : notification basique avec couleurs SGLP
        this.createNotification(message, 'success');
    }
};

/**
 * Créer notification basique avec style SGLP
 */
window.FileUploadSGLP.createNotification = function(message, type = 'info') {
    const notification = document.createElement('div');
    
    // Couleurs SGLP selon le type
    let bgColor, iconClass;
    switch(type) {
        case 'success':
            bgColor = this.config.colors.green;
            iconClass = 'fas fa-check-circle';
            break;
        case 'danger':
            bgColor = this.config.colors.red;
            iconClass = 'fas fa-times-circle';
            break;
        case 'warning':
            bgColor = this.config.colors.yellow;
            iconClass = 'fas fa-exclamation-triangle';
            break;
        default:
            bgColor = this.config.colors.blue;
            iconClass = 'fas fa-info-circle';
            break;
    }
    
    notification.className = 'position-fixed top-0 end-0 m-3 fade show';
    notification.style.cssText = `
        z-index: 9999;
        background: ${bgColor};
        color: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center p-3">
            <i class="${iconClass} me-3 fs-5"></i>
            <span class="flex-grow-1">${message}</span>
            <button type="button" class="btn-close btn-close-white ms-3" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove après 5 secondes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
};

// ========================================
// VALIDATION NIP TEMPS RÉEL SGLP
// ========================================

/**
 * Valider un NIP gabonais en temps réel
 */
window.FileUploadSGLP.validateNipRealTime = function(nipValue) {
    const result = {
        valid: false,
        errors: [],
        warnings: []
    };
    
    if (!nipValue || nipValue.trim() === '') {
        result.errors.push('NIP requis');
        return result;
    }
    
    const nip = nipValue.trim().toUpperCase();
    
    // Validation format général
    if (!this.config.validation.nipFormat.test(nip)) {
        result.errors.push('Format invalide (XX-QQQQ-YYYYMMDD)');
        return result;
    }
    
    // Validation des parties
    const parts = nip.split('-');
    if (parts.length !== 3) {
        result.errors.push('Format invalide (utiliser des tirets)');
        return result;
    }
    
    const [prefix, number, dateStr] = parts;
    
    // Validation préfixe (2 caractères alphanumériques)
    if (prefix.length !== 2) {
        result.errors.push('Préfixe doit contenir 2 caractères');
        return result;
    }
    
    // Validation numéro (4 chiffres)
    if (number.length !== 4 || !/^\d{4}$/.test(number)) {
        result.errors.push('Numéro doit contenir 4 chiffres');
        return result;
    }
    
    // Validation date (8 chiffres YYYYMMDD)
    if (dateStr.length !== 8 || !/^\d{8}$/.test(dateStr)) {
        result.errors.push('Date doit contenir 8 chiffres (YYYYMMDD)');
        return result;
    }
    
    // Validation détaillée de la date
    const year = parseInt(dateStr.slice(0, 4));
    const month = parseInt(dateStr.slice(4, 6));
    const day = parseInt(dateStr.slice(6, 8));
    const currentYear = new Date().getFullYear();
    
    if (year < 1920 || year > currentYear) {
        result.warnings.push(`Année ${year} semble inhabituelle`);
    }
    
    if (month < 1 || month > 12) {
        result.errors.push(`Mois invalide: ${month}`);
        return result;
    }
    
    if (day < 1 || day > 31) {
        result.errors.push(`Jour invalide: ${day}`);
        return result;
    }
    
    // Validation plus poussée du jour selon le mois
    const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    const isLeapYear = (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
    
    if (month === 2 && isLeapYear) {
        if (day > 29) {
            result.errors.push(`Jour invalide pour février ${year}: ${day}`);
            return result;
        }
    } else if (day > daysInMonth[month - 1]) {
        result.errors.push(`Jour invalide pour le mois ${month}: ${day}`);
        return result;
    }
    
    // Si on arrive ici, le NIP est valide
    result.valid = true;
    
    // Ajouter des informations utiles
    const birthDate = new Date(year, month - 1, day);
    const age = currentYear - year;
    
    if (age < 16) {
        result.warnings.push('Âge inférieur à 16 ans');
    } else if (age > 100) {
        result.warnings.push('Âge supérieur à 100 ans');
    }
    
    return result;
};

// ========================================
// UTILITAIRES DE FORMATAGE SGLP
// ========================================

/**
 * Formater un numéro de téléphone gabonais
 */
window.FileUploadSGLP.formatPhoneGabon = function(phone) {
    if (!phone) return '';
    
    // Nettoyer le numéro
    const cleanPhone = phone.replace(/[\s\-\+\(\)]/g, '');
    
    // Si commence par 241, ajouter +
    if (cleanPhone.startsWith('241')) {
        const localNumber = cleanPhone.slice(3);
        if (localNumber.length === 8 || localNumber.length === 9) {
            return '+241 ' + localNumber.replace(/(\d{2})(\d{3})(\d{3,4})/, '$1 $2 $3');
        }
    }
    
    // Si commence par 0 (format local)
    if (cleanPhone.startsWith('0')) {
        const localNumber = cleanPhone.slice(1);
        if (localNumber.length === 8 || localNumber.length === 9) {
            return '+241 ' + localNumber.replace(/(\d{2})(\d{3})(\d{3,4})/, '$1 $2 $3');
        }
    }
    
    // Format direct 8-9 chiffres
    if (cleanPhone.length === 8 || cleanPhone.length === 9) {
        return '+241 ' + cleanPhone.replace(/(\d{2})(\d{3})(\d{3,4})/, '$1 $2 $3');
    }
    
    return phone; // Retourner tel quel si format non reconnu
};

/**
 * Normaliser les données d'un adhérent SGLP
 */
window.FileUploadSGLP.normalizeAdherent = function(adherent) {
    const normalized = { ...adherent };
    
    // Normaliser civilité
    if (normalized.civilite) {
        const civiliteMap = {
            'M': 'M.',
            'MR': 'M.',
            'MONSIEUR': 'M.',
            'MME': 'Mme',
            'MADAME': 'Mme',
            'MLLE': 'Mlle',
            'MADEMOISELLE': 'Mlle'
        };
        const upperCivilite = normalized.civilite.toUpperCase();
        normalized.civilite = civiliteMap[upperCivilite] || normalized.civilite;
    }
    
    // Normaliser nom et prénom
    if (normalized.nom) {
        normalized.nom = normalized.nom.trim().toUpperCase();
    }
    if (normalized.prenom) {
        normalized.prenom = normalized.prenom.trim()
            .split(' ')
            .map(part => part.charAt(0).toUpperCase() + part.slice(1).toLowerCase())
            .join(' ');
    }
    
    // Normaliser NIP
    if (normalized.nip) {
        normalized.nip = normalized.nip.trim().toUpperCase();
    }
    
    // Normaliser téléphone
    if (normalized.telephone) {
        normalized.telephone = this.formatPhoneGabon(normalized.telephone);
    }
    
    // Normaliser email
    if (normalized.email) {
        normalized.email = normalized.email.trim().toLowerCase();
    }
    
    return normalized;
};

/**
 * Générer un rapport de validation SGLP
 */
window.FileUploadSGLP.generateValidationReport = function(validationResult) {
    const { valid, invalid, stats } = validationResult;
    
    const report = {
        summary: {
            total: stats.total,
            valides: stats.valides,
            invalides: stats.invalides,
            taux_validite: Math.round((stats.valides / stats.total) * 100)
        },
        anomalies: {
            critiques: stats.anomalies.critiques,
            majeures: stats.anomalies.majeures,
            mineures: stats.anomalies.mineures,
            total: stats.anomalies.critiques + stats.anomalies.majeures + stats.anomalies.mineures
        },
        erreurs_frequentes: {},
        recommandations: []
    };
    
    // Analyser les erreurs fréquentes
    invalid.forEach(item => {
        item.errors.forEach(error => {
            report.erreurs_frequentes[error] = (report.erreurs_frequentes[error] || 0) + 1;
        });
    });
    
    // Générer des recommandations
    if (report.anomalies.critiques > 0) {
        report.recommandations.push('Corriger les anomalies critiques avant import');
    }
    
    if (report.summary.taux_validite < 80) {
        report.recommandations.push('Vérifier le format du fichier - taux de validité faible');
    }
    
    const nipErrors = Object.keys(report.erreurs_frequentes)
        .filter(error => error.includes('NIP'))
        .reduce((sum, error) => sum + report.erreurs_frequentes[error], 0);
    
    if (nipErrors > report.summary.total * 0.1) {
        report.recommandations.push('Problème fréquent avec le format NIP - vérifier le format XX-QQQQ-YYYYMMDD');
    }
    
    return report;
};

// ========================================
// INTÉGRATION AVEC CONFIRMATION.JS
// ========================================

/**
 * Fonction d'initialisation pour l'intégration avec confirmation.js
 */
window.FileUploadSGLP.initializeForConfirmation = function() {
    console.log('📦 FileUploadSGLP v1.0 initialisé pour confirmation.blade.php');
    
    // Exposer les fonctions principales pour confirmation.js
    window.FileUploadSGLP.processAdditionalFile = function(file, dossierId) {
        return this.processFile(file, {
            showModal: false, // Utiliser le mode inline
            dossierId: dossierId,
            onSuccess: (data) => {
                console.log('✅ Fichier SGLP traité avec succès:', data);
                // Callback vers confirmation.js si disponible
                if (window.ConfirmationApp && window.ConfirmationApp.handleFileProcessed) {
                    window.ConfirmationApp.handleFileProcessed(data);
                }
            },
            onError: (error) => {
                console.error('❌ Erreur traitement fichier SGLP:', error);
                // Callback vers confirmation.js si disponible
                if (window.ConfirmationApp && window.ConfirmationApp.handleFileError) {
                    window.ConfirmationApp.handleFileError(error);
                }
            },
            onProgress: (data) => {
                // Callback de progression
                if (window.ConfirmationApp && window.ConfirmationApp.updateFileProgress) {
                    window.ConfirmationApp.updateFileProgress(data);
                }
            }
        });
    };
    
    // Validation temps réel pour les champs de saisie manuelle
    window.FileUploadSGLP.setupRealtimeValidation = function() {
        const nipInput = document.getElementById('additional_adherent_nip');
        if (nipInput) {
            nipInput.addEventListener('input', (e) => {
                const validation = this.validateNipRealTime(e.target.value);
                this.updateNipValidationUI(validation, 'additional');
            });
        }
    };
    
    // Mettre à jour l'interface de validation NIP
    window.FileUploadSGLP.updateNipValidationUI = function(validation, prefix = '') {
        const validIcon = document.getElementById(`nip-valid-${prefix}`);
        const invalidIcon = document.getElementById(`nip-invalid-${prefix}`);
        const pendingIcon = document.getElementById(`nip-pending-${prefix}`);
        const errorDiv = document.getElementById(`${prefix}_adherent_nip_error`);
        const nipInput = document.getElementById(`${prefix}_adherent_nip`);
        
        // Réinitialiser les icônes
        [validIcon, invalidIcon, pendingIcon].forEach(icon => {
            if (icon) icon.classList.add('d-none');
        });
        
        if (!validation.valid && validation.errors.length > 0) {
            // Erreurs critiques
            if (invalidIcon) invalidIcon.classList.remove('d-none');
            if (nipInput) {
                nipInput.classList.remove('nip-valid', 'nip-validating');
                nipInput.classList.add('nip-invalid');
            }
            if (errorDiv) errorDiv.textContent = validation.errors.join(', ');
        } else if (validation.valid) {
            // Valide
            if (validIcon) validIcon.classList.remove('d-none');
            if (nipInput) {
                nipInput.classList.remove('nip-invalid', 'nip-validating');
                nipInput.classList.add('nip-valid');
            }
            if (errorDiv) {
                errorDiv.textContent = validation.warnings.length > 0 ? 
                    'Valide (avertissements: ' + validation.warnings.join(', ') + ')' : '';
            }
        } else {
            // En attente
            if (pendingIcon) pendingIcon.classList.remove('d-none');
            if (nipInput) {
                nipInput.classList.remove('nip-valid', 'nip-invalid');
                nipInput.classList.add('nip-validating');
            }
        }
    };
    
    return true;
};

// ========================================
// TEMPLATE ET AIDE
// ========================================

/**
 * Générer un template Excel pour SGLP
 */
window.FileUploadSGLP.generateTemplate = function() {
    const templateData = [
        ['Civilité', 'Nom', 'Prénom', 'NIP', 'Téléphone', 'Profession', 'Email', 'Adresse'],
        ['M.', 'MBENG', 'Jean Claude', 'A1-2345-19901225', '01234567', 'Ingénieur', 'jean.mbeng@email.ga', 'Libreville'],
        ['Mme', 'OBAMA', 'Marie France', 'B2-3456-19851115', '07654321', 'Professeure', 'marie.obama@email.ga', 'Port-Gentil'],
        ['Mlle', 'NZIGOU', 'Sarah', 'C3-4567-19950310', '02345678', 'Étudiante', 'sarah.nzigou@email.ga', 'Franceville']
    ];
    
    // Si XLSX est disponible, générer un vrai fichier Excel
    if (typeof XLSX !== 'undefined') {
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.aoa_to_sheet(templateData);
        
        // Définir la largeur des colonnes
        worksheet['!cols'] = [
            { width: 10 },  // Civilité
            { width: 20 },  // Nom
            { width: 20 },  // Prénom
            { width: 18 },  // NIP
            { width: 15 },  // Téléphone
            { width: 20 },  // Profession
            { width: 25 },  // Email
            { width: 30 }   // Adresse
        ];
        
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Adhérents');
        XLSX.writeFile(workbook, 'modele_adherents_sglp.xlsx');
    } else {
        // Fallback CSV
        const csvContent = templateData.map(row => row.join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'modele_adherents_sglp.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    this.showSuccess('Modèle téléchargé avec succès !');
};

/**
 * Afficher l'aide sur le format des fichiers SGLP
 */
window.FileUploadSGLP.showFormatHelp = function() {
    const helpContent = `
        <div class="format-help-sglp">
            <h5><i class="fas fa-question-circle me-2" style="color: #003f7f;"></i>Format des fichiers adhérents SGLP</h5>
            
            <div class="mb-3">
                <h6 class="text-success">Colonnes obligatoires :</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check me-2 text-success"></i><strong>Civilité</strong> : M., Mme, Mlle</li>
                    <li><i class="fas fa-check me-2 text-success"></i><strong>Nom</strong> : Nom de famille (minimum 2 caractères)</li>
                    <li><i class="fas fa-check me-2 text-success"></i><strong>Prénom</strong> : Prénom(s) (minimum 2 caractères)</li>
                    <li><i class="fas fa-check me-2 text-success"></i><strong>NIP</strong> : Format XX-QQQQ-YYYYMMDD</li>
                </ul>
            </div>
            
            <div class="mb-3">
                <h6 class="text-info">Colonnes optionnelles :</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-info me-2 text-info"></i><strong>Téléphone</strong> : Format gabonais (+241 XX XXX XXX)</li>
                    <li><i class="fas fa-info me-2 text-info"></i><strong>Profession</strong> : Métier ou fonction</li>
                    <li><i class="fas fa-info me-2 text-info"></i><strong>Email</strong> : Adresse email valide</li>
                    <li><i class="fas fa-info me-2 text-info"></i><strong>Adresse</strong> : Adresse complète</li>
                </ul>
            </div>
            
            <div class="alert" style="background: rgba(0,158,63,0.1); border-color: #009e3f;">
                <h6 class="text-success">Format NIP gabonais :</h6>
                <p class="mb-1"><strong>Structure :</strong> XX-QQQQ-YYYYMMDD</p>
                <ul class="small mb-0">
                    <li><strong>XX</strong> : 2 caractères (lettres ou chiffres)</li>
                    <li><strong>QQQQ</strong> : 4 chiffres (numéro séquentiel)</li>
                    <li><strong>YYYYMMDD</strong> : Date de naissance (année-mois-jour)</li>
                </ul>
                <p class="mt-2 mb-0"><strong>Exemple :</strong> A1-2345-19901225 (né le 25 décembre 1990)</p>
            </div>
            
            <div class="text-center mt-3">
                <button class="btn btn-success" onclick="FileUploadSGLP.generateTemplate()">
                    <i class="fas fa-download me-2"></i>Télécharger le modèle
                </button>
            </div>
        </div>
    `;
    
    // Afficher dans une modal Bootstrap si disponible
    if (typeof bootstrap !== 'undefined') {
        const modalHTML = `
            <div class="modal fade" id="formatHelpModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%); color: white;">
                            <h5 class="modal-title">Aide - Format des fichiers</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${helpContent}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const existingModal = document.getElementById('formatHelpModal');
        if (existingModal) existingModal.remove();
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('formatHelpModal'));
        modal.show();
    } else {
        // Fallback : afficher directement dans une div
        const helpDiv = document.createElement('div');
        helpDiv.innerHTML = helpContent;
        helpDiv.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            z-index: 9999;
        `;
        
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9998;
        `;
        
        overlay.addEventListener('click', () => {
            document.body.removeChild(helpDiv);
            document.body.removeChild(overlay);
        });
        
        document.body.appendChild(overlay);
        document.body.appendChild(helpDiv);
    }
};

// ========================================
// INITIALISATION AUTOMATIQUE
// ========================================

/**
 * Initialiser le module SGLP
 */
window.FileUploadSGLP.init = function() {
    console.log('📦 FileUploadSGLP v1.0 - Module d\'upload adapté pour SGLP initialisé');
    
    // Vérifier les dépendances
    const dependencies = {
        'Bootstrap': typeof bootstrap !== 'undefined',
        'XLSX': typeof XLSX !== 'undefined',
        'PapaParse': typeof Papa !== 'undefined'
    };
    
    console.log('📋 Dépendances SGLP:', dependencies);
    
    // Initialiser pour confirmation.blade.php si détecté
    if (window.ConfirmationConfig) {
        this.initializeForConfirmation();
        this.setupRealtimeValidation();
    }
    
    return true;
};

// Auto-initialisation
document.addEventListener('DOMContentLoaded', function() {
    window.FileUploadSGLP.init();
});

// Export pour modules ES6 si supporté
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.FileUploadSGLP;
}

console.log(`
🎉 ========================================================================
   FILE-UPLOAD-SGLP.JS v1.0 - MODULE ADAPTÉ GABON
   ========================================================================
   
   ✅ Adaptation du module file-upload-common.js pour SGLP
   🇬🇦 Format NIP gabonais : XX-QQQQ-YYYYMMDD implémenté
   🎨 Couleurs officielles du Gabon intégrées
   📱 Compatible confirmation.blade.php + notification SGLP
   🔍 Validation temps réel avec interface gabonaise
   📊 Génération de rapports et templates SGLP
   
   🚀 FONCTIONNALITÉS PRINCIPALES :
   ✅ Validation fichiers Excel/CSV avec format gabonais
   ✅ Progress bars avec couleurs nationales
   ✅ Validation NIP temps réel XX-QQQQ-YYYYMMDD
   ✅ Formatage téléphone gabonais (+241)
   ✅ Génération template avec exemples gabonais
   ✅ Intégration native avec confirmation.js
   ✅ Notifications modernes style SGLP
   ✅ Mode inline et modal pour différents contextes
   
   🎯 Prêt pour l'intégration avec confirmation.blade.php
   📦 Module autonome avec fallbacks intelligents
   🇬🇦 Optimisé pour l'administration gabonaise
========================================================================
`);