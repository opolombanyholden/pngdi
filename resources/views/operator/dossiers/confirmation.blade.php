{{-- ✅ CORRECTION MAJEURE: confirmation.blade.php --}}
{{-- PROBLÈME RÉSOLU: Undefined variable: dossier --}}
{{-- SOLUTION: Utiliser $confirmationData['dossier'] au lieu de $dossier --}}

@extends('layouts.operator')

@section('title', 'Confirmation de Soumission')

@section('content')
<div class="container-fluid py-4">
    {{-- ✅ CORRECTION 1: Utiliser $confirmationData['dossier'] au lieu de $dossier --}}
    @php
        $dossier = $confirmationData['dossier'];
        $organisation = $confirmationData['organisation'];
        
        // ✅ NOUVEAU: Détection Phase 2 en cours
        $sessionKey = 'phase2_adherents_' . $dossier->id;
        $adherentsPhase2 = session($sessionKey, []);
        $hasPhase2Pending = !empty($adherentsPhase2);
        $expirationKey = 'phase2_expires_' . $dossier->id;
        $sessionExpiration = session($expirationKey);
    @endphp

    {{-- Header avec statut dynamique --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success shadow-lg">
                <div class="card-header bg-success text-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <h3 class="mb-0">
                                @if($hasPhase2Pending)
                                    Phase 1 Complétée avec Succès !
                                @else
                                    Dossier Soumis avec Succès !
                                @endif
                            </h3>
                            <p class="mb-0 opacity-75">
                                @if($hasPhase2Pending)
                                    Procédez maintenant à l'import des {{ count($adherentsPhase2) }} adhérents
                                @else
                                    Votre dossier {{ $dossier->numero_dossier }} a été enregistré
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ✅ NOUVEAU: Interface Phase 2 Chunking --}}
                @if($hasPhase2Pending)
                <div class="card-body bg-light">
                    <div class="alert alert-warning border-warning">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-users fa-2x text-warning me-3"></i>
                            <div>
                                <h5 class="mb-1">Phase 2 : Import des Adhérents</h5>
                                <p class="mb-0">{{ count($adherentsPhase2) }} adhérents détectés, traitement par lots recommandé</p>
                            </div>
                        </div>

                        {{-- Progress Bar pour Phase 2 --}}
                        <div id="phase2-progress" class="d-none">
                            <div class="d-flex align-items-center mb-2">
                                <strong class="me-2">Traitement en cours...</strong>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 25px;">
                                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                             role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <span id="progress-text">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="progress-details" class="small text-muted"></div>
                        </div>

                        {{-- Bouton pour démarrer Phase 2 --}}
                        <div id="phase2-controls" class="text-center">
                            <button id="start-phase2-import" class="btn btn-warning btn-lg">
                                <i class="fas fa-rocket me-2"></i>
                                Démarrer l'Import des {{ count($adherentsPhase2) }} Adhérents
                            </button>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Session expire le {{ $sessionExpiration ? $sessionExpiration->format('d/m/Y à H:i') : 'N/A' }}
                                </small>
                            </div>
                        </div>

                        {{-- Résultats Phase 2 --}}
                        <div id="phase2-results" class="d-none">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle me-2"></i>Import Terminé avec Succès !</h6>
                                <div id="import-stats"></div>
                                <div class="mt-3">
                                    <a href="{{ route('operator.dossiers.confirmation', $dossier->id) }}" class="btn btn-success">
                                        <i class="fas fa-refresh me-2"></i>Actualiser la Page
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Informations principales --}}
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Organisation</h6>
                            <p class="fw-bold mb-3">{{ $organisation->nom }}</p>
                            
                            <h6 class="text-muted mb-2">Type</h6>
                            <p class="mb-3">
                                <span class="badge bg-info fs-6">
                                    {{ ucfirst(str_replace('_', ' ', $organisation->type)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Numéro de Dossier</h6>
                            <p class="fw-bold mb-3">{{ $dossier->numero_dossier }}</p>
                            
                            <h6 class="text-muted mb-2">Numéro de Récépissé</h6>
                            <p class="fw-bold mb-3">{{ $confirmationData['numero_recepisse'] ?? 'En génération' }}</p>
                            
                            <h6 class="text-muted mb-2">Date de Soumission</h6>
                            <p class="mb-3">{{ $dossier->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistiques des adhérents (si Phase 2 non en cours) --}}
    @if(!$hasPhase2Pending && isset($confirmationData['adherents_stats']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Statistiques des Adhérents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-primary mb-1">{{ $confirmationData['adherents_stats']['total'] ?? 0 }}</h3>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-success mb-1">{{ $confirmationData['adherents_stats']['valides'] ?? 0 }}</h3>
                                <small class="text-muted">Valides</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-warning mb-1">
                                    {{ ($confirmationData['adherents_stats']['anomalies_majeures'] ?? 0) + ($confirmationData['adherents_stats']['anomalies_mineures'] ?? 0) }}
                                </h3>
                                <small class="text-muted">Anomalies</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger mb-1">{{ $confirmationData['adherents_stats']['anomalies_critiques'] ?? 0 }}</h3>
                            <small class="text-muted">Critiques</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Actions et documents --}}
    <div class="row">
        <div class="col-lg-8">
            {{-- Prochaines étapes --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-road me-2"></i>Prochaines Étapes
                    </h5>
                </div>
                <div class="card-body">
                    @if($hasPhase2Pending)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Action Immédiate Requise</h6>
                            <p class="mb-0">
                                Cliquez sur le bouton ci-dessus pour traiter les {{ count($adherentsPhase2) }} adhérents 
                                en attente. Le système utilisera un traitement par lots optimisé.
                            </p>
                        </div>
                    @else
                        @if(isset($confirmationData['prochaines_etapes']))
                        <ol class="list-group list-group-numbered list-group-flush">
                            @foreach($confirmationData['prochaines_etapes'] as $etape)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">{{ $etape['titre'] }}</div>
                                    {{ $etape['description'] }}
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $etape['delai'] }}</span>
                            </li>
                            @endforeach
                        </ol>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Message légal --}}
            @if(!$hasPhase2Pending && isset($confirmationData['message_legal']))
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Information Légale Importante
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 small">{{ $confirmationData['message_legal'] }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Actions rapides --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        {{-- Télécharger l'accusé de réception --}}
                        @if(isset($confirmationData['accuse_reception_url']) && $confirmationData['accuse_reception_url'])
                        <a href="{{ $confirmationData['accuse_reception_url'] }}" 
                           class="btn btn-outline-success" target="_blank">
                            <i class="fas fa-download me-2"></i>Télécharger l'Accusé
                        </a>
                        @endif

                        {{-- Imprimer --}}
                        <button onclick="window.print()" class="btn btn-outline-secondary">
                            <i class="fas fa-print me-2"></i>Imprimer cette Page
                        </button>

                        {{-- Retour au tableau de bord --}}
                        <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-primary">
                            <i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord
                        </a>
                    </div>
                </div>
            </div>

            {{-- Contact support --}}
            @if(isset($confirmationData['contact_support']))
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-headset me-2"></i>Support
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        @if(isset($confirmationData['contact_support']['email']))
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <a href="mailto:{{ $confirmationData['contact_support']['email'] }}">
                                {{ $confirmationData['contact_support']['email'] }}
                            </a>
                        </p>
                        @endif

                        @if(isset($confirmationData['contact_support']['telephone']))
                        <p class="mb-2">
                            <i class="fas fa-phone me-2 text-success"></i>
                            {{ $confirmationData['contact_support']['telephone'] }}
                        </p>
                        @endif

                        @if(isset($confirmationData['contact_support']['horaires']))
                        <p class="mb-2">
                            <i class="fas fa-clock me-2 text-warning"></i>
                            {{ $confirmationData['contact_support']['horaires'] }}
                        </p>
                        @endif

                        @if(isset($confirmationData['contact_support']['adresse']))
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                            {{ $confirmationData['contact_support']['adresse'] }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Toast de succès --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="success-toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Succès</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Votre dossier a été soumis avec succès !
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- ✅ JavaScript pour Phase 2 Chunking --}}
@if($hasPhase2Pending)
<script>
// Configuration Phase 2 Chunking
const Phase2Config = {
    dossierId: {{ $dossier->id }},
    adherentsCount: {{ count($adherentsPhase2) }},
    sessionKey: '{{ $sessionKey }}',
    chunkSize: 100,
    urls: {
        getSessionData: '{{ route("operator.chunking.get-session-data") }}',
        processChunk: '{{ route("operator.chunking.process-chunk") }}',
        cleanup: '{{ route("operator.chunking.cleanup-session") }}'
    },
    csrf: '{{ csrf_token() }}'
};

