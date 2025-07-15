<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Adherent;

class Dossier extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'type_operation',
        'numero_dossier',
        'statut',
        'date_soumission',        
        'submitted_at',           
        'date_traitement',
        'validated_at',           
        'motif_rejet',
        'current_step_id',
        'is_active',
        'metadata',
        'donnees_supplementaires'
    ];

    protected $casts = [
        'date_soumission' => 'datetime',
        'date_traitement' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

    // Constantes pour les types d'opération
    const TYPE_CREATION = 'creation';
    const TYPE_MODIFICATION = 'modification';
    const TYPE_CESSATION = 'cessation';
    const TYPE_DECLARATION = 'declaration';
    const TYPE_FUSION = 'fusion';
    const TYPE_ABSORPTION = 'absorption';

    // Constantes pour les statuts
    const STATUT_BROUILLON = 'brouillon';
    const STATUT_SOUMIS = 'soumis';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_ACCEPTE = 'accepte';
    const STATUT_REJETE = 'rejete';
    const STATUT_ARCHIVE = 'archive';

    /**
     * Boot method pour générer automatiquement le numéro de dossier
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dossier) {
            if (empty($dossier->numero_dossier)) {
                $dossier->numero_dossier = self::generateNumeroDossier($dossier->type_operation);
            }
        });
    }

    /**
     * Générer un numéro de dossier unique
     */
    public static function generateNumeroDossier($typeOperation): string
    {
        // Version compatible PHP 7.4
        switch($typeOperation) {
            case self::TYPE_CREATION:
                $prefix = 'CRE';
                break;
            case self::TYPE_MODIFICATION:
                $prefix = 'MOD';
                break;
            case self::TYPE_CESSATION:
                $prefix = 'CES';
                break;
            case self::TYPE_DECLARATION:
                $prefix = 'DEC';
                break;
            case self::TYPE_FUSION:
                $prefix = 'FUS';
                break;
            case self::TYPE_ABSORPTION:
                $prefix = 'ABS';
                break;
            default:
                $prefix = 'DOS';
                break;
        }

        $year = date('Y');
        $lastDossier = self::where('numero_dossier', 'like', $prefix . '-' . $year . '-%')
            ->orderBy('numero_dossier', 'desc')
            ->first();

        if ($lastDossier) {
            $lastNumber = intval(substr($lastDossier->numero_dossier, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $newNumber);
    }

    /**
     * Relations
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(DossierValidation::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(DossierOperation::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DossierComment::class);
    }

    public function lock(): HasOne
    {
        return $this->hasOne(DossierLock::class);
    }

        /**
     * Agent assigné au dossier
     */
    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    /**
     * ✅ CORRECTION PRINCIPALE - Relation avec les adhérents via organisation
     * 
     * LOGIQUE : Un dossier appartient à une organisation, 
     * et les adhérents appartiennent également à cette organisation.
     * Donc on accède aux adhérents VIA l'organisation.
     */
    public function adherents()
    {
        return $this->hasManyThrough(
            Adherent::class,           // Modèle final (Adherent)
            Organisation::class,       // Modèle intermédiaire (Organisation)
            'id',                      // Clé étrangère sur table organisations (pour relation dossiers->organisations)
            'organisation_id',         // Clé étrangère sur table adherents (pour relation organisations->adherents)  
            'organisation_id',         // Clé locale sur table dossiers
            'id'                       // Clé locale sur table organisations
        );
    }

    /**
     * ✅ MÉTHODE ALTERNATIVE - Accès direct aux adhérents de l'organisation
     * 
     * Plus simple et plus claire que hasManyThrough
     */
    public function getAdherentsAttribute()
    {
        return $this->organisation ? $this->organisation->adherents : collect();
    }

    /**
     * ✅ MÉTHODE UTILITAIRE - Obtenir les adhérents avec filtres
     */
    public function getAdherentsWithFilters($actifs = null, $withAnomalies = null)
    {
        if (!$this->organisation) {
            return collect();
        }

        $query = $this->organisation->adherents();

        if ($actifs !== null) {
            $query->where('is_active', $actifs);
        }

        if ($withAnomalies !== null) {
            $query->where('has_anomalies', $withAnomalies);
        }

        return $query->get();
    }

    /**
     * ✅ MÉTHODE STATISTIQUES - Compteurs adhérents
     */
    public function getAdherentsStats(): array
    {
        if (!$this->organisation) {
            return [
                'total' => 0,
                'actifs' => 0,
                'inactifs' => 0,
                'fondateurs' => 0,
                'avec_anomalies' => 0,
                'sans_anomalies' => 0
            ];
        }

        $adherents = $this->organisation->adherents();
        
        return [
            'total' => $adherents->count(),
            'actifs' => $adherents->where('is_active', true)->count(),
            'inactifs' => $adherents->where('is_active', false)->count(),
            'fondateurs' => $adherents->where('is_fondateur', true)->count(),
            'avec_anomalies' => $adherents->where('has_anomalies', true)->count(),
            'sans_anomalies' => $adherents->where('has_anomalies', false)->count()
        ];
    }

    /**
     * Scopes
     */
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeSoumis($query)
    {
        return $query->where('statut', self::STATUT_SOUMIS);
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    public function scopeNonTraites($query)
    {
        return $query->whereIn('statut', [self::STATUT_SOUMIS, self::STATUT_EN_COURS]);
    }

    /**
     * Vérifier si le dossier est verrouillé
     */
    public function isLocked(): bool
    {
        return $this->lock()->where('is_active', true)->exists();
    }

    /**
     * Vérifier si le dossier est verrouillé par un utilisateur spécifique
     */
    public function isLockedBy($userId): bool
    {
        return $this->lock()
            ->where('is_active', true)
            ->where('locked_by', $userId)
            ->exists();
    }

    /**
     * Obtenir l'utilisateur qui a verrouillé le dossier
     */
    public function getLockedByUser()
    {
        $lock = $this->lock()->where('is_active', true)->first();
        return $lock ? $lock->user : null;
    }

    /**
     * Vérifier si le dossier peut être modifié
     */
    public function canBeModified(): bool
    {
        return in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_REJETE]);
    }

    /**
     * Vérifier si le dossier peut être soumis
     */
    public function canBeSubmitted(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Vérifier si tous les documents obligatoires sont fournis
     */
    public function hasAllRequiredDocuments(): bool
    {
        $requiredTypes = DocumentType::where('type_organisation', $this->organisation->type)
            ->where('is_active', true)
            ->where('is_obligatoire', true)
            ->pluck('id');

        $providedTypes = $this->documents()->pluck('document_type_id');

        return $requiredTypes->diff($providedTypes)->isEmpty();
    }

    /**
     * Obtenir les documents manquants
     */
    public function getMissingDocuments()
    {
        $requiredTypes = DocumentType::where('type_organisation', $this->organisation->type)
            ->where('is_active', true)
            ->where('is_obligatoire', true)
            ->pluck('id');

        $providedTypes = $this->documents()->pluck('document_type_id');

        $missingIds = $requiredTypes->diff($providedTypes);

        return DocumentType::whereIn('id', $missingIds)->get();
    }

    /**
     * Obtenir la prochaine étape du workflow
     */
    public function getNextStep()
    {
        if (!$this->current_step_id) {
            return WorkflowStep::where('type_organisation', $this->organisation->type)
                ->where('type_operation', $this->type_operation)
                ->where('is_active', true)
                ->orderBy('ordre')
                ->first();
        }

        return WorkflowStep::where('type_organisation', $this->organisation->type)
            ->where('type_operation', $this->type_operation)
            ->where('is_active', true)
            ->where('ordre', '>', $this->currentStep->ordre)
            ->orderBy('ordre')
            ->first();
    }

    /**
     * Obtenir l'étape précédente du workflow (pour retour en cas de rejet)
     */
    public function getPreviousStep()
    {
        if (!$this->current_step_id) {
            return null;
        }

        return WorkflowStep::where('type_organisation', $this->organisation->type)
            ->where('type_operation', $this->type_operation)
            ->where('is_active', true)
            ->where('ordre', '<', $this->currentStep->ordre)
            ->orderBy('ordre', 'desc')
            ->first();
    }

    /**
     * Obtenir le dernier rejet
     */
    public function getLastRejection()
    {
        return $this->validations()
            ->where('decision', 'rejete')
            ->latest()
            ->first();
    }

    /**
     * Obtenir le pourcentage de progression
     */
    public function getProgressionPercentage(): int
    {
        if (!$this->current_step_id) {
            return 0;
        }

        $totalSteps = WorkflowStep::where('type_organisation', $this->organisation->type)
            ->where('type_operation', $this->type_operation)
            ->where('is_active', true)
            ->count();

        if ($totalSteps === 0) {
            return 0;
        }

        $currentStepOrder = $this->currentStep->ordre;

        return round(($currentStepOrder / $totalSteps) * 100);
    }

    /**
     * Accesseurs
     */
    public function getTypeOperationLabelAttribute(): string
    {
        $labels = [
            self::TYPE_CREATION => 'Création',
            self::TYPE_MODIFICATION => 'Modification',
            self::TYPE_CESSATION => 'Cessation',
            self::TYPE_DECLARATION => 'Déclaration',
            self::TYPE_FUSION => 'Fusion',
            self::TYPE_ABSORPTION => 'Absorption'
        ];

        return $labels[$this->type_operation] ?? $this->type_operation;
    }

    public function getStatutLabelAttribute(): string
    {
        $labels = [
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_SOUMIS => 'Soumis',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_ACCEPTE => 'Accepté',
            self::STATUT_REJETE => 'Rejeté',
            self::STATUT_ARCHIVE => 'Archivé'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        $colors = [
            self::STATUT_BROUILLON => 'secondary',
            self::STATUT_SOUMIS => 'info',
            self::STATUT_EN_COURS => 'warning',
            self::STATUT_ACCEPTE => 'success',
            self::STATUT_REJETE => 'danger',
            self::STATUT_ARCHIVE => 'dark'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }
}