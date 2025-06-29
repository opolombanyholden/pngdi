<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'user_id',
        'type_operation',
        'ancien_statut',
        'nouveau_statut',
        'workflow_step_id',
        'description',
        'donnees_avant',
        'donnees_apres',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'donnees_avant' => 'array',
        'donnees_apres' => 'array'
    ];

    // Constantes basées sur l'enum existant dans la DB
    const TYPE_CREATION = 'creation';
    const TYPE_SOUMISSION = 'soumission';
    const TYPE_VALIDATION = 'validation';
    const TYPE_REJET = 'rejet';
    const TYPE_MODIFICATION = 'modification';
    const TYPE_RETOUR_POUR_CORRECTION = 'retour_pour_correction';
    const TYPE_ARCHIVAGE = 'archivage';
    const TYPE_VERROUILLAGE = 'verrouillage';
    const TYPE_DEVERROUILLAGE = 'deverrouillage';
    const TYPE_ASSIGNATION = 'assignation';
    const TYPE_COMMENTAIRE = 'commentaire';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($operation) {
            if (empty($operation->user_id)) {
                $operation->user_id = auth()->id();
            }
            if (empty($operation->ip_address)) {
                $operation->ip_address = request()->ip();
            }
            if (empty($operation->user_agent)) {
                $operation->user_agent = request()->userAgent();
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_operation', $type);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDossier($query, $dossierId)
    {
        return $query->where('dossier_id', $dossierId);
    }

    /**
     * Accesseurs
     */
    public function getTypeOperationLabelAttribute(): string
    {
        $labels = [
            self::TYPE_CREATION => 'Création',
            self::TYPE_SOUMISSION => 'Soumission',
            self::TYPE_VALIDATION => 'Validation',
            self::TYPE_REJET => 'Rejet',
            self::TYPE_MODIFICATION => 'Modification',
            self::TYPE_RETOUR_POUR_CORRECTION => 'Retour pour correction',
            self::TYPE_ARCHIVAGE => 'Archivage',
            self::TYPE_VERROUILLAGE => 'Verrouillage',
            self::TYPE_DEVERROUILLAGE => 'Déverrouillage',
            self::TYPE_ASSIGNATION => 'Assignation',
            self::TYPE_COMMENTAIRE => 'Commentaire'
        ];

        return $labels[$this->type_operation] ?? $this->type_operation;
    }

    /**
     * Méthodes utilitaires pour créer des opérations
     */
    public static function recordWorkflowInitialization(Dossier $dossier, $description = 'Initialisation du workflow'): self
    {
        return self::create([
            'dossier_id' => $dossier->id,
            'type_operation' => self::TYPE_CREATION,
            'nouveau_statut' => $dossier->statut,
            'workflow_step_id' => $dossier->current_step_id,
            'description' => $description,
            'donnees_apres' => [
                'workflow_step_id' => $dossier->current_step_id,
                'statut' => $dossier->statut
            ]
        ]);
    }

    public static function recordStatusChange(Dossier $dossier, $oldStatus, $newStatus, $description = null): self
    {
        return self::create([
            'dossier_id' => $dossier->id,
            'type_operation' => self::TYPE_MODIFICATION,
            'ancien_statut' => $oldStatus,
            'nouveau_statut' => $newStatus,
            'workflow_step_id' => $dossier->current_step_id,
            'description' => $description ?? "Changement de statut de {$oldStatus} vers {$newStatus}",
            'donnees_avant' => ['statut' => $oldStatus],
            'donnees_apres' => ['statut' => $newStatus]
        ]);
    }

    public static function recordValidation(Dossier $dossier, $decision, $comment = null): self
    {
        return self::create([
            'dossier_id' => $dossier->id,
            'type_operation' => $decision === 'approuve' ? self::TYPE_VALIDATION : self::TYPE_REJET,
            'workflow_step_id' => $dossier->current_step_id,
            'description' => $comment ?? "Décision: {$decision}",
            'donnees_apres' => [
                'decision' => $decision,
                'commentaire' => $comment
            ]
        ]);
    }

    public static function recordAssignment(Dossier $dossier, $assignedTo, $assignedBy = null): self
    {
        return self::create([
            'dossier_id' => $dossier->id,
            'type_operation' => self::TYPE_ASSIGNATION,
            'workflow_step_id' => $dossier->current_step_id,
            'description' => "Assignation à l'utilisateur #{$assignedTo}",
            'donnees_apres' => [
                'assigned_to' => $assignedTo,
                'assigned_by' => $assignedBy ?? auth()->id()
            ]
        ]);
    }

    public static function recordComment(Dossier $dossier, $comment): self
    {
        return self::create([
            'dossier_id' => $dossier->id,
            'type_operation' => self::TYPE_COMMENTAIRE,
            'workflow_step_id' => $dossier->current_step_id,
            'description' => 'Ajout de commentaire',
            'donnees_apres' => [
                'commentaire' => $comment
            ]
        ]);
    }

    /**
     * Obtenir l'historique des opérations pour un dossier
     */
    public static function getHistoryForDossier($dossierId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('dossier_id', $dossierId)
            ->with(['user', 'workflowStep'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtenir les statistiques d'opérations
     */
    public static function getStatsForPeriod($startDate, $endDate): array
    {
        $operations = self::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('type_operation, COUNT(*) as count')
            ->groupBy('type_operation')
            ->get();

        return $operations->pluck('count', 'type_operation')->toArray();
    }
}