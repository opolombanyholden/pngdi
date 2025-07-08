{{--
============================================================================
ADHERENT-FORM-UPLOAD.BLADE.PHP - FORMULAIRE UPLOAD FICHIER
Partial pour confirmation.blade.php - Section import fichier
Version: 2.0 - Upload drag&drop avec validation avancée
============================================================================
--}}

<div class="upload-form">
    <div class="form-header mb-4">
        <h5 class="text-success">
            <i class="fas fa-file-excel me-2"></i>
            Import par Fichier Excel/CSV
        </h5>
        <p class="text-muted mb-0">
            Importez vos adhérents en masse via un fichier Excel (.xlsx) ou CSV.
            Traitement automatique avec détection des anomalies.
        </p>
    </div>

    <!-- Zone de téléchargement template -->
    <div class="template-download mb-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-download me-2"></i>Modèle de fichier
                </h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <p class="mb-2">
                            <strong>Téléchargez le modèle officiel</strong> pour garantir un import réussi.
                        </p>
                        <p class="text-muted mb-0">
                            Contient les colonnes requises et des exemples d'adhérents valides.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ $urls['template_download'] ?? '#' }}" class="btn btn-info" id="download-template-btn">
                            <i class="fas fa-file-excel me-2"></i>
                            Télécharger Modèle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone de drop principale -->
    <div class="upload-zone" id="file-upload-zone">
        <div class="upload-content">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <div class="upload-title">
                Glissez-déposez votre fichier ici
            </div>
            <div class="upload-subtitle">
                ou cliquez pour sélectionner un fichier
            </div>
            <div class="upload-formats">
                <span class="badge badge-gabon-secondary me-2">
                    <i class="fas fa-file-excel me-1"></i>Excel (.xlsx)
                </span>
                <span class="badge badge-gabon-secondary">
                    <i class="fas fa-file-csv me-1"></i>CSV (.csv)
                </span>
            </div>
            <div class="upload-limits mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Taille max: {{ $upload_config['max_file_size'] ?? '10MB' }} | 
                    Adhérents max: {{ number_format($upload_config['max_adherents'] ?? 50000) }}
                </small>
            </div>
        </div>
        
        <!-- Input file caché -->
        <input type="file" id="file-input" accept=".xlsx,.xls,.csv" style="display: none;">
    </div>

    <!-- Zone d'information du fichier sélectionné -->
    <div class="file-info" id="file-info" style="display: none;">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-file-check me-2"></i>Fichier sélectionné
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="clearSelectedFile()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="file-details">
                            <h6 class="file-name text-primary mb-1" id="file-name">-</h6>
                            <div class="file-meta">
                                <span class="me-3">
                                    <i class="fas fa-weight me-1"></i>
                                    <span id="file-size">-</span>
                                </span>
                                <span class="me-3">
                                    <i class="fas fa-calendar me-1"></i>
                                    <span id="file-date">-</span>
                                </span>
                                <span class="badge badge-gabon-primary" id="file-type">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-gabon-primary" onclick="processSelectedFile()" id="process-file-btn">
                            <i class="fas fa-cogs me-2"></i>
                            Traiter le Fichier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone de progression -->
    <div class="upload-progress" id="upload-progress" style="display: none;">
        <div class="card border-warning">
            <div class="card-header bg-warning">
                <h6 class="mb-0 text-dark">
                    <i class="fas fa-spinner fa-spin me-2"></i>Traitement en cours...
                </h6>
            </div>
            <div class="card-body">
                <!-- Barre de progression principale -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="progress-label" id="progress-label">Initialisation...</span>
                        <span class="progress-percentage" id="progress-percentage">0%</span>
                    </div>
                    <div class="progress progress-gabon">
                        <div class="progress-bar" id="progress-bar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Statistiques temps réel -->
                <div class="progress-stats">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value text-primary" id="progress-total">-</div>
                                <div class="stat-label">Total</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value text-success" id="progress-processed">0</div>
                                <div class="stat-label">Traités</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value text-warning" id="progress-warnings">0</div>
                                <div class="stat-label">Avertissements</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value text-danger" id="progress-errors">0</div>
                                <div class="stat-label">Erreurs</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contrôles de traitement -->
                <div class="progress-controls text-center mt-3" id="progress-controls">
                    <button type="button" class="btn btn-warning me-2" onclick="pauseProcessing()" id="pause-btn">
                        <i class="fas fa-pause me-1"></i>Pause
                    </button>
                    <button type="button" class="btn btn-danger" onclick="cancelProcessing()" id="cancel-btn">
                        <i class="fas fa-stop me-1"></i>Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone de résultats -->
    <div class="upload-results" id="upload-results" style="display: none;">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="fas fa-check-circle me-2"></i>Import terminé
                </h6>
            </div>
            <div class="card-body">
                <!-- Résumé des résultats -->
                <div class="results-summary mb-4">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="result-stat success">
                                <div class="result-number" id="result-success">0</div>
                                <div class="result-label">Importés</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="result-stat warning">
                                <div class="result-number" id="result-warnings">0</div>
                                <div class="result-label">Avertissements</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="result-stat danger">
                                <div class="result-number" id="result-errors">0</div>
                                <div class="result-label">Erreurs</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="result-stat info">
                                <div class="result-number" id="result-duration">0s</div>
                                <div class="result-label">Durée</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions post-import -->
                <div class="results-actions text-center">
                    <div class="row justify-content-center">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="downloadReport()" id="download-report-btn">
                                <i class="fas fa-file-pdf me-2"></i>
                                Rapport Détaillé
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-warning w-100" onclick="viewAnomalies()" id="view-anomalies-btn">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Voir Anomalies
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-info w-100" onclick="importAnotherFile()" id="import-another-btn">
                                <i class="fas fa-plus me-2"></i>
                                Autre Fichier
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-gabon-primary w-100" onclick="proceedToValidation()" id="proceed-btn">
                                <i class="fas fa-arrow-right me-2"></i>
                                Continuer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone d'erreurs critiques -->
    <div class="upload-errors" id="upload-errors" style="display: none;">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>Erreurs détectées
                </h6>
            </div>
            <div class="card-body">
                <div class="error-content" id="error-content">
                    <!-- Contenu d'erreur sera ajouté dynamiquement -->
                </div>
                <div class="error-actions text-center mt-3">
                    <button type="button" class="btn btn-outline-danger me-2" onclick="retryUpload()">
                        <i class="fas fa-redo me-1"></i>Réessayer
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearUploadForm()">
                        <i class="fas fa-trash me-1"></i>Tout Effacer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Guide d'utilisation -->
    <div class="usage-guide mt-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-question-circle me-2"></i>Guide d'import
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">
                            <i class="fas fa-check-circle me-1"></i>Colonnes requises
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right me-2 text-success"></i><strong>Civilité:</strong> M, Mme, Mlle</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i><strong>Nom:</strong> Nom de famille (obligatoire)</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i><strong>Prénom:</strong> Prénom(s) (obligatoire)</li>
                            <li><i class="fas fa-arrow-right me-2 text-success"></i><strong>NIP:</strong> Format XX-QQQQ-YYYYMMDD</li>
                            <li><i class="fas fa-arrow-right me-2 text-info"></i><strong>Téléphone:</strong> Optionnel</li>
                            <li><i class="fas fa-arrow-right me-2 text-info"></i><strong>Profession:</strong> Optionnel</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">
                            <i class="fas fa-lightbulb me-1"></i>Conseils d'optimisation
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Utilisez le modèle fourni</li>
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Vérifiez les formats NIP</li>
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Supprimez les lignes vides</li>
                            <li><i class="fas fa-arrow-right me-2 text-primary"></i>Évitez les caractères spéciaux</li>
                            <li><i class="fas fa-arrow-right me-2 text-warning"></i>Chunking automatique si > {{ $upload_config['chunking_threshold'] ?? 200 }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript spécifique à l'upload --}}
<script>
// Variables globales pour l'upload
let selectedFile = null;
let uploadState = {
    isProcessing: false,
    isPaused: false,
    currentProgress: 0,
    totalRecords: 0,
    processedRecords: 0,
    startTime: null,
    results: null
};

document.addEventListener('DOMContentLoaded', function() {
    initializeFileUpload();
});

/**
 * Initialiser l'interface d'upload
 */
function initializeFileUpload() {
    console.log('🔧 Initialisation interface upload fichier');
    
    setupDropZone();
    setupFileInput();
    
    console.log('✅ Interface upload initialisée');
}

/**
 * Configurer la zone de drop
 */
function setupDropZone() {
    const dropZone = document.getElementById('file-upload-zone');
    const fileInput = document.getElementById('file-input');
    
    if (!dropZone || !fileInput) return;
    
    // Clic sur la zone
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });
    
    // Drag & Drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelection(files[0]);
        }
    });
}

