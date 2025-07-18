@extends('layouts.admin')

@section('title', 'Import Base NIP')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-upload text-primary"></i>
                    Import Base NIP
                </h1>
                <a href="{{ route('admin.nip-database.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </div>
    </div>

    <!-- Messages d'alerte -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            
            @if(session('import_stats'))
                <hr>
                <h6 class="mb-2">Détails de l'import :</h6>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>Total lignes :</strong> {{ session('import_stats')['total_rows'] }}
                    </div>
                    <div class="col-sm-3">
                        <strong>Importés :</strong> 
                        <span class="text-success">{{ session('import_stats')['imported'] }}</span>
                    </div>
                    <div class="col-sm-3">
                        <strong>Mis à jour :</strong> 
                        <span class="text-info">{{ session('import_stats')['updated'] }}</span>
                    </div>
                    <div class="col-sm-3">
                        <strong>Erreurs :</strong> 
                        <span class="text-danger">{{ session('import_stats')['errors'] }}</span>
                    </div>
                </div>
                
                @if(session('import_stats')['errors'] > 0 && !empty(session('import_stats')['error_details']))
                    <div class="mt-3">
                        <details>
                            <summary class="text-danger">
                                <strong>Détail des erreurs ({{ count(session('import_stats')['error_details']) }} premières)</strong>
                            </summary>
                            <div class="mt-2">
                                @foreach(array_slice(session('import_stats')['error_details'], 0, 10) as $error)
                                    <div class="text-small">
                                        <strong>Ligne {{ $error['row'] }} :</strong> {{ $error['error'] }}
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    </div>
                @endif
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Formulaire d'import -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-excel"></i>
                        Import fichier Excel
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" 
                          action="{{ route('admin.nip-database.process-import') }}" 
                          enctype="multipart/form-data"
                          id="importForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="excel_file" class="form-label">
                                <strong>Fichier Excel (.xlsx ou .xls)</strong>
                            </label>
                            <input type="file" 
                                   class="form-control @error('excel_file') is-invalid @enderror" 
                                   id="excel_file" 
                                   name="excel_file"
                                   accept=".xlsx,.xls"
                                   required>
                            @error('excel_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info"></i>
                                Taille maximum : 50MB. Formats acceptés : .xlsx, .xls
                            </div>
                        </div>

                        <!-- Aperçu du fichier sélectionné -->
                        <div id="file-preview" class="mb-4" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-excel fa-2x text-success me-3"></i>
                                    <div>
                                        <strong>Fichier sélectionné :</strong>
                                        <div id="file-name" class="text-muted"></div>
                                        <div id="file-size" class="text-small"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Options d'import -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-cogs"></i>
                                    Options d'import
                                </h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="update_existing"
                                           name="update_existing"
                                           checked>
                                    <label class="form-check-label" for="update_existing">
                                        Mettre à jour les NIP existants
                                    </label>
                                    <div class="form-text">
                                        Si décoché, les NIP existants seront ignorés
                                    </div>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="validate_dates"
                                           name="validate_dates"
                                           checked>
                                    <label class="form-check-label" for="validate_dates">
                                        Valider la cohérence des dates
                                    </label>
                                    <div class="form-text">
                                        Vérifier que la date Excel correspond au NIP
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.nip-database.template') }}" 
                               class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-download"></i>
                                Télécharger Template
                            </a>
                            <button type="submit" 
                                    class="btn btn-primary" 
                                    id="submitBtn">
                                <i class="fas fa-upload"></i>
                                <span id="submitText">Lancer l'import</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-info-circle"></i>
                        Instructions
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">Format du fichier Excel :</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr style="font-size: 0.8rem;">
                                    <th>Colonne</th>
                                    <th>En-tête</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.8rem;">
                                <tr><td>A</td><td>Nom</td></tr>
                                <tr><td>B</td><td>Prénom</td></tr>
                                <tr><td>C</td><td>Date de naissance</td></tr>
                                <tr><td>D</td><td>Lieu de naissance</td></tr>
                                <tr><td>E</td><td>NIP</td></tr>
                                <tr><td>F</td><td>Statut</td></tr>
                                <tr><td>G</td><td>Téléphone</td></tr>
                                <tr><td>H</td><td>Email</td></tr>
                                <tr><td>I</td><td>Remarques</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="text-primary">Règles importantes :</h6>
                    <ul class="small">
                        <li><strong>Format NIP :</strong> XX-QQQQ-YYYYMMDD</li>
                        <li><strong>Colonnes obligatoires :</strong> Nom, Prénom, NIP</li>
                        <li><strong>Statut :</strong> actif, inactif, decede, suspendu</li>
                        <li><strong>Date :</strong> Format DD/MM/YYYY ou YYYY-MM-DD</li>
                        <li><strong>Unicité :</strong> Chaque NIP doit être unique</li>
                    </ul>

                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention :</strong> L'import peut prendre plusieurs minutes pour de gros fichiers. 
                        Ne fermez pas la page pendant le traitement.
                    </div>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-pie"></i>
                        État actuel de la base
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 mb-0 text-primary">
                                    {{ number_format(\App\Models\NipDatabase::count()) }}
                                </div>
                                <small class="text-muted">Total NIP</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success">
                                {{ number_format(\App\Models\NipDatabase::where('statut', 'actif')->count()) }}
                            </div>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                    
                    @php
                        $lastImport = \App\Models\NipDatabase::latest('date_import')->first();
                    @endphp
                    
                    @if($lastImport)
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i>
                            Dernier import : {{ $lastImport->date_import->format('d/m/Y à H:i') }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Prévisualisation du fichier sélectionné
    $('#excel_file').on('change', function() {
        const file = this.files[0];
        const preview = $('#file-preview');
        
        if (file) {
            const fileName = file.name;
            const fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            
            $('#file-name').text(fileName);
            $('#file-size').text('Taille: ' + fileSize);
            preview.show();
            
            // Validation de la taille
            if (file.size > 50 * 1024 * 1024) {
                $(this).addClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
                $(this).after('<div class="invalid-feedback">Le fichier est trop volumineux (max 50MB)</div>');
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        } else {
            preview.hide();
        }
    });

    // Gestion de la soumission du formulaire
    $('#importForm').on('submit', function() {
        const submitBtn = $('#submitBtn');
        const submitText = $('#submitText');
        
        // Désactiver le bouton et changer le texte
        submitBtn.prop('disabled', true);
        submitText.html('<i class="fas fa-spinner fa-spin"></i> Import en cours...');
        
        // Afficher une alerte de patience
        const alertHtml = `
            <div class="alert alert-info alert-dismissible fade show" id="processing-alert">
                <i class="fas fa-info-circle"></i>
                <strong>Import en cours...</strong> Cette opération peut prendre plusieurs minutes. 
                Veuillez patienter et ne pas fermer cette page.
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        `;
        
        $('.container-fluid').prepend(alertHtml);
        
        // Scroll vers le haut pour voir l'alerte
        $('html, body').animate({ scrollTop: 0 }, 500);
    });
});
</script>
@endpush