<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Anomalies - {{ $stats['organisation'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #dc3545;
            font-size: 24px;
            margin: 0 0 10px 0;
        }

        .header h2 {
            color: #666;
            font-size: 16px;
            margin: 0;
            font-weight: normal;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .info-row strong {
            display: inline-block;
            width: 150px;
            color: #495057;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .stats-row {
            display: table-row;
        }

        .stats-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .stats-number {
            font-size: 28px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 5px;
        }

        .stats-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }

        .critique .stats-number { color: #dc3545; }
        .majeure .stats-number { color: #fd7e14; }
        .mineure .stats-number { color: #20c997; }

        .section-title {
            background: #dc3545;
            color: white;
            padding: 10px 15px;
            margin: 30px 0 15px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .anomalies-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .anomalies-table th {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            color: #495057;
        }

        .anomalies-table td {
            border: 1px solid #dee2e6;
            padding: 8px 6px;
            vertical-align: top;
        }

        .anomalies-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .severity-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }

        .severity-critique { background: #dc3545; }
        .severity-majeure { background: #fd7e14; }
        .severity-mineure { background: #20c997; }

        .anomalie-item {
            font-size: 9px;
            margin-bottom: 3px;
            padding: 2px 0;
        }

        .anomalie-critique { color: #dc3545; }
        .anomalie-majeure { color: #fd7e14; }
        .anomalie-mineure { color: #20c997; }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }

        .summary-table th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .nip-code {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- En-tête du rapport -->
    <div class="header">
        <h1>RAPPORT DES ANOMALIES</h1>
    </div>

    <!-- Informations générales -->
    <div class="info-box">
        <div class="info-row">
            <strong>Organisation :</strong> {{ $stats['organisation'] }}
        </div>
        <div class="info-row">
            <strong>Numéro de dossier :</strong> {{ $stats['dossier_numero'] }}
        </div>
        <div class="info-row">
            <strong>Date de génération :</strong> {{ $stats['date_generation'] }}
        </div>
        <div class="info-row">
            <strong>Total d'anomalies :</strong> {{ $stats['total'] }} anomalie(s) détectée(s)
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="section-title">STATISTIQUES GLOBALES</div>
    
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell">
                <div class="stats-number">{{ $stats['total'] }}</div>
                <div class="stats-label">Total</div>
            </div>
            <div class="stats-cell critique">
                <div class="stats-number">{{ $stats['critiques'] }}</div>
                <div class="stats-label">Critiques</div>
            </div>
            <div class="stats-cell majeure">
                <div class="stats-number">{{ $stats['majeures'] }}</div>
                <div class="stats-label">Majeures</div>
            </div>
            <div class="stats-cell mineure">
                <div class="stats-number">{{ $stats['mineures'] }}</div>
                <div class="stats-label">Mineures</div>
            </div>
        </div>
    </div>

    <!-- Résumé par type d'anomalie -->
    @if(isset($stats['anomalies_par_type']) && count($stats['anomalies_par_type']) > 0)
    <div class="section-title">RÉPARTITION PAR TYPE D'ANOMALIE</div>
    
    <table class="summary-table">
        <thead>
            <tr>
                <th>Type d'anomalie</th>
                <th>Nombre d'occurrences</th>
                <th>Sévérité</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['anomalies_par_type'] as $typeAnomalie)
            <tr>
                <td>{{ $typeAnomalie['message'] }}</td>
                <td>{{ $typeAnomalie['count'] }}</td>
                <td>
                    <span class="severity-badge severity-{{ $typeAnomalie['severity'] }}">
                        {{ ucfirst($typeAnomalie['severity']) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Liste détaillée des anomalies -->
    @if($anomalies->count() > 0)
    <div class="section-title">DÉTAIL DES ANOMALIES PAR ADHÉRENT</div>
    
    <table class="anomalies-table">
        <thead>
            <tr>
                <th style="width: 20%">Adhérent</th>
                <th style="width: 15%">NIP</th>
                <th style="width: 10%">Sévérité</th>
                <th style="width: 40%">Anomalies détectées</th>
                <th style="width: 15%">Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($anomalies as $adherent)
            <tr>
                <td>
                    <strong>{{ $adherent->nom }} {{ $adherent->prenom }}</strong>
                </td>
                <td>
                    @if($adherent->nip)
                        <span class="nip-code">{{ $adherent->nip }}</span>
                    @else
                        <span class="severity-badge severity-critique">NIP manquant</span>
                    @endif
                </td>
                <td>
                    <span class="severity-badge severity-{{ $adherent->anomalies_severity ?? 'mineure' }}">
                        {{ ucfirst($adherent->anomalies_severity ?? 'Mineure') }}
                    </span>
                </td>
                <td>
                    @if($adherent->anomalies_data && count($adherent->anomalies_data) > 0)
                        @foreach($adherent->anomalies_data as $anomalie)
                        <div class="anomalie-item anomalie-{{ $anomalie['type'] ?? 'mineure' }}">
                            • {{ $anomalie['message'] ?? $anomalie['code'] ?? 'Anomalie non spécifiée' }}
                        </div>
                        @endforeach
                    @else
                        <em>Détails non disponibles</em>
                    @endif
                </td>
                <td>
                    {{ $adherent->telephone ?? 'N/A' }}<br>
                    <small>{{ $adherent->email ?? 'Email non renseigné' }}</small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="section-title">✅ AUCUNE ANOMALIE DÉTECTÉE</div>
    <p>Tous les adhérents de cette organisation sont conformes aux règles de validation.</p>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        

        <p>
            <table style="float: left; width:100%;">
                <tr>
                    <td style="width:60%;"></td>
                
                    <td style="width:40%; font-weight: bold; text-align: center; font-size:14px; color:#000000;">
                        Le Directeur Général des Elections<br/>
                        et des Libertés Publiques<br/><br/><br/><br/><br/><br/>

                        Dieudonné YAYA
                    </td>
                </tr>
            </table>
        </p>

        
        <p style="margin-top:60px;">
            <br/><br/><br/><br/><br/><br/>
            Rapport généré automatiquement le {{ $stats['date_generation'] }} | 
            SGLP - Système de Gestion des Libertés Publiques
        </p>
        <p style="font-weight:bold">
            Ce document contient des informations confidentielles. 
            Sa diffusion est strictement réservée aux personnes autorisées.
        </p>
    </div>
</body>
</html>