<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReferentielController extends Controller
{
    /**
     * Page principale des référentiels
     */
    public function index()
    {
        return view('admin.referentiels.index');
    }

    /**
     * ⚡ MÉTHODES SIMPLES POUR LE MENU ADMIN - OBLIGATOIRES
     */
    
    /**
     * Page de gestion des types d'organisations
     */
    public function typesOrganisations()
    {
        $types = [
            'association' => 'Association',
            'ong' => 'ONG',
            'parti_politique' => 'Parti Politique',
            'confession_religieuse' => 'Confession Religieuse'
        ];
        
        return view('admin.referentiels.types-organisations', compact('types'));
    }

    /**
     * Page de gestion des types de documents
     */
    public function documentTypes()
    {
        $documentTypes = [
            'statuts' => 'Statuts de l\'organisation',
            'reglement_interieur' => 'Règlement intérieur',
            'liste_membres_fondateurs' => 'Liste des membres fondateurs',
            'proces_verbal_ag' => 'Procès-verbal d\'AG constitutive',
            'piece_identite' => 'Pièce d\'identité dirigeants',
            'casier_judiciaire' => 'Casier judiciaire'
        ];
        
        return view('admin.referentiels.document-types', compact('documentTypes'));
    }

    /**
     * Page de gestion des zones géographiques
     */
    public function zones()
    {
        $provinces = [
            'estuaire' => 'Estuaire',
            'haut_ogooue' => 'Haut-Ogooué',
            'moyen_ogooue' => 'Moyen-Ogooué',
            'ngounie' => 'Ngounié',
            'nyanga' => 'Nyanga',
            'ogooue_ivindo' => 'Ogooué-Ivindo',
            'ogooue_lolo' => 'Ogooué-Lolo',
            'ogooue_maritime' => 'Ogooué-Maritime',
            'woleu_ntem' => 'Woleu-Ntem'
        ];
        
        return view('admin.referentiels.zones', compact('provinces'));
    }

    /**
     * MÉTHODES CRUD POUR TYPES D'ORGANISATIONS
     */
    
    public function typesIndex()
    {
        return response()->json(['message' => 'Types index - À implémenter']);
    }

    public function typesStore(Request $request)
    {
        return response()->json(['message' => 'Type créé - À implémenter']);
    }

    public function typesUpdate(Request $request, $id)
    {
        return response()->json(['message' => 'Type mis à jour - À implémenter']);
    }

    public function typesDestroy($id)
    {
        return response()->json(['message' => 'Type supprimé - À implémenter']);
    }

    public function typesReorder(Request $request)
    {
        return response()->json(['message' => 'Ordre mis à jour - À implémenter']);
    }

    /**
     * MÉTHODES CRUD POUR DOCUMENTS
     */
    
    public function documentsIndex()
    {
        return response()->json(['message' => 'Documents index - À implémenter']);
    }

    public function documentsStore(Request $request)
    {
        return response()->json(['message' => 'Document créé - À implémenter']);
    }

    public function documentsUpdate(Request $request, $id)
    {
        return response()->json(['message' => 'Document mis à jour - À implémenter']);
    }

    public function documentsDestroy($id)
    {
        return response()->json(['message' => 'Document supprimé - À implémenter']);
    }

    /**
     * MÉTHODES CRUD POUR ZONES
     */
    
    public function zonesIndex()
    {
        return response()->json(['message' => 'Zones index - À implémenter']);
    }

    public function zonesStore(Request $request)
    {
        return response()->json(['message' => 'Zone créée - À implémenter']);
    }

    public function zonesUpdate(Request $request, $id)
    {
        return response()->json(['message' => 'Zone mise à jour - À implémenter']);
    }

    public function zonesDestroy($id)
    {
        return response()->json(['message' => 'Zone supprimée - À implémenter']);
    }

    public function getDepartements($province)
    {
        // Exemple de départements par province
        $departements = [
            'estuaire' => ['libreville', 'komo-mondah', 'noya'],
            'haut_ogooue' => ['franceville', 'lekoko', 'djouori-aguili'],
            // ... autres provinces
        ];
        
        return response()->json($departements[$province] ?? []);
    }

    public function getCommunes($departement)
    {
        // Exemple de communes par département
        $communes = [
            'libreville' => ['libreville-1', 'libreville-2', 'libreville-3'],
            'franceville' => ['franceville-1', 'franceville-2'],
            // ... autres départements
        ];
        
        return response()->json($communes[$departement] ?? []);
    }
}