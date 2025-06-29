<?php

namespace App\Services;

use App\Models\OrganizationDraft;
use App\Models\Organisation;
use App\Models\Fondateur;
use App\Models\Adherent;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrganisationStepService
{
    /**
     * Configuration des étapes et leurs exigences
     */
    const STEPS_CONFIG = [
        1 => [
            'name' => 'Type d\'organisation',
            'required_fields' => ['type_organisation'],
            'validation_rules' => [
                'type_organisation' => 'required|in:association,ong,parti_politique,confession_religieuse'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        2 => [
            'name' => 'Guide et exigences',
            'required_fields' => ['guide_read_confirm'],
            'validation_rules' => [
                'guide_read_confirm' => 'required|accepted'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        3 => [
            'name' => 'Informations demandeur',
            'required_fields' => ['demandeur_nip', 'demandeur_nom', 'demandeur_prenom', 'demandeur_email', 'demandeur_telephone'],
            'validation_rules' => [
                'demandeur_nip' => 'required|digits:13',
                'demandeur_nom' => 'required|string|max:255',
                'demandeur_prenom' => 'required|string|max:255',
                'demandeur_email' => 'required|email|max:255',
                'demandeur_telephone' => 'required|string|max:20',
                'demandeur_role' => 'required|string'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        4 => [
            'name' => 'Informations organisation',
            'required_fields' => ['org_nom', 'org_objet', 'org_telephone'],
            'validation_rules' => [
                'org_nom' => 'required|string|max:255',
                'org_objet' => 'required|string|min:50',
                'org_telephone' => 'required|string|max:255',
                'org_date_creation' => 'required|date'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        5 => [
            'name' => 'Coordonnées et localisation',
            'required_fields' => ['org_adresse_complete', 'org_province', 'org_prefecture'],
            'validation_rules' => [
                'org_adresse_complete' => 'required|string|max:255',
                'org_province' => 'required|string|max:255',
                'org_prefecture' => 'required|string|max:255',
                'org_zone_type' => 'required|in:urbaine,rurale'
            ],
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        6 => [
            'name' => 'Fondateurs',
            'required_fields' => ['fondateurs'],
            'validation_rules' => [],
            'custom_validation' => 'validateFondateurs',
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        7 => [
            'name' => 'Adhérents',
            'required_fields' => ['adherents'],
            'validation_rules' => [],
            'custom_validation' => 'validateAdherents',
            'can_generate_accuse' => true,
            'auto_save_enabled' => true
        ],
        8 => [
            'name' => 'Documents',
            'required_fields' => ['documents'],
            'validation_rules' => [],
            'custom_validation' => 'validateDocuments',
            'can_generate_accuse' => false,
            'auto_save_enabled' => true
        ],
        9 => [
            'name' => 'Validation finale',
            'required_fields' => ['declaration_veracite', 'declaration_conformite', 'declaration_autorisation'],
            'validation_rules' => [
                'declaration_veracite' => 'required|accepted',
                'declaration_conformite' => 'required|accepted',
                'declaration_autorisation' => 'required|accepted'
            ],
            'can_generate_accuse' => false,
            'auto_save_enabled' => false
        ]
    ];

    /**
     * Sauvegarder une étape spécifique
     */
    public function saveStep(int $stepNumber, array $data, int $userId, string $sessionId = null): array
    {
        try {
            Log::info("Sauvegarde étape {$stepNumber}", [
                'user_id' => $userId,
                'data_keys' => array_keys($data),
                'session_id' => $sessionId
            ]);

            // Validation des données de l'étape
            $validationResult = $this->validateStep($stepNumber, $data);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'message' => 'Erreurs de validation détectées',
                    'errors' => $validationResult['errors'],
                    'step' => $stepNumber
                ];
            }

            // Récupérer ou créer le brouillon
            $draft = $this->getOrCreateDraft($userId, $sessionId);
            
            // Mettre à jour les données de l'étape
            $stepData = $this->updateStepData($draft, $stepNumber, $data);
            
            // Sauvegarder en base
            $draft->save();

            // Générer accusé si possible
            $accuseGenerated = false;
            if ($this->canGenerateAccuse($stepNumber)) {
                $accuseGenerated = $this->generateStepAccuse($stepNumber, $draft);
            }

            Log::info("Étape {$stepNumber} sauvegardée avec succès", [
                'draft_id' => $draft->id,
                'accuse_generated' => $accuseGenerated
            ]);

            return [
                'success' => true,
                'message' => "Étape {$stepNumber} sauvegardée avec succès",
                'draft_id' => $draft->id,
                'step_status' => $stepData['status'],
                'accuse_generated' => $accuseGenerated,
                'can_proceed_next' => $this->canProceedToStep($stepNumber + 1, $draft->id),
                'completion_percentage' => $this->calculateCompletionPercentage($draft)
            ];

        } catch (\Exception $e) {
            Log::error("Erreur sauvegarde étape {$stepNumber}", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde',
                'error' => $e->getMessage(),
                'step' => $stepNumber
            ];
        }
    }

    /**
     * Valider une étape spécifique
     */
    public function validateStep(int $stepNumber, array $data): array
    {
        $config = self::STEPS_CONFIG[$stepNumber] ?? null;
        if (!$config) {
            return [
                'valid' => false,
                'errors' => ['step' => 'Étape non reconnue']
            ];
        }

        $errors = [];

        // Validation des règles standard
        if (!empty($config['validation_rules'])) {
            $validator = \Validator::make($data, $config['validation_rules']);
            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->toArray());
            }
        }

        // Validation personnalisée
        if (!empty($config['custom_validation'])) {
            $customMethod = $config['custom_validation'];
            if (method_exists($this, $customMethod)) {
                $customResult = $this->$customMethod($data);
                if (!$customResult['valid']) {
                    $errors = array_merge($errors, $customResult['errors']);
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Récupérer les données d'une étape
     */
    public function getStepData(int $stepNumber, int $draftId): array
    {
        $draft = OrganizationDraft::find($draftId);
        if (!$draft) {
            return [
                'success' => false,
                'message' => 'Brouillon non trouvé'
            ];
        }

        $formData = $draft->form_data ?? [];
        $stepKey = "step_{$stepNumber}";
        $stepData = $formData[$stepKey] ?? [];

        return [
            'success' => true,
            'data' => $stepData['data'] ?? [],
            'status' => $stepData['status'] ?? 'pending',
            'validated_at' => $stepData['validated_at'] ?? null,
            'errors' => $stepData['errors'] ?? []
        ];
    }

    /**
     * Vérifier si on peut passer à l'étape suivante
     */
    public function canProceedToStep(int $stepNumber, int $draftId): bool
    {
        if ($stepNumber <= 1) {
            return true; // Première étape toujours accessible
        }

        $previousStepNumber = $stepNumber - 1;
        $previousStepData = $this->getStepData($previousStepNumber, $draftId);

        return $previousStepData['success'] && 
               $previousStepData['status'] === 'completed';
    }

    /**
     * Générer un accusé de réception pour une étape
     */
    public function generateStepAccuse(int $stepNumber, OrganizationDraft $draft): bool
    {
        try {
            $config = self::STEPS_CONFIG[$stepNumber];
            if (!$config['can_generate_accuse']) {
                return false;
            }

            $accuseData = [
                'draft_id' => $draft->id,
                'step_number' => $stepNumber,
                'step_name' => $config['name'],
                'completed_at' => now(),
                'user_id' => $draft->user_id,
                'organization_type' => $draft->organization_type
            ];

            // Ici on pourrait sauvegarder l'accusé en base ou générer un PDF
            Log::info("Accusé de réception généré pour étape {$stepNumber}", $accuseData);

            return true;

        } catch (\Exception $e) {
            Log::error("Erreur génération accusé étape {$stepNumber}", [
                'draft_id' => $draft->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Finaliser le processus et créer l'organisation
     */
    public function finalizeOrganisation(int $draftId): array
    {
        try {
            $draft = OrganizationDraft::find($draftId);
            if (!$draft) {
                return [
                    'success' => false,
                    'message' => 'Brouillon non trouvé'
                ];
            }

            // Vérifier que toutes les étapes sont complétées
            for ($step = 1; $step <= 9; $step++) {
                if (!$this->canProceedToStep($step + 1, $draftId) && $step < 9) {
                    return [
                        'success' => false,
                        'message' => "L'étape {$step} n'est pas complétée",
                        'missing_step' => $step
                    ];
                }
            }

            DB::beginTransaction();

            // Créer l'organisation finale
            $organisationData = $this->consolidateOrganisationData($draft);
            $organisation = Organisation::create($organisationData);

            // Créer les entités liées
            $this->createRelatedEntities($organisation, $draft);

            // Marquer le brouillon comme finalisé
            $draft->update([
                'completion_percentage' => 100,
                'current_step' => 9,
                'expires_at' => now()->addDays(30) // Garder 30 jours pour référence
            ]);

            DB::commit();

            Log::info("Organisation finalisée avec succès", [
                'organisation_id' => $organisation->id,
                'draft_id' => $draft->id
            ]);

            return [
                'success' => true,
                'message' => 'Organisation créée avec succès',
                'organisation_id' => $organisation->id,
                'organisation' => $organisation
            ];

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error("Erreur finalisation organisation", [
                'draft_id' => $draftId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'organisation',
                'error' => $e->getMessage()
            ];
        }
    }

    // =============================================
    // MÉTHODES PRIVÉES
    // =============================================

    /**
     * Récupérer ou créer un brouillon
     */
    private function getOrCreateDraft(int $userId, string $sessionId = null): OrganizationDraft
    {
        $query = OrganizationDraft::where('user_id', $userId);
        
        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        $draft = $query->latest()->first();

        if (!$draft) {
            $draft = OrganizationDraft::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'form_data' => [],
                'current_step' => 1,
                'completion_percentage' => 0,
                'last_saved_at' => now(),
                'expires_at' => now()->addDays(7)
            ]);
        }

        return $draft;
    }

    /**
     * Mettre à jour les données d'une étape
     */
    private function updateStepData(OrganizationDraft $draft, int $stepNumber, array $data): array
    {
        $formData = $draft->form_data ?? [];
        $stepKey = "step_{$stepNumber}";

        $stepData = [
            'status' => 'completed',
            'data' => $data,
            'validated_at' => now()->toISOString(),
            'errors' => []
        ];

        $formData[$stepKey] = $stepData;

        // Mettre à jour le brouillon
        $draft->form_data = $formData;
        $draft->current_step = max($draft->current_step, $stepNumber);
        $draft->completion_percentage = $this->calculateCompletionPercentage($draft);
        $draft->last_saved_at = now();

        return $stepData;
    }

    /**
     * Calculer le pourcentage de completion
     */
    private function calculateCompletionPercentage(OrganizationDraft $draft): int
    {
        $formData = $draft->form_data ?? [];
        $completedSteps = 0;

        for ($i = 1; $i <= 9; $i++) {
            $stepKey = "step_{$i}";
            if (isset($formData[$stepKey]) && $formData[$stepKey]['status'] === 'completed') {
                $completedSteps++;
            }
        }

        return round(($completedSteps / 9) * 100);
    }

    /**
     * Vérifier si on peut générer un accusé pour cette étape
     */
    private function canGenerateAccuse(int $stepNumber): bool
    {
        $config = self::STEPS_CONFIG[$stepNumber] ?? null;
        return $config ? $config['can_generate_accuse'] : false;
    }

    /**
     * Validation personnalisée pour les fondateurs
     */
    private function validateFondateurs(array $data): array
    {
        $fondateurs = $data['fondateurs'] ?? [];
        
        if (empty($fondateurs)) {
            return [
                'valid' => false,
                'errors' => ['fondateurs' => 'Au moins un fondateur est requis']
            ];
        }

        // Validation selon le type d'organisation
        $organizationType = $data['organization_type'] ?? 'association';
        $minRequired = $this->getMinFondateurs($organizationType);

        if (count($fondateurs) < $minRequired) {
            return [
                'valid' => false,
                'errors' => ['fondateurs' => "Minimum {$minRequired} fondateurs requis"]
            ];
        }

        return ['valid' => true, 'errors' => []];
    }

    /**
     * Validation personnalisée pour les adhérents
     */
    private function validateAdherents(array $data): array
    {
        $adherents = $data['adherents'] ?? [];
        
        if (empty($adherents)) {
            return [
                'valid' => false,
                'errors' => ['adherents' => 'Au moins un adhérent est requis']
            ];
        }

        // Validation selon le type d'organisation
        $organizationType = $data['organization_type'] ?? 'association';
        $minRequired = $this->getMinAdherents($organizationType);

        if (count($adherents) < $minRequired) {
            return [
                'valid' => false,
                'errors' => ['adherents' => "Minimum {$minRequired} adhérents requis"]
            ];
        }

        return ['valid' => true, 'errors' => []];
    }

    /**
     * Validation personnalisée pour les documents
     */
    private function validateDocuments(array $data): array
    {
        // Pour l'instant, accepter même sans documents
        // La validation complète sera faite à la soumission finale
        return ['valid' => true, 'errors' => []];
    }

    /**
     * Obtenir le minimum de fondateurs requis
     */
    private function getMinFondateurs(string $type): int
    {
        $minimums = [
            'association' => 3,
            'ong' => 5,
            'parti_politique' => 3,
            'confession_religieuse' => 3
        ];

        return $minimums[$type] ?? 3;
    }

    /**
     * Obtenir le minimum d'adhérents requis
     */
    private function getMinAdherents(string $type): int
    {
        $minimums = [
            'association' => 10,
            'ong' => 15,
            'parti_politique' => 50,
            'confession_religieuse' => 10
        ];

        return $minimums[$type] ?? 10;
    }

    /**
     * Consolider les données pour créer l'organisation finale
     */
    private function consolidateOrganisationData(OrganizationDraft $draft): array
    {
        $formData = $draft->form_data ?? [];
        $consolidatedData = [];

        // Extraire et consolider les données de chaque étape
        foreach ($formData as $stepKey => $stepData) {
            if (isset($stepData['data'])) {
                $consolidatedData = array_merge($consolidatedData, $stepData['data']);
            }
        }

        // Mapper vers les colonnes de la table organisations
        return [
            'user_id' => $draft->user_id,
            'type' => $consolidatedData['type_organisation'] ?? 'association',
            'nom' => $consolidatedData['org_nom'] ?? '',
            'sigle' => $consolidatedData['org_sigle'] ?? null,
            'objet' => $consolidatedData['org_objet'] ?? '',
            'siege_social' => $consolidatedData['org_adresse_complete'] ?? '',
            'province' => $consolidatedData['org_province'] ?? '',
            'departement' => $consolidatedData['org_departement'] ?? null,
            'prefecture' => $consolidatedData['org_prefecture'] ?? '',
            'zone_type' => $consolidatedData['org_zone_type'] ?? 'urbaine',
            'latitude' => $consolidatedData['org_latitude'] ?? null,
            'longitude' => $consolidatedData['org_longitude'] ?? null,
            'email' => $consolidatedData['org_email'] ?? null,
            'telephone' => $consolidatedData['org_telephone'] ?? '',
            'site_web' => $consolidatedData['org_site_web'] ?? null,
            'date_creation' => $consolidatedData['org_date_creation'] ?? now(),
            'statut' => 'soumis',
            'is_active' => true,
            'nombre_adherents_min' => $this->getMinAdherents($consolidatedData['type_organisation'] ?? 'association')
        ];
    }

    /**
     * Créer les entités liées (fondateurs, adhérents, etc.)
     */
    private function createRelatedEntities(Organisation $organisation, OrganizationDraft $draft): void
    {
        $formData = $draft->form_data ?? [];

        // Créer les fondateurs
        if (isset($formData['step_6']['data']['fondateurs'])) {
            foreach ($formData['step_6']['data']['fondateurs'] as $fondateurData) {
                Fondateur::create([
                    'organisation_id' => $organisation->id,
                    'nip' => $fondateurData['nip'],
                    'nom' => $fondateurData['nom'],
                    'prenom' => $fondateurData['prenom'],
                    'fonction' => $fondateurData['fonction'],
                    'telephone' => $fondateurData['telephone'],
                    'email' => $fondateurData['email'] ?? null
                ]);
            }
        }

        // Créer les adhérents
        if (isset($formData['step_7']['data']['adherents'])) {
            foreach ($formData['step_7']['data']['adherents'] as $adherentData) {
                Adherent::create([
                    'organisation_id' => $organisation->id,
                    'nip' => $adherentData['nip'],
                    'nom' => $adherentData['nom'],
                    'prenom' => $adherentData['prenom'],
                    'profession' => $adherentData['profession'] ?? null,
                    'fonction' => $adherentData['fonction'] ?? 'Membre',
                    'telephone' => $adherentData['telephone'] ?? null,
                    'date_adhesion' => now(),
                    'is_active' => true
                ]);
            }
        }
    }
}