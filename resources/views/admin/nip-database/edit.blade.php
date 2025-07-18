@extends('layouts.admin')

@section('title', 'Modifier NIP - ' . $nip->nip)

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-edit text-warning"></i>
                    Modifier NIP : <code class="text-primary">{{ $nip->nip }}</code>
                </h1>
                <div class="btn-group">
                    <a href="{{ route('admin.nip-database.show', $nip) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour aux détails
                    </a>
                    <a href="{{ route('admin.nip-database.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> Liste des NIP
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulaire d'édition -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-user-edit"></i>
                        Modification des informations
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.nip-database.update', $nip) }}" id="editForm">
                        @csrf
                        @method('PUT')

                        <!-- Informations non modifiables -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Information :</strong> Le NIP, la date de naissance et le sexe sont 
                            extraits automatiquement du numéro et ne peuvent pas être modifiés.
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted">NIP (non modifiable)</label>
                                <div class="form-control-plaintext">
                                    <code class="text-primary fs-6">{{ $nip->nip }}</code>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted">Date de naissance</label>
                                <div class="form-control-plaintext">
                                    {{ $nip->date_naissance->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted">Sexe</label>
                                <div class="form-control-plaintext">
                                    <i class="fas fa-{{ $nip->sexe == 'M' ? 'mars text-primary' : 'venus text-danger' }} me-2"></i>
                                    {{ $nip->sexe == 'M' ? 'Homme' : 'Femme' }}
                                </div>
                            </div>
                        </div>

                        <!-- Champs modifiables -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">
                                    <span class="text-danger">*</span> Nom
                                </label>
                                <input type="text" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" 
                                       name="nom"
                                       value="{{ old('nom', $nip->nom) }}"
                                       required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">
                                    <span class="text-danger">*</span> Prénom
                                </label>
                                <input type="text" 
                                       class="form-control @error('prenom') is-invalid @enderror" 
                                       id="prenom" 
                                       name="prenom"
                                       value="{{ old('prenom', $nip->prenom) }}"
                                       required>
                                @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="lieu_naissance" class="form-label">Lieu de naissance</label>
                                <input type="text" 
                                       class="form-control @error('lieu_naissance') is-invalid @enderror" 
                                       id="lieu_naissance" 
                                       name="lieu_naissance"
                                       value="{{ old('lieu_naissance', $nip->lieu_naissance) }}"
                                       placeholder="Ex: Libreville, Port-Gentil...">
                                @error('lieu_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="statut" class="form-label">
                                    <span class="text-danger">*</span> Statut
                                </label>
                                <select class="form-select @error('statut') is-invalid @enderror" 
                                        id="statut" 
                                        name="statut"
                                        required>
                                    <option value="actif" {{ old('statut', $nip->statut) == 'actif' ? 'selected' : '' }}>
                                        Actif
                                    </option>
                                    <option value="inactif" {{ old('statut', $nip->statut) == 'inactif' ? 'selected' : '' }}>
                                        Inactif
                                    </option>
                                    <option value="suspendu" {{ old('statut', $nip->statut) == 'suspendu' ? 'selected' : '' }}>
                                        Suspendu
                                    </option>
                                    <option value="decede" {{ old('statut', $nip->statut) == 'decede' ? 'selected' : '' }}>
                                        Décédé
                                    </option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" 
                                       class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" 
                                       name="telephone"
                                       value="{{ old('telephone', $nip->telephone) }}"
                                       placeholder="Ex: 066123456">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email"
                                       value="{{ old('email', $nip->email) }}"
                                       placeholder="Ex: nom.prenom@email.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="remarques" class="form-label">Remarques</label>
                            <textarea class="form-control @error('remarques') is-invalid @enderror" 
                                      id="remarques" 
                                      name="remarques"
                                      rows="3"
                                      placeholder="Notes ou remarques administratives...">{{ old('remarques', $nip->remarques) }}</textarea>
                            @error('remarques')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-danger">*</span> 
                                <small class="text-muted">Champs obligatoires</small>
                            </div>
                            <div>
                                <a href="{{ route('admin.nip-database.show', $nip) }}" 
                                   class="btn btn-secondary me-2">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar avec informations -->
        <div class="col-lg-4">
            <!-- Informations actuelles -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle"></i>
                        Informations actuelles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Nom complet actuel</label>
                        <div class="text-dark h6">{{ $nip->nom_complet }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Âge</label>
                        <div class="text-dark">
                            <span class="badge bg-info">{{ $nip->age }} ans</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Statut actuel</label>
                        <div>
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
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Dernière modification</label>
                        <div class="text-dark">
                            {{ $nip->updated_at->format('d/m/Y à H:i') }}
                            <br><small class="text-muted">
                                Il y a {{ $nip->updated_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aide et conseils -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-lightbulb"></i>
                        Aide et conseils
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">Bonnes pratiques :</h6>
                    <ul class="small">
                        <li><strong>Nom :</strong> Toujours en MAJUSCULES</li>
                        <li><strong>Prénom :</strong> Première lettre en majuscule</li>
                        <li><strong>Lieu :</strong> Ville principale (ex: Libreville)</li>
                        <li><strong>Téléphone :</strong> Format gabonais (066xxxxxx)</li>
                        <li><strong>Email :</strong> Vérifiez la validité</li>
                    </ul>

                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important :</strong> Les modifications sont automatiquement 
                        marquées avec la date et l'heure de modification.
                    </div>

                    <h6 class="text-primary">Statuts disponibles :</h6>
                    <ul class="small">
                        <li><span class="badge bg-success">Actif</span> - NIP valide et utilisable</li>
                        <li><span class="badge bg-secondary">Inactif</span> - NIP temporairement désactivé</li>
                        <li><span class="badge bg-warning">Suspendu</span> - NIP suspendu administrativement</li>
                        <li><span class="badge bg-dark">Décédé</span> - Personne décédée</li>
                    </ul>
                </div>
            </div>

            <!-- Utilisation dans les organisations -->
            @if(isset($adherents) && $adherents->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-exclamation-circle"></i>
                            Attention - NIP utilisé
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning small">
                            <i class="fas fa-info-circle"></i>
                            Ce NIP est utilisé dans <strong>{{ $adherents->count() }} organisation(s)</strong>. 
                            Les modifications seront répercutées automatiquement.
                        </div>
                        
                        <h6 class="small">Organisations concernées :</h6>
                        <ul class="small">
                            @foreach($adherents->take(3) as $adherent)
                                <li>{{ $adherent->organisation->nom }}</li>
                            @endforeach
                            @if($adherents->count() > 3)
                                <li class="text-muted">... et {{ $adherents->count() - 3 }} autre(s)</li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Formatage automatique du nom en majuscules
    $('#nom').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Formatage automatique du prénom
    $('#prenom').on('input', function() {
        let prenom = $(this).val().toLowerCase();
        prenom = prenom.charAt(0).toUpperCase() + prenom.slice(1);
        $(this).val(prenom);
    });

    // Formatage du téléphone
    $('#telephone').on('input', function() {
        let tel = $(this).val().replace(/\D/g, ''); // Supprimer tout sauf les chiffres
        if (tel.length > 0) {
            // Format gabonais : 0XX XX XX XX
            if (tel.length > 3) {
                tel = tel.substring(0, 3) + ' ' + tel.substring(3);
            }
            if (tel.length > 6) {
                tel = tel.substring(0, 6) + ' ' + tel.substring(6);
            }
            if (tel.length > 9) {
                tel = tel.substring(0, 9) + ' ' + tel.substring(9, 11);
            }
        }
        $(this).val(tel);
    });

    // Validation du formulaire
    $('#editForm').on('submit', function(e) {
        let isValid = true;
        let errors = [];

        // Validation nom
        if ($('#nom').val().trim() === '') {
            errors.push('Le nom est obligatoire');
            isValid = false;
        }

        // Validation prénom
        if ($('#prenom').val().trim() === '') {
            errors.push('Le prénom est obligatoire');
            isValid = false;
        }

        // Validation email si renseigné
        const email = $('#email').val().trim();
        if (email && !isValidEmail(email)) {
            errors.push('Format d\'email invalide');
            isValid = false;
        }

        // Validation téléphone si renseigné
        const telephone = $('#telephone').val().replace(/\D/g, '');
        if (telephone && (telephone.length < 8 || telephone.length > 9)) {
            errors.push('Numéro de téléphone invalide');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Erreurs de validation :\n' + errors.join('\n'));
        }
    });

    // Alerte avant quitter sans sauvegarder
    let formChanged = false;
    
    $('#editForm input, #editForm select, #editForm textarea').on('change', function() {
        formChanged = true;
    });

    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter ?';
        }
    });

    $('#editForm').on('submit', function() {
        formChanged = false; // Éviter l'alerte lors de la soumission
    });
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
</script>
@endpush