let currentChunk = 0;
let totalChunks = 0;
let processedCount = 0;
let totalProcessed = 0;
let totalWithAnomalies = 0;

// Démarrer l'import Phase 2
async function startPhase2Import() {
    console.log('🚀 DÉMARRAGE IMPORT PHASE 2', Phase2Config);
    
    const startBtn = document.getElementById('start-phase2-import');
    const progressDiv = document.getElementById('phase2-progress');
    const controlsDiv = document.getElementById('phase2-controls');
    
    // Masquer le bouton, afficher la progress
    startBtn.disabled = true;
    startBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Démarrage...';
    progressDiv.classList.remove('d-none');
    
    try {
        // Récupérer les données de session
        const sessionResponse = await fetch(Phase2Config.urls.getSessionData, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': Phase2Config.csrf
            },
            body: JSON.stringify({
                dossier_id: Phase2Config.dossierId,
                session_key: Phase2Config.sessionKey
            })
        });

        const sessionData = await sessionResponse.json();
        
        if (!sessionData.success) {
            throw new Error(sessionData.message || 'Impossible de récupérer les données');
        }

        const adherents = sessionData.data;
        totalChunks = Math.ceil(adherents.length / Phase2Config.chunkSize);
        
        console.log(`📊 Traitement de ${adherents.length} adhérents en ${totalChunks} chunks`);
        
        updateProgress(0, totalChunks, `Initialisation...`);
        
        // Traiter chunk par chunk
        for (let i = 0; i < totalChunks; i++) {
            await processChunk(adherents, i);
            currentChunk = i + 1;
            
            // Pause entre les chunks pour éviter la surcharge
            if (i < totalChunks - 1) {
                await new Promise(resolve => setTimeout(resolve, 500));
            }
        }
        
        // Nettoyage et finalisation
        await cleanupPhase2();
        showSuccessResults();
        
    } catch (error) {
        console.error('❌ Erreur Phase 2:', error);
        showImportError(error.message);
    }
}

