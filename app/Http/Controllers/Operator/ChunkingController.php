<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;  // ✅ CORRECTION : Import correct du Controller de base
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\Adherent;

/**
 * ✅ CHUNKING CONTROLLER COMPLET POUR PHASE 2 - VERSION FINALE CORRIGÉE
 * Gestion du traitement par lots des adhérents
 * Compatible PHP 7.3.29 avec Laravel
 */
class ChunkingController extends Controller
{
    /**
     * Récupérer les données de session pour Phase 2
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
            
            Log::info('🔍 ANALYSE STRUCTURE SESSION', [
                'session_key' => $sessionKey,
                'data_type' => gettype($sessionData),
                'data_keys' => is_array($sessionData) ? array_keys($sessionData) : 'N/A',
                'is_structured' => is_array($sessionData) && isset($sessionData['data'])
            ]);
            
            // ✅ NOUVEAU : Gérer les deux formats possibles de session
            $adherentsData = [];
            $totalCount = 0;
            $expiresAt = null;
            
            if (is_array($sessionData)) {
                // Format structuré avec 'data', 'total', 'expires_at'
                if (isset($sessionData['data']) && is_array($sessionData['data'])) {
                    $adherentsData = $sessionData['data'];
                    $totalCount = $sessionData['total'] ?? count($adherentsData);
                    $expiresAt = $sessionData['expires_at'] ?? null;
                    
                    // Vérifier l'autorisation si présente
                    if (isset($sessionData['user_id']) && $sessionData['user_id'] !== auth()->id()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Accès non autorisé'
                        ], 403);
                    }
                    
                    Log::info('📋 FORMAT STRUCTURÉ DÉTECTÉ', [
                        'adherents_count' => $totalCount,
                        'has_expiration' => !is_null($expiresAt),
                        'expires_at' => $expiresAt
                    ]);
                    
                } else {
                    // Format simple : tableau direct d'adhérents
                    $adherentsData = $sessionData;
                    $totalCount = count($adherentsData);
                    
                    Log::info('📋 FORMAT SIMPLE DÉTECTÉ', [
                        'adherents_count' => $totalCount
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de session invalide'
                ], 422);
            }
            
            // Vérifier l'expiration si disponible
            if ($expiresAt) {
                try {
                    $expirationTime = \Carbon\Carbon::parse($expiresAt);
                    if ($expirationTime->isPast()) {
                        // Nettoyer la session expirée
                        session()->forget($sessionKey);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Session expirée'
                        ], 410);
                    }
                } catch (\Exception $e) {
                    Log::warning('⚠️ Erreur parsing expiration', ['expires_at' => $expiresAt]);
                }
            }
            
            Log::info('✅ DONNÉES SESSION RÉCUPÉRÉES', [
                'adherents_count' => $totalCount,
                'first_item_keys' => !empty($adherentsData) && is_array($adherentsData[0]) ? array_keys($adherentsData[0]) : [],
                'expires_at' => $expiresAt,
                'session_sample' => !empty($adherentsData) ? array_slice($adherentsData, 0, 1) : []
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
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Traiter un chunk d'adhérents
     */
    public function processChunk(Request $request)
    {
        try {
            $dossierId = $request->input('dossier_id');
            $adherents = $request->input('adherents', []);
            $chunkIndex = $request->input('chunk_index', 0);
            $totalChunks = $request->input('total_chunks', 1);
            $isFinalChunk = $request->input('is_final_chunk', false);
            
            Log::info('🔄 TRAITEMENT CHUNK PHASE 2', [
                'dossier_id' => $dossierId,
                'chunk_index' => $chunkIndex,
                'total_chunks' => $totalChunks,
                'adherents_count' => count($adherents),
                'is_final' => $isFinalChunk,
                'user_id' => auth()->id()
            ]);
            
            // Vérifier le dossier
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
            
            // Traiter les adhérents du chunk avec le système d'anomalies existant
            $result = $this->processAdherentsChunk($adherents, $organisation, $dossier);
            
            Log::info('✅ CHUNK TRAITÉ AVEC SUCCÈS', [
                'chunk_index' => $chunkIndex,
                'processed' => $result['processed'],
                'errors' => $result['errors'],
                'adherents_with_anomalies' => $result['adherents_with_anomalies']
            ]);
            
            return response()->json([
                'success' => true,
                'chunk_index' => $chunkIndex,
                'processed' => $result['processed'],
                'errors' => $result['errors'],
                'valid_adherents' => $result['valid_adherents'],
                'adherents_with_anomalies' => $result['adherents_with_anomalies'],
                'is_final_chunk' => $isFinalChunk,
                'message' => "Chunk {$chunkIndex} traité avec succès"
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ ERREUR TRAITEMENT CHUNK', [
                'chunk_index' => $chunkIndex ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur traitement chunk: ' . $e->getMessage(),
                'chunk_index' => $chunkIndex ?? null
            ], 500);
        }
    }
    
