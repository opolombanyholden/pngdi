<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class TwoFactorController extends Controller
{
    /**
     * Afficher le formulaire 2FA
     */
    public function index()
    {
        if (!session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    /**
     * Vérifier le code 2FA
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('two_factor_user_id');
        
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }

        $user = User::find($userId);
        
        if (!$user) {
            session()->forget('two_factor_user_id');
            return redirect()->route('login')
                ->with('error', 'Utilisateur non trouvé.');
        }

        // Vérifier le code
        $twoFactorCode = $user->twoFactorCodes()
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$twoFactorCode) {
            return back()->withErrors([
                'code' => 'Code invalide ou expiré.'
            ]);
        }

        // Marquer le code comme utilisé
        $twoFactorCode->update(['used' => true]);

        // Nettoyer la session
        session()->forget(['two_factor_user_id', 'two_factor_code_debug']);

        // Connecter l'utilisateur
        auth()->login($user, session('remember', false));

        // Message de bienvenue
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
     * Renvoyer un code 2FA
     */
    public function resend()
    {
        $userId = session('two_factor_user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        
        if (!$user || !$user->requiresTwoFactor()) {
            return redirect()->route('login');
        }

        // Générer un nouveau code
        $twoFactorCode = $user->generateTwoFactorCode();

        // En mode debug, afficher le code
        if (config('app.debug')) {
            session()->put('two_factor_code_debug', $twoFactorCode->code);
            return back()->with('info', 'Nouveau code: ' . $twoFactorCode->code);
        }

        return back()->with('success', 'Un nouveau code de vérification a été envoyé.');
    }
}