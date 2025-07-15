<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Gestion des actualités
     * Route: /admin/content/actualites
     */
    public function actualites(Request $request)
    {
        try {
            // Simulation d'actualités (à terme, utiliser un modèle Actualite)
            $actualites = collect([
                [
                    'id' => 1,
                    'titre' => 'Nouvelle procédure de déclaration des partis politiques',
                    'contenu' => 'La loi 016/2025 introduit de nouvelles modalités...',
                    'auteur' => 'Admin SGLP',
                    'statut' => 'publiee',
                    'date_publication' => now()->subDays(2),
                    'vues' => 1250,
                    'featured' => true
                ],
                [
                    'id' => 2,
                    'titre' => 'Simplification des démarches pour les associations',
                    'contenu' => 'De nouvelles mesures facilitent la création...',
                    'auteur' => 'Admin SGLP',
                    'statut' => 'brouillon',
                    'date_creation' => now()->subDays(5),
                    'vues' => 0,
                    'featured' => false
                ],
                [
                    'id' => 3,
                    'titre' => 'Formation des agents du SGLP',
                    'contenu' => 'Programme de formation continue...',
                    'auteur' => 'Admin SGLP',
                    'statut' => 'publiee',
                    'date_publication' => now()->subWeek(),
                    'vues' => 890,
                    'featured' => false
                ]
            ]);

            // Filtres
            if ($request->filled('statut')) {
                $actualites = $actualites->where('statut', $request->statut);
            }

            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $actualites = $actualites->filter(function($actualite) use ($search) {
                    return str_contains(strtolower($actualite['titre']), $search) ||
                           str_contains(strtolower($actualite['contenu']), $search);
                });
            }

            // Pagination manuelle
            $perPage = 10;
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $actualitesPaginated = $actualites->slice($offset, $perPage);

            // Statistiques
            $stats = [
                'total' => $actualites->count(),
                'publiees' => $actualites->where('statut', 'publiee')->count(),
                'brouillons' => $actualites->where('statut', 'brouillon')->count(),
                'vues_total' => $actualites->sum('vues'),
                'featured' => $actualites->where('featured', true)->count()
            ];

            return view('admin.content.actualites', compact('actualitesPaginated', 'stats', 'actualites'));

        } catch (\Exception $e) {
            \Log::error('Erreur ContentController@actualites: ' . $e->getMessage());
            
            return view('admin.content.actualites', [
                'actualitesPaginated' => collect(),
                'stats' => ['total' => 0, 'publiees' => 0, 'brouillons' => 0, 'vues_total' => 0, 'featured' => 0],
                'actualites' => collect()
            ]);
        }
    }

    /**
     * Gestion des documents
     * Route: /admin/content/documents
     */
    public function documents(Request $request)
    {
        try {
            // Simulation de documents publics
            $documents = collect([
                [
                    'id' => 1,
                    'nom' => 'Guide de création d\'association',
                    'description' => 'Guide complet pour créer une association au Gabon',
                    'fichier' => 'guide-association-2025.pdf',
                    'type' => 'guide',
                    'taille' => '2.5 MB',
                    'telechargements' => 1845,
                    'date_upload' => now()->subMonths(2),
                    'statut' => 'public',
                    'categorie' => 'Association'
                ],
                [
                    'id' => 2,
                    'nom' => 'Formulaire de déclaration ONG',
                    'description' => 'Formulaire officiel pour les ONG',
                    'fichier' => 'formulaire-ong.pdf',
                    'type' => 'formulaire',
                    'taille' => '580 KB',
                    'telechargements' => 672,
                    'date_upload' => now()->subMonth(),
                    'statut' => 'public',
                    'categorie' => 'ONG'
                ],
                [
                    'id' => 3,
                    'nom' => 'Loi 016/2025 - Partis politiques',
                    'description' => 'Texte intégral de la loi sur les partis politiques',
                    'fichier' => 'loi-016-2025.pdf',
                    'type' => 'legislation',
                    'taille' => '1.2 MB',
                    'telechargements' => 3521,
                    'date_upload' => now()->subWeeks(3),
                    'statut' => 'public',
                    'categorie' => 'Parti Politique'
                ],
                [
                    'id' => 4,
                    'nom' => 'Manuel des procédures internes',
                    'description' => 'Documentation interne pour les agents',
                    'fichier' => 'manuel-procedures.pdf',
                    'type' => 'manuel',
                    'taille' => '4.1 MB',
                    'telechargements' => 89,
                    'date_upload' => now()->subDays(10),
                    'statut' => 'interne',
                    'categorie' => 'Administration'
                ]
            ]);

            // Filtres
            if ($request->filled('type')) {
                $documents = $documents->where('type', $request->type);
            }

            if ($request->filled('categorie')) {
                $documents = $documents->where('categorie', $request->categorie);
            }

            if ($request->filled('statut')) {
                $documents = $documents->where('statut', $request->statut);
            }

            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $documents = $documents->filter(function($document) use ($search) {
                    return str_contains(strtolower($document['nom']), $search) ||
                           str_contains(strtolower($document['description']), $search);
                });
            }

            // Statistiques
            $stats = [
                'total' => $documents->count(),
                'publics' => $documents->where('statut', 'public')->count(),
                'internes' => $documents->where('statut', 'interne')->count(),
                'telechargements_total' => $documents->sum('telechargements'),
                'taille_totale' => $this->calculateTotalSize($documents)
            ];

            // Types et catégories pour les filtres
            $types = $documents->pluck('type')->unique()->values();
            $categories = $documents->pluck('categorie')->unique()->values();

            return view('admin.content.documents', compact('documents', 'stats', 'types', 'categories'));

        } catch (\Exception $e) {
            \Log::error('Erreur ContentController@documents: ' . $e->getMessage());
            
            return view('admin.content.documents', [
                'documents' => collect(),
                'stats' => ['total' => 0, 'publics' => 0, 'internes' => 0, 'telechargements_total' => 0, 'taille_totale' => '0 MB'],
                'types' => collect(),
                'categories' => collect()
            ]);
        }
    }

    /**
     * Gestion de la FAQ
     * Route: /admin/content/faq
     */
    public function faq(Request $request)
    {
        try {
            // Simulation de FAQ
            $faqs = collect([
                [
                    'id' => 1,
                    'question' => 'Quels documents sont nécessaires pour créer une association ?',
                    'reponse' => 'Pour créer une association, vous devez fournir : les statuts signés, le procès-verbal de l\'AG constitutive, la liste des membres fondateurs, et les pièces d\'identité des dirigeants.',
                    'categorie' => 'Association',
                    'ordre' => 1,
                    'vues' => 2156,
                    'utile_votes' => 89,
                    'statut' => 'publiee',
                    'derniere_modification' => now()->subDays(15)
                ],
                [
                    'id' => 2,
                    'question' => 'Combien de temps prend la validation d\'un dossier ?',
                    'reponse' => 'Le délai de traitement varie selon le type d\'organisation : 15 jours pour les associations, 30 jours pour les ONG, et 21 jours pour les partis politiques.',
                    'categorie' => 'Procédures',
                    'ordre' => 2,
                    'vues' => 1847,
                    'utile_votes' => 76,
                    'statut' => 'publiee',
                    'derniere_modification' => now()->subDays(8)
                ],
                [
                    'id' => 3,
                    'question' => 'Comment modifier les statuts d\'une organisation ?',
                    'reponse' => 'Pour modifier les statuts, vous devez soumettre une demande de modification avec les nouveaux statuts, le PV de l\'AG extraordinaire, et justifier les changements.',
                    'categorie' => 'Modifications',
                    'ordre' => 3,
                    'vues' => 1234,
                    'utile_votes' => 54,
                    'statut' => 'publiee',
                    'derniere_modification' => now()->subDays(22)
                ],
                [
                    'id' => 4,
                    'question' => 'Que faire en cas de rejet de dossier ?',
                    'reponse' => 'En cas de rejet, vous pouvez corriger les éléments mentionnés dans la notification et resoummettre votre dossier, ou faire un recours administratif.',
                    'categorie' => 'Procédures',
                    'ordre' => 4,
                    'vues' => 987,
                    'utile_votes' => 32,
                    'statut' => 'brouillon',
                    'derniere_modification' => now()->subDays(5)
                ],
                [
                    'id' => 5,
                    'question' => 'Les partis politiques ont-ils des exigences spéciales ?',
                    'reponse' => 'Oui, selon la loi 016/2025, les partis politiques doivent respecter des conditions particulières concernant la représentativité nationale et le programme politique.',
                    'categorie' => 'Parti Politique',
                    'ordre' => 5,
                    'vues' => 1567,
                    'utile_votes' => 67,
                    'statut' => 'publiee',
                    'derniere_modification' => now()->subDays(12)
                ]
            ]);

            // Filtres
            if ($request->filled('categorie')) {
                $faqs = $faqs->where('categorie', $request->categorie);
            }

            if ($request->filled('statut')) {
                $faqs = $faqs->where('statut', $request->statut);
            }

            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $faqs = $faqs->filter(function($faq) use ($search) {
                    return str_contains(strtolower($faq['question']), $search) ||
                           str_contains(strtolower($faq['reponse']), $search);
                });
            }

            // Tri
            $sortBy = $request->get('sort', 'ordre');
            switch ($sortBy) {
                case 'popularite':
                    $faqs = $faqs->sortByDesc('vues');
                    break;
                case 'utilite':
                    $faqs = $faqs->sortByDesc('utile_votes');
                    break;
                case 'recent':
                    $faqs = $faqs->sortByDesc('derniere_modification');
                    break;
                default:
                    $faqs = $faqs->sortBy('ordre');
                    break;
            }

            // Statistiques
            $stats = [
                'total' => $faqs->count(),
                'publiees' => $faqs->where('statut', 'publiee')->count(),
                'brouillons' => $faqs->where('statut', 'brouillon')->count(),
                'vues_total' => $faqs->sum('vues'),
                'votes_utiles_total' => $faqs->sum('utile_votes')
            ];

            // Catégories pour les filtres
            $categories = $faqs->pluck('categorie')->unique()->values();

            return view('admin.content.faq', compact('faqs', 'stats', 'categories'));

        } catch (\Exception $e) {
            \Log::error('Erreur ContentController@faq: ' . $e->getMessage());
            
            return view('admin.content.faq', [
                'faqs' => collect(),
                'stats' => ['total' => 0, 'publiees' => 0, 'brouillons' => 0, 'vues_total' => 0, 'votes_utiles_total' => 0],
                'categories' => collect()
            ]);
        }
    }

    /**
     * Créer une nouvelle actualité
     */
    public function createActualite()
    {
        return view('admin.content.create-actualite');
    }

    /**
     * Uploader un nouveau document
     */
    public function uploadDocument(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'fichier' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
                'type' => 'required|in:guide,formulaire,legislation,manuel',
                'categorie' => 'required|string|max:100',
                'statut' => 'required|in:public,interne'
            ]);

            // Simuler l'upload (à terme, utiliser Storage::disk())
            $fileName = time() . '_' . $request->file('fichier')->getClientOriginalName();
            
            // Dans la vraie implémentation :
            // $path = $request->file('fichier')->storeAs('documents', $fileName, 'public');

            return response()->json([
                'success' => true,
                'message' => 'Document uploadé avec succès',
                'document' => [
                    'nom' => $request->nom,
                    'fichier' => $fileName,
                    'taille' => $this->formatFileSize($request->file('fichier')->getSize())
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle question FAQ
     */
    public function createFaq(Request $request)
    {
        try {
            $request->validate([
                'question' => 'required|string|max:500',
                'reponse' => 'required|string|max:2000',
                'categorie' => 'required|string|max:100',
                'ordre' => 'nullable|integer|min:1',
                'statut' => 'required|in:publiee,brouillon'
            ]);

            // Simuler la création (à terme, utiliser un modèle FAQ)
            $faq = [
                'id' => rand(1000, 9999),
                'question' => $request->question,
                'reponse' => $request->reponse,
                'categorie' => $request->categorie,
                'ordre' => $request->ordre ?? 999,
                'statut' => $request->statut,
                'vues' => 0,
                'utile_votes' => 0,
                'derniere_modification' => now()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Question FAQ créée avec succès',
                'faq' => $faq
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTHODES PRIVÉES ==========

    /**
     * Calculer la taille totale des documents
     */
    private function calculateTotalSize($documents)
    {
        // Simulation du calcul de taille
        $totalMB = $documents->count() * 2.1; // Moyenne simulée
        
        if ($totalMB > 1024) {
            return round($totalMB / 1024, 1) . ' GB';
        } else {
            return round($totalMB, 1) . ' MB';
        }
    }

    /**
     * Formater la taille d'un fichier
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}