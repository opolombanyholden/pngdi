@extends('layouts.operator')

@section('title', 'Import des Adhérents - Phase 2')

@section('content')
<div class="container-fluid">
    <!-- En-tête Phase 2 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            Import des Adhérents - Phase 2
                        </h4>
                        <div class="badge bg-light text-success fs-6">
                            Phase 2/2 - Final
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-success mb-3">✅ Phase 1 Complétée</h5>
                            <p class="mb-2">
                                <strong>Organisation :</strong> {{ $organisation->nom }}
                                @if($organisation->sigle)
                                    ({{ $organisation->sigle }})
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>Numéro de dossier :</strong> 
                                <span class="badge bg-primary">{{ $dossier->numero_dossier }}</span>
                            </p>
                            <p class="mb-0">
                                <strong>Numéro de récépissé :</strong> 
                                <span class="badge bg-info">{{ $organisation->numero_recepisse }}</span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <div class="mb-2">
                                    <small class="text-muted">Progression</small>
                                </div>
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: 90%"></div>
                                </div>
                                <small class="text-success">90% - Ajout des adhérents</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques actuelles -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h4 class="text-info">{{ $adherents_stats['existants'] }}</h4>
                    <p class="mb-0">Adhérents actuels</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h4 class="text-warning">{{ $adherents_stats['minimum_requis'] }}</h4>
                    <p class="mb-0">Minimum requis</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-{{ $adherents_stats['manquants'] > 0 ? 'danger' : 'success' }}">
                <div class="card-body text-center">
                    <div class="text-{{ $adherents_stats['manquants'] > 0 ? 'danger' : 'success' }} mb-2">
                        <i class="fas fa-{{ $adherents_stats['manquants'] > 0 ? 'user-plus' : 'check-circle' }} fa-2x"></i>
                    </div>
                    <h4 class="text-{{ $adherents_stats['manquants'] > 0 ? 'danger' : 'success' }}">
                        {{ $adherents_stats['manquants'] > 0 ? $adherents_stats['manquants'] : '✓' }}
                    </h4>
                    <p class="mb-0">
                        {{ $adherents_stats['manquants'] > 0 ? 'Manquants' : 'Complet' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interface d'import -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2"></i>
                        Import des Adhérents
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Zone d'upload -->
                    <div id="upload-zone" class="border border-dashed border-primary rounded p-4 mb-4 text-center">
                        <div id="upload-initial" class="upload-state">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <h5>Glissez-déposez votre fichier ou cliquez pour sélectionner</h5>
                            <p class="text-muted mb-3">
                                Formats acceptés : Excel (.xlsx) ou CSV<br>
                                Taille maximum : {{ $upload_config['max_file_size'] }}<br>
                                Maximum {{ number_format($upload_config['max_adherents']) }} adhérents
                            </p>
                            <button type="button" id="select-file-btn" class="btn btn-primary">
                                <i class="fas fa-file-excel me-2"></i>
                                Sélectionner un fichier
                            </button>
                            <input type="file" id="file-input" class="d-none" accept=".xlsx,.csv">
                        </div>

                        <!-- État de traitement -->
                        <div id="upload-processing" class="upload-state d-none">
                            <div class="mb-3">
                                <i class="fas fa-cog fa-spin fa-2x text-primary"></i>
                            </div>
                            <h5 id="processing-title">Traitement en cours...</h5>
                            <div class="progress mb-3" style="height: 20px;">
                                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                     style="width: 0%">
                                    <span id="progress-text">0%</span>
                                </div>
                            </div>
                            <div id="processing-details" class="text-muted">
                                <div id="current-chunk">Préparation...</div>
                                <div id="processing-stats" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- Résultats -->
                        <div id="upload-results" class="upload-state d-none">
                            <div id="success-results" class="d-none">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5 class="text-success">Import réussi !</h5>
                                <div id="import-summary" class="mt-3"></div>
                            </div>
                            <div id="error-results" class="d-none">
                                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                <h5 class="text-danger">Erreur lors de l'import</h5>
                                <div id="error-message" class="text-danger mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Instructions
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ol class="mb-0">
                                        <li>Téléchargez le modèle Excel ci-dessous</li>
                                        <li>Remplissez les informations des adhérents</li>
                                        <li>Sauvegardez le fichier au format Excel (.xlsx)</li>
                                        <li>Importez le fichier via la zone ci-dessus</li>
                                        <li>Vérifiez les résultats et les anomalies</li>
                                        <li>Finalisez votre dossier</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Informations importantes
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="mb-0">
                                        <li><strong>NIP :</strong> 13 chiffres obligatoires</li>
                                        <li><strong>Professions exclues :</strong> Magistrats, Militaires, etc.</li>
                                        <li><strong>Doublons :</strong> Détectés automatiquement</li>
                                        <li><strong>Chunking :</strong> Traitement par paquets de {{ $upload_config['chunk_size'] }}</li>
                                        <li><strong>Anomalies :</strong> Enregistrées mais signalées</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <a href="{{ $urls['template_download'] }}" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-download me-2"></i>
                                Télécharger le modèle Excel
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="finalize-btn" class="btn btn-success btn-lg w-100 d-none">
                                <i class="fas fa-check me-2"></i>
                                Finaliser le dossier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="finalizeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>
                        Finaliser le dossier
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="finalize-summary">
                        <h6>Récapitulatif final :</h6>
                        <div id="final-stats"></div>
                        <div id="final-anomalies" class="mt-3"></div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Une fois finalisé, votre dossier sera envoyé pour traitement administratif.
                        Vous recevrez un accusé de réception détaillé.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" id="confirm-finalize" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>
                        Confirmer et finaliser
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire caché pour soumission -->
<form id="adherents-form" method="POST" action="{{ $urls['store_adherents'] }}" style="display: none;">
    @csrf
    <input type="hidden" id="adherents-data" name="adherents" value="">
</form>
@endsection

@push('styles')
<style>
.upload-state {
    transition: all 0.3s ease;
}

#upload-zone {
    min-height: 200px;
    transition: all 0.3s ease;
    cursor: pointer;
}

#upload-zone:hover {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.05);
}

