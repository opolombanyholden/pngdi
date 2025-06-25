<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\DossierValidation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class DossierLock
{
    /**
     * Durée du verrou en minutes
     */
    const LOCK_DURATION = 30;

    /**
     * Durée d'extension automatique en minutes
     */
    const AUTO_EXTEND_DURATION = 10;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action = 'view'): Response
    {
        $user = auth()->user();
        $dossierId = $request->route('dossier') ?: $request->route('id') ?: $request->input('dossier_id');

        if (!$user || !$dossierId) {
            return $next($request);
        }

        // Récupérer le dossier
        $dossier = Dossier::find($dossierId);
        if (!$dossier) {
            return $this->errorResponse('Dossier non trouvé', 404);
        }

        // Vérifications selon l'action
        switch ($action) {
            case 'edit':
            case 'update':
                return $this->handleEditAction($request, $next, $dossier, $user);
                
            case 'validate':
            case 'reject':
                return $this->handleValidationAction($request, $next, $dossier, $user);
                
            case 'view':
            default:
                return $this->handleViewAction($request, $next, $dossier, $user);
        }
    }

    /**
     * Gérer l'action d'édition (opérateur)
     */
    private function handleEditAction(Request $request, Closure $next, Dossier $dossier, $user): Response
    {
        // Seul le propriétaire peut éditer son dossier
        if ($dossier->user_id !== $user->id) {
            return $this->errorResponse('Vous n\'êtes pas autorisé à modifier ce dossier', 403);
        }

        // Un dossier soumis ne peut plus être modifié par l'opérateur
        if (in_array($dossier->statut, ['soumis', 'en_cours', 'approuve', 'rejete'])) {
            return $this->errorResponse('Ce dossier ne peut plus être modifié car il est en cours de traitement', 403);
        }

        // Vérifier le verrou pour les brouillons
        if ($dossier->statut === 'brouillon') {
            $lockKey = "dossier_edit_lock_{$dossier->id}";
            $currentLock = Cache::get($lockKey);

            if ($currentLock && $currentLock !== $user->id) {
                $lockUser = \App\Models\User::find($currentLock);
                return $this->errorResponse(
                    "Ce dossier est actuellement en cours d'édition par " . ($lockUser->name ?? 'un autre utilisateur'),
                    423
                );
            }

            // Acquérir ou renouveler le verrou
            Cache::put($lockKey, $user->id, now()->addMinutes(self::LOCK_DURATION));
        }

        return $next($request);
    }

    /**
     * Gérer l'action de validation (agent/admin)
     */
    private function handleValidationAction(Request $request, Closure $next, Dossier $dossier, $user): Response
    {
        // Vérifier que l'utilisateur est agent ou admin
        if (!in_array($user->role, ['agent', 'admin'])) {
            return $this->errorResponse('Vous n\'êtes pas autorisé à valider ce dossier', 403);
        }

        // Le dossier doit être en cours de traitement
        if (!in_array($dossier->statut, ['soumis', 'en_cours'])) {
            return $this->errorResponse('Ce dossier n\'est pas en cours de traitement', 400);
        }

        $lockKey = "dossier_validation_lock_{$dossier->id}";
        $currentLock = Cache::get($lockKey);

        // Vérifier si le dossier est verrouillé par un autre agent
        if ($currentLock && $currentLock !== $user->id) {
            $lockUser = \App\Models\User::find($currentLock);
            $lockExpiry = Cache::get($lockKey . '_expiry');
            
            return $this->errorResponse(
                "Ce dossier est actuellement traité par " . ($lockUser->name ?? 'un autre agent') . 
                ". Verrou expirera à " . ($lockExpiry ? Carbon::parse($lockExpiry)->format('H:i') : 'inconnu'),
                423
            );
        }

        // Vérifier le workflow FIFO pour les nouveaux verrouillages
        if (!$currentLock) {
            if (!$this->canProcessInFifoOrder($dossier, $user)) {
                return $this->errorResponse(
                    'Vous devez traiter les dossiers dans l\'ordre d\'arrivée (FIFO). Veuillez d\'abord traiter les dossiers précédents.',
                    400
                );
            }
        }

        // Acquérir ou renouveler le verrou de validation
        $expiryTime = now()->addMinutes(self::LOCK_DURATION);
        Cache::put($lockKey, $user->id, $expiryTime);
        Cache::put($lockKey . '_expiry', $expiryTime->toISOString(), $expiryTime);

        // Mettre à jour le statut si nécessaire
        if ($dossier->statut === 'soumis') {
            $dossier->update(['statut' => 'en_cours']);
        }

        return $next($request);
    }

    /**
     * Gérer l'action de visualisation
     */
    private function handleViewAction(Request $request, Closure $next, Dossier $dossier, $user): Response
    {
        // Vérifier les permissions de lecture
        if (!$this->canViewDossier($dossier, $user)) {
            return $this->errorResponse('Vous n\'êtes pas autorisé à consulter ce dossier', 403);
        }

        return $next($request);
    }

    /**
     * Vérifier si l'utilisateur peut voir le dossier
     */
    private function canViewDossier(Dossier $dossier, $user): bool
    {
        // Le propriétaire peut toujours voir son dossier
        if ($dossier->user_id === $user->id) {
            return true;
        }

        // Les agents et admins peuvent voir tous les dossiers
        if (in_array($user->role, ['agent', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier le respect de l'ordre FIFO
     */
    private function canProcessInFifoOrder(Dossier $dossier, $user): bool
    {
        // Admins peuvent ignorer l'ordre FIFO
        if ($user->role === 'admin') {
            return true;
        }

        // Vérifier s'il y a des dossiers plus anciens en attente
        $olderDossiers = Dossier::where('created_at', '<', $dossier->created_at)
            ->where('statut', 'soumis')
            ->where('type_organisation', $dossier->type_organisation)
            ->exists();

        return !$olderDossiers;
    }

    /**
     * Libérer le verrou d'un dossier
     */
    public static function releaseLock(int $dossierId, string $lockType = 'validation'): bool
    {
        $lockKey = "dossier_{$lockType}_lock_{$dossierId}";
        $expiryKey = $lockKey . '_expiry';
        
        $released = Cache::forget($lockKey);
        Cache::forget($expiryKey);
        
        Log::info("Verrou libéré pour dossier {$dossierId} (type: {$lockType})");
        
        return $released;
    }

    /**
     * Étendre la durée du verrou
     */
    public static function extendLock(int $dossierId, int $userId, string $lockType = 'validation'): bool
    {
        $lockKey = "dossier_{$lockType}_lock_{$dossierId}";
        $currentLock = Cache::get($lockKey);

        if ($currentLock === $userId) {
            $expiryTime = now()->addMinutes(self::AUTO_EXTEND_DURATION);
            Cache::put($lockKey, $userId, $expiryTime);
            Cache::put($lockKey . '_expiry', $expiryTime->toISOString(), $expiryTime);
            
            return true;
        }

        return false;
    }

    /**
     * Obtenir les informations du verrou
     */
    public static function getLockInfo(int $dossierId, string $lockType = 'validation'): ?array
    {
        $lockKey = "dossier_{$lockType}_lock_{$dossierId}";
        $expiryKey = $lockKey . '_expiry';
        
        $userId = Cache::get($lockKey);
        $expiry = Cache::get($expiryKey);

        if (!$userId) {
            return null;
        }

        $user = \App\Models\User::find($userId);
        
        return [
            'user_id' => $userId,
            'user_name' => $user->name ?? 'Utilisateur inconnu',
            'locked_at' => $expiry ? Carbon::parse($expiry)->subMinutes(self::LOCK_DURATION) : null,
            'expires_at' => $expiry ? Carbon::parse($expiry) : null,
            'time_remaining' => $expiry ? Carbon::parse($expiry)->diffInMinutes(now()) : 0
        ];
    }

    /**
     * Nettoyer les verrous expirés
     */
    public static function cleanExpiredLocks(): int
    {
        $cleaned = 0;
        $pattern = 'dossier_*_lock_*';
        
        // Cette méthode dépend du driver de cache utilisé
        // Pour Redis, on pourrait utiliser SCAN
        // Pour file/database cache, on aurait besoin d'une approche différente
        
        Log::info("Nettoyage des verrous expirés effectué: {$cleaned} verrous supprimés");
        
        return $cleaned;
    }

    /**
     * Réponse d'erreur formatée
     */
    private function errorResponse(string $message, int $code = 400): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'DOSSIER_LOCK_ERROR'
            ], $code);
        }

        // Déterminer la route de redirection selon le code d'erreur
        switch ($code) {
            case 403:
            case 423:
                $route = 'operator.dossiers.index';
                break;
            case 404:
                $route = 'operator.dashboard';
                break;
            default:
                return back()->with('error', $message);
        }

        return redirect()->route($route)->with('error', $message);
    }
}