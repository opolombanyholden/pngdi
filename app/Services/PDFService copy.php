<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Dossier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PDFService
{
    /**
     * Générer l'accusé de réception PDF
     */
    public function generateAccuseReception(Dossier $dossier)
    {
        try {
            // Préparer les données pour le template
            $data = $this->prepareAccuseData($dossier);
            
            // Générer le PDF avec DomPDF
            $pdf = Pdf::loadView('admin.pdf.accuse-reception', $data);
            
            // Configuration du PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);
            
            return $pdf;
            
        } catch (\Exception $e) {
            Log::error('Erreur génération accusé PDF: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération de l\'accusé de réception: ' . $e->getMessage());
        }
    }
    
    /**
     * Générer le récépissé définitif PDF
     */
    public function generateRecepisseDefinitif(Dossier $dossier)
    {
        try {
            // Vérifier que le dossier est approuvé
            if ($dossier->statut !== 'approuve') {
                throw new \Exception('Le récépissé ne peut être généré que pour les dossiers approuvés');
            }
            
            // Préparer les données pour le template
            $data = $this->prepareRecepisseData($dossier);
            
            // Générer le PDF avec DomPDF
            $pdf = Pdf::loadView('admin.pdf.recepisse-definitif', $data);
            
            // Configuration du PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);
            
            return $pdf;
            
        } catch (\Exception $e) {
            Log::error('Erreur génération récépissé PDF: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération du récépissé: ' . $e->getMessage());
        }
    }
    
     /**
     * Récupérer les informations complètes du déclarant
     * Basé sur la structure DB réelle : tables fondateurs et organisations
     * 
     * @param mixed $fondateur
     * @param Organisation $organisation
     * @return array
     */
    private function getInfoDeclarant($fondateur, $organisation)
    {
        // Récupérer les informations du fondateur principal (déclarant)
        $nomComplet = trim(($fondateur->prenom ?? '') . ' ' . ($fondateur->nom ?? ''));
        
        // Adresse du déclarant (utiliser les champs de la table fondateurs)
        $adresseDeclarant = $this->formatAdresseDeclarant($fondateur, $organisation);
        
        // Téléphone prioritaire du déclarant
        $telephoneDeclarant = $this->getTelephonePrincipalDeclarant($fondateur, $organisation);
        
        return [
            // NIP selon structure DB (table fondateurs)
            'nip' => $fondateur->nip ?? 'Non renseigné',
            'nom_complet' => $nomComplet ?: 'Non renseigné',
            'telephone' => $telephoneDeclarant,
            'adresse' => $adresseDeclarant,
            'email' => $fondateur->email ?? $organisation->email ?? 'Non renseigné',
            
            // Informations spécifiques table fondateurs
            'numero_piece' => $fondateur->numero_piece ?? 'Non renseigné',
            'type_piece' => $fondateur->type_piece ?? 'CNI',
            'date_naissance' => $fondateur->date_naissance ? 
                Carbon::parse($fondateur->date_naissance)->format('d/m/Y') : 'Non renseignée',
            'lieu_naissance' => $fondateur->lieu_naissance ?? 'Non renseigné',
            'nationalite' => $fondateur->nationalite ?? 'Gabonaise',
            'fonction' => $fondateur->fonction ?? 'Président fondateur',
            'sexe' => $fondateur->sexe ?? 'M'
        ];
    }


    /**
     * Préparer les données pour l'accusé de réception
     */
    private function prepareAccuseData(Dossier $dossier)
    {
        $organisation = $dossier->organisation;
        $fondateur = $organisation->fondateurs->first(); // Premier fondateur

        $declarant = $dossier->user;
        
        if (!$declarant) {
            throw new \Exception('Aucun déclarant trouvé pour ce dossier. Impossible de générer l\'accusé.');
        }

        return [
            // Informations administratives
            'numero_dossier' => $dossier->numero_dossier,
            'date_generation' => Carbon::now()->locale('fr_FR')->isoFormat('DD MMMM YYYY'),
            'date_soumission' => $dossier->created_at->locale('fr_FR')->isoFormat('DD MMMM YYYY'),
            
            // Informations représentant = DÉCLARANT (pas le fondateur)
            'civilite' => ($declarant->sexe ?? $declarant->genre ?? 'M') === 'F' ? 'Madame' : 'Monsieur',
            'nom_prenom' => $declarant->name,
            'telephone' => $declarant->telephone ?? $declarant->phone ?? 'Non renseigné',
            'nationalite' => $declarant->nationalite ?? 'gabonaise',
            'domicile' => $declarant->adresse ?? $declarant->address ?? $organisation->adresse_siege ?? 'Libreville',
            
            // Informations organisation
            'nom_organisation' => $organisation->nom,
            'sigle_organisation' => $organisation->sigle ?? '',
            'type_organisation' => $this->getTypeOrganisationLabel($organisation->type),
            
            // Informations légales
            'loi_reference' => $this->getLoiReference($organisation->type),
            'ministre_nom' => 'Hermann IMMONGAULT',
            
            // Numéro administratif
            'numero_administratif' => $this->generateNumeroAdministratif($dossier),
        ];
    }

    /**
     * Mise à jour de la méthode prepareRecepisseProvisoireData pour utiliser les informations complètes
     */
    private function prepareRecepisseProvisoireDataComplete(Dossier $dossier, $fondateur)
    {
        $organisation = $dossier->organisation;
        
        // Récupérer les informations complètes du déclarant
        $infoDeclarant = $this->getInfoDeclarant($fondateur, $organisation);
        
        // Déterminer la civilité selon le genre
        $civilite = $this->getCiviliteProvisoire($fondateur);
        
        // Déterminer le type d'organisation et ses libellés
        $typeInfo = $this->getTypeOrganisationInfoProvisoire($organisation->type);
        
        // Fonction dirigeant selon le type d'organisation
        $fonctionDirigeant = $this->getFonctionDirigeantProvisoire($organisation->type, $fondateur->sexe ?? 'M');
        
        // Référence légale selon le type
        $referenceLegale = $this->getReferenceLegaleProvisoire($organisation->type);
        
        // Générer numéro de référence unique
        $numeroReference = $this->generateNumeroReferenceProvisoire($dossier);

        return [
            // Informations organisation
            'organisation' => $organisation,
            'numero_reference' => $numeroReference,
            
            // Informations fondateur/déclarant de base
            'civilite' => $civilite,
            'fondateur_nom' => trim($fondateur->prenom . ' ' . $fondateur->nom),
            'nationalite' => $fondateur->nationalite ?? 'gabonaise',
            'fonction_dirigeant' => $fonctionDirigeant,
            
            // Informations déclarant complètes
            'declarant_nip' => $infoDeclarant['nip'],
            'declarant_nom_complet' => $infoDeclarant['nom_complet'],
            'declarant_telephone' => $infoDeclarant['telephone'],
            'declarant_adresse' => $infoDeclarant['adresse'],
            'declarant_email' => $infoDeclarant['email'],
            'declarant_numero_piece' => $infoDeclarant['numero_piece'],
            'declarant_type_piece' => $infoDeclarant['type_piece'],
            'declarant_date_naissance' => $infoDeclarant['date_naissance'],
            'declarant_lieu_naissance' => $infoDeclarant['lieu_naissance'],
            'declarant_fonction' => $infoDeclarant['fonction'],
            
            // Informations organisation détaillées
            'type_organisation_libelle' => $typeInfo['libelle'],
            'domaine_activite' => $organisation->objet ?? 'Social',
            'adresse_siege' => $this->formatAdresseProvisoire($organisation),
            'telephone' => $this->formatTelephoneProvisoire($fondateur, $organisation),
            
            // Références légales
            'reference_legale' => $referenceLegale,
            
            // Informations administratives
            'date_emission' => $this->formatDateEmissionProvisoire(),
            'nom_ministre' => 'Hermann IMMONGAULT',
            
            // Métadonnées
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'Système'
        ];

    }


    /**
     * Préparer les données pour le récépissé définitif
     */
    private function prepareRecepisseData(Dossier $dossier)
    {
        $organisation = $dossier->organisation;
        
        return [
            // Informations administratives
            'numero_dossier' => $dossier->numero_dossier,
            'numero_recepisse' => $dossier->numero_recepisse,
            'date_generation' => Carbon::now()->locale('fr_FR')->isoFormat('DD MMMM YYYY'),
            'date_approbation' => $dossier->validated_at ? 
                $dossier->validated_at->locale('fr_FR')->isoFormat('DD MMMM YYYY') : 
                Carbon::now()->locale('fr_FR')->isoFormat('DD MMMM YYYY'),
            
            // Informations organisation
            'nom_organisation' => $organisation->nom,
            'sigle_organisation' => $organisation->sigle ?? '',
            'objet_organisation' => $organisation->objet ?? 'Non spécifié',
            'adresse_siege' => $organisation->adresse_siege ?? 'Non spécifiée',
            'telephone_organisation' => $organisation->telephone ?? 'Non renseigné',
            'type_organisation' => $this->getTypeOrganisationLabel($organisation->type),
            
            // Dirigeants (organes de direction)
            'dirigeants' => $this->prepareDirigeants($organisation),
            
            // Informations légales
            'loi_reference' => $this->getLoiReference($organisation->type),
            'ministre_nom' => 'Hermann IMMONGAULT',
            
            // Numéro administratif
            'numero_administratif' => $this->generateNumeroAdministratif($dossier),
            
            // Pièces jointes
            'pieces_annexees' => $this->getPiecesAnnexees($organisation->type),
            
            // Prescriptions légales
            'prescriptions' => $this->getPrescriptionsLegales($organisation->type),
        ];
    }
    
    /**
     * Obtenir le libellé du type d'organisation
     */
    private function getTypeOrganisationLabel($type)
    {
        $types = [
            'association' => 'Association',
            'ong' => 'Organisation Non Gouvernementale (ONG)',
            'parti_politique' => 'Parti Politique',
            'confession_religieuse' => 'Organisation Religieuse',
        ];
        
        return $types[$type] ?? 'Organisation';
    }
    
    /**
     * Obtenir la référence légale selon le type
     */
    private function getLoiReference($type)
    {
        $references = [
            'association' => 'loi n°35/62 du 10 décembre 1962',
            'ong' => 'loi n°35/62 du 10 décembre 1962',
            'parti_politique' => 'loi n°016/2025 du 27 juin 2025 relative aux partis politiques en République Gabonaise',
            'confession_religieuse' => 'loi n°35/62 du 10 décembre 1962',
        ];
        
        return $references[$type] ?? 'législation en vigueur';
    }
    
    /**
     * Générer le numéro administratif pour l'en-tête
     */
    private function generateNumeroAdministratif(Dossier $dossier)
    {
        $year = $dossier->created_at->year;
        $sequence = str_pad($dossier->id, 4, '0', STR_PAD_LEFT);
        
        return "{$sequence}/MISD/SG/DGELP/DPPALC/{$year}";
    }
    
    /**
     * Préparer les dirigeants pour le récépissé
     */
    private function prepareDirigeants($organisation)
    {
        $dirigeants = [];
        
        // Récupérer les fondateurs/dirigeants principaux
        foreach ($organisation->fondateurs->take(7) as $fondateur) {
            $poste = $this->determinerPoste($fondateur, $organisation->type);
            $dirigeants[] = [
                'poste' => $poste,
                'nom_prenom' => "{$fondateur->nom} {$fondateur->prenom}",
            ];
        }
        
        // Compléter avec des postes par défaut si nécessaire
        $postesDefaut = $this->getPostesDefaut($organisation->type);
        while (count($dirigeants) < 7 && count($dirigeants) < count($postesDefaut)) {
            $dirigeants[] = [
                'poste' => $postesDefaut[count($dirigeants)],
                'nom_prenom' => 'Non désigné',
            ];
        }
        
        return $dirigeants;
    }
    
    /**
     * Déterminer le poste d'un dirigeant
     */
    private function determinerPoste($fondateur, $typeOrganisation)
    {
        // Logique simplifiée - à adapter selon vos besoins
        static $index = 0;
        $postes = $this->getPostesDefaut($typeOrganisation);
        
        return $postes[$index++] ?? 'Membre du Bureau';
    }
    
    /**
     * Obtenir les postes par défaut selon le type
     */
    private function getPostesDefaut($type)
    {
        $postes = [
            'association' => [
                'Président(e)',
                'Vice-Président(e)',
                'Secrétaire Général(e)',
                'Secrétaire Général(e) Adjoint(e)',
                'Trésorier Général',
                'Trésorier Général Adjoint',
                'Commissaire aux Comptes',
            ],
            'parti_politique' => [
                'Président du Parti',
                'Secrétaire Général',
                'Trésorier Général',
                'Commissaire aux Comptes',
                'Responsable Communication',
                'Responsable Organisation',
                'Responsable Jeunesse',
            ],
        ];
        
        return $postes[$type] ?? $postes['association'];
    }
    
    /**
     * Obtenir les pièces annexées selon le type
     */
    private function getPiecesAnnexees($type)
    {
        return [
            'Statuts',
            'Procès-verbal de l\'assemblée constitutive',
            'Liste des membres du comité directeur',
            'Demande adressée au Ministre de l\'Intérieur',
            'Reçu de 10.000 frs CFA délivré par la Direction du Journal Officiel',
        ];
    }
    
    /**
     * Obtenir les prescriptions légales
     */
    private function getPrescriptionsLegales($type)
    {
        return [
            'Toutes modifications apportées aux statuts de l\'organisation et tous les changements survenus dans son administration ou sa direction devront être déclarés dans un délai d\'un mois.',
            'Un registre spécial doit être tenu au siège de l\'organisation et présenté sur demande aux autorités compétentes.',
            'L\'organisation doit respecter strictement les dispositions légales en vigueur sous peine de dissolution.',
        ];
    }


   /**
     * ========================================
     * NOUVELLE MÉTHODE : RÉCÉPISSÉ PROVISOIRE
     * ========================================
     */
    
    /**
     * Générer le récépissé provisoire PDF
     * 
     * @param Dossier $dossier
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateRecepisseProvisoire(Dossier $dossier)
    {
        try {
            // Valider les données requises
            if (!$dossier->organisation) {
                throw new \Exception('Organisation manquante pour le dossier');
            }

            // Récupérer le premier fondateur
            $fondateur = $dossier->organisation->fondateurs()->first();
            if (!$fondateur) {
                throw new \Exception('Aucun fondateur trouvé pour l\'organisation');
            }

            // Préparer les données pour le template
            $data = $this->prepareRecepisseProvisoireDataComplete($dossier, $fondateur);

            // Générer le PDF avec le template
            $pdf = Pdf::loadView('admin.pdf.recepisse-provisoire', $data);
            
            // Configuration PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

            return $pdf;

        } catch (\Exception $e) {
            Log::error('Erreur génération récépissé provisoire: ' . $e->getMessage(), [
                'dossier_id' => $dossier->id ?? null,
                'organisation_id' => $dossier->organisation->id ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Préparer les données pour le récépissé provisoire
     * 
     * @param Dossier $dossier
     * @param mixed $fondateur
     * @return array
     */
    private function prepareRecepisseProvisoireData(Dossier $dossier, $fondateur)
    {
        $organisation = $dossier->organisation;
        
        // Déterminer la civilité selon le genre
        $civilite = $this->getCiviliteProvisoire($fondateur);
        
        // Déterminer le type d'organisation et ses libellés
        $typeInfo = $this->getTypeOrganisationInfoProvisoire($organisation->type);
        
        // Fonction dirigeant selon le type d'organisation
        $fonctionDirigeant = $this->getFonctionDirigeantProvisoire($organisation->type, $fondateur->sexe ?? 'M');
        
        // Référence légale selon le type
        $referenceLegale = $this->getReferenceLegaleProvisoire($organisation->type);
        
        // Générer numéro de référence unique
        $numeroReference = $this->generateNumeroReferenceProvisoire($dossier);

        return [
            // Informations organisation
            'organisation' => $organisation,
            'numero_reference' => $numeroReference,
            
            // Informations fondateur
            'civilite' => $civilite,
            'fondateur_nom' => trim($fondateur->prenom . ' ' . $fondateur->nom),
            'nationalite' => $fondateur->nationalite ?? 'gabonaise',
            'fonction_dirigeant' => $fonctionDirigeant,
            
            // Informations organisation détaillées
            'type_organisation_libelle' => $typeInfo['libelle'],
            'domaine_activite' => $organisation->objet ?? $organisation->domaine_activite ?? 'Social',
            'adresse_siege' => $this->formatAdresseProvisoire($organisation),
            'telephone' => $this->formatTelephoneProvisoire($fondateur, $organisation),
            
            // Références légales
            'reference_legale' => $referenceLegale,
            
            // Informations administratives
            'date_emission' => $this->formatDateEmissionProvisoire(),
            'nom_ministre' => 'Hermann IMMONGAULT',
            
            // Métadonnées
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'Système'
        ];
    }

    /**
     * Déterminer la civilité selon le genre
     */
    private function getCiviliteProvisoire($fondateur)
    {
        $genre = strtoupper($fondateur->sexe ?? 'M');
        
        switch ($genre) {
            case 'F':
            case 'FEMME':
            case 'FEMININE':
                return 'Madame';
            case 'M':
            case 'HOMME':
            case 'MASCULIN':
            default:
                return 'Monsieur';
        }
    }

    /**
     * Obtenir les informations du type d'organisation
     */
    private function getTypeOrganisationInfoProvisoire($type)
    {
        $types = [
            'association' => [
                'libelle' => 'Association',
                'description' => 'Association à but non lucratif'
            ],
            'ong' => [
                'libelle' => 'Organisation Non Gouvernementale',
                'description' => 'ONG à but non lucratif'
            ],
            'parti_politique' => [
                'libelle' => 'Parti Politique',
                'description' => 'Formation politique'
            ],
            'confession_religieuse' => [
                'libelle' => 'Confession Religieuse',
                'description' => 'Organisation religieuse'
            ]
        ];

        return $types[$type] ?? $types['association'];
    }

    /**
     * Déterminer la fonction dirigeant selon le type et le genre
     */
    private function getFonctionDirigeantProvisoire($type, $genre = 'M')
    {
        $estFeminin = in_array(strtoupper($genre), ['F', 'FEMME', 'FEMININE']);
        
        $fonctions = [
            'association' => $estFeminin ? 'Présidente' : 'Président',
            'ong' => $estFeminin ? 'Présidente' : 'Président',
            'parti_politique' => $estFeminin ? 'Présidente' : 'Président',
            'confession_religieuse' => $estFeminin ? 'Responsable Spirituelle' : 'Responsable Spirituel'
        ];

        return $fonctions[$type] ?? ($estFeminin ? 'Présidente' : 'Président');
    }

    /**
     * Obtenir la référence légale selon le type d'organisation
     */
    private function getReferenceLegaleProvisoire($type)
    {
        $references = [
            'association' => 'la loi n°35/62 du 10 Décembre 1962 relative aux associations',
            'ong' => 'la loi n°35/62 du 10 Décembre 1962 relative aux associations',
            'parti_politique' => 'la loi n°016/2025 du 27 juin 2025 relative aux partis politiques',
            'confession_religieuse' => 'la loi n°35/62 du 10 Décembre 1962 relative aux associations'
        ];

        return $references[$type] ?? $references['association'];
    }

    /**
     * Formater l'adresse du siège
     */
   private function formatAdresseProvisoire($organisation)
    {
        $adresse = [];
        
        // Adresse principale du siège
        if ($organisation->siege_social) {
            $adresse[] = $organisation->siege_social;
        }
        
        // Détails géographiques selon structure DB
        if ($organisation->quartier) {
            $adresse[] = 'Quartier ' . $organisation->quartier;
        } elseif ($organisation->village) {
            $adresse[] = 'Village ' . $organisation->village;
        }
        
        if ($organisation->lieu_dit) {
            $adresse[] = $organisation->lieu_dit;
        }
        
        if ($organisation->ville_commune) {
            $adresse[] = $organisation->ville_commune;
        }
        
        if ($organisation->arrondissement) {
            $adresse[] = $organisation->arrondissement . 'e arrondissement';
        }
        
        if ($organisation->prefecture) {
            $adresse[] = $organisation->prefecture;
        }
        
        if ($organisation->province) {
            $adresse[] = 'Province ' . $organisation->province;
        }
        
        // Par défaut Libreville si pas d'adresse
        return !empty($adresse) ? implode(', ', $adresse) : 'Libreville, Gabon';
    }

    /**
     * Formater les numéros de téléphone
     */
    private function formatTelephoneProvisoire($fondateur, $organisation)
    {
        $telephones = [];
        
        // Téléphone du fondateur en priorité
        if ($fondateur->telephone) {
            $telephones[] = $fondateur->telephone;
        }
        
        // Téléphone secondaire du fondateur
        if ($fondateur->telephone_secondaire && $fondateur->telephone_secondaire !== $fondateur->telephone) {
            $telephones[] = $fondateur->telephone_secondaire;
        }
        
        // Téléphone de l'organisation en complément
        if ($organisation->telephone && !in_array($organisation->telephone, $telephones)) {
            $telephones[] = $organisation->telephone;
        }
        
        // Téléphone secondaire de l'organisation
        if ($organisation->telephone_secondaire && !in_array($organisation->telephone_secondaire, $telephones)) {
            $telephones[] = $organisation->telephone_secondaire;
        }

        return !empty($telephones) ? implode(' / ', $telephones) : 'Non renseigné';
    }

    /**
     * Générer un numéro de référence unique
     */
    private function generateNumeroReferenceProvisoire($dossier)
    {
        // Format : {sequence}/MISD/SG/DGELP/DPPALC/{année}
        $sequence = str_pad($dossier->id, 4, '0', STR_PAD_LEFT);
        $annee = date('Y');
        
        return "{$sequence}/MISD/SG/DGELP/DPPALC/{$annee}";
    }

    /**
     * Formater la date d'émission
     */
    private function formatDateEmissionProvisoire()
    {
        // Format français : "25 juillet 2025"
        $mois = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];
        
        $date = Carbon::now();
        
        return $date->day . ' ' . $mois[$date->month] . ' ' . $date->year;
    }
    
    /**
     * Formater l'adresse du déclarant selon la structure DB
     * Utilise les champs géographiques de la table fondateurs
     * 
     * @param mixed $fondateur
     * @param Organisation $organisation
     * @return string
     */
    private function formatAdresseDeclarant($fondateur, $organisation)
    {
        $adresse = [];
        
        // Priorité à l'adresse complète du fondateur
        if ($fondateur->adresse_complete) {
            $adresse[] = $fondateur->adresse_complete;
        }
        
        // Construire l'adresse avec les champs géographiques du fondateur
        $adresseGeo = [];
        
        if ($fondateur->quartier) {
            $adresseGeo[] = 'Quartier ' . $fondateur->quartier;
        } elseif ($fondateur->village) {
            $adresseGeo[] = 'Village ' . $fondateur->village;
        }
        
        if ($fondateur->lieu_dit) {
            $adresseGeo[] = $fondateur->lieu_dit;
        }
        
        if ($fondateur->ville_commune) {
            $adresseGeo[] = $fondateur->ville_commune;
        }
        
        if ($fondateur->arrondissement) {
            $adresseGeo[] = $fondateur->arrondissement . 'e arrondissement';
        }
        
        if ($fondateur->prefecture) {
            $adresseGeo[] = $fondateur->prefecture;
        }
        
        if ($fondateur->province) {
            $adresseGeo[] = 'Province ' . $fondateur->province;
        }
        
        // Ajouter l'adresse géographique construite
        if (!empty($adresseGeo)) {
            $adresse[] = implode(', ', $adresseGeo);
        }
        
        // Si pas d'adresse du fondateur, utiliser l'adresse du siège de l'organisation
        if (empty($adresse) && $organisation) {
            $adresseOrg = [];
            
            if ($organisation->siege_social) {
                $adresseOrg[] = $organisation->siege_social;
            }
            
            if ($organisation->quartier) {
                $adresseOrg[] = 'Quartier ' . $organisation->quartier;
            } elseif ($organisation->village) {
                $adresseOrg[] = 'Village ' . $organisation->village;
            }
            
            if ($organisation->ville_commune) {
                $adresseOrg[] = $organisation->ville_commune;
            }
            
            if ($organisation->prefecture) {
                $adresseOrg[] = $organisation->prefecture;
            }
            
            if ($organisation->province) {
                $adresseOrg[] = 'Province ' . $organisation->province;
            }
            
            if (!empty($adresseOrg)) {
                $adresse = $adresseOrg;
            }
        }
        
        // Par défaut Libreville
        return !empty($adresse) ? implode(', ', $adresse) : 'Libreville, Gabon';
    }

    /**
     * Récupérer le téléphone principal du déclarant selon structure DB
     * 
     * @param mixed $fondateur
     * @param Organisation $organisation
     * @return string
     */
    private function getTelephonePrincipalDeclarant($fondateur, $organisation)
    {
        // Ordre de priorité basé sur la structure DB
        $telephones = array_filter([
            $fondateur->telephone ?? null,
            $fondateur->telephone_secondaire ?? null,
            $organisation->telephone ?? null,
            $organisation->telephone_secondaire ?? null
        ]);
        
        return !empty($telephones) ? $telephones[0] : 'Non renseigné';
    }


}