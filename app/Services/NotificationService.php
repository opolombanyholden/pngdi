<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use App\Models\ValidationEntity;
use App\Models\Dossier;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Envoyer une notification à un utilisateur
     */
    public function notify(User $user, string $subject, string $content, string $type = 'info')
    {
        // Créer le message interne
        $message = Message::create([
            'sender_id' => auth()->id() ?? 1, // 1 = système
            'receiver_id' => $user->id,
            'subject' => $subject,
            'content' => $content,
            'type' => $type,
            'is_read' => false
        ]);
        
        // Créer la notification
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'subject' => $subject,
            'message' => $content,
            'is_read' => false
        ]);
        
        // Envoyer par email si activé
        if ($user->notifications_email ?? true) {
            $this->sendEmail($user, $subject, $content, $type);
        }
        
        // Envoyer par SMS si activé et disponible
        if (($user->notifications_sms ?? false) && !empty($user->telephone)) {
            $this->sendSms($user->telephone, $content);
        }
        
        return $message;
    }
    
    /**
     * Notifier un changement de statut de dossier
     */
    public function notifyDossierStatusChange($dossier, $oldStatus, $newStatus)
    {
        $statusLabels = [
            Dossier::STATUT_BROUILLON => 'Brouillon',
            Dossier::STATUT_SOUMIS => 'Soumis',
            Dossier::STATUT_EN_COURS => 'En cours',
            Dossier::STATUT_ACCEPTE => 'Accepté',
            Dossier::STATUT_REJETE => 'Rejeté',
            Dossier::STATUT_ARCHIVE => 'Archivé'
        ];
        
        $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;
        $newLabel = $statusLabels[$newStatus] ?? $newStatus;
        
        $subject = "Changement de statut de votre dossier";
        $content = "Le statut de votre dossier {$dossier->numero_dossier} est passé de {$oldLabel} à {$newLabel}.";
        
        if ($newStatus === Dossier::STATUT_REJETE && $dossier->motif_rejet) {
            $content .= "\n\nMotif du rejet : {$dossier->motif_rejet}";
        }
        
        if ($dossier->organisation && $dossier->organisation->user) {
            return $this->notify($dossier->organisation->user, $subject, $content, 'status_change');
        }
    }
    
    /**
     * Notifier tous les membres d'une entité de validation
     */
    public function notifyValidationEntity(ValidationEntity $entity, string $subject, string $content, string $type = 'info'): void
    {
        $users = $entity->users()->where('is_active', true)->get();
        
        foreach ($users as $user) {
            $this->notify($user, $subject, $content, $type);
        }
    }
    
    /**
     * Envoyer une notification de masse
     */
    public function notifyBulk(Collection $users, string $subject, string $content, string $type = 'info'): void
    {
        foreach ($users as $user) {
            $this->notify($user, $subject, $content, $type);
        }
    }
    
    /**
     * Créer un message interne
     */
    public function createMessage(User $from, User $to, string $subject, string $content, array $attachments = []): Message
    {
        $message = Message::create([
            'sender_id' => $from->id,
            'receiver_id' => $to->id,
            'subject' => $subject,
            'content' => $content,
            'attachments' => $attachments,
            'is_read' => false
        ]);
        
        // Notification de nouveau message
        $this->notify($to, 'Nouveau message', "Vous avez reçu un nouveau message de {$from->name}: {$subject}", 'message');
        
        return $message;
    }
    
    /**
     * Envoyer un email
     */
    protected function sendEmail(User $user, string $subject, string $content, string $type): void
    {
        try {
            Mail::send('emails.notification', [
                'user' => $user,
                'subject' => $subject,
                'content' => $content,
                'type' => $type
            ], function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                    ->subject('[PNGDI] ' . $subject);
            });
        } catch (\Exception $e) {
            Log::error('Erreur envoi email : ' . $e->getMessage());
        }
    }
    
    /**
     * Envoyer un SMS
     */
    protected function sendSms(string $phoneNumber, string $message): void
    {
        // TODO: Implémenter l'envoi SMS via un provider (Twilio, etc.)
        Log::info("SMS à envoyer à {$phoneNumber}: {$message}");
    }
    
    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
    
    /**
     * Marquer un message comme lu
     */
    public function markMessageAsRead(Message $message): void
    {
        $message->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
    
    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(User $user): void
    {
        // Notifications
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        
        // Messages
        Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }
    
    /**
     * Obtenir les notifications non lues d'un utilisateur
     */
    public function getUnreadNotifications(User $user): Collection
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Obtenir les messages non lus d'un utilisateur
     */
    public function getUnreadMessages(User $user): Collection
    {
        return Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount(User $user): array
    {
        return [
            'notifications' => Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'messages' => Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count()
        ];
    }
    
    /**
     * Envoyer des rappels automatiques
     */
    public function sendReminders(): void
    {
        // Rappel pour les déclarations annuelles
        $this->sendDeclarationReminders();
        
        // Rappel pour les documents expirés
        $this->sendDocumentExpirationReminders();
        
        // Rappel pour les dossiers en attente
        $this->sendPendingDossierReminders();
    }
    
    /**
     * Rappels pour les déclarations annuelles
     */
    protected function sendDeclarationReminders(): void
    {
        // Obtenir les organisations qui doivent faire leur déclaration
        $deadline = now()->addDays(30);
        
        // TODO: Implémenter la logique selon les règles métier
        // Par exemple, trouver les organisations dont la dernière déclaration date de plus d'un an
        
        Log::info('Envoi des rappels de déclaration annuelle');
    }
    
    /**
     * Rappels pour les documents expirés
     */
    protected function sendDocumentExpirationReminders(): void
    {
        // TODO: Implémenter la logique de rappel pour documents expirés
        // Identifier les documents qui expirent dans 30 jours
        
        Log::info('Envoi des rappels pour documents expirés');
    }
    
    /**
     * Rappels pour les dossiers en attente
     */
    protected function sendPendingDossierReminders(): void
    {
        // Dossiers en attente depuis plus de 7 jours
        $dossiers = Dossier::whereIn('statut', [Dossier::STATUT_SOUMIS, Dossier::STATUT_EN_COURS])
            ->where('date_soumission', '<', now()->subDays(7))
            ->with(['currentStep.validationEntity.users'])
            ->get();
        
        foreach ($dossiers as $dossier) {
            if ($dossier->currentStep && $dossier->currentStep->validationEntity) {
                $this->notifyValidationEntity(
                    $dossier->currentStep->validationEntity,
                    'Rappel: Dossier en attente',
                    "Le dossier {$dossier->numero_dossier} est en attente de validation depuis plus de 7 jours.",
                    'warning'
                );
            }
        }
    }
    
    /**
     * Nettoyer les anciennes notifications
     */
    public function cleanupOldNotifications(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        // Supprimer les notifications lues
        $deletedNotifications = Notification::where('created_at', '<', $cutoffDate)
            ->where('is_read', true)
            ->delete();
        
        // Supprimer les messages lus
        $deletedMessages = Message::where('created_at', '<', $cutoffDate)
            ->where('is_read', true)
            ->delete();
        
        return $deletedNotifications + $deletedMessages;
    }
}