<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ValidationEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'description',
        'type',
        'email',
        'telephone',
        'adresse',
        'responsable_nom',
        'responsable_fonction',
        'responsable_email',
        'responsable_telephone',
        'is_active',
        'can_validate_all_types',
        'allowed_organisation_types',
        'allowed_operation_types',
        'notification_settings',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'can_validate_all_types' => 'boolean',
        'allowed_organisation_types' => 'array',
        'allowed_operation_types' => 'array',
        'notification_settings' => 'array',
        'metadata' => 'array'
    ];

    // Constantes pour les types d'entités
    const TYPE_INTERNE = 'interne';
    const TYPE_EXTERNE = 'externe';
    const TYPE_COMMISSION = 'commission';
    const TYPE_DIRECTION = 'direction';
    const TYPE_SERVICE = 'service';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entity) {
            // Générer un code unique si non fourni
            if (empty($entity->code)) {
                $entity->code = self::generateCode($entity->nom, $entity->type);
            }

            // Définir les paramètres de notification par défaut
            if (empty($entity->notification_settings)) {
                $entity->notification_settings = [
                    'email_enabled' => true,
                    'sms_enabled' => false,
                    'notify_on_assignment' => true,
                    'notify_on_deadline' => true,
                    'deadline_reminder_days' => [1, 3]
                ];
            }
        });
    }

    /**
     * Générer un code unique
     */
    public static function generateCode($nom, $type): string
    {
        $typePrefixes = [
            self::TYPE_INTERNE => 'INT',
            self::TYPE_EXTERNE => 'EXT',
            self::TYPE_COMMISSION => 'COM',
            self::TYPE_DIRECTION => 'DIR',
            self::TYPE_SERVICE => 'SRV'
        ];

        $prefix = $typePrefixes[$type] ?? 'ENT';
        
        // Créer un code à partir du nom
        $namePart = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nom), 0, 10));
        
        $baseCode = $prefix . '_' . $namePart;
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
    public function agents(): HasMany
    {
        return $this->hasMany(EntityAgent::class);
    }

    public function activeAgents(): HasMany
    {
        return $this->hasMany(EntityAgent::class)->where('is_active', true);
    }

    public function workflowSteps(): BelongsToMany
    {
        return $this->belongsToMany(WorkflowStep::class, 'workflow_step_entities')
            ->withPivot(['ordre', 'is_optional'])
            ->withTimestamps();
    }

    public function validations(): HasMany
    {
        return $this->hasMany(DossierValidation::class);
    }

    /**
     * Scopes
     */
    public function scopeActives($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInternes($query)
    {
        return $query->where('type', self::TYPE_INTERNE);
    }

    public function scopeExternes($query)
    {
        return $query->where('type', self::TYPE_EXTERNE);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Accesseurs
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_INTERNE => 'Interne',
            self::TYPE_EXTERNE => 'Externe',
            self::TYPE_COMMISSION => 'Commission',
            self::TYPE_DIRECTION => 'Direction',
            self::TYPE_SERVICE => 'Service'
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getStatutLabelAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getStatutColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'danger';
    }

    public function getAgentCountAttribute(): int
    {
        return $this->activeAgents()->count();
    }

    /**
     * Méthodes utilitaires
     */
    public function canValidateOrganisationType($type): bool
    {
        if ($this->can_validate_all_types) {
            return true;
        }

        return in_array($type, $this->allowed_organisation_types ?? []);
    }

    public function canValidateOperationType($type): bool
    {
        if ($this->can_validate_all_types) {
            return true;
        }

        return in_array($type, $this->allowed_operation_types ?? []);
    }

    public function hasAgents(): bool
    {
        return $this->activeAgents()->count() > 0;
    }

    public function hasCapacity(): bool
    {
        // Vérifier si l'entité a la capacité de traiter de nouveaux dossiers
        $activeValidations = $this->validations()
            ->whereNull('validated_at')
            ->count();

        $agentCount = $this->agent_count;
        
        // Règle métier : max 10 dossiers en cours par agent
        $maxCapacity = $agentCount * 10;

        return $activeValidations < $maxCapacity;
    }

    /**
     * Obtenir l'agent avec le moins de dossiers
     */
    public function getAvailableAgent()
    {
        return $this->activeAgents()
            ->withCount(['validations' => function ($query) {
                $query->whereNull('validated_at');
            }])
            ->orderBy('validations_count')
            ->first();
    }

    /**
     * Ajouter un agent
     */
    public function addAgent($userId, $role = 'validator'): EntityAgent
    {
        return $this->agents()->create([
            'user_id' => $userId,
            'role' => $role,
            'is_active' => true,
            'assigned_at' => now()
        ]);
    }

    /**
     * Retirer un agent
     */
    public function removeAgent($userId): bool
    {
        return $this->agents()
            ->where('user_id', $userId)
            ->update(['is_active' => false]);
    }

    /**
     * Activer/Désactiver l'entité
     */
    public function toggleActive(): bool
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Obtenir les statistiques de validation
     */
    public function getStatistics($startDate = null, $endDate = null): array
    {
        $query = $this->validations();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $approved = clone $query;
        $rejected = clone $query;
        $pending = clone $query;
        $avgTime = clone $query;

        $approved = $approved->where('decision', 'approuve')->count();
        $rejected = $rejected->where('decision', 'rejete')->count();
        $pending = $pending->whereNull('decision')->count();

        // Temps moyen de traitement
        $completedValidations = $avgTime->whereNotNull('validated_at')->get();
        $totalHours = 0;
        
        foreach ($completedValidations as $validation) {
            $totalHours += $validation->created_at->diffInHours($validation->validated_at);
        }

        $averageTime = $completedValidations->count() > 0 
            ? round($totalHours / $completedValidations->count(), 2) 
            : 0;

        return [
            'total' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'pending' => $pending,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'rejection_rate' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
            'average_time_hours' => $averageTime,
            'agent_count' => $this->agent_count
        ];
    }

    /**
     * Obtenir la charge de travail par agent
     */
    public function getWorkloadByAgent(): array
    {
        $agents = $this->activeAgents()
            ->with(['user', 'validations' => function ($query) {
                $query->whereNull('validated_at');
            }])
            ->get();

        $workload = [];

        foreach ($agents as $agent) {
            $workload[] = [
                'agent' => $agent->user->name,
                'email' => $agent->user->email,
                'role' => $agent->role,
                'pending_count' => $agent->validations->count(),
                'total_processed' => $agent->validations()
                    ->whereNotNull('validated_at')
                    ->count()
            ];
        }

        return $workload;
    }

    /**
     * Obtenir les types d'entités disponibles
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_INTERNE => 'Interne',
            self::TYPE_EXTERNE => 'Externe',
            self::TYPE_COMMISSION => 'Commission',
            self::TYPE_DIRECTION => 'Direction',
            self::TYPE_SERVICE => 'Service'
        ];
    }

    /**
     * Vérifier si l'entité peut traiter un dossier spécifique
     */
    public function canProcessDossier($dossierId): array
    {
        $dossier = Dossier::find($dossierId);

        if (!$dossier) {
            return ['can_process' => false, 'reason' => 'Dossier non trouvé'];
        }

        // Vérifier si l'entité est active
        if (!$this->is_active) {
            return ['can_process' => false, 'reason' => 'Entité inactive'];
        }

        // Vérifier si l'entité a des agents
        if (!$this->hasAgents()) {
            return ['can_process' => false, 'reason' => 'Aucun agent disponible'];
        }

        // Vérifier le type d'organisation
        if (!$this->canValidateOrganisationType($dossier->organisation->type)) {
            return ['can_process' => false, 'reason' => 'Type d\'organisation non autorisé'];
        }

        // Vérifier le type d'opération
        if (!$this->canValidateOperationType($dossier->type_operation)) {
            return ['can_process' => false, 'reason' => 'Type d\'opération non autorisé'];
        }

        // Vérifier la capacité
        if (!$this->hasCapacity()) {
            return ['can_process' => false, 'reason' => 'Capacité maximale atteinte'];
        }

        return ['can_process' => true];
    }

    /**
     * Envoyer une notification
     */
    public function sendNotification($type, $data = []): bool
    {
        $settings = $this->notification_settings;

        if (!$settings) {
            return false;
        }

        // Vérifier si le type de notification est activé
        switch ($type) {
            case 'assignment':
                if (!($settings['notify_on_assignment'] ?? false)) {
                    return false;
                }
                break;
            case 'deadline':
                if (!($settings['notify_on_deadline'] ?? false)) {
                    return false;
                }
                break;
        }

        // Envoyer les notifications selon les paramètres
        if ($settings['email_enabled'] ?? false) {
            // Logique d'envoi d'email
            // Mail::to($this->email)->send(new EntityNotification($type, $data));
        }

        if ($settings['sms_enabled'] ?? false) {
            // Logique d'envoi de SMS
            // SMS::send($this->telephone, $message);
        }

        return true;
    }
}