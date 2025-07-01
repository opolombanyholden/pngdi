#!/bin/bash

echo "=================================================================="
echo "🔧 DIAGNOSTIC COMPLET - PROBLÈME REDIRECTION CONFIRMATION"
echo "=================================================================="
echo ""

# Étape 1: Nettoyage complet des caches Laravel
echo "📋 ÉTAPE 1: NETTOYAGE DES CACHES LARAVEL"
echo "----------------------------------------------------------"

echo "1️⃣ Nettoyage cache des routes..."
php artisan route:clear
echo "✅ Cache des routes vidé"

echo "2️⃣ Nettoyage cache de configuration..."
php artisan config:clear
echo "✅ Cache de configuration vidé"

echo "3️⃣ Nettoyage cache général..."
php artisan cache:clear
echo "✅ Cache général vidé"

echo "4️⃣ Nettoyage cache des vues..."
php artisan view:clear
echo "✅ Cache des vues vidé"

echo "5️⃣ Optimisation automatique (optionnel)..."
php artisan optimize:clear
echo "✅ Optimisation nettoyée"

echo ""
echo "📋 ÉTAPE 2: VÉRIFICATION DES ROUTES ACTIVES"
echo "----------------------------------------------------------"

echo "6️⃣ Génération de la liste des routes de confirmation..."
php artisan route:list | grep -i confirmation > routes_confirmation.txt
echo "✅ Routes de confirmation extraites dans routes_confirmation.txt"

echo "7️⃣ Affichage des routes de confirmation:"
echo ""
cat routes_confirmation.txt

echo ""
echo "8️⃣ Vérification des routes dossiers..."
php artisan route:list | grep "operator/dossiers" > routes_dossiers.txt
echo "✅ Routes dossiers extraites dans routes_dossiers.txt"

echo ""
echo "📋 ÉTAPE 3: DIAGNOSTIC APPROFONDI"
echo "----------------------------------------------------------"

echo "9️⃣ Test de la route spécifique..."
php artisan route:list --name=operator.dossiers.confirmation

echo ""
echo "🔟 Vérification ordre des routes dans operator/dossiers..."
php artisan route:list | grep "operator/dossiers" | head -20

echo ""
echo "📋 ÉTAPE 4: VÉRIFICATION MIDDLEWARES"
echo "----------------------------------------------------------"

echo "1️⃣1️⃣ Vérification des middlewares actifs..."
php artisan route:list --name=operator.dossiers.confirmation --columns=name,uri,action,middleware

echo ""
echo "=================================================================="
echo "✅ DIAGNOSTIC TERMINÉ"
echo "=================================================================="
echo ""
echo "📁 Fichiers générés:"
echo "   - routes_confirmation.txt"
echo "   - routes_dossiers.txt"
echo ""
echo "🔍 Prochaines étapes:"
echo "   1. Analyser le contenu des fichiers générés"
echo "   2. Vérifier l'ordre des routes"
echo "   3. Tester la route manuellement"
echo ""
echo "💡 Pour tester manuellement la route:"
echo "   php artisan tinker"
echo "   >>> route('operator.dossiers.confirmation', 19)"
echo ""