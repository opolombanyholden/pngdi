<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Constructeur - Vérifier que l'utilisateur est un opérateur
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user() && auth()->user()->role !== 'operator') {
                abort(403, 'Accès non autorisé');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard principal (méthode existante conservée)
     */
    public function dashboard()
    {
        return view('operator.dashboard');
    }

    /**
     * Afficher le profil de l'opérateur
     */
    public function index()
    {
        $user = auth()->user();
        $isComplete = $this->isProfileComplete($user);
        $provinces = $this->getProvinces();
        
        return view('operator.profile.index', compact('user', 'isComplete', 'provinces'));
    }

    /**
     * Afficher le formulaire d'édition (méthode existante mise à jour)
     */
    public function edit()
    {
        $user = auth()->user();
        $provinces = $this->getProvinces();
        
        return view('operator.profile.edit', compact('user', 'provinces'));
    }

    /**
     * Afficher le formulaire de completion du profil
     */
    public function complete()
    {
        $user = auth()->user();
        
        // Si le profil est déjà complet, rediriger vers le dashboard
        if ($this->isProfileComplete($user)) {
            return redirect()->route('operator.dashboard')
                ->with('info', 'Votre profil est déjà complet.');
        }

        $provinces = $this->getProvinces();
        return view('operator.profile.complete', compact('user', 'provinces'));
    }

    /**
     * Mettre à jour le profil (méthode existante améliorée)
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'required|string|max:20',
            'nip' => 'required|string|max:20|unique:users,nip,' . $user->id,
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'profession' => 'nullable|string|max:100',
            'date_naissance' => 'nullable|date|before:today',
        ];

        $validatedData = $request->validate($rules);

        try {
            $user->update($validatedData);

            // Vérifier si le profil est maintenant complet
            $isComplete = $this->isProfileComplete($user->fresh());
            
            $message = 'Profil mis à jour avec succès.';
            $redirectRoute = 'operator.profile.index';

            // Si on vient de la page de completion et que le profil est complet
            if ($isComplete && $request->route()->getName() === 'operator.profile.update' && 
                $request->input('from_complete')) {
                $message .= ' Votre profil est maintenant complet !';
                $redirectRoute = 'operator.dashboard';
            }

            return redirect()->route($redirectRoute)->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour profil: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du profil.')
                ->withInput();
        }
    }

    /**
     * Mettre à jour le mot de passe (méthode existante améliorée)
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.'])
                ->withInput();
        }

        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return redirect()->route('operator.profile.index')
                ->with('success', 'Mot de passe mis à jour avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour mot de passe: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du mot de passe');
        }
    }

    /**
     * Guides (méthode existante conservée)
     */
    public function guides()
    {
        return view('operator.guides');
    }

    /**
     * Documents types (méthode existante conservée)
     */
    public function documentsTypes()
    {
        return view('operator.documents-types');
    }

    /**
     * Calendrier (méthode existante conservée)
     */
    public function calendrier()
    {
        return view('operator.calendrier');
    }

    /**
     * Vérifier si le profil de l'opérateur est complet
     */
    private function isProfileComplete($user): bool
    {
        if ($user->role !== 'operator') {
            return true;
        }

        // Champs obligatoires pour un opérateur
        $requiredFields = ['name', 'email', 'telephone', 'nip'];
        
        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtenir les provinces du Gabon
     */
    private function getProvinces(): array
    {
        return [
            'Estuaire' => 'Estuaire',
            'Haut-Ogooué' => 'Haut-Ogooué', 
            'Moyen-Ogooué' => 'Moyen-Ogooué',
            'Ngounié' => 'Ngounié',
            'Nyanga' => 'Nyanga',
            'Ogooué-Ivindo' => 'Ogooué-Ivindo',
            'Ogooué-Lolo' => 'Ogooué-Lolo',
            'Ogooué-Maritime' => 'Ogooué-Maritime',
            'Woleu-Ntem' => 'Woleu-Ntem'
        ];
    }

    /**
     * Obtenir les statistiques du profil
     */
    public function getProfileStats()
    {
        $user = auth()->user();
        
        $stats = [
            'profile_completion' => $this->calculateProfileCompletion($user),
            'required_fields_missing' => $this->getMissingRequiredFields($user),
            'last_updated' => $user->updated_at,
            'account_created' => $user->created_at,
        ];

        return response()->json($stats);
    }

    /**
     * Calculer le pourcentage de completion du profil
     */
    private function calculateProfileCompletion($user): int
    {
        $allFields = ['name', 'email', 'telephone', 'nip', 'adresse', 'ville', 'province', 'profession', 'date_naissance'];
        $completedFields = 0;

        foreach ($allFields as $field) {
            if (!empty($user->$field)) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($allFields)) * 100);
    }

    /**
     * Obtenir les champs obligatoires manquants
     */
    private function getMissingRequiredFields($user): array
    {
        $requiredFields = [
            'name' => 'Nom complet',
            'email' => 'Adresse email', 
            'telephone' => 'Téléphone',
            'nip' => 'Numéro NIP'
        ];
        
        $missing = [];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($user->$field)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * Exporter les données du profil
     */
    public function exportProfile()
    {
        $user = auth()->user();
        
        $profileData = [
            'nom' => $user->name,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'nip' => $user->nip,
            'adresse' => $user->adresse,
            'ville' => $user->ville,
            'province' => $user->province,
            'profession' => $user->profession,
            'date_naissance' => $user->date_naissance,
            'role' => $user->role,
            'compte_cree_le' => $user->created_at->format('d/m/Y H:i'),
            'derniere_mise_a_jour' => $user->updated_at->format('d/m/Y H:i'),
        ];

        $filename = 'profil_' . $user->name . '_' . date('Y-m-d') . '.json';

        return response()
            ->json($profileData, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Supprimer la photo de profil (si implementée)
     */
    public function deleteProfilePhoto()
    {
        $user = auth()->user();
        
        try {
            // Si vous avez un champ photo_profile
            if (!empty($user->photo_profile)) {
                // Supprimer le fichier physique
                $photoPath = public_path('storage/profiles/' . $user->photo_profile);
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
                
                // Mettre à jour la base de données
                $user->update(['photo_profile' => null]);
            }

            return redirect()->back()->with('success', 'Photo de profil supprimée avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur suppression photo profil: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression de la photo.');
        }
    }
}