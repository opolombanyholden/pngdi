<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'description',
        'type_organisation',
        'type_operation',
        'ordre',
        'delai_traitement',
        'is_active',
        'can_reject',
        'can_request_info',
        'required_documents',
        'validation_rules',
        'email_template',
        'sms_template'
    ];

    protected $casts = [
        'ordre' => 'integer',
        'delai_traitement' => 'integer',
        'is_active' => 'boolean',
        'can_reject' => 'boolean',
        'can_request_info' => 'boolean',
        'required_documents' => 'array',
        'validation_rules' => 'array'
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($step) {
            // Générer un code unique si non fourni
            if (empty($step->code)) {
                $step->code = self::generateCode($step->nom, $step->type_organisation, $step->type_operation);
            }

            // Définir l'ordre si non fourni
            if (empty($step->ordre)) {
                $maxOrdre = self::where('type_organisation', $step->type_organisation)
                    ->where('type_operation', $step->type_operation)
                    ->max('ordre');
                $step->ordre = ($maxOrdre ?? 0) + 1;
            }

            // Définir le délai par défaut (3 jours)
            if (empty($step->delai_traitement)) {
                $step->delai_traitement = 3;
            }
        });
    }

    /**
     * Générer un code unique
     */
    public static function generateCode($nom, $typeOrganisation, $typeOperation): string
    {
        // Préfixes pour type d'organisation
        $orgPrefixes = [
            Organisation::TYPE_ASSOCIATION => 'ASS',
            Organisation::TYPE_ONG => 'ONG',
            Organisation::TYPE_PARTI => 'PP',
            Organisation::TYPE_CONFESSION => 'CR'
        ];

        // Préfixes pour type d'opération
        $opPrefixes = [
            Dossier::TYPE_CREATION => 'CRE',
            Dossier::TYPE_MODIFICATION => 'MOD',
            Dossier::TYPE_CESSATION => 'CES',
            Dossier::TYPE_DECLARATION => 'DEC',
            Dossier::TYPE_FUSION => 'FUS',
            Dossier::TYPE_ABSORPTION => 'ABS'
        ];

        $orgPrefix = $orgPrefixes[$typeOrganisation] ?? 'ORG';
        $opPrefix = $opPrefixes[$typeOperation] ?? 'OP';
        
        // Créer un code à partir du nom
        $namePart = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nom), 0, 6));
        
        // Ajouter un numéro si nécessaire pour l'unicité
        $baseCode = $orgPrefix . '_' . $opPrefix . '_' . $namePart;
        $code = $baseCode;
        $counter = 1;

        while (self::where('code', $code)->exists()) {
            $code = $baseCode . '_' . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Relations
     */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(ValidationEntity::class, 'workflow_step_entities')
            ->withPivot(['ordre', 'is_optional'])
            ->withTimestamps();
    }

    public function validations(): HasMany
    {
        return $this->hasMany(DossierValidation::class);
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'current_step_id');
    }

    /**
     * Scopes
     */
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOrganisationType($query, $type)
    {
        return $query->where('type_organisation', $type);
    }

    public function scopeForOperation($query, $operation)
    {
        return $query->where('type_operation', $operation);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    /**
     * Obtenir les étapes pour un type d'organisation et d'opération
     */
    public static function getWorkflow($typeOrganisation, $typeOperation)
    {
        return self::actifs()
            ->forOrganisationType($typeOrganisation)
            ->forOperation($typeOperation)
            ->ordered()
            ->get();
    }

    /**
     * Obtenir la première étape
     */
    public static function getFirstStep($typeOrganisation, $typeOperation)
    {
        return self::actifs()
            ->forOrganisationType($typeOrganisation)
            ->forOperation($typeOperation)
            ->ordered()
            ->first();
    }

    /**
     * Obtenir la dernière étape
     */
    public static function getLastStep($typeOrganisation, $typeOperation)
    {
        return self::actifs()
            ->forOrganisationType($typeOrganisation)
            ->forOperation($typeOperation)
            ->ordered()
            ->get()
            ->last();
    }

    /**
     * Obtenir l'étape suivante
     */
    public function getNextStep()
    {
        return self::actifs()
            ->where('type_organisation', $this->type_organisation)
            ->where('type_operation', $this->type_operation)
            ->where('ordre', '>', $this->ordre)
            ->ordered()
            ->first();
    }

    /**
     * Obtenir l'étape précédente
     */
    public function getPreviousStep()
    {
        return self::actifs()
            ->where('type_organisation', $this->type_organisation)
            ->where('type_operation', $this->type_operation)
            ->where('ordre', '<', $this->ordre)
            ->orderBy('ordre', 'desc')
            ->first();
    }

    /**
     * Accesseurs
     */
    public function getIsFirstStepAttribute(): bool
    {
        $firstStep = self::getFirstStep($this->type_organisation, $this->type_operation);
        return $firstStep && $firstStep->id === $this->id;
    }

    public function getIsLastStepAttribute(): bool
    {
        $lastStep = self::getLastStep($this->type_organisation, $this->type_operation);
        return $lastStep && $lastStep->id === $this->id;
    }

    public function getDelaiTraitementLabelAttribute(): string
    {
        if ($this->delai_traitement === 1) {
            return '1 jour';
        }
        return $this->delai_traitement . ' jours';
    }

    public function getPositionLabelAttribute(): string
    {
        $total = self::where('type_organisation', $this->type_organisation)
            ->where('type_operation', $this->type_operation)
            ->where('is_active', true)
            ->count();

        return "Étape {$this->ordre} sur {$total}";
    }

    /**
     * Méthodes utilitaires
     */
    public function canReject(): bool
    {
        return $this->can_reject;
    }

    public function canRequestInfo(): bool
    {
        return $this->can_request_info;
    }

    public function hasEntities(): bool
    {
        return $this->entities()->count() > 0;
    }

    public function hasRequiredDocuments(): bool
    {
        return !empty($this->required_documents) && count($this->required_documents) > 0;
    }

    public function hasValidationRules(): bool
    {
        return !empty($this->validation_rules) && count($this->validation_rules) > 0;
    }

    /**
     * Activer/Désactiver l'étape
     */
    public function toggleActive(): bool
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Réordonner les étapes
     */
    public static function reorder($typeOrganisation, $typeOperation, array $orderedIds): void
    {
        foreach ($orderedIds as $ordre => $id) {
            self::where('id', $id)
                ->where('type_organisation', $typeOrganisation)
                ->where('type_operation', $typeOperation)
                ->update(['ordre' => $ordre + 1]);
        }
    }

    /**
     * Ajouter une entité de validation
     */
    public function addEntity($entityId, $ordre = null, $isOptional = false): void
    {
        if (!$ordre) {
            $maxOrdre = $this->entities()->max('ordre');
            $ordre = ($maxOrdre ?? 0) + 1;
        }

        $this->entities()->attach($entityId, [
            'ordre' => $ordre,
            'is_optional' => $isOptional
        ]);
    }

    /**
     * Retirer une entité de validation
     */
    public function removeEntity($entityId): void
    {
        $this->entities()->detach($entityId);
    }

    /**
     * Vérifier si un dossier peut passer cette étape
     */
    public function canProcessDossier($dossierId): array
    {
        $dossier = Dossier::find($dossierId);
        
        if (!$dossier) {
            return ['can_process' => false, 'reason' => 'Dossier non trouvé'];
        }

        // Vérifier que le dossier est à cette étape
        if ($dossier->current_step_id !== $this->id) {
            return ['can_process' => false, 'reason' => 'Le dossier n\'est pas à cette étape'];
        }

        // Vérifier les documents requis
        if ($this->hasRequiredDocuments()) {
            foreach ($this->required_documents as $docTypeId) {
                $hasDoc = $dossier->documents()
                    ->where('document_type_id', $docTypeId)
                    ->where('is_validated', true)
                    ->exists();

                if (!$hasDoc) {
                    $docType = DocumentType::find($docTypeId);
                    return [
                        'can_process' => false, 
                        'reason' => "Document requis manquant : " . ($docType->nom ?? 'Document')
                    ];
                }
            }
        }

        // Vérifier les règles de validation personnalisées
        if ($this->hasValidationRules()) {
            foreach ($this->validation_rules as $rule) {
                // Implémenter la logique de validation personnalisée
                // Exemple : vérifier le nombre minimum d'adhérents
                if ($rule['type'] === 'minimum_adherents') {
                    $count = $dossier->organisation->adherentsActifs()->count();
                    if ($count < $rule['value']) {
                        return [
                            'can_process' => false,
                            'reason' => "Nombre minimum d'adhérents non atteint : {$rule['value']} requis"
                        ];
                    }
                }
            }
        }

        return ['can_process' => true];
    }

    /**
     * Obtenir le temps moyen de traitement
     */
    public function getAverageProcessingTime(): ?float
    {
        $validations = $this->validations()
            ->whereNotNull('validated_at')
            ->get();

        if ($validations->isEmpty()) {
            return null;
        }

        $totalHours = 0;
        foreach ($validations as $validation) {
            $totalHours += $validation->created_at->diffInHours($validation->validated_at);
        }

        return round($totalHours / $validations->count(), 2);
    }

    /**
     * Obtenir les statistiques de traitement
     */
    public function getStatistics(): array
    {
        $total = $this->validations()->count();
        $approved = $this->validations()->where('decision', 'approuve')->count();
        $rejected = $this->validations()->where('decision', 'rejete')->count();
        $pending = $this->validations()->whereNull('decision')->count();
        $avgTime = $this->getAverageProcessingTime();

        return [
            'total' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'pending' => $pending,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'average_time_hours' => $avgTime
        ];
    }

    /**
     * Dupliquer pour un autre type d'organisation
     */
    public function duplicateFor($typeOrganisation, $typeOperation = null): WorkflowStep
    {
        $newStep = $this->replicate();
        $newStep->type_organisation = $typeOrganisation;
        
        if ($typeOperation) {
            $newStep->type_operation = $typeOperation;
        }
        
        $newStep->code = self::generateCode($this->nom, $typeOrganisation, $newStep->type_operation);
        $newStep->save();

        // Dupliquer les entités associées
        foreach ($this->entities as $entity) {
            $newStep->entities()->attach($entity->id, [
                'ordre' => $entity->pivot->ordre,
                'is_optional' => $entity->pivot->is_optional
            ]);
        }

        return $newStep;
    }
}