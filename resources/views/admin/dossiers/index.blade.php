{{-- resources/views/admin/dossiers/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Toutes les Organisations')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-building me-2" style="color: #003f7f;"></i>
                Gestion des Organisations
            </h1>
            <p class="text-muted">Vue d'ensemble de toutes les organisations inscrites</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.dossiers.en-attente') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-clock"></i> En Attente ({{ \App\Models\Dossier::whereIn('statut', ['soumis'])->count() }})
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshPage()">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
            </div>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Organisations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Organisation::count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
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
                                Approuvées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Dossier::where('statut', 'approuve')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En Traitement
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Dossier::whereIn('statut', ['soumis', 'en_cours'])->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
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
                                Cette Semaine
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Organisation::where('created_at', '>=', now()->startOfWeek())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par type -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Répartition par Type d'Organisation
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $types = [
                                'association' => ['label' => 'Associations', 'color' => 'primary', 'icon' => 'users'],
                                'ong' => ['label' => 'ONG', 'color' => 'success', 'icon' => 'globe'],
                                'parti_politique' => ['label' => 'Partis Politiques', 'color' => 'warning', 'icon' => 'flag'],
                                'confession_religieuse' => ['label' => 'Confessions Religieuses', 'color' => 'info', 'icon' => 'pray']
                            ];
                        @endphp
                        
                        @foreach($types as $type => $config)
                            @php
                                $count = \App\Models\Organisation::where('type', $type)->count();
                                $percentage = \App\Models\Organisation::count() > 0 
                                    ? round(($count / \App\Models\Organisation::count()) * 100, 1) 
                                    : 0;
                            @endphp
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-{{ $config['color'] }} h-100">
                                    <div class="card-body py-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-{{ $config['color'] }} text-uppercase mb-1">
                                                    {{ $config['label'] }}
                                                </div>
                                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                    {{ $count }} <small>({{ $percentage }}%)</small>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-{{ $config['icon'] }} fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filtres de Recherche
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dossiers.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search" class="form-label">Recherche</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nom, sigle, numéro de récépissé...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type" class="form-label">Type d'Organisation</label>
                            <select name="type" id="type" class="form-control">
                                <option value="">Tous les types</option>
                                @foreach($types as $type => $config)
                                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                        {{ $config['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="statut" class="form-label">Statut du Dossier</label>
                            <select name="statut" id="statut" class="form-control">
                                <option value="">Tous les statuts</option>
                                <option value="brouillon" {{ request('statut') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                <option value="soumis" {{ request('statut') === 'soumis' ? 'selected' : '' }}>Soumis</option>
                                <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En Cours</option>
                                <option value="approuve" {{ request('statut') === 'approuve' ? 'selected' : '' }}>Approuvé</option>
                                <option value="rejete" {{ request('statut') === 'rejete' ? 'selected' : '' }}>Rejeté</option>
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

    <!-- Liste des organisations -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Liste des Organisations
                @if(isset($organisations) && $organisations->total() > 0)
                    <span class="badge badge-primary ms-2">{{ $organisations->total() }}</span>
                @endif
            </h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Exporter
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="exportData('excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportData('pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportData('csv')">
                        <i class="fas fa-file-csv"></i> CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(isset($organisations) && $organisations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Organisation</th>
                                <th>Type</th>
                                <th>Localisation</th>
                                <th>Contact</th>
                                <th>Statut Dossier</th>
                                <th>Date Création</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organisations as $organisation)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $organisation->nom }}</strong>
                                        @if($organisation->sigle)
                                            <br><small class="text-muted">({{ $organisation->sigle }})</small>
                                        @endif
                                        @if($organisation->numero_recepisse)
                                            <br><small class="text-info">N° {{ $organisation->numero_recepisse }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $types[$organisation->type]['color'] ?? 'secondary' }}">
                                        {{ $types[$organisation->type]['label'] ?? ucfirst($organisation->type) }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        @if($organisation->prefecture)
                                            <strong>{{ $organisation->prefecture }}</strong><br>
                                        @endif
                                        @if($organisation->commune)
                                            <small class="text-muted">{{ $organisation->commune }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @if($organisation->user)
                                            <strong>{{ $organisation->user->name }}</strong><br>
                                            <small class="text-muted">{{ $organisation->user->email }}</small>
                                            @if($organisation->user->phone)
                                                <br><small class="text-info">{{ $organisation->user->phone }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Non renseigné</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $dernierDossier = $organisation->dossiers->first();
                                    @endphp
                                    @if($dernierDossier)
                                        @php
                                            $statusConfig = [
                                                'brouillon' => ['class' => 'secondary', 'icon' => 'edit'],
                                                'soumis' => ['class' => 'warning', 'icon' => 'clock'],
                                                'en_cours' => ['class' => 'info', 'icon' => 'cogs'],
                                                'approuve' => ['class' => 'success', 'icon' => 'check'],
                                                'rejete' => ['class' => 'danger', 'icon' => 'times']
                                            ];
                                            $config = $statusConfig[$dernierDossier->statut] ?? ['class' => 'secondary', 'icon' => 'question'];
                                        @endphp
                                        <span class="badge badge-{{ $config['class'] }}">
                                            <i class="fas fa-{{ $config['icon'] }}"></i>
                                            {{ ucfirst($dernierDossier->statut) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $dernierDossier->numero_dossier }}</small>
                                    @else
                                        <span class="badge badge-light">Aucun dossier</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        {{ \Carbon\Carbon::parse($organisation->created_at)->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($organisation->created_at)->format('H:i') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($dernierDossier)
                                            <a href="{{ route('admin.dossiers.show', $dernierDossier->id) }}" 
                                               class="btn btn-outline-primary btn-sm" 
                                               title="Voir le dossier">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm" 
                                                onclick="viewOrganisation({{ $organisation->id }})"
                                                title="Détails organisation">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-secondary btn-sm" 
                                                onclick="contactOrganisation({{ $organisation->id }})"
                                                title="Contacter">
                                            <i class="fas fa-envelope"></i>
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
                        Affichage de {{ $organisations->firstItem() ?? 0 }} à {{ $organisations->lastItem() ?? 0 }} 
                        sur {{ $organisations->total() }} résultats
                    </div>
                    <div>
                        {{ $organisations->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-building fa-4x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucune organisation trouvée</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'type', 'statut']))
                            Aucune organisation ne correspond aux critères de recherche.
                            <br>
                            <a href="{{ route('admin.dossiers.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fas fa-times"></i> Effacer les filtres
                            </a>
                        @else
                            Aucune organisation n'est encore enregistrée dans le système.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
});

function initializeFilters() {
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

    ['type', 'statut'].forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
}

function refreshPage() {
    location.reload();
}

function exportData(format) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('admin.exports.organisations') }}`;
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);
    
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = format;
    form.appendChild(formatInput);
    
    // Ajouter les filtres actuels
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.forEach((value, key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function viewOrganisation(id) {
    // TODO: Implémenter la vue détaillée d'organisation
    alert('Fonction à implémenter: Voir détails organisation #' + id);
}

function contactOrganisation(id) {
    // TODO: Implémenter la fonction de contact
    alert('Fonction à implémenter: Contacter organisation #' + id);
}
</script>
@endpush

@push('styles')
<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }

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
</style>
@endpush