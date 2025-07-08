<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase 2 - Gestion Adhérents | SGLP Gabon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ========================================================================
           PHASE 2 - INTERFACE GESTION ADHÉRENTS - CHARTE GABONAISE
           Design moderne avec couleurs officielles du Gabon
        ======================================================================== */
        
        :root {
            --gabon-green: #009e3f;
            --gabon-green-light: #00b347;
            --gabon-green-dark: #006d2c;
            --gabon-yellow: #ffcd00;
            --gabon-yellow-light: #ffd700;
            --gabon-yellow-dark: #b8930b;
            --gabon-blue: #003f7f;
            --gabon-blue-light: #0056b3;
            --gabon-blue-dark: #002855;
            
            /* Gradients */
            --primary-gradient: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-green-light) 100%);
            --warning-gradient: linear-gradient(135deg, var(--gabon-yellow) 0%, #fd7e14 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            --secondary-gradient: linear-gradient(135deg, var(--gabon-blue) 0%, var(--gabon-blue-light) 100%);
            
            /* Ombres et transitions */
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.15);
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ========================================================================
           HEADER PHASE 2 - DESIGN GABONAIS MODERNE
        ======================================================================== */
        
        .phase2-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 2rem 0;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .phase2-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="gabon-pattern" x="0" y="0" width="25" height="25" patternUnits="userSpaceOnUse"><circle cx="12.5" cy="12.5" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23gabon-pattern)"/></svg>');
            opacity: 0.3;
        }

        .phase2-content {
            position: relative;
            z-index: 2;
        }

        .phase-indicator {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            display: inline-block;
            margin-bottom: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            animation: phaseGlow 3s ease-in-out infinite;
        }

        @keyframes phaseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            50% { box-shadow: 0 0 0 15px rgba(255, 255, 255, 0); }
        }

        .breadcrumb-gabon {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-gabon .breadcrumb-item + .breadcrumb-item::before {
            content: "→";
            color: rgba(255, 255, 255, 0.7);
        }

        /* ========================================================================
           SECTION INFORMATIONS ORGANISATION - RÉCAPITULATIF
        ======================================================================== */
        
        .organization-summary {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: -3rem 0 2rem 0;
            position: relative;
            z-index: 10;
            border: 3px solid var(--gabon-green);
        }

        .organization-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--primary-gradient);
            border-radius: 20px 20px 0 0;
        }

        .org-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f3f5;
        }

        .org-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            box-shadow: var(--shadow-md);
            animation: orgIconPulse 3s ease-in-out infinite;
        }

        @keyframes orgIconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .org-details h3 {
            color: var(--gabon-blue);
            margin-bottom: 0.5rem;
        }

        .org-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .org-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            transition: all var(--transition-normal);
            border: 2px solid transparent;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            border-color: var(--gabon-green);
            box-shadow: var(--shadow-md);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--gabon-blue);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ========================================================================
           STATISTIQUES ADHÉRENTS - DASHBOARD TEMPS RÉEL
        ======================================================================== */
        
        .adherents-dashboard {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: 2rem 0;
            border-left: 5px solid var(--gabon-yellow);
        }

        .dashboard-header {
            background: var(--warning-gradient);
            color: #333;
            padding: 1.5rem;
            border-radius: 15px;
            margin: -2rem -2rem 2rem -2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2));
            transform: translateX(100px);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100px); }
            100% { transform: translateX(200px); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all var(--transition-normal);
            border: 2px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gabon-green);
            transform: scaleX(0);
            transition: transform var(--transition-normal);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--gabon-green);
        }

        .stat-card.highlight {
            background: var(--primary-gradient);
            color: white;
            border-color: var(--gabon-green-dark);
        }

        .stat-card.warning {
            background: var(--warning-gradient);
            color: #333;
            border-color: var(--gabon-yellow-dark);
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-color: #b21e2f;
        }

        .stat-icon-large {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .stat-card.highlight .stat-icon-large,
        .stat-card.warning .stat-icon-large,
        .stat-card.danger .stat-icon-large {
            background: rgba(255, 255, 255, 0.3);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .stat-description {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .stat-progress {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            position: relative;
        }

        .stat-progress-bar {
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            transition: width 1s ease-in-out;
            position: relative;
            overflow: hidden;
        }

        .stat-progress-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: progressShine 2s ease-in-out infinite;
        }

        @keyframes progressShine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* ========================================================================
           ZONE ACTIONS PRINCIPALES - BOUTONS GABONAIS
        ======================================================================== */
        
        .actions-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: 2rem 0;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .btn-gabon {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all var(--transition-normal);
            border: none;
            position: relative;
            overflow: hidden;
            min-width: 220px;
            font-size: 0.9rem;
        }

        .btn-gabon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-gabon:hover::before {
            left: 100%;
        }

        .btn-primary-gabon {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 6px 20px rgba(0, 158, 63, 0.4);
        }

        .btn-primary-gabon:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 158, 63, 0.6);
        }

        .btn-secondary-gabon {
            background: white;
            color: var(--gabon-blue);
            border: 3px solid var(--gabon-blue);
            box-shadow: 0 6px 20px rgba(0, 63, 127, 0.2);
        }

        .btn-secondary-gabon:hover {
            background: var(--gabon-blue);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 63, 127, 0.4);
        }

        .btn-warning-gabon {
            background: var(--warning-gradient);
            color: #333;
            box-shadow: 0 6px 20px rgba(255, 205, 0, 0.4);
        }

        .btn-warning-gabon:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 205, 0, 0.6);
        }

        /* ========================================================================
           SECTION PROGRESS TEMPS RÉEL
        ======================================================================== */
        
        .progress-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: 2rem 0;
            display: none; /* Affiché pendant l'import */
        }

        .progress-section.active {
            display: block;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .progress-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .progress-main {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .progress-bar-container {
            background: #e9ecef;
            border-radius: 25px;
            height: 20px;
            overflow: hidden;
            position: relative;
            margin-bottom: 1rem;
        }

        .progress-bar-gabon {
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 25px;
            transition: width 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .progress-bar-gabon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: progressAnimation 2s ease-in-out infinite;
        }

        @keyframes progressAnimation {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .progress-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .progress-stat {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
        }

        .progress-stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--gabon-blue);
            margin-bottom: 0.25rem;
        }

        .progress-stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
        }

        /* ========================================================================
           MESSAGES ET ALERTES GABONAISES
        ======================================================================== */
        
        .alert-gabon {
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            position: relative;
            overflow: hidden;
        }

        .alert-gabon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--gabon-green);
        }

        .alert-success-gabon {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
            border-left: 5px solid var(--gabon-green);
            color: var(--gabon-green-dark);
        }

        .alert-warning-gabon {
            background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
            border-left: 5px solid var(--gabon-yellow);
            color: var(--gabon-yellow-dark);
        }

        .alert-info-gabon {
            background: linear-gradient(135deg, #e6f3ff 0%, #f0f8ff 100%);
            border-left: 5px solid var(--gabon-blue);
            color: var(--gabon-blue-dark);
        }

        .alert-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            float: left;
            margin-right: 1rem;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .alert-success-gabon .alert-icon {
            background: var(--gabon-green);
            color: white;
        }

        .alert-warning-gabon .alert-icon {
            background: var(--gabon-yellow);
            color: #333;
        }

        .alert-info-gabon .alert-icon {
            background: var(--gabon-blue);
            color: white;
        }

        .alert-content {
            overflow: hidden;
        }

        .alert-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .alert-text {
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* ========================================================================
           TABLEAU ADHÉRENTS MODERNE
        ======================================================================== */
        
        .adherents-table-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: 2rem 0;
        }

        .table-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin: -2rem -2rem 2rem -2rem;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .table-controls {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 2px solid #e9ecef;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem 0.75rem 3rem;
            transition: all var(--transition-normal);
        }

        .search-box input:focus {
            border-color: var(--gabon-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 158, 63, 0.25);
        }

        .search-box .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-gabon {
            margin-bottom: 0;
        }

        .table-gabon thead {
            background: var(--primary-gradient);
            color: white;
        }

        .table-gabon th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-gabon td {
            border: none;
            padding: 1rem;
            border-bottom: 1px solid #f1f3f5;
            vertical-align: middle;
        }

        .table-gabon tbody tr:hover {
            background-color: rgba(0, 158, 63, 0.05);
        }

        .badge-gabon {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success-gabon {
            background: var(--gabon-green);
            color: white;
        }

        .badge-warning-gabon {
            background: var(--gabon-yellow);
            color: #333;
        }

        .badge-danger-gabon {
            background: #dc3545;
            color: white;
        }

        /* ========================================================================
           RESPONSIVE DESIGN
        ======================================================================== */
        
        @media (max-width: 768px) {
            .org-header {
                flex-direction: column;
                text-align: center;
            }

            .org-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .org-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-gabon {
                width: 100%;
                max-width: 300px;
            }

            .progress-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .table-controls .row > div {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 576px) {
            .organization-summary,
            .adherents-dashboard,
            .actions-section,
            .adherents-table-section {
                margin-left: -1rem;
                margin-right: -1rem;
                border-radius: 0;
            }

            .org-stats,
            .progress-stats {
                grid-template-columns: 1fr;
            }

            .phase2-header {
                padding: 1rem 0;
            }

            .alert-gabon {
                padding: 1rem;
            }

            .alert-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }

        /* ========================================================================
           ANIMATIONS SUPPLÉMENTAIRES
        ======================================================================== */
        
        .fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in-left {
            animation: slideInLeft 0.6s ease-out;
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .slide-in-right {
            animation: slideInRight 0.6s ease-out;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ========================================================================
           FLOATING ACTION BUTTON GABONAIS
        ======================================================================== */
        
        .fab-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        .fab-menu {
            position: relative;
        }

        .fab-main {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 158, 63, 0.4);
            font-size: 1.5rem;
            transition: all var(--transition-normal);
            cursor: pointer;
        }

        .fab-main:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 30px rgba(0, 158, 63, 0.6);
        }

        .fab-options {
            position: absolute;
            bottom: 70px;
            right: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all var(--transition-normal);
            pointer-events: none;
        }

        .fab-menu.active .fab-options {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }

        .fab-option {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-normal);
            cursor: pointer;
        }

        .fab-option-green {
            background: var(--gabon-green);
        }

        .fab-option-yellow {
            background: var(--gabon-yellow);
            color: #333;
        }

        .fab-option-blue {
            background: var(--gabon-blue);
        }

        .fab-option:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- ========================================================================
         HEADER PHASE 2 - NAVIGATION ET CONTEXTE
    ======================================================================== -->
    <div class="phase2-header">
        <div class="container">
            <div class="phase2-content">
                <!-- Indicateur de phase -->
                <div class="phase-indicator">
                    <i class="fas fa-users me-2"></i>
                    Phase 2 : Gestion des Adhérents
                </div>

                <!-- Breadcrumb gabonais -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-gabon mb-0">
                        <li class="breadcrumb-item">
                            <a href="#" class="text-white opacity-75">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-white opacity-75">Organisations</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-white opacity-75">Association Jeunesse</a>
                        </li>
                        <li class="breadcrumb-item active text-white">Adhérents</li>
                    </ol>
                </nav>

                <!-- Titre principal -->
                <div class="row align-items-center mt-3">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-2">Gestion des Adhérents</h1>
                        <p class="lead mb-0 opacity-90">
                            Import et traitement intelligent de 1,247 adhérents
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Phase 1
                            </button>
                            <button class="btn btn-warning" onclick="showHelp()">
                                <i class="fas fa-question-circle me-2"></i>Aide
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- ========================================================================
             SECTION INFORMATIONS ORGANISATION - RÉCAPITULATIF INTERACTIF
        ======================================================================== -->
        <div class="organization-summary fade-in">
            <div class="org-header">
                <div class="org-icon">
                    <i class="fas fa-building fa-2x text-white"></i>
                </div>
                <div class="org-details flex-grow-1">
                    <h3 class="mb-1">Association Jeunesse Solidarité Gabon</h3>
                    <div class="org-meta">
                        <span class="me-3">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Libreville, Estuaire
                        </span>
                        <span class="me-3">
                            <i class="fas fa-calendar me-1"></i>
                            Créée le 15/07/2025
                        </span>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>
                            Phase 1 Complétée
                        </span>
                    </div>
                </div>
                <div class="org-actions">
                    <button class="btn btn-outline-primary btn-sm me-2" onclick="viewDetails()">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="editOrganization()">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>

            <!-- Statistiques organisation -->
            <div class="org-stats">
                <div class="stat-item">
                    <div class="stat-number">5</div>
                    <div class="stat-label">Fondateurs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Documents</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">#SGF-001234</div>
                    <div class="stat-label">N° Récépissé</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">Association</div>
                    <div class="stat-label">Type</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">15</div>
                    <div class="stat-label">Min. Requis</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">2h</div>
                    <div class="stat-label">Session</div>
                </div>
            </div>
        </div>

        <!-- ========================================================================
             DASHBOARD STATISTIQUES ADHÉRENTS - TEMPS RÉEL
        ======================================================================== -->
        <div class="adherents-dashboard slide-in-left">
            <div class="dashboard-header">
                <h2 class="h3 mb-2">
                    <i class="fas fa-chart-line me-2"></i>
                    Statistiques des Adhérents en Temps Réel
                </h2>
                <p class="mb-0">Suivi intelligent du traitement par lots adaptatif</p>
            </div>

            <!-- Grid des statistiques -->
            <div class="stats-grid">
                <div class="stat-card highlight">
                    <div class="stat-icon-large">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value" id="total-adherents">1,247</div>
                    <div class="stat-description">Total Adhérents</div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" style="width: 100%;"></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon-large">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value" id="processed-adherents">0</div>
                    <div class="stat-description">Traités avec succès</div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" id="processed-progress" style="width: 0%;"></div>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon-large">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value" id="pending-adherents">1,247</div>
                    <div class="stat-description">En attente de traitement</div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" id="pending-progress" style="width: 100%;"></div>
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-icon-large">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value" id="anomalies-count">0</div>
                    <div class="stat-description">Anomalies détectées</div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar" id="anomalies-progress" style="width: 0%;"></div>
                    </div>
                </div>
            </div>

            <!-- Indicateurs supplémentaires -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="alert-gabon alert-info-gabon">
                        <div class="alert-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Traitement par Lots</div>
                            <div class="alert-text">
                                Les adhérents sont traités par lots de 100 pour optimiser les performances 
                                et éviter les timeouts serveur.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert-gabon alert-success-gabon">
                        <div class="alert-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Sécurité Garantie</div>
                            <div class="alert-text">
                                Toutes les données sont sauvegardées automatiquement. 
                                Vous pouvez reprendre en cas d'interruption.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================
             SECTION ACTIONS PRINCIPALES - BOUTONS GABONAIS
        ======================================================================== -->
        <div class="actions-section slide-in-right">
            <h3 class="text-primary fw-bold mb-4">
                <i class="fas fa-cogs me-2"></i>
                Actions Disponibles
            </h3>

            <!-- Messages d'orientation -->
            <div class="alert-gabon alert-warning-gabon mb-4">
                <div class="alert-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">Prêt pour l'Import</div>
                    <div class="alert-text">
                        1,247 adhérents sont prêts à être importés en base de données. 
                        Le traitement prendra environ 15 minutes avec notre système intelligent.
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="action-buttons">
                <button class="btn btn-gabon btn-primary-gabon" onclick="startImport()">
                    <i class="fas fa-play me-2"></i>
                    Commencer l'Import
                </button>
                <button class="btn btn-gabon btn-warning-gabon" onclick="saveDraft()">
                    <i class="fas fa-save me-2"></i>
                    Sauvegarder Brouillon
                </button>
                <button class="btn btn-gabon btn-secondary-gabon" onclick="submitToAdmin()">
                    <i class="fas fa-paper-plane me-2"></i>
                    Soumettre à l'Administration
                </button>
            </div>

            <!-- Informations complémentaires -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="h4 text-primary mb-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="fw-bold">~15 minutes</div>
                        <small class="text-muted">Temps estimé</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="h4 text-success mb-2">
                            <i class="fas fa-layers"></i>
                        </div>
                        <div class="fw-bold">Lots de 100</div>
                        <small class="text-muted">Traitement optimisé</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="h4 text-info mb-2">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="fw-bold">Temps réel</div>
                        <small class="text-muted">Suivi live</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================
             SECTION PROGRESS (Affichée pendant l'import)
        ======================================================================== -->
        <div class="progress-section" id="progress-section">
            <div class="progress-header text-center">
                <h3 class="text-primary fw-bold">
                    <i class="fas fa-cog fa-spin me-2"></i>
                    Import en Cours...
                </h3>
                <p class="text-muted">Traitement intelligent par lots adaptatif</p>
            </div>

            <div class="progress-main">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="progress-bar-container">
                            <div class="progress-bar-gabon" id="main-progress" style="width: 0%;"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">0%</span>
                            <span class="small fw-bold text-primary" id="progress-text">Préparation...</span>
                            <span class="small text-muted">100%</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="h4 text-primary mb-1" id="progress-percentage">0%</div>
                        <small class="text-muted">Progression globale</small>
                    </div>
                </div>

                <!-- Statistiques temps réel -->
                <div class="progress-stats">
                    <div class="progress-stat">
                        <div class="progress-stat-value" id="current-batch">1</div>
                        <div class="progress-stat-label">Lot actuel</div>
                    </div>
                    <div class="progress-stat">
                        <div class="progress-stat-value" id="total-batches">13</div>
                        <div class="progress-stat-label">Total lots</div>
                    </div>
                    <div class="progress-stat">
                        <div class="progress-stat-value" id="processed-count">0</div>
                        <div class="progress-stat-label">Traités</div>
                    </div>
                    <div class="progress-stat">
                        <div class="progress-stat-value" id="elapsed-time">00:00</div>
                        <div class="progress-stat-label">Temps écoulé</div>
                    </div>
                    <div class="progress-stat">
                        <div class="progress-stat-value" id="remaining-time">15:00</div>
                        <div class="progress-stat-label">Temps restant</div>
                    </div>
                    <div class="progress-stat">
                        <div class="progress-stat-value" id="speed-rate">0/min</div>
                        <div class="progress-stat-label">Vitesse</div>
                    </div>
                </div>
            </div>

            <!-- Actions pendant l'import -->
            <div class="text-center">
                <button class="btn btn-outline-warning me-2" onclick="pauseImport()">
                    <i class="fas fa-pause me-2"></i>Pause
                </button>
                <button class="btn btn-outline-danger" onclick="cancelImport()">
                    <i class="fas fa-stop me-2"></i>Annuler
                </button>
            </div>
        </div>

        <!-- ========================================================================
             TABLEAU APERÇU ADHÉRENTS (Optionnel)
        ======================================================================== -->
        <div class="adherents-table-section fade-in">
            <div class="table-header">
                <h4 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Aperçu des Adhérents
                </h4>
                <button class="btn btn-light btn-sm" onclick="toggleTable()">
                    <i class="fas fa-eye me-1"></i>Voir tout
                </button>
            </div>

            <!-- Contrôles de table -->
            <div class="table-controls">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" placeholder="Rechercher un adhérent..." id="search-adherent">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filter-status">
                            <option value="all">Tous les statuts</option>
                            <option value="pending">En attente</option>
                            <option value="processed">Traités</option>
                            <option value="error">Erreurs</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-primary w-100" onclick="refreshTable()">
                            <i class="fas fa-sync-alt me-2"></i>Actualiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table responsive -->
            <div class="table-responsive">
                <table class="table table-gabon">
                    <thead>
                        <tr>
                            <th>NIP</th>
                            <th>Nom Complet</th>
                            <th>Téléphone</th>
                            <th>Profession</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adherents-table-body">
                        <tr>
                            <td>A1-2345-19901225</td>
                            <td>M. MBOUMBA Jean-Pierre</td>
                            <td>+241 01 23 45 67</td>
                            <td>Enseignant</td>
                            <td><span class="badge-gabon badge-warning-gabon">En attente</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>B2-6789-19850630</td>
                            <td>Mme NZAMBA Marie-Claire</td>
                            <td>+241 02 34 56 78</td>
                            <td>Infirmière</td>
                            <td><span class="badge-gabon badge-warning-gabon">En attente</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>C3-9012-19930415</td>
                            <td>M. OBAME Paul-Henri</td>
                            <td>+241 03 45 67 89</td>
                            <td>Comptable</td>
                            <td><span class="badge-gabon badge-warning-gabon">En attente</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Affichage de 1 à 10 sur 1,247 adhérents
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">Précédent</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- ========================================================================
         FLOATING ACTION BUTTON - ACTIONS RAPIDES
    ======================================================================== -->
    <div class="fab-container">
        <div class="fab-menu" id="fabMenu">
            <button class="fab-main" onclick="toggleFAB()">
                <i class="fas fa-plus"></i>
            </button>
            <div class="fab-options">
                <button class="fab-option fab-option-green" title="Actualiser" onclick="refreshStats()">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="fab-option fab-option-yellow" title="Aide" onclick="showHelp()">
                    <i class="fas fa-question-circle"></i>
                </button>
                <button class="fab-option fab-option-blue" title="Export" onclick="exportData()">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========================================================================
        // JAVASCRIPT POUR INTERFACE PHASE 2 - GESTION ADHÉRENTS MODERNE
        // ========================================================================

        // Configuration globale
        const Phase2Config = {
            dossierId: 'SGF-2025-001234',
            totalAdherents: 1247,
            batchSize: 100,
            estimatedTime: 15 * 60 * 1000, // 15 minutes en ms
            updateInterval: 1000, // 1 seconde
            sessionExpiry: 2 * 60 * 60 * 1000, // 2 heures
            apiEndpoints: {
                import: '/api/adherents/import',
                status: '/api/adherents/status',
                pause: '/api/adherents/pause',
                cancel: '/api/adherents/cancel'
            }
        };

        // État global
        let importState = {
            isRunning: false,
            isPaused: false,
            currentBatch: 0,
            totalBatches: Math.ceil(Phase2Config.totalAdherents / Phase2Config.batchSize),
            processed: 0,
            errors: 0,
            startTime: null,
            elapsedTime: 0
        };

        // ========================================================================
        // FONCTIONS PRINCIPALES
        // ========================================================================

        // Démarrer l'import
        function startImport() {
            if (importState.isRunning) return;

            // Confirmation utilisateur
            if (!confirm('Démarrer l\'import de 1,247 adhérents ? Cette opération prendra environ 15 minutes.')) {
                return;
            }

            // Initialiser l'état
            importState.isRunning = true;
            importState.startTime = Date.now();
            importState.currentBatch = 1;
            importState.processed = 0;
            importState.errors = 0;

            // Afficher la section progress
            document.getElementById('progress-section').classList.add('active');

            // Démarrer le traitement
            processNextBatch();

            // Démarrer le timer
            startTimer();

            showNotification('info', 'Import Démarré', 'Le traitement des adhérents a commencé.');
        }

        // Traiter le lot suivant
        async function processNextBatch() {
            if (!importState.isRunning || importState.isPaused) return;

            const batchNumber = importState.currentBatch;
            const startIndex = (batchNumber - 1) * Phase2Config.batchSize;
            const endIndex = Math.min(startIndex + Phase2Config.batchSize, Phase2Config.totalAdherents);
            const batchSize = endIndex - startIndex;

            // Mettre à jour l'interface
            updateProgressText(`Traitement du lot ${batchNumber}/${importState.totalBatches} (${batchSize} adhérents)`);
            document.getElementById('current-batch').textContent = batchNumber;

            try {
                // Simulation d'appel API (à remplacer par vraie API)
                await simulateAPICall(batchSize);

                // Mettre à jour les statistiques
                importState.processed += batchSize;
                updateStatistics();

                // Vérifier si terminé
                if (importState.currentBatch >= importState.totalBatches) {
                    completeImport();
                } else {
                    // Passer au lot suivant
                    importState.currentBatch++;
                    setTimeout(processNextBatch, 1000); // Délai entre les lots
                }

            } catch (error) {
                console.error('Erreur traitement lot:', error);
                importState.errors++;
                updateStatistics();
                
                // Continuer avec le lot suivant malgré l'erreur
                importState.currentBatch++;
                setTimeout(processNextBatch, 2000);
            }
        }

        // Simulation d'appel API
        function simulateAPICall(batchSize) {
            return new Promise((resolve) => {
                // Temps aléatoire entre 2-5 secondes par lot
                const processingTime = Math.random() * 3000 + 2000;
                setTimeout(resolve, processingTime);
            });
        }

        // Mettre à jour les statistiques
        function updateStatistics() {
            const progressPercentage = Math.round((importState.processed / Phase2Config.totalAdherents) * 100);
            const remaining = Phase2Config.totalAdherents - importState.processed;

            // Mettre à jour les compteurs principaux
            document.getElementById('processed-adherents').textContent = importState.processed.toLocaleString();
            document.getElementById('pending-adherents').textContent = remaining.toLocaleString();
            document.getElementById('anomalies-count').textContent = importState.errors;

            // Mettre à jour les barres de progression
            document.getElementById('processed-progress').style.width = `${progressPercentage}%`;
            document.getElementById('pending-progress').style.width = `${100 - progressPercentage}%`;
            
            if (importState.errors > 0) {
                const errorPercentage = (importState.errors / importState.processed) * 100;
                document.getElementById('anomalies-progress').style.width = `${errorPercentage}%`;
            }

            // Mettre à jour la progress bar principale
            if (document.getElementById('main-progress')) {
                document.getElementById('main-progress').style.width = `${progressPercentage}%`;
                document.getElementById('progress-percentage').textContent = `${progressPercentage}%`;
            }

            // Mettre à jour les statistiques de traitement
            document.getElementById('processed-count').textContent = importState.processed;
            
            // Calculer la vitesse
            if (importState.elapsedTime > 0) {
                const speedPerMinute = Math.round((importState.processed / (importState.elapsedTime / 60000)));
                document.getElementById('speed-rate').textContent = `${speedPerMinute}/min`;
            }
        }

        // Mettre à jour le texte de progression
        function updateProgressText(text) {
            const progressTextElement = document.getElementById('progress-text');
            if (progressTextElement) {
                progressTextElement.textContent = text;
            }
        }

        // Timer pour le temps écoulé et restant
        function startTimer() {
            const timer = setInterval(() => {
                if (!importState.isRunning) {
                    clearInterval(timer);
                    return;
                }

                importState.elapsedTime = Date.now() - importState.startTime;
                
                // Mettre à jour le temps écoulé
                const elapsedMinutes = Math.floor(importState.elapsedTime / 60000);
                const elapsedSeconds = Math.floor((importState.elapsedTime % 60000) / 1000);
                document.getElementById('elapsed-time').textContent = 
                    `${elapsedMinutes.toString().padStart(2, '0')}:${elapsedSeconds.toString().padStart(2, '0')}`;

                // Calculer et afficher le temps restant
                if (importState.processed > 0) {
                    const averageTimePerAdherent = importState.elapsedTime / importState.processed;
                    const remainingAdherents = Phase2Config.totalAdherents - importState.processed;
                    const estimatedRemainingTime = remainingAdherents * averageTimePerAdherent;
                    
                    const remainingMinutes = Math.floor(estimatedRemainingTime / 60000);
                    const remainingSeconds = Math.floor((estimatedRemainingTime % 60000) / 1000);
                    document.getElementById('remaining-time').textContent = 
                        `${remainingMinutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        }

        // Terminer l'import
        function completeImport() {
            importState.isRunning = false;
            
            updateProgressText('Import terminé avec succès !');
            document.getElementById('progress-percentage').textContent = '100%';
            document.getElementById('main-progress').style.width = '100%';

            // Afficher notification de succès
            showNotification('success', 'Import Terminé !', 
                `${importState.processed} adhérents traités avec succès en ${Math.round(importState.elapsedTime / 60000)} minutes.`);

            // Activer le bouton de soumission
            setTimeout(() => {
                if (confirm('Import terminé avec succès ! Souhaitez-vous soumettre le dossier à l\'administration ?')) {
                    submitToAdmin();
                }
            }, 2000);
        }

        // Pause/Reprendre l'import
        function pauseImport() {
            if (importState.isPaused) {
                importState.isPaused = false;
                processNextBatch();
                showNotification('info', 'Import Repris', 'Le traitement a repris.');
            } else {
                importState.isPaused = true;
                showNotification('warning', 'Import en Pause', 'Le traitement est temporairement suspendu.');
            }
        }

        // Annuler l'import
        function cancelImport() {
            if (!confirm('Êtes-vous sûr de vouloir annuler l\'import ? Les données déjà traitées seront conservées.')) {
                return;
            }

            importState.isRunning = false;
            importState.isPaused = false;
            
            updateProgressText('Import annulé par l\'utilisateur');
            showNotification('warning', 'Import Annulé', 'Le traitement a été interrompu. Les données déjà traitées sont conservées.');
        }

        // Sauvegarder en brouillon
        function saveDraft() {
            showLoadingOverlay('Sauvegarde en cours...');
            
            // Simulation de sauvegarde
            setTimeout(() => {
                hideLoadingOverlay();
                showNotification('success', 'Brouillon Sauvegardé', 
                    'Vos données sont sécurisées. Vous pouvez reprendre plus tard.');
            }, 2000);
        }

        // Soumettre à l'administration
        function submitToAdmin() {
            if (importState.processed < Phase2Config.totalAdherents) {
                if (!confirm('L\'import n\'est pas terminé. Voulez-vous vraiment soumettre maintenant ?')) {
                    return;
                }
            }

            showLoadingOverlay('Soumission à l\'administration en cours...');
            
            // Simulation de soumission
            setTimeout(() => {
                hideLoadingOverlay();
                showNotification('success', 'Dossier Soumis !', 
                    'Votre dossier a été transmis à l\'administration. Délai de traitement : 15 jours ouvrés.');
                
                // Redirection vers la confirmation
                setTimeout(() => {
                    window.location.href = `/operator/dossiers/confirmation/${Phase2Config.dossierId}`;
                }, 3000);
            }, 3000);
        }

        // ========================================================================
        // UTILITAIRES ET INTERFACE
        // ========================================================================

        // Toggle FAB menu
        function toggleFAB() {
            const fabMenu = document.getElementById('fabMenu');
            fabMenu.classList.toggle('active');
        }

        // Actualiser les statistiques
        function refreshStats() {
            showNotification('info', 'Actualisation', 'Statistiques mises à jour.');
            updateStatistics();
        }

        // Afficher l'aide
        function showHelp() {
            const helpContent = `
                <div class="text-center mb-3">
                    <i class="fas fa-users fa-3x text-primary mb-2"></i>
                    <h4>Aide - Gestion des Adhérents</h4>
                </div>
                <div class="text-start">
                    <h6><i class="fas fa-info-circle me-2 text-info"></i>Fonctionnement</h6>
                    <p>Le système traite vos adhérents par lots de 100 pour éviter les timeouts et garantir la stabilité.</p>
                    
                    <h6><i class="fas fa-clock me-2 text-warning"></i>Temps de traitement</h6>
                    <p>Environ 15 minutes pour 1,247 adhérents. Vous pouvez suivre la progression en temps réel.</p>
                    
                    <h6><i class="fas fa-pause me-2 text-primary"></i>Pause et reprise</h6>
                    <p>Vous pouvez suspendre et reprendre l'import à tout moment sans perte de données.</p>
                    
                    <h6><i class="fas fa-shield-alt me-2 text-success"></i>Sécurité</h6>
                    <p>Toutes les données sont sauvegardées automatiquement. En cas de problème, vous ne perdrez rien.</p>
                </div>
            `;
            
            showModal('Aide - Gestion des Adhérents', helpContent);
        }

        // Exporter les données
        function exportData() {
            showNotification('info', 'Export en cours', 'Génération du fichier Excel...');
            
            // Simulation d'export
            setTimeout(() => {
                showNotification('success', 'Export Terminé', 'Le fichier a été téléchargé.');
            }, 2000);
        }

        // Fonctions de notification et modal (réutilisées du premier fichier)
        function showNotification(type, title, message) {
            const colors = {
                success: 'var(--gabon-green)',
                warning: 'var(--gabon-yellow)',
                error: '#dc3545',
                info: 'var(--gabon-blue)'
            };

            const notificationHtml = `
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                    <div class="toast show" role="alert" style="border-left: 4px solid ${colors[type]};">
                        <div class="toast-header">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'error' ? 'times-circle' : 'info-circle'} me-2" style="color: ${colors[type]};"></i>
                            <strong class="me-auto">${title}</strong>
                            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.parentElement.remove()"></button>
                        </div>
                        <div class="toast-body">${message}</div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', notificationHtml);
            
            setTimeout(() => {
                const toast = document.querySelector('.toast');
                if (toast) toast.remove();
            }, 5000);
        }

        function showLoadingOverlay(message = 'Chargement...') {
            const loadingHtml = `
                <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.7); z-index: 9999;">
                    <div class="text-center text-white">
                        <div class="spinner-border text-light mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5>${message}</h5>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loadingHtml);
        }

        function hideLoadingOverlay() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
        }

        function showModal(title, content) {
            const modalHtml = `
                <div class="modal fade" id="helpModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="background: var(--primary-gradient); color: white;">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">${content}</div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('helpModal');
            if (existingModal) existingModal.remove();
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('helpModal'));
            modal.show();
        }

        // Autres fonctions de l'interface
        function viewDetails() {
            showNotification('info', 'Détails', 'Affichage des détails de l\'organisation.');
        }

        function editOrganization() {
            showNotification('info', 'Modification', 'Redirection vers l\'édition...');
        }

        function toggleTable() {
            showNotification('info', 'Tableau', 'Affichage complet du tableau des adhérents.');
        }

        function refreshTable() {
            showNotification('info', 'Actualisation', 'Tableau actualisé.');
        }

        // ========================================================================
        // INITIALISATION
        // ========================================================================

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🇬🇦 Interface Phase 2 - Gestion des Adhérents initialisée');
            
            // Mettre à jour les statistiques initiales
            updateStatistics();
            
            // Configuration des événements
            setupEventListeners();
            
            // Simulation de mise à jour temps réel (hors import)
            setInterval(() => {
                if (!importState.isRunning) {
                    // Mettre à jour l'heure ou autres stats temps réel
                    const now = new Date();
                    console.log('Temps réel:', now.toLocaleTimeString());
                }
            }, 30000);
        });

        function setupEventListeners() {
            // Recherche dans le tableau
            document.getElementById('search-adherent')?.addEventListener('input', function(e) {
                // Logique de recherche
                console.log('Recherche:', e.target.value);
            });

            // Filtre de statut
            document.getElementById('filter-status')?.addEventListener('change', function(e) {
                // Logique de filtrage
                console.log('Filtre:', e.target.value);
            });

            // Fermer FAB au clic extérieur
            document.addEventListener('click', function(e) {
                const fabMenu = document.getElementById('fabMenu');
                if (fabMenu && !fabMenu.contains(e.target)) {
                    fabMenu.classList.remove('active');
                }
            });
        }

        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-card, .alert-gabon, .stat-item').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>