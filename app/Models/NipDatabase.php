<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NipDatabase extends Model
{
    protected $table = 'nip_database';

    protected $fillable = [
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'nip',
        'sexe',
        'statut',
        'telephone',
        'email',
        'remarques',
        'source_import',
        'date_import',
        'imported_by',
        'last_verified_at'
    ];

    protected $dates = [
        'date_naissance',
        'date_import',
        'last_verified_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_import' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur qui a importé
     */
    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Validation d'un NIP selon le format XX-QQQQ-YYYYMMDD avec unicité
     */
    public static function validateNipFormat($nip, $excludeId = null)
    {
        // Format attendu: XX-QQQQ-YYYYMMDD (2 lettres - 4 chiffres - 8 chiffres pour date)
        $pattern = '/^[A-Z]{2}-\d{4}-\d{8}$/';
        
        if (!preg_match($pattern, $nip)) {
            return [
                'valid' => false,
                'error' => 'Format NIP invalide. Format attendu: XX-QQQQ-YYYYMMDD'
            ];
        }

        // Vérification d'unicité dans la base
        $query = self::where('nip', $nip);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        if ($query->exists()) {
            return [
                'valid' => false,
                'error' => 'Ce NIP existe déjà dans la base de données'
            ];
        }

        // Extraction de la date
        $datePart = substr($nip, -8);
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);

        // Validation de la date
        if (!checkdate($month, $day, $year)) {
            return [
                'valid' => false,
                'error' => 'Date de naissance invalide dans le NIP'
            ];
        }

        // Vérification que la date n'est pas future
        $birthDate = Carbon::createFromFormat('Y-m-d', "$year-$month-$day");
        if ($birthDate->isFuture()) {
            return [
                'valid' => false,
                'error' => 'Date de naissance ne peut pas être dans le futur'
            ];
        }

        return [
            'valid' => true,
            'date_naissance' => $birthDate,
            'age' => $birthDate->age
        ];
    }

    /**
     * Extraction des informations depuis le NIP
     */
    public static function extractInfoFromNip($nip)
    {
        $validation = self::validateNipFormat($nip);
        
        if (!$validation['valid']) {
            return $validation;
        }

        $datePart = substr($nip, -8);
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        $birthDate = Carbon::createFromFormat('Y-m-d', "$year-$month-$day");
        
        // Extraction du sexe (basé sur le chiffre des unités de l'année)
        $lastDigit = (int)substr($year, -1);
        $sexe = ($lastDigit % 2 === 0) ? 'F' : 'M';

        return [
            'valid' => true,
            'date_naissance' => $birthDate,
            'sexe' => $sexe,
            'age' => $birthDate->age,
            'prefix' => substr($nip, 0, 2),
            'sequence' => substr($nip, 3, 4)
        ];
    }

    /**
     * Vérifier si un NIP existe dans la base
     */
    public static function nipExists($nip)
    {
        return self::where('nip', $nip)->exists();
    }

    /**
     * Récupérer les informations d'un NIP
     */
    public static function getNipInfo($nip)
    {
        return self::where('nip', $nip)->first();
    }

    /**
     * Recherche de doublons potentiels
     */
    public static function findPotentialDuplicates($nom, $prenom, $dateNaissance)
    {
        return self::where('nom', 'LIKE', "%{$nom}%")
                   ->where('prenom', 'LIKE', "%{$prenom}%")
                   ->where('date_naissance', $dateNaissance)
                   ->get();
    }

    /**
     * Statistiques de la base NIP
     */
    public static function getStatistics()
    {
        return [
            'total' => self::count(),
            'actifs' => self::where('statut', 'actif')->count(),
            'inactifs' => self::where('statut', 'inactif')->count(),
            'decedes' => self::where('statut', 'decede')->count(),
            'suspendus' => self::where('statut', 'suspendu')->count(),
            'hommes' => self::where('sexe', 'M')->count(),
            'femmes' => self::where('sexe', 'F')->count(),
            'derniere_maj' => self::latest('updated_at')->value('updated_at')
        ];
    }

    /**
     * Scope pour les NIP actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour recherche
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nip', 'LIKE', "%{$search}%")
              ->orWhere('nom', 'LIKE', "%{$search}%")
              ->orWhere('prenom', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Mutateur pour formater le nom en majuscules
     */
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = strtoupper($value);
    }

    /**
     * Mutateur pour formater le prénom
     */
    public function setPrenomAttribute($value)
    {
        $this->attributes['prenom'] = ucwords(strtolower($value));
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getNomCompletAttribute()
    {
        return "{$this->nom} {$this->prenom}";
    }

    /**
     * Accesseur pour l'âge calculé
     */
    public function getAgeAttribute()
    {
        return $this->date_naissance ? $this->date_naissance->age : null;
    }
}