@extends('layouts.admin')

@section('title', 'Détails NIP - ' . $nip->nip)

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user text-primary"></i>
                    Détails NIP : <code class="text-primary">{{ $nip->nip }}</code>
                </h1>
                <div class="btn-group">
                    <a href="{{ route('admin.nip-database.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                    <a href="{{ route('admin.nip-database.edit', $nip) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-id-card"></i>
                        Informations personnelles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Nom</label>
                            <div class="h5 text-dark">{{ $nip->nom }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Prénom</label>
                            <div class="h5 text-dark">{{ $nip->prenom }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">NIP</label>
                            <div class="h5">
                                <code class="text-primary fs-6">{{ $nip->nip }}</code>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary ms-2" 
                                        onclick="copyToClipboard('{{ $nip->nip }}')"
                                        title="Copier le NIP">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Date de naissance</label>
                            <div class="h6">
                                {{ $nip->date_naissance->format('d/m/Y') }}
                                <span class="badge bg-info ms-2">{{ $nip->age }} ans</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Sexe</label>
                            <div class="h6">
                                <i class="fas fa-{{ $nip->sexe == 'M' ? 'mars text-primary' : 'venus text-danger' }} me-2"></i>
                                {{ $nip->sexe == 'M' ? 'Homme' : 'Femme' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Lieu de naissance</label>
                            <div class="h6">{{ $nip->lieu_naissance ?: 'Non renseigné' }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Statut</label>
                            <div>
                                @switch($nip->statut)
                                    @case('actif')
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-check-circle"></i> Actif
                                        </span>
                                        @break
                                    @case('inactif')
                                        <span class="badge bg-secondary fs-6">
                                            <i class="fas fa-pause-circle"></i> Inactif
                                        </span>
                                        @break
                                    @case('decede')
                                        <span class="badge bg-dark fs-6">
                                            <i class="fas fa-times-circle"></i> Décédé
                                        </span>
                                        @break
                                    @case('suspendu')
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-exclamation-circle"></i> Suspendu
                                        </span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Téléphone</label>
                            <div class="h6">
                                @if($nip->telephone)
                                    <a href="tel:{{ $nip->telephone }}" class="text-decoration-none">
                                        <i class="fas fa-phone text-success me-2"></i>
                                        {{ $nip->telephone }}
                                    </a>
                                @else
                                    Non renseigné
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted">Email</label>
                            <div class="h6">
                                @if($nip->email)
                                    <a href="mailto:{{ $nip->email }}" class="text-decoration-none">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        {{ $nip->email }}
                                    </a>
                                @else
                                    Non renseigné
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($nip->remarques)
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label text-muted">Remarques</label>
                                <div class="alert alert-light">
                                    <i class="fas fa-comment-alt text-info me-2"></i>
                                    {{ $nip->remarques }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Utilisation dans les organisations -->
            @if($adherents->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-users"></i>
                            Utilisation dans les organisations ({{ $adherents->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Organisation</th>
                                        <th>Type</th>
                                        <th>Fonction</th>
                                        <th>Date adhésion</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adherents as $adherent)
                                        <tr>
                                            <td>
                                                <strong>{{ $adherent->organisation->nom }}</strong>
                                                @if($adherent->organisation->sigle)
                                                    <br><small class="text-muted">({{ $adherent->organisation->sigle }})</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst(str_replace('_', ' ', $adherent->organisation->type)) }}
                                                </span>
                                            </td>
                                            <td>{{ $adherent->fonction }}</td>
                                            <td>{{ $adherent->date_adhesion->format('d/m/Y') }}</td>
                                            <td>
                                                @if($adherent->is_active)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactif</span>
                                                @endif
                                                
                                                @if($adherent->has_anomalies)
                                                    <span class="badge bg-warning ms-1" title="Anomalies détectées">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.organisations.show', $adherent->organisation) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Voir organisation">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar avec informations techniques -->
        <div class="col-lg-4">
            <!-- Informations techniques -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-cogs"></i>
                        Informations techniques
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Source d'import</label>
                        <div class="text-dark">
                            @if($nip->source_import)
                                <span class="badge bg-light text-dark">{{ $nip->source_import }}</span>
                            @else
                                <span class="text-muted">Non renseigné</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Date d'import</label>
                        <div class="text-dark">
                            @if($nip->date_import)
                                {{ $nip->date_import->format('d/m/Y à H:i') }}
                            @else
                                <span class="text-muted">Non renseigné</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Importé par</label>
                        <div class="text-dark">
                            @if($nip->importedBy)
                                {{ $nip->importedBy->name }}
                                <br><small class="text-muted">{{ $nip->importedBy->email }}</small>
                            @else
                                <span class="text-muted">Non renseigné</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Dernière vérification</label>
                        <div class="text-dark">
                            @if($nip->last_verified_at)
                                {{ $nip->last_verified_at->format('d/m/Y à H:i') }}
                                <br><small class="text-muted">
                                    Il y a {{ $nip->last_verified_at->diffForHumans() }}
                                </small>
                            @else
                                <span class="text-danger">Jamais vérifié</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Créé le</label>
                        <div class="text-dark">{{ $nip->created_at->format('d/m/Y à H:i') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Modifié le</label>
                        <div class="text-dark">{{ $nip->updated_at->format('d/m/Y à H:i') }}</div>
                    </div>

                    <hr>
                    
                    <button type="button" 
                            class="btn btn-sm btn-outline-success w-100"
                            onclick="markAsVerified()">
                        <i class="fas fa-check-circle"></i>
                        Marquer comme vérifié
                    </button>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-tools"></i>
                        Actions rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm"
                                onclick="searchInOrganizations()">
                            <i class="fas fa-search"></i>
                            Rechercher dans les organisations
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-info btn-sm"
                                onclick="copyNipData()">
                            <i class="fas fa-copy"></i>
                            Copier les données
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-warning btn-sm"
                                onclick="checkDuplicates()">
                            <i class="fas fa-user-friends"></i>
                            Vérifier les doublons
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-trash"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Attention !</strong> Cette action est irréversible.
                </div>
                
                <p>Voulez-vous vraiment supprimer le NIP <strong>{{ $nip->nip }}</strong> 
                   de <strong>{{ $nip->nom_complet }}</strong> ?</p>
                
                @if($adherents->count() > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Attention :</strong> Ce NIP est utilisé dans {{ $adherents->count() }} organisation(s). 
                        La suppression pourrait affecter ces données.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="{{ route('admin.nip-database.destroy', $nip) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Confirmer la suppression
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Toast de succès
        showToast('NIP copié dans le presse-papier', 'success');
    }, function(err) {
        console.error('Erreur lors de la copie: ', err);
        showToast('Erreur lors de la copie', 'error');
    });
}

function copyNipData() {
    const data = `NIP: {{ $nip->nip }}
Nom: {{ $nip->nom }}
Prénom: {{ $nip->prenom }}
Date de naissance: {{ $nip->date_naissance->format('d/m/Y') }}
Lieu de naissance: {{ $nip->lieu_naissance ?? 'Non renseigné' }}
Sexe: {{ $nip->sexe == 'M' ? 'Homme' : 'Femme' }}
Statut: {{ ucfirst($nip->statut) }}`;

    navigator.clipboard.writeText(data).then(function() {
        showToast('Données copiées dans le presse-papier', 'success');
    });
}

function markAsVerified() {
    if (confirm('Marquer ce NIP comme vérifié ?')) {
        // Faire un appel AJAX pour marquer comme vérifié
        fetch('{{ route("admin.nip-database.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                nip: '{{ $nip->nip }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('NIP marqué comme vérifié', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Erreur lors de la vérification', 'error');
            }
        });
    }
}

function searchInOrganizations() {
    // Rediriger vers la recherche dans les organisations avec ce NIP
    window.open('/admin/organisations?search={{ $nip->nip }}', '_blank');
}

function checkDuplicates() {
    // Rechercher des doublons potentiels
    const searchParams = new URLSearchParams({
        search: '{{ $nip->nom }} {{ $nip->prenom }}',
        date_from: '{{ $nip->date_naissance->format("Y-m-d") }}',
        date_to: '{{ $nip->date_naissance->format("Y-m-d") }}'
    });
    
    window.open('/admin/nip-database?' + searchParams.toString(), '_blank');
}

function showToast(message, type = 'info') {
    // Créer un toast Bootstrap simple
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Ajouter le toast au DOM
    if (!document.getElementById('toast-container')) {
        document.body.insertAdjacentHTML('beforeend', 
            '<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>'
        );
    }
    
    const container = document.getElementById('toast-container');
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialiser et afficher le toast
    const toastElement = container.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Supprimer l'élément après qu'il soit caché
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}
</script>
@endpush