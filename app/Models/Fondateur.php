<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fondateur extends Model
{
    use HasFactory;

    /**
     * ✅ FILLABLE - Ajusté selon la structure DB réelle
     */
    protected $fillable = [
        // Identification
        'organisation_id',
        'nip',
        'nom',
        'prenom',
        
        // Informations personnelles (colonnes existantes dans la DB)
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        'telephone',
        'telephone_secondaire',    // ⭐ Colonne existante dans la DB
        'email',
        
        // Adresse complète (colonnes existantes dans la DB)
        'adresse_complete',
        'province',
        'departement',
        'canton',
        'prefecture',
        'sous_prefecture',
        'regroupement',
        'zone_type',
        'ville_commune',
        'arrondissement',
        'quartier',
        'village',
        'lieu_dit',
        
        // Documents et pièces d'identité (colonnes existantes dans la DB)
        'photo',
        'piece_identite',
        'type_piece',
        'numero_piece',
        
        // Fonction et ordre
        'fonction',
        'ordre',
        
        // Métadonnées (si cette colonne n'existe pas, elle sera ignorée)
        // 'metadata'  // ⚠️ À vérifier selon la structure DB
    ];

    /**
     * ✅ CASTS - Ajustés selon les colonnes DB
     */
    protected $casts = [
        'date_naissance' => 'date',
        'ordre' => 'integer',
        // 'metadata' => 'array',  // ⚠️ Seulement si colonne existe
    ];

    /**
     * ✅ CONSTANTES - Enrichies
     */
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';

    const ZONE_URBAINE = 'urbaine';
    const ZONE_RURALE = 'rurale';

    const PIECE_CNI = 'cni';
    const PIECE_PASSEPORT = 'passeport';
    const PIECE_CARTE_SEJOUR = 'carte_sejour';
    const PIECE_PERMIS_CONDUIRE = 'permis_conduire';
    
    // ⭐ NOUVELLES CONSTANTES - Fonctions de fondateur
    const FONCTION_PRESIDENT = 'Président';
    const FONCTION_VICE_PRESIDENT = 'Vice-Président';
    const FONCTION_SECRETAIRE_GENERAL = 'Secrétaire Général';
    const FONCTION_SECRETAIRE_ADJOINT = 'Secrétaire Adjoint';
    const FONCTION_TRESORIER = 'Trésorier';
    const FONCTION_TRESORIER_ADJOINT = 'Trésorier Adjoint';
    const FONCTION_COMMISSAIRE_COMPTES = 'Commissaire aux Comptes';
    const FONCTION_MEMBRE_BUREAU = 'Membre du Bureau';
    const FONCTION_FONDATEUR = 'Fondateur';

    /**
     * ✅ BOOT - Amélioré avec gestion des nouvelles colonnes
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fondateur) {
            // Définir l'ordre si non fourni
            if (empty($fondateur->ordre)) {
                $maxOrdre = self::where('organisation_id', $fondateur->organisation_id)->max('ordre');
                $fondateur->ordre = ($maxOrdre ?? 0) + 1;
            }
            
            // Définir fonction par défaut
            if (empty($fondateur->fonction)) {
                $fondateur->fonction = self::FONCTION_FONDATEUR;
            }
            
            // Définir nationalité par défaut
            if (empty($fondateur->nationalite)) {
                $fondateur->nationalite = 'Gabonaise';
            }
            
            // Définir zone_type par défaut
            if (empty($fondateur->zone_type)) {
                $fondateur->zone_type = self::ZONE_URBAINE;
            }
        });

        static::created(function ($fondateur) {
            // Créer automatiquement un adhérent correspondant
            self::createAdherentFromFondateur($fondateur);
        });

        static::updated(function ($fondateur) {
            // Mettre à jour l'adhérent correspondant
            self::updateAdherentFromFondateur($fondateur);
        });
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE - Créer un adhérent à partir d'un fondateur
     */
    protected static function createAdherentFromFondateur($fondateur): void
    {
        // Vérifier si l'adhérent n'existe pas déjà
        $existingAdherent = Adherent::where('organisation_id', $fondateur->organisation_id)
            ->where('nip', $fondateur->nip)
            ->first();

        if (!$existingAdherent) {
            // ✅ Données compatibles avec la nouvelle structure Adherent
            $adherentData = [
                'organisation_id' => $fondateur->organisation_id,
                'nip' => $fondateur->nip,
                'nom' => $fondateur->nom,
                'prenom' => $fondateur->prenom,
                'date_naissance' => $fondateur->date_naissance,
                'lieu_naissance' => $fondateur->lieu_naissance,
                'sexe' => $fondateur->sexe,
                'nationalite' => $fondateur->nationalite,
                'telephone' => $fondateur->telephone,
                'email' => $fondateur->email,
                'adresse_complete' => $fondateur->adresse_complete,
                'province' => $fondateur->province,
                'departement' => $fondateur->departement,
                'canton' => $fondateur->canton,
                'prefecture' => $fondateur->prefecture,
                'sous_prefecture' => $fondateur->sous_prefecture,
                'regroupement' => $fondateur->regroupement,
                'zone_type' => $fondateur->zone_type,
                'ville_commune' => $fondateur->ville_commune,
                'arrondissement' => $fondateur->arrondissement,
                'quartier' => $fondateur->quartier,
                'village' => $fondateur->village,
                'lieu_dit' => $fondateur->lieu_dit,
                'photo' => $fondateur->photo,
                'piece_identite' => $fondateur->piece_identite,
                'date_adhesion' => now(),
                'is_fondateur' => true,
                'is_active' => true,
                'fondateur_id' => $fondateur->id,
                
                // ⭐ NOUVELLES COLONNES - Profession et fonction
                'profession' => self::extractProfessionFromFonction($fondateur->fonction),
                'fonction' => $fondateur->fonction,
                
                // ⭐ HISTORIQUE JSON - Traçabilité
                'historique' => [
                    'creation' => now()->toISOString(),
                    'source' => 'fondateur_auto_creation',
                    'fondateur_id' => $fondateur->id,
                    'ordre_fondateur' => $fondateur->ordre,
                    'fonction_fondateur' => $fondateur->fonction,
                    'events' => [
                        [
                            'type' => 'creation_depuis_fondateur',
                            'date' => now()->toISOString(),
                            'data' => [
                                'fondateur_id' => $fondateur->id,
                                'fonction' => $fondateur->fonction
                            ]
                        ]
                    ]
                ]
            ];

            Adherent::create($adherentData);
        }
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE - Mettre à jour l'adhérent correspondant
     */
    protected static function updateAdherentFromFondateur($fondateur): void
    {
        $adherent = Adherent::where('organisation_id', $fondateur->organisation_id)
            ->where('nip', $fondateur->nip)
            ->first();

        if ($adherent) {
            $updateData = [
                'nom' => $fondateur->nom,
                'prenom' => $fondateur->prenom,
                'date_naissance' => $fondateur->date_naissance,
                'lieu_naissance' => $fondateur->lieu_naissance,
                'sexe' => $fondateur->sexe,
                'nationalite' => $fondateur->nationalite,
                'telephone' => $fondateur->telephone,
                'email' => $fondateur->email,
                'adresse_complete' => $fondateur->adresse_complete,
                'province' => $fondateur->province,
                'departement' => $fondateur->departement,
                'canton' => $fondateur->canton,
                'prefecture' => $fondateur->prefecture,
                'sous_prefecture' => $fondateur->sous_prefecture,
                'regroupement' => $fondateur->regroupement,
                'zone_type' => $fondateur->zone_type,
                'ville_commune' => $fondateur->ville_commune,
                'arrondissement' => $fondateur->arrondissement,
                'quartier' => $fondateur->quartier,
                'village' => $fondateur->village,
                'lieu_dit' => $fondateur->lieu_dit,
                'photo' => $fondateur->photo,
                'piece_identite' => $fondateur->piece_identite,
                
                // ⭐ NOUVELLES COLONNES
                'profession' => self::extractProfessionFromFonction($fondateur->fonction),
                'fonction' => $fondateur->fonction,
            ];

            $adherent->update($updateData);
            
            // ⭐ Ajouter événement à l'historique
            $adherent->addToHistorique('mise_a_jour_depuis_fondateur', [
                'fondateur_id' => $fondateur->id,
                'fonction' => $fondateur->fonction,
                'modifications' => array_keys($updateData)
            ]);
        }
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Extraire profession depuis fonction
     */
    protected static function extractProfessionFromFonction($fonction): ?string
    {
        // Mapping fonction → profession approximative
        $mapping = [
            self::FONCTION_PRESIDENT => 'Dirigeant d\'organisation',
            self::FONCTION_VICE_PRESIDENT => 'Dirigeant d\'organisation',
            self::FONCTION_SECRETAIRE_GENERAL => 'Secrétaire',
            self::FONCTION_TRESORIER => 'Comptable/Gestionnaire',
            self::FONCTION_COMMISSAIRE_COMPTES => 'Expert-comptable',
        ];
        
        return $mapping[$fonction] ?? 'Membre actif d\'organisation';
    }

    /**
     * ✅ RELATIONS - Enrichies
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * ⭐ NOUVELLE RELATION - Adhérent correspondant (relation formelle)
     */
    public function adherent(): HasOne
    {
        return $this->hasOne(Adherent::class, 'fondateur_id');
    }

    /**
     * ⭐ MÉTHODE ALTERNATIVE - Trouver adhérent par NIP (plus flexible)
     */
    public function getAdherentByNip()
    {
        return Adherent::where('organisation_id', $this->organisation_id)
            ->where('nip', $this->nip)
            ->first();
    }

    /**
     * ✅ SCOPES - Enrichis
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    public function scopeByNip($query, $nip)
    {
        return $query->where('nip', $nip);
    }

    /**
     * ⭐ NOUVEAUX SCOPES
     */
    public function scopeByFonction($query, $fonction)
    {
        return $query->where('fonction', $fonction);
    }

    public function scopeResponsables($query)
    {
        return $query->whereIn('fonction', [
            self::FONCTION_PRESIDENT,
            self::FONCTION_VICE_PRESIDENT,
            self::FONCTION_SECRETAIRE_GENERAL,
            self::FONCTION_TRESORIER
        ]);
    }

    public function scopeDirigeants($query)
    {
        return $query->whereIn('fonction', [
            self::FONCTION_PRESIDENT,
            self::FONCTION_VICE_PRESIDENT
        ]);
    }

    public function scopeByProvince($query, $province)
    {
        return $query->where('province', $province);
    }

    public function scopeHommes($query)
    {
        return $query->where('sexe', self::SEXE_MASCULIN);
    }

    public function scopeFemmes($query)
    {
        return $query->where('sexe', self::SEXE_FEMININ);
    }

    /**
     * ✅ ACCESSEURS - Enrichis
     */
    public function getNomCompletAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    public function getSexeLabelAttribute(): string
    {
        return $this->sexe === self::SEXE_MASCULIN ? 'Masculin' : 'Féminin';
    }

    public function getPieceIdentiteTypeLabelAttribute(): string
    {
        $labels = [
            self::PIECE_CNI => 'Carte Nationale d\'Identité',
            self::PIECE_PASSEPORT => 'Passeport',
            self::PIECE_CARTE_SEJOUR => 'Carte de Séjour',
            self::PIECE_PERMIS_CONDUIRE => 'Permis de Conduire'
        ];

        return $labels[$this->type_piece] ?? $this->type_piece;
    }

    public function getAdresseCompleteFormatteeAttribute(): string
    {
        $parts = array_filter([
            $this->adresse_complete,
            $this->quartier,
            $this->arrondissement,
            $this->ville_commune,
            $this->village,
            $this->sous_prefecture,
            $this->prefecture,
            $this->departement,
            $this->province
        ]);

        return implode(', ', $parts);
    }

    public function getAge(): int
    {
        return $this->date_naissance ? $this->date_naissance->age : 0;
    }

    /**
     * ⭐ NOUVEAUX ACCESSEURS
     */
    public function getFonctionLabelAttribute(): string
    {
        return $this->fonction ?? self::FONCTION_FONDATEUR;
    }

    public function getIsResponsableAttribute(): bool
    {
        return in_array($this->fonction, [
            self::FONCTION_PRESIDENT,
            self::FONCTION_VICE_PRESIDENT,
            self::FONCTION_SECRETAIRE_GENERAL,
            self::FONCTION_TRESORIER
        ]);
    }

    public function getIsDirigeantAttribute(): bool
    {
        return in_array($this->fonction, [
            self::FONCTION_PRESIDENT,
            self::FONCTION_VICE_PRESIDENT
        ]);
    }

    public function getOrdreFormatteAttribute(): string
    {
        return str_pad($this->ordre, 2, '0', STR_PAD_LEFT);
    }

    public function getStatutAttribute(): string
    {
        if ($this->is_responsable) {
            return 'Responsable';
        } elseif ($this->is_dirigeant) {
            return 'Dirigeant';
        } else {
            return 'Fondateur';
        }
    }

    /**
     * ✅ MÉTHODES UTILITAIRES - Améliorées
     */
    public function canBeResponsable(): bool
    {
        return $this->getAge() >= 18 && $this->hasCompleteInformation();
    }

    public function hasCompleteInformation(): bool
    {
        $requiredFields = [
            'nip', 'nom', 'prenom', 'date_naissance', 'lieu_naissance',
            'sexe', 'nationalite', 'fonction', 'adresse_complete', 'telephone',
            'type_piece', 'numero_piece'
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Promouvoir à une fonction
     */
    public function promoteToFonction($nouvelleFonction): bool
    {
        $ancienneFonction = $this->fonction;
        
        // Vérifier que c'est une fonction valide
        if (!in_array($nouvelleFonction, self::getFonctionsDisponibles())) {
            throw new \Exception("Fonction invalide: {$nouvelleFonction}");
        }
        
        // Pour certaines fonctions uniques, retirer des autres
        $fonctionsUniques = [
            self::FONCTION_PRESIDENT,
            self::FONCTION_SECRETAIRE_GENERAL,
            self::FONCTION_TRESORIER
        ];
        
        if (in_array($nouvelleFonction, $fonctionsUniques)) {
            self::where('organisation_id', $this->organisation_id)
                ->where('id', '!=', $this->id)
                ->where('fonction', $nouvelleFonction)
                ->update(['fonction' => self::FONCTION_FONDATEUR]);
        }
        
        $this->update(['fonction' => $nouvelleFonction]);
        
        // Mettre à jour l'adhérent correspondant
        $adherent = $this->getAdherentByNip();
        if ($adherent) {
            $adherent->update([
                'fonction' => $nouvelleFonction,
                'profession' => self::extractProfessionFromFonction($nouvelleFonction)
            ]);
            
            $adherent->addToHistorique('promotion_fonction', [
                'ancienne_fonction' => $ancienneFonction,
                'nouvelle_fonction' => $nouvelleFonction,
                'fondateur_id' => $this->id
            ]);
        }
        
        return true;
    }

    /**
     * ✅ VALIDATION - Améliorée
     */
    public function validate(): array
    {
        $errors = [];

        // Vérifier l'âge minimum
        if ($this->getAge() < 18) {
            $errors['age'] = 'Le fondateur doit avoir au moins 18 ans';
        }

        // Vérifier l'unicité du NIP
        if (!self::isNipUniqueInOrganisation($this->nip, $this->organisation_id, $this->id)) {
            $errors['nip'] = 'Ce NIP est déjà utilisé par un autre fondateur de cette organisation';
        }

        // Vérifier les informations complètes
        if (!$this->hasCompleteInformation()) {
            $errors['information'] = 'Toutes les informations obligatoires doivent être renseignées';
        }

        // ⭐ Vérifier unicité des fonctions importantes
        $fonctionsUniques = [self::FONCTION_PRESIDENT, self::FONCTION_SECRETAIRE_GENERAL, self::FONCTION_TRESORIER];
        
        if (in_array($this->fonction, $fonctionsUniques)) {
            $existing = self::where('organisation_id', $this->organisation_id)
                ->where('fonction', $this->fonction)
                ->where('id', '!=', $this->id)
                ->exists();
                
            if ($existing) {
                $errors['fonction'] = "Il ne peut y avoir qu'un seul {$this->fonction} par organisation";
            }
        }

        // ⭐ Vérifier qu'il y a au moins un président
        if ($this->wasRecentlyCreated || $this->isDirty('fonction')) {
            $hasPresident = self::where('organisation_id', $this->organisation_id)
                ->where('fonction', self::FONCTION_PRESIDENT)
                ->when($this->exists, function($query) {
                    return $query->where('id', '!=', $this->id);
                })
                ->exists();

            if (!$hasPresident && $this->fonction !== self::FONCTION_PRESIDENT) {
                $errors['president'] = 'Au moins un fondateur doit être désigné comme Président';
            }
        }

        return $errors;
    }

    /**
     * ✅ MÉTHODES STATIQUES - Enrichies
     */
    public static function isNipUniqueInOrganisation($nip, $organisationId, $excludeId = null): bool
    {
        $query = self::where('nip', $nip)
            ->where('organisation_id', $organisationId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    public static function getPieceIdentiteTypes(): array
    {
        return [
            self::PIECE_CNI => 'Carte Nationale d\'Identité',
            self::PIECE_PASSEPORT => 'Passeport',
            self::PIECE_CARTE_SEJOUR => 'Carte de Séjour',
            self::PIECE_PERMIS_CONDUIRE => 'Permis de Conduire'
        ];
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Fonctions disponibles
     */
    public static function getFonctionsDisponibles(): array
    {
        return [
            self::FONCTION_PRESIDENT,
            self::FONCTION_VICE_PRESIDENT,
            self::FONCTION_SECRETAIRE_GENERAL,
            self::FONCTION_SECRETAIRE_ADJOINT,
            self::FONCTION_TRESORIER,
            self::FONCTION_TRESORIER_ADJOINT,
            self::FONCTION_COMMISSAIRE_COMPTES,
            self::FONCTION_MEMBRE_BUREAU,
            self::FONCTION_FONDATEUR
        ];
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Fonctions obligatoires
     */
    public static function getFonctionsObligatoires(): array
    {
        return [
            self::FONCTION_PRESIDENT,
            self::FONCTION_SECRETAIRE_GENERAL,
            self::FONCTION_TRESORIER
        ];
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE - Nombre minimum de fondateurs
     */
    public static function getMinimumRequired($typeOrganisation): int
    {
        $minimums = [
            'association' => 1,         // ⭐ Ajusté selon les nouvelles règles
            'ong' => 1,
            'parti_politique' => 1,     // ⭐ Utiliser les vraies valeurs enum
            'confession_religieuse' => 1,
            'syndicat' => 1,
            'organisation_patronale' => 1
        ];

        return $minimums[$typeOrganisation] ?? 1;
    }

    /**
     * ✅ VÉRIFICATION - Améliorée
     */
    public static function hasMinimumFondateurs($organisationId): bool
    {
        $organisation = Organisation::find($organisationId);
        if (!$organisation) {
            return false;
        }

        $count = self::where('organisation_id', $organisationId)->count();
        $minimum = self::getMinimumRequired($organisation->type);

        return $count >= $minimum;
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Vérifier structure complète organisation
     */
    public static function hasCompleteStructure($organisationId): array
    {
        $results = [
            'has_minimum' => false,
            'has_president' => false,
            'has_secretaire' => false,
            'has_tresorier' => false,
            'complete' => false,
            'missing' => []
        ];

        $fondateurs = self::where('organisation_id', $organisationId)->get();
        
        // Vérifier nombre minimum
        $organisation = Organisation::find($organisationId);
        $minimum = self::getMinimumRequired($organisation->type ?? 'association');
        $results['has_minimum'] = $fondateurs->count() >= $minimum;
        
        if (!$results['has_minimum']) {
            $results['missing'][] = "Minimum {$minimum} fondateurs requis (" . $fondateurs->count() . " actuellement)";
        }

        // Vérifier fonctions obligatoires
        $fonctions = $fondateurs->pluck('fonction')->toArray();
        
        $results['has_president'] = in_array(self::FONCTION_PRESIDENT, $fonctions);
        $results['has_secretaire'] = in_array(self::FONCTION_SECRETAIRE_GENERAL, $fonctions);
        $results['has_tresorier'] = in_array(self::FONCTION_TRESORIER, $fonctions);

        if (!$results['has_president']) {
            $results['missing'][] = 'Un Président doit être désigné';
        }
        if (!$results['has_secretaire']) {
            $results['missing'][] = 'Un Secrétaire Général doit être désigné';
        }
        if (!$results['has_tresorier']) {
            $results['missing'][] = 'Un Trésorier doit être désigné';
        }

        $results['complete'] = $results['has_minimum'] && 
                              $results['has_president'] && 
                              $results['has_secretaire'] && 
                              $results['has_tresorier'];

        return $results;
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Statistiques fondateurs
     */
    public static function getStatistiques($organisationId): array
    {
        $fondateurs = self::where('organisation_id', $organisationId)->get();
        
        return [
            'total' => $fondateurs->count(),
            'hommes' => $fondateurs->where('sexe', self::SEXE_MASCULIN)->count(),
            'femmes' => $fondateurs->where('sexe', self::SEXE_FEMININ)->count(),
            'responsables' => $fondateurs->filter(function($f) { return $f->is_responsable; })->count(),
            'dirigeants' => $fondateurs->filter(function($f) { return $f->is_dirigeant; })->count(),
            'age_moyen' => round($fondateurs->avg(function($f) { return $f->getAge(); }), 1),
            'provinces' => $fondateurs->pluck('province')->unique()->count(),
            'fonctions' => $fondateurs->pluck('fonction')->unique()->values()->toArray(),
            'structure_complete' => self::hasCompleteStructure($organisationId)
        ];
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Export pour rapports
     */
    public function toExportArray(): array
    {
        return [
            'Ordre' => $this->ordre_formatte,
            'NIP' => $this->nip,
            'Nom complet' => $this->nom_complet,
            'Fonction' => $this->fonction_label,
            'Statut' => $this->statut,
            'Sexe' => $this->sexe_label,
            'Âge' => $this->getAge(),
            'Téléphone' => $this->telephone,
            'Email' => $this->email,
            'Province' => $this->province,
            'Adresse' => $this->adresse_complete_formattee,
        ];
    }
}