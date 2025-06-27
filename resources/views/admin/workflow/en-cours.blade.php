@extends('layouts.admin')

@section('title', 'Dossiers En Cours')

@section('content')
<div class="container-fluid">
    <!-- Header avec statistiques inspiré du design opérateur -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-cogs me-2"></i>
                                Dossiers En Cours de Traitement
                            </h2>
                            <p class="mb-0 opacity-90">Gérez et validez les dossiers assignés aux agents</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button onclick="refreshDossiers()" class="btn btn-dark btn-lg">
                                <i class="fas fa-sync me-2"></i>
                                Actualiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Cards avec style gabonais -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $totalEnCours ?? 12 }}</h3>
                            <p class="mb-0 small">En Cours</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: 70%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $assignesAujourdhui ?? 5 }}</h3>
                            <p class="mb-0 small opacity-90">Assignés Aujourd'hui</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 45%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $tempsTraitementMoyen ?? '2.5j' }}</h3>
                            <p class="mb-0 small opacity-90">Temps Moyen</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-stopwatch fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 65%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $retardAlerte ?? 2 }}</h3>
                            <p class="mb-0 small opacity-90">En Retard</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 20%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher un dossier..." id="searchInput">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterAgent">
                                <option value="">Tous les agents</option>
                                @if(isset($agents))
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterType">
                                <option value="">Tous les types</option>
                                <option value="association">Association</option>
                                <option value="ong">ONG</option>
                                <option value="parti_politique">Parti Politique</option>
                                <option value="confession_religieuse">Confession Religieuse</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterPriorite">
                                <option value="">Toutes priorités</option>
                                <option value="urgent">Urgent</option>
                                <option value="normal">Normal</option>
                                <option value="faible">Faible</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-success" onclick="assignerDossiers()">
                                    <i class="fas fa-user-plus me-2"></i>Assigner
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="exporterDossiers()">
                                    <i class="fas fa-download me-2"></i>Exporter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions en lot -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" id="selectAll" class="form-check-input me-2">
                                <label for="selectAll" class="form-check-label me-3">Sélectionner tout</label>
                                <span id="selectedCount" class="badge bg-light text-dark">0 sélectionné(s)</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <button onclick="validerSelection()" class="btn btn-success" disabled id="btnValider">
                                    <i class="fas fa-check me-1"></i>Valider
                                </button>
                                <button onclick="rejeterSelection()" class="btn btn-danger" disabled id="btnRejeter">
                                    <i class="fas fa-times me-1"></i>Rejeter
                                </button>
                                <button onclick="reassignerSelection()" class="btn btn-warning" disabled id="btnReassigner">
                                    <i class="fas fa-user-plus me-1"></i>Réassigner
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Dossiers En Cours -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-folder-open me-2" style="color: #ffcd00;"></i>
                            Dossiers En Cours de Traitement
                        </h5>
                        <span class="badge bg-warning text-dark">{{ $totalEnCours ?? 12 }} dossiers</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($dossiers) && count($dossiers) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">
                                            <input type="checkbox" class="form-check-input" id="selectAllTable">
                                        </th>
                                        <th class="border-0">Dossier</th>
                                        <th class="border-0">Organisation</th>
                                        <th class="border-0">Agent</th>
                                        <th class="border-0">Étape</th>
                                        <th class="border-0">Temps Écoulé</th>
                                        <th class="border-0">Priorité</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dossiers as $dossier)
                                    <tr class="dossier-row">
                                        <td>
                                            <input type="checkbox" class="form-check-input dossier-checkbox" value="{{ $dossier->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="dossier-icon me-3">
                                                    <i class="fas fa-folder-open fa-2x" style="color: #ffcd00;"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $dossier->numero_dossier }}</h6>
                                                    <small class="text-muted">{{ $dossier->type_operation }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $dossier->organisation->nom ?? 'Organisation' }}</strong>
                                                @if($dossier->organisation->sigle)
                                                    <br><small class="text-muted">({{ $dossier->organisation->sigle }})</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($dossier->assigned_to)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <i class="fas fa-user-circle fa-2x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <small><strong>{{ $dossier->assignedAgent->name ?? 'Agent' }}</strong></small>
                                                        <br><small class="text-muted">{{ $dossier->assignedAgent->email ?? '' }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">Non assigné</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="step-indicator me-2">
                                                    <i class="fas fa-circle text-warning"></i>
                                                </div>
                                                <div>
                                                    <small><strong>{{ $dossier->currentStep->libelle ?? 'Validation en cours' }}</strong></small>
                                                    <br><small class="text-muted">{{ $dossier->workflowStepDetails ?? 'En cours de traitement' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $joursEcoules = $dossier->temps_ecoule_jours ?? now()->diffInDays($dossier->created_at);
                                                $couleur = $joursEcoules > 5 ? 'danger' : ($joursEcoules > 3 ? 'warning' : 'success');
                                            @endphp
                                            <div class="text-center">
                                                <div class="text-{{ $couleur }}">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>{{ $joursEcoules }} jour{{ $joursEcoules > 1 ? 's' : '' }}</strong>
                                                </div>
                                                <small class="text-muted">{{ $dossier->temps_restant ?? 'Dans les délais' }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if(isset($dossier->priorite))
                                                <span class="badge bg-{{ $dossier->priorite_color ?? 'success' }}">
                                                    {{ ucfirst($dossier->priorite) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Normale</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="voirDossier({{ $dossier->id }})" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="validerDossier({{ $dossier->id }})" title="Valider">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="rejeterDossier({{ $dossier->id }})" title="Rejeter">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="Plus d'actions">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="reassignerDossier({{ $dossier->id }})">
                                                            <i class="fas fa-user-plus me-2"></i>Réassigner
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="ajouterCommentaire({{ $dossier->id }})">
                                                            <i class="fas fa-comment me-2"></i>Commenter
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="voirHistorique({{ $dossier->id }})">
                                                            <i class="fas fa-history me-2"></i>Historique
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-warning" href="#" onclick="modifierPriorite({{ $dossier->id }})">
                                                            <i class="fas fa-flag me-2"></i>Changer priorité
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(isset($dossiers) && method_exists($dossiers, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $dossiers->links() }}
                            </div>
                        @endif
                    @else
                        <!-- État vide avec style gabonais -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-folder-open fa-5x text-muted opacity-50"></i>
                            </div>
                            <h4 class="text-muted mb-3">Aucun dossier en cours</h4>
                            <p class="text-muted mb-4">Tous les dossiers ont été traités ou sont en attente d'assignation.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('admin.workflow.en-attente') }}" class="btn btn-warning btn-lg">
                                    <i class="fas fa-clock me-2"></i>Voir dossiers en attente
                                </a>
                                <button class="btn btn-outline-primary btn-lg" onclick="refreshDossiers()">
                                    <i class="fas fa-sync me-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) tricolore -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-plus fab-icon"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #009e3f;" title="Valider sélection" onclick="validerSelection()">
                <i class="fas fa-check"></i>
            </button>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Assigner agent" onclick="assignerDossiers()">
                <i class="fas fa-user-plus"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Exporter données" onclick="exporterDossiers()">
                <i class="fas fa-download"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal Validation -->
<div class="modal fade" id="validationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check me-2"></i>Valider le Dossier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="validationForm">
                    <input type="hidden" id="validationDossierId">
                    <div class="mb-3">
                        <label class="form-label">Numéro d'enregistrement</label>
                        <input type="text" class="form-control" id="numeroEnregistrement" placeholder="Ex: REG-2025-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire de validation</label>
                        <textarea class="form-control" id="validationCommentaire" rows="3" placeholder="Commentaire de validation..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="confirmerValidation()">
                    <i class="fas fa-check me-2"></i>Valider le Dossier
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rejet -->
<div class="modal fade" id="rejetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times me-2"></i>Rejeter le Dossier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejetForm">
                    <input type="hidden" id="rejetDossierId">
                    <div class="mb-3">
                        <label class="form-label">Motif de rejet <span class="text-danger">*</span></label>
                        <select class="form-select" id="motifRejet" required>
                            <option value="">Sélectionnez un motif</option>
                            <option value="documents_incomplets">Documents incomplets</option>
                            <option value="informations_incorrectes">Informations incorrectes</option>
                            <option value="non_conforme">Non conforme aux règlements</option>
                            <option value="duplicate">Dossier en doublon</option>
                            <option value="autre">Autre motif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire détaillé <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejetCommentaire" rows="4" placeholder="Expliquez en détail les raisons du rejet..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" onclick="confirmerRejet()">
                    <i class="fas fa-times me-2"></i>Rejeter le Dossier
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles gabonais inspirés du design opérateur */
.stats-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.1);
}

.dossier-row {
    transition: background-color 0.2s ease;
}

.dossier-row:hover {
    background-color: rgba(255, 205, 0, 0.05);
}

.dossier-icon {
    width: 40px;
    text-align: center;
}

.avatar-sm {
    width: 30px;
    text-align: center;
}

.step-indicator {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* FAB Style gabonais */
.fab-container {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
}

.fab-menu {
    position: relative;
}

.fab-main {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.fab-main:hover {
    transform: scale(1.1);
}

.fab-icon {
    color: white;
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.fab-options {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.fab-menu.active .fab-options {
    opacity: 1;
    visibility: visible;
}

.fab-menu.active .fab-icon {
    transform: rotate(45deg);
}

.fab-option {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.fab-option:hover {
    transform: scale(1.1);
}

/* Animation d'entrée */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}
</style>

<script>
let selectedDossiers = [];
let currentDossierId = null;

// Toggle FAB Menu
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.classList.toggle('active');
}

// Fermer FAB en cliquant ailleurs
document.addEventListener('click', function(event) {
    const fabMenu = document.getElementById('fabMenu');
    if (!fabMenu.contains(event.target)) {
        fabMenu.classList.remove('active');
    }
});

// Fonctions des actions principales
function voirDossier(dossierId) {
    window.location.href = `/admin/dossiers/${dossierId}`;
}

function validerDossier(dossierId) {
    currentDossierId = dossierId;
    document.getElementById('validationDossierId').value = dossierId;
    new bootstrap.Modal(document.getElementById('validationModal')).show();
}

function rejeterDossier(dossierId) {
    currentDossierId = dossierId;
    document.getElementById('rejetDossierId').value = dossierId;
    new bootstrap.Modal(document.getElementById('rejetModal')).show();
}

function refreshDossiers() {
    location.reload();
}

// Gestion de la sélection multiple
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.dossier-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = `${count} sélectionné(s)`;
    
    // Activer/désactiver les boutons d'action
    const btnValider = document.getElementById('btnValider');
    const btnRejeter = document.getElementById('btnRejeter');
    const btnReassigner = document.getElementById('btnReassigner');
    
    if (count > 0) {
        btnValider.disabled = false;
        btnRejeter.disabled = false;
        btnReassigner.disabled = false;
    } else {
        btnValider.disabled = true;
        btnRejeter.disabled = true;
        btnReassigner.disabled = true;
    }
    
    selectedDossiers = Array.from(checkboxes).map(cb => cb.value);
}

// Event listeners pour la sélection
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.dossier-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.dossier-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    document.getElementById('selectAll').checked = this.checked;
    updateSelectedCount();
});

document.querySelectorAll('.dossier-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

// Soumission des formulaires
function confirmerValidation() {
    const dossierId = document.getElementById('validationDossierId').value;
    const numeroEnregistrement = document.getElementById('numeroEnregistrement').value;
    const commentaire = document.getElementById('validationCommentaire').value;
    
    fetch(`/admin/workflow/validate/${dossierId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            numero_enregistrement: numeroEnregistrement,
            commentaire: commentaire
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Dossier validé avec succès');
            bootstrap.Modal.getInstance(document.getElementById('validationModal')).hide();
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la validation');
    });
}

function confirmerRejet() {
    const dossierId = document.getElementById('rejetDossierId').value;
    const motif = document.getElementById('motifRejet').value;
    const commentaire = document.getElementById('rejetCommentaire').value;
    
    if (!motif || !commentaire) {
        alert('Veuillez remplir tous les champs obligatoires');
        return;
    }
    
    fetch(`/admin/workflow/reject/${dossierId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            motif: motif,
            commentaire: commentaire
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Dossier rejeté avec succès');
            bootstrap.Modal.getInstance(document.getElementById('rejetModal')).hide();
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors du rejet');
    });
}

// Actions en lot
function validerSelection() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir valider ${selectedDossiers.length} dossier(s) ?`)) {
        fetch('/admin/workflow/validate-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                dossiers: selectedDossiers
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${data.validated_count} dossier(s) validé(s) avec succès`);
                location.reload();
            } else {
                alert('Erreur lors de la validation');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la validation');
        });
    }
}