/**
 * Configurer l'input file
 */
function setupFileInput() {
    const fileInput = document.getElementById('file-input');
    
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelection(e.target.files[0]);
            }
        });
    }
}

/**
 * Gérer la sélection d'un fichier
 */
function handleFileSelection(file) {
    console.log('📁 Fichier sélectionné:', file.name);
    
    // Validation du fichier
    if (!validateFile(file)) {
        return;
    }
    
    selectedFile = file;
    displayFileInfo(file);
    
    // Masquer la zone de drop et afficher les infos du fichier
    document.getElementById('file-upload-zone').style.display = 'none';
    document.getElementById('file-info').style.display = 'block';
}

/**
 * Valider le fichier sélectionné
 */
function validateFile(file) {
    const maxSize = parseInt('{{ $upload_config["max_file_size_bytes"] ?? 10485760 }}'); // 10MB par défaut
    const allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel', // .xls
        'text/csv' // .csv
    ];
    
    // Vérifier le type
    if (!allowedTypes.includes(file.type)) {
        showUploadError('Type de fichier non supporté. Utilisez Excel (.xlsx) ou CSV (.csv)');
        return false;
    }
    
    // Vérifier la taille
    if (file.size > maxSize) {
        showUploadError(`Fichier trop volumineux. Taille maximum: {{ $upload_config["max_file_size"] ?? "10MB" }}`);
        return false;
    }
    
    return true;
}

