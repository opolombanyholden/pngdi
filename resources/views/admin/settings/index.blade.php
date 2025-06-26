@extends('layouts.admin')

@section('title', 'Paramètres')

@section('breadcrumb')
<li class="breadcrumb-item active">Paramètres</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="alert alert-info">
                <h4><i class="fas fa-cog"></i> Paramètres Administrateur</h4>
                <p>La section complète des paramètres sera implémentée dans l'<strong>Étape 6</strong>.</p>
                <p>Fonctionnalités prévues :</p>
                <ul class="mb-0">
                    <li>Préférences utilisateur (thème, langue, timezone)</li>
                    <li>Paramètres de notifications</li>
                    <li>Configuration système (admin seulement)</li>
                    <li>Gestion du mode maintenance</li>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6>Informations Système</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>PHP :</strong></td>
                            <td>{{ $system_info['php_version'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Laravel :</strong></td>
                            <td>{{ $system_info['laravel_version'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Environnement :</strong></td>
                            <td>
                                <span class="badge bg-{{ $system_info['environment'] === 'production' ? 'success' : 'warning' }}">
                                    {{ ucfirst($system_info['environment'] ?? 'unknown') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Maintenance :</strong></td>
                            <td>
                                <span class="badge bg-{{ $system_info['maintenance_mode'] ? 'warning' : 'success' }}">
                                    {{ $system_info['maintenance_mode'] ? 'Activé' : 'Désactivé' }}
                                </span>
                            </td>
                        </tr>
                    </table>
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
