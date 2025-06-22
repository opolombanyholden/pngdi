<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Données pour la page d'accueil
        $stats = [
            'associations' => 150,
            'confessions' => 45,
            'partis' => 12,
            'ong' => 87
        ];

        $actualites = [
            [
                'id' => 1,
                'slug' => 'nouvelle-procedure-formalisation-simplifiee',
                'titre' => 'Nouvelle procédure de formalisation simplifiée',
                'date' => '2025-01-15',
                'extrait' => 'Le Ministère de l\'Intérieur annonce la simplification des démarches administratives pour la création d\'associations.',
                'image' => null,
                'categorie' => 'Réglementation'
            ],
            [
                'id' => 2,
                'slug' => 'seminaire-national-associations-2025',
                'titre' => 'Séminaire national sur les associations',
                'date' => '2025-01-10',
                'extrait' => 'Un séminaire de formation pour les responsables d\'associations se tiendra du 25 au 27 janvier 2025.',
                'image' => null,
                'categorie' => 'Événement'
            ],
            [
                'id' => 3,
                'slug' => 'mise-a-jour-documents-requis-ong',
                'titre' => 'Mise à jour des documents requis',
                'date' => '2025-01-05',
                'extrait' => 'La liste des documents nécessaires pour la création d\'ONG a été actualisée. Consultez les nouvelles exigences.',
                'image' => null,
                'categorie' => 'Documentation'
            ]
        ];

        $services = [
            [
                'icon' => 'fas fa-file-alt',
                'titre' => 'Formalisation en ligne',
                'description' => 'Créez et soumettez vos dossiers de formalisation directement en ligne, 24h/24 et 7j/7.'
            ],
            [
                'icon' => 'fas fa-search',
                'titre' => 'Suivi en temps réel',
                'description' => 'Suivez l\'état d\'avancement de vos dossiers en temps réel depuis votre espace personnel.'
            ],
            [
                'icon' => 'fas fa-comments',
                'titre' => 'Communication directe',
                'description' => 'Échangez directement avec l\'administration via notre messagerie sécurisée intégrée.'
            ],
            [
                'icon' => 'fas fa-download',
                'titre' => 'Documents et guides',
                'description' => 'Accédez à tous les documents types, guides et ressources nécessaires à vos démarches.'
            ]
        ];

        return view('home', compact('stats', 'actualites', 'services'));
    }

 

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email',
            'sujet' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        // Logique d'envoi de message (à implémenter plus tard)
        
        return redirect()->route('contact')->with('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
    }

    public function documents()
    {
        // Rediriger vers le DocumentController
        return redirect()->route('documents.index');
    }

    public function faq()
    {
        $faqs = [
            'general' => [
                'titre' => 'Questions générales',
                'questions' => [
                    [
                        'question' => 'Qu\'est-ce que le PNGDI ?',
                        'reponse' => 'Le Portail National de Gestion des Libertés Individuelles (PNGDI) est une plateforme numérique mise en place par le Ministère de l\'Intérieur pour faciliter la formalisation et la gestion des organisations associatives, religieuses et politiques au Gabon.'
                    ],
                    [
                        'question' => 'Qui peut utiliser le PNGDI ?',
                        'reponse' => 'Le portail est accessible à toute personne souhaitant créer ou gérer une association, une ONG, un parti politique ou une confession religieuse au Gabon. Les responsables d\'organisations existantes peuvent également l\'utiliser pour leurs démarches administratives.'
                    ],
                    [
                        'question' => 'Le service est-il gratuit ?',
                        'reponse' => 'L\'inscription et l\'utilisation du portail sont gratuites. Cependant, certaines procédures administratives peuvent être soumises à des frais réglementaires selon la législation en vigueur.'
                    ]
                ]
            ],
            'creation' => [
                'titre' => 'Création d\'organisation',
                'questions' => [
                    [
                        'question' => 'Quels sont les documents nécessaires pour créer une association ?',
                        'reponse' => 'Pour créer une association, vous devez fournir : les statuts de l\'association, le procès-verbal de l\'assemblée constitutive, la liste des membres fondateurs avec leurs coordonnées, une copie des pièces d\'identité des dirigeants, et un justificatif de domicile du siège social.'
                    ],
                    [
                        'question' => 'Combien de temps prend le traitement d\'un dossier ?',
                        'reponse' => 'Le délai de traitement varie selon le type d\'organisation : 15 jours ouvrables pour une association, 30 jours pour une ONG, 45 jours pour un parti politique, et 30 jours pour une confession religieuse. Ces délais commencent à partir de la réception d\'un dossier complet.'
                    ],
                    [
                        'question' => 'Puis-je créer plusieurs organisations ?',
                        'reponse' => 'Un utilisateur peut créer plusieurs associations ou ONG. Cependant, il ne peut créer qu\'un seul parti politique actif et qu\'une seule confession religieuse active à la fois, conformément à la réglementation.'
                    ]
                ]
            ],
            'technique' => [
                'titre' => 'Questions techniques',
                'questions' => [
                    [
                        'question' => 'Puis-je suivre l\'avancement de mon dossier en ligne ?',
                        'reponse' => 'Oui, une fois votre dossier soumis, vous pouvez suivre son avancement en temps réel depuis votre espace personnel. Vous recevrez également des notifications par email à chaque étape importante du traitement.'
                    ],
                    [
                        'question' => 'Comment récupérer mon mot de passe ?',
                        'reponse' => 'Cliquez sur "Mot de passe oublié" sur la page de connexion. Entrez votre adresse email, et vous recevrez un lien pour réinitialiser votre mot de passe. Ce lien est valable pendant 24 heures.'
                    ],
                    [
                        'question' => 'Quels formats de documents sont acceptés ?',
                        'reponse' => 'Le portail accepte les formats PDF, JPEG, PNG pour les documents scannés. La taille maximale par fichier est de 5 MB. Assurez-vous que vos documents sont lisibles et complets avant de les télécharger.'
                    ]
                ]
            ],
            'gestion' => [
                'titre' => 'Gestion et conformité',
                'questions' => [
                    [
                        'question' => 'Quand dois-je faire ma déclaration annuelle ?',
                        'reponse' => 'La déclaration annuelle doit être soumise avant le 31 mars de chaque année. Elle comprend le rapport d\'activités de l\'année écoulée, le bilan financier et la liste actualisée des membres du bureau.'
                    ],
                    [
                        'question' => 'Que se passe-t-il si je ne fais pas ma déclaration annuelle ?',
                        'reponse' => 'Le non-respect de l\'obligation de déclaration annuelle peut entraîner des sanctions allant de l\'avertissement à la suspension temporaire ou définitive de l\'agrément de votre organisation.'
                    ],
                    [
                        'question' => 'Comment modifier les informations de mon organisation ?',
                        'reponse' => 'Connectez-vous à votre espace personnel et accédez à la section "Gérer mon organisation". Vous pourrez y modifier les informations et soumettre les documents justificatifs nécessaires. Certaines modifications nécessitent une validation administrative.'
                    ]
                ]
            ]
        ];
        
        return view('public.faq', compact('faqs'));
    }

    public function guides()
    {
        $guides = [
            [
                'titre' => 'Guide de création d\'association',
                'description' => 'Tout ce que vous devez savoir pour créer votre association étape par étape',
                'pages' => 24,
                'mise_a_jour' => '2025-01-10',
                'categorie' => 'Association',
                'telechargements' => 1543
            ],
            [
                'titre' => 'Manuel de l\'utilisateur PNGDI',
                'description' => 'Guide complet d\'utilisation de la plateforme avec captures d\'écran',
                'pages' => 45,
                'mise_a_jour' => '2025-01-05',
                'categorie' => 'Général',
                'telechargements' => 2876
            ],
            [
                'titre' => 'Procédures pour les ONG',
                'description' => 'Procédures spécifiques et exigences pour les organisations non gouvernementales',
                'pages' => 32,
                'mise_a_jour' => '2024-12-20',
                'categorie' => 'ONG',
                'telechargements' => 987
            ],
            [
                'titre' => 'Guide fiscal pour associations',
                'description' => 'Comprendre les obligations fiscales et les exonérations possibles',
                'pages' => 18,
                'mise_a_jour' => '2024-11-15',
                'categorie' => 'Association',
                'telechargements' => 654
            ],
            [
                'titre' => 'Modèle de gestion pour partis politiques',
                'description' => 'Bonnes pratiques de gestion administrative et financière',
                'pages' => 28,
                'mise_a_jour' => '2024-10-30',
                'categorie' => 'Parti politique',
                'telechargements' => 234
            ]
        ];
        
        return view('public.guides', compact('guides'));
    }

    public function calendrier()
    {
        $evenements = [
            [
                'titre' => 'Date limite déclarations annuelles',
                'date' => '2025-03-31',
                'type' => 'echeance',
                'description' => 'Toutes les organisations doivent soumettre leur déclaration annuelle avant cette date.',
                'important' => true
            ],
            [
                'titre' => 'Formation en ligne - Gestion associative',
                'date' => '2025-02-15',
                'type' => 'formation',
                'description' => 'Webinaire gratuit sur les bonnes pratiques de gestion d\'association.',
                'important' => false
            ],
            [
                'titre' => 'Maintenance programmée du portail',
                'date' => '2025-02-01',
                'type' => 'maintenance',
                'description' => 'Le portail sera indisponible de 22h à 2h pour maintenance.',
                'important' => true
            ],
            [
                'titre' => 'Séminaire national des ONG',
                'date' => '2025-04-10',
                'type' => 'evenement',
                'description' => 'Rencontre annuelle des ONG au Centre International de Conférences.',
                'important' => false
            ],
            [
                'titre' => 'Ouverture inscriptions formations Q2',
                'date' => '2025-03-15',
                'type' => 'formation',
                'description' => 'Inscriptions pour les formations du deuxième trimestre.',
                'important' => false
            ]
        ];
        
        // Trier par date
        usort($evenements, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
        
        return view('public.calendrier', compact('evenements'));
    }
}