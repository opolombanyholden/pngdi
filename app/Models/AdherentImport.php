<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AdherentAnomalie; // ✅ NOUVEAU IMPORT

class AdherentImport extends Model
{
    use HasFactory;

    /**
     * Table associée au modèle
     */
    protected $table = 'adherent_imports';

    /**
     * Les attributs qui sont mass assignable
     */
    protected $fillable = [
        'organisation_id',
        'imported_by',
        'nom_fichier',
        'taille_fichier',
        'type_fichier',
        'statut',
        'total_lignes',
        'lignes_traitees',
        'lignes_importees',
        'lignes_erreur',
        'lignes_anomalies',
        'methode_traitement',
        'duree_traitement',
        'rapport_import',
        'rapport_anomalies',
        'checksum_fichier',
        'configuration_import',
        'date_debut',
        'date_fin',
        'commentaires',
        'chemin_fichier' // ✅ AJOUTÉ
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'rapport_import' => 'json',
        'rapport_anomalies' => 'json',
        'configuration_import' => 'json',
        'duree_traitement' => 'integer',
        'taille_fichier' => 'integer',
        'total_lignes' => 'integer',
        'lignes_traitees' => 'integer',
        'lignes_importees' => 'integer',
        'lignes_erreur' => 'integer',
        'lignes_anomalies' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec l'organisation
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Relation avec l'utilisateur importateur
     */
    public function importateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * ✅ NOUVELLE RELATION : Anomalies détectées pendant cet import
     */
    public function anomalies(): HasMany
    {
        return $this->hasMany(AdherentAnomalie::class, 'organisation_id', 'organisation_id')
            ->where('created_at', '>=', $this->date_debut)
            ->where('created_at', '<=', $this->date_fin ?? now());
    }

    /**
     * ✅ NOUVELLE RELATION : Adhérents créés lors de cet import
     */
    public function adherentsImportes(): HasMany
    {
        return $this->hasMany(Adherent::class, 'organisation_id', 'organisation_id')
            ->where('created_at', '>=', $this->date_debut)
            ->where('created_at', '<=', $this->date_fin ?? now())
            ->where('source', 'import_csv');
    }

    /**
     * Scopes pour les requêtes
     */
    public function scopeByOrganisation($query, $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeTermines($query)
    {
        return $query->where('statut', 'termine');
    }

    public function scopeEnEchec($query)
    {
        return $query->where('statut', 'echec');
    }

    public function scopeAvecAnomalies($query)
    {
        return $query->where('lignes_anomalies', '>', 0);
    }

    public function scopeRecents($query, $jours = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }

    /**
     * ✅ NOUVEAUX ACCESSEURS POUR ANOMALIES
     */
    public function getTauxReussiteAttribute()
    {
        if ($this->total_lignes == 0) return 0;
        return round(($this->lignes_importees / $this->total_lignes) * 100, 2);
    }

    public function getTauxAnomaliesAttribute()
    {
        if ($this->total_lignes == 0) return 0;
        return round(($this->lignes_anomalies / $this->total_lignes) * 100, 2);
    }

    public function getTauxErreurAttribute()
    {
        if ($this->total_lignes == 0) return 0;
        return round(($this->lignes_erreur / $this->total_lignes) * 100, 2);
    }

    public function getDureeFormateeAttribute()
    {
        if (!$this->duree_traitement) return 'N/A';
        
        $duree = $this->duree_traitement; // en millisecondes
        if ($duree < 1000) {
            return $duree . 'ms';
        } elseif ($duree < 60000) {
            return round($duree / 1000, 1) . 's';
        } else {
            return round($duree / 60000, 1) . 'min';
        }
    }

    public function getTailleFormateeAttribute()
    {
        if (!$this->taille_fichier) return 'N/A';
        
        $taille = $this->taille_fichier;
        $unites = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($taille >= 1024 && $i < count($unites) - 1) {
            $taille /= 1024;
            $i++;
        }
        
        return round($taille, 2) . ' ' . $unites[$i];
    }

    public function getIsTermineAttribute()
    {
        return $this->statut === 'termine';
    }

    public function getIsEnCoursAttribute()
    {
        return $this->statut === 'en_cours';
    }

    public function getIsEchecAttribute()
    {
        return $this->statut === 'echec';
    }

    public function getHasAnomaliesAttribute()
    {
        return $this->lignes_anomalies > 0;
    }

    /**
     * ✅ NOUVELLES MÉTHODES MÉTIER POUR ANOMALIES
     */
    public function marquerEnCours()
    {
        $this->update([
            'statut' => 'en_cours',
            'date_debut' => now()
        ]);
    }

    public function marquerTermine($stats = [])
    {
        $data = [
            'statut' => 'termine',
            'date_fin' => now()
        ];

        if (!empty($stats)) {
            $data = array_merge($data, [
                'lignes_traitees' => $stats['lignes_traitees'] ?? $this->lignes_traitees,
                'lignes_importees' => $stats['lignes_importees'] ?? $this->lignes_importees,
                'lignes_erreur' => $stats['lignes_erreur'] ?? $this->lignes_erreur,
                'lignes_anomalies' => $stats['lignes_anomalies'] ?? $this->lignes_anomalies,
                'rapport_import' => $stats['rapport_import'] ?? $this->rapport_import,
                'rapport_anomalies' => $stats['rapport_anomalies'] ?? $this->rapport_anomalies
            ]);
        }

        if ($this->date_debut) {
            $data['duree_traitement'] = now()->diffInMilliseconds($this->date_debut);
        }

        $this->update($data);
    }

    public function marquerEchec($erreur = null)
    {
        $this->update([
            'statut' => 'echec',
            'date_fin' => now(),
            'commentaires' => $erreur,
            'duree_traitement' => $this->date_debut ? now()->diffInMilliseconds($this->date_debut) : null
        ]);
    }

    public function ajouterCommentaire($commentaire)
    {
        $commentaireActuel = $this->commentaires ?? '';
        $nouveauCommentaire = $commentaireActuel . "\n[" . now()->format('Y-m-d H:i:s') . "] " . $commentaire;
        
        $this->update(['commentaires' => trim($nouveauCommentaire)]);
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Génération de rapport complet avec anomalies
     */
    public function genererRapportComplet()
    {
        return [
            'import' => [
                'id' => $this->id,
                'fichier' => $this->nom_fichier,
                'taille' => $this->taille_formatee,
                'date_import' => $this->created_at->format('d/m/Y H:i:s'),
                'importateur' => $this->importateur->name ?? 'Inconnu',
                'organisation' => $this->organisation->nom ?? 'Inconnue',
                'statut' => $this->statut,
                'duree' => $this->duree_formatee,
                'methode' => $this->methode_traitement ?? 'standard'
            ],
            'statistiques' => [
                'total_lignes' => $this->total_lignes,
                'lignes_traitees' => $this->lignes_traitees,
                'lignes_importees' => $this->lignes_importees,
                'lignes_erreur' => $this->lignes_erreur,
                'lignes_anomalies' => $this->lignes_anomalies,
                'taux_reussite' => $this->taux_reussite,
                'taux_anomalies' => $this->taux_anomalies,
                'taux_erreur' => $this->taux_erreur
            ],
            'details' => [
                'rapport_import' => $this->rapport_import,
                'rapport_anomalies' => $this->rapport_anomalies,
                'configuration' => $this->configuration_import,
                'commentaires' => $this->commentaires
            ],
            'anomalies_detail' => $this->anomalies()->with('adherent')->get()
        ];
    }

    /**
     * ✅ NOUVELLES MÉTHODES STATIQUES POUR ANOMALIES
     */
    public static function getStatuts()
    {
        return [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'echec' => 'Échec'
        ];
    }

    public static function getMethodesTraitement()
    {
        return [
            'standard' => 'Standard',
            'chunking' => 'Chunking',
            'manuel' => 'Manuel'
        ];
    }

    public static function getStatistiquesParOrganisation($organisationId, $periode = 30)
    {
        return self::where('organisation_id', $organisationId)
            ->where('created_at', '>=', now()->subDays($periode))
            ->selectRaw('
                COUNT(*) as total_imports,
                SUM(CASE WHEN statut = "termine" THEN 1 ELSE 0 END) as imports_reussis,
                SUM(CASE WHEN statut = "echec" THEN 1 ELSE 0 END) as imports_echec,
                SUM(total_lignes) as total_lignes_traitees,
                SUM(lignes_importees) as total_lignes_importees,
                SUM(lignes_anomalies) as total_anomalies,
                AVG(duree_traitement) as duree_moyenne
            ')
            ->first();
    }

    public static function getStatistiquesGenerales($periode = 30)
    {
        return self::where('created_at', '>=', now()->subDays($periode))
            ->selectRaw('
                COUNT(*) as total_imports,
                COUNT(DISTINCT organisation_id) as organisations_actives,
                COUNT(DISTINCT imported_by) as importateurs_actifs,
                SUM(CASE WHEN statut = "termine" THEN 1 ELSE 0 END) as imports_reussis,
                SUM(CASE WHEN statut = "echec" THEN 1 ELSE 0 END) as imports_echec,
                SUM(total_lignes) as total_lignes_traitees,
                SUM(lignes_importees) as total_lignes_importees,
                SUM(lignes_anomalies) as total_anomalies,
                AVG(duree_traitement) as duree_moyenne
            ')
            ->first();
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Obtenir le résumé des anomalies de cet import
     */
    public function getResumeAnomalies()
    {
        if (!$this->has_anomalies) {
            return null;
        }

        $anomalies = $this->anomalies()->get();
        
        return [
            'total' => $anomalies->count(),
            'critiques' => $anomalies->where('type_anomalie', 'critique')->count(),
            'majeures' => $anomalies->where('type_anomalie', 'majeure')->count(),
            'mineures' => $anomalies->where('type_anomalie', 'mineure')->count(),
            'en_attente' => $anomalies->where('statut', 'en_attente')->count(),
            'resolues' => $anomalies->where('statut', 'resolu')->count(),
            'ignorees' => $anomalies->where('statut', 'ignore')->count(),
            'champs_concernes' => $anomalies->groupBy('champ_concerne')->map->count()->toArray()
        ];
    }

    /**
     * ✅ Boot method pour les événements
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($import) {
            if (!$import->statut) {
                $import->statut = 'en_attente';
            }
        });

        static::created(function ($import) {
            \Log::info('Import créé', [
                'import_id' => $import->id,
                'fichier' => $import->nom_fichier,
                'organisation_id' => $import->organisation_id,
                'imported_by' => $import->imported_by
            ]);
        });

        static::updated(function ($import) {
            if ($import->isDirty('statut')) {
                \Log::info('Statut import modifié', [
                    'import_id' => $import->id,
                    'ancien_statut' => $import->getOriginal('statut'),
                    'nouveau_statut' => $import->statut
                ]);
            }
        });
    }
}