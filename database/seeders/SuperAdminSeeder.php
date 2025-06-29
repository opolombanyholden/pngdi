<?php
/**
 * SUPER ADMIN SEEDER - PNGDI
 * Création du Super Administrateur et utilisateurs de test
 * Compatible PHP 7.3.29 - Laravel
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->command->info('🚀 Création du Super Administrateur PNGDI...');
        
        // Récupérer le rôle Super Admin
        $superAdminRole = Role::where('name', Role::SUPER_ADMIN)->first();
        
        if (!$superAdminRole) {
            $this->command->error('❌ Le rôle Super Admin doit être créé avant cet utilisateur !');
            $this->command->error('⚠️  Exécutez d\'abord: php artisan db:seed --class=RoleSeeder');
            return;
        }
        
        // Créer le Super Admin principal
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@pngdi.ga'],
            [
                'name' => 'Super Administrateur PNGDI',
                'password' => Hash::make('Admin@PNGDI2025!'),
                'role' => 'admin', // Ancien système (compatibilité)
                'role_id' => $superAdminRole->id, // Nouveau système
                'status' => 'active',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'phone' => '+241 01 02 03 04',
                'address' => 'Ministère de l\'Intérieur',
                'city' => 'Libreville',
                'country' => 'Gabon',
                'preferences' => [
                    'theme' => 'gabonais',
                    'language' => 'fr',
                    'notifications' => true,
                    'two_factor' => false,
                    'dashboard_layout' => 'modern'
                ],
                'metadata' => [
                    'source' => 'system_seed',
                    'department' => 'Administration Centrale',
                    'function' => 'Super Administrateur Système'
                ]
            ]
        );
        
        if ($superAdmin->wasRecentlyCreated) {
            $this->command->info('✅ Super Administrateur créé avec succès !');
            $this->command->info('📧 Email: admin@pngdi.ga');
            $this->command->info('🔑 Mot de passe: Admin@PNGDI2025!');
            $this->command->info('🎭 Rôle: ' . $superAdminRole->display_name);
        } else {
            // Mettre à jour le rôle si nécessaire
            $superAdmin->update(['role_id' => $superAdminRole->id]);
            $this->command->info('🔄 Super Administrateur mis à jour.');
        }
        
        // Créer des utilisateurs de test pour chaque rôle
        $this->createTestUsers();
        
        // Afficher les statistiques finales
        $this->displayUserStats();
        
        $this->command->info('🎉 Utilisateurs système PNGDI créés avec succès !');
    }
    
    /**
     * Créer des utilisateurs de test pour chaque rôle PNGDI
     */
    private function createTestUsers()
    {
        $this->command->info('👥 Création des utilisateurs de test...');
        
        $testUsers = [
            [
                'name' => 'Admin Général PNGDI',
                'email' => 'admin.general@pngdi.ga',
                'role_name' => Role::ADMIN_GENERAL,
                'password' => 'General@2025!',
                'old_role' => 'admin',
                'department' => 'Direction Générale',
                'function' => 'Administrateur Général'
            ],
            [
                'name' => 'Admin Associations',
                'email' => 'admin.associations@pngdi.ga',
                'role_name' => Role::ADMIN_ASSOCIATIONS,
                'password' => 'Assoc@2025!',
                'old_role' => 'agent',
                'department' => 'Service Associations',
                'function' => 'Responsable Associations'
            ],
            [
                'name' => 'Admin Religieuses',
                'email' => 'admin.religieuses@pngdi.ga',
                'role_name' => Role::ADMIN_RELIGIEUSES,
                'password' => 'Relig@2025!',
                'old_role' => 'agent',
                'department' => 'Service Confessions',
                'function' => 'Responsable Confessions Religieuses'
            ],
            [
                'name' => 'Admin Politiques',
                'email' => 'admin.politiques@pngdi.ga',
                'role_name' => Role::ADMIN_POLITIQUES,
                'password' => 'Polit@2025!',
                'old_role' => 'agent',
                'department' => 'Service Partis Politiques',
                'function' => 'Responsable Partis Politiques'
            ],
            [
                'name' => 'Modérateur PNGDI',
                'email' => 'moderateur@pngdi.ga',
                'role_name' => Role::MODERATEUR,
                'password' => 'Modo@2025!',
                'old_role' => 'agent',
                'department' => 'Service Validation',
                'function' => 'Modérateur Principal'
            ],
            [
                'name' => 'Opérateur PNGDI',
                'email' => 'operateur@pngdi.ga',
                'role_name' => Role::OPERATEUR,
                'password' => 'Opera@2025!',
                'old_role' => 'operator',
                'department' => 'Service Saisie',
                'function' => 'Opérateur de Saisie'
            ],
            [
                'name' => 'Auditeur PNGDI',
                'email' => 'auditeur@pngdi.ga',
                'role_name' => Role::AUDITEUR,
                'password' => 'Audit@2025!',
                'old_role' => 'visitor',
                'department' => 'Service Audit',
                'function' => 'Auditeur Système'
            ]
        ];
        
        $created = 0;
        $updated = 0;
        
        foreach ($testUsers as $userData) {
            $role = Role::where('name', $userData['role_name'])->first();
            
            if (!$role) {
                $this->command->warn("⚠️  Rôle {$userData['role_name']} non trouvé, utilisateur {$userData['name']} ignoré");
                continue;
            }
            
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['old_role'], // Ancien système
                    'role_id' => $role->id, // Nouveau système
                    'status' => 'active',
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'phone' => '+241 0' . rand(1, 9) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                    'address' => 'Ministère de l\'Intérieur',
                    'city' => 'Libreville',
                    'country' => 'Gabon',
                    'preferences' => [
                        'theme' => 'gabonais',
                        'language' => 'fr',
                        'notifications' => true,
                        'two_factor' => false,
                        'dashboard_layout' => 'standard'
                    ],
                    'metadata' => [
                        'source' => 'system_seed',
                        'department' => $userData['department'],
                        'function' => $userData['function']
                    ]
                ]
            );
            
            if ($user->wasRecentlyCreated) {
                $created++;
                $this->command->info("  ✅ {$userData['name']} ({$role->display_name})");
            } else {
                // Mettre à jour le rôle si nécessaire
                $user->update(['role_id' => $role->id]);
                $updated++;
                $this->command->info("  🔄 {$userData['name']} (mis à jour)");
            }
        }
        
        $this->command->info("✅ Utilisateurs de test créés: {$created}");
        $this->command->info("🔄 Utilisateurs mis à jour: {$updated}");
    }
    
    /**
     * Afficher les statistiques des utilisateurs créés
     */
    private function displayUserStats()
    {
        $this->command->info('');
        $this->command->info('📊 STATISTIQUES UTILISATEURS PNGDI');
        $this->command->info('=====================================');
        
        // Statistiques par rôle
        $roleStats = User::whereNotNull('role_id')
                        ->join('roles', 'users.role_id', '=', 'roles.id')
                        ->selectRaw('roles.display_name, roles.color, roles.level, COUNT(*) as count')
                        ->groupBy('roles.id', 'roles.display_name', 'roles.color', 'roles.level')
                        ->orderBy('roles.level', 'desc')
                        ->get();
        
        foreach ($roleStats as $stat) {
            $this->command->info("🎭 {$stat->display_name}: {$stat->count} utilisateur(s) (Niveau {$stat->level})");
        }
        
        // Statistiques générales
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $verifiedUsers = User::where('is_verified', true)->count();
        $newSystemUsers = User::whereNotNull('role_id')->count();
        
        $this->command->info('');
        $this->command->info('📈 STATISTIQUES GÉNÉRALES:');
        $this->command->info("👥 Total utilisateurs: {$totalUsers}");
        $this->command->info("✅ Utilisateurs actifs: {$activeUsers}");
        $this->command->info("🔐 Utilisateurs vérifiés: {$verifiedUsers}");
        $this->command->info("🆕 Nouveau système rôles: {$newSystemUsers}");
        
        // Afficher les comptes créés
        $this->command->info('');
        $this->command->info('🔑 COMPTES SYSTÈME CRÉÉS:');
        $this->command->info('=======================');
        
        $systemUsers = [
            ['email' => 'admin@pngdi.ga', 'password' => 'Admin@PNGDI2025!', 'role' => 'Super Admin'],
            ['email' => 'admin.general@pngdi.ga', 'password' => 'General@2025!', 'role' => 'Admin Général'],
            ['email' => 'admin.associations@pngdi.ga', 'password' => 'Assoc@2025!', 'role' => 'Admin Associations'],
            ['email' => 'admin.religieuses@pngdi.ga', 'password' => 'Relig@2025!', 'role' => 'Admin Religieuses'],
            ['email' => 'admin.politiques@pngdi.ga', 'password' => 'Polit@2025!', 'role' => 'Admin Politiques'],
            ['email' => 'moderateur@pngdi.ga', 'password' => 'Modo@2025!', 'role' => 'Modérateur'],
            ['email' => 'operateur@pngdi.ga', 'password' => 'Opera@2025!', 'role' => 'Opérateur'],
            ['email' => 'auditeur@pngdi.ga', 'password' => 'Audit@2025!', 'role' => 'Auditeur']
        ];
        
        foreach ($systemUsers as $account) {
            $this->command->info("📧 {$account['email']}");
            $this->command->info("   🔑 {$account['password']}");
            $this->command->info("   🎭 {$account['role']}");
            $this->command->info('');
        }
        
        $this->command->warn('⚠️  IMPORTANT: Changez ces mots de passe par défaut en production !');
        $this->command->info('🎨 Thème gabonais activé avec couleurs officielles du drapeau');
        $this->command->info('🔐 Système de permissions granulaires opérationnel');
        $this->command->info('📱 Sessions utilisateurs avec audit trail activé');
    }
}