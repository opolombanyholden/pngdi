@extends('layouts.public')

@section('title', 'Inscription')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Inscription</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Page d'inscription temporaire. L'authentification sera implémentée plus tard.
                    </div>
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" class="form-control" placeholder="Votre nom">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="email@exemple.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms">
                                <label class="form-check-label" for="terms">
                                    J'accepte les conditions d'utilisation
                                </label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="alert('Inscription non implémentée')">
                            S'inscrire
                        </button>
                        <a href="{{ route('home') }}" class="btn btn-link">Retour</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection