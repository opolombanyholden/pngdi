/**
 * ========================================================================
 * ADHERENTS MANAGER - GESTIONNAIRE AUTONOME DES ADHÉRENTS
 * Version: 2.0 - Module dédié pour le formulaire externe
 * ========================================================================
 * 
 * Fonctionnalités:
 * - Saisie manuelle avec validation
 * - Import de fichiers Excel/CSV
 * - Validation en temps réel des NIP
 * - Gestion des doublons
 * - Export CSV
 * - Sauvegarde automatique
 */

window.AdherentsManager = {
    // ===== CONFIGURATION =====
    config: {
        maxAdherents: 10000,
        autoSaveInterval: 30000,
        supportedFormats: ['.xlsx', '.xls', '.csv'],
        requiredColumns: ['civilite', 'nom', 'prenom', 'nip'],
        optionalColumns: ['telephone', 'profession'],
        nipPattern: /^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/
    },

    // ===== ÉTAT INTERNE =====
    state: {
        adherents: [],
        currentMode: 'manuel',
        isLoading: false,
        hasUnsavedChanges: false,
        lastSaveTime: null,
        validationErrors: [],
        duplicates: [],
        statistics: {
            total: 0,
            valid: 0,
            errors: 0,
            duplicates: 0
        }
    },

    // ===== INITIALISATION =====
    init() {
        console.log('🚀 Initialisation AdherentsManager v2.0');
        
        this.setupEventListeners();
        this.loadExistingData();
        this.setupAutoSave();
        this.updateRequirements();
        this.renderAdherentsList();
        
        console.log('✅ AdherentsManager initialisé avec succès');
    },

    // ===== CONFIGURATION DES ÉVÉNEMENTS =====
    setupEventListeners() {
        // Mode de saisie
        const modeInputs = document.querySelectorAll('input[name="adherent_mode"]');
        modeInputs.forEach(input => {
            input.addEventListener('change', (e) => this.switchMode(e.target.value));
        });

        // Formulaire de saisie manuelle
        const addBtn = document.getElementById('addAdherentBtn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.addAdherentManually());
        }

        // Upload de fichier
        const fileInput = document.getElementById('adherents_file');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileUpload(e));
        }

        // Zone de drop
        const dropZone = document.getElementById('file-drop-zone');
        if (dropZone) {
            this.setupDropZone(dropZone, fileInput);
        }

        // Bouton de sélection de fichier
        const selectBtn = document.getElementById('select-file-btn-manual');
        if (selectBtn) {
            selectBtn.addEventListener('click', () => fileInput?.click());
        }

        // Téléchargement du template
        const templateBtn = document.getElementById('downloadTemplateBtn');
        if (templateBtn) {
            templateBtn.addEventListener('click', () => this.downloadTemplate());
        }

        // Validation NIP en temps réel
        const nipInput = document.getElementById('adherent_nip');
        if (nipInput) {
            nipInput.addEventListener('input', (e) => this.validateNipRealTime(e.target));
        }

        // Boutons d'action
        const saveBtn = document.getElementById('saveProgressBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveProgress());
        }

        const finalizeBtn = document.getElementById('finalizeAdherentsBtn');
        if (finalizeBtn) {
            finalizeBtn.addEventListener('click', () => this.finalizeAdherents());
        }

        // Prévenir la perte de données
        window.addEventListener('beforeunload', (e) => {
            if (this.state.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter ?';
                return e.returnValue;
            }
        });
    },

    // ===== GESTION DES MODES =====
    switchMode(mode) {
        console.log(`🔄 Changement de mode: ${this.state.currentMode} → ${mode}`);
        
        this.state.currentMode = mode;
        
        const manuelSection = document.getElementById('adherent_manuel_section');
        const fichierSection = document.getElementById('adherent_fichier_section');
        
        if (mode === 'manuel') {
            manuelSection?.classList.remove('d-none');
            fichierSection?.classList.add('d-none');
        } else if (mode === 'fichier') {
            manuelSection?.classList.add('d-none');
            fichierSection?.classList.remove('d-none');
        }
    },

    // ===== SAISIE MANUELLE =====
    addAdherentManually() {
        const formData = this.getManualFormData();
        
        if (!this.validateAdherentData(formData)) {
            return;
        }

        // Vérifier les doublons
        if (this.isDuplicate(formData.nip)) {
            this.showError('Ce NIP existe déjà dans la liste');
            return;
        }

        // Ajouter l'adhérent
        const adherent = {
            id: Date.now(),
            ...formData,
            dateAjout: new Date().toISOString(),
            source: 'manuel'
        };

        this.state.adherents.push(adherent);
        this.clearManualForm();
        this.updateStatistics();
        this.renderAdherentsList();
        this.markAsChanged();
        
        this.showSuccess(`Adhérent ${adherent.prenom} ${adherent.nom} ajouté avec succès`);
        
        console.log('➕ Adhérent ajouté:', adherent);
    },

    getManualFormData() {
        return {
            civilite: document.getElementById('adherent_civilite')?.value || '',
            nom: document.getElementById('adherent_nom')?.value?.trim() || '',
            prenom: document.getElementById('adherent_prenom')?.value?.trim() || '',
            nip: document.getElementById('adherent_nip')?.value?.trim() || '',
            telephone: document.getElementById('adherent_telephone')?.value?.trim() || '',
            profession: document.getElementById('adherent_profession')?.value?.trim() || ''
        };
    },

    clearManualForm() {
        const form = document.querySelector('#adherent_manuel_section .card-body');
        if (form) {
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'select-one') {
                    input.selectedIndex = 0;
                } else {
                    input.value = '';
                }
                input.classList.remove('is-valid', 'is-invalid');
            });
        }
    },

    // ===== VALIDATION =====
    validateAdherentData(data) {
        const errors = [];
        
        // Vérifications obligatoires
        if (!data.nom) errors.push('Le nom est obligatoire');
        if (!data.prenom) errors.push('Le prénom est obligatoire');
        if (!data.nip) errors.push('Le NIP est obligatoire');
        
        // Validation du NIP
        if (data.nip && !this.config.nipPattern.test(data.nip)) {
            errors.push('Format NIP invalide (attendu: XX-QQQQ-YYYYMMDD)');
        }

        // Validation du téléphone (si fourni)
        if (data.telephone && !/^[0-9]{8,9}$/.test(data.telephone.replace(/\s/g, ''))) {
            errors.push('Format téléphone invalide (8-9 chiffres)');
        }

        if (errors.length > 0) {
            this.showError('Erreurs de validation:<br>• ' + errors.join('<br>• '));
            return false;
        }

        return true;
    },

    validateNipRealTime(input) {
        const value = input.value.trim();
        const isValid = this.config.nipPattern.test(value);
        
        input.classList.remove('is-valid', 'is-invalid');
        
        if (value.length === 0) {
            return; // Pas de validation si vide
        }
        
        if (isValid) {
            input.classList.add('is-valid');
            
            // Extraire l'âge si possible
            if (window.NipValidation) {
                const validation = window.NipValidation.validateFormat(value);
                if (validation.valid && validation.extracted_info) {
                    const ageInfo = document.querySelector('#adherent_nip + .input-group-text + .age-info');
                    if (ageInfo) {
                        ageInfo.textContent = `${validation.extracted_info.age} ans`;
                    }
                }
            }
        } else {
            input.classList.add('is-invalid');
        }
    },

    isDuplicate(nip) {
        return this.state.adherents.some(adherent => adherent.nip === nip);
    },

    // ===== IMPORT DE FICHIERS =====
    setupDropZone(dropZone, fileInput) {
        // Événements de drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFileUpload({ target: { files } });
            }
        });

        // Clic sur la zone
        dropZone.addEventListener('click', () => fileInput?.click());
    },

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    },

    async handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        console.log('📁 Traitement du fichier:', file.name);

        // Validation du fichier
        if (!this.validateFile(file)) {
            return;
        }

        this.showLoading('Analyse du fichier en cours...');

        try {
            const data = await this.parseFile(file);
            await this.processImportedData(data);
            
            this.showSuccess(`Fichier importé avec succès: ${this.state.adherents.length} adhérents`);
        } catch (error) {
            console.error('❌ Erreur import:', error);
            this.showError(`Erreur lors de l'import: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    },

    validateFile(file) {
        // Vérifier l'extension
        const extension = '.' + file.name.split('.').pop().toLowerCase();
        if (!this.config.supportedFormats.includes(extension)) {
            this.showError(`Format non supporté. Utilisez: ${this.config.supportedFormats.join(', ')}`);
            return false;
        }

        // Vérifier la taille (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            this.showError('Fichier trop volumineux (maximum 10MB)');
            return false;
        }

        return true;
    },

    async parseFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    const extension = '.' + file.name.split('.').pop().toLowerCase();
                    
                    if (extension === '.csv') {
                        const data = this.parseCSV(e.target.result);
                        resolve(data);
                    } else {
                        const workbook = XLSX.read(e.target.result, { type: 'binary' });
                        const sheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[sheetName];
                        const data = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                        resolve(data);
                    }
                } catch (error) {
                    reject(new Error('Erreur de lecture du fichier: ' + error.message));
                }
            };
            
            reader.onerror = () => reject(new Error('Erreur de lecture du fichier'));
            
            if (file.name.endsWith('.csv')) {
                reader.readAsText(file, 'UTF-8');
            } else {
                reader.readAsBinaryString(file);
            }
        });
    },

    parseCSV(text) {
        const lines = text.split('\n').filter(line => line.trim());
        return lines.map(line => {
            // Simple CSV parsing (peut être amélioré)
            return line.split(',').map(cell => cell.trim().replace(/"/g, ''));
        });
    },

    async processImportedData(rawData) {
        if (!rawData || rawData.length === 0) {
            throw new Error('Fichier vide ou non lisible');
        }

        const headers = rawData[0].map(h => h.toLowerCase().trim());
        const rows = rawData.slice(1);

        console.log('📊 Headers détectés:', headers);
        console.log('📊 Nombre de lignes:', rows.length);

        // Mapper les colonnes
        const columnMap = this.mapColumns(headers);
        if (!columnMap) {
            throw new Error('Colonnes requises manquantes');
        }

        // Traiter chaque ligne
        const newAdherents = [];
        const errors = [];

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            if (row.every(cell => !cell || cell.trim() === '')) {
                continue; // Ignorer les lignes vides
            }

            try {
                const adherent = this.mapRowToAdherent(row, columnMap, i + 2);
                if (adherent) {
                    newAdherents.push(adherent);
                }
            } catch (error) {
                errors.push(`Ligne ${i + 2}: ${error.message}`);
            }
        }

        if (errors.length > 0) {
            console.warn('⚠️ Erreurs lors de l\'import:', errors);
            this.showWarning(`${errors.length} erreurs détectées. Voir la console pour plus de détails.`);
        }

        // Ajouter les nouveaux adhérents
        this.state.adherents = [...this.state.adherents, ...newAdherents];
        this.updateStatistics();
        this.renderAdherentsList();
        this.markAsChanged();

        console.log(`✅ Import terminé: ${newAdherents.length} adhérents ajoutés`);
    },

    mapColumns(headers) {
        const map = {};
        
        // Mapping flexible des colonnes
        const mappings = {
            civilite: ['civilite', 'civilité', 'titre', 'mr', 'mme'],
            nom: ['nom', 'name', 'lastname', 'family_name'],
            prenom: ['prenom', 'prénom', 'prénoms', 'firstname', 'given_name'],
            nip: ['nip', 'numero', 'numéro', 'id', 'identifier'],
            telephone: ['telephone', 'téléphone', 'phone', 'tel', 'mobile'],
            profession: ['profession', 'metier', 'métier', 'job', 'occupation']
        };

        for (const [field, possibilities] of Object.entries(mappings)) {
            const index = headers.findIndex(h => possibilities.includes(h));
            if (index !== -1) {
                map[field] = index;
            }
        }

        // Vérifier les colonnes obligatoires
        const required = ['nom', 'prenom', 'nip'];
        const missing = required.filter(field => map[field] === undefined);
        
        if (missing.length > 0) {
            this.showError(`Colonnes manquantes: ${missing.join(', ')}`);
            return null;
        }

        return map;
    },

    mapRowToAdherent(row, columnMap, lineNumber) {
        const data = {};
        
        // Extraire les données selon le mapping
        for (const [field, index] of Object.entries(columnMap)) {
            data[field] = row[index] ? row[index].toString().trim() : '';
        }

        // Valider les données
        if (!data.nom || !data.prenom || !data.nip) {
            throw new Error('Données obligatoires manquantes');
        }

        // Valider le NIP
        if (!this.config.nipPattern.test(data.nip)) {
            throw new Error(`Format NIP invalide: ${data.nip}`);
        }

        // Vérifier les doublons
        if (this.isDuplicate(data.nip)) {
            throw new Error(`NIP déjà existant: ${data.nip}`);
        }

        return {
            id: Date.now() + Math.random(),
            civilite: data.civilite || 'M',
            nom: data.nom,
            prenom: data.prenom,
            nip: data.nip,
            telephone: data.telephone || '',
            profession: data.profession || '',
            dateAjout: new Date().toISOString(),
            source: 'import',
            ligne: lineNumber
        };
    },

    // ===== AFFICHAGE ET RENDU =====
    renderAdherentsList() {
        const container = document.getElementById('adherents_list');
        if (!container) return;

        if (this.state.adherents.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-user-plus fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted">Aucun adhérent ajouté</h5>
                    <p class="text-muted mb-4">
                        Commencez par importer un fichier Excel/CSV ou ajoutez des adhérents manuellement.
                    </p>
                </div>
            `;
            return;
        }

        const tableHtml = this.generateAdherentsTable();
        container.innerHTML = tableHtml;
        
        this.setupTableActions();
    },

    generateAdherentsTable() {
        const adherents = this.state.adherents;
        
        let rows = '';
        adherents.forEach((adherent, index) => {
            const age = this.calculateAge(adherent.nip);
            const sourceIcon = adherent.source === 'manuel' 
                ? '<i class="fas fa-keyboard text-primary" title="Saisie manuelle"></i>'
                : '<i class="fas fa-file-excel text-success" title="Import fichier"></i>';
            
            rows += `
                <tr data-id="${adherent.id}">
                    <td>
                        <input type="checkbox" class="form-check-input adherent-select" value="${adherent.id}">
                    </td>
                    <td>${index + 1}</td>
                    <td>${adherent.civilite}</td>
                    <td><strong>${adherent.nom}</strong></td>
                    <td>${adherent.prenom}</td>
                    <td><code>${adherent.nip}</code></td>
                    <td>${age ? age + ' ans' : '-'}</td>
                    <td>${adherent.telephone || '-'}</td>
                    <td>${adherent.profession || '-'}</td>
                    <td class="text-center">${sourceIcon}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm" onclick="AdherentsManager.editAdherent('${adherent.id}')" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="AdherentsManager.deleteAdherent('${adherent.id}')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        return `
            <div class="table-controls">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">
                                    Tout sélectionner
                                </label>
                            </div>
                            <div class="selected-actions d-none">
                                <button class="btn btn-outline-danger btn-sm" onclick="AdherentsManager.deleteSelected()">
                                    <i class="fas fa-trash me-1"></i>Supprimer sélectionnés
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-outline-success btn-sm" onclick="AdherentsManager.exportToCSV()">
                                <i class="fas fa-download me-1"></i>Export CSV
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="AdherentsManager.refreshList()">
                                <i class="fas fa-sync me-1"></i>Actualiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="30"></th>
                            <th width="50">#</th>
                            <th width="80">Civilité</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>NIP</th>
                            <th width="80">Âge</th>
                            <th>Téléphone</th>
                            <th>Profession</th>
                            <th width="80" class="text-center">Source</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            </div>
        `;
    },

    setupTableActions() {
        // Sélection multiple
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.adherent-select');
        
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                this.updateSelectedActions();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => this.updateSelectedActions());
        });
    },

    updateSelectedActions() {
        const selected = document.querySelectorAll('.adherent-select:checked');
        const actionsDiv = document.querySelector('.selected-actions');
        
        if (actionsDiv) {
            if (selected.length > 0) {
                actionsDiv.classList.remove('d-none');
            } else {
                actionsDiv.classList.add('d-none');
            }
        }
    },

    // ===== ACTIONS SUR LES ADHÉRENTS =====
    editAdherent(id) {
        const adherent = this.state.adherents.find(a => a.id == id);
        if (!adherent) return;

        // Pré-remplir le formulaire manuel
        document.getElementById('adherent_civilite').value = adherent.civilite;
        document.getElementById('adherent_nom').value = adherent.nom;
        document.getElementById('adherent_prenom').value = adherent.prenom;
        document.getElementById('adherent_nip').value = adherent.nip;
        document.getElementById('adherent_telephone').value = adherent.telephone;
        document.getElementById('adherent_profession').value = adherent.profession;

        // Supprimer l'ancien
        this.deleteAdherent(id, false);

        // Passer en mode manuel
        const modeManuel = document.getElementById('mode_manuel');
        if (modeManuel) {
            modeManuel.checked = true;
            this.switchMode('manuel');
        }

        // Scroll vers le formulaire
        const formSection = document.getElementById('adherent_manuel_section');
        if (formSection) {
            formSection.scrollIntoView({ behavior: 'smooth' });
        }

        this.showInfo('Adhérent chargé pour modification');
    },

    deleteAdherent(id, confirm = true) {
        if (confirm && !window.confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?')) {
            return;
        }

        const index = this.state.adherents.findIndex(a => a.id == id);
        if (index !== -1) {
            const adherent = this.state.adherents[index];
            this.state.adherents.splice(index, 1);
            this.updateStatistics();
            this.renderAdherentsList();
            this.markAsChanged();
            
            if (confirm) {
                this.showSuccess(`Adhérent ${adherent.prenom} ${adherent.nom} supprimé`);
            }
        }
    },

    deleteSelected() {
        const selected = document.querySelectorAll('.adherent-select:checked');
        if (selected.length === 0) return;

        if (!window.confirm(`Supprimer ${selected.length} adhérent(s) sélectionné(s) ?`)) {
            return;
        }

        const ids = Array.from(selected).map(cb => cb.value);
        ids.forEach(id => this.deleteAdherent(id, false));
        
        this.showSuccess(`${ids.length} adhérent(s) supprimé(s)`);
    },

    refreshList() {
        this.updateStatistics();
        this.renderAdherentsList();
        this.showInfo('Liste actualisée');
    },

    // ===== UTILITAIRES =====
    calculateAge(nip) {
        if (!nip || !this.config.nipPattern.test(nip)) return null;
        
        try {
            const datePart = nip.split('-')[2];
            const year = parseInt(datePart.substring(0, 4));
            const month = parseInt(datePart.substring(4, 6)) - 1;
            const day = parseInt(datePart.substring(6, 8));
            
            const birthDate = new Date(year, month, day);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            
            if (today.getMonth() < month || (today.getMonth() === month && today.getDate() < day)) {
                age--;
            }
            
            return age;
        } catch (error) {
            return null;
        }
    },

    updateStatistics() {
        const total = this.state.adherents.length;
        const duplicatesMap = new Map();
        
        // Détecter les doublons
        this.state.adherents.forEach(adherent => {
            if (duplicatesMap.has(adherent.nip)) {
                duplicatesMap.set(adherent.nip, duplicatesMap.get(adherent.nip) + 1);
            } else {
                duplicatesMap.set(adherent.nip, 1);
            }
        });
        
        const duplicates = Array.from(duplicatesMap.values()).filter(count => count > 1).length;
        
        this.state.statistics = {
            total,
            valid: total - duplicates,
            errors: 0, // À implémenter si nécessaire
            duplicates
        };

        this.updateUI();
    },

    updateUI() {
        // Mettre à jour le compteur
        const countElement = document.getElementById('adherents_count');
        if (countElement) {
            countElement.textContent = `${this.state.statistics.total} adhérent(s)`;
        }

        // Mettre à jour les exigences
        this.updateRequirements();
    },

    updateRequirements() {
        const requirementsDiv = document.getElementById('adherents_requirements');
        if (!requirementsDiv) return;

        // Déterminer le minimum requis selon le type d'organisation
        let minRequired = 10; // Valeur par défaut
        
        // Cette logique peut être étendue selon le contexte
        const organisationType = this.getOrganisationType();
        switch (organisationType) {
            case 'association':
                minRequired = 10;
                break;
            case 'ong':
                minRequired = 15;
                break;
            case 'parti_politique':
                minRequired = 50;
                break;
            case 'confession_religieuse':
                minRequired = 10;
                break;
        }

        const current = this.state.statistics.total;
        const isValid = current >= minRequired;
        const statusClass = isValid ? 'text-success' : 'text-warning';
        const statusIcon = isValid ? 'fa-check-circle' : 'fa-exclamation-triangle';

        requirementsDiv.innerHTML = `
            <p class="mb-0">
                <strong>Minimum requis :</strong> <span id="min_adherents" class="${statusClass}">${minRequired}</span> adhérents
                <br>
                <span class="${statusClass}">
                    <i class="fas ${statusIcon} me-1"></i>
                    Actuellement : ${current} adhérent(s) ${isValid ? '✓' : '(insuffisant)'}
                </span>
            </p>
        `;
    },

    getOrganisationType() {
        // Récupérer le type depuis la page ou les méta-données
        const typeInput = document.querySelector('input[name="type_organisation"]:checked');
        return typeInput ? typeInput.value : 'association';
    },

    // ===== SAUVEGARDE ET PERSISTANCE =====
    markAsChanged() {
        this.state.hasUnsavedChanges = true;
        
        const indicator = document.getElementById('save-indicator');
        if (indicator) {
            indicator.innerHTML = '<i class="fas fa-circle text-warning"></i> Modifications non sauvegardées';
        }
    },

    markAsSaved() {
        this.state.hasUnsavedChanges = false;
        this.state.lastSaveTime = new Date();
        
        const indicator = document.getElementById('save-indicator');
        if (indicator) {
            const time = this.state.lastSaveTime.toLocaleTimeString();
            indicator.innerHTML = `<i class="fas fa-check-circle text-success"></i> Sauvegardé à ${time}`;
        }
    },

    async saveProgress() {
        if (this.state.adherents.length === 0) {
            this.showWarning('Aucun adhérent à sauvegarder');
            return;
        }

        this.showLoading('Sauvegarde en cours...');

        try {
            const response = await fetch(window.AdherentsConfig.routes.save, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({
                    adherents: this.state.adherents,
                    statistics: this.state.statistics,
                    organisation_id: document.querySelector('input[name="organisation_id"]')?.value,
                    dossier_id: document.querySelector('input[name="dossier_id"]')?.value
                })
            });

            const result = await response.json();

            if (result.success) {
                this.markAsSaved();
                this.showSuccess('Adhérents sauvegardés avec succès');
            } else {
                throw new Error(result.message || 'Erreur de sauvegarde');
            }
        } catch (error) {
            console.error('❌ Erreur sauvegarde:', error);
            this.showError('Erreur lors de la sauvegarde: ' + error.message);
        } finally {
            this.hideLoading();
        }
    },

    async finalizeAdherents() {
        if (this.state.adherents.length === 0) {
            this.showError('Aucun adhérent à finaliser');
            return;
        }

        // Vérifier les exigences minimales
        if (!this.checkMinimumRequirements()) {
            return;
        }

        if (!window.confirm('Finaliser la liste des adhérents ? Cette action est définitive.')) {
            return;
        }

        this.showLoading('Finalisation en cours...');

        try {
            // Sauvegarder d'abord
            await this.saveProgress();

            // Rediriger ou continuer le processus
            this.showSuccess('Adhérents finalisés avec succès');
            
            // Redirection possible
            setTimeout(() => {
                const organisationId = document.querySelector('input[name="organisation_id"]')?.value;
                if (organisationId) {
                    window.location.href = `/operator/organisations/${organisationId}`;
                }
            }, 2000);

        } catch (error) {
            console.error('❌ Erreur finalisation:', error);
            this.showError('Erreur lors de la finalisation: ' + error.message);
        } finally {
            this.hideLoading();
        }
    },

    checkMinimumRequirements() {
        const minRequired = parseInt(document.getElementById('min_adherents')?.textContent) || 10;
        const current = this.state.statistics.total;

        if (current < minRequired) {
            this.showError(`Nombre d'adhérents insuffisant. Minimum requis: ${minRequired}, actuel: ${current}`);
            return false;
        }

        return true;
    },

    setupAutoSave() {
        setInterval(() => {
            if (this.state.hasUnsavedChanges && this.state.adherents.length > 0) {
                console.log('💾 Sauvegarde automatique...');
                this.saveProgress();
            }
        }, this.config.autoSaveInterval);
    },

    loadExistingData() {
        // Charger les données existantes si disponibles
        // Cette fonction peut être étendue selon les besoins
        console.log('📊 Chargement des données existantes...');
    },

    // ===== EXPORT =====
    exportToCSV() {
        if (this.state.adherents.length === 0) {
            this.showWarning('Aucun adhérent à exporter');
            return;
        }

        const headers = ['Civilité', 'Nom', 'Prénom', 'NIP', 'Téléphone', 'Profession'];
        const rows = this.state.adherents.map(adherent => [
            adherent.civilite,
            adherent.nom,
            adherent.prenom,
            adherent.nip,
            adherent.telephone,
            adherent.profession
        ]);

        const csvContent = [headers, ...rows]
            .map(row => row.map(cell => `"${cell}"`).join(','))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `adherents-${new Date().toISOString().split('T')[0]}.csv`;
        link.click();

        this.showSuccess('Export CSV téléchargé');
    },

    downloadTemplate() {
        // Créer un template Excel
        const headers = ['Civilité', 'Nom', 'Prénom', 'NIP', 'Téléphone', 'Profession'];
        const examples = [
            ['M', 'OBAME', 'Jean-Pierre', 'A1-2345-19901225', '01234567', 'Ingénieur'],
            ['Mme', 'MBANI', 'Marie', 'B2-0001-19850630', '07654321', 'Enseignante']
        ];

        const wsData = [headers, ...examples];
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Adherents');

        XLSX.writeFile(wb, 'template-adherents.xlsx');
        this.showSuccess('Template Excel téléchargé');
    },

    // ===== NOTIFICATIONS =====
    showSuccess(message) {
        this.showNotification(message, 'success');
    },

    showError(message) {
        this.showNotification(message, 'error');
    },

    showWarning(message) {
        this.showNotification(message, 'warning');
    },

    showInfo(message) {
        this.showNotification(message, 'info');
    },

    showNotification(message, type = 'info') {
        // Utiliser Toastr ou une autre bibliothèque de notifications
        // Pour l'instant, utiliser des alertes Bootstrap
        const alertClass = {
            success: 'alert-success',
            error: 'alert-danger',
            warning: 'alert-warning',
            info: 'alert-info'
        }[type] || 'alert-info';

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alertDiv);

        // Auto-remove après 5 secondes
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    },

    showLoading(message = 'Chargement...') {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.querySelector('.visually-hidden').textContent = message;
            loader.classList.remove('d-none');
        }
        this.state.isLoading = true;
    },

    hideLoading() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.classList.add('d-none');
        }
        this.state.isLoading = false;
    }
};

// ===== INITIALISATION AUTOMATIQUE =====
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.AdherentsManager !== 'undefined') {
        window.AdherentsManager.init();
    }
});

console.log('📋 AdherentsManager v2.0 chargé');