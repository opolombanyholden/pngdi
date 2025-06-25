<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeclarationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'is_active',
        'ordre',
        'delai_traitement',
        'documents_requis'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'documents_requis' => 'array'
    ];

    /**
     * Relation avec les déclarations
     */
    public function declarations()
    {
        return $this->hasMany(Declaration::class);
    }

    /**
     * Scope pour les types actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordonné
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre', 'asc');
    }
}