<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSystemeAnomalies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:systeme-anomalies {--reset : Nettoyer les données de test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester le système d\'enregistrement des anomalies';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('reset')) {
            $this->resetTestData();
            return 0;
        }

        $this->info('🚀 Test du système d\'anomalies...');
        
        try {
            // Test 1: Vérification des classes
            $this->testClasses();
            
            // Test 2: Test des modèles
            $this->testModels();
            
            // Test 3: Test des services
            $this->testServices();
            
            $this->info('✅ Tous les tests sont passés !');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur : ' . $e->getMessage());
            $this->error('📍 Ligne : ' . $e->getLine() . ' dans ' . basename($e->getFile()));
            return 1;
        }
        
        return 0;
    }

    protected function testClasses()
    {
        $this->info('📝 Test 1: Vérification des classes...');
        
        $classes = [
            'AdherentAnomalie' => \App\Models\AdherentAnomalie::class,
            'AnomalieService' => \App\Services\AnomalieService::class,
            'Adherent' => \App\Models\Adherent::class,
        ];
        
        foreach ($classes as $name => $class) {
            if (class_exists($class)) {
                $this->info("✅ Classe {$name} trouvée");
            } else {
                throw new \Exception("Classe {$name} non trouvée");
            }
        }
    }

    protected function testModels()
    {
        $this->info('📝 Test 2: Test des modèles...');
        
        // Test AdherentAnomalie
        $anomalie = new \App\Models\AdherentAnomalie();
        $this->info('✅ Modèle AdherentAnomalie instancié');
        
        // Test méthodes statiques
        $types = \App\Models\AdherentAnomalie::getTypes();
        $this->info('✅ Types: ' . implode(', ', array_keys($types)));
        
        $statuts = \App\Models\AdherentAnomalie::getStatuts();
        $this->info('✅ Statuts: ' . implode(', ', array_keys($statuts)));
        
        // Test statistiques
        $stats = \App\Models\AdherentAnomalie::getStatistiquesGenerales();
        $this->info("✅ Statistiques: {$stats->total} anomalies total");
    }

    protected function testServices()
    {
        $this->info('📝 Test 3: Test des services...');
        
        // Test AnomalieService
        $service = new \App\Services\AnomalieService();
        $this->info('✅ Service AnomalieService instancié');
        
        // Test méthodes du service
        $stats = $service->getStatistiques();
        $this->info("✅ Service statistiques: {$stats->total} anomalies");
    }

    protected function resetTestData()
    {
        $this->info('🧹 Nettoyage des données de test...');
        
        try {
            // Supprimer les anomalies de test
            \App\Models\AdherentAnomalie::where('message_anomalie', 'LIKE', '%test%')->delete();
            
            // Supprimer les adhérents de test
            \App\Models\Adherent::where('nom', 'LIKE', '%TEST%')->delete();
            
            $this->info('✅ Données de test nettoyées');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur nettoyage : ' . $e->getMessage());
        }
    }
}