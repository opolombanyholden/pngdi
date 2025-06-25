<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Dossier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class FileUploadService
{
    /**
     * Configuration des uploads
     */
    protected $config = [
        'disk' => 'public',
        'max_size' => 10485760, // 10MB par défaut
        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
        'allowed_mimes' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        'image_max_width' => 2000,
        'image_max_height' => 2000,
        'image_quality' => 85
    ];
    
    /**
     * Upload un fichier
     */
    public function upload(UploadedFile $file, string $folder = 'documents'): array
    {
        // Valider le fichier de base
        $this->validateFile($file);
        
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::random(40) . '.' . $extension;
        
        // Traiter selon le type de fichier
        if ($this->isImage($file)) {
            $path = $this->uploadImage($file, $folder, $fileName);
        } else {
            $path = $file->storeAs("uploads/{$folder}", $fileName, $this->config['disk']);
        }
        
        return [
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ];
    }
    
    /**
     * Uploader un document avec validation par type
     */
    public function uploadDocument(UploadedFile $file, Dossier $dossier, int $documentTypeId): array
    {
        // Récupérer le type de document
        $documentType = DocumentType::findOrFail($documentTypeId);
        
        // Valider le fichier selon le type de document
        $this->validateFileForDocumentType($file, $documentType);
        
        // Générer le chemin et le nom du fichier
        $path = $this->generatePath($dossier, $documentType);
        $filename = $this->generateFilename($file);
        
        // Uploader
        $fullPath = $file->storeAs($path, $filename, $this->config['disk']);
        
        // Calculer le hash du fichier
        $hash = hash_file('sha256', Storage::disk($this->config['disk'])->path($fullPath));
        
        return [
            'path' => $fullPath,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'hash' => $hash
        ];
    }
    
    /**
     * Uploader une photo d'adhérent
     */
    public function uploadAdherentPhoto(UploadedFile $file, int $adherentId): string
    {
        // Validation spécifique pour les photos
        if (!$this->isImage($file)) {
            throw new Exception('Le fichier doit être une image');
        }
        
        if ($file->getSize() > 2097152) { // 2MB max pour les photos
            throw new Exception('La photo ne doit pas dépasser 2MB');
        }
        
        // Chemin de stockage
        $path = "adherents/{$adherentId}/photos";
        $filename = 'photo_' . time() . '.' . $file->getClientOriginalExtension();
        
        // Uploader et optimiser
        return $this->uploadImage($file, $path, $filename);
    }
    
    /**
     * Uploader un fichier d'import
     */
    public function uploadImportFile(UploadedFile $file, string $type): array
    {
        // Validation pour les fichiers d'import
        $allowedExtensions = ['csv', 'xlsx', 'xls'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Format de fichier non supporté. Formats acceptés : ' . implode(', ', $allowedExtensions));
        }
        
        // Limite de taille pour les imports (5MB)
        if ($file->getSize() > 5242880) {
            throw new Exception('Le fichier d\'import ne doit pas dépasser 5MB');
        }
        
        // Chemin et nom du fichier
        $path = "imports/{$type}/" . date('Y/m');
        $filename = time() . '_' . Str::random(8) . '.' . $extension;
        
        // Sauvegarder
        $storedPath = $file->storeAs($path, $filename, $this->config['disk']);
        
        return [
            'path' => $storedPath,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ];
    }
    
    /**
     * Valider un fichier de base
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Vérifier la taille
        if ($file->getSize() > $this->config['max_size']) {
            throw new Exception(sprintf(
                'Le fichier dépasse la taille maximale autorisée (%s)',
                $this->formatBytes($this->config['max_size'])
            ));
        }
        
        // Vérifier l'extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->config['allowed_extensions'])) {
            throw new Exception(sprintf(
                'Extension non autorisée. Extensions acceptées : %s',
                implode(', ', $this->config['allowed_extensions'])
            ));
        }
        
        // Vérifier le type MIME
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $this->config['allowed_mimes'])) {
            throw new Exception('Type de fichier non autorisé');
        }
    }
    
    /**
     * Valider un fichier pour un type de document spécifique
     */
    protected function validateFileForDocumentType(UploadedFile $file, DocumentType $documentType): void
    {
        // Vérifier la taille
        $maxSize = $documentType->taille_max ?? $this->config['max_size'];
        if ($file->getSize() > $maxSize) {
            throw new Exception(sprintf(
                'Le fichier dépasse la taille maximale autorisée pour ce type de document (%s)',
                $this->formatBytes($maxSize)
            ));
        }
        
        // Vérifier l'extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = $documentType->extensions_autorisees ?? $this->config['allowed_extensions'];
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception(sprintf(
                'Extension non autorisée pour ce type de document. Extensions acceptées : %s',
                implode(', ', $allowedExtensions)
            ));
        }
        
        // Validation supplémentaire pour les PDFs
        if ($extension === 'pdf') {
            $this->validatePdf($file);
        }
    }
    
    /**
     * Valider un fichier PDF
     */
    protected function validatePdf(UploadedFile $file): void
    {
        // Vérifier que c'est vraiment un PDF en lisant les premiers octets
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 4);
        fclose($handle);
        
        if ($header !== '%PDF') {
            throw new Exception('Le fichier n\'est pas un PDF valide');
        }
    }
    
    /**
     * Uploader une image avec optimisation
     */
    protected function uploadImage(UploadedFile $file, string $folder, string $filename): string
    {
        $path = "uploads/{$folder}";
        
        // Créer le répertoire s'il n'existe pas
        Storage::disk($this->config['disk'])->makeDirectory($path);
        
        // Pour l'instant, on fait un upload simple
        // TODO: Ajouter l'optimisation d'image avec Intervention Image si nécessaire
        return $file->storeAs($path, $filename, $this->config['disk']);
    }
    
    /**
     * Générer le chemin de stockage pour un document
     */
    protected function generatePath(Dossier $dossier, DocumentType $documentType): string
    {
        return sprintf(
            'documents/%s/%s/%s',
            $dossier->organisation_id,
            $dossier->id,
            $documentType->id
        );
    }
    
    /**
     * Générer un nom de fichier unique
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return sprintf(
            '%s_%s.%s',
            time(),
            Str::random(16),
            strtolower($extension)
        );
    }
    
    /**
     * Vérifier si le fichier est une image
     */
    protected function isImage(UploadedFile $file): bool
    {
        return strpos($file->getMimeType(), 'image/') === 0;
    }
    
    /**
     * Supprimer un fichier
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->config['disk'])->delete($path);
    }
    
    /**
     * Vérifier si un fichier existe
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->config['disk'])->exists($path);
    }
    
    /**
     * Déplacer un fichier
     */
    public function moveFile(string $from, string $to): bool
    {
        if ($this->exists($from)) {
            return Storage::disk($this->config['disk'])->move($from, $to);
        }
        
        return false;
    }
    
    /**
     * Copier un fichier
     */
    public function copyFile(string $from, string $to): bool
    {
        if ($this->exists($from)) {
            return Storage::disk($this->config['disk'])->copy($from, $to);
        }
        
        return false;
    }
    
    /**
     * Obtenir l'URL d'un fichier
     */
    public function getFileUrl(string $path): ?string
    {
        if ($this->exists($path)) {
            return Storage::disk($this->config['disk'])->url($path);
        }
        
        return null;
    }
    
    /**
     * Formater la taille en octets
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Nettoyer les fichiers orphelins
     */
    public function cleanupOrphanFiles(): int
    {
        $count = 0;
        
        // Parcourir tous les fichiers de documents
        $files = Storage::disk($this->config['disk'])->allFiles('documents');
        
        foreach ($files as $file) {
            // Vérifier si le fichier est référencé en base
            $exists = Document::where('chemin_fichier', $file)->exists();
            
            if (!$exists) {
                // Le fichier n'est pas référencé, le supprimer
                $this->delete($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Obtenir les statistiques de stockage
     */
    public function getStorageStatistics(): array
    {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'by_type' => [],
            'by_organisation' => []
        ];
        
        // Statistiques globales depuis la base de données
        $documents = Document::selectRaw('
            COUNT(*) as count,
            SUM(taille) as total_size,
            mime_type
        ')
        ->groupBy('mime_type')
        ->get();
        
        foreach ($documents as $doc) {
            $stats['total_files'] += $doc->count;
            $stats['total_size'] += $doc->total_size;
            $stats['by_type'][$doc->mime_type] = [
                'count' => $doc->count,
                'size' => $doc->total_size
            ];
        }
        
        // Formater la taille totale
        $stats['total_size_formatted'] = $this->formatBytes($stats['total_size']);
        
        return $stats;
    }
}