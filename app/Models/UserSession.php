<?php
/**
 * MODÈLE USER SESSION - PNGDI
 * Système d'audit trail et gestion des sessions utilisateurs
 * Compatible PHP 7.3.29 - Laravel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // =================================================================
    // RELATIONS
    // =================================================================

    /**
     * Utilisateur de la session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =================================================================
    // SCOPES
    // =================================================================

    /**
     * Scope pour sessions actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour sessions d'un utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour sessions récentes
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('login_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope pour sessions par IP
     */
    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope pour sessions suspectes (même IP, utilisateurs différents)
     */
    public function scopeSuspicious($query, $timeframe = 60)
    {
        return $query->whereIn('ip_address', function($subQuery) use ($timeframe) {
            $subQuery->select('ip_address')
                     ->from('user_sessions')
                     ->where('login_at', '>=', now()->subMinutes($timeframe))
                     ->groupBy('ip_address')
                     ->havingRaw('COUNT(DISTINCT user_id) > 1');
        });
    }

    /**
     * Scope pour sessions de longue durée
     */
    public function scopeLongRunning($query, $hours = 24)
    {
        return $query->where('is_active', true)
                     ->where('login_at', '<=', now()->subHours($hours));
    }

    /**
     * Scope pour sessions par période
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('login_at', [$startDate, $endDate]);
    }

    // =================================================================
    // ACCESSEURS ET PROPRIÉTÉS CALCULÉES
    // =================================================================

    /**
     * Obtenir la durée de la session en minutes
     */
    public function getDurationAttribute()
    {
        $end = $this->logout_at ?: now();
        return $this->login_at->diffInMinutes($end);
    }

    /**
     * Obtenir la durée formatée
     */
    public function getFormattedDurationAttribute()
    {
        $duration = $this->duration;
        
        if ($duration < 60) {
            return $duration . ' min';
        } elseif ($duration < 1440) {
            $hours = floor($duration / 60);
            $minutes = $duration % 60;
            return $hours . 'h ' . $minutes . 'min';
        } else {
            $days = floor($duration / 1440);
            $hours = floor(($duration % 1440) / 60);
            return $days . 'j ' . $hours . 'h';
        }
    }

    /**
     * Obtenir le navigateur à partir du user agent
     */
    public function getBrowserAttribute()
    {
        $userAgent = $this->user_agent;
        
        if (preg_match('/Chrome\/[\d\.]+/', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Firefox\/[\d\.]+/', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Safari\/[\d\.]+/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Edge\/[\d\.]+/', $userAgent)) {
            return 'Edge';
        } elseif (preg_match('/Opera\/[\d\.]+/', $userAgent)) {
            return 'Opera';
        }
        
        return 'Inconnu';
    }

    /**
     * Obtenir la version du navigateur
     */
    public function getBrowserVersionAttribute()
    {
        $userAgent = $this->user_agent;
        $browser = $this->browser;
        
        $patterns = [
            'Chrome' => '/Chrome\/([\d\.]+)/',
            'Firefox' => '/Firefox\/([\d\.]+)/',
            'Safari' => '/Version\/([\d\.]+)/',
            'Edge' => '/Edge\/([\d\.]+)/',
            'Opera' => '/Opera\/([\d\.]+)/'
        ];
        
        if (isset($patterns[$browser]) && preg_match($patterns[$browser], $userAgent, $matches)) {
            return $matches[1];
        }
        
        return 'Inconnue';
    }

    /**
     * Obtenir le système d'exploitation
     */
    public function getOsAttribute()
    {
        $userAgent = $this->user_agent;
        
        if (preg_match('/Windows NT ([\d\.]+)/', $userAgent, $matches)) {
            $versions = [
                '10.0' => 'Windows 10',
                '6.3' => 'Windows 8.1',
                '6.2' => 'Windows 8',
                '6.1' => 'Windows 7',
                '6.0' => 'Windows Vista'
            ];
            return $versions[$matches[1]] ?? 'Windows ' . $matches[1];
        } elseif (preg_match('/Mac OS X ([\d_]+)/', $userAgent, $matches)) {
            return 'macOS ' . str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/', $userAgent)) {
            if (preg_match('/Ubuntu/', $userAgent)) {
                return 'Ubuntu';
            }
            return 'Linux';
        } elseif (preg_match('/Android ([\d\.]+)/', $userAgent, $matches)) {
            return 'Android ' . $matches[1];
        } elseif (preg_match('/iPhone OS ([\d_]+)/', $userAgent, $matches)) {
            return 'iOS ' . str_replace('_', '.', $matches[1]);
        }
        
        return 'Inconnu';
    }

    /**
     * Obtenir le type d'appareil
     */
    public function getDeviceTypeAttribute()
    {
        $userAgent = $this->user_agent;
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                return 'Tablette';
            }
            return 'Mobile';
        }
        
        return 'Desktop';
    }

    /**
     * Obtenir l'icône du navigateur (Font Awesome)
     */
    public function getBrowserIconAttribute()
    {
        $icons = [
            'Chrome' => 'fab fa-chrome',
            'Firefox' => 'fab fa-firefox',
            'Safari' => 'fab fa-safari',
            'Edge' => 'fab fa-edge',
            'Opera' => 'fab fa-opera'
        ];
        
        return $icons[$this->browser] ?? 'fas fa-globe';
    }

    /**
     * Obtenir l'icône du système d'exploitation
     */
    public function getOsIconAttribute()
    {
        $os = $this->os;
        
        if (strpos($os, 'Windows') !== false) {
            return 'fab fa-windows';
        } elseif (strpos($os, 'macOS') !== false) {
            return 'fab fa-apple';
        } elseif (strpos($os, 'Linux') !== false || strpos($os, 'Ubuntu') !== false) {
            return 'fab fa-linux';
        } elseif (strpos($os, 'Android') !== false) {
            return 'fab fa-android';
        } elseif (strpos($os, 'iOS') !== false) {
            return 'fab fa-apple';
        }
        
        return 'fas fa-desktop';
    }

    /**
     * Vérifier si la session est suspecte
     */
    public function getIsSuspiciousAttribute()
    {
        // Critères de suspicion
        $factors = [];
        
        // Durée excessive (plus de 12h)
        if ($this->is_active && $this->duration > 720) {
            $factors[] = 'long_duration';
        }
        
        // IP inhabituelle pour cet utilisateur
        $userUsualIps = self::forUser($this->user_id)
                           ->where('id', '!=', $this->id)
                           ->pluck('ip_address')
                           ->unique();
        
        if ($userUsualIps->count() > 0 && !$userUsualIps->contains($this->ip_address)) {
            $factors[] = 'unusual_ip';
        }
        
        // Connexion en dehors des heures normales (22h-6h)
        $hour = $this->login_at->hour;
        if ($hour >= 22 || $hour <= 6) {
            $factors[] = 'unusual_time';
        }
        
        return count($factors) >= 2;
    }

    /**
     * Obtenir le niveau de sécurité de la session
     */
    public function getSecurityLevelAttribute()
    {
        $score = 0;
        
        // User agent présent
        if (!empty($this->user_agent)) {
            $score += 20;
        }
        
        // IP locale (Gabon)
        if ($this->isGaboneseIp()) {
            $score += 30;
        }
        
        // Navigateur moderne
        if (in_array($this->browser, ['Chrome', 'Firefox', 'Safari', 'Edge'])) {
            $score += 20;
        }
        
        // Durée raisonnable
        if ($this->duration <= 480) { // 8 heures max
            $score += 20;
        }
        
        // Pas de flags suspects
        if (!$this->is_suspicious) {
            $score += 10;
        }
        
        if ($score >= 80) return 'high';
        if ($score >= 60) return 'medium';
        return 'low';
    }

    // =================================================================
    // MÉTHODES UTILITAIRES
    // =================================================================

    /**
     * Fermer la session
     */
    public function close()
    {
        $this->update([
            'logout_at' => now(),
            'is_active' => false
        ]);
    }

    /**
     * Vérifier si la session est expirée
     */
    public function isExpired($timeoutMinutes = 120)
    {
        if (!$this->is_active) {
            return true;
        }
        
        $lastActivity = $this->updated_at ?: $this->login_at;
        return $lastActivity->diffInMinutes(now()) > $timeoutMinutes;
    }

    /**
     * Marquer comme active (heartbeat)
     */
    public function heartbeat()
    {
        if ($this->is_active) {
            $this->touch();
        }
    }

    /**
     * Vérifier si l'IP est gabonaise
     */
    public function isGaboneseIp()
    {
        // Plages IP gabonaises approximatives
        $gabonRanges = [
            '41.158.0.0/15',    // Gabon Telecom
            '41.205.0.0/16',    // Airtel Gabon
            '196.11.240.0/20',  // Autres FAI gabonais
        ];
        
        foreach ($gabonRanges as $range) {
            if ($this->ipInRange($this->ip_address, $range)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Vérifier si une IP est dans une plage
     */
    private function ipInRange($ip, $range)
    {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) == $subnet;
    }

    /**
     * Obtenir la géolocalisation approximative
     */
    public function getLocationAttribute()
    {
        // Simulation basée sur l'IP (en production, utiliser un service de géolocalisation)
        if ($this->isGaboneseIp()) {
            return [
                'country' => 'Gabon',
                'city' => 'Libreville', // Approximation
                'region' => 'Estuaire'
            ];
        }
        
        return [
            'country' => 'Inconnu',
            'city' => 'Inconnu',
            'region' => 'Inconnu'
        ];
    }

    // =================================================================
    // MÉTHODES STATISTIQUES
    // =================================================================

    /**
     * Obtenir les statistiques de la session
     */
    public function getStatsAttribute()
    {
        return [
            'duration_minutes' => $this->duration,
            'is_active' => $this->is_active,
            'browser' => $this->browser,
            'os' => $this->os,
            'device_type' => $this->device_type,
            'is_suspicious' => $this->is_suspicious,
            'security_level' => $this->security_level,
            'is_gabonese_ip' => $this->isGaboneseIp(),
            'location' => $this->location
        ];
    }

    // =================================================================
    // MÉTHODES D'EXPORT ET FORMATAGE
    // =================================================================

    /**
     * Formatter pour l'export
     */
    public function toExportArray()
    {
        return [
            'ID' => $this->id,
            'Utilisateur' => $this->user->name ?? 'Inconnu',
            'Email' => $this->user->email ?? 'Inconnu',
            'IP' => $this->ip_address,
            'Navigateur' => $this->browser . ' ' . $this->browser_version,
            'OS' => $this->os,
            'Type appareil' => $this->device_type,
            'Connexion' => $this->login_at->format('d/m/Y H:i:s'),
            'Déconnexion' => $this->logout_at ? $this->logout_at->format('d/m/Y H:i:s') : 'En cours',
            'Durée' => $this->formatted_duration,
            'Statut' => $this->is_active ? 'Active' : 'Fermée',
            'Sécurité' => ucfirst($this->security_level),
            'Suspect' => $this->is_suspicious ? 'Oui' : 'Non',
            'Pays' => $this->location['country']
        ];
    }

    /**
     * Obtenir la représentation JSON pour l'API
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name ?? 'Inconnu',
            'ip_address' => $this->ip_address,
            'browser' => [
                'name' => $this->browser,
                'version' => $this->browser_version,
                'icon' => $this->browser_icon
            ],
            'os' => [
                'name' => $this->os,
                'icon' => $this->os_icon
            ],
            'device_type' => $this->device_type,
            'login_at' => $this->login_at->toISOString(),
            'logout_at' => $this->logout_at ? $this->logout_at->toISOString() : null,
            'duration_minutes' => $this->duration,
            'formatted_duration' => $this->formatted_duration,
            'is_active' => $this->is_active,
            'security_level' => $this->security_level,
            'is_suspicious' => $this->is_suspicious,
            'location' => $this->location,
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    // =================================================================
    // MÉTHODES STATIQUES UTILITAIRES
    // =================================================================

    /**
     * Obtenir les sessions actives par utilisateur
     */
    public static function getActiveByUser()
    {
        return self::active()
                   ->with('user')
                   ->get()
                   ->groupBy('user_id');
    }

    /**
     * Obtenir les statistiques globales des sessions
     */
    public static function getGlobalStats($period = 30)
    {
        $startDate = now()->subDays($period);
        
        return [
            'total_sessions' => self::where('login_at', '>=', $startDate)->count(),
            'active_sessions' => self::active()->count(),
            'unique_users' => self::where('login_at', '>=', $startDate)
                                  ->distinct('user_id')
                                  ->count(),
            'avg_duration' => self::where('login_at', '>=', $startDate)
                                  ->whereNotNull('logout_at')
                                  ->get()
                                  ->avg('duration'),
            'suspicious_sessions' => self::where('login_at', '>=', $startDate)
                                         ->get()
                                         ->filter(function($session) {
                                             return $session->is_suspicious;
                                         })
                                         ->count(),
            'browsers' => self::where('login_at', '>=', $startDate)
                              ->get()
                              ->groupBy('browser')
                              ->map(function($group) {
                                  return $group->count();
                              }),
            'countries' => self::where('login_at', '>=', $startDate)
                               ->get()
                               ->groupBy('location.country')
                               ->map(function($group) {
                                   return $group->count();
                               })
        ];
    }

    /**
     * Nettoyer les anciennes sessions
     */
    public static function cleanup($daysToKeep = 90)
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return self::where('login_at', '<', $cutoffDate)
                   ->where('is_active', false)
                   ->delete();
    }

    /**
     * Forcer la déconnexion de toutes les sessions d'un utilisateur
     */
    public static function forceLogoutUser($userId)
    {
        return self::where('user_id', $userId)
                   ->where('is_active', true)
                   ->update([
                       'logout_at' => now(),
                       'is_active' => false
                   ]);
    }

    /**
     * Obtenir les sessions suspectes récentes
     */
    public static function getRecentSuspicious($hours = 24)
    {
        return self::recent($hours)
                   ->get()
                   ->filter(function($session) {
                       return $session->is_suspicious;
                   });
    }
}