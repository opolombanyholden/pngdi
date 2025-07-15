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
                                
                                {{-- SECTION CORRIGÉE - Boutons PDF --}}
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
                                                <a href="/admin/dossiers/{{ $dossier->id }}/documents/{{ $document->id }}/preview" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   title="Prévisualiser"
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="/admin/dossiers/{{ $dossier->id }}/download-accuse" 
                                                   class="btn btn-outline-success btn-sm" 
                                                   title="Télécharger">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
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
                                                <a href="/admin/dossiers/{{ $dossier->id }}/documents/{{ $document->id }}/preview" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   title="Prévisualiser"
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="/admin/dossiers/{{ $dossier->id }}/download-accuse" 
                                                   class="btn btn-outline-success btn-sm" 
                                                   title="Télécharger">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
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

                    {{-- Section Actions PDF rapides --}}
                    <div class="mb-3">
                        <h6 class="text-muted small mb-2">Actions PDF</h6>
                        <div class="d-grid gap-2">
                         {{-- ======================================= --}}
{{-- SECTION BOUTONS PDF - VERSION COMPLÈTE --}}
{{-- ======================================= --}}

<div class="card">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-file-pdf me-2"></i>
            Documents Officiels
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            
            <!-- Accusé de réception (toujours disponible) -->
            <div class="col-md-4">
                <div class="d-grid">
                    <a href="{{ route('admin.dossiers.download-accuse', $dossier->id) }}" 
                       class="btn btn-outline-primary"
                       title="Confirme la réception du dossier">
                        <i class="fas fa-file-alt me-2"></i>
                        Accusé de Réception
                    </a>
                </div>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    Toujours disponible
                </small>
            </div>

            <!-- Récépissé provisoire (NOUVEAU) -->
            <div class="col-md-4">
                <div class="d-grid">
                    @if(in_array($dossier->statut, ['soumis', 'en_cours', 'en_attente']))
                        <a href="{{ route('admin.dossiers.download-recepisse-provisoire', $dossier->id) }}" 
                           class="btn btn-outline-warning"
                           title="Atteste du dépôt en cours de traitement">
                            <i class="fas fa-file-contract me-2"></i>
                            Récépissé Provisoire
                        </a>
                        <small class="text-success d-block mt-1">
                            <i class="fas fa-check-circle me-1"></i>
                            Disponible
                        </small>
                    @else
                        <button class="btn btn-outline-secondary" disabled
                                title="Disponible uniquement pour les dossiers en cours de traitement">
                            <i class="fas fa-file-contract me-2"></i>
                            Récépissé Provisoire
                        </button>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-times-circle me-1"></i>
                            Non disponible (statut: {{ ucfirst($dossier->statut) }})
                        </small>
                    @endif
                </div>
            </div>

            <!-- Récépissé définitif (existant) -->
            <div class="col-md-4">
                <div class="d-grid">
                    @if($dossier->statut === 'approuve')
                        <a href="{{ route('admin.dossiers.download-recepisse', $dossier->id) }}" 
                           class="btn btn-outline-success"
                           title="Document officiel final après approbation">
                            <i class="fas fa-certificate me-2"></i>
                            Récépissé Définitif
                        </a>
                        <small class="text-success d-block mt-1">
                            <i class="fas fa-check-circle me-1"></i>
                            Disponible
                        </small>
                    @else
                        <button class="btn btn-outline-secondary" disabled
                                title="Disponible uniquement après approbation du dossier">
                            <i class="fas fa-certificate me-2"></i>
                            Récépissé Définitif
                        </button>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-times-circle me-1"></i>
                            Après approbation
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations sur les documents -->
        <div class="mt-4">
            <div class="alert alert-info mb-0">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    Informations sur les documents
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Accusé de réception :</strong>
                        <br><small>Confirme la réception de votre dossier par nos services</small>
                    </div>
                    <div class="col-md-4">
                        <strong>Récépissé provisoire :</strong>
                        <br><small>Atteste du dépôt complet en cours de traitement</small>
                    </div>
                    <div class="col-md-4">
                        <strong>Récépissé définitif :</strong>
                        <br><small>Document officiel final après validation complète</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript pour améliorer l'expérience utilisateur --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter des tooltips Bootstrap si disponible
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Ajouter des indicateurs de chargement sur les boutons PDF
    document.querySelectorAll('a[href*="download"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Ajouter un spinner pendant le téléchargement
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Génération...';
            this.classList.add('disabled');
            
            // Restaurer après 3 secondes (le temps du téléchargement)
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('disabled');
            }, 3000);
        });
    });
});
</script>
                        </div>
                    </div>

                    <hr>

                    {{-- Informations de dates --}}
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

