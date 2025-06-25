<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Declaration extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'declaration_type_id',
        'numero_declaration',
        'titre',
        'description',
        'date_evenement',
        'date_fin_evenement',
        'lieu',
        'nombre_participants',
        'budget',
        'statut',
        'submitted_by',
        'submitted_at',
        'validated_by',
        'validated_at',
        'motif_rejet',
        'donnees_specifiques'
    ];

    protected $casts = [
        'date_evenement' => 'date',
        'date_fin_evenement' => 'date',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime',
        'donnees_specifiques' => 'array',
        'budget' => 'decimal:2'
    ];

    // Statuts selon la migration existante
    const STATUT_BROUILLON = 'brouillon';
    const STATUT_SOUMISE = 'soumise';
    const STATUT_VALIDEE = 'validee';
    const STATUT_REJETEE = 'rejetee';
    const STATUT_ARCHIVEE = 'archivee';

    public static function getStatuts()
    {
        return [
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_SOUMISE => 'Soumise',
            self::STATUT_VALIDEE => 'Validée',
            self::STATUT_REJETEE => 'Rejetée',
            self::STATUT_ARCHIVEE => 'Archivée'
        ];
    }

    /**
     * Relation avec l'organisation
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Relation avec le type de déclaration
     */
    public function declarationType()
    {
        return $this->belongsTo(DeclarationType::class);
    }

    /**
     * Relation avec l'utilisateur qui a soumis
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relation avec l'administrateur validateur
     */
    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Vérifier si la déclaration peut être modifiée
     */
    public function canBeModified()
    {
        return in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_REJETEE]);
    }

    /**
     * Vérifier si la déclaration peut être soumise
     */
    public function canBeSubmitted()
    {
        return $this->statut === self::STATUT_BROUILLON && $this->isComplete();
    }

    /**
     * Vérifier si la déclaration est complète
     */
    public function isComplete()
    {
        return !empty($this->titre) && 
               !empty($this->description) && 
               !empty($this->date_evenement);
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLabelAttribute()
    {
        return self::getStatuts()[$this->statut] ?? 'Inconnu';
    }

    /**
     * Obtenir la classe CSS pour le statut
     */
    public function getStatutClassAttribute()
    {
        switch ($this->statut) {
            case self::STATUT_BROUILLON:
                return 'en-cours'; // Mapping pour la vue
            case self::STATUT_SOUMISE:
                return 'soumise';
            case self::STATUT_VALIDEE:
                return 'validee';
            case self::STATUT_REJETEE:
                return 'rejetee';
            case self::STATUT_ARCHIVEE:
                return 'archivee';
            default:
                return 'inconnu';
        }
    }

    /**
     * Obtenir l'icône pour le statut
     */
    public function getStatutIconAttribute()
    {
        switch ($this->statut) {
            case self::STATUT_BROUILLON:
                return 'edit';
            case self::STATUT_SOUMISE:
                return 'paper-plane';
            case self::STATUT_VALIDEE:
                return 'check-circle';
            case self::STATUT_REJETEE:
                return 'times-circle';
            case self::STATUT_ARCHIVEE:
                return 'archive';
            default:
                return 'question-circle';
        }
    }

    /**
     * Obtenir la durée de l'événement
     */
    public function getDureeAttribute()
    {
        if (!$this->date_evenement) {
            return null;
        }
        
        if (!$this->date_fin_evenement) {
            return 1; // 1 jour par défaut
        }
        
        return $this->date_evenement->diffInDays($this->date_fin_evenement) + 1;
    }

    /**
     * Scope pour les déclarations d'un utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('organisation', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope pour les déclarations par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    /**
     * Scope pour les déclarations d'une année
     */
    public function scopeForYear($query, $year)
    {
        return $query->where(function ($q) use ($year) {
            $q->whereYear('date_evenement', $year)
              ->orWhereYear('created_at', $year);
        });
    }

    /**
     * Générer automatiquement le numéro de déclaration
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($declaration) {
            if (!$declaration->numero_declaration) {
                $year = date('Y');
                $count = self::whereYear('created_at', $year)->count() + 1;
                $declaration->numero_declaration = "DECL-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
            
            if (!$declaration->statut) {
                $declaration->statut = self::STATUT_BROUILLON;
            }
        });
    }

    /**
     * Formater le budget
     */
    public function getBudgetFormateAttribute()
    {
        if (!$this->budget) {
            return 'Non défini';
        }
        
        return number_format($this->budget, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Obtenir l'année de la déclaration
     */
    public function getAnneeAttribute()
    {
        return $this->date_evenement ? $this->date_evenement->year : $this->created_at->year;
    }
}