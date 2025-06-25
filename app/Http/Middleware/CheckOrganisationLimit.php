<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organisation;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganisationLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $typeOrganisation = null): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Si aucun type spécifié, on laisse passer (pour les routes générales)
        if (!$typeOrganisation) {
            return $next($request);
        }

        // Récupérer le type depuis l'URL ou le formulaire
        $type = $typeOrganisation ?: $request->route('type') ?: $request->input('type_organisation');
        
        // Vérifications selon le type d'organisation
        switch ($type) {
            case 'parti':
            case 'parti_politique':
                if ($this->hasActiveOrganisation($user->id, 'parti_politique')) {
                    return $this->redirectWithError(
                        'Vous avez déjà un parti politique actif. Un opérateur ne peut créer qu\'un seul parti politique à la fois.',
                        'operator.dossiers.index'
                    );
                }
                break;

            case 'confession':
            case 'confession_religieuse':
                if ($this->hasActiveOrganisation($user->id, 'confession_religieuse')) {
                    return $this->redirectWithError(
                        'Vous avez déjà une confession religieuse active. Un opérateur ne peut créer qu\'une seule confession religieuse à la fois.',
                        'operator.dossiers.index'
                    );
                }
                break;

            case 'association':
            case 'ong':
                // Pas de limite pour les associations et ONG
                break;

            default:
                return $this->redirectWithError(
                    'Type d\'organisation non reconnu.',
                    'operator.dashboard'
                );
        }

        return $next($request);
    }

    /**
     * Vérifier si l'utilisateur a déjà une organisation active du type donné
     */
    private function hasActiveOrganisation(int $userId, string $type): bool
    {
        return Organisation::where('user_id', $userId)
            ->where('type_organisation', $type)
            ->whereIn('statut', [
                'brouillon',
                'soumis',
                'en_cours',
                'en_attente',
                'approuve',
                'actif'
            ])
            ->exists();
    }

    /**
     * Redirection avec message d'erreur
     */
    private function redirectWithError(string $message, string $route): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'ORGANISATION_LIMIT_EXCEEDED'
            ], 403);
        }

        return redirect()->route($route)->with('error', $message);
    }

    /**
     * Obtenir les statistiques des organisations de l'utilisateur
     */
    public static function getUserOrganisationStats(int $userId): array
    {
        $stats = Organisation::where('user_id', $userId)
            ->selectRaw('type_organisation, COUNT(*) as total')
            ->whereIn('statut', [
                'brouillon',
                'soumis', 
                'en_cours',
                'en_attente',
                'approuve',
                'actif'
            ])
            ->groupBy('type_organisation')
            ->get()
            ->pluck('total', 'type_organisation')
            ->toArray();

        return [
            'parti_politique' => $stats['parti_politique'] ?? 0,
            'confession_religieuse' => $stats['confession_religieuse'] ?? 0,
            'association' => $stats['association'] ?? 0,
            'ong' => $stats['ong'] ?? 0,
            'total' => array_sum($stats),
            'limits' => [
                'parti_politique' => 1,
                'confession_religieuse' => 1,
                'association' => null, // Pas de limite
                'ong' => null // Pas de limite
            ]
        ];
    }

    /**
     * Vérifier si l'utilisateur peut créer un type d'organisation donné
     */
    public static function canCreateOrganisation(int $userId, string $type): array
    {
        $stats = self::getUserOrganisationStats($userId);
        
        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $canCreate = $stats['parti_politique'] < $stats['limits']['parti_politique'];
                $message = $canCreate 
                    ? 'Vous pouvez créer un parti politique'
                    : 'Limite atteinte : vous avez déjà un parti politique actif';
                break;

            case 'confession':
            case 'confession_religieuse':
                $canCreate = $stats['confession_religieuse'] < $stats['limits']['confession_religieuse'];
                $message = $canCreate 
                    ? 'Vous pouvez créer une confession religieuse'
                    : 'Limite atteinte : vous avez déjà une confession religieuse active';
                break;

            case 'association':
            case 'ong':
                $canCreate = true;
                $message = 'Vous pouvez créer une ' . ($type === 'ong' ? 'ONG' : 'association');
                break;

            default:
                $canCreate = false;
                $message = 'Type d\'organisation non reconnu';
        }

        return [
            'can_create' => $canCreate,
            'message' => $message,
            'current_count' => $stats[$type] ?? 0,
            'limit' => $stats['limits'][$type] ?? null
        ];
    }
}