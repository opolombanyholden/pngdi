{{-- ✅ CORRECTION MAJEURE: confirmation.blade.php --}}
{{-- PROBLÈME RÉSOLU: Interface Phase 2 complète avec éléments HTML corrects --}}

@extends('layouts.operator')

@section('title', 'Confirmation de Soumission')

@section('content')
<div class="container-fluid py-4">
    {{-- ✅ CORRECTION 1: Utiliser $confirmationData['dossier'] au lieu de $dossier --}}
    @php
    $dossier = $confirmationData['dossier'];
    $organisation = $confirmationData['organisation'];
    
    // ✅ NOUVEAU: Détection Phase 2 en cours avec gestion des deux formats
    $sessionKey = 'phase2_adherents_' . $dossier->id;
    $sessionData = session($sessionKey, []);
    
    // Initialiser les variables par défaut
    $adherentsPhase2 = [];
    $adherentsCount = 0;
    $sessionExpiration = null;
    $hasPhase2Pending = false;
    
    // Gérer les deux formats de session de manière sécurisée
    if (is_array($sessionData) && !empty($sessionData)) {
        if (isset($sessionData['data']) && is_array($sessionData['data'])) {
            // Format structuré
            $adherentsPhase2 = $sessionData['data'];
            $adherentsCount = is_numeric($sessionData['total']) ? (int)$sessionData['total'] : count($adherentsPhase2);
            $sessionExpiration = $sessionData['expires_at'] ?? null;
        } else {
            // Format simple - tableau direct
            $adherentsPhase2 = $sessionData;
            $adherentsCount = count($adherentsPhase2);
            $sessionExpiration = session('phase2_expires_' . $dossier->id);
        }
        
        $hasPhase2Pending = $adherentsCount > 0;
    }
    
    // S'assurer que les variables sont des types corrects pour l'affichage
    $adherentsCount = (int)$adherentsCount;
    $sessionExpirationFormatted = null;
    if ($sessionExpiration) {
        try {
            $sessionExpirationFormatted = \Carbon\Carbon::parse($sessionExpiration)->format('d/m/Y à H:i');
        } catch (\Exception $e) {
            $sessionExpirationFormatted = 'N/A';
        }
    }
    
    // ✅ NOUVEAU : Variables pour le dashboard temps réel et lots supplémentaires
    $adherentsEnBase = $organisation->adherents()->count();
    
    // PHP 7.3 compatible
    switch($organisation->type) {
        case 'association':
            $minAdherents = 15;
            break;
        case 'ong':
            $minAdherents = 25;
            break;
        case 'fondation':
            $minAdherents = 10;
            break;
        case 'cooperative':
            $minAdherents = 20;
            break;
        default:
            $minAdherents = 15;
            break;
    }
    
    $totalAdherents = $adherentsEnBase + $adherentsCount;
    $adherentsManquants = max(0, $minAdherents - $totalAdherents);
    $pretPourSoumission = $totalAdherents >= $minAdherents && !$hasPhase2Pending;
    
    // Informations sur les lots déjà uploadés
    $metadataKey = 'phase2_metadata_' . $dossier->id;
    $lotsHistory = session($metadataKey . '.lots_history', []);
    $nombreLots = count($lotsHistory) + ($adherentsCount > 0 ? 1 : 0);
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
                                    Procédez maintenant à l'import des {{ $adherentsCount }} adhérents
                                @else
                                    Votre dossier {{ $dossier->numero_dossier }} a été enregistré
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ✅ NOUVEAU: Interface Phase 2 Chunking Complète --}}
                @if($hasPhase2Pending)
                <div class="card-body bg-light">
                    <div class="alert alert-warning border-warning">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-users fa-2x text-warning me-3"></i>
                            <div>
                                <h5 class="mb-1">Phase 2 : Import des Adhérents</h5>
                                <p class="mb-0">{{ $adherentsCount }} adhérents détectés, traitement par lots recommandé</p>
                            </div>
                        </div>

                        {{-- ✅ CORRECTION: Progress Bar avec tous les éléments requis --}}
                        <div id="phase2-progress" class="d-none mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <strong id="progress-label" class="me-2">Traitement en cours...</strong>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 25px;">
                                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                             role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <span id="progress-text">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="progress-details" class="small text-muted">
                                <div>Progression: 0/0 lots traités</div>
                                <div>Adhérents traités: 0</div>
                            </div>
                        </div>

                        {{-- ✅ CORRECTION: Bouton et messages avec IDs corrects --}}
                        <div id="phase2-controls" class="text-center">
                            <button id="start-phase2-import" class="btn btn-warning btn-lg" onclick="startPhase2Import()">
                                <i class="fas fa-rocket me-2"></i>
                                Démarrer l'Import des {{ $adherentsCount }} Adhérents
                            </button>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Session expire le {{ $sessionExpirationFormatted ?? 'N/A' }}
                                </small>
                            </div>
                        </div>

                        {{-- ✅ NOUVEAU: Zone de résultats --}}
                        <div id="phase2-results" class="d-none">
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i>Import Terminé avec Succès !</h5>
                                <div id="results-summary">
                                    <p><strong>Adhérents traités :</strong> <span id="total-processed">0</span></p>
                                    <p><strong>Anomalies détectées :</strong> <span id="total-anomalies">0</span></p>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-success" onclick="window.location.reload()">
                                        <i class="fas fa-sync me-2"></i>Actualiser la Page
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ✅ NOUVEAU: Zone d'erreurs --}}
                        <div id="phase2-errors" class="d-none">
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Erreur lors de l'Import</h5>
                                <div id="error-message">Une erreur est survenue pendant le traitement.</div>
                                <div class="mt-3">
                                    <button class="btn btn-warning" onclick="retryImport()">
                                        <i class="fas fa-redo me-2"></i>Réessayer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Informations du dossier --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Détails du Dossier
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Organisation :</strong> {{ $organisation->nom }}</p>
                            <p><strong>Type :</strong> 
                                <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $organisation->type)) }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Numéro de Dossier :</strong> {{ $dossier->numero_dossier }}</p>
                            <p><strong>Numéro de Récépissé :</strong> {{ $confirmationData['numero_recepisse'] ?? 'En attente' }}</p>
                            <p><strong>Date de Soumission :</strong> {{ $dossier->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prochaines étapes ou Phase 2 --}}
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        @if($hasPhase2Pending)
                            Action Immédiate Requise
                        @else
                            Prochaines Étapes
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($hasPhase2Pending)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-exclamation-circle me-2"></i>Phase 2 en Attente</h6>
                            <p class="mb-2">
                                Cliquez sur le bouton ci-dessus pour traiter les {{ $adherentsCount }} adhérents en attente. 
                                Le système utilisera un traitement par lots optimisé.
                            </p>
                        </div>
                    @else
                        @if(isset($confirmationData['prochaines_etapes']) && is_array($confirmationData['prochaines_etapes']))
                        <ol class="list-group list-group-numbered list-group-flush">
                            @foreach($confirmationData['prochaines_etapes'] as $etape)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">
                                        {{ is_string($etape['titre'] ?? '') ? ($etape['titre'] ?? 'Étape') : 'Étape' }}
                                    </div>
                                    {{ is_string($etape['description'] ?? '') ? ($etape['description'] ?? 'Description en cours') : 'Description en cours' }}
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    {{ is_string($etape['delai'] ?? '') ? ($etape['delai'] ?? '') : '' }}
                                </span>
                            </li>
                            @endforeach
                        </ol>
                        @endif
                    @endif
                </div>
            </div>
        </div>


        {{-- ✅ NOUVEAU : Dashboard statistiques temps réel des adhérents --}}
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4" id="dashboard-adherents">
                <div class="card-header bg-gradient" style="background: linear-gradient(45deg, #28a745, #20c997);">
                    <div class="d-flex justify-content-between align-items-center text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Dashboard Adhérents Temps Réel
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="refreshStatistics()" id="refresh-stats-btn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="stats-container">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <div class="h4 text-primary mb-1" id="adherents-en-base">{{ $adherentsEnBase }}</div>
                                <small class="text-muted">En base de données</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <div class="h4 text-warning mb-1" id="adherents-en-session">{{ $adherentsCount }}</div>
                                <small class="text-muted">En attente (session)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <div class="h4 text-success mb-1" id="total-adherents">{{ $totalAdherents }}</div>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded 
                                {{ $pretPourSoumission ? 'bg-success text-white' : 'bg-light' }}">
                                <div class="h4 mb-1" id="adherents-requis">{{ $minAdherents }}</div>
                                <small>Requis ({{ $organisation->type }})</small>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Progress bar globale --}}
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Progression vers soumission</span>
                            <span class="small" id="progress-percentage">{{ round(($totalAdherents / $minAdherents) * 100, 1) }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $pretPourSoumission ? 'bg-success' : 'bg-warning' }}" 
                                 id="progress-bar" 
                                 style="width: {{ min(($totalAdherents / $minAdherents) * 100, 100) }}%">
                            </div>
                        </div>
                        @if($adherentsManquants > 0)
                            <small class="text-danger mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $adherentsManquants }} adhérent(s) manquant(s) pour pouvoir soumettre
                            </small>
                        @else
                            <small class="text-success mt-1">
                                <i class="fas fa-check-circle"></i>
                                Conditions remplies pour la soumission
                            </small>
                        @endif
                    </div>

                    {{-- Historique des lots --}}
                    @if($nombreLots > 0)
                    <div class="mt-3">
                        <h6><i class="fas fa-layer-group me-2"></i>Historique des lots ({{ $nombreLots }})</h6>
                        <div class="row" id="lots-history">
                            @if($adherentsCount > 0)
                            <div class="col-md-6 mb-2">
                                <div class="border rounded p-2 bg-warning bg-opacity-25">
                                    <small class="fw-bold">Lot actuel (en session)</small><br>
                                    <small>{{ $adherentsCount }} adhérents - Expire: {{ $sessionExpirationFormatted }}</small>
                                </div>
                            </div>
                            @endif
                            @foreach($lotsHistory as $lotNum => $lotInfo)
                            <div class="col-md-6 mb-2">
                                <div class="border rounded p-2 bg-success bg-opacity-25">
                                    <small class="fw-bold">Lot {{ $lotNum }}</small><br>
                                    <small>{{ $lotInfo['adherents_count'] ?? 0 }} adhérents - {{ isset($lotInfo['uploaded_at']) ? \Carbon\Carbon::parse($lotInfo['uploaded_at'])->format('d/m H:i') : 'N/A' }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>



        {{-- ✅ NOUVEAU : Zone upload lots supplémentaires (conditionnelle) --}}
        @if($totalAdherents > 0 && !$pretPourSoumission)
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4" id="upload-additional-section">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload me-2"></i>Ajouter des adhérents supplémentaires
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Vous pouvez charger des lots supplémentaires de 10 000 adhérents maximum.</strong><br>
                        <small>Les fichiers seront traités automatiquement et fusionnés avec les adhérents existants.</small>
                    </div>

                    <form id="additional-batch-form" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label for="additional-file" class="form-label">
                                    <i class="fas fa-file-excel me-1"></i>Fichier Excel/CSV
                                </label>
                                <input type="file" 
                                       class="form-control" 
                                       id="additional-file" 
                                       name="fichier_adherents"
                                       accept=".xlsx,.csv"
                                       required>
                                <small class="text-muted">Format accepté : XLSX, CSV (max 10MB)</small>
                            </div>
                            <div class="col-md-3">
                                <label for="lot-numero" class="form-label">Numéro de lot</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="lot-numero" 
                                       name="lot_numero"
                                       value="{{ $nombreLots + 1 }}" 
                                       min="2" 
                                       readonly>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" id="upload-additional-btn">
                                    <i class="fas fa-upload me-2"></i>Charger le lot
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Zone de progression upload --}}
                    <div id="upload-progress" class="mt-3 d-none">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Upload en cours...</span>
                            <span class="small" id="upload-percentage">0%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 id="upload-progress-bar" 
                                 style="width: 0%"></div>
                        </div>
                    </div>

                    {{-- Zone de résultats upload --}}
                    <div id="upload-results" class="mt-3 d-none">
                        <div class="alert" id="upload-alert">
                            <div id="upload-message"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif


        {{-- ✅ NOUVEAU : Guide procédure soumission finale --}}
        @if($pretPourSoumission)
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4" id="submission-guide">
                <div class="card-header" style="background: linear-gradient(45deg, #6f42c1, #e83e8c);">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-rocket me-2"></i>Prêt pour la soumission finale à l'administration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle me-2"></i>Toutes les conditions sont remplies !</h6>
                        <p class="mb-0">Votre dossier contient {{ $totalAdherents }} adhérents (minimum requis : {{ $minAdherents }})</p>
                    </div>

                    {{-- Étapes de soumission --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="fw-bold mb-3">Procédure de soumission finale :</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                            1
                                        </div>
                                        <div>
                                            <small class="fw-bold">Vérification finale</small><br>
                                            <small class="text-muted">Contrôle automatique des données</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                            2
                                        </div>
                                        <div>
                                            <small class="fw-bold">Traitement adhérents</small><br>
                                            <small class="text-muted">Import en base de données</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                            3
                                        </div>
                                        <div>
                                            <small class="fw-bold">Transmission</small><br>
                                            <small class="text-muted">Envoi à l'administration</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informations importantes --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="border-start border-warning border-3 ps-3">
                                <h6 class="text-warning">Important à savoir</h6>
                                <ul class="small mb-0">
                                    <li>La soumission est <strong>définitive</strong></li>
                                    <li>Délai de traitement : <strong>15 jours ouvrés</strong></li>
                                    <li>Vous recevrez un accusé de réception</li>
                                    <li>Suivi par email automatique</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-start border-info border-3 ps-3">
                                <h6 class="text-info">Après soumission</h6>
                                <ul class="small mb-0">
                                    <li>Aucune modification possible</li>
                                    <li>Dossier verrouillé automatiquement</li>
                                    <li>Notifications aux étapes clés</li>
                                    <li>Support disponible 24h/24</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Formulaire de soumission finale --}}
                    <form id="final-submission-form">
                        @csrf
                        <div class="bg-light rounded p-3 mb-3">
                            <h6 class="fw-bold mb-3">Déclarations obligatoires</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="declaration-finale" name="declaration_finale" required>
                                <label class="form-check-label" for="declaration-finale">
                                    <strong>Je certifie sur l'honneur</strong> que toutes les informations fournies sont exactes et complètes
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmation-soumission" name="confirmation_soumission" required>
                                <label class="form-check-label" for="confirmation-soumission">
                                    <strong>Je confirme</strong> vouloir soumettre définitivement ce dossier à l'administration
                                </label>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-lg btn-success px-5" id="submit-to-admin-btn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Soumettre mon dossier à l'administration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif




        <div class="col-lg-4">
            {{-- Actions rapides --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body text-center">
                    <button class="btn btn-outline-primary btn-sm mb-2" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimer cette Page
                    </button>
                    <br>
                    <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord
                    </a>
                </div>
            </div>

            {{-- Support --}}
            @if(isset($confirmationData['contact_support']) && is_string($confirmationData['contact_support']))
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-headset me-2"></i>Besoin d'Aide ?
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small mb-2">{{ $confirmationData['contact_support'] }}</p>
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-envelope me-1"></i>Contacter le Support
                    </button>
                </div>
            </div>
            @endif
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
    adherentsCount: {{ $adherentsCount }},
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
    const errorsDiv = document.getElementById('phase2-errors');
    
    // Masquer les erreurs précédentes
    errorsDiv.classList.add('d-none');
    
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
        
        const sessionResult = await sessionResponse.json();
        console.log('📥 Données session récupérées:', sessionResult);
        
        if (!sessionResult.success) {
            throw new Error(sessionResult.message || 'Impossible de récupérer les données de session');
        }
        
        const adherentsData = sessionResult.data;
        const totalAdherents = sessionResult.total;
        
        // Calculer les chunks
        totalChunks = Math.ceil(totalAdherents / Phase2Config.chunkSize);
        console.log(`📊 Total: ${totalAdherents} adhérents, ${totalChunks} chunks de ${Phase2Config.chunkSize}`);
        
        // Traiter chunk par chunk
        for (let i = 0; i < totalChunks; i++) {
            const start = i * Phase2Config.chunkSize;
            const end = Math.min(start + Phase2Config.chunkSize, totalAdherents);
            const chunkData = adherentsData.slice(start, end);
            
            updateProgress(i, totalChunks, `Traitement du lot ${i + 1}/${totalChunks}...`);
            
            await processChunk(chunkData, i);
            
            // Pause entre les chunks
            if (i < totalChunks - 1) {
                await new Promise(resolve => setTimeout(resolve, 500));
            }
        }
        
        // Finalisation
        updateProgress(totalChunks, totalChunks, 'Import terminé !');
        await cleanupPhase2();
        showSuccessResults();
        
    } catch (error) {
        console.error('❌ Erreur durant l\'import:', error);
        showError(error.message);
    }
}

