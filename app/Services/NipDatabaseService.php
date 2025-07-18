<?php

namespace App\Services;

use App\Models\NipDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Adherent;
use App\Models\Organisation;

class NipDatabaseService
{
    protected $importStats = [
        'total_rows' => 0,
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'error_details' => []
    ];

    /**
     * Import d'un fichier Excel/CSV de base NIP (Version hybride avec fallback)
     */
    public function importFromExcel(UploadedFile $file, $userId)
    {
        try {
            // Réinitialiser les stats
            $this->resetImportStats();

            // Validation du fichier
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            $extension = strtolower($file->getClientOriginalExtension());

            // ✅ STRATÉGIE HYBRIDE: CSV d'abord, puis Excel si PhpSpreadsheet disponible
            if (in_array($extension, ['csv', 'txt', 'tsv'])) {
                return $this->importFromCsv($file, $userId);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                // Vérifier si PhpSpreadsheet est disponible
                if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                    return $this->importFromExcelFile($file, $userId);
                } else {
                    return [
                        'success' => false,
                        'message' => 'PhpSpreadsheet non disponible. Veuillez convertir votre fichier Excel en CSV ou activer l\'extension ZIP.'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Format de fichier non supporté'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import NIP: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'import: ' . $e->getMessage(),
                'stats' => $this->importStats
            ];
        }
    }

    /**
     * ✅ MÉTHODE CORRIGÉE: Import CSV avec suppression BOM
     */
    protected function importFromCsv(UploadedFile $file, $userId)
    {
        try {
            $csvData = file_get_contents($file->getPathname());
            
            // ✅ SUPPRIMER LE BOM (Byte Order Mark) s'il existe
            $csvData = $this->removeBOM($csvData);
            
            // Détecter le délimiteur automatiquement
            $delimiter = $this->detectCsvDelimiter($csvData);
            Log::info("Délimiteur détecté: '{$delimiter}'");
            
            $lines = explode("\n", $csvData);
            $this->importStats['total_rows'] = count($lines) - 1;

            if ($this->importStats['total_rows'] <= 0) {
                return [
                    'success' => false,
                    'message' => 'Fichier vide ou sans données'
                ];
            }

            // Vérifier les en-têtes avec le bon délimiteur
            $headers = $this->getExpectedHeaders();
            $fileHeaders = str_getcsv(trim($lines[0]), $delimiter);
            
            // ✅ NETTOYER LES EN-TÊTES (supprimer BOM résiduel)
            $fileHeaders = array_map(function($header) {
                return $this->cleanHeader($header);
            }, $fileHeaders);
            
            Log::info("En-têtes du fichier (nettoyés): " . implode(', ', $fileHeaders));
            
            if (!$this->validateHeadersCsv($fileHeaders, $headers)) {
                return [
                    'success' => false,
                    'message' => $this->generateValidationErrorMessage($fileHeaders)
                ];
            }

            DB::beginTransaction();

            try {
                // Traitement ligne par ligne
                for ($i = 1; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (empty($line)) continue;

                    $rowData = str_getcsv($line, $delimiter);
                    $this->processRowCsv($rowData, $fileHeaders, $userId, $i + 1);
                }

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Import CSV terminé avec succès',
                    'stats' => $this->importStats
                ];

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erreur import CSV: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE: Supprimer le BOM du fichier
     */
    protected function removeBOM($data)
    {
        // BOM UTF-8: EF BB BF
        if (substr($data, 0, 3) === pack('CCC', 0xEF, 0xBB, 0xBF)) {
            $data = substr($data, 3);
            Log::info("BOM UTF-8 supprimé");
        }
        
        // BOM UTF-16 BE: FE FF
        if (substr($data, 0, 2) === pack('CC', 0xFE, 0xFF)) {
            $data = substr($data, 2);
            Log::info("BOM UTF-16 BE supprimé");
        }
        
        // BOM UTF-16 LE: FF FE
        if (substr($data, 0, 2) === pack('CC', 0xFF, 0xFE)) {
            $data = substr($data, 2);
            Log::info("BOM UTF-16 LE supprimé");
        }
        
        return $data;
    }

    /**
     * ✅ NOUVELLE MÉTHODE: Nettoyer un en-tête individuel
     */
    protected function cleanHeader($header)
    {
        // Supprimer tous les caractères invisibles et BOM résiduels
        $header = trim($header);
        $header = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $header); // Caractères de contrôle
        $header = str_replace(["\xEF\xBB\xBF", "\xFF\xFE", "\xFE\xFF"], '', $header); // BOM
        $header = preg_replace('/[^\x20-\x7E\x{00A0}-\x{00FF}]/u', '', $header); // Caractères non-printables
        
        return trim($header);
    }

    /**
     * ✅ MÉTHODE CORRIGÉE: Normaliser en-têtes avec nettoyage
     */
    protected function normalizeHeader($header)
    {
        // ✅ D'ABORD NETTOYER, PUIS NORMALISER
        $header = $this->cleanHeader($header);
        
        $normalized = strtolower(trim($header));
        $normalized = str_replace([' ', '-', '_'], '', $normalized);
        
        // ✅ MAPPING EXACT pour votre fichier
        $mappings = [
            // Variations de LASTNAME (nom)
            'lastname' => 'nom',
            'nom' => 'nom',
            'noms' => 'nom',
            'familyname' => 'nom',
            
            // Variations de FIRSTNAME (prénom)
            'firstname' => 'prenom', 
            'prenom' => 'prenom',
            'prenoms' => 'prenom',
            'givenname' => 'prenom',
            
            // Variations de UIN (NIP)
            'uin' => 'nip',
            'nip' => 'nip',
            'numerodidentification' => 'nip',
            'identifiant' => 'nip',
            
            // Variations de DATE_OF_BIRTH
            'dateofbirth' => 'date_naissance',
            'datenaissance' => 'date_naissance',
            'birthdate' => 'date_naissance',
            'naissance' => 'date_naissance',
            
            // Variations de PLACE_OF_BIRTH
            'placeofbirth' => 'lieu_naissance',
            'lieunaissance' => 'lieu_naissance',
            'birthplace' => 'lieu_naissance',
            'lieu' => 'lieu_naissance',
            
            // Autres champs optionnels
            'statut' => 'statut',
            'telephone' => 'telephone',
            'email' => 'email',
            'remarques' => 'remarques'
        ];
        
        return $mappings[$normalized] ?? $normalized;
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE: Validation avec debugging
     */
    protected function validateHeadersCsv($fileHeaders, $expectedHeaders)
    {
        // Normaliser les en-têtes du fichier
        $normalizedFileHeaders = array_map([$this, 'normalizeHeader'], $fileHeaders);

        Log::info("Headers originaux: " . json_encode($fileHeaders));
        Log::info("Headers normalisés: " . json_encode($normalizedFileHeaders));

        // ✅ CHAMPS OBLIGATOIRES selon votre fichier
        $requiredHeaders = ['nom', 'prenom', 'nip']; // LASTNAME, FIRSTNAME, UIN
        
        $missingHeaders = [];
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $normalizedFileHeaders)) {
                $missingHeaders[] = $required;
            }
        }

        if (!empty($missingHeaders)) {
            Log::warning("En-têtes manquants: " . implode(', ', $missingHeaders));
            Log::info("Headers trouvés: " . implode(', ', $normalizedFileHeaders));
            Log::info("Headers originaux: " . implode(', ', $fileHeaders));
            return false;
        }

        Log::info("✅ Validation en-têtes OK pour fichier avec: " . implode(', ', $fileHeaders));
        return true;
    }

