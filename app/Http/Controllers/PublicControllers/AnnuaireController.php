<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnnuaireController extends Controller
{
    /**
     * Base de données simulée des organisations
     */
    private static function getAllOrganisations()
    {
        return [
            // Associations
            [
                'id' => 1,
                'nom' => 'Association Jeunesse Active du Gabon',
                'slug' => 'association-jeunesse-active-gabon',
                'type' => 'association',
                'categorie' => 'Jeunesse et éducation',
                'description' => 'Association œuvrant pour l\'éducation et l\'insertion professionnelle des jeunes gabonais.',
                'date_creation' => '2020-03-15',
                'ville' => 'Libreville',
                'province' => 'Estuaire',
                'adresse' => 'Quartier Louis, BP 1234',
                'telephone' => '+241 01 23 45 67',
                'email' => 'contact@ajag.ga',
                'site_web' => 'www.ajag.ga',
                'responsable' => 'M. Jean NDONG',
                'membres' => 250,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Formation', 'Sensibilisation', 'Accompagnement']
            ],
            [
                'id' => 2,
                'nom' => 'Association des Femmes Entrepreneures',
                'slug' => 'association-femmes-entrepreneures',
                'type' => 'association',
                'categorie' => 'Économie sociale',
                'description' => 'Promotion de l\'entrepreneuriat féminin et soutien aux femmes chefs d\'entreprise.',
                'date_creation' => '2018-06-20',
                'ville' => 'Port-Gentil',
                'province' => 'Ogooué-Maritime',
                'adresse' => 'Centre-ville, Avenue du Port',
                'telephone' => '+241 01 98 76 54',
                'email' => 'afe@gmail.com',
                'site_web' => null,
                'responsable' => 'Mme Marie OBAME',
                'membres' => 180,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Microfinance', 'Formation', 'Réseautage']
            ],
            [
                'id' => 3,
                'nom' => 'Association Environnement Plus',
                'slug' => 'association-environnement-plus',
                'type' => 'association',
                'categorie' => 'Environnement',
                'description' => 'Protection de l\'environnement et sensibilisation aux enjeux climatiques.',
                'date_creation' => '2019-09-10',
                'ville' => 'Franceville',
                'province' => 'Haut-Ogooué',
                'adresse' => 'Quartier Potos',
                'telephone' => '+241 01 55 66 77',
                'email' => 'envplus@yahoo.fr',
                'site_web' => null,
                'responsable' => 'Dr. Paul MOUSSAVOU',
                'membres' => 120,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Reboisement', 'Éducation environnementale', 'Plaidoyer']
            ],
            
            // ONG
            [
                'id' => 4,
                'nom' => 'ONG Santé Pour Tous',
                'slug' => 'ong-sante-pour-tous',
                'type' => 'ong',
                'categorie' => 'Santé',
                'description' => 'Amélioration de l\'accès aux soins de santé dans les zones rurales du Gabon.',
                'date_creation' => '2017-01-12',
                'ville' => 'Libreville',
                'province' => 'Estuaire',
                'adresse' => 'Owendo, Route de Nkok',
                'telephone' => '+241 01 33 44 55',
                'email' => 'spt@ong-spt.org',
                'site_web' => 'www.ong-spt.org',
                'responsable' => 'Dr. Jeanne MBA',
                'membres' => 45,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Cliniques mobiles', 'Formation sanitaire', 'Prévention']
            ],
            [
                'id' => 5,
                'nom' => 'ONG Éducation Sans Frontières',
                'slug' => 'ong-education-sans-frontieres',
                'type' => 'ong',
                'categorie' => 'Éducation',
                'description' => 'Soutien à la scolarisation des enfants défavorisés et construction d\'écoles.',
                'date_creation' => '2016-09-01',
                'ville' => 'Lambaréné',
                'province' => 'Moyen-Ogooué',
                'adresse' => 'BP 789, Centre-ville',
                'telephone' => '+241 01 77 88 99',
                'email' => 'esf.gabon@gmail.com',
                'site_web' => null,
                'responsable' => 'M. Pierre NZIGOU',
                'membres' => 68,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Construction écoles', 'Bourses scolaires', 'Fournitures']
            ],
            [
                'id' => 6,
                'nom' => 'ONG Droits et Justice',
                'slug' => 'ong-droits-justice',
                'type' => 'ong',
                'categorie' => 'Droits humains',
                'description' => 'Défense des droits humains et accompagnement juridique des populations vulnérables.',
                'date_creation' => '2015-03-20',
                'ville' => 'Libreville',
                'province' => 'Estuaire',
                'adresse' => 'Glass, Immeuble Les Palmiers',
                'telephone' => '+241 01 22 33 44',
                'email' => 'droitsjustice@yahoo.fr',
                'site_web' => null,
                'responsable' => 'Me. Sylvie BONGO',
                'membres' => 32,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Assistance juridique', 'Sensibilisation', 'Plaidoyer']
            ],
            
            // Partis politiques
            [
                'id' => 7,
                'nom' => 'Parti Démocratique Gabonais',
                'slug' => 'parti-democratique-gabonais',
                'type' => 'parti',
                'categorie' => 'Parti politique',
                'description' => 'Parti politique œuvrant pour le développement démocratique du Gabon.',
                'date_creation' => '1968-03-12',
                'ville' => 'Libreville',
                'province' => 'Estuaire',
                'adresse' => 'Boulevard Triomphal',
                'telephone' => '+241 01 74 00 00',
                'email' => 'pdg@pdg.ga',
                'site_web' => 'www.pdg.ga',
                'responsable' => 'Secrétaire Général',
                'membres' => 50000,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Meetings', 'Formation politique', 'Actions sociales']
            ],
            [
                'id' => 8,
                'nom' => 'Union Nationale',
                'slug' => 'union-nationale',
                'type' => 'parti',
                'categorie' => 'Parti politique',
                'description' => 'Parti d\'opposition prônant l\'alternance démocratique.',
                'date_creation' => '2010-05-15',
                'ville' => 'Libreville',
                'province' => 'Estuaire',
                'adresse' => 'Quartier Batterie IV',
                'telephone' => '+241 01 65 43 21',
                'email' => 'contact@un.ga',
                'site_web' => null,
                'responsable' => 'Président National',
                'membres' => 15000,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Conférences', 'Manifestations', 'Propositions']
            ],
            
            // Confessions religieuses
            [
                'id' => 9,
                'nom' => 'Église Évangélique du Gabon',
                'slug' => 'eglise-evangelique-gabon',
                'type' => 'confession',
                'categorie' => 'Religion chrétienne',
                'description' => 'Communauté chrétienne évangélique présente sur tout le territoire national.',
                'date_creation' => '1952-01-01',
                'ville' => 'Libreville',
                'province' => 'Estuaire',
                'adresse' => 'Montagne Sainte',
                'telephone' => '+241 01 76 54 32',
                'email' => 'eeg@eeg-gabon.org',
                'site_web' => 'www.eeg-gabon.org',
                'responsable' => 'Pasteur Principal',
                'membres' => 25000,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Cultes', 'Évangélisation', 'Actions sociales']
            ],
            [
                'id' => 10,
                'nom' => 'Communauté Islamique du Gabon',
                'slug' => 'communaute-islamique-gabon',
                'type' => 'confession',
                'categorie' => 'Religion musulmane',
                'description' => 'Organisation représentative de la communauté musulmane au Gabon.',
                'date_creation' => '1975-06-10',
                'ville' => 'Libreville',
                'province' => 'Estuaire',
                'adresse' => 'Akébé-Plaine',
                'telephone' => '+241 01 78 90 12',
                'email' => 'cig@islam-gabon.org',
                'site_web' => null,
                'responsable' => 'Imam Principal',
                'membres' => 15000,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Prières', 'Éducation religieuse', 'Solidarité']
            ],
            [
                'id' => 11,
                'nom' => 'Église Catholique Saint-Michel',
                'slug' => 'eglise-catholique-saint-michel',
                'type' => 'confession',
                'categorie' => 'Religion chrétienne',
                'description' => 'Paroisse catholique au service de la communauté chrétienne de Libreville.',
                'date_creation' => '1960-12-25',
                'ville' => 'Libreville',
                'province' => 'Estuaire',
                'adresse' => 'Quartier Nombakélé',
                'telephone' => '+241 01 72 13 14',
                'email' => 'stmichel@catholique.ga',
                'site_web' => null,
                'responsable' => 'Père Supérieur',
                'membres' => 5000,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Messes', 'Catéchèse', 'Œuvres caritatives']
            ],
            [
                'id' => 12,
                'nom' => 'Association Sportive de Moanda',
                'slug' => 'association-sportive-moanda',
                'type' => 'association',
                'categorie' => 'Sport et loisirs',
                'description' => 'Promotion du sport amateur et organisation d\'événements sportifs locaux.',
                'date_creation' => '2021-02-10',
                'ville' => 'Moanda',
                'province' => 'Haut-Ogooué',
                'adresse' => 'Stade Municipal, BP 456',
                'telephone' => '+241 01 87 65 43',
                'email' => 'asm@sport-moanda.ga',
                'site_web' => null,
                'responsable' => 'M. Patrick MBOUMBA',
                'membres' => 320,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Football', 'Basketball', 'Athlétisme']
            ],
            [
                'id' => 13,
                'nom' => 'ONG Protection de l\'Enfance',
                'slug' => 'ong-protection-enfance',
                'type' => 'ong',
                'categorie' => 'Protection sociale',
                'description' => 'Protection et accompagnement des enfants en situation difficile.',
                'date_creation' => '2019-11-20',
                'ville' => 'Port-Gentil',
                'province' => 'Ogooué-Maritime',
                'adresse' => 'Quartier Grand Village',
                'telephone' => '+241 01 34 56 78',
                'email' => 'ope@protection-enfance.ga',
                'site_web' => null,
                'responsable' => 'Mme Claudine NGOMA',
                'membres' => 56,
                'statut' => 'active',
                'logo' => null,
                'activites' => ['Hébergement', 'Éducation', 'Réinsertion']
            ]
        ];
    }

    /**
     * Page principale de l'annuaire
     */
    public function index(Request $request)
    {
        $organisations = self::getAllOrganisations();
        
        // Filtrage par recherche
        $search = $request->get('search', '');
        if ($search) {
            $searchLower = strtolower($search);
            $organisations = array_filter($organisations, function($org) use ($searchLower) {
                return str_contains(strtolower($org['nom']), $searchLower) ||
                       str_contains(strtolower($org['description']), $searchLower) ||
                       str_contains(strtolower($org['ville']), $searchLower) ||
                       str_contains(strtolower($org['categorie']), $searchLower);
            });
        }
        
        // Filtrage par type
        $type = $request->get('type');
        if ($type && $type !== 'all') {
            $organisations = array_filter($organisations, function($org) use ($type) {
                return $org['type'] === $type;
            });
        }
        
        // Filtrage par province
        $province = $request->get('province');
        if ($province && $province !== 'all') {
            $organisations = array_filter($organisations, function($org) use ($province) {
                return $org['province'] === $province;
            });
        }
        
        // Filtrage par catégorie
        $categorie = $request->get('categorie');
        if ($categorie && $categorie !== 'all') {
            $organisations = array_filter($organisations, function($org) use ($categorie) {
                return $org['categorie'] === $categorie;
            });
        }
        
        // Réindexer le tableau
        $organisations = array_values($organisations);
        
        // Statistiques
        $allOrgs = self::getAllOrganisations();
        $stats = [
            'total' => count($allOrgs),
            'associations' => count(array_filter($allOrgs, function($o) { return $o['type'] === 'association'; })),
            'ong' => count(array_filter($allOrgs, function($o) { return $o['type'] === 'ong'; })),
            'partis' => count(array_filter($allOrgs, function($o) { return $o['type'] === 'parti'; })),
            'confessions' => count(array_filter($allOrgs, function($o) { return $o['type'] === 'confession'; }))
        ];
        
        // Provinces uniques
        $provinces = array_unique(array_column($allOrgs, 'province'));
        sort($provinces);
        
        // Catégories uniques
        $categories = array_unique(array_column($allOrgs, 'categorie'));
        sort($categories);
        
        return view('public.annuaire.index', compact(
            'organisations',
            'stats',
            'provinces',
            'categories',
            'search',
            'type',
            'province',
            'categorie'
        ));
    }
    
    /**
     * Afficher les associations
     */
    public function associations()
    {
        return redirect()->route('annuaire.index', ['type' => 'association']);
    }
    
    /**
     * Afficher les ONG
     */
    public function ong()
    {
        return redirect()->route('annuaire.index', ['type' => 'ong']);
    }
    
    /**
     * Afficher les partis politiques
     */
    public function partisPolitiques()
    {
        return redirect()->route('annuaire.index', ['type' => 'parti']);
    }
    
    /**
     * Afficher les confessions religieuses
     */
    public function confessionsReligieuses()
    {
        return redirect()->route('annuaire.index', ['type' => 'confession']);
    }
    
    /**
     * Afficher le détail d'une organisation
     */
    public function show($type, $slug)
    {
        $organisations = self::getAllOrganisations();
        $organisation = null;
        
        // Rechercher l'organisation par type et slug
        foreach ($organisations as $org) {
            if ($org['type'] === $type && $org['slug'] === $slug) {
                $organisation = $org;
                break;
            }
        }
        
        if (!$organisation) {
            abort(404, 'Organisation non trouvée');
        }
        
        // Organisations similaires (même type et même province)
        $similaires = array_filter($organisations, function($org) use ($organisation) {
            return $org['type'] === $organisation['type'] && 
                   $org['province'] === $organisation['province'] &&
                   $org['id'] !== $organisation['id'];
        });
        
        // Limiter à 3 organisations similaires
        $similaires = array_slice($similaires, 0, 3);
        
        return view('public.annuaire.show', compact('organisation', 'similaires'));
    }
}