// Traiter un chunk
async function processChunk(chunkData, chunkIndex) {
    console.log(`🔄 Traitement chunk ${chunkIndex + 1}: ${chunkData.length} adhérents`);
    
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
    
    document.getElementById('total-processed').textContent = totalProcessed;
    document.getElementById('total-anomalies').textContent = totalWithAnomalies;
}

// Afficher une erreur
function showError(message) {
    const progressDiv = document.getElementById('phase2-progress');
    const errorsDiv = document.getElementById('phase2-errors');
    const controlsDiv = document.getElementById('phase2-controls');
    
    progressDiv.classList.add('d-none');
    controlsDiv.classList.add('d-none');
    errorsDiv.classList.remove('d-none');
    
    document.getElementById('error-message').textContent = message;
}

// Réessayer l'import
function retryImport() {
    const errorsDiv = document.getElementById('phase2-errors');
    const controlsDiv = document.getElementById('phase2-controls');
    
    errorsDiv.classList.add('d-none');
    controlsDiv.classList.remove('d-none');
    
    // Remettre le bouton dans son état initial
    const startBtn = document.getElementById('start-phase2-import');
    startBtn.disabled = false;
    startBtn.innerHTML = '<i class="fas fa-rocket me-2"></i>Démarrer l\'Import des ' + Phase2Config.adherentsCount + ' Adhérents';
}
</script>

