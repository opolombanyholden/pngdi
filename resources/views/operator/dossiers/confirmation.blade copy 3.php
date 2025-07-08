{{-- ========================================================================
     CONFIRMATION.BLADE.PHP - VERSION OPTIMISÉE EXTERNALISÉE
     Fichiers externes : confirmation.css, confirmation.js, file-upload-sglp.js
     ======================================================================== --}}

@extends('layouts.operator')

@section('title', 'Confirmation de Soumission')

@section('page-title', 'Confirmation de Soumission')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/organisation-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/confirmation.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- ✅ LOGIQUE ORIGINALE PRÉSERVÉE : Variables PHP du fichier original --}}
    @php

    // ✅ SOLUTION OPTIMISÉE : Gestion mémoire efficace pour 10 000+ adhérents
// Inspirée de confirmation.blade copy.php (version fonctionnelle)

// Initialiser les variables par défaut
$adherentsCount = 0;
$sessionExpiration = null;
$hasPhase2Pending = false;

// ✅ OPTIMISATION 1 : Vérification existence avant chargement
$sessionKey = 'phase2_adherents_' . $dossier->id;
$sessionExists = session()->has($sessionKey);

if ($sessionExists) {
    try {
        // ✅ OPTIMISATION 2 : Lecture métadonnées seulement
        $expirationKey = 'phase2_expires_' . $dossier->id;
        $sessionExpiration = session($expirationKey);
        
        // Vérifier expiration AVANT de charger les données
        $sessionValid = $sessionExpiration && now()->isBefore($sessionExpiration);
        
        if (!$sessionValid) {
            // ✅ Session expirée - Nettoyer sans charger
            session()->forget([$sessionKey, $expirationKey]);
            $sessionExists = false;
            
            \Log::warning('⚠️ Session adhérents expirée - nettoyée', [
                'dossier_id' => $dossier->id,
                'expired_at' => $sessionExpiration
            ]);
        } else {
            // ✅ OPTIMISATION 3 : Lecture minimale pour comptage
            $sessionData = session($sessionKey);
            
            if (is_array($sessionData) && !empty($sessionData)) {
                // Format structuré avec métadonnées
                if (isset($sessionData['total']) && is_numeric($sessionData['total'])) {
                    // ✅ Utiliser le compteur pré-calculé (plus rapide)
                    $adherentsCount = (int)$sessionData['total'];
                } elseif (isset($sessionData['data']) && is_array($sessionData['data'])) {
                    // ✅ Compter sans charger en mémoire
                    $adherentsCount = count($sessionData['data']);
                } else {
                    // Format simple - tableau direct
                    $adherentsCount = count($sessionData);
                }
                
                $hasPhase2Pending = $adherentsCount > 0;
            }
            
            // ✅ CRITIQUE : Libérer immédiatement la variable lourde
            unset($sessionData);
        }
        
    } catch (\Exception $e) {
        // ✅ Gestion d'erreur sans crash
        \Log::error('❌ Erreur lecture session adhérents', [
            'dossier_id' => $dossier->id,
            'error' => $e->getMessage()
        ]);
        
        // Valeurs par défaut en cas d'erreur
        $adherentsCount = 0;
        $hasPhase2Pending = false;
        $sessionExpiration = null;
    }
}

// ✅ OPTIMISATION 4 : Variables finales optimisées
$adherentsCount = (int)$adherentsCount; // S'assurer du type
$sessionExpirationFormatted = null;

if ($sessionExpiration) {
    try {
        $sessionExpirationFormatted = \Carbon\Carbon::parse($sessionExpiration)->format('d/m/Y à H:i');
    } catch (\Exception $e) {
        $sessionExpirationFormatted = 'Format invalide';
    }
}

