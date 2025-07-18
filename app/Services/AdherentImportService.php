<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Adherent;
use App\Models\AdherentImport;
use App\Models\AdherentHistory;
use App\Services\AnomalieService; // ✅ VÉRIFIER
use App\Services\NipValidationService; // ✅ VÉRIFIER
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // ✅ AJOUTER SI MANQUANT
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;
use League\Csv\Writer;
use Exception;
use Carbon\Carbon; // ✅ AJOUTER SI MANQUANT

class AdherentImportService
{
    protected $anomalieService; // ✅ NOUVEAU
    protected $nipValidationService; // ✅ NOUVEAU

    
    public function __construct(AnomalieService $anomalieService, NipValidationService $nipValidationService) // ✅ NOUVEAU
    {
        // ✅ RÉSOLUTION VIA CONTENEUR AVEC VÉRIFICATION
    try {
        $this->anomalieService = app(\App\Services\AnomalieService::class);
        } catch (\Exception $e) {
            $this->anomalieService = null;
            \Log::warning('AnomalieService non disponible : ' . $e->getMessage());
        }
        
        try {
            $this->nipValidationService = app(\App\Services\NipValidationService::class);
        } catch (\Exception $e) {
            $this->nipValidationService = null;
            \Log::warning('NipValidationService non disponible : ' . $e->getMessage());
        }
    }



    protected $requiredColumns = [
        'nip',
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        'profession',
        'adresse',
        'province',
        'departement',
        'telephone',
        'email'
    ];
    
    protected $optionalColumns = [
        'canton',
        'prefecture',
        'sous_prefecture',
        'date_adhesion',
        'numero_carte',
        'is_fondateur'
    ];
    
