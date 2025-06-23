<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DossierController extends Controller
{
    /**
     * Afficher la liste des dossiers
     */
    public function index()
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module dossiers en cours de développement');
    }

    /**
     * Afficher le formulaire de création
     */
    public function create($type)
    {
        if (!in_array($type, ['association', 'ong', 'parti', 'confession'])) {
            abort(404);
        }

        return redirect()->route('operator.dashboard')
            ->with('info', "Module création $type en cours de développement");
    }

    /**
     * Enregistrer un nouveau dossier
     */
    public function store(Request $request)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module sauvegarde dossier en cours de développement');
    }

    /**
     * Afficher un dossier spécifique
     */
    public function show($dossier)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module détail dossier en cours de développement');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($dossier)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module édition dossier en cours de développement');
    }

    /**
     * Mettre à jour un dossier
     */
    public function update(Request $request, $dossier)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module mise à jour dossier en cours de développement');
    }

    /**
     * Soumettre un dossier
     */
    public function soumettre($dossier)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module soumission dossier en cours de développement');
    }

    /**
     * Uploader un document
     */
    public function uploadDocument(Request $request, $dossier)
    {
        return redirect()->back()
            ->with('info', 'Module upload document en cours de développement');
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument($dossier, $document)
    {
        return redirect()->back()
            ->with('info', 'Module suppression document en cours de développement');
    }

    /**
     * Télécharger un document
     */
    public function downloadDocument($document)
    {
        return redirect()->back()
            ->with('info', 'Module téléchargement document en cours de développement');
    }

    /**
     * Liste des subventions
     */
    public function subventionsIndex()
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module subventions en cours de développement');
    }

    /**
     * Créer une demande de subvention
     */
    public function subventionCreate($organisation)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module création subvention en cours de développement');
    }

    /**
     * Enregistrer une demande de subvention
     */
    public function subventionStore(Request $request)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module sauvegarde subvention en cours de développement');
    }

    /**
     * Afficher une demande de subvention
     */
    public function subventionShow($subvention)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module détail subvention en cours de développement');
    }
}