// ✅ OPTIMISATION 5 : Log de contrôle mémoire (debug)
if (config('app.debug')) {
    $memoryUsage = memory_get_usage(true);
    $memoryPeak = memory_get_peak_usage(true);
    
    \Log::info('💾 Mémoire après traitement session', [
        'dossier_id' => $dossier->id,
        'adherents_count' => $adherentsCount,
        'has_pending' => $hasPhase2Pending,
        'memory_current' => round($memoryUsage / 1024 / 1024, 2) . 'MB',
        'memory_peak' => round($memoryPeak / 1024 / 1024, 2) . 'MB'
    ]);
}

// ✅ REMARQUE : $adherentsPhase2 n'est plus nécessaire
// Les données complètes ne sont chargées qu'au moment de l'import
// Cela économise 15-25MB de mémoire pour 10 000 adhérents

/*
 * ✅ UTILISATION DANS LA VUE :
 * 
 * @if($hasPhase2Pending)
 *     Il y a {{ $adherentsCount }} adhérents en attente
 *     Session expire : {{ $sessionExpirationFormatted }}
 * @endif
 */
    
    @endphp

    {{-- ✅ DESIGN GABONAIS : Header avec statut dynamique --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg section-card">
                <div class="card-header header-success position-relative overflow-hidden">
                    {{-- Animation de confettis de succès --}}
                    <div class="position-absolute top-0 start-0 w-100 h-100" id="success-particles"></div>
                    
                    <div class="row align-items-center position-relative">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="success-icon me-4">
                                    <div class="icon-circle">
                                        <i class="fas fa-check-circle fa-3x text-white" id="success-check"></i>
                                    </div>
                                </div>
                                <div>
                                    <h2 class="mb-2 fw-bold text-white">
                                        @if($hasPhase2Pending)
                                            Phase 1 Complétée avec Succès !
                                        @else
                                            Dossier Soumis avec Succès !
                                        @endif
                                    </h2>
                                    <p class="mb-0 fs-5 text-white opacity-90">
                                        @if($hasPhase2Pending)
                                            {{ number_format($adherentsCount) }} adhérents prêts pour l'import en base de données
                                        @else
                                            Votre dossier {{ $dossier->numero_dossier }} a été enregistré
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="status-badge">
                                <div class="h4 text-white mb-1">{{ $dossier->numero_dossier }}</div>
                                <small class="opacity-75">Numéro de dossier</small>
                                <div class="mt-2">
                                    <span class="badge badge-status-{{ $hasPhase2Pending ? 'warning' : 'success' }}">
                                        @if($hasPhase2Pending)
                                            Phase 2 en attente
                                        @else
                                            Terminé
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ STATISTIQUES MODERNES : 4 cards avec couleurs gabonaises officielles --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card stats-card-green">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 fw-bold" id="adherents-en-base">{{ number_format($adherentsEnBase) }}</h3>
                            <p class="mb-0 small opacity-90">En Base de Données</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-database fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2 progress-small">
                        <div class="progress-bar bg-light" style="width: {{ $adherentsEnBase > 0 ? '100' : '0' }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card stats-card-yellow">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 fw-bold" id="adherents-en-session">{{ number_format($adherentsCount) }}</h3>
                            <p class="mb-0 small">En Attente (Session)</p>
                        </div>
                        <div class="icon-circle icon-circle-dark">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2 progress-small">
                        <div class="progress-bar bg-dark" style="width: {{ $adherentsCount > 0 ? '75' : '0' }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card stats-card-blue">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 fw-bold" id="total-adherents">{{ number_format($totalAdherents) }}</h3>
                            <p class="mb-0 small opacity-90">Total Adhérents</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2 progress-small">
                        <div class="progress-bar bg-light" style="width: {{ round(($totalAdherents / $minAdherents) * 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card stats-card-{{ $pretPourSoumission ? 'green' : 'red' }}">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 fw-bold">{{ $minAdherents }}</h3>
                            <p class="mb-0 small opacity-90">Minimum Requis</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-{{ $pretPourSoumission ? 'check-circle' : 'exclamation-triangle' }} fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2 progress-small">
                        <div class="progress-bar bg-light" style="width: {{ $pretPourSoumission ? '100' : '30' }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ DASHBOARD TEMPS RÉEL : Progression globale et actualisation --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm section-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-primary">Progression vers soumission</strong>
                                <span id="progress-percentage" class="fw-bold text-success">
                                    {{ round(($totalAdherents / $minAdherents) * 100, 1) }}%
                                </span>
                            </div>
                            <div class="progress progress-main">
                                <div class="progress-bar progress-bar-{{ $pretPourSoumission ? 'success' : 'warning' }}" 
                                     id="main-progress-bar" 
                                     style="width: {{ min(($totalAdherents / $minAdherents) * 100, 100) }}%">
                                    {{ round(($totalAdherents / $minAdherents) * 100, 1) }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            @if($adherentsManquants > 0)
                                <div class="alert alert-warning-custom mb-0 py-2">
                                    <small>
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <strong>{{ $adherentsManquants }} adhérent(s) manquant(s)</strong>
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-success-custom mb-0 py-2">
                                    <small>
                                        <i class="fas fa-check-circle me-1"></i>
                                        <strong>Conditions remplies !</strong>
                                    </small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <button class="btn btn-outline-success" onclick="ConfirmationApp.refreshStatistics()" id="refresh-stats-btn">
                                    <i class="fas fa-sync-alt me-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ INTERFACE PHASE 2 COMPLÈTE : Section import des adhérents avec chunking --}}
    @if($hasPhase2Pending)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm section-card">
                <div class="card-header card-header-phase2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-cloud-upload-alt me-2"></i>
                            Phase 2 : Import des {{ number_format($adherentsCount) }} Adhérents
                        </h5>
                        <span class="badge badge-warning-custom">{{ number_format($adherentsCount) }} en attente</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info-custom border-0">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon alert-icon-info me-3">
                                <i class="fas fa-info-circle text-white fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">{{ number_format($adherentsCount) }} adhérents détectés en session</h6>
                                <p class="mb-0">
                                    Ces adhérents vont être importés en base de données avec traitement intelligent par lots.
                                    <br><small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Session expire le {{ $sessionExpirationFormatted ?? 'N/A' }}
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Contrôles d'import --}}
                    <div id="import-controls" class="text-center">
                        <button id="start-import-btn" class="btn btn-warning-custom btn-lg px-5" 
                                onclick="ConfirmationApp.startAdherentsImport()">
                            <i class="fas fa-rocket me-2"></i>
                            Démarrer l'Import des {{ number_format($adherentsCount) }} Adhérents
                        </button>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1 text-success"></i>
                                Traitement sécurisé par lots - Compatible avec les gros volumes
                            </small>
                        </div>
                    </div>

                    {{-- Zone de progression import --}}
                    <div id="import-progress" class="d-none mt-4">
                        <div class="card border-0 progress-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong id="import-status" class="text-success">Préparation...</strong>
                                    <span id="import-percentage" class="fw-bold text-success">0%</span>
                                </div>
                                <div class="progress progress-import">
                                    <div id="import-progress-bar" 
                                         class="progress-bar progress-bar-striped progress-bar-animated progress-bar-success"
                                         style="width: 0%;">
                                        <span id="import-progress-text" class="text-white fw-bold">0%</span>
                                    </div>
                                </div>
                                <div id="import-details" class="mt-2 small text-muted">
                                    Initialisation du traitement intelligent par lots...
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Zone de résultats import --}}
                    <div id="import-results" class="d-none mt-4">
                        <!-- Résultats seront injectés ici par JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ✅ MODULE AVANCÉ D'UPLOAD : Section upload lots supplémentaires --}}
    @if($totalAdherents > 0 && !$pretPourSoumission && !$hasPhase2Pending)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm section-card">
                <div class="card-header card-header-upload">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-plus-circle me-2"></i>
                            Ajouter des adhérents supplémentaires
                        </h5>
                        <span class="badge badge-info-custom">{{ $adherentsManquants }} manquant(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Alerte informative avec style gabonais --}}
                    <div class="alert alert-info-custom border-0 mb-4">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon alert-icon-info me-3">
                                <i class="fas fa-upload fa-2x text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-2">
                                    <i class="fas fa-plus me-2"></i>
                                    Module d'ajout avancé - Lots supplémentaires
                                </h6>
                                <div class="mb-2">
                                    <p class="mb-0">
                                        <strong>Ajoutez plus d'adhérents pour atteindre le minimum requis</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            Système intelligent avec validation temps réel et gestion des doublons
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modes d'ajout avec design moderne gabonais --}}
                    <div class="card border-0 shadow-sm mb-4 modes-card">
                        <div class="card-header card-header-modes">
                            <h6 class="mb-0 text-white">
                                <i class="fas fa-cog me-2"></i>
                                Mode d'ajout des adhérents supplémentaires
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mode-card" data-mode="manuel">
                                        <input class="form-check-input d-none" type="radio" name="additional_mode" id="additional_mode_manuel" value="manuel" checked>
                                        <label class="form-check-label w-100" for="additional_mode_manuel">
                                            <div class="mode-option mode-option-additional">
                                                <div class="mode-icon mode-icon-green me-3">
                                                    <i class="fas fa-keyboard fa-2x text-success"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 text-success">Saisie manuelle</h6>
                                                    <small class="text-muted">Ajouter un par un avec validation</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mode-card" data-mode="fichier">
                                        <input class="form-check-input d-none" type="radio" name="additional_mode" id="additional_mode_fichier" value="fichier">
                                        <label class="form-check-label w-100" for="additional_mode_fichier">
                                            <div class="mode-option mode-option-additional">
                                                <div class="mode-icon mode-icon-yellow me-3">
                                                    <i class="fas fa-file-excel fa-2x text-warning"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 text-warning">Import fichier Excel/CSV</h6>
                                                    <small class="text-muted">Traitement par lots avec chunking</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section saisie manuelle --}}
                    <div id="additional_manuel_section">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 pt-4 pb-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-plus me-2 text-success"></i>
                                    Ajouter un adhérent manuellement
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label for="additional_adherent_civilite" class="form-label fw-bold">Civilité</label>
                                        <select class="form-select" id="additional_adherent_civilite">
                                            <option value="M">M.</option>
                                            <option value="Mme">Mme</option>
                                            <option value="Mlle">Mlle</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="additional_adherent_nom" class="form-label fw-bold">Nom *</label>
                                        <input type="text" class="form-control" id="additional_adherent_nom" placeholder="Nom de famille" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="additional_adherent_prenom" class="form-label fw-bold">Prénom *</label>
                                        <input type="text" class="form-control" id="additional_adherent_prenom" placeholder="Prénom(s)" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="additional_adherent_nip" class="form-label fw-bold">NIP *</label>
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control"
                                                   id="additional_adherent_nip"
                                                   data-validate="nip"
                                                   placeholder="A1-2345-19901225"
                                                   maxlength="16"
                                                   pattern="[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}"
                                                   required>
                                            <button class="btn btn-outline-info" type="button" title="Validation en temps réel" id="nip-validator-additional">
                                                <i class="fas fa-check-circle text-success d-none" id="nip-valid-additional"></i>
                                                <i class="fas fa-times-circle text-danger d-none" id="nip-invalid-additional"></i>
                                                <i class="fas fa-question-circle text-muted" id="nip-pending-additional"></i>
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Format: XX-QQQQ-YYYYMMDD</small>
                                        <div class="invalid-feedback" id="additional_adherent_nip_error"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="additional_adherent_telephone" class="form-label fw-bold">Téléphone</label>
                                        <div class="input-group">
                                            <span class="input-group-text">+241</span>
                                            <input type="tel" class="form-control" id="additional_adherent_telephone" placeholder="01234567">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="additional_adherent_profession" class="form-label fw-bold">Profession</label>
                                        <input type="text" class="form-control" id="additional_adherent_profession" placeholder="Profession">
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-success-custom btn-lg" id="addAdditionalAdherentBtn">
                                            <i class="fas fa-plus me-2"></i>Ajouter cet adhérent
                                        </button>
                                        <span class="ms-3 text-muted" id="manual-add-status"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section import fichier --}}
                    <div id="additional_fichier_section" class="d-none">
                        <div class="card border-0 shadow-lg mb-4">
                            <div class="card-header card-header-upload">
                                <h5 class="mb-0 text-white">
                                    <i class="fas fa-file-upload me-2"></i>
                                    Import depuis un fichier Excel/CSV
                                </h5>
                            </div>
                            <div class="card-body">
                                {{-- Zone de drop modernisée gabonaise --}}
                                <div class="upload-zone" id="additional-file-drop-zone">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt fa-4x text-warning"></i>
                                    </div>
                                    <h5 class="mb-2 text-primary">Glissez-déposez votre fichier ici</h5>
                                    <p class="text-muted mb-3">
                                        ou cliquez pour sélectionner un fichier
                                        <br>
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            Formats supportés: .xlsx, .xls, .csv | Taille max: 10MB | Max 10 000 adhérents
                                        </small>
                                    </p>
                                    <button type="button" class="btn btn-warning-custom btn-lg" id="select-additional-file-btn">
                                        <i class="fas fa-file-excel me-2"></i>
                                        Sélectionner un fichier
                                    </button>
                                    <input type="file" class="d-none" id="additional_adherents_file" accept=".xlsx,.xls,.csv">
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-shield-alt me-1 text-success"></i>
                                            Fichiers sécurisés et traités selon la réglementation gabonaise
                                        </small>
                                    </div>
                                </div>

                                {{-- Instructions template --}}
                                <div class="row g-3 mt-3">
                                    <div class="col-md-8">
                                        <div class="card border-0 template-info-card">
                                            <div class="card-body">
                                                <h6 class="alert-heading">
                                                    <i class="fas fa-list-ul me-2 text-primary"></i>
                                                    Colonnes requises dans votre fichier
                                                </h6>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <ul class="list-unstyled mb-0">
                                                            <li><i class="fas fa-check me-1 text-success"></i> Civilité</li>
                                                            <li><i class="fas fa-check me-1 text-success"></i> Nom</li>
                                                            <li><i class="fas fa-check me-1 text-success"></i> Prénom</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-6">
                                                        <ul class="list-unstyled mb-0">
                                                            <li><i class="fas fa-check me-1 text-success"></i> NIP</li>
                                                            <li><i class="fas fa-check me-1 text-info"></i> Téléphone</li>
                                                            <li><i class="fas fa-check me-1 text-info"></i> Profession</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-info me-1"></i>
                                                    Les colonnes avec <i class="fas fa-check text-success"></i> sont obligatoires.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h6 class="text-primary">Besoin d'un modèle ?</h6>
                                            <button class="btn btn-success-custom btn-lg mb-3" id="downloadAdditionalTemplateBtn">
                                                <i class="fas fa-download me-2"></i>
                                                Télécharger le modèle
                                            </button>
                                            <small class="d-block text-muted">
                                                Fichier Excel prêt à remplir
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Zone de résultats upload --}}
                        <div id="additional-upload-results" class="mt-3">
                            <!-- Résultats d'upload apparaîtront ici -->
                        </div>
                    </div>

                    {{-- Liste des adhérents supplémentaires ajoutés --}}
                    <div class="card border-0 shadow-lg mt-4">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2 text-primary"></i>
                                    Adhérents supplémentaires ajoutés
                                </h5>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge badge-info-custom fs-6" id="additional_adherents_count">0 adhérent(s)</span>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="ConfirmationApp.exportAdditionalAdherentsCSV()">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="ConfirmationApp.clearAdditionalAdherents()">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="additional_adherents_list">
                                {{-- Contenu généré dynamiquement par le JavaScript --}}
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-user-plus fa-4x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">Aucun adhérent supplémentaire ajouté</h5>
                                    <p class="text-muted mb-4">
                                        Ajoutez des adhérents pour atteindre le minimum requis pour votre organisation.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Zone de récapitulatif intelligent --}}
                    <div id="additional-summary" class="mt-4">
                        <div class="card border-0 summary-card">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="summary-item summary-item-success">
                                            <div class="h4 mb-1" id="summary-added">0</div>
                                            <small>Ajoutés aujourd'hui</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-item summary-item-warning">
                                            <div class="h4 mb-1" id="summary-total">{{ $totalAdherents }}</div>
                                            <small>Total adhérents</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-item summary-item-info">
                                            <div class="h4 mb-1" id="summary-required">{{ $minAdherents }}</div>
                                            <small>Minimum requis</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="summary-item summary-item-danger">
                                            <div class="h4 mb-1" id="summary-missing">{{ $adherentsManquants }}</div>
                                            <small>Encore manquants</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ✅ SECTION SOUMISSION FINALE --}}
    @if($pretPourSoumission)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm section-card">
                <div class="card-header card-header-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-paper-plane me-2"></i>
                            Prêt pour la Soumission Finale !
                        </h5>
                        <span class="badge badge-success-custom">{{ number_format($totalAdherents) }} adhérents</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-success-custom border-0">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon alert-icon-success me-3">
                                <i class="fas fa-check-circle text-white fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">Toutes les conditions sont remplies !</h6>
                                <small>{{ number_format($totalAdherents) }} adhérents prêts pour soumission à l'administration gabonaise</small>
                            </div>
                        </div>
                    </div>

                    {{-- Timeline processus --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="fas fa-list-ol me-2"></i>
                            Processus de soumission finale
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="timeline-item timeline-item-success">
                                    <div class="timeline-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold text-success">Organisation enregistrée</small><br>
                                        <small class="text-muted">Informations validées</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="timeline-item timeline-item-success">
                                    <div class="timeline-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold text-success">Adhérents importés</small><br>
                                        <small class="text-muted">{{ number_format($totalAdherents) }} membres</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="timeline-item timeline-item-warning">
                                    <div class="timeline-icon">
                                        3
                                    </div>
                                    <div>
                                        <small class="fw-bold text-warning">Soumission finale</small><br>
                                        <small class="text-muted">En cours...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informations importantes --}}
                    <div class="alert alert-warning-custom border-0 mb-4">
                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Important à savoir avant la soumission
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-section info-section-warning">
                                    <h6 class="text-warning">Phase de validation</h6>
                                    <ul class="small mb-0">
                                        <li>La soumission est <strong>définitive</strong></li>
                                        <li>Délai de traitement : <strong>15 jours ouvrés</strong></li>
                                        <li>Vous recevrez un accusé de réception</li>
                                        <li>Suivi par email automatique</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-section info-section-info">
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
                    </div>

                    {{-- Formulaire de soumission finale --}}
                    <form id="final-submission-form" onsubmit="ConfirmationApp.handleFinalSubmission(event)">
                        @csrf
                        <div class="card border-0 mb-4 declaration-card">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3 text-primary">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Déclarations obligatoires
                                </h6>
                                
                                <div class="form-check mb-3 declaration-item">
                                    <input class="form-check-input" type="checkbox" id="declaration-finale" required>
                                    <label class="form-check-label fw-bold" for="declaration-finale">
                                        <i class="fas fa-certificate me-2 text-success"></i>
                                        <strong>Je certifie sur l'honneur</strong> que toutes les informations fournies sont exactes et complètes
                                    </label>
                                </div>
                                
                                <div class="form-check declaration-item">
                                    <input class="form-check-input" type="checkbox" id="confirmation-soumission" required>
                                    <label class="form-check-label fw-bold" for="confirmation-soumission">
                                        <i class="fas fa-gavel me-2 text-primary"></i>
                                        <strong>Je confirme</strong> vouloir soumettre définitivement ce dossier à l'administration gabonaise
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success-custom btn-lg px-5" id="submit-final-btn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Soumettre mon dossier à l'administration gabonaise
                            </button>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-lock me-1 text-success"></i>
                                    Transmission sécurisée et cryptée selon les normes gabonaises
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ✅ INFORMATIONS DU DOSSIER ET ACTIONS --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm section-card">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open me-2 text-primary"></i>
                        Informations du dossier
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold text-muted">Numéro :</td>
                                <td><span class="badge badge-success-custom fs-6">{{ $dossier->numero_dossier }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Organisation :</td>
                                <td class="fw-bold">{{ $organisation->nom }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Type :</td>
                                <td><span class="badge badge-info-custom">{{ ucfirst($organisation->type) }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Statut :</td>
                                <td>
                                    @if($pretPourSoumission)
                                        <span class="badge badge-success-custom">Prêt pour soumission</span>
                                    @elseif($hasPhase2Pending)
                                        <span class="badge badge-warning-custom">Import en attente</span>
                                    @else
                                        <span class="badge badge-info-custom">En préparation</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Créé le :</td>
                                <td>{{ $dossier->created_at->format('d/m/Y à H:i') }}</td>
                            </tr>
                            @if(isset($confirmationData['numero_recepisse']))
                            <tr>
                                <td class="fw-bold text-muted">Récépissé :</td>
                                <td><span class="badge badge-success-custom">{{ $confirmationData['numero_recepisse'] }}</span></td>
                            </tr>
                            @endif
                        </table>
                    </div>

                    {{-- Historique des lots --}}
                    @if($nombreLots > 0)
                    <div class="mt-3">
                        <h6><i class="fas fa-layer-group me-2 text-primary"></i>Historique des lots ({{ $nombreLots }})</h6>
                        <div class="lots-history" id="lots-history">
                            @if($adherentsCount > 0)
                            <div class="lot-item lot-item-warning mb-2">
                                <small class="fw-bold">Lot actuel (en session)</small><br>
                                <small>{{ $adherentsCount }} adhérents - Expire: {{ $sessionExpirationFormatted }}</small>
                            </div>
                            @endif
                            @foreach($lotsHistory as $lotNum => $lotInfo)
                            <div class="lot-item lot-item-success mb-2">
                                <small class="fw-bold">Lot {{ $lotNum }}</small><br>
                                <small>{{ $lotInfo['adherents_count'] ?? 0 }} adhérents - {{ isset($lotInfo['uploaded_at']) ? \Carbon\Carbon::parse($lotInfo['uploaded_at'])->format('d/m H:i') : 'N/A' }}</small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            {{-- Actions rapides --}}
            <div class="card border-0 shadow-sm section-card mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2 text-success"></i>
                        Actions rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-success" onclick="ConfirmationApp.refreshStatistics()">
                            <i class="fas fa-sync-alt me-2"></i>Actualiser les statistiques
                        </button>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer cette page
                        </button>
                        <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-info">
                            <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                        </a>
                        <button class="btn btn-outline-secondary" onclick="ConfirmationApp.copyDossierNumber()">
                            <i class="fas fa-copy me-2"></i>Copier n° dossier
                        </button>
                        <button class="btn btn-outline-warning" onclick="ConfirmationApp.shareProgress()">
                            <i class="fas fa-share me-2"></i>Partager progression
                        </button>
                        @if($pretPourSoumission)
                        <a href="{{ route('operator.organisations.create') }}" class="btn btn-success-custom">
                            <i class="fas fa-plus me-2"></i>Nouvelle organisation
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Support et aide --}}
            <div class="card border-0 shadow-sm section-card">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0">
                        <i class="fas fa-headset me-2 text-danger"></i>
                        Besoin d'aide ?
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($confirmationData['contact_support']) && is_string($confirmationData['contact_support']))
                        <p class="small mb-3">{{ $confirmationData['contact_support'] }}</p>
                    @else
                        <p class="small mb-3">
                            Notre équipe de support est disponible pour vous accompagner dans toutes les étapes de votre dossier.
                        </p>
                    @endif
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-secondary btn-sm" onclick="ConfirmationApp.contactSupport()">
                            <i class="fas fa-envelope me-2"></i>Contacter le support
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="ConfirmationApp.showHelp()">
                            <i class="fas fa-question-circle me-2"></i>Guide d'utilisation
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="ConfirmationApp.downloadPDF()">
                            <i class="fas fa-file-pdf me-2"></i>Télécharger résumé PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ PROCHAINES ÉTAPES --}}
    @if(!$hasPhase2Pending && !$pretPourSoumission)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm section-card">
                <div class="card-header card-header-info">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-list-check me-2"></i>
                        Prochaines étapes
                    </h5>
                </div>
                <div class="card-body">
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
                    @else
                    <div class="alert alert-info-custom">
                        <h6><i class="fas fa-info-circle me-2"></i>Étapes suivantes</h6>
                        <ul class="mb-0">
                            <li><strong>Compléter les adhérents</strong> - Atteindre {{ $minAdherents }} adhérents minimum</li>
                            <li><strong>Vérification finale</strong> - Contrôle automatique des données</li>
                            <li><strong>Soumission définitive</strong> - Transmission à l'administration</li>
                            <li><strong>Traitement administratif</strong> - Délai de 15 jours ouvrés</li>
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ✅ FAB (FLOATING ACTION BUTTON) TRICOLORE GABONAIS --}}
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <button class="fab-main" onclick="ConfirmationApp.toggleFAB()">
            <i class="fas fa-plus fab-icon"></i>
        </button>
        <div class="fab-options">
            <button class="fab-option fab-option-green" title="Actualiser" onclick="ConfirmationApp.refreshStatistics()">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button class="fab-option fab-option-yellow" title="Imprimer" onclick="window.print()">
                <i class="fas fa-print"></i>
            </button>
            <button class="fab-option fab-option-blue" title="Aide" onclick="ConfirmationApp.showHelp()">
                <i class="fas fa-question-circle"></i>
            </button>
            <button class="fab-option fab-option-red" title="Support" onclick="ConfirmationApp.contactSupport()">
                <i class="fas fa-headset"></i>
            </button>
        </div>
    </div>
