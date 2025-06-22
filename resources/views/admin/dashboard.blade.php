@extends('layouts.public')

@section('title', 'Dashboard Admin - PNGDI')

@section('content')
<div class="container-fluid my-4">
    <!-- En-tête avec informations utilisateur -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary text-white p-4 rounded shadow">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Tableau de bord Administration</h2>
                        <p class="mb-0">Bienvenue, {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de session -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-4 shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Dossiers en attente
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Dossiers approuvés
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">45</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Organisations actives
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ \App\Models\User::where('role', 'operator')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-info opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Utilisateurs
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ \App\Models\User::count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu rapide -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>
                        Actions rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('admin.dossiers.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-folder me-2"></i>
                                Gérer les dossiers
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-users me-2"></i>
                                Gérer les utilisateurs
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-outline-success w-100">
                                <i class="fas fa-chart-bar me-2"></i>
                                Voir les statistiques
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-outline-warning w-100">
                                <i class="fas fa-cog me-2"></i>
                                Paramètres
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableaux -->
    <div class="row">
        <!-- Derniers dossiers -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-file-alt me-2"></i>
                        Derniers dossiers soumis
                    </h6>
                    <a href="{{ route('admin.dossiers.index') }}" class="btn btn-sm btn-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Organisation</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>REF-2025-001</td>
                                    <td>Association XYZ</td>
                                    <td><span class="badge bg-info">Association</span></td>
                                    <td>{{ date('d/m/Y') }}</td>
                                    <td><span class="badge bg-warning">En attente</span></td>
                                </tr>
                                <tr>
                                    <td>REF-2025-002</td>
                                    <td>ONG ABC</td>
                                    <td><span class="badge bg-success">ONG</span></td>
                                    <td>{{ date('d/m/Y', strtotime('-1 day')) }}</td>
                                    <td><span class="badge bg-success">Approuvé</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activités récentes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        Activités récentes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Nouvelle inscription</h6>
                                <small class="text-muted">Il y a 5 min</small>
                            </div>
                            <p class="mb-1 text-muted">Un nouvel opérateur s'est inscrit sur la plateforme.</p>
                            <small><i class="fas fa-user me-1"></i> Jean NGUEMA</small>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Dossier approuvé</h6>
                                <small class="text-muted">Il y a 2 heures</small>
                            </div>
                            <p class="mb-1 text-muted">Le dossier REF-2025-002 a été approuvé.</p>
                            <small><i class="fas fa-check me-1"></i> Par Agent01</small>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Connexion administrateur</h6>
                                <small class="text-muted">{{ now()->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-1 text-muted">Vous vous êtes connecté au système.</p>
                            <small><i class="fas fa-sign-in-alt me-1"></i> IP: {{ request()->ip() }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Note d'information -->
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    Information
                </h5>
                <p class="mb-0">
                    Ce tableau de bord est une version simplifiée. Les fonctionnalités complètes 
                    (graphiques, filtres avancés, exports) seront implémentées dans les prochaines discussions.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .text-xs {
        font-size: 0.875rem;
    }
    .opacity-25 {
        opacity: 0.25;
    }
    .border-start {
        border-left-width: 0.25rem !important;
    }
</style>
@endsection