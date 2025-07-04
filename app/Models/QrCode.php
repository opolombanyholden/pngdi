<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'verifiable_type',
        'verifiable_id',
        'document_numero',
        'donnees_verification',
        'hash_verification',
        'nombre_verifications',
        'derniere_verification',
        'expire_at',
        'is_active'
    ];

    protected $casts = [
        'donnees_verification' => 'array',
        'expire_at' => 'datetime',
        'derniere_verification' => 'datetime',
        'is_active' => 'boolean',
        'nombre_verifications' => 'integer'
    ];

    // Types de QR codes
    const TYPE_ORGANISATION = 'organisation_verification';
    const TYPE_DOSSIER = 'dossier_verification';
    const TYPE_ADHERENT = 'adherent_verification';
    const TYPE_DOCUMENT = 'document_verification';
    const TYPE_ACCUSE = 'accuse_verification';

    /**
     * Relation polymorphe vers l'entité vérifiable
     */
    public function verifiable()
    {
        return $this->morphTo();
    }

    /**
     * Scope pour les QR codes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les QR codes non expirés
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expire_at')->orWhere('expire_at', '>', now());
        });
    }

    /**
     * Vérifie si le QR code est expiré
     */
    public function isExpired(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }

    /**
     * Vérifie si le QR code est valide
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Marque le QR code comme vérifié
     */
    public function markAsVerified(): void
    {
        $this->increment('nombre_verifications');
        $this->update(['derniere_verification' => now()]);
    }

    /**
     * Génère un code unique
     */
    public static function generateUniqueCode(int $length = 20): string
    {
        do {
            $code = 'QR-' . strtoupper(substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(15))), 0, $length - 3));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Génère le hash de vérification
     */
    public static function generateVerificationHash(array $data): string
    {
        return hash('sha256', json_encode($data) . config('app.key'));
    }

    /**
     * Crée un QR code pour une entité
     */
    public static function createForEntity($entity, string $type, array $additionalData = [], string $documentNumero = null): self
    {
        $baseData = [
            'entity_name' => $entity->nom ?? $entity->name ?? 'Entité',
            'entity_id' => $entity->id,
            'generated_at' => now()->toISOString(),
            'type' => $type
        ];

        $verificationData = array_merge($baseData, $additionalData);
        $hash = self::generateVerificationHash($verificationData);

        return self::create([
            'code' => self::generateUniqueCode(),
            'type' => $type,
            'verifiable_type' => get_class($entity),
            'verifiable_id' => $entity->id,
            'document_numero' => $documentNumero,
            'donnees_verification' => $verificationData,
            'hash_verification' => $hash,
            'is_active' => true,
            'nombre_verifications' => 0
        ]);
    }

    /**
     * Crée un QR code pour un dossier
     */
    public static function createForDossier($dossier, array $additionalData = []): self
    {
        return self::createForEntity(
            $dossier,
            self::TYPE_DOSSIER,
            array_merge([
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Organisation',
                'phase' => $dossier->phase,
                'statut' => $dossier->statut
            ], $additionalData),
            $dossier->numero_dossier
        );
    }

    /**
     * Crée un QR code pour un accusé de réception
     */
    public static function createForAccuse($dossier, string $filename, array $additionalData = []): self
    {
        return self::createForEntity(
            $dossier,
            self::TYPE_ACCUSE,
            array_merge([
                'document_type' => 'accuse_reception',
                'filename' => $filename,
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Organisation',
                'phase' => $dossier->phase
            ], $additionalData),
            $filename
        );
    }

    /**
     * Valide un code QR
     */
    public static function validateCode(string $code, string $expectedType = null): array
    {
        $qrCode = self::active()->notExpired()->where('code', $code)
            ->with('verifiable')->first();

        if (!$qrCode) {
            return [
                'valid' => false,
                'message' => 'Code QR invalide ou expiré'
            ];
        }

        if ($expectedType && $qrCode->type !== $expectedType) {
            return [
                'valid' => false,
                'message' => 'Type de code QR incorrect'
            ];
        }

        // Vérification de l'intégrité
        $expectedHash = self::generateVerificationHash($qrCode->donnees_verification);
        if ($qrCode->hash_verification !== $expectedHash) {
            return [
                'valid' => false,
                'message' => 'Code QR compromis - Intégrité non vérifiée'
            ];
        }

        $qrCode->markAsVerified();

        return [
            'valid' => true,
            'qr_code' => $qrCode,
            'entity' => $qrCode->verifiable,
            'data' => $qrCode->donnees_verification,
            'verification_count' => $qrCode->nombre_verifications,
            'last_verified' => $qrCode->derniere_verification,
            'message' => 'Code QR vérifié avec succès'
        ];
    }

    /**
     * Récupère les informations de vérification
     */
    public function getVerificationInfo(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'document_numero' => $this->document_numero,
            'data' => $this->donnees_verification,
            'verified_count' => $this->nombre_verifications,
            'last_verified' => $this->derniere_verification,
            'is_valid' => $this->isValid(),
            'entity' => $this->verifiable
        ];
    }
}