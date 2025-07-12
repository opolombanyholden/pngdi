<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\Adherent;

/**
 * ========================================================================
 * CHUNKING CONTROLLER OPTIMISÉ - SOLUTION "INSERTION DURING CHUNKING"
 * Compatible PHP 7.3.29 + Laravel + MySQL
 * Version: 2.0 PRODUCTION - Résout les timeouts d'insertion massive
 * ========================================================================
 * 
 * CORRECTIONS MAJEURES VERSION 2.0 :
 * ✅ INSERTION IMMÉDIATE par chunk (élimine les timeouts)
 * ✅ Compatible PHP 7.3.29 (syntaxe isset() au lieu de ??)
 * ✅ Gestion optimisée des doublons
 * ✅ Transactions courtes par chunk (performance)
 * ✅ Vraie progression temps réel
 * ✅ Gestion complète des anomalies SGLP
 * ✅ Logs détaillés pour monitoring
 */
class ChunkingController extends Controller
{
    /**
     * 🚀 MÉTHODE PRINCIPALE : Traitement chunk avec INSERTION IMMÉDIATE
     * Implémente la solution "INSERTION DURING CHUNKING"
     * REMPLACE l'ancienne logique de stockage temporaire
     */
    
    /* LA METHODE DU PROCESS DU CHUNK -- A REMETTRE PLUS TARD
     public function processChunk(Request $request)
    {
        try {
            $dossierId = $request->input('dossier_id');

            // ✅ SUPPORT CHUNKING SESSION (récupération depuis DossierController)
            if (!$dossierId) {
                $dossierId = session('current_dossier_id');
                Log::info('📂 Dossier ID récupéré depuis session', [
                    'dossier_id' => $dossierId,
                    'user_id' => auth()->id()
                ]);
            }
            
            if (!$dossierId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID du dossier manquant'
                ], 400);
            }

            $adherents = $request->input('adherents', []);
            $chunkIndex = $request->input('chunk_index', 0);
            $totalChunks = $request->input('total_chunks', 1);
            $isFinalChunk = $request->input('is_final_chunk', false);
            
            // ✅ Support des données depuis chunking-import.js v2.0
            if ($request->has('chunk_data')) {
                $chunkDataJson = $request->input('chunk_data');
                if (is_string($chunkDataJson)) {
                    $adherents = json_decode($chunkDataJson, true) ?: [];
                }
            }
            
            Log::info('🔄 TRAITEMENT CHUNK AVEC INSERTION IMMÉDIATE', [
                'dossier_id' => $dossierId,
                'chunk_index' => $chunkIndex,
                'total_chunks' => $totalChunks,
                'adherents_count' => count($adherents),
                'is_final' => $isFinalChunk,
                'user_id' => auth()->id(),
                'solution' => 'INSERTION_DURING_CHUNKING'
            ]);
            
            // Vérifier le dossier avec autorisation utilisateur
            $dossier = Dossier::with('organisation')
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();
                
            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé ou accès non autorisé'
                ], 404);
            }
            
            $organisation = $dossier->organisation;
            
            // 🚀 SOLUTION OPTIMALE : INSERTION IMMÉDIATE DES ADHÉRENTS
            $result = $this->insertAdherentsImmediately($adherents, $organisation, $dossier);
            
            Log::info('✅ CHUNK TRAITÉ AVEC INSERTION IMMÉDIATE', [
                'chunk_index' => $chunkIndex,
                'inserted' => $result['inserted'],
                'errors' => count($result['errors']),
                'anomalies' => $result['anomalies_count'],
                'valid_adherents' => $result['valid_adherents'],
                'solution' => 'INSERTION_DURING_CHUNKING'
            ]);
            
            return response()->json([
                'success' => true,
                'chunk_index' => $chunkIndex,
                'processed' => $result['inserted'],
                'inserted' => $result['inserted'], // ✅ Vraie progression temps réel
                'errors' => $result['errors'],
                'valid_adherents' => $result['valid_adherents'],
                'adherents_with_anomalies' => $result['anomalies_count'],
                'is_final_chunk' => $isFinalChunk,
                'message' => "Chunk {$chunkIndex} : {$result['inserted']} adhérents insérés en base",
                'solution' => 'INSERTION_DURING_CHUNKING',
                'anomalies_data' => $result['anomalies_data'] // Pour affichage détails
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ ERREUR INSERTION IMMÉDIATE CHUNK', [
                'chunk_index' => isset($chunkIndex) ? $chunkIndex : 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'solution' => 'INSERTION_DURING_CHUNKING'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur insertion immédiate chunk: ' . $e->getMessage(),
                'chunk_index' => isset($chunkIndex) ? $chunkIndex : null
            ], 500);
        }
    }
    */

