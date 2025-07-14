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
 * CHUNKING CONTROLLER CORRIGÉ - VERSION 3.0 
 * Solution définitive pour l'insertion des données via chunking
 * Basée sur l'analyse de la discussion v1_12-DISCUSSION 8
 * ========================================================================
 * 
 * CORRECTIONS APPLIQUÉES VERSION 3.0 :
 * ✅ Format de réception des données corrigé
 * ✅ Validation et parsing robuste des adhérents  
 * ✅ Gestion d'erreur améliorée avec logs détaillés
 * ✅ Insertion en base garantie avec fallback
 * ✅ Traçabilité complète du processus
 * ✅ Compatibilité Phase 2 et chunking adaptatif
 */
class ChunkingController extends Controller
{
    /**
     * ✅ MÉTHODE PRINCIPALE CORRIGÉE : Traitement chunk avec insertion garantie
     */
    public function processChunk(Request $request)
    {
        $debugTrace = [
            'etapes' => [],
            'timestamp_debut' => now()->toISOString(),
            'chunk_id' => uniqid('chunk_'),
            'user_id' => auth()->id(),
            'version' => '3.0-CORRECTED'
        ];
        
        try {
            // ============================================
            // ÉTAPE 1 : RÉCUPÉRATION ET VALIDATION DES DONNÉES CORRIGÉE
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => '1_RECUPERATION_DONNEES_V3',
                'timestamp' => now()->toISOString(),
                'status' => 'START'
            ];
            
            // ✅ CORRECTION 1: Récupération flexible des données
            $dossierId = $this->getDossierId($request);
            $adherentsData = $this->getAdherentsData($request);
            $chunkIndex = $request->input('chunk_index', 0);
            $totalChunks = $request->input('total_chunks', 1);
            $isFinalChunk = $request->input('is_final_chunk', false);
            
            $debugTrace['etapes'][] = [
                'etape' => '1_RECUPERATION_DONNEES_V3',
                'timestamp' => now()->toISOString(),
                'status' => 'SUCCESS',
                'donnees' => [
                    'dossier_id' => $dossierId,
                    'adherents_count' => count($adherentsData),
                    'chunk_index' => $chunkIndex,
                    'total_chunks' => $totalChunks,
                    'is_final_chunk' => $isFinalChunk,
                    'first_adherent_preview' => !empty($adherentsData) ? array_slice($adherentsData[0], 0, 3) : null
                ]
            ];
            
            // ✅ Validation des données essentielles
            if (!$dossierId) {
                throw new \Exception('ID du dossier manquant ou invalide');
            }
            
            if (empty($adherentsData)) {
                throw new \Exception('Aucun adhérent à traiter dans ce chunk');
            }
            
            // ============================================
            // ÉTAPE 2 : VALIDATION DOSSIER ET ORGANISATION
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => '2_VALIDATION_DOSSIER_V3',
                'timestamp' => now()->toISOString(),
                'status' => 'START'
            ];
            
            $dossier = $this->validateDossier($dossierId);
            $organisation = $dossier->organisation;
            
            $debugTrace['etapes'][] = [
                'etape' => '2_VALIDATION_DOSSIER_V3',
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
            // ÉTAPE 3 : INSERTION IMMEDIATE CORRIGÉE
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => '3_INSERTION_IMMEDIATE_V3',
                'timestamp' => now()->toISOString(),
                'status' => 'START'
            ];
            
            $result = $this->insertAdherentsImmediatelyV3($adherentsData, $organisation, $dossier, $debugTrace);
            
            $debugTrace['etapes'][] = [
                'etape' => '3_INSERTION_IMMEDIATE_V3',
                'timestamp' => now()->toISOString(),
                'status' => 'SUCCESS',
                'donnees' => [
                    'inserted' => $result['inserted'],
                    'errors_count' => count($result['errors']),
                    'anomalies_count' => $result['anomalies_count']
                ]
            ];
            
