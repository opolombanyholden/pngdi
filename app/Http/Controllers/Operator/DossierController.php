<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\QrCode;
use App\Services\DossierService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\OrganisationValidationService;
use App\Services\AdherentImportService; // ✅ NOUVEAU
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Adherent;
use Exception;

class DossierController extends Controller
{
    protected $dossierService;
    protected $fileUploadService;
    protected $notificationService;
    protected $validationService;
    protected $adherentImportService; // ✅ NOUVEAU
    
    public function __construct(
        DossierService $dossierService,
        FileUploadService $fileUploadService,
        NotificationService $notificationService,
        OrganisationValidationService $validationService,
        AdherentImportService $adherentImportService // ✅ NOUVEAU
    ) {
        $this->dossierService = $dossierService;
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
        $this->validationService = $validationService;
        $this->adherentImportService = $adherentImportService; // ✅ NOUVEAU
    }

    /* *****************************************************
    ** DEBUT DU NOUVEAU CODE
    *********************************************************/

/**
     * ✅ TRAITEMENT STANDARD OPTIMISÉ AVEC SYSTÈME D'ANOMALIES INTÉGRÉ
     */
    private function processStandardOptimized(array $adherentsArray, $organisation, $dossier, Request $request)
    {
        DB::beginTransaction();

        try {
            $inserted = 0;
            $errors = [];
            $anomaliesCreated = [];
            $anomalyCount = 0;

            Log::info('🔄 DÉBUT TRAITEMENT AVEC ANOMALIES', [
                'total_adherents' => count($adherentsArray),
                'organisation_id' => $organisation->id
            ]);

            // ✅ TRAITEMENT PAR LOTS AVEC SYSTÈME D'ANOMALIES
            $chunks = array_chunk($adherentsArray, 100);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                foreach ($chunk as $index => $adherentData) {
                    try {
                        if (!is_array($adherentData)) continue;
                        
                        $lineNumber = ($chunkIndex * 100) + $index + 1;

                        // ✅ UTILISER LE SYSTÈME D'ANOMALIES DE L'AdherentImportService
                        $validationResult = $this->adherentImportService->validateAndDetectAnomalies(
                            $adherentData, 
                            $organisation, 
                            $lineNumber
                        );

                        // ✅ CRÉER L'ADHÉRENT AVEC ANOMALIES (même si anomalies détectées)
                        $adherent = $this->adherentImportService->createAdherentWithAnomalies(
                            $organisation,
                            $validationResult['cleaned_data'],
                            $validationResult['anomalies'],
                            $lineNumber
                        );

                        if ($adherent) {
                            $inserted++;

                            // ✅ ENREGISTRER LES ANOMALIES EN BASE
                            if (!empty($validationResult['anomalies'])) {
                                $this->adherentImportService->createAnomalieRecords(
                                    $adherent, 
                                    $validationResult['anomalies'], 
                                    $organisation->id, 
                                    $lineNumber
                                );
                                $anomalyCount++;
                                $anomaliesCreated = array_merge($anomaliesCreated, $validationResult['anomalies']);
                            }

                            Log::debug("✅ Adhérent créé avec anomalies", [
                                'adherent_id' => $adherent->id,
                                'nip' => $adherent->nip,
                                'anomalies_count' => count($validationResult['anomalies'])
                            ]);
                        }

                    } catch (\Exception $e) {
                        $errors[] = "Ligne $lineNumber: " . $e->getMessage();
                        Log::error("❌ Erreur adhérent ligne $lineNumber", [
                            'error' => $e->getMessage(),
                            'data' => $adherentData
                        ]);
                    }
                }
                
                // ✅ NETTOYAGE MÉMOIRE ENTRE CHUNKS
                if (memory_get_usage() > 1000000000) { // 1GB
                    gc_collect_cycles();
                }
            }

            // ✅ GÉNÉRER LE RAPPORT D'ANOMALIES
            $anomalyReport = $this->adherentImportService->generateAnomalyReport($anomaliesCreated);

            $dossier->update([
                'statut' => 'soumis',
                'donnees_supplementaires' => json_encode([
                    'solution' => 'STANDARD_OPTIMIZED_WITH_ANOMALIES',
                    'total_inserted' => $inserted,
                    'errors_count' => count($errors),
                    'anomalies_count' => $anomalyCount,
                    'anomaly_report' => $anomalyReport,
                    'anomalies_created' => $anomaliesCreated,
                    'processed_at' => now()->toISOString()
                ])
            ]);

            DB::commit();

            Log::info('✅ TRAITEMENT STANDARD OPTIMISÉ TERMINÉ AVEC ANOMALIES', [
                'inserted' => $inserted,
                'errors_count' => count($errors),
                'anomalies_count' => $anomalyCount,
                'anomaly_report' => $anomalyReport
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Adhérents traités avec succès avec détection d\'anomalies',
                'data' => [
                    'total_inserted' => $inserted,
                    'errors' => $errors,
                    'anomalies_count' => $anomalyCount,
                    'anomaly_report' => $anomalyReport
                ],
                'redirect_url' => route('operator.dossiers.confirmation', $dossier->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ ERREUR TRAITEMENT AVEC ANOMALIES', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ TRAITEMENT CHUNKING ULTRA-OPTIMISÉ AVEC ANOMALIES POUR 50K
     */
    private function processWithOptimizedChunking(array $adherentsArray, $organisation, $dossier, Request $request)
    {
        try {
            $chunkSize = 250;
            $chunks = array_chunk($adherentsArray, $chunkSize);
            $totalChunks = count($chunks);
            
            $totalInserted = 0;
            $allErrors = [];
            $totalAnomaliesCount = 0;
            $allAnomaliesCreated = [];

            Log::info('🔄 DÉBUT CHUNKING ULTRA-OPTIMISÉ AVEC ANOMALIES', [
                'total_adherents' => count($adherentsArray),
                'total_chunks' => $totalChunks,
                'chunk_size' => $chunkSize
            ]);

            DB::beginTransaction();

            foreach ($chunks as $chunkIndex => $chunk) {
                $chunkStartTime = microtime(true);
                
                $chunkInserted = 0;
                $chunkAnomalies = 0;
                
                foreach ($chunk as $index => $adherentData) {
                    try {
                        if (!is_array($adherentData)) continue;
                        
                        $lineNumber = ($chunkIndex * $chunkSize) + $index + 1;

                        // ✅ SYSTÈME D'ANOMALIES POUR GROS VOLUMES
                        $validationResult = $this->adherentImportService->validateAndDetectAnomalies(
                            $adherentData, 
                            $organisation, 
                            $lineNumber
                        );

                        $adherent = $this->adherentImportService->createAdherentWithAnomalies(
                            $organisation,
                            $validationResult['cleaned_data'],
                            $validationResult['anomalies'],
                            $lineNumber
                        );

                        if ($adherent) {
                            $chunkInserted++;

                            if (!empty($validationResult['anomalies'])) {
                                $this->adherentImportService->createAnomalieRecords(
                                    $adherent, 
                                    $validationResult['anomalies'], 
                                    $organisation->id, 
                                    $lineNumber
                                );
                                $chunkAnomalies++;
                                $allAnomaliesCreated = array_merge($allAnomaliesCreated, $validationResult['anomalies']);
                            }
                        }

                    } catch (\Exception $e) {
                        $allErrors[] = "Chunk $chunkIndex, ligne $lineNumber: " . $e->getMessage();
                    }
                }

                $totalInserted += $chunkInserted;
                $totalAnomaliesCount += $chunkAnomalies;
                
                $chunkTime = round((microtime(true) - $chunkStartTime) * 1000, 2);
                
                Log::info("✅ CHUNK ULTRA-OPTIMISÉ AVEC ANOMALIES $chunkIndex/$totalChunks", [
                    'inserted' => $chunkInserted,
                    'anomalies' => $chunkAnomalies,
                    'total_so_far' => $totalInserted,
                    'chunk_time_ms' => $chunkTime
                ]);

                // ✅ NETTOYAGE MÉMOIRE POUR 50K
                if ($chunkIndex % 10 === 0) {
                    gc_collect_cycles();
                }

                if ($totalChunks > 100) {
                    usleep(250000); // 0.25 seconde
                }
            }

            DB::commit();

            // ✅ RAPPORT FINAL D'ANOMALIES
            $finalAnomalyReport = $this->adherentImportService->generateAnomalyReport($allAnomaliesCreated);

            $dossier->update([
                'statut' => 'soumis',
                'donnees_supplementaires' => json_encode([
                    'solution' => 'ULTRA_OPTIMIZED_CHUNKING_WITH_ANOMALIES',
                    'chunks_processed' => $totalChunks,
                    'total_inserted' => $totalInserted,
                    'errors_count' => count($allErrors),
                    'anomalies_count' => $totalAnomaliesCount,
                    'anomaly_report' => $finalAnomalyReport,
                    'processed_at' => now()->toISOString(),
                    'performance_optimized' => true
                ])
            ]);

            Log::info('🎉 CHUNKING ULTRA-OPTIMISÉ AVEC ANOMALIES TERMINÉ', [
                'total_inserted' => $totalInserted,
                'total_anomalies' => $totalAnomaliesCount,
                'chunks_processed' => $totalChunks
            ]);

            return response()->json([
                'success' => true,
                'message' => "Adhérents traités avec succès par chunking ultra-optimisé avec détection d'anomalies",
                'data' => [
                    'total_inserted' => $totalInserted,
                    'chunks_processed' => $totalChunks,
                    'errors' => $allErrors,
                    'anomalies_count' => $totalAnomaliesCount,
                    'anomaly_report' => $finalAnomalyReport,
                    'solution' => 'ULTRA_OPTIMIZED_CHUNKING_WITH_ANOMALIES'
                ],
                'redirect_url' => route('operator.dossiers.confirmation', $dossier->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('❌ ERREUR CHUNKING ULTRA-OPTIMISÉ AVEC ANOMALIES', [
                'error' => $e->getMessage(),
                'dossier_id' => $dossier->id
            ]);

            throw $e;
        }
    }

    // ========================================================================
    // 🔧 CALCUL OPTIMISÉ DES STATISTIQUES AVEC ANOMALIES
    // ========================================================================

    /**
     * ✅ CALCUL OPTIMISÉ DES STATISTIQUES AVEC ANOMALIES RÉELLES
     */
    private function calculateAdherentsStatsOptimized($organisation)
    {
        $stats = [
            'total' => 0,
            'valides' => 0,
            'anomalies_critiques' => 0,
            'anomalies_majeures' => 0,
            'anomalies_mineures' => 0,
            'anomalies_total' => 0
        ];

        if (!$organisation) {
            return $stats;
        }

        try {
            // ✅ TOTAL ADHÉRENTS
            $stats['total'] = $organisation->adherents()->count();
            
            if ($stats['total'] > 10000) {
                Log::info('🔍 ÉCHANTILLONNAGE ACTIVÉ POUR GROS VOLUME', [
                    'total_adherents' => $stats['total']
                ]);
                
                // Échantillon pour estimation
                $sample = $organisation->adherents()
                    ->with('anomalies')
                    ->limit(1000)
                    ->get();
                
                $sample_stats = $this->analyzeAnomaliesSampleFromDB($sample);
                
                // Extrapolation
                $ratio = $stats['total'] / 1000;
                $stats['anomalies_critiques'] = round($sample_stats['critiques'] * $ratio);
                $stats['anomalies_majeures'] = round($sample_stats['majeures'] * $ratio);
                $stats['anomalies_mineures'] = round($sample_stats['mineures'] * $ratio);
                
            } else {
                // ✅ CALCUL DIRECT AVEC LA TABLE adherent_anomalies
                $stats['anomalies_critiques'] = \App\Models\AdherentAnomalie::where('organisation_id', $organisation->id)
                    ->where('type_anomalie', 'critique')
                    ->count();
                
                $stats['anomalies_majeures'] = \App\Models\AdherentAnomalie::where('organisation_id', $organisation->id)
                    ->where('type_anomalie', 'majeure')
                    ->count();
                
                $stats['anomalies_mineures'] = \App\Models\AdherentAnomalie::where('organisation_id', $organisation->id)
                    ->where('type_anomalie', 'mineure')
                    ->count();
            }

            $stats['anomalies_total'] = $stats['anomalies_critiques'] + $stats['anomalies_majeures'] + $stats['anomalies_mineures'];
            $stats['valides'] = $stats['total'] - $stats['anomalies_total'];

        } catch (\Exception $e) {
            Log::error('❌ ERREUR CALCUL STATS AVEC ANOMALIES', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);
            
            // Valeurs par défaut
            $stats['valides'] = $stats['total'];
        }

        return $stats;
    }

    /**
     * ✅ ANALYSE ÉCHANTILLON DEPUIS LA TABLE adherent_anomalies
     */
    private function analyzeAnomaliesSampleFromDB($sample)
    {
        $stats = ['critiques' => 0, 'majeures' => 0, 'mineures' => 0];
        
        foreach ($sample as $adherent) {
            $anomaliesCritiques = $adherent->anomalies()->where('type_anomalie', 'critique')->count();
            $anomaliesMajeures = $adherent->anomalies()->where('type_anomalie', 'majeure')->count();
            $anomaliesMineures = $adherent->anomalies()->where('type_anomalie', 'mineure')->count();
            
            if ($anomaliesCritiques > 0) $stats['critiques']++;
            elseif ($anomaliesMajeures > 0) $stats['majeures']++;
            elseif ($anomaliesMineures > 0) $stats['mineures']++;
        }
        
        return $stats;
    }

    // ========================================================================
    // TOUTES LES AUTRES MÉTHODES RESTENT IDENTIQUES
    // ========================================================================

    /**
     * Afficher la liste des dossiers
     */
    public function index(Request $request)
    {
        $query = Dossier::whereHas('organisation', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['organisation', 'currentStep']);
        
        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        if ($request->has('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }
        
        if ($request->has('type_operation')) {
            $query->where('type_operation', $request->type_operation);
        }
        
        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_dossier', 'like', "%{$search}%")
                    ->orWhereHas('organisation', function ($q2) use ($search) {
                        $q2->where('nom', 'like', "%{$search}%");
                    });
            });
        }
        
        $dossiers = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Organisations pour le filtre
        $organisations = Organisation::where('user_id', Auth::id())
            ->orderBy('nom')
            ->get();
        
        return view('operator.dossiers.index', compact('dossiers', 'organisations'));
    }



    /* *****************************************************
    ** FIN DU NOUVEAU CODE
    *********************************************************/
    
    /**
     * Afficher la liste des dossiers
     */


    /**
     * Afficher le formulaire de création selon le type
     */
    public function create($type)
    {
        // Mapper les types courts vers les types complets
        $typeMapping = [
            'association' => Organisation::TYPE_ASSOCIATION,
            'ong' => Organisation::TYPE_ONG,
            'parti' => Organisation::TYPE_PARTI,
            'confession' => Organisation::TYPE_CONFESSION
        ];
        
        if (!isset($typeMapping[$type])) {
            abort(404, 'Type d\'organisation non reconnu');
        }
        
        $fullType = $typeMapping[$type];
        
        // Vérifier les limites de création
        $limits = $this->checkOrganisationLimits(Auth::user(), $fullType);
        
        if (!$limits['can_create']) {
            return redirect()->route('operator.dashboard')
                ->with('error', $limits['message']);
        }
        
        // Directement afficher le formulaire de création au lieu de rediriger vers un guide
        $provinces = $this->getProvinces();
        $guides = $this->getGuideContent($fullType);
        $documentTypes = $this->getRequiredDocuments($fullType);
        
        return view('operator.dossiers.create', compact('type', 'fullType', 'provinces', 'guides', 'documentTypes'));
    }

    /**
     * ✅ MÉTHODE TEMPLATE EXCEL ADHÉRENTS
     * Résout: Route [operator.templates.adherents-excel] not defined
     */
    public function downloadTemplate()
    {
        try {
            Log::info("=== TÉLÉCHARGEMENT TEMPLATE ADHÉRENTS ===", [
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            // Créer le contenu CSV du template
            $csvContent = "NIP,Nom,Prénom,Téléphone,Profession,Adresse\n";
            $csvContent .= "A1-0001-19801225,MOUNDOUNGA,Jean,+24101234567,Ingénieur,Libreville\n";
            $csvContent .= "B2-0002-19751110,OBAME,Marie,+24101234568,Professeur,Port-Gentil\n";
            $csvContent .= "C3-0003-19900315,NGUEMA,Paul,+24101234569,Médecin,Franceville\n";

            $fileName = 'template_adherents_' . date('Y-m-d') . '.csv';

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error("=== ERREUR TÉLÉCHARGEMENT TEMPLATE ===", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors du téléchargement du template : ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer un nouveau dossier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'type_operation' => 'required|in:creation,modification,cessation,declaration'
        ]);
        
        // Vérifier que l'organisation appartient à l'utilisateur
        $organisation = Organisation::where('id', $validated['organisation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        try {
            // Créer le dossier via le service
            $dossier = $this->dossierService->createDossier([
                'organisation_id' => $organisation->id,
                'type_operation' => $validated['type_operation']
            ]);
            
            return redirect()->route('operator.dossiers.edit', $dossier->id)
                ->with('success', 'Dossier créé avec succès. Complétez les informations requises.');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un dossier spécifique
     */
    public function show($dossier)
    {
        $dossier = Dossier::with([
            'organisation',
            'documents.documentType',
            'currentStep.validationEntity',
            'validations.validatedBy',
            'comments.user'
        ])->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Obtenir le statut détaillé
        $status = $this->dossierService->getDossierStatus($dossier);
        
        return view('operator.dossiers.show', compact('dossier', 'status'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($dossier)
    {
        $dossier = Dossier::with(['organisation', 'documents'])->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$dossier->canBeModified()) {
            return redirect()->route('operator.dossiers.show', $dossier->id)
                ->with('error', 'Ce dossier ne peut plus être modifié');
        }
        
        // Documents requis
        $requiredDocuments = DocumentType::where('type_organisation', $dossier->organisation->type)
            ->where(function ($query) use ($dossier) {
                $query->where('type_operation', $dossier->type_operation)
                    ->orWhereNull('type_operation');
            })
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();
        
        return view('operator.dossiers.edit', compact('dossier', 'requiredDocuments'));
    }

    /**
     * Mettre à jour un dossier
     */
    public function update(Request $request, $dossier)
    {
        $dossier = Dossier::findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Sauvegarder les métadonnées
        $dossier->update([
            'metadata' => array_merge($dossier->metadata ?? [], [
                'last_updated' => now()->toDateTimeString(),
                'form_data' => $request->except(['_token', '_method'])
            ])
        ]);
        
        return redirect()->route('operator.dossiers.edit', $dossier->id)
            ->with('success', 'Modifications enregistrées');
    }

    /**
     * Soumettre un dossier
     */
    public function soumettre($dossier)
    {
        $dossier = Dossier::with('organisation')->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        try {
            // Valider l'organisation
            $validation = $this->validationService->validateBeforeSubmission($dossier->organisation);
            
            if (!$validation['is_valid']) {
                return redirect()->back()
                    ->with('error', 'Validation échouée')
                    ->with('validation_errors', $validation['errors']);
            }
            
            // Soumettre le dossier
            $this->dossierService->submitDossier($dossier);
            
            return redirect()->route('operator.dossiers.show', $dossier->id)
                ->with('success', 'Dossier soumis avec succès');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la soumission : ' . $e->getMessage());
        }
    }

    /**
     * Uploader un document
     */
    public function uploadDocument(Request $request, $dossier)
    {
        $dossier = Dossier::findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'document' => 'required|file|max:10240', // 10MB max
            'document_type_id' => 'required|exists:document_types,id'
        ]);
        
        try {
            // Ajouter le document via le service
            $document = $this->dossierService->addDocument(
                $dossier,
                $request->document_type_id,
                $request->file('document')
            );
            
            return redirect()->back()
                ->with('success', 'Document téléchargé avec succès');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du téléchargement : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument($dossier, $document)
    {
        $dossier = Dossier::findOrFail($dossier);
        $document = Document::findOrFail($document);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id() || $document->dossier_id !== $dossier->id) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$dossier->canBeModified()) {
            return redirect()->back()
                ->with('error', 'Ce dossier ne peut plus être modifié');
        }
        
        // Supprimer le fichier physique
        if ($document->chemin_fichier && Storage::exists($document->chemin_fichier)) {
            Storage::delete($document->chemin_fichier);
        }
        
        // Supprimer l'enregistrement
        $document->delete();
        
        return redirect()->back()
            ->with('success', 'Document supprimé');
    }

    /**
     * Télécharger un document
     */
    public function downloadDocument($document)
    {
        $document = Document::with(['dossier.organisation'])->findOrFail($document);
        
        // Vérifier l'accès
        if ($document->dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le fichier existe
        if (!$document->fileExists()) {
            return redirect()->back()
                ->with('error', 'Fichier introuvable');
        }
        
        return Storage::download($document->chemin_fichier, $document->nom_original);
    }

    /**
     * ✅ MÉTHODE CONFIRMATION OPTIMISÉE POUR 50K ADHÉRENTS
     * Correction complète avec gestion optimisée des gros volumes
     */
    public function confirmation(Request $request, $dossier)
    {
        try {
            Log::info("=== DÉBUT CONFIRMATION OPTIMISÉE 50K ===", [
                'dossier_param' => $dossier,
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            // ✅ CORRECTION : Gestion ID ou objet
            if (is_object($dossier) && is_a($dossier, 'App\Models\Dossier')) {
                $dossierObj = $dossier;
            } else {
                $dossierId = is_numeric($dossier) ? (int)$dossier : $dossier;
                
                // ✅ REQUÊTE OPTIMISÉE AVEC whereHas
                $dossierObj = Dossier::with([
                    'organisation',
                    'documents'
                ])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

                if (!$dossierObj) {
                    Log::error("=== DOSSIER NON TROUVÉ ===", [
                        'dossier_id' => $dossierId,
                        'user_id' => auth()->id()
                    ]);
                    
                    return redirect()->route('operator.dashboard')
                        ->with('error', 'Dossier non trouvé ou accès non autorisé.');
                }
            }

            // ✅ CALCUL OPTIMISÉ DES STATISTIQUES POUR GROS VOLUMES
            $adherents_stats = $this->calculateAdherentsStatsOptimized($dossierObj->organisation);
            
            // ✅ QR CODE AVEC GESTION D'ERREUR
            $qrCode = $this->getQrCodeForDossier($dossierObj);
            
            // ✅ ACCUSÉ DE RÉCEPTION OPTIMISÉ
            $accuseReceptionUrl = $this->getAccuseReceptionDownloadUrl($dossierObj);
            
            // ✅ DONNÉES DE CONFIRMATION OPTIMISÉES
            $confirmationData = [
                'organisation' => $dossierObj->organisation,
                'dossier' => $dossierObj,
                'numero_recepisse' => $dossierObj->organisation->numero_recepisse ?? 'Non attribué',
                'numero_dossier' => $dossierObj->numero_dossier ?? 'Non attribué',
                'qr_code' => $qrCode,
                'adherents_stats' => $adherents_stats,
                'anomalies' => $this->getAnomaliesFromDossier($dossierObj),
                'accuse_reception_url' => $accuseReceptionUrl,
                'delai_traitement' => '72 heures ouvrées',
                'message_legal' => $this->getMessageLegal(),
                'prochaines_etapes' => $this->getProchainesEtapes(),
                'contact_support' => $this->getContactSupport(),
                'submitted_at' => $dossierObj->submitted_at ?? $dossierObj->created_at ?? now(),
                'estimated_completion' => $this->calculateEstimatedCompletion($dossierObj)
            ];

            // Nettoyer la session
            session()->forget('success_data');

            Log::info('✅ PAGE CONFIRMATION CHARGÉE AVEC SUCCÈS', [
                'dossier_id' => $dossierObj->id,
                'adherents_total' => $adherents_stats['total'],
                'performance_optimized' => true
            ]);

            return view('operator.dossiers.confirmation', compact('confirmationData'));

        } catch (\Exception $e) {
            Log::error('❌ ERREUR CONFIRMATION OPTIMISÉE', [
                'dossier_param' => $dossier,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return redirect()->route('operator.dashboard')
                ->with('error', 'Erreur lors de l\'affichage de la confirmation: ' . $e->getMessage());
        }
    }

    /**
     * ✅ CALCUL OPTIMISÉ DES STATISTIQUES - GESTION 50K ADHÉRENTS
     */


    /**
     * ✅ ANALYSE ÉCHANTILLON POUR ESTIMATION GROS VOLUMES
     */
    private function analyzeAnomaliesSample($sample)
    {
        $stats = ['critiques' => 0, 'majeures' => 0, 'mineures' => 0];
        
        foreach ($sample as $adherent) {
            if (!$adherent->is_active) continue;
            
            $anomalies = json_decode($adherent->anomalies ?? '[]', true) ?: [];
            
            if (empty($anomalies)) continue;
            
            // Classification simple pour l'estimation
            if (count($anomalies) > 5) {
                $stats['critiques']++;
            } elseif (count($anomalies) > 2) {
                $stats['majeures']++;
            } else {
                $stats['mineures']++;
            }
        }
        
        return $stats;
    }

    /**
     * ✅ FINALISATION "PLUS TARD" - OPTIMISÉE
     */
    public function finalizeLater(Request $request, $dossierId)
    {
        try {
            Log::info('💾 DÉBUT finalizeLater OPTIMISÉE', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            $cleanDossierId = (int) $dossierId;
            if ($cleanDossierId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiant de dossier invalide'
                ], 400);
            }

            // ✅ REQUÊTE OPTIMISÉE
            $dossier = Dossier::with('organisation')
                             ->where('id', $cleanDossierId)
                             ->whereHas('organisation', function($query) {
                                 $query->where('user_id', auth()->id());
                             })
                             ->first();

            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé ou accès non autorisé'
                ], 404);
            }

            // ✅ MISE À JOUR OPTIMISÉE
            $updateData = [
                'statut' => 'brouillon_phase2_complete',
                'updated_at' => now()
            ];

            // ✅ MÉTADONNÉES DANS JSON EXISTANT
            $donneesSupplementaires = $this->getExistingDonneesSupplementaires($dossier);
            $donneesSupplementaires['finalisation_later'] = [
                'finalized_at' => now()->toISOString(),
                'finalized_by' => auth()->id(),
                'status' => 'saved_for_later_submission',
                'adherents_count' => $dossier->organisation->adherents()->count(),
                'correction_applied' => 'optimized_for_50k'
            ];

            $updateData['donnees_supplementaires'] = json_encode($donneesSupplementaires);

            $updated = $dossier->update($updateData);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la sauvegarde'
                ], 500);
            }

            Log::info('✅ Finalisation LATER réussie', [
                'dossier_id' => $cleanDossierId,
                'statut' => $updateData['statut'],
                'adherents_count' => $dossier->organisation->adherents()->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dossier sauvegardé avec succès. Vous pourrez le soumettre plus tard.',
                'redirect_url' => route('operator.dossiers.index'),
                'dossier' => [
                    'id' => $dossier->id,
                    'numero_dossier' => $dossier->numero_dossier,
                    'statut' => $updateData['statut'],
                    'adherents_count' => $dossier->organisation->adherents()->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR finalizeLater OPTIMISÉE', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur système lors de la sauvegarde. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * ✅ FINALISATION "MAINTENANT" - OPTIMISÉE
     */
    public function finalizeNow(Request $request, $dossierId)
    {
        try {
            Log::info('🚀 DÉBUT finalizeNow OPTIMISÉE', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            $cleanDossierId = (int) $dossierId;
            if ($cleanDossierId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiant de dossier invalide'
                ], 400);
            }

            // ✅ REQUÊTE OPTIMISÉE
            $dossier = Dossier::with('organisation')
                             ->where('id', $cleanDossierId)
                             ->whereHas('organisation', function($query) {
                                 $query->where('user_id', auth()->id());
                             })
                             ->first();

            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé ou accès non autorisé'
                ], 404);
            }

            // ✅ MISE À JOUR OPTIMISÉE
            $updateData = [
                'statut' => 'soumis',
                'submitted_at' => now(),
                'updated_at' => now()
            ];

            // ✅ MÉTADONNÉES OPTIMISÉES
            $donneesSupplementaires = $this->getExistingDonneesSupplementaires($dossier);
            $donneesSupplementaires['finalisation_now'] = [
                'submitted_at' => now()->toISOString(),
                'submitted_by' => auth()->id(),
                'status' => 'submitted_immediately',
                'adherents_count' => $dossier->organisation->adherents()->count(),
                'qr_code_required' => true,
                'correction_applied' => 'optimized_for_50k'
            ];

            $updateData['donnees_supplementaires'] = json_encode($donneesSupplementaires);

            $updated = $dossier->update($updateData);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la soumission'
                ], 500);
            }

            Log::info('✅ Finalisation NOW réussie', [
                'dossier_id' => $cleanDossierId,
                'statut' => $updateData['statut'],
                'adherents_count' => $dossier->organisation->adherents()->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dossier finalisé et soumis avec succès. Un accusé de réception sera généré.',
                'redirect_url' => route('operator.dossiers.confirmation', $cleanDossierId),
                'dossier' => [
                    'id' => $dossier->id,
                    'numero_dossier' => $dossier->numero_dossier,
                    'statut' => $updateData['statut'],
                    'submitted_at' => $updateData['submitted_at']->format('d/m/Y H:i'),
                    'adherents_count' => $dossier->organisation->adherents()->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR finalizeNow OPTIMISÉE', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur système lors de la soumission. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * ✅ STORE ADHÉRENTS PHASE 2 - OPTIMISÉ 50K
     */
    public function storeAdherentsPhase2(Request $request, $dossierId)
    {
        try {
            Log::info('🚀 DÉBUT storeAdherentsPhase2 OPTIMISÉ 50K', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);

            // ✅ CONFIGURATION OPTIMISÉE POUR 50K
            @set_time_limit(900); // 15 minutes
            @ini_set('memory_limit', '2048M'); // 2GB

            $dossier = Dossier::with('organisation')
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé'
                ], 404);
            }

            // ✅ VÉRIFICATION VOLUME EXISTANT
            $adherentsExistants = Adherent::where('organisation_id', $dossier->organisation->id)->count();

            if ($adherentsExistants > 0) {
                Log::info('⚠️ ADHÉRENTS DÉJÀ EXISTANTS - FINALISATION DIRECTE', [
                    'organisation_id' => $dossier->organisation->id,
                    'count' => $adherentsExistants
                ]);
                
                $dossier->update([
                    'statut' => 'soumis',
                    'donnees_supplementaires' => json_encode([
                        'solution' => 'EXISTING_DATA_FINALIZED',
                        'total_existing' => $adherentsExistants,
                        'processed_at' => now()->toISOString()
                    ])
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Dossier finalisé avec succès',
                    'data' => [
                        'total_existing' => $adherentsExistants,
                        'solution' => 'EXISTING_DATA'
                    ],
                    'redirect_url' => route('operator.dossiers.confirmation', $dossier->id)
                ]);
            }

            // ✅ TRAITEMENT NOUVEAU VOLUME
            $adherentsData = $request->input('adherents');
            
            if (is_string($adherentsData)) {
                $adherentsArray = json_decode($adherentsData, true) ?: [];
            } else {
                $adherentsArray = is_array($adherentsData) ? $adherentsData : [];
            }

            if (empty($adherentsArray)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée d\'adhérents fournie'
                ], 422);
            }

            $totalAdherents = count($adherentsArray);
            
            // ✅ DÉCISION INTELLIGENTE SELON VOLUME
            if ($totalAdherents >= 1000) {
                Log::info('🔄 ACTIVATION TRAITEMENT CHUNKING OPTIMISÉ', [
                    'total_adherents' => $totalAdherents,
                    'chunks_estimated' => ceil($totalAdherents / 500)
                ]);
                
                return $this->processWithOptimizedChunking($adherentsArray, $dossier->organisation, $dossier, $request);
            } else {
                return $this->processStandardOptimized($adherentsArray, $dossier->organisation, $dossier, $request);
            }

        } catch (\Exception $e) {
            Log::error('❌ ERREUR storeAdherentsPhase2 OPTIMISÉ', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

 

    /**
     * Page d'import des adhérents - Phase 2
     */
    public function adherentsImportPage($dossierId)
    {
        try {
            $dossier = Dossier::with(['organisation', 'adherents'])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->firstOrFail();

            $organisation = $dossier->organisation;
            $organisationType = $organisation->type ?? 'association';

            $adherents_stats = [
                'existants' => $dossier->adherents()->count(),
                'minimum_requis' => $this->getMinimumAdherentsRequired($organisationType),
                'manquants' => 0,
                'peut_soumettre' => false
            ];
            
            $adherents_stats['manquants'] = max(0, $adherents_stats['minimum_requis'] - $adherents_stats['existants']);
            $adherents_stats['peut_soumettre'] = $adherents_stats['manquants'] <= 0;

            $upload_config = [
                'max_file_size' => '50MB',
                'chunk_size' => 250,
                'max_adherents' => 100000,
                'chunking_threshold' => 1000
            ];

            $urls = [
                'store_adherents' => route('operator.dossiers.store-adherents', $dossier->id),
                'template_download' => route('operator.templates.adherents-excel'),
                'confirmation' => route('operator.dossiers.confirmation', $dossier->id)
            ];

            return view('operator.dossiers.adherents-import', compact(
                'dossier', 'organisation', 'adherents_stats', 'upload_config', 'urls'
            ));

        } catch (\Exception $e) {
            Log::error('❌ ERREUR adherentsImportPage', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('operator.dashboard')
                ->with('error', 'Erreur lors du chargement de la page d\'import');
        }
    }

    // ========================================================================
    // MÉTHODES AUXILIAIRES OPTIMISÉES
    // ========================================================================

    /**
     * ✅ RÉCUPÉRATION SÉCURISÉE DES DONNÉES JSON
     */
    private function getExistingDonneesSupplementaires($dossier)
    {
        $donneesSupplementaires = [];
        
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $decoded = json_decode($dossier->donnees_supplementaires, true);
                $donneesSupplementaires = $decoded && is_array($decoded) ? $decoded : [];
            } elseif (is_array($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = $dossier->donnees_supplementaires;
            }
        }
        
        return $donneesSupplementaires;
    }

    /**
     * ✅ QR CODE OPTIMISÉ
     */
    private function getQrCodeForDossier(Dossier $dossier)
    {
        try {
            return QrCode::where('verifiable_type', 'App\\Models\\Dossier')
                ->where('verifiable_id', $dossier->id)
                ->where('is_active', 1)
                ->orderBy('created_at', 'desc')
                ->first();
        } catch (\Exception $e) {
            Log::error('❌ ERREUR QR CODE', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ ACCUSÉ DE RÉCEPTION OPTIMISÉ
     */
    private function getAccuseReceptionDownloadUrl(Dossier $dossier)
    {
        try {
            $accuseDocument = $dossier->documents()
                ->where(function($query) {
                    $query->where('nom_fichier', 'LIKE', 'accuse_reception_%')
                          ->orWhere('nom_fichier', 'LIKE', 'accuse_phase1_%');
                })
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($accuseDocument && $accuseDocument->chemin_fichier) {
                return route('operator.dossiers.download-accuse', ['path' => basename($accuseDocument->chemin_fichier)]);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('❌ ERREUR URL ACCUSÉ', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ VALIDATION ADHÉRENT OPTIMISÉE
     */
    private function validateAdherentData(array $data)
    {
        return [
            'nip' => strtoupper(trim($data['nip'] ?? '')),
            'nom' => trim($data['nom'] ?? ''),
            'prenom' => trim($data['prenom'] ?? ''),
            'profession' => trim($data['profession'] ?? ''),
            'fonction' => trim($data['fonction'] ?? 'Membre'),
            'telephone' => preg_replace('/[^0-9+]/', '', $data['telephone'] ?? ''),
            'email' => isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? $data['email'] : null
        ];
    }

    /**
     * ✅ ANOMALIES DEPUIS DOSSIER
     */
    private function getAnomaliesFromDossier(Dossier $dossier)
    {
        $donneesSupplementaires = $this->getExistingDonneesSupplementaires($dossier);
        return $donneesSupplementaires['adherents_anomalies'] ?? [];
    }

    /**
     * ✅ MESSAGE LÉGAL
     */
    private function getMessageLegal()
    {
        return 'Votre dossier numérique a été soumis avec succès. Conformément aux dispositions légales en vigueur, vous devez déposer votre dossier physique en 3 exemplaires auprès de la Direction Générale des Élections et des Libertés Publiques.';
    }

    /**
     * ✅ PROCHAINES ÉTAPES
     */
    private function getProchainesEtapes()
    {
        return [
            [
                'numero' => 1,
                'titre' => 'Assignation d\'un agent',
                'description' => 'Un agent sera assigné à votre dossier sous 48h ouvrées',
                'delai' => '48h ouvrées'
            ],
            [
                'numero' => 2,
                'titre' => 'Examen du dossier',
                'description' => 'Votre dossier sera examiné selon l\'ordre d\'arrivée',
                'delai' => '72h ouvrées'
            ],
            [
                'numero' => 3,
                'titre' => 'Notification du résultat',
                'description' => 'Vous recevrez une notification par email',
                'delai' => 'Variable'
            ],
            [
                'numero' => 4,
                'titre' => 'Dépôt physique requis',
                'description' => 'Déposer le dossier physique en 3 exemplaires à la DGELP',
                'delai' => 'Dans les 7 jours'
            ]
        ];
    }

    /**
     * ✅ CONTACT SUPPORT
     */
    private function getContactSupport()
    {
        return [
            'email' => 'support@pngdi.ga',
            'telephone' => '+241 01 23 45 67',
            'horaires' => 'Lundi - Vendredi: 08h00 - 17h00'
        ];
    }

    /**
     * ✅ CALCUL ESTIMATION COMPLETION
     */
    private function calculateEstimatedCompletion(Dossier $dossier)
    {
        $baseHours = 72;
        
        switch ($dossier->organisation->type) {
            case 'parti_politique':
                $baseHours += 24;
                break;
            case 'confession_religieuse':
                $baseHours += 12;
                break;
        }
        
        $nombreAdherents = $dossier->organisation->adherents()->count();
        if ($nombreAdherents > 10000) {
            $baseHours += 24;
        } elseif ($nombreAdherents > 1000) {
            $baseHours += 12;
        }
        
        return now()->addHours($baseHours);
    }

    /**
     * Obtenir le minimum d'adhérents requis selon le type d'organisation
     */
    private function getMinimumAdherentsRequired($organisationType)
    {
        $minimums = [
            'association' => 10,
            'ong' => 15,
            'parti_politique' => 500,
            'confession_religieuse' => 20
        ];
        
        return $minimums[$organisationType] ?? 10;
    }
    
    /**
     * Vérifier les limites de création d'organisation
     */
    protected function checkOrganisationLimits($user, $type)
    {
        if ($type === Organisation::TYPE_PARTI) {
            $hasActiveParti = Organisation::where('user_id', $user->id)
                ->where('type', Organisation::TYPE_PARTI)
                ->where('is_active', true)
                ->exists();
            
            if ($hasActiveParti) {
                return [
                    'can_create' => false,
                    'message' => 'Vous avez déjà un parti politique actif.'
                ];
            }
        }
        
        return ['can_create' => true, 'message' => ''];
    }

    /**
     * Obtenir le contenu du guide pour un type d'organisation
     */
    private function getGuideContent($type)
    {
        $guides = [
            Organisation::TYPE_ASSOCIATION => [
                'title' => 'Création d\'une association',
                'description' => 'Formalités pour créer une association au Gabon',
                'requirements' => [
                    'Minimum 7 membres fondateurs',
                    'Objet social déterminé',
                    'Siège social au Gabon'
                ]
            ]
        ];

        return $guides[$type] ?? $guides[Organisation::TYPE_ASSOCIATION];
    }

    /**
     * Obtenir les documents requis selon le type d'organisation
     */
    private function getRequiredDocuments($type)
    {
        return [
            'statuts' => ['name' => 'Statuts', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true]
        ];
    }

    /**
     * Obtenir la liste des provinces du Gabon
     */
    private function getProvinces(): array
    {
        return [
            'Estuaire' => 'Estuaire',
            'Haut-Ogooué' => 'Haut-Ogooué',
            'Moyen-Ogooué' => 'Moyen-Ogooué',
            'Ngounié' => 'Ngounié',
            'Nyanga' => 'Nyanga',
            'Ogooué-Ivindo' => 'Ogooué-Ivindo',
            'Ogooué-Lolo' => 'Ogooué-Lolo',
            'Ogooué-Maritime' => 'Ogooué-Maritime',
            'Woleu-Ntem' => 'Woleu-Ntem'
        ];
    }

    // DÉLIMITEUR DÉBUT : MÉTHODE RAPPORT ANOMALIES
    /**
     * Afficher la page des anomalies pour un dossier
    */
    // DÉLIMITEUR DÉBUT : MÉTHODE RAPPORT ANOMALIES
    /**
     * Afficher la page des anomalies pour un dossier
     */
    public function rapportAnomalies(Dossier $dossier)
    {
        try {
            // Vérifier l'accès
            if ($dossier->organisation->user_id !== Auth::id()) {
                abort(403);
            }

            // Récupérer les adhérents avec anomalies pour cette organisation
            $anomalies = Adherent::where('organisation_id', $dossier->organisation->id)
                ->where('has_anomalies', true)
                ->with(['organisation'])
                ->paginate(20);

            // ✅ CORRECTION : Traiter les données d'anomalies avec vérification de type
            foreach ($anomalies as $adherent) {
                if ($adherent->anomalies_data) {
                    // Vérifier si c'est une string JSON ou déjà un array
                    if (is_string($adherent->anomalies_data)) {
                        $decoded = json_decode($adherent->anomalies_data, true);
                        $adherent->anomalies_data = $decoded ?: [];
                    } elseif (!is_array($adherent->anomalies_data)) {
                        $adherent->anomalies_data = [];
                    }
                } else {
                    $adherent->anomalies_data = [];
                }
            }

            // Récupérer toutes les organisations de l'utilisateur pour le filtre
            $organisations = Organisation::where('user_id', Auth::id())
                ->orderBy('nom')
                ->get();

            // ✅ CORRECTION : Statistiques optimisées
            $stats = [
                'total' => $anomalies->total(),
                'critiques' => Adherent::where('organisation_id', $dossier->organisation->id)
                    ->where('anomalies_severity', 'critique')
                    ->count(),
                'majeures' => Adherent::where('organisation_id', $dossier->organisation->id)
                    ->where('anomalies_severity', 'majeure')
                    ->count(),
                'mineures' => Adherent::where('organisation_id', $dossier->organisation->id)
                    ->where('anomalies_severity', 'mineure')
                    ->count()
            ];

            Log::info('✅ Page anomalies chargée', [
                'dossier_id' => $dossier->id,
                'organisation_id' => $dossier->organisation->id,
                'anomalies_count' => $anomalies->total(),
                'stats' => $stats
            ]);

            return view('operator.dossiers.anomalies', compact(
                'dossier',
                'anomalies', 
                'organisations',
                'stats'
            ));
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur affichage anomalies', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->route('operator.dossiers.show', $dossier->id)
                ->with('error', 'Erreur lors de l\'affichage des anomalies : ' . $e->getMessage());
        }
    }

    /**
     * ✅ EXPORT PDF DES ANOMALIES
     */
    public function exportAnomaliesPDF(Dossier $dossier)
    {
        try {
            // Vérifier l'accès utilisateur
            if ($dossier->organisation->user_id !== Auth::id()) {
                abort(403, 'Accès non autorisé à ce dossier');
            }

            // Récupérer les anomalies avec pagination désactivée pour le PDF
            $anomalies = Adherent::where('organisation_id', $dossier->organisation->id)
                ->where(function($query) {
                    $query->whereNotNull('anomalies_data')
                          ->where('anomalies_data', '!=', '[]')
                          ->orWhereNotNull('anomalies_severity');
                })
                ->with('organisation')
                ->orderBy('anomalies_severity', 'desc')
                ->orderBy('nom')
                ->get();

            // Traitement des données d'anomalies
            foreach ($anomalies as $adherent) {
                if ($adherent->anomalies_data) {
                    if (is_string($adherent->anomalies_data)) {
                        $adherent->anomalies_data = json_decode($adherent->anomalies_data, true) ?: [];
                    } elseif (!is_array($adherent->anomalies_data)) {
                        $adherent->anomalies_data = [];
                    }
                } else {
                    $adherent->anomalies_data = [];
                }
            }

            // Calcul des statistiques
            $stats = [
                'total' => $anomalies->count(),
                'critiques' => $anomalies->where('anomalies_severity', 'critique')->count(),
                'majeures' => $anomalies->where('anomalies_severity', 'majeure')->count(),
                'mineures' => $anomalies->where('anomalies_severity', 'mineure')->count(),
                'organisation' => $dossier->organisation->nom,
                'dossier_numero' => $dossier->numero_recepisse ?? $dossier->id,
                'date_generation' => now()->format('d/m/Y à H:i')
            ];

            // Groupement des anomalies par type pour les statistiques détaillées
            $anomaliesParType = [];
            foreach ($anomalies as $adherent) {
                foreach ($adherent->anomalies_data as $anomalie) {
                    $code = $anomalie['code'] ?? 'non_categorise';
                    if (!isset($anomaliesParType[$code])) {
                        $anomaliesParType[$code] = [
                            'code' => $code,
                            'message' => $anomalie['message'] ?? $code,
                            'count' => 0,
                            'severity' => $anomalie['type'] ?? 'mineure'
                        ];
                    }
                    $anomaliesParType[$code]['count']++;
                }
            }

            $stats['anomalies_par_type'] = array_values($anomaliesParType);

            Log::info('📄 Export PDF anomalies initié', [
                'dossier_id' => $dossier->id,
                'organisation_id' => $dossier->organisation->id,
                'anomalies_count' => $anomalies->count(),
                'user_id' => Auth::id()
            ]);

            // Génération du PDF avec Dompdf
            $pdf = \PDF::loadView('operator.dossiers.pdf.rapport-anomalies', compact(
                'dossier',
                'anomalies',
                'stats'
            ));

            // Configuration du PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            $filename = "rapport_anomalies_{$dossier->organisation->nom}_{$dossier->id}_" . date('Ymd_His') . ".pdf";

            Log::info('✅ PDF anomalies généré avec succès', [
                'dossier_id' => $dossier->id,
                'filename' => $filename,
                'anomalies_count' => $anomalies->count()
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('❌ Erreur génération PDF anomalies', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors de la génération du rapport PDF : ' . $e->getMessage());
        }
    }


// DÉLIMITEUR FIN : MÉTHODE RAPPORT ANOMALIES
    // DÉLIMITEUR FIN : MÉTHODE RAPPORT ANOMALIES

    /**
     * ✅ RÉCUPÉRATION DES ANOMALIES DEPUIS LA BASE DE DONNÉES
     */
    private function getAnomaliesFromDossierDB($dossierId)
    {
        try {
            // Récupérer le dossier avec l'organisation
            $dossier = Dossier::with('organisation')->find($dossierId);
            
            if (!$dossier || !$dossier->organisation) {
                return [];
            }

            // Récupérer les anomalies depuis la table adherent_anomalies
            $anomaliesDB = \DB::table('adherent_anomalies')
                ->join('adherents', 'adherent_anomalies.adherent_id', '=', 'adherents.id')
                ->where('adherent_anomalies.organisation_id', $dossier->organisation->id)
                ->select(
                    'adherent_anomalies.*',
                    'adherents.nip',
                    'adherents.nom',
                    'adherents.prenom'
                )
                ->get();

            // Formatter les anomalies pour l'affichage
            $anomaliesFormatted = [];
            
            foreach ($anomaliesDB as $anomalie) {
                $anomaliesFormatted[] = [
                    'id' => $anomalie->id,
                    'adherent_id' => $anomalie->adherent_id,
                    'adherent_nip' => $anomalie->nip,
                    'adherent_nom' => $anomalie->nom . ' ' . $anomalie->prenom,
                    'type_anomalie' => $anomalie->type_anomalie,
                    'code_anomalie' => $anomalie->code_anomalie ?? 'NON_DEFINI',
                    'champ_concerne' => $anomalie->champ_concerne,
                    'description' => $anomalie->description ?? $anomalie->message_anomalie,
                    'valeur_detectee' => $anomalie->valeur_incorrecte ?? $anomalie->valeur_erronee,
                    'suggestion' => $anomalie->suggestion ?? null,
                    'est_resolu' => (bool) ($anomalie->statut === 'resolu'),
                    'date_detection' => $anomalie->detectee_le ?? $anomalie->created_at,
                    'gravite' => $this->getAnomalieGravite($anomalie->type_anomalie)
                ];
            }

            Log::info('✅ Anomalies récupérées depuis DB', [
                'dossier_id' => $dossierId,
                'organisation_id' => $dossier->organisation->id,
                'total_anomalies' => count($anomaliesFormatted)
            ]);

            return $anomaliesFormatted;

        } catch (\Exception $e) {
            Log::error('❌ ERREUR getAnomaliesFromDossierDB', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * ✅ DÉTERMINER LA GRAVITÉ D'UNE ANOMALIE
     */
    private function getAnomalieGravite($typeAnomalie)
    {
        $gravites = [
            'critique' => [
                'niveau' => 'Critique',
                'couleur' => 'danger',
                'priorite' => 1
            ],
            'majeure' => [
                'niveau' => 'Majeure', 
                'couleur' => 'warning',
                'priorite' => 2
            ],
            'mineure' => [
                'niveau' => 'Mineure',
                'couleur' => 'info', 
                'priorite' => 3
            ]
        ];

        return $gravites[$typeAnomalie] ?? [
            'niveau' => 'Inconnue',
            'couleur' => 'secondary',
            'priorite' => 4
        ];
    }

// DÉLIMITEUR FIN : MÉTHODE RAPPORT ANOMALIES

    // ========================================================================
    // MÉTHODES PLACEHOLDER POUR COMPATIBILITÉ
    // ========================================================================





    public function downloadAccuse($dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function anomalies(Request $request) { return redirect()->back()->with('info', 'En développement'); }
    public function resolveAnomalie(Request $request, $adherentId) { return redirect()->back()->with('info', 'En développement'); }
    public function subventionsIndex() { return redirect()->back()->with('info', 'En développement'); }
    public function subventionCreate($organisation) { return redirect()->back()->with('info', 'En développement'); }
    public function subventionStore(Request $request) { return redirect()->back()->with('info', 'En développement'); }
    public function subventionShow($subvention) { return redirect()->back()->with('info', 'En développement'); }
    public function brouillons() { return redirect()->route('operator.dossiers.index', ['statut' => 'brouillon']); }
    public function saveDraft(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function restoreDraft(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function historique($dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function timeline($dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function extendLock(Request $request, $dossier) { return response()->json(['message' => 'En développement']); }
    public function releaseLock(Request $request, $dossier) { return response()->json(['message' => 'En développement']); }
    public function duplicate(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function saveAsTemplate(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function templates() { return redirect()->back()->with('info', 'En développement'); }
    public function createFromTemplate(Request $request, $template) { return redirect()->back()->with('info', 'En développement'); }
    public function addComment(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function updateComment(Request $request, $comment) { return redirect()->back()->with('info', 'En développement'); }
    public function deleteComment($comment) { return redirect()->back()->with('info', 'En développement'); }
    public function replaceDocument(Request $request, $dossier, $document) { return redirect()->back()->with('info', 'En développement'); }
    public function previewDocument($dossier, $document) { return redirect()->back()->with('info', 'En développement'); }
    public function getStats() { return response()->json(['total_dossiers' => 0, 'en_cours' => 0, 'approuves' => 0, 'rejetes' => 0]); }
}