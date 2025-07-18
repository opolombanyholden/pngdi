@extends('layouts.admin')

@section('title', 'Gestion Base NIP')

@section('content')
<div class="container-fluid">
    <!-- En-tête avec statistiques -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-database text-primary"></i>
                    Gestion Base NIP
                </h1>
                <div class="btn-group">
                    <a href="{{ route('admin.nip-database.import') }}" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Importer NIP
                    </a>
                    <a href="{{ route('admin.nip-database.template') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download"></i> Template Excel
                    </a>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cleanupModal">
                        <i class="fas fa-broom"></i> Nettoyage
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total NIP
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($statistics['total']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($statistics['actifs']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Hommes / Femmes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($statistics['hommes']) }} / {{ number_format($statistics['femmes']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-venus-mars fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Dernière MAJ
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                @if($statistics['derniere_maj'])
                                    {{ \Carbon\Carbon::parse($statistics['derniere_maj'])->format('d/m/Y H:i') }}
                                @else
                                    Aucune
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recherche et Filtres</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nip-database.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Recherche</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ $query }}"
                           placeholder="NIP, nom, prénom...">
                </div>
                
                <div class="col-md-2">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous</option>
                        <option value="actif" {{ $filters['statut'] == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ $filters['statut'] == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="decede" {{ $filters['statut'] == 'decede' ? 'selected' : '' }}>Décédé</option>
                        <option value="suspendu" {{ $filters['statut'] == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="sexe" class="form-label">Sexe</label>
                    <select class="form-select" id="sexe" name="sexe">
                        <option value="">Tous</option>
                        <option value="M" {{ $filters['sexe'] == 'M' ? 'selected' : '' }}>Homme</option>
                        <option value="F" {{ $filters['sexe'] == 'F' ? 'selected' : '' }}>Femme</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_from" class="form-label">Né après</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_from" 
                           name="date_from" 
                           value="{{ $filters['date_from'] }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">Né avant</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_to" 
                           name="date_to" 
                           value="{{ $filters['date_to'] }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    <a href="{{ route('admin.nip-database.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des résultats -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Liste des NIP ({{ $nips->total() }} résultats)
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary" onclick="selectAll()">
                    Sélectionner tout
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="deselectAll()">
                    Désélectionner
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($nips->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>NIP</th>
                                <th>Âge</th>
                                <th>Lieu Naissance</th>
                                <th>Statut</th>
                                <th>Dernière Vérif.</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nips as $nip)
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               class="form-check-input row-select" 
                                               value="{{ $nip->id }}">
                                    </td>
                                    <td class="fw-bold">{{ $nip->nom }}</td>
                                    <td>{{ $nip->prenom }}</td>
                                    <td>
                                        <code class="text-primary">{{ $nip->nip }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $nip->age }} ans</span>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-{{ $nip->sexe == 'M' ? 'mars text-blue' : 'venus text-pink' }}"></i>
                                            {{ $nip->sexe == 'M' ? 'Homme' : 'Femme' }}
                                        </small>
                                    </td>
                                    <td>{{ $nip->lieu_naissance ?: '-' }}</td>
                                    <td>
                                        @switch($nip->statut)
                                            @case('actif')
                                                <span class="badge bg-success">Actif</span>
                                                @break
                                            @case('inactif')
                                                <span class="badge bg-secondary">Inactif</span>
                                                @break
                                            @case('decede')
                                                <span class="badge bg-dark">Décédé</span>
                                                @break
                                            @case('suspendu')
                                                <span class="badge bg-warning">Suspendu</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($nip->last_verified_at)
                                            <small class="text-muted">
                                                {{ $nip->last_verified_at->format('d/m/Y') }}
                                            </small>
                                        @else
                                            <small class="text-danger">Jamais</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.nip-database.show', $nip) }}" 
                                               class="btn btn-outline-primary btn-sm" 
                                               title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.nip-database.edit', $nip) }}" 
                                               class="btn btn-outline-warning btn-sm" 
                                               title="Modifier">
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
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Affichage de {{ $nips->firstItem() }} à {{ $nips->lastItem() }} 
                            sur {{ $nips->total() }} résultats
                        </small>
                    </div>
                    <div>
                        {{ $nips->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-gray-400 mb-3"></i>
                    <h5 class="text-gray-600">Aucun NIP trouvé</h5>
                    <p class="text-muted">
                        @if($query || array_filter($filters))
                            Aucun résultat ne correspond à vos critères de recherche.
                        @else
                            La base de données NIP est vide. Commencez par importer des données.
                        @endif
                    </p>
                    @if(!$query && !array_filter($filters))
                        <a href="{{ route('admin.nip-database.import') }}" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Importer des NIP
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de nettoyage -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nettoyage de la base NIP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Attention !</strong> Cette opération va supprimer les doublons détectés dans la base NIP.
                    Cette action est irréversible.
                </div>
                <p>Le nettoyage comprend :</p>
                <ul>
                    <li>Suppression des NIP en doublon (garde le plus récent)</li>
                    <li>Vérification de l'intégrité des données</li>
                    <li>Optimisation des index</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="{{ route('admin.nip-database.cleanup') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-broom"></i> Lancer le nettoyage
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Gestion des cases à cocher
    $('#select-all').on('change', function() {
        $('.row-select').prop('checked', $(this).prop('checked'));
    });

    $('.row-select').on('change', function() {
        if ($('.row-select:checked').length === $('.row-select').length) {
            $('#select-all').prop('checked', true);
        } else {
            $('#select-all').prop('checked', false);
        }
    });
});

function selectAll() {
    $('.row-select').prop('checked', true);
    $('#select-all').prop('checked', true);
}

function deselectAll() {
    $('.row-select').prop('checked', false);
    $('#select-all').prop('checked', false);
}
</script>
@endpush