/**
 * Afficher les informations du fichier
 */
function displayFileInfo(file) {
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const fileDate = document.getElementById('file-date');
    const fileType = document.getElementById('file-type');
    
    if (fileName) fileName.textContent = file.name;
    if (fileSize) fileSize.textContent = formatFileSize(file.size);
    if (fileDate) fileDate.textContent = new Date(file.lastModified).toLocaleDateString('fr-FR');
    
    if (fileType) {
        let typeLabel = 'Inconnu';
        if (file.type.includes('excel') || file.name.endsWith('.xlsx')) {
            typeLabel = 'Excel';
        } else if (file.type.includes('csv')) {
            typeLabel = 'CSV';
        }
        fileType.textContent = typeLabel;
    }
}

/**
 * Traiter le fichier sélectionné
 */
async function processSelectedFile() {
    if (!selectedFile) {
        showUploadError('Aucun fichier sélectionné');
        return;
    }
    
    console.log('🚀 Début traitement fichier:', selectedFile.name);
    
    // Afficher la zone de progression
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('upload-progress').style.display = 'block';
    
    uploadState.isProcessing = true;
    uploadState.startTime = Date.now();
    
    try {
        // Lire et analyser le fichier
        updateProgress(10, 'Lecture du fichier...');
        const fileData = await readFile(selectedFile);
        
        updateProgress(25, 'Analyse des données...');
        const adherentsData = await parseFileData(fileData, selectedFile.type);
        
        updateProgress(40, 'Validation des adhérents...');
        const validationResults = await validateAdherentsData(adherentsData);
        
        // Décider si utiliser le chunking
        const useChunking = adherentsData.length >= (window.ConfirmationConfig?.chunking?.threshold || 200);
        
        if (useChunking) {
            updateProgress(50, 'Préparation traitement par lots...');
            await processWithChunking(adherentsData, validationResults);
        } else {
            updateProgress(50, 'Traitement standard...');
            await processStandard(adherentsData, validationResults);
        }
        
        updateProgress(100, 'Import terminé !');
        showUploadResults();
        
    } catch (error) {
        console.error('❌ Erreur traitement fichier:', error);
        showUploadError(error.message);
    }
}

/**
 * Lire le fichier
 */
