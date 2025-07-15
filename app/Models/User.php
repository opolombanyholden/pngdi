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

    /**
     * ✅ FILLABLE - Mis à jour avec nouvelles colonnes nom/prenom
     */
    protected $fillable = [
        // Identification de base
        'name',
        'email',
        'password',
        
        // ✅ NOUVELLES COLONNES AJOUTÉES PAR LA MIGRATION (OPTIONNELLES)
        'nom',                 // ⭐ Nom de famille séparé
        'prenom',              // ⭐ Prénom séparé
        
        // Rôles et permissions
        'role',                // Ancien système de rôles
        'role_id',             // Nouveau système avancé
        'status',
        
        // Informations personnelles
        'phone',
        'nip',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'address',
        'city',
        'country',
        
        // Médias
        'photo_path',
        'avatar',
        
        // Sécurité et état
        'is_active',
        'is_verified',
        'verification_token',
        'two_factor_enabled',
        'two_factor_secret',
        
        // Connexions et sécurité
        'last_login_at',
        'last_login_ip',
        'login_attempts',
        'failed_login_attempts',
        'locked_until',
        
        // Métadonnées
        'preferences',
        'metadata',
        
        // Audit
        'created_by',
        'updated_by'
    ];

    /**
     * ✅ HIDDEN - Sécurité
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'verification_token',
    ];

    /**
     * ✅ CASTS - Mis à jour
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_naissance' => 'date',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'login_attempts' => 'integer',
        'failed_login_attempts' => 'integer',
        'preferences' => 'array',
        'metadata' => 'array',
    ];

    /**
     * ✅ CONSTANTES - Enrichies
     */
    // Ancien système de rôles (conservé pour compatibilité)
    const ROLE_ADMIN = 'admin';
    const ROLE_AGENT = 'agent';
    const ROLE_OPERATOR = 'operator';
    const ROLE_VISITOR = 'visitor';

    // Constantes pour les sexes
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';
    
    // ⭐ NOUVEAUX STATUTS AVANCÉS
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_PENDING = 'pending';

    /**
     * ✅ BOOT - Enrichi avec gestion nom/prenom
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Auto-assign creating user
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            
            // ⭐ AUTO-SPLIT nom/prenom si seulement 'name' fourni
            if (!empty($model->name) && empty($model->nom) && empty($model->prenom)) {
                $model->splitNameIntoParts();
            }
            
            // ⭐ AUTO-COMBINE nom+prenom si seulement eux fournis
            if (empty($model->name) && (!empty($model->nom) || !empty($model->prenom))) {
                $model->combinePartsIntoName();
            }
            
            // Définir le pays par défaut
            if (empty($model->country)) {
                $model->country = 'Gabon';
            }
        });
        
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            
            // ⭐ SYNCHRONISATION AUTOMATIQUE nom/prenom ↔ name
            if ($model->isDirty(['nom', 'prenom']) && !$model->isDirty('name')) {
                $model->combinePartsIntoName();
            } elseif ($model->isDirty('name') && !$model->isDirty(['nom', 'prenom'])) {
                $model->splitNameIntoParts();
            }
        });
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Séparer name en nom/prenom
     */
    private function splitNameIntoParts(): void
    {
        if (empty($this->name)) {
            return;
        }
        
        $nameParts = explode(' ', trim($this->name), 2);
        
        if (count($nameParts) >= 2) {
            $this->nom = trim($nameParts[0]);
            $this->prenom = trim($nameParts[1]);
        } else {
            $this->nom = trim($this->name);
            $this->prenom = null;
        }
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Combiner nom+prenom en name
     */
    private function combinePartsIntoName(): void
    {
        $parts = array_filter([trim($this->nom), trim($this->prenom)]);
        
        if (!empty($parts)) {
            $this->name = implode(' ', $parts);
        }
    }

    // =================================================================
    // AUTHENTIFICATION À DEUX FACTEURS - CORRECTION PRINCIPALE
    // =================================================================

    /**
     * ✅ CORRECTION - Méthode manquante requiresTwoFactor()
     */
    public function requiresTwoFactor(): bool
    {
        // Retourne true si l'utilisateur a activé la 2FA
        return $this->two_factor_enabled === true;
    }

    /**
     * ⭐ NOUVELLES MÉTHODES - Gestion complète 2FA
     */
    public function enableTwoFactor($secret = null): void
    {
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret ?? $this->generateTwoFactorSecret()
        ]);
    }

    public function disableTwoFactor(): void
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null
        ]);

        // Supprimer tous les codes 2FA existants
        $this->twoFactorCodes()->delete();
    }

    public function generateTwoFactorSecret(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function generateTwoFactorCode(): string
    {
        // Supprimer les anciens codes
        $this->twoFactorCodes()->where('expires_at', '<', now())->delete();
        
        // Générer un nouveau code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->twoFactorCodes()->create([
            'code' => $code,
            'expires_at' => now()->addMinutes(10), // Code valide 10 minutes
            'used' => false,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return $code;
    }

    public function verifyTwoFactorCode($code): bool
    {
        $twoFactorCode = $this->twoFactorCodes()
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($twoFactorCode) {
            $twoFactorCode->update([
                'used' => true,
                'used_at' => now()
            ]);
            return true;
        }

        return false;
    }

    public function hasValidTwoFactorCode(): bool
    {
        return $this->twoFactorCodes()
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->exists();
    }


        /**
     * Dossiers assignés à cet agent
     */
    public function assignedDossiers()
    {
        return $this->hasMany(Dossier::class, 'assigned_to');
    }

    /**
     * Validations effectuées par cet agent
     */
    public function dossierValidations()
    {
        return $this->hasMany(DossierValidation::class, 'validated_by');
    }

    /**
     * Organisations créées par cet opérateur
     */
    public function organisations()
    {
        return $this->hasMany(Organisation::class, 'user_id');
    }

    // =================================================================
    // RELATIONS - Enrichies avec nouvelles tables
    // =================================================================

    /**
     * ✅ RELATION AVANCÉE - Rôle du nouveau système
     */
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * ⭐ NOUVELLES RELATIONS - Audit utilisateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * ⭐ NOUVELLES RELATIONS - Sessions avancées
     */
    public function userSessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function activeUserSessions(): HasMany
    {
        return $this->userSessions()->where('is_active', true);
    }

    /**
     * ⭐ NOUVELLES RELATIONS - Documents uploadés
     */
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * ✅ RELATIONS EXISTANTES - PNGDI (conservées)
     */

    public function activeOrganisations(): HasMany
    {
        return $this->hasMany(Organisation::class)->where('is_active', true);
    }


    public function twoFactorCodes(): HasMany
    {
        return $this->hasMany(TwoFactorCode::class);
    }

   

    // =================================================================
    // SYSTÈME PERMISSIONS AVANCÉ
    // =================================================================

    /**
     * ✅ PERMISSIONS - Améliorées
     */
    public function hasAdvancedRole($roleName): bool
    {
        return $this->roleModel && $this->roleModel->name === $roleName;
    }

    public function hasPermission($permissionName): bool
    {
        if (!$this->roleModel) {
            return false;
        }
        
        return $this->roleModel->permissions()
            ->where('name', $permissionName)
            ->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->roleModel) {
            return false;
        }
        
        return $this->roleModel->permissions()
            ->whereIn('name', $permissions)
            ->exists();
    }

    public function getAllPermissions()
    {
        if (!$this->roleModel) {
            return collect();
        }
        
        return $this->roleModel->permissions;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAdvancedRole('super_admin') || $this->role === 'admin';
    }

    // =================================================================
    // MÉTHODES RÔLES - Compatibilité ancien/nouveau système
    // =================================================================

    /**
     * ✅ RÔLES - Système hybride ancien/nouveau
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN || 
               $this->hasAnyPermission(['users.create', 'users.edit', 'users.delete']) ||
               $this->hasAdvancedRole('super_admin');
    }

    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT || 
               $this->hasAdvancedRole('moderateur') ||
               $this->hasAnyPermission(['workflow.validate']);
    }

    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERATOR || 
               $this->hasAdvancedRole('operateur');
    }

    public function isVisitor(): bool
    {
        return $this->role === self::ROLE_VISITOR || 
               $this->hasAdvancedRole('auditeur');
    }

    public function hasRole($role): bool
    {
        if (is_array($role)) {
            $hasOldRole = in_array($this->role, $role);
            $hasNewRole = $this->roleModel && in_array($this->roleModel->name, $role);
            return $hasOldRole || $hasNewRole;
        }
        
        return $this->role === $role || $this->hasAdvancedRole($role);
    }

    // =================================================================
    // SÉCURITÉ ET AUTHENTIFICATION
    // =================================================================

    /**
     * ✅ SÉCURITÉ - Améliorée
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function lockAccount(int $minutes = 30): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
            'status' => self::STATUS_SUSPENDED
        ]);
    }

    public function incrementFailedAttempts(): void
    {
        $this->increment('failed_login_attempts');
        $this->increment('login_attempts');
        
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount();
        }
    }

    public function resetFailedAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'login_attempts' => 0,
            'locked_until' => null,
            'status' => self::STATUS_ACTIVE
        ]);
    }

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

    public function canLogin(): bool
    {
        return $this->is_active &&
               (!$this->status || $this->status === self::STATUS_ACTIVE) && 
               $this->login_attempts < 5 &&
               (!$this->locked_until || $this->locked_until->isPast());
    }

    // =================================================================
    // SCOPES - Enrichis
    // =================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->where('status', self::STATUS_ACTIVE)
                           ->orWhereNull('status');
                     });
    }

    public function scopeBlocked($query)
    {
        return $query->where('locked_until', '>', now())
                     ->orWhere('status', self::STATUS_SUSPENDED);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * ⭐ NOUVEAUX SCOPES - Recherche améliorée
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('nip', 'like', "%{$search}%");
              
            // ⭐ Recherche aussi dans nom/prenom séparés si colonnes existent
            if (Schema::hasColumn('users', 'nom')) {
                $q->orWhere('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%");
            }
        });
    }

    public function scopeByAdvancedRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    public function scopeWithAdvancedRole($query, $roleName)
    {
        return $query->whereHas('roleModel', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * ⭐ NOUVEAUX SCOPES - Gestion nom/prenom
     */
    public function scopeByNom($query, $nom)
    {
        if (Schema::hasColumn('users', 'nom')) {
            return $query->where('nom', 'like', "%{$nom}%");
        }
        
        // Fallback sur name
        return $query->where('name', 'like', "%{$nom}%");
    }

    public function scopeByPrenom($query, $prenom)
    {
        if (Schema::hasColumn('users', 'prenom')) {
            return $query->where('prenom', 'like', "%{$prenom}%");
        }
        
        // Fallback sur name
        return $query->where('name', 'like', "%{$prenom}%");
    }

    // =================================================================
    // ACCESSEURS - Enrichis avec nom/prenom
    // =================================================================

    /**
     * ⭐ NOUVEAUX ACCESSEURS - Gestion intelligente nom/prenom
     */
    public function getNomCompletAttribute(): string
    {
        // Priorité : nom + prenom si disponibles
        if (Schema::hasColumn('users', 'nom') && (!empty($this->nom) || !empty($this->prenom))) {
            return trim(($this->nom ?? '') . ' ' . ($this->prenom ?? ''));
        }
        
        // Fallback sur name
        return $this->name ?? '';
    }

    public function getNomFamilleAttribute(): string
    {
        if (Schema::hasColumn('users', 'nom') && !empty($this->nom)) {
            return $this->nom;
        }
        
        // Extraire de name si pas de nom séparé
        $parts = explode(' ', $this->name ?? '', 2);
        return $parts[0] ?? '';
    }

    public function getPrenomAttribute(): string
    {
        if (Schema::hasColumn('users', 'prenom') && !empty($this->prenom)) {
            return $this->prenom;
        }
        
        // Extraire de name si pas de prénom séparé
        $parts = explode(' ', $this->name ?? '', 2);
        return $parts[1] ?? '';
    }

    public function getInitialesAttribute(): string
    {
        $nom = $this->nom_famille;
        $prenom = $this->getPrenom();
        
        $initiales = '';
        if (!empty($nom)) {
            $initiales .= strtoupper(substr($nom, 0, 1));
        }
        if (!empty($prenom)) {
            $initiales .= strtoupper(substr($prenom, 0, 1));
        }
        
        return $initiales ?: strtoupper(substr($this->name ?? 'U', 0, 2));
    }

    /**
     * ✅ ACCESSEURS EXISTANTS - Améliorés
     */
    public function getRoleLabelAttribute(): string
    {
        if ($this->roleModel) {
            return $this->roleModel->display_name;
        }
        
        $labels = [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_AGENT => 'Agent',
            self::ROLE_OPERATOR => 'Opérateur',
            self::ROLE_VISITOR => 'Visiteur'
        ];

        return $labels[$this->role] ?? $this->role;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->status) {
            return $this->is_active ? 'Actif' : 'Inactif';
        }
        
        $statuses = [
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_INACTIVE => 'Inactif',
            self::STATUS_SUSPENDED => 'Suspendu',
            self::STATUS_PENDING => 'En attente'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    public function getPhotoUrlAttribute(): string
    {
        // Priorité : avatar, puis photo_path, puis défaut avec initiales
        if ($this->avatar && file_exists(storage_path('app/public/avatars/' . $this->avatar))) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        if ($this->photo_path && file_exists(storage_path('app/public/' . $this->photo_path))) {
            return asset('storage/' . $this->photo_path);
        }
        
        // ⭐ Avatar par défaut avec initiales intelligentes
        $initiales = $this->initiales;
        return "https://ui-avatars.com/api/?name={$initiales}&background=009e3f&color=fff&size=128&font-size=0.5";
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

    /**
     * ⭐ NOUVEAUX ACCESSEURS - Métadonnées avancées
     */
    public function getHasNomPrenomSeparesAttribute(): bool
    {
        return Schema::hasColumn('users', 'nom') && 
               Schema::hasColumn('users', 'prenom');
    }

    public function getFullRoleNameAttribute(): string
    {
        if ($this->roleModel) {
            return $this->roleModel->display_name . ' (Système avancé)';
        }
        
        return $this->role_label . ' (Système de base)';
    }

    public function getAdvancedStatsAttribute(): array
    {
        return [
            'organisations_created' => $this->organisations()->count(),
            'documents_uploaded' => $this->uploadedDocuments()->count(),
            'last_activity' => $this->last_login_at,
            'sessions_count' => $this->userSessions()->count(),
            'role_level' => $this->roleModel ? $this->roleModel->level : 0,
            'permissions_count' => $this->getAllPermissions()->count(),
            'is_verified' => $this->is_verified,
            'login_attempts' => $this->login_attempts,
        ];
    }

    // =================================================================
    // MÉTHODES UTILITAIRES - Enrichies
    // =================================================================

    /**
     * ⭐ NOUVELLES MÉTHODES - Gestion nom/prenom
     */
    public function updateName($nom, $prenom = null): void
    {
        if ($this->has_nom_prenom_separes) {
            $this->update([
                'nom' => $nom,
                'prenom' => $prenom,
                'name' => trim($nom . ' ' . $prenom)
            ]);
        } else {
            $this->update([
                'name' => trim($nom . ' ' . $prenom)
            ]);
        }
    }

    /**
     * ✅ MÉTHODES MÉTIER - Conservées et améliorées
     */
    public function canCreateOrganisation($type): bool
    {
        if (!$this->isOperator()) {
            return false;
        }

        if (in_array($type, ['parti_politique', 'confession_religieuse'])) {
            return !$this->activeOrganisations()
                ->where('type', $type)
                ->exists();
        }

        return true;
    }

    public function canValidateDossiers(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_AGENT]) ||
               $this->hasAnyPermission(['orgs.validate', 'workflow.validate']);
    }

    public function getPerformanceStats(): array
    {
        if (!$this->canValidateDossiers()) {
            return [];
        }

        $totalAssigned = $this->assignedDossiers()->count();
        $enCours = $this->assignedDossiers()->where('statut', 'en_cours')->count();

        return [
            'total_assignes' => $totalAssigned,
            'en_cours' => $enCours,
            'charge_actuelle' => $enCours,
            'documents_uploades' => $this->uploadedDocuments()->count(),
        ];
    }

    // =================================================================
    // MÉTHODES EXPORT ET FORMATAGE
    // =================================================================

    /**
     * ⭐ EXPORT - Avec nom/prenom séparés
     */
    public function toExportArray(): array
    {
        $data = [
            'ID' => $this->id,
            'Email' => $this->email,
            'NIP' => $this->nip,
            'Téléphone' => $this->phone,
            'Rôle' => $this->role_label,
            'Statut' => $this->status_label,
            'Vérifié' => $this->is_verified ? 'Oui' : 'Non',
            'Actif' => $this->is_active ? 'Oui' : 'Non',
            'Dernière connexion' => $this->last_login_at ? $this->last_login_at->format('d/m/Y H:i') : 'Jamais',
            'Créé le' => $this->created_at->format('d/m/Y H:i'),
        ];

        // ⭐ Ajouter nom/prenom séparés si disponibles
        if ($this->has_nom_prenom_separes) {
            $data['Nom'] = $this->nom ?? '';
            $data['Prénom'] = $this->prenom ?? '';
        } else {
            $data['Nom complet'] = $this->name;
        }

        return $data;
    }

    /**
     * ⭐ FORMATAGE - Pour APIs et JSON
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nom' => $this->nom ?? null,
            'prenom' => $this->prenom ?? null,
            'nom_complet' => $this->nom_complet,
            'initiales' => $this->initiales,
            'email' => $this->email,
            'photo_url' => $this->photo_url,
            'role' => $this->role,
            'role_label' => $this->role_label,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'last_login_at' => $this->last_login_at,
        ];
    }

    // =================================================================
    // MUTATEURS - Nouveaux
    // =================================================================

    /**
     * ⭐ MUTATEURS - Nettoyage automatique
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = preg_replace('/[^0-9+]/', '', $value);
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    public function setNomAttribute($value): void
    {
        $this->attributes['nom'] = $value ? ucfirst(strtolower(trim($value))) : null;
    }

    public function setPrenomAttribute($value): void
    {
        $this->attributes['prenom'] = $value ? ucfirst(strtolower(trim($value))) : null;
    }

    // =================================================================
    // MÉTHODES STATIQUES - Enrichies
    // =================================================================

    /**
     * ✅ UTILITAIRES STATIQUES
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

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_INACTIVE => 'Inactif',
            self::STATUS_SUSPENDED => 'Suspendu',
            self::STATUS_PENDING => 'En attente'
        ];
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Statistiques globales utilisateurs
     */
    public static function getGlobalStats(): array
    {
        return [
            'total_users' => self::count(),
            'active_users' => self::active()->count(),
            'verified_users' => self::where('is_verified', true)->count(),
            'admins' => self::where('role', self::ROLE_ADMIN)->count(),
            'operators' => self::where('role', self::ROLE_OPERATOR)->count(),
            'agents' => self::where('role', self::ROLE_AGENT)->count(),
            'recent_logins' => self::where('last_login_at', '>=', now()->subDays(7))->count(),
        ];
    }

    // =================================================================
    // NOTIFICATIONS PERSONNALISÉES
    // =================================================================

    /**
     * ✅ NOTIFICATIONS - Conservées
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * ⭐ NOUVELLES MÉTHODES - Gestion préférences
     */
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

    /**
     * ⭐ NOUVELLES MÉTHODES - Sessions avancées
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
}