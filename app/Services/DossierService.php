<?php
namespace App\Services;

use App\Models\Dossier;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DossierService
{
    /**
     * Créer un nouveau dossier
     */
    public function createDossier(array $data)
    {
        return DB::transaction(function () use ($data) {
            $dossier = Dossier::create($data);
            return $dossier;
        });
    }

    /**
     * Mettre à jour le statut d'un dossier
     */
    public function updateStatus(Dossier $dossier, string $status, ?string $comment = null)
    {
        $dossier->status = $status;
        if ($comment) {
            $dossier->admin_comment = $comment;
        }
        $dossier->save();
        
        return $dossier;
    }

    /**
     * Attacher des documents à un dossier
     */
    public function attachDocuments(Dossier $dossier, array $documents)
    {
        foreach ($documents as $document) {
            // Logique d'upload et d'attachement
        }
    }
}