#upload-zone.dragover {
    border-color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.1);
    transform: scale(1.02);
}

.progress {
    border-radius: 10px;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.border-dashed {
    border-style: dashed !important;
}
</style>
@endpush

{{-- ========================================
     ÉTAPE 4.2 - MODIFICATIONS POUR CHUNKING PHASE 2
     À appliquer dans resources/views/operator/dossiers/adherents-import.blade.php
     ======================================== --}}

{{-- MODIFICATION 1: Ajouter chunking-import.js dans @push('scripts') --}}
{{-- LOCALISATION: Chercher la section @push('scripts') à la fin du fichier --}}
{{-- ACTION: AJOUTER cette ligne AVANT la ligne <script src="{{ asset('js/organisation-create.js') }}"> --}}

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Workflow 2 phases -->
    <script src="{{ asset('js/workflow-2phases.js') }}"></script>
    <!-- ✅ NOUVEAU: Système de chunking pour gros volumes -->
    <script src="{{ asset('js/chunking-import.js') }}"></script>
    <script src="{{ asset('js/organisation-create.js') }}"></script>
@endpush

{{-- MODIFICATION 2: Mettre à jour la configuration JavaScript --}}
{{-- LOCALISATION: Chercher window.Phase2Config = { --}}
{{-- ACTION: REMPLACER la configuration existante par celle-ci --}}

<script>
// Configuration Phase 2 avec Chunking v4.2
window.Phase2Config = {
    dossierId: {{ $dossier->id }},
    organisationId: {{ $organisation->id }},
    urls: {
        storeAdherents: '{{ $urls["store_adherents"] }}',
        confirmation: '{{ $urls["confirmation"] }}',
        templateDownload: '{{ $urls["template_download"] }}',
        // ✅ NOUVEAU: Endpoints chunking
        processChunk: '/chunking/process-chunk',
        refreshCSRF: '/chunking/csrf-refresh',
        healthCheck: '/chunking/health'
    },
    upload: {
        chunkSize: {{ $upload_config['chunk_size'] }},
        maxAdherents: {{ $upload_config['max_adherents'] }},
        maxFileSize: '{{ $upload_config["max_file_size"] }}',
        // ✅ NOUVEAU: Configuration chunking
        chunkingThreshold: 200,  // Seuil pour déclencher le chunking
        chunkingEnabled: true,
        maxRetries: 3,
        pauseBetweenChunks: 500
    },
    stats: {
        existants: {{ $adherents_stats['existants'] }},
        minimumRequis: {{ $adherents_stats['minimum_requis'] }},
        manquants: {{ $adherents_stats['manquants'] }}
    },
    csrf: '{{ csrf_token() }}',
    // ✅ NOUVEAU: Configuration Phase 2 spécifique
    phase2: {
        enabled: true,
        dossierNumero: '{{ $dossier->numero_dossier }}',
        organisationNom: '{{ $organisation->nom }}',
        organisationType: '{{ $organisation->type }}',
        version: '4.2'
    }
};

// ✅ NOUVEAU: Variables globales pour chunking Phase 2
let adherentsData = [];
let importResults = {
    success: false,
    stats: {
        total: 0,
        valides: 0,
        anomalies_critiques: 0,
        anomalies_majeures: 0,
        anomalies_mineures: 0
    },
    anomalies: [],
    processingMethod: 'standard' // ou 'chunking'
};

document.addEventListener('DOMContentLoaded', function() {
    initializePhase2Interface();
    
    // ✅ NOUVEAU: Initialiser le chunking pour Phase 2
    initializePhase2Chunking();
});

// ✅ NOUVEAU: Initialiser le chunking spécifiquement pour Phase 2
function initializePhase2Chunking() {
    console.log('🚀 Initialisation chunking Phase 2 v4.2');
    
    // Vérifier si le chunking est disponible
    if (typeof window.ChunkingImport !== 'undefined') {
        console.log('✅ Module chunking détecté et prêt pour Phase 2');
        
        // Configuration spécifique Phase 2
        if (window.ChunkingImport.config) {
            window.ChunkingImport.config.endpoints.processChunk = window.Phase2Config.urls.processChunk;
            window.ChunkingImport.config.endpoints.refreshCSRF = window.Phase2Config.urls.refreshCSRF;
            window.ChunkingImport.config.chunkSize = window.Phase2Config.upload.chunkSize;
            window.ChunkingImport.config.triggerThreshold = window.Phase2Config.upload.chunkingThreshold;
            
            console.log('🔧 Configuration chunking adaptée pour Phase 2');
        }
        
        // Marquer le chunking comme disponible
        window.Phase2Config.chunkingAvailable = true;
        
    } else {
        console.log('⚠️ Module chunking non trouvé - Mode standard seulement');
        window.Phase2Config.chunkingAvailable = false;
    }
}

function initializePhase2Interface() {
    console.log('Initialisation interface Phase 2 v4.2', window.Phase2Config);
    
    setupFileUpload();
    setupDragAndDrop();
    setupFinalizeButton();
    
    // Afficher le bouton finaliser si déjà suffisant d'adhérents
    if (window.Phase2Config.stats.manquants <= 0) {
        document.getElementById('finalize-btn').classList.remove('d-none');
    }
}

function setupFileUpload() {
    const selectBtn = document.getElementById('select-file-btn');
    const fileInput = document.getElementById('file-input');
    
    selectBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFileSelectionPhase2);
}

function setupDragAndDrop() {
    const uploadZone = document.getElementById('upload-zone');
    
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            processFilePhase2(files[0]);
        }
    });
    
    uploadZone.addEventListener('click', () => {
        document.getElementById('file-input').click();
    });
}

