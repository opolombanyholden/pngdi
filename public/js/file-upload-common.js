/**
 * ========================================================================
 * MODULE COMMUN UPLOAD FICHIERS - PHASES 1 & 2
 * ========================================================================
 * 
 * Module réutilisable pour l'upload et traitement des fichiers adhérents
 * Compatible avec organisation-create.js (Phase 1) et confirmation.blade.php (Phase 2)
 * 
 * Fichier: public/js/file-upload-common.js
 * Version: 1.0
 * Compatibilité: PHP 7.3.29, Laravel, Bootstrap 5
 */

window.FileUploadCommon = window.FileUploadCommon || {};

// ========================================
// CONFIGURATION GLOBALE
// ========================================

window.FileUploadCommon.config = {
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
        requiredFields: ['nom', 'prenom', 'nip'],
        nipFormat: /^\d{10}$/,
        phoneFormat: /^\d{8,15}$/
    }
};

// ========================================
// VALIDATION FICHIER
// ========================================

/**
 * Valider le fichier sélectionné (taille, format)
 */
window.FileUploadCommon.validateFile = function(file) {
    console.log('🔍 Validation fichier:', file.name);
    
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
    
    console.log('✅ Fichier valide');
    return true;
};

// ========================================
// INTERFACE PROGRESS BAR MODERNE
// ========================================

/**
 * Créer et afficher la modal de progression
 */