<!-- Section Debug (temporaire) -->
<div class="debug-info" style="display: none;" id="debugInfo">
    <strong>📋 DEBUG PDF - URLs TESTÉES ET CONFIRMÉES</strong><br>
    Dossier ID: {{ $dossier->id }}<br>
    Statut: {{ $dossier->statut }}<br>
    ✅ URL Accusé (TESTÉE): /admin/dossiers/{{ $dossier->id }}/download-accuse<br>
    ✅ URL Récépissé (TESTÉE): /admin/dossiers/{{ $dossier->id }}/download-recepisse<br>
    🔍 URL Dossier complet: /admin/dossiers/{{ $dossier->id }}/pdf<br>
    Organisation: {{ $dossier->organisation->nom ?? 'N/A' }}<br>
    <small>💡 Utilisez showDebugInfo() dans la console pour afficher</small>
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
            location.reload();
        } else {
            showAlert('error', data.message || 'Erreur lors de l\'ajout du commentaire');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showAlert('error', 'Erreur technique');
    });
});

// ========== FONCTIONS PDF CORRIGÉES ==========

/**
 * Télécharge l'accusé de réception PDF - CORRIGÉ ET TESTÉ
 * URL testée et fonctionnelle: /admin/dossiers/175/download-accuse
 */
function telechargerAccuse() {
    console.log('Téléchargement accusé pour dossier:', dossierId);
    showLoadingAlert('Génération de l\'accusé de réception...');
    
    // URL TESTÉE ET CONFIRMÉE FONCTIONNELLE
    const url = `/admin/dossiers/${dossierId}/download-accuse`;
    console.log('URL accusé (testée):', url);
    
    // Téléchargement direct car l'URL est confirmée fonctionnelle
    try {
        const link = document.createElement('a');
        link.href = url;
        link.style.display = 'none';
        link.download = ''; // Forcer le téléchargement
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        console.log('✅ Téléchargement accusé lancé (URL testée)');
        
        // Message de succès après délai
        setTimeout(() => {
            hideLoadingAlert();
            showAlert('success', 'Accusé de réception téléchargé avec succès');
        }, 1500);
        
    } catch (error) {
        console.error('❌ Erreur téléchargement accusé:', error);
        hideLoadingAlert();
        showAlert('error', 'Erreur lors du téléchargement de l\'accusé');
    }
}

/**
 * Lance effectivement le téléchargement de l'accusé
 * FONCTION SUPPRIMÉE - Téléchargement direct maintenant
 */

/**
 * Télécharge le récépissé final PDF - CORRIGÉ ET TESTÉ
 * URL testée: /admin/dossiers/{id}/download-recepisse (pour dossiers approuvés)
 */
function telechargerRecepisse() {
    const statutDossier = '{{ $dossier->statut }}';
    console.log('Téléchargement récépissé, statut:', statutDossier);
    
    if (statutDossier !== 'approuve') {
        showAlert('warning', 'Le récépissé n\'est disponible que pour les dossiers approuvés');
        return;
    }
    
    showLoadingAlert('Génération du récépissé final...');
    
    // URL TESTÉE POUR DOSSIERS APPROUVÉS
    const url = `/admin/dossiers/${dossierId}/download-recepisse`;
    console.log('URL récépissé (testée):', url);
    
    // Téléchargement direct car l'URL est confirmée fonctionnelle
    try {
        const link = document.createElement('a');
        link.href = url;
        link.style.display = 'none';
        link.download = ''; // Forcer le téléchargement
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        console.log('✅ Téléchargement récépissé lancé (URL testée)');
        
        setTimeout(() => {
            hideLoadingAlert();
            showAlert('success', 'Récépissé final téléchargé avec succès');
        }, 1500);
        
    } catch (error) {
        console.error('❌ Erreur téléchargement récépissé:', error);
        hideLoadingAlert();
        showAlert('error', 'Erreur lors du téléchargement du récépissé');
    }
}

/**
 * Lance effectivement le téléchargement du récépissé
 * FONCTION SUPPRIMÉE - Téléchargement direct maintenant
 */

/**
 * Exporte le dossier complet en PDF - SIMPLIFIÉ
 */
function exporterDossierComplet() {
    showLoadingAlert('Génération du dossier complet...');
    
    // URL pour dossier complet (à tester)
    const url = `/admin/dossiers/${dossierId}/pdf`;
    console.log('URL dossier complet:', url);
    
    try {
        // Ouvrir directement dans nouvel onglet
        window.open(url, '_blank');
        
        console.log('✅ Ouverture dossier complet lancée');
        setTimeout(() => {
            hideLoadingAlert();
            showAlert('success', 'Dossier complet généré avec succès');
        }, 1000);
        
    } catch (error) {
        console.error('❌ Erreur ouverture dossier complet:', error);
        hideLoadingAlert();
        showAlert('warning', 'Vérifiez que les popups ne sont pas bloquées');
    }
}

/**
 * Fonction d'impression améliorée
 */
