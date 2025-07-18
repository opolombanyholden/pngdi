<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AdherentAnomalie extends Model
{
    use HasFactory;

    /**
     * Table associée au modèle
     */
    protected $table = 'adherent_anomalies';

    /**
     * Les attributs qui sont mass assignable
     */
    protected $fillable = [
        'adherent_id',
        'organisation_id', 
        'ligne_import',
        'type_anomalie',
        'champ_concerne',
        'message_anomalie',
        'valeur_erronee',
        'valeur_corrigee',
        'statut',
        'priorite',
        'detectee_le',
        'corrige_par',
        'date_correction',
        'commentaire_correction',
        'description',
        'valeur_incorrecte',
        'impact_metier'
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'detectee_le' => 'datetime',
        'date_correction' => 'datetime',
        'valeur_erronee' => 'json',
        'priorite' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Types d'anomalies autorisés
     */
    const TYPE_CRITIQUE = 'critique';
    const TYPE_MAJEURE = 'majeure';
    const TYPE_MINEURE = 'mineure';

    /**
     * Statuts autorisés
     */
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_RESOLU = 'resolu';
    const STATUT_IGNORE = 'ignore';

    /**
     * Relation avec l'adhérent
     */
    public function adherent(): BelongsTo
    {
        return $this->belongsTo(Adherent::class);
    }

    /**
     * Relation avec l'organisation
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Relation avec l'utilisateur qui a corrigé
     */
    public function correcteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrige_par');
    }

    /**
     * Scopes pour les requêtes
     */
    public function scopeByOrganisation($query, $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type_anomalie', $type);
    }

    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeCritiques($query)
    {
        return $query->where('type_anomalie', self::TYPE_CRITIQUE);
    }

    public function scopeMajeures($query)
    {
        return $query->where('type_anomalie', self::TYPE_MAJEURE);
    }

    public function scopeMineures($query)
    {
        return $query->where('type_anomalie', self::TYPE_MINEURE);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    public function scopeResolues($query)
    {
        return $query->where('statut', self::STATUT_RESOLU);
    }

    public function scopeIgnorees($query)
    {
        return $query->where('statut', self::STATUT_IGNORE);
    }

    public function scopeRecentes($query, $jours = 30)
    {
        return $query->where('detectee_le', '>=', now()->subDays($jours));
    }

    /**
     * Accesseurs
     */
    public function getIsResoluAttribute()
    {
        return $this->statut === self::STATUT_RESOLU;
    }

    public function getIsIgnoreAttribute()
    {
        return $this->statut === self::STATUT_IGNORE;
    }

    public function getIsEnAttenteAttribute()
    {
        return $this->statut === self::STATUT_EN_ATTENTE;
    }

    public function getIsCritiqueAttribute()
    {
        return $this->type_anomalie === self::TYPE_CRITIQUE;
    }

    public function getTempsResolutionAttribute()
    {
        if ($this->date_correction && $this->detectee_le) {
            return $this->detectee_le->diffInHours($this->date_correction);
        }
        return null;
    }

    public function getTempsAtttenteAttribute()
    {
        if ($this->statut === self::STATUT_EN_ATTENTE && $this->detectee_le) {
            return $this->detectee_le->diffInHours(now());
        }
        return null;
    }

    /**
     * Méthodes métier
     */
    public function resoudre($valeurCorrigee = null, $commentaire = null, $userId = null)
    {
        $this->update([
            'statut' => self::STATUT_RESOLU,
            'valeur_corrigee' => $valeurCorrigee,
            'commentaire_correction' => $commentaire,
            'corrige_par' => $userId ?: auth()->id(),
            'date_correction' => now()
        ]);

        return $this;
    }

    public function ignorer($commentaire = null, $userId = null)
    {
        $this->update([
            'statut' => self::STATUT_IGNORE,
            'commentaire_correction' => $commentaire,
            'corrige_par' => $userId ?: auth()->id(),
            'date_correction' => now()
        ]);

        return $this;
    }

    public function reouvrir($commentaire = null)
    {
        $this->update([
            'statut' => self::STATUT_EN_ATTENTE,
            'valeur_corrigee' => null,
            'commentaire_correction' => $commentaire,
            'corrige_par' => null,
            'date_correction' => null
        ]);

        return $this;
    }

    /**
     * Méthodes statiques utilitaires
     */
    public static function getTypes()
    {
        return [
            self::TYPE_CRITIQUE => 'Critique',
            self::TYPE_MAJEURE => 'Majeure', 
            self::TYPE_MINEURE => 'Mineure'
        ];
    }

    public static function getStatuts()
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_RESOLU => 'Résolu',
            self::STATUT_IGNORE => 'Ignoré'
        ];
    }

    public static function getChampsConcernes()
    {
        return [
            'nip' => 'NIP',
            'nom' => 'Nom',
            'prenom' => 'Prénom',
            'date_naissance' => 'Date de naissance',
            'lieu_naissance' => 'Lieu de naissance',
            'profession' => 'Profession',
            'telephone' => 'Téléphone',
            'email' => 'Email',
            'adresse' => 'Adresse',
            'appartenance_multiple' => 'Appartenance multiple'
        ];
    }

    /**
     * Statistiques par organisation
     */
    public static function getStatistiquesParOrganisation($organisationId)
    {
        return self::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN type_anomalie = "critique" THEN 1 ELSE 0 END) as critiques,
            SUM(CASE WHEN type_anomalie = "majeure" THEN 1 ELSE 0 END) as majeures,
            SUM(CASE WHEN type_anomalie = "mineure" THEN 1 ELSE 0 END) as mineures,
            SUM(CASE WHEN statut = "en_attente" THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN statut = "resolu" THEN 1 ELSE 0 END) as resolues,
            SUM(CASE WHEN statut = "ignore" THEN 1 ELSE 0 END) as ignorees,
            AVG(CASE WHEN statut = "resolu" AND date_correction IS NOT NULL 
                THEN TIMESTAMPDIFF(HOUR, detectee_le, date_correction) END) as temps_resolution_moyen
        ')
        ->where('organisation_id', $organisationId)
        ->first();
    }

    /**
     * Statistiques générales
     */
    public static function getStatistiquesGenerales()
    {
        return self::selectRaw('
            COUNT(*) as total,
            COUNT(DISTINCT organisation_id) as organisations_concernees,
            SUM(CASE WHEN type_anomalie = "critique" THEN 1 ELSE 0 END) as critiques,
            SUM(CASE WHEN type_anomalie = "majeure" THEN 1 ELSE 0 END) as majeures,
            SUM(CASE WHEN type_anomalie = "mineure" THEN 1 ELSE 0 END) as mineures,
            SUM(CASE WHEN statut = "en_attente" THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN statut = "resolu" THEN 1 ELSE 0 END) as resolues,
            SUM(CASE WHEN statut = "ignore" THEN 1 ELSE 0 END) as ignorees,
            AVG(CASE WHEN statut = "resolu" AND date_correction IS NOT NULL 
                THEN TIMESTAMPDIFF(HOUR, detectee_le, date_correction) END) as temps_resolution_moyen
        ')
        ->first();
    }

    /**
     * Boot method pour les événements
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($anomalie) {
            if (!$anomalie->detectee_le) {
                $anomalie->detectee_le = now();
            }
            if (!$anomalie->statut) {
                $anomalie->statut = self::STATUT_EN_ATTENTE;
            }
            if (!$anomalie->priorite) {
                $anomalie->priorite = $anomalie->type_anomalie === self::TYPE_CRITIQUE ? 1 : 
                                     ($anomalie->type_anomalie === self::TYPE_MAJEURE ? 2 : 3);
            }
        });

        static::created(function ($anomalie) {
            \Log::info('Anomalie créée', [
                'anomalie_id' => $anomalie->id,
                'adherent_id' => $anomalie->adherent_id,
                'type' => $anomalie->type_anomalie,
                'champ' => $anomalie->champ_concerne
            ]);
        });
    }
}