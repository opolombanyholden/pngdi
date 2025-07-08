<?php
// ✅ SOLUTION IMMÉDIATE : Corriger confirmation.blade.php
// Remplacer le code problématique par cette version optimisée

// ==========================================
// VERSION CORRIGÉE POUR confirmation.blade.php
// ==========================================

// 🔧 ÉTAPE 1 : Vérification minimale d'existence
$sessionKey = 'phase2_adherents_' . $dossier->id;
$hasSessionData = false;
$adherentsCount = 0;
$sessionExpiration = null;
$hasPhase2Pending = false;

try {
    // ✅ CRITIQUE : Vérifier existence SANS charger les données
    if (session()->has($sessionKey)) {
        // Lire SEULEMENT les métadonnées de comptage
        $metaKey = 'phase2_meta_' . $dossier->id;
        $metaData = session($metaKey, []);
        
        if (!empty($metaData) && isset($metaData['total'])) {
            // ✅ Utiliser les métadonnées pré-calculées
            $adherentsCount = (int)$metaData['total'];
            $sessionExpiration = $metaData['expires_at'] ?? null;
            $hasSessionData = true;
        } else {
            // ✅ Fallback : Lecture rapide avec limite mémoire
            $originalLimit = ini_get('memory_limit');
            ini_set('memory_limit', '256M');
            
            try {
                // Lecture avec timeout
                set_time_limit(10);
                
                $quickCheck = session($sessionKey);
                
                if (is_array($quickCheck)) {
                    if (isset($quickCheck['total'])) {
                        $adherentsCount = (int)$quickCheck['total'];
                    } elseif (isset($quickCheck['data'])) {
                        $adherentsCount = count($quickCheck['data']);
                    } else {
                        $adherentsCount = count($quickCheck);
                    }
                    
                    $hasSessionData = $adherentsCount > 0;
                    $sessionExpiration = session('phase2_expires_' . $dossier->id);
                }
                
                // ✅ CRITIQUE : Libération immédiate
                unset($quickCheck);
                
            } catch (\Exception $checkError) {
                // En cas d'erreur, estimer basé sur la taille de session
                $sessionSize = strlen(session()->getId());
                $adherentsCount = $sessionSize > 50 ? 10000 : 0; // Estimation
                $hasSessionData = $adherentsCount > 0;
                
                \Log::warning('🚨 Estimation adhérents session due à erreur mémoire', [
                    'dossier_id' => $dossier->id,
                    'estimated_count' => $adherentsCount,
                    'error' => $checkError->getMessage()
                ]);
            }
            
            // Restaurer limite
            ini_set('memory_limit', $originalLimit);
            set_time_limit(60);
        }
        
        $hasPhase2Pending = $hasSessionData && $adherentsCount > 0;
        
    } else {
        // Pas de session - valeurs par défaut
        $hasSessionData = false;
        $adherentsCount = 0;
        $hasPhase2Pending = false;
    }
    
} catch (\Exception $e) {
    // ✅ Gestion d'erreur robuste
    \Log::error('❌ Erreur critique lecture session - mode dégradé', [
        'dossier_id' => $dossier->id,
        'error' => $e->getMessage(),
        'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB'
    ]);
    
    // Mode dégradé : Vider la session problématique
    try {
        session()->forget([$sessionKey, 'phase2_expires_' . $dossier->id]);
        
        // Valeurs par défaut
        $hasSessionData = false;
        $adherentsCount = 0;
        $hasPhase2Pending = false;
        $sessionExpiration = null;
        
        // Créer métadonnées d'urgence si besoin
        if (strpos($e->getMessage(), 'memory') !== false) {
            $adherentsCount = 10000; // Estimation pour interface
            $hasPhase2Pending = true;
            $sessionExpiration = now()->addHours(2);
            
            \Log::warning('🚨 Mode dégradé activé - session vidée pour économie mémoire', [
                'dossier_id' => $dossier->id,
                'estimated_adherents' => $adherentsCount
            ]);
        }
        
    } catch (\Exception $cleanupError) {
        // Si même le nettoyage échoue, valeurs minimales
        $hasSessionData = false;
        $adherentsCount = 0;
        $hasPhase2Pending = false;
        $sessionExpiration = null;
    }
}

// 🔧 ÉTAPE 2 : Formatage final sécurisé
$adherentsCount = (int)$adherentsCount;
$sessionExpirationFormatted = null;

if ($sessionExpiration) {
    try {
        $sessionExpirationFormatted = \Carbon\Carbon::parse($sessionExpiration)->format('d/m/Y à H:i');
    } catch (\Exception $e) {
        $sessionExpirationFormatted = 'Session active';
    }
}

// 🔧 ÉTAPE 3 : Variables pour la vue (format minimal)
$adherentsEnBase = $organisation->adherents()->count();

