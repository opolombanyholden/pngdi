@extends('layouts.public')

@section('title', 'Dashboard Opérateur - PNGDI')

@push('styles')
<style>
    /* Variables personnalisées */
    :root {
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-success: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        --gradient-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --gradient-info: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        --gradient-dark: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
    }

    /* Background animé */
    .dashboard-bg {
        position: relative;
        background: #f8f9fa;
        overflow: hidden;
        min-height: 100vh;
    }

    .dashboard-bg::before {
        content: '';
        position: fixed;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle at 20% 80%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(118, 75, 162, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 40% 40%, rgba(250, 112, 154, 0.05) 0%, transparent 50%);
        animation: bgAnimation 20s ease infinite;
        z-index: -1;
    }

    @keyframes bgAnimation {
        0% { transform: rotate(0deg) scale(1); }
        50% { transform: rotate(180deg) scale(1.1); }
        100% { transform: rotate(360deg) scale(1); }
    }

    /* Header moderne */
    .dashboard-header {
        background: var(--gradient-primary);
        color: white;
        padding: 3rem 0;
        border-radius: 0 0 50px 50px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Cards 3D modernes */
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: none;
        transform-style: preserve-3d;
        perspective: 1000px;
    }

    .stat-card:hover {
        transform: translateY(-10px) rotateX(5deg);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -100%;
        left: -100%;
        width: 300%;
        height: 300%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: all 0.5s;
        transform: rotate(45deg);
    }

    .stat-card:hover::before {
        animation: shine 0.5s ease;
    }

    @keyframes shine {
        0% { transform: rotate(45deg) translateX(-100%); }
        100% { transform: rotate(45deg) translateX(100%); }
    }

    /* Icônes animées */
    .stat-icon {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        font-size: 2rem;
        position: relative;
        overflow: hidden;
    }

    .stat-icon::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: inherit;
        filter: blur(20px);
        opacity: 0.3;
        z-index: -1;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.2); opacity: 0.5; }
    }

    .icon-primary { background: var(--gradient-primary); color: white; }
    .icon-warning { background: var(--gradient-warning); color: white; }
    .icon-success { background: var(--gradient-success); color: white; }
    .icon-info { background: var(--gradient-info); color: white; }

    /* Boutons modernes */
    .btn-gradient {
        position: relative;
        overflow: hidden;
        border: none;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        z-index: 1;
    }

    .btn-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.2);
        transition: left 0.3s ease;
        z-index: -1;
    }

    .btn-gradient:hover::before {
        left: 0;
    }

    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        color: white;
    }

    .btn-gradient-primary { background: var(--gradient-primary); }
    .btn-gradient-success { background: var(--gradient-success); }

    /* Quick actions cards */
    .quick-action-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .quick-action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .quick-action-card:hover {
        transform: translateY(-5px) scale(1.02);
        border-color: #667eea;
        box-shadow: 0 15px 30px rgba(102, 126, 234, 0.2);
    }

    .quick-action-card:hover::before {
        opacity: 0.05;
    }

    .quick-action-card i {
        font-size: 3rem;
        margin-bottom: 1rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Progress bars animés */
    .progress-custom {
        height: 10px;
        border-radius: 10px;
        background: #e9ecef;
        overflow: visible;
        margin-top: 1rem;
    }

    .progress-bar-custom {
        border-radius: 10px;
        background: var(--gradient-primary);
        position: relative;
        overflow: visible;
        animation: progressAnimation 2s ease-out;
    }

    @keyframes progressAnimation {
        0% { width: 0; }
    }

    .progress-bar-custom::after {
        content: attr(data-value);
        position: absolute;
        right: -20px;
        top: -25px;
        background: #667eea;
        color: white;
        padding: 2px 8px;
        border-radius: 5px;
        font-size: 0.75rem;
        font-weight: bold;
    }

    /* Timeline moderne - CORRIGÉ */
    .timeline-container {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .timeline-container::-webkit-scrollbar {
        width: 6px;
    }

    .timeline-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .timeline-container::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }

    .timeline-container::-webkit-scrollbar-thumb:hover {
        background: #5a67d8;
    }

    .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 2rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: -2rem;
        width: 2px;
        background: linear-gradient(to bottom, #667eea, transparent);
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: 0;
        top: 5px;
        width: 20px;
        height: 20px;
        background: white;
        border: 3px solid #667eea;
        border-radius: 50%;
        z-index: 1;
        animation: timelinePulse 2s ease-in-out infinite;
    }

    @keyframes timelinePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
    }

    /* Notification badge animé */
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ff4757;
        color: white;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
        animation: badgePulse 1.5s ease-in-out infinite;
    }

    @keyframes badgePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    /* Chart container - CORRIGÉ */
    .chart-container {
        position: relative;
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        height: 100%;
        max-height: 500px;
        display: flex;
        flex-direction: column;
    }

    .chart-wrapper {
        position: relative;
        flex: 1;
        min-height: 300px;
        max-height: 350px;
    }

    /* Welcome animation */
    .welcome-text {
        animation: slideInLeft 0.8s ease-out;
    }

    @keyframes slideInLeft {
        0% { opacity: 0; transform: translateX(-50px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 2rem 0;
            border-radius: 0 0 30px 30px;
        }
        
        .stat-card {
            margin-bottom: 1rem;
        }

        .quick-action-card i {
            font-size: 2rem;
        }

        .chart-container {
            max-height: 400px;
        }

        .timeline-container {
            max-height: 300px;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-bg">
    <!-- Header moderne avec gradient -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="welcome-text mb-3">Bonjour {{ auth()->user()->name }} ! 👋</h1>
                    <p class="lead mb-0 welcome-text" style="animation-delay: 0.2s;">
                        Bienvenue dans votre espace personnel. Que souhaitez-vous faire aujourd'hui ?
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block position-relative">
                        <button class="btn btn-light btn-lg rounded-pill shadow">
                            <i class="fas fa-bell me-2"></i>
                            Notifications
                        </button>
                        <span class="notification-badge">3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: -50px;">
        <!-- Cartes de statistiques avec design 3D -->
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Mes organisations</p>
                            <h2 class="mb-0 fw-bold">0</h2>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                Prêt à créer
                            </small>
                        </div>
                        <div class="stat-icon icon-primary">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: 10%;" data-value="10%"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Dossiers en cours</p>
                            <h2 class="mb-0 fw-bold">0</h2>
                            <small class="text-warning">
                                <i class="fas fa-clock me-1"></i>
                                En attente
                            </small>
                        </div>
                        <div class="stat-icon icon-warning">
                            <i class="fas fa-folder-open"></i>
                        </div>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: 0%; background: var(--gradient-warning);" data-value="0%"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Taux de conformité</p>
                            <h2 class="mb-0 fw-bold">100%</h2>
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Excellent
                            </small>
                        </div>
                        <div class="stat-icon icon-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: 100%; background: var(--gradient-success);" data-value="100%"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Messages</p>
                            <h2 class="mb-0 fw-bold">1</h2>
                            <small class="text-info">
                                <i class="fas fa-envelope me-1"></i>
                                Non lu
                            </small>
                        </div>
                        <div class="stat-icon icon-info">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: 20%; background: var(--gradient-info);" data-value="1"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides avec cards modernes -->
        <div class="row mb-5">
            <div class="col-12 mb-4">
                <h3 class="fw-bold">
                    <i class="fas fa-rocket me-2 text-primary"></i>
                    Actions rapides
                </h3>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="quick-action-card" onclick="location.href='{{ route('operator.organisations.create') }}'">
                    <i class="fas fa-plus-circle"></i>
                    <h5>Créer une organisation</h5>
                    <p class="text-muted small mb-0">Commencez votre formalisation</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="quick-action-card">
                    <i class="fas fa-file-upload"></i>
                    <h5>Soumettre un dossier</h5>
                    <p class="text-muted small mb-0">Envoyez vos documents</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="quick-action-card">
                    <i class="fas fa-message"></i>
                    <h5>Messagerie</h5>
                    <p class="text-muted small mb-0">Contactez l'administration</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="quick-action-card" onclick="location.href='{{ route('guides') }}'">
                    <i class="fas fa-book-open"></i>
                    <h5>Guides pratiques</h5>
                    <p class="text-muted small mb-0">Toute la documentation</p>
                </div>
            </div>
        </div>

        <!-- Timeline et graphiques -->
        <div class="row mb-5">
            <!-- Timeline moderne - CORRIGÉ -->
            <div class="col-lg-4 mb-4">
                <div class="chart-container">
                    <h4 class="fw-bold mb-4">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Activité récente
                    </h4>
                    <div class="timeline-container">
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-1">Compte créé</h6>
                                <p class="text-muted small mb-0">Bienvenue sur PNGDI !</p>
                                <small class="text-primary">{{ now()->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-1">Email vérifié</h6>
                                <p class="text-muted small mb-0">Votre compte est activé</p>
                                <small class="text-primary">{{ now()->subMinutes(5)->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6 class="fw-bold mb-1">Première connexion</h6>
                                <p class="text-muted small mb-0">Découvrez votre espace</p>
                                <small class="text-primary">Maintenant</small>
                            </div>
                        </div>
                        <!-- Vous pouvez ajouter plus d'éléments ici pour tester le scroll -->
                    </div>
                </div>
            </div>

            <!-- Graphique moderne - CORRIGÉ -->
            <div class="col-lg-8 mb-4">
                <div class="chart-container">
                    <h4 class="fw-bold mb-4">
                        <i class="fas fa-chart-area me-2 text-primary"></i>
                        Vue d'ensemble
                    </h4>
                    <div class="chart-wrapper">
                        <canvas id="dashboardChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to action -->
        <div class="row mt-5 mb-5">
            <div class="col-12">
                <div class="text-center p-5 rounded-3" style="background: var(--gradient-dark);">
                    <h2 class="text-white mb-3">Prêt à commencer ?</h2>
                    <p class="text-white mb-4">Créez votre première organisation et lancez le processus de formalisation</p>
                    <button class="btn btn-light btn-lg rounded-pill px-5" onclick="location.href='{{ route('operator.organisations.create') }}'">
                        <i class="fas fa-rocket me-2"></i>
                        Commencer maintenant
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphique moderne avec hauteur contrôlée
const ctx = document.getElementById('dashboardChart').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(102, 126, 234, 0.5)');
gradient.addColorStop(1, 'rgba(102, 126, 234, 0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin'],
        datasets: [{
            label: 'Activité',
            data: [0, 0, 0, 0, 0, 1],
            backgroundColor: gradient,
            borderColor: '#667eea',
            borderWidth: 3,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    borderDash: [5, 5],
                    color: 'rgba(0,0,0,0.05)'
                }
            }
        }
    }
});

// Animation des nombres
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.textContent = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animer les statistiques au chargement
document.addEventListener('DOMContentLoaded', function() {
    const stats = document.querySelectorAll('.stat-card h2');
    stats.forEach(stat => {
        const value = parseInt(stat.textContent);
        if (value > 0) {
            animateValue(stat, 0, value, 1000);
        }
    });
});
</script>
@endpush