<script>
/**
 * ===================================================================
 * JAVASCRIPT POUR GESTION LOTS SUPPLÉMENTAIRES ET SOUMISSION FINALE
 * ===================================================================
 */

// Variables globales
let statisticsRefreshInterval;
const dossierId = {{ $dossier->id }};

// Initialisation au chargement de la page
$(document).ready(function() {
    initializeAdditionalBatchUpload();
    initializeFinalSubmission();
    initializeStatisticsRefresh();
    
    // Vérifier l'état initial
    refreshStatistics();
});

/**
 * Initialiser l'upload de lots supplémentaires
 */
function initializeAdditionalBatchUpload() {
    $('#additional-batch-form').on('submit', function(e) {
        e.preventDefault();
        uploadAdditionalBatch();
    });
}

/**
 * Upload d'un lot supplémentaire
 */
async function uploadAdditionalBatch() {
    const form = document.getElementById('additional-batch-form');
    const formData = new FormData(form);
    const uploadBtn = $('#upload-additional-btn');
    const progressDiv = $('#upload-progress');
    const resultsDiv = $('#upload-results');
    
    try {
        // Afficher la progression
        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Upload en cours...');
        progressDiv.removeClass('d-none');
        resultsDiv.addClass('d-none');
        
        // Simuler progression
        animateProgress();
        
        const response = await fetch(`/operator/organisations/${dossierId}/upload-additional-batch`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showUploadSuccess(data);
            form.reset();
            // Actualiser les statistiques
            setTimeout(refreshStatistics, 1000);
        } else {
            showUploadError(data.message || 'Erreur lors de l\'upload');
        }
        
    } catch (error) {
        console.error('Erreur upload:', error);
        showUploadError('Erreur de connexion lors de l\'upload');
    } finally {
        uploadBtn.prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Charger le lot');
        progressDiv.addClass('d-none');
    }
}

