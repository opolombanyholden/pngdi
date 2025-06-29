<?php
// ========================================================================
// FICHIER: app/Models/OrganizationDraft.php
// Modèle pour les brouillons d'organisations
// ========================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OrganizationDraft extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organization_drafts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'organization_type',
        'form_data',
        'current_step',
        'completion_percentage',
        'validation_errors',
        'session_id',
        'last_saved_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'form_data' => 'array',
        'validation_errors' => 'array',
        'last_saved_at' => 'datetime',
        'expires_at' => 'datetime',
        'completion_percentage' => 'integer',
        'current_step' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'session_id',
    ];

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * Get the user that owns the draft.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope pour filtrer par type d'organisation
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('organization_type', $type);
    }

    /**
     * Scope pour les brouillons récents
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('last_saved_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour les brouillons expirés
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope pour les brouillons actifs (non expirés)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>=', now());
        });
    }

    /**
     * Scope pour filtrer par étape
     */
    public function scopeAtStep(Builder $query, int $step): Builder
    {
        return $query->where('current_step', $step);
    }

    /**
     * Scope pour les brouillons avec un pourcentage de completion minimum
     */
    public function scopeMinCompletion(Builder $query, int $percentage): Builder
    {
        return $query->where('completion_percentage', '>=', $percentage);
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get the human readable organization type.
     */
    public function getOrganizationTypeHumanAttribute(): string
    {
        $types = [
            'association' => 'Association',
            'ong' => 'ONG',
            'parti_politique' => 'Parti Politique',
            'confession_religieuse' => 'Confession Religieuse',
        ];

        return $types[$this->organization_type] ?? 'Non défini';
    }

    /**
     * Check if the draft is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get the time since last save in human readable format.
     */
    public function getLastSavedHumanAttribute(): string
    {
        return $this->last_saved_at->diffForHumans();
    }

    /**
     * Get the completion status.
     */
    public function getCompletionStatusAttribute(): string
    {
        if ($this->completion_percentage === 0) return 'Pas commencé';
        if ($this->completion_percentage < 25) return 'Démarré';
        if ($this->completion_percentage < 50) return 'En cours';
        if ($this->completion_percentage < 75) return 'Avancé';
        if ($this->completion_percentage < 100) return 'Presque terminé';
        return 'Complet';
    }

    /**
     * Set the expiration date automatically when updating.
     */
    public function setLastSavedAtAttribute($value)
    {
        $this->attributes['last_saved_at'] = $value;
        
        // Auto-définir l'expiration à 7 jours si non définie
        if (!$this->expires_at) {
            $this->attributes['expires_at'] = now()->addDays(7);
        }
    }

    // ========================================
    // MÉTHODES MÉTIER
    // ========================================

    /**
     * Update the completion percentage based on form data.
     */
    public function updateCompletionPercentage(): void
    {
        $totalSteps = 9;
        $completedSteps = 0;
        
        if (is_array($this->form_data) && isset($this->form_data['steps'])) {
            foreach ($this->form_data['steps'] as $stepData) {
                if (is_array($stepData) && !empty(array_filter($stepData))) {
                    $completedSteps++;
                }
            }
        }
        
        $this->completion_percentage = round(($completedSteps / $totalSteps) * 100);
    }

    /**
     * Extend the expiration date.
     */
    public function extendExpiration(int $days = 7): void
    {
        $this->expires_at = now()->addDays($days);
        $this->save();
    }

    /**
     * Mark as recently saved.
     */
    public function touch($attribute = null)
    {
        if ($attribute === null) {
            $this->last_saved_at = now();
        }
        
        return parent::touch($attribute);
    }

    /**
     * Get summary data for display.
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'organization_type' => $this->organization_type_human,
            'current_step' => $this->current_step,
            'completion_percentage' => $this->completion_percentage,
            'completion_status' => $this->completion_status,
            'last_saved' => $this->last_saved_human,
            'is_expired' => $this->is_expired,
            'expires_at' => $this->expires_at ? $this->expires_at->format('d/m/Y H:i') : null,
        ];
    }

    /**
     * Convert form data to organization data for final submission.
     */
    public function toOrganizationData(): array
    {
        $formData = $this->form_data;
        
        // Transformer les données du formulaire en format attendu par Organisation
        $organizationData = [
            'type' => $this->organization_type,
            'user_id' => $this->user_id,
        ];
        
        // Extraire les données des différentes étapes
        if (isset($formData['steps'])) {
            // Étape 3 - Informations demandeur (déjà dans user)
            // Étape 4 - Informations organisation
            if (isset($formData['steps'][4])) {
                $orgData = $formData['steps'][4];
                $organizationData = array_merge($organizationData, [
                    'nom' => $orgData['org_nom'] ?? '',
                    'sigle' => $orgData['org_sigle'] ?? null,
                    'objet' => $orgData['org_objet'] ?? '',
                    'email' => $orgData['org_email'] ?? null,
                    'telephone' => $orgData['org_telephone'] ?? '',
                    'site_web' => $orgData['org_site_web'] ?? null,
                ]);
            }
            
            // Étape 5 - Coordonnées
            if (isset($formData['steps'][5])) {
                $coordData = $formData['steps'][5];
                $organizationData = array_merge($organizationData, [
                    'siege_social' => $coordData['org_adresse_complete'] ?? '',
                    'province' => $coordData['org_province'] ?? '',
                    'departement' => $coordData['org_departement'] ?? null,
                    'prefecture' => $coordData['org_prefecture'] ?? '',
                    'zone_type' => $coordData['org_zone_type'] ?? 'urbaine',
                    'latitude' => $coordData['org_latitude'] ?? null,
                    'longitude' => $coordData['org_longitude'] ?? null,
                ]);
            }
        }
        
        return $organizationData;
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================

    /**
     * Find or create a draft for a user and organization type.
     */
    public static function findOrCreateForUser(int $userId, ?string $organizationType = null): self
    {
        return static::firstOrCreate([
            'user_id' => $userId,
            'organization_type' => $organizationType,
        ], [
            'form_data' => [],
            'current_step' => 1,
            'last_saved_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Clean up old/expired drafts.
     */
    public static function cleanupOldDrafts(int $days = 30): int
    {
        return static::where('last_saved_at', '<', now()->subDays($days))
                    ->orWhere('expires_at', '<', now()->subDays(7)) // Expirés depuis plus de 7 jours
                    ->delete();
    }

    /**
     * Get user's drafts summary.
     */
    public static function getUserDraftsSummary(int $userId): array
    {
        $drafts = static::where('user_id', $userId)
                       ->active()
                       ->orderBy('last_saved_at', 'desc')
                       ->get();
        
                       return $drafts->map(function($draft) {
                        return $draft->getSummary();
                    })->toArray();
    }

    /**
     * Get statistics about drafts.
     */
    public static function getStatistics(): array
    {
        $total = static::count();
        $active = static::active()->count();
        $expired = static::expired()->count();
        $byType = static::selectRaw('organization_type, COUNT(*) as count')
                       ->whereNotNull('organization_type')
                       ->groupBy('organization_type')
                       ->pluck('count', 'organization_type')
                       ->toArray();
        
        $avgCompletion = static::avg('completion_percentage');
        
        return [
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
            'by_type' => $byType,
            'completion_avg' => $avgCompletion ? $avgCompletion : 0,
        ];
    }

    // ========================================
    // ÉVÉNEMENTS DU MODÈLE
    // ========================================

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Auto-update completion percentage when saving
        static::saving(function (OrganizationDraft $draft) {
            $draft->updateCompletionPercentage();
        });
        
        // Clean up expired drafts when creating new ones
        static::created(function (OrganizationDraft $draft) {
            // Nettoyer les anciens brouillons de façon asynchrone
            dispatch(function () {
                OrganizationDraft::cleanupOldDrafts();
            })->afterResponse();
        });
    }
}