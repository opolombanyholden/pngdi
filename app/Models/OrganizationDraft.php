<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class OrganizationDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_type',
        'form_data',
        'current_step',
        'completion_percentage',
        'validation_errors',
        'session_id',
        'last_saved_at',
        'expires_at'
    ];

    protected $casts = [
        'form_data' => 'array',
        'validation_errors' => 'array',
        'completion_percentage' => 'integer',
        'current_step' => 'integer',
        'last_saved_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    // =============================================
    // RELATIONS
    // =============================================

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les accusés de réception générés
     */
    public function accuses(): HasMany
    {
        return $this->hasMany(DraftAccuse::class, 'draft_id');
    }

    // =============================================
    // SCOPES
    // =============================================

    /**
     * Brouillons actifs (non expirés)
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Brouillons expirés
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Brouillons par type d'organisation
     */
    public function scopeByType($query, $type)
    {
        return $query->where('organization_type', $type);
    }

    /**
     * Brouillons récents (derniers 7 jours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(7));
    }

    // =============================================
    // MÉTHODES UTILITAIRES
    // =============================================

    /**
     * Vérifier si le brouillon a expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at <= now();
    }

    /**
     * Vérifier si une étape est complétée
     */
    public function isStepCompleted(int $stepNumber): bool
    {
        $stepKey = "step_{$stepNumber}";
        $stepData = $this->form_data[$stepKey] ?? null;
        
        return $stepData && $stepData['status'] === 'completed';
    }

    /**
     * Obtenir les données d'une étape spécifique
     */
    public function getStepData(int $stepNumber): array
    {
        $stepKey = "step_{$stepNumber}";
        return $this->form_data[$stepKey] ?? [
            'status' => 'pending',
            'data' => [],
            'validated_at' => null,
            'errors' => []
        ];
    }

    /**
     * Mettre à jour les données d'une étape
     */
    public function updateStepData(int $stepNumber, array $data, string $status = 'completed'): void
    {
        $formData = $this->form_data ?? [];
        $stepKey = "step_{$stepNumber}";

        $formData[$stepKey] = [
            'status' => $status,
            'data' => $data,
            'validated_at' => now()->toISOString(),
            'errors' => []
        ];

        $this->form_data = $formData;
        $this->current_step = max($this->current_step, $stepNumber);
        $this->completion_percentage = $this->calculateCompletionPercentage();
        $this->last_saved_at = now();
    }

    /**
     * Marquer une étape comme en erreur
     */
    public function markStepAsError(int $stepNumber, array $errors): void
    {
        $formData = $this->form_data ?? [];
        $stepKey = "step_{$stepNumber}";

        if (isset($formData[$stepKey])) {
            $formData[$stepKey]['status'] = 'error';
            $formData[$stepKey]['errors'] = $errors;
            $formData[$stepKey]['validated_at'] = now()->toISOString();
        } else {
            $formData[$stepKey] = [
                'status' => 'error',
                'data' => [],
                'validated_at' => now()->toISOString(),
                'errors' => $errors
            ];
        }

        $this->form_data = $formData;
        $this->validation_errors = array_merge($this->validation_errors ?? [], $errors);
    }

    /**
     * Calculer le pourcentage de completion
     */
    public function calculateCompletionPercentage(): int
    {
        $formData = $this->form_data ?? [];
        $completedSteps = 0;
        $totalSteps = 9;

        for ($i = 1; $i <= $totalSteps; $i++) {
            $stepKey = "step_{$i}";
            if (isset($formData[$stepKey]) && $formData[$stepKey]['status'] === 'completed') {
                $completedSteps++;
            }
        }

        return round(($completedSteps / $totalSteps) * 100);
    }

    /**
     * Obtenir la prochaine étape à compléter
     */
    public function getNextStep(): int
    {
        for ($i = 1; $i <= 9; $i++) {
            if (!$this->isStepCompleted($i)) {
                return $i;
            }
        }
        return 9; // Toutes les étapes sont complétées
    }

    /**
     * Vérifier si le brouillon est prêt pour soumission finale
     */
    public function isReadyForSubmission(): bool
    {
        for ($i = 1; $i <= 9; $i++) {
            if (!$this->isStepCompleted($i)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Obtenir un résumé des étapes
     */
    public function getStepsSummary(): array
    {
        $summary = [];
        $stepNames = [
            1 => 'Type d\'organisation',
            2 => 'Guide et exigences',
            3 => 'Informations demandeur',
            4 => 'Informations organisation',
            5 => 'Coordonnées',
            6 => 'Fondateurs',
            7 => 'Adhérents',
            8 => 'Documents',
            9 => 'Validation finale'
        ];

        for ($i = 1; $i <= 9; $i++) {
            $stepData = $this->getStepData($i);
            $summary[$i] = [
                'name' => $stepNames[$i],
                'status' => $stepData['status'],
                'completed' => $stepData['status'] === 'completed',
                'has_errors' => !empty($stepData['errors']),
                'validated_at' => $stepData['validated_at']
            ];
        }

        return $summary;
    }

    /**
     * Étendre l'expiration du brouillon
     */
    public function extendExpiration(int $days = 7): void
    {
        $this->expires_at = now()->addDays($days);
        $this->save();
    }

    /**
     * Nettoyer les données sensibles avant export
     */
    public function sanitizeForExport(): array
    {
        $data = $this->toArray();
        
        // Retirer les données sensibles
        unset($data['session_id']);
        
        // Anonymiser les données personnelles dans form_data si nécessaire
        if (isset($data['form_data'])) {
            // Ici on pourrait masquer certaines données sensibles
            // Par exemple les NIP complets, etc.
        }

        return $data;
    }

    /**
     * Obtenir les statistiques du brouillon
     */
    public function getStatistics(): array
    {
        $stepsSummary = $this->getStepsSummary();
        
        // Compatible PHP 7.3 - utilisation de fonction anonyme classique
        $completedSteps = array_filter($stepsSummary, function($step) {
            return $step['completed'];
        });
        
        $stepsWithErrors = array_filter($stepsSummary, function($step) {
            return $step['has_errors'];
        });

        return [
            'total_steps' => 9,
            'completed_steps' => count($completedSteps),
            'steps_with_errors' => count($stepsWithErrors),
            'completion_percentage' => $this->completion_percentage,
            'current_step' => $this->current_step,
            'is_ready_for_submission' => $this->isReadyForSubmission(),
            'days_until_expiration' => $this->expires_at ? 
                max(0, $this->expires_at->diffInDays(now())) : null,
            'last_activity' => $this->last_saved_at ? $this->last_saved_at->diffForHumans() : null,
            'organization_type' => $this->organization_type
        ];
    }

    // =============================================
    // MÉTHODES DE CONVERSION
    // =============================================

    /**
     * Convertir en données pour création d'organisation
     */
    public function toOrganisationData(): array
    {
        $formData = $this->form_data ?? [];
        $consolidatedData = [];

        // Extraire toutes les données des étapes
        foreach ($formData as $stepKey => $stepData) {
            if (isset($stepData['data'])) {
                $consolidatedData = array_merge($consolidatedData, $stepData['data']);
            }
        }

        return $consolidatedData;
    }

    /**
     * Obtenir les données des fondateurs
     */
    public function getFondateursData(): array
    {
        $step6Data = $this->getStepData(6);
        return $step6Data['data']['fondateurs'] ?? [];
    }

    /**
     * Obtenir les données des adhérents
     */
    public function getAdherentsData(): array
    {
        $step7Data = $this->getStepData(7);
        return $step7Data['data']['adherents'] ?? [];
    }

    /**
     * Obtenir les données des documents
     */
    public function getDocumentsData(): array
    {
        $step8Data = $this->getStepData(8);
        return $step8Data['data']['documents'] ?? [];
    }

    // =============================================
    // ÉVÉNEMENTS DU MODÈLE
    // =============================================

    /**
     * Actions à effectuer lors de la création
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($draft) {
            // Définir une expiration par défaut si non spécifiée
            if (!$draft->expires_at) {
                $draft->expires_at = now()->addDays(7);
            }

            // Initialiser les données de base
            if (!$draft->form_data) {
                $draft->form_data = [];
            }

            if (!$draft->completion_percentage) {
                $draft->completion_percentage = 0;
            }

            if (!$draft->current_step) {
                $draft->current_step = 1;
            }
        });

        static::updating(function ($draft) {
            $draft->last_saved_at = now();
        });
    }
}