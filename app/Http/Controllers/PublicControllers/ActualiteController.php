<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActualiteController extends Controller
{
    /**
     * Base de données simulée des actualités
     */
    public static function getAllActualites()
    {
        return [
            [
                'id' => 1,
                'slug' => 'nouvelle-procedure-formalisation-simplifiee',
                'titre' => 'Nouvelle procédure de formalisation simplifiée',
                'date' => '2025-01-15',
                'extrait' => 'Le Ministère de l\'Intérieur annonce la simplification des démarches administratives pour la création d\'associations. Cette nouvelle procédure vise à réduire les délais de traitement et à faciliter l\'accès aux services.',
                'contenu' => '
                    <p>Le Ministère de l\'Intérieur et de la Sécurité a le plaisir d\'annoncer la mise en place d\'une nouvelle procédure simplifiée pour la formalisation des organisations associatives. Cette initiative s\'inscrit dans le cadre de la modernisation de l\'administration publique et vise à faciliter l\'engagement civique.</p>
                    
                    <h3>Les principales améliorations</h3>
                    <ul>
                        <li>Réduction du nombre de documents requis de 12 à 7</li>
                        <li>Délai de traitement ramené de 30 à 15 jours ouvrables</li>
                        <li>Possibilité de soumettre tous les documents en ligne</li>
                        <li>Suivi en temps réel de l\'avancement du dossier</li>
                        <li>Notification automatique à chaque étape</li>
                    </ul>
                    
                    <h3>Qui est concerné ?</h3>
                    <p>Cette nouvelle procédure s\'applique à toutes les nouvelles demandes de création d\'associations à partir du 1er février 2025. Les dossiers en cours de traitement continueront à suivre l\'ancienne procédure.</p>
                    
                    <h3>Comment en bénéficier ?</h3>
                    <p>Pour bénéficier de cette procédure simplifiée, il suffit de créer un compte sur le portail PNGDI et de suivre les étapes indiquées. Un guide détaillé est disponible dans la section "Guides et ressources".</p>
                    
                    <p>Pour toute question, n\'hésitez pas à contacter notre service d\'assistance via le formulaire de contact ou par téléphone au +241 01 23 45 67.</p>
                ',
                'image' => null,
                'categorie' => 'Réglementation',
                'auteur' => 'Administration PNGDI',
                'vues' => 234,
                'tags' => ['association', 'procédure', 'simplification', 'formalisation']
            ],
            [
                'id' => 2,
                'slug' => 'seminaire-national-associations-2025',
                'titre' => 'Séminaire national sur les associations',
                'date' => '2025-01-10',
                'extrait' => 'Un séminaire de formation pour les responsables d\'associations se tiendra du 25 au 27 janvier 2025 à Libreville. Inscription gratuite mais obligatoire.',
                'contenu' => '
                    <p>Le Ministère de l\'Intérieur organise un séminaire national de formation pour les responsables d\'associations du 25 au 27 janvier 2025 à Libreville.</p>
                    
                    <h3>Programme</h3>
                    <ul>
                        <li>Jour 1 : Cadre juridique et réglementaire</li>
                        <li>Jour 2 : Gestion administrative et financière</li>
                        <li>Jour 3 : Communication et développement</li>
                    </ul>
                    
                    <h3>Inscription</h3>
                    <p>L\'inscription est gratuite mais obligatoire. Les places sont limitées à 200 participants.</p>
                    
                    <h3>Lieu</h3>
                    <p>Centre International de Conférences de Libreville<br>
                    Boulevard du Bord de Mer, Libreville</p>
                    
                    <h3>Contact</h3>
                    <p>Pour plus d\'informations et inscriptions :<br>
                    Email : seminaire@pngdi.ga<br>
                    Tél : +241 01 23 45 68</p>
                ',
                'image' => null,
                'categorie' => 'Événement',
                'auteur' => 'Direction des Associations',
                'vues' => 156,
                'tags' => ['séminaire', 'formation', 'associations']
            ],
            [
                'id' => 3,
                'slug' => 'mise-a-jour-documents-requis-ong',
                'titre' => 'Mise à jour des documents requis pour les ONG',
                'date' => '2025-01-05',
                'extrait' => 'La liste des documents nécessaires pour la création d\'ONG a été actualisée. Consultez les nouvelles exigences pour éviter tout retard dans le traitement de vos dossiers.',
                'contenu' => '
                    <p>Suite aux recommandations du comité de simplification administrative, la liste des documents requis pour la création d\'ONG a été révisée.</p>
                    
                    <h3>Nouveaux documents requis</h3>
                    <ol>
                        <li>Statuts de l\'ONG (3 exemplaires)</li>
                        <li>Procès-verbal de l\'assemblée constitutive</li>
                        <li>Liste des membres fondateurs avec leurs coordonnées</li>
                        <li>Plan d\'action triennal détaillé</li>
                        <li>Budget prévisionnel sur 3 ans</li>
                        <li>Justificatif de domicile du siège social</li>
                        <li>CV du président et du trésorier</li>
                    </ol>
                    
                    <h3>Documents supprimés</h3>
                    <p>Les documents suivants ne sont plus requis :</p>
                    <ul>
                        <li>Certificat de moralité</li>
                        <li>Attestation bancaire (peut être fournie après agrément)</li>
                        <li>Photos du local (sauf cas spécifiques)</li>
                    </ul>
                    
                    <p>Ces modifications entrent en vigueur immédiatement pour toutes les nouvelles demandes.</p>
                ',
                'image' => null,
                'categorie' => 'Documentation',
                'auteur' => 'Service Juridique',
                'vues' => 412,
                'tags' => ['ONG', 'documents', 'mise à jour']
            ],
            [
                'id' => 4,
                'slug' => 'bilan-annuel-2024-organisations',
                'titre' => 'Bilan 2024 : 200 nouvelles organisations créées',
                'date' => '2025-01-02',
                'extrait' => 'Le PNGDI dresse un bilan positif de l\'année 2024 avec plus de 200 nouvelles organisations formalisées, marquant une augmentation de 35% par rapport à 2023.',
                'contenu' => '
                    <p>Le Portail National de Gestion des Libertés Individuelles (PNGDI) présente son bilan annuel pour l\'année 2024, marquée par une croissance significative du nombre d\'organisations formalisées.</p>
                    
                    <h3>Chiffres clés 2024</h3>
                    <ul>
                        <li>200 nouvelles organisations créées (+35% vs 2023)</li>
                        <li>87 associations</li>
                        <li>45 ONG</li>
                        <li>12 confessions religieuses</li>
                        <li>8 partis politiques</li>
                        <li>48 autres organisations</li>
                    </ul>
                    
                    <h3>Répartition géographique</h3>
                    <p>Les créations se répartissent comme suit :</p>
                    <ul>
                        <li>Libreville : 45%</li>
                        <li>Port-Gentil : 20%</li>
                        <li>Franceville : 15%</li>
                        <li>Autres provinces : 20%</li>
                    </ul>
                    
                    <h3>Perspectives 2025</h3>
                    <p>Avec la nouvelle procédure simplifiée et le portail modernisé, nous visons 300 nouvelles organisations pour 2025.</p>
                ',
                'image' => null,
                'categorie' => 'Statistiques',
                'auteur' => 'Administration PNGDI',
                'vues' => 523,
                'tags' => ['bilan', 'statistiques', '2024']
            ],
            [
                'id' => 5,
                'slug' => 'nouvelle-plateforme-pngdi-lancee',
                'titre' => 'Lancement officiel de la nouvelle plateforme PNGDI',
                'date' => '2024-12-20',
                'extrait' => 'La nouvelle version du portail PNGDI est maintenant disponible avec une interface modernisée et de nouvelles fonctionnalités pour faciliter vos démarches.',
                'contenu' => '
                    <p>Nous sommes heureux d\'annoncer le lancement officiel de la nouvelle version du Portail National de Gestion des Libertés Individuelles (PNGDI).</p>
                    
                    <h3>Nouvelles fonctionnalités</h3>
                    <ul>
                        <li>Interface utilisateur entièrement repensée</li>
                        <li>Tableau de bord personnalisé pour chaque organisation</li>
                        <li>Suivi en temps réel des dossiers</li>
                        <li>Messagerie intégrée avec l\'administration</li>
                        <li>Espace documentaire enrichi</li>
                        <li>Version mobile optimisée</li>
                    </ul>
                    
                    <h3>Améliorations techniques</h3>
                    <ul>
                        <li>Temps de chargement réduit de 60%</li>
                        <li>Sécurité renforcée avec authentification à deux facteurs</li>
                        <li>Sauvegarde automatique des formulaires</li>
                        <li>Compatibilité avec tous les navigateurs modernes</li>
                    </ul>
                    
                    <h3>Migration des comptes</h3>
                    <p>Les utilisateurs existants peuvent se connecter avec leurs identifiants habituels. Un guide de migration est disponible dans la section aide.</p>
                ',
                'image' => null,
                'categorie' => 'Annonce',
                'auteur' => 'Équipe Technique',
                'vues' => 789,
                'tags' => ['plateforme', 'lancement', 'nouveautés']
            ],
            [
                'id' => 6,
                'slug' => 'guide-declaration-annuelle-2025',
                'titre' => 'Guide pratique : Déclaration annuelle 2025',
                'date' => '2024-12-15',
                'extrait' => 'Tout ce que vous devez savoir pour préparer et soumettre votre déclaration annuelle 2025. Dates limites, documents requis et procédure en ligne.',
                'contenu' => '
                    <p>La période de déclaration annuelle 2025 approche. Voici tout ce que vous devez savoir pour être en conformité.</p>
                    
                    <h3>Dates importantes</h3>
                    <ul>
                        <li>Ouverture des déclarations : 2 janvier 2025</li>
                        <li>Date limite associations : 31 mars 2025</li>
                        <li>Date limite ONG : 30 avril 2025</li>
                        <li>Date limite partis politiques : 28 février 2025</li>
                    </ul>
                    
                    <h3>Documents à préparer</h3>
                    <ol>
                        <li>Rapport d\'activités 2024</li>
                        <li>Bilan financier certifié</li>
                        <li>Liste actualisée des membres du bureau</li>
                        <li>PV de la dernière assemblée générale</li>
                        <li>Justificatifs des activités réalisées</li>
                    </ol>
                    
                    <h3>Procédure en ligne</h3>
                    <p>La déclaration se fait exclusivement en ligne via votre espace personnel sur le portail PNGDI. Un tutoriel vidéo est disponible pour vous guider pas à pas.</p>
                    
                    <h3>Sanctions en cas de retard</h3>
                    <p>Attention : tout retard dans la déclaration peut entraîner des sanctions allant de l\'avertissement à la suspension temporaire d\'agrément.</p>
                ',
                'image' => null,
                'categorie' => 'Guide',
                'auteur' => 'Service Conformité',
                'vues' => 345,
                'tags' => ['déclaration', 'guide', '2025']
            ]
        ];
    }

    /**
     * Liste des actualités
     */
    public function index(Request $request)
    {
        // Log pour debug
        \Log::info('ActualiteController@index - Accès à la liste des actualités');
        
        $allActualites = self::getAllActualites();

        // Filtrage par catégorie si demandé
        $categorie = $request->get('categorie', null);
        $actualites = $allActualites;
        
        if ($categorie && $categorie !== 'all') {
            $actualites = array_filter($allActualites, function($actu) use ($categorie) {
                return $actu['categorie'] === $categorie;
            });
            $actualites = array_values($actualites); // Réindexer
        }

        // Filtrage par recherche
        $search = $request->get('search', '');
        if ($search) {
            $searchLower = strtolower($search);
            $actualites = array_filter($actualites, function($actu) use ($searchLower) {
                return str_contains(strtolower($actu['titre']), $searchLower) || 
                       str_contains(strtolower($actu['extrait']), $searchLower) ||
                       str_contains(strtolower($actu['categorie']), $searchLower);
            });
            $actualites = array_values($actualites); // Réindexer
        }

        // Récupération des catégories uniques avec leur comptage
        $categories = [];
        $categoryCounts = [];
        foreach ($allActualites as $actu) {
            $cat = $actu['categorie'];
            if (!in_array($cat, $categories)) {
                $categories[] = $cat;
            }
            if (!isset($categoryCounts[$cat])) {
                $categoryCounts[$cat] = 0;
            }
            $categoryCounts[$cat]++;
        }
        sort($categories);

        // Pagination simulée
        $perPage = 6;
        $page = max(1, intval($request->get('page', 1)));
        $total = count($actualites);
        $totalPages = max(1, ceil($total / $perPage));
        
        // S'assurer que la page demandée est valide
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        
        $offset = ($page - 1) * $perPage;
        $actualitesPaginated = array_slice($actualites, $offset, $perPage);

        return view('public.actualites.index', compact(
            'actualitesPaginated', 
            'categories', 
            'categoryCounts',
            'categorie', 
            'total', 
            'page', 
            'perPage',
            'totalPages',
            'search'
        ));
    }

    /**
     * Afficher une actualité spécifique
     */
    public function show($slug)
    {
        // Log pour debug
        \Log::info('ActualiteController@show - Slug reçu: ' . $slug);
        
        $allActualites = self::getAllActualites();
        
        // Rechercher l'actualité par slug
        $actualite = null;
        foreach ($allActualites as $actu) {
            if ($actu['slug'] === $slug) {
                $actualite = $actu;
                break;
            }
        }
        
        // Si l'actualité n'est pas trouvée, retourner une erreur 404
        if (!$actualite) {
            \Log::error('Actualité non trouvée pour le slug: ' . $slug);
            abort(404, 'Actualité non trouvée');
        }
        
        // Incrémenter les vues
        $actualite['vues']++;
        
        // Actualités similaires (même catégorie, sauf l'actuelle)
        $similaires = array_filter($allActualites, function($item) use ($actualite) {
            return $item['categorie'] === $actualite['categorie'] && $item['slug'] !== $actualite['slug'];
        });
        
        // Limiter à 3 actualités similaires
        $similaires = array_slice($similaires, 0, 3);

        return view('public.actualites.show', compact('actualite', 'similaires'));
    }
}