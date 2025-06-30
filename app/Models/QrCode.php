<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'entity_type', 'entity_id', 'data',
        'expires_at', 'is_active', 'generated_by',
        'verification_count', 'last_verified_at'
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'last_verified_at' => 'datetime'
    ];

    const TYPE_ORGANISATION = 'organisation';
    const TYPE_DOSSIER = 'dossier';
    const TYPE_ADHERENT = 'adherent';
    const TYPE_DOCUMENT = 'document';
    const TYPE_ACCUSE = 'accuse';

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function markAsVerified(): void
    {
        $this->increment('verification_count');
        $this->update(['last_verified_at' => now()]);
    }

    public static function generateUniqueCode(int $length = 12): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public static function createForEntity($entity, string $type, array $additionalData = []): self
    {
        return self::create([
            'code' => self::generateUniqueCode(),
            'type' => $type,
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'data' => array_merge([
                'entity_name' => $entity->nom ?? $entity->name ?? 'Entité',
                'generated_at' => now()->toISOString()
            ], $additionalData),
            'is_active' => true,
            'generated_by' => auth()->id(),
            'verification_count' => 0
        ]);
    }

    public static function validateCode(string $code, string $expectedType = null): array
    {
        $qrCode = self::active()->notExpired()->where('code', $code)
            ->with(['entity', 'generatedBy'])->first();

        if (!$qrCode) {
            return ['valid' => false, 'message' => 'Code QR invalide ou expiré'];
        }

        if ($expectedType && $qrCode->type !== $expectedType) {
            return ['valid' => false, 'message' => 'Type de code QR incorrect'];
        }

        $qrCode->markAsVerified();

        return [
            'valid' => true,
            'qr_code' => $qrCode,
            'entity' => $qrCode->entity,
            'data' => $qrCode->data,
            'verification_count' => $qrCode->verification_count,
            'message' => 'Code QR vérifié avec succès'
        ];
    }
}