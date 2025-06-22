<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Traiter la tentative de connexion
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Vérifier le rate limiting
        $this->ensureIsNotRateLimited($request);

        // Récupérer l'utilisateur
        $user = \App\Models\User::where('email', $request->email)->first();

        // Vérifier si le compte est verrouillé
        if ($user && $user->isLocked()) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte est temporairement verrouillé suite à plusieurs tentatives échouées. Réessayez dans ' . 
                            $user->locked_until->diffForHumans() . '.'],
            ]);
        }

        // Vérifier si le compte est actif
        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte a été désactivé. Veuillez contacter l\'administration.'],
            ]);
        }

        // Tentative de connexion
        if ($this->attemptLogin($request)) {
            // Réinitialiser les tentatives échouées
            if ($user) {
                $user->resetFailedAttempts();
                // Mise à jour de la dernière connexion
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip()
                ]);
            }

            // Effacer le rate limiter
            RateLimiter::clear($this->throttleKey($request));

            return $this->sendLoginResponse($request);
        }

        // Si la connexion échoue
        if ($user) {
            $user->incrementFailedAttempts();
        }

        // Incrémenter le rate limiter
        RateLimiter::hit($this->throttleKey($request));

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Valider les données de connexion
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);
    }

    /**
     * Vérifier le rate limiting
     */
    protected function ensureIsNotRateLimited(Request $request)
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => ['Trop de tentatives de connexion. Veuillez réessayer dans ' . $seconds . ' secondes.'],
        ]);
    }

    /**
     * Tentative de connexion
     */
    protected function attemptLogin(Request $request)
    {
        return Auth::attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    /**
     * Récupérer les credentials
     */
    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }

    /**
     * Réponse après connexion réussie
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $user = Auth::user();
        
        // Vérifier si l'authentification à deux facteurs est requise
        if ($user->requiresTwoFactor()) {
            // Générer un nouveau code 2FA
            $twoFactorCode = $user->generateTwoFactorCode();
            
            // Stocker l'ID utilisateur en session pour la vérification 2FA
            session()->put('two_factor_user_id', $user->id);
            
            // En mode debug, afficher le code (à retirer en production)
            if (config('app.debug')) {
                session()->put('two_factor_code_debug', $twoFactorCode->code);
            }
            
            // Déconnecter l'utilisateur temporairement
            Auth::logout();
            
            // Rediriger vers la page 2FA
            return redirect()->route('two-factor.index')
                ->with('info', config('app.debug') ? 'Code de vérification: ' . $twoFactorCode->code : 'Un code de vérification a été envoyé.');
        }

        // Message de bienvenue personnalisé
        $message = 'Bienvenue ' . $user->name . ' !';

        // Redirection selon le rôle
        if (in_array($user->role, ['admin', 'agent'])) {
            return redirect()->intended(route('admin.dashboard'))->with('success', $message);
        } elseif ($user->role === 'operator') {
            return redirect()->intended(route('operator.dashboard'))->with('success', $message);
        }

        return redirect()->intended(route('home'))->with('success', $message);
    }

    /**
     * Réponse après échec de connexion
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => ['Ces identifiants ne correspondent à aucun compte.'],
        ]);
    }

    /**
     * Clé pour le rate limiting
     */
    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}