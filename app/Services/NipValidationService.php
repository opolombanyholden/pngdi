<?php

namespace App\Services;

use App\Models\NipDatabase;
use App\Models\Adherent;
use App\Models\Organisation;
use App\Models\AdherentAnomalie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class NipValidationService
{
    /**
     * ✅ MÉTHODE PRINCIPALE - Validation complète NIP
     * Utilisée par AdherentController
     * 
     * @param string $nip
     * @param string|null $nom
     * @param string|null $prenom
     * @return array
     */
    public function validateNip($nip, $nom = null, $prenom = null)
    {
        try {
            $result = [
                'valid' => false,
                'error' => null,
                'warning' => false,
                'message' => '',
                'nip_data' => null,
                'existing_memberships' => null,
                'requires_validation' => false,
                'source' => 'nip_validation'
            ];

            // 1. Vérification format NIP
            if (empty(trim($nip))) {
                $result['error'] = 'NIP requis';
                return $result;
            }

            $formatValidation = $this->validateNipFormat($nip);
            if (!$formatValidation['valid']) {
                $result['error'] = $formatValidation['error'];
                return $result;
            }

            // 2. Rechercher dans la base NIP centrale
            $nipRecord = NipDatabase::where('nip', trim($nip))->first();
            
            if ($nipRecord) {
                // 3. Vérification cohérence nom/prénom si fournis
                if ($nom || $prenom) {
                    $coherenceCheck = $this->checkNameCoherence($nipRecord, $nom, $prenom);
                    if (!$coherenceCheck['coherent']) {
                        $result['warning'] = true;
                        $result['message'] = $coherenceCheck['message'];
                    }
                }

                // 4. Récupérer données pour pré-remplissage
                $result['nip_data'] = [
                    'nom' => $nipRecord->nom,
                    'prenom' => $nipRecord->prenom,
                    'date_naissance' => $nipRecord->date_naissance->format('Y-m-d'),
                    'lieu_naissance' => $nipRecord->lieu_naissance,
                    'sexe' => $nipRecord->sexe,
                    'telephone' => $nipRecord->telephone,
                    'email' => $nipRecord->email,
                    'age' => $nipRecord->age
                ];
            }

            // 5. Vérifier appartenances multiples (partis politiques)
            $membershipCheck = $this->checkUniquenessForPoliticalParties($nip);
            if (!empty($membershipCheck)) {
                $result['warning'] = true;
                $result['requires_validation'] = true;
                $result['existing_memberships'] = $membershipCheck;
                $result['message'] = 'Appartenance multiple détectée pour les partis politiques';
            }

            $result['valid'] = true;
            if (!$result['message']) {
                $result['message'] = 'NIP valide';
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur validation NIP', [
                'nip' => $nip,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'error' => 'Erreur lors de la validation du NIP',
                'source' => 'nip_validation'
            ];
        }
    }

    /**
     * ✅ API VALIDATION NIP TEMPS RÉEL (NON-BLOQUANTE)
     * Route: POST /api/validate-nip
     */
    public function validateNipApi(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nip' => 'nullable|string|max:20', // ✅ NULLABLE pour règle non-bloquante
                'nom' => 'nullable|string|max:100',
                'prenom' => 'nullable|string|max:100',
                'date_naissance' => 'nullable|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'organisation_type' => 'nullable|string|in:association,ong,parti_politique,confession_religieuse'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Données invalides : ' . $validator->errors()->first()
                ], 400);
            }

            $nip = trim($request->nip);
            $organisationType = $request->organisation_type ?? 'association';

            // === DÉTECTION COMPLÈTE DES ANOMALIES ===
            $anomalies = $this->detectAllNipAnomalies($nip, $organisationType);
            
            // === VALIDATION BASE NIP + IDENTITÉ ===
            $validation = $this->validateNipWithIdentity($nip, [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'date_naissance' => $request->date_naissance,
                'lieu_naissance' => $request->lieu_naissance
            ]);

            // === AJOUTER ANOMALIES DE COHÉRENCE ===
            if (isset($validation['identity_anomalies'])) {
                $anomalies = array_merge($anomalies, $validation['identity_anomalies']);
            }

            // === RÉPONSE TOUJOURS POSITIVE (NON-BLOQUANT) ===
            $hasAnomaliesCritiques = $this->hasAnomaliesCritiques($anomalies);

            // === FORMATER LA RÉPONSE API (INFORMATIVE SEULEMENT) ===
            $response = [
                'success' => true,
                'valid' => true, // ✅ TOUJOURS VALIDE (non-bloquant)
                'available' => true, // ✅ TOUJOURS DISPONIBLE
                'format_valid' => $validation['valid'], // Info sur le format seulement
                'message' => $this->formatApiMessageInformatif($validation, $anomalies),
                'anomalies' => $anomalies,
                'severity' => $this->getHighestSeverity($anomalies),
                'info_only' => true, // ✅ INDICATEUR INFORMATIF
                'will_be_saved' => true, // ✅ CONFIRME QUE L'ADHÉRENT SERA ENREGISTRÉ
                'statut_suggestion' => $hasAnomaliesCritiques ? 'en_attente' : 'valide'
            ];

            // === AJOUTER DONNÉES NIP SI DISPONIBLES ===
            if (isset($validation['nip_data'])) {
                $response['data'] = $validation['nip_data'];
                
                // Ajouter l'âge calculé
                if (isset($validation['nip_data']['date_naissance'])) {
                    $response['age'] = Carbon::parse($validation['nip_data']['date_naissance'])->age;
                }
            } else {
                // Calculer l'âge depuis le NIP si format valide
                $age = $this->extractAgeFromNip($nip);
                if ($age !== null) {
                    $response['age'] = $age;
                }
            }

            // === AJOUTER ORGANISATIONS EXISTANTES ===
            if (isset($validation['existing_memberships'])) {
                $response['existing_memberships'] = $validation['existing_memberships'];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Erreur API validation NIP', [
                'nip' => $request->nip ?? 'non_fourni',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Erreur lors de la validation du NIP'
            ], 500);
        }
    }

    /**
     * ✅ VALIDATION FORMAT NIP XX-QQQQ-YYYYMMDD
     */
    public function validateNipFormat($nip)
    {
        $nip = trim($nip);
        
        if (empty($nip)) {
            return [
                'valid' => false,
                'error' => 'NIP requis'
            ];
        }

        // Pattern: XX-QQQQ-YYYYMMDD
        if (!preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', $nip)) {
            return [
                'valid' => false,
                'error' => 'Format NIP invalide. Format attendu: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)'
            ];
        }

        // Vérifier que la date est valide
        $datePart = substr($nip, -8); // Derniers 8 chiffres
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);

        if (!checkdate($month, $day, $year)) {
            return [
                'valid' => false,
                'error' => 'Date de naissance invalide dans le NIP'
            ];
        }

        // Vérifier l'unicité du NIP
        $existing = NipDatabase::where('nip', $nip)->first();
        if ($existing) {
            // NIP existe mais format valide
            return [
                'valid' => true,
                'warning' => true,
                'message' => 'NIP trouvé dans la base centrale'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Format NIP valide'
        ];
    }

    /**
     * ✅ VALIDATION NIP + IDENTITÉ COMPLÈTE
     * Vérification NIP + cohérence identité (nom, prénom, date naissance, lieu)
     */
    public function validateNipWithIdentity($nip, $identityData)
    {
        try {
            $result = [
                'valid' => false,
                'error' => null,
                'warning' => false,
                'message' => '',
                'nip_data' => null,
                'existing_memberships' => null,
                'requires_validation' => false,
                'identity_anomalies' => [],
                'source' => 'nip_validation'
            ];

            // 1. Vérification format NIP
            if (empty(trim($nip))) {
                $result['error'] = 'NIP requis';
                return $result;
            }

            $formatValidation = $this->validateNipFormat($nip);
            if (!$formatValidation['valid']) {
                $result['error'] = $formatValidation['error'];
                return $result;
            }

            // 2. Rechercher dans la base NIP centrale
            $nipRecord = NipDatabase::where('nip', trim($nip))->first();
            
            if ($nipRecord) {
                // 3. Vérification cohérence COMPLÈTE identité
                $coherenceCheck = $this->checkCompleteIdentityCoherence($nipRecord, $identityData);
                if (!empty($coherenceCheck['anomalies'])) {
                    $result['identity_anomalies'] = $coherenceCheck['anomalies'];
                    $result['warning'] = true;
                    $result['message'] = $coherenceCheck['summary'];
                }

                // 4. Récupérer données pour pré-remplissage
                $result['nip_data'] = [
                    'nom' => $nipRecord->nom,
                    'prenom' => $nipRecord->prenom,
                    'date_naissance' => $nipRecord->date_naissance->format('Y-m-d'),
                    'lieu_naissance' => $nipRecord->lieu_naissance,
                    'sexe' => $nipRecord->sexe,
                    'telephone' => $nipRecord->telephone,
                    'email' => $nipRecord->email,
                    'age' => $nipRecord->age
                ];
            }

            // 5. Vérifier appartenances multiples (partis politiques)
            $membershipCheck = $this->checkUniquenessForPoliticalParties($nip);
            if (!empty($membershipCheck)) {
                $result['warning'] = true;
                $result['requires_validation'] = true;
                $result['existing_memberships'] = $membershipCheck;
                if (!$result['message']) {
                    $result['message'] = 'Appartenance multiple détectée pour les partis politiques';
                }
            }

            $result['valid'] = true;
            if (!$result['message']) {
                $result['message'] = 'NIP valide';
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur validation NIP avec identité', [
                'nip' => $nip,
                'identity' => $identityData,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'error' => 'Erreur lors de la validation du NIP',
                'source' => 'nip_validation'
            ];
        }
    }

    /**
     * ✅ VÉRIFICATION COHÉRENCE IDENTITÉ COMPLÈTE
     * Vérifie nom, prénom, date naissance, lieu naissance
     */
    public function checkCompleteIdentityCoherence($nipRecord, $identityData)
    {
        if (!$nipRecord) {
            return ['anomalies' => [], 'summary' => ''];
        }

        $anomalies = [];
        $issues = [];

        // 1. Vérification NOM
        if (!empty($identityData['nom'])) {
            $nomNip = $this->normalizeString($nipRecord->nom);
            $nomSaisi = $this->normalizeString($identityData['nom']);
            
            if ($nomNip !== $nomSaisi) {
                $anomalies[] = [
                    'code' => 'NOM_INCOHERENT',
                    'type' => 'majeure',
                    'message' => "Nom incohérent - Base NIP: '{$nipRecord->nom}', Saisi: '{$identityData['nom']}'",
                    'field' => 'nom',
                    'base_value' => $nipRecord->nom,
                    'saisie_value' => $identityData['nom']
                ];
                $issues[] = "Nom différent";
            }
        }

        // 2. Vérification PRÉNOM
        if (!empty($identityData['prenom'])) {
            $prenomNip = $this->normalizeString($nipRecord->prenom);
            $prenomSaisi = $this->normalizeString($identityData['prenom']);
            
            if ($prenomNip !== $prenomSaisi) {
                $anomalies[] = [
                    'code' => 'PRENOM_INCOHERENT',
                    'type' => 'majeure',
                    'message' => "Prénom incohérent - Base NIP: '{$nipRecord->prenom}', Saisi: '{$identityData['prenom']}'",
                    'field' => 'prenom',
                    'base_value' => $nipRecord->prenom,
                    'saisie_value' => $identityData['prenom']
                ];
                $issues[] = "Prénom différent";
            }
        }

        // 3. Vérification DATE DE NAISSANCE
        if (!empty($identityData['date_naissance'])) {
            try {
                $dateNipFormatted = $nipRecord->date_naissance->format('Y-m-d');
                $dateSaisie = Carbon::parse($identityData['date_naissance'])->format('Y-m-d');
                
                if ($dateNipFormatted !== $dateSaisie) {
                    $anomalies[] = [
                        'code' => 'DATE_NAISSANCE_INCOHERENTE',
                        'type' => 'majeure',
                        'message' => "Date de naissance incohérente - Base NIP: '{$dateNipFormatted}', Saisie: '{$dateSaisie}'",
                        'field' => 'date_naissance',
                        'base_value' => $dateNipFormatted,
                        'saisie_value' => $dateSaisie
                    ];
                    $issues[] = "Date de naissance différente";
                }
            } catch (\Exception $e) {
                $anomalies[] = [
                    'code' => 'DATE_NAISSANCE_FORMAT_INVALIDE',
                    'type' => 'majeure',
                    'message' => "Format de date de naissance invalide: '{$identityData['date_naissance']}'",
                    'field' => 'date_naissance',
                    'saisie_value' => $identityData['date_naissance']
                ];
                $issues[] = "Format date invalide";
            }
        }

        // 4. Vérification LIEU DE NAISSANCE
        if (!empty($identityData['lieu_naissance']) && !empty($nipRecord->lieu_naissance)) {
            $lieuNip = $this->normalizeString($nipRecord->lieu_naissance);
            $lieuSaisi = $this->normalizeString($identityData['lieu_naissance']);
            
            if ($lieuNip !== $lieuSaisi) {
                $anomalies[] = [
                    'code' => 'LIEU_NAISSANCE_INCOHERENT',
                    'type' => 'majeure',
                    'message' => "Lieu de naissance incohérent - Base NIP: '{$nipRecord->lieu_naissance}', Saisi: '{$identityData['lieu_naissance']}'",
                    'field' => 'lieu_naissance',
                    'base_value' => $nipRecord->lieu_naissance,
                    'saisie_value' => $identityData['lieu_naissance']
                ];
                $issues[] = "Lieu de naissance différent";
            }
        }

        $summary = '';
        if (!empty($issues)) {
            $summary = 'Incohérences identité détectées: ' . implode(', ', $issues);
        }

        return [
            'anomalies' => $anomalies,
            'summary' => $summary,
            'coherent' => empty($anomalies)
        ];
    }

    /**
     * ✅ NORMALISATION CHAÎNE POUR COMPARAISON
     * Supprime accents, espaces, casse pour comparaison robuste
     */
    private function normalizeString($string)
    {
        if (empty($string)) return '';
        
        // Convertir en minuscules
        $normalized = strtolower(trim($string));
        
        // Supprimer les accents (conversion basique)
        $normalized = str_replace(
            ['à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ'],
            ['a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y'],
            $normalized
        );
        
        // Supprimer caractères spéciaux et espaces multiples
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        return trim($normalized);
    }

    /**
     * ✅ VÉRIFICATION UNICITÉ PARTIS POLITIQUES
     */
    public function checkUniquenessForPoliticalParties($nip)
    {
        $existingMemberships = Adherent::where('nip', trim($nip))
            ->where('is_active', true)
            ->where('statut_validation', 'valide')
            ->with('organisation')
            ->get();

        if ($existingMemberships->isEmpty()) {
            return [];
        }

        // Filtrer seulement les partis politiques
        $politicalParties = $existingMemberships->filter(function($adherent) {
            return $adherent->organisation->type === 'parti_politique';
        });

        if ($politicalParties->isEmpty()) {
            return [];
        }

        return $politicalParties->map(function($adherent) {
            return [
                'organisation_id' => $adherent->organisation->id,
                'nom' => $adherent->organisation->nom,
                'type' => $adherent->organisation->type,
                'statut' => $adherent->organisation->statut,
                'date_adhesion' => $adherent->date_adhesion
            ];
        })->toArray();
    }

    /**
     * ✅ VALIDATION EN LOT
     */
    public function validateNipBatch(array $nips)
    {
        $results = [];
        
        foreach ($nips as $index => $nip) {
            $results[$index] = $this->validateNip($nip);
        }

        return [
            'success' => true,
            'total' => count($nips),
            'results' => $results,
            'summary' => [
                'valid' => count(array_filter($results, function($r) { return $r['valid']; })),
                'invalid' => count(array_filter($results, function($r) { return !$r['valid']; })),
                'warnings' => count(array_filter($results, function($r) { return $r['warning'] ?? false; }))
            ]
        ];
    }

    /**
     * ✅ DÉTECTION COMPLÈTE ANOMALIES NIP
     */
    private function detectAllNipAnomalies($nip, $organisationType)
    {
        $anomalies = [];

        // 1. NIP absent/vide
        if (empty(trim($nip))) {
            $anomalies[] = [
                'code' => 'NIP_ABSENT',
                'type' => 'critique',
                'message' => 'NIP absent ou vide',
                'field' => 'nip',
                'value' => $nip
            ];
            return $anomalies;
        }

        // 2. Format NIP incorrect
        $formatCheck = $this->validateNipFormat($nip);
        if (!$formatCheck['valid']) {
            $anomalies[] = [
                'code' => 'NIP_FORMAT_INVALIDE',
                'type' => 'majeure',
                'message' => $formatCheck['error'],
                'field' => 'nip',
                'value' => $nip,
                'expected_format' => 'XX-QQQQ-YYYYMMDD'
            ];
            return $anomalies; // Arrêter si format invalide
        }

        // 3. Âge mineur/suspect
        $age = $this->extractAgeFromNip($nip);
        if ($age !== null) {
            if ($age < 18) {
                $anomalies[] = [
                    'code' => 'AGE_MINEUR',
                    'type' => 'critique',
                    'message' => "Personne mineure détectée (âge: {$age} ans)",
                    'field' => 'age',
                    'value' => $age
                ];
            } elseif ($age > 100) {
                $anomalies[] = [
                    'code' => 'AGE_SUSPECT',
                    'type' => 'majeure',
                    'message' => "Âge suspect détecté (âge: {$age} ans)",
                    'field' => 'age',
                    'value' => $age
                ];
            }
        }

        // 4. Double appartenance parti politique
        if ($organisationType === 'parti_politique') {
            $existingMemberships = $this->checkUniquenessForPoliticalParties($nip);
            if (!empty($existingMemberships)) {
                $anomalies[] = [
                    'code' => 'DOUBLE_APPARTENANCE_PARTI',
                    'type' => 'critique',
                    'message' => 'Appartenance multiple à des partis politiques détectée',
                    'field' => 'appartenance',
                    'value' => $existingMemberships
                ];
            }
        }

        // 5. Profession interdite (si applicable)
        // Cette vérification sera faite côté AdherentController avec les données complètes

        return $anomalies;
    }

    /**
     * ✅ VÉRIFIER PROFESSION INTERDITE
     */
    public function isProfessionInterdite($profession, $organisationType)
    {
        if (empty($profession) || $organisationType !== 'parti_politique') {
            return false;
        }

        try {
            $professionsPath = storage_path('app/config/professions_interdites.txt');
            
            if (!file_exists($professionsPath)) {
                Log::warning('Fichier professions interdites non trouvé: ' . $professionsPath);
                return false;
            }

            $professionsInterdites = file($professionsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            // Nettoyer et normaliser
            $professionsInterdites = array_map(function($p) {
                return strtolower(trim(str_replace('#', '', $p)));
            }, $professionsInterdites);
            
            // Filtrer les commentaires
            $professionsInterdites = array_filter($professionsInterdites, function($p) {
                return !empty($p) && substr($p, 0, 1) !== '#';
            });

            $professionNormalisee = strtolower(trim($profession));
            
            return in_array($professionNormalisee, $professionsInterdites);

        } catch (\Exception $e) {
            Log::error('Erreur vérification profession interdite', [
                'profession' => $profession,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ✅ EXTRAIRE ÂGE DEPUIS NIP
     */
    public function extractAgeFromNip($nip)
    {
        if (!$this->validateNipFormat($nip)['valid']) {
            return null;
        }
        
        $datePart = substr($nip, -8); // Derniers 8 chiffres: YYYYMMDD
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        try {
            $birthDate = Carbon::createFromDate($year, $month, $day);
            return $birthDate->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * ✅ VÉRIFIER ANOMALIES CRITIQUES
     */
    private function hasAnomaliesCritiques($anomalies)
    {
        return !empty(array_filter($anomalies, function($a) {
            return $a['type'] === 'critique';
        }));
    }

    /**
     * ✅ NIVEAU SÉVÉRITÉ LE PLUS ÉLEVÉ
     */
    private function getHighestSeverity($anomalies)
    {
        if (empty($anomalies)) return null;

        foreach (['critique', 'majeure', 'mineure'] as $severity) {
            foreach ($anomalies as $anomalie) {
                if ($anomalie['type'] === $severity) {
                    return $severity;
                }
            }
        }

        return 'mineure';
    }

    /**
     * ✅ FORMATER MESSAGE API INFORMATIF
     */
    private function formatApiMessageInformatif($validation, $anomalies)
    {
        if (!empty($anomalies)) {
            $critiques = array_filter($anomalies, function($a) {
                return $a['type'] === 'critique';
            });
            
            $majeures = array_filter($anomalies, function($a) {
                return $a['type'] === 'majeure';
            });
            
            if (!empty($critiques)) {
                return 'ℹ️ Anomalies critiques détectées - L\'adhérent sera enregistré avec statut "en attente"';
            } elseif (!empty($majeures)) {
                return 'ℹ️ Anomalies majeures détectées - L\'adhérent sera enregistré avec suivi';
            } else {
                return 'ℹ️ Anomalies mineures détectées - Correction recommandée ultérieurement';
            }
        }

        if (isset($validation['warning']) && $validation['warning']) {
            return 'ℹ️ ' . ($validation['message'] ?? 'NIP valide avec avertissement - Enregistrement possible');
        }

        return '✅ NIP valide - Enregistrement normal';
    }

    /**
     * ✅ API VÉRIFICATION SIMPLE NIP
     * Route: POST /api/verify-nip
     */
    public function verifyNipApi(Request $request)
    {
        try {
            $nip = $request->nip;
            
            if (empty($nip)) {
                return response()->json([
                    'success' => false,
                    'found' => false,
                    'message' => 'NIP requis'
                ]);
            }

            // Rechercher dans la base NIP
            $nipRecord = NipDatabase::where('nip', $nip)->first();
            
            if ($nipRecord) {
                return response()->json([
                    'success' => true,
                    'found' => true,
                    'message' => 'NIP trouvé dans la base centrale',
                    'data' => [
                        'nom' => $nipRecord->nom,
                        'prenom' => $nipRecord->prenom,
                        'date_naissance' => $nipRecord->date_naissance->format('Y-m-d'),
                        'lieu_naissance' => $nipRecord->lieu_naissance,
                        'sexe' => $nipRecord->sexe,
                        'telephone' => $nipRecord->telephone,
                        'email' => $nipRecord->email,
                        'age' => $nipRecord->age,
                        'statut' => $nipRecord->statut
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'found' => false,
                    'message' => 'NIP non trouvé dans la base centrale',
                    'suggestions' => $this->generateNipSuggestions($nip)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur API vérification NIP', [
                'nip' => $request->nip ?? 'non_fourni',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'found' => false,
                'message' => 'Erreur lors de la vérification'
            ], 500);
        }
    }

    /**
     * ✅ GÉNÉRER SUGGESTIONS NIP
     */
    private function generateNipSuggestions($invalidNip)
    {
        $suggestions = [];
        
        // Suggestion de format si proche du bon format
        if (strlen($invalidNip) >= 10) {
            $cleaned = preg_replace('/[^A-Z0-9]/', '', strtoupper($invalidNip));
            if (strlen($cleaned) >= 14) {
                $formatted = substr($cleaned, 0, 2) . '-' . 
                           substr($cleaned, 2, 4) . '-' . 
                           substr($cleaned, 6, 8);
                $suggestions[] = [
                    'type' => 'format_correction',
                    'value' => $formatted,
                    'message' => 'Format suggéré basé sur votre saisie'
                ];
            }
        }
        
        return $suggestions;
    }
}