// Traiter un chunk
async function processChunk(adherents, chunkIndex) {
    const start = chunkIndex * Phase2Config.chunkSize;
    const end = Math.min(start + Phase2Config.chunkSize, adherents.length);
    const chunkData = adherents.slice(start, end);
    
    updateProgress(chunkIndex + 1, totalChunks, `Traitement lot ${chunkIndex + 1}/${totalChunks}...`);
    
    console.log(`📤 Envoi chunk ${chunkIndex}:`, {
        start, end, 
        chunkSize: chunkData.length,
        isLast: chunkIndex === totalChunks - 1
    });
    
    const response = await fetch(Phase2Config.urls.processChunk, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': Phase2Config.csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            dossier_id: Phase2Config.dossierId,
            adherents: chunkData,
            chunk_index: chunkIndex,
            total_chunks: totalChunks,
            is_final_chunk: chunkIndex === totalChunks - 1
        })
    });
    
    const result = await response.json();
    console.log(`📥 Résultat chunk ${chunkIndex}:`, result);
    
    if (!result.success) {
        throw new Error(`Erreur chunk ${chunkIndex + 1}: ${result.message}`);
    }
    
    // Accumuler les statistiques
    totalProcessed += result.processed || 0;
    totalWithAnomalies += result.adherents_with_anomalies || 0;
    
    console.log(`✅ Chunk ${chunkIndex + 1} traité: ${result.processed} adhérents`);
}