function rejeterSelection() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    const motif = prompt('Motif de rejet pour tous les dossiers sélectionnés:');
    if (!motif) return;
    
    if (confirm(`Êtes-vous sûr de vouloir rejeter ${selectedDossiers.length} dossier(s) ?`)) {
        fetch('/admin/workflow/reject-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                dossiers: selectedDossiers,
                motif: motif
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${data.rejected_count} dossier(s) rejeté(s) avec succès`);
                location.reload();
            } else {
                alert('Erreur lors du rejet');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du rejet');
        });
    }
}

function assignerDossiers() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    console.log('Assigner les dossiers:', selectedDossiers);
}

function reassignerSelection() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    console.log('Réassigner les dossiers:', selectedDossiers);
}

function exporterDossiers() {
    const params = new URLSearchParams();
    if (selectedDossiers.length > 0) {
        params.append('dossiers', selectedDossiers.join(','));
    }
    window.open(`/admin/workflow/export?${params.toString()}`);
}

// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.dossier-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filtres
['filterAgent', 'filterType', 'filterPriorite'].forEach(filterId => {
    document.getElementById(filterId).addEventListener('change', function() {
        applyFilters();
    });
});

function applyFilters() {
    const agent = document.getElementById('filterAgent').value;
    const type = document.getElementById('filterType').value;
    const priorite = document.getElementById('filterPriorite').value;
    
    const rows = document.querySelectorAll('.dossier-row');
    
    rows.forEach(row => {
        let show = true;
        
        if (agent && !row.textContent.includes(agent)) show = false;
        if (type && !row.textContent.toLowerCase().includes(type)) show = false;
        if (priorite && !row.textContent.toLowerCase().includes(priorite)) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}
</script>
@endsection