/**
 * Animer la barre de progression
 */
function animateProgress() {
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
        }
        $('#upload-progress-bar').css('width', progress + '%');
        $('#upload-percentage').text(Math.round(progress) + '%');
    }, 200);
}

/**
 * Afficher le succès de l'upload
 */
function showUploadSuccess(data) {
    const alert = $('#upload-alert');
    const message = $('#upload-message');
    
    alert.removeClass('alert-danger').addClass('alert-success');
    message.html(`
        <h6><i class="fas fa-check-circle me-2"></i>Lot ${data.data.lot_numero} traité avec succès !</h6>
        <ul class="mb-0">
            <li>Nouveaux adhérents : ${data.data.nouveaux_adherents}</li>
            <li>Total adhérents : ${data.data.total_adherents}</li>
            ${data.data.doublons_supprimes > 0 ? `<li>Doublons supprimés : ${data.data.doublons_supprimes}</li>` : ''}
        </ul>
    `);
    
    $('#upload-results').removeClass('d-none');
    
    // Incrementer le numéro de lot pour le prochain upload
    $('#lot-numero').val(parseInt($('#lot-numero').val()) + 1);
}

/**
 * Afficher l'erreur de l'upload
 */
function showUploadError(message) {
    const alert = $('#upload-alert');
    const messageDiv = $('#upload-message');
    
    alert.removeClass('alert-success').addClass('alert-danger');
    messageDiv.html(`
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Erreur lors de l'upload</h6>
        <p class="mb-0">${message}</p>
    `);
    
    $('#upload-results').removeClass('d-none');
}

