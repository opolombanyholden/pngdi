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
     * ✅ FILLABLE - Mis à jour avec toutes les nouvelles colonnes
     */
    protected $fillable = [
        // Identification de base
        'organisation_id',
        'nip',
        'nom',
        'prenom',
        
        // Informations personnelles
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        'telephone',
        'email',
        
        // ✅ NOUVELLES COLONNES
        'profession',
        'fonction',
        
        // Adresse complète
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
        
        // ✅ GESTION DES ANOMALIES
        'has_anomalies',
        'anomalies_data',
        'anomalies_severity',
        
        // Historique
        'historique'
    ];

    /**
     * ✅ CASTS - Mis à jour
     */
    protected $casts = [
        'date_naissance' => 'date',
        'date_adhesion' => 'date',
        'date_exclusion' => 'date',
        'is_fondateur' => 'boolean',
        'is_active' => 'boolean',
        'has_anomalies' => 'boolean',
        'historique' => 'array',
        'anomalies_data' => 'array',
    ];

    /**
     * ✅ CONSTANTES
     */
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';
    
    // Fonctions disponibles
    const FONCTION_MEMBRE = 'Membre';
    const FONCTION_PRESIDENT = 'Président';
    const FONCTION_VICE_PRESIDENT = 'Vice-Président';
    const FONCTION_SECRETAIRE_GENERAL = 'Secrétaire Général';
    const FONCTION_TRESORIER = 'Trésorier';
    const FONCTION_COMMISSAIRE = 'Commissaire aux Comptes';
    
    // Professions exclues pour partis politiques
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

    // ✅ CONSTANTES POUR ANOMALIES
    const ANOMALIE_CRITIQUE = 'critique';
    const ANOMALIE_MAJEURE = 'majeure';
    const ANOMALIE_MINEURE = 'mineure';

    // ✅ CODES D'ANOMALIES ÉTENDUS POUR NIP
    const ANOMALIE_DOUBLE_APPARTENANCE_PARTI = 'double_appartenance_parti';
    const ANOMALIE_PROFESSION_EXCLUE = 'profession_exclue_parti';
    const ANOMALIE_NIP_INVALIDE = 'nip_invalide';
    const ANOMALIE_NIP_ABSENT = 'nip_absent';
    const ANOMALIE_NIP_DOUBLON_FICHIER = 'nip_doublon_fichier';
    const ANOMALIE_NIP_DOUBLON_ORGANISATION = 'nip_doublon_organisation';
    const ANOMALIE_TELEPHONE_INVALIDE = 'telephone_invalide';
    const ANOMALIE_PROFESSION_MANQUANTE = 'profession_manquante';

    // ✅ PROPRIÉTÉ STATIQUE POUR TRACKER LES DOUBLONS DANS LE BATCH
    protected static $nipBatchTracker = [];

    /**
     * ✅ BOOT - NOUVELLE LOGIQUE AVEC GESTION COMPLÈTE DES ANOMALIES NIP
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adherent) {
            // Définir fonction par défaut
            if (empty($adherent->fonction)) {
                $adherent->fonction = self::FONCTION_MEMBRE;
            }
            
            // Initialiser l'historique si vide
            if (empty($adherent->historique)) {
                $adherent->historique = [
                    'creation' => now()->toISOString(),
                    'source' => 'creation_manuelle',
                    'events' => []
                ];
            }

            // ✅ NOUVELLE LOGIQUE : Détecter et gérer TOUTES les anomalies SANS bloquer
            $adherent->detectAndManageAllAnomalies();
        });

        static::created(function ($adherent) {
            try {
                // Ajouter l'événement d'adhésion dans l'historique
                $adherent->addToHistorique('adhesion', [
                    'date' => $adherent->date_adhesion ?? now(),
                    'organisation_id' => $adherent->organisation_id,
                    'profession' => $adherent->profession,
                    'fonction' => $adherent->fonction,
                    'has_anomalies' => $adherent->has_anomalies,
                    'anomalies_severity' => $adherent->anomalies_severity,
                    'nip_status' => $adherent->getNipStatus()
                ]);

                // Logger si des anomalies ont été détectées
                if ($adherent->has_anomalies) {
                    \Log::warning('Adhérent créé avec anomalies', [
                        'adherent_id' => $adherent->id,
                        'nip' => $adherent->nip,
                        'organisation_id' => $adherent->organisation_id,
                        'anomalies' => $adherent->anomalies_data,
                        'severity' => $adherent->anomalies_severity,
                        'enregistrement_force' => 'limite_minimale_atteinte'
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'ajout à l\'historique: ' . $e->getMessage());
            }
            
            // Créer l'historique dans la table dédiée si elle existe
            try {
                if (class_exists('App\Models\AdherentHistory')) {
                    \App\Models\AdherentHistory::create([
                        'adherent_id' => $adherent->id,
                        'organisation_id' => $adherent->organisation_id,
                        'type_mouvement' => 'adhesion',
                        'motif' => 'Adhésion initiale - Profession: ' . ($adherent->profession ?? 'Non renseignée') 
                            . ($adherent->has_anomalies ? ' [ANOMALIES: ' . $adherent->anomalies_severity . ']' : ''),
                        'date_effet' => $adherent->date_adhesion ?? now(),
                        'created_by' => auth()->id() ?? 1
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la création de l\'historique dédié: ' . $e->getMessage());
            }
        });
    }

    /**
     * ✅ MÉTHODE PRINCIPALE MISE À JOUR - Détecter TOUTES les anomalies
     */
    public function detectAndManageAllAnomalies()
    {
        $anomalies = [];
        $severityLevel = null;

        // ✅ 1. VÉRIFICATIONS NIP COMPLÈTES (PRIORITÉ ABSOLUE)
        $nipAnomalies = $this->checkAllNipAnomalies();
        if (!empty($nipAnomalies)) {
            $anomalies = array_merge($anomalies, $nipAnomalies);
            // Déterminer la sévérité maximale des anomalies NIP
            foreach ($nipAnomalies as $anomalie) {
                if ($anomalie['type'] === self::ANOMALIE_CRITIQUE) {
                    $severityLevel = self::ANOMALIE_CRITIQUE;
                } elseif ($anomalie['type'] === self::ANOMALIE_MAJEURE && $severityLevel !== self::ANOMALIE_CRITIQUE) {
                    $severityLevel = self::ANOMALIE_MAJEURE;
                } elseif ($anomalie['type'] === self::ANOMALIE_MINEURE && !$severityLevel) {
                    $severityLevel = self::ANOMALIE_MINEURE;
                }
            }
        }

        // ✅ 2. VÉRIFICATION DOUBLE APPARTENANCE PARTI (ANOMALIE CRITIQUE)
        if ($this->organisation && $this->organisation->type === 'parti_politique') {
            $doubleAppartenance = $this->checkDoubleAppartenanceParti();
            if ($doubleAppartenance) {
                $anomalies[] = [
                    'code' => self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI,
                    'type' => self::ANOMALIE_CRITIQUE,
                    'message' => "Membre actif du parti politique '{$doubleAppartenance['parti_nom']}'",
                    'details' => $doubleAppartenance,
                    'date_detection' => now()->toISOString(),
                    'action_requise' => 'Exclusion formelle du parti actuel avant validation définitive'
                ];
                $severityLevel = self::ANOMALIE_CRITIQUE;
            }
        }

        // ✅ 3. VÉRIFICATION PROFESSION EXCLUE (ANOMALIE CRITIQUE)
        if ($this->organisation && $this->organisation->type === 'parti_politique' && $this->profession) {
            $professionExclue = $this->checkProfessionExclue();
            if ($professionExclue) {
                $anomalies[] = [
                    'code' => self::ANOMALIE_PROFESSION_EXCLUE,
                    'type' => self::ANOMALIE_CRITIQUE,
                    'message' => "Profession '{$this->profession}' exclue pour parti politique",
                    'details' => ['profession' => $this->profession],
                    'date_detection' => now()->toISOString(),
                    'action_requise' => 'Changement de profession ou refus d\'adhésion'
                ];
                $severityLevel = self::ANOMALIE_CRITIQUE;
            }
        }

        // ✅ 4. VÉRIFICATION TÉLÉPHONE (ANOMALIE MAJEURE)
        if (!empty($this->telephone) && !$this->isValidTelephone()) {
            $anomalies[] = [
                'code' => self::ANOMALIE_TELEPHONE_INVALIDE,
                'type' => self::ANOMALIE_MAJEURE,
                'message' => 'Format de téléphone incorrect',
                'details' => ['telephone_fourni' => $this->telephone],
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Correction du numéro de téléphone'
            ];
            if (!$severityLevel || $severityLevel === self::ANOMALIE_MINEURE) {
                $severityLevel = self::ANOMALIE_MAJEURE;
            }
        }

        // ✅ 5. VÉRIFICATION PROFESSION MANQUANTE (ANOMALIE MINEURE)
        if (empty($this->profession)) {
            $anomalies[] = [
                'code' => self::ANOMALIE_PROFESSION_MANQUANTE,
                'type' => self::ANOMALIE_MINEURE,
                'message' => 'Profession non renseignée',
                'details' => [],
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Saisie de la profession'
            ];
            if (!$severityLevel) {
                $severityLevel = self::ANOMALIE_MINEURE;
            }
        }

        // ✅ GESTION DE L'ÉTAT ACTIF SELON LA NOUVELLE LOGIQUE
        $this->setActiveStatusBasedOnAnomalies($anomalies, $severityLevel);

        // ✅ ENREGISTRER LES ANOMALIES
        if (!empty($anomalies)) {
            $this->has_anomalies = true;
            $this->anomalies_data = $anomalies;
            $this->anomalies_severity = $severityLevel;

            // Ajouter dans l'historique
            $this->addToHistoriqueInternal('anomalies_detected', [
                'total_anomalies' => count($anomalies),
                'severity' => $severityLevel,
                'anomalies_summary' => array_column($anomalies, 'code'),
                'enregistrement_force' => 'limite_minimale_respectee'
            ]);
        } else {
            $this->has_anomalies = false;
            $this->anomalies_data = null;
            $this->anomalies_severity = null;
            $this->is_active = true;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Vérifier TOUTES les anomalies NIP
     * 
     * HIÉRARCHIE DES ANOMALIES NIP :
     * - NIP absent/vide → CRITIQUE (is_active = false)
     * - Format NIP incorrect → MAJEURE (is_active = true)
     * - Doublon dans fichier → MINEURE (is_active = true) 
     * - Doublon avec autre organisation → CRITIQUE (is_active = false)
     */
    private function checkAllNipAnomalies(): array
    {
        $anomalies = [];

        // 1. ✅ NIP ABSENT OU VIDE (CRITIQUE)
        if (empty($this->nip) || trim($this->nip) === '') {
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_ABSENT,
                'type' => self::ANOMALIE_CRITIQUE,
                'message' => 'NIP absent ou vide',
                'details' => ['nip_fourni' => $this->nip],
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Saisie obligatoire du NIP'
            ];
            return $anomalies; // Arrêter ici si NIP absent
        }

        // 2. ✅ FORMAT NIP INVALIDE (MAJEURE)
        if (!$this->isValidNipFormat()) {
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_INVALIDE,
                'type' => self::ANOMALIE_MAJEURE,
                'message' => 'Format NIP incorrect (doit être 13 chiffres)',
                'details' => [
                    'nip_fourni' => $this->nip,
                    'longueur' => strlen($this->nip),
                    'contient_non_numerique' => !is_numeric($this->nip)
                ],
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Correction du format NIP'
            ];
        }

        // 3. ✅ DOUBLON DANS LE FICHIER/BATCH (MINEURE)
        $doublonFichier = $this->checkDoublonDansFichier();
        if ($doublonFichier) {
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_DOUBLON_FICHIER,
                'type' => self::ANOMALIE_MINEURE,
                'message' => "NIP '{$this->nip}' présent plusieurs fois dans le fichier",
                'details' => $doublonFichier,
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Supprimer les doublons du fichier'
            ];
        }

        // 4. ✅ DOUBLON AVEC AUTRE ORGANISATION (CRITIQUE)
        $doublonOrganisation = $this->checkDoublonAvecAutreOrganisation();
        if ($doublonOrganisation) {
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_DOUBLON_ORGANISATION,
                'type' => self::ANOMALIE_CRITIQUE,
                'message' => "NIP '{$this->nip}' déjà enregistré dans une autre organisation",
                'details' => $doublonOrganisation,
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Vérification et résolution du conflit'
            ];
        }

        return $anomalies;
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Vérifier format NIP
     */
    private function isValidNipFormat(): bool
    {
        return !empty($this->nip) && preg_match('/^[0-9]{13}$/', $this->nip);
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Détecter doublon dans le fichier/batch
     */
    private function checkDoublonDansFichier(): ?array
    {
        if (empty($this->nip)) {
            return null;
        }

        // Utiliser le tracker statique pour détecter les doublons dans le batch
        if (!isset(self::$nipBatchTracker[$this->nip])) {
            self::$nipBatchTracker[$this->nip] = [
                'count' => 1,
                'first_occurrence' => [
                    'nom' => $this->nom,
                    'prenom' => $this->prenom,
                    'organisation_id' => $this->organisation_id
                ]
            ];
            return null; // Première occurrence
        }

        // C'est un doublon !
        self::$nipBatchTracker[$this->nip]['count']++;
        
        return [
            'nip' => $this->nip,
            'occurrence_numero' => self::$nipBatchTracker[$this->nip]['count'],
            'premiere_occurrence' => self::$nipBatchTracker[$this->nip]['first_occurrence'],
            'occurrence_actuelle' => [
                'nom' => $this->nom,
                'prenom' => $this->prenom,
                'organisation_id' => $this->organisation_id
            ]
        ];
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Détecter doublon avec autre organisation
     */
    private function checkDoublonAvecAutreOrganisation(): ?array
    {
        if (empty($this->nip)) {
            return null;
        }

        $existingAdherent = self::where('nip', $this->nip)
            ->where('organisation_id', '!=', $this->organisation_id)
            ->with('organisation')
            ->first();

        if ($existingAdherent) {
            return [
                'nip' => $this->nip,
                'organisation_existante_id' => $existingAdherent->organisation_id,
                'organisation_existante_nom' => $existingAdherent->organisation->nom ?? 'Organisation inconnue',
                'organisation_existante_type' => $existingAdherent->organisation->type ?? 'Type inconnu',
                'adherent_existant' => [
                    'id' => $existingAdherent->id,
                    'nom' => $existingAdherent->nom,
                    'prenom' => $existingAdherent->prenom,
                    'is_active' => $existingAdherent->is_active,
                    'date_adhesion' => $existingAdherent->date_adhesion
                ]
            ];
        }

        return null;
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Définir le statut actif selon les anomalies
     */
    private function setActiveStatusBasedOnAnomalies(array $anomalies, ?string $severityLevel)
    {
        if (empty($anomalies)) {
            $this->is_active = true;
            return;
        }

        // ✅ NOUVELLE LOGIQUE : Plus restrictive pour certains cas
        $codesAnomaliesCritiques = array_column(
            array_filter($anomalies, function($a) { return $a['type'] === self::ANOMALIE_CRITIQUE; }),
            'code'
        );

        // Cas spéciaux où on désactive même avec limite minimale
        $casDesactivationForce = [
            self::ANOMALIE_NIP_ABSENT,
            self::ANOMALIE_PROFESSION_EXCLUE,
            self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI,
            self::ANOMALIE_NIP_DOUBLON_ORGANISATION
        ];

        $hasDesactivationForce = !empty(array_intersect($codesAnomaliesCritiques, $casDesactivationForce));

        if ($hasDesactivationForce) {
            $this->is_active = false;
        } else {
            // ✅ Pour les autres anomalies (format NIP, doublons), on reste actif 
            // car la limite minimale est respectée
            $this->is_active = true;
        }
    }

    /**
     * ✅ MÉTHODE MISE À JOUR - Double appartenance parti SANS exception
     */
    private function checkDoubleAppartenanceParti()
    {
        if (empty($this->nip)) {
            return false;
        }

        $existingMembership = self::where('nip', $this->nip)
            ->where('is_active', true)
            ->whereHas('organisation', function ($query) {
                $query->where('type', 'parti_politique')
                      ->where('statut', '!=', 'radie');
            })
            ->where('organisation_id', '!=', $this->organisation_id)
            ->with('organisation')
            ->first();

        if ($existingMembership) {
            return [
                'parti_id' => $existingMembership->organisation_id,
                'parti_nom' => $existingMembership->organisation->nom,
                'date_adhesion_existante' => $existingMembership->date_adhesion,
                'fonction_existante' => $existingMembership->fonction
            ];
        }

        return false;
    }

    /**
     * ✅ MÉTHODE MISE À JOUR - Profession exclue SANS exception
     */
    private function checkProfessionExclue()
    {
        if (empty($this->profession)) {
            return false;
        }
        
        $professionLower = strtolower($this->profession);
        $exclusLower = array_map('strtolower', self::PROFESSIONS_EXCLUES_PARTIS);
        
        return in_array($professionLower, $exclusLower);
    }

    /**
     * ✅ MÉTHODES DE VALIDATION
     */
    private function isValidTelephone(): bool
    {
        return preg_match('/^[0-9]{8,9}$/', $this->telephone);
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Obtenir le statut du NIP
     */
    public function getNipStatus(): array
    {
        if (empty($this->nip)) {
            return ['status' => 'absent', 'valid' => false];
        }

        if (!$this->isValidNipFormat()) {
            return ['status' => 'invalide', 'valid' => false, 'nip' => $this->nip];
        }

        $anomalies = [];
        if ($this->has_anomalies && $this->anomalies_data) {
            $nipAnomalies = array_filter($this->anomalies_data, function($anomalie) {
                return in_array($anomalie['code'], [
                    self::ANOMALIE_NIP_DOUBLON_FICHIER,
                    self::ANOMALIE_NIP_DOUBLON_ORGANISATION
                ]);
            });
            $anomalies = array_column($nipAnomalies, 'code');
        }

        return [
            'status' => empty($anomalies) ? 'valide' : 'valide_avec_anomalies',
            'valid' => true,
            'nip' => $this->nip,
            'anomalies' => $anomalies
        ];
    }

    /**
     * ✅ MÉTHODE STATIQUE - Réinitialiser le tracker de batch
     */
    public static function resetBatchTracker()
    {
        self::$nipBatchTracker = [];
    }

    /**
     * ✅ MÉTHODE STATIQUE - Obtenir les statistiques du batch
     */
    public static function getBatchStatistics(): array
    {
        $totalNips = count(self::$nipBatchTracker);
        $doublons = array_filter(self::$nipBatchTracker, function($data) { return $data['count'] > 1; });
        
        return [
            'total_nips_traites' => $totalNips,
            'nips_uniques' => $totalNips - count($doublons),
            'nips_doublons' => count($doublons),
            'details_doublons' => $doublons
        ];
    }

    /**
     * ✅ MÉTHODE CORRIGÉE - addToHistorique sécurisée
     */
    public function addToHistorique($type, $data = [])
    {
        try {
            $this->addToHistoriqueInternal($type, $data);
            $this->updateQuietly(['historique' => $this->historique]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans addToHistorique: ' . $e->getMessage(), [
                'adherent_id' => $this->id,
                'type' => $type,
                'data' => $data
            ]);
        }
    }

    /**
     * ✅ MÉTHODE INTERNE pour éviter les conflits de sauvegarde
     */
    private function addToHistoriqueInternal($type, $data = [])
    {
        $currentHistorique = $this->historique;
        
        if (is_null($currentHistorique)) {
            $historique = ['events' => []];
        } elseif (is_string($currentHistorique)) {
            $decoded = json_decode($currentHistorique, true);
            $historique = is_array($decoded) ? $decoded : ['events' => []];
        } elseif (is_array($currentHistorique)) {
            $historique = $currentHistorique;
        } else {
            $historique = ['events' => []];
        }
        
        if (!isset($historique['events']) || !is_array($historique['events'])) {
            $historique['events'] = [];
        }
        
        $historique['events'][] = [
            'type' => $type,
            'date' => now()->toISOString(),
            'data' => $data,
            'user_id' => auth()->id()
        ];
        
        $this->historique = $historique;
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
    
    public function fondateur(): BelongsTo
    {
        return $this->belongsTo(Fondateur::class, 'fondateur_id');
    }

    /**
     * ✅ SCOPES - Enrichis avec anomalies NIP
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

    public function scopeWithAnomalies($query)
    {
        return $query->where('has_anomalies', true);
    }

    public function scopeWithoutAnomalies($query)
    {
        return $query->where('has_anomalies', false);
    }

    public function scopeAnomaliesCritiques($query)
    {
        return $query->where('anomalies_severity', self::ANOMALIE_CRITIQUE);
    }

    public function scopeAnomaliesMajeures($query)
    {
        return $query->where('anomalies_severity', self::ANOMALIE_MAJEURE);
    }

    public function scopeAnomaliesMineures($query)
    {
        return $query->where('anomalies_severity', self::ANOMALIE_MINEURE);
    }

    public function scopeDoubleAppartenance($query)
    {
        return $query->whereJsonContains('anomalies_data', [
            ['code' => self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI]
        ]);
    }

    // ✅ NOUVEAUX SCOPES POUR ANOMALIES NIP
    public function scopeNipAbsent($query)
    {
        return $query->whereJsonContains('anomalies_data', [
            ['code' => self::ANOMALIE_NIP_ABSENT]
        ]);
    }

    public function scopeNipInvalide($query)
    {
        return $query->whereJsonContains('anomalies_data', [
            ['code' => self::ANOMALIE_NIP_INVALIDE]
        ]);
    }

    public function scopeNipDoublonFichier($query)
    {
        return $query->whereJsonContains('anomalies_data', [
            ['code' => self::ANOMALIE_NIP_DOUBLON_FICHIER]
        ]);
    }

    public function scopeNipDoublonOrganisation($query)
    {
        return $query->whereJsonContains('anomalies_data', [
            ['code' => self::ANOMALIE_NIP_DOUBLON_ORGANISATION]
        ]);
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

    /**
     * ✅ MÉTHODES UTILITAIRES - Enrichies avec anomalies NIP
     */
    public function isExcluded(): bool
    {
        return !$this->is_active || $this->date_exclusion !== null;
    }

    public function canBeTransferred(): bool
    {
        return !$this->is_fondateur && $this->is_active && !$this->isExcluded() 
            && (!$this->has_anomalies || $this->anomalies_severity === self::ANOMALIE_MINEURE);
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

    /**
     * ✅ NOUVELLES MÉTHODES POUR GESTION DES ANOMALIES NIP
     */
    public function hasNipAbsent(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_NIP_ABSENT);
    }

    public function hasNipInvalide(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_NIP_INVALIDE);
    }

    public function hasNipDoublonFichier(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_NIP_DOUBLON_FICHIER);
    }

    public function hasNipDoublonOrganisation(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_NIP_DOUBLON_ORGANISATION);
    }

    public function hasDoubleAppartenance(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI);
    }

    private function hasAnomalieCode(string $code): bool
    {
        if (!$this->has_anomalies || !$this->anomalies_data) {
            return false;
        }

        return collect($this->anomalies_data)->contains('code', $code);
    }

    public function getDoubleAppartenanceDetails(): ?array
    {
        if (!$this->hasDoubleAppartenance()) {
            return null;
        }

        $anomalie = collect($this->anomalies_data)->firstWhere('code', self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI);
        return $anomalie['details'] ?? null;
    }

    public function getNipDoublonDetails(): ?array
    {
        $doublonFichier = collect($this->anomalies_data ?? [])->firstWhere('code', self::ANOMALIE_NIP_DOUBLON_FICHIER);
        $doublonOrganisation = collect($this->anomalies_data ?? [])->firstWhere('code', self::ANOMALIE_NIP_DOUBLON_ORGANISATION);

        return [
            'doublon_fichier' => $doublonFichier['details'] ?? null,
            'doublon_organisation' => $doublonOrganisation['details'] ?? null
        ];
    }

    /**
     * ✅ MÉTHODES D'ACCÈS AUX ANOMALIES PAR TYPE
     */
    public function getAnomaliesCritiquesAttribute(): array
    {
        if (!$this->has_anomalies || !$this->anomalies_data) {
            return [];
        }

        return array_filter($this->anomalies_data, function($anomalie) {
            return $anomalie['type'] === self::ANOMALIE_CRITIQUE;
        });
    }

    public function getAnomaliesMajeuressAttribute(): array
    {
        if (!$this->has_anomalies || !$this->anomalies_data) {
            return [];
        }

        return array_filter($this->anomalies_data, function($anomalie) {
            return $anomalie['type'] === self::ANOMALIE_MAJEURE;
        });
    }

    public function getAnomaliesMineuressAttribute(): array
    {
        if (!$this->has_anomalies || !$this->anomalies_data) {
            return [];
        }

        return array_filter($this->anomalies_data, function($anomalie) {
            return $anomalie['type'] === self::ANOMALIE_MINEURE;
        });
    }

    /**
     * ✅ MÉTHODE POUR RÉSOUDRE LES ANOMALIES
     */
    public function resolveAnomalie($anomalieCode, $resolution = [], $userId = null)
    {
        if (!$this->has_anomalies || !$this->anomalies_data) {
            return false;
        }

        $anomalies = $this->anomalies_data;
        $anomalieIndex = null;

        foreach ($anomalies as $index => $anomalie) {
            if ($anomalie['code'] === $anomalieCode) {
                $anomalieIndex = $index;
                break;
            }
        }

        if ($anomalieIndex === null) {
            return false;
        }

        // Marquer l'anomalie comme résolue
        $anomalies[$anomalieIndex]['resolved'] = true;
        $anomalies[$anomalieIndex]['resolution_date'] = now()->toISOString();
        $anomalies[$anomalieIndex]['resolution_details'] = $resolution;
        $anomalies[$anomalieIndex]['resolved_by'] = $userId ?? auth()->id();

        // Recalculer le niveau de sévérité
        $unresolvedAnomalies = array_filter($anomalies, function($anomalie) {
            return !isset($anomalie['resolved']) || !$anomalie['resolved'];
        });

        if (empty($unresolvedAnomalies)) {
            $this->has_anomalies = false;
            $this->anomalies_severity = null;
            $this->is_active = true; // Réactiver si toutes les anomalies sont résolues
        } else {
            // Recalculer la sévérité basée sur les anomalies non résolues
            $maxSeverity = self::ANOMALIE_MINEURE;
            foreach ($unresolvedAnomalies as $anomalie) {
                if ($anomalie['type'] === self::ANOMALIE_CRITIQUE) {
                    $maxSeverity = self::ANOMALIE_CRITIQUE;
                    break;
                } elseif ($anomalie['type'] === self::ANOMALIE_MAJEURE && $maxSeverity !== self::ANOMALIE_CRITIQUE) {
                    $maxSeverity = self::ANOMALIE_MAJEURE;
                }
            }
            $this->anomalies_severity = $maxSeverity;
        }

        $this->anomalies_data = $anomalies;
        $this->save();

        // Ajouter à l'historique
        $this->addToHistorique('anomalie_resolved', [
            'anomalie_code' => $anomalieCode,
            'resolution' => $resolution
        ]);

        return true;
    }

    /**
     * ✅ MÉTHODE MISE À JOUR - Exclure l'adhérent
     */
    public function exclude($motif, $dateExclusion = null, $documentPath = null)
    {
        $dateExclusion = $dateExclusion ?? now();
        
        $this->update([
            'is_active' => false,
            'date_exclusion' => $dateExclusion,
            'motif_exclusion' => $motif
        ]);

        $this->addToHistorique('exclusion', [
            'motif' => $motif,
            'date_exclusion' => $dateExclusion,
            'document_path' => $documentPath
        ]);

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
     * ✅ MÉTHODE MISE À JOUR - Réactiver l'adhérent
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
     * ✅ MÉTHODE MISE À JOUR - Vérifier possibilité d'adhésion
     */
    public static function canJoinOrganisation($nip, $organisationId): array
    {
        $organisation = Organisation::find($organisationId);
        
        if (!$organisation) {
            return ['can_join' => false, 'reason' => 'Organisation non trouvée'];
        }

        // ✅ NOUVELLE LOGIQUE : Toujours autoriser avec anomalies
        $anomalies = [];

        // Vérifier format NIP
        if (empty($nip) || !preg_match('/^[0-9]{13}$/', $nip)) {
            $anomalies[] = [
                'type' => 'nip_invalide',
                'message' => 'Format NIP incorrect ou absent'
            ];
        }

        // Vérifier double appartenance parti
        if ($organisation->type === 'parti_politique' && !empty($nip)) {
            $existingInParti = self::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
                ->where('adherents.nip', $nip)
                ->where('adherents.is_active', true)
                ->where('organisations.type', 'parti_politique')
                ->first();

            if ($existingInParti) {
                $anomalies[] = [
                    'type' => 'double_appartenance_parti',
                    'message' => "Déjà membre du parti politique '{$existingInParti->nom}'",
                    'existing_organisation' => $existingInParti
                ];
            }
        }

        return [
            'can_join' => true, // ✅ Toujours autorisé
            'has_anomalies' => !empty($anomalies),
            'anomalies' => $anomalies,
            'will_be_recorded_with_anomalies' => !empty($anomalies)
        ];
    }
    
    /**
     * ✅ MÉTHODES STATIQUES UTILITAIRES
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

    public static function getAnomalieTypes(): array
    {
        return [
            self::ANOMALIE_CRITIQUE,
            self::ANOMALIE_MAJEURE,
            self::ANOMALIE_MINEURE
        ];
    }

    public static function getAnomalieCodes(): array
    {
        return [
            self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI,
            self::ANOMALIE_PROFESSION_EXCLUE,
            self::ANOMALIE_NIP_INVALIDE,
            self::ANOMALIE_NIP_ABSENT,
            self::ANOMALIE_NIP_DOUBLON_FICHIER,
            self::ANOMALIE_NIP_DOUBLON_ORGANISATION,
            self::ANOMALIE_TELEPHONE_INVALIDE,
            self::ANOMALIE_PROFESSION_MANQUANTE
        ];
    }

    /**
     * ✅ STATISTIQUES MISES À JOUR
     */
    public function getStatistiques(): array
    {
        return [
            'age' => $this->getAge(),
            'anciennete_jours' => $this->date_adhesion ? $this->date_adhesion->diffInDays(now()) : 0,
            'is_responsable' => $this->is_responsable,
            'has_anomalies' => $this->has_anomalies,
            'anomalies_severity' => $this->anomalies_severity,
            'total_anomalies' => $this->has_anomalies ? count($this->anomalies_data) : 0,
            'has_double_appartenance' => $this->hasDoubleAppartenance(),
            'nip_status' => $this->getNipStatus(),
            'is_active' => $this->is_active,
            'enregistrement_force' => $this->has_anomalies
        ];
    }
}