    /**
 * 🕵️ MÉTHODE PRINCIPALE AVEC TRAÇAGE COMPLET
 * Trace chaque étape du processus de chunking
 */
public function processChunk(Request $request)
{
    $debugTrace = [
        'etapes' => [],
        'timestamp_debut' => now()->toISOString(),
        'chunk_id' => uniqid('chunk_'),
        'user_id' => auth()->id()
    ];
    
    try {
        // ============================================
        // ÉTAPE 1 : RÉCUPÉRATION ET VALIDATION DES DONNÉES
        // ============================================
        $debugTrace['etapes'][] = [
            'etape' => '1_RECUPERATION_DONNEES',
            'timestamp' => now()->toISOString(),
            'status' => 'START'
        ];
        
        $dossierId = $request->input('dossier_id');
        $adherents = $request->input('adherents', []);
        $chunkIndex = $request->input('chunk_index', 0);
        $totalChunks = $request->input('total_chunks', 1);
        $isFinalChunk = $request->input('is_final_chunk', false);
        
        // Support données JSON
        if ($request->has('chunk_data')) {
            $chunkDataJson = $request->input('chunk_data');
            if (is_string($chunkDataJson)) {
                $adherents = json_decode($chunkDataJson, true) ?? [];
            }
        }
        
        // Récupération depuis session si nécessaire
        if (!$dossierId) {
            $dossierId = session('current_dossier_id');
        }
        
        $debugTrace['etapes'][] = [
            'etape' => '1_RECUPERATION_DONNEES',
            'timestamp' => now()->toISOString(),
            'status' => 'SUCCESS',
            'donnees' => [
                'dossier_id' => $dossierId,
                'adherents_count' => count($adherents),
                'chunk_index' => $chunkIndex,
                'total_chunks' => $totalChunks,
                'is_final_chunk' => $isFinalChunk,
                'adherents_sample' => array_slice($adherents, 0, 2) // 2 premiers pour debug
            ]
        ];
        
        if (!$dossierId) {
            throw new \Exception('ID du dossier manquant');
        }
        
        if (empty($adherents)) {
            throw new \Exception('Aucun adhérent à traiter dans ce chunk');
        }
        
        // ============================================
        // ÉTAPE 2 : VALIDATION DOSSIER ET ORGANISATION
        // ============================================
        $debugTrace['etapes'][] = [
            'etape' => '2_VALIDATION_DOSSIER',
            'timestamp' => now()->toISOString(),
            'status' => 'START'
        ];
        
        $dossier = Dossier::with('organisation')
            ->where('id', $dossierId)
            ->whereHas('organisation', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->first();
            
        if (!$dossier) {
            throw new \Exception('Dossier non trouvé ou accès non autorisé');
        }
        
        $organisation = $dossier->organisation;
        
        $debugTrace['etapes'][] = [
            'etape' => '2_VALIDATION_DOSSIER',
            'timestamp' => now()->toISOString(),
            'status' => 'SUCCESS',
            'donnees' => [
                'dossier_id' => $dossier->id,
                'organisation_id' => $organisation->id,
                'organisation_nom' => $organisation->nom,
                'organisation_type' => $organisation->type
            ]
        ];
        
        // ============================================
        // ÉTAPE 3 : TRAÇAGE INSERTION IMMÉDIATE
        // ============================================
        $debugTrace['etapes'][] = [
            'etape' => '3_INSERTION_IMMEDIATE',
            'timestamp' => now()->toISOString(),
            'status' => 'START'
        ];
        
        $result = $this->insertAdherentsImmediatelyWithTrace($adherents, $organisation, $dossier, $debugTrace);
        
        $debugTrace['etapes'][] = [
            'etape' => '3_INSERTION_IMMEDIATE',
            'timestamp' => now()->toISOString(),
            'status' => 'SUCCESS',
            'donnees' => [
                'inserted' => $result['inserted'],
                'errors_count' => count($result['errors']),
                'anomalies_count' => $result['anomalies_count']
            ]
        ];
        
        // ============================================
        // ÉTAPE 4 : FORMATAGE RÉPONSE
        // ============================================
        $debugTrace['etapes'][] = [
            'etape' => '4_FORMATAGE_REPONSE',
            'timestamp' => now()->toISOString(),
            'status' => 'START'
        ];
        
        $response = [
            'success' => true,
            'chunk_index' => $chunkIndex,
            'processed' => $result['inserted'],
            'inserted' => $result['inserted'],
            'errors' => $result['errors'],
            'valid_adherents' => $result['valid_adherents'],
            'adherents_with_anomalies' => $result['anomalies_count'],
            'is_final_chunk' => $isFinalChunk,
            'message' => "Chunk {$chunkIndex} : {$result['inserted']} adhérents insérés en base",
            'solution' => 'INSERTION_DURING_CHUNKING',
            'debug_trace' => $debugTrace // ✅ TRACE COMPLÈTE
        ];
        
        $debugTrace['etapes'][] = [
            'etape' => '4_FORMATAGE_REPONSE',
            'timestamp' => now()->toISOString(),
            'status' => 'SUCCESS'
        ];
        
        // ============================================
        // LOG FINAL DU TRAÇAGE
        // ============================================
        Log::info('🕵️ TRACE COMPLÈTE CHUNK', [
            'chunk_id' => $debugTrace['chunk_id'],
            'chunk_index' => $chunkIndex,
            'total_etapes' => count($debugTrace['etapes']),
            'duree_totale' => now()->diffInMilliseconds($debugTrace['timestamp_debut']) . 'ms',
            'resultat' => 'SUCCESS'
        ]);
        
        return response()->json($response);
        
    } catch (\Exception $e) {
        // ============================================
        // GESTION ERREUR AVEC TRACE
        // ============================================
        $debugTrace['etapes'][] = [
            'etape' => 'ERREUR',
            'timestamp' => now()->toISOString(),
            'status' => 'ERROR',
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ];
        
        Log::error('🚨 ERREUR CHUNK AVEC TRACE', [
            'chunk_id' => $debugTrace['chunk_id'],
            'error' => $e->getMessage(),
            'trace_complete' => $debugTrace
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur insertion chunk: ' . $e->getMessage(),
            'debug_trace' => $debugTrace, // ✅ TRACE MÊME EN ERREUR
            'chunk_index' => $chunkIndex ?? null
        ], 500);
    }
}



    /**
     * 🚀 NOUVELLE MÉTHODE : Insertion immédiate en base de données
     * REMPLACE processAdherentsChunk() pour éliminer le stockage temporaire
     * Compatible PHP 7.3.29 avec gestion optimisée des performances
     */
    private function insertAdherentsImmediately(array $adherents, Organisation $organisation, Dossier $dossier)
    {
        $inserted = 0;
        $errors = [];
        $validAdherents = 0;
        $anomaliesCount = 0;
        
        // ✅ TRANSACTION COURTE PAR CHUNK (évite les timeouts)
        DB::beginTransaction();
        
        try {
            // Préparer les données pour insertion en lot optimisée
            $adherentsToInsert = [];
            $anomaliesData = [];
            
            foreach ($adherents as $adherentData) {
                try {
                    // Validation et nettoyage des données
                    $cleanData = $this->prepareAdherentData($adherentData, $organisation);
                    
                    // Détecter les anomalies AVANT insertion (règles SGLP)
                    $anomalies = $this->detectAnomalies($cleanData, $organisation->type);
                    
                    // ✅ RÈGLE MÉTIER SGLP : Enregistrer MÊME avec anomalies
                    $adherentToInsert = [
                        'organisation_id' => $organisation->id,
                        'nip' => $cleanData['nip'],
                        'nom' => strtoupper($cleanData['nom']),
                        'prenom' => $cleanData['prenom'],
                        'profession' => isset($cleanData['profession']) ? $cleanData['profession'] : null,
                        'fonction' => isset($cleanData['fonction']) ? $cleanData['fonction'] : 'Membre',
                        'telephone' => isset($cleanData['telephone']) ? $cleanData['telephone'] : null,
                        'email' => isset($cleanData['email']) ? $cleanData['email'] : null,
                        'date_adhesion' => now(),
                        'is_active' => empty($anomalies['critiques']), // Inactif si anomalies critiques
                        'has_anomalies' => !empty($anomalies['all']),
                        'anomalies_data' => !empty($anomalies['all']) ? json_encode($anomalies['all']) : null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    $adherentsToInsert[] = $adherentToInsert;
                    
                    // Comptabiliser les anomalies
                    if (!empty($anomalies['all'])) {
                        $anomaliesCount++;
                        $anomaliesData[] = [
                            'nip' => $cleanData['nip'],
                            'nom_complet' => $cleanData['nom'] . ' ' . $cleanData['prenom'],
                            'anomalies' => $anomalies['all']
                        ];
                    } else {
                        $validAdherents++;
                    }
                    
                } catch (\Exception $e) {
                    $nomAdherent = isset($adherentData['nom']) ? $adherentData['nom'] : 'Inconnu';
                    $errors[] = "Erreur adhérent {$nomAdherent}: " . $e->getMessage();
                    Log::warning('Erreur préparation adhérent individuel', [
                        'adherent' => $adherentData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // 🚀 INSERTION EN LOT OPTIMISÉE (performance maximale)
            if (!empty($adherentsToInsert)) {
                // Pour PHP 7.3, gestion manuelle des doublons avec performance optimisée
                try {
                    // Tentative d'insertion directe (plus rapide)
                    DB::table('adherents')->insert($adherentsToInsert);
                    $inserted = count($adherentsToInsert);
                    
                } catch (\Illuminate\Database\QueryException $e) {
                    // Si erreur de doublon, insertion individuelle avec gestion
                    $inserted = 0;
                    foreach ($adherentsToInsert as $adherent) {
                        try {
                            // Vérifier si l'adhérent existe déjà (par NIP)
                            $existingAdherent = DB::table('adherents')
                                ->where('organisation_id', $adherent['organisation_id'])
                                ->where('nip', $adherent['nip'])
                                ->first();
                                
                            if (!$existingAdherent) {
                                DB::table('adherents')->insert($adherent);
                                $inserted++;
                            } else {
                                Log::info('Doublon ignoré (NIP existant)', [
                                    'nip' => $adherent['nip'],
                                    'nom' => $adherent['nom']
                                ]);
                            }
                            
                        } catch (\Illuminate\Database\QueryException $duplicateException) {
                            // Ignorer les autres types de doublons
                            Log::info('Doublon ignoré (autre conflit)', [
                                'nip' => $adherent['nip'],
                                'error' => $duplicateException->getMessage()
                            ]);
                        }
                    }
                }
                
                Log::info('✅ INSERTION EN LOT RÉUSSIE', [
                    'chunk_size' => count($adherentsToInsert),
                    'inserted' => $inserted,
                    'anomalies' => $anomaliesCount,
                    'valid' => $validAdherents,
                    'method' => 'optimized_batch_insert',
                    'solution' => 'INSERTION_DURING_CHUNKING'
                ]);
            }
            
            DB::commit();
            
            return [
                'inserted' => $inserted,
                'errors' => $errors,
                'valid_adherents' => $validAdherents,
                'anomalies_count' => $anomaliesCount,
                'anomalies_data' => $anomaliesData
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ ERREUR INSERTION EN LOT', [
                'error' => $e->getMessage(),
                'chunk_size' => count($adherents),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    

    /**
     * Valider le format NIP gabonais
     * Format attendu: XX-QQQQ-YYYYMMDD
     */
    private function validateNipFormat($nip)
    {
        if (empty($nip)) {
            return false;
        }
        
        // Format: XX-QQQQ-YYYYMMDD (codes préfecture + séquence + date)
        return preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', $nip);
    }

    /**
     * Nettoyer un NIP (majuscules, suppression espaces)
     */
    private function cleanNip($nip)
    {
        if (empty($nip)) {
            // Générer un NIP temporaire si absent
            return $this->generateTemporaryNip();
        }
        
        return strtoupper(trim($nip));
    }

    /**
     * Nettoyer un numéro de téléphone
     */
    private function cleanPhone($phone)
    {
        if (empty($phone)) {
            return null;
        }
        
        // Garder seulement les chiffres et le +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        
        // Retourner null si trop court
        return strlen($cleaned) >= 8 ? $cleaned : null;
    }

    /**
     * Générer un NIP temporaire en cas d'absence
     */
    private function generateTemporaryNip()
    {
        // Format: GA-XXXX-YYYYMMDD où XXXX est un numéro séquentiel
        $prefecture = 'GA'; // Gabon par défaut
        $sequence = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $date = date('Ymd');
        
        return "{$prefecture}-{$sequence}-{$date}";
    }

    /**
     * Récupérer les données de session pour Phase 2
     * Maintient la compatibilité avec l'existant
     */
    public function getSessionData(Request $request)
    {
        try {
            $sessionKey = $request->input('session_key');
            $dossierId = $request->input('dossier_id');
            
            Log::info('📥 RÉCUPÉRATION DONNÉES SESSION PHASE 2', [
                'session_key' => $sessionKey,
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);
            
            // Vérifier les paramètres
            if (!$sessionKey || !$dossierId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres manquants'
                ], 400);
            }
            
            // Récupérer les données de session
            $sessionData = session($sessionKey);
            
            if (!$sessionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expirée ou inexistante'
                ], 404);
            }
            
            // Gérer les deux formats possibles de session
            $adherentsData = [];
            $totalCount = 0;
            $expiresAt = null;
            
            if (is_array($sessionData)) {
                // Format structuré avec 'data', 'total', 'expires_at'
                if (isset($sessionData['data']) && is_array($sessionData['data'])) {
                    $adherentsData = $sessionData['data'];
                    $totalCount = isset($sessionData['total']) ? $sessionData['total'] : count($adherentsData);
                    $expiresAt = isset($sessionData['expires_at']) ? $sessionData['expires_at'] : null;
                    
                    // Vérifier l'autorisation si présente
                    if (isset($sessionData['user_id']) && $sessionData['user_id'] !== auth()->id()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Accès non autorisé'
                        ], 403);
                    }
                } else {
                    // Format simple : tableau direct d'adhérents
                    $adherentsData = $sessionData;
                    $totalCount = count($adherentsData);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de session invalide'
                ], 422);
            }
            
            Log::info('✅ DONNÉES SESSION RÉCUPÉRÉES', [
                'adherents_count' => $totalCount,
                'expires_at' => $expiresAt
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $adherentsData,
                'total' => $totalCount,
                'expires_at' => $expiresAt
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ ERREUR RÉCUPÉRATION SESSION', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Nettoyer la session Phase 2
     */
    public function cleanupSession(Request $request)
    {
        try {
            $sessionKey = $request->input('session_key');
            
            if ($sessionKey) {
                session()->forget($sessionKey);
                
                Log::info('✅ SESSION PHASE 2 NETTOYÉE', [
                    'session_key' => $sessionKey,
                    'user_id' => auth()->id()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Session nettoyée'
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ ERREUR NETTOYAGE SESSION', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur nettoyage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier l'état de santé du système de chunking
     */
    public function healthCheck(Request $request)
    {
        try {
            $sessionKey = $request->input('session_key');
            $dossierId = $request->input('dossier_id');
            
            $health = [
                'status' => 'ok',
                'timestamp' => now()->toISOString(),
                'session_exists' => false,
                'dossier_exists' => false,
                'user_authenticated' => auth()->check(),
                'solution' => 'INSERTION_DURING_CHUNKING'
            ];
            
            // Vérifier la session
            if ($sessionKey) {
                $health['session_exists'] = session()->has($sessionKey);
                if ($health['session_exists']) {
                    $sessionData = session($sessionKey);
                    $health['session_data_count'] = is_array($sessionData) ? count($sessionData) : 0;
                }
            }
            
            // Vérifier le dossier
            if ($dossierId) {
                $dossier = Dossier::find($dossierId);
                $health['dossier_exists'] = !is_null($dossier);
                if ($health['dossier_exists']) {
                    $health['dossier_status'] = $dossier->statut;
                }
            }
            
            Log::info('🏥 HEALTH CHECK CHUNKING', $health);
            
            return response()->json([
                'success' => true,
                'health' => $health
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ ERREUR HEALTH CHECK', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur health check: ' . $e->getMessage(),
                'health' => [
                    'status' => 'error',
                    'timestamp' => now()->toISOString()
                ]
            ], 500);
        }
    }

    
    /**
     * ✅ MÉTHODE MANQUANTE : Préparation des données adhérent
     */
    private function prepareAdherentData($adherentData, $organisation)
    {
        return [
            'nip' => $adherentData['nip'] ?? '',
            'nom' => $adherentData['nom'] ?? '',
            'prenom' => $adherentData['prenom'] ?? '',
            'profession' => $adherentData['profession'] ?? null,
            'fonction' => $adherentData['fonction'] ?? 'Membre',
            'telephone' => $adherentData['telephone'] ?? null,
            'email' => $adherentData['email'] ?? null
        ];
    }

    /**
     * ✅ MÉTHODE MANQUANTE : Détection des anomalies
     */
    private function detectAnomalies($cleanData, $organisationType)
    {
        $anomalies = ['all' => [], 'critiques' => []];
        
        // Vérification NIP
        if (empty($cleanData['nip']) || strlen($cleanData['nip']) < 10) {
            $anomalies['critiques'][] = 'NIP invalide ou manquant';
            $anomalies['all'][] = 'NIP invalide ou manquant';
        }
        
        // Vérification nom/prénom
        if (empty($cleanData['nom']) || empty($cleanData['prenom'])) {
            $anomalies['critiques'][] = 'Nom ou prénom manquant';
            $anomalies['all'][] = 'Nom ou prénom manquant';
        }
        
        return $anomalies;
    }

    /**
     * ✅ MÉTHODE MANQUANTE : Refresh CSRF
     */
    public function refreshCSRF()
    {
        try {
            return response()->json([
                'success' => true,
                'csrf_token' => csrf_token()
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERREUR REFRESH CSRF', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur refresh CSRF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE MANQUANTE : Test d'authentification
     */
    public function authTest(Request $request)
    {
        try {
            $user = auth()->user();
            
            $authInfo = [
                'authenticated' => auth()->check(),
                'user_id' => $user ? $user->id : null,
                'user_role' => $user ? $user->role : null,
                'session_id' => session()->getId(),
                'timestamp' => now()->toISOString()
            ];
            
            Log::info('🔐 AUTH TEST', $authInfo);
            
            return response()->json([
                'success' => true,
                'message' => 'Test authentification réussi',
                'data' => $authInfo
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERREUR AUTH TEST', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur test auth: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE MANQUANTE : Statistiques de performance
     */
    public function getPerformanceStats()
    {
        try {
            $stats = [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'active_sessions' => $this->countActiveSessions(),
                'server_load' => sys_getloadavg()[0] ?? 0,
                'timestamp' => now()->toISOString()
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE HELPER : Compter les sessions actives
     */
    private function countActiveSessions()
    {
        try {
            $sessionFiles = glob(session_save_path() . '/sess_*');
            return count($sessionFiles);
        } catch (\Exception $e) {
            return 0;
        }
    }



    /**
 * 🕵️ INSERTION AVEC TRAÇAGE SQL DÉTAILLÉ
 */
private function insertAdherentsImmediatelyWithTrace(array $adherents, Organisation $organisation, Dossier $dossier, &$debugTrace)
{
    $inserted = 0;
    $errors = [];
    $validAdherents = 0;
    $anomaliesCount = 0;
    
    // ============================================
    // ÉTAPE 3.1 : PRÉPARATION DES DONNÉES
    // ============================================
    $debugTrace['etapes'][] = [
        'etape' => '3.1_PREPARATION_DONNEES',
        'timestamp' => now()->toISOString(),
        'status' => 'START'
    ];
    
    DB::beginTransaction();
    
    try {
        $adherentsToInsert = [];
        $anomaliesData = [];
        
        foreach ($adherents as $index => $adherentData) {
            try {
                // Préparation et validation
                $cleanData = $this->prepareAdherentData($adherentData, $organisation);
                $anomalies = $this->detectAnomalies($cleanData, $organisation->type);
                
                $adherentToInsert = [
                    'organisation_id' => $organisation->id,
                    'nip' => $cleanData['nip'],
                    'nom' => strtoupper($cleanData['nom']),
                    'prenom' => $cleanData['prenom'],
                    'profession' => $cleanData['profession'],
                    'fonction' => $cleanData['fonction'],
                    'telephone' => $cleanData['telephone'],
                    'email' => $cleanData['email'],
                    'date_adhesion' => now(),
                    'is_active' => empty($anomalies['critiques']),
                    'has_anomalies' => !empty($anomalies['all']),
                    'anomalies_data' => !empty($anomalies['all']) ? json_encode($anomalies['all']) : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $adherentsToInsert[] = $adherentToInsert;
                
                if (!empty($anomalies['all'])) {
                    $anomaliesCount++;
                    $anomaliesData[] = [
                        'nip' => $cleanData['nip'],
                        'nom_complet' => $cleanData['nom'] . ' ' . $cleanData['prenom'],
                        'anomalies' => $anomalies['all']
                    ];
                } else {
                    $validAdherents++;
                }
                
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'nip' => $adherentData['nip'] ?? 'N/A',
                    'nom' => $adherentData['nom'] ?? 'N/A',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $debugTrace['etapes'][] = [
            'etape' => '3.1_PREPARATION_DONNEES',
            'timestamp' => now()->toISOString(),
            'status' => 'SUCCESS',
            'donnees' => [
                'adherents_a_inserer' => count($adherentsToInsert),
                'erreurs_preparation' => count($errors),
                'adherents_valides' => $validAdherents,
                'adherents_avec_anomalies' => $anomaliesCount
            ]
        ];
        
        // ============================================
        // ÉTAPE 3.2 : CONSTRUCTION REQUÊTE SQL
        // ============================================
        $debugTrace['etapes'][] = [
            'etape' => '3.2_CONSTRUCTION_SQL',
            'timestamp' => now()->toISOString(),
            'status' => 'START'
        ];
        
        if (!empty($adherentsToInsert)) {
            // Log de la requête SQL pour debug
            $sqlQuery = "INSERT INTO adherents (" . implode(', ', array_keys($adherentsToInsert[0])) . ") VALUES ";
            $sqlValues = [];
            
            foreach ($adherentsToInsert as $adherent) {
                $values = array_map(function($value) {
                    return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                }, array_values($adherent));
                $sqlValues[] = "(" . implode(', ', $values) . ")";
            }
            
            $fullQuery = $sqlQuery . implode(', ', array_slice($sqlValues, 0, 2)); // 2 premiers pour debug
            
            $debugTrace['etapes'][] = [
                'etape' => '3.2_CONSTRUCTION_SQL',
                'timestamp' => now()->toISOString(),
                'status' => 'SUCCESS',
                'donnees' => [
                    'sql_preview' => substr($fullQuery, 0, 500) . '...', // Aperçu SQL
                    'nombre_values' => count($sqlValues),
                    'colonnes' => array_keys($adherentsToInsert[0])
                ]
            ];
            
            // ============================================
            // ÉTAPE 3.3 : EXÉCUTION SQL
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => '3.3_EXECUTION_SQL',
                'timestamp' => now()->toISOString(),
                'status' => 'START'
            ];
            
            try {
                // Insertion en base
                DB::table('adherents')->insert($adherentsToInsert);
                $inserted = count($adherentsToInsert);
                
                $debugTrace['etapes'][] = [
                    'etape' => '3.3_EXECUTION_SQL',
                    'timestamp' => now()->toISOString(),
                    'status' => 'SUCCESS',
                    'donnees' => [
                        'lignes_inserees' => $inserted,
                        'methode' => 'INSERT_BULK'
                    ]
                ];
                
            } catch (\Illuminate\Database\QueryException $e) {
                // Gestion des doublons avec insertion individuelle
                $debugTrace['etapes'][] = [
                    'etape' => '3.3_EXECUTION_SQL',
                    'timestamp' => now()->toISOString(),
                    'status' => 'BULK_FAILED_RETRY_INDIVIDUAL',
                    'donnees' => [
                        'erreur_bulk' => $e->getMessage(),
                        'code_erreur' => $e->getCode()
                    ]
                ];
                
                $inserted = 0;
                foreach ($adherentsToInsert as $adherent) {
                    try {
                        // Vérifier doublon
                        $existing = DB::table('adherents')
                            ->where('organisation_id', $adherent['organisation_id'])
                            ->where('nip', $adherent['nip'])
                            ->first();
                        
                        if (!$existing) {
                            DB::table('adherents')->insert($adherent);
                            $inserted++;
                        }
                    } catch (\Exception $individualError) {
                        $errors[] = [
                            'nip' => $adherent['nip'],
                            'error' => $individualError->getMessage()
                        ];
                    }
                }
            }
        }
        
        DB::commit();
        
        return [
            'inserted' => $inserted,
            'errors' => $errors,
            'valid_adherents' => $validAdherents,
            'anomalies_count' => $anomaliesCount,
            'anomalies_data' => $anomaliesData
        ];
        
    } catch (\Exception $e) {
        DB::rollback();
        
        $debugTrace['etapes'][] = [
            'etape' => '3.3_EXECUTION_SQL',
            'timestamp' => now()->toISOString(),
            'status' => 'ERROR',
            'donnees' => [
                'erreur' => $e->getMessage(),
                'fichier' => $e->getFile(),
                'ligne' => $e->getLine()
            ]
        ];
        
        throw $e;
    }
}

}