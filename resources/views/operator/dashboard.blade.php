@extends('layouts.operator')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

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
        background: #f5f7fa;
        min-height: calc(100vh - var(--header-height));
        padding: 0;
    }

    /* Header moderne */
    .dashboard-header {
        background: var(--gradient-primary);
        color: white;
        padding: 3rem 2rem;
        margin: 0;
        border-radius: 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -5%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        animation: float 8s ease-in-out infinite reverse;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Welcome text */
    .welcome-section {
        position: relative;
        z-index: 1;
    }

    .welcome-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        animation: slideInLeft 0.6s ease-out;
    }

    .welcome-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        animation: slideInLeft 0.8s ease-out;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Cards 3D modernes */
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: none;
        height: 100%;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    /* Icônes animées */
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
        font-size: 1.5rem;
        position: relative;
        transition: transform 0.3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .icon-primary { background: var(--gradient-primary); color: white; }
    .icon-warning { background: var(--gradient-warning); color: white; }
    .icon-success { background: var(--gradient-success); color: white; }
    .icon-info { background: var(--gradient-info); color: white; }

    /* Progress bars */
    .progress-wrapper {
        margin-top: 1rem;
    }

    .progress-custom {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
        overflow: visible;
        position: relative;
    }

    .progress-bar-custom {
        height: 100%;
        border-radius: 10px;
        position: relative;
        overflow: visible;
        animation: progressAnimation 1.5s ease-out;
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
    }

    @keyframes progressAnimation {
        from { width: 0; }
    }

    /* Quick actions cards */
    .quick-action-card {
        background: white;
        border-radius: 15px;
        padding: 2rem 1.5rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
        height: 100%;
        box-shadow: 0 3px 15px rgba(0,0,0,0.06);
    }

    .quick-action-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        transform: scale(0);
        transition: transform 0.5s ease;
    }

    .quick-action-card:hover::before {
        transform: scale(1);
    }

    .quick-action-card:hover {
        transform: translateY(-8px);
        border-color: #667eea;
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
    }

    .quick-action-card i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        transition: transform 0.3s ease;
    }

    .quick-action-card:hover i {
        transform: scale(1.2) rotate(10deg);
    }

    .quick-action-card h5 {
        font-weight: 600;
        color: #1a1d23;
        margin-bottom: 0.5rem;
    }

    .quick-action-card p {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
    }

    /* Timeline moderne */
    .timeline-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        height: 100%;
    }

    .timeline-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .timeline-container {
        max-height: 350px;
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

    .timeline-item {
        position: relative;
        padding-left: 45px;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f8f9fa;
        animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .timeline-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: 0;
        top: 5px;
        width: 24px;
        height: 24px;
        background: white;
        border: 3px solid #667eea;
        border-radius: 50%;
        box-shadow: 0 0 0 5px rgba(102, 126, 234, 0.1);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 5px rgba(102, 126, 234, 0.1); }
        50% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
    }

    .timeline-content h6 {
        font-weight: 600;
        color: #1a1d23;
        margin-bottom: 0.25rem;
    }

    .timeline-content p {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
    }

    .timeline-time {
        font-size: 0.75rem;
        color: #667eea;
        font-weight: 500;
    }

    /* Chart container */
    .chart-container {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        height: 100%;
    }

    .chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .chart-wrapper {
        position: relative;
        height: 300px;
    }

    /* Call to action */
    .cta-section {
        background: var(--gradient-dark);
        border-radius: 25px;
        padding: 3rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        margin-top: 3rem;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        animation: float 10s ease-in-out infinite;
    }

    .cta-content {
        position: relative;
        z-index: 1;
    }

    .cta-title {
        color: white;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .cta-text {
        color: rgba(255,255,255,0.9);
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    .btn-cta {
        background: white;
        color: #330867;
        padding: 1rem 3rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .btn-cta:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 2rem 1rem;
            margin: -1rem -1rem 1rem -1rem;
        }

        .welcome-title {
            font-size: 1.5rem;
        }

        .stat-card {
            margin-bottom: 1rem;
        }

        .quick-action-card {
            padding: 1.5rem 1rem;
        }

        .cta-section {
            padding: 2rem 1rem;
        }

        .cta-title {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-bg">
    <!-- Header moderne avec gradient -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1 class="welcome-title">Bonjour {{ auth()->user()->name }} ! 👋</h1>
            <p class="welcome-subtitle">
                Bienvenue dans votre espace personnel. Gérez vos organisations et suivez vos dossiers en toute simplicité.
            </p>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">

    <!-- Cartes de statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Mes organisations</p>
                        <h3 class="mb-0 fw-bold">0</h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            Prêt à créer
                        </small>
                    </div>
                    <div class="stat-icon icon-primary">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="progress-wrapper">
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: 10%; background: var(--gradient-primary);"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Dossiers en cours</p>
                        <h3 class="mb-0 fw-bold">0</h3>
                        <small class="text-warning">
                            <i class="fas fa-clock me-1"></i>
                            En attente
                        </small>
                    </div>
                    <div class="stat-icon icon-warning">
                        <i class="fas fa-folder-open"></i>
                    </div>
                </div>
                <div class="progress-wrapper">
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: 0%; background: var(--gradient-warning);"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Taux de conformité</p>
                        <h3 class="mb-0 fw-bold">100%</h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Excellent
                        </small>
                    </div>
                    <div class="stat-icon icon-success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="progress-wrapper">
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: 100%; background: var(--gradient-success);"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Messages</p>
                        <h3 class="mb-0 fw-bold">1</h3>
                        <small class="text-info">
                            <i class="fas fa-envelope me-1"></i>
                            Non lu
                        </small>
                    </div>
                    <div class="stat-icon icon-info">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <div class="progress-wrapper">
                    <div class="progress-custom">
                        <div class="progress-bar-custom" style="width: 20%; background: var(--gradient-info);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h4 class="fw-bold">
                <i class="fas fa-rocket me-2 text-primary"></i>
                Actions rapides
            </h4>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="quick-action-card" onclick="location.href='{{ route('operator.organisations.create') }}'">
                <i class="fas fa-plus-circle"></i>
                <h5>Créer une organisation</h5>
                <p>Commencez votre formalisation</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="quick-action-card" onclick="location.href='{{ route('operator.dossiers.index') }}'">
                <i class="fas fa-file-upload"></i>
                <h5>Mes dossiers</h5>
                <p>Gérez vos demandes</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="quick-action-card" onclick="location.href='{{ route('operator.messages.index') }}'">
                <i class="fas fa-message"></i>
                <h5>Messagerie</h5>
                <p>Contactez l'administration</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="quick-action-card" onclick="window.open('{{ route('guides') }}', '_blank')">
                <i class="fas fa-book-open"></i>
                <h5>Guides pratiques</h5>
                <p>Toute la documentation</p>
            </div>
        </div>
    </div>

    <!-- Timeline et graphiques -->
    <div class="row">
        <!-- Timeline -->
        <div class="col-lg-4 mb-4">
            <div class="timeline-card">
                <div class="timeline-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Activité récente
                    </h5>
                    <span class="badge bg-primary">Nouveau</span>
                </div>
                <div class="timeline-container">
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <h6>Compte créé</h6>
                            <p>Bienvenue sur PNGDI !</p>
                            <span class="timeline-time">{{ now()->format('H:i') }}</span>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <h6>Email vérifié</h6>
                            <p>Votre compte est activé</p>
                            <span class="timeline-time">{{ now()->subMinutes(5)->format('H:i') }}</span>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <h6>Première connexion</h6>
                            <p>Découvrez votre espace</p>
                            <span class="timeline-time">Maintenant</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique -->
        <div class="col-lg-8 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2 text-primary"></i>
                        Vue d'ensemble
                    </h5>
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option>6 derniers mois</option>
                        <option>12 derniers mois</option>
                        <option>Cette année</option>
                    </select>
                </div>
                <div class="chart-wrapper">
                    <canvas id="dashboardChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to action -->
    <div class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Prêt à formaliser votre organisation ?</h2>
            <p class="cta-text">
                Créez votre première organisation et lancez le processus de formalisation en quelques clics
            </p>
            <button class="btn btn-cta" onclick="location.href='{{ route('operator.organisations.create') }}'">
                <i class="fas fa-rocket me-2"></i>
                Commencer maintenant
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Configuration du graphique
const ctx = document.getElementById('dashboardChart').getContext('2d');

// Créer un gradient
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(102, 126, 234, 0.5)');
gradient.addColorStop(1, 'rgba(102, 126, 234, 0)');

// Chart configuration
const myChart = new Chart(ctx, {
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
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12,
                cornerRadius: 8,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Activité: ' + context.parsed.y;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 12
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    borderDash: [5, 5],
                    color: 'rgba(0,0,0,0.05)'
                },
                ticks: {
                    font: {
                        size: 12
                    },
                    stepSize: 1
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
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
    const stats = document.querySelectorAll('.stat-card h3');
    stats.forEach(stat => {
        const value = parseInt(stat.textContent);
        if (value > 0) {
            animateValue(stat, 0, value, 1500);
        }
    });
});

// Effet de parallaxe subtil sur les cartes
document.addEventListener('mousemove', (e) => {
    const cards = document.querySelectorAll('.stat-card, .quick-action-card');
    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;
    
    cards.forEach(card => {
        const rect = card.getBoundingClientRect();
        const cardX = (rect.left + rect.width / 2) / window.innerWidth;
        const cardY = (rect.top + rect.height / 2) / window.innerHeight;
        
        const deltaX = (x - cardX) * 10;
        const deltaY = (y - cardY) * 10;
        
        card.style.transform = `translateY(-5px) rotateX(${deltaY}deg) rotateY(${deltaX}deg)`;
    });
});
</script>
@endpush