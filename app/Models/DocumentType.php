<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'description',
        'type_organisation',
        'type_operation',
        'is_active',
        'is_obligatoire',
        'ordre',
        'taille_max',
        'extensions_autorisees',
        'instructions',
        'exemple_path'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_obligatoire' => 'boolean',
        'ordre' => 'integer',
        'taille_max' => 'integer',
        'extensions_autorisees' => 'array'
    ];

    // Taille maximale par défaut (5 MB)
    const DEFAULT_MAX_SIZE = 5242880;

    // Extensions par défaut
    const DEFAULT_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documentType) {
            // Générer un code unique si non fourni
            if (empty($documentType->code)) {
                $documentType->code = self::generateCode($documentType->nom, $documentType->type_organisation);
            }

            // Définir la taille maximale par défaut
            if (empty($documentType->taille_max)) {
                $documentType->taille_max = self::DEFAULT_MAX_SIZE;
            }

            // Définir les extensions par défaut
            if (empty($documentType->extensions_autorisees)) {
                $documentType->extensions_autorisees = self::DEFAULT_EXTENSIONS;
            }

            // Définir l'ordre si non fourni
            if (empty($documentType->ordre)) {
                $maxOrdre = self::where('type_organisation', $documentType->type_organisation)
                    ->where('type_operation', $documentType->type_operation)
                    ->max('ordre');
                $documentType->ordre = ($maxOrdre ?? 0) + 1;
            }
        });
    }

    /**
     * Générer un code unique
     */
    public static function generateCode($nom, $typeOrganisation): string
    {
        // Préfixe selon le type d'organisation
        $prefixes = [
            Organisation::TYPE_ASSOCIATION => 'ASS',
            Organisation::TYPE_ONG => 'ONG',
            Organisation::TYPE_PARTI => 'PP',
            Organisation::TYPE_CONFESSION => 'CR'
        ];

        $prefix = $prefixes[$typeOrganisation] ?? 'DOC';
        
        // Créer un code à partir du nom
        $namePart = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nom), 0, 10));
        
        // Ajouter un numéro si nécessaire pour l'unicité
        $baseCode = $prefix . '_' . $namePart;
        $code = $baseCode;
        $counter = 1;

        while (self::where('code', $code)->exists()) {
            $code = $baseCode . '_' . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Relations
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function template(): HasOne
    {
        return $this->hasOne(DocumentTemplate::class);
    }

    /**
     * Scopes
     */
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeObligatoires($query)
    {
        return $query->where('is_obligatoire', true);
    }

    public function scopeFacultatifs($query)
    {
        return $query->where('is_obligatoire', false);
    }

    public function scopeForOrganisationType($query, $type)
    {
        return $query->where('type_organisation', $type);
    }

    public function scopeForOperation($query, $operation)
    {
        return $query->where('type_operation', $operation);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    /**
     * Obtenir les types de documents pour une organisation et une opération
     */
    public static function getForOrganisationAndOperation($typeOrganisation, $typeOperation)
    {
        return self::actifs()
            ->where('type_organisation', $typeOrganisation)
            ->where(function ($query) use ($typeOperation) {
                $query->where('type_operation', $typeOperation)
                      ->orWhereNull('type_operation');
            })
            ->ordered()
            ->get();
    }

    /**
     * Obtenir les types de documents obligatoires
     */
    public static function getRequiredFor($typeOrganisation, $typeOperation)
    {
        return self::getForOrganisationAndOperation($typeOrganisation, $typeOperation)
            ->where('is_obligatoire', true);
    }

    /**
     * Accesseurs
     */
    public function getTailleMaxLisibleAttribute(): string
    {
        $bytes = $this->taille_max;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getExtensionsStringAttribute(): string
    {
        return implode(', ', $this->extensions_autorisees ?? []);
    }

    public function getStatutLabelAttribute(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public function getStatutColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'danger';
    }

    public function getObligatoireLabelAttribute(): string
    {
        return $this->is_obligatoire ? 'Obligatoire' : 'Facultatif';
    }

    public function getObligatoireColorAttribute(): string
    {
        return $this->is_obligatoire ? 'danger' : 'info';
    }

    /**
     * Méthodes utilitaires
     */
    public function hasTemplate(): bool
    {
        return $this->template()->exists();
    }

    public function hasExample(): bool
    {
        return !empty($this->exemple_path) && file_exists(storage_path('app/public/' . $this->exemple_path));
    }

    public function getExampleUrl(): ?string
    {
        if ($this->hasExample()) {
            return asset('storage/' . $this->exemple_path);
        }
        return null;
    }

    /**
     * Vérifier si une extension est autorisée
     */
    public function isExtensionAllowed($extension): bool
    {
        return in_array(strtolower($extension), $this->extensions_autorisees ?? []);
    }

    /**
     * Vérifier si une taille de fichier est acceptable
     */
    public function isSizeAllowed($size): bool
    {
        return $size <= $this->taille_max;
    }

    /**
     * Activer/Désactiver le type de document
     */
    public function toggleActive(): bool
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Rendre obligatoire/facultatif
     */
    public function toggleObligatoire(): bool
    {
        $this->is_obligatoire = !$this->is_obligatoire;
        return $this->save();
    }

    /**
     * Réordonner les types de documents
     */
    public static function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $ordre => $id) {
            self::where('id', $id)->update(['ordre' => $ordre + 1]);
        }
    }

    /**
     * Dupliquer pour un autre type d'organisation
     */
    public function duplicateFor($typeOrganisation): DocumentType
    {
        $newDocumentType = $this->replicate();
        $newDocumentType->type_organisation = $typeOrganisation;
        $newDocumentType->code = self::generateCode($this->nom, $typeOrganisation);
        $newDocumentType->save();

        // Dupliquer le template s'il existe
        if ($this->hasTemplate()) {
            $template = $this->template;
            $newTemplate = $template->replicate();
            $newTemplate->document_type_id = $newDocumentType->id;
            $newTemplate->save();
        }

        return $newDocumentType;
    }

    /**
     * Obtenir les statistiques d'utilisation
     */
    public function getStatistics(): array
    {
        $totalDocuments = $this->documents()->count();
        $validatedDocuments = $this->documents()->validated()->count();
        $rejectedDocuments = $this->documents()->rejected()->count();
        $pendingDocuments = $this->documents()->pending()->count();

        return [
            'total' => $totalDocuments,
            'validated' => $validatedDocuments,
            'rejected' => $rejectedDocuments,
            'pending' => $pendingDocuments,
            'validation_rate' => $totalDocuments > 0 
                ? round(($validatedDocuments / $totalDocuments) * 100, 2) 
                : 0
        ];
    }

    /**
     * Obtenir la liste des types d'organisation comme options
     */
    public static function getTypeOrganisationOptions(): array
    {
        return [
            Organisation::TYPE_ASSOCIATION => 'Association',
            Organisation::TYPE_ONG => 'ONG',
            Organisation::TYPE_PARTI => 'Parti politique',
            Organisation::TYPE_CONFESSION => 'Confession religieuse'
        ];
    }

    /**
     * Obtenir la liste des types d'opération comme options
     */
    public static function getTypeOperationOptions(): array
    {
        return [
            Dossier::TYPE_CREATION => 'Création',
            Dossier::TYPE_MODIFICATION => 'Modification',
            Dossier::TYPE_CESSATION => 'Cessation',
            Dossier::TYPE_DECLARATION => 'Déclaration',
            Dossier::TYPE_FUSION => 'Fusion',
            Dossier::TYPE_ABSORPTION => 'Absorption'
        ];
    }
}