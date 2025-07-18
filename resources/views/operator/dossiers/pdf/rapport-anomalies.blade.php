<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Anomalies - {{ $stats['organisation'] }}</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        /* Styles officiels du ministère */
        .header-ministeriel {
            margin-bottom: 20px;
        }

        .bande-drapeau {
            width: 100%;
            margin-bottom: 10px;
        }

        .bande-drapeau tr td {
            height: 4px;
        }

        .bande-vert { background-color: #009e3f; }
        .bande-jaune { background-color: #ffcd00; }
        .bande-bleu { background-color: #003f7f; }

        .entete-ministeriel {
            width: 100%;
            margin-bottom: 25px;
        }

        .ministere-info {
            width: 60%;
            color: #333333;
            font-weight: bold;
            font-size: 10px;
            text-align: left;
            vertical-align: top;
            padding-right: 20px;
        }

        .ministere-titre {
            font-size: 11px;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .ministere-separation {
            border-bottom: 1px dotted #333;
            margin: 5px 0;
            width: 40%;
        }

        .ministere-direction {
            font-size: 10px;
            margin: 3px 0;
        }

        .numero-reference {
            font-size: 12pt;
            margin-top: 15px;
            color: #003f7f;
        }

        .republique-info {
            width: 40%;
            color: #003f7f;
            font-weight: bold;
            font-size: 13pt;
            text-align: center;
            vertical-align: top;
        }

        .republique-titre {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .republique-devise {
            font-size: 11pt;
            font-weight: normal;
        }

        /* Titre principal du rapport */
        .titre-rapport {
            text-align: center;
            margin: 30px 0;
        }

        .titre-principal {
            color: #003f7f;
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #003f7f;
            padding: 15px 25px;
            display: inline-block;
            margin: 20px 0;
        }

        .sous-titre {
            color: #009e3f;
            font-size: 16pt;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Informations générales */
        .info-box {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom:5px;
            font-size: 11pt;
        }

        .info-row {
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .info-row strong {
            display: inline-block;
            width: 180px;
            color: #003f7f;
            font-weight: bold;
        }

        /* Statistiques */
        .stats-section {
            margin: 25px 0;
        }

        .stats-title {
            background: linear-gradient(90deg, #003f7f 0%, #009e3f 100%);
            color: #003f7f;
            padding: 12px 20px;
            margin-top: 10px;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .stats-row {
            display: table-row;
        }

        .stats-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 20px 10px;
            border-right: 1px solid #003f7f;
            background: #f8f9fa;
            vertical-align: middle;
        }

        .stats-cell:last-child {
            border-right: none;
        }

        .stats-number {
            font-size: 28pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #003f7f;
        }

        .stats-label {
            font-size: 10pt;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }

        .stats-critique .stats-number { color: #dc3545; }
        .stats-majeure .stats-number { color: #fd7e14; }
        .stats-mineure .stats-number { color: #009e3f; }

        /* Tableaux */
        .section-title {
            background: linear-gradient(90deg, #009e3f 0%, #ffcd00 100%);
            color: #003f7f;
            padding: 12px 20px;
            margin: 30px 0 15px 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 2px solid #003f7f;
        }

        .tableau-officiel {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 10pt;
            border: 2px solid #003f7f;
        }

        .tableau-officiel th {
            background: linear-gradient(90deg, #003f7f 0%, #009e3f 100%);
            color: white;
            border: 1px solid #003f7f;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
        }

        .tableau-officiel td {
            border: 1px solid #003f7f;
            padding: 10px 8px;
            vertical-align: top;
        }

        .tableau-officiel tr:nth-child(even) {
            background: #f8f9fa;
        }

        .tableau-officiel tr:hover {
            background: #e3f2fd;
        }

        /* Badges de sévérité */
        .severity-badge {
            display: inline-block;
            padding: 4px;
            border-radius:5px;
            font-size: 6pt;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            border: 1px solid;
        }

        .severity-critique { 
            background: #dc3545; 
            border-color: #dc3545;
        }
        .severity-majeure { 
            background: #fd7e14; 
            border-color: #fd7e14;
        }
        .severity-mineure { 
            background: #009e3f; 
            border-color: #009e3f;
        }

        /* Code NIP */
        .nip-code {
            font-family: 'Courier New', monospace;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9pt;
            color: #003f7f;
            font-weight: bold;
        }

        /* Anomalies détaillées */
        .anomalie-item {
            font-size: 9pt;
            margin-bottom: 4px;
            padding: 3px 0;
            border-left: 3px solid;
            padding-left: 8px;
        }

        .anomalie-critique { 
            color: #dc3545; 
            border-left-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }
        .anomalie-majeure { 
            color: #fd7e14; 
            border-left-color: #fd7e14;
            background: rgba(253, 126, 20, 0.1);
        }
        .anomalie-mineure { 
            color: #009e3f; 
            border-left-color: #009e3f;
            background: rgba(0, 158, 63, 0.1);
        }

        /* Pied de page officiel */
        .footer-officiel {
            margin-top: 40px;
            padding-top: 20px;
            font-size: 10pt;
            color: #666;
            text-align: center;
            line-height: 1.3;
        }

        .footer-ministeriel {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #003f7f;
            font-style: italic;
            color: #003f7f;
        }

        /* Utilitaires */
        .page-break {
            page-break-before: always;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Styles pour impression */
        @media print {
            body { margin: 0; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <!-- Bande drapeau officielle -->
    <table class="bande-drapeau" cellspacing="0" cellpadding="0">
        <tr>
            <td class="bande-vert"></td>
        </tr>
        <tr>
            <td class="bande-jaune"></td>
        </tr>
        <tr>
            <td class="bande-bleu"></td>
        </tr>
    </table>

    <!-- En-tête ministériel officiel -->
    <table class="entete-ministeriel">
        <tr>
            <td class="ministere-info">
                <div class="ministere-titre">
                    MINISTÈRE DE L'INTÉRIEUR, DE LA SÉCURITÉ<br>
                    ET DE LA DÉCENTRALISATION
                </div>
                <div class="ministere-separation"></div>
                <div class="ministere-direction">SECRÉTARIAT GÉNÉRAL</div>
                <div class="ministere-separation"></div>
                <div class="ministere-direction">
                    DIRECTION GÉNÉRALE DES ÉLECTIONS<br>
                    ET DES LIBERTÉS PUBLIQUES
                </div>
                <div class="ministere-separation"></div>
                <div class="ministere-direction">
                    DIRECTION DES PARTIS POLITIQUES,<br>
                    ASSOCIATIONS ET LIBERTÉ DE CULTE
                </div>
                <div class="ministere-separation"></div>
            </td>
            <td class="republique-info">
                <div class="republique-titre">RÉPUBLIQUE GABONAISE</div>
                <div class="republique-devise">UNION • TRAVAIL • JUSTICE</div>
            </td>
        </tr>
    </table>

    <!-- Titre principal du rapport -->
    <div class="titre-rapport">
        <div class="titre-principal">
            RAPPORT D'ANOMALIES DE L'ETAT D'ADHESION
        </div>
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
        <div class="info-row">
            <strong>Générateur :</strong> {{ Auth::user()->email }} - Système SGLP
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="stats-title">STATISTIQUES GLOBALES DES ANOMALIES</div>
    
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell">
                <div class="stats-number">{{ $stats['total'] }}</div>
                <div class="stats-label">Total Anomalies</div>
            </div>
            <div class="stats-cell stats-critique">
                <div class="stats-number">{{ $stats['critiques'] }}</div>
                <div class="stats-label">Critiques</div>
            </div>
            <div class="stats-cell stats-majeure">
                <div class="stats-number">{{ $stats['majeures'] }}</div>
                <div class="stats-label">Majeures</div>
            </div>
            <div class="stats-cell stats-mineure">
                <div class="stats-number">{{ $stats['mineures'] }}</div>
                <div class="stats-label">Mineures</div>
            </div>
        </div>
    </div>

    <!-- Résumé par type d'anomalie -->
    @if(isset($stats['anomalies_par_type']) && count($stats['anomalies_par_type']) > 0)
    <div class="section-title">RÉPARTITION PAR TYPE D'ANOMALIE</div>
    
    <table class="tableau-officiel">
        <thead>
            <tr>
                <th style="width: 50%; background-color:#003f7f; color: white;">TYPE D'ANOMALIE</th>
                <th style="width: 20%; background-color:#003f7f; color: white;">OCCURRENCES</th>
                <th style="width: 20%; background-color:#003f7f; color: white;">SÉVÉRITÉ</th>
                <th style="width: 10%; background-color:#003f7f; color: white;">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['anomalies_par_type'] as $typeAnomalie)
            <tr>
                <td style="font-weight: bold;">{{ $typeAnomalie['message'] }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $typeAnomalie['count'] }}</td>
                <td style="text-align: center;">
                    <span class="severity-badge severity-{{ $typeAnomalie['severity'] }}">
                        {{ ucfirst($typeAnomalie['severity']) }}
                    </span>
                </td>
                <td style="text-align: center;">
                    {{ $stats['total'] > 0 ? round(($typeAnomalie['count'] / $stats['total']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Liste détaillée des anomalies -->
    @if($anomalies->count() > 0)
    <div class="section-title">DÉTAIL DES ANOMALIES PAR ADHÉRENT</div>
    
    <table class="tableau-officiel">
        <thead>
            <tr>
                <th style="width: 25%; background-color:#003f7f; color: white;">ADHÉRENT</th>
                <th style="width: 15%; background-color:#003f7f; color: white;">NIP</th>
                <th style="width: 10%; background-color:#003f7f; color: white;">SÉVÉRITÉ</th>
                <th style="width: 35%; background-color:#003f7f; color: white;">ANOMALIES DÉTECTÉES</th>
                <th style="width: 15%; background-color:#003f7f; color: white;">CONTACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($anomalies as $adherent)
            <tr>
                <td style="font-weight: bold;">
                    {{ strtoupper($adherent->nom) }} {{ ucfirst($adherent->prenom) }}
                </td>
                <td style="text-align: center;">
                    @if($adherent->nip)
                        <span class="nip-code">{{ $adherent->nip }}</span>
                    @else
                        <span class="severity-badge severity-critique">NIP MANQUANT</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="severity-badge severity-{{ $adherent->anomalies_severity ?? 'mineure' }}">
                        {{ strtoupper($adherent->anomalies_severity ?? 'Mineure') }}
                    </span>
                </td>
                <td>
                    @if($adherent->anomalies_data && count($adherent->anomalies_data) > 0)
                        @foreach($adherent->anomalies_data as $anomalie)
                        <div class="anomalie-item anomalie-{{ $anomalie['type'] ?? 'mineure' }}">
                            <strong>•</strong> {{ $anomalie['message'] ?? $anomalie['code'] ?? 'Anomalie non spécifiée' }}
                        </div>
                        @endforeach
                    @else
                        <em style="color: #666;">Détails non disponibles</em>
                    @endif
                </td>
                <td style="font-size: 9pt;">
                    <strong>Tél:</strong> {{ $adherent->telephone ?? 'N/A' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="section-title">✅ AUCUNE ANOMALIE DÉTECTÉE</div>
    <div style="text-align: center; padding: 30px; background: #e8f5e8; border: 2px solid #009e3f; border-radius: 8px;">
        <h3 style="color: #009e3f; margin: 0;">CONFORMITÉ TOTALE</h3>
        <p style="margin: 10px 0;">Tous les adhérents de cette organisation sont conformes aux règles de validation du système SGLP.</p>
    </div>
    @endif

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
    <br/><br/><br/><br/><br/><br/><br/>

    <!-- Pied de page officiel -->
    <div class="footer-officiel">
        <div style="font-weight: bold; color: #003f7f;">
            Rapport généré automatiquement le {{ $stats['date_generation'] }} par le Système de Gestion des Libertés Publiques.
        </div>
        
        <div class="footer-ministeriel">
            <strong>MINISTÈRE DE L'INTÉRIEUR, DE LA SÉCURITÉ ET DE LA DÉCENTRALISATION</strong><br>
            119, RUE Jean Baptiste NDENDE, (Avenue de Cointet BP 2110 Libreville, Gabon)<br>
            <em>Ce document contient des informations confidentielles. Sa diffusion est strictement réservée aux personnes autorisées.</em>
        </div>
    </div>
</body>
</html>