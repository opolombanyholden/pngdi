<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Liste des opérateurs
     * Route: /admin/users/operators
     */
    public function operators(Request $request)
    {
        try {
            // Query de base pour les opérateurs
            $query = User::where('role', 'operator')
                ->orderBy('created_at', 'desc');

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%");
                });
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtre par état actif
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Pagination
            $operators = $query->paginate(15);

            // Enrichir chaque opérateur avec des statistiques
            $operators->getCollection()->transform(function ($operator) {
                return $this->enrichOperatorData($operator);
            });

            // Statistiques générales
            $stats = [
                'total_operators' => User::where('role', 'operator')->count(),
                'active_operators' => User::where('role', 'operator')->where('is_active', 1)->count(),
                'pending_operators' => User::where('role', 'operator')->where('status', 'pending')->count(),
                'suspended_operators' => User::where('role', 'operator')->where('status', 'suspended')->count(),
            ];

            return view('admin.users.operators', compact('operators', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@operators: ' . $e->getMessage());
            
            return back()->with('error', 'Erreur lors du chargement des opérateurs.');
        }
    }

    /**
     * Liste des agents
     * Route: /admin/users/agents
     */
    public function agents(Request $request)
    {
        try {
            // Query de base pour les agents
            $query = User::where('role', 'agent')
                ->orderBy('created_at', 'desc');

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%");
                });
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Pagination
            $agents = $query->paginate(15);

            // Enrichir chaque agent avec des statistiques
            $agents->getCollection()->transform(function ($agent) {
                return $this->enrichAgentData($agent);
            });

            // Statistiques générales
            $stats = [
                'total_agents' => User::where('role', 'agent')->count(),
                'active_agents' => User::where('role', 'agent')->where('is_active', 1)->count(),
                'agents_online' => User::where('role', 'agent')
                    ->where('last_login_at', '>=', now()->subHours(2))
                    ->count(),
                'agents_with_workload' => $this->getAgentsWithWorkload(),
            ];

            return view('admin.users.agents', compact('agents', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@agents: ' . $e->getMessage());
            
            return back()->with('error', 'Erreur lors du chargement des agents.');
        }
    }

    /**
     * Créer un nouvel agent
     * Route: /admin/users/create
     */
    public function create()
    {
        try {
            return view('admin.users.create');
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@create: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Afficher un utilisateur
     * Route: /admin/users/{id}
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Enrichir avec des statistiques
            $user = $this->enrichUserData($user);
            
            // Obtenir l'historique d'activité
            $activities = $this->getUserActivities($user);
            
            // Statistiques détaillées
            $detailedStats = $this->getDetailedUserStats($user);

            return view('admin.users.show', compact('user', 'activities', 'detailedStats'));

        } catch (\ModelNotFoundException $e) {
            return redirect()->route('admin.users.operators')
                ->with('error', 'Utilisateur non trouvé.');
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@show: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement de l\'utilisateur.');
        }
    }

    /**
     * Stocker un nouvel agent
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'nullable|string|max:20',
                'role' => 'required|in:agent,operator',
                'password' => 'required|string|min:8|confirmed',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user = User::create([
                'name' => $request->nom . ' ' . $request->prenom,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'city' => $request->city,
                'status' => 'active',
                'is_active' => true,
                'created_by' => auth()->id(),
                'email_verified_at' => now(), // Auto-vérification pour les agents
            ]);

            $redirectRoute = $request->role === 'agent' ? 'admin.users.agents' : 'admin.users.operators';
            
            return redirect()->route($redirectRoute)
                ->with('success', ucfirst($request->role) . ' créé(e) avec succès.');

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@store: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'utilisateur.')
                ->withInput();
        }
    }

    /**
     * Éditer un utilisateur
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.edit', compact('user'));
        } catch (\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé.');
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@edit: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement du formulaire d\'édition.');
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => 'nullable|string|max:20',
                'role' => 'required|in:agent,operator,admin',
                'status' => 'required|in:active,inactive,suspended,pending',
                'is_active' => 'boolean',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $updateData = [
                'name' => $request->nom . ' ' . $request->prenom,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => $request->status,
                'is_active' => $request->has('is_active'),
                'address' => $request->address,
                'city' => $request->city,
                'updated_by' => auth()->id(),
            ];

            // Mettre à jour le mot de passe seulement s'il est fourni
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('admin.users.show', $user->id)
                ->with('success', 'Utilisateur mis à jour avec succès.');

        } catch (\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé.');
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@update: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'utilisateur.')
                ->withInput();
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Empêcher la suppression du compte admin connecté
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer votre propre compte.'
                ], 403);
            }

            // Empêcher la suppression si l'utilisateur a des dossiers assignés
            if ($user->role === 'agent' && $user->assignedDossiers && $user->assignedDossiers->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cet agent car il a des dossiers assignés.'
                ], 422);
            }

            $userName = $user->name;
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => "L'utilisateur {$userName} a été supprimé avec succès."
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur.'
            ], 500);
        }
    }

    /**
     * Changer le statut d'un utilisateur (AJAX)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive,suspended',
                'reason' => 'nullable|string|max:500'
            ]);

            $user = User::findOrFail($id);
            
            $user->update([
                'status' => $request->status,
                'is_active' => $request->status === 'active',
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Statut de {$user->name} mis à jour avec succès."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut.'
            ], 500);
        }
    }

    // ========== MÉTHODES PRIVÉES ==========

    /**
     * Enrichir les données d'un opérateur
     */
    private function enrichOperatorData($operator)
    {
        // Nombre d'organisations créées
        $operator->organisations_count = $operator->organisations ? $operator->organisations->count() : 0;
        
        // Statut de connexion
        $operator->is_online = $operator->last_login_at && $operator->last_login_at->gt(now()->subHours(2));
        
        // Dernière activité
        $operator->last_activity = $operator->last_login_at ? $operator->last_login_at->diffForHumans() : 'Jamais connecté';
        
        return $operator;
    }

    /**
     * Enrichir les données d'un agent
     */
    private function enrichAgentData($agent)
    {
        // Charge de travail actuelle
        $agent->current_workload = $agent->assignedDossiers ? 
            $agent->assignedDossiers->where('statut', 'en_cours')->count() : 0;
        
        // Statut de connexion
        $agent->is_online = $agent->last_login_at && $agent->last_login_at->gt(now()->subHours(2));
        
        // Performance (simple)
        $agent->dossiers_traites_mois = $agent->dossierValidations ? 
            $agent->dossierValidations->where('decided_at', '>=', now()->subMonth())->count() : 0;
        
        // Disponibilité
        $agent->availability = $agent->current_workload < 5 ? 'Disponible' : 'Chargé';
        
        return $agent;
    }

    /**
     * Enrichir les données d'un utilisateur
     */
    private function enrichUserData($user)
    {
        if ($user->role === 'operator') {
            return $this->enrichOperatorData($user);
        } elseif ($user->role === 'agent') {
            return $this->enrichAgentData($user);
        }
        
        return $user;
    }

    /**
     * Obtenir les activités d'un utilisateur
     */
    private function getUserActivities($user)
    {
        // Placeholder pour l'historique d'activité
        // À terme, utiliser une table d'audit ou activity_logs
        return collect([
            [
                'date' => now()->subDays(1),
                'action' => 'Connexion',
                'description' => 'Connexion à la plateforme',
                'ip' => '192.168.1.1'
            ],
            [
                'date' => now()->subDays(2),
                'action' => 'Validation',
                'description' => 'Validation d\'un dossier',
                'ip' => '192.168.1.1'
            ]
        ]);
    }

    /**
     * Obtenir les statistiques détaillées d'un utilisateur
     */
    private function getDetailedUserStats($user)
    {
        $stats = [
            'account_age' => $user->created_at->diffInDays(now()),
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais',
            'total_logins' => 0, // À implémenter avec table sessions
            'failed_attempts' => $user->failed_login_attempts ?? 0,
        ];

        if ($user->role === 'agent') {
            $stats['dossiers_traites'] = $user->dossierValidations ? $user->dossierValidations->count() : 0;
            $stats['dossiers_en_cours'] = $user->assignedDossiers ? 
                $user->assignedDossiers->where('statut', 'en_cours')->count() : 0;
        } elseif ($user->role === 'operator') {
            $stats['organisations_creees'] = $user->organisations ? $user->organisations->count() : 0;
            $stats['dossiers_soumis'] = 0; // À calculer selon vos relations
        }

        return $stats;
    }

    /**
     * Obtenir le nombre d'agents avec charge de travail
     */
    private function getAgentsWithWorkload()
    {
        try {
            return User::where('role', 'agent')
                ->whereHas('assignedDossiers', function($query) {
                    $query->where('statut', 'en_cours');
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}