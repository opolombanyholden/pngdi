@extends('layouts.admin')

@section('title', 'Dossiers En Attente')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise jaune/orange pour "En Attente" -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-clock me-2"></i>
                                Dossiers En Attente de Traitement
                            </h2>
                            <p class="mb-0 opacity-90">Assignez les dossiers reçus aux agents pour traitement</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-dark fs-6 me-3">
                                {{ $totalEnAttente ?? 0 }} dossiers
                            </span>
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
                            <h3 class="mb-1">{{ $totalEnAttente ?? 8 }}</h3>
                            <p class="mb-0 small">En Attente</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: 80%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $prioriteHaute ?? 3 }}</h3>
                            <p class="mb-0 small opacity-90">Priorité Haute</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 40%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ round($delaiMoyen ?? 0) }}j</h3>
                            <p class="mb-0 small opacity-90">Délai Moyen</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-stopwatch fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $agents->count() ?? 5 }}</h3>
                            <p class="mb-0 small opacity-90">Agents Disponibles</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 85%"></div>
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
                                <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher un dossier..." id="searchInput" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterType">
                                <option value="">Tous les types</option>
                                <option value="association" {{ request('type') == 'association' ? 'selected' : '' }}>Association</option>
                                <option value="ong" {{ request('type') == 'ong' ? 'selected' : '' }}>ONG</option>
                                <option value="parti_politique" {{ request('type') == 'parti_politique' ? 'selected' : '' }}>Parti Politique</option>
                                <option value="confession_religieuse" {{ request('type') == 'confession_religieuse' ? 'selected' : '' }}>Confession Religieuse</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterPriorite">
                                <option value="">Toutes priorités</option>
                                <option value="haute" {{ request('priorite') == 'haute' ? 'selected' : '' }}>Haute</option>
                                <option value="moyenne" {{ request('priorite') == 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                                <option value="normale" {{ request('priorite') == 'normale' ? 'selected' : '' }}>Normale</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterAgent">
                                <option value="">Agent cible</option>
                                @if(isset($agents))
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-warning" onclick="assignerSelection()">
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
                                <button onclick="assignerSelection()" class="btn btn-warning" disabled id="btnAssigner">
                                    <i class="fas fa-user-plus me-1"></i>Assigner Agent
                                </button>
                                <button onclick="modifierPriorite()" class="btn btn-info" disabled id="btnPriorite">
                                    <i class="fas fa-flag me-1"></i>Changer Priorité
                                </button>
                                <button onclick="supprimerSelection()" class="btn btn-outline-danger" disabled id="btnSupprimer">
                                    <i class="fas fa-trash me-1"></i>Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Dossiers En Attente -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-folder me-2" style="color: #ffcd00;"></i>
                            Dossiers En Attente d'Assignation
                        </h5>
                        <span class="badge bg-warning text-dark">{{ $totalEnAttente ?? 8 }} dossiers</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($dossiersEnAttente) && count($dossiersEnAttente) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">
                                            <input type="checkbox" class="form-check-input" id="selectAllTable">
                                        </th>
                                        <th class="border-0">Dossier</th>
                                        <th class="border-0">Organisation</th>
                                        <th class="border-0">Type</th>
                                        <th class="border-0">Priorité</th>
                                        <th class="border-0">Temps d'Attente</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dossiersEnAttente ?? [] as $dossier)
                                    <tr class="dossier-row">
                                        <td>
                                            <input type="checkbox" class="form-check-input dossier-checkbox" value="{{ $dossier->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="dossier-icon me-3">
                                                    <i class="fas fa-folder fa-2x" style="color: #ffcd00;"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $dossier->numero_dossier }}</h6>
                                                    <small class="text-muted">{{ ucfirst($dossier->type_operation) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $dossier->organisation->nom ?? 'Organisation' }}</strong>
                                                @if($dossier->organisation->sigle ?? null)
                                                    <br><small class="text-muted">({{ $dossier->organisation->sigle }})</small>
                                                @endif
                                                <br><small class="text-info">{{ ucfirst($dossier->organisation->type ?? 'N/A') }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">
                                                {{ ucfirst($dossier->organisation->type ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if(isset($dossier->priorite))
                                                @php
                                                    $prioriteColor = 'secondary';
                                                    if($dossier->priorite == 'haute') {
                                                        $prioriteColor = 'danger';
                                                    } elseif($dossier->priorite == 'moyenne') {
                                                        $prioriteColor = 'warning';
                                                    } elseif($dossier->priorite == 'normale') {
                                                        $prioriteColor = 'success';
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $prioriteColor }}">
                                                    {{ ucfirst($dossier->priorite) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Normale</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $joursAttente = $dossier->jours_attente ?? now()->diffInDays($dossier->created_at);
                                                $couleur = $joursAttente > 7 ? 'danger' : ($joursAttente > 3 ? 'warning' : 'success');
                                            @endphp
                                            <div class="text-center">
                                                <div class="text-{{ $couleur }}">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>{{ $joursAttente }} jour{{ $joursAttente > 1 ? 's' : '' }}</strong>
                                                </div>
                                                <small class="text-muted">{{ $joursAttente > 7 ? 'Urgent' : ($joursAttente > 3 ? 'À traiter' : 'Récent') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="voirDossier({{ $dossier->id }})" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="assignerDossier({{ $dossier->id }})" title="Assigner">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="Plus d'actions">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="modifierPrioriteDossier({{ $dossier->id }})">
                                                            <i class="fas fa-flag me-2"></i>Changer priorité
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="ajouterCommentaire({{ $dossier->id }})">
                                                            <i class="fas fa-comment me-2"></i>Ajouter commentaire
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="voirHistorique({{ $dossier->id }})">
                                                            <i class="fas fa-history me-2"></i>Voir historique
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="supprimerDossier({{ $dossier->id }})">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="mb-4">
                                                <i class="fas fa-folder fa-5x text-muted opacity-50"></i>
                                            </div>
                                            <h4 class="text-muted mb-3">Aucun dossier en attente</h4>
                                            <p class="text-muted mb-4">Tous les dossiers ont été assignés ou traités.</p>
                                            <div class="d-flex justify-content-center gap-3">
                                                <button class="btn btn-outline-primary btn-lg" onclick="refreshDossiers()">
                                                    <i class="fas fa-sync me-2"></i>Actualiser
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(isset($dossiersEnAttente) && method_exists($dossiersEnAttente, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $dossiersEnAttente->links() }}
                            </div>
                        @endif
                    @else
                        <!-- État vide avec style gabonais -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-folder fa-5x text-muted opacity-50"></i>
                            </div>
                            <h4 class="text-muted mb-3">Aucun dossier en attente</h4>
                            <p class="text-muted mb-4">Tous les dossiers ont été assignés ou sont en cours de traitement.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('admin.workflow.en-cours') }}" class="btn btn-success btn-lg">
                                    <i class="fas fa-cogs me-2"></i>Voir dossiers en cours
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
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Assigner agent" onclick="assignerSelection()">
                <i class="fas fa-user-plus"></i>
            </button>
            <button class="fab-option" style="background: #009e3f;" title="Changer priorité" onclick="modifierPriorite()">
                <i class="fas fa-flag"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Exporter données" onclick="exporterDossiers()">
                <i class="fas fa-download"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal Assignation -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Assigner le Dossier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignForm">
                    <input type="hidden" id="assignDossierId">
                    <div class="mb-3">
                        <label class="form-label">Agent assigné <span class="text-danger">*</span></label>
                        <select id="agentSelect" class="form-select" required>
                            <option value="">Sélectionner un agent</option>
                            @if(isset($agents))
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }} - {{ $agent->email }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priorité</label>
                        <select id="prioriteSelect" class="form-select">
                            <option value="normale">Normale</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="haute">Haute</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire d'assignation</label>
                        <textarea id="commentaireAssign" class="form-control" rows="3" placeholder="Instructions ou commentaires pour l'agent..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="confirmerAssignation()">
                    <i class="fas fa-user-plus me-2"></i>Assigner le Dossier
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles gabonais identiques à en-cours.blade.php */
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

function assignerDossier(dossierId) {
    currentDossierId = dossierId;
    document.getElementById('assignDossierId').value = dossierId;
    new bootstrap.Modal(document.getElementById('assignModal')).show();
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
    const btnAssigner = document.getElementById('btnAssigner');
    const btnPriorite = document.getElementById('btnPriorite');
    const btnSupprimer = document.getElementById('btnSupprimer');
    
    if (count > 0) {
        btnAssigner.disabled = false;
        btnPriorite.disabled = false;
        btnSupprimer.disabled = false;
    } else {
        btnAssigner.disabled = true;
        btnPriorite.disabled = true;
        btnSupprimer.disabled = true;
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

// Soumission du formulaire d'assignation
function confirmerAssignation() {
    const dossierId = document.getElementById('assignDossierId').value;
    const agentId = document.getElementById('agentSelect').value;
    const priorite = document.getElementById('prioriteSelect').value;
    const commentaire = document.getElementById('commentaireAssign').value;
    
    if (!agentId) {
        alert('Veuillez sélectionner un agent');
        return;
    }
    
    fetch(`/admin/workflow/assign/${dossierId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            agent_id: agentId,
            priorite: priorite,
            commentaire: commentaire
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Dossier assigné avec succès');
            bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'assignation');
    });
}

// Actions en lot
function assignerSelection() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    // Utiliser le modal avec selection multiple
    currentDossierId = 'multiple';
    document.getElementById('assignDossierId').value = 'multiple';
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

function modifierPriorite() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    const nouvellePriorite = prompt('Nouvelle priorité (normale/moyenne/haute):');
    if (!nouvellePriorite) return;
    
    fetch('/admin/workflow/update-priority-multiple', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            dossiers: selectedDossiers,
            priorite: nouvellePriorite
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Priorité mise à jour pour ${data.updated_count} dossier(s)`);
            location.reload();
        } else {
            alert('Erreur lors de la mise à jour');
        }
    });
}

function supprimerSelection() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedDossiers.length} dossier(s) ?`)) {
        fetch('/admin/workflow/delete-multiple', {
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
                alert(`${data.deleted_count} dossier(s) supprimé(s) avec succès`);
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        });
    }
}

function exporterDossiers() {
    const params = new URLSearchParams();
    if (selectedDossiers.length > 0) {
        params.append('dossiers', selectedDossiers.join(','));
    }
    window.open(`/admin/workflow/export-en-attente?${params.toString()}`);
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
['filterType', 'filterPriorite', 'filterAgent'].forEach(filterId => {
    document.getElementById(filterId).addEventListener('change', function() {
        applyFilters();
    });
});

function applyFilters() {
    const type = document.getElementById('filterType').value;
    const priorite = document.getElementById('filterPriorite').value;
    const agent = document.getElementById('filterAgent').value;
    
    const rows = document.querySelectorAll('.dossier-row');
    
    rows.forEach(row => {
        let show = true;
        
        if (type && !row.textContent.toLowerCase().includes(type)) show = false;
        if (priorite && !row.textContent.toLowerCase().includes(priorite)) show = false;
        if (agent && !row.textContent.includes(agent)) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}
</script>
@endsection