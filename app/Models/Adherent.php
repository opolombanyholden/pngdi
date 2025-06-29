<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Adherent extends Model
{
    use HasFactory;

    /**
     * ✅ FILLABLE - Mis à jour avec toutes les nouvelles colonnes de la migration
     */
    protected $fillable = [
        // Identification de base
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
        'email',
        
        // ✅ NOUVELLES COLONNES AJOUTÉES PAR LA MIGRATION
        'profession',           // ⭐ Ajoutée par migration
        'fonction',             // ⭐ Ajoutée par migration (default 'Membre')
        
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
        
        // Documents et photos
        'photo',
        'piece_identite',
        
        // Dates importantes
        'date_adhesion',
        'date_exclusion',
        'motif_exclusion',
        
        // Statuts et relations
        'is_fondateur',
        'is_active',
        'fondateur_id',
        
        // ✅ HISTORIQUE - Colonne JSON existante dans la DB
        'historique'            // ⭐ Colonne JSON pour stocker l'historique
    ];

    /**
     * ✅ CASTS - Mis à jour avec les nouveaux types
     */
    protected $casts = [
        'date_naissance' => 'date',
        'date_adhesion' => 'date',
        'date_exclusion' => 'date',
        'is_fondateur' => 'boolean',
        'is_active' => 'boolean',
        'historique' => 'array',    // ⭐ Cast JSON en array
    ];

    /**
     * ✅ CONSTANTES - Enrichies
     */
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';
    
    // ⭐ NOUVELLES CONSTANTES POUR FONCTIONS
    const FONCTION_MEMBRE = 'Membre';
    const FONCTION_PRESIDENT = 'Président';
    const FONCTION_VICE_PRESIDENT = 'Vice-Président';
    const FONCTION_SECRETAIRE_GENERAL = 'Secrétaire Général';
    const FONCTION_TRESORIER = 'Trésorier';
    const FONCTION_COMMISSAIRE = 'Commissaire aux Comptes';
    
    // ⭐ PROFESSIONS EXCLUES POUR PARTIS POLITIQUES
    const PROFESSIONS_EXCLUES_PARTIS = [
        'Magistrat', 'Juge', 'Procureur', 'Commissaire de police',
        'Officier de police judiciaire', 'Militaire en activité',
        'Gendarme en activité', 'Fonctionnaire de la sécurité d\'État',
        'Agent des services de renseignement', 'Diplomate en mission',
        'Gouverneur de province', 'Préfet', 'Sous-préfet', 'Maire en exercice',
        'Membre du Conseil constitutionnel', 'Membre de la Cour de cassation',
        'Membre du Conseil d\'État', 'Contrôleur général d\'État',
        'Inspecteur général d\'État', 'Agent comptable de l\'État',
        'Trésorier payeur général', 'Receveur des finances'
    ];

    /**
     * ✅ BOOT - Enrichi avec gestion des nouvelles colonnes
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adherent) {
            // Définir fonction par défaut si non spécifiée
            if (empty($adherent->fonction)) {
                $adherent->fonction = self::FONCTION_MEMBRE;
            }
            
            // Vérifier profession exclue pour parti politique
            if ($adherent->organisation && $adherent->organisation->isPartiPolitique()) {
                self::verifyProfessionForPartiPolitique($adherent);
                self::verifyUnicityForPartiPolitique($adherent);
            }
            
            // ✅ CORRECTION - Initialiser l'historique correctement
            if (empty($adherent->historique)) {
                $adherent->historique = [
                    'creation' => now()->toISOString(),
                    'source' => 'creation_manuelle',
                    'events' => []
                ];
            }
        });

        static::created(function ($adherent) {
            // ✅ CORRECTION - Utiliser try-catch pour éviter les erreurs fatales
            try {
                // Ajouter l'événement d'adhésion dans l'historique
                $adherent->addToHistorique('adhesion', [
                    'date' => $adherent->date_adhesion ?? now(),
                    'organisation_id' => $adherent->organisation_id,
                    'profession' => $adherent->profession,
                    'fonction' => $adherent->fonction
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'ajout à l\'historique JSON: ' . $e->getMessage());
                // Continue sans faire échouer la création
            }
            
            // ✅ CORRECTION - Créer l'historique dans la table dédiée si elle existe
            try {
                if (class_exists('App\Models\AdherentHistory')) {
                    \App\Models\AdherentHistory::create([
                        'adherent_id' => $adherent->id,
                        'organisation_id' => $adherent->organisation_id,
                        'type_mouvement' => 'adhesion',
                        'motif' => 'Adhésion initiale - Profession: ' . ($adherent->profession ?? 'Non renseignée'),
                        'date_effet' => $adherent->date_adhesion ?? now(),
                        'created_by' => auth()->id() ?? 1
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la création de l\'historique dédié: ' . $e->getMessage());
                // Continue sans faire échouer la création
            }
        });
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Vérifier profession exclue pour parti politique
     */
    protected static function verifyProfessionForPartiPolitique($adherent)
    {
        if (empty($adherent->profession)) {
            return; // Pas de profession spécifiée, on laisse passer
        }
        
        $professionLower = strtolower($adherent->profession);
        $exclusLower = array_map('strtolower', self::PROFESSIONS_EXCLUES_PARTIS);
        
        if (in_array($professionLower, $exclusLower)) {
            throw new \Exception(
                "La profession '{$adherent->profession}' est incompatible avec l'adhésion à un parti politique selon la législation gabonaise."
            );
        }
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE - Vérifier l'unicité pour les partis politiques
     */
    protected static function verifyUnicityForPartiPolitique($adherent)
    {
        $existingAdhesion = self::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
            ->where('adherents.nip', $adherent->nip)
            ->where('adherents.is_active', true)
            ->where('organisations.type', 'parti_politique') // ⭐ Utiliser la valeur enum réelle
            ->where('adherents.organisation_id', '!=', $adherent->organisation_id)
            ->first();

        if ($existingAdhesion) {
            throw new \Exception(
                "Cette personne (NIP: {$adherent->nip}) est déjà membre actif du parti politique '{$existingAdhesion->nom}'. " .
                "Une exclusion formelle est requise avant de pouvoir adhérer à un autre parti."
            );
        }
    }

    /**
     * ✅ CORRECTION PRINCIPALE - Méthode addToHistorique sécurisée
     */
    public function addToHistorique($type, $data = [])
    {
        try {
            // ✅ S'assurer que historique est toujours un array valide
            $currentHistorique = $this->historique;
            
            // Gérer les différents cas : null, string, array
            if (is_null($currentHistorique)) {
                $historique = ['events' => []];
            } elseif (is_string($currentHistorique)) {
                // Tenter de decoder le JSON, sinon initialiser
                $decoded = json_decode($currentHistorique, true);
                $historique = is_array($decoded) ? $decoded : ['events' => []];
            } elseif (is_array($currentHistorique)) {
                $historique = $currentHistorique;
            } else {
                // Type inattendu, réinitialiser
                $historique = ['events' => []];
            }
            
            // ✅ S'assurer que la clé 'events' existe et est un array
            if (!isset($historique['events']) || !is_array($historique['events'])) {
                $historique['events'] = [];
            }
            
            // ✅ Ajouter le nouvel événement
            $historique['events'][] = [
                'type' => $type,
                'date' => now()->toISOString(),
                'data' => $data,
                'user_id' => auth()->id()
            ];
            
            // ✅ Sauvegarder en utilisant update plutôt que save pour éviter les événements récursifs
            $this->updateQuietly(['historique' => $historique]);
            
        } catch (\Exception $e) {
            // ✅ Logger l'erreur mais ne pas faire échouer l'opération
            \Log::error('Erreur dans addToHistorique: ' . $e->getMessage(), [
                'adherent_id' => $this->id,
                'type' => $type,
                'data' => $data,
                'current_historique' => $this->historique
            ]);
        }
    }

    /**
     * ✅ RELATIONS - Inchangées
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AdherentHistory::class);
    }

    public function exclusion(): HasOne
    {
        return $this->hasOne(AdherentExclusion::class)->latest();
    }

    public function imports(): HasMany
    {
        return $this->hasMany(AdherentImport::class);
    }
    
    /**
     * ⭐ NOUVELLE RELATION - Lien vers le fondateur si is_fondateur
     */
    public function fondateur(): BelongsTo
    {
        return $this->belongsTo(Fondateur::class, 'fondateur_id');
    }

    /**
     * ✅ SCOPES - Enrichis avec nouveaux scopes
     */
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactifs($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeFondateurs($query)
    {
        return $query->where('is_fondateur', true);
    }

    public function scopeByNip($query, $nip)
    {
        return $query->where('nip', $nip);
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
     * ⭐ NOUVEAUX SCOPES - Pour profession et fonction
     */
    public function scopeByProfession($query, $profession)
    {
        return $query->where('profession', $profession);
    }
    
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
    
    public function scopeWithProfessionExclue($query)
    {
        return $query->whereIn('profession', self::PROFESSIONS_EXCLUES_PARTIS);
    }

    /**
     * ✅ MÉTHODES UTILITAIRES - Enrichies
     */
    public function isExcluded(): bool
    {
        return !$this->is_active || $this->date_exclusion !== null;
    }

    public function canBeTransferred(): bool
    {
        return !$this->is_fondateur && $this->is_active && !$this->isExcluded();
    }

    public function getAge(): int
    {
        return $this->date_naissance ? $this->date_naissance->age : 0;
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    public function getSexeLabelAttribute(): string
    {
        return $this->sexe === self::SEXE_MASCULIN ? 'Masculin' : 'Féminin';
    }
    
    /**
     * ⭐ NOUVEAUX ACCESSORS - Pour profession et fonction
     */
    public function getProfessionLabelAttribute(): string
    {
        return $this->profession ?? 'Non renseignée';
    }
    
    public function getFonctionLabelAttribute(): string
    {
        return $this->fonction ?? self::FONCTION_MEMBRE;
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
    
    public function getHasProfessionExclueAttribute(): bool
    {
        if (empty($this->profession)) {
            return false;
        }
        
        return in_array(strtolower($this->profession), 
            array_map('strtolower', self::PROFESSIONS_EXCLUES_PARTIS)
        );
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE - Exclure l'adhérent
     */
    public function exclude($motif, $dateExclusion = null, $documentPath = null)
    {
        $dateExclusion = $dateExclusion ?? now();
        
        // Marquer l'adhérent comme inactif
        $this->update([
            'is_active' => false,
            'date_exclusion' => $dateExclusion,
            'motif_exclusion' => $motif
        ]);

        // Ajouter à l'historique JSON
        $this->addToHistorique('exclusion', [
            'motif' => $motif,
            'date_exclusion' => $dateExclusion,
            'document_path' => $documentPath
        ]);

        // Créer l'enregistrement d'exclusion si la table existe
        if (class_exists('App\Models\AdherentExclusion')) {
            \App\Models\AdherentExclusion::create([
                'adherent_id' => $this->id,
                'organisation_id' => $this->organisation_id,
                'type_exclusion' => 'exclusion_disciplinaire',
                'motif_detaille' => $motif,
                'date_decision' => $dateExclusion,
                'document_decision' => $documentPath,
                'validated_by' => auth()->id() ?? 1
            ]);
        }

        return true;
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE - Réactiver l'adhérent
     */
    public function reactivate($motif = 'Réactivation')
    {
        $this->update([
            'is_active' => true,
            'date_exclusion' => null,
            'motif_exclusion' => null
        ]);

        $this->addToHistorique('reactivation', [
            'motif' => $motif,
            'date_reactivation' => now()
        ]);

        return true;
    }

    /**
     * ✅ MÉTHODE VÉRIFICATION - Mise à jour pour nouvelles contraintes
     */
    public static function canJoinOrganisation($nip, $organisationId): array
    {
        $organisation = Organisation::find($organisationId);
        
        if (!$organisation) {
            return ['can_join' => false, 'reason' => 'Organisation non trouvée'];
        }

        // Pour les partis politiques
        if ($organisation->type === 'parti_politique') {
            $existingInParti = self::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
                ->where('adherents.nip', $nip)
                ->where('adherents.is_active', true)
                ->where('organisations.type', 'parti_politique')
                ->first();

            if ($existingInParti) {
                return [
                    'can_join' => false,
                    'reason' => "Déjà membre du parti politique '{$existingInParti->nom}'",
                    'existing_organisation' => $existingInParti
                ];
            }
        }

        return ['can_join' => true];
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Détecter les anomalies
     */
    public function detectAnomalies(): array
    {
        $anomalies = [
            'critiques' => [],
            'majeures' => [],
            'mineures' => []
        ];

        // Anomalies critiques
        if (!preg_match('/^[0-9]{13}$/', $this->nip)) {
            $anomalies['critiques'][] = [
                'code' => 'nip_invalide',
                'message' => 'Format NIP incorrect'
            ];
        }

        if ($this->organisation && $this->organisation->type === 'parti_politique' && $this->has_profession_exclue) {
            $anomalies['critiques'][] = [
                'code' => 'profession_exclue_parti',
                'message' => 'Profession exclue pour parti politique: ' . $this->profession
            ];
        }

        // Anomalies majeures
        if (!empty($this->telephone) && !preg_match('/^[0-9]{8,9}$/', $this->telephone)) {
            $anomalies['majeures'][] = [
                'code' => 'telephone_invalide',
                'message' => 'Format de téléphone incorrect'
            ];
        }

        // Anomalies mineures
        if (empty($this->profession)) {
            $anomalies['mineures'][] = [
                'code' => 'profession_manquante',
                'message' => 'Profession non renseignée'
            ];
        }

        return $anomalies;
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Obtenir les fonctions disponibles
     */
    public static function getFonctionsDisponibles(): array
    {
        return [
            self::FONCTION_MEMBRE,
            self::FONCTION_PRESIDENT,
            self::FONCTION_VICE_PRESIDENT,
            self::FONCTION_SECRETAIRE_GENERAL,
            self::FONCTION_TRESORIER,
            self::FONCTION_COMMISSAIRE
        ];
    }

    /**
     * ⭐ NOUVELLE MÉTHODE - Statistiques de l'adhérent
     */
    public function getStatistiques(): array
    {
        return [
            'age' => $this->getAge(),
            'anciennete_jours' => $this->date_adhesion ? $this->date_adhesion->diffInDays(now()) : 0,
            'is_responsable' => $this->is_responsable,
            'has_anomalies' => !empty($this->detectAnomalies()['critiques']),
            'profession_exclue' => $this->has_profession_exclue
        ];
    }
}