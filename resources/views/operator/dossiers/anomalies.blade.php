@extends('layouts.operator')

@section('title', 'Gestion des Anomalies')

@section('page-title', 'Anomalies des Adhérents')

@section('content')
<div class="container-fluid">
    <!-- Header avec navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <nav aria-label="breadcrumb" class="mb-2">
                                <ol class="breadcrumb text-white-50 mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('operator.dashboard') }}" class="text-white opacity-75">
                                            <i class="fas fa-home me-1"></i>Dashboard
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('operator.dossiers.index') }}" class="text-white opacity-75">Dossiers</a>
                                    </li>
                                    <li class="breadcrumb-item active text-white">Anomalies</li>
                                </ol>
                            </nav>
                            <h2 class="mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Gestion des Anomalies
                            </h2>
                            <p class="mb-0 opacity-90">Suivez et résolvez les anomalies détectées dans vos adhérents</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('operator.dossiers.export-anomalies-pdf', $dossier->id) }}" 
                                   class="btn btn-warning me-2" 
                                   target="_blank"
                                   title="Télécharger le rapport PDF des anomalies">
                                    <i class="fas fa-file-pdf me-2"></i>Rapport PDF
                                </a>
                                <a href="{{ route('operator.dossiers.index') }}" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>Retour aux Dossiers
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des anomalies -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $anomalies->total() ?? 0 }}</h3>
                            <p class="mb-0 small opacity-90">Total Anomalies</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $anomalies->where('anomalies_severity', 'critique')->count() ?? 0 }}</h3>
                            <p class="mb-0 small opacity-90">Critiques</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $anomalies->where('anomalies_severity', 'majeure')->count() ?? 0 }}</h3>
                            <p class="mb-0 small">Majeures</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffc107 0%, #28a745 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $anomalies->where('anomalies_severity', 'mineure')->count() ?? 0 }}</h3>
                            <p class="mb-0 small">Mineures</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('operator.dossiers.anomalies', $dossier->id) }}">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="organisation_id" class="form-label">Organisation</label>
                                <select class="form-select" name="organisation_id" id="organisation_id">
                                    <option value="">Toutes les organisations</option>
                                    @foreach($organisations as $org)
                                        <option value="{{ $org->id }}" {{ request('organisation_id') == $org->id ? 'selected' : '' }}>
                                            {{ $org->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="severity" class="form-label">Sévérité</label>
                                <select class="form-select" name="severity" id="severity">
                                    <option value="">Toutes les sévérités</option>
                                    <option value="critique" {{ request('severity') == 'critique' ? 'selected' : '' }}>Critique</option>
                                    <option value="majeure" {{ request('severity') == 'majeure' ? 'selected' : '' }}>Majeure</option>
                                    <option value="mineure" {{ request('severity') == 'mineure' ? 'selected' : '' }}>Mineure</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-2"></i>Filtrer
                                    </button>
                                    <a href="{{ route('operator.dossiers.anomalies', $dossier->id) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des anomalies -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>
                            Anomalies des Adhérents
                        </h5>
                        <span class="badge bg-light text-dark">{{ $anomalies->count() }} anomalie(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($anomalies->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Adhérent</th>
                                        <th class="border-0">Organisation</th>
                                        <th class="border-0">NIP</th>
                                        <th class="border-0">Sévérité</th>
                                        <th class="border-0">Anomalies</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($anomalies as $adherent)
                                    <tr class="anomalie-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    @if($adherent->anomalies_severity == 'critique')
                                                        <i class="fas fa-user-times fa-2x text-danger"></i>
                                                    @elseif($adherent->anomalies_severity == 'majeure')
                                                        <i class="fas fa-user-clock fa-2x text-warning"></i>
                                                    @else
                                                        <i class="fas fa-user-check fa-2x text-info"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $adherent->nom }} {{ $adherent->prenom }}</h6>
                                                    <small class="text-muted">{{ $adherent->email ?? 'Email non renseigné' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $adherent->organisation->nom ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ ucfirst($adherent->organisation->type ?? '') }}</small>
                                        </td>
                                        <td>
                                            @if($adherent->nip)
                                                <code class="bg-light px-2 py-1 rounded">{{ $adherent->nip }}</code>
                                            @else
                                                <span class="badge bg-danger">NIP manquant</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($adherent->anomalies_severity == 'critique')
                                                <span class="badge bg-danger">Critique</span>
                                            @elseif($adherent->anomalies_severity == 'majeure')
                                                <span class="badge bg-warning">Majeure</span>
                                            @else
                                                <span class="badge bg-info">Mineure</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($adherent->anomalies_data)
                                                <div class="anomalies-list">
                                                    @foreach($adherent->anomalies_data as $anomalie)
                                                        <small class="d-block">
                                                            <i class="fas fa-dot-circle me-1 text-{{ $anomalie['type'] == 'critique' ? 'danger' : ($anomalie['type'] == 'majeure' ? 'warning' : 'info') }}"></i>
                                                            {{ $anomalie['message'] ?? $anomalie['code'] }}
                                                        </small>
                                                    @endforeach
                                                </div>
                                            @else
                                                <small class="text-muted">Aucun détail</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal"
                                                        data-adherent="{{ $adherent->id }}"
                                                        title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#resolveModal"
                                                        data-adherent="{{ $adherent->id }}"
                                                        title="Résoudre">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                
                                                <a href="{{ route('operator.members.show', $adherent->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Éditer adhérent">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $anomalies->appends(request()->query())->links() }}
                        </div>
                    @else
                        <!-- État vide -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-check-circle fa-5x text-success opacity-50"></i>
                            </div>
                            <h4 class="text-success mb-3">Aucune anomalie trouvée !</h4>
                            <p class="text-muted mb-4">
                                @if(request()->hasAny(['organisation_id', 'severity']))
                                    Aucune anomalie ne correspond à vos critères de filtrage.
                                @else
                                    Tous vos adhérents sont conformes, aucune anomalie détectée.
                                @endif
                            </p>
                            <a href="{{ route('operator.dossiers.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Retour aux dossiers
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de résolution d'anomalie -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Résoudre l'anomalie
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="resolveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="anomalie_code" class="form-label">Code de l'anomalie</label>
                        <select class="form-select" name="anomalie_code" id="anomalie_code" required>
                            <option value="">Sélectionnez l'anomalie à résoudre</option>
                            <option value="nip_absent">NIP absent</option>
                            <option value="nip_invalide">Format NIP invalide</option>
                            <option value="double_appartenance_parti">Double appartenance parti</option>
                            <option value="profession_exclue">Profession exclue</option>
                            <option value="telephone_invalide">Téléphone invalide</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="action" class="form-label">Action à effectuer</label>
                        <select class="form-select" name="action" id="action" required>
                            <option value="">Choisissez une action</option>
                            <option value="resolve">Marquer comme résolu</option>
                            <option value="update">Mettre à jour les données</option>
                            <option value="exclude">Exclure l'adhérent</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="resolution_details" class="form-label">Détails de la résolution</label>
                        <textarea class="form-control" name="resolution_details" id="resolution_details" 
                                  rows="4" required placeholder="Décrivez comment l'anomalie a été résolue..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Résoudre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de détails -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Détails des anomalies
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.1);
}

.anomalie-row {
    transition: background-color 0.2s ease;
}

.anomalie-row:hover {
    background-color: rgba(220, 53, 69, 0.05);
}

.anomalies-list {
    max-height: 100px;
    overflow-y: auto;
}

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
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du modal de résolution
    const resolveModal = document.getElementById('resolveModal');
    if (resolveModal) {
        resolveModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const adherentId = button.getAttribute('data-adherent');
            const form = document.getElementById('resolveForm');
            form.action = `/operator/dossiers/anomalies/resolve/${adherentId}`;
        });
    }
    
    // Gestion du modal de détails
    const detailModal = document.getElementById('detailModal');
    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const adherentId = button.getAttribute('data-adherent');
            const modalBody = document.getElementById('detailModalBody');
            
            // Charger les détails via AJAX (à implémenter)
            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            `;
            
            // Simuler le chargement des détails
            setTimeout(() => {
                modalBody.innerHTML = `
                    <div class="alert alert-info">
                        <h6>Détails de l'adhérent ID: ${adherentId}</h6>
                        <p>Cette fonctionnalité sera implémentée pour afficher les détails complets des anomalies.</p>
                    </div>
                `;
            }, 1000);
        });
    }
});
</script>
@endsection