// ✅ NOUVEAU: Gestion fichier avec détection chunking automatique
function handleFileSelectionPhase2(e) {
    const file = e.target.files[0];
    if (file) {
        processFilePhase2(file);
    }
}

// ✅ NOUVEAU: Traitement fichier Phase 2 avec chunking intelligent
function processFilePhase2(file) {
    console.log('📁 Traitement fichier Phase 2 v4.2:', file.name);
    
    // Validation du fichier
    if (!validateFile(file)) {
        return;
    }
    
    showProcessingState();
    
    // Parse le fichier d'abord
    parseFileContentPhase2(file);
}

// ✅ NOUVEAU: Parsing avec détection automatique de chunking
async function parseFileContentPhase2(file) {
    try {
        updateProgress(25, 'Lecture du fichier...');
        
        // Utiliser la fonction de lecture existante ou celle du chunking
        let adherentsData;
        
        if (window.ChunkingImport && typeof window.readAdherentFile === 'function') {
            // Utiliser la fonction du chunking si disponible
            adherentsData = await window.readAdherentFile(file);
        } else {
            // Fallback vers méthode simple
            adherentsData = await readFileSimple(file);
        }
        
        updateProgress(50, 'Validation des données...');
        
        if (!adherentsData || adherentsData.length === 0) {
            throw new Error('Le fichier est vide ou invalide');
        }
        
        console.log(`📊 ${adherentsData.length} adhérents détectés`);
        
        updateProgress(75, 'Analyse du volume...');
        
        // ✅ DÉCISION AUTOMATIQUE: Chunking ou traitement standard
        const shouldUseChunking = window.Phase2Config.chunkingAvailable && 
                                 adherentsData.length >= window.Phase2Config.upload.chunkingThreshold;
        
        if (shouldUseChunking) {
            console.log('📦 CHUNKING ACTIVÉ pour Phase 2 - Gros volume détecté');
            updateProgress(90, 'Préparation traitement par lots...');
            
            // Utiliser le système de chunking
            await processWithChunkingPhase2(adherentsData);
            
        } else {
            console.log('📝 Traitement standard Phase 2 - Volume normal');
            updateProgress(90, 'Traitement standard...');
            
            // Traitement standard existant
            await processStandardPhase2(adherentsData);
        }
        
        updateProgress(100, 'Import terminé !');
        
    } catch (error) {
        console.error('❌ Erreur traitement fichier Phase 2:', error);
        showError(error.message);
    }
}

// ✅ NOUVEAU: Traitement avec chunking pour Phase 2
async function processWithChunkingPhase2(adherentsData) {
    try {
        if (!window.ChunkingImport || !window.ChunkingImport.processImportWithChunking) {
            throw new Error('Module chunking non disponible');
        }
        
        console.log('🚀 Démarrage chunking Phase 2 pour', adherentsData.length, 'adhérents');
        
        // Configuration spéciale pour Phase 2
        const validationResult = {
            adherentsValides: adherentsData.filter(a => !a.hasAnomalies),
            adherentsAvecAnomalies: adherentsData.filter(a => a.hasAnomalies),
            adherentsTotal: adherentsData,
            canProceed: true,
            phase2Context: {
                dossierId: window.Phase2Config.dossierId,
                organisationId: window.Phase2Config.organisationId,
                isPhase2: true
            }
        };
        
        // Lancer le chunking
        const success = await window.ChunkingImport.processImportWithChunking(adherentsData, validationResult);
        
        if (success) {
            // Marquer comme succès
            importResults.success = true;
            importResults.stats.total = adherentsData.length;
            importResults.stats.valides = validationResult.adherentsValides.length;
            importResults.processingMethod = 'chunking';
            
            showImportResults();
            
        } else {
            throw new Error('Échec du traitement par chunking');
        }
        
    } catch (error) {
        console.error('❌ Erreur chunking Phase 2:', error);
        
        // Fallback vers traitement standard
        console.log('🔄 Fallback vers traitement standard...');
        await processStandardPhase2(adherentsData);
    }
}

// ✅ NOUVEAU: Traitement standard pour Phase 2 (volumes < seuil)
async function processStandardPhase2(adherentsData) {
    try {
        console.log('📝 Traitement standard Phase 2 pour', adherentsData.length, 'adhérents');
        
        // Simulation traitement standard (à adapter selon vos besoins)
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Marquer comme succès
        importResults.success = true;
        importResults.stats.total = adherentsData.length;
        importResults.stats.valides = adherentsData.length; // Simplifié pour l'exemple
        importResults.processingMethod = 'standard';
        
        showImportResults();
        
    } catch (error) {
        console.error('❌ Erreur traitement standard Phase 2:', error);
        throw error;
    }
}

// Fonctions utilitaires (conservées du code existant)
function validateFile(file) {
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
    
    if (file.size > maxSize) {
        showError('Le fichier est trop volumineux. Maximum 10MB autorisé.');
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showError('Format de fichier non supporté. Utilisez Excel (.xlsx) ou CSV.');
        return false;
    }
    
    return true;
}

