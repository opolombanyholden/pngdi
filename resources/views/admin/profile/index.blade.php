@extends('layouts.admin')

@section('title', 'Mon Profil')

@section('breadcrumb')
<li class="breadcrumb-item active">Mon Profil</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user"></i> Informations du Profil</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="user-avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                        </div>
                        <div class="col-md-9">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nom complet :</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email :</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Rôle :</strong></td>
                                    <td>
                                        <span class="badge bg-success">{{ ucfirst($user->role) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Téléphone :</strong></td>
                                    <td>{{ $user->phone ?? 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ville :</strong></td>
                                    <td>{{ $user->city ?? 'Non renseignée' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Membre depuis :</strong></td>
                                    <td>{{ $account_info['created_at'] ?? $user->created_at->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <h5>Fonctionnalités à venir</h5>
                <p>La gestion complète du profil sera implémentée dans l'<strong>Étape 6</strong> :</p>
                <ul class="mb-0">
                    <li>Modification des informations personnelles</li>
                    <li>Upload et gestion de l'avatar</li>
                    <li>Paramètres de sécurité et 2FA</li>
                    <li>Historique des sessions et connexions</li>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6>Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h4 class="text-primary">{{ $stats['dossiers_traites'] ?? 0 }}</h4>
                        <p class="text-muted">Dossiers traités</p>
                    </div>
                    <div class="text-center mb-3">
                        <h4 class="text-success">{{ $stats['actions_today'] ?? 0 }}</h4>
                        <p class="text-muted">Actions aujourd'hui</p>
                    </div>
                    <div class="text-center">
                        <p class="text-muted">Compte créé {{ $stats['account_age'] ?? 'récemment' }}</p>
                    </div>
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
@endsection
