<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fondateur extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'nip',
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        'profession',
        'fonction',
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
        'piece_identite_type',
        'piece_identite_numero',
        'piece_identite_delivree_le',
        'piece_identite_delivree_par',
        'photo_path',
        'piece_identite_path',
        'ordre',
        'is_representant',
        'metadata'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'piece_identite_delivree_le' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'ordre' => 'integer',
        'is_representant' => 'boolean',
        'metadata' => 'array'
    ];

    // Constantes
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';

    const ZONE_URBAINE = 'urbaine';
    const ZONE_RURALE = 'rurale';

    const PIECE_CNI = 'cni';
    const PIECE_PASSEPORT = 'passeport';
    const PIECE_CARTE_SEJOUR = 'carte_sejour';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fondateur) {
            // Définir l'ordre si non fourni
            if (empty($fondateur->ordre)) {
                $maxOrdre = self::where('organisation_id', $fondateur->organisation_id)->max('ordre');
                $fondateur->ordre = ($maxOrdre ?? 0) + 1;
            }
        });

        static::created(function ($fondateur) {
            // Créer automatiquement un adhérent correspondant
            self::createAdherentFromFondateur($fondateur);
        });

        static::updated(function ($fondateur) {
            // Mettre à jour l'adhérent correspondant
            self::updateAdherentFromFondateur($fondateur);
        });
    }

    /**
     * Créer un adhérent à partir d'un fondateur
     */
    protected static function createAdherentFromFondateur($fondateur): void
    {
        // Vérifier si l'adhérent n'existe pas déjà
        $existingAdherent = Adherent::where('organisation_id', $fondateur->organisation_id)
            ->where('nip', $fondateur->nip)
            ->first();

        if (!$existingAdherent) {
            Adherent::create([
                'organisation_id' => $fondateur->organisation_id,
                'nip' => $fondateur->nip,
                'nom' => $fondateur->nom,
                'prenom' => $fondateur->prenom,
                'date_naissance' => $fondateur->date_naissance,
                'lieu_naissance' => $fondateur->lieu_naissance,
                'sexe' => $fondateur->sexe,
                'nationalite' => $fondateur->nationalite,
                'profession' => $fondateur->profession,
                'adresse' => $fondateur->adresse,
                'province' => $fondateur->province,
                'departement' => $fondateur->departement,
                'canton' => $fondateur->canton,
                'prefecture' => $fondateur->prefecture,
                'sous_prefecture' => $fondateur->sous_prefecture,
                'telephone' => $fondateur->telephone,
                'email' => $fondateur->email,
                'photo_path' => $fondateur->photo_path,
                'piece_identite_path' => $fondateur->piece_identite_path,
                'date_adhesion' => now(),
                'is_fondateur' => true,
                'is_active' => true
            ]);
        }
    }

    /**
     * Mettre à jour l'adhérent correspondant
     */
    protected static function updateAdherentFromFondateur($fondateur): void
    {
        $adherent = Adherent::where('organisation_id', $fondateur->organisation_id)
            ->where('nip', $fondateur->nip)
            ->first();

        if ($adherent) {
            $adherent->update([
                'nom' => $fondateur->nom,
                'prenom' => $fondateur->prenom,
                'date_naissance' => $fondateur->date_naissance,
                'lieu_naissance' => $fondateur->lieu_naissance,
                'sexe' => $fondateur->sexe,
                'nationalite' => $fondateur->nationalite,
                'profession' => $fondateur->profession,
                'adresse' => $fondateur->adresse,
                'province' => $fondateur->province,
                'departement' => $fondateur->departement,
                'canton' => $fondateur->canton,
                'prefecture' => $fondateur->prefecture,
                'sous_prefecture' => $fondateur->sous_prefecture,
                'telephone' => $fondateur->telephone,
                'email' => $fondateur->email,
                'photo_path' => $fondateur->photo_path,
                'piece_identite_path' => $fondateur->piece_identite_path
            ]);
        }
    }

    /**
     * Relations
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function adherent()
    {
        return Adherent::where('organisation_id', $this->organisation_id)
            ->where('nip', $this->nip)
            ->first();
    }

    /**
     * Scopes
     */
    public function scopeRepresentants($query)
    {
        return $query->where('is_representant', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    public function scopeByNip($query, $nip)
    {
        return $query->where('nip', $nip);
    }

    /**
     * Accesseurs
     */
    public function getNomCompletAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    public function getSexeLabelAttribute(): string
    {
        return $this->sexe === self::SEXE_MASCULIN ? 'Masculin' : 'Féminin';
    }

    public function getPieceIdentiteTypeLabelAttribute(): string
    {
        $labels = [
            self::PIECE_CNI => 'Carte Nationale d\'Identité',
            self::PIECE_PASSEPORT => 'Passeport',
            self::PIECE_CARTE_SEJOUR => 'Carte de Séjour'
        ];

        return $labels[$this->piece_identite_type] ?? $this->piece_identite_type;
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

    public function getAge(): int
    {
        return $this->date_naissance ? $this->date_naissance->age : 0;
    }

    /**
     * Méthodes utilitaires
     */
    public function isRepresentant(): bool
    {
        return $this->is_representant;
    }

    public function canBeRepresentant(): bool
    {
        // Vérifier l'âge minimum (18 ans)
        return $this->getAge() >= 18;
    }

    public function hasCompleteInformation(): bool
    {
        $requiredFields = [
            'nip', 'nom', 'prenom', 'date_naissance', 'lieu_naissance',
            'sexe', 'nationalite', 'profession', 'adresse', 'telephone',
            'piece_identite_type', 'piece_identite_numero'
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    /**
     * Définir comme représentant
     */
    public function setAsRepresentant(): bool
    {
        // Retirer le statut de représentant des autres fondateurs
        self::where('organisation_id', $this->organisation_id)
            ->where('id', '!=', $this->id)
            ->update(['is_representant' => false]);

        // Définir ce fondateur comme représentant
        $this->is_representant = true;
        return $this->save();
    }

    /**
     * Vérifier l'unicité du NIP dans l'organisation
     */
    public static function isNipUniqueInOrganisation($nip, $organisationId, $excludeId = null): bool
    {
        $query = self::where('nip', $nip)
            ->where('organisation_id', $organisationId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Obtenir les types de pièces d'identité
     */
    public static function getPieceIdentiteTypes(): array
    {
        return [
            self::PIECE_CNI => 'Carte Nationale d\'Identité',
            self::PIECE_PASSEPORT => 'Passeport',
            self::PIECE_CARTE_SEJOUR => 'Carte de Séjour'
        ];
    }

    /**
     * Validation des données
     */
    public function validate(): array
    {
        $errors = [];

        // Vérifier l'âge minimum
        if ($this->getAge() < 18) {
            $errors['age'] = 'Le fondateur doit avoir au moins 18 ans';
        }

        // Vérifier l'unicité du NIP
        if (!self::isNipUniqueInOrganisation($this->nip, $this->organisation_id, $this->id)) {
            $errors['nip'] = 'Ce NIP est déjà utilisé par un autre fondateur de cette organisation';
        }

        // Vérifier les informations complètes
        if (!$this->hasCompleteInformation()) {
            $errors['information'] = 'Toutes les informations obligatoires doivent être renseignées';
        }

        // Vérifier qu'il y a au moins un représentant
        if ($this->wasRecentlyCreated && !$this->is_representant) {
            $hasRepresentant = self::where('organisation_id', $this->organisation_id)
                ->where('is_representant', true)
                ->exists();

            if (!$hasRepresentant) {
                $errors['representant'] = 'Au moins un fondateur doit être désigné comme représentant';
            }
        }

        return $errors;
    }

    /**
     * Obtenir le nombre minimum de fondateurs requis
     */
    public static function getMinimumRequired($typeOrganisation): int
    {
        // Ces valeurs peuvent être paramétrées dans la base de données
        $minimums = [
            Organisation::TYPE_ASSOCIATION => 7,
            Organisation::TYPE_ONG => 7,
            Organisation::TYPE_PARTI => 10,
            Organisation::TYPE_CONFESSION => 5
        ];

        return $minimums[$typeOrganisation] ?? 3;
    }

    /**
     * Vérifier si l'organisation a le nombre minimum de fondateurs
     */
    public static function hasMinimumFondateurs($organisationId): bool
    {
        $organisation = Organisation::find($organisationId);
        if (!$organisation) {
            return false;
        }

        $count = self::where('organisation_id', $organisationId)->count();
        $minimum = self::getMinimumRequired($organisation->type);

        return $count >= $minimum;
    }
}