// Mettre à jour la progression
function updateProgress(current, total, label) {
    const percentage = Math.round((current / total) * 100);
    
    document.getElementById('progress-label').textContent = label;
    document.getElementById('progress-bar').style.width = percentage + '%';
    document.getElementById('progress-bar').setAttribute('aria-valuenow', percentage);
    document.getElementById('progress-text').textContent = percentage + '%';
    
    document.getElementById('progress-details').innerHTML = `
        <div>Progression: ${current}/${total} lots traités</div>
        <div>Adhérents traités: ${totalProcessed}</div>
        ${totalWithAnomalies > 0 ? `<div class="text-warning">Anomalies détectées: ${totalWithAnomalies}</div>` : ''}
    `;
}

// Nettoyage final
async function cleanupPhase2() {
    try {
        await fetch(Phase2Config.urls.cleanup, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': Phase2Config.csrf
            },
            body: JSON.stringify({
                dossier_id: Phase2Config.dossierId,
                session_key: Phase2Config.sessionKey
            })
        });
        console.log('🧹 Nettoyage session terminé');
    } catch (error) {
        console.warn('⚠️ Erreur nettoyage session:', error);
    }
}

// Afficher les résultats de succès
function showSuccessResults() {
    const progressDiv = document.getElementById('phase2-progress');
    const resultsDiv = document.getElementById('phase2-results');
    const controlsDiv = document.getElementById('phase2-controls');
    
    progressDiv.classList.add('d-none');
    controlsDiv.classList.add('d-none');
    resultsDiv.classList.remove('d-none');
    
    document.getElementById('import-stats').innerHTML = `
        <div class="row text-center">
            <div class="col-md-4">
                <strong class="text-primary">${totalProcessed}</strong>
                <br><small>Adhérents traités</small>
            </div>
            <div class="col-md-4">
                <strong class="text-success">${totalProcessed - totalWithAnomalies}</strong>
                <br><small>Valides</small>
            </div>
            <div class="col-md-4">
                <strong class="text-warning">${totalWithAnomalies}</strong>
                <br><small>Avec anomalies</small>
            </div>
        </div>
    `;
}

// Afficher les erreurs
function showImportError(message) {
    const progressDiv = document.getElementById('phase2-progress');
    const controlsDiv = document.getElementById('phase2-controls');
    const startBtn = document.getElementById('start-phase2-import');
    
    progressDiv.classList.add('d-none');
    
    startBtn.disabled = false;
    startBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Réessayer';
    
    // Afficher l'erreur
    const errorAlert = document.createElement('div');
    errorAlert.className = 'alert alert-danger mt-3';
    errorAlert.innerHTML = `
        <h6><i class="fas fa-exclamation-circle me-2"></i>Erreur lors de l'import</h6>
        <p class="mb-0">${message}</p>
    `;
    
    controlsDiv.appendChild(errorAlert);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('start-phase2-import');
    if (startBtn) {
        startBtn.addEventListener('click', startPhase2Import);
    }
});
</script>
@endif

{{-- Toast de succès général --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher le toast de succès si pas de Phase 2
    @if(!$hasPhase2Pending)
    const showSuccessToast = () => {
        const toastElement = document.getElementById('success-toast');
        if (toastElement) {
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    };
    
    // Afficher le toast après le chargement
    setTimeout(showSuccessToast, 1000);
    @endif
});
</script>
@endpush