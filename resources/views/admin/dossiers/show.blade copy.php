{{-- resources/views/admin/dossiers/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Détail Dossier - ' . ($dossier->numero_dossier ?? 'N/A'))

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.dossiers.en-attente') }}">Dossiers</a></li>
                    <li class="breadcrumb-item active">{{ $dossier->numero_dossier ?? 'Détail' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header du dossier avec actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" 
                 style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div class="status-circle me-3">
                                    @php
                                        $statusIcons = [
                                            'brouillon' => ['icon' => 'edit', 'bg' => 'secondary'],
                                            'soumis' => ['icon' => 'clock', 'bg' => 'warning'],
                                            'en_cours' => ['icon' => 'cogs', 'bg' => 'info'],
                                            'approuve' => ['icon' => 'check', 'bg' => 'success'],
                                            'rejete' => ['icon' => 'times', 'bg' => 'danger']
                                        ];
                                        $statusConfig = $statusIcons[$dossier->statut] ?? ['icon' => 'question', 'bg' => 'secondary'];
                                    @endphp
                                    <div class="status-circle bg-{{ $statusConfig['bg'] }}">
                                        <i class="fas fa-{{ $statusConfig['icon'] }} text-white fa-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h2 class="mb-1">{{ $dossier->numero_dossier }}</h2>
                                    <h4 class="mb-0 opacity-90">{{ $dossier->organisation->nom ?? 'Organisation non définie' }}</h4>
                                    <div class="mt-2">
                                        <span class="badge bg-light text-dark fs-6">
                                            {{ ucfirst(str_replace('_', ' ', $dossier->organisation->type ?? 'N/A')) }}
                                        </span>
                                        @if($dossier->organisation && $dossier->organisation->prefecture)
                                            <span class="badge bg-light text-dark fs-6 ms-2">
                                                {{ $dossier->organisation->prefecture }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Informations de base -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <small class="opacity-75">Date de soumission</small>
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y à H:i') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <small class="opacity-75">Délai d'attente</small>
                                        <div class="fw-bold">
                                            @php
                                                $delai = \Carbon\Carbon::parse($dossier->created_at)->diffInDays(now());
                                            @endphp
                                            {{ $delai }} jour{{ $delai > 1 ? 's' : '' }}
                                            @if($delai > 7)
                                                <i class="fas fa-exclamation-triangle text-warning ms-1" title="Priorité haute"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <!-- Actions principales -->
                            <div class="btn-group-vertical w-100" role="group">
                                @if($dossier->statut === 'soumis')
                                    <button type="button" class="btn btn-success mb-2" onclick="assignerDossier()">
                                        <i class="fas fa-user-check"></i> Assigner à un Agent
                                    </button>
                                    <button type="button" class="btn btn-warning mb-2" onclick="demanderModification()">
                                        <i class="fas fa-edit"></i> Demander Modification
                                    </button>
                                @elseif($dossier->statut === 'en_cours')
                                    <button type="button" class="btn btn-success mb-2" onclick="approuverDossier()">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <button type="button" class="btn btn-danger mb-2" onclick="rejeterDossier()">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                @endif
                                <div class="btn-group" role="group">
    <button type="button" class="btn btn-outline-light btn-sm" onclick="imprimerDossier()">
        <i class="fas fa-print"></i> Imprimer
    </button>
    
    {{-- Dropdown pour les PDF --}}
    <div class="btn-group" role="group">
        <button id="pdfDropdown" type="button" class="btn btn-outline-light btn-sm dropdown-toggle" 
                data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-file-pdf"></i> PDF
        </button>
        <ul class="dropdown-menu" aria-labelledby="pdfDropdown">
            <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="telechargerAccuse()">
                    <i class="fas fa-file-alt text-primary"></i> Accusé de réception
                </a>
            </li>
            @if($dossier->statut === 'approuve')
            <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="telechargerRecepisse()">
                    <i class="fas fa-certificate text-success"></i> Récépissé final
                </a>
            </li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="exporterDossierComplet()">
                    <i class="fas fa-file-pdf text-info"></i> Dossier complet
                </a>
            </li>
        </ul>
    </div>
    
    <button type="button" class="btn btn-outline-light btn-sm" onclick="envoyerEmail()">
        <i class="fas fa-envelope"></i> Email
    </button>
</div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale - Détails -->
        <div class="col-lg-8">
            <!-- Informations de l'organisation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building me-2"></i>Informations de l'Organisation
                    </h6>
                </div>
                <div class="card-body">
                    @if($dossier->organisation)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Nom complet</label>
                                    <div class="fw-bold">{{ $dossier->organisation->nom }}</div>
                                </div>
                                @if($dossier->organisation->sigle)
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Sigle</label>
                                    <div class="fw-bold">{{ $dossier->organisation->sigle }}</div>
                                </div>
                                @endif
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Type d'organisation</label>
                                    <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $dossier->organisation->type)) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if($dossier->organisation->numero_recepisse)
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Numéro de récépissé</label>
                                    <div class="fw-bold">{{ $dossier->organisation->numero_recepisse }}</div>
                                </div>
                                @endif
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Localisation</label>
                                    <div class="fw-bold">
                                        {{ $dossier->organisation->prefecture ?? 'Non renseigné' }}
                                        @if($dossier->organisation->commune)
                                            <br><small>{{ $dossier->organisation->commune }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($dossier->organisation->objet)
                        <div class="info-group">
                            <label class="text-muted small">Objet social</label>
                            <div class="fw-bold">{{ $dossier->organisation->objet }}</div>
                        </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucune information d'organisation disponible
                        </div>
                    @endif
                </div>
            </div>

            <!-- Documents du dossier -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-folder-open me-2"></i>Documents du Dossier
                        @if($dossier->documents && $dossier->documents->count() > 0)
                            <span class="badge badge-primary">{{ $dossier->documents->count() }}</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @if($dossier->documents && $dossier->documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th>Taille</th>
                                        <th>Date d'ajout</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dossier->documents as $document)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-pdf text-danger me-2 fa-lg"></i>
                                                <div>
                                                    <strong>{{ $document->nom_original ?? $document->nom_fichier }}</strong>
                                                    @if($document->description)
                                                        <br><small class="text-muted">{{ $document->description }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $document->documentType->libelle ?? $document->type_document ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($document->taille_fichier)
                                                {{ number_format($document->taille_fichier / 1024, 0) }} KB
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   title="Prévisualiser"
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="" 
                                                   class="btn btn-outline-success btn-sm" 
                                                   title="Télécharger">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-gray-300 mb-3"></i>
                            <h6 class="text-gray-600">Aucun document</h6>
                            <p class="text-muted">Ce dossier ne contient aucun document.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historique et commentaires -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Historique et Commentaires
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Événement de création -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary">
                                <i class="fas fa-plus text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="mb-1">Dossier créé</h6>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y à H:i') }}
                                        par {{ $dossier->user->name ?? 'Système' }}
                                    </small>
                                </div>
                                <p class="mb-0">Le dossier a été créé et soumis pour traitement.</p>
                            </div>
                        </div>

                        <!-- Commentaires s'il y en a -->
                            @if($dossier->operations && $dossier->operations->where('type_operation', 'commentaire')->count() > 0)
                            @foreach($dossier->operations->where('type_operation', 'commentaire')->sortBy('created_at') as $comment)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info">
                                    <i class="fas fa-comment text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <h6 class="mb-1">
                                            {{ ucfirst($comment->type) }}
                                            @if($comment->type === 'assignation')
                                                <span class="badge badge-success">Assignation</span>
                                            @elseif($comment->type === 'validation')
                                                <span class="badge badge-warning">Validation</span>
                                            @else
                                                <span class="badge badge-info">Note</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($comment->created_at)->format('d/m/Y à H:i') }}
                                            par {{ $comment->user->name ?? 'Système' }}
                                        </small>
                                    </div>
                                    <p class="mb-0">{{ $comment->contenu }}</p>
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Assignation si elle existe -->
                        @if($dossier->assigned_to && $dossier->assignedAgent)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success">
                                <i class="fas fa-user-check text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="mb-1">Dossier assigné</h6>
                                    <small class="text-muted">
                                        {{ $dossier->assigned_at ? \Carbon\Carbon::parse($dossier->assigned_at)->format('d/m/Y à H:i') : 'Date non renseignée' }}
                                    </small>
                                </div>
                                <p class="mb-0">
                                    Assigné à <strong>{{ $dossier->assignedAgent->name }}</strong>
                                    ({{ $dossier->assignedAgent->email }})
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Formulaire d'ajout de commentaire -->
                    <div class="mt-4">
                        <h6 class="mb-3">Ajouter un commentaire</h6>
                        <form id="commentForm">
                            <div class="form-group mb-3">
                                <textarea name="comment_text" 
                                          id="comment_text" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="Votre commentaire sur ce dossier..."
                                          required></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-comment"></i> Ajouter le Commentaire
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne secondaire - Informations complémentaires -->
        <div class="col-lg-4">
            <!-- Statut et assignation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Statut du Dossier
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="status-badge-large bg-{{ $statusConfig['bg'] }} text-white">
                            <i class="fas fa-{{ $statusConfig['icon'] }} fa-2x mb-2"></i>
                            <h5 class="mb-0">{{ ucfirst($dossier->statut) }}</h5>
                        </div>
                    </div>
                    
                    @if($dossier->assigned_to && $dossier->assignedAgent)
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-user-check"></i> Agent Assigné
                        </h6>
                        <strong>{{ $dossier->assignedAgent->name }}</strong><br>
                        <small>{{ $dossier->assignedAgent->email }}</small>
                        @if($dossier->assigned_at)
                            <hr class="my-2">
                            <small class="text-muted">
                                Assigné le {{ \Carbon\Carbon::parse($dossier->assigned_at)->format('d/m/Y à H:i') }}
                            </small>
                        @endif
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Non assigné</strong><br>
                        Ce dossier n'est pas encore assigné à un agent.
                    </div>
                    @endif

                    <!-- Priorité calculée -->
                    <div class="mb-3">
                        <label class="text-muted small">Priorité</label>
                        <div>
                            @php
                                $isPriority = false;
                                if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
                                    $isPriority = true;
                                    $reason = 'Parti politique';
                                } elseif (\Carbon\Carbon::parse($dossier->created_at)->diffInDays(now()) > 7) {
                                    $isPriority = true;
                                    $reason = 'Délai > 7 jours';
                                } else {
                                    $reason = 'Normale';
                                }
                            @endphp
                            
                            @if($isPriority)
                                <span class="badge badge-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Haute
                                </span>
                                <br><small class="text-muted">{{ $reason }}</small>
                            @else
                                <span class="badge badge-secondary">Normale</span>
                            @endif
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    @if($dossier->statut === 'soumis')
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-sm" onclick="assignerDossier()">
                            <i class="fas fa-user-check"></i> Assigner
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations du demandeur -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user me-2"></i>Demandeur
                    </h6>
                </div>
                <div class="card-body">
                    @if($dossier->user)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-primary text-white me-3">
                                {{ strtoupper(substr($dossier->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <strong>{{ $dossier->user->name }}</strong><br>
                                <small class="text-muted">{{ $dossier->user->email }}</small>
                            </div>
                        </div>
                        
                        @if($dossier->user->phone)
                        <div class="mb-2">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <span>{{ $dossier->user->phone }}</span>
                        </div>
                        @endif
                        
                        <div class="mb-2">
                            <i class="fas fa-calendar text-muted me-2"></i>
                            <span>Inscrit le {{ \Carbon\Carbon::parse($dossier->user->created_at)->format('d/m/Y') }}</span>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="contacterDemandeur()">
                                <i class="fas fa-envelope"></i> Contacter
                            </button>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucune information de demandeur disponible
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques du dossier -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Statistiques
                    </h6>
                </div>
                <div class="card-body">
                    
                    <div class="row text-center">
    <div class="col-6">
        <div class="stat-item">
            <h4 class="text-primary">{{ $dossier->documents ? $dossier->documents->count() : 0 }}</h4>
            <small class="text-muted">Document{{ ($dossier->documents && $dossier->documents->count() > 1) ? 's' : '' }}</small>
        </div>
    </div>
    <div class="col-6">
        <div class="stat-item">
            <h4 class="text-info">{{ $dossier->operations ? $dossier->operations->where('type_operation', 'commentaire')->count() : 0 }}</h4>
            <small class="text-muted">Commentaire{{ ($dossier->operations && $dossier->operations->where('type_operation', 'commentaire')->count() > 1) ? 's' : '' }}</small>
        </div>
    </div>
</div>

<hr>

{{-- Nouvelle section: Actions PDF rapides --}}
<div class="mb-3">
    <h6 class="text-muted small mb-2">Actions PDF</h6>
    <div class="d-grid gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="telechargerAccuse()">
            <i class="fas fa-file-alt"></i> Accusé réception
        </button>
        @if($dossier->statut === 'approuve')
        <button type="button" class="btn btn-outline-success btn-sm" onclick="telechargerRecepisse()">
            <i class="fas fa-certificate"></i> Récépissé final
        </button>
        @endif
    </div>
</div>

<hr>

{{-- Informations de dates existantes --}}
<div class="small">
    <div class="d-flex justify-content-between">
        <span>Créé le:</span>
        <strong>{{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y') }}</strong>
    </div>
    <div class="d-flex justify-content-between">
        <span>Dernière maj:</span>
        <strong>{{ \Carbon\Carbon::parse($dossier->updated_at)->format('d/m/Y') }}</strong>
    </div>
    @if($dossier->statut === 'approuve' && $dossier->validated_at)
    <div class="d-flex justify-content-between">
        <span>Approuvé le:</span>
        <strong>{{ \Carbon\Carbon::parse($dossier->validated_at)->format('d/m/Y') }}</strong>
    </div>
    @endif
</div>



                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('admin.dossiers.modals.assign')
@include('admin.dossiers.modals.approve')
@include('admin.dossiers.modals.reject')

@endsection

@push('scripts')
<script>
let dossierId = {{ $dossier->id }};

// Ajout de commentaire
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`/admin/dossiers/${dossierId}/comment`, {
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
            location.reload(); // Recharger pour voir le nouveau commentaire
        } else {
            showAlert('error', data.message || 'Erreur lors de l\'ajout du commentaire');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('error', 'Erreur technique');
    });
});

// Fonctions pour les actions
function assignerDossier() {
    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
}

function approuverDossier() {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejeterDossier() {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function demanderModification() {
    if (confirm('Demander des modifications à l\'organisation ?')) {
        // TODO: Implémenter la demande de modification
        alert('Fonction à implémenter');
    }
}

function imprimerDossier() {
    window.print();
}

// ========== FONCTIONS PDF AMÉLIORÉES ==========

/**
 * Télécharge l'accusé de réception PDF
 * Disponible pour tous les dossiers
 */
function telechargerAccuse() {
    showLoadingAlert('Génération de l\'accusé de réception...');
    
    // Utilisation de la route spécifique pour l'accusé
    const url = `{{ route('admin.dossiers.download-accuse', ['dossier' => $dossier->id]) }}`;
    
    // Créer un lien temporaire pour le téléchargement
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Masquer le message de chargement après un délai
    setTimeout(() => {
        hideLoadingAlert();
        showAlert('success', 'Accusé de réception téléchargé avec succès');
    }, 2000);
}

/**
 * Télécharge le récépissé final PDF
 * Disponible uniquement pour les dossiers approuvés
 */
function telechargerRecepisse() {
    @if($dossier->statut !== 'approuve')
        showAlert('warning', 'Le récépissé n\'est disponible que pour les dossiers approuvés');
        return;
    @endif
    
    showLoadingAlert('Génération du récépissé final...');
    
    // Utilisation de la route spécifique pour le récépissé
    const url = `{{ route('admin.dossiers.download-recepisse', ['dossier' => $dossier->id]) }}`;
    
    // Créer un lien temporaire pour le téléchargement
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Masquer le message de chargement après un délai
    setTimeout(() => {
        hideLoadingAlert();
        showAlert('success', 'Récépissé final téléchargé avec succès');
    }, 2000);
}

/**
 * Exporte le dossier complet en PDF
 * Version complète avec tous les détails
 */
function exporterDossierComplet() {
    showLoadingAlert('Génération du dossier complet...');
    
    // Utilisation de la route générale pour le PDF complet
    const url = `/admin/dossiers/${dossierId}/pdf`;
    
    window.open(url, '_blank');
    
    setTimeout(() => {
        hideLoadingAlert();
        showAlert('success', 'Dossier complet généré avec succès');
    }, 1500);
}

/**
 * Fonction d'impression améliorée
 */
function imprimerDossier() {
    // Masquer les éléments non imprimables
    const elementsToHide = document.querySelectorAll('.btn, .breadcrumb, .dropdown-menu');
    elementsToHide.forEach(el => el.style.display = 'none');
    
    // Ajout d'un titre pour l'impression
    const titre = document.createElement('h1');
    titre.innerHTML = `DOSSIER ${document.querySelector('h2').textContent}`;
    titre.style.textAlign = 'center';
    titre.style.marginBottom = '20px';
    titre.className = 'print-title';
    document.querySelector('.container-fluid').insertBefore(titre, document.querySelector('.row'));
    
    // Lancer l'impression
    window.print();
    
    // Restaurer les éléments cachés après impression
    setTimeout(() => {
        elementsToHide.forEach(el => el.style.display = '');
        const printTitle = document.querySelector('.print-title');
        if (printTitle) printTitle.remove();
    }, 1000);
}

// ========== FONCTIONS UTILITAIRES ==========

/**
 * Affiche un message de chargement
 */
function showLoadingAlert(message) {
    // Supprimer les alertes existantes
    const existingAlerts = document.querySelectorAll('.loading-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info loading-alert';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            ${message}
        </div>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
}

/**
 * Masque le message de chargement
 */
function hideLoadingAlert() {
    const loadingAlerts = document.querySelectorAll('.loading-alert');
    loadingAlerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    });
}

function envoyerEmail() {
    // TODO: Implémenter l'envoi d'email
    alert('Fonction à implémenter');
}

function contacterDemandeur() {
    // TODO: Implémenter le contact du demandeur
    alert('Fonction à implémenter');
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
</script>
@endpush

@push('styles')
<style>
.status-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-item {
    margin-bottom: 1rem;
}

.info-group {
    margin-bottom: 1rem;
}

.status-badge-large {
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 1rem;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-content {
    background: #f8f9fc;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 3px solid #4e73df;
}

.timeline-header h6 {
    color: #5a5c69;
    margin-bottom: 0.25rem;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.stat-item h4 {
    margin-bottom: 0.25rem;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

/* ========== NOUVEAUX STYLES PDF ========== */

/* Dropdown PDF avec style gabonais */
.dropdown-menu {
    border: 1px solid #e3e6f0;
    border-radius: 0.5rem;
    box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.15);
    min-width: 200px;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    margin: 0.125rem;
}

.dropdown-item:hover {
    background-color: #f8f9fc;
    color: #3d5a80;
}

.dropdown-item i {
    width: 20px;
    margin-right: 8px;
}

/* Animation de chargement */
.loading-alert {
    border-left: 4px solid #4e73df;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Spinner personnalisé */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.125rem;
}

/* Boutons PDF dans la colonne secondaire */
.btn-outline-primary.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.825rem;
}

.btn-outline-success.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.825rem;
}

/* Style pour l'impression */
@media print {
    .btn, .breadcrumb, .dropdown-menu, .card-header {
        display: none !important;
    }
    
    .print-title {
        color: #000;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        background-color: #ddd !important;
    }
}

/* Responsiveness pour mobile */
@media (max-width: 768px) {
    .dropdown-menu {
        min-width: 180px;
        margin-left: -50px;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

</style>
@endpush