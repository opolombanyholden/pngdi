@extends('layouts.public')

@section('title', 'Email Vérifié - PNGDI')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-check-circle fs-1 text-success"></i>
                        </div>
                    </div>
                    
                    <h3 class="mb-3 text-success">Email vérifié avec succès !</h3>
                    
                    <p class="text-muted mb-4">
                        Félicitations ! Votre adresse email a été vérifiée avec succès. 
                        Votre compte est maintenant actif et vous pouvez accéder à toutes les fonctionnalités de la plateforme.
                    </p>

                    <div class="d-grid gap-2 col-md-8 mx-auto">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Se connecter
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            Retour à l'accueil
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-muted small">
                        <p class="mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            Votre compte est sécurisé et prêt à l'emploi
                        </p>
                    </div>
                </div>
            </div>

            <!-- Prochaines étapes -->
            <div class="card mt-4 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Prochaines étapes
                    </h6>
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Connectez-vous à votre compte
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Complétez votre profil d'organisation
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Soumettez votre dossier de formalisation
                        </li>
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            Suivez l'avancement de votre demande
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection