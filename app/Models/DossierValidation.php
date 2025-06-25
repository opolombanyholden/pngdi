<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DossierValidation extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'workflow_step_id',
        'validation_entity_id',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'decision',
        'commentaire',
        'visa',
        'reference',
        'numero_enregistrement',
        'validated_at',
        'validated_by',
        'deadline',
        'is_returned',
        'returned_from_step_id',
        'documents_added',
        'metadata'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'validated_at' => 'datetime',
        'deadline' => 'datetime',
        'is_returned' => 'boolean',
        'documents_added' => 'array',
        'metadata' => 'array'
    ];

    // Constantes pour les décisions
    const DECISION_APPROUVE = 'approuve';
    const DECISION_REJETE = 'rejete';
    const DECISION_EN_ATTENTE = 'en_attente';
    const DECISION_DEMANDE_INFO = 'demande_info';

    // Constantes pour les statuts
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($validation) {
            // Définir la date d'assignation
            if (empty($validation->assigned_at)) {
                $validation->assigned_at = now();
            }

            // Calculer la deadline basée sur le délai de traitement de l'étape
            if (empty($validation->deadline) && $validation->workflowStep) {
                $delaiJours = $validation->workflowStep->delai_traitement ?? 3;
                $validation->deadline = now()->addDays($delaiJours);
            }
        });

        static::updating(function ($validation) {
            // Si une décision est prise, enregistrer la date et l'utilisateur
            if ($validation->isDirty('decision') && !empty($validation->decision)) {
                if (empty($validation->validated_at)) {
                    $validation->validated_at = now();
                }
                if (empty($validation->validated_by)) {
                    $validation->validated_by = auth()->id();
                }
            }
        });
    }

    /**
     * Relations
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function validationEntity(): BelongsTo
    {
        return $this->belongsTo(ValidationEntity::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function returnedFromStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'returned_from_step_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DossierComment::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->whereNull('decision');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('decision');
    }

    public function scopeApproved($query)
    {
        return $query->where('decision', self::DECISION_APPROUVE);
    }

    public function scopeRejected($query)
    {
        return $query->where('decision', self::DECISION_REJETE);
    }

    public function scopeExpired($query)
    {
        return $query->whereNull('decision')
            ->where('deadline', '<', now());
    }

    public function scopeByAgent($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByEntity($query, $entityId)
    {
        return $query->where('validation_entity_id', $entityId);
    }

    /**
     * Accesseurs
     */
    public function getStatusAttribute(): string
    {
        if ($this->decision) {
            return self::STATUS_COMPLETED;
        }

        if ($this->deadline < now()) {
            return self::STATUS_EXPIRED;
        }

        if ($this->assigned_to) {
            return self::STATUS_IN_PROGRESS;
        }

        return self::STATUS_ASSIGNED;
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_ASSIGNED => 'Assigné',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_COMPLETED => 'Terminé',
            self::STATUS_EXPIRED => 'Expiré'
        ];

        return $labels[$this->status] ?? 'Inconnu';
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_ASSIGNED => 'info',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_EXPIRED => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getDecisionLabelAttribute(): string
    {
        $labels = [
            self::DECISION_APPROUVE => 'Approuvé',
            self::DECISION_REJETE => 'Rejeté',
            self::DECISION_EN_ATTENTE => 'En attente',
            self::DECISION_DEMANDE_INFO => 'Demande d\'informations'
        ];

        return $labels[$this->decision] ?? 'Non traité';
    }

    public function getDecisionColorAttribute(): string
    {
        $colors = [
            self::DECISION_APPROUVE => 'success',
            self::DECISION_REJETE => 'danger',
            self::DECISION_EN_ATTENTE => 'warning',
            self::DECISION_DEMANDE_INFO => 'info'
        ];

        return $colors[$this->decision] ?? 'secondary';
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->deadline || $this->decision) {
            return null;
        }

        return now()->diffInDays($this->deadline, false);
    }

    public function getProcessingTimeAttribute(): ?string
    {
        if (!$this->validated_at) {
            return null;
        }

        $hours = $this->assigned_at->diffInHours($this->validated_at);
        
        if ($hours < 24) {
            return $hours . ' heures';
        }

        $days = floor($hours / 24);
        return $days . ' jours';
    }

    /**
     * Méthodes utilitaires
     */
    public function isExpired(): bool
    {
        return !$this->decision && $this->deadline && $this->deadline < now();
    }

    public function isCompleted(): bool
    {
        return !empty($this->decision);
    }

    public function isPending(): bool
    {
        return empty($this->decision);
    }

    public function isApproved(): bool
    {
        return $this->decision === self::DECISION_APPROUVE;
    }

    public function isRejected(): bool
    {
        return $this->decision === self::DECISION_REJETE;
    }

    public function canBeValidated(): bool
    {
        return $this->isPending() && !$this->isExpired();
    }

    /**
     * Approuver la validation
     */
    public function approve($commentaire = null, $visa = null, $reference = null, $numeroEnregistrement = null): bool
    {
        if (!$this->canBeValidated()) {
            throw new \Exception('Cette validation ne peut pas être traitée');
        }

        $this->update([
            'decision' => self::DECISION_APPROUVE,
            'commentaire' => $commentaire,
            'visa' => $visa,
            'reference' => $reference,
            'numero_enregistrement' => $numeroEnregistrement,
            'validated_at' => now(),
            'validated_by' => auth()->id()
        ]);

        // Passer à l'étape suivante
        $this->moveToNextStep();

        return true;
    }

    /**
     * Rejeter la validation
     */
    public function reject($commentaire): bool
    {
        if (!$this->canBeValidated()) {
            throw new \Exception('Cette validation ne peut pas être traitée');
        }

        if (empty($commentaire)) {
            throw new \Exception('Un commentaire est obligatoire pour rejeter');
        }

        $this->update([
            'decision' => self::DECISION_REJETE,
            'commentaire' => $commentaire,
            'validated_at' => now(),
            'validated_by' => auth()->id()
        ]);

        // Mettre à jour le statut du dossier
        $this->dossier->update([
            'statut' => Dossier::STATUT_REJETE,
            'motif_rejet' => $commentaire
        ]);

        return true;
    }

    /**
     * Demander des informations complémentaires
     */
    public function requestInfo($commentaire): bool
    {
        if (!$this->canBeValidated()) {
            throw new \Exception('Cette validation ne peut pas être traitée');
        }

        if (empty($commentaire)) {
            throw new \Exception('Un commentaire est obligatoire');
        }

        $this->update([
            'decision' => self::DECISION_DEMANDE_INFO,
            'commentaire' => $commentaire,
            'validated_at' => now(),
            'validated_by' => auth()->id()
        ]);

        return true;
    }

    /**
     * Passer à l'étape suivante
     */
    protected function moveToNextStep(): void
    {
        $nextStep = $this->workflowStep->getNextStep();

        if ($nextStep) {
            // Mettre à jour l'étape courante du dossier
            $this->dossier->update([
                'current_step_id' => $nextStep->id,
                'statut' => Dossier::STATUT_EN_COURS
            ]);

            // Créer les validations pour la prochaine étape
            foreach ($nextStep->entities as $entity) {
                if ($entity->canProcessDossier($this->dossier->id)['can_process']) {
                    $agent = $entity->getAvailableAgent();

                    self::create([
                        'dossier_id' => $this->dossier->id,
                        'workflow_step_id' => $nextStep->id,
                        'validation_entity_id' => $entity->id,
                        'assigned_to' => $agent ? $agent->user_id : null,
                        'assigned_by' => auth()->id()
                    ]);
                }
            }
        } else {
            // C'était la dernière étape, marquer le dossier comme accepté
            $this->dossier->update([
                'statut' => Dossier::STATUT_ACCEPTE,
                'date_traitement' => now()
            ]);

            // Mettre à jour le statut de l'organisation si c'est une création
            if ($this->dossier->type_operation === Dossier::TYPE_CREATION) {
                $this->dossier->organisation->update([
                    'statut' => Organisation::STATUT_APPROUVE,
                    'numero_recepisse' => $this->generateNumeroRecepisse()
                ]);
            }
        }
    }

    /**
     * Générer un numéro de récépissé
     */
    protected function generateNumeroRecepisse(): string
    {
        $year = date('Y');
        $typePrefix = substr(strtoupper($this->dossier->organisation->type), 0, 3);
        
        $lastOrg = Organisation::where('numero_recepisse', 'like', $typePrefix . '-' . $year . '-%')
            ->orderBy('numero_recepisse', 'desc')
            ->first();

        if ($lastOrg) {
            $lastNumber = intval(substr($lastOrg->numero_recepisse, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%06d', $typePrefix, $year, $newNumber);
    }

    /**
     * Réassigner à un autre agent
     */
    public function reassignTo($userId, $reason = null): bool
    {
        $oldAssignee = $this->assigned_to;

        $this->update([
            'assigned_to' => $userId,
            'assigned_by' => auth()->id(),
            'assigned_at' => now()
        ]);

        // Créer un commentaire pour tracer la réassignation
        $this->comments()->create([
            'dossier_id' => $this->dossier_id,
            'user_id' => auth()->id(),
            'type' => 'reassignment',
            'contenu' => $reason ?? "Réassigné de l'agent #{$oldAssignee} à l'agent #{$userId}"
        ]);

        return true;
    }

    /**
     * Ajouter un document
     */
    public function addDocument($documentId): void
    {
        $documents = $this->documents_added ?? [];
        $documents[] = $documentId;
        $this->update(['documents_added' => array_unique($documents)]);
    }

    /**
     * Vérifier si l'utilisateur peut valider
     */
    public function canUserValidate($userId): bool
    {
        // L'agent assigné peut valider
        if ($this->assigned_to === $userId) {
            return true;
        }

        // Les superviseurs de l'entité peuvent valider
        $agent = EntityAgent::where('validation_entity_id', $this->validation_entity_id)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        return $agent && in_array($agent->role, ['supervisor', 'manager']);
    }

    /**
     * Obtenir l'historique des actions
     */
    public function getHistory(): array
    {
        $history = [];

        // Assignation
        $history[] = [
            'date' => $this->assigned_at,
            'action' => 'Assigné',
            'user' => $this->assignedBy->name ?? 'Système',
            'details' => "Assigné à " . ($this->assignedTo->name ?? 'Non assigné')
        ];

        // Commentaires
        foreach ($this->comments as $comment) {
            $history[] = [
                'date' => $comment->created_at,
                'action' => 'Commentaire',
                'user' => $comment->user->name,
                'details' => $comment->contenu
            ];
        }

        // Décision
        if ($this->decision) {
            $history[] = [
                'date' => $this->validated_at,
                'action' => $this->decision_label,
                'user' => $this->validatedBy->name ?? 'Inconnu',
                'details' => $this->commentaire
            ];
        }

        // Trier par date
        usort($history, function ($a, $b) {
            return $a['date']->timestamp - $b['date']->timestamp;
        });

        return $history;
    }
}