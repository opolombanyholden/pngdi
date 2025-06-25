<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Organisation;

// Tester que le modèle est accessible
echo "Test du modèle Organisation:\n";
echo "Classe existe: " . (class_exists(Organisation::class) ? "OUI" : "NON") . "\n";
echo "Constantes définies:\n";
echo "- TYPE_ASSOCIATION: " . Organisation::TYPE_ASSOCIATION . "\n";
echo "- STATUT_BROUILLON: " . Organisation::STATUT_BROUILLON . "\n";