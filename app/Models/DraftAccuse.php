<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftAccuse extends Model
{
    use HasFactory;

    protected $fillable = [
        'draft_id',
        'user_id',
        'step_number',
        'step_name',
        'accuse_type',
        'numero_accuse',
        'contenu_html',
        'fichier_pdf',
        'donnees_etape',
        'hash_verification',
        'qr_code',
        'is_valide',
        'generated_at',
        'expires_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'donnees_etape' => 'array',
        'is_valide' => 'boolean',
        'generated_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    // Relations
    public function draft(): BelongsTo
    {
        return $this->belongsTo(OrganizationDraft::class, 'draft_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('is_valide', true);
    }

    public function scopeByStep($query, $stepNumber)
    {
        return $query->where('step_number', $stepNumber);
    }

    // Méthodes utilitaires
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at <= now();
    }

    public function generateQrCode(): string
    {
        return 'ACCUSE-' . $this->numero_accuse . '-' . substr($this->hash_verification, 0, 8);
    }
}