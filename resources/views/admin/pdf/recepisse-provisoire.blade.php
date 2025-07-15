<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Récépissé Provisoire - {{ $organisation->nom }}</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            margin: 2cm;
        }
        h1 {
            color: #009e3f;
            border: 2px solid #009e3f;
            padding: 5px 15px;
            text-align: center;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            font-size: 10pt;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            margin-top: 30px;
        }
    </style>
</head>
<body style="margin:0px;">

    <!-- Bande drapeau (30%) -->
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:5px;">
        <tr>
            <td style="background-color: #009e3f; height: 5px;"></td>
        </tr>
        <tr>
            <td style="background-color: #ffcd00; height: 5px;"></td>
        </tr>
        <tr>
            <td style="background-color: #003f7f; height: 5px;"></td>
        </tr>
    </table>

    <!-- En-tête -->
    <table width="100%" style="margin-bottom: 15px;">
        <tr>
            <td width="200" align="center" style="color: #333333; font-weight: bold; font-size:14px;">
                <div style="font-size:17px;">MINISTÈRE DE L’INTÉRIEUR,
                DE LA SÉCURITÉ <br> ET DE LA DÉCENTRALISATION</div>
                <div>
            <div>_____________________</div>
            <div>SECRETARIAT GENERAL</div>
            <div>_____________________</div>
            <div>DIRECTION GENERALE DES ELECTIONS 			
         <br>ET DES LIBERTES PUBLIQUES</div>
            <div>_____________________</div>
            <div>DIRECTION DES PARTIS POLITIQUES<br>
ASSOCIATIONS ET LIBERTE DE CULTE</div>
            <div>_____________________</div><br/>
            <div style="font-size:14px;">N° {{ $numero_reference }}</div>

                </div>
            </td>
            <td  width="70"></td>
            <td align="right" style="color: #003f7f; font-weight: bold; font-size:19px;">
                <div style="text-align:center" align="top">
                RÉPUBLIQUE GABONAISE<br>
                <div style="font-size:12px">UNION • TRAVAIL • JUSTICE</div>
                </div><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>    
            </td>
        </tr>
    </table>

    

    <!-- Titre principal -->
    <div style="text-align: center; font-size:20px; text-transform: uppercase;">
        <h1>RECEPISSE PROVISOIRE<br>
        DE {{ $type_organisation_libelle }}</h1>
    </div>
    <br/>

    <!-- Contenu -->
    <p style="text-align:justify; line-height:40px;">
               
            Nous soussignés, Ministre de l'Intérieur, de la Sécurité et de la Décentralisation, 
            attestons que <span class="emphasis">{{ $civilite }} {{ $fondateur_nom }},</span> 
            de nationalité {{ $nationalite }}, {{ $fonction_dirigeant }} de 
            l'{{ $type_organisation_libelle }} à but non lucratif, œuvrant dans le domaine du 
            <span class="emphasis">{{ $domaine_activite }}</span>, dénommée :
        

        <div class="organisation-nom">
            « {{ strtoupper($organisation->nom) }} »
        </div>

        <div>
            Dont le siège social est fixé à {{ $adresse_siege }}, téléphone : 
            <span class="emphasis">{{ $telephone }}</span>, a déposé à nos services un dossier complet 
            visant à obtenir un récépissé définitif de déclaration d'association, conformément aux 
            dispositions de {{ $reference_legale }}.
    </div>

        <p>
            En foi de quoi, le présent récépissé est délivré à l'intéressé{{ $civilite === 'Madame' ? 'e' : '' }} pour servir et valoir ce que de droit.
        </p>
    </p>

    <p style="margin-top:30px; text-align:right; margin-top:5px;">
        Fait à Libreville, le {{ $date_emission }}
    </p>

    <p style="text-align: right; margin-top: 50px;">
        Le Ministre<br><br><br><br><br>
        <strong>{{ $nom_ministre }}</strong>
    </p>

    <p style="margin-top: 10px;">
        Copie : J.O
    </p>

    <!-- Pied de page -->
    <div class="footer">
        Ministère de l’Intérieur, de la Sécurité et de la Décentralisation –<br>
        119, RUE Jean Baptiste NDENDE, (Avenue de Cointet BP 2110 Libreville, Gabon)
    </div>

</body>
</html>
