<?php

namespace App\Services;

use App\Models\AdherentAnomalie;
use App\Models\AdherentImport;
use App\Models\Adherent;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Writer;
use Barryvdh\DomPDF\Facade\Pdf;

class AnomalieService
{
    /**
     * Créer une anomalie pour un adhérent
     */
    public function creerAnomalie(array $data)
    {
        try {
            // Validation des données requises
            $this->validerDonneesAnomalie($data);

            // Créer l'anomalie
            $anomalie = AdherentAnomalie::create([
                'adherent_id' => $data['adherent_id'],
                'organisation_id' => $data['organisation_id'],
                'ligne_import' => $data['ligne_import'] ?? null,
                'type_anomalie' => $data['type_anomalie'],
                'champ_concerne' => $data['champ_concerne'],
                'message_anomalie' => $data['message_anomalie'],
                'valeur_erronee' => $data['valeur_erronee'],
                'statut' => 'en_attente',
                'detectee_le' => now(),
                'description' => $data['description'] ?? $data['message_anomalie'],
                'valeur_incorrecte' => $data['valeur_incorrecte'] ?? $data['valeur_erronee'],
                'priorite' => $this->determinerPriorite($data['type_anomalie']),
                'impact_metier' => $data['impact_metier'] ?? $this->determinerImpactMetier($data['type_anomalie'])
            ]);

            Log::info('Anomalie créée', [
                'anomalie_id' => $anomalie->id,
                'type' => $anomalie->type_anomalie,
                'adherent_id' => $anomalie->adherent_id,
                'organisation_id' => $anomalie->organisation_id
            ]);

            return $anomalie;

        } catch (\Exception $e) {
            Log::error('Erreur création anomalie', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Créer plusieurs anomalies en lot
     */
    public function creerAnomaliesEnLot(array $anomalies)
    {
        try {
            DB::beginTransaction();

            $anomaliesCreees = [];
            foreach ($anomalies as $anomalieData) {
                $anomaliesCreees[] = $this->creerAnomalie($anomalieData);
            }

            DB::commit();

            Log::info('Anomalies créées en lot', [
                'count' => count($anomaliesCreees),
                'ids' => array_column($anomaliesCreees, 'id')
            ]);

            return $anomaliesCreees;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur création anomalies en lot', [
                'error' => $e->getMessage(),
                'count' => count($anomalies)
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les anomalies d'une organisation avec filtres
     */
    public function getAnomaliesOrganisation($organisationId, array $filtres = [])
    {
        $query = AdherentAnomalie::byOrganisation($organisationId)
            ->with(['adherent', 'correcteur']);

        // Appliquer les filtres
        if (isset($filtres['type_anomalie'])) {
            $query->byType($filtres['type_anomalie']);
        }

        if (isset($filtres['statut'])) {
            $query->byStatut($filtres['statut']);
        }

        if (isset($filtres['champ_concerne'])) {
            $query->where('champ_concerne', $filtres['champ_concerne']);
        }

        if (isset($filtres['date_debut'])) {
            $query->where('detectee_le', '>=', $filtres['date_debut']);
        }

        if (isset($filtres['date_fin'])) {
            $query->where('detectee_le', '<=', $filtres['date_fin']);
        }

        if (isset($filtres['search'])) {
            $search = $filtres['search'];
            $query->where(function($q) use ($search) {
                $q->where('message_anomalie', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('adherent', function($subQ) use ($search) {
                      $subQ->where('nom', 'LIKE', "%{$search}%")
                           ->orWhere('prenom', 'LIKE', "%{$search}%")
                           ->orWhere('nip', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Tri
        $orderBy = $filtres['order_by'] ?? 'detectee_le';
        $orderDirection = $filtres['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $perPage = $filtres['per_page'] ?? 20;

        return $query->paginate($perPage);
    }

    /**
     * Obtenir toutes les anomalies (pour admin)
     */
    public function getToutesAnomalies(array $filtres = [])
    {
        $query = AdherentAnomalie::with(['adherent', 'organisation', 'correcteur']);

        // Appliquer les mêmes filtres que pour une organisation
        if (isset($filtres['organisation_id'])) {
            $query->byOrganisation($filtres['organisation_id']);
        }

        if (isset($filtres['type_anomalie'])) {
            $query->byType($filtres['type_anomalie']);
        }

        if (isset($filtres['statut'])) {
            $query->byStatut($filtres['statut']);
        }

        if (isset($filtres['champ_concerne'])) {
            $query->where('champ_concerne', $filtres['champ_concerne']);
        }

        if (isset($filtres['date_debut'])) {
            $query->where('detectee_le', '>=', $filtres['date_debut']);
        }

        if (isset($filtres['date_fin'])) {
            $query->where('detectee_le', '<=', $filtres['date_fin']);
        }

        if (isset($filtres['search'])) {
            $search = $filtres['search'];
            $query->where(function($q) use ($search) {
                $q->where('message_anomalie', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('adherent', function($subQ) use ($search) {
                      $subQ->where('nom', 'LIKE', "%{$search}%")
                           ->orWhere('prenom', 'LIKE', "%{$search}%")
                           ->orWhere('nip', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('organisation', function($subQ) use ($search) {
                      $subQ->where('nom', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Tri
        $orderBy = $filtres['order_by'] ?? 'detectee_le';
        $orderDirection = $filtres['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $perPage = $filtres['per_page'] ?? 20;

        return $query->paginate($perPage);
    }

    /**
     * Résoudre une anomalie
     */
    public function resoudreAnomalie($anomalieId, $valeurCorrigee, $commentaire = null, $userId = null)
    {
        try {
            $anomalie = AdherentAnomalie::findOrFail($anomalieId);
            $anomalie->resoudre($valeurCorrigee, $commentaire, $userId);

            Log::info('Anomalie résolue', [
                'anomalie_id' => $anomalieId,
                'resolu_par' => $userId ?: auth()->id(),
                'valeur_corrigee' => $valeurCorrigee
            ]);

            return $anomalie;

        } catch (\Exception $e) {
            Log::error('Erreur résolution anomalie', [
                'anomalie_id' => $anomalieId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Ignorer une anomalie
     */
    public function ignorerAnomalie($anomalieId, $commentaire = null, $userId = null)
    {
        try {
            $anomalie = AdherentAnomalie::findOrFail($anomalieId);
            $anomalie->ignorer($commentaire, $userId);

            Log::info('Anomalie ignorée', [
                'anomalie_id' => $anomalieId,
                'ignore_par' => $userId ?: auth()->id(),
                'commentaire' => $commentaire
            ]);

            return $anomalie;

        } catch (\Exception $e) {
            Log::error('Erreur ignore anomalie', [
                'anomalie_id' => $anomalieId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Résoudre plusieurs anomalies en lot
     */
    public function resoudreAnomaliesEnLot(array $anomalieIds, $action, $commentaire = null, $userId = null)
    {
        try {
            DB::beginTransaction();

            $anomaliesModifiees = [];
            foreach ($anomalieIds as $anomalieId) {
                $anomalie = AdherentAnomalie::findOrFail($anomalieId);
                
                if ($action === 'resoudre') {
                    $anomalie->resoudre(null, $commentaire, $userId);
                } elseif ($action === 'ignorer') {
                    $anomalie->ignorer($commentaire, $userId);
                }
                
                $anomaliesModifiees[] = $anomalie;
            }

            DB::commit();

            Log::info('Anomalies traitées en lot', [
                'action' => $action,
                'count' => count($anomaliesModifiees),
                'traite_par' => $userId ?: auth()->id()
            ]);

            return $anomaliesModifiees;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur traitement lot anomalies', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Exporter les anomalies en CSV
     */
    public function exporterAnomaliesCSV($organisationId = null, array $filtres = [])
    {
        try {
            // Récupérer les anomalies
            if ($organisationId) {
                $anomalies = $this->getAnomaliesOrganisation($organisationId, 
                    array_merge($filtres, ['per_page' => 10000]))->items();
            } else {
                $anomalies = $this->getToutesAnomalies(
                    array_merge($filtres, ['per_page' => 10000]))->items();
            }

            // Créer le CSV
            $csv = Writer::createFromString('');
            $csv->setDelimiter(';');

            // En-têtes
            $headers = [
                'ID Anomalie',
                'Date Détection',
                'Type',
                'Statut',
                'Champ Concerné',
                'Message',
                'Valeur Erronée',
                'Valeur Corrigée',
                'NIP Adhérent',
                'Nom Adhérent',
                'Prénom Adhérent',
                'Organisation',
                'Corrigé Par',
                'Date Correction',
                'Commentaire'
            ];

            $csv->insertOne($headers);

            // Données
            foreach ($anomalies as $anomalie) {
                $row = [
                    $anomalie->id,
                    $anomalie->detectee_le ? $anomalie->detectee_le->format('d/m/Y H:i:s') : '',
                    ucfirst($anomalie->type_anomalie),
                    ucfirst($anomalie->statut),
                    $anomalie->champ_concerne,
                    $anomalie->message_anomalie,
                    is_array($anomalie->valeur_erronee) ? json_encode($anomalie->valeur_erronee) : $anomalie->valeur_erronee,
                    $anomalie->valeur_corrigee,
                    $anomalie->adherent->nip ?? '',
                    $anomalie->adherent->nom ?? '',
                    $anomalie->adherent->prenom ?? '',
                    $anomalie->organisation->nom ?? '',
                    $anomalie->correcteur->name ?? '',
                    $anomalie->date_correction ? $anomalie->date_correction->format('d/m/Y H:i:s') : '',
                    $anomalie->commentaire_correction
                ];

                $csv->insertOne($row);
            }

            return $csv->toString();

        } catch (\Exception $e) {
            Log::error('Erreur export CSV anomalies', [
                'organisation_id' => $organisationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Générer un rapport PDF des anomalies
     */
    public function genererRapportPDF($organisationId = null, array $filtres = [])
    {
        try {
            // Récupérer les données
            if ($organisationId) {
                $anomalies = $this->getAnomaliesOrganisation($organisationId, 
                    array_merge($filtres, ['per_page' => 1000]))->items();
                $organisation = Organisation::find($organisationId);
                $titre = "Rapport d'Anomalies - " . ($organisation->nom ?? 'Organisation');
            } else {
                $anomalies = $this->getToutesAnomalies(
                    array_merge($filtres, ['per_page' => 1000]))->items();
                $titre = "Rapport Global d'Anomalies";
            }

            // Statistiques
            $stats = $this->calculerStatistiques($anomalies);

            // Données pour la vue
            $data = [
                'titre' => $titre,
                'date_generation' => now()->format('d/m/Y H:i:s'),
                'anomalies' => $anomalies,
                'statistiques' => $stats,
                'filtres' => $filtres,
                'organisation' => $organisation ?? null
            ];

            // Générer le PDF
            $pdf = Pdf::loadView('reports.anomalies', $data);

            return $pdf;

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF anomalies', [
                'organisation_id' => $organisationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques d'anomalies
     */
    public function getStatistiques($organisationId = null)
    {
        if ($organisationId) {
            return AdherentAnomalie::getStatistiquesParOrganisation($organisationId);
        } else {
            return AdherentAnomalie::getStatistiquesGenerales();
        }
    }

    /**
     * Obtenir les statistiques d'évolution
     */
    public function getStatistiquesEvolution($organisationId = null, $periode = 30)
    {
        $query = AdherentAnomalie::selectRaw('
            DATE(detectee_le) as date,
            COUNT(*) as total,
            SUM(CASE WHEN type_anomalie = "critique" THEN 1 ELSE 0 END) as critiques,
            SUM(CASE WHEN type_anomalie = "majeure" THEN 1 ELSE 0 END) as majeures,
            SUM(CASE WHEN type_anomalie = "mineure" THEN 1 ELSE 0 END) as mineures,
            SUM(CASE WHEN statut = "resolu" THEN 1 ELSE 0 END) as resolues
        ')
        ->where('detectee_le', '>=', now()->subDays($periode))
        ->groupBy('date')
        ->orderBy('date');

        if ($organisationId) {
            $query->where('organisation_id', $organisationId);
        }

        return $query->get();
    }

    /**
     * Nettoyer les anciennes anomalies résolues
     */
    public function nettoyerAnciennesAnomalies($joursConservation = 365)
    {
        try {
            $dateLimit = now()->subDays($joursConservation);
            
            $count = AdherentAnomalie::where('statut', 'resolu')
                ->where('date_correction', '<', $dateLimit)
                ->delete();

            Log::info('Nettoyage anomalies anciennes', [
                'supprimees' => $count,
                'date_limite' => $dateLimit->format('Y-m-d')
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Erreur nettoyage anomalies', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Méthodes privées
     */
    private function validerDonneesAnomalie(array $data)
    {
        $required = ['adherent_id', 'organisation_id', 'type_anomalie', 'champ_concerne', 'message_anomalie'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("Le champ {$field} est requis");
            }
        }

        if (!in_array($data['type_anomalie'], ['critique', 'majeure', 'mineure'])) {
            throw new \InvalidArgumentException("Type d'anomalie invalide");
        }
    }

    private function determinerPriorite($type)
    {
        $priorites = [
            'critique' => 1,
            'majeure' => 2,
            'mineure' => 3
        ];

        return $priorites[$type] ?? 3;
    }

    private function determinerImpactMetier($type)
    {
        $impacts = [
            'critique' => 'Bloquant - Validation manuelle requise',
            'majeure' => 'Important - Correction recommandée',
            'mineure' => 'Mineur - Information seulement'
        ];

        return $impacts[$type] ?? 'Non défini';
    }

    private function calculerStatistiques($anomalies)
    {
        $stats = [
            'total' => count($anomalies),
            'critiques' => 0,
            'majeures' => 0,
            'mineures' => 0,
            'en_attente' => 0,
            'resolues' => 0,
            'ignorees' => 0,
            'champs_concernes' => []
        ];

        foreach ($anomalies as $anomalie) {
            $stats[$anomalie->type_anomalie . 's']++;
            $stats[$anomalie->statut === 'resolu' ? 'resolues' : 
                   ($anomalie->statut === 'ignore' ? 'ignorees' : 'en_attente')]++;

            if (!isset($stats['champs_concernes'][$anomalie->champ_concerne])) {
                $stats['champs_concernes'][$anomalie->champ_concerne] = 0;
            }
            $stats['champs_concernes'][$anomalie->champ_concerne]++;
        }

        return $stats;
    }
}