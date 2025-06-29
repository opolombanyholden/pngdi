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

    /**
     * ✅ FILLABLE - Mis à jour avec toutes les nouvelles colonnes de la migration
     */
    protected $fillable = [
        // Clés étrangères
        'dossier_id',
        'document_type_id',
        
        // Informations fichier (colonnes existantes dans la DB)
        'nom_fichier',
        'chemin_fichier',
        'type_mime',           // ⚠️ Corriger : utiliser 'type_mime' (colonne DB) au lieu de 'mime_type'
        'taille',
        'hash_fichier',
        
        // ✅ NOUVELLES COLONNES AJOUTÉES PAR LA MIGRATION
        'nom_original',        // ⭐ Nom original du fichier uploadé
        'uploaded_by',         // ⭐ ID de l'utilisateur qui a uploadé
        'is_system_generated', // ⭐ Document généré automatiquement
        
        // Validation (colonnes existantes dans la DB)
        'is_validated',
        'commentaire',         // ⚠️ Corriger : utiliser 'commentaire' (colonne DB) au lieu de 'validation_comment'
        
        // Métadonnées (si la colonne existe - à vérifier)
        // 'metadata'  // ⚠️ Cette colonne n'existe pas dans votre DB selon le dump
    ];

    /**
     * ✅ CASTS - Mis à jour et corrigés
     */
    protected $casts = [
        'taille' => 'integer',
        'is_validated' => 'boolean',
        'is_system_generated' => 'boolean',  // ⭐ Nouveau cast pour colonne ajoutée
        // 'validated_at' => 'datetime',      // ⚠️ Cette colonne n'existe pas dans votre DB
        // 'metadata' => 'array'              // ⚠️ Cette colonne n'existe pas dans votre DB
    ];

    /**
     * ✅ CONSTANTES - Enrichies
     */
    const STATUS_PENDING = 'pending';
    const STATUS_VALIDATED = 'validated';
    const STATUS_REJECTED = 'rejected';

    // Taille maximale par défaut (10 MB)
    const MAX_FILE_SIZE = 10485760;

    // Extensions autorisées selon document_types de votre DB
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    // Types MIME autorisés
    const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    // ⭐ NOUVEAUX TYPES DE DOCUMENTS SYSTÈME
    const TYPE_ACCUSE_RECEPTION = 'accuse_reception';
    const TYPE_CERTIFICAT = 'certificat';
    const TYPE_RECEPISSE = 'recepisse';
    const TYPE_NOTIFICATION = 'notification';

    /**
     * ✅ BOOT - Amélioré avec gestion des nouvelles colonnes
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            // Générer un nom unique pour le fichier si non fourni
            if (empty($document->nom_fichier) && !empty($document->nom_original)) {
                $extension = pathinfo($document->nom_original, PATHINFO_EXTENSION);
                $document->nom_fichier = time() . '_' . Str::random(10) . '.' . $extension;
            }

            // Définir uploaded_by automatiquement si non fourni
            if (empty($document->uploaded_by) && auth()->check()) {
                $document->uploaded_by = auth()->id();
            }
            
            // Définir is_system_generated par défaut
            if ($document->is_system_generated === null) {
                $document->is_system_generated = false;
            }

            // Calculer le hash du fichier si non fourni et fichier existe
            if (empty($document->hash_fichier) && $document->chemin_fichier) {
                $fullPath = storage_path('app/public/' . $document->chemin_fichier);
                if (file_exists($fullPath)) {
                    $document->hash_fichier = hash_file('sha256', $fullPath);
                }
            }
            
            // Nettoyer le nom original si fourni
            if (!empty($document->nom_original)) {
                $document->nom_original = self::sanitizeFileName($document->nom_original);
            }
        });

        static::deleting(function ($document) {
            // Supprimer le fichier physique lors de la suppression de l'enregistrement
            if ($document->chemin_fichier) {
                $fullPath = storage_path('app/public/' . $document->chemin_fichier);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                
                // Aussi essayer avec Storage facade
                if (Storage::disk('public')->exists($document->chemin_fichier)) {
                    Storage::disk('public')->delete($document->chemin_fichier);
                }
            }
        });
    }

    /**
     * ✅ RELATIONS - Enrichies avec nouvelle relation
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * ⭐ NOUVELLE RELATION - Utilisateur qui a uploadé le document
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * ⚠️ RELATION SUPPRIMÉE - validatedBy n'existe pas dans votre DB
     * Si vous voulez l'ajouter, créez une migration pour 'validated_by' et 'validated_at'
     */
    // public function validatedBy(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'validated_by');
    // }

    /**
     * ✅ SCOPES - Améliorés
     */
    public function scopeValidated($query)
    {
        return $query->where('is_validated', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_validated', false);
    }

    public function scopeRejected($query)
    {
        return $query->where('is_validated', false)
            ->whereNotNull('commentaire');
    }
    
    /**
     * ⭐ NOUVEAUX SCOPES - Pour les nouvelles colonnes
     */
    public function scopeSystemGenerated($query)
    {
        return $query->where('is_system_generated', true);
    }
    
    public function scopeUserUploaded($query)
    {
        return $query->where('is_system_generated', false);
    }
    
    public function scopeByUploader($query, $userId)
    {
        return $query->where('uploaded_by', $userId);
    }
    
    public function scopeAccusesReception($query)
    {
        return $query->where('nom_fichier', 'like', 'accuse_reception_%');
    }

    /**
     * ✅ ACCESSEURS - Mis à jour et enrichis
     */
    public function getTailleLisibleAttribute(): string
    {
        $bytes = $this->taille ?: 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getExtensionAttribute(): string
    {
        $fileName = $this->nom_original ?: $this->nom_fichier;
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->type_mime, [
            'image/jpeg', 'image/png', 'image/gif', 'image/jpg'
        ]);
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->type_mime === 'application/pdf';
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_validated === null || $this->is_validated === false) {
            return self::STATUS_PENDING;
        }
        
        return self::STATUS_VALIDATED;
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
     * ⭐ NOUVEAUX ACCESSEURS - Pour les nouvelles colonnes
     */
    public function getNomAffichageAttribute(): string
    {
        return $this->nom_original ?: $this->nom_fichier;
    }
    
    public function getTypeSourceAttribute(): string
    {
        return $this->is_system_generated ? 'Système' : 'Utilisateur';
    }
    
    public function getUploaderNameAttribute(): string
    {
        return $this->uploadedBy ? $this->uploadedBy->name : 'Système';
    }

    /**
     * ✅ MÉTHODES UTILITAIRES - Mises à jour
     */
    public function validate($comment = null): bool
    {
        $this->update([
            'is_validated' => true,
            'commentaire' => $comment
        ]);

        return true;
    }

    public function reject($comment): bool
    {
        if (empty($comment)) {
            throw new \Exception('Un commentaire est obligatoire pour rejeter un document');
        }

        $this->update([
            'is_validated' => false,
            'commentaire' => $comment
        ]);

        return true;
    }

    /**
     * ✅ URLs - Mises à jour avec sécurité
     */
    public function getDownloadUrl(): string
    {
        return route('operator.documents.download', [
            'document' => $this->id,
            'hash' => substr($this->hash_fichier, 0, 8) // Sécurité basique
        ]);
    }

    public function getPreviewUrl(): ?string
    {
        if ($this->is_image || $this->is_pdf) {
            return route('operator.documents.preview', [
                'document' => $this->id,
                'hash' => substr($this->hash_fichier, 0, 8)
            ]);
        }

        return null;
    }

    /**
     * ✅ GESTION FICHIERS - Améliorée
     */
    public function fileExists(): bool
    {
        if (!$this->chemin_fichier) {
            return false;
        }
        
        // Essayer avec le chemin storage/app/public
        $fullPath = storage_path('app/public/' . $this->chemin_fichier);
        if (file_exists($fullPath)) {
            return true;
        }
        
        // Essayer avec Storage facade
        return Storage::disk('public')->exists($this->chemin_fichier);
    }

    public function getFullPath(): ?string
    {
        if (!$this->chemin_fichier) {
            return null;
        }

        return storage_path('app/public/' . $this->chemin_fichier);
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Obtenir l'URL publique du fichier
     */
    public function getPublicUrl(): ?string
    {
        if (!$this->chemin_fichier) {
            return null;
        }
        
        return Storage::disk('public')->url($this->chemin_fichier);
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE - Dupliquer le document
     */
    public function duplicateFor($dossierId): Document
    {
        if (!$this->fileExists()) {
            throw new \Exception('Le fichier source n\'existe pas');
        }
        
        // Créer le nouveau chemin
        $pathInfo = pathinfo($this->chemin_fichier);
        $newPath = $pathInfo['dirname'] . '/' . time() . '_copy_' . $pathInfo['basename'];

        // Copier le fichier
        $sourcePath = storage_path('app/public/' . $this->chemin_fichier);
        $destPath = storage_path('app/public/' . $newPath);
        
        if (!copy($sourcePath, $destPath)) {
            throw new \Exception('Impossible de copier le fichier');
        }

        // Créer un nouvel enregistrement
        return self::create([
            'dossier_id' => $dossierId,
            'document_type_id' => $this->document_type_id,
            'nom_fichier' => basename($newPath),
            'nom_original' => $this->nom_original,
            'chemin_fichier' => $newPath,
            'taille' => $this->taille,
            'type_mime' => $this->type_mime,
            'hash_fichier' => $this->hash_fichier, // Même hash car même contenu
            'uploaded_by' => auth()->id(),
            'is_system_generated' => true // Copie = génération système
        ]);
    }

    /**
     * ✅ VÉRIFICATIONS - Améliorées
     */
    public function isDuplicate(): bool
    {
        return self::where('hash_fichier', $this->hash_fichier)
            ->where('id', '!=', $this->id)
            ->where('dossier_id', $this->dossier_id)
            ->exists();
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Vérifier si c'est un document d'accusé de réception
     */
    public function isAccuseReception(): bool
    {
        return $this->is_system_generated && 
               str_contains($this->nom_fichier, 'accuse_reception');
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Vérifier l'intégrité du fichier
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->fileExists() || !$this->hash_fichier) {
            return false;
        }
        
        $currentHash = hash_file('sha256', $this->getFullPath());
        return $currentHash === $this->hash_fichier;
    }

    /**
     * ✅ MÉTHODES STATIQUES - Enrichies
     */
    public static function isAllowedExtension($extension): bool
    {
        return in_array(strtolower($extension), self::ALLOWED_EXTENSIONS);
    }

    public static function isAllowedMimeType($mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_MIMES);
    }

    public static function getStoragePath($dossierId, $documentTypeId = null): string
    {
        $path = "documents/organisations/{$dossierId}";
        
        if ($documentTypeId) {
            $path .= "/{$documentTypeId}";
        }
        
        return $path;
    }

    public static function generateFileName($originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return time() . '_' . Str::random(10) . '.' . strtolower($extension);
    }

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
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Créer un document système (accusé, certificat, etc.)
     */
    public static function createSystemDocument($dossierId, $filename, $filePath, $originalName = null): self
    {
        $fileInfo = pathinfo($filePath);
        $size = file_exists($filePath) ? filesize($filePath) : 0;
        $hash = file_exists($filePath) ? hash_file('sha256', $filePath) : null;
        
        // Déterminer le type MIME
        $mimeType = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath) ?: $mimeType;
        }
        
        return self::create([
            'dossier_id' => $dossierId,
            'document_type_id' => 99, // Type spécial pour documents système
            'nom_fichier' => $filename,
            'nom_original' => $originalName ?: $filename,
            'chemin_fichier' => str_replace(storage_path('app/public/'), '', $filePath),
            'taille' => $size,
            'type_mime' => $mimeType,
            'hash_fichier' => $hash,
            'is_validated' => true, // Documents système pré-validés
            'is_system_generated' => true,
            'uploaded_by' => auth()->id() ?? 1
        ]);
    }
    
    /**
     * ⭐ NOUVELLE MÉTHODE - Obtenir les statistiques des documents
     */
    public static function getStatistiques($dossierId = null): array
    {
        $query = self::query();
        
        if ($dossierId) {
            $query->where('dossier_id', $dossierId);
        }
        
        return [
            'total' => $query->count(),
            'valides' => $query->where('is_validated', true)->count(),
            'en_attente' => $query->where('is_validated', false)->count(),
            'systeme' => $query->where('is_system_generated', true)->count(),
            'utilisateur' => $query->where('is_system_generated', false)->count(),
            'taille_totale' => $query->sum('taille')
        ];
    }
}