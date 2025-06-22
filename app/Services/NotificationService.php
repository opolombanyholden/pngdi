<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;

class NotificationService
{
    /**
     * Envoyer une notification à un utilisateur
     */
    public function notify(User $user, string $subject, string $content, string $type = 'info')
    {
        return Message::create([
            'sender_id' => auth()->id() ?? 1, // 1 = système
            'receiver_id' => $user->id,
            'subject' => $subject,
            'content' => $content,
            'type' => $type,
            'is_read' => false
        ]);
    }

    /**
     * Notifier un changement de statut de dossier
     */
    public function notifyDossierStatusChange($dossier, $oldStatus, $newStatus)
    {
        $subject = "Changement de statut de votre dossier";
        $content = "Le statut de votre dossier {$dossier->reference} est passé de {$oldStatus} à {$newStatus}.";
        
        return $this->notify($dossier->user, $subject, $content, 'status_change');
    }
}