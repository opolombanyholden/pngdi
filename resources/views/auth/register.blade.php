@extends('layouts.public')

@section('title', 'Inscription - PNGDI')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Créer un compte
                    </h3>
                    <p class="mb-0 mt-2 small">Rejoignez la plateforme PNGDI</p>
                </div>
                
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
                        @csrf
                        
                        <!-- Nom complet -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-1"></i>
                                Nom complet <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Ex: Jean NGUEMA"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>
                                Adresse email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="exemple@email.com"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Téléphone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone me-1"></i>
                                Numéro de téléphone <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <img src="https://flagcdn.com/w20/ga.png" alt="Gabon"> +241
                                </span>
                                <input type="tel" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}" 
                                       placeholder="01234567"
                                       required>
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Adresse -->
                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                Adresse
                            </label>
                            <input type="text" 
                                   class="form-control @error('address') is-invalid @enderror" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address') }}" 
                                   placeholder="Quartier, BP...">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ville -->
                        <div class="mb-3">
                            <label for="city" class="form-label">
                                <i class="fas fa-city me-1"></i>
                                Ville
                            </label>
                            <select class="form-select @error('city') is-invalid @enderror" 
                                    id="city" 
                                    name="city">
                                <option value="">Sélectionnez une ville</option>
                                <option value="Libreville" {{ old('city') == 'Libreville' ? 'selected' : '' }}>Libreville</option>
                                <option value="Port-Gentil" {{ old('city') == 'Port-Gentil' ? 'selected' : '' }}>Port-Gentil</option>
                                <option value="Franceville" {{ old('city') == 'Franceville' ? 'selected' : '' }}>Franceville</option>
                                <option value="Oyem" {{ old('city') == 'Oyem' ? 'selected' : '' }}>Oyem</option>
                                <option value="Moanda" {{ old('city') == 'Moanda' ? 'selected' : '' }}>Moanda</option>
                                <option value="Mouila" {{ old('city') == 'Mouila' ? 'selected' : '' }}>Mouila</option>
                                <option value="Lambaréné" {{ old('city') == 'Lambaréné' ? 'selected' : '' }}>Lambaréné</option>
                                <option value="Tchibanga" {{ old('city') == 'Tchibanga' ? 'selected' : '' }}>Tchibanga</option>
                                <option value="Koulamoutou" {{ old('city') == 'Koulamoutou' ? 'selected' : '' }}>Koulamoutou</option>
                                <option value="Makokou" {{ old('city') == 'Makokou' ? 'selected' : '' }}>Makokou</option>
                            </select>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>
                                Mot de passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Minimum 8 caractères"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Le mot de passe doit contenir au moins 8 caractères
                            </div>
                        </div>

                        <!-- Confirmation du mot de passe -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-1"></i>
                                Confirmer le mot de passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Répétez le mot de passe"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Conditions d'utilisation -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" 
                                       type="checkbox" 
                                       id="terms" 
                                       name="terms" 
                                       {{ old('terms') ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#termsModal">conditions d'utilisation</a> et la <a href="#" class="text-primary">politique de confidentialité</a> <span class="text-danger">*</span>
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>
                                S'inscrire
                            </button>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0">
                                Vous avez déjà un compte ?
                                <a href="{{ route('login') }}" class="text-primary fw-bold">Se connecter</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information supplémentaire -->
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Vos données sont protégées et sécurisées
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal des conditions d'utilisation -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Conditions d'utilisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Acceptation des conditions</h6>
                <p>En créant un compte sur la plateforme PNGDI, vous acceptez les présentes conditions d'utilisation...</p>
                
                <h6>2. Utilisation du service</h6>
                <p>La plateforme est destinée à la gestion des organisations associatives, religieuses et politiques...</p>
                
                <h6>3. Responsabilités</h6>
                <p>Vous êtes responsable de la véracité des informations fournies...</p>
                
                <h6>4. Protection des données</h6>
                <p>Vos données personnelles sont protégées conformément à la législation en vigueur...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        button.classList.remove('fa-eye');
        button.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        button.classList.remove('fa-eye-slash');
        button.classList.add('fa-eye');
    }
}
</script>
@endsection