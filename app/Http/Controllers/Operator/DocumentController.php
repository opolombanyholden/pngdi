<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Dossier;
use App\Models\DocumentType;
use App\Services\FileUploadService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
{
    protected $fileUploadService;
    protected $qrCodeService;
    
    public function __construct(
        FileUploadService $fileUploadService,
        QrCodeService $qrCodeService
    ) {
        $this->fileUploadService = $fileUploadService;
        $this->qrCodeService = $qrCodeService;
    }
    
    /**
     * Upload un document
     */
    public function upload(Request $request, Dossier $dossier)
    {
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$dossier->canBeModified()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce dossier ne peut plus être modifié'
            ], 403);
        }
        
        // Validation
        $request->validate([
            'document' => 'required|file|max:10240',
            'document_type_id' => 'required|exists:document_types,id'
        ]);
        
        try {
            // Upload via le service
            $uploadResult = $this->fileUploadService->uploadDocument(
                $request->file('document'),
                $dossier,
                $request->document_type_id
            );
            
            // Créer l'enregistrement
            $document = Document::create([
                'dossier_id' => $dossier->id,
                'document_type_id' => $request->document_type_id,
                'nom_fichier' => $uploadResult['filename'],
                'nom_original' => $uploadResult['original_name'],
                'chemin_fichier' => $uploadResult['path'],
                'taille' => $uploadResult['size'],
                'mime_type' => $uploadResult['mime_type'],
                'hash_fichier' => $uploadResult['hash'],
                'uploaded_by' => Auth::id()
            ]);
            
            // Générer le QR Code
            $this->qrCodeService->generateForDocument($document);
            
            return response()->json([
                'success' => true,
                'message' => 'Document téléchargé avec succès',
                'document' => [
                    'id' => $document->id,
                    'nom' => $document->nom_original,
                    'taille' => $document->taille_lisible,
                    'type' => $document->documentType->nom,
                    'status' => $document->status_label,
                    'preview_url' => $document->getPreviewUrl(),
                    'download_url' => $document->getDownloadUrl()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Télécharger un document
     */
    public function download(Document $document)
    {
        // Vérifier l'accès
        if ($document->dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le fichier existe
        if (!$document->fileExists()) {
            abort(404, 'Fichier introuvable');
        }
        
        return Storage::disk('public')->download(
            $document->chemin_fichier,
            $document->nom_original
        );
    }
    
    /**
     * Prévisualiser un document
     */
    public function preview(Document $document)
    {
        // Vérifier l'accès
        if ($document->dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le fichier existe
        if (!$document->fileExists()) {
            abort(404, 'Fichier introuvable');
        }
        
        // Prévisualisation selon le type
        if ($document->is_image) {
            return $this->previewImage($document);
        } elseif ($document->is_pdf) {
            return $this->previewPdf($document);
        } else {
            // Pour les autres types, télécharger
            return $this->download($document);
        }
    }
    
    /**
     * Supprimer un document
     */
    public function destroy(Document $document)
    {
        // Vérifier l'accès
        if ($document->dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$document->dossier->canBeModified()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce dossier ne peut plus être modifié'
            ], 403);
        }
        
        // Supprimer le fichier physique
        $this->fileUploadService->delete($document->chemin_fichier);
        
        // Supprimer l'enregistrement
        $document->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Document supprimé avec succès'
        ]);
    }
    
    /**
     * Remplacer un document
     */
    public function replace(Request $request, Document $document)
    {
        // Vérifier l'accès
        if ($document->dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$document->dossier->canBeModified()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce dossier ne peut plus être modifié'
            ], 403);
        }
        
        // Validation
        $request->validate([
            'document' => 'required|file|max:10240'
        ]);
        
        try {
            // Sauvegarder l'ancien chemin
            $oldPath = $document->chemin_fichier;
            
            // Upload le nouveau fichier
            $uploadResult = $this->fileUploadService->uploadDocument(
                $request->file('document'),
                $document->dossier,
                $document->document_type_id
            );
            
            // Mettre à jour l'enregistrement
            $document->update([
                'nom_fichier' => $uploadResult['filename'],
                'nom_original' => $uploadResult['original_name'],
                'chemin_fichier' => $uploadResult['path'],
                'taille' => $uploadResult['size'],
                'mime_type' => $uploadResult['mime_type'],
                'hash_fichier' => $uploadResult['hash'],
                'uploaded_by' => Auth::id(),
                'is_validated' => null,
                'validated_by' => null,
                'validated_at' => null,
                'validation_comment' => null
            ]);
            
            // Supprimer l'ancien fichier
            $this->fileUploadService->delete($oldPath);
            
            // Régénérer le QR Code
            $this->qrCodeService->generateForDocument($document);
            
            return response()->json([
                'success' => true,
                'message' => 'Document remplacé avec succès',
                'document' => [
                    'id' => $document->id,
                    'nom' => $document->nom_original,
                    'taille' => $document->taille_lisible,
                    'status' => $document->status_label
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du remplacement : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtenir les types de documents pour un dossier
     */
    public function getDocumentTypes(Dossier $dossier)
    {
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Obtenir les types de documents
        $documentTypes = DocumentType::where('type_organisation', $dossier->organisation->type)
            ->where(function ($query) use ($dossier) {
                $query->where('type_operation', $dossier->type_operation)
                    ->orWhereNull('type_operation');
            })
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();
        
        // Documents déjà fournis
        $providedTypes = $dossier->documents->pluck('document_type_id')->toArray();
        
        // Formater la réponse
        $types = $documentTypes->map(function ($type) use ($providedTypes) {
            return [
                'id' => $type->id,
                'nom' => $type->nom,
                'description' => $type->description,
                'obligatoire' => $type->is_obligatoire,
                'extensions' => $type->extensions_autorisees,
                'taille_max' => $type->taille_max_lisible,
                'fourni' => in_array($type->id, $providedTypes),
                'exemple_url' => $type->getExampleUrl()
            ];
        });
        
        return response()->json([
            'success' => true,
            'types' => $types
        ]);
    }
    
    /**
     * Télécharger un modèle de document
     */
    public function downloadTemplate(DocumentType $documentType)
    {
        if (!$documentType->hasTemplate()) {
            abort(404, 'Aucun modèle disponible pour ce type de document');
        }
        
        $template = $documentType->template;
        
        return Storage::disk('public')->download(
            $template->chemin_fichier,
            $template->nom_fichier
        );
    }
    
    /**
     * Prévisualiser une image
     */
    protected function previewImage(Document $document)
    {
        $path = Storage::disk('public')->path($document->chemin_fichier);
        $file = file_get_contents($path);
        
        return Response::make($file, 200, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . $document->nom_original . '"'
        ]);
    }
    
    /**
     * Prévisualiser un PDF
     */
    protected function previewPdf(Document $document)
    {
        $path = Storage::disk('public')->path($document->chemin_fichier);
        $file = file_get_contents($path);
        
        return Response::make($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->nom_original . '"'
        ]);
    }

    /**
     * Afficher la liste des documents de l'opérateur
     */
    public function index(Request $request)
    {
        // Récupérer toutes les organisations de l'utilisateur
        $organisations = Auth::user()->organisations()->with(['documents.documentType'])->get();
        
        // Récupérer tous les documents de l'utilisateur groupés par organisation
        $documentsQuery = Document::whereHas('dossier.organisation', function ($query) {
            $query->where('user_id', Auth::id());
        })->with(['dossier.organisation', 'documentType']);
        
        // Filtres
        if ($request->filled('organisation')) {
            $documentsQuery->whereHas('dossier.organisation', function ($query) use ($request) {
                $query->where('id', $request->organisation);
            });
        }
        
        if ($request->filled('type')) {
            $documentsQuery->where('document_type_id', $request->type);
        }
        
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'validated':
                    $documentsQuery->where('is_validated', true);
                    break;
                case 'pending':
                    $documentsQuery->whereNull('is_validated');
                    break;
                case 'rejected':
                    $documentsQuery->where('is_validated', false);
                    break;
            }
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $documentsQuery->where(function ($query) use ($search) {
                $query->where('nom_original', 'like', "%{$search}%")
                      ->orWhere('nom_fichier', 'like', "%{$search}%")
                      ->orWhereHas('documentType', function ($q) use ($search) {
                          $q->where('libelle', 'like', "%{$search}%");
                      });
            });
        }
        
        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $documentsQuery->orderBy($sortBy, $sortOrder);
        
        // Pagination
        $documents = $documentsQuery->paginate(15);
        
        // Statistiques
        $totalDocuments = Document::whereHas('dossier.organisation', function ($query) {
            $query->where('user_id', Auth::id());
        })->count();
        
        $documentsValides = Document::whereHas('dossier.organisation', function ($query) {
            $query->where('user_id', Auth::id());
        })->where('is_validated', true)->count();
        
        $documentsEnAttente = Document::whereHas('dossier.organisation', function ($query) {
            $query->where('user_id', Auth::id());
        })->whereNull('is_validated')->count();
        
        $documentsRejetes = Document::whereHas('dossier.organisation', function ($query) {
            $query->where('user_id', Auth::id());
        })->where('is_validated', false)->count();
        
        // Types de documents pour les filtres
        $documentTypes = DocumentType::where('is_active', true)->orderBy('libelle')->get();
        
        // Calcul de l'espace utilisé
        $totalSize = Document::whereHas('dossier.organisation', function ($query) {
            $query->where('user_id', Auth::id());
        })->sum('taille');
        
        $maxSize = 1024 * 1024 * 1024; // 1GB limite par défaut
        $usedPercentage = $totalSize > 0 ? round(($totalSize / $maxSize) * 100, 2) : 0;
        
        return view('operator.files.index', compact(
            'documents',
            'organisations',
            'documentTypes',
            'totalDocuments',
            'documentsValides',
            'documentsEnAttente',
            'documentsRejetes',
            'totalSize',
            'maxSize',
            'usedPercentage'
        ));
    }

}