<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récépissé Définitif - {{ $nom_organisation }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
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
            font-size: 10pt;
            line-height: 1.2;
        }
        
        .ministry-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .department-divider {
            margin: 2px 0;
            border-bottom: 1px solid #000;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .reference-number {
            text-align: right;
            margin: 15px 0;
            font-size: 10pt;
        }
        
        .document-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
            text-transform: uppercase;
        }
        
        .content {
            text-align: justify;
            margin: 20px 0;
            line-height: 1.5;
        }
        
        .content p {
            margin-bottom: 12px;
        }
        
        .organization-details {
            margin: 20px 0;
        }
        
        .detail-line {
            margin-bottom: 8px;
        }
        
        .dirigeants-section {
            margin: 15px 0;
        }
        
        .dirigeant-line {
            margin-bottom: 5px;
        }
        
        .pieces-section {
            margin: 20px 0;
        }
        
        .pieces-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        
        .pieces-list {
            margin-left: 20px;
        }
        
        .prescriptions-section {
            margin: 25px 0;
        }
        
        .prescription-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        
        .prescription-content {
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 15px;
        }
        
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-location {
            margin-bottom: 40px;
        }
        
        .minister-name {
            font-weight: bold;
            margin-top: 60px;
        }
        
        .ampliations-section {
            margin-top: 30px;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .ampliations-list {
            margin-left: 20px;
            font-weight: normal;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .highlight {
            font-weight: bold;
        }
        
        ul {
            margin: 0;
            padding-left: 20px;
        }
        
        li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="logo-section">
            <!-- Logo du Gabon -->
            <div style="width: 100px; height: 70px; border: 1px solid #ccc; text-align: center; line-height: 70px; font-size: 8pt;">
                LOGO<br>GABON
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
        RÉCÉPISSÉ DÉFINITIF DE<br>
        DÉCLARATION {{ strtoupper($type_organisation) }}
    </div>

    <div class="content">
        <p><strong>Le Ministre de l'Intérieur, de la Sécurité, et de la Décentralisation</strong></p>
        
        <p>
            Agissant conformément à ses attributions en matière {{ $type_organisation == 'parti_politique' ? 'de parti politique' : 'd\'association' }}, 
            donne aux personnes ci-après désignées, récépissé définitif de déclaration pour 
            {{ $type_organisation == 'parti_politique' ? 'le parti politique' : 'l\'organisation' }} 
            définie comme suit, régie par la <span class="highlight">{{ $loi_reference }}</span>.
        </p>
    </div>

    <div class="organization-details">
        <div class="detail-line">
            <span class="highlight"><u>Dénomination {{ $type_organisation == 'parti_politique' ? 'du Parti' : 'de l\'Organisation' }}</u> :</span> 
            <span class="highlight">{{ $nom_organisation }}{{ $sigle_organisation ? ' (' . $sigle_organisation . ')' : '' }}</span>
        </div>
        
        <div class="detail-line">
            <span class="highlight"><u>Objet :</u></span><br>
            {{ $objet_organisation }}
        </div>
        
        <div class="detail-line">
            <span class="highlight"><u>Siège Social</u> :</span> {{ $adresse_siege }} ; {{ $telephone_organisation }}
        </div>
    </div>

    <div class="dirigeants-section">
        @foreach($dirigeants as $dirigeant)
            <div class="dirigeant-line">
                <span class="highlight"><u>{{ $dirigeant['poste'] }} :</u></span> {{ $dirigeant['nom_prenom'] }} ;
            </div>
        @endforeach
    </div>

    <div class="pieces-section">
        <div class="pieces-title">Pièces annexées à la déclaration et autres prescriptions :</div>
        
        <div style="margin-bottom: 15px;">
            <span class="highlight"><u>1. Pièces annexées :</u></span>
            <div class="pieces-list">
                @foreach($pieces_annexees as $piece)
                    - {{ $piece }} ;<br>
                @endforeach
            </div>
        </div>
    </div>

    <div class="prescriptions-section">
        <div class="prescription-title">2 - Prescriptions :</div>
        
        <div class="prescription-content">
            Toutes modifications apportées aux statuts de {{ $type_organisation == 'parti_politique' ? 'l\'organisation politique' : 'l\'organisation' }} 
            et tous les changements survenus dans son administration ou sa direction devront être déclarés dans un délai d'un mois 
            et mentionnés en outre dans le registre spécial tenu aussi bien au secrétariat de la préfecture qu'au siège de 
            {{ $type_organisation == 'parti_politique' ? 'l\'organisation politique' : 'l\'organisation' }}, 
            conformément aux dispositions de l'article 11 de la loi citée ci-dessus. Ce registre devra être présenté sur leur demande 
            aux autorités administratives et judiciaires.
        </div>
        
        <div class="prescription-content">
            Sous peine de nullité de {{ $type_organisation == 'parti_politique' ? 'l\'organisation politique' : 'l\'organisation' }} 
            dont la dissolution peut être à tout moment prononcée par décret pris par l'autorité compétente conformément aux dispositions 
            de l'ordonnance numéro 17/PR du 17 avril 1965, les membres de ladite {{ $type_organisation == 'parti_politique' ? 'organisation politique' : 'organisation' }} 
            doivent strictement observer les dispositions des articles 4 et 5 de cette même ordonnance qui stipule que :
        </div>
        
        <div class="prescription-content">
            <span class="highlight"><u>Premièrement :</u></span> « Toute {{ $type_organisation == 'parti_politique' ? 'organisation politique' : 'association' }} 
            fondée sur une cause en vue d'un objet illicite contrairement aux lois, aux bonnes mœurs ou qui aurait pour but de porter atteinte 
            à l'intégrité du territoire national et à la forme républicaine du Gouvernement, ou qui serait de nature à compromettre la sécurité publique, 
            à provoquer la haine entre groupes ethniques, à occasionner des troubles publics, à jeter le discrédit sur les institutions politiques 
            ou leur fonctionnement, à inciter les citoyens à enfreindre les lois et à nuire à l'intérêt général est nulle et de nul effet ».
        </div>
        
        <div class="prescription-content">
            <span class="highlight"><u>Deuxièmement :</u></span> « Sous peine de nullité de {{ $type_organisation == 'parti_politique' ? 'l\'organisation politique' : 'l\'association' }}, 
            les membres chargés de son administration ou de sa direction doivent être majeurs, jouir de leurs droits civiques et ne pas avoir encouru 
            de condamnation à une peine criminelle ou correctionnelle, à l'exception toutefois des condamnations pour délit d'imprudence hors le cas 
            de délit de fuite ».
        </div>
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

    <div class="ampliations-section">
        <u>AMPLIATIONS :</u>
        <div class="ampliations-list">
            <ul>
                <li>MIS</li>
                <li>SG</li>
                <li>DGAT</li>
                <li>MINISTÈRE CONCERNÉ</li>
                <li>J.O</li>
            </ul>
        </div>
    </div>
</body>
</html>