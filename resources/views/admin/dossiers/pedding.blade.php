@extends('layouts.admin')

@section('title', 'Dossiers en Attente')

@section('breadcrumb')
<li class="breadcrumb-item">Dossiers</li>
<li class="breadcrumb-item active">En Attente</li>
@endsection

@section('content')
<div class="pending-dossiers">
    <!-- En-tête avec statistiques -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gabon-blue fw-bold">
                <i class="fas fa-clock text-warning me-2"></i>
                Dossiers en Attente
            </h1>
            <p class="text-muted mb-0">Gestion des dossiers nécessitant une validation</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-gabon-green btn-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <div class="dropdown">
                <button class="btn btn-gabon-blue btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-filter"></i> Filtres
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?sort=oldest">Plus anciens</a></li>
                    <li><a class="dropdown-item" href="?sort=newest">Plus récents</a></li>
                    <li><a class="dropdown-item" href="?type=association">Associations</a></li>
                    <li><a class="dropdown-item" href="?type=ong">ONG</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card gabon-yellow">
                <div class="stats-card-body">
                    <div class="stats-content">
                        <div class="stats-number">{{ $dossiers->total() }}</div>
                        <div class="stats-label">Total en Attente</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card gabon-red">
                <div class="stats-content">
                    <div class="stats-number">{{ $dossiers->where('created_at', '<', now()->subDays(7))->count() }}</div>
                    <div class="stats-label">Plus de 7 jours</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card gabon-blue">
                <div class="stats-content">
                    <div class="stats-number">{{ $dossiers->whereNull('agent_id')->count() }}</div>
                    <div class="stats-label">Non Assignés</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card gabon-green">
                <div class="stats-content">
                    <div class="stats-number">{{ $dossiers->whereNotNull('agent_id')->count() }}</div>
                    <div class="stats-label">Assignés</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des dossiers -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Liste des Dossiers en Attente
            </h5>
        </div>
        <div class="card-body p-0">
            @if($dossiers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Organisation</th>
                            <th>Type</th>
                            <th>Opérateur</th>
                            <th>Date Soumission</th>
                            <th>Attente</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dossiers as $dossier)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="org-icon me-3">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $dossier->nom ?? $dossier->name }}</div>
                                        <small class="text-muted">ID: #{{ $dossier->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $dossier->type)) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-sm me-2">
                                        {{ substr($dossier->user->name ?? 'N/A', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $dossier->user->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $dossier->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $dossier->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $dossier->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                @php
                                    $jours = $dossier->created_at->diffInDays(now());
                                    $color = $jours > 7 ? 'danger' : ($jours > 3 ? 'warning' : 'success');
                                @endphp
                                <span class="badge bg-{{ $color }}">
                                    {{ $jours }} jour{{ $jours > 1 ? 's' : '' }}
                                </span>
                            </td>
                            <td>
                                @if($dossier->agent_id)
                                    <span class="badge bg-primary">
                                        <i class="fas fa-user-check me-1"></i>
                                        Assigné
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>
                                        En attente
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.dossiers.show', $dossier->id) }}" 
                                       class="btn btn-outline-primary" 
                                       data-bs-toggle="tooltip" 
                                       title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$dossier->agent_id)
                                    <button class="btn btn-outline-success" 
                                            onclick="assignToMe({{ $dossier->id }})"
                                            data-bs-toggle="tooltip" 
                                            title="M'assigner ce dossier">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                    @endif
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('admin.dossiers.show', $dossier->id) }}">
                                                <i class="fas fa-eye me-2"></i> Voir détails
                                            </a></li>
                                            @if(!$dossier->agent_id)
                                            <li><a class="dropdown-item" href="#" onclick="assignToMe({{ $dossier->id }})">
                                                <i class="fas fa-user-check me-2"></i> M'assigner
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="assignToOther({{ $dossier->id }})">
                                                <i class="fas fa-users me-2"></i> Assigner à un autre
                                            </a></li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="markUrgent({{ $dossier->id }})">
                                                <i class="fas fa-exclamation-triangle me-2"></i> Marquer urgent
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
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Affichage de {{ $dossiers->firstItem() }} à {{ $dossiers->lastItem() }} 
                        sur {{ $dossiers->total() }} dossiers
                    </div>
                    <div>
                        {{ $dossiers->links() }}
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4 class="text-muted">Aucun dossier en attente</h4>
                <p class="text-muted">Tous les dossiers ont été traités !</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal d'assignation -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gabon-blue text-white">
                <h5 class="modal-title">Assigner le Dossier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignForm">
                    <div class="mb-3">
                        <label class="form-label">Assigner à :</label>
                        <select class="form-select" id="agentSelect" required>
                            <option value="">Choisir un agent...</option>
                            @foreach(App\Models\User::whereIn('role', ['admin', 'agent'])->get() as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }} ({{ ucfirst($agent->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire (optionnel) :</label>
                        <textarea class="form-control" id="assignComment" rows="3" 
                                  placeholder="Ajouter une note ou instruction..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-gabon-green" onclick="confirmAssign()">
                    <i class="fas fa-check me-2"></i>Assigner
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stats-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    position: relative;
    color: white;
}

.stats-card:hover {
    transform: translateY(-3px);
}

.stats-card.gabon-yellow {
    background: linear-gradient(135deg, var(--gabon-yellow) 0%, #ffd700 100%);
    color: #333;
}

.stats-card.gabon-red {
    background: linear-gradient(135deg, var(--gabon-red) 0%, #a01e3c 100%);
}

.stats-card.gabon-blue {
    background: linear-gradient(135deg, var(--gabon-blue) 0%, #0056b3 100%);
}

.stats-card.gabon-green {
    background: linear-gradient(135deg, var(--gabon-green) 0%, #00b347 100%);
}

.stats-card-body {
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}

.stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-top: 0.5rem;
}

.stats-icon {
    font-size: 2.5rem;
    opacity: 0.3;
}

.org-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: rgba(13, 110, 253, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--gabon-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
}

.btn-gabon-green {
    background-color: var(--gabon-green);
    border-color: var(--gabon-green);
    color: white;
}

.btn-gabon-green:hover {
    background-color: #00b347;
    border-color: #00b347;
    color: white;
}

.btn-gabon-blue {
    background-color: var(--gabon-blue);
    border-color: var(--gabon-blue);
    color: white;
}

.btn-gabon-blue:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white;
}

.btn-outline-gabon-green {
    border-color: var(--gabon-green);
    color: var(--gabon-green);
}

.btn-outline-gabon-green:hover {
    background-color: var(--gabon-green);
    border-color: var(--gabon-green);
    color: white;
}

.text-gabon-blue {
    color: var(--gabon-blue) !important;
}
</style>
@endpush

@push('scripts')
<script>
let currentDossierId = null;

function assignToMe(dossierId) {
    if (confirm('Voulez-vous vous assigner ce dossier ?')) {
        fetch(`/admin/dossiers/${dossierId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                agent_id: {{ auth()->id() }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                // Afficher un message de succès
                showAlert('Dossier assigné avec succès !', 'success');
                // Actualiser la page après 1 seconde
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('Erreur lors de l\'assignation', 'danger');
        });
    }
}

function assignToOther(dossierId) {
    currentDossierId = dossierId;
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

function confirmAssign() {
    const agentId = document.getElementById('agentSelect').value;
    const comment = document.getElementById('assignComment').value;
    
    if (!agentId) {
        showAlert('Veuillez sélectionner un agent', 'warning');
        return;
    }
    
    fetch(`/admin/dossiers/${currentDossierId}/assign`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            agent_id: agentId,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
            showAlert('Dossier assigné avec succès !', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('Erreur lors de l\'assignation', 'danger');
    });
}

function markUrgent(dossierId) {
    if (confirm('Marquer ce dossier comme urgent ?')) {
        // Logique pour marquer urgent
        showAlert('Dossier marqué comme urgent', 'warning');
    }
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss après 3 secondes
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

// Initialiser les tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush