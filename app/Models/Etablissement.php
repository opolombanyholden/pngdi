<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etablissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'nom',
        'type',
        'adresse',
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
        'telephone',
        'email',
        'responsable_nom',
        'responsable_telephone',
        'responsable_email',
        'responsable_fonction',
        'date_ouverture',
        'is_siege_social',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'date_ouverture' => 'date',
        'is_siege_social' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

    // Constantes
    const TYPE_SIEGE = 'siege';
    const TYPE_BRANCHE = 'branche';
    const TYPE_ANTENNE = 'antenne';
    const TYPE_BUREAU = 'bureau';
    const TYPE_REPRESENTATION = 'representation';

    const ZONE_URBAINE = 'urbaine';
    const ZONE_RURALE = 'rurale';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($etablissement) {
            // S'assurer qu'il n'y a qu'un seul siège social
            if ($etablissement->is_siege_social) {
                self::where('organisation_id', $etablissement->organisation_id)
                    ->where('is_siege_social', true)
                    ->update(['is_siege_social' => false]);
            }
        });

        static::updating(function ($etablissement) {
            // S'assurer qu'il n'y a qu'un seul siège social
            if ($etablissement->is_siege_social && $etablissement->isDirty('is_siege_social')) {
                self::where('organisation_id', $etablissement->organisation_id)
                    ->where('id', '!=', $etablissement->id)
                    ->where('is_siege_social', true)
                    ->update(['is_siege_social' => false]);
            }
        });
    }

    /**
     * Relations
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(EtablissementActivity::class);
    }

    /**
     * Scopes
     */
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactifs($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeSiegeSocial($query)
    {
        return $query->where('is_siege_social', true);
    }

    public function scopeBranches($query)
    {
        return $query->where('type', self::TYPE_BRANCHE);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProvince($query, $province)
    {
        return $query->where('province', $province);
    }

    public function scopeByZone($query, $zoneType)
    {
        return $query->where('zone_type', $zoneType);
    }

    /**
     * Accesseurs
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_SIEGE => 'Siège social',
            self::TYPE_BRANCHE => 'Branche',
            self::TYPE_ANTENNE => 'Antenne',
            self::TYPE_BUREAU => 'Bureau',
            self::TYPE_REPRESENTATION => 'Représentation'
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getAdresseCompleteAttribute(): string
    {
        $parts = array_filter([
            $this->adresse,
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

    public function getStatutLabelAttribute(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public function getStatutColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Méthodes utilitaires
     */
    public function isSiegeSocial(): bool
    {
        return $this->is_siege_social;
    }

    public function hasResponsable(): bool
    {
        return !empty($this->responsable_nom);
    }

    public function hasCoordinates(): bool
    {
        return !empty($this->latitude) && !empty($this->longitude);
    }

    /**
     * Définir comme siège social
     */
    public function setAsSiegeSocial(): bool
    {
        // Retirer le statut de siège social des autres établissements
        self::where('organisation_id', $this->organisation_id)
            ->where('id', '!=', $this->id)
            ->update(['is_siege_social' => false]);

        // Définir cet établissement comme siège social
        $this->is_siege_social = true;
        $this->type = self::TYPE_SIEGE;
        return $this->save();
    }

    /**
     * Activer/Désactiver l'établissement
     */
    public function toggleActive(): bool
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Enregistrer une activité
     */
    public function logActivity($type, $description, $userId = null)
    {
        return $this->activities()->create([
            'type' => $type,
            'description' => $description,
            'user_id' => $userId ?? auth()->id(),
            'occurred_at' => now()
        ]);
    }

    /**
     * Obtenir la distance depuis un autre point
     */
    public function getDistanceFrom($latitude, $longitude): ?float
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        // Formule de Haversine pour calculer la distance
        $earthRadius = 6371; // Rayon de la Terre en kilomètres

        $latFrom = deg2rad($latitude);
        $lonFrom = deg2rad($longitude);
        $latTo = deg2rad($this->latitude);
        $lonTo = deg2rad($this->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * Obtenir les établissements proches
     */
    public static function getNearby($latitude, $longitude, $radius = 10)
    {
        // Approximation : 1 degré = 111 km
        $latRange = $radius / 111;
        $lonRange = $radius / (111 * cos(deg2rad($latitude)));

        return self::whereBetween('latitude', [$latitude - $latRange, $latitude + $latRange])
            ->whereBetween('longitude', [$longitude - $lonRange, $longitude + $lonRange])
            ->where('is_active', true)
            ->get()
            ->filter(function ($etablissement) use ($latitude, $longitude, $radius) {
                $distance = $etablissement->getDistanceFrom($latitude, $longitude);
                return $distance !== null && $distance <= $radius;
            });
    }

    /**
     * Obtenir les types d'établissement
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_SIEGE => 'Siège social',
            self::TYPE_BRANCHE => 'Branche',
            self::TYPE_ANTENNE => 'Antenne',
            self::TYPE_BUREAU => 'Bureau',
            self::TYPE_REPRESENTATION => 'Représentation'
        ];
    }

    /**
     * Obtenir les statistiques par province
     */
    public static function getStatsByProvince($organisationId = null)
    {
        $query = self::actifs()
            ->selectRaw('province, COUNT(*) as total')
            ->groupBy('province');

        if ($organisationId) {
            $query->where('organisation_id', $organisationId);
        }

        return $query->pluck('total', 'province');
    }

    /**
     * Obtenir les statistiques par type
     */
    public static function getStatsByType($organisationId = null)
    {
        $query = self::actifs()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type');

        if ($organisationId) {
            $query->where('organisation_id', $organisationId);
        }

        return $query->pluck('total', 'type');
    }

    /**
     * Valider les données
     */
    public function validate(): array
    {
        $errors = [];

        // Une organisation doit avoir au moins un siège social
        if (!$this->is_siege_social) {
            $hasSiege = self::where('organisation_id', $this->organisation_id)
                ->where('is_siege_social', true)
                ->where('id', '!=', $this->id)
                ->exists();

            if (!$hasSiege) {
                $errors['siege_social'] = 'L\'organisation doit avoir au moins un siège social';
            }
        }

        // Vérifier que le nom est unique pour l'organisation
        $exists = self::where('organisation_id', $this->organisation_id)
            ->where('nom', $this->nom)
            ->where('id', '!=', $this->id)
            ->exists();

        if ($exists) {
            $errors['nom'] = 'Un établissement avec ce nom existe déjà pour cette organisation';
        }

        return $errors;
    }
}