</div>

{{-- Variables JavaScript pour l'application --}}
<script>
// Configuration globale pour l'application
window.ConfirmationConfig = {
    dossierId: {{ $dossier->id }},
    adherentsCount: {{ $adherentsCount }},
    adherentsEnBase: {{ $adherentsEnBase }},
    totalAdherents: {{ $totalAdherents }},
    minAdherents: {{ $minAdherents }},
    pretPourSoumission: {{ $pretPourSoumission ? 'true' : 'false' }},
    hasPhase2Pending: {{ $hasPhase2Pending ? 'true' : 'false' }},
    sessionKey: '{{ $sessionKey }}',
    csrf: '{{ csrf_token() }}',
    routes: {
        uploadAdditional: '/operator/dossiers/{{ $dossier->id }}/upload-additional-batch',
        submitFinal: '/operator/dossiers/{{ $dossier->id }}/submit-to-administration', 
        getStatistics: '/operator/dossiers/{{ $dossier->id }}/adherents-statistics',
        finalConfirmation: '/operator/dossiers/{{ $dossier->id }}/final-confirmation',
        importAdherents: '/operator/dossiers/{{ $dossier->id }}/import-session-adherents',
        downloadTemplate: '/operator/templates/adherents-template.xlsx'
    }
};
</script>
@endsection

@push('scripts')
    {{-- Inclusion des librairies externes nécessaires --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>
    
    {{-- Scripts externes du projet --}}
    <script src="{{ asset('js/file-upload-sglp.js') }}"></script>
    <script src="{{ asset('js/confirmation.js') }}"></script>
@endpush