window.FileUploadCommon.showProgressModal = function(title = 'Upload en cours...', showInModal = true) {
    this.hideProgressModal(); // Nettoyer d'abord
    
    if (showInModal) {
        const modalHTML = `
            <div class="modal fade" id="fileUploadProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-file-upload me-2"></i>
                                ${title}
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                            
                            <div class="progress mb-3" style="height: 25px;">
                                <div id="fileUploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span id="fileUploadProgressText">0%</span>
                                </div>
                            </div>
                            
                            <div id="fileUploadProgressMessage" class="text-center text-muted">
                                Initialisation...
                            </div>
                            
                            <div id="fileUploadProgressDetails" class="mt-3 small text-muted d-none">
                                <!-- Détails supplémentaires -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('fileUploadProgressModal'));
        modal.show();
    } else {
        // Mode inline pour confirmation.blade.php
        this.createInlineProgress();
    }
};

/**
 * Créer la progress bar inline (pour Phase 2)
 */
window.FileUploadCommon.createInlineProgress = function() {
    const existingProgress = document.getElementById('inline-upload-progress');
    if (existingProgress) {
        existingProgress.remove();
    }
    
    const progressHTML = `
        <div id="inline-upload-progress" class="mt-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="small fw-bold">Upload en cours...</span>
                <span class="small" id="inline-upload-percentage">0%</span>
            </div>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                     id="inline-upload-progress-bar" 
                     style="width: 0%"
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <div id="inline-upload-message" class="small text-muted mt-1">
                Initialisation...
            </div>
        </div>
    `;
    
    // Insérer après le formulaire d'upload
    const uploadForm = document.getElementById('additional-batch-form');
    if (uploadForm) {
        uploadForm.insertAdjacentHTML('afterend', progressHTML);
    }
};

/**
 * Mettre à jour la progression
 */
window.FileUploadCommon.updateProgress = function(percentage, message, details = '') {
    percentage = Math.min(100, Math.max(0, percentage));
    
    // Mise à jour modal
    const modalProgressBar = document.getElementById('fileUploadProgressBar');
    const modalProgressText = document.getElementById('fileUploadProgressText');
    const modalProgressMessage = document.getElementById('fileUploadProgressMessage');
    
    if (modalProgressBar) {
        modalProgressBar.style.width = percentage + '%';
        modalProgressBar.setAttribute('aria-valuenow', percentage);
    }
    if (modalProgressText) modalProgressText.textContent = percentage + '%';
    if (modalProgressMessage) modalProgressMessage.textContent = message;
    
    // Mise à jour inline
    const inlineProgressBar = document.getElementById('inline-upload-progress-bar');
    const inlinePercentage = document.getElementById('inline-upload-percentage');
    const inlineMessage = document.getElementById('inline-upload-message');
    
    if (inlineProgressBar) {
        inlineProgressBar.style.width = percentage + '%';
        inlineProgressBar.setAttribute('aria-valuenow', percentage);
    }
    if (inlinePercentage) inlinePercentage.textContent = percentage + '%';
    if (inlineMessage) inlineMessage.textContent = message;
    
    // Détails supplémentaires
    if (details) {
        const modalDetails = document.getElementById('fileUploadProgressDetails');
        if (modalDetails) {
            modalDetails.innerHTML = details;
            modalDetails.classList.remove('d-none');
        }
    }
    
    console.log(`📊 Progress: ${percentage}% - ${message}`);
};

/**
 * Masquer la modal de progression
 */
window.FileUploadCommon.hideProgressModal = function() {
    const existingModal = document.getElementById('fileUploadProgressModal');
    if (existingModal) {
        const modalInstance = bootstrap.Modal.getInstance(existingModal);
        if (modalInstance) {
            modalInstance.hide();
        }
        setTimeout(() => existingModal.remove(), 300);
    }
    
    const inlineProgress = document.getElementById('inline-upload-progress');
    if (inlineProgress) {
        inlineProgress.remove();
    }
};

// ========================================
// LECTURE ET TRAITEMENT FICHIERS
// ========================================

/**
 * Lire le fichier avec progression
 */
window.FileUploadCommon.readFile = function(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            try {
                this.updateProgress(this.config.progressSteps.reading, '📖 Lecture du fichier...');
                
                const data = e.target.result;
                let parsedData = [];
                
                if (file.name.toLowerCase().endsWith('.csv')) {
                    parsedData = this.parseCSV(data);
                } else {
                    parsedData = this.parseExcel(data);
                }
                
                console.log(`✅ Fichier lu: ${parsedData.length} lignes`);
                resolve(parsedData);
                
            } catch (error) {
                console.error('❌ Erreur lecture fichier:', error);
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
 * Parser CSV simple
 */
window.FileUploadCommon.parseCSV = function(data) {
    const lines = data.split('\n').filter(line => line.trim());
    if (lines.length < 2) return [];
    
    const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
    const rows = [];
    
    for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(',');
        if (values.length >= headers.length) {
            const row = {};
            headers.forEach((header, index) => {
                row[header] = values[index] ? values[index].trim() : '';
            });
            rows.push(row);
        }
    }
    
    return rows;
};

/**
 * Parser Excel avec XLSX
 */
window.FileUploadCommon.parseExcel = function(data) {
    if (typeof XLSX === 'undefined') {
        throw new Error('Librairie XLSX non disponible');
    }
    
    const workbook = XLSX.read(data, { type: 'binary' });
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];
    
    return XLSX.utils.sheet_to_json(worksheet, { header: 1 }).slice(1).map(row => {
        return {
            nom: row[0] || '',
            prenom: row[1] || '',
            nip: row[2] || '',
            telephone: row[3] || '',
            profession: row[4] || ''
        };
    }).filter(row => row.nom || row.prenom || row.nip);
};

// ========================================
// VALIDATION DONNÉES
// ========================================

/**
 * Valider les données adhérents
 */
window.FileUploadCommon.validateData = function(data) {
    this.updateProgress(this.config.progressSteps.validation, '🔍 Validation des données...');
    
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
        const validation = this.validateRow(row, index + 1);
        
        if (validation.isValid) {
            results.valid.push({
                ...row,
                line_number: index + 1,
                anomalies: validation.anomalies
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
 * Valider une ligne de données
 */
window.FileUploadCommon.validateRow = function(row, lineNumber) {
    const errors = [];
    const anomalies = [];
    
    // Validation NIP (obligatoire)
    if (!row.nip || !this.config.validation.nipFormat.test(row.nip)) {
        errors.push('NIP invalide (10 chiffres requis)');
        anomalies.push({
            field: 'nip',
            type: 'format_invalide',
            severity: 'critiques',
            message: 'NIP invalide'
        });
    }
    
    // Validation nom/prénom (obligatoires)
    if (!row.nom || row.nom.length < 2) {
        errors.push('Nom manquant ou trop court');
        anomalies.push({
            field: 'nom',
            type: 'donnee_manquante',
            severity: 'critiques',
            message: 'Nom manquant'
        });
    }
    
    if (!row.prenom || row.prenom.length < 2) {
        errors.push('Prénom manquant ou trop court');
        anomalies.push({
            field: 'prenom',
            type: 'donnee_manquante',
            severity: 'critiques',
            message: 'Prénom manquant'
        });
    }
    
    // Validation téléphone (optionnel mais si présent, doit être valide)
    if (row.telephone && !this.config.validation.phoneFormat.test(row.telephone)) {
        anomalies.push({
            field: 'telephone',
            type: 'format_invalide',
            severity: 'mineures',
            message: 'Format téléphone invalide'
        });
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors,
        anomalies: anomalies
    };
};

// ========================================
// FONCTION PRINCIPALE D'UPLOAD
// ========================================

/**
 * Fonction principale pour traiter un fichier
 */
window.FileUploadCommon.processFile = function(file, options = {}) {
    const config = {
        showModal: true,
        onSuccess: null,
        onError: null,
        onProgress: null,
        saveToSession: false,
        sessionKey: null,
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
            this.showProgressModal('Upload fichier adhérents', config.showModal);
            
            // Lecture fichier
            const rawData = await this.readFile(file);
            
            // Validation données
            const validationResult = await this.validateData(rawData);
            
            // Préparation finale
            this.updateProgress(this.config.progressSteps.preparation, '⚙️ Préparation des données...');
            
            const finalData = {
                adherents: validationResult.valid,
                invalides: validationResult.invalid,
                stats: validationResult.stats,
                metadata: {
                    filename: file.name,
                    filesize: file.size,
                    processed_at: new Date().toISOString(),
                    total_rows: rawData.length
                }
            };
            
            // Sauvegarde en session si demandée
            if (config.saveToSession && config.sessionKey) {
                this.updateProgress(this.config.progressSteps.saving, '💾 Sauvegarde en session...');
                await this.saveToSession(finalData, config.sessionKey);
            }
            
            // Succès
            this.updateProgress(this.config.progressSteps.complete, '✅ Traitement terminé avec succès !');
            
            setTimeout(() => {
                this.hideProgressModal();
                resolve(finalData);
            }, 1500);
            
        } catch (error) {
            console.error('❌ Erreur traitement fichier:', error);
            this.hideProgressModal();
            this.showError(error.message);
            reject(error);
        }
    });
};

// ========================================
// UTILITAIRES
// ========================================

/**
 * Sauvegarder en session
 */
window.FileUploadCommon.saveToSession = function(data, sessionKey) {
    return new Promise((resolve) => {
        // Simulation sauvegarde session
        sessionStorage.setItem(sessionKey, JSON.stringify(data));
        setTimeout(resolve, 500);
    });
};

/**
 * Afficher une erreur
 */
window.FileUploadCommon.showError = function(message) {
    if (typeof showNotification === 'function') {
        showNotification(message, 'danger');
    } else {
        alert('Erreur: ' + message);
    }
};

/**
 * Afficher un succès
 */
window.FileUploadCommon.showSuccess = function(message) {
    if (typeof showNotification === 'function') {
        showNotification(message, 'success');
    } else {
        alert('Succès: ' + message);
    }
};

// ========================================
// INITIALISATION
// ========================================

/**
 * Initialiser le module
 */
window.FileUploadCommon.init = function() {
    console.log('📦 FileUploadCommon v1.0 initialisé');
    return true;
};

// Auto-initialisation
document.addEventListener('DOMContentLoaded', function() {
    window.FileUploadCommon.init();
});