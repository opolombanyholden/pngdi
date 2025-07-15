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
     * Générer le récépissé provisoire PDF
     */
    public function generateRecepisseProvisoire(Dossier $dossier)
    {
        try {
            // Valider les données requises
            if (!$dossier->organisation) {
                throw new \Exception('Organisation manquante pour le dossier');
            }

            // ✅ RÉCÉPISSÉ PROVISOIRE : Pas besoin de fondateur
            // Il utilise uniquement les données du déclarant stockées en JSON
            
            // Vérifier que les données du déclarant existent
            if (empty($dossier->donnees_supplementaires)) {
                throw new \Exception('Données du déclarant manquantes dans le dossier');
            }

            // Préparer les données pour le template (sans paramètre fondateur)
            $data = $this->prepareRecepisseProvisoireData($dossier);

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
            \Log::error('Erreur génération récépissé provisoire: ' . $e->getMessage(), [
                'dossier_id' => $dossier->id ?? null,
                'organisation_id' => $dossier->organisation->id ?? null
            ]);
            throw $e;
        }
    }

    /**
     * ===================================================================
     * NOUVELLES MÉTHODES - RÉCUPÉRATION DÉCLARANT DEPUIS JSON
     * ===================================================================
     */

    /**
     * Récupérer les informations du déclarant depuis le JSON donnees_supplementaires
     * 
     * @param Dossier $dossier
     * @return array
     */
    private function getDeclarantFromJson(Dossier $dossier)
    {
        try {
            // Décoder le JSON donnees_supplementaires
            $donneesSupplementaires = json_decode($dossier->donnees_supplementaires ?? '{}', true);
            
            // Récupérer les données du demandeur/déclarant
            $demandeur = $donneesSupplementaires['demandeur'] ?? [];
            
            // Structurer les données avec valeurs par défaut
            return [
                'nip' => $demandeur['nip'] ?? 'Non renseigné',
                'nom' => $demandeur['nom'] ?? 'Non renseigné',
                'prenom' => $demandeur['prenom'] ?? 'Non renseigné',
                'nom_complet' => trim(($demandeur['prenom'] ?? '') . ' ' . ($demandeur['nom'] ?? '')),
                'email' => $demandeur['email'] ?? 'Non renseigné',
                'telephone' => $demandeur['telephone'] ?? 'Non renseigné',
                'role' => $demandeur['role'] ?? 'Président',
                'civilite' => $demandeur['civilite'] ?? 'M',
                'date_naissance' => $demandeur['date_naissance'] ?? null,
                'nationalite' => $demandeur['nationalite'] ?? 'Gabonaise',
                'adresse' => $demandeur['adresse'] ?? 'Non renseignée',
                'profession' => $demandeur['profession'] ?? 'Non renseignée'
            ];
            
        } catch (\Exception $e) {
            Log::warning('Erreur récupération déclarant JSON: ' . $e->getMessage(), [
                'dossier_id' => $dossier->id,
                'donnees_supplementaires' => $dossier->donnees_supplementaires
            ]);
            
            // Retourner des valeurs par défaut en cas d'erreur
            return [
                'nip' => 'Non renseigné',
                'nom' => 'Non renseigné',
                'prenom' => 'Non renseigné',
                'nom_complet' => 'Non renseigné',
                'email' => 'Non renseigné',
                'telephone' => 'Non renseigné',
                'role' => 'Président',
                'civilite' => 'M',
                'date_naissance' => null,
                'nationalite' => 'Gabonaise',
                'adresse' => 'Non renseignée',
                'profession' => 'Non renseignée'
            ];
        }
    }

    /**
     * Déterminer la civilité complète selon le genre
     * 
     * @param string $civilite
     * @return string
     */
    private function getCiviliteComplete($civilite)
    {
        switch (strtoupper($civilite)) {
            case 'F':
            case 'FEMME':
            case 'MME':
            case 'MADAME':
                return 'Madame';
            case 'MLLE':
            case 'MADEMOISELLE':
                return 'Mademoiselle';
            case 'M':
            case 'HOMME':
            case 'MONSIEUR':
            default:
                return 'Monsieur';
        }
    }

    /**
     * Formater la date de naissance pour affichage
     * 
     * @param string|null $dateNaissance
     * @return string
     */
    private function formatDateNaissance($dateNaissance)
    {
        if (!$dateNaissance) {
            return 'Non renseignée';
        }
        
        try {
            return Carbon::parse($dateNaissance)->format('d/m/Y');
        } catch (\Exception $e) {
            return 'Non renseignée';
        }
    }

    /**
     * ===================================================================
     * MÉTHODES DE PRÉPARATION DES DONNÉES - MISES À JOUR
     * ===================================================================
     */

    /**
     * Préparer les données pour l'accusé de réception
     */
    private function prepareAccuseData(Dossier $dossier)
    {
        $organisation = $dossier->organisation;
        
        // Récupérer les informations du déclarant depuis le JSON
        $declarant = $this->getDeclarantFromJson($dossier);
        
        if (!$organisation) {
            throw new \Exception('Aucune organisation trouvée pour ce dossier. Impossible de générer l\'accusé.');
        }

        return [
            // Informations administratives
            'numero_dossier' => $dossier->numero_dossier,
            'date_generation' => Carbon::now()->locale('fr_FR')->isoFormat('DD MMMM YYYY'),
            'date_soumission' => $dossier->created_at->locale('fr_FR')->isoFormat('DD MMMM YYYY'),
            
            // Informations déclarant (depuis JSON)
            'civilite' => $this->getCiviliteComplete($declarant['civilite']),
            'nom_prenom' => $declarant['nom'],
            'telephone' => $declarant['telephone'],
            'nationalite' => $declarant['nationalite'],
            'domicile' => $declarant['adresse'],
            'email' => $declarant['email'],
            'nip' => $declarant['nip'],
            'profession' => $declarant['profession'],
            'date_naissance' => $this->formatDateNaissance($declarant['date_naissance']),
            
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
     * Préparer les données pour le récépissé provisoire
     */
    private function prepareRecepisseProvisoireData(Dossier $dossier)
    {
        $organisation = $dossier->organisation;
        
        // Récupérer les informations du déclarant depuis le JSON
        $declarant = $this->getDeclarantFromJson($dossier);
        
        // Déterminer la civilité selon le genre
        $civilite = $this->getCiviliteComplete($declarant['civilite']);
        
        // Déterminer le type d'organisation et ses libellés
        $typeInfo = $this->getTypeOrganisationInfoProvisoire($organisation->type);
        
        // Fonction dirigeant selon le type d'organisation
        $fonctionDirigeant = $this->getFonctionDirigeantProvisoire($organisation->type, $declarant['civilite']);
        
        // Référence légale selon le type
        $referenceLegale = $this->getReferenceLegaleProvisoire($organisation->type);
        
        // Générer numéro de référence unique
        $numeroReference = $this->generateNumeroReferenceProvisoire($dossier);

        return [
            // Informations organisation
            'organisation' => $organisation,
            'numero_reference' => $numeroReference,
            
            // Informations déclarant (depuis JSON - PAS de fondateur)
            'civilite' => $civilite,
            'fondateur_nom' => $declarant['nom_complet'],  // ✅ Variable conservée pour compatibilité template
            'nationalite' => $declarant['nationalite'],
            'fonction_dirigeant' => $fonctionDirigeant,
            
            // Informations déclarant complètes
            'declarant_nip' => $declarant['nip'],
            'declarant_nom_complet' => $declarant['nom'],
            'declarant_telephone' => $declarant['telephone'],
            'declarant_adresse' => $declarant['adresse'],
            'declarant_email' => $declarant['email'],
            'declarant_profession' => $declarant['profession'],
            'declarant_date_naissance' => $this->formatDateNaissance($declarant['date_naissance']),
            'declarant_fonction' => $declarant['role'],
            
            // Informations organisation détaillées
            'type_organisation_libelle' => $typeInfo['libelle'],
            'domaine_activite' => $organisation->objet ?? $organisation->domaine_activite ?? 'Social',
            'adresse_siege' => $this->formatAdresseOrganisation($organisation),
            'telephone' => $this->formatTelephoneOrganisation($organisation),
            
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
            'adresse_siege' => $this->formatAdresseOrganisation($organisation),
            'telephone_organisation' => $this->formatTelephoneOrganisation($organisation),
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
     * ===================================================================
     * MÉTHODES UTILITAIRES - FORMATAGE
     * ===================================================================
     */

    /**
     * Formater l'adresse de l'organisation
     */
    private function formatAdresseOrganisation($organisation)
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
            $adresse[] = $organisation->arrondissement . 'arrondissement';
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
     * Formater les numéros de téléphone de l'organisation
     */
    private function formatTelephoneOrganisation($organisation)
    {
        $telephones = [];
        
        // Téléphone principal de l'organisation
        if ($organisation->telephone) {
            $telephones[] = $organisation->telephone;
        }
        
        // Téléphone secondaire de l'organisation
        if ($organisation->telephone_secondaire && $organisation->telephone_secondaire !== $organisation->telephone) {
            $telephones[] = $organisation->telephone_secondaire;
        }

        return !empty($telephones) ? implode(' / ', $telephones) : 'Non renseigné';
    }

    /**
     * ===================================================================
     * MÉTHODES UTILITAIRES - TYPES ET RÉFÉRENCES LÉGALES
     * ===================================================================
     */
    
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
     * Obtenir les informations du type d'organisation pour récépissé provisoire
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
    private function getFonctionDirigeantProvisoire($type, $civilite = 'M')
    {
        $estFeminin = in_array(strtoupper($civilite), ['F', 'FEMME', 'MME', 'MADAME']);
        
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
     * ===================================================================
     * MÉTHODES UTILITAIRES - NUMÉROTATION ET ADMINISTRATION
     * ===================================================================
     */
    
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
     * Générer un numéro de référence unique pour récépissé provisoire
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
     * ===================================================================
     * MÉTHODES POUR RÉCÉPISSÉ DÉFINITIF - DIRIGEANTS
     * ===================================================================
     */
    
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
}