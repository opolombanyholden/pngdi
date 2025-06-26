<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TwoFactorCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
        'used',
        'used_at',
        'ip_address',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'used' => 'boolean'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour codes valides (non expirés et non utilisés)
     */
    public function scopeValid($query)
    {
        return $query->where('used', false)
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope pour codes expirés
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Vérifier si le code est valide
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    /**
     * Vérifier si le code est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Marquer le code comme utilisé
     */
    public function markAsUsed(): void
    {
        $this->update([
            'used' => true,
            'used_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Générer un nouveau code 2FA pour un utilisateur
     */
    public static function generateForUser(User $user): self
    {
        // Supprimer les anciens codes non utilisés
        $user->twoFactorCodes()->where('used', false)->delete();
        
        // Générer un nouveau code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        return self::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false
        ]);
    }

    /**
     * Vérifier et valider un code 2FA
     */
    public static function validateCode(User $user, string $code): bool
    {
        $twoFactorCode = self::where('user_id', $user->id)
            ->where('code', $code)
            ->valid()
            ->first();

        if ($twoFactorCode) {
            $twoFactorCode->markAsUsed();
            return true;
        }

        return false;
    }

    /**
     * Nettoyer les codes expirés
     */
    public static function cleanExpired(): int
    {
        return self::expired()->delete();
    }
}