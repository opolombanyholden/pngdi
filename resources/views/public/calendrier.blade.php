@extends('layouts.public')

@section('title', 'Calendrier des événements')

@section('content')
<!-- Header Section -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title">Calendrier des événements</h1>
                <p class="page-subtitle">
                    Consultez les dates importantes, formations et échéances 
                    pour ne rien manquer de l'actualité du PNGDI.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Calendrier</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Calendar Navigation -->
<section class="calendar-nav py-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="mb-0">{{ \Carbon\Carbon::now()->format('F Y') }}</h3>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-outline-primary">
                    Aujourd'hui
                </button>
                <button type="button" class="btn btn-outline-primary">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Events List -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Events Timeline -->
            <div class="col-lg-8 mb-5 mb-lg-0">
                <h3 class="mb-4">Événements à venir</h3>
                
                <div class="events-timeline">
                    @foreach($evenements as $event)
                    <div class="event-item {{ $event['important'] ? 'important' : '' }}">
                        <div class="event-date">
                            <div class="date-day">{{ \Carbon\Carbon::parse($event['date'])->format('d') }}</div>
                            <div class="date-month">{{ \Carbon\Carbon::parse($event['date'])->format('M') }}</div>
                        </div>
                        
                        <div class="event-content">
                            <div class="event-type-badge {{ $event['type'] }}">
                                @switch($event['type'])
                                    @case('echeance')
                                        <i class="fas fa-calendar-check me-1"></i>Échéance
                                        @break
                                    @case('formation')
                                        <i class="fas fa-graduation-cap me-1"></i>Formation
                                        @break
                                    @case('maintenance')
                                        <i class="fas fa-tools me-1"></i>Maintenance
                                        @break
                                    @case('evenement')
                                        <i class="fas fa-calendar-star me-1"></i>Événement
                                        @break
                                @endswitch
                            </div>
                            
                            <h5 class="event-title">{{ $event['titre'] }}</h5>
                            <p class="event-description">{{ $event['description'] }}</p>
                            
                            @if($event['important'])
                            <div class="alert alert-warning alert-sm">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Événement important - À ne pas manquer !
                            </div>
                            @endif
                            
                            <div class="event-actions">
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-bell me-1"></i>Me rappeler
                                </button>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-share-alt me-1"></i>Partager
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Mini Calendar -->
                <div class="mini-calendar mb-4">
                    <h5 class="mb-3">Calendrier rapide</h5>
                    <div class="calendar-widget">
                        <div class="calendar-header">
                            <div class="calendar-month">{{ \Carbon\Carbon::now()->format('F Y') }}</div>
                        </div>
                        <div class="calendar-weekdays">
                            <div>Lun</div>
                            <div>Mar</div>
                            <div>Mer</div>
                            <div>Jeu</div>
                            <div>Ven</div>
                            <div>Sam</div>
                            <div>Dim</div>
                        </div>
                        <div class="calendar-days">
                            @for($i = 1; $i <= 31; $i++)
                            <div class="calendar-day {{ $i == \Carbon\Carbon::now()->day ? 'today' : '' }}">
                                {{ $i }}
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>
                
                <!-- Event Filters -->
                <div class="event-filters mb-4">
                    <h5 class="mb-3">Filtrer par type</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="filter-echeance" checked>
                        <label class="form-check-label" for="filter-echeance">
                            <i class="fas fa-calendar-check text-danger me-1"></i>Échéances
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="filter-formation" checked>
                        <label class="form-check-label" for="filter-formation">
                            <i class="fas fa-graduation-cap text-success me-1"></i>Formations
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="filter-maintenance" checked>
                        <label class="form-check-label" for="filter-maintenance">
                            <i class="fas fa-tools text-warning me-1"></i>Maintenance
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="filter-evenement" checked>
                        <label class="form-check-label" for="filter-evenement">
                            <i class="fas fa-calendar-star text-info me-1"></i>Événements
                        </label>
                    </div>
                </div>
                
                <!-- Download Calendar -->
                <div class="download-calendar">
                    <h5 class="mb-3">Exporter le calendrier</h5>
                    <p class="text-muted mb-3">
                        Téléchargez le calendrier pour l'importer dans votre application favorite
                    </p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary">
                            <i class="fab fa-google me-2"></i>Google Calendar
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fab fa-apple me-2"></i>Apple Calendar
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-file-download me-2"></i>Fichier ICS
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter CTA -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h3 class="mb-3">Ne manquez aucun événement !</h3>
                <p class="mb-0">
                    Inscrivez-vous à notre newsletter pour recevoir les rappels 
                    des événements importants directement dans votre boîte mail.
                </p>
            </div>
            <div class="col-lg-6">
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control form-control-lg" 
                               placeholder="Votre adresse email" required>
                        <button class="btn btn-warning btn-lg" type="submit">
                            <i class="fas fa-envelope me-2"></i>S'inscrire
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
        color: white;
        padding: 4rem 0 3rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    /* Calendar Navigation */
    .calendar-nav {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    /* Events Timeline */
    .events-timeline {
        position: relative;
        padding-left: 30px;
    }

    .events-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .event-item {
        position: relative;
        margin-bottom: 3rem;
        display: flex;
        gap: 2rem;
    }

    .event-item::before {
        content: '';
        position: absolute;
        left: -35px;
        top: 15px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e0e0e0;
        border: 2px solid white;
    }

    .event-item.important::before {
        background: var(--secondary-gold);
        width: 16px;
        height: 16px;
        left: -37px;
        top: 13px;
    }

    .event-date {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        min-width: 80px;
    }

    .date-day {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-blue);
        line-height: 1;
    }

    .date-month {
        font-size: 0.875rem;
        color: #666;
        text-transform: uppercase;
    }

    .event-content {
        flex: 1;
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .event-type-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .event-type-badge.echeance {
        background: rgba(220,53,69,0.1);
        color: #dc3545;
    }

    .event-type-badge.formation {
        background: rgba(40,167,69,0.1);
        color: #28a745;
    }

    .event-type-badge.maintenance {
        background: rgba(255,193,7,0.1);
        color: #ffc107;
    }

    .event-type-badge.evenement {
        background: rgba(23,162,184,0.1);
        color: #17a2b8;
    }

    .event-title {
        color: var(--primary-blue);
        margin-bottom: 0.5rem;
    }

    .event-description {
        color: #666;
        margin-bottom: 1rem;
    }

    .alert-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .event-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    /* Mini Calendar */
    .mini-calendar {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .calendar-widget {
        margin-top: 1rem;
    }

    .calendar-month {
        text-align: center;
        font-weight: 600;
        color: var(--primary-blue);
        margin-bottom: 1rem;
    }

    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.25rem;
        margin-bottom: 0.5rem;
    }

    .calendar-weekdays div {
        text-align: center;
        font-size: 0.75rem;
        color: #999;
        font-weight: 600;
    }

    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.25rem;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .calendar-day:hover {
        background: #f0f0f0;
    }

    .calendar-day.today {
        background: var(--primary-blue);
        color: white;
        font-weight: 600;
    }

    /* Event Filters */
    .event-filters {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    /* Download Calendar */
    .download-calendar {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    /* Newsletter Form */
    .newsletter-form .form-control {
        border: 2px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.1);
        color: white;
    }

    .newsletter-form .form-control::placeholder {
        color: rgba(255,255,255,0.7);
    }

    .newsletter-form .form-control:focus {
        border-color: white;
        background: rgba(255,255,255,0.2);
        box-shadow: none;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .event-item {
            flex-direction: column;
            gap: 1rem;
        }
        
        .events-timeline {
            padding-left: 15px;
        }
        
        .event-date {
            align-self: flex-start;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Calendar navigation functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners for calendar navigation buttons
        // This is a placeholder - in production, you would implement full calendar functionality
    });
</script>
@endpush