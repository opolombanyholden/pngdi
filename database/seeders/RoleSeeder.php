<?php
/**
 * ROLE SEEDER - PNGDI
 * Initialisation des rôles système avec permissions pour PNGDI
 * Compatible PHP 7.3.29 - Laravel
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->command->info('🚀 Création des rôles système PNGDI...');
        
        $roles = Role::getSystemRoles();
        $created = 0;
        $updated = 0;
        
        foreach ($roles as $name => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'color' => $roleData['color'],
                    'level' => $roleData['level'],
                    'is_active' => true
                ]
            );
            
            if ($role->wasRecentlyCreated) {
                $created++;
                $this->command->info("✅ Créé: {$roleData['display_name']} (Niveau {$roleData['level']})");
            } else {
                // Mettre à jour les données si nécessaire
                $role->update([
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'color' => $roleData['color'],
                    'level' => $roleData['level'],
                    'is_active' => true
                ]);
                $updated++;
                $this->command->info("🔄 Mis à jour: {$roleData['display_name']}");
            }
            
            // Attribuer les permissions selon le rôle
            $this->assignPermissionsToRole($role);
        }
        
        $this->command->info('');
        $this->command->info("✅ Rôles créés: {$created}");
        $this->command->info("🔄 Rôles mis à jour: {$updated}");
        $this->command->info('📊 Total rôles système: ' . Role::count());
        
        // Valider la cohérence
        $this->validateRoles();
        
        $this->command->info('🎉 Rôles système PNGDI initialisés avec succès !');
    }
    
    /**
     * Attribuer les permissions à chaque rôle selon la logique métier PNGDI
     */
    private function assignPermissionsToRole(Role $role)
    {
        $permissions = [];
        
        $this->command->info("🔑 Attribution permissions pour: {$role->display_name}");
        
        switch ($role->name) {
            case Role::SUPER_ADMIN:
                // Toutes les permissions pour le Super Admin
                $permissions = Permission::all()->pluck('name')->toArray();
                $this->command->line("  🌟 SUPER ADMIN: Toutes les permissions (" . count($permissions) . ")");
                break;
                
            case Role::ADMIN_GENERAL:
                $permissions = [
                    // Gestion Utilisateurs (presque complète)
                    'users.view', 'users.create', 'users.edit', 'users.export', 'users.import',
                    'users.roles', 'users.sessions', 'users.verify',
                    
                    // Gestion Organisations (complète)
                    'orgs.view', 'orgs.create', 'orgs.edit', 'orgs.validate', 'orgs.reject',
                    'orgs.archive', 'orgs.export', 'orgs.suspend', 'orgs.reactivate',
                    'orgs.manage_adherents', 'orgs.manage_documents',
                    
                    // Gestion Workflow (complète)
                    'workflow.view', 'workflow.assign', 'workflow.validate', 'workflow.reject',
                    'workflow.reports', 'workflow.lock', 'workflow.unlock', 'workflow.comment',
                    'workflow.history', 'workflow.priority',
                    
                    // Gestion Contenus (complète)
                    'content.view', 'content.create', 'content.edit', 'content.delete',
                    'content.publish', 'content.moderate', 'content.media', 'content.templates',
                    
                    // Rapports (complète)
                    'reports.view', 'reports.create', 'reports.export', 'reports.schedule',
                    'reports.analytics', 'reports.statistics',
                    
                    // Système (limité)
                    'system.reports', 'system.monitoring', 'system.logs'
                ];
                $this->command->line("  👨‍💼 ADMIN GÉNÉRAL: " . count($permissions) . " permissions");
                break;
                
            case Role::ADMIN_ASSOCIATIONS:
                $permissions = [
                    // Organisations (spécialisé associations)
                    'orgs.view', 'orgs.create', 'orgs.edit', 'orgs.validate', 'orgs.reject',
                    'orgs.export', 'orgs.suspend', 'orgs.reactivate',
                    'orgs.manage_adherents', 'orgs.manage_documents',
                    
                    // Workflow (validation)
                    'workflow.view', 'workflow.assign', 'workflow.validate', 'workflow.reject',
                    'workflow.reports', 'workflow.comment', 'workflow.history',
                    
                    // Contenus (modération)
                    'content.view', 'content.create', 'content.edit', 'content.moderate',
                    'content.media',
                    
                    // Rapports (consultation)
                    'reports.view', 'reports.create', 'reports.export'
                ];
                $this->command->line("  🏢 ADMIN ASSOCIATIONS: " . count($permissions) . " permissions");
                break;
                
            case Role::ADMIN_RELIGIEUSES:
                $permissions = [
                    // Organisations (spécialisé religieuses)
                    'orgs.view', 'orgs.create', 'orgs.edit', 'orgs.validate', 'orgs.reject',
                    'orgs.export', 'orgs.suspend', 'orgs.reactivate',
                    'orgs.manage_adherents', 'orgs.manage_documents',
                    
                    // Workflow (validation)
                    'workflow.view', 'workflow.assign', 'workflow.validate', 'workflow.reject',
                    'workflow.reports', 'workflow.comment', 'workflow.history',
                    
                    // Contenus (modération)
                    'content.view', 'content.create', 'content.edit', 'content.moderate',
                    'content.media',
                    
                    // Rapports (consultation)
                    'reports.view', 'reports.create', 'reports.export'
                ];
                $this->command->line("  ⛪ ADMIN RELIGIEUSES: " . count($permissions) . " permissions");
                break;
                
            case Role::ADMIN_POLITIQUES:
                $permissions = [
                    // Organisations (spécialisé politiques)
                    'orgs.view', 'orgs.create', 'orgs.edit', 'orgs.validate', 'orgs.reject',
                    'orgs.export', 'orgs.suspend', 'orgs.reactivate',
                    'orgs.manage_adherents', 'orgs.manage_documents',
                    
                    // Workflow (validation)
                    'workflow.view', 'workflow.assign', 'workflow.validate', 'workflow.reject',
                    'workflow.reports', 'workflow.comment', 'workflow.history',
                    
                    // Contenus (modération)
                    'content.view', 'content.create', 'content.edit', 'content.moderate',
                    'content.media',
                    
                    // Rapports (consultation)
                    'reports.view', 'reports.create', 'reports.export'
                ];
                $this->command->line("  🗳️ ADMIN POLITIQUES: " . count($permissions) . " permissions");
                break;
                
            case Role::MODERATEUR:
                $permissions = [
                    // Organisations (validation uniquement)
                    'orgs.view', 'orgs.validate', 'orgs.reject', 'orgs.export',
                    
                    // Workflow (validation et modération)
                    'workflow.view', 'workflow.validate', 'workflow.reject',
                    'workflow.comment', 'workflow.history',
                    
                    // Contenus (modération complète)
                    'content.view', 'content.moderate', 'content.publish',
                    
                    // Rapports (consultation)
                    'reports.view', 'reports.export'
                ];
                $this->command->line("  🛡️ MODÉRATEUR: " . count($permissions) . " permissions");
                break;
                
            case Role::OPERATEUR:
                $permissions = [
                    // Organisations (saisie et consultation)
                    'orgs.view', 'orgs.create', 'orgs.edit', 'orgs.export',
                    'orgs.manage_adherents', 'orgs.manage_documents',
                    
                    // Workflow (consultation et commentaires)
                    'workflow.view', 'workflow.comment',
                    
                    // Contenus (création et édition)
                    'content.view', 'content.create', 'content.edit', 'content.media',
                    
                    // Rapports (consultation de base)
                    'reports.view'
                ];
                $this->command->line("  👥 OPÉRATEUR: " . count($permissions) . " permissions");
                break;
                
            case Role::AUDITEUR:
                $permissions = [
                    // Consultation uniquement
                    'orgs.view',
                    'workflow.view', 'workflow.history',
                    'content.view',
                    'reports.view', 'reports.analytics', 'reports.statistics',
                    'system.reports', 'system.logs'
                ];
                $this->command->line("  📊 AUDITEUR: " . count($permissions) . " permissions (lecture seule)");
                break;
        }
        
        // Synchroniser les permissions
        if (!empty($permissions)) {
            $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
            $role->permissions()->sync($permissionIds);
            
            $this->command->line("  ✅ {$permissionIds->count()} permissions synchronisées");
        }
    }
    
    /**
     * Valider la cohérence des rôles créés
     */
    private function validateRoles()
    {
        $this->command->info('🔍 Validation de la cohérence des rôles...');
        
        $errors = [];
        $warnings = [];
        
        // Vérifier que tous les rôles système sont présents
        $systemRoles = collect(Role::getSystemRoles())->keys();
        $dbRoles = Role::pluck('name');
        
        $missing = $systemRoles->diff($dbRoles);
        if ($missing->count() > 0) {
            $errors[] = "Rôles manquants: " . $missing->implode(', ');
        }
        
        // Vérifier les niveaux hiérarchiques
        $levelConflicts = Role::select('level')
                             ->selectRaw('GROUP_CONCAT(name) as roles')
                             ->groupBy('level')
                             ->havingRaw('COUNT(*) > 3') // Max 3 rôles par niveau
                             ->get();
        
        foreach ($levelConflicts as $conflict) {
            $warnings[] = "Niveau {$conflict->level} a trop de rôles: {$conflict->roles}";
        }
        
        // Vérifier les couleurs gabonaises pour les rôles système
        $gabonColors = ['#009e3f', '#ffcd00', '#003f7f', '#8b1538'];
        $nonGabonRoles = Role::whereIn('name', $systemRoles->toArray())
                            ->whereNotIn('color', $gabonColors)
                            ->pluck('name');
        
        if ($nonGabonRoles->count() > 0) {
            $warnings[] = "Rôles sans couleurs gabonaises: " . $nonGabonRoles->implode(', ');
        }
        
        // Vérifier la cohérence des permissions
        foreach (Role::all() as $role) {
            $permissionCount = $role->permissions()->count();
            
            if ($role->name === Role::SUPER_ADMIN && $permissionCount < Permission::count()) {
                $warnings[] = "Super Admin n'a pas toutes les permissions ({$permissionCount}/" . Permission::count() . ")";
            }
            
            if ($role->name === Role::AUDITEUR && $role->permissions()->where('name', 'like', '%delete%')->exists()) {
                $errors[] = "Auditeur a des permissions de suppression (incohérent)";
            }
        }
        
        // Afficher les résultats
        if (count($errors) > 0) {
            $this->command->error('❌ Erreurs détectées:');
            foreach ($errors as $error) {
                $this->command->error("  - {$error}");
            }
        }
        
        if (count($warnings) > 0) {
            $this->command->warn('⚠️  Avertissements:');
            foreach ($warnings as $warning) {
                $this->command->warn("  - {$warning}");
            }
        }
        
        if (count($errors) === 0 && count($warnings) === 0) {
            $this->command->info('✅ Tous les rôles sont cohérents !');
        }
        
        // Statistiques détaillées
        $this->command->info('📊 Statistiques des rôles:');
        
        foreach (Role::orderByLevel('desc')->get() as $role) {
            $permissionsCount = $role->permissions()->count();
            $usersCount = $role->users()->count();
            
            $this->command->info("  🎭 {$role->display_name}:");
            $this->command->info("     - Niveau: {$role->level}");
            $this->command->info("     - Couleur: {$role->color}");
            $this->command->info("     - Permissions: {$permissionsCount}");
            $this->command->info("     - Utilisateurs: {$usersCount}");
        }
    }
}