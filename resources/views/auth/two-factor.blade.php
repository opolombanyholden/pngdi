@extends('layouts.public')

@section('title', 'Authentification à deux facteurs - PNGDI')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt fs-1 text-info"></i>
                        </div>
                    </div>
                    
                    <h3 class="mb-3">Authentification à deux facteurs</h3>
                    
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        L'authentification à deux facteurs est temporairement désactivée sur cette plateforme.
                    </div>

                    <p class="text-muted mb-4">
                        Vous allez être redirigé automatiquement vers la page de connexion.
                    </p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour à la connexion
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            Retour à l'accueil
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-muted small">
                        <p class="mb-0">
                            <i class="fas fa-lock me-1"></i>
                            Votre sécurité reste notre priorité
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Redirection automatique après 3 secondes
setTimeout(function() {
    window.location.href = "{{ route('login') }}";
}, 3000);
</script>
@endsection