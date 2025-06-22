@extends('layouts.public')

@section('title', 'Connexion - PNGDI')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <!-- Messages de session -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fs-4 me-3"></i>
                        <div>
                            <p class="mb-0">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Connexion
                    </h3>
                    <p class="mb-0 mt-2 small">Accédez à votre espace</p>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>
                                Adresse email
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="exemple@email.com"
                                   required 
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>
                                Mot de passe
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Votre mot de passe"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Se souvenir de moi et Mot de passe oublié -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="remember" 
                                       name="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                            <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                Mot de passe oublié ?
                            </a>
                        </div>

                        <!-- Bouton de connexion -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>
                        </div>

                        <!-- Indicateur de chargement (caché par défaut) -->
                        <div class="d-grid mt-3 d-none" id="loadingIndicator">
                            <button class="btn btn-primary btn-lg" type="button" disabled>
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Connexion en cours...
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <!-- Lien vers l'inscription -->
                    <div class="text-center">
                        <p class="mb-0">
                            Pas encore de compte ?
                            <a href="{{ route('register') }}" class="text-primary fw-bold">
                                Créer un compte
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Note sur la vérification email (si message de succès après inscription) -->
            @if(session('success') && str_contains(session('success'), 'email'))
                <div class="card mt-3 border-warning">
                    <div class="card-body">
                        <h6 class="card-title text-warning">
                            <i class="fas fa-envelope me-2"></i>
                            Vérifiez votre email
                        </h6>
                        <p class="card-text small mb-2">
                            Un email de vérification a été envoyé à votre adresse email. 
                            Veuillez cliquer sur le lien dans l'email pour activer votre compte.
                        </p>
                        <p class="card-text small mb-0 text-muted">
                            Si vous ne recevez pas l'email dans les prochaines minutes, 
                            vérifiez votre dossier spam.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Informations de test (à supprimer en production) -->
            @if(config('app.debug'))
                <div class="card mt-3 border-info">
                    <div class="card-body">
                        <h6 class="card-title text-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Mode Debug - Comptes de test
                        </h6>
                        <small class="text-muted">
                            <p class="mb-1">Email : test@example.com</p>
                            <p class="mb-0">Mot de passe : password123</p>
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleBtn = passwordField.nextElementSibling.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleBtn.classList.remove('fa-eye');
        toggleBtn.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleBtn.classList.remove('fa-eye-slash');
        toggleBtn.classList.add('fa-eye');
    }
}

// Afficher l'indicateur de chargement lors de la soumission
document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('loginBtn').classList.add('d-none');
    document.getElementById('loadingIndicator').classList.remove('d-none');
});

// Empêcher la double soumission
let isSubmitting = false;
document.getElementById('loginForm').addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    isSubmitting = true;
});
</script>
@endsection