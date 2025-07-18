<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NipDatabase;
use App\Services\NipDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NipDatabaseController extends Controller
{
    protected $nipService;

    public function __construct(NipDatabaseService $nipService)
    {
        $this->nipService = $nipService;
        $this->middleware('auth');
        $this->middleware('role:admin'); // Supposant que vous avez un middleware role
    }

    /**
     * Affichage de la page principale de gestion NIP
     */
    public function index(Request $request)
    {
        $statistics = NipDatabase::getStatistics();
        
        // Recherche et filtres
        $query = $request->get('search');
        $filters = [
            'statut' => $request->get('statut'),
            'sexe' => $request->get('sexe'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to')
        ];

        $nips = $this->nipService->searchNip($query, $filters);

        return view('admin.nip-database.index', compact('statistics', 'nips', 'query', 'filters'));
    }

    /**
     * Affichage du formulaire d'import
     */
    public function import()
    {
        return view('admin.nip-database.import');
    }

    /**
     * Traitement de l'import Excel
     */
    public function processImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:61200'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            $result = $this->nipService->importFromExcel(
                $request->file('excel_file'),
                Auth::id()
            );

            if ($result['success']) {
                $message = "Import terminé avec succès. ";
                $message .= "Importés: {$result['stats']['imported']}, ";
                $message .= "Mis à jour: {$result['stats']['updated']}, ";
                $message .= "Ignorés: {$result['stats']['skipped']}, ";
                $message .= "Erreurs: {$result['stats']['errors']}";

                return redirect()->route('admin.nip-database.index')
                               ->with('success', $message)
                               ->with('import_stats', $result['stats']);
            } else {
                return redirect()->back()
                               ->with('error', $result['message'])
                               ->with('import_stats', $result['stats'] ?? null);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }

    /**
     * Affichage des détails d'un NIP
     */
    public function show($id)
    {
        $nip = NipDatabase::findOrFail($id);
        
        // Recherche d'adhérents utilisant ce NIP
        $adherents = \App\Models\Adherent::where('nip', $nip->nip)
                                        ->with('organisation')
                                        ->get();

        return view('admin.nip-database.show', compact('nip', 'adherents'));
    }

    /**
     * Formulaire d'édition d'un NIP
     */
    public function edit($id)
    {
        $nip = NipDatabase::findOrFail($id);
        return view('admin.nip-database.edit', compact('nip'));
    }

    /**
     * Mise à jour d'un NIP
     */
    public function update(Request $request, $id)
    {
        $nip = NipDatabase::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'lieu_naissance' => 'nullable|string|max:255',
            'statut' => 'required|in:actif,inactif,decede,suspendu',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'remarques' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $nip->update($request->only([
            'nom', 'prenom', 'lieu_naissance', 
            'statut', 'telephone', 'email', 'remarques'
        ]));

        $nip->update(['last_verified_at' => now()]);

        return redirect()->route('admin.nip-database.show', $nip)
                        ->with('success', 'NIP mis à jour avec succès');
    }

    /**
     * Recherche Ajax de NIP
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 3) {
            return response()->json(['results' => []]);
        }

        $nips = NipDatabase::search($query)
                          ->actifs()
                          ->limit(20)
                          ->get(['id', 'nom', 'prenom', 'date_naissance', 'lieu_naissance', 'nip'])
                          ->map(function($nip) {
                              return [
                                  'id' => $nip->id,
                                  'text' => "{$nip->nom} {$nip->prenom} - {$nip->nip}",
                                  'nip' => $nip->nip,
                                  'nom' => $nip->nom,
                                  'prenom' => $nip->prenom,
                                  'age' => $nip->age
                              ];
                          });

        return response()->json(['results' => $nips]);
    }

    /**
     * Vérification d'un NIP spécifique
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'NIP requis'
            ]);
        }

        $result = $this->nipService->verifyNip($request->nip);

        return response()->json([
            'success' => $result['found'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null
        ]);
    }

    /**
     * Suppression d'un NIP (soft delete ou hard delete selon politique)
     */
    public function destroy($id)
    {
        $nip = NipDatabase::findOrFail($id);
        
        // Vérifier s'il y a des adhérents utilisant ce NIP
        $adherentsCount = \App\Models\Adherent::where('nip', $nip->nip)->count();
        
        if ($adherentsCount > 0) {
            return redirect()->back()
                           ->with('error', "Impossible de supprimer ce NIP. {$adherentsCount} adhérent(s) l'utilise(nt) encore.");
        }

        $nip->delete();

        return redirect()->route('admin.nip-database.index')
                        ->with('success', 'NIP supprimé avec succès');
    }

    /**
     * Export Excel de la base NIP
     */
    public function export(Request $request)
    {
        // Cette fonction sera implémentée selon les besoins
        return redirect()->back()
                        ->with('info', 'Fonction d\'export en cours de développement');
    }

    /**
     * Nettoyage de la base (suppression des doublons, etc.)
     */
    public function cleanup()
    {
        try {
            // Logique de nettoyage - à implémenter selon les besoins
            $duplicates = NipDatabase::select('nip')
                                    ->groupBy('nip')
                                    ->havingRaw('COUNT(*) > 1')
                                    ->get();

            $cleanedCount = 0;
            foreach ($duplicates as $duplicate) {
                // Garder le plus récent et supprimer les autres
                $nips = NipDatabase::where('nip', $duplicate->nip)
                                  ->orderBy('updated_at', 'desc')
                                  ->get();
                
                for ($i = 1; $i < $nips->count(); $i++) {
                    $nips[$i]->delete();
                    $cleanedCount++;
                }
            }

            return redirect()->route('admin.nip-database.index')
                           ->with('success', "Nettoyage terminé. {$cleanedCount} doublons supprimés.");

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Erreur lors du nettoyage: ' . $e->getMessage());
        }
    }

    /**
     * Téléchargement du template Excel
     */
    public function downloadTemplate()
    {
        $headers = [
            'Nom', 'Prénom', 'Date de naissance', 'Lieu de naissance', 'NIP',
            'Statut', 'Téléphone', 'Email', 'Remarques'
        ];

        // Création d'un fichier CSV simple pour le template
        $filename = 'template_import_nip.csv';
        
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        fputcsv($handle, $headers);
        
        // Ligne d'exemple avec l'ordre correct (sans nationalité)
        fputcsv($handle, [
            'MVONDO',
            'Jean Pierre',
            '15/01/1990',
            'Libreville',
            'GA-1234-19900115',
            'actif',
            '066123456',
            'jean.mvondo@email.com',
            'Exemple de NIP'
        ]);
        
        fclose($handle);
        
        return;
    }
}