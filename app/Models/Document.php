<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'document_type_id',
        'nom_fichier',
        'nom_original',
        'chemin_fichier',
        'taille',
        'mime_type',
        'hash_fichier',
        'metadata',
        'uploaded_by',
        'validated_by',
        'validated_at',
        'is_validated',
        'validation_comment'
    ];

    protected $casts = [
        'taille' => 'integer',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Constantes pour les statuts
    const STATUS_PENDING = 'pending';
    const STATUS_VALIDATED = 'validated';
    const STATUS_REJECTED = 'rejected';

    // Taille maximale par défaut (10 MB)
    const MAX_FILE_SIZE = 10485760;

    // Extensions autorisées
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    // Types MIME autorisés
    const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            // Générer un nom unique pour le fichier
            if (empty($document->nom_fichier)) {
                $extension = pathinfo($document->nom_original, PATHINFO_EXTENSION);
                $document->nom_fichier = Str::uuid() . '.' . $extension;
            }

            // Calculer le hash du fichier si non fourni
            if (empty($document->hash_fichier) && $document->chemin_fichier) {
                $fullPath = Storage::path($document->chemin_fichier);
                if (file_exists($fullPath)) {
                    $document->hash_fichier = hash_file('sha256', $fullPath);
                }
            }
        });

        static::deleting(function ($document) {
            // Supprimer le fichier physique lors de la suppression de l'enregistrement
            if ($document->chemin_fichier && Storage::exists($document->chemin_fichier)) {
                Storage::delete($document->chemin_fichier);
            }
        });
    }

    /**
     * Relations
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scopes
     */
    public function scopeValidated($query)
    {
        return $query->where('is_validated', true);
    }

    public function scopePending($query)
    {
        return $query->whereNull('is_validated');
    }

    public function scopeRejected($query)
    {
        return $query->where('is_validated', false)->whereNotNull('validated_at');
    }

    /**
     * Accesseurs
     */
    public function getTailleLisibleAttribute(): string
    {
        $bytes = $this->taille;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->nom_original, PATHINFO_EXTENSION));
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/gif']);
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_validated === null) {
            return self::STATUS_PENDING;
        }
        
        return $this->is_validated ? self::STATUS_VALIDATED : self::STATUS_REJECTED;
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_REJECTED => 'Rejeté'
        ];

        return $labels[$this->status] ?? 'Inconnu';
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_VALIDATED => 'success',
            self::STATUS_REJECTED => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Méthodes utilitaires
     */
    public function validate($userId, $comment = null): bool
    {
        $this->update([
            'is_validated' => true,
            'validated_by' => $userId,
            'validated_at' => now(),
            'validation_comment' => $comment
        ]);

        return true;
    }

    public function reject($userId, $comment): bool
    {
        if (empty($comment)) {
            throw new \Exception('Un commentaire est obligatoire pour rejeter un document');
        }

        $this->update([
            'is_validated' => false,
            'validated_by' => $userId,
            'validated_at' => now(),
            'validation_comment' => $comment
        ]);

        return true;
    }

    /**
     * Obtenir l'URL de téléchargement
     */
    public function getDownloadUrl(): string
    {
        return route('operator.documents.download', $this->id);
    }

    /**
     * Obtenir l'URL de prévisualisation
     */
    public function getPreviewUrl(): ?string
    {
        if ($this->is_image || $this->is_pdf) {
            return route('operator.documents.preview', $this->id);
        }

        return null;
    }

    /**
     * Vérifier si le fichier existe physiquement
     */
    public function fileExists(): bool
    {
        return $this->chemin_fichier && Storage::exists($this->chemin_fichier);
    }

    /**
     * Obtenir le chemin complet du fichier
     */
    public function getFullPath(): ?string
    {
        if (!$this->chemin_fichier) {
            return null;
        }

        return Storage::path($this->chemin_fichier);
    }

    /**
     * Dupliquer le document pour un autre dossier
     */
    public function duplicateFor($dossierI
    ): Document
    {
        // Créer une copie du fichier
        $newPath = str_replace(
            $this->dossier_id,
            $dossierId,
            $this->chemin_fichier
        );

        Storage::copy($this->chemin_fichier, $newPath);

        // Créer un nouvel enregistrement
        return self::create([
            'dossier_id' => $dossierId,
            'document_type_id' => $this->document_type_id,
            'nom_fichier' => $this->nom_fichier,
            'nom_original' => $this->nom_original,
            'chemin_fichier' => $newPath,
            'taille' => $this->taille,
            'mime_type' => $this->mime_type,
            'hash_fichier' => $this->hash_fichier,
            'metadata' => $this->metadata,
            'uploaded_by' => auth()->id()
        ]);
    }

    /**
     * Vérifier si le document est un doublon
     */
    public function isDuplicate(): bool
    {
        return self::where('hash_fichier', $this->hash_fichier)
            ->where('id', '!=', $this->id)
            ->where('dossier_id', $this->dossier_id)
            ->exists();
    }

    /**
     * Vérifier si l'extension est autorisée
     */
    public static function isAllowedExtension($extension): bool
    {
        return in_array(strtolower($extension), self::ALLOWED_EXTENSIONS);
    }

    /**
     * Vérifier si le type MIME est autorisé
     */
    public static function isAllowedMimeType($mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_MIMES);
    }

    /**
     * Obtenir le répertoire de stockage pour un dossier
     */
    public static function getStoragePath($dossierId, $documentTypeId): string
    {
        return "documents/{$dossierId}/{$documentTypeId}";
    }

    /**
     * Générer un nom de fichier unique
     */
    public static function generateFileName($originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return Str::uuid() . '.' . strtolower($extension);
    }

    /**
     * Nettoyer le nom de fichier original
     */
    public static function sanitizeFileName($fileName): string
    {
        // Remplacer les caractères spéciaux
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // Limiter la longueur
        if (strlen($fileName) > 100) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $name = pathinfo($fileName, PATHINFO_FILENAME);
            $fileName = substr($name, 0, 90) . '.' . $extension;
        }

        return $fileName;
    }
}