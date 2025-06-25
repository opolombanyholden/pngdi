<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

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
        // Nouveaux champs pour PNGDI
        'nip',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'photo_path',
        'preferences',
        'metadata'
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
        'metadata' => 'array'
    ];

    // Constantes pour les rôles
    const ROLE_ADMIN = 'admin';
    const ROLE_AGENT = 'agent';
    const ROLE_OPERATOR = 'operator';
    const ROLE_VISITOR = 'visitor';

    // Constantes pour les sexes
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';

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
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is agent
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Check if user is operator
     */
    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    /**
     * Check if user is visitor
     */
    public function isVisitor(): bool
    {
        return $this->role === 'visitor';
    }

    /**
     * Check if user has role
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
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
            'locked_until' => now()->addMinutes($minutes)
        ]);
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedAttempts(): void
    {
        $this->increment('failed_login_attempts');
        
        // Lock account after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount();
        }
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Record successful login
     */
    public function recordLogin($ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
            'failed_login_attempts' => 0
        ]);
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

    /**
     * Relations PNGDI
     */
    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class);
    }

    public function activeOrganisations(): HasMany
    {
        return $this->hasMany(Organisation::class)
            ->where('is_active', true);
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

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBlocked($query)
    {
        return $query->where('locked_until', '>', now());
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

    /**
     * Accesseurs
     */
    public function getRoleLabelAttribute(): string
    {
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
        if ($this->photo_path && file_exists(storage_path('app/public/' . $this->photo_path))) {
            return asset('storage/' . $this->photo_path);
        }
        return asset('images/default-avatar.png');
    }

    /**
     * Permissions et capacités
     */
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
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_AGENT]);
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canAccessBackoffice(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_AGENT]);
    }

    /**
     * Préférences utilisateur
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
     * Statistiques utilisateur
     */
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
        }

        return $stats;
    }

    /**
     * Activité
     */
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

    /**
     * Obtenir les rôles disponibles
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
}