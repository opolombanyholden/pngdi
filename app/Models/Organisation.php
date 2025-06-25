<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'nom',
        'sigle',
        'objet',
        'siege_social',
        'province',
        'departement',
        'canton',
        'prefecture',
        'sous_prefecture',
        'regroupement',
        'zone_type',
        'ville_commune',
        'arrondissement',
        'quartier',
        'village',
        'lieu_dit',
        'latitude',
        'longitude',
        'email',
        'telephone',
        'telephone_secondaire',
        'site_web',
        'numero_recepisse',
        'date_creation',
        'statut',
        'is_active',
        'nombre_adherents_min',
        'organes_gestion'
    ];

    protected $casts = [
        'date_creation' => 'date',
        'is_active' => 'boolean',
        'organes_gestion' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    // Constantes pour les types d'organisation
    const TYPE_ASSOCIATION = 'association';
    const TYPE_ONG = 'ong';
    const TYPE_PARTI = 'parti_politique';
    const TYPE_CONFESSION = 'confession_religieuse';

    // Constantes pour les statuts
    const STATUT_BROUILLON = 'brouillon';
    const STATUT_SOUMIS = 'soumis';
    const STATUT_EN_VALIDATION = 'en_validation';
    const STATUT_APPROUVE = 'approuve';
    const STATUT_REJETE = 'rejete';
    const STATUT_SUSPENDU = 'suspendu';
    const STATUT_RADIE = 'radie';

    // Constantes pour les zones
    const ZONE_URBAINE = 'urbaine';
    const ZONE_RURALE = 'rurale';

    /**
     * Relation avec l'utilisateur créateur (opérateur)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les dossiers
     */
    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * Relation avec le dossier actif
     */
    public function dossierActif(): HasOne
    {
        return $this->hasOne(Dossier::class)
            ->where('is_active', true)
            ->latest();
    }

    /**
     * Relation avec les fondateurs
     */
    public function fondateurs(): HasMany
    {
        return $this->hasMany(Fondateur::class);
    }

    /**
     * Relation avec les adhérents
     */
    public function adherents(): HasMany
    {
        return $this->hasMany(Adherent::class);
    }

    /**
     * Relation avec les adhérents actifs
     */
    public function adherentsActifs(): HasMany
    {
        return $this->hasMany(Adherent::class)
            ->where('is_active', true);
    }

    /**
     * Relation avec les établissements
     */
    public function etablissements(): HasMany
    {
        return $this->hasMany(Etablissement::class);
    }

    /**
     * Relation avec les membres des organes
     */
    public function organeMembres(): HasMany
    {
        return $this->hasMany(OrganeMember::class);
    }

    /**
     * Relation avec les déclarations
     */
    public function declarations(): HasMany
    {
        return $this->hasMany(Declaration::class);
    }

    /**
     * Scopes
     */
    public function scopeActives($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeApprouvees($query)
    {
        return $query->where('statut', self::STATUT_APPROUVE);
    }

    /**
     * Méthodes utilitaires
     */
    public function isPartiPolitique(): bool
    {
        return $this->type === self::TYPE_PARTI;
    }

    public function isConfessionReligieuse(): bool
    {
        return $this->type === self::TYPE_CONFESSION;
    }

    public function isAssociation(): bool
    {
        return $this->type === self::TYPE_ASSOCIATION;
    }

    public function isOng(): bool
    {
        return $this->type === self::TYPE_ONG;
    }

    public function isApprouvee(): bool
    {
        return $this->statut === self::STATUT_APPROUVE;
    }

    public function canAddAdherent(): bool
    {
        // Pour les partis politiques et confessions religieuses, 
        // vérifier s'il n'y a pas déjà une organisation active
        if (in_array($this->type, [self::TYPE_PARTI, self::TYPE_CONFESSION])) {
            return $this->is_active && $this->isApprouvee();
        }
        
        return $this->isApprouvee();
    }

    public function hasMinimumAdherents(): bool
    {
        return $this->adherentsActifs()->count() >= $this->nombre_adherents_min;
    }

    /**
     * Obtenir l'adresse complète formatée
     */
    public function getAdresseCompleteAttribute(): string
    {
        $parts = array_filter([
            $this->siege_social,
            $this->quartier,
            $this->arrondissement,
            $this->ville_commune,
            $this->village,
            $this->sous_prefecture,
            $this->prefecture,
            $this->departement,
            $this->province
        ]);

        return implode(', ', $parts);
    }

    /**
     * Obtenir le label du type
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_ASSOCIATION => 'Association',
            self::TYPE_ONG => 'ONG',
            self::TYPE_PARTI => 'Parti politique',
            self::TYPE_CONFESSION => 'Confession religieuse'
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Obtenir le label du statut
     */
    public function getStatutLabelAttribute(): string
    {
        $labels = [
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_SOUMIS => 'Soumis',
            self::STATUT_EN_VALIDATION => 'En validation',
            self::STATUT_APPROUVE => 'Approuvé',
            self::STATUT_REJETE => 'Rejeté',
            self::STATUT_SUSPENDU => 'Suspendu',
            self::STATUT_RADIE => 'Radié'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir la couleur du statut pour l'affichage
     */
    public function getStatutColorAttribute(): string
    {
        $colors = [
            self::STATUT_BROUILLON => 'secondary',
            self::STATUT_SOUMIS => 'info',
            self::STATUT_EN_VALIDATION => 'warning',
            self::STATUT_APPROUVE => 'success',
            self::STATUT_REJETE => 'danger',
            self::STATUT_SUSPENDU => 'dark',
            self::STATUT_RADIE => 'danger'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }
}