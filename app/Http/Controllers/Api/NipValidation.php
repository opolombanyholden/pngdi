<?php

namespace App\Services;

use App\Models\NipDatabase;
use App\Models\Adherent;
use Illuminate\Support\Facades\Log;

class NipValidationService
{
    /**
     * Validation complète d'un NIP avec vérification dans la base centrale
     */
    public function validateNip($nip, $nom = null, $prenom = null)
    {
        try {
            // 1. Validation du format
            $formatValidation = NipDatabase::validateNipFormat($nip);
            if (!$formatValidation['valid']) {
                return [
                    'valid' => false,
                    'error' => $formatValidation['error'],
                    'source' => 'format_validation'
                ];
            }

            // 2. Vérification dans la base centrale NIP
            $nipRecord = NipDatabase::getNipInfo($nip);
            
            if (!$nipRecord) {
                return [
                    'valid' => false,
                    'error' => 'NIP non trouvé dans la base de données centrale',
                    'source' => 'nip_database',
                    'suggestion' => 'Veuillez contacter l\'administration pour vérifier ce NIP'
                ];
            }

            // 3. Vérification du statut
            if ($nipRecord->statut !== 'actif') {
                return [
                    'valid' => false,
                    'error' => "NIP {$nipRecord->statut}. Impossible de l'utiliser",
                    'source' => 'nip_status',
                    'nip_data' => [
                        'nom' => $nipRecord->nom,
                        'prenom' => $nipRecord->prenom,
                        'statut' => $nipRecord->statut
                    ]
                ];
            }

            // 4. Vérification de cohérence nom/prénom si fournis
            if ($nom && $prenom) {
                $coherenceCheck = $this->checkNameCoherence($nipRecord, $nom, $prenom);
                if (!$coherenceCheck['coherent']) {
                    return [
                        'valid' => false,
                        'error' => 'Incohérence entre le NIP et le nom/prénom fourni',
                        'source' => 'name_coherence',
                        'details' => $coherenceCheck,
                        'nip_data' => [
                            'nom' => $nipRecord->nom,
                            'prenom' => $nipRecord->prenom
                        ]
                    ];
                }
            }

            // 5. Vérification de l'unicité dans les organisations (pour partis politiques)
            $uniquenessCheck = $this->checkUniquenessForPoliticalParties($nip);
            if (!$uniquenessCheck['unique']) {
                return [
                    'valid' => true, // Le NIP est valide mais nécessite validation manuelle
                    'warning' => true,
                    'message' => 'NIP déjà utilisé dans une autre organisation politique',
                    'requires_validation' => true,
                    'existing_memberships' => $uniquenessCheck['existing_memberships'],
                    'nip_data' => [
                        'nom' => $nipRecord->nom,
                        'prenom' => $nipRecord->prenom,
                        'age' => $nipRecord->age
                    ]
                ];
            }

            // 6. Succès - NIP valide
            return [
                'valid' => true,
                'message' => 'NIP valide et utilisable',
                'nip_data' => [
                    'nom' => $nipRecord->nom,
                    'prenom' => $nipRecord->prenom,
                    'date_naissance' => $nipRecord->date_naissance,
                    'lieu_naissance' => $nipRecord->lieu_naissance,
                    'sexe' => $nipRecord->sexe,
                    'age' => $nipRecord->age,
                    'telephone' => $nipRecord->telephone,
                    'email' => $nipRecord->email
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation NIP: ' . $e->getMessage());
            return [
                'valid' => false,
                'error' => 'Erreur technique lors de la validation du NIP',
                'source' => 'system_error'
            ];
        }
    }

    /**
     * Vérification de cohérence entre NIP et nom/prénom
     */
    protected function checkNameCoherence($nipRecord, $nom, $prenom)
    {
        $similarities = [];
        
        // Normalisation des noms pour comparaison
        $nipNom = strtolower(trim($this->removeAccents($nipRecord->nom)));
        $nipPrenom = strtolower(trim($this->removeAccents($nipRecord->prenom)));
        $inputNom = strtolower(trim($this->removeAccents($nom)));
        $inputPrenom = strtolower(trim($this->removeAccents($prenom)));

        // Calcul de similarité pour le nom
        $nomSimilarity = $this->calculateSimilarity($nipNom, $inputNom);
        $prenomSimilarity = $this->calculateSimilarity($nipPrenom, $inputPrenom);

        $similarities['nom'] = $nomSimilarity;
        $similarities['prenom'] = $prenomSimilarity;

        // Seuil de tolérance pour les fautes de frappe
        $threshold = 0.8;

        $coherent = ($nomSimilarity >= $threshold && $prenomSimilarity >= $threshold);

        return [
            'coherent' => $coherent,
            'similarities' => $similarities,
            'threshold' => $threshold,
            'nip_name' => $nipRecord->nom_complet,
            'input_name' => "$nom $prenom"
        ];
    }

    /**
     * Vérification d'unicité pour les partis politiques
     */
    protected function checkUniquenessForPoliticalParties($nip)
    {
        // Rechercher les adhésions actives dans des partis politiques
        $existingMemberships = Adherent::where('nip', $nip)
                                       ->where('is_active', true)
                                       ->whereHas('organisation', function($query) {
                                           $query->where('type', 'parti_politique')
                                                 ->where('is_active', true);
                                       })
                                       ->with(['organisation' => function($query) {
                                           $query->select('id', 'nom', 'sigle', 'type');
                                       }])
                                       ->get();

        return [
            'unique' => $existingMemberships->isEmpty(),
            'existing_memberships' => $existingMemberships->map(function($adherent) {
                return [
                    'organisation_id' => $adherent->organisation->id,
                    'organisation_nom' => $adherent->organisation->nom,
                    'organisation_sigle' => $adherent->organisation->sigle,
                    'date_adhesion' => $adherent->date_adhesion,
                    'fonction' => $adherent->fonction
                ];
            })->toArray()
        ];
    }

    /**
     * Validation en lot de NIP (pour imports)
     */
    public function validateNipBatch(array $nipList)
    {
        $results = [];
        
        foreach ($nipList as $index => $nipData) {
            $nip = $nipData['nip'] ?? null;
            $nom = $nipData['nom'] ?? null;
            $prenom = $nipData['prenom'] ?? null;

            if (!$nip) {
                $results[$index] = [
                    'valid' => false,
                    'error' => 'NIP manquant',
                    'line' => $index + 1
                ];
                continue;
            }

            $validation = $this->validateNip($nip, $nom, $prenom);
            $validation['line'] = $index + 1;
            $results[$index] = $validation;
        }

        return [
            'results' => $results,
            'summary' => $this->generateBatchSummary($results)
        ];
    }

    /**
     * Génération du résumé de validation en lot
     */
    protected function generateBatchSummary(array $results)
    {
        $total = count($results);
        $valid = count(array_filter($results, fn($r) => $r['valid'] === true && !isset($r['warning'])));
        $warnings = count(array_filter($results, fn($r) => $r['valid'] === true && isset($r['warning'])));
        $errors = count(array_filter($results, fn($r) => $r['valid'] === false));

        return [
            'total' => $total,
            'valid' => $valid,
            'warnings' => $warnings,
            'errors' => $errors,
            'success_rate' => $total > 0 ? round(($valid / $total) * 100, 2) : 0
        ];
    }

    /**
     * Recherche de NIP par nom/prénom
     */
    public function searchNipByName($nom, $prenom, $dateNaissance = null)
    {
        $query = NipDatabase::where('nom', 'LIKE', "%{$nom}%")
                            ->where('prenom', 'LIKE', "%{$prenom}%")
                            ->where('statut', 'actif');

        if ($dateNaissance) {
            $query->where('date_naissance', $dateNaissance);
        }

        $results = $query->limit(10)->get();

        return [
            'found' => $results->isNotEmpty(),
            'count' => $results->count(),
            'results' => $results->map(function($nip) {
                return [
                    'nip' => $nip->nip,
                    'nom_complet' => $nip->nom_complet,
                    'date_naissance' => $nip->date_naissance,
                    'age' => $nip->age,
                    'lieu_naissance' => $nip->lieu_naissance
                ];
            })->toArray()
        ];
    }

    /**
     * Calcul de similarité entre deux chaînes
     */
    protected function calculateSimilarity($str1, $str2)
    {
        if (empty($str1) || empty($str2)) {
            return 0;
        }

        // Utiliser la distance de Levenshtein pour calculer la similarité
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            return 1;
        }

        $distance = levenshtein($str1, $str2);
        return 1 - ($distance / $maxLen);
    }

    /**
     * Suppression des accents pour comparaison
     */
    protected function removeAccents($string)
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    }

    /**
     * Validation API pour frontend
     */
    public function validateNipApi($nip)
    {
        $validation = $this->validateNip($nip);
        
        return response()->json([
            'valid' => $validation['valid'],
            'message' => $validation['error'] ?? $validation['message'] ?? 'Validation effectuée',
            'data' => $validation['nip_data'] ?? null,
            'warning' => $validation['warning'] ?? false,
            'requires_validation' => $validation['requires_validation'] ?? false,
            'existing_memberships' => $validation['existing_memberships'] ?? null
        ]);
    }
}