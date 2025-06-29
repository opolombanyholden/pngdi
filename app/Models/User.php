<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    // SUPPRIMÉ: SoftDeletes (car colonne deleted_at n'existe pas)

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'city',
        'country',
        'is_active',
        'two_factor_enabled',
        'two_factor_secret',
        'last_login_at',
        'last_login_ip',
        'locked_until',
        'failed_login_attempts',
        // Champs PNGDI existants
        'nip',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'photo_path',
        'preferences',
        'metadata',
        // Nouveaux champs système avancé
        'role_id',
        'status',
        'login_attempts',
        'is_verified',
        'verification_token',
        'avatar',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'verification_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'failed_login_attempts' => 'integer',
        // Nouveaux casts
        'date_naissance' => 'date',
        'preferences' => 'array',
        'metadata' => 'array',
        // Système avancé
        'is_verified' => 'boolean',
        'login_attempts' => 'integer',
    ];

    // Constantes pour les rôles (ANCIEN SYSTÈME - Conservé pour compatibilité)
    const ROLE_ADMIN = 'admin';
    const ROLE_AGENT = 'agent';
    const ROLE_OPERATOR = 'operator';
    const ROLE_VISITOR = 'visitor';

    // Constantes pour les sexes
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-assign creating user
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
        
        // Auto-assign updating user
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    // =================================================================
    // RELATIONS SYSTÈME AVANCÉ (NOUVELLES)
    // =================================================================

    /**
     * Relation avec le rôle PNGDI avancé
     */
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Utilisateur créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur modificateur
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Sessions utilisateur avancées
     */
    public function userSessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Sessions actives avancées
     */
    public function activeUserSessions(): HasMany
    {
        return $this->userSessions()->where('is_active', true);
    }

    // =================================================================
    // SYSTÈME PERMISSIONS AVANCÉ (NOUVEAU)
    // =================================================================

    /**
     * Vérifier si l'utilisateur a un rôle avancé
     */
    public function hasAdvancedRole($roleName): bool
    {
        return $this->roleModel && $this->roleModel->name === $roleName;
    }

    /**
     * Vérifier si l'utilisateur a une permission
     */
    public function hasPermission($permissionName): bool
    {
        if (!$this->roleModel) {
            return false;
        }
        
        return $this->roleModel->permissions()
            ->where('name', $permissionName)
            ->exists();
    }

    /**
     * Vérifier si l'utilisateur a l'une des permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->roleModel) {
            return false;
        }
        
        return $this->roleModel->permissions()
            ->whereIn('name', $permissions)
            ->exists();
    }

    /**
     * Vérifier si l'utilisateur a toutes les permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->roleModel) {
            return false;
        }
        
        $userPermissions = $this->roleModel->permissions()->pluck('name')->toArray();
        return count(array_intersect($permissions, $userPermissions)) === count($permissions);
    }

    /**
     * Obtenir toutes les permissions de l'utilisateur
     */
    public function getAllPermissions()
    {
        if (!$this->roleModel) {
            return collect();
        }
        
        return $this->roleModel->permissions;
    }

    /**
     * Vérifier si l'utilisateur est super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasAdvancedRole('super_admin');
    }

    /**
     * Vérifier si l'utilisateur peut gérer le type d'organisation
     */
    public function canManageOrganisationType($type): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        $rolePermissions = [
            'association' => 'admin_associations',
            'confession_religieuse' => 'admin_religieuses',
            'parti_politique' => 'admin_politiques'
        ];
        
        return $this->hasAdvancedRole($rolePermissions[$type] ?? null) || 
               $this->hasAdvancedRole('admin_general');
    }

    // =================================================================
    // MÉTHODES EXISTANTES (CONSERVÉES ET AMÉLIORÉES)
    // =================================================================

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Check if user is admin (ANCIEN SYSTÈME - Conservé)
     */
    public function isAdmin(): bool
    {
        // Compatibilité : ancien système OU nouveau système
        return $this->role === 'admin' || 
               $this->hasAnyPermission(['users.create', 'users.edit', 'users.delete']);
    }

    /**
     * Check if user is agent
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent' || 
               $this->hasAdvancedRole('moderateur');
    }

    /**
     * Check if user is operator
     */
    public function isOperator(): bool
    {
        return $this->role === 'operator' || 
               $this->hasAdvancedRole('operateur');
    }

    /**
     * Check if user is visitor
     */
    public function isVisitor(): bool
    {
        return $this->role === 'visitor' || 
               $this->hasAdvancedRole('auditeur');
    }

    /**
     * Check if user has role (AMÉLIORÉ - supporte ancien et nouveau)
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            // Vérifier ancien système
            $hasOldRole = in_array($this->role, $role);
            // Vérifier nouveau système
            $hasNewRole = $this->roleModel && in_array($this->roleModel->name, $role);
            return $hasOldRole || $hasNewRole;
        }
        
        return $this->role === $role || $this->hasAdvancedRole($role);
    }

    /**
     * Check if user account is locked
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock user account
     */
    public function lockAccount(int $minutes = 30): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
            'status' => 'suspended'
        ]);
    }

    /**
     * Increment failed login attempts (AMÉLIORÉ)
     */
    public function incrementFailedAttempts(): void
    {
        $this->increment('failed_login_attempts');
        $this->increment('login_attempts');
        
        // Lock account after 5 failed attempts
        if ($this->failed_login_attempts >= 5 || $this->login_attempts >= 5) {
            $this->lockAccount();
        }
    }

    /**
     * Reset failed login attempts (AMÉLIORÉ)
     */
    public function resetFailedAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'login_attempts' => 0,
            'locked_until' => null,
            'status' => 'active'
        ]);
    }

    /**
     * Record successful login (AMÉLIORÉ)
     */
    public function recordLogin($ip = null): void
    {
        $request = request();
        
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? $request->ip(),
            'failed_login_attempts' => 0,
            'login_attempts' => 0
        ]);

        // Créer session avancée si table existe
        if (Schema::hasTable('user_sessions')) {
            UserSession::create([
                'user_id' => $this->id,
                'session_id' => session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
                'is_active' => true
            ]);
        }
    }

    /**
     * Relation avec les codes 2FA
     */
    public function twoFactorCodes()
    {
        return $this->hasMany(TwoFactorCode::class);
    }

    /**
     * Vérifier si l'utilisateur doit utiliser 2FA
     */
    public function requiresTwoFactor(): bool
    {
        return in_array($this->role, ['admin', 'agent']) && $this->two_factor_enabled;
    }

    /**
     * Générer un nouveau code 2FA
     */
    public function generateTwoFactorCode(): TwoFactorCode
    {
        // Supprimer les anciens codes non utilisés
        $this->twoFactorCodes()->where('used', false)->delete();
        
        // Générer un nouveau code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        return $this->twoFactorCodes()->create([
            'code' => $code,
            'expires_at' => now()->addMinutes(10)
        ]);
    }

    // =================================================================
    // RELATIONS PNGDI EXISTANTES (CONSERVÉES)
    // =================================================================

    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class);
    }

    public function activeOrganisations(): HasMany
    {
        return $this->hasMany(Organisation::class)
            ->where('is_active', true);
    }

    // ===== RELATIONS WORKFLOW ADMIN EXISTANTES (CONSERVÉES) =====

    public function assignedDossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'assigned_to');
    }

    public function dossiersEnCours(): HasMany
    {
        return $this->assignedDossiers()->where('statut', 'en_cours');
    }

    public function dossiersValides(): HasMany
    {
        return $this->hasMany(DossierValidation::class, 'validated_by')
            ->whereNotNull('validated_at');
    }

    public function dossierComments(): HasMany
    {
        return $this->hasMany(DossierComment::class, 'user_id');
    }

    public function dossierOperations(): HasMany
    {
        return $this->hasMany(DossierOperation::class, 'user_id');
    }

    public function dossierArchives(): HasMany
    {
        return $this->hasMany(DossierArchive::class, 'archived_by');
    }

    public function adherentImports(): HasMany
    {
        return $this->hasMany(AdherentImport::class, 'imported_by');
    }

    public function inscriptionLinks(): HasMany
    {
        return $this->hasMany(InscriptionLink::class, 'created_by');
    }

    public function validatedExclusions(): HasMany
    {
        return $this->hasMany(AdherentExclusion::class, 'validated_by');
    }

    public function createdAdherentHistories(): HasMany
    {
        return $this->hasMany(AdherentHistory::class, 'created_by');
    }

    public function validatedAdherentHistories(): HasMany
    {
        return $this->hasMany(AdherentHistory::class, 'validated_by');
    }

    public function submittedDeclarations(): HasMany
    {
        return $this->hasMany(Declaration::class, 'submitted_by');
    }

    public function validatedDeclarations(): HasMany
    {
        return $this->hasMany(Declaration::class, 'validated_by');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(DossierValidation::class, 'assigned_to');
    }

    public function assignedValidations(): HasMany
    {
        return $this->hasMany(DossierValidation::class, 'assigned_by');
    }

    public function completedValidations(): HasMany
    {
        return $this->hasMany(DossierValidation::class, 'validated_by');
    }

    public function dossierLocks(): HasMany
    {
        return $this->hasMany(DossierLock::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function entityAgents(): HasMany
    {
        return $this->hasMany(EntityAgent::class);
    }

    // =================================================================
    // SCOPES (CONSERVÉS ET AMÉLIORÉS)
    // =================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->where('status', 'active')
                           ->orWhereNull('status');
                     });
    }

    public function scopeBlocked($query)
    {
        return $query->where('locked_until', '>', now())
                     ->orWhere('status', 'suspended');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeAgents($query)
    {
        return $query->where('role', self::ROLE_AGENT);
    }

    public function scopeOperators($query)
    {
        return $query->where('role', self::ROLE_OPERATOR);
    }

    // NOUVEAUX SCOPES AVANCÉS

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByAdvancedRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('nip', 'like', "%{$search}%");
        });
    }

    public function scopeWithAdvancedRole($query, $roleName)
    {
        return $query->whereHas('roleModel', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    // =================================================================
    // ACCESSEURS (CONSERVÉS ET AMÉLIORÉS)
    // =================================================================

    public function getRoleLabelAttribute(): string
    {
        // Priorité au nouveau système
        if ($this->roleModel) {
            return $this->roleModel->display_name;
        }
        
        // Fallback ancien système
        $labels = [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_AGENT => 'Agent',
            self::ROLE_OPERATOR => 'Opérateur',
            self::ROLE_VISITOR => 'Visiteur'
        ];

        return $labels[$this->role] ?? $this->role;
    }

    public function getSexeLabelAttribute(): string
    {
        if (!$this->sexe) return '';
        return $this->sexe === self::SEXE_MASCULIN ? 'Masculin' : 'Féminin';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance ? $this->date_naissance->age : null;
    }

    public function getPhotoUrlAttribute(): string
    {
        // Priorité : avatar, puis photo_path, puis défaut gabonais
        if ($this->avatar && file_exists(storage_path('app/public/avatars/' . $this->avatar))) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        if ($this->photo_path && file_exists(storage_path('app/public/' . $this->photo_path))) {
            return asset('storage/' . $this->photo_path);
        }
        
        // Avatar par défaut avec couleurs gabonaises
        $initials = strtoupper(substr($this->name, 0, 2));
        return "https://ui-avatars.com/api/?name={$initials}&background=009e3f&color=fff&size=128&font-size=0.5";
    }

    // NOUVEAUX ACCESSEURS AVANCÉS

    public function getStatusLabelAttribute(): string
    {
        if (!$this->status) {
            return $this->is_active ? 'Actif' : 'Inactif';
        }
        
        $statuses = [
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'suspended' => 'Suspendu',
            'pending' => 'En attente'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    public function getFullRoleNameAttribute(): string
    {
        if ($this->roleModel) {
            return $this->roleModel->display_name . ' (Nouveau système)';
        }
        
        return $this->role_label . ' (Ancien système)';
    }

    // =================================================================
    // MÉTHODES MÉTIER (CONSERVÉES ET AMÉLIORÉES)
    // =================================================================

    public function canCreateOrganisation($type): bool
    {
        if (!$this->isOperator()) {
            return false;
        }

        // Vérifier les limites pour parti politique et confession religieuse
        if (in_array($type, [Organisation::TYPE_PARTI, Organisation::TYPE_CONFESSION])) {
            return !$this->activeOrganisations()
                ->where('type', $type)
                ->exists();
        }

        return true;
    }

    public function canValidateDossiers(): bool
    {
        // Ancien système OU nouveau système
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_AGENT]) ||
               $this->hasAnyPermission(['orgs.validate', 'workflow.validate']);
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin() || 
               $this->hasAnyPermission(['users.create', 'users.edit', 'users.delete']);
    }

    public function canAccessBackoffice(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_AGENT]) ||
               $this->hasAnyPermission(['workflow.view', 'orgs.view']);
    }

    // =================================================================
    // MÉTHODES AVANCÉES SÉCURITÉ (NOUVELLES)
    // =================================================================

    /**
     * Fermer toutes les sessions actives
     */
    public function logoutAllSessions(): void
    {
        if (Schema::hasTable('user_sessions')) {
            $this->activeUserSessions()->update([
                'logout_at' => now(),
                'is_active' => false
            ]);
        }
    }

    /**
     * Vérifier si l'utilisateur peut se connecter
     */
    public function canLogin(): bool
    {
        return $this->is_active &&
               (!$this->status || in_array($this->status, ['active'])) && 
               $this->login_attempts < 5 &&
               (!$this->locked_until || $this->locked_until->isPast());
    }

    /**
     * Générer un token de vérification
     */
    public function generateVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update(['verification_token' => $token]);
        return $token;
    }

    /**
     * Marquer comme vérifié
     */
    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verification_token' => null,
            'email_verified_at' => now(),
            'status' => 'active'
        ]);
    }

    // =================================================================
    // MÉTHODES UTILITAIRES EXISTANTES (CONSERVÉES)
    // =================================================================

    public function getPreference($key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference($key, $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->update(['preferences' => $preferences]);
    }

    public function getDossiersEnCoursCount(): int
    {
        return $this->assignedDossiers()
            ->where('statut', 'en_cours')
            ->count();
    }

    public function getDossiersValidesThisMonth(): int
    {
        return $this->dossiersValides()
            ->whereHas('dossier', function($query) {
                $query->where('statut', 'approuve')
                      ->whereMonth('validated_at', now()->month)
                      ->whereYear('validated_at', now()->year);
            })
            ->count();
    }

    public function getChargeActuelle(): int
    {
        return $this->assignedDossiers()
            ->whereIn('statut', ['soumis', 'en_cours'])
            ->count();
    }

    public function canReceiveNewDossiers(int $capaciteMax = 5): bool
    {
        return $this->getChargeActuelle() < $capaciteMax;
    }

    public function getPerformanceStats(): array
    {
        if (!$this->canValidateDossiers()) {
            return [];
        }

        $totalAssigned = $this->assignedDossiers()->count();
        $totalValidated = $this->dossiersValides()->count();
        $enCours = $this->getDossiersEnCoursCount();
        $thisMonth = $this->getDossiersValidesThisMonth();

        return [
            'total_assignes' => $totalAssigned,
            'total_valides' => $totalValidated,
            'en_cours' => $enCours,
            'valides_ce_mois' => $thisMonth,
            'taux_completion' => $totalAssigned > 0 ? round(($totalValidated / $totalAssigned) * 100, 1) : 0,
            'charge_actuelle' => $this->getChargeActuelle()
        ];
    }

    public function isAvailableForAssignment(): bool
    {
        return $this->is_active && 
               $this->canValidateDossiers() && 
               $this->canReceiveNewDossiers() &&
               !$this->isLocked() &&
               $this->canLogin();
    }

    public function getStatistics(): array
    {
        $stats = [
            'organisations' => [
                'total' => $this->organisations()->count(),
                'active' => $this->activeOrganisations()->count(),
                'approved' => $this->organisations()->where('statut', Organisation::STATUT_APPROUVE)->count()
            ]
        ];

        if ($this->canValidateDossiers()) {
            $stats['validations'] = [
                'total' => $this->validations()->count(),
                'pending' => $this->validations()->whereNull('decision')->count(),
                'completed' => $this->validations()->whereNotNull('decision')->count()
            ];

            $stats['performance'] = $this->getPerformanceStats();
        }

        // Ajouter statistiques avancées
        if ($this->roleModel) {
            $stats['advanced'] = [
                'role_level' => $this->roleModel->level,
                'permissions_count' => $this->getAllPermissions()->count(),
                'sessions_count' => $this->userSessions()->count(),
                'last_activity' => $this->last_login_at,
            ];
        }

        return $stats;
    }

    public function logActivity($action, $description = null, $properties = []): void
    {
        $this->activityLogs()->create([
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    // =================================================================
    // MÉTHODES UTILITAIRES AVANCÉES (NOUVELLES)
    // =================================================================

    /**
     * Obtenir les statistiques complètes utilisateur
     */
    public function getAdvancedStatsAttribute(): array
    {
        return [
            'organisations_created' => $this->organisations()->count(),
            'last_activity' => $this->last_login_at,
            'sessions_count' => $this->userSessions()->count(),
            'role_level' => $this->roleModel ? $this->roleModel->level : 0,
            'permissions_count' => $this->getAllPermissions()->count(),
            'is_verified' => $this->is_verified,
            'login_attempts' => $this->login_attempts,
            'performance' => $this->getPerformanceStats(),
        ];
    }

    /**
     * Vérifier si l'utilisateur est gabonais
     */
    public function isGabonese(): bool
    {
        return $this->country === 'Gabon' || 
               (isset($this->metadata['nationalite']) && $this->metadata['nationalite'] === 'Gabonaise');
    }

    /**
     * Formatter pour l'export
     */
    public function toExportArray(): array
    {
        return [
            'ID' => $this->id,
            'Nom' => $this->name,
            'Email' => $this->email,
            'NIP' => $this->nip,
            'Téléphone' => $this->phone,
            'Rôle Ancien' => $this->role,
            'Rôle Nouveau' => $this->roleModel ? $this->roleModel->display_name : 'N/A',
            'Statut' => $this->status_label,
            'Vérifié' => $this->is_verified ? 'Oui' : 'Non',
            'Actif' => $this->is_active ? 'Oui' : 'Non',
            'Dernière connexion' => $this->last_login_at ? $this->last_login_at->format('d/m/Y H:i') : 'Jamais',
            'Créé le' => $this->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Obtenir les rôles disponibles (CONSERVÉ)
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_AGENT => 'Agent',
            self::ROLE_OPERATOR => 'Opérateur',
            self::ROLE_VISITOR => 'Visiteur'
        ];
    }

    // =================================================================
    // MUTATEURS (NOUVEAUX)
    // =================================================================

    /**
     * Mutateur pour le téléphone
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = preg_replace('/[^0-9+]/', '', $value);
    }

    /**
     * Mutateur pour l'email (lowercase)
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower($value);
    }
}