/**
 * Initialiser la soumission finale
 */
function initializeFinalSubmission() {
    $('#final-submission-form').on('submit', function(e) {
        e.preventDefault();
        submitToAdministration();
    });
}

/**
 * Soumettre le dossier à l'administration
 */
async function submitToAdministration() {
    const form = document.getElementById('final-submission-form');
    const formData = new FormData(form);
    const submitBtn = $('#submit-to-admin-btn');
    
    // Vérifier les déclarations
    if (!$('#declaration-finale').is(':checked') || !$('#confirmation-soumission').is(':checked')) {
        alert('Vous devez cocher toutes les déclarations obligatoires');
        return;
    }
    
    // Confirmation utilisateur
    if (!confirm('Êtes-vous sûr de vouloir soumettre définitivement ce dossier à l\'administration ?\n\nCette action est irréversible.')) {
        return;
    }
    
    try {
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Soumission en cours...');
        
        const response = await fetch(`/operator/organisations/${dossierId}/submit-to-administration`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redirection vers la confirmation définitive
            showLoadingMessage('Dossier soumis avec succès ! Redirection...');
            setTimeout(() => {
                window.location.href = data.data.redirect_url;
            }, 2000);
        } else {
            alert('Erreur lors de la soumission : ' + (data.message || 'Erreur inconnue'));
            submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Soumettre mon dossier à l\'administration');
        }
        
    } catch (error) {
        console.error('Erreur soumission:', error);
        alert('Erreur de connexion lors de la soumission');
        submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Soumettre mon dossier à l\'administration');
    }
}

