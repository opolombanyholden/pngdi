<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Base de données simulée des documents
     */
    public static function getAllDocuments()
    {
        return [
            // Guides et manuels
            [
                'id' => 1,
                'titre' => 'Guide de création d\'association',
                'description' => 'Guide complet pour créer votre association étape par étape. Inclut tous les formulaires nécessaires et des conseils pratiques.',
                'categorie' => 'Guides',
                'type' => 'association',
                'fichier' => 'guide-creation-association.pdf',
                'taille' => '2.4 MB',
                'format' => 'PDF',
                'date_mise_jour' => '2025-01-10',
                'telechargements' => 1234,
                'tags' => ['association', 'création', 'guide']
            ],
            [
                'id' => 2,
                'titre' => 'Manuel de gestion des ONG',
                'description' => 'Manuel détaillé sur la gestion administrative et financière des ONG. Conforme aux normes gabonaises.',
                'categorie' => 'Guides',
                'type' => 'ong',
                'fichier' => 'manuel-gestion-ong.pdf',
                'taille' => '3.1 MB',
                'format' => 'PDF',
                'date_mise_jour' => '2024-12-20',
                'telechargements' => 876,
                'tags' => ['ong', 'gestion', 'manuel']
            ],
            [
                'id' => 3,
                'titre' => 'Guide de création de parti politique',
                'description' => 'Procédures et exigences pour la création d\'un parti politique au Gabon.',
                'categorie' => 'Guides',
                'type' => 'parti',
                'fichier' => 'guide-parti-politique.pdf',
                'taille' => '1.8 MB',
                'format' => 'PDF',
                'date_mise_jour' => '2025-01-05',
                'telechargements' => 234,
                'tags' => ['parti', 'politique', 'création']
            ],
            
            // Formulaires
            [
                'id' => 4,
                'titre' => 'Formulaire de déclaration d\'association',
                'description' => 'Formulaire officiel pour déclarer une nouvelle association. Version mise à jour 2025.',
                'categorie' => 'Formulaires',
                'type' => 'association',
                'fichier' => 'form-declaration-association.pdf',
                'taille' => '450 KB',
                'format' => 'PDF',
                'date_mise_jour' => '2025-01-15',
                'telechargements' => 2156,
                'tags' => ['formulaire', 'association', 'déclaration']
            ],
            [
                'id' => 5,
                'titre' => 'Formulaire de demande d\'agrément ONG',
                'description' => 'Formulaire de demande d\'agrément pour les organisations non gouvernementales.',
                'categorie' => 'Formulaires',
                'type' => 'ong',
                'fichier' => 'form-agrement-ong.pdf',
                'taille' => '520 KB',
                'format' => 'PDF',
                'date_mise_jour' => '2025-01-12',
                'telechargements' => 987,
                'tags' => ['formulaire', 'ong', 'agrément']
            ],
            [
                'id' => 6,
                'titre' => 'Formulaire de déclaration annuelle',
                'description' => 'Formulaire pour la déclaration annuelle d\'activités, obligatoire pour toutes les organisations.',
                'categorie' => 'Formulaires',
                'type' => 'tous',
                'fichier' => 'form-declaration-annuelle.pdf',
                'taille' => '380 KB',
                'format' => 'PDF',
                'date_mise_jour' => '2024-12-01',
                'telechargements' => 3421,
                'tags' => ['formulaire', 'déclaration', 'annuelle']
            ],
            
            // Modèles
            [
                'id' => 7,
                'titre' => 'Modèle de statuts d\'association',
                'description' => 'Modèle type de statuts pour association, personnalisable selon vos besoins.',
                'categorie' => 'Modèles',
                'type' => 'association',
                'fichier' => 'modele-statuts-association.docx',
                'taille' => '125 KB',
                'format' => 'DOCX',
                'date_mise_jour' => '2025-01-08',
                'telechargements' => 1876,
                'tags' => ['modèle', 'statuts', 'association']
            ],
            [
                'id' => 8,
                'titre' => 'Modèle de règlement intérieur',
                'description' => 'Modèle de règlement intérieur adaptable pour associations et ONG.',
                'categorie' => 'Modèles',
                'type' => 'tous',
                'fichier' => 'modele-reglement-interieur.docx',
                'taille' => '98 KB',
                'format' => 'DOCX',
                'date_mise_jour' => '2024-11-20',
                'telechargements' => 1234,
                'tags' => ['modèle', 'règlement', 'intérieur']
            ],
            [
                'id' => 9,
                'titre' => 'Modèle de PV d\'assemblée générale',
                'description' => 'Modèle de procès-verbal pour vos assemblées générales constitutives ou ordinaires.',
                'categorie' => 'Modèles',
                'type' => 'tous',
                'fichier' => 'modele-pv-ag.docx',
                'taille' => '76 KB',
                'format' => 'DOCX',
                'date_mise_jour' => '2025-01-03',
                'telechargements' => 2345,
                'tags' => ['modèle', 'pv', 'assemblée']
            ],
            
            // Réglementation
            [
                'id' => 10,
                'titre' => 'Loi sur les associations',
                'description' => 'Texte intégral de la loi régissant les associations au Gabon.',
                'categorie' => 'Réglementation',
                'type' => 'association',
                'fichier' => 'loi-associations.pdf',
                'taille' => '890 KB',
                'format' => 'PDF',
                'date_mise_jour' => '2024-06-15',
                'telechargements' => 567,
                'tags' => ['loi', 'réglementation', 'association']
            ],
            [
                'id' => 11,
                'titre' => 'Décret sur les ONG',
                'description' => 'Décret d\'application concernant les organisations non gouvernementales.',
                'categorie' => 'Réglementation',
                'type' => 'ong',
                'fichier' => 'decret-ong.pdf',
                'taille' => '456 KB',
                'format' => 'PDF',
                'date_mise_jour' => '2024-09-10',
                'telechargements' => 432,
                'tags' => ['décret', 'réglementation', 'ong']
            ],
            [
                'id' => 12,
                'titre' => 'Charte des partis politiques',
                'description' => 'Charte nationale régissant la création et le fonctionnement des partis politiques.',
                'categorie' => 'Réglementation',
                'type' => 'parti',
                'fichier' => 'charte-partis-politiques.pdf',
                'taille' => '1.2 MB',
                'format' => 'PDF',
                'date_mise_jour' => '2024-03-20',
                'telechargements' => 189,
                'tags' => ['charte', 'parti', 'politique']
            ]
        ];
    }

    /**
     * Afficher la liste des documents
     */
    public function index(Request $request)
    {
        $allDocuments = self::getAllDocuments();
        
        // Filtrage par catégorie
        $categorie = $request->get('categorie');
        $documents = $allDocuments;
        
        if ($categorie && $categorie !== 'all') {
            $documents = array_filter($allDocuments, function($doc) use ($categorie) {
                return $doc['categorie'] === $categorie;
            });
            $documents = array_values($documents);
        }
        
        // Filtrage par type d'organisation
        $type = $request->get('type');
        if ($type && $type !== 'all') {
            $documents = array_filter($documents, function($doc) use ($type) {
                return $doc['type'] === $type || $doc['type'] === 'tous';
            });
            $documents = array_values($documents);
        }
        
        // Recherche
        $search = $request->get('search', '');
        if ($search) {
            $searchLower = strtolower($search);
            $documents = array_filter($documents, function($doc) use ($searchLower) {
                return str_contains(strtolower($doc['titre']), $searchLower) || 
                       str_contains(strtolower($doc['description']), $searchLower) ||
                       in_array($searchLower, array_map('strtolower', $doc['tags']));
            });
            $documents = array_values($documents);
        }
        
        // Tri
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'recent':
                usort($documents, function($a, $b) {
                    return strtotime($b['date_mise_jour']) - strtotime($a['date_mise_jour']);
                });
                break;
            case 'populaire':
                usort($documents, function($a, $b) {
                    return $b['telechargements'] - $a['telechargements'];
                });
                break;
            case 'nom':
                usort($documents, function($a, $b) {
                    return strcmp($a['titre'], $b['titre']);
                });
                break;
        }
        
        // Catégories et types uniques
        $categories = array_unique(array_column($allDocuments, 'categorie'));
        sort($categories);
        
        $types = [
            'tous' => 'Tous types',
            'association' => 'Associations',
            'ong' => 'ONG',
            'parti' => 'Partis politiques',
            'confession' => 'Confessions religieuses'
        ];
        
        // Statistiques pour la page
        $stats = [
            'total' => count($allDocuments),
            'telechargements' => array_sum(array_column($allDocuments, 'telechargements'))
        ];
        
        return view('public.documents.index', compact(
            'documents',
            'categories',
            'types',
            'categorie',
            'type',
            'search',
            'sort',
            'stats'
        ));
    }
    
    /**
     * Télécharger un document (simulation)
     */
    public function download($id)
    {
        $documents = self::getAllDocuments();
        $document = null;
        
        foreach ($documents as $doc) {
            if ($doc['id'] == $id) {
                $document = $doc;
                break;
            }
        }
        
        if (!$document) {
            abort(404, 'Document non trouvé');
        }
        
        // Log du téléchargement
        \Log::info('Téléchargement du document: ' . $document['titre']);
        
        // En production, ici on retournerait le fichier réel
        // Pour la démo, on redirige avec un message
        return redirect()->route('documents.index')
            ->with('success', 'Le téléchargement du document "' . $document['titre'] . '" va commencer.');
    }
}