<?php
/**
 * MODÈLE PERMISSION - PNGDI
 * Système de gestion des permissions granulaires pour PNGDI
 * Compatible PHP 7.3.29 - Laravel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'category',
        'description'
    ];

    // =================================================================
    // CONSTANTES - PERMISSIONS SYSTÈME PNGDI
    // =================================================================

    /**
     * Permissions système prédéfinies organisées par catégories
     */
    public static function getSystemPermissions()
    {
        return [
            // Gestion Utilisateurs
            'users' => [
                'users.view' => 'Consulter les utilisateurs',
                'users.create' => 'Créer des utilisateurs',
                'users.edit' => 'Modifier les utilisateurs',
                'users.delete' => 'Supprimer les utilisateurs',
                'users.export' => 'Exporter les utilisateurs',
                'users.import' => 'Importer les utilisateurs',
                'users.roles' => 'Gérer les rôles utilisateurs',
                'users.permissions' => 'Gérer les permissions utilisateurs',
                'users.sessions' => 'Gérer les sessions utilisateurs',
                'users.verify' => 'Vérifier les comptes utilisateurs'
            ],
            
            // Gestion Organisations
            'organizations' => [
                'orgs.view' => 'Consulter les organisations',
                'orgs.create' => 'Créer des organisations',
                'orgs.edit' => 'Modifier les organisations',
                'orgs.delete' => 'Supprimer les organisations',
                'orgs.validate' => 'Valider les organisations',
                'orgs.reject' => 'Rejeter les organisations',
                'orgs.archive' => 'Archiver les organisations',
                'orgs.export' => 'Exporter les organisations',
                'orgs.suspend' => 'Suspendre les organisations',
                'orgs.reactivate' => 'Réactiver les organisations',
                'orgs.manage_adherents' => 'Gérer les adhérents',
                'orgs.manage_documents' => 'Gérer les documents'
            ],
            
            // Gestion Workflow
            'workflow' => [
                'workflow.view' => 'Consulter le workflow',
                'workflow.assign' => 'Assigner des tâches',
                'workflow.validate' => 'Valider les étapes',
                'workflow.reject' => 'Rejeter les demandes',
                'workflow.reports' => 'Générer des rapports workflow',
                'workflow.lock' => 'Verrouiller les dossiers',
                'workflow.unlock' => 'Déverrouiller les dossiers',
                'workflow.comment' => 'Commenter les dossiers',
                'workflow.history' => 'Consulter l\'historique',
                'workflow.priority' => 'Modifier les priorités'
            ],
            
            // Gestion Système
            'system' => [
                'system.config' => 'Configuration système',
                'system.backup' => 'Sauvegardes système',
                'system.logs' => 'Consulter les logs',
                'system.reports' => 'Rapports système',
                'system.maintenance' => 'Mode maintenance',
                'system.updates' => 'Mises à jour système',
                'system.monitoring' => 'Monitoring système',
                'system.security' => 'Paramètres de sécurité',
                'system.integrations' => 'Gestion des intégrations',
                'system.notifications' => 'Configuration notifications'
            ],
            
            // Gestion Contenus
            'content' => [
                'content.view' => 'Consulter les contenus',
                'content.create' => 'Créer des contenus',
                'content.edit' => 'Modifier les contenus',
                'content.delete' => 'Supprimer les contenus',
                'content.publish' => 'Publier les contenus',
                'content.moderate' => 'Modérer les contenus',
                'content.media' => 'Gérer les médias',
                'content.templates' => 'Gérer les templates'
            ],

            // Gestion Rapports et Analytics
            'reports' => [
                'reports.view' => 'Consulter les rapports',
                'reports.create' => 'Créer des rapports',
                'reports.export' => 'Exporter les rapports',
                'reports.schedule' => 'Programmer les rapports',
                'reports.analytics' => 'Accès aux analytics',
                'reports.statistics' => 'Statistiques avancées'
            ],

            // Gestion API et Intégrations
            'api' => [
                'api.access' => 'Accès API',
                'api.manage' => 'Gérer les clés API',
                'api.webhooks' => 'Gérer les webhooks',
                'api.logs' => 'Logs API'
            ]
        ];
    }

    // =================================================================
    // RELATIONS
    // =================================================================

    /**
     * Rôles ayant cette permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
                    ->withTimestamps();
    }

    // =================================================================
    // SCOPES
    // =================================================================

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope pour recherche
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('display_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope pour permissions système uniquement
     */
    public function scopeSystemOnly($query)
    {
        $systemPermissions = collect(self::getSystemPermissions())
                           ->flatten()
                           ->keys()
                           ->toArray();
        
        return $query->whereIn('name', $systemPermissions);
    }

    /**
     * Scope pour permissions par niveau d'accès
     */
    public function scopeByAccessLevel($query, $level)
    {
        $levelCategories = [
            'read' => ['view', 'reports'],
            'write' => ['create', 'edit'],
            'admin' => ['delete', 'system', 'users'],
            'super' => ['permissions', 'config', 'backup']
        ];
        
        if (isset($levelCategories[$level])) {
            return $query->where(function ($q) use ($levelCategories, $level) {
                foreach ($levelCategories[$level] as $category) {
                    $q->orWhere('name', 'like', "%{$category}%");
                }
            });
        }
        
        return $query;
    }

    // =================================================================
    // MÉTHODES UTILITAIRES
    // =================================================================

    /**
     * Obtenir toutes les catégories
     */
    public static function getCategories()
    {
        return self::distinct('category')->pluck('category')->sort()->values();
    }

    /**
     * Obtenir les permissions par catégorie
     */
    public static function getByCategory()
    {
        return self::all()->groupBy('category');
    }

    /**
     * Vérifier si c'est une permission système
     */
    public function isSystemPermission()
    {
        $systemPermissions = collect(self::getSystemPermissions())
                           ->flatten()
                           ->keys();
        
        return $systemPermissions->contains($this->name);
    }

    /**
     * Obtenir le nom formaté de la catégorie
     */
    public function getCategoryLabelAttribute()
    {
        $categories = [
            'users' => 'Gestion Utilisateurs',
            'organizations' => 'Gestion Organisations',
            'workflow' => 'Gestion Workflow',
            'system' => 'Gestion Système',
            'content' => 'Gestion Contenus',
            'reports' => 'Rapports et Analytics',
            'api' => 'API et Intégrations'
        ];
        
        return $categories[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Obtenir l'icône de la catégorie (Font Awesome)
     */
    public function getCategoryIconAttribute()
    {
        $icons = [
            'users' => 'fas fa-users',
            'organizations' => 'fas fa-building',
            'workflow' => 'fas fa-tasks',
            'system' => 'fas fa-cogs',
            'content' => 'fas fa-file-alt',
            'reports' => 'fas fa-chart-line',
            'api' => 'fas fa-code'
        ];
        
        return $icons[$this->category] ?? 'fas fa-key';
    }

    /**
     * Obtenir la couleur de la catégorie (couleurs gabonaises)
     */
    public function getCategoryColorAttribute()
    {
        $colors = [
            'users' => '#003f7f',        // Bleu gabonais
            'organizations' => '#009e3f', // Vert gabonais
            'workflow' => '#ffcd00',      // Jaune gabonais
            'system' => '#8b1538',        // Rouge gabonais
            'content' => '#17a2b8',       // Cyan
            'reports' => '#28a745',       // Vert
            'api' => '#6f42c1'           // Violet
        ];
        
        return $colors[$this->category] ?? '#007bff';
    }

    /**
     * Vérifier si la permission peut être supprimée
     */
    public function canBeDeleted()
    {
        return !$this->isSystemPermission() && $this->roles()->count() === 0;
    }

    /**
     * Obtenir le niveau de risque de la permission
     */
    public function getRiskLevelAttribute()
    {
        $highRiskPatterns = ['delete', 'system', 'config', 'backup', 'permissions'];
        $mediumRiskPatterns = ['create', 'edit', 'validate', 'reject'];
        
        foreach ($highRiskPatterns as $pattern) {
            if (strpos($this->name, $pattern) !== false) {
                return 'high';
            }
        }
        
        foreach ($mediumRiskPatterns as $pattern) {
            if (strpos($this->name, $pattern) !== false) {
                return 'medium';
            }
        }
        
        return 'low';
    }

    /**
     * Obtenir le badge HTML de la permission
     */
    public function getBadgeAttribute()
    {
        $riskColors = [
            'high' => '#dc3545',
            'medium' => '#ffc107',
            'low' => '#28a745'
        ];
        
        $color = $riskColors[$this->risk_level];
        
        return "<span class='badge' style='background-color: {$color}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>{$this->display_name}</span>";
    }

    // =================================================================
    // MÉTHODES DE VALIDATION MÉTIER PNGDI
    // =================================================================

    /**
     * Valider la cohérence de la permission pour PNGDI
     */
    public function validateForPNGDI()
    {
        $errors = [];
        
        // Vérifier le format du nom
        if (!preg_match('/^[a-z]+\.[a-z_]+$/', $this->name)) {
            $errors[] = 'Le nom doit suivre le format "catégorie.action"';
        }
        
        // Vérifier la catégorie
        $validCategories = array_keys(self::getSystemPermissions());
        $category = explode('.', $this->name)[0];
        
        if (!in_array($category, $validCategories) && $this->isSystemPermission()) {
            $errors[] = 'Catégorie non valide pour une permission système';
        }
        
        // Vérifier la cohérence avec la catégorie stockée
        if ($this->category !== $category) {
            $errors[] = 'La catégorie stockée ne correspond pas au nom de la permission';
        }
        
        return $errors;
    }

    /**
     * Obtenir les permissions requises pour cette permission
     */
    public function getRequiredPermissions()
    {
        $dependencies = [
            // Pour éditer, il faut pouvoir voir
            'users.edit' => ['users.view'],
            'users.delete' => ['users.view', 'users.edit'],
            'orgs.edit' => ['orgs.view'],
            'orgs.delete' => ['orgs.view', 'orgs.edit'],
            'orgs.validate' => ['orgs.view'],
            'workflow.assign' => ['workflow.view'],
            'workflow.validate' => ['workflow.view'],
            'system.config' => ['system.view'],
        ];
        
        return $dependencies[$this->name] ?? [];
    }

    /**
     * Vérifier si cette permission est incompatible avec une autre
     */
    public function isIncompatibleWith($permissionName)
    {
        $incompatibilities = [
            // Auditeur ne peut pas modifier
            'users.view' => ['users.create', 'users.edit', 'users.delete'],
            'orgs.view' => ['orgs.create', 'orgs.edit', 'orgs.delete'],
        ];
        
        return in_array($permissionName, $incompatibilities[$this->name] ?? []);
    }

    // =================================================================
    // MÉTHODES STATISTIQUES
    // =================================================================

    /**
     * Obtenir les statistiques de la permission
     */
    public function getStatsAttribute()
    {
        return [
            'roles_count' => $this->roles()->count(),
            'users_count' => $this->getUsersCount(),
            'risk_level' => $this->risk_level,
            'is_system' => $this->isSystemPermission(),
            'category' => $this->category,
            'required_permissions' => $this->getRequiredPermissions(),
        ];
    }

    /**
     * Obtenir le nombre d'utilisateurs ayant cette permission
     */
    public function getUsersCount()
    {
        return User::whereHas('roleModel', function ($query) {
            $query->whereHas('permissions', function ($q) {
                $q->where('permissions.id', $this->id);
            });
        })->count();
    }

    /**
     * Obtenir les utilisateurs ayant cette permission
     */
    public function getUsers()
    {
        return User::whereHas('roleModel', function ($query) {
            $query->whereHas('permissions', function ($q) {
                $q->where('permissions.id', $this->id);
            });
        })->get();
    }

    // =================================================================
    // ACCESSEURS ET MUTATEURS
    // =================================================================

    /**
     * Accessor pour la description courte
     */
    public function getShortDescriptionAttribute()
    {
        return strlen($this->description) > 80 
            ? substr($this->description, 0, 77) . '...'
            : $this->description;
    }

    /**
     * Accessor pour l'action de la permission
     */
    public function getActionAttribute()
    {
        return explode('.', $this->name)[1] ?? 'unknown';
    }

    /**
     * Mutateur pour le nom (validation format)
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    /**
     * Mutateur pour automatiquement définir la catégorie
     */
    public function setCategoryFromName()
    {
        $this->category = explode('.', $this->name)[0] ?? 'other';
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
            'Nom' => $this->name,
            'Nom d\'affichage' => $this->display_name,
            'Catégorie' => $this->category_label,
            'Description' => $this->description,
            'Niveau de risque' => ucfirst($this->risk_level),
            'Système' => $this->isSystemPermission() ? 'Oui' : 'Non',
            'Rôles' => $this->roles()->count(),
            'Utilisateurs' => $this->getUsersCount(),
            'Créé le' => $this->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Obtenir la représentation JSON pour l'API
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'category_icon' => $this->category_icon,
            'category_color' => $this->category_color,
            'description' => $this->short_description,
            'risk_level' => $this->risk_level,
            'is_system' => $this->isSystemPermission(),
            'roles_count' => $this->roles()->count(),
            'users_count' => $this->getUsersCount(),
            'required_permissions' => $this->getRequiredPermissions(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    // =================================================================
    // MÉTHODES STATIQUES UTILITAIRES
    // =================================================================

    /**
     * Créer une permission système
     */
    public static function createSystemPermission($name, $displayName, $category, $description = null)
    {
        return self::create([
            'name' => $name,
            'display_name' => $displayName,
            'category' => $category,
            'description' => $description ?: "Permission pour {$displayName}"
        ]);
    }

    /**
     * Obtenir les permissions par niveau de risque
     */
    public static function getByRiskLevel($level = null)
    {
        $permissions = self::all()->groupBy('risk_level');
        
        return $level ? ($permissions[$level] ?? collect()) : $permissions;
    }

    /**
     * Obtenir les permissions les plus utilisées
     */
    public static function getMostUsed($limit = 10)
    {
        return self::withCount('roles')
                   ->orderBy('roles_count', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Obtenir les permissions inutilisées
     */
    public static function getUnused()
    {
        return self::doesntHave('roles')->get();
    }

    /**
     * Recherche intelligente de permissions
     */
    public static function smartSearch($query)
    {
        return self::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('display_name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%")
              ->orWhere('category', 'like', "%{$query}%");
        })
        ->orderByRaw("
            CASE 
                WHEN name = '{$query}' THEN 1
                WHEN name LIKE '{$query}%' THEN 2
                WHEN display_name LIKE '{$query}%' THEN 3
                ELSE 4
            END
        ")
        ->get();
    }
}