    /**
     * ✅ MÉTHODE AJUSTÉE: Message d'erreur informatif
     */
    protected function generateValidationErrorMessage($fileHeaders)
    {
        $found = implode(', ', $fileHeaders);
        $expected = "LASTNAME, FIRSTNAME, UIN (obligatoires) + DATE_OF_BIRTH, PLACE_OF_BIRTH (optionnels)";
        
        return "Format de fichier invalide.\n" .
               "Headers trouvés: {$found}\n" .
               "Headers attendus: {$expected}\n" .
               "Seuls LASTNAME, FIRSTNAME et UIN sont obligatoires.";
    }

    /**
     * ✅ MÉTHODE MANQUANTE: Détecter délimiteur CSV
     */
    protected function detectCsvDelimiter($csvData)
    {
        $delimiters = [',', ';', "\t", '|'];
        $firstLine = strtok($csvData, "\n");
        
        $delimiterCounts = [];
        foreach ($delimiters as $delimiter) {
            $delimiterCounts[$delimiter] = substr_count($firstLine, $delimiter);
        }
        
        // Retourner le délimiteur avec le plus d'occurrences
        $detectedDelimiter = array_search(max($delimiterCounts), $delimiterCounts);
        
        // Si aucun délimiteur trouvé, utiliser la virgule par défaut
        return $detectedDelimiter !== false ? $detectedDelimiter : ',';
    }

