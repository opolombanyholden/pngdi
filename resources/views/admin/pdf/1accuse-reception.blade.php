<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accusé de Réception - {{ $nom_organisation }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .logo-section {
            float: left;
            width: 120px;
            height: 80px;
            margin-right: 20px;
        }
        
        .header-text {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            line-height: 1.2;
        }
        
        .ministry-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .department-divider {
            margin: 3px 0;
            border-bottom: 1px solid #000;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .reference-number {
            text-align: right;
            margin: 20px 0;
            font-size: 11pt;
        }
        
        .document-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 30px 0;
            text-transform: uppercase;
        }
        
        .content {
            text-align: justify;
            margin: 30px 0;
            line-height: 1.6;
        }
        
        .content p {
            margin-bottom: 15px;
        }
        
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        
        .signature-location {
            margin-bottom: 40px;
        }
        
        .minister-name {
            font-weight: bold;
            margin-top: 60px;
        }
        
        .copy-section {
            margin-top: 30px;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .highlight {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="logo-section">
            <!-- Logo du Gabon - remplacer par le chemin réel si nécessaire -->
            <div style="width: 100px; height: 70px; border: 1px solid #ccc; text-align: center; line-height: 70px; font-size: 8pt;">
        
            <img src="{{ public_path('images/logo.png') }}" 
                alt="Armoiries du Gabon" 
                style="width: 100px; height: 70px;">
            </div>

        </div>
        
        <div class="header-text">
            <div class="ministry-title">MINISTÈRE DE L'INTÉRIEUR, DE LA SÉCURITÉ</div>
            <div class="ministry-title">ET DE LA DÉCENTRALISATION</div>
            <div class="department-divider"></div>
            <div>SECRÉTARIAT GÉNÉRAL</div>
            <div class="department-divider"></div>
            <div>DIRECTION GÉNÉRALE DES ÉLECTIONS</div>
            <div>ET DES LIBERTÉS PUBLIQUES</div>
            <div class="department-divider"></div>
            <div>DIRECTION DES PARTIS POLITIQUES</div>
            <div>ASSOCIATIONS ET LIBERTÉ DE CULTE</div>
            <div class="department-divider"></div>
        </div>
    </div>

    <div class="reference-number">
        N° {{ $numero_administratif }}
    </div>

    <div class="document-title">
        ACCUSÉ DE RÉCEPTION DE DOSSIER<br>
        DE DÉCLARATION {{ strtoupper($type_organisation) }}
    </div>

    <div class="content">
        <p><strong>Le Ministre de l'Intérieur, de la Sécurités et de la Décentralisation</strong></p>
        
        <p>
            Agissant conformément à ses attributions en matière de déclaration {{ $type_organisation == 'parti_politique' ? 'de partis politiques' : 'd\'organisations' }}, 
            atteste que <span class="highlight">{{ $civilite }} {{ $nom_prenom }}</span>, de nationalité {{ $nationalite }}, 
            domicilié à {{ $domicile }}, Téléphone : <span class="highlight">{{ $telephone }}</span>, 
            a déposé, aux services du Ministère de l'Intérieur, de la Sécurité et de la Décentralisation, 
            un dossier complet de déclaration {{ $type_organisation == 'parti_politique' ? 'du parti politique' : 'de l\'organisation' }} 
            dénommé{{ $sigle_organisation ? 'e' : '' }} <span class="highlight">{{ $nom_organisation }}{{ $sigle_organisation ? ' (' . $sigle_organisation . ')' : '' }}</span> 
            conformément aux dispositions de la {{ $loi_reference }}.
        </p>
        
        <p>
            En foi de quoi le présent accusé de réception lui est délivré pour servir et faire valoir ce que de droit.
        </p>
    </div>

    <div class="signature-section">
        <div class="signature-location">
            Fait à Libreville, le {{ $date_generation }}
        </div>
        
        <div>Le Ministre</div>
        
        <div class="minister-name">
            {{ $ministre_nom }}
        </div>
    </div>

    <div class="copy-section">
        <u>Copie</u><br>
        - J.O
    </div>
</body>
</html>