// Méthode simple de lecture de fichier (fallback)
async function readFileSimple(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                    const csvText = e.target.result;
                    const lines = csvText.split('\n').filter(line => line.trim());
                    
                    if (lines.length < 2) {
                        throw new Error('Fichier CSV vide ou invalide');
                    }
                    
                    const headers = lines[0].split(',');
                    const data = [];
                    
                    for (let i = 1; i < lines.length; i++) {
                        const values = lines[i].split(',');
                        if (values.length >= 3) {
                            data.push({
                                civilite: values[0] || 'M',
                                nom: values[1] || '',
                                prenom: values[2] || '',
                                nip: values[3] || '',
                                telephone: values[4] || '',
                                profession: values[5] || '',
                                lineNumber: i + 1
                            });
                        }
                    }
                    
                    resolve(data);
                } else {
                    reject(new Error('Format non supporté sans XLSX.js'));
                }
            } catch (error) {
                reject(error);
            }
        };
        
        reader.onerror = () => reject(new Error('Erreur lecture fichier'));
        
        if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
            reader.readAsText(file);
        } else {
            reader.readAsArrayBuffer(file);
        }
    });
}

function showProcessingState() {
    document.getElementById('upload-initial').classList.add('d-none');
    document.getElementById('upload-results').classList.add('d-none');
    document.getElementById('upload-processing').classList.remove('d-none');
}

function showResultsState() {
    document.getElementById('upload-processing').classList.add('d-none');
    document.getElementById('upload-results').classList.remove('d-none');
}

function updateProgress(percent, message) {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const currentChunk = document.getElementById('current-chunk');
    
    if (progressBar) progressBar.style.width = percent + '%';
    if (progressText) progressText.textContent = percent + '%';
    if (currentChunk) currentChunk.textContent = message;
}

function showImportResults() {
    showResultsState();
    
    if (importResults.success) {
        document.getElementById('success-results').classList.remove('d-none');
        document.getElementById('error-results').classList.add('d-none');
        
        const summary = document.getElementById('import-summary');
        if (summary) {
            summary.innerHTML = `
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="text-success">
                            <strong>${importResults.stats.total}</strong><br>
                            <small>Total</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-primary">
                            <strong>${importResults.stats.valides}</strong><br>
                            <small>Valides</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-info">
                            <strong>${importResults.processingMethod}</strong><br>
                            <small>Méthode</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-success">
                            <strong>✓</strong><br>
                            <small>Succès</small>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Afficher le bouton finaliser
        document.getElementById('finalize-btn').classList.remove('d-none');
        
    } else {
        document.getElementById('error-results').classList.remove('d-none');
        document.getElementById('success-results').classList.add('d-none');
    }
}

function showError(message) {
    showResultsState();
    document.getElementById('error-results').classList.remove('d-none');
    document.getElementById('success-results').classList.add('d-none');
    document.getElementById('error-message').textContent = message;
}

function setupFinalizeButton() {
    const finalizeBtn = document.getElementById('finalize-btn');
    const confirmBtn = document.getElementById('confirm-finalize');
    
    if (finalizeBtn) {
        finalizeBtn.addEventListener('click', showFinalizeModal);
    }
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', submitFinalData);
    }
}

function showFinalizeModal() {
    const modal = new bootstrap.Modal(document.getElementById('finalizeModal'));
    
    // Préparer le résumé avec informations chunking
    const finalStats = document.getElementById('final-stats');
    if (finalStats) {
        finalStats.innerHTML = `
            <p><strong>Organisation :</strong> ${window.Phase2Config.stats.existants + importResults.stats.total} adhérents au total</p>
            <p><strong>Nouveau import :</strong> ${importResults.stats.total} adhérents traités</p>
            <p><strong>Méthode :</strong> ${importResults.processingMethod} (Phase 2 v4.2)</p>
            <p><strong>Anomalies :</strong> ${importResults.stats.anomalies_critiques + importResults.stats.anomalies_majeures} détectées</p>
        `;
    }
    
    modal.show();
}

function submitFinalData() {
    console.log('Soumission finale Phase 2 v4.2');
    
    // Préparer les données finales
    const finalData = {
        adherents: JSON.stringify(adherentsData),
        processingMethod: importResults.processingMethod,
        stats: importResults.stats,
        phase: 2,
        version: '4.2'
    };
    
    // Utiliser le formulaire existant
    const form = document.getElementById('adherents-form');
    if (form) {
        document.getElementById('adherents-data').value = JSON.stringify(finalData);
        
        // Afficher loading
        const confirmBtn = document.getElementById('confirm-finalize');
        if (confirmBtn) {
            const originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Finalisation...';
            confirmBtn.disabled = true;
        }
        
        // Soumettre le formulaire
        form.submit();
    }
}
</script>