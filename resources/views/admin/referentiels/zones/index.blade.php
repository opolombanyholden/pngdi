@extends('layouts.admin')

@section('title', 'Zones')

@section('breadcrumb')
<li class="breadcrumb-item active">Zones</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h4><i class="fas fa-chart-line"></i> Zones & Statistiques Avancées</h4>
                <p>Cette section sera entièrement implémentée dans l'<strong>Étape 6 - Reporting et Statistiques</strong>.</p>
                <p>Fonctionnalités prévues :</p>
                <ul class="mb-0">
                    <li>Graphiques de tendances interactifs</li>
                    <li>Analyses géographiques par région</li>
                    <li>Métriques de performance détaillées</li>
                    <li>Prédictions basées sur l'historique</li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary">{{ $organisations_count ?? 0 }}</h3>
                            <p class="text-muted">Total Organisations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning">{{ $pending_count ?? 0 }}</h3>
                            <p class="text-muted">En Attente</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success">{{ $approved_count ?? 0 }}</h3>
                            <p class="text-muted">Approuvées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info">{{ $users_count ?? 0 }}</h3>
                            <p class="text-muted">Utilisateurs</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour au Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