    /**
     * Traiter un chunk d'adhérents avec le système d'anomalies
     */
    private function processAdherentsChunk(array $adherents, Organisation $organisation, Dossier $dossier)
    {
        $processed = 0;
        $errors = [];
        $validAdherents = 0;
        $adherentsWithAnomalies = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($adherents as $adherentData) {
                try {
                    // Créer l'adhérent
                    $adherent = $this->createAdherent($organisation, $adherentData);
                    $processed++;
                    
                    // Vérifier les anomalies (logique simplifiée)
                    if ($this->hasAnomalies($adherentData)) {
                        $adherentsWithAnomalies++;
                        // TODO: Intégrer ici le système d'anomalies existant d'OrganisationController
                    } else {
                        $validAdherents++;
                    }
                    
                } catch (\Exception $e) {
                    $nomAdherent = isset($adherentData['nom']) ? $adherentData['nom'] : 'Inconnu';
                    $errors[] = "Erreur adhérent {$nomAdherent}: " . $e->getMessage();
                    Log::warning('Erreur traitement adhérent individuel', [
                        'adherent' => $adherentData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            DB::commit();
            
            return [
                'processed' => $processed,
                'errors' => $errors,
                'valid_adherents' => $validAdherents,
                'adherents_with_anomalies' => $adherentsWithAnomalies
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    /**
     * Créer un adhérent individual
     */
    private function createAdherent(Organisation $organisation, array $adherentData)
    {
        // Générer un NIP unique si nécessaire
        $nip = isset($adherentData['nip']) && !empty($adherentData['nip']) 
            ? $adherentData['nip'] 
            : $this->generateNip($organisation);
        
        return Adherent::create([
            'organisation_id' => $organisation->id,
            'nom' => $adherentData['nom'] ?? '',
            'prenom' => $adherentData['prenom'] ?? '',
            'nip' => $nip,
            'civilite' => $adherentData['civilite'] ?? 'M',
            'telephone' => $adherentData['telephone'] ?? null,
            'email' => $adherentData['email'] ?? null,
            'profession' => $adherentData['profession'] ?? null,
            'is_active' => true,
            'date_adhesion' => now()
        ]);
    }
    
    /**
     * Vérifier si un adhérent a des anomalies (logique simplifiée)
     */
    private function hasAnomalies(array $adherentData)
    {
        // Vérifications de base pour les anomalies
        $hasAnomalies = false;
        
        // Nom manquant
        if (empty($adherentData['nom'])) {
            $hasAnomalies = true;
        }
        
        // Prénom manquant
        if (empty($adherentData['prenom'])) {
            $hasAnomalies = true;
        }
        
        // Email invalide
        if (isset($adherentData['email']) && !empty($adherentData['email'])) {
            if (!filter_var($adherentData['email'], FILTER_VALIDATE_EMAIL)) {
                $hasAnomalies = true;
            }
        }
        
        // Téléphone invalide (vérification simple)
        if (isset($adherentData['telephone']) && !empty($adherentData['telephone'])) {
            $telephone = preg_replace('/[^0-9+]/', '', $adherentData['telephone']);
            if (strlen($telephone) < 8) {
                $hasAnomalies = true;
            }
        }
        
        return $hasAnomalies;
    }
    
    /**
     * Générer un NIP unique
     */
    private function generateNip(Organisation $organisation)
    {
        // Format : XX-QQQQ-YYYYMMDD (selon la spécification existante)
        $prefecture = $organisation->prefecture ?? 'GA';
        $sequence = str_pad(Adherent::where('organisation_id', $organisation->id)->count() + 1, 4, '0', STR_PAD_LEFT);
        $date = date('Ymd');
        
        return "{$prefecture}-{$sequence}-{$date}";
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
                'user_authenticated' => auth()->check()
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
     * Rafraîchir le token CSRF
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
}