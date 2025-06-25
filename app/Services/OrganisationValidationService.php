<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Dossier;
use App\Models\DocumentType;
use App\Models\Adherent;
use App\Models\Fondateur;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class OrganisationValidationService
{
    /**
     * Valider une organisation avant soumission
     */
    public function validateBeforeSubmission(Organisation $organisation): array
    {
        $errors = [];
        $warnings = [];
        
        // 1. Vérifier les informations de base
        if (!$this->validateBasicInfo($organisation)) {
            $errors[] = 'Les informations de base de l\'organisation sont incomplètes';
        }
        
        // 2. Vérifier les fondateurs
        $fondateursValidation = $this->validateFondateurs($organisation);
        $errors = array_merge($errors, $fondateursValidation['errors']);
        $warnings = array_merge($warnings, $fondateursValidation['warnings']);
        
        // 3. Vérifier les adhérents
        $adherentsValidation = $this->validateAdherents($organisation);
        $errors = array_merge($errors, $adherentsValidation['errors']);
        $warnings = array_merge($warnings, $adherentsValidation['warnings']);
        
        // 4. Vérifier les documents obligatoires
        $documentsValidation = $this->validateDocuments($organisation);
        $errors = array_merge($errors, $documentsValidation['errors']);
        $warnings = array_merge($warnings, $documentsValidation['warnings']);
        
        // 5. Vérifications spécifiques par type
        $typeValidation = $this->validateByType($organisation);
        $errors = array_merge($errors, $typeValidation['errors']);
        $warnings = array_merge($warnings, $typeValidation['warnings']);
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'summary' => $this->generateValidationSummary($organisation, $errors, $warnings)
        ];
    }
    
    /**
     * Valider les informations de base
     */
    protected function validateBasicInfo(Organisation $organisation): bool
    {
        $requiredFields = [
            'nom', 'objet', 'siege_social', 'province', 
            'departement', 'email', 'telephone'
        ];
        
        foreach ($requiredFields as $field) {
            if (empty($organisation->$field)) {
                return false;
            }
        }
        
        // Validation email
        if (!filter_var($organisation->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Validation téléphone (format Gabon)
        if (!preg_match('/^(\+241|0)[0-9]{7,8}$/', $organisation->telephone)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Valider les fondateurs
     */
    protected function validateFondateurs(Organisation $organisation): array
    {
        $errors = [];
        $warnings = [];
        
        $fondateurs = $organisation->fondateurs()->actifs()->get();
        
        // Vérifier le nombre minimum de fondateurs
        $minFondateurs = $this->getMinimumFondateurs($organisation->type);
        
        if ($fondateurs->count() < $minFondateurs) {
            $errors[] = sprintf(
                'Le nombre minimum de fondateurs requis est de %d. Actuellement : %d',
                $minFondateurs,
                $fondateurs->count()
            );
        }
        
        // Vérifier que chaque fondateur a les documents requis
        foreach ($fondateurs as $fondateur) {
            if (empty($fondateur->nip)) {
                $errors[] = sprintf('Le fondateur %s n\'a pas de NIP', $fondateur->nom_complet);
            }
            
            if (empty($fondateur->piece_identite_path)) {
                $warnings[] = sprintf('Le fondateur %s n\'a pas fourni de pièce d\'identité', $fondateur->nom_complet);
            }
            
            // Vérifier l'âge minimum
            if ($fondateur->getAge() < 21) {
                $errors[] = sprintf('Le fondateur %s doit avoir au moins 21 ans', $fondateur->nom_complet);
            }
        }
        
        return compact('errors', 'warnings');
    }
    
    /**
     * Valider les adhérents
     */
    protected function validateAdherents(Organisation $organisation): array
    {
        $errors = [];
        $warnings = [];
        
        $adherents = $organisation->adherentsActifs;
        $minAdherents = $organisation->nombre_adherents_min ?? $this->getDefaultMinimumAdherents($organisation->type);
        
        if ($adherents->count() < $minAdherents) {
            $errors[] = sprintf(
                'Le nombre minimum d\'adhérents requis est de %d. Actuellement : %d',
                $minAdherents,
                $adherents->count()
            );
        }
        
        // Pour les partis politiques, vérifier l'unicité
        if ($organisation->isPartiPolitique()) {
            $duplicates = $this->checkDuplicateAdherentsInPartis($adherents);
            if (!empty($duplicates)) {
                foreach ($duplicates as $duplicate) {
                    $errors[] = sprintf(
                        'L\'adhérent %s (NIP: %s) est déjà membre du parti politique "%s"',
                        $duplicate['adherent']->nom_complet,
                        $duplicate['adherent']->nip,
                        $duplicate['other_parti']->nom
                    );
                }
            }
        }
        
        // Vérifier la répartition géographique pour les partis
        if ($organisation->isPartiPolitique()) {
            $provinceCoverage = $this->checkProvincialCoverage($adherents);
            if ($provinceCoverage['coverage'] < 50) {
                $warnings[] = sprintf(
                    'La couverture provinciale est de %d%%. Il est recommandé d\'avoir une représentation dans au moins 50%% des provinces',
                    $provinceCoverage['coverage']
                );
            }
        }
        
        return compact('errors', 'warnings');
    }
    
    /**
     * Valider les documents
     */
    protected function validateDocuments(Organisation $organisation): array
    {
        $errors = [];
        $warnings = [];
        
        $dossierActif = $organisation->dossierActif;
        if (!$dossierActif) {
            $errors[] = 'Aucun dossier actif trouvé pour cette organisation';
            return compact('errors', 'warnings');
        }
        
        $requiredDocTypes = DocumentType::getRequiredFor(
            $organisation->type,
            $dossierActif->type_operation
        );
        
        $providedDocTypes = $dossierActif->documents()
            ->pluck('document_type_id')
            ->toArray();
        
        foreach ($requiredDocTypes as $docType) {
            if (!in_array($docType->id, $providedDocTypes)) {
                $errors[] = sprintf('Le document "%s" est obligatoire et n\'a pas été fourni', $docType->nom);
            }
        }
        
        // Vérifier la validité des documents fournis
        $documents = $dossierActif->documents;
        foreach ($documents as $document) {
            if (!$document->fileExists()) {
                $errors[] = sprintf('Le fichier pour le document "%s" est introuvable', $document->documentType->nom);
            }
            
            if ($document->status === 'rejected') {
                $warnings[] = sprintf(
                    'Le document "%s" a été rejeté : %s',
                    $document->documentType->nom,
                    $document->validation_comment
                );
            }
        }
        
        return compact('errors', 'warnings');
    }
    
    /**
     * Validations spécifiques par type d'organisation
     */
    protected function validateByType(Organisation $organisation): array
    {
        $errors = [];
        $warnings = [];
        
        switch ($organisation->type) {
            case Organisation::TYPE_PARTI:
                // Vérifier le programme politique
                if (empty($organisation->metadata['programme_politique'])) {
                    $errors[] = 'Un parti politique doit avoir un programme politique défini';
                }
                
                // Vérifier les organes dirigeants
                if ($organisation->organeMembres()->count() < 5) {
                    $errors[] = 'Un parti politique doit avoir au moins 5 membres dans ses organes dirigeants';
                }
                break;
                
            case Organisation::TYPE_CONFESSION:
                // Vérifier la doctrine
                if (empty($organisation->metadata['doctrine'])) {
                    $warnings[] = 'Il est recommandé de définir la doctrine de la confession religieuse';
                }
                
                // Vérifier le lieu de culte
                if (empty($organisation->metadata['lieu_culte_principal'])) {
                    $errors[] = 'Une confession religieuse doit avoir au moins un lieu de culte principal';
                }
                break;
                
            case Organisation::TYPE_ONG:
                // Vérifier le domaine d'intervention
                if (empty($organisation->metadata['domaines_intervention'])) {
                    $errors[] = 'Une ONG doit définir ses domaines d\'intervention';
                }
                
                // Vérifier le budget prévisionnel
                if (empty($organisation->metadata['budget_previsionnel'])) {
                    $warnings[] = 'Il est recommandé de fournir un budget prévisionnel';
                }
                break;
        }
        
        return compact('errors', 'warnings');
    }
    
    /**
     * Vérifier les doublons d'adhérents dans les partis
     */
    protected function checkDuplicateAdherentsInPartis(Collection $adherents): array
    {
        $duplicates = [];
        
        foreach ($adherents as $adherent) {
            $otherAdhesions = Adherent::where('nip', $adherent->nip)
                ->where('id', '!=', $adherent->id)
                ->where('is_active', true)
                ->whereHas('organisation', function ($query) {
                    $query->where('type', Organisation::TYPE_PARTI)
                        ->where('is_active', true);
                })
                ->with('organisation')
                ->get();
            
            foreach ($otherAdhesions as $otherAdhesion) {
                $duplicates[] = [
                    'adherent' => $adherent,
                    'other_parti' => $otherAdhesion->organisation
                ];
            }
        }
        
        return $duplicates;
    }
    
    /**
     * Vérifier la couverture provinciale
     */
    protected function checkProvincialCoverage(Collection $adherents): array
    {
        $totalProvinces = 9; // Nombre de provinces au Gabon
        $provincesRepresentees = $adherents->pluck('province')->unique()->count();
        
        return [
            'total_provinces' => $totalProvinces,
            'provinces_representees' => $provincesRepresentees,
            'coverage' => round(($provincesRepresentees / $totalProvinces) * 100)
        ];
    }
    
    /**
     * Obtenir le nombre minimum de fondateurs selon le type
     */
    protected function getMinimumFondateurs(string $type): int
    {
        $minimums = [
            Organisation::TYPE_ASSOCIATION => 3,
            Organisation::TYPE_ONG => 5,
            Organisation::TYPE_PARTI => 10,
            Organisation::TYPE_CONFESSION => 7
        ];
        
        return $minimums[$type] ?? 3;
    }
    
    /**
     * Obtenir le nombre minimum d'adhérents par défaut
     */
    protected function getDefaultMinimumAdherents(string $type): int
    {
        $minimums = [
            Organisation::TYPE_ASSOCIATION => 10,
            Organisation::TYPE_ONG => 20,
            Organisation::TYPE_PARTI => 1000,
            Organisation::TYPE_CONFESSION => 50
        ];
        
        return $minimums[$type] ?? 10;
    }
    
    /**
     * Générer un résumé de validation
     */
    protected function generateValidationSummary(Organisation $organisation, array $errors, array $warnings): array
    {
        return [
            'organisation' => [
                'nom' => $organisation->nom,
                'type' => $organisation->type_label,
                'statut' => $organisation->statut
            ],
            'statistiques' => [
                'nombre_fondateurs' => $organisation->fondateurs()->count(),
                'nombre_adherents' => $organisation->adherentsActifs()->count(),
                'nombre_documents' => $organisation->dossierActif ? $organisation->dossierActif->documents()->count() : 0,
                'nombre_etablissements' => $organisation->etablissements()->count()
            ],
            'validation' => [
                'nombre_erreurs' => count($errors),
                'nombre_avertissements' => count($warnings),
                'pret_pour_soumission' => empty($errors)
            ],
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    /**
     * Valider une organisation pour changement de statut
     */
    public function canChangeStatus(Organisation $organisation, string $newStatus): array
    {
        $currentStatus = $organisation->statut;
        $errors = [];
        
        // Définir les transitions autorisées
        $allowedTransitions = [
            Organisation::STATUT_BROUILLON => [
                Organisation::STATUT_SOUMIS
            ],
            Organisation::STATUT_SOUMIS => [
                Organisation::STATUT_EN_VALIDATION,
                Organisation::STATUT_BROUILLON
            ],
            Organisation::STATUT_EN_VALIDATION => [
                Organisation::STATUT_APPROUVE,
                Organisation::STATUT_REJETE
            ],
            Organisation::STATUT_REJETE => [
                Organisation::STATUT_BROUILLON
            ],
            Organisation::STATUT_APPROUVE => [
                Organisation::STATUT_SUSPENDU
            ],
            Organisation::STATUT_SUSPENDU => [
                Organisation::STATUT_APPROUVE,
                Organisation::STATUT_RADIE
            ]
        ];
        
        // Vérifier si la transition est autorisée
        if (!isset($allowedTransitions[$currentStatus]) || 
            !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            $errors[] = sprintf(
                'Transition non autorisée de "%s" vers "%s"',
                $currentStatus,
                $newStatus
            );
        }
        
        // Validations spécifiques selon la transition
        if ($currentStatus === Organisation::STATUT_BROUILLON && 
            $newStatus === Organisation::STATUT_SOUMIS) {
            // Validation complète avant soumission
            $validation = $this->validateBeforeSubmission($organisation);
            $errors = array_merge($errors, $validation['errors']);
        }
        
        return [
            'can_change' => empty($errors),
            'errors' => $errors
        ];
    }
}