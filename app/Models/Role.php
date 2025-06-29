<?php
/**
 * MODÈLE ROLE - PNGDI
 * Système de gestion des rôles avec permissions pour PNGDI
 * Compatible PHP 7.3.29 - Laravel
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'level',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    // =================================================================
    // CONSTANTES - RÔLES SYSTÈME PNGDI
    // =================================================================

    const SUPER_ADMIN = 'super_admin';
    const ADMIN_GENERAL = 'admin_general';
    const ADMIN_ASSOCIATIONS = 'admin_associations';
    const ADMIN_RELIGIEUSES = 'admin_religieuses';
    const ADMIN_POLITIQUES = 'admin_politiques';
    const MODERATEUR = 'moderateur';
    const OPERATEUR = 'operateur';
    const AUDITEUR = 'auditeur';

    /**
     * Rôles système prédéfinis avec couleurs gabonaises
     */
    public static function getSystemRoles()
    {
        return [
            self::SUPER_ADMIN => [
                'display_name' => 'Super Administrateur',
                'description' => 'Accès total au système PNGDI - Tous pouvoirs',
                'color' => '#8b1538', // Rouge drapeau gabonais
                'level' => 10
            ],
            self::ADMIN_GENERAL => [
                'display_name' => 'Administrateur Général',
                'description' => 'Gestion globale de toutes les organisations',
                'color' => '#003f7f', // Bleu drapeau gabonais
                'level' => 9
            ],
            self::ADMIN_ASSOCIATIONS => [
                'display_name' => 'Admin Associations',
                'description' => 'Gestion spécialisée des organisations associatives',
                'color' => '#009e3f', // Vert drapeau gabonais
                'level' => 8
            ],
            self::ADMIN_RELIGIEUSES => [
                'display_name' => 'Admin Religieuses',
                'description' => 'Gestion spécialisée des confessions religieuses',
                'color' => '#ffcd00', // Jaune drapeau gabonais
                'level' => 8
            ],
            self::ADMIN_POLITIQUES => [
                'display_name' => 'Admin Politiques',
                'description' => 'Gestion spécialisée des partis politiques',
                'color' => '#007bff', // Bleu complémentaire
                'level' => 8
            ],
            self::MODERATEUR => [
                'display_name' => 'Modérateur',
                'description' => 'Validation et modération des contenus',
                'color' => '#17a2b8', // Cyan
                'level' => 6
            ],
            self::OPERATEUR => [
                'display_name' => 'Opérateur',
                'description' => 'Saisie et consultation des données',
                'color' => '#28a745', // Vert
                'level' => 4
            ],
            self::AUDITEUR => [
                'display_name' => 'Auditeur',
                'description' => 'Consultation uniquement - Accès lecture seule',
                'color' => '#6c757d', // Gris
                'level' => 2
            ]
        ];
    }

    // =================================================================
    // RELATIONS
    // =================================================================

    /**
     * Utilisateurs ayant ce rôle
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Permissions de ce rôle
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
                    ->withTimestamps();
    }

    // =================================================================
    // SCOPES
    // =================================================================

    /**
     * Scope pour rôles actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour ordonner par niveau
     */
    public function scopeOrderByLevel($query, $direction = 'desc')
    {
        return $query->orderBy('level', $direction);
    }

    /**
     * Scope pour filtrer par niveau minimum
     */
    public function scopeMinLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }

    /**
     * Scope pour filtrer par niveau maximum
     */
    public function scopeMaxLevel($query, $level)
    {
        return $query->where('level', '<=', $level);
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

    // =================================================================
    // MÉTHODES PERMISSIONS
    // =================================================================

    /**
     * Attribuer une permission
     */
    public function givePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }
        
        if ($permission && !$this->hasPermission($permission->name)) {
            $this->permissions()->attach($permission->id);
        }
        
        return $this;
    }

    /**
     * Retirer une permission
     */
    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }
        
        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
        
        return $this;
    }

    /**
     * Vérifier si le rôle a une permission
     */
    public function hasPermission($permissionName)
    {
        return $this->permissions()
                    ->where('name', $permissionName)
                    ->exists();
    }

    /**
     * Attribuer plusieurs permissions
     */
    public function givePermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            $this->givePermission($permission);
        }
        
        return $this;
    }

    /**
     * Synchroniser les permissions
     */
    public function syncPermissions(array $permissions)
    {
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $this->permissions()->sync($permissionIds);
        
        return $this;
    }

    /**
     * Obtenir les noms des permissions
     */
    public function getPermissionNames()
    {
        return $this->permissions()->pluck('name')->toArray();
    }

    // =================================================================
    // MÉTHODES UTILITAIRES
    // =================================================================

    /**
     * Vérifier si c'est un rôle système
     */
    public function isSystemRole()
    {
        return array_key_exists($this->name, self::getSystemRoles());
    }

    /**
     * Obtenir la couleur du rôle
     */
    public function getColorAttribute($value)
    {
        return $value ?: '#007bff';
    }

    /**
     * Obtenir le badge HTML du rôle
     */
    public function getBadgeAttribute()
    {
        return "<span class='badge' style='background-color: {$this->color}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>{$this->display_name}</span>";
    }

    /**
     * Obtenir les permissions groupées par catégorie
     */
    public function getPermissionsByCategory()
    {
        return $this->permissions()
                    ->get()
                    ->groupBy('category');
    }

    /**
     * Vérifier si le rôle peut être supprimé
     */
    public function canBeDeleted()
    {
        return !$this->isSystemRole() && $this->users()->count() === 0;
    }

    /**
     * Dupliquer le rôle avec ses permissions
     */
    public function duplicate($newName, $newDisplayName = null)
    {
        $newRole = $this->replicate();
        $newRole->name = $newName;
        $newRole->display_name = $newDisplayName ?: ($this->display_name . ' (Copie)');
        $newRole->save();
        
        // Copier les permissions
        $permissionIds = $this->permissions()->pluck('permissions.id');
        $newRole->permissions()->sync($permissionIds);
        
        return $newRole;
    }

    /**
     * Obtenir le nombre d'utilisateurs
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Obtenir le nombre de permissions
     */
    public function getPermissionsCountAttribute()
    {
        return $this->permissions()->count();
    }

    /**
     * Vérifier si le rôle est de niveau administrateur
     */
    public function isAdminLevel()
    {
        return $this->level >= 8;
    }

    /**
     * Vérifier si le rôle peut gérer un type d'organisation
     */
    public function canManageOrganisationType($type)
    {
        if ($this->name === self::SUPER_ADMIN || $this->name === self::ADMIN_GENERAL) {
            return true;
        }
        
        $typeRoles = [
            'association' => self::ADMIN_ASSOCIATIONS,
            'confession_religieuse' => self::ADMIN_RELIGIEUSES,
            'parti_politique' => self::ADMIN_POLITIQUES
        ];
        
        return $this->name === ($typeRoles[$type] ?? null);
    }

    // =================================================================
    // MÉTHODES STATISTIQUES
    // =================================================================

    /**
     * Obtenir les statistiques du rôle
     */
    public function getStatsAttribute()
    {
        return [
            'users_count' => $this->users()->count(),
            'active_users_count' => $this->users()->where('is_active', true)->count(),
            'permissions_count' => $this->permissions()->count(),
            'level' => $this->level,
            'is_system' => $this->isSystemRole(),
            'is_admin_level' => $this->isAdminLevel(),
        ];
    }

    /**
     * Obtenir les utilisateurs actifs avec ce rôle
     */
    public function getActiveUsersAttribute()
    {
        return $this->users()->where('is_active', true)->get();
    }

    // =================================================================
    // MÉTHODES DE VALIDATION MÉTIER PNGDI
    // =================================================================

    /**
     * Valider la cohérence du rôle pour PNGDI
     */
    public function validateForPNGDI()
    {
        $errors = [];
        
        // Vérifier le niveau
        if ($this->level < 1 || $this->level > 10) {
            $errors[] = 'Le niveau doit être entre 1 et 10';
        }
        
        // Vérifier les permissions selon le niveau
        if ($this->level >= 9 && !$this->hasPermission('users.create')) {
            $errors[] = 'Un rôle de niveau 9+ doit avoir la permission users.create';
        }
        
        // Vérifier la couleur gabonaise
        $gabonColors = ['#009e3f', '#ffcd00', '#003f7f', '#8b1538'];
        if ($this->isSystemRole() && !in_array($this->color, $gabonColors)) {
            $errors[] = 'Les rôles système doivent utiliser les couleurs du drapeau gabonais';
        }
        
        return $errors;
    }

    /**
     * Obtenir les organisations que ce rôle peut gérer
     */
    public function getManagedOrganizationTypes()
    {
        $managedTypes = [];
        
        $types = ['association', 'confession_religieuse', 'parti_politique'];
        
        foreach ($types as $type) {
            if ($this->canManageOrganisationType($type)) {
                $managedTypes[] = $type;
            }
        }
        
        return $managedTypes;
    }

    // =================================================================
    // ACCESSEURS ET MUTATEURS
    // =================================================================

    /**
     * Accessor pour le nom formaté
     */
    public function getFormattedNameAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->name));
    }

    /**
     * Accessor pour la description courte
     */
    public function getShortDescriptionAttribute()
    {
        return strlen($this->description) > 100 
            ? substr($this->description, 0, 97) . '...'
            : $this->description;
    }

    /**
     * Mutateur pour le nom (slug automatique)
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower(str_replace(' ', '_', $value));
    }

    /**
     * Mutateur pour la couleur (validation hex)
     */
    public function setColorAttribute($value)
    {
        // Valider format hex
        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            $this->attributes['color'] = strtolower($value);
        } else {
            $this->attributes['color'] = '#007bff'; // Couleur par défaut
        }
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
            'Description' => $this->description,
            'Niveau' => $this->level,
            'Couleur' => $this->color,
            'Actif' => $this->is_active ? 'Oui' : 'Non',
            'Système' => $this->isSystemRole() ? 'Oui' : 'Non',
            'Utilisateurs' => $this->users_count,
            'Permissions' => $this->permissions_count,
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
            'description' => $this->short_description,
            'color' => $this->color,
            'level' => $this->level,
            'is_active' => $this->is_active,
            'is_system' => $this->isSystemRole(),
            'users_count' => $this->users_count,
            'permissions_count' => $this->permissions_count,
            'managed_types' => $this->getManagedOrganizationTypes(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    // =================================================================
    // MÉTHODES STATIQUES UTILITAIRES
    // =================================================================

    /**
     * Obtenir tous les rôles par niveau
     */
    public static function getByLevel()
    {
        return self::orderByLevel()->get()->groupBy('level');
    }

    /**
     * Obtenir les rôles disponibles pour un type d'organisation
     */
    public static function getForOrganizationType($type)
    {
        return self::active()->get()->filter(function ($role) use ($type) {
            return $role->canManageOrganisationType($type);
        });
    }

    /**
     * Créer un rôle système
     */
    public static function createSystemRole($name, $data = null)
    {
        $systemRoles = self::getSystemRoles();
        
        if (!isset($systemRoles[$name])) {
            throw new \InvalidArgumentException("Rôle système {$name} non défini");
        }
        
        $roleData = $systemRoles[$name];
        
        if ($data) {
            $roleData = array_merge($roleData, $data);
        }
        
        return self::create([
            'name' => $name,
            'display_name' => $roleData['display_name'],
            'description' => $roleData['description'],
            'color' => $roleData['color'],
            'level' => $roleData['level'],
            'is_active' => true
        ]);
    }

    /**
     * Obtenir le rôle le plus élevé
     */
    public static function getHighestLevel()
    {
        return self::orderByLevel('desc')->first();
    }

    /**
     * Obtenir les couleurs utilisées
     */
    public static function getUsedColors()
    {
        return self::pluck('color')->unique()->values();
    }
}