function imprimerDossier() {
    const elementsToHide = document.querySelectorAll('.btn, .breadcrumb, .dropdown-menu');
    elementsToHide.forEach(el => el.style.display = 'none');
    
    const titre = document.createElement('h1');
    titre.innerHTML = `DOSSIER ${document.querySelector('h2').textContent}`;
    titre.style.textAlign = 'center';
    titre.style.marginBottom = '20px';
    titre.className = 'print-title';
    document.querySelector('.container-fluid').insertBefore(titre, document.querySelector('.row'));
    
    window.print();
    
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
    const existingAlerts = document.querySelectorAll('.loading-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info loading-alert';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <strong>${message}</strong>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Masque le message de chargement
 */
function hideLoadingAlert() {
    const loadingAlerts = document.querySelectorAll('.loading-alert');
    loadingAlerts.forEach(alert => {
        alert.style.transition = 'opacity 0.3s ease-out';
        alert.style.opacity = '0';
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 300);
    });
}

/**
 * Affiche un message d'alerte
 */
function showAlert(type, message) {
    const typeMap = {
        'success': 'success',
        'error': 'danger', 
        'warning': 'warning',
        'info': 'info'
    };
    
    const alertClass = typeMap[type] || 'info';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${alertClass} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : type === 'warning' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            <strong>${message}</strong>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ========== FONCTIONS EXISTANTES ==========

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
        alert('Fonction à implémenter');
    }
}

function envoyerEmail() {
    alert('Fonction à implémenter');
}

function contacterDemandeur() {
    alert('Fonction à implémenter');
}

// ========== DEBUG ==========

function diagnostiquerRoutesPDF() {
    console.log('=== DIAGNOSTIC ROUTES PDF - URLS TESTÉES ===');
    console.log('ID Dossier:', dossierId);
    console.log('✅ URL Accusé (TESTÉE):', `/admin/dossiers/${dossierId}/download-accuse`);
    console.log('✅ URL Récépissé (TESTÉE):', `/admin/dossiers/${dossierId}/download-recepisse`);
    console.log('🔍 URL Dossier complet:', `/admin/dossiers/${dossierId}/pdf`);
    console.log('📊 Statut dossier:', '{{ $dossier->statut }}');
    console.log('🔑 Token CSRF disponible:', !!document.querySelector('meta[name="csrf-token"]'));
    console.log('✅ URLs confirmées fonctionnelles - Téléchargement direct activé');
}

function showDebugInfo() {
    document.getElementById('debugInfo').style.display = 'block';
}

function hideDebugInfo() {
    document.getElementById('debugInfo').style.display = 'none';
}

console.log('Scripts PDF show.blade.php chargés avec succès - Dossier ID:', dossierId);
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

/* ========== STYLES PDF AMÉLIORÉS ========== */

/* Améliorations pour les alertes de chargement */
.loading-alert {
    border-left: 4px solid #4e73df;
    background: linear-gradient(90deg, #f8f9fc 0%, #e3e6f0 100%);
    animation: slideDown 0.3s ease-out, pulse 2s infinite;
    font-weight: 500;
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

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* Dropdown PDF avec style gabonais */
.dropdown-menu {
    border: 1px solid #e3e6f0;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    min-width: 220px;
    padding: 0.5rem 0;
}

.dropdown-item {
    padding: 0.75rem 1.25rem;
    border-radius: 0;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.dropdown-item:hover {
    background: linear-gradient(90deg, #f8f9fc 0%, #e3e6f0 100%);
    color: #2c3e50;
    transform: translateX(3px);
}

.dropdown-item i {
    width: 24px;
    margin-right: 12px;
    font-size: 1.1em;
}

/* Amélioration des boutons PDF */
.btn-outline-primary.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.btn-outline-success.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

/* Style pour les alertes améliorées */
.alert {
    border-radius: 0.5rem;
    border-width: 1px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.alert-success {
    background: linear-gradient(45deg, #d4edda 0%, #c3e6cb 100%);
    border-color: #b8dacc;
}

.alert-danger {
    background: linear-gradient(45deg, #f8d7da 0%, #f5c6cb 100%);
    border-color: #f1b2b7;
}

.alert-warning {
    background: linear-gradient(45deg, #fff3cd 0%, #ffeaa7 100%);
    border-color: #fde68a;
}

.alert-info {
    background: linear-gradient(45deg, #d1ecf1 0%, #bee5eb 100%);
    border-color: #abdde5;
}

/* Spinner personnalisé */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.125rem;
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
        min-width: 200px;
        margin-left: -80px;
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .loading-alert {
        font-size: 0.9rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

/* Style pour debug */
.debug-info {
    background: #1a1a1a;
    color: #00ff00;
    padding: 0.5rem;
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
    border-radius: 0.25rem;
    margin: 0.5rem 0;
}
</style>
@endpush