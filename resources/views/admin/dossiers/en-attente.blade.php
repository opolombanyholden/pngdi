{{-- resources/views/admin/dossiers/en-attente.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dossiers en Attente')

@section('content')
<div class="container-fluid">
    <!-- Header avec titre et actions -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-clock me-2" style="color: #ffcd00;"></i>
                Dossiers en Attente
            </h1>
            <p class="text-muted">Gestion des dossiers soumis en attente de traitement</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshPage()">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-download"></i> Exporter
                </button>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total en Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalEnAttente ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Priorité Haute
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $prioriteHaute ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Délai Moyen
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $delaiMoyen ?? 0 }} jours
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Agents Disponibles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $agents->count() ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres de recherche -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filtres de Recherche
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dossiers.en-attente') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search" class="form-label">Recherche</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nom, sigle, numéro..."
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="type" class="form-label">Type Organisation</label>
                            <select name="type" id="type" class="form-control">
                                <option value="">Tous types</option>
                                <option value="association" {{ request('type') === 'association' ? 'selected' : '' }}>
                                    Association
                                </option>
                                <option value="ong" {{ request('type') === 'ong' ? 'selected' : '' }}>
                                    ONG
                                </option>
                                <option value="parti_politique" {{ request('type') === 'parti_politique' ? 'selected' : '' }}>
                                    Parti Politique
                                </option>
                                <option value="confession_religieuse" {{ request('type') === 'confession_religieuse' ? 'selected' : '' }}>
                                    Confession Religieuse
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="priorite" class="form-label">Priorité</label>
                            <select name="priorite" id="priorite" class="form-control">
                                <option value="">Toutes priorités</option>
                                <option value="haute" {{ request('priorite') === 'haute' ? 'selected' : '' }}>
                                    Haute
                                </option>
                                <option value="normale" {{ request('priorite') === 'normale' ? 'selected' : '' }}>
                                    Normale
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="periode" class="form-label">Période de Soumission</label>
                            <select name="periode" id="periode" class="form-control">
                                <option value="">Toute période</option>
                                <option value="today" {{ request('periode') === 'today' ? 'selected' : '' }}>
                                    Aujourd'hui
                                </option>
                                <option value="week" {{ request('periode') === 'week' ? 'selected' : '' }}>
                                    Cette semaine
                                </option>
                                <option value="month" {{ request('periode') === 'month' ? 'selected' : '' }}>
                                    Ce mois
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des dossiers -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Liste des Dossiers en Attente
                @if(isset($dossiersEnAttente) && $dossiersEnAttente->total() > 0)
                    <span class="badge badge-warning ms-2">{{ $dossiersEnAttente->total() }}</span>
                @endif
            </h6>
            
            <!-- Actions groupées -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i> Actions
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="assignMultiple()">
                        <i class="fas fa-user-check"></i> Assigner en lot
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportSelection()">
                        <i class="fas fa-download"></i> Exporter sélection
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(isset($dossiersEnAttente) && $dossiersEnAttente->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dossiersTable">
                        <thead class="table-light">
                            <tr>
                                <th width="30">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th>Dossier</th>
                                <th>Organisation</th>
                                <th>Type</th>
                                <th>Date Soumission</th>
                                <th>Priorité</th>
                                <th>Délai</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossiersEnAttente as $dossier)
                            <tr data-dossier-id="{{ $dossier->id }}">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input dossier-checkbox" 
                                               type="checkbox" 
                                               value="{{ $dossier->id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="status-icon me-2">
                                            @if($dossier->priorite_calculee === 'haute')
                                                <i class="fas fa-exclamation-triangle text-danger" title="Priorité haute"></i>
                                            @else
                                                <i class="fas fa-clock text-warning" title="Priorité normale"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <strong>{{ $dossier->numero_dossier }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                Statut: <span class="badge badge-warning">{{ ucfirst($dossier->statut) }}</span>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $dossier->organisation->nom ?? 'N/A' }}</strong>
                                        @if($dossier->organisation->sigle ?? null)
                                            <br><small class="text-muted">({{ $dossier->organisation->sigle }})</small>
                                        @endif
                                        @if($dossier->organisation->prefecture ?? null)
                                            <br><small class="text-info">{{ $dossier->organisation->prefecture }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ ucfirst(str_replace('_', ' ', $dossier->organisation->type ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        {{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($dossier->created_at)->format('H:i') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @if($dossier->priorite_calculee === 'haute')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Haute
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">Normale</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $delai = \Carbon\Carbon::parse($dossier->created_at)->diffInDays(now());
                                    @endphp
                                    <div class="text-center">
                                        <span class="badge {{ $delai > 7 ? 'badge-danger' : ($delai > 3 ? 'badge-warning' : 'badge-success') }}">
                                            {{ $delai }} jour{{ $delai > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.dossiers.show', $dossier->id) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-success btn-sm" 
                                                onclick="assignerDossier({{ $dossier->id }})"
                                                title="Assigner">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm" 
                                                onclick="addComment({{ $dossier->id }})"
                                                title="Ajouter commentaire">
                                            <i class="fas fa-comment"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Affichage de {{ $dossiersEnAttente->firstItem() ?? 0 }} à {{ $dossiersEnAttente->lastItem() ?? 0 }} 
                        sur {{ $dossiersEnAttente->total() }} résultats
                    </div>
                    <div>
                        {{ $dossiersEnAttente->links() }}
                    </div>
                </div>
            @else
                <!-- État vide -->
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucun dossier en attente</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'type', 'priorite', 'periode']))
                            Aucun dossier ne correspond aux critères de filtrage.
                            <br>
                            <a href="{{ route('admin.dossiers.en-attente') }}" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fas fa-times"></i> Effacer les filtres
                            </a>
                        @else
                            Tous les dossiers ont été traités ou aucun nouveau dossier n'a été soumis.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal d'assignation -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assigner le Dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="agent_id" class="form-label">Sélectionner un Agent</label>
                        <select name="agent_id" id="agent_id" class="form-control" required>
                            <option value="">-- Choisir un agent --</option>
                            @if(isset($agents))
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">
                                        {{ $agent->name }} - {{ $agent->email }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                        <textarea name="commentaire" 
                                  id="commentaire" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Instructions ou commentaires pour l'agent..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Assigner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de commentaire -->
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Commentaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="commentForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="comment_text" class="form-label">Commentaire</label>
                        <textarea name="comment_text" 
                                  id="comment_text" 
                                  class="form-control" 
                                  rows="4"
                                  placeholder="Votre commentaire..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Variables globales
let currentDossierId = null;

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
    initializeModals();
    initializeFilters();
});

// Initialisation du tableau avec recherche en temps réel
function initializeDataTable() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                document.getElementById('filterForm').submit();
            }, 500);
        });
    }
}