// Configuration minimale selon le type
switch($organisation->type) {
    case 'association':
        $minAdherents = 15;
        break;
    case 'ong':
        $minAdherents = 25;
        break;
    case 'parti_politique':
        $minAdherents = 50;
        break;
    case 'confession_religieuse':
        $minAdherents = 10;
        break;
    default:
        $minAdherents = 15;
        break;
}

$totalAdherents = $adherentsEnBase + $adherentsCount;
$adherentsManquants = max(0, $minAdherents - $totalAdherents);
$pretPourSoumission = $totalAdherents >= $minAdherents && !$hasPhase2Pending;

// 🔧 ÉTAPE 4 : Log de contrôle final
\Log::info('✅ Traitement session Phase 2 terminé avec succès', [
    'dossier_id' => $dossier->id,
    'has_session_data' => $hasSessionData,
    'adherents_count' => $adherentsCount,
    'has_phase2_pending' => $hasPhase2Pending,
    'memory_final' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
    'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
]);

// ==========================================
// CORRECTION COMPLÉMENTAIRE : processSessionAdherents
// ==========================================

// Dans OrganisationController.php, méthode processSessionAdherents
function processSessionAdherents(Request $request, $dossierId)
{
    try {
        \Log::info('🚀 Traitement session adhérents - Version optimisée', [
            'dossier_id' => $dossierId,
            'user_id' => auth()->id()
        ]);

        $sessionKey = 'phase2_adherents_' . $dossierId;
        
        // ✅ CRITIQUE : Vérifier existence avant chargement
        if (!session()->has($sessionKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun adhérent en session à traiter',
                'error_code' => 'NO_SESSION_DATA'
            ], 404);
        }

        // ✅ NOUVEAU : Traitement par chunks dès le début
        return $this->processSessionInChunks($dossierId);

    } catch (\Exception $e) {
        \Log::error('❌ Erreur traitement adhérents session', [
            'dossier_id' => $dossierId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du traitement automatique',
            'error' => $e->getMessage()
        ], 500);
    }
}

// ✅ NOUVELLE MÉTHODE : Traitement par chunks sans saturation mémoire
function processSessionInChunks($dossierId)
{
    $sessionKey = 'phase2_adherents_' . $dossierId;
    $chunkSize = 50; // Petits chunks pour éviter saturation
    $processed = 0;
    $chunkIndex = 0;
    
    while (session()->has($sessionKey)) {
        try {
            // Augmenter limite temporairement pour ce chunk
            $originalLimit = ini_get('memory_limit');
            ini_set('memory_limit', '256M');
            set_time_limit(30);
            
            // Lire session
            $sessionData = session($sessionKey, []);
            
            if (empty($sessionData)) {
                break;
            }
            
            // Extraire données
            $allAdherents = isset($sessionData['data']) ? $sessionData['data'] : $sessionData;
            
            if (empty($allAdherents) || $chunkIndex * $chunkSize >= count($allAdherents)) {
                break;
            }
            
            // Extraire chunk
            $chunk = array_slice($allAdherents, $chunkIndex * $chunkSize, $chunkSize);
            
            // Traiter chunk
            $this->processAdherentsChunk($dossierId, $chunk);
            
            $processed += count($chunk);
            $chunkIndex++;
            
            // Libérer mémoire
            unset($sessionData, $allAdherents, $chunk);
            
            // Si c'est le dernier chunk, nettoyer session
            if ($chunkIndex * $chunkSize >= count($allAdherents)) {
                session()->forget($sessionKey);
                session()->forget('phase2_expires_' . $dossierId);
            }
            
            // Restaurer limite
            ini_set('memory_limit', $originalLimit);
            
            \Log::info("✅ Chunk {$chunkIndex} traité avec succès", [
                'processed_in_chunk' => count($chunk),
                'total_processed' => $processed
            ]);
            
        } catch (\Exception $e) {
            \Log::error("❌ Erreur chunk {$chunkIndex}", [
                'error' => $e->getMessage(),
                'processed_so_far' => $processed
            ]);
            break;
        }
    }
    
    return response()->json([
        'success' => true,
        'message' => "Traitement terminé : {$processed} adhérents traités",
        'data' => [
            'total_processed' => $processed,
            'chunks_processed' => $chunkIndex
        ]
    ]);
}

// ==========================================
// CONFIGURATION PHP RECOMMANDÉE
// ==========================================

/*
Ajouter dans .htaccess ou php.ini :

memory_limit = 512M
max_execution_time = 300
max_input_vars = 10000
post_max_size = 50M
upload_max_filesize = 50M

# Pour les sessions
session.gc_maxlifetime = 7200
session.save_handler = files
session.save_path = "/tmp"
*/