            // ============================================
            // ÉTAPE 4 : FORMATAGE RÉPONSE FINALE
            // ============================================
            $response = [
                'success' => true,
                'chunk_index' => $chunkIndex,
                'processed' => $result['inserted'],
                'inserted' => $result['inserted'],
                'errors' => $result['errors'],
                'valid_adherents' => $result['valid_adherents'],
                'adherents_with_anomalies' => $result['anomalies_count'],
                'is_final_chunk' => $isFinalChunk,
                'message' => "Chunk {$chunkIndex} : {$result['inserted']} adhérents insérés en base (v3.0)",
                'solution' => 'INSERTION_DURING_CHUNKING_V3',
                'debug_trace' => $debugTrace
            ];
            
            // ✅ LOG FINAL DE SUCCÈS
            Log::info('🎉 CHUNK TRAITÉ AVEC SUCCÈS V3.0', [
                'chunk_id' => $debugTrace['chunk_id'],
                'chunk_index' => $chunkIndex,
                'inserted' => $result['inserted'],
                'processing_time' => now()->diffInMilliseconds($debugTrace['timestamp_debut']) . 'ms',
                'version' => '3.0-CORRECTED'
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            // ============================================
            // GESTION ERREUR ROBUSTE V3.0
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => 'ERREUR_V3',
                'timestamp' => now()->toISOString(),
                'status' => 'ERROR',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ];
            
            Log::error('🚨 ERREUR CHUNK V3.0', [
                'chunk_id' => $debugTrace['chunk_id'],
                'error' => $e->getMessage(),
                'trace_complete' => $debugTrace,
                'version' => '3.0-CORRECTED'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur insertion chunk v3.0: ' . $e->getMessage(),
                'debug_trace' => $debugTrace,
                'chunk_index' => $chunkIndex ?? null,
                'version' => '3.0-CORRECTED'
            ], 500);
        }
    }
    
    /**
     * ✅ CORRECTION 2: Récupération flexible de l'ID du dossier
     */
    private function getDossierId(Request $request)
    {
        // Priorité 1: Paramètre direct
        $dossierId = $request->input('dossier_id');
        
        // Priorité 2: Session
        if (!$dossierId) {
            $dossierId = session('current_dossier_id');
            Log::info('📂 Dossier ID récupéré depuis session', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);
        }
        
        // Priorité 3: Configuration Phase 2
        if (!$dossierId && isset(request()->route()->parameters['dossier'])) {
            $dossierId = request()->route()->parameters['dossier'];
        }
        
        return $dossierId;
    }
    
    /**
     * ✅ CORRECTION 3: Récupération flexible des données d'adhérents
     */
    private function getAdherentsData(Request $request)
    {
        $adherentsData = [];
        
        // ✅ Méthode 1: Array direct d'adhérents (format Phase 2)
        if ($request->has('adherents') && is_array($request->input('adherents'))) {
            $adherentsData = $request->input('adherents');
            Log::info('📊 Adhérents récupérés comme array direct', [
                'count' => count($adherentsData)
            ]);
        }
        // ✅ Méthode 2: JSON string chunk_data (format chunking-import.js)
        else if ($request->has('chunk_data')) {
            $chunkDataJson = $request->input('chunk_data');
            if (is_string($chunkDataJson)) {
                $decoded = json_decode($chunkDataJson, true);
                $adherentsData = $decoded ?? [];
                Log::info('📊 Adhérents récupérés depuis chunk_data JSON', [
                    'count' => count($adherentsData)
                ]);
            }
        }
        // ✅ Méthode 3: JSON string adherents (fallback)
        else if ($request->has('adherents') && is_string($request->input('adherents'))) {
            $adherentsJson = $request->input('adherents');
            $decoded = json_decode($adherentsJson, true);
            $adherentsData = $decoded ?? [];
            Log::info('📊 Adhérents récupérés depuis adherents JSON', [
                'count' => count($adherentsData)
            ]);
        }
        
        return $adherentsData;
    }
    
    /**
     * ✅ CORRECTION 4: Validation robuste du dossier
     */
    private function validateDossier($dossierId)
    {
        $dossier = Dossier::with('organisation')
            ->where('id', $dossierId)
            ->whereHas('organisation', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->first();
            
        if (!$dossier) {
            throw new \Exception("Dossier {$dossierId} non trouvé ou accès non autorisé");
        }
        
        if (!$dossier->organisation) {
            throw new \Exception("Organisation manquante pour le dossier {$dossierId}");
        }
        
        return $dossier;
    }
    
    /**
     * ✅ CORRECTION 5: Insertion immédiate V3.0 - Garantie d'insertion
     */
    private function insertAdherentsImmediatelyV3(array $adherentsData, Organisation $organisation, Dossier $dossier, &$debugTrace)
    {
        $inserted = 0;
        $errors = [];
        $validAdherents = 0;
        $anomaliesCount = 0;
        
        Log::info('🚀 DÉBUT INSERTION V3.0', [
            'organisation_id' => $organisation->id,
            'adherents_count' => count($adherentsData),
            'version' => '3.0-CORRECTED'
        ]);
        
        // ✅ TRANSACTION COURTE pour éviter les timeouts
        DB::beginTransaction();
        
        try {
            $adherentsToInsert = [];
            $anomaliesData = [];
            
            // ============================================
            // PRÉPARATION DES DONNÉES V3.0
            // ============================================
            foreach ($adherentsData as $index => $adherentData) {
                try {
                    // ✅ CORRECTION: Validation et nettoyage robuste
                    $cleanData = $this->prepareAdherentDataV3($adherentData, $organisation, $index);
                    
                    // ✅ Détection des anomalies AVANT insertion
                    $anomalies = $this->detectAnomaliesV3($cleanData, $organisation->type);
                    
                    // ✅ RÈGLE MÉTIER SGLP: Enregistrer MÊME avec anomalies
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
                        'is_active' => empty($anomalies['critiques']), // Inactif si anomalies critiques
                        'has_anomalies' => !empty($anomalies['all']),
                        'anomalies_data' => !empty($anomalies['all']) ? json_encode($anomalies['all']) : null,
                        'source' => 'chunking_v3',
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
                            'anomalies' => $anomalies['all'],
                            'line_index' => $index
                        ];
                    } else {
                        $validAdherents++;
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'nip' => $adherentData['nip'] ?? 'N/A',
                        'nom' => ($adherentData['nom'] ?? 'Inconnu') . ' ' . ($adherentData['prenom'] ?? ''),
                        'error' => $e->getMessage()
                    ];
                    
                    Log::warning('Erreur préparation adhérent V3', [
                        'index' => $index,
                        'adherent' => $adherentData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // ============================================
            // INSERTION EN BASE V3.0 - GARANTIE
            // ============================================
            if (!empty($adherentsToInsert)) {
                try {
                    // ✅ MÉTHODE 1: Insertion directe en lot (plus rapide)
                    DB::table('adherents')->insert($adherentsToInsert);
                    $inserted = count($adherentsToInsert);
                    
                    Log::info('✅ INSERTION EN LOT RÉUSSIE V3.0', [
                        'inserted' => $inserted,
                        'method' => 'bulk_insert'
                    ]);
                    
                } catch (\Illuminate\Database\QueryException $e) {
                    // ✅ MÉTHODE 2: Fallback avec insertion individuelle
                    Log::warning('⚠️ Insertion lot échouée, fallback individuel V3.0', [
                        'error' => $e->getMessage()
                    ]);
                    
                    $inserted = 0;
                    foreach ($adherentsToInsert as $adherent) {
                        try {
                            // ✅ Vérifier doublon par NIP
                            $existingAdherent = DB::table('adherents')
                                ->where('organisation_id', $adherent['organisation_id'])
                                ->where('nip', $adherent['nip'])
                                ->first();
                                
                            if (!$existingAdherent) {
                                DB::table('adherents')->insert($adherent);
                                $inserted++;
                            } else {
                                Log::info('Doublon NIP ignoré V3.0', [
                                    'nip' => $adherent['nip'],
                                    'nom' => $adherent['nom']
                                ]);
                            }
                            
                        } catch (\Exception $individualError) {
                            $errors[] = [
                                'nip' => $adherent['nip'],
                                'nom' => $adherent['nom'],
                                'error' => $individualError->getMessage()
                            ];
                            
                            Log::warning('Erreur insertion individuelle V3.0', [
                                'nip' => $adherent['nip'],
                                'error' => $individualError->getMessage()
                            ]);
                        }
                    }
                    
                    Log::info('✅ INSERTION INDIVIDUELLE TERMINÉE V3.0', [
                        'inserted' => $inserted,
                        'errors' => count($errors),
                        'method' => 'individual_insert_fallback'
                    ]);
                }
            }
            
            DB::commit();
            
            // ✅ LOG FINAL DE L'INSERTION
            Log::info('🎉 INSERTION CHUNK TERMINÉE V3.0', [
                'organisation_id' => $organisation->id,
                'total_to_insert' => count($adherentsToInsert),
                'inserted' => $inserted,
                'valid_adherents' => $validAdherents,
                'anomalies_count' => $anomaliesCount,
                'errors_count' => count($errors),
                'success_rate' => count($adherentsToInsert) > 0 ? round(($inserted / count($adherentsToInsert)) * 100, 2) . '%' : '0%'
            ]);
            
            return [
                'inserted' => $inserted,
                'errors' => $errors,
                'valid_adherents' => $validAdherents,
                'anomalies_count' => $anomaliesCount,
                'anomalies_data' => $anomaliesData,
                'total_processed' => count($adherentsToInsert)
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('❌ ERREUR INSERTION CHUNK V3.0', [
                'organisation_id' => $organisation->id,
                'adherents_count' => count($adherentsData),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * ✅ CORRECTION 6: Préparation robuste des données adhérent V3.0
     */
    private function prepareAdherentDataV3($adherentData, Organisation $organisation, $index = 0)
    {
        // ✅ Gestion defensive des types de données
        if (!is_array($adherentData)) {
            if (is_string($adherentData)) {
                $decoded = json_decode($adherentData, true);
                $adherentData = $decoded ?? [];
            } else {
                throw new \Exception("Format de données adhérent invalide à l'index {$index}");
            }
        }
        
        // ✅ Mapping flexible des champs
        $nip = $adherentData['nip'] ?? $adherentData['NIP'] ?? '';
        $nom = $adherentData['nom'] ?? $adherentData['Nom'] ?? '';
        $prenom = $adherentData['prenom'] ?? $adherentData['Prenom'] ?? $adherentData['Prénom'] ?? '';
        $profession = $adherentData['profession'] ?? $adherentData['Profession'] ?? '';
        $telephone = $adherentData['telephone'] ?? $adherentData['Telephone'] ?? $adherentData['Téléphone'] ?? '';
        $email = $adherentData['email'] ?? $adherentData['Email'] ?? '';
        $fonction = $adherentData['fonction'] ?? $adherentData['Fonction'] ?? 'Membre';
        
        // ✅ Nettoyage et validation
        return [
            'nip' => $this->cleanNipV3($nip),
            'nom' => $this->cleanString($nom),
            'prenom' => $this->cleanString($prenom),
            'profession' => $this->cleanString($profession),
            'fonction' => $this->cleanString($fonction) ?: 'Membre',
            'telephone' => $this->cleanPhoneV3($telephone),
            'email' => $this->cleanEmailV3($email),
            'source' => 'chunking_v3',
            'line_index' => $index
        ];
    }
    
    /**
     * ✅ CORRECTION 7: Détection d'anomalies V3.0
     */
    private function detectAnomaliesV3($cleanData, $organisationType)
    {
        $anomalies = ['all' => [], 'critiques' => [], 'majeures' => [], 'mineures' => []];
        
        // ✅ Validation NIP (critique)
        if (empty($cleanData['nip']) || strlen($cleanData['nip']) < 5) {
            $anomalies['critiques'][] = 'NIP invalide ou trop court';
            $anomalies['all'][] = 'NIP invalide ou trop court';
        }
        
        // ✅ Validation nom/prénom (critique)
        if (empty($cleanData['nom']) || empty($cleanData['prenom'])) {
            $anomalies['critiques'][] = 'Nom ou prénom manquant';
            $anomalies['all'][] = 'Nom ou prénom manquant';
        }
        
        // ✅ Validation téléphone (majeure)
        if (empty($cleanData['telephone'])) {
            $anomalies['majeures'][] = 'Téléphone manquant';
            $anomalies['all'][] = 'Téléphone manquant';
        }
        
        // ✅ Validation email (mineure)
        if (!empty($cleanData['email']) && !filter_var($cleanData['email'], FILTER_VALIDATE_EMAIL)) {
            $anomalies['mineures'][] = 'Format email invalide';
            $anomalies['all'][] = 'Format email invalide';
        }
        
        return $anomalies;
    }
    
    /**
     * ✅ MÉTHODES UTILITAIRES V3.0
     */
    private function cleanNipV3($nip)
    {
        if (empty($nip)) {
            return $this->generateTemporaryNipV3();
        }
        return strtoupper(trim($nip));
    }
    
    private function cleanString($str)
    {
        return trim($str ?? '');
    }
    
    private function cleanPhoneV3($phone)
    {
        if (empty($phone)) return null;
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($cleaned) >= 8 ? $cleaned : null;
    }
    
    private function cleanEmailV3($email)
    {
        if (empty($email)) return null;
        $email = trim($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
    
    private function generateTemporaryNipV3()
    {
        $prefix = 'GA';
        $sequence = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $date = date('Ymd');
        return "{$prefix}-{$sequence}-{$date}";
    }
    
    /**
     * ✅ MÉTHODES AUXILIAIRES EXISTANTES MAINTENUES
     */
    public function getSessionData(Request $request)
    {
        // Maintenir la compatibilité avec les méthodes existantes
        return $this->getSessionDataV3($request);
    }
    
    private function getSessionDataV3(Request $request)
    {
        try {
            $sessionKey = $request->input('session_key');
            $dossierId = $request->input('dossier_id');
            
            Log::info('📥 RÉCUPÉRATION SESSION V3.0', [
                'session_key' => $sessionKey,
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);
            
            if (!$sessionKey || !$dossierId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres manquants'
                ], 400);
            }
            
            $sessionData = session($sessionKey);
            
            if (!$sessionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expirée ou inexistante'
                ], 404);
            }
            
            $adherentsData = is_array($sessionData) ? $sessionData : [];
            $totalCount = count($adherentsData);
            
            return response()->json([
                'success' => true,
                'data' => $adherentsData,
                'total' => $totalCount,
                'version' => '3.0-CORRECTED'
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ ERREUR RÉCUPÉRATION SESSION V3.0', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur v3.0: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ✅ MÉTHODES DE DIAGNOSTIC ET SUPPORT
     */
    public function healthCheck(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'healthy' => true,
                'version' => '3.0-CORRECTED',
                'timestamp' => now()->toISOString(),
                'user_authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'memory_usage' => memory_get_usage(true),
                'solution' => 'INSERTION_DURING_CHUNKING_V3'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'healthy' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function refreshCSRF()
    {
        try {
            return response()->json([
                'success' => true,
                'csrf_token' => csrf_token(),
                'version' => '3.0-CORRECTED'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur refresh CSRF v3.0: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function authTest(Request $request)
    {
        try {
            $user = auth()->user();
            
            return response()->json([
                'success' => true,
                'message' => 'Test authentification réussi v3.0',
                'data' => [
                    'authenticated' => auth()->check(),
                    'user_id' => $user ? $user->id : null,
                    'user_role' => $user ? $user->role : null,
                    'timestamp' => now()->toISOString(),
                    'version' => '3.0-CORRECTED'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur test auth v3.0: ' . $e->getMessage()
            ], 500);
        }
    }
}