function readFile(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            resolve(e.target.result);
        };
        
        reader.onerror = () => {
            reject(new Error('Erreur de lecture du fichier'));
        };
        
        if (file.type.includes('csv')) {
            reader.readAsText(file);
        } else {
            reader.readAsArrayBuffer(file);
        }
    });
}

/**
 * Parser les données du fichier
 */
async function parseFileData(fileData, fileType) {
    if (fileType.includes('csv')) {
        return parseCSV(fileData);
    } else {
        return parseExcel(fileData);
    }
}

/**
 * Parser CSV
 */
function parseCSV(csvData) {
    // Utiliser la bibliothèque Papa Parse si disponible
    if (typeof Papa !== 'undefined') {
        const results = Papa.parse(csvData, {
            header: true,
            skipEmptyLines: true,
            dynamicTyping: true
        });
        
        return results.data.map(mapRowToAdherent);
    } else {
        throw new Error('Bibliothèque CSV non disponible');
    }
}

/**
 * Parser Excel
 */
function parseExcel(excelData) {
    // Utiliser la bibliothèque XLSX si disponible
    if (typeof XLSX !== 'undefined') {
        const workbook = XLSX.read(excelData, { type: 'array' });
        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];
        
        const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
        
        if (jsonData.length < 2) {
            throw new Error('Le fichier Excel doit contenir au moins une ligne d\'en-têtes et une ligne de données');
        }
        
        const headers = jsonData[0];
        const rows = jsonData.slice(1);
        
        return rows.map(row => {
            const obj = {};
            headers.forEach((header, index) => {
                obj[header] = row[index];
            });
            return mapRowToAdherent(obj);
        }).filter(adherent => adherent.nom || adherent.prenom); // Filtrer les lignes vides
        
    } else {
        throw new Error('Bibliothèque Excel non disponible');
    }
}

/**
 * Mapper une ligne vers un objet adhérent
 */
function mapRowToAdherent(row) {
    return {
        civilite: row.civilite || row.Civilite || row.CIVILITE || 'M',
        nom: (row.nom || row.Nom || row.NOM || '').toString().trim().toUpperCase(),
        prenom: (row.prenom || row.Prenom || row.PRENOM || '').toString().trim(),
        nip: (row.nip || row.NIP || row.Nip || '').toString().trim().toUpperCase(),
        telephone: (row.telephone || row.Telephone || row.TELEPHONE || '').toString().trim(),
        profession: (row.profession || row.Profession || row.PROFESSION || '').toString().trim(),
        source: 'fichier'
    };
}

/**
 * Valider les données d'adhérents
 */
async function validateAdherentsData(adherentsData) {
    const results = {
        valid: [],
        warnings: [],
        errors: [],
        total: adherentsData.length
    };
    
    adherentsData.forEach((adherent, index) => {
        const validation = validateSingleAdherent(adherent, index + 1);
        
        if (validation.isValid) {
            results.valid.push(adherent);
        }
        
        if (validation.warnings.length > 0) {
            results.warnings.push(...validation.warnings);
        }
        
        if (validation.errors.length > 0) {
            results.errors.push(...validation.errors);
        }
    });
    
    return results;
}

/**
 * Valider un adhérent
 */
function validateSingleAdherent(adherent, lineNumber) {
    const validation = {
        isValid: true,
        warnings: [],
        errors: []
    };
    
    // Validation obligatoire
    if (!adherent.nom) {
        validation.errors.push(`Ligne ${lineNumber}: Nom obligatoire`);
        validation.isValid = false;
    }
    
    if (!adherent.prenom) {
        validation.errors.push(`Ligne ${lineNumber}: Prénom obligatoire`);
        validation.isValid = false;
    }
    
    if (!adherent.nip) {
        validation.errors.push(`Ligne ${lineNumber}: NIP obligatoire`);
        validation.isValid = false;
    } else {
        // Validation format NIP
        const nipPattern = /^[A-Z]{2}-[0-9]{4}-[0-9]{8}$/;
        if (!nipPattern.test(adherent.nip)) {
            validation.errors.push(`Ligne ${lineNumber}: Format NIP invalide (${adherent.nip})`);
            validation.isValid = false;
        } else {
            // Vérifier l'âge
            const age = extractAgeFromNIP(adherent.nip);
            if (age < 18) {
                validation.errors.push(`Ligne ${lineNumber}: Adhérent mineur (${age} ans)`);
                validation.isValid = false;
            }
        }
    }
    
    // Avertissements
    if (!adherent.telephone) {
        validation.warnings.push(`Ligne ${lineNumber}: Téléphone manquant`);
    }
    
    if (!adherent.profession) {
        validation.warnings.push(`Ligne ${lineNumber}: Profession non spécifiée`);
    }
    
    return validation;
}