// Initialisation des modales
function initializeModals() {
    // Modal d'assignation
    const assignForm = document.getElementById('assignForm');
    if (assignForm) {
        assignForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAssignation();
        });
    }

    // Modal de commentaire
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitComment();
        });
    }
}

// Initialisation des filtres
function initializeFilters() {
    const selects = ['type', 'priorite', 'periode'];
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
}

// Fonction pour assigner un dossier
function assignerDossier(dossierId) {
    currentDossierId = dossierId;
    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
}

// Soumission de l'assignation
function submitAssignation() {
    const formData = new FormData(document.getElementById('assignForm'));
    
    fetch(`/admin/dossiers/${currentDossierId}/assign`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Dossier assigné avec succès');
            bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
            location.reload();
        } else {
            showAlert('error', data.message || 'Erreur lors de l\'assignation');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('error', 'Erreur technique lors de l\'assignation');
    });
}

// Fonction pour ajouter un commentaire
function addComment(dossierId) {
    currentDossierId = dossierId;
    document.getElementById('comment_text').value = '';
    const modal = new bootstrap.Modal(document.getElementById('commentModal'));
    modal.show();
}

// Soumission du commentaire
function submitComment() {
    const formData = new FormData(document.getElementById('commentForm'));
    
    fetch(`/admin/dossiers/${currentDossierId}/comment`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Commentaire ajouté avec succès');
            bootstrap.Modal.getInstance(document.getElementById('commentModal')).hide();
        } else {
            showAlert('error', data.message || 'Erreur lors de l\'ajout du commentaire');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('error', 'Erreur technique lors de l\'ajout du commentaire');
    });
}

// Sélection multiple
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.dossier-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        });
    });
});

// Fonctions utilitaires
function refreshPage() {
    location.reload();
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function exportSelection() {
    const selectedIds = Array.from(document.querySelectorAll('.dossier-checkbox:checked')).map(cb => cb.value);
    if (selectedIds.length === 0) {
        showAlert('error', 'Veuillez sélectionner au moins un dossier');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.exports.dossiers") }}';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);
    
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'dossier_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endpush

@push('styles')
<style>
.status-icon {
    width: 20px;
    text-align: center;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-size: 0.75em;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
@endpush