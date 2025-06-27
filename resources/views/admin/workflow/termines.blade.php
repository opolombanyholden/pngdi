@extends('layouts.admin')

@section('title', 'Dossiers Terminés')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise verte pour "Terminés" -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                Dossiers Terminés
                            </h2>
                            <p class="mb-0 opacity-90">Historique des dossiers validés et rejetés avec outils d'analyse</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6 me-3">
                                {{ $totalTermines ?? 0 }} dossiers
                            </span>
                            <button onclick="genererRapport()" class="btn btn-light btn-lg me-2">
                                <i class="fas fa-chart-bar me-2"></i>
                                Rapport
                            </button>
                            <button onclick="refreshDossiers()" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sync me-2"></i>
                                Actualiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Cards avec style gabonais amélioré -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $totalValides ?? 45 }}</h3>
                            <p class="mb-0 small opacity-90">Validés</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-check fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 85%"></div>
                    </div>
                    <small class="opacity-75 mt-1 d-block">
                        <i class="fas fa-arrow-up me-1"></i>+12% ce mois
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $totalRejetes ?? 8 }}</h3>
                            <p class="mb-0 small opacity-90">Rejetés</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-times fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 15%"></div>
                    </div>
                    <small class="opacity-75 mt-1 d-block">
                        <i class="fas fa-arrow-down me-1"></i>-5% ce mois
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $tauxValidation ?? 85 }}%</h3>
                            <p class="mb-0 small opacity-90">Taux de Validation</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-chart-pie fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: {{ $tauxValidation ?? 85 }}%"></div>
                    </div>
                    <small class="opacity-75 mt-1 d-block">
                        Objectif: 90%
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $tempsTraitementMoyen ?? 3.2 }}j</h3>
                            <p class="mb-0 small">Temps Moyen</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: 65%"></div>
                    </div>
                    <small class="opacity-75 mt-1 d-block">
                        Cible: ≤5 jours
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Actions améliorés -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher..." id="searchInput" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select border-0 bg-light" id="filterStatut">
                                <option value="">Tous les statuts</option>
                                <option value="approuve" {{ request('statut') == 'approuve' ? 'selected' : '' }}>Approuvé</option>
                                <option value="rejete" {{ request('statut') == 'rejete' ? 'selected' : '' }}>Rejeté</option>
                            </select>
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
                            <select class="form-select border-0 bg-light" id="filterPeriode">
                                <option value="">Toutes périodes</option>
                                <option value="aujourd_hui" {{ request('periode') == 'aujourd_hui' ? 'selected' : '' }}>Aujourd'hui</option>
                                <option value="cette_semaine" {{ request('periode') == 'cette_semaine' ? 'selected' : '' }}>Cette semaine</option>
                                <option value="ce_mois" {{ request('periode') == 'ce_mois' ? 'selected' : '' }}>Ce mois</option>
                                <option value="ce_trimestre" {{ request('periode') == 'ce_trimestre' ? 'selected' : '' }}>Ce trimestre</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-success" onclick="exporterSelection()">
                                    <i class="fas fa-download me-2"></i>Exporter
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="genererRapport()">
                                    <i class="fas fa-chart-bar me-2"></i>Rapport
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="archiverSelection()">
                                    <i class="fas fa-archive me-2"></i>Archiver
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions en lot améliorées -->
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
                                <button onclick="exporterSelection()" class="btn btn-success" disabled id="btnExporter">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                                <button onclick="exporterPDF()" class="btn btn-danger" disabled id="btnPDF">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </button>
                                <button onclick="genererCertificats()" class="btn btn-info" disabled id="btnCertificats">
                                    <i class="fas fa-certificate me-1"></i>Certificats
                                </button>
                                <button onclick="archiverSelection()" class="btn btn-warning" disabled id="btnArchiver">
                                    <i class="fas fa-archive me-1"></i>Archiver
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Dossiers Terminés -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle me-2" style="color: #009e3f;"></i>
                            Historique des Dossiers Terminés
                        </h5>
                        <span class="badge bg-success">{{ $totalTermines ?? 53 }} dossiers</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($dossiersTermines) && count($dossiersTermines) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">
                                            <input type="checkbox" class="form-check-input" id="selectAllTable">
                                        </th>
                                        <th class="border-0">Dossier</th>
                                        <th class="border-0">Organisation</th>
                                        <th class="border-0">Statut Final</th>
                                        <th class="border-0">Agent Traitant</th>
                                        <th class="border-0">Date Finalisation</th>
                                        <th class="border-0">Durée</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dossiersTermines ?? [] as $dossier)
                                    <tr class="dossier-row">
                                        <td>
                                            <input type="checkbox" class="form-check-input dossier-checkbox" value="{{ $dossier->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="dossier-status-icon me-3">
                                                    @if($dossier->statut === 'approuve')
                                                        <div class="status-circle bg-success">
                                                            <i class="fas fa-check text-white"></i>
                                                        </div>
                                                    @else
                                                        <div class="status-circle bg-danger">
                                                            <i class="fas fa-times text-white"></i>
                                                        </div>
                                                    @endif
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
                                        <td>
                                            @if($dossier->statut === 'approuve')
                                                <div class="d-flex flex-column">
                                                    <span class="badge bg-success mb-1">
                                                        <i class="fas fa-check me-1"></i>Approuvé
                                                    </span>
                                                    @if($dossier->numero_recepisse ?? null)
                                                        <small class="text-success fw-bold">{{ $dossier->numero_recepisse }}</small>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="d-flex flex-column">
                                                    <span class="badge bg-danger mb-1">
                                                        <i class="fas fa-times me-1"></i>Rejeté
                                                    </span>
                                                    @if($dossier->motif_rejet ?? null)
                                                        <small class="text-danger" title="{{ $dossier->motif_rejet }}">
                                                            {{ Str::limit($dossier->motif_rejet, 20) }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($dossier->validated_by ?? null)
                                                <div class="d-flex align-items-center">
                                                    <div class="agent-avatar me-2">
                                                        <div class="avatar-circle bg-primary text-white">
                                                            {{ substr($dossier->validatedBy->name ?? 'A', 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <small class="fw-bold">{{ $dossier->validatedBy->name ?? 'Agent' }}</small>
                                                        <br><small class="text-muted">{{ $dossier->validatedBy->email ?? '' }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">Non renseigné</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="text-sm">
                                                <strong>{{ $dossier->validated_at ? $dossier->validated_at->format('d/m/Y') : 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $dossier->validated_at ? $dossier->validated_at->format('H:i') : '' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $dureeJours = $dossier->duree_traitement_jours ?? 
                                                    ($dossier->validated_at && $dossier->created_at ? 
                                                        $dossier->created_at->diffInDays($dossier->validated_at) : 0);
                                                $couleur = $dureeJours > 5 ? 'danger' : ($dureeJours > 3 ? 'warning' : 'success');
                                            @endphp
                                            <div class="text-center">
                                                <div class="performance-indicator bg-{{ $couleur }}">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>{{ $dureeJours }}j</strong>
                                                </div>
                                                <small class="text-muted">
                                                    {{ $dureeJours > 5 ? 'En retard' : ($dureeJours > 3 ? 'Limite' : 'Rapide') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="voirDetails({{ $dossier->id }})" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($dossier->statut === 'approuve')
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="telechargerRecepisse({{ $dossier->id }})" title="Récépissé">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                @endif
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="Plus">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="voirHistorique({{ $dossier->id }})">
                                                            <i class="fas fa-history me-2"></i>Historique complet
                                                        </a></li>
                                                        @if($dossier->statut === 'approuve')
                                                            <li><a class="dropdown-item" href="#" onclick="genererCertificat({{ $dossier->id }})">
                                                                <i class="fas fa-certificate me-2"></i>Certificat
                                                            </a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="#" onclick="exporterDossier({{ $dossier->id }})">
                                                            <i class="fas fa-file-export me-2"></i>Exporter
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-warning" href="#" onclick="archiverDossier({{ $dossier->id }})">
                                                            <i class="fas fa-archive me-2"></i>Archiver
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="mb-4">
                                                <i class="fas fa-check-circle fa-5x text-muted opacity-50"></i>
                                            </div>
                                            <h4 class="text-muted mb-3">Aucun dossier terminé</h4>
                                            <p class="text-muted mb-4">Les dossiers validés et rejetés apparaîtront ici.</p>
                                            <div class="d-flex justify-content-center gap-3">
                                                <a href="{{ route('admin.workflow.en-cours') }}" class="btn btn-warning btn-lg">
                                                    <i class="fas fa-cogs me-2"></i>Voir dossiers en cours
                                                </a>
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
                        @if(isset($dossiersTermines) && method_exists($dossiersTermines, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $dossiersTermines->links() }}
                            </div>
                        @endif
                    @else
                        <!-- État vide premium -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-check-circle fa-5x text-muted opacity-50"></i>
                            </div>
                            <h4 class="text-muted mb-3">Aucun dossier terminé</h4>
                            <p class="text-muted mb-4">Les dossiers validés et rejetés apparaîtront ici avec leurs détails complets.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('admin.workflow.en-cours') }}" class="btn btn-warning btn-lg">
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

<!-- FAB (Floating Action Button) tricolore spécialisé terminés -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-tools fab-icon"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #009e3f;" title="Exporter sélection" onclick="exporterSelection()">
                <i class="fas fa-download"></i>
            </button>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Générer rapport" onclick="genererRapport()">
                <i class="fas fa-chart-bar"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Archiver sélection" onclick="archiverSelection()">
                <i class="fas fa-archive"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal Rapport Avancé -->
<div class="modal fade" id="rapportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar me-2"></i>Générer un Rapport Avancé
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rapportForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date de début <span class="text-danger">*</span></label>
                            <input type="date" id="dateDebut" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date de fin <span class="text-danger">*</span></label>
                            <input type="date" id="dateFin" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type de rapport <span class="text-danger">*</span></label>
                            <select id="typeRapport" class="form-select" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="synthese">Rapport de synthèse</option>
                                <option value="detaille">Rapport détaillé</option>
                                <option value="statistiques">Rapport statistiques</option>
                                <option value="performance">Rapport de performance</option>
                                <option value="comparatif">Analyse comparative</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Format d'export</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" name="formatExport" value="pdf" id="formatPDF" class="btn-check" checked>
                                <label class="btn btn-outline-primary" for="formatPDF">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </label>
                                <input type="radio" name="formatExport" value="excel" id="formatExcel" class="btn-check">
                                <label class="btn btn-outline-success" for="formatExcel">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </label>
                                <input type="radio" name="formatExport" value="word" id="formatWord" class="btn-check">
                                <label class="btn btn-outline-info" for="formatWord">
                                    <i class="fas fa-file-word me-1"></i>Word
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Filtre statut</label>
                            <select id="filtreStatutRapport" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="approuve">Approuvés uniquement</option>
                                <option value="rejete">Rejetés uniquement</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Filtre type organisation</label>
                            <select id="filtreTypeRapport" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="association">Associations</option>
                                <option value="ong">ONG</option>
                                <option value="parti_politique">Partis Politiques</option>
                                <option value="confession_religieuse">Confessions Religieuses</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Inclure dans le rapport</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" id="includeStats" class="form-check-input" checked>
                                    <label class="form-check-label" for="includeStats">Statistiques générales</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="includeGraphiques" class="form-check-input" checked>
                                    <label class="form-check-label" for="includeGraphiques">Graphiques</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" id="includeDetails" class="form-check-input">
                                    <label class="form-check-label" for="includeDetails">Détails dossiers</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="includePerformance" class="form-check-input" checked>
                                    <label class="form-check-label" for="includePerformance">Performance agents</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" id="includeRecommandations" class="form-check-input">
                                    <label class="form-check-label" for="includeRecommandations">Recommandations</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="includeAnnexes" class="form-check-input">
                                    <label class="form-check-label" for="includeAnnexes">Annexes</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="confirmerRapport()">
                    <i class="fas fa-chart-bar me-2"></i>Générer le Rapport
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historique Détaillé -->
<div class="modal fade" id="historiqueModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>Historique Détaillé du Dossier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="historiqueContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2 text-muted">Chargement de l'historique détaillé...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="exporterHistorique()">
                    <i class="fas fa-download me-2"></i>Exporter Historique
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles gabonais améliorés pour vue terminés */
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
    background-color: rgba(0, 158, 63, 0.05);
}

.status-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.agent-avatar {
    width: 32px;
    text-align: center;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
}

.performance-indicator {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 12px;
    color: white;
    font-size: 0.8rem;
}

/* FAB Style gabonais pour terminés */
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

/* Styles pour les badges et indicateurs */
.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

.text-sm {
    font-size: 0.9rem;
}

/* Modal améliorée */
.modal-xl {
    max-width: 1200px;
}

.btn-check:checked + .btn {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}
</style>

<script>
let selectedDossiers = [];

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

// Fonctions principales
function voirDetails(dossierId) {
    window.location.href = `/admin/dossiers/${dossierId}`;
}

function telechargerRecepisse(dossierId) {
    window.open(`/admin/dossiers/${dossierId}/recepisse`);
}

function genererCertificat(dossierId) {
    window.open(`/admin/dossiers/${dossierId}/certificat`);
}

function exporterDossier(dossierId) {
    window.open(`/admin/dossiers/${dossierId}/export`);
}

function archiverDossier(dossierId) {
    if (confirm('Êtes-vous sûr de vouloir archiver ce dossier ?')) {
        fetch(`/admin/workflow/archive/${dossierId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Dossier archivé avec succès');
                location.reload();
            } else {
                alert('Erreur lors de l\'archivage');
            }
        });
    }
}

function refreshDossiers() {
    location.reload();
}

function voirHistorique(dossierId) {
    document.getElementById('historiqueModal').classList.add('show');
    new bootstrap.Modal(document.getElementById('historiqueModal')).show();
    
    // Charger l'historique via AJAX
    fetch(`/admin/dossiers/${dossierId}/historique`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('historiqueContent').innerHTML = generateHistoriqueHTML(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('historiqueContent').innerHTML = 
                '<div class="text-center text-danger">Erreur lors du chargement de l\'historique</div>';
        });
}

function generateHistoriqueHTML(historique) {
    if (!historique || historique.length === 0) {
        return '<div class="text-center text-muted">Aucun historique disponible</div>';
    }
    
    let html = '<div class="timeline">';
    
    historique.forEach((item, index) => {
        const date = new Date(item.created_at).toLocaleDateString('fr-FR');
        const heure = new Date(item.created_at).toLocaleTimeString('fr-FR');
        
        html += `
            <div class="timeline-item">
                <div class="timeline-marker bg-primary"></div>
                <div class="timeline-content">
                    <h6 class="mb-1">${item.type_operation || 'Action'}</h6>
                    <p class="mb-1">${item.description || 'Aucune description'}</p>
                    <small class="text-muted">
                        Par: ${item.user_name || 'Système'} • ${date} à ${heure}
                    </small>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    return html;
}

// Gestion de la sélection multiple
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.dossier-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = `${count} sélectionné(s)`;
    
    // Activer/désactiver les boutons d'action
    const buttons = ['btnExporter', 'btnPDF', 'btnCertificats', 'btnArchiver'];
    buttons.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.disabled = count === 0;
        }
    });
    
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

// Fonctions de rapport
function genererRapport() {
    // Pré-remplir les dates avec le mois en cours
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    document.getElementById('dateDebut').value = firstDay.toISOString().split('T')[0];
    document.getElementById('dateFin').value = lastDay.toISOString().split('T')[0];
    
    new bootstrap.Modal(document.getElementById('rapportModal')).show();
}

function confirmerRapport() {
    const dateDebut = document.getElementById('dateDebut').value;
    const dateFin = document.getElementById('dateFin').value;
    const typeRapport = document.getElementById('typeRapport').value;
    const formatExport = document.querySelector('input[name="formatExport"]:checked').value;
    const filtreStatut = document.getElementById('filtreStatutRapport').value;
    const filtreType = document.getElementById('filtreTypeRapport').value;
    
    if (!dateDebut || !dateFin || !typeRapport) {
        alert('Veuillez remplir tous les champs obligatoires');
        return;
    }
    
    const params = new URLSearchParams({
        date_debut: dateDebut,
        date_fin: dateFin,
        type_rapport: typeRapport,
        format: formatExport
    });
    
    if (filtreStatut) params.append('filtre_statut', filtreStatut);
    if (filtreType) params.append('filtre_type', filtreType);
    
    // Ajouter les options incluses
    const options = [];
    if (document.getElementById('includeStats').checked) options.push('stats');
    if (document.getElementById('includeGraphiques').checked) options.push('graphiques');
    if (document.getElementById('includeDetails').checked) options.push('details');
    if (document.getElementById('includePerformance').checked) options.push('performance');
    if (document.getElementById('includeRecommandations').checked) options.push('recommandations');
    if (document.getElementById('includeAnnexes').checked) options.push('annexes');
    
    if (options.length > 0) {
        params.append('options', options.join(','));
    }
    
    window.open(`/admin/rapports/termines?${params.toString()}`);
    bootstrap.Modal.getInstance(document.getElementById('rapportModal')).hide();
}

// Actions en lot
function exporterSelection() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    const params = new URLSearchParams();
    params.append('dossiers', selectedDossiers.join(','));
    params.append('format', 'excel');
    
    window.open(`/admin/workflow/export-termines?${params.toString()}`);
}

function exporterPDF() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    const params = new URLSearchParams();
    params.append('dossiers', selectedDossiers.join(','));
    params.append('format', 'pdf');
    
    window.open(`/admin/workflow/export-termines?${params.toString()}`);
}

function genererCertificats() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    // Filtrer seulement les dossiers approuvés
    const dossiersApprouves = selectedDossiers.filter(id => {
        const row = document.querySelector(`input[value="${id}"]`).closest('tr');
        return row.textContent.includes('Approuvé');
    });
    
    if (dossiersApprouves.length === 0) {
        alert('Aucun dossier approuvé sélectionné. Les certificats ne peuvent être générés que pour les dossiers validés.');
        return;
    }
    
    const params = new URLSearchParams();
    params.append('dossiers', dossiersApprouves.join(','));
    
    window.open(`/admin/workflow/certificats-batch?${params.toString()}`);
}

function archiverSelection() {
    if (selectedDossiers.length === 0) {
        alert('Veuillez sélectionner au moins un dossier');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir archiver ${selectedDossiers.length} dossier(s) ?`)) {
        fetch('/admin/workflow/archive-multiple', {
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
                alert(`${data.archived_count} dossier(s) archivé(s) avec succès`);
                location.reload();
            } else {
                alert('Erreur lors de l\'archivage');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'archivage');
        });
    }
}

function exporterHistorique() {
    alert('Fonctionnalité d\'export d\'historique en cours de développement');
}

// Recherche et filtres
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.dossier-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

['filterStatut', 'filterType', 'filterPeriode'].forEach(filterId => {
    document.getElementById(filterId).addEventListener('change', function() {
        applyFilters();
    });
});

function applyFilters() {
    const statut = document.getElementById('filterStatut').value;
    const type = document.getElementById('filterType').value;
    const periode = document.getElementById('filterPeriode').value;
    
    const rows = document.querySelectorAll('.dossier-row');
    
    rows.forEach(row => {
        let show = true;
        
        if (statut && !row.textContent.toLowerCase().includes(statut)) show = false;
        if (type && !row.textContent.toLowerCase().includes(type)) show = false;
        // Le filtre période nécessiterait une logique plus complexe basée sur les dates
        
        row.style.display = show ? '' : 'none';
    });
}
</script>
@endsection