    /**
     * ✅ MÉTHODE AMÉLIORÉE : Import avec enregistrement d'anomalies
     */
    public function importFromCsv(Organisation $organisation, UploadedFile $file): array
    {
        // Créer l'enregistrement d'import
        $import = AdherentImport::create([
            'organisation_id' => $organisation->id,
            'imported_by' => auth()->id(),
            'nom_fichier' => $file->getClientOriginalName(),
            'taille_fichier' => $file->getSize(),
            'type_fichier' => $file->getClientMimeType(),
            'statut' => 'en_cours',
            'methode_traitement' => 'standard',
            'date_debut' => now()
        ]);

        try {
            // Marquer l'import en cours
            $import->marquerEnCours();

            // Sauvegarder le fichier
            $path = $file->store('imports/adherents/' . $organisation->id);
            $import->update(['chemin_fichier' => $path]);

            // Parser le fichier
            $parsedData = $this->parseFile($file, $path);

            // Mise à jour du nombre total de lignes
            $import->update(['total_lignes' => count($parsedData)]);

            // ✅ NOUVEAU : Traitement avec gestion d'anomalies
            $result = $this->processDataWithAnomalies($organisation, $parsedData, $import);

            // Finaliser l'import
            $import->marquerTermine([
                'lignes_traitees' => $result['total_processed'],
                'lignes_importees' => $result['imported_count'],
                'lignes_erreur' => $result['error_count'],
                'lignes_anomalies' => $result['anomaly_count'],
                'rapport_import' => $result['import_report'],
                'rapport_anomalies' => $result['anomaly_report']
            ]);

            Log::info('Import terminé avec succès', [
                'import_id' => $import->id,
                'organisation_id' => $organisation->id,
                'stats' => $result
            ]);

            return [
                'success' => true,
                'import' => $import,
                'summary' => [
                    'total' => $result['total_processed'],
                    'imported' => $result['imported_count'],
                    'errors' => $result['error_count'],
                    'anomalies' => $result['anomaly_count']
                ],
                'details' => $result
            ];

        } catch (\Exception $e) {
            $import->marquerEchec($e->getMessage());
            
            Log::error('Erreur lors de l\'import', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Traitement avec gestion d'anomalies
     */
    protected function processDataWithAnomalies(Organisation $organisation, array $data, AdherentImport $import)
    {
        $results = [
            'total_processed' => 0,
            'imported_count' => 0,
            'error_count' => 0,
            'anomaly_count' => 0,
            'import_report' => [],
            'anomaly_report' => [],
            'anomalies_created' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($data as $lineIndex => $row) {
                $results['total_processed']++;
                $lineNumber = $lineIndex + 2; // +2 car ligne 1 = headers

                try {
                    // ✅ VALIDATION ET DÉTECTION D'ANOMALIES
                    $validationResult = $this->validateAndDetectAnomalies($row, $organisation, $lineNumber);

                    // ✅ RÈGLE MÉTIER SGLP : Enregistrer MÊME avec anomalies
                    $adherent = $this->createAdherentWithAnomalies(
                        $organisation,
                        $validationResult['cleaned_data'],
                        $validationResult['anomalies'],
                        $lineNumber
                    );

                    if ($adherent) {
                        $results['imported_count']++;

                        // ✅ CRÉER LES ENREGISTREMENTS D'ANOMALIES
                        if (!empty($validationResult['anomalies'])) {
                            $this->createAnomalieRecords($adherent, $validationResult['anomalies'], $organisation->id, $lineNumber);
                            $results['anomaly_count']++;
                            $results['anomalies_created'] = array_merge($results['anomalies_created'], $validationResult['anomalies']);
                        }

                        $results['import_report'][] = [
                            'line' => $lineNumber,
                            'status' => 'success',
                            'adherent_id' => $adherent->id,
                            'anomalies_count' => count($validationResult['anomalies']),
                            'nip' => $adherent->nip,
                            'nom_complet' => $adherent->prenom . ' ' . $adherent->nom
                        ];
                    }

                } catch (\Exception $e) {
                    $results['error_count']++;
                    $results['import_report'][] = [
                        'line' => $lineNumber,
                        'status' => 'error',
                        'error' => $e->getMessage(),
                        'data' => $row
                    ];

                    Log::warning("Erreur ligne {$lineNumber}", [
                        'error' => $e->getMessage(),
                        'data' => $row
                    ]);
                }
            }

            // ✅ GÉNÉRER LE RAPPORT D'ANOMALIES
            $results['anomaly_report'] = $this->generateAnomalyReport($results['anomalies_created']);

            DB::commit();

            Log::info('Traitement données terminé', [
                'organisation_id' => $organisation->id,
                'import_id' => $import->id,
                'total_processed' => $results['total_processed'],
                'imported' => $results['imported_count'],
                'anomalies' => $results['anomaly_count'],
                'errors' => $results['error_count']
            ]);

            return $results;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur traitement données', [
                'error' => $e->getMessage(),
                'organisation_id' => $organisation->id
            ]);
            throw $e;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Validation et détection d'anomalies
     */
    public function validateAndDetectAnomalies(array $row, Organisation $organisation, int $lineNumber)
    {
        $anomalies = [];
        $cleanedData = [];

        // ✅ NETTOYAGE ET VALIDATION DES CHAMPS OBLIGATOIRES

        // NIP
        $nip = trim($row['nip'] ?? '');
        if (empty($nip)) {
            $anomalies[] = [
                'type' => 'critique',
                'champ' => 'nip',
                'message' => 'NIP manquant',
                'valeur_erronee' => $nip
            ];
            $nip = $this->generateTemporaryNip(); // Générer un NIP temporaire
        } else {
            // Valider le format NIP via le service
            $nipValidation = $this->nipValidationService->validateNipFormat($nip);
            if (!$nipValidation['valid']) {
                $anomalies[] = [
                    'type' => 'majeure',
                    'champ' => 'nip',
                    'message' => 'Format NIP invalide (attendu: XX-QQQQ-YYYYMMDD)',
                    'valeur_erronee' => $nip
                ];
            } else {
                // Vérifier l'âge si NIP valide
                $age = $this->nipValidationService->extractAgeFromNip($nip);
                if ($age !== null && $age < 18) {
                    $anomalies[] = [
                        'type' => 'critique',
                        'champ' => 'nip',
                        'message' => "Âge mineur détecté ({$age} ans)",
                        'valeur_erronee' => $age
                    ];
                } elseif ($age !== null && $age > 100) {
                    $anomalies[] = [
                        'type' => 'majeure',
                        'champ' => 'nip',
                        'message' => "Âge suspect détecté ({$age} ans)",
                        'valeur_erronee' => $age
                    ];
                }
            }
        }
        $cleanedData['nip'] = $nip;

        // Nom
        $nom = trim($row['nom'] ?? '');
        if (empty($nom)) {
            $anomalies[] = [
                'type' => 'critique',
                'champ' => 'nom',
                'message' => 'Nom manquant',
                'valeur_erronee' => $nom
            ];
        }
        $cleanedData['nom'] = strtoupper($nom);

        // Prénom
        $prenom = trim($row['prenom'] ?? '');
        if (empty($prenom)) {
            $anomalies[] = [
                'type' => 'critique',
                'champ' => 'prenom',
                'message' => 'Prénom manquant',
                'valeur_erronee' => $prenom
            ];
        }
        $cleanedData['prenom'] = $prenom;

        // ✅ VALIDATION CHAMPS OPTIONNELS AVEC ANOMALIES

        // Téléphone
        $telephone = trim($row['telephone'] ?? '');
        if (!empty($telephone) && !$this->validatePhoneFormat($telephone)) {
            $anomalies[] = [
                'type' => 'mineure',
                'champ' => 'telephone',
                'message' => 'Format de téléphone invalide',
                'valeur_erronee' => $telephone
            ];
        }
        $cleanedData['telephone'] = $telephone;

        // Email
        $email = trim($row['email'] ?? '');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $anomalies[] = [
                'type' => 'mineure',
                'champ' => 'email',
                'message' => 'Format email invalide',
                'valeur_erronee' => $email
            ];
        }
        $cleanedData['email'] = $email;

        // Profession
        $profession = trim($row['profession'] ?? '');
        if (empty($profession)) {
            $anomalies[] = [
                'type' => 'mineure',
                'champ' => 'profession',
                'message' => 'Profession non renseignée',
                'valeur_erronee' => null
            ];
        } elseif ($this->isProfessionInterdite($profession, $organisation->type)) {
            $anomalies[] = [
                'type' => 'critique',
                'champ' => 'profession',
                'message' => "Profession interdite: {$profession}",
                'valeur_erronee' => $profession
            ];
        }
        $cleanedData['profession'] = $profession;

        // Date de naissance
        $dateNaissance = trim($row['date_naissance'] ?? '');
        if (!empty($dateNaissance)) {
            try {
                $cleanedData['date_naissance'] = \Carbon\Carbon::parse($dateNaissance)->format('Y-m-d');
            } catch (\Exception $e) {
                $anomalies[] = [
                    'type' => 'majeure',
                    'champ' => 'date_naissance',
                    'message' => 'Format de date invalide',
                    'valeur_erronee' => $dateNaissance
                ];
                $cleanedData['date_naissance'] = null;
            }
        } else {
            $anomalies[] = [
                'type' => 'majeure',
                'champ' => 'date_naissance',
                'message' => 'Date de naissance manquante',
                'valeur_erronee' => null
            ];
            $cleanedData['date_naissance'] = null;
        }

        // ✅ VÉRIFICATION APPARTENANCE MULTIPLE POUR PARTIS POLITIQUES
        if ($organisation->type === 'parti_politique') {
            $existingMembership = $this->checkExistingPoliticalMembership($nip, $organisation->id);
            if ($existingMembership) {
                $anomalies[] = [
                    'type' => 'critique',
                    'champ' => 'appartenance_multiple',
                    'message' => 'Appartenance multiple à des partis politiques détectée',
                    'valeur_erronee' => $existingMembership
                ];
            }
        }

        // Compléter les autres champs
        $cleanedData = array_merge($cleanedData, [
            'lieu_naissance' => trim($row['lieu_naissance'] ?? ''),
            'sexe' => strtoupper(trim($row['sexe'] ?? '')),
            'nationalite' => trim($row['nationalite'] ?? 'Gabonaise'),
            'adresse' => trim($row['adresse'] ?? ''),
            'province' => trim($row['province'] ?? ''),
            'departement' => trim($row['departement'] ?? ''),
            'canton' => trim($row['canton'] ?? ''),
            'prefecture' => trim($row['prefecture'] ?? ''),
            'sous_prefecture' => trim($row['sous_prefecture'] ?? ''),
            'date_adhesion' => now()->format('Y-m-d'),
            'numero_carte' => trim($row['numero_carte'] ?? ''),
            'is_fondateur' => $this->parseBooleanValue($row['is_fondateur'] ?? 'non')
        ]);

        return [
            'cleaned_data' => $cleanedData,
            'anomalies' => $anomalies
        ];
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Créer adhérent avec anomalies
     */
    public function createAdherentWithAnomalies(Organisation $organisation, array $data, array $anomalies, int $lineNumber)
    {
        // Déterminer le statut selon les anomalies
        $hasCriticalAnomalies = collect($anomalies)->contains('type', 'critique');

        $adherent = Adherent::create([
            'organisation_id' => $organisation->id,
            'nip' => $data['nip'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_naissance' => $data['date_naissance'],
            'lieu_naissance' => $data['lieu_naissance'],
            'sexe' => $data['sexe'],
            'nationalite' => $data['nationalite'],
            'profession' => $data['profession'],
            'adresse' => $data['adresse'],
            'province' => $data['province'],
            'departement' => $data['departement'],
            'telephone' => $data['telephone'],
            'email' => $data['email'],
            'canton' => $data['canton'],
            'prefecture' => $data['prefecture'],
            'sous_prefecture' => $data['sous_prefecture'],
            'date_adhesion' => $data['date_adhesion'],
            'numero_carte' => $data['numero_carte'],
            'is_fondateur' => $data['is_fondateur'],

            // ✅ NOUVEAUX CHAMPS POUR ANOMALIES
            'statut_validation' => $hasCriticalAnomalies ? 'en_attente' : 'valide',
            'is_active' => !$hasCriticalAnomalies,
            'has_anomalies' => !empty($anomalies),
            'anomalies_severity' => $this->getHighestSeverity($anomalies),
            'anomalies_data' => !empty($anomalies) ? json_encode($anomalies) : null,
            'source' => 'import_csv',
            'ligne_import' => $lineNumber
        ]);

        return $adherent;
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Créer les enregistrements d'anomalies
     */
    public function createAnomalieRecords(Adherent $adherent, array $anomalies, int $organisationId, int $lineNumber)
    {
        $anomaliesData = [];

        foreach ($anomalies as $anomalie) {
            $anomaliesData[] = [
                'adherent_id' => $adherent->id,
                'organisation_id' => $organisationId,
                'ligne_import' => $lineNumber,
                'type_anomalie' => $anomalie['type'],
                'champ_concerne' => $anomalie['champ'],
                'message_anomalie' => $anomalie['message'],
                'valeur_erronee' => json_encode($anomalie['valeur_erronee']),
                'statut' => 'en_attente',
                'detectee_le' => now(),
                'priorite' => $this->getAnomalyPriority($anomalie['type']),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Utiliser le service d'anomalies pour créer les enregistrements
        if (!empty($anomaliesData)) {
            $this->anomalieService->creerAnomaliesEnLot($anomaliesData);
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Générer rapport d'anomalies
     */
    public function generateAnomalyReport(array $anomalies)
    {
        $report = [
            'total_anomalies' => count($anomalies),
            'critiques' => 0,
            'majeures' => 0,
            'mineures' => 0,
            'champs_les_plus_concernes' => [],
            'date_generation' => now()->format('Y-m-d H:i:s')
        ];

        $champsStats = [];

        foreach ($anomalies as $anomalie) {
            // Compter par type
            $report[$anomalie['type'] . 's']++;

            // Compter par champ
            $champ = $anomalie['champ'];
            if (!isset($champsStats[$champ])) {
                $champsStats[$champ] = 0;
            }
            $champsStats[$champ]++;
        }

        // Trier les champs par nombre d'anomalies
        arsort($champsStats);
        $report['champs_les_plus_concernes'] = $champsStats;

        return $report;
    }

    /**
     * ✅ MÉTHODES UTILITAIRES
     */
    private function parseFile(UploadedFile $file, string $path)
    {
        $csv = Reader::createFromPath(Storage::path($path), 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');

        $headers = $csv->getHeader();
        $missingColumns = $this->validateHeaders($headers);

        if (!empty($missingColumns)) {
            throw new Exception('Colonnes manquantes : ' . implode(', ', $missingColumns));
        }

        return iterator_to_array($csv->getRecords());
    }

    private function validateHeaders(array $headers)
    {
        $missingColumns = [];
        foreach ($this->requiredColumns as $required) {
            if (!in_array($required, $headers)) {
                $missingColumns[] = $required;
            }
        }
        return $missingColumns;
    }

    private function generateTemporaryNip()
    {
        return 'TMP-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT) . '-' . date('Ymd') . mt_rand(10, 99);
    }

    private function validatePhoneFormat($phone)
    {
        return preg_match('/^(\+241|241|0)[0-9]{8}$/', preg_replace('/[\s\-\.]/', '', $phone));
    }

    private function isProfessionInterdite($profession, $organisationType)
    {
        if ($organisationType !== 'parti_politique') return false;
        return $this->nipValidationService->isProfessionInterdite($profession, $organisationType);
    }

    private function checkExistingPoliticalMembership($nip, $currentOrgId)
    {
        return $this->nipValidationService->checkUniquenessForPoliticalParties($nip);
    }

    private function parseBooleanValue($value)
    {
        return in_array(strtolower(trim($value)), ['oui', 'yes', '1', 'true', 'vrai']);
    }

    private function getHighestSeverity(array $anomalies)
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

    private function getAnomalyPriority($type)
    {
        $priorities = [
            'critique' => 1,
            'majeure' => 2,
            'mineure' => 3
        ];

        return $priorities[$type] ?? 3;
    }

    /**
     * Historique des imports (méthode existante)
     */
    public function getImportHistory(Organisation $organisation)
    {
        return AdherentImport::where('organisation_id', $organisation->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
}