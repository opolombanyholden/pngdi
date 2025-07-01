<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ========================================================================
 * CONTRÔLEUR DE TRAITEMENT DES CHUNKS - PNGDI
 * Fichier: app/Http/Controllers/Api/ChunkProcessorController.php
 * Compatible: PHP 7.3.29 + Laravel
 * Date: 1er juillet 2025
 * Version: 1.0 - Solution Chunking Laravel
 * ========================================================================
 * 
 * OBJECTIF : Traiter les chunks d'adhérents envoyés par le JavaScript
 * sans provoquer de timeout ou d'erreur 419 CSRF.
 */
class ChunkProcessorController extends Controller
{
    /**
     * Configuration du traitement par chunks
     */
    private $chunkConfig = [
        'max_chunk_size' => 100,
        'max_execution_time' => 25,  // 25 secondes (inférieur à 30s)
        'memory_limit' => '256M',
        'batch_insert_size' => 50,   // Insertion par lots de 50
    ];

    /**
     * Traiter un chunk d'adhérents
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processChunk(Request $request)
    {
        // Augmenter les limites pour ce processus
        set_time_limit($this->chunkConfig['max_execution_time']);
        ini_set('memory_limit', $this->chunkConfig['memory_limit']);
        
        Log::info('📦 CHUNK PROCESSOR: Début traitement chunk', [
            'chunk_id' => $request->input('chunk_id'),
            'is_chunk' => $request->input('is_chunk'),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ]);

        try {
            // Validation des données du chunk
            $validator = $this->validateChunkData($request);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de chunk invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Extraire les données du chunk
            $chunkId = $request->input('chunk_id');
            $chunkData = json_decode($request->input('chunk_data'), true);
            $totalChunks = $request->input('total_chunks', 1);
            $startIndex = $request->input('chunk_start_index', 0);
            $endIndex = $request->input('chunk_end_index', 0);

            Log::info('📊 CHUNK PROCESSOR: Données extraites', [
                'chunk_id' => $chunkId,
                'data_count' => count($chunkData),
                'total_chunks' => $totalChunks,
                'range' => "$startIndex-$endIndex"
            ]);

            // Vérifier la taille du chunk
            if (count($chunkData) > $this->chunkConfig['max_chunk_size']) {
                return response()->json([
                    'success' => false,
                    'message' => "Chunk trop volumineux. Maximum {$this->chunkConfig['max_chunk_size']} éléments."
                ], 422);
            }

            // Traitement principal du chunk
            $startTime = microtime(true);
            $result = $this->processAdherentsChunk($chunkData, $chunkId);
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('✅ CHUNK PROCESSOR: Traitement terminé', [
                'chunk_id' => $chunkId,
                'processing_time_ms' => $processingTime,
                'processed_count' => $result['processed'],
                'errors_count' => $result['errors'],
                'memory_used' => $this->getMemoryUsage()
            ]);

            // Réponse de succès
            return response()->json([
                'success' => true,
                'message' => "Chunk $chunkId traité avec succès",
                'data' => [
                    'chunk_id' => $chunkId,
                    'processed' => $result['processed'],
                    'errors' => $result['errors'],
                    'valid_adherents' => $result['valid_adherents'],
                    'adherents_with_anomalies' => $result['adherents_with_anomalies'],
                    'processing_time_ms' => $processingTime,
                    'memory_used' => $this->getMemoryUsage(),
                    'timestamp' => now()->toISOString(),
                    
                    // Informations pour le frontend
                    'chunk_progress' => [
                        'current' => $chunkId,
                        'total' => $totalChunks,
                        'percentage' => round(($chunkId / $totalChunks) * 100, 1)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ CHUNK PROCESSOR: Erreur traitement chunk', [
                'chunk_id' => $request->input('chunk_id'),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du chunk',
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'memory_used' => $this->getMemoryUsage()
                ]
            ], 500);
        }
    }

    /**
     * Traiter les adhérents d'un chunk
     *
     * @param array $adherentsData
     * @param int $chunkId
     * @return array
     */
    private function processAdherentsChunk($adherentsData, $chunkId)
    {
        $processed = 0;
        $errors = 0;
        $validAdherents = 0;
        $adherentsWithAnomalies = 0;
        $errorDetails = [];

        Log::info("🔄 Début traitement adhérents chunk $chunkId", [
            'count' => count($adherentsData)
        ]);

        // Traiter les adhérents par petits lots pour optimiser la mémoire
        $batchSize = $this->chunkConfig['batch_insert_size'];
        $batches = array_chunk($adherentsData, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            try {
                Log::debug("📦 Traitement batch " . ($batchIndex + 1) . "/" . count($batches));

                // Commencer une transaction pour ce batch
                DB::beginTransaction();

                foreach ($batch as $adherentData) {
                    try {
                        // Valider et nettoyer les données de l'adhérent
                        $cleanedData = $this->validateAndCleanAdherent($adherentData);
                        
                        if ($cleanedData['is_valid']) {
                            // Sauvegarder l'adhérent valide
                            $adherent = $this->saveAdherent($cleanedData['data'], $chunkId);
                            
                            if ($cleanedData['has_anomalies']) {
                                $adherentsWithAnomalies++;
                                
                                // Sauvegarder les anomalies
                                $this->saveAnomalies($adherent->id, $cleanedData['anomalies']);
                            } else {
                                $validAdherents++;
                            }
                            
                            $processed++;
                            
                        } else {
                            $errors++;
                            $errorDetails[] = [
                                'adherent' => $adherentData['nom'] . ' ' . $adherentData['prenom'],
                                'nip' => $adherentData['nip'] ?? 'N/A',
                                'errors' => $cleanedData['errors']
                            ];
                        }

                    } catch (\Exception $e) {
                        $errors++;
                        $errorDetails[] = [
                            'adherent' => ($adherentData['nom'] ?? 'N/A') . ' ' . ($adherentData['prenom'] ?? 'N/A'),
                            'nip' => $adherentData['nip'] ?? 'N/A',
                            'errors' => ['Erreur technique: ' . $e->getMessage()]
                        ];
                        
                        Log::warning("⚠️ Erreur traitement adhérent individuel", [
                            'adherent_data' => $adherentData,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Valider la transaction du batch
                DB::commit();
                
                Log::debug("✅ Batch " . ($batchIndex + 1) . " traité avec succès");

            } catch (\Exception $e) {
                // Annuler la transaction en cas d'erreur
                DB::rollBack();
                
                Log::error("❌ Erreur traitement batch " . ($batchIndex + 1), [
                    'error' => $e->getMessage(),
                    'batch_size' => count($batch)
                ]);

                // Marquer tous les adhérents de ce batch comme erreur
                $errors += count($batch);
                foreach ($batch as $adherentData) {
                    $errorDetails[] = [
                        'adherent' => ($adherentData['nom'] ?? 'N/A') . ' ' . ($adherentData['prenom'] ?? 'N/A'),
                        'nip' => $adherentData['nip'] ?? 'N/A',
                        'errors' => ['Erreur batch: ' . $e->getMessage()]
                    ];
                }
            }

            // Vérifier que nous ne dépassons pas le temps limite
            if (microtime(true) - LARAVEL_START > ($this->chunkConfig['max_execution_time'] - 5)) {
                Log::warning("⏰ Arrêt anticipé du traitement pour éviter timeout", [
                    'chunk_id' => $chunkId,
                    'batches_processed' => $batchIndex + 1,
                    'total_batches' => count($batches)
                ]);
                break;
            }
        }

        Log::info("✅ Chunk $chunkId traité", [
            'processed' => $processed,
            'errors' => $errors,
            'valid_adherents' => $validAdherents,
            'adherents_with_anomalies' => $adherentsWithAnomalies
        ]);

        return [
            'processed' => $processed,
            'errors' => $errors,
            'valid_adherents' => $validAdherents,
            'adherents_with_anomalies' => $adherentsWithAnomalies,
            'error_details' => $errorDetails
        ];
    }

    /**
     * Valider et nettoyer les données d'un adhérent
     *
     * @param array $adherentData
     * @return array
     */
    private function validateAndCleanAdherent($adherentData)
    {
        $errors = [];
        $anomalies = [];
        $hasAnomalies = false;

        // Validation des champs obligatoires
        if (empty($adherentData['nom'])) {
            $errors[] = 'Nom manquant';
        }
        if (empty($adherentData['prenom'])) {
            $errors[] = 'Prénom manquant';
        }
        if (empty($adherentData['nip'])) {
            $errors[] = 'NIP manquant';
        }

        // Si champs obligatoires manquants, retourner invalide
        if (!empty($errors)) {
            return [
                'is_valid' => false,
                'errors' => $errors,
                'data' => null,
                'has_anomalies' => false,
                'anomalies' => []
            ];
        }

        // Validation du NIP
        $nip = trim($adherentData['nip']);
        if (!preg_match('/^[0-9]{13}$/', $nip)) {
            $anomalies[] = [
                'type' => 'nip_invalide',
                'level' => 'critique',
                'message' => 'Format NIP incorrect',
                'details' => "NIP '$nip' ne respecte pas le format 13 chiffres"
            ];
            $hasAnomalies = true;
        }

        // Validation du téléphone (si présent)
        if (!empty($adherentData['telephone'])) {
            $telephone = preg_replace('/\s+/', '', $adherentData['telephone']);
            if (!preg_match('/^[0-9]{8,9}$/', $telephone)) {
                $anomalies[] = [
                    'type' => 'telephone_invalide',
                    'level' => 'majeure',
                    'message' => 'Format téléphone incorrect',
                    'details' => "Téléphone '{$adherentData['telephone']}' ne respecte pas le format gabonais"
                ];
                $hasAnomalies = true;
            }
        }

        // Validation des professions exclues pour parti politique
        if (!empty($adherentData['profession'])) {
            $professionsExclues = [
                'magistrat', 'juge', 'procureur', 'avocat_general',
                'militaire', 'gendarme', 'policier', 'forces_armee',
                'prefet', 'sous_prefet', 'gouverneur', 'maire',
                'fonctionnaire_administration', 'ambassadeur', 'consul'
            ];

            if (in_array(strtolower($adherentData['profession']), $professionsExclues)) {
                // TODO: Vérifier le type d'organisation depuis la session ou base de données
                $anomalies[] = [
                    'type' => 'profession_exclue_parti',
                    'level' => 'critique',
                    'message' => 'Profession potentiellement exclue',
                    'details' => "Profession '{$adherentData['profession']}' peut être exclue pour certains types d'organisations"
                ];
                $hasAnomalies = true;
            }
        }

        // Nettoyer et normaliser les données
        $cleanedData = [
            'civilite' => $adherentData['civilite'] ?? 'M',
            'nom' => ucwords(strtolower(trim($adherentData['nom']))),
            'prenom' => ucwords(strtolower(trim($adherentData['prenom']))),
            'nip' => $nip,
            'telephone' => !empty($adherentData['telephone']) ? preg_replace('/\s+/', '', $adherentData['telephone']) : null,
            'profession' => !empty($adherentData['profession']) ? trim($adherentData['profession']) : null,
            'processed_at' => now(),
            'chunk_id' => null, // Sera défini lors de la sauvegarde
            'has_anomalies' => $hasAnomalies
        ];

        return [
            'is_valid' => true,
            'errors' => [],
            'data' => $cleanedData,
            'has_anomalies' => $hasAnomalies,
            'anomalies' => $anomalies
        ];
    }

    /**
     * Sauvegarder un adhérent en base de données
     *
     * @param array $adherentData
     * @param int $chunkId
     * @return object
     */
    private function saveAdherent($adherentData, $chunkId)
    {
        // Ajouter les informations du chunk
        $adherentData['chunk_id'] = $chunkId;
        $adherentData['imported_by'] = auth()->id();
        $adherentData['import_session'] = session()->getId();

        // TODO: Adapter selon votre modèle Adherent existant
        // Pour l'instant, simulation d'insertion
        
        /*
        $adherent = \App\Models\Adherent::create($adherentData);
        */
        
        // Simulation temporaire
        $adherent = (object) array_merge($adherentData, [
            'id' => rand(1000, 9999),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Log::debug("💾 Adhérent sauvegardé", [
            'id' => $adherent->id,
            'nom' => $adherentData['nom'],
            'prenom' => $adherentData['prenom'],
            'nip' => $adherentData['nip']
        ]);

        return $adherent;
    }

    /**
     * Sauvegarder les anomalies d'un adhérent
     *
     * @param int $adherentId
     * @param array $anomalies
     */
    private function saveAnomalies($adherentId, $anomalies)
    {
        foreach ($anomalies as $anomalie) {
            // TODO: Adapter selon votre modèle Anomalie existant
            
            /*
            \App\Models\AdherentAnomalie::create([
                'adherent_id' => $adherentId,
                'type' => $anomalie['type'],
                'level' => $anomalie['level'],
                'message' => $anomalie['message'],
                'details' => $anomalie['details'],
                'detected_at' => now(),
                'status' => 'detected'
            ]);
            */
            
            Log::debug("📋 Anomalie sauvegardée", [
                'adherent_id' => $adherentId,
                'type' => $anomalie['type'],
                'level' => $anomalie['level'],
                'message' => $anomalie['message']
            ]);
        }
    }

    /**
     * Valider les données du chunk
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateChunkData(Request $request)
    {
        return Validator::make($request->all(), [
            'chunk_id' => 'required|integer|min:1',
            'chunk_data' => 'required|string|min:10',
            'is_chunk' => 'required|in:true,1',
            'total_chunks' => 'sometimes|integer|min:1|max:1000',
            'chunk_start_index' => 'sometimes|integer|min:0',
            'chunk_end_index' => 'sometimes|integer|min:0'
        ], [
            'chunk_id.required' => 'ID du chunk manquant',
            'chunk_data.required' => 'Données du chunk manquantes',
            'is_chunk.required' => 'Indicateur de chunk manquant',
            'total_chunks.max' => 'Trop de chunks (maximum 1000)',
        ]);
    }

    /**
     * Rafraîchir le token CSRF
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshCSRF(Request $request)
    {
        try {
            // Générer un nouveau token CSRF
            $newToken = csrf_token();
            
            Log::info('🔄 Token CSRF rafraîchi', [
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'csrf_token' => $newToken,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erreur refresh CSRF', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du refresh CSRF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'utilisation mémoire actuelle
     *
     * @return string
     */
    private function getMemoryUsage()
    {
        $bytes = memory_get_usage(true);
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obtenir les statistiques de performance
     *
     * @return array
     */
    public function getPerformanceStats()
    {
        return [
            'memory_usage' => $this->getMemoryUsage(),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
            'execution_time' => round(microtime(true) - LARAVEL_START, 3) . 's',
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Formater les bytes
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}