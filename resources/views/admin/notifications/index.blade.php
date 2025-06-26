@extends('layouts.admin')

@section('title', 'Notifications')

@section('breadcrumb')
<li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell"></i> Centre de Notifications</h2>
                <span class="badge bg-warning">{{ $unread_count ?? 0 }} non lues</span>
            </div>

            <div class="alert alert-info">
                <h5>Section en développement</h5>
                <p>Le système de notifications complet sera implémenté dans l'<strong>Étape 6</strong>.</p>
                <p>Fonctionnalités prévues : notifications temps réel, paramètres personnalisés, historique complet.</p>
            </div>

            @if(isset($notifications) && $notifications->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5>Notifications récentes (simulation)</h5>
                </div>
                <div class="card-body">
                    @foreach($notifications as $notification)
                    <div class="d-flex align-items-center mb-3 p-3 border rounded {{ $notification['read'] ? 'bg-light' : 'bg-white' }}">
                        <div class="me-3">
                            <i class="{{ $notification['icon'] }} text-{{ $notification['color'] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $notification['title'] }}</h6>
                            <p class="text-muted mb-0">{{ $notification['message'] }}</p>
                            <small class="text-muted">{{ $notification['time'] }}</small>
                        </div>
                        @if(!$notification['read'])
                        <div>
                            <span class="badge bg-warning">Non lu</span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <div class="mt-3">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour au Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
