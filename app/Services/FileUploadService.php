<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload un fichier
     */
    public function upload(UploadedFile $file, string $folder = 'documents'): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::random(40) . '.' . $extension;
        
        $path = $file->storeAs("uploads/{$folder}", $fileName, 'public');
        
        return [
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ];
    }

    /**
     * Supprimer un fichier
     */
    public function delete(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }

    /**
     * Vérifier si un fichier existe
     */
    public function exists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }
}