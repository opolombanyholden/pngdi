<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'locked_by',
        'workflow_step_id',
        'session_id',
        'locked_at',
        'expires_at',
        'is_active',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Relations
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('locked_by', $userId);
    }

    /**
     * Méthodes utilitaires
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function isActive(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function getRemainingTime(): ?int
    {
        if (!$this->expires_at || $this->isExpired()) {
            return null;
        }

        return now()->diffInMinutes($this->expires_at);
    }

    /**
     * Étendre le verrou
     */
    public function extend(int $minutes = 30): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->update([
            'expires_at' => now()->addMinutes($minutes)
        ]);
    }

    /**
     * Libérer le verrou
     */
    public function release(): bool
    {
        return $this->update([
            'is_active' => false
        ]);
    }
}