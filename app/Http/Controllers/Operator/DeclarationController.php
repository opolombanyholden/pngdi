<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeclarationController extends Controller
{
    /**
     * Liste des déclarations
     */
    public function index()
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module déclarations annuelles en cours de développement');
    }

    /**
     * Créer une nouvelle déclaration
     */
    public function create($organisation)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module création déclaration en cours de développement');
    }

    /**
     * Enregistrer une déclaration
     */
    public function store(Request $request)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module sauvegarde déclaration en cours de développement');
    }

    /**
     * Afficher une déclaration
     */
    public function show($declaration)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module détail déclaration en cours de développement');
    }

    /**
     * Éditer une déclaration
     */
    public function edit($declaration)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module édition déclaration en cours de développement');
    }

    /**
     * Mettre à jour une déclaration
     */
    public function update(Request $request, $declaration)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module mise à jour déclaration en cours de développement');
    }

    /**
     * Soumettre une déclaration
     */
    public function soumettre($declaration)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module soumission déclaration en cours de développement');
    }

    /**
     * Upload document pour déclaration
     */
    public function uploadDocument(Request $request, $declaration)
    {
        return redirect()->back()
            ->with('info', 'Module upload document déclaration en cours de développement');
    }

    /**
     * Supprimer document de déclaration
     */
    public function deleteDocument($declaration, $document)
    {
        return redirect()->back()
            ->with('info', 'Module suppression document déclaration en cours de développement');
    }

    /**
     * Liste des rapports d'activité
     */
    public function rapportsIndex()
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module rapports d\'activité en cours de développement');
    }

    /**
     * Créer un rapport d'activité
     */
    public function rapportCreate($organisation)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module création rapport en cours de développement');
    }

    /**
     * Enregistrer un rapport
     */
    public function rapportStore(Request $request)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module sauvegarde rapport en cours de développement');
    }

    /**
     * Afficher un rapport
     */
    public function rapportShow($rapport)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module détail rapport en cours de développement');
    }
}