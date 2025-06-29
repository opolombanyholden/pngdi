<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdherentHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'adherent_id',
        'organisation_id',
        'type_mouvement',
        'ancienne_organisation_id',
        'nouvelle_organisation_id',
        'motif',
        'document_justificatif',
        'date_effet',
        'created_by',
        'validated_by',
        'statut',
        'commentaire_validation'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_effet' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec le modèle Adherent
     */
    public function adherent()
    {
        return $this->belongsTo(Adherent::class);
    }

    /**
     * Relation avec le modèle Organisation
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Relation avec l'utilisateur qui a créé l'historique
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a validé
     */
    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relation avec l'ancienne organisation (pour les transferts)
     */
    public function ancienneOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'ancienne_organisation_id');
    }

    /**
     * Relation avec la nouvelle organisation (pour les transferts)
     */
    public function nouvelleOrganisation()
    {
        return $this->belongsTo(Organisation::class, 'nouvelle_organisation_id');
    }
}