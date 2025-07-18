<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\DocumentType;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    /**
     * Afficher le guide de création d'organisation
     */
    public function creation(Request $request)
    {
        $type = $request->get('type');
        
        // Si pas de type spécifié, afficher la sélection
        if (!$type) {
            return view('operator.guides.creation-select');
        }
        
        // Valider le type
        if (!in_array($type, [
            Organisation::TYPE_ASSOCIATION,
            Organisation::TYPE_ONG,
            Organisation::TYPE_PARTI,
            Organisation::TYPE_CONFESSION
        ])) {
            return redirect()->route('operator.guides.creation')
                ->with('error', 'Type d\'organisation invalide');
        }
        
        // Obtenir les informations pour le guide
        $guide = $this->getCreationGuide($type);
        
        return view('operator.guides.creation', compact('guide', 'type'));
    }
    
    /**
     * Afficher le guide de modification
     */
    public function modification(Organisation $organisation)
    {
        // Vérifier que l'organisation appartient à l'utilisateur
        if ($organisation->user_id !== auth()->id()) {
            abort(403);
        }
        
        $guide = $this->getModificationGuide($organisation);
        
        return view('operator.guides.modification', compact('guide', 'organisation'));
    }
    
    /**
     * Afficher le guide de cessation
     */
    public function cessation(Organisation $organisation)
    {
        // Vérifier que l'organisation appartient à l'utilisateur
        if ($organisation->user_id !== auth()->id()) {
            abort(403);
        }
        
        $guide = $this->getCessationGuide($organisation);
        
        return view('operator.guides.cessation', compact('guide', 'organisation'));
    }
    
    /**
     * Afficher le guide de déclaration annuelle
     */
    public function declaration(Organisation $organisation)
    {
        // Vérifier que l'organisation appartient à l'utilisateur
        if ($organisation->user_id !== auth()->id()) {
            abort(403);
        }
        
        $guide = $this->getDeclarationGuide($organisation);
        
        return view('operator.guides.declaration', compact('guide', 'organisation'));
    }
    
    /**
     * Afficher le guide de fusion
     */
    public function fusion(Request $request)
    {
        $organisationIds = $request->get('organisations', []);
        
        if (count($organisationIds) < 2) {
            return redirect()->route('operator.dashboard')
                ->with('error', 'Vous devez sélectionner au moins 2 organisations pour une fusion');
        }
        
        // Vérifier que toutes les organisations appartiennent à l'utilisateur
        $organisations = Organisation::whereIn('id', $organisationIds)
            ->where('user_id', auth()->id())
            ->get();
        
        if ($organisations->count() !== count($organisationIds)) {
            abort(403);
        }
        
        $guide = $this->getFusionGuide($organisations);
        
        return view('operator.guides.fusion', compact('guide', 'organisations'));
    }
    
    /**
     * Afficher le guide d'absorption
     */
    public function absorption(Request $request)
    {
        $absorbante = Organisation::find($request->get('absorbante'));
        $absorbee = Organisation::find($request->get('absorbee'));
        
        if (!$absorbante || !$absorbee) {
            return redirect()->route('operator.dashboard')
                ->with('error', 'Organisations non trouvées');
        }
        
        // Vérifier les permissions
        if ($absorbante->user_id !== auth()->id() || $absorbee->user_id !== auth()->id()) {
            abort(403);
        }
        
        $guide = $this->getAbsorptionGuide($absorbante, $absorbee);
        
        return view('operator.guides.absorption', compact('guide', 'absorbante', 'absorbee'));
    }
    
    /**
     * Obtenir le guide de création selon le type
     */
    protected function getCreationGuide(string $type): array
    {
        $guide = [
            'title' => $this->getTypeLabel($type),
            'description' => $this->getTypeDescription($type),
            'etapes' => [],
            'documents_requis' => [],
            'conditions' => [],
            'delais' => [],
            'conseils' => []
        ];
        
        // Étapes communes
        $guide['etapes'] = [
            [
                'numero' => 1,
                'titre' => 'Informations de base',
                'description' => 'Renseigner les informations générales de votre organisation',
                'duree_estimee' => '10 minutes'
            ],
            [
                'numero' => 2,
                'titre' => 'Ajout des fondateurs',
                'description' => 'Enregistrer les membres fondateurs (minimum ' . $this->getMinimumFondateurs($type) . ')',
                'duree_estimee' => '15 minutes'
            ],
            [
                'numero' => 3,
                'titre' => 'Documents justificatifs',
                'description' => 'Télécharger tous les documents requis',
                'duree_estimee' => '20 minutes'
            ],
            [
                'numero' => 4,
                'titre' => 'Liste des adhérents',
                'description' => 'Importer ou saisir la liste des adhérents (minimum ' . $this->getMinimumAdherents($type) . ')',
                'duree_estimee' => '30 minutes'
            ],
            [
                'numero' => 5,
                'titre' => 'Vérification et soumission',
                'description' => 'Vérifier toutes les informations et soumettre le dossier',
                'duree_estimee' => '5 minutes'
            ]
        ];
        
        // Documents requis
        $documents = DocumentType::where('type_organisation', $type)
            ->where('type_operation', 'creation')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();
        
        foreach ($documents as $doc) {
            $guide['documents_requis'][] = [
                'nom' => $doc->nom,
                'description' => $doc->description,
                'obligatoire' => $doc->is_obligatoire,
                'format' => implode(', ', $doc->extensions_autorisees ?? ['pdf']),
                'taille_max' => $this->formatBytes($doc->taille_max ?? 5242880)
            ];
        }
        
        // Conditions spécifiques par type
        $guide['conditions'] = $this->getConditionsByType($type);
        
        // Délais
        $guide['delais'] = [
            'traitement_normal' => '15 jours ouvrés',
            'traitement_urgent' => '5 jours ouvrés (avec justification)',
            'validite_recepisse' => $type === Organisation::TYPE_PARTI ? 'Permanente' : '5 ans'
        ];
        
        // Conseils
        $guide['conseils'] = $this->getConseilsByType($type);
        
        return $guide;
    }
    
    /**
     * Obtenir le guide de modification
     */
    protected function getModificationGuide(Organisation $organisation): array
    {
        return [
            'title' => 'Modification de ' . $organisation->nom,
            'description' => 'Guide pour modifier les informations de votre organisation',
            'etapes' => [
                [
                    'numero' => 1,
                    'titre' => 'Identifier les modifications',
                    'description' => 'Déterminer précisément les éléments à modifier'
                ],
                [
                    'numero' => 2,
                    'titre' => 'Préparer les justificatifs',
                    'description' => 'Rassembler les documents justifiant les modifications'
                ],
                [
                    'numero' => 3,
                    'titre' => 'Effectuer les modifications',
                    'description' => 'Mettre à jour les informations dans le système'
                ],
                [
                    'numero' => 4,
                    'titre' => 'Soumettre le dossier',
                    'description' => 'Envoyer le dossier de modification pour validation'
                ]
            ],
            'documents_requis' => $this->getDocumentsForModification($organisation),
            'conditions' => [
                'L\'organisation doit être active',
                'Aucune procédure en cours',
                'Respecter les statuts de l\'organisation'
            ]
        ];
    }
    
    /**
     * Obtenir le guide de cessation
     */
    protected function getCessationGuide(Organisation $organisation): array
    {
        return [
            'title' => 'Cessation d\'activité',
            'description' => 'Procédure de dissolution de votre organisation',
            'avertissement' => 'Cette action est irréversible. Une fois la cessation approuvée, l\'organisation sera définitivement fermée.',
            'etapes' => [
                [
                    'numero' => 1,
                    'titre' => 'Décision de dissolution',
                    'description' => 'Organiser une assemblée générale extraordinaire pour voter la dissolution'
                ],
                [
                    'numero' => 2,
                    'titre' => 'Liquidation des biens',
                    'description' => 'Procéder à la liquidation des biens selon les statuts'
                ],
                [
                    'numero' => 3,
                    'titre' => 'Documents de cessation',
                    'description' => 'Préparer tous les documents requis'
                ],
                [
                    'numero' => 4,
                    'titre' => 'Soumission finale',
                    'description' => 'Soumettre le dossier de cessation'
                ]
            ],
            'documents_requis' => [
                'Procès-verbal de l\'assemblée générale de dissolution',
                'Rapport de liquidation',
                'Quitus fiscal',
                'Attestation de non-dette sociale',
                'Liste des biens et leur destination'
            ]
        ];
    }
    
    /**
     * Obtenir le guide de déclaration annuelle
     */
    protected function getDeclarationGuide(Organisation $organisation): array
    {
        return [
            'title' => 'Déclaration annuelle',
            'description' => 'Guide pour votre déclaration annuelle d\'activités',
            'periode' => 'Année ' . date('Y'),
            'date_limite' => '31 mars ' . (date('Y') + 1),
            'etapes' => [
                [
                    'numero' => 1,
                    'titre' => 'Rapport d\'activités',
                    'description' => 'Rédiger le rapport détaillé des activités de l\'année'
                ],
                [
                    'numero' => 2,
                    'titre' => 'Rapport financier',
                    'description' => 'Préparer le bilan financier et le compte de résultat'
                ],
                [
                    'numero' => 3,
                    'titre' => 'Mise à jour des adhérents',
                    'description' => 'Actualiser la liste des adhérents actifs'
                ],
                [
                    'numero' => 4,
                    'titre' => 'Validation et envoi',
                    'description' => 'Faire valider par les organes dirigeants et soumettre'
                ]
            ],
            'documents_requis' => [
                'Rapport d\'activités détaillé',
                'États financiers certifiés',
                'Procès-verbal de l\'assemblée générale',
                'Liste actualisée des membres du bureau',
                'Liste des adhérents à jour'
            ]
        ];
    }
    
    /**
     * Obtenir le guide de fusion
     */
    protected function getFusionGuide($organisations): array
    {
        return [
            'title' => 'Fusion d\'organisations',
            'description' => 'Procédure de fusion de plusieurs organisations en une seule',
            'organisations_concernees' => $organisations->pluck('nom')->toArray(),
            'etapes' => [
                [
                    'numero' => 1,
                    'titre' => 'Accord de principe',
                    'description' => 'Obtenir l\'accord de toutes les organisations concernées'
                ],
                [
                    'numero' => 2,
                    'titre' => 'Projet de fusion',
                    'description' => 'Élaborer le projet détaillé de fusion'
                ],
                [
                    'numero' => 3,
                    'titre' => 'Assemblées générales',
                    'description' => 'Faire approuver la fusion par chaque organisation'
                ],
                [
                    'numero' => 4,
                    'titre' => 'Constitution de la nouvelle entité',
                    'description' => 'Créer la nouvelle organisation issue de la fusion'
                ]
            ]
        ];
    }
    
    /**
     * Obtenir le guide d'absorption
     */
    protected function getAbsorptionGuide($absorbante, $absorbee): array
    {
        return [
            'title' => 'Absorption d\'organisation',
            'description' => sprintf(
                'Procédure d\'absorption de "%s" par "%s"',
                $absorbee->nom,
                $absorbante->nom
            ),
            'etapes' => [
                [
                    'numero' => 1,
                    'titre' => 'Négociation',
                    'description' => 'Négocier les termes de l\'absorption'
                ],
                [
                    'numero' => 2,
                    'titre' => 'Traité d\'absorption',
                    'description' => 'Rédiger et signer le traité d\'absorption'
                ],
                [
                    'numero' => 3,
                    'titre' => 'Transfert des actifs',
                    'description' => 'Organiser le transfert des biens et des membres'
                ],
                [
                    'numero' => 4,
                    'titre' => 'Finalisation',
                    'description' => 'Finaliser l\'absorption et dissoudre l\'organisation absorbée'
                ]
            ]
        ];
    }
    
    /**
     * Helpers
     */
    protected function getTypeLabel(string $type): string
    {
        $labels = [
            Organisation::TYPE_ASSOCIATION => 'Création d\'une Association',
            Organisation::TYPE_ONG => 'Création d\'une ONG',
            Organisation::TYPE_PARTI => 'Création d\'un Parti Politique',
            Organisation::TYPE_CONFESSION => 'Création d\'une Confession Religieuse'
        ];
        
        return $labels[$type] ?? 'Création d\'organisation';
    }
    
    protected function getTypeDescription(string $type): string
    {
        $descriptions = [
            Organisation::TYPE_ASSOCIATION => 'Une association est un groupement de personnes volontaires réunies autour d\'un projet commun ou partageant des activités, mais sans chercher à réaliser de bénéfices.',
            Organisation::TYPE_ONG => 'Une Organisation Non Gouvernementale (ONG) est une association à but non lucratif, d\'intérêt public, qui ne relève ni de l\'État, ni d\'institutions internationales.',
            Organisation::TYPE_PARTI => 'Un parti politique est une organisation politique qui cherche à influencer une politique gouvernementale, en nommant ses propres candidats et en tentant d\'obtenir des mandats politiques.',
            Organisation::TYPE_CONFESSION => 'Une confession religieuse est une communauté de personnes partageant une même doctrine religieuse et pratiquant ensemble leur foi.'
        ];
        
        return $descriptions[$type] ?? '';
    }
    
    protected function getMinimumFondateurs(string $type): int
    {
        $minimums = [
            Organisation::TYPE_ASSOCIATION => 1,
            Organisation::TYPE_ONG => 1,
            Organisation::TYPE_PARTI => 1,
            Organisation::TYPE_CONFESSION => 1
        ];
        
        return $minimums[$type] ?? 1;
    }
    
    protected function getMinimumAdherents(string $type): int
    {
        $minimums = [
            Organisation::TYPE_ASSOCIATION => 10,
            Organisation::TYPE_ONG => 20,
            Organisation::TYPE_PARTI => 1000,
            Organisation::TYPE_CONFESSION => 50
        ];
        
        return $minimums[$type] ?? 10;
    }
    
    protected function getConditionsByType(string $type): array
    {
        $conditions = [
            Organisation::TYPE_ASSOCIATION => [
                'Minimum 3 fondateurs majeurs',
                'Minimum 10 adhérents',
                'Objet social non lucratif',
                'Siège social au Gabon'
            ],
            Organisation::TYPE_ONG => [
                'Minimum 5 fondateurs',
                'Minimum 20 adhérents',
                'Domaine d\'intervention défini',
                'Budget prévisionnel'
            ],
            Organisation::TYPE_PARTI => [
                'Minimum 10 fondateurs',
                'Minimum 1000 adhérents',
                'Représentation dans au moins 50% des provinces',
                'Programme politique défini',
                'Un seul parti par opérateur'
            ],
            Organisation::TYPE_CONFESSION => [
                'Minimum 7 fondateurs',
                'Minimum 50 adhérents',
                'Lieu de culte identifié',
                'Doctrine religieuse claire',
                'Une seule confession par opérateur'
            ]
        ];
        
        return $conditions[$type] ?? [];
    }
    
    protected function getConseilsByType(string $type): array
    {
        return [
            'Préparez tous vos documents avant de commencer',
            'Assurez-vous que tous les fondateurs ont leur NIP',
            'Vérifiez l\'exactitude de toutes les informations',
            'Gardez une copie de tous les documents soumis',
            'Respectez les formats de fichiers demandés'
        ];
    }
    
    protected function getDocumentsForModification(Organisation $organisation): array
    {
        return [
            'Procès-verbal de la décision de modification',
            'Nouveaux statuts si modification statutaire',
            'Justificatifs des changements',
            'Formulaire de modification dûment rempli'
        ];
    }
    
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}