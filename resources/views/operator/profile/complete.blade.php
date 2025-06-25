@extends('layouts.operator')

@section('title', 'Compléter mon profil')
@section('page-title', 'Compléter mon profil')

@push('styles')
<style>
    .completion-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .completion-progress {
        background: rgba(255,255,255,0.2);
        height: 6px;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 1rem;
    }
    
    .completion-progress-bar {
        background: #ffd700;
        height: 100%;
        transition: width 0.3s ease;
    }
    
    .required-field {
        position: relative;
    }
    
    .required-field::after {
        content: '*';
        color: #dc3545;
        margin-left: 3px;
    }
    
    .profile-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .btn-complete {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        border-radius: 25px;
        padding: 12px 30px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-complete:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header de completion -->
    <div class="completion-header">
        <h2><i class="fas fa-user-edit me-2"></i>Finalisation de votre profil</h2>
        <p class="mb-3">Veuillez compléter les informations manquantes pour accéder à votre espace opérateur</p>
        <div class="completion-progress">
            <div class="completion-progress-bar" style="width: 75%"></div>
        </div>
        <small class="d-block mt-2">Progression : 75% complété</small>
    </div>

    <!-- Formulaire de completion -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="profile-card">
                <form method="POST" action="{{ route('operator.profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Champ caché pour indiquer qu'on vient de la page de completion -->
                    <input type="hidden" name="from_complete" value="1">

                    <!-- Informations personnelles -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-user me-2"></i>Informations personnelles
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Nom complet</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Adresse email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Téléphone</label>
                                <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                                       name="telephone" value="{{ old('telephone', $user->telephone) }}" 
                                       placeholder="+241 01 23 45 67" required>
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">NIP (Numéro d'Identification Personnel)</label>
                                <input type="text" class="form-control @error('nip') is-invalid @enderror" 
                                       name="nip" value="{{ old('nip', $user->nip) }}" 
                                       placeholder="Votre numéro NIP" required>
                                @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Informations complémentaires -->
                    <div class="mb-4">
                        <h5 class="text-secondary mb-3">
                            <i class="fas fa-map-marker-alt me-2"></i>Informations complémentaires (optionnel)
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" class="form-control @error('adresse') is-invalid @enderror" 
                                       name="adresse" value="{{ old('adresse', $user->adresse) }}" 
                                       placeholder="Votre adresse complète">
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ville</label>
                                <input type="text" class="form-control @error('ville') is-invalid @enderror" 
                                       name="ville" value="{{ old('ville', $user->ville) }}" 
                                       placeholder="Votre ville">
                                @error('ville')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Province</label>
                                <select class="form-select @error('province') is-invalid @enderror" name="province">
                                    <option value="">Sélectionner une province</option>
                                    <option value="Estuaire" {{ old('province', $user->province) == 'Estuaire' ? 'selected' : '' }}>Estuaire</option>
                                    <option value="Haut-Ogooué" {{ old('province', $user->province) == 'Haut-Ogooué' ? 'selected' : '' }}>Haut-Ogooué</option>
                                    <option value="Moyen-Ogooué" {{ old('province', $user->province) == 'Moyen-Ogooué' ? 'selected' : '' }}>Moyen-Ogooué</option>
                                    <option value="Ngounié" {{ old('province', $user->province) == 'Ngounié' ? 'selected' : '' }}>Ngounié</option>
                                    <option value="Nyanga" {{ old('province', $user->province) == 'Nyanga' ? 'selected' : '' }}>Nyanga</option>
                                    <option value="Ogooué-Ivindo" {{ old('province', $user->province) == 'Ogooué-Ivindo' ? 'selected' : '' }}>Ogooué-Ivindo</option>
                                    <option value="Ogooué-Lolo" {{ old('province', $user->province) == 'Ogooué-Lolo' ? 'selected' : '' }}>Ogooué-Lolo</option>
                                    <option value="Ogooué-Maritime" {{ old('province', $user->province) == 'Ogooué-Maritime' ? 'selected' : '' }}>Ogooué-Maritime</option>
                                    <option value="Woleu-Ntem" {{ old('province', $user->province) == 'Woleu-Ntem' ? 'selected' : '' }}>Woleu-Ntem</option>
                                </select>
                                @error('province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profession</label>
                                <input type="text" class="form-control @error('profession') is-invalid @enderror" 
                                       name="profession" value="{{ old('profession', $user->profession) }}" 
                                       placeholder="Votre profession">
                                @error('profession')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de naissance</label>
                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                       name="date_naissance" value="{{ old('date_naissance', $user->date_naissance) }}">
                                @error('date_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Informations importantes -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important :</strong> Les champs marqués d'un astérisque (*) sont obligatoires pour accéder à votre espace opérateur. 
                        Vous pourrez modifier ces informations à tout moment depuis votre profil.
                    </div>

                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between align-items-center pt-3">
                        <a href="{{ route('operator.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Plus tard
                        </a>
                        
                        <button type="submit" class="btn btn-complete">
                            <i class="fas fa-check me-2"></i>Compléter mon profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculer la progression en temps réel
    function updateProgress() {
        const requiredFields = document.querySelectorAll('input[required]');
        let completedFields = 0;
        
        requiredFields.forEach(field => {
            if (field.value.trim() !== '') {
                completedFields++;
            }
        });
        
        const progress = Math.round((completedFields / requiredFields.length) * 100);
        const progressBar = document.querySelector('.completion-progress-bar');
        const progressText = document.querySelector('.completion-header small');
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
        
        if (progressText) {
            progressText.textContent = `Progression : ${progress}% complété`;
        }
    }
    
    // Mettre à jour la progression lors de la saisie
    document.querySelectorAll('input[required]').forEach(field => {
        field.addEventListener('input', updateProgress);
    });
    
    // Calcul initial
    updateProgress();
});
</script>
@endpush