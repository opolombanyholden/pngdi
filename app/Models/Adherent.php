<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Adherent extends Model
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
        'adresse',
        'province',
        'departement',
        'canton',
        'prefecture',
        'sous_prefecture',
        'telephone',
        'email',
        'photo_path',
        'piece_identite_path',
        'date_adhesion',
        'numero_carte',
        'is_fondateur',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_adhesion' => 'date',
        'is_fondateur' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

    // Constantes
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';

    /**
     * Boot method pour gérer les événements
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adherent) {
            // Générer le numéro de carte si non fourni
            if (empty($adherent->numero_carte)) {
                $adherent->numero_carte = self::generateNumeroCarte($adherent->organisation_id);
            }

            // Vérifier l'unicité pour les partis politiques
            if ($adherent->organisation->isPartiPolitique()) {
                self::verifyUnicityForPartiPolitique($adherent);
            }
        });

        static::created(function ($adherent) {
            // Créer l'historique d'adhésion
            AdherentHistory::create([
                'adherent_id' => $adherent->id,
                'organisation_id' => $adherent->organisation_id,
                'type_mouvement' => 'adhesion',
                'date_mouvement' => $adherent->date_adhesion ?? now(),
                'motif' => 'Adhésion initiale'
            ]);
        });
    }

    /**
     * Générer un numéro de carte unique
     */
    public static function generateNumeroCarte($organisationId): string
    {
        $organisation = Organisation::find($organisationId);
        $prefix = strtoupper(substr($organisation->sigle ?? $organisation->nom, 0, 3));
        $year = date('Y');
        
        $lastAdherent = self::where('organisation_id', $organisationId)
            ->where('numero_carte', 'like', $prefix . '-' . $year . '-%')
            ->orderBy('numero_carte', 'desc')
            ->first();

        if ($lastAdherent) {
            $lastNumber = intval(substr($lastAdherent->numero_carte, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $newNumber);
    }

    /**
     * Vérifier l'unicité pour les partis politiques
     */
    protected static function verifyUnicityForPartiPolitique($adherent)
    {
        $existingAdhesion = self::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
            ->where('adherents.nip', $adherent->nip)
            ->where('adherents.is_active', true)
            ->where('organisations.type', Organisation::TYPE_PARTI)
            ->where('adherents.organisation_id', '!=', $adherent->organisation_id)
            ->first();

        if ($existingAdhesion) {
            throw new \Exception(
                "Cette personne (NIP: {$adherent->nip}) est déjà membre du parti politique '{$existingAdhesion->organisation->nom}'. " .
                "Une exclusion formelle est requise avant de pouvoir adhérer à un autre parti."
            );
        }
    }

    /**
     * Relations
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AdherentHistory::class);
    }

    public function exclusion(): HasOne
    {
        return $this->hasOne(AdherentExclusion::class)->latest();
    }

    public function imports(): HasMany
    {
        return $this->hasMany(AdherentImport::class);
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

    public function scopeFondateurs($query)
    {
        return $query->where('is_fondateur', true);
    }

    public function scopeByNip($query, $nip)
    {
        return $query->where('nip', $nip);
    }

    public function scopeHommes($query)
    {
        return $query->where('sexe', self::SEXE_MASCULIN);
    }

    public function scopeFemmes($query)
    {
        return $query->where('sexe', self::SEXE_FEMININ);
    }

    /**
     * Méthodes utilitaires
     */
    public function isExcluded(): bool
    {
        return $this->exclusion()->exists();
    }

    public function canBeTransferred(): bool
    {
        // Un adhérent peut être transféré s'il n'est pas fondateur et est actif
        return !$this->is_fondateur && $this->is_active;
    }

    public function getAge(): int
    {
        return $this->date_naissance ? $this->date_naissance->age : 0;
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    public function getSexeLabelAttribute(): string
    {
        return $this->sexe === self::SEXE_MASCULIN ? 'Masculin' : 'Féminin';
    }

    /**
     * Exclure l'adhérent
     */
    public function exclude($motif, $dateExclusion = null, $documentPath = null)
    {
        // Marquer l'adhérent comme inactif
        $this->update(['is_active' => false]);

        // Créer l'enregistrement d'exclusion
        AdherentExclusion::create([
            'adherent_id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'date_exclusion' => $dateExclusion ?? now(),
            'motif' => $motif,
            'document_path' => $documentPath
        ]);

        // Créer l'historique
        AdherentHistory::create([
            'adherent_id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'type_mouvement' => 'exclusion',
            'date_mouvement' => $dateExclusion ?? now(),
            'motif' => $motif
        ]);

        return true;
    }

    /**
     * Réactiver l'adhérent
     */
    public function reactivate($motif = 'Réactivation')
    {
        $this->update(['is_active' => true]);

        AdherentHistory::create([
            'adherent_id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'type_mouvement' => 'reactivation',
            'date_mouvement' => now(),
            'motif' => $motif
        ]);

        return true;
    }

    /**
     * Transférer vers une autre organisation
     */
    public function transferTo($newOrganisationId, $motif = 'Transfert', $documentPath = null)
    {
        $oldOrganisationId = $this->organisation_id;

        // Si c'est un parti politique, vérifier l'unicité
        $newOrganisation = Organisation::find($newOrganisationId);
        if ($newOrganisation->isPartiPolitique()) {
            // Vérifier qu'il n'est pas déjà dans un autre parti
            $existingInParti = self::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
                ->where('adherents.nip', $this->nip)
                ->where('adherents.is_active', true)
                ->where('organisations.type', Organisation::TYPE_PARTI)
                ->where('adherents.id', '!=', $this->id)
                ->exists();

            if ($existingInParti) {
                throw new \Exception("Cette personne est déjà membre d'un parti politique actif.");
            }
        }

        // Effectuer le transfert
        $this->update([
            'organisation_id' => $newOrganisationId,
            'date_adhesion' => now(),
            'numero_carte' => self::generateNumeroCarte($newOrganisationId)
        ]);

        // Créer l'historique de sortie
        AdherentHistory::create([
            'adherent_id' => $this->id,
            'organisation_id' => $oldOrganisationId,
            'type_mouvement' => 'sortie',
            'date_mouvement' => now(),
            'motif' => $motif,
            'organisation_destination_id' => $newOrganisationId,
            'document_path' => $documentPath
        ]);

        // Créer l'historique d'entrée
        AdherentHistory::create([
            'adherent_id' => $this->id,
            'organisation_id' => $newOrganisationId,
            'type_mouvement' => 'entree',
            'date_mouvement' => now(),
            'motif' => $motif,
            'organisation_source_id' => $oldOrganisationId
        ]);

        return true;
    }

    /**
     * Vérifier si l'adhérent peut adhérer à une organisation
     */
    public static function canJoinOrganisation($nip, $organisationId): array
    {
        $organisation = Organisation::find($organisationId);
        
        if (!$organisation) {
            return ['can_join' => false, 'reason' => 'Organisation non trouvée'];
        }

        // Pour les partis politiques
        if ($organisation->isPartiPolitique()) {
            $existingInParti = self::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
                ->where('adherents.nip', $nip)
                ->where('adherents.is_active', true)
                ->where('organisations.type', Organisation::TYPE_PARTI)
                ->first();

            if ($existingInParti) {
                return [
                    'can_join' => false,
                    'reason' => "Déjà membre du parti politique '{$existingInParti->organisation->nom}'",
                    'existing_organisation' => $existingInParti->organisation
                ];
            }
        }

        // Pour les confessions religieuses (si restriction similaire)
        if ($organisation->isConfessionReligieuse()) {
            $existingInConfession = self::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
                ->where('adherents.nip', $nip)
                ->where('adherents.is_active', true)
                ->where('organisations.type', Organisation::TYPE_CONFESSION)
                ->first();

            if ($existingInConfession) {
                return [
                    'can_join' => false,
                    'reason' => "Déjà membre de la confession religieuse '{$existingInConfession->organisation->nom}'",
                    'existing_organisation' => $existingInConfession->organisation
                ];
            }
        }

        return ['can_join' => true];
    }

    /**
     * Obtenir toutes les organisations de l'adhérent
     */
    public function getAllOrganisations()
    {
        return Organisation::whereHas('adherents', function ($query) {
            $query->where('nip', $this->nip);
        })->get();
    }

    /**
     * Obtenir les organisations actives
     */
    public function getActiveOrganisations()
    {
        return Organisation::whereHas('adherents', function ($query) {
            $query->where('nip', $this->nip)
                  ->where('is_active', true);
        })->get();
    }
}