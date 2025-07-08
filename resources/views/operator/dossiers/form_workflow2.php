<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow 2 Phases - SGLP Gabon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ========================================================================
           CHARTE GRAPHIQUE OFFICIELLE GABONAISE - WORKFLOW 2 PHASES
           Couleurs officielles : Vert #009e3f, Jaune #ffcd00, Bleu #003f7f
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
            --gabon-red: #8b1538;
            
            /* Gradients thématiques gabonais */
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
            --transition-slow: 0.5s ease;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ========================================================================
           INTERFACE PHASE 1 TERMINÉE - DESIGN SUCCÈS GABONAIS
        ======================================================================== */
        
        .phase-success-banner {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .phase-success-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="gabon-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23gabon-pattern)"/></svg>');
            opacity: 0.3;
        }

        .phase-success-content {
            position: relative;
            z-index: 2;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: successPulse 3s ease-in-out infinite;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        @keyframes successPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(255, 255, 255, 0); }
        }

        .success-stats {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ========================================================================
           WORKFLOW PROGRESS - PHASES GABONAISES
        ======================================================================== */
        
        .workflow-progress {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: -3rem 0 2rem 0;
            position: relative;
            z-index: 10;
        }

        .phases-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin: 2rem 0;
        }

        .phase-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .phase-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
            transition: all var(--transition-normal);
        }

        .phase-step.completed .phase-circle {
            background: var(--primary-gradient);
            box-shadow: 0 8px 25px rgba(0, 158, 63, 0.4);
            animation: completedGlow 2s ease-in-out infinite;
        }

        .phase-step.active .phase-circle {
            background: var(--warning-gradient);
            color: #333;
            box-shadow: 0 8px 25px rgba(255, 205, 0, 0.4);
            animation: activePulse 2s ease-in-out infinite;
        }

        .phase-step.pending .phase-circle {
            background: #6c757d;
            color: white;
        }

        @keyframes completedGlow {
            0%, 100% { box-shadow: 0 8px 25px rgba(0, 158, 63, 0.4); }
            50% { box-shadow: 0 8px 25px rgba(0, 158, 63, 0.7), 0 0 0 10px rgba(0, 158, 63, 0.1); }
        }

        @keyframes activePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .phase-connector {
            position: absolute;
            top: 40px;
            left: 50%;
            right: -50%;
            height: 4px;
            background: #dee2e6;
            z-index: 1;
        }

        .phase-step.completed + .phase-step .phase-connector {
            background: var(--primary-gradient);
            animation: progressFill 1s ease-in-out;
        }

        @keyframes progressFill {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        .phase-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .phase-description {
            color: #6c757d;
            font-size: 0.9rem;
            text-align: center;
        }

        /* ========================================================================
           CARTES INFORMATIVES ORGANISATION
        ======================================================================== */
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-normal);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .info-card-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px 12px 0 0;
            margin: -1.5rem -1.5rem 1.5rem -1.5rem;
            position: relative;
            overflow: hidden;
        }

        .info-card-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1));
            transform: translateX(100px);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100px); }
            100% { transform: translateX(200px); }
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }

        .stat-value {
            font-weight: bold;
            color: var(--gabon-blue);
        }

        /* ========================================================================
           SECTION PHASE 2 - GESTION ADHÉRENTS MODERNE
        ======================================================================== */
        
        .phase2-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin: 2rem 0;
            border: 2px solid var(--gabon-yellow);
            position: relative;
            overflow: hidden;
        }

        .phase2-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--warning-gradient);
        }

        .phase2-header {
            background: var(--warning-gradient);
            color: #333;
            padding: 1.5rem;
            border-radius: 15px;
            margin: -2rem -2rem 2rem -2rem;
            text-align: center;
            position: relative;
        }

        .adherents-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all var(--transition-normal);
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--gabon-green);
            box-shadow: var(--shadow-md);
        }

        .stat-card.highlight {
            background: var(--primary-gradient);
            color: white;
        }

        .stat-card.warning {
            background: var(--warning-gradient);
            color: #333;
        }

        .stat-icon {
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

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-text {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* ========================================================================
           BOUTONS D'ACTION GABONAIS
        ======================================================================== */
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .btn-gabon {
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all var(--transition-normal);
            border: none;
            position: relative;
            overflow: hidden;
            min-width: 200px;
        }

        .btn-gabon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-gabon:hover::before {
            left: 100%;
        }

        .btn-primary-gabon {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 158, 63, 0.4);
        }

        .btn-primary-gabon:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 158, 63, 0.6);
        }

        .btn-secondary-gabon {
            background: white;
            color: var(--gabon-blue);
            border: 2px solid var(--gabon-blue);
            box-shadow: 0 4px 15px rgba(0, 63, 127, 0.2);
        }

        .btn-secondary-gabon:hover {
            background: var(--gabon-blue);
            color: white;
            transform: translateY(-2px);
        }

        .btn-warning-gabon {
            background: var(--warning-gradient);
            color: #333;
            box-shadow: 0 4px 15px rgba(255, 205, 0, 0.4);
        }

        .btn-warning-gabon:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 205, 0, 0.6);
        }

        /* ========================================================================
           GUIDE PROCÉDURE MODERNE
        ======================================================================== */
        
        .procedure-guide {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin: 2rem 0;
            border-left: 5px solid var(--gabon-green);
        }

        .guide-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .guide-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            height: 100%;
            width: 3px;
            background: linear-gradient(to bottom, var(--gabon-green), var(--gabon-yellow), var(--gabon-blue));
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 2rem;
        }

        .timeline-marker {
            position: absolute;
            left: -2.2rem;
            top: 0.5rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gabon-green);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0, 158, 63, 0.3);
            border: 4px solid white;
        }

        .timeline-content {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid #e9ecef;
        }

        .timeline-title {
            font-weight: bold;
            color: var(--gabon-blue);
            margin-bottom: 0.5rem;
        }

        .timeline-description {
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .timeline-badge {
            display: inline-block;
            background: var(--gabon-yellow);
            color: #333;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        /* ========================================================================
           MESSAGES EXPLICATIFS ANIMÉS
        ======================================================================== */
        
        .message-box {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
            border: 2px solid var(--gabon-green);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            position: relative;
            overflow: hidden;
        }

        .message-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--gabon-green);
        }

        .message-icon {
            width: 50px;
            height: 50px;
            background: var(--gabon-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            float: left;
            margin-right: 1rem;
            margin-bottom: 1rem;
            animation: iconBounce 2s ease-in-out infinite;
        }

        @keyframes iconBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .message-content {
            overflow: hidden;
        }

        .message-title {
            font-weight: bold;
            color: var(--gabon-green-dark);
            margin-bottom: 0.5rem;
        }

        .message-text {
            color: #495057;
            line-height: 1.6;
        }

        /* ========================================================================
           RESPONSIVE DESIGN
        ======================================================================== */
        
        @media (max-width: 768px) {
            .phases-container {
                flex-direction: column;
                gap: 2rem;
            }

            .phase-connector {
                display: none;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-gabon {
                width: 100%;
                max-width: 300px;
            }

            .adherents-stats {
                grid-template-columns: 1fr;
            }

            .success-stats {
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .workflow-progress,
            .phase2-section,
            .procedure-guide {
                margin-left: -1rem;
                margin-right: -1rem;
                border-radius: 0;
            }

            .phase-success-banner {
                padding: 1rem 0;
            }

            .success-icon {
                width: 70px;
                height: 70px;
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
            animation: fabPulse 3s ease-in-out infinite;
        }

        .fab-main:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 30px rgba(0, 158, 63, 0.6);
        }

        @keyframes fabPulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(0, 158, 63, 0.4); }
            50% { box-shadow: 0 4px 20px rgba(0, 158, 63, 0.8), 0 0 0 10px rgba(0, 158, 63, 0.1); }
        }
    </style>
</head>
<body>
    <!-- ========================================================================
         BANNIÈRE SUCCÈS PHASE 1 - DESIGN GABONAIS OFFICIEL
    ======================================================================== -->
    <div class="phase-success-banner">
        <div class="container">
            <div class="phase-success-content text-center">
                <div class="success-icon">
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
                <h1 class="display-4 fw-bold mb-3">Phase 1 Complétée avec Succès !</h1>
                <p class="lead mb-4">Votre organisation a été créée et enregistrée dans la base de données SGLP</p>
                
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="success-stats fade-in">
                            <div class="row text-center">
                                <div class="col-md-4 mb-3">
                                    <div class="h4 mb-1">
                                        <i class="fas fa-building me-2"></i>
                                        Organisation Créée
                                    </div>
                                    <small class="opacity-90">N° Récépissé: #SGF-2025-001234</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="h4 mb-1">
                                        <i class="fas fa-users me-2"></i>
                                        1,247 Adhérents
                                    </div>
                                    <small class="opacity-90">En attente d'import Phase 2</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="h4 mb-1">
                                        <i class="fas fa-clock me-2"></i>
                                        15 min
                                    </div>
                                    <small class="opacity-90">Temps estimé Phase 2</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- ========================================================================
             INDICATEUR DE PROGRESSION - WORKFLOW 2 PHASES
        ======================================================================== -->
        <div class="workflow-progress fade-in">
            <div class="text-center mb-4">
                <h2 class="h3 text-primary fw-bold">Workflow de Création en 2 Phases</h2>
                <p class="text-muted">Système optimisé pour les gros volumes d'adhérents</p>
            </div>

            <div class="phases-container">
                <div class="phase-step completed slide-in-left">
                    <div class="phase-circle">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="phase-title">Phase 1</div>
                    <div class="phase-description">
                        Création Organisation<br>
                        <small class="text-success"><i class="fas fa-check-circle"></i> Terminée</small>
                    </div>
                    <div class="phase-connector"></div>
                </div>

                <div class="phase-step active slide-in-right">
                    <div class="phase-circle">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="phase-title">Phase 2</div>
                    <div class="phase-description">
                        Import Adhérents<br>
                        <small class="text-warning"><i class="fas fa-clock"></i> En cours</small>
                    </div>
                </div>
            </div>

            <!-- Messages explicatifs modernisés -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="message-box">
                        <div class="message-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-title">Pourquoi 2 Phases ?</div>
                            <div class="message-text">
                                Le système 2 phases évite les timeouts pour les organisations avec de nombreux adhérents, 
                                garantissant un traitement sûr et efficace de vos données.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="message-box">
                        <div class="message-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-title">Sécurité Garantie</div>
                            <div class="message-text">
                                Vos données sont sauvegardées et sécurisées. En cas d'interruption, 
                                vous pourrez reprendre exactement où vous vous êtes arrêté.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================
             INFORMATIONS ORGANISATION - RÉCAPITULATIF ÉLÉGANT
        ======================================================================== -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="info-card fade-in">
                    <div class="info-card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-building me-2"></i>
                            Informations de l'Organisation
                        </h4>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Nom de l'organisation</span>
                        <span class="stat-value">Association Jeunesse Solidarité Gabon</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Type</span>
                        <span class="stat-value">Association</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Siège social</span>
                        <span class="stat-value">Libreville, Estuaire</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Demandeur principal</span>
                        <span class="stat-value">M. Jean MBOUMBA</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Fondateurs</span>
                        <span class="stat-value">5 personnes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Statut</span>
                        <span class="stat-value">
                            <span class="badge bg-warning text-dark">Phase 2 en cours</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="info-card fade-in">
                    <div class="info-card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Progression
                        </h4>
                    </div>
                    <div class="text-center py-3">
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: 50%; background: var(--primary-gradient);" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="h4 text-primary">50%</div>
                        <small class="text-muted">Workflow global terminé</small>
                    </div>
                    <div class="mt-3">
                        <div class="small text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Temps écoulé: 25 minutes
                        </div>
                        <div class="small text-muted">
                            <i class="fas fa-hourglass-half me-1"></i>
                            Temps restant estimé: 15 minutes
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================
             SECTION PHASE 2 - GESTION ADHÉRENTS AVANCÉE
        ======================================================================== -->
        <div class="phase2-section fade-in">
            <div class="phase2-header">
                <h2 class="h3 mb-3">
                    <i class="fas fa-user-plus me-2"></i>
                    Phase 2 : Import des Adhérents
                </h2>
                <p class="mb-0">Système d'import intelligent avec traitement par lots adaptatif</p>
            </div>

            <!-- Statistiques des adhérents -->
            <div class="adherents-stats">
                <div class="stat-card highlight">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">1,247</div>
                    <div class="stat-text">Adhérents en attente</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number">0</div>
                    <div class="stat-text">Adhérents traités</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-number">0</div>
                    <div class="stat-text">Anomalies détectées</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number">15</div>
                    <div class="stat-text">Minutes estimées</div>
                </div>
            </div>

            <!-- Message d'orientation -->
            <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #e8f4fd 0%, #f0f8ff 100%);">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-lightbulb fa-2x text-info"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading mb-2">Import Intelligent Activé</h5>
                        <p class="mb-0">
                            Le système va traiter vos 1,247 adhérents par lots optimisés pour éviter les timeouts. 
                            Vous pourrez suivre la progression en temps réel et reprendre en cas d'interruption.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action principaux -->
            <div class="action-buttons">
                <button class="btn btn-gabon btn-primary-gabon" onclick="proceedToPhase2()">
                    <i class="fas fa-play me-2"></i>
                    Commencer l'Import des Adhérents
                </button>
                <button class="btn btn-gabon btn-secondary-gabon" onclick="saveDraft()">
                    <i class="fas fa-save me-2"></i>
                    Sauvegarder en Brouillon
                </button>
            </div>
        </div>

        <!-- ========================================================================
             GUIDE PROCÉDURE - TIMELINE MODERNE
        ======================================================================== -->
        <div class="procedure-guide fade-in">
            <div class="text-center mb-4">
                <h3 class="text-primary fw-bold">
                    <i class="fas fa-map-signs me-2"></i>
                    Guide de la Procédure
                </h3>
                <p class="text-muted">Étapes détaillées du processus d'enregistrement</p>
            </div>

            <div class="guide-timeline">
                <div class="timeline-item">
                    <div class="timeline-marker">1</div>
                    <div class="timeline-content">
                        <div class="timeline-title">Phase 1 : Création Organisation</div>
                        <div class="timeline-description">
                            Enregistrement des informations de base, fondateurs et documents justificatifs.
                        </div>
                        <span class="timeline-badge">✓ Terminé</span>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker">2</div>
                    <div class="timeline-content">
                        <div class="timeline-title">Phase 2 : Import Adhérents</div>
                        <div class="timeline-description">
                            Traitement intelligent des adhérents par lots adaptatifs avec validation en temps réel.
                        </div>
                        <span class="timeline-badge">En cours</span>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker">3</div>
                    <div class="timeline-content">
                        <div class="timeline-title">Soumission à l'Administration</div>
                        <div class="timeline-description">
                            Transmission automatique du dossier complet aux services d'enregistrement.
                        </div>
                        <span class="timeline-badge">À venir</span>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker">4</div>
                    <div class="timeline-content">
                        <div class="timeline-title">Validation Administrative</div>
                        <div class="timeline-description">
                            Examen du dossier par les agents habilités (délai : 15 jours ouvrés).
                        </div>
                        <span class="timeline-badge">À venir</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================
         FLOATING ACTION BUTTON - AIDE ET ACTIONS RAPIDES
    ======================================================================== -->
    <div class="fab-container">
        <button class="fab-main" onclick="showHelp()" title="Aide et Support">
            <i class="fas fa-question-circle"></i>
        </button>
    </div>

    <!-- Scripts JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========================================================================
        // FONCTIONS JAVASCRIPT POUR L'INTERFACE WORKFLOW 2 PHASES
        // ========================================================================

        // Configuration globale
        const WorkflowConfig = {
            currentPhase: 2,
            dossierId: 'SGF-2025-001234',
            adherentsCount: 1247,
            organizationName: 'Association Jeunesse Solidarité Gabon',
            sessionExpiry: 2 * 60 * 60 * 1000, // 2 heures
            chunkSize: 100 // Traitement par lots de 100
        };

        // Procéder à la Phase 2
        function proceedToPhase2() {
            // Animation de transition
            showLoadingState('Redirection vers l\'interface d\'import des adhérents...');
            
            // Simulation de redirection (à remplacer par la vraie URL)
            setTimeout(() => {
                window.location.href = `/operator/dossiers/${WorkflowConfig.dossierId}/adherents-import`;
            }, 2000);
        }

        // Sauvegarder en brouillon
        function saveDraft() {
            showLoadingState('Sauvegarde en cours...');
            
            // Simulation d'API call
            setTimeout(() => {
                hideLoadingState();
                showNotification('success', 'Brouillon sauvegardé avec succès!', 'Vous pourrez reprendre plus tard.');
            }, 1500);
        }

        // Afficher l'aide
        function showHelp() {
            const helpContent = `
                <div class="text-center mb-3">
                    <i class="fas fa-question-circle fa-3x text-primary mb-2"></i>
                    <h4>Aide - Workflow 2 Phases</h4>
                </div>
                <div class="text-start">
                    <h6><i class="fas fa-info-circle me-2 text-info"></i>Phase actuelle</h6>
                    <p>Vous êtes en Phase 2 : Import des adhérents. Cette phase traite intelligemment vos adhérents par lots pour éviter les timeouts.</p>
                    
                    <h6><i class="fas fa-clock me-2 text-warning"></i>Temps estimé</h6>
                    <p>Environ 15 minutes pour 1,247 adhérents (traitement par lots de 100).</p>
                    
                    <h6><i class="fas fa-shield-alt me-2 text-success"></i>Sécurité</h6>
                    <p>Vos données sont sauvegardées automatiquement. En cas d'interruption, vous pourrez reprendre.</p>
                    
                    <h6><i class="fas fa-phone me-2 text-primary"></i>Support</h6>
                    <p>Besoin d'aide ? Contactez le support SGLP au +241 01 23 45 67</p>
                </div>
            `;
            
            showModal('Aide et Support', helpContent);
        }

        // Utilitaires pour notifications et états
        function showLoadingState(message = 'Chargement...') {
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

        function hideLoadingState() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
        }

        function showNotification(type, title, message) {
            const colors = {
                success: 'var(--gabon-green)',
                warning: 'var(--gabon-yellow)',
                error: 'var(--gabon-red)',
                info: 'var(--gabon-blue)'
            };

            const notificationHtml = `
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                    <div class="toast show" role="alert" style="border-left: 4px solid ${colors[type]};">
                        <div class="toast-header">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'error' ? 'times-circle' : 'info-circle'} me-2" style="color: ${colors[type]};"></i>
                            <strong class="me-auto">${title}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">${message}</div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', notificationHtml);
            
            // Auto-remove après 5 secondes
            setTimeout(() => {
                const toast = document.querySelector('.toast');
                if (toast) toast.remove();
            }, 5000);
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
            
            // Supprimer modal existant
            const existingModal = document.getElementById('helpModal');
            if (existingModal) existingModal.remove();
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('helpModal'));
            modal.show();
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🇬🇦 Interface Workflow 2 Phases - SGLP Gabon initialisée');
            
            // Simulation mise à jour temps réel des stats
            updateStatsRealTime();
        });

        function updateStatsRealTime() {
            // Simulation de mise à jour en temps réel
            setInterval(() => {
                const progressBar = document.querySelector('.progress-bar');
                if (progressBar) {
                    const currentWidth = parseInt(progressBar.style.width) || 50;
                    if (currentWidth < 100) {
                        progressBar.style.width = `${Math.min(currentWidth + 1, 100)}%`;
                    }
                }
            }, 30000); // Mise à jour toutes les 30 secondes
        }

        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationDelay = '0s';
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observer tous les éléments avec animation
        document.querySelectorAll('.info-card, .stat-card, .timeline-item').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>