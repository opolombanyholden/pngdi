<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyOperatorRole
{
    /**
     * Rôles autorisés et leurs permissions
     */
    const ROLE_PERMISSIONS = [
        'operator' => [
            'routes' => ['operator.*'],
            'redirect_on_fail' => 'home',
            'description' => 'Accès espace opérateur'
        ],
        'agent' => [
            'routes' => ['admin.*', 'agent.*'],
            'redirect_on_fail' => 'admin.dashboard',
            'description' => 'Accès espace administration'
        ],
        'admin' => [
            'routes' => ['admin.*', 'agent.*', 'operator.*'],
            'redirect_on_fail' => 'admin.dashboard',
            'description' => 'Accès complet administration'
        ],
        'visitor' => [
            'routes' => ['home', 'actualites.*', 'documents.*', 'faq', 'contact'],
            'redirect_on_fail' => 'home',
            'description' => 'Accès public uniquement'
        ]
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();
        $userRole = $user->role ?? 'visitor';

        // Si aucun rôle spécifié, on vérifie juste l'authentification
        if (empty($allowedRoles)) {
            return $next($request);
        }

        // Vérifier si le rôle de l'utilisateur est autorisé
        if (!in_array($userRole, $allowedRoles)) {
            return $this->handleUnauthorizedAccess($request, $user, $allowedRoles);
        }

        // Vérifications spécifiques selon le rôle
        switch ($userRole) {
            case 'operator':
                return $this->handleOperatorAccess($request, $next, $user);
                
            case 'agent':
                return $this->handleAgentAccess($request, $next, $user);
                
            case 'admin':
                return $this->handleAdminAccess($request, $next, $user);
                
            default:
                return $this->handleVisitorAccess($request, $next, $user);
        }
    }

    /**
     * Gérer l'accès des opérateurs
     */
    private function handleOperatorAccess(Request $request, Closure $next, $user): Response
    {
        // Vérifier que l'email est vérifié
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Veuillez vérifier votre adresse email avant d\'accéder à votre espace.');
        }

        // Vérifier que le compte n'est pas suspendu
        if ($user->statut === 'suspendu') {
            Auth::logout();
            return redirect()->route('home')
                ->with('error', 'Votre compte a été suspendu. Contactez l\'administration.');
        }

        // Vérifier que le profil est complet
        if (!$this->isProfileComplete($user)) {
            return redirect()->route('operator.profile.complete')
                ->with('info', 'Veuillez compléter votre profil avant de continuer.');
        }

        return $next($request);
    }

    /**
     * Gérer l'accès des agents
     */
    private function handleAgentAccess(Request $request, Closure $next, $user): Response
    {
        // Vérifier que l'agent est actif
        if ($user->statut !== 'actif') {
            Auth::logout();
            return redirect()->route('home')
                ->with('error', 'Votre compte agent n\'est pas actif. Contactez l\'administrateur.');
        }

        // Vérifier l'authentification à deux facteurs pour les agents
        if (!$user->two_factor_confirmed_at && config('app.require_2fa_agents', true)) {
            return redirect()->route('two-factor.challenge')
                ->with('warning', 'L\'authentification à deux facteurs est requise pour les agents.');
        }

        return $next($request);
    }

    /**
     * Gérer l'accès des administrateurs
     */
    private function handleAdminAccess(Request $request, Closure $next, $user): Response
    {
        // Vérifier que l'admin est actif
        if ($user->statut !== 'actif') {
            Auth::logout();
            return redirect()->route('home')
                ->with('error', 'Votre compte administrateur n\'est pas actif.');
        }

        // Log de l'accès administrateur
        $route = $request->route();
        \Illuminate\Support\Facades\Log::info('Accès administrateur', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'ip' => $request->ip(),
            'route' => $route ? $route->getName() : null,
            'timestamp' => now()
        ]);

        return $next($request);
    }

    /**
     * Gérer l'accès des visiteurs
     */
    private function handleVisitorAccess(Request $request, Closure $next, $user): Response
    {
        // Les visiteurs connectés ont accès aux pages publiques
        return $next($request);
    }

    /**
     * Rediriger vers la page de connexion
     */
    private function redirectToLogin(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise',
                'error_code' => 'AUTHENTICATION_REQUIRED'
            ], 401);
        }

        return redirect()->route('login')
            ->with('info', 'Veuillez vous connecter pour accéder à cette page.');
    }

    /**
     * Gérer l'accès non autorisé
     */
    private function handleUnauthorizedAccess(Request $request, $user, array $allowedRoles): Response
    {
        $userRole = $user->role ?? 'visitor';
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé pour votre rôle',
                'error_code' => 'INSUFFICIENT_PRIVILEGES',
                'required_roles' => $allowedRoles,
                'user_role' => $userRole
            ], 403);
        }

        // Redirection selon le rôle de l'utilisateur
        $redirectRoute = $this->getRedirectRouteForRole($userRole);
        $message = $this->getUnauthorizedMessage($userRole, $allowedRoles);

        return redirect()->route($redirectRoute)->with('error', $message);
    }

    /**
     * Obtenir la route de redirection selon le rôle
     */
    private function getRedirectRouteForRole(string $role): string
    {
        switch ($role) {
            case 'admin':
            case 'agent':
                return 'admin.dashboard';
            case 'operator':
                return 'operator.dashboard';
            default:
                return 'home';
        }
    }

    /**
     * Obtenir le message d'erreur personnalisé
     */
    private function getUnauthorizedMessage(string $userRole, array $allowedRoles): string
    {
        $roleNames = [
            'admin' => 'administrateur',
            'agent' => 'agent',
            'operator' => 'opérateur',
            'visitor' => 'visiteur'
        ];

        $allowedRoleNames = array_map(function($role) use ($roleNames) {
            return $roleNames[$role] ?? $role;
        }, $allowedRoles);

        $currentRoleName = $roleNames[$userRole] ?? $userRole;

        return sprintf(
            'Accès refusé. Cette page est réservée aux %s. Votre rôle actuel : %s.',
            implode(', ', $allowedRoleNames),
            $currentRoleName
        );
    }

    /**
     * Vérifier si le profil de l'opérateur est complet
     */
    private function isProfileComplete($user): bool
    {
        if ($user->role !== 'operator') {
            return true;
        }

        // Vérifier les champs obligatoires pour un opérateur
        $requiredFields = ['name', 'email', 'telephone', 'nip'];
        
        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifier si l'utilisateur peut accéder à une route spécifique
     */
    public static function canAccessRoute(string $routeName, ?string $userRole = null): bool
    {
        if (!$userRole) {
            $user = Auth::user();
            $userRole = $user ? $user->role : 'visitor';
        }

        $permissions = self::ROLE_PERMISSIONS[$userRole] ?? self::ROLE_PERMISSIONS['visitor'];
        
        foreach ($permissions['routes'] as $allowedRoute) {
            if (fnmatch($allowedRoute, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir toutes les permissions d'un rôle
     */
    public static function getRolePermissions(string $role): array
    {
        return self::ROLE_PERMISSIONS[$role] ?? self::ROLE_PERMISSIONS['visitor'];
    }

    /**
     * Vérifier si un utilisateur a un rôle spécifique ou supérieur
     */
    public static function hasRoleOrHigher(string $requiredRole, ?string $userRole = null): bool
    {
        if (!$userRole) {
            $user = Auth::user();
            $userRole = $user ? $user->role : 'visitor';
        }

        $hierarchy = ['visitor', 'operator', 'agent', 'admin'];
        $requiredLevel = array_search($requiredRole, $hierarchy);
        $userLevel = array_search($userRole, $hierarchy);

        return $userLevel !== false && $userLevel >= $requiredLevel;
    }

    /**
     * Middleware pour vérifier l'email vérifié
     */
    public static function requiresVerifiedEmail(): Closure
    {
        return function (Request $request, Closure $next) {
            $user = Auth::user();
            if (!$user || !$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            return $next($request);
        };
    }

    /**
     * Middleware pour vérifier l'authentification à deux facteurs
     */
    public static function requires2FA(): Closure
    {
        return function (Request $request, Closure $next) {
            $user = Auth::user();
            if ($user && !$user->two_factor_confirmed_at && in_array($user->role, ['admin', 'agent'])) {
                return redirect()->route('two-factor.challenge');
            }
            return $next($request);
        };
    }
}