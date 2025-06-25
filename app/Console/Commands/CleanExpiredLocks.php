<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Middleware\DossierLock;
use Illuminate\Support\Facades\Cache;

class CleanExpiredLocks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pngdi:clean-locks 
                           {--type=all : Type de verrous à nettoyer (all, edit, validation)}
                           {--force : Forcer le nettoyage sans confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Nettoyer les verrous de dossiers expirés du système PNGDI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $force = $this->option('force');
        
        if (!$force) {
            if (!$this->confirm('Voulez-vous vraiment nettoyer les verrous expirés ?')) {
                $this->info('Opération annulée.');
                return 0;
            }
        }
        
        $this->info('Début du nettoyage des verrous...');
        
        $cleaned = 0;
        
        switch ($type) {
            case 'edit':
                $cleaned = $this->cleanLocksByType('edit');
                break;
                
            case 'validation':
                $cleaned = $this->cleanLocksByType('validation');
                break;
                
            case 'all':
            default:
                $cleaned += $this->cleanLocksByType('edit');
                $cleaned += $this->cleanLocksByType('validation');
                break;
        }
        
        $this->info("Nettoyage terminé : {$cleaned} verrous supprimés");
        
        // Logger l'opération
        \Illuminate\Support\Facades\Log::info('Nettoyage des verrous expirés', [
            'type' => $type,
            'cleaned_count' => $cleaned,
            'executed_by' => 'console',
            'timestamp' => now()
        ]);
        
        return 0;
    }
    
    /**
     * Nettoyer les verrous d'un type spécifique
     */
    private function cleanLocksByType(string $lockType): int
    {
        $cleaned = 0;
        $pattern = "dossier_{$lockType}_lock_*";
        
        $this->line("Nettoyage des verrous de type: {$lockType}");
        
        // Pour les drivers de cache qui le supportent
        if (config('cache.default') === 'redis') {
            $cleaned = $this->cleanRedisLocks($pattern);
        } else {
            // Fallback pour les autres drivers
            $cleaned = $this->cleanGenericLocks($lockType);
        }
        
        $this->line("  → {$cleaned} verrous de type '{$lockType}' supprimés");
        
        return $cleaned;
    }
    
    /**
     * Nettoyer les verrous Redis avec SCAN
     */
    private function cleanRedisLocks(string $pattern): int
    {
        $cleaned = 0;
        
        try {
            $redis = Cache::getRedis();
            $keys = $redis->keys($pattern);
            
            foreach ($keys as $key) {
                // Vérifier si la clé existe encore (race condition)
                if ($redis->exists($key)) {
                    $redis->del($key);
                    $cleaned++;
                }
                
                // Supprimer aussi la clé d'expiration associée
                $expiryKey = $key . '_expiry';
                if ($redis->exists($expiryKey)) {
                    $redis->del($expiryKey);
                }
            }
        } catch (\Exception $e) {
            $this->error("Erreur lors du nettoyage Redis: " . $e->getMessage());
        }
        
        return $cleaned;
    }
    
    /**
     * Nettoyer les verrous pour les drivers génériques
     */
    private function cleanGenericLocks(string $lockType): int
    {
        $cleaned = 0;
        
        // Simuler un nettoyage en vérifiant quelques IDs de dossiers courants
        // En production, vous pourriez interroger la base de données pour obtenir les IDs
        $sampleDossierIds = range(1, 100); // Ajustez selon vos besoins
        
        foreach ($sampleDossierIds as $dossierId) {
            $lockKey = "dossier_{$lockType}_lock_{$dossierId}";
            $expiryKey = $lockKey . '_expiry';
            
            if (Cache::has($lockKey)) {
                // Vérifier si le verrou est expiré
                $expiry = Cache::get($expiryKey);
                if ($expiry && now()->gt($expiry)) {
                    Cache::forget($lockKey);
                    Cache::forget($expiryKey);
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Afficher des statistiques sur les verrous actifs
     */
    public function showLockStats()
    {
        $this->info('Statistiques des verrous actifs:');
        
        $editLocks = $this->countActiveLocks('edit');
        $validationLocks = $this->countActiveLocks('validation');
        
        $this->table(
            ['Type', 'Verrous actifs'],
            [
                ['Édition', $editLocks],
                ['Validation', $validationLocks],
                ['Total', $editLocks + $validationLocks]
            ]
        );
    }
    
    /**
     * Compter les verrous actifs d'un type
     */
    private function countActiveLocks(string $lockType): int
    {
        $count = 0;
        
        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::getRedis();
                $keys = $redis->keys("dossier_{$lockType}_lock_*");
                $count = count($keys);
            } catch (\Exception $e) {
                $this->error("Erreur lors du comptage Redis: " . $e->getMessage());
            }
        } else {
            // Méthode approximative pour les autres drivers
            $sampleDossierIds = range(1, 100);
            foreach ($sampleDossierIds as $dossierId) {
                $lockKey = "dossier_{$lockType}_lock_{$dossierId}";
                if (Cache::has($lockKey)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}