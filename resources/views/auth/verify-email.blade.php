@extends('layouts.public')

@section('title', 'Vérification Email - PNGDI')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-envelope fs-1 text-warning"></i>
                        </div>
                    </div>
                    
                    <h3 class="mb-3">Vérifiez votre adresse email</h3>
                    
                    <p class="text-muted mb-4">
                        Merci pour votre inscription ! Avant de commencer, veuillez vérifier votre adresse email 
                        en cliquant sur le lien que nous venons de vous envoyer. Si vous n'avez pas reçu l'email, 
                        nous serons heureux de vous en envoyer un autre.
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            Un nouveau lien de vérification a été envoyé à l'adresse email que vous avez fournie lors de l'inscription.
                        </div>
                    @endif

                    <div class="d-flex justify-content-center gap-3">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                Renvoyer l'email
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                Se déconnecter
                            </button>
                        </form>
                    </div>

                    <hr class="my-4">

                    <div class="text-muted small">
                        <p class="mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            L'email peut prendre quelques minutes pour arriver.
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Pensez à vérifier votre dossier spam ou courrier indésirable.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Aide supplémentaire -->
            <div class="text-center mt-4">
                <p class="text-muted small">
                    Besoin d'aide ? 
                    <a href="{{ route('contact') }}" class="text-primary">Contactez-nous</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection