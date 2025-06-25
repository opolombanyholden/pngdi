<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Adherent;
use App\Models\AdherentImport;
use App\Models\AdherentHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;
use League\Csv\Writer;
use Exception;

class AdherentImportService
{
    protected $requiredColumns = [
        'nip',
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        'profession',
        'adresse',
        'province',
        'departement',
        'telephone',
        'email'
    ];
    
    protected $optionalColumns = [
        'canton',
        'prefecture',
        'sous_prefecture',
        'date_adhesion',
        'numero_carte',
        'is_fondateur'
    ];
    
    /**
     * Importer des adhérents depuis un fichier CSV
     */
    public function importFromCsv(Organisation $organisation, UploadedFile $file): array
    {
        // Créer l'enregistrement d'import
        $import = AdherentImport::create([
            'organisation_id' => $organisation->id,
            'user_id' => auth()->id(),
            'nom_fichier' => $file->getClientOriginalName(),
            'statut' => 'en_cours',
            'total_lignes' => 0,
            'lignes_importees' => 0,
            'lignes_erreur' => 0
        ]);
        
        try {
            // Sauvegarder le fichier
            $path = $file->store('imports/adherents/' . $organisation->id);
            $import->update(['chemin_fichier' => $path]);
            
            // Lire le CSV
            $csv = Reader::createFromPath(Storage::path($path), 'r');
            $csv->setHeaderOffset(0);
            $csv->setDelimiter(';'); // Délimiteur par défaut
            
            // Vérifier les colonnes
            $headers = $csv->getHeader();
            $missingColumns = $this->validateHeaders($headers);
            
            if (!empty($missingColumns)) {
                throw new Exception('Colonnes manquantes : ' . implode(', ', $missingColumns));
            }
            
            // Traiter les lignes
            $results = $this->processRows($organisation, $csv, $import);
            
            // Mettre à jour l'import
            $import->update([
                'statut' => 'termine',
                'total_lignes' => $results['total'],
                'lignes_importees' => $results['success'],
                'lignes_erreur' => $results['errors'],
                'erreurs' => $results['error_details'],
                'termine_at' => now()
            ]);
            
            return [
                'success' => true,
                'import_id' => $import->id,
                'summary' => $results
            ];
            
        } catch (Exception $e) {
            $import->update([
                'statut' => 'erreur',
                'erreurs' => ['message' => $e->getMessage()],
                'termine_at' => now()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Valider les en-têtes du CSV
     */
    protected function validateHeaders(array $headers): array
    {
        $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));
        $missingColumns = [];
        
        foreach ($this->requiredColumns as $required) {
            if (!in_array(strtolower($required), $normalizedHeaders)) {
                $missingColumns[] = $required;
            }
        }
        
        return $missingColumns;
    }
    
    /**
     * Traiter les lignes du CSV
     */
    protected function processRows(Organisation $organisation, Reader $csv, AdherentImport $import): array
    {
        $total = 0;
        $success = 0;
        $errors = 0;
        $errorDetails = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($csv->getRecords() as $offset => $record) {
                $total++;
                $lineNumber = $offset + 2; // +2 car offset commence à 0 et on a une ligne d'en-tête
                
                try {
                    // Normaliser les données
                    $data = $this->normalizeRecord($record);
                    
                    // Valider les données
                    $validation = $this->validateRecord($data, $organisation);
                    
                    if ($validation['valid']) {
                        // Créer ou mettre à jour l'adhérent
                        $this->createOrUpdateAdherent($organisation, $data, $import);
                        $success++;
                    } else {
                        $errors++;
                        $errorDetails[] = [
                            'ligne' => $lineNumber,
                            'nip' => $data['nip'] ?? 'N/A',
                            'erreurs' => $validation['errors']
                        ];
                    }
                    
                } catch (Exception $e) {
                    $errors++;
                    $errorDetails[] = [
                        'ligne' => $lineNumber,
                        'nip' => $record['nip'] ?? 'N/A',
                        'erreurs' => [$e->getMessage()]
                    ];
                }
                
                // Commit par batch de 100
                if ($total % 100 === 0) {
                    DB::commit();
                    DB::beginTransaction();
                }
            }
            
            DB::commit();
            
            return [
                'total' => $total,
                'success' => $success,
                'errors' => $errors,
                'error_details' => $errorDetails
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Normaliser un enregistrement
     */
    protected function normalizeRecord(array $record): array
    {
        $normalized = [];
        
        foreach ($record as $key => $value) {
            $key = strtolower(trim($key));
            $value = trim($value);
            
            // Convertir les valeurs vides en null
            if ($value === '') {
                $value = null;
            }
            
            // Formater les dates
            if (in_array($key, ['date_naissance', 'date_adhesion']) && $value) {
                try {
                    $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                } catch (Exception $e) {
                    // Garder la valeur originale si parsing échoue
                }
            }
            
            // Normaliser le sexe
            if ($key === 'sexe' && $value) {
                $value = strtoupper(substr($value, 0, 1));
                if (!in_array($value, ['M', 'F'])) {
                    $value = null;
                }
            }
            
            // Normaliser is_fondateur
            if ($key === 'is_fondateur') {
                $value = in_array(strtolower($value), ['oui', 'yes', '1', 'true']) ? '1' : '0';
            }
            
            $normalized[$key] = $value;
        }
        
        return $normalized;
    }
    
    /**
     * Valider un enregistrement
     */
    protected function validateRecord(array $data, Organisation $organisation): array
    {
        $errors = [];
        
        // Règles de validation
        $rules = [
            'nip' => 'required|string|max:20',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'nationalite' => 'required|string|max:100',
            'profession' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'province' => 'required|string|max:100',
            'departement' => 'required|string|max:100',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255'
        ];
        
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $errors[] = $error;
            }
        }
        
        // Validation métier supplémentaire
        if (isset($data['date_naissance'])) {
            $age = \Carbon\Carbon::parse($data['date_naissance'])->age;
            if ($age < 18) {
                $errors[] = 'L\'adhérent doit avoir au moins 18 ans';
            }
        }
        
        // Pour les partis politiques, vérifier l'unicité
        if ($organisation->isPartiPolitique() && isset($data['nip'])) {
            $canJoin = Adherent::canJoinOrganisation($data['nip'], $organisation->id);
            if (!$canJoin['can_join']) {
                $errors[] = $canJoin['reason'];
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Créer ou mettre à jour un adhérent
     */
    protected function createOrUpdateAdherent(Organisation $organisation, array $data, AdherentImport $import): Adherent
    {
        // Rechercher un adhérent existant
        $adherent = Adherent::where('organisation_id', $organisation->id)
            ->where('nip', $data['nip'])
            ->first();
        
        $isNew = !$adherent;
        
        if ($isNew) {
            // Créer un nouvel adhérent
            $data['organisation_id'] = $organisation->id;
            $data['date_adhesion'] = $data['date_adhesion'] ?? now();
            $data['is_active'] = true;
            
            $adherent = Adherent::create($data);
        } else {
            // Mettre à jour l'adhérent existant
            $adherent->update($data);
        }
        
        // Lier à l'import
        DB::table('adherent_import_lines')->insert([
            'adherent_import_id' => $import->id,
            'adherent_id' => $adherent->id,
            'is_new' => $isNew,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return $adherent;
    }
    
    /**
     * Générer un modèle de fichier CSV
     */
    public function generateTemplate(): string
    {
        $csv = Writer::createFromString('');
        $csv->setDelimiter(';');
        
        // En-têtes
        $headers = array_merge($this->requiredColumns, $this->optionalColumns);
        $csv->insertOne($headers);
        
        // Ligne d'exemple
        $example = [
            '123456789',                    // nip
            'DUPONT',                       // nom
            'Jean',                         // prenom
            '1990-05-15',                   // date_naissance
            'Libreville',                   // lieu_naissance
            'M',                            // sexe
            'Gabonaise',                    // nationalite
            'Ingénieur',                    // profession
            '123 Rue de la Paix',           // adresse
            'Estuaire',                     // province
            'Libreville',                   // departement
            '077123456',                    // telephone
            'jean.dupont@email.com',        // email
            'Canton Centre',                // canton
            'Libreville',                   // prefecture
            '',                             // sous_prefecture
            '2024-01-01',                   // date_adhesion
            '',                             // numero_carte (sera généré)
            'non'                           // is_fondateur
        ];
        $csv->insertOne($example);
        
        return $csv->toString();
    }
    
    /**
     * Exporter les adhérents d'une organisation
     */
    public function exportAdherents(Organisation $organisation, array $filters = []): string
    {
        $query = $organisation->adherents();
        
        // Appliquer les filtres
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['is_fondateur'])) {
            $query->where('is_fondateur', $filters['is_fondateur']);
        }
        
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $adherents = $query->get();
        
        // Créer le CSV
        $csv = Writer::createFromString('');
        $csv->setDelimiter(';');
        
        // En-têtes
        $headers = array_merge($this->requiredColumns, $this->optionalColumns, ['statut']);
        $csv->insertOne($headers);
        
        // Données
        foreach ($adherents as $adherent) {
            $row = [
                $adherent->nip,
                $adherent->nom,
                $adherent->prenom,
                $adherent->date_naissance ? $adherent->date_naissance->format('Y-m-d') : '',
                $adherent->lieu_naissance,
                $adherent->sexe,
                $adherent->nationalite,
                $adherent->profession,
                $adherent->adresse,
                $adherent->province,
                $adherent->departement,
                $adherent->telephone,
                $adherent->email,
                $adherent->canton,
                $adherent->prefecture,
                $adherent->sous_prefecture,
                $adherent->date_adhesion ? $adherent->date_adhesion->format('Y-m-d') : '',
                $adherent->numero_carte,
                $adherent->is_fondateur ? 'oui' : 'non',
                $adherent->is_active ? 'actif' : 'inactif'
            ];
            
            $csv->insertOne($row);
        }
        
        return $csv->toString();
    }
    
    /**
     * Obtenir l'historique des imports
     */
    public function getImportHistory(Organisation $organisation): array
    {
        return AdherentImport::where('organisation_id', $organisation->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($import) {
                return [
                    'id' => $import->id,
                    'fichier' => $import->nom_fichier,
                    'utilisateur' => $import->user->name,
                    'date' => $import->created_at->format('d/m/Y H:i'),
                    'statut' => $import->statut,
                    'total' => $import->total_lignes,
                    'importes' => $import->lignes_importees,
                    'erreurs' => $import->lignes_erreur,
                    'taux_reussite' => $import->total_lignes > 0 
                        ? round(($import->lignes_importees / $import->total_lignes) * 100, 1) 
                        : 0
                ];
            })
            ->toArray();
    }
    
    /**
     * Détecter et gérer les doublons
     */
    public function detectDuplicates(Organisation $organisation): array
    {
        $duplicates = [];
        
        // Doublons par NIP dans la même organisation
        $nipDuplicates = DB::table('adherents')
            ->select('nip', DB::raw('COUNT(*) as count'))
            ->where('organisation_id', $organisation->id)
            ->groupBy('nip')
            ->having('count', '>', 1)
            ->get();
        
        foreach ($nipDuplicates as $duplicate) {
            $adherents = Adherent::where('organisation_id', $organisation->id)
                ->where('nip', $duplicate->nip)
                ->get();
            
            $duplicates[] = [
                'type' => 'nip',
                'value' => $duplicate->nip,
                'count' => $duplicate->count,
                'adherents' => $adherents->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'nom_complet' => $a->nom_complet,
                        'date_adhesion' => $a->date_adhesion ? $a->date_adhesion->format('d/m/Y') : 'N/A',
                        'is_active' => $a->is_active
                    ];
                })->toArray()
            ];
        }
        
        return $duplicates;
    }
}