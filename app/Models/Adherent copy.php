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
    const ANOMALIE_AGE_MINEUR = 'age_mineur';
    const ANOMALIE_AGE_SUSPECT = 'age_suspect';

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
     * - Âge mineur → CRITIQUE (is_active = false)
     * - Âge suspect → MAJEURE (is_active = true)
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

        // 2. ✅ FORMAT NIP INVALIDE (MAJEURE) - CORRIGÉ POUR NOUVEAU FORMAT
        if (!$this->isValidNipFormat()) {
            $nipValidation = $this->analyzeNipFormat();
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_INVALIDE,
                'type' => self::ANOMALIE_MAJEURE,
                'message' => 'Format NIP incorrect - Attendu: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)',
                'details' => [
                    'nip_fourni' => $this->nip,
                    'longueur' => strlen($this->nip),
                    'format_detecte' => $nipValidation['format'],
                    'format_attendu' => 'XX-QQQQ-YYYYMMDD',
                    'exemple_valide' => 'A1-2345-19901225',
                    'details_analyse' => $nipValidation
                ],
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Correction du format NIP vers le nouveau standard'
            ];
        } else {
            // ✅ Si format valide, vérifier l'âge extrait
            $age = $this->getAgeFromNip();
            if ($age !== null) {
                if ($age < 18) {
                    $anomalies[] = [
                        'code' => self::ANOMALIE_AGE_MINEUR,
                        'type' => self::ANOMALIE_CRITIQUE,
                        'message' => "Personne mineure détectée (âge: {$age} ans)",
                        'details' => [
                            'age_calcule' => $age,
                            'nip' => $this->nip,
                            'date_naissance_extraite' => $this->extractDateFromNip(),
                            'regle' => 'Seuls les majeurs (18+ ans) sont autorisés'
                        ],
                        'date_detection' => now()->toISOString(),
                        'action_requise' => 'Vérification de l\'âge - Exclusion si confirmé mineur'
                    ];
                } elseif ($age > 100) {
                    $anomalies[] = [
                        'code' => self::ANOMALIE_AGE_SUSPECT,
                        'type' => self::ANOMALIE_MAJEURE,
                        'message' => "Âge suspect détecté (âge: {$age} ans)",
                        'details' => [
                            'age_calcule' => $age,
                            'nip' => $this->nip,
                            'date_naissance_extraite' => $this->extractDateFromNip()
                        ],
                        'date_detection' => now()->toISOString(),
                        'action_requise' => 'Vérification de la date de naissance dans le NIP'
                    ];
                }
            }
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
     * ✅ MÉTHODE CORRIGÉE - Validation format NIP pour nouveau standard
     */
    private function isValidNipFormat(): bool
    {
        if (empty($this->nip)) {
            return false;
        }
        
        $nip = trim($this->nip);
        
        // ✅ 1. NOUVEAU FORMAT PRINCIPAL: XX-QQQQ-YYYYMMDD
        $newFormatPattern = '/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/';
        if (preg_match($newFormatPattern, $nip)) {
            // Validation additionnelle de la date
            $parts = explode('-', $nip);
            if (count($parts) === 3) {
                $dateStr = $parts[2]; // YYYYMMDD
                $year = (int)substr($dateStr, 0, 4);
                $month = (int)substr($dateStr, 4, 2);
                $day = (int)substr($dateStr, 6, 2);
                
                // Vérifier que la date est valide et raisonnable
                if (checkdate($month, $day, $year) && $year >= 1900 && $year <= date('Y')) {
                    return true;
                }
            }
        }
        
        // ✅ 2. ANCIEN FORMAT (TRANSITOIRE): 13 chiffres
        // Considéré comme invalide pour forcer la migration, mais enregistré avec anomalie
        $oldFormatPattern = '/^[0-9]{13}$/';
        if (preg_match($oldFormatPattern, $nip)) {
            \Log::info('NIP ancien format détecté dans modèle Adherent', [
                'nip' => $nip,
                'action' => 'Sera marqué comme anomalie pour migration'
            ]);
            return false; // Invalide pour forcer l'anomalie
        }
        
        // ✅ 3. Tout autre format est invalide
        return false;
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Analyser le format du NIP
     */
    private function analyzeNipFormat(): array
    {
        $nip = trim($this->nip);
        $analysis = [
            'format' => 'inconnu',
            'longueur' => strlen($nip),
            'contient_tirets' => strpos($nip, '-') !== false,
            'est_numerique' => is_numeric(str_replace('-', '', $nip)),
            'structure' => []
        ];

        if (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', $nip)) {
            $analysis['format'] = 'nouveau_valide';
            $parts = explode('-', $nip);
            $analysis['structure'] = [
                'prefix' => $parts[0],
                'sequence' => $parts[1],
                'date' => $parts[2]
            ];
        } elseif (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-/', $nip)) {
            $analysis['format'] = 'nouveau_incomplet';
        } elseif (preg_match('/^[0-9]{13}$/', $nip)) {
            $analysis['format'] = 'ancien_13_chiffres';
        } elseif (preg_match('/^[0-9]+$/', $nip)) {
            $analysis['format'] = 'numerique_simple';
        } elseif (strpos($nip, '-') !== false) {
            $analysis['format'] = 'avec_tirets_invalide';
        }

        return $analysis;
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Extraire l'âge depuis le nouveau format NIP
     */
    public function getAgeFromNip(): ?int
    {
        if (empty($this->nip)) {
            return null;
        }
        
        // Vérifier format nouveau
        if (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-([0-9]{8})$/', $this->nip, $matches)) {
            $dateStr = $matches[1]; // YYYYMMDD
            $year = (int)substr($dateStr, 0, 4);
            $month = (int)substr($dateStr, 4, 2);
            $day = (int)substr($dateStr, 6, 2);
            
            if (checkdate($month, $day, $year)) {
                $birthDate = \Carbon\Carbon::createFromDate($year, $month, $day);
                return $birthDate->diffInYears(now());
            }
        }
        
        return null;
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Extraire la date formatée du NIP
     */
    public function extractDateFromNip(): ?string
    {
        if (empty($this->nip)) {
            return null;
        }
        
        if (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-([0-9]{8})$/', $this->nip, $matches)) {
            $dateStr = $matches[1]; // YYYYMMDD
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);
            $day = substr($dateStr, 6, 2);
            
            if (checkdate((int)$month, (int)$day, (int)$year)) {
                return "{$day}/{$month}/{$year}";
            }
        }
        
        return null;
    }

    /**
     * ✅ MÉTHODE STATIQUE - Valider et normaliser un NIP
     */
    public static function validateAndNormalizeNip($nip): array
    {
        $result = [
            'valid' => false,
            'normalized' => '',
            'format' => 'unknown',
            'anomalies' => [],
            'age' => null,
            'date_naissance' => null
        ];
        
        if (empty($nip)) {
            $result['anomalies'][] = 'NIP vide ou absent';
            return $result;
        }
        
        $cleanNip = strtoupper(trim($nip));
        $result['normalized'] = $cleanNip;
        
        // Test nouveau format
        if (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-([0-9]{8})$/', $cleanNip, $matches)) {
            $dateStr = $matches[1];
            $year = (int)substr($dateStr, 0, 4);
            $month = (int)substr($dateStr, 4, 2);
            $day = (int)substr($dateStr, 6, 2);
            
            if (checkdate($month, $day, $year) && $year >= 1900 && $year <= date('Y')) {
                $result['valid'] = true;
                $result['format'] = 'nouveau';
                $birthDate = \Carbon\Carbon::createFromDate($year, $month, $day);
                $result['age'] = $birthDate->diffInYears(now());
                $result['date_naissance'] = $birthDate->format('d/m/Y');
                
                if ($result['age'] < 18) {
                    $result['anomalies'][] = "Personne mineure ({$result['age']} ans)";
                } elseif ($result['age'] > 100) {
                    $result['anomalies'][] = "Âge suspect ({$result['age']} ans)";
                }
            } else {
                $result['anomalies'][] = 'Date de naissance invalide dans le NIP';
            }
        }
        // Test ancien format
        elseif (preg_match('/^[0-9]{13}$/', $cleanNip)) {
            $result['format'] = 'ancien';
            $result['anomalies'][] = 'Format ancien (13 chiffres) - Migration requise vers XX-QQQQ-YYYYMMDD';
        }
        // Format inconnu
        else {
            $result['anomalies'][] = 'Format NIP non reconnu - Attendu: XX-QQQQ-YYYYMMDD';
        }
        
        return $result;
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
            self::ANOMALIE_NIP_DOUBLON_ORGANISATION,
            self::ANOMALIE_AGE_MINEUR
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
     * ✅ NOUVELLE MÉTHODE - Obtenir le statut du NIP avec détails
     */
    public function getNipStatus(): array
    {
        if (empty($this->nip)) {
            return ['status' => 'absent', 'valid' => false];
        }

        $validation = self::validateAndNormalizeNip($this->nip);
        
        $anomalies = [];
        if ($this->has_anomalies && $this->anomalies_data) {
            $nipAnomalies = array_filter($this->anomalies_data, function($anomalie) {
                return in_array($anomalie['code'], [
                    self::ANOMALIE_NIP_INVALIDE,
                    self::ANOMALIE_NIP_DOUBLON_FICHIER,
                    self::ANOMALIE_NIP_DOUBLON_ORGANISATION,
                    self::ANOMALIE_AGE_MINEUR,
                    self::ANOMALIE_AGE_SUSPECT
                ]);
            });
            $anomalies = array_column($nipAnomalies, 'code');
        }

        return [
            'status' => $validation['valid'] ? (empty($anomalies) ? 'valide' : 'valide_avec_anomalies') : 'invalide',
            'valid' => $validation['valid'],
            'nip' => $this->nip,
            'format' => $validation['format'],
            'age' => $validation['age'],
            'date_naissance' => $validation['date_naissance'],
            'anomalies' => $anomalies,
            'details' => $validation
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
     * ✅ SCOPES - Enrichis avec nouvelles anomalies
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

    // ✅ NOUVEAUX SCOPES POUR ANOMALIES NIP
    public function scopeNipAbsent($query)
    {
        return $query->whereJsonContains('anomalies_data', [['code' => self::ANOMALIE_NIP_ABSENT]]);
    }

    public function scopeNipInvalide($query)
    {
        return $query->whereJsonContains('anomalies_data', [['code' => self::ANOMALIE_NIP_INVALIDE]]);
    }

    public function scopeNipFormatNouveau($query)
    {
        return $query->where('nip', 'REGEXP', '^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$');
    }

    public function scopeNipFormatAncien($query)
    {
        return $query->where('nip', 'REGEXP', '^[0-9]{13}$');
    }

    public function scopeAgeMineur($query)
    {
        return $query->whereJsonContains('anomalies_data', [['code' => self::ANOMALIE_AGE_MINEUR]]);
    }

    public function scopeAgeSuspect($query)
    {
        return $query->whereJsonContains('anomalies_data', [['code' => self::ANOMALIE_AGE_SUSPECT]]);
    }

    public function scopeDoubleAppartenance($query)
    {
        return $query->whereJsonContains('anomalies_data', [['code' => self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI]]);
    }

    public function scopeByNip($query, $nip)
    {
        return $query->where('nip', $nip);
    }

    public function scopeByAge($query, $minAge = null, $maxAge = null)
    {
        return $query->when($minAge, function($q, $min) {
            return $q->whereRaw('TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) >= ?', [$min]);
        })->when($maxAge, function($q, $max) {
            return $q->whereRaw('TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) <= ?', [$max]);
        });
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

    public function hasAgeMineur(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_AGE_MINEUR);
    }

    public function hasAgeSuspect(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_AGE_SUSPECT);
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

    /**
     * ✅ MÉTHODES UTILITAIRES - Enrichies
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
        // Priorité à l'âge extrait du NIP si disponible
        $ageFromNip = $this->getAgeFromNip();
        if ($ageFromNip !== null) {
            return $ageFromNip;
        }
        
        // Sinon utiliser date_naissance si disponible
        return $this->date_naissance ? $this->date_naissance->age : 0;
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    /**
     * ✅ NOUVELLES MÉTHODES DE STATISTIQUES ENRICHIES
     */
    public function getStatistiquesCompletes(): array
    {
        $nipStatus = $this->getNipStatus();
        
        return [
            'identification' => [
                'nip' => $this->nip,
                'nip_status' => $nipStatus,
                'nom_complet' => $this->nom_complet,
                'age' => $this->getAge(),
                'age_source' => $this->getAgeFromNip() ? 'nip' : 'date_naissance'
            ],
            'statuts' => [
                'is_active' => $this->is_active,
                'is_fondateur' => $this->is_fondateur,
                'is_responsable' => $this->is_responsable,
                'is_excluded' => $this->isExcluded(),
                'can_be_transferred' => $this->canBeTransferred()
            ],
            'anomalies' => [
                'has_anomalies' => $this->has_anomalies,
                'severity' => $this->anomalies_severity,
                'total_count' => $this->has_anomalies ? count($this->anomalies_data) : 0,
                'critiques_count' => count($this->anomalies_critiques),
                'majeures_count' => count($this->anomalies_majeures),
                'mineures_count' => count($this->anomalies_mineures),
                'nip_related' => [
                    'format_invalide' => $this->hasNipInvalide(),
                    'age_mineur' => $this->hasAgeMineur(),
                    'age_suspect' => $this->hasAgeSuspect(),
                    'doublon_fichier' => $this->hasNipDoublonFichier(),
                    'doublon_organisation' => $this->hasNipDoublonOrganisation()
                ]
            ],
            'organisation' => [
                'id' => $this->organisation_id,
                'nom' => $this->organisation->nom ?? 'Inconnue',
                'type' => $this->organisation->type ?? 'Inconnu',
                'date_adhesion' => $this->date_adhesion,
                'anciennete_jours' => $this->date_adhesion ? $this->date_adhesion->diffInDays(now()) : 0
            ],
            'metadata' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'enregistrement_force' => $this->has_anomalies
            ]
        ];
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

        // ✅ VALIDATION COMPLÈTE DU NIP
        $nipValidation = self::validateAndNormalizeNip($nip);
        $anomalies = $nipValidation['anomalies'];

        // Vérifier double appartenance parti
        if ($organisation->type === 'parti_politique' && !empty($nip)) {
            $existingInParti = self::join('organisations', 'adherents.organisation_id', '=', 'organisations.id')
                ->where('adherents.nip', $nip)
                ->where('adherents.is_active', true)
                ->where('organisations.type', 'parti_politique')
                ->first();

            if ($existingInParti) {
                $anomalies[] = "Déjà membre du parti politique '{$existingInParti->nom}'";
            }
        }

        return [
            'can_join' => true, // ✅ Toujours autorisé avec système d'anomalies
            'nip_validation' => $nipValidation,
            'has_anomalies' => !empty($anomalies),
            'anomalies' => $anomalies,
            'will_be_recorded_with_anomalies' => !empty($anomalies),
            'recommendation' => empty($anomalies) ? 'Adhésion sans problème' : 'Adhésion avec suivi des anomalies'
        ];
    }

    /**
     * ✅ MÉTHODES STATIQUES UTILITAIRES ENRICHIES
     */
    public static function getAnomalieTypes(): array
    {
        return [
            self::ANOMALIE_CRITIQUE => 'Critique (désactive l\'adhérent)',
            self::ANOMALIE_MAJEURE => 'Majeure (adhérent actif avec suivi)',
            self::ANOMALIE_MINEURE => 'Mineure (correction recommandée)'
        ];
    }

    public static function getAnomalieCodes(): array
    {
        return [
            self::ANOMALIE_NIP_ABSENT => 'NIP absent ou vide',
            self::ANOMALIE_NIP_INVALIDE => 'Format NIP incorrect',
            self::ANOMALIE_AGE_MINEUR => 'Personne mineure',
            self::ANOMALIE_AGE_SUSPECT => 'Âge suspect',
            self::ANOMALIE_NIP_DOUBLON_FICHIER => 'Doublon dans le fichier',
            self::ANOMALIE_NIP_DOUBLON_ORGANISATION => 'Doublon avec autre organisation',
            self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI => 'Double appartenance parti',
            self::ANOMALIE_PROFESSION_EXCLUE => 'Profession exclue',
            self::ANOMALIE_TELEPHONE_INVALIDE => 'Téléphone invalide',
            self::ANOMALIE_PROFESSION_MANQUANTE => 'Profession manquante'
        ];
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Statistiques globales des NIP
     */
    public static function getNipStatistics(): array
    {
        $total = self::count();
        $nouveauFormat = self::nipFormatNouveau()->count();
        $ancienFormat = self::nipFormatAncien()->count();
        $invalides = self::nipInvalide()->count();
        $mineurs = self::ageMineur()->count();
        $suspects = self::ageSuspect()->count();

        return [
            'total_adherents' => $total,
            'formats' => [
                'nouveau_XX_QQQQ_YYYYMMDD' => $nouveauFormat,
                'ancien_13_chiffres' => $ancienFormat,
                'invalides' => $invalides,
                'pourcentage_nouveau' => $total > 0 ? round(($nouveauFormat / $total) * 100, 2) : 0
            ],
            'ages' => [
                'mineurs_detectes' => $mineurs,
                'ages_suspects' => $suspects,
                'age_moyen' => self::selectRaw('AVG(TIMESTAMPDIFF(YEAR, date_naissance, CURDATE())) as moyenne')->value('moyenne')
            ],
            'anomalies_nip' => [
                'total_avec_anomalies' => self::withAnomalies()->count(),
                'anomalies_critiques' => self::anomaliesCritiques()->count(),
                'anomalies_majeures' => self::anomaliesMajeures()->count(),
                'anomalies_mineures' => self::anomaliesMineures()->count()
            ]
        ];
    }
}