/**
 * Traitement avec chunking
 */
async function processWithChunking(adherentsData, validationResults) {
    console.log('🚀 Traitement avec chunking pour', adherentsData.length, 'adhérents');
    
    if (window.ChunkingEngine && window.ChunkingEngine.processImportWithChunking) {
        return await window.ChunkingEngine.processImportWithChunking(
            validationResults.valid,
            {
                onProgress: (progress) => {
                    updateProgress(50 + (progress * 0.45), `Traitement lot ${progress.currentChunk}/${progress.totalChunks}`);
                    updateStats(progress);
                },
                onComplete: (results) => {
                    uploadState.results = results;
                }
            }
        );
    } else {
        // Fallback vers traitement standard
        return await processStandard(adherentsData, validationResults);
    }
}

/**
 * Traitement standard
 */
async function processStandard(adherentsData, validationResults) {
    console.log('📝 Traitement standard pour', validationResults.valid.length, 'adhérents valides');
    
    const batchSize = 50;
    const validAdherents = validationResults.valid;
    let processed = 0;
    
    for (let i = 0; i < validAdherents.length; i += batchSize) {
        const batch = validAdherents.slice(i, i + batchSize);
        
        // Traitement du lot
        for (const adherent of batch) {
            if (window.ConfirmationApp && window.ConfirmationApp.addAdherent) {
                window.ConfirmationApp.addAdherent(adherent);
            }
            processed++;
            
            const progress = 50 + ((processed / validAdherents.length) * 45);
            updateProgress(progress, `Traitement: ${processed}/${validAdherents.length}`);
            updateStats({
                processed: processed,
                total: validAdherents.length,
                warnings: validationResults.warnings.length,
                errors: validationResults.errors.length
            });
        }
        
        // Petite pause entre les lots
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    
    uploadState.results = {
        success: processed,
        warnings: validationResults.warnings.length,
        errors: validationResults.errors.length,
        total: validAdherents.length
    };
}

/**
 * Mettre à jour la progression
 */
function updateProgress(percentage, label) {
    const progressBar = document.getElementById('progress-bar');
    const progressPercentage = document.getElementById('progress-percentage');
    const progressLabel = document.getElementById('progress-label');
    
    if (progressBar) progressBar.style.width = percentage + '%';
    if (progressPercentage) progressPercentage.textContent = Math.round(percentage) + '%';
    if (progressLabel) progressLabel.textContent = label;
    
    uploadState.currentProgress = percentage;
}

/**
 * Mettre à jour les statistiques
 */
function updateStats(stats) {
    const totalEl = document.getElementById('progress-total');
    const processedEl = document.getElementById('progress-processed');
    const warningsEl = document.getElementById('progress-warnings');
    const errorsEl = document.getElementById('progress-errors');
    
    if (totalEl) totalEl.textContent = stats.total || 0;
    if (processedEl) processedEl.textContent = stats.processed || 0;
    if (warningsEl) warningsEl.textContent = stats.warnings || 0;
    if (errorsEl) errorsEl.textContent = stats.errors || 0;
}

/**
 * Afficher les résultats
 */
function showUploadResults() {
    const progressDiv = document.getElementById('upload-progress');
    const resultsDiv = document.getElementById('upload-results');
    
    if (progressDiv) progressDiv.style.display = 'none';
    if (resultsDiv) resultsDiv.style.display = 'block';
    
    // Mettre à jour les statistiques de résultat
    const duration = Math.round((Date.now() - uploadState.startTime) / 1000);
    
    updateResultStat('result-success', uploadState.results?.success || 0);
    updateResultStat('result-warnings', uploadState.results?.warnings || 0);
    updateResultStat('result-errors', uploadState.results?.errors || 0);
    updateResultStat('result-duration', duration + 's');
    
    uploadState.isProcessing = false;
}

/**
 * Mettre à jour une statistique de résultat
 */
function updateResultStat(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

/**
 * Afficher une erreur d'upload
 */
function showUploadError(message) {
    const errorDiv = document.getElementById('upload-errors');
    const errorContent = document.getElementById('error-content');
    
    if (errorContent) {
        errorContent.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }
    
    if (errorDiv) {
        errorDiv.style.display = 'block';
    }
    
    // Masquer la progression si elle était affichée
    const progressDiv = document.getElementById('upload-progress');
    if (progressDiv) {
        progressDiv.style.display = 'none';
    }
    
    uploadState.isProcessing = false;
}

/**
 * Fonctions utilitaires
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function extractAgeFromNIP(nip) {
    if (!nip || nip.length < 13) return 0;
    
    try {
        const datePart = nip.substring(7, 15); // YYYYMMDD
        const year = parseInt(datePart.substring(0, 4));
        const month = parseInt(datePart.substring(4, 6)) - 1;
        const day = parseInt(datePart.substring(6, 8));
        
        const birthDate = new Date(year, month, day);
        const today = new Date();
        
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    } catch (error) {
        return 0;
    }
}

/**
 * Actions de l'interface
 */
function clearSelectedFile() {
    selectedFile = null;
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('file-upload-zone').style.display = 'block';
    document.getElementById('file-input').value = '';
}

function pauseProcessing() {
    uploadState.isPaused = !uploadState.isPaused;
    const pauseBtn = document.getElementById('pause-btn');
    
    if (pauseBtn) {
        if (uploadState.isPaused) {
            pauseBtn.innerHTML = '<i class="fas fa-play me-1"></i>Reprendre';
            pauseBtn.classList.remove('btn-warning');
            pauseBtn.classList.add('btn-success');
        } else {
            pauseBtn.innerHTML = '<i class="fas fa-pause me-1"></i>Pause';
            pauseBtn.classList.remove('btn-success');
            pauseBtn.classList.add('btn-warning');
        }
    }
}

function cancelProcessing() {
    if (confirm('Êtes-vous sûr de vouloir annuler l\'import ?')) {
        uploadState.isProcessing = false;
        uploadState.isPaused = false;
        clearUploadForm();
    }
}

function downloadReport() {
    // Générer et télécharger le rapport d'import
    console.log('📄 Téléchargement du rapport');
    
    if (window.ConfirmationApp && window.ConfirmationApp.showInfo) {
        window.ConfirmationApp.showInfo('Fonctionnalité de rapport en développement');
    }
}

function viewAnomalies() {
    // Afficher les anomalies détectées
    console.log('⚠️ Affichage des anomalies');
    
    if (window.ConfirmationApp && window.ConfirmationApp.showInfo) {
        window.ConfirmationApp.showInfo('Consultation des anomalies en développement');
    }
}

function importAnotherFile() {
    // Permettre l'import d'un autre fichier
    clearUploadForm();
}

function proceedToValidation() {
    // Passer à l'étape de validation
    if (window.ConfirmationApp && window.ConfirmationApp.updateStatistics) {
        window.ConfirmationApp.updateStatistics();
    }
    
    if (window.ConfirmationApp && window.ConfirmationApp.showSuccess) {
        window.ConfirmationApp.showSuccess('Import terminé. Vous pouvez maintenant valider vos données.');
    }
}

function retryUpload() {
    // Réessayer l'upload
    document.getElementById('upload-errors').style.display = 'none';
    
    if (selectedFile) {
        processSelectedFile();
    } else {
        clearUploadForm();
    }
}

function clearUploadForm() {
    // Réinitialiser complètement l'interface
    selectedFile = null;
    uploadState = {
        isProcessing: false,
        isPaused: false,
        currentProgress: 0,
        totalRecords: 0,
        processedRecords: 0,
        startTime: null,
        results: null
    };
    
    // Masquer tous les éléments
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('upload-progress').style.display = 'none';
    document.getElementById('upload-results').style.display = 'none';
    document.getElementById('upload-errors').style.display = 'none';
    
    // Réafficher la zone de drop
    document.getElementById('file-upload-zone').style.display = 'block';
    document.getElementById('file-input').value = '';
}
</script>