    /**
     * ✅ MÉTHODE MANQUANTE: Parser date depuis CSV
     */
    protected function parseDate($dateString)
    {
        if (empty($dateString)) return 'N/A';

        $dateString = trim($dateString);
        
        // Formats supportés
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'];
        
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date) {
                    return $date->format('d/m/Y'); // Format Excel compatible
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $dateString; // Retourner tel quel si pas de format reconnu
    }

    /**
     * ✅ MÉTHODE MANQUANTE: Nettoyer téléphone
     */
    protected function cleanPhone($phone)
    {
        if (empty($phone)) return null;
        
        // Enlever tous les caractères non numériques sauf +
        $cleaned = preg_replace('/[^0-9+]/', '', trim($phone));
        
        return $cleaned ?: null;
    }

    /**
     * ✅ MÉTHODE EXISTANTE: Import depuis Excel (si PhpSpreadsheet disponible)
     */
    protected function importFromExcelFile(UploadedFile $file, $userId)
    {
        try {
            // Vérifier dynamiquement si PhpSpreadsheet est disponible
            if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                throw new \Exception('PhpSpreadsheet non disponible');
            }

            // Charger le fichier Excel en utilisant la classe dynamiquement
            $ioFactoryClass = 'PhpOffice\PhpSpreadsheet\IOFactory';
            $spreadsheet = $ioFactoryClass::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            // Vérifier les en-têtes (ligne 1)
            $headers = $this->getExpectedHeaders();
            $fileHeaders = $this->extractHeaders($worksheet);
            
            if (!$this->validateHeaders($fileHeaders, $headers)) {
                return [
                    'success' => false,
                    'message' => 'Format de fichier invalide. En-têtes attendus: ' . implode(', ', $headers)
                ];
            }

            $this->importStats['total_rows'] = $highestRow - 1; // Exclure l'en-tête

            // Traitement par lots pour éviter les timeouts
            $batchSize = 500;
            $processedRows = 0;

            DB::beginTransaction();

            try {
                for ($row = 2; $row <= $highestRow; $row += $batchSize) {
                    $endRow = min($row + $batchSize - 1, $highestRow);
                    $this->processBatch($worksheet, $row, $endRow, $userId);
                    $processedRows += ($endRow - $row + 1);
                    
                    // Log de progression
                    Log::info("NIP Import: Processed {$processedRows}/{$this->importStats['total_rows']} rows");
                }

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Import Excel terminé avec succès',
                    'stats' => $this->importStats
                ];

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erreur import Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE: Traitement ligne CSV
     */
    protected function processRowCsv($rowData, $actualHeaders, $userId, $rowNumber)
    {
        try {
            // Debug: afficher les données brutes
            Log::info("Ligne {$rowNumber} - Headers: " . implode('|', $actualHeaders));
            Log::info("Ligne {$rowNumber} - Data: " . implode('|', $rowData));

            // Créer un tableau associatif avec les vrais en-têtes
            $data = [];
            for ($i = 0; $i < count($actualHeaders); $i++) {
                $headerKey = $this->normalizeHeader($actualHeaders[$i]);
                $data[$headerKey] = isset($rowData[$i]) ? trim($rowData[$i]) : '';
            }

            Log::info("Ligne {$rowNumber} - Data normalisée: " . json_encode($data));

            // Nettoyer et normaliser les données
            $cleanData = $this->cleanRowDataCsv($data);

            // ✅ VÉRIFIER SEULEMENT LES 3 CHAMPS OBLIGATOIRES
            if (empty($cleanData['nip']) || empty($cleanData['nom']) || empty($cleanData['prenom'])) {
                $this->importStats['skipped']++;
                Log::warning("Ligne {$rowNumber} ignorée: champs obligatoires manquants");
                Log::warning("NIP: '{$cleanData['nip']}', Nom: '{$cleanData['nom']}', Prénom: '{$cleanData['prenom']}'");
                return;
            }

            Log::info("Ligne {$rowNumber} - Données finales: " . json_encode($cleanData));

            // Traitement avec la méthode existante
            $this->processRow($cleanData, $userId, $rowNumber);

        } catch (\Exception $e) {
            $this->importStats['errors']++;
            $this->importStats['error_details'][] = [
                'row' => $rowNumber,
                'error' => $e->getMessage(),
                'data' => isset($cleanData) ? $cleanData : $rowData
            ];
            Log::error("Erreur ligne CSV {$rowNumber}: " . $e->getMessage());
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE: Nettoyer données CSV
     */
    protected function cleanRowDataCsv($data)
    {
        return [
            // ✅ CHAMPS DE VOTRE FICHIER
            'nom' => strtoupper(trim($data['nom'] ?? '')), // LASTNAME
            'prenom' => ucwords(strtolower(trim($data['prenom'] ?? ''))), // FIRSTNAME
            'nip' => strtoupper(trim($data['nip'] ?? '')), // UIN
            'date_naissance_excel' => $this->parseDate($data['date_naissance'] ?? ''), // DATE_OF_BIRTH
            'lieu_naissance' => ucwords(trim($data['lieu_naissance'] ?? '')), // PLACE_OF_BIRTH
            
            // ✅ CHAMPS OPTIONNELS (défaut si absents)
            'statut' => strtolower(trim($data['statut'] ?? 'actif')),
            'telephone' => $this->cleanPhone($data['telephone'] ?? ''),
            'email' => strtolower(trim($data['email'] ?? '')),
            'remarques' => trim($data['remarques'] ?? '')
        ];
    }

    // ✅ TOUTES LES MÉTHODES EXISTANTES CONSERVÉES
    
    /**
     * Traitement d'un lot de lignes (Excel)
     */
    protected function processBatch($worksheet, $startRow, $endRow, $userId)
    {
        for ($row = $startRow; $row <= $endRow; $row++) {
            try {
                $rowData = $this->extractRowData($worksheet, $row);
                
                if (empty($rowData['nip'])) {
                    $this->importStats['skipped']++;
                    continue;
                }

                $this->processRow($rowData, $userId, $row);

            } catch (\Exception $e) {
                $this->importStats['errors']++;
                $this->importStats['error_details'][] = [
                    'row' => $row,
                    'error' => $e->getMessage()
                ];
                Log::warning("Erreur ligne {$row}: " . $e->getMessage());
            }
        }
    }

    /**
     * Traitement d'une ligne de données avec validation renforcée
     */
    protected function processRow($data, $userId, $rowNumber)
    {
        // Validation du NIP avec vérification d'unicité
        $nipValidation = NipDatabase::validateNipFormat($data['nip']);
        if (!$nipValidation['valid']) {
            throw new \Exception("NIP invalide: " . $nipValidation['error']);
        }

        // Validation des champs obligatoires
        if (empty($data['nom']) || empty($data['prenom'])) {
            throw new \Exception("Nom et prénom sont obligatoires");
        }

        // Extraction des infos du NIP
        $nipInfo = NipDatabase::extractInfoFromNip($data['nip']);
        
        // Validation cohérence date de naissance si fournie dans Excel
        if (!empty($data['date_naissance_excel']) && $data['date_naissance_excel'] !== 'N/A') {
            // Comparaison avec la date extraite du NIP
            $excelDate = $this->parseExcelDate($data['date_naissance_excel']);
            if ($excelDate && !$nipInfo['date_naissance']->isSameDay($excelDate)) {
                throw new \Exception("Date de naissance incohérente entre NIP et colonne Excel");
            }
        }
        
        // Vérifier si le NIP existe déjà
        $existingNip = NipDatabase::where('nip', $data['nip'])->first();

        if ($existingNip) {
            // Mise à jour si nécessaire
            $this->updateExistingNip($existingNip, $data, $userId);
        } else {
            // Création nouveau NIP
            $this->createNewNip($data, $nipInfo, $userId);
        }
    }

    /**
     * Mise à jour d'un NIP existant
     */
    protected function updateExistingNip($existingNip, $data, $userId)
    {
        $needsUpdate = false;
        $fieldsToUpdate = [];

        // Comparer les champs et marquer pour mise à jour si différent
        $comparableFields = ['nom', 'prenom', 'lieu_naissance', 'statut', 'telephone', 'email'];
        
        foreach ($comparableFields as $field) {
            if (isset($data[$field]) && $existingNip->{$field} !== $data[$field]) {
                $fieldsToUpdate[$field] = $data[$field];
                $needsUpdate = true;
            }
        }

        if ($needsUpdate) {
            $fieldsToUpdate['last_verified_at'] = now();
            $existingNip->update($fieldsToUpdate);
            $this->importStats['updated']++;
            
            Log::info("NIP mis à jour: {$existingNip->nip}");
        } else {
            // Marquer comme vérifié même si pas de changement
            $existingNip->update(['last_verified_at' => now()]);
            $this->importStats['skipped']++;
        }
    }

    /**
     * Création d'un nouveau NIP selon l'ordre des colonnes (sans nationalité)
     */
    protected function createNewNip($data, $nipInfo, $userId)
    {
        $nipData = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_naissance' => $nipInfo['date_naissance'],
            'lieu_naissance' => $data['lieu_naissance'] ?? null,
            'nip' => $data['nip'],
            'sexe' => $nipInfo['sexe'],
            'statut' => $data['statut'] ?? 'actif',
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
            'remarques' => $data['remarques'] ?? null,
            'source_import' => 'excel_import',
            'date_import' => now(),
            'imported_by' => $userId,
            'last_verified_at' => now()
        ];

        NipDatabase::create($nipData);
        $this->importStats['imported']++;
        
        Log::info("Nouveau NIP créé: {$data['nip']}");
    }

    /**
     * Extraction des données d'une ligne selon l'ordre correct (sans nationalité)
     */
    protected function extractRowData($worksheet, $row)
    {
        return [
            'nom' => trim($worksheet->getCell("A{$row}")->getCalculatedValue()),
            'prenom' => trim($worksheet->getCell("B{$row}")->getCalculatedValue()),
            'date_naissance_excel' => trim($worksheet->getCell("C{$row}")->getCalculatedValue()), // Pour validation
            'lieu_naissance' => trim($worksheet->getCell("D{$row}")->getCalculatedValue()),
            'nip' => trim($worksheet->getCell("E{$row}")->getCalculatedValue()),
            'statut' => strtolower(trim($worksheet->getCell("F{$row}")->getCalculatedValue())) ?: 'actif',
            'telephone' => trim($worksheet->getCell("G{$row}")->getCalculatedValue()),
            'email' => trim($worksheet->getCell("H{$row}")->getCalculatedValue()),
            'remarques' => trim($worksheet->getCell("I{$row}")->getCalculatedValue())
        ];
    }

    /**
     * Extraction des en-têtes selon le nouvel ordre (sans nationalité)
     */
    protected function extractHeaders($worksheet)
    {
        $headers = [];
        for ($col = 'A'; $col <= 'I'; $col++) { // Maintenant jusqu'à I car on a 9 colonnes
            $headers[] = trim($worksheet->getCell("{$col}1")->getCalculatedValue());
        }
        return $headers;
    }

    /**
     * En-têtes attendus dans l'ordre correct (sans nationalité)
     */
    protected function getExpectedHeaders()
    {
        return [
            'LASTNAME',         // OBLIGATOIRE - Nom de famille
            'FIRSTNAME',        // OBLIGATOIRE - Prénom
            'UIN',              // OBLIGATOIRE - Numéro d'identification
            'DATE_OF_BIRTH',    // OPTIONNEL - Date de naissance
            'PLACE_OF_BIRTH'    // OPTIONNEL - Lieu de naissance
        ];
    }

    /**
     * Validation des en-têtes (Excel)
     */
    protected function validateHeaders($fileHeaders, $expectedHeaders)
    {
        for ($i = 0; $i < count($expectedHeaders); $i++) {
            if (!isset($fileHeaders[$i]) || 
                strtolower(trim($fileHeaders[$i])) !== strtolower(trim($expectedHeaders[$i]))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validation du fichier (modifiée pour supporter CSV)
     */
    protected function validateFile(UploadedFile $file)
    {
        // Vérifier l'extension
        $allowedExtensions = ['xlsx', 'xls', 'csv', 'txt', 'tsv'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'valid' => false,
                'message' => 'Format de fichier non supporté. Extensions autorisées: ' . implode(', ', $allowedExtensions)
            ];
        }

        // Vérifier la taille (max 50MB)
        if ($file->getSize() > 50 * 1024 * 1024) {
            return [
                'valid' => false,
                'message' => 'Fichier trop volumineux. Taille maximum: 50MB'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Réinitialiser les statistiques d'import
     */
    protected function resetImportStats()
    {
        $this->importStats = [
            'total_rows' => 0,
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'error_details' => []
        ];
    }

    /**
     * Recherche dans la base NIP
     */
    public function searchNip($query, $filters = [])
    {
        $queryBuilder = NipDatabase::query();

        // Recherche textuelle
        if (!empty($query)) {
            $queryBuilder->search($query);
        }

        // Filtres
        if (!empty($filters['statut'])) {
            $queryBuilder->where('statut', $filters['statut']);
        }

        if (!empty($filters['sexe'])) {
            $queryBuilder->where('sexe', $filters['sexe']);
        }

        if (!empty($filters['date_from'])) {
            $queryBuilder->where('date_naissance', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $queryBuilder->where('date_naissance', '<=', $filters['date_to']);
        }

        return $queryBuilder->orderBy('nom')->paginate(50);
    }

    /**
     * Vérification d'un NIP spécifique
     */
    public function verifyNip($nip)
    {
        $nipRecord = NipDatabase::getNipInfo($nip);
        
        if (!$nipRecord) {
            return [
                'found' => false,
                'message' => 'NIP non trouvé dans la base de données'
            ];
        }

        return [
            'found' => true,
            'data' => $nipRecord,
            'message' => 'NIP trouvé et vérifié'
        ];
    }

    /**
     * Parser une date provenant d'Excel
     */
    protected function parseExcelDate($excelDate)
    {
        try {
            // Si c'est un nombre (format Excel)
            if (is_numeric($excelDate)) {
                if (class_exists('PhpOffice\PhpSpreadsheet\Shared\Date')) {
                    $dateClass = 'PhpOffice\PhpSpreadsheet\Shared\Date';
                    return Carbon::instance($dateClass::excelToDateTimeObject($excelDate));
                }
            }
            
            // Si c'est une chaîne de caractères, essayer plusieurs formats
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
            
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $excelDate);
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Export de la base NIP vers Excel
     */
    public function exportToExcel($filters = [])
    {
        // Cette méthode sera implémentée dans la suite si nécessaire
        // Pour l'instant, on retourne la structure de base
        return [
            'success' => true,
            'message' => 'Export Excel sera implémenté dans la prochaine phase'
        ];
    }

    /**
     * API pour validation NIP temps réel avec détection anomalies complète
     * Route: POST /api/validate-nip
     */
    public function validateNipApi(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nip' => 'required|string|min:10|max:20',
                'nom' => 'nullable|string|max:100',
                'prenom' => 'nullable|string|max:100',
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
            
            // === VALIDATION BASE NIP ===
            $validation = $this->validateNip($nip, $request->nom, $request->prenom);

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
     * Vérification simple d'un NIP (pour auto-complétion)
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

            // === RECHERCHER DANS LA BASE NIP ===
            $nipRecord = NipDatabase::where('nip', $nip)->first();
            
            if ($nipRecord) {
                return response()->json([
                    'success' => true,
                    'found' => true,
                    'message' => 'NIP trouvé dans la base centrale',
                    'data' => [
                        'nom' => $nipRecord->nom,
                        'prenom' => $nipRecord->prenom,
                        'date_naissance' => $nipRecord->date_naissance,
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
     * ✅ MÉTHODE MANQUANTE: Validation complète NIP
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

            // Vérification format NIP
            if (empty(trim($nip))) {
                $result['error'] = 'NIP requis';
                return $result;
            }

            if (!$this->validateNipFormat($nip)) {
                $result['error'] = 'Format NIP invalide (attendu: XX-QQQQ-YYYYMMDD)';
                return $result;
            }

            // Rechercher dans la base NIP centrale
            $nipRecord = NipDatabase::where('nip', trim($nip))->first();
            
            if ($nipRecord) {
                // Récupérer données pour pré-remplissage
                $result['nip_data'] = [
                    'nom' => $nipRecord->nom,
                    'prenom' => $nipRecord->prenom,
                    'date_naissance' => $nipRecord->date_naissance->format('Y-m-d'),
                    'lieu_naissance' => $nipRecord->lieu_naissance,
                    'sexe' => $nipRecord->sexe,
                    'telephone' => $nipRecord->telephone,
                    'email' => $nipRecord->email
                ];

                // Vérification cohérence nom/prénom si fournis
                if ($nom || $prenom) {
                    $coherenceIssues = [];
                    if ($nom && strtoupper($nom) !== strtoupper($nipRecord->nom)) {
                        $coherenceIssues[] = "Nom différent (base: {$nipRecord->nom}, fourni: {$nom})";
                    }
                    if ($prenom && strtoupper($prenom) !== strtoupper($nipRecord->prenom)) {
                        $coherenceIssues[] = "Prénom différent (base: {$nipRecord->prenom}, fourni: {$prenom})";
                    }

                    if (!empty($coherenceIssues)) {
                        $result['warning'] = true;
                        $result['message'] = 'Incohérences détectées: ' . implode(', ', $coherenceIssues);
                    }
                }
            }

            // Vérifier appartenance à d'autres organisations
            $existingMemberships = Adherent::where('nip', trim($nip))
                                          ->where('is_active', true)
                                          ->with('organisation')
                                          ->get();

            if ($existingMemberships->count() > 0) {
                $result['existing_memberships'] = $existingMemberships->map(function($adherent) {
                    return [
                        'organisation' => $adherent->organisation->nom,
                        'type' => $adherent->organisation->type,
                        'fonction' => $adherent->fonction,
                        'date_adhesion' => $adherent->date_adhesion
                    ];
                });
            }

            // Le NIP est toujours considéré comme valide (non-bloquant)
            $result['valid'] = true;
            $result['message'] = $result['message'] ?: 'NIP valide';

            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur validation NIP: ' . $e->getMessage());
            return [
                'valid' => false,
                'error' => 'Erreur lors de la validation',
                'source' => 'nip_validation'
            ];
        }
    }

    // ✅ MÉTHODES PRIVÉES POUR L'API
    
    /**
     * Détecter toutes les anomalies NIP pour l'API
     */
    private function detectAllNipAnomalies(string $nip, string $organisationType): array
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
            return $anomalies; // Arrêter ici si NIP vide
        }

        // 2. Format NIP incorrect
        if (!$this->validateNipFormat($nip)) {
            $anomalies[] = [
                'code' => 'NIP_FORMAT_INVALIDE',
                'type' => 'majeure',
                'message' => 'Format NIP incorrect (attendu: XX-QQQQ-YYYYMMDD)',
                'field' => 'nip',
                'value' => $nip,
                'expected_format' => 'XX-QQQQ-YYYYMMDD'
            ];
            return $anomalies; // Arrêter ici si format invalide
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
            $existingMemberships = $this->checkExistingMemberships($nip);
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

        return $anomalies;
    }

    /**
     * Vérifier si il y a des anomalies critiques
     */
    private function hasAnomaliesCritiques(array $anomalies): bool
    {
        foreach ($anomalies as $anomalie) {
            if ($anomalie['type'] === 'critique') {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtenir le niveau de sévérité le plus élevé
     */
    private function getHighestSeverity(array $anomalies): ?string
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
     * Formater le message API (INFORMATIF - NON BLOQUANT)
     */
    private function formatApiMessageInformatif(array $validation, array $anomalies): string
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

        if (isset($validation['warning'])) {
            return 'ℹ️ ' . ($validation['message'] ?? 'NIP valide avec avertissement - Enregistrement possible');
        }

        return '✅ NIP valide - Enregistrement normal';
    }

    /**
     * Valider le format NIP XX-QQQQ-YYYYMMDD
     */
    private function validateNipFormat(string $nip): bool
    {
        return preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', trim($nip));
    }

    /**
     * Extraire l'âge depuis un NIP valide
     */
    private function extractAgeFromNip(string $nip): ?int
    {
        if (!$this->validateNipFormat($nip)) {
            return null;
        }
        
        $datePart = substr($nip, -8); // Derniers 8 chiffres: YYYYMMDD
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        try {
            $birthDate = new \DateTime("{$year}-{$month}-{$day}");
            $now = new \DateTime();
            return $now->diff($birthDate)->y;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Vérifier les appartenances existantes
     */
    private function checkExistingMemberships(string $nip): array
    {
        $existing = \App\Models\Adherent::where('nip', $nip)
            ->where('is_active', true)
            ->with('organisation')
            ->get();
        
        if ($existing->isEmpty()) {
            return [];
        }
        
        return $existing->map(function($adherent) {
            return [
                'id' => $adherent->organisation->id,
                'nom' => $adherent->organisation->nom,
                'type' => $adherent->organisation->type,
                'statut' => $adherent->organisation->statut
            ];
        })->toArray();
    }

    /**
     * Générer des suggestions de NIP
     */
    private function generateNipSuggestions(string $invalidNip): array
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