/**
 * Actualiser les statistiques temps réel
 */
async function refreshStatistics() {
    try {
        const response = await fetch(`/operator/organisations/${dossierId}/adherents-statistics`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateStatisticsDisplay(data.statistics);
        }
        
    } catch (error) {
        console.error('Erreur actualisation statistiques:', error);
    }
}

/**
 * Mettre à jour l'affichage des statistiques
 */
function updateStatisticsDisplay(stats) {
    $('#adherents-en-base').text(stats.adherents_en_base);
    $('#adherents-en-session').text(stats.adherents_en_session);
    $('#total-adherents').text(stats.total_adherents);
    
    const progressPercentage = Math.round((stats.total_adherents / stats.adherents_requis) * 100);
    $('#progress-percentage').text(progressPercentage + '%');
    $('#progress-bar').css('width', Math.min(progressPercentage, 100) + '%');
    
    // Mettre à jour la couleur de la barre selon le statut
    const progressBar = $('#progress-bar');
    if (stats.pret_pour_soumission) {
        progressBar.removeClass('bg-warning').addClass('bg-success');
    } else {
        progressBar.removeClass('bg-success').addClass('bg-warning');
    }
}

/**
 * Initialiser l'actualisation automatique des statistiques
 */
function initializeStatisticsRefresh() {
    // Actualiser toutes les 30 secondes
    statisticsRefreshInterval = setInterval(refreshStatistics, 30000);
    
    // Bouton d'actualisation manuelle
    $('#refresh-stats-btn').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin"></i>');
        refreshStatistics();
        setTimeout(() => {
            $(this).html('<i class="fas fa-sync-alt"></i>');
        }, 1000);
    });
}

/**
 * Afficher un message de chargement
 */
function showLoadingMessage(message) {
    const overlay = $(`
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
             style="background: rgba(0,0,0,0.8); z-index: 9999; color: white;">
            <div class="text-center">
                <div class="spinner-border mb-3" role="status"></div>
                <h5>${message}</h5>
            </div>
        </div>
    `);
    $('body').append(overlay);
}

// Nettoyer les intervalles à la fermeture de la page
$(window).on('beforeunload', function() {
    if (statisticsRefreshInterval) {
        clearInterval(statisticsRefreshInterval);
    }
});
</script>


@endif
@endpush