<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Liste des messages - Interface complète
     */
    public function index()
    {
        try {
            // Récupérer les statistiques des messages
            $totalMessages = 12; // À remplacer par une vraie requête DB
            $messagesLus = 8;
            $messagesNonLus = 4;
            $messagesEnvoyes = 6;
            $brouillons = 2;
            
            // Messages factices (à remplacer par de vraies données depuis la DB)
            $messages = $this->getFakeMessages();
            
            return view('operator.messages.index', compact(
                'totalMessages',
                'messagesLus', 
                'messagesNonLus',
                'messagesEnvoyes',
                'brouillons',
                'messages'
            ));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans messages index: ' . $e->getMessage());
            
            return redirect()->route('operator.dashboard')
                ->with('error', 'Erreur lors du chargement des messages');
        }
    }

    /**
     * Créer un nouveau message
     */
    public function create()
    {
        try {
            $destinataires = $this->getDestinataires();
            
            return view('operator.messages.create', compact('destinataires'));
            
        } catch (\Exception $e) {
            Log::error('Erreur création message: ' . $e->getMessage());
            
            return redirect()->route('operator.messages.index')
                ->with('error', 'Erreur lors de l\'ouverture du formulaire');
        }
    }

    /**
     * Envoyer un message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'nullable|in:normal,urgent,important',
            'attachments.*' => 'nullable|file|max:10240' // 10MB max par fichier
        ], [
            'recipient.required' => 'Le destinataire est obligatoire',
            'subject.required' => 'L\'objet est obligatoire',
            'content.required' => 'Le contenu du message est obligatoire',
            'attachments.*.max' => 'Chaque fichier ne doit pas dépasser 10MB'
        ]);

        try {
            // Ici vous ajouteriez la logique pour sauvegarder en DB
            // $message = Message::create([...]);
            
            // Simulation de sauvegarde
            $messageData = [
                'from_user_id' => Auth::id(),
                'to_recipient' => $validated['recipient'],
                'subject' => $validated['subject'],
                'content' => $validated['content'],
                'priority' => $validated['priority'] ?? 'normal',
                'sent_at' => now(),
                'status' => 'sent'
            ];
            
            // Gestion des pièces jointes
            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    // Logique de sauvegarde des fichiers
                    $attachments[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
                $messageData['attachments'] = $attachments;
            }
            
            Log::info('Message envoyé:', $messageData);
            
            return response()->json([
                'success' => true,
                'message' => 'Message envoyé avec succès',
                'redirect' => route('operator.messages.index')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur envoi message: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un message
     */
    public function show($messageId)
    {
        try {
            // Ici vous récupéreriez le message depuis la DB
            // $message = Message::findOrFail($messageId);
            
            $message = $this->getFakeMessage($messageId);
            
            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message non trouvé'
                ], 404);
            }
            
            // Marquer comme lu automatiquement
            $this->markAsRead($messageId);
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur affichage message: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement'
            ], 500);
        }
    }

    /**
     * Répondre à un message
     */
    public function reply(Request $request, $messageId)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240'
        ]);

        try {
            // Récupérer le message original
            $originalMessage = $this->getFakeMessage($messageId);
            
            if (!$originalMessage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message original non trouvé'
                ], 404);
            }
            
            // Créer la réponse
            $replyData = [
                'from_user_id' => Auth::id(),
                'to_recipient' => $originalMessage['sender_email'],
                'subject' => 'Re: ' . $originalMessage['subject'],
                'content' => $validated['content'],
                'original_message_id' => $messageId,
                'sent_at' => now(),
                'status' => 'sent'
            ];
            
            Log::info('Réponse envoyée:', $replyData);
            
            return response()->json([
                'success' => true,
                'message' => 'Réponse envoyée avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur réponse message: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la réponse'
            ], 500);
        }
    }

    /**
     * Transférer un message
     */
    public function forward(Request $request, $messageId)
    {
        $validated = $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'string',
            'additional_content' => 'nullable|string'
        ]);

        try {
            $originalMessage = $this->getFakeMessage($messageId);
            
            if (!$originalMessage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message original non trouvé'
                ], 404);
            }
            
            foreach ($validated['recipients'] as $recipient) {
                $forwardData = [
                    'from_user_id' => Auth::id(),
                    'to_recipient' => $recipient,
                    'subject' => 'Fwd: ' . $originalMessage['subject'],
                    'content' => ($validated['additional_content'] ?? '') . "\n\n--- Message transféré ---\n" . $originalMessage['content'],
                    'original_message_id' => $messageId,
                    'sent_at' => now(),
                    'status' => 'sent'
                ];
                
                // Sauvegarder en DB
                Log::info('Message transféré:', $forwardData);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Message transféré avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur transfert message: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du transfert'
            ], 500);
        }
    }

    /**
     * Marquer un message comme lu
     */
    public function markAsRead($messageId)
    {
        try {
            // Ici vous mettriez à jour en DB
            // Message::where('id', $messageId)->update(['read_at' => now()]);
            
            Log::info('Message marqué comme lu:', ['message_id' => $messageId, 'user_id' => Auth::id()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Message marqué comme lu'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur marquer comme lu: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Supprimer un message
     */
    public function destroy($messageId)
    {
        try {
            // Vérifier que le message appartient à l'utilisateur
            // $message = Message::where('id', $messageId)->where('user_id', Auth::id())->first();
            
            // Simulation de suppression
            Log::info('Message supprimé:', ['message_id' => $messageId, 'user_id' => Auth::id()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Message supprimé avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur suppression message: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Sauvegarder un brouillon
     */
    public function saveDraft(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'priority' => 'nullable|in:normal,urgent,important'
        ]);

        try {
            $draftData = array_merge($validated, [
                'from_user_id' => Auth::id(),
                'status' => 'draft',
                'saved_at' => now()
            ]);
            
            Log::info('Brouillon sauvegardé:', $draftData);
            
            return response()->json([
                'success' => true,
                'message' => 'Brouillon sauvegardé avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde brouillon: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde'
            ], 500);
        }
    }

    /**
     * Rechercher dans les messages
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $folder = $request->get('folder', 'inbox');
        
        try {
            // Ici vous feriez une recherche en DB
            // $messages = Message::where('content', 'LIKE', "%{$query}%")->get();
            
            $allMessages = $this->getFakeMessages();
            $filteredMessages = $allMessages->filter(function ($message) use ($query) {
                return stripos($message['subject'], $query) !== false || 
                       stripos($message['content'], $query) !== false ||
                       stripos($message['sender_name'], $query) !== false;
            });
            
            return response()->json([
                'success' => true,
                'messages' => $filteredMessages->values(),
                'total' => $filteredMessages->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur recherche messages: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * ============================================
     * SECTION NOTIFICATIONS
     * ============================================
     */

    /**
     * Centre de notifications
     */
    public function notifications()
    {
        try {
            $totalNotifications = 15;
            $notificationsNonLues = 7;
            $notificationsUrgentes = 3;
            $notificationsAujourdhui = 5;
            
            // Notifications factices (à remplacer par de vraies données)
            $notifications = $this->getFakeNotifications();
            
            return view('operator.notifications.index', compact(
                'totalNotifications',
                'notificationsNonLues',
                'notificationsUrgentes', 
                'notificationsAujourdhui',
                'notifications'
            ));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans notifications index: ' . $e->getMessage());
            
            return redirect()->route('operator.dashboard')
                ->with('error', 'Erreur lors du chargement des notifications');
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllNotificationsAsRead()
    {
        try {
            // Ici vous mettriez à jour toutes les notifications en DB
            // Notification::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
            
            Log::info('Toutes les notifications marquées comme lues:', ['user_id' => Auth::id()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Toutes les notifications ont été marquées comme lues'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur marquer toutes notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markNotificationAsRead($notificationId)
    {
        try {
            // Ici vous mettriez à jour en DB
            // Notification::where('id', $notificationId)->where('user_id', Auth::id())->update(['read_at' => now()]);
            
            Log::info('Notification marquée comme lue:', ['notification_id' => $notificationId, 'user_id' => Auth::id()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marquée comme lue'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur marquer notification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Supprimer une notification
     */
    public function deleteNotification($notificationId)
    {
        try {
            // Ici vous supprimeriez en DB
            // Notification::where('id', $notificationId)->where('user_id', Auth::id())->delete();
            
            Log::info('Notification supprimée:', ['notification_id' => $notificationId, 'user_id' => Auth::id()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification supprimée avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur suppression notification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Compteur de notifications non lues
     */
    public function unreadCount()
    {
        try {
            // Ici vous compteriez en DB
            // $count = Notification::where('user_id', Auth::id())->whereNull('read_at')->count();
            
            $count = 7; // Simulation
            
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur compteur notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'count' => 0
            ]);
        }
    }

    /**
     * Rechercher dans les notifications
     */
    public function searchNotifications(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', '');
        $priority = $request->get('priority', '');
        
        try {
            // Ici vous feriez une recherche en DB avec filtres
            $notifications = $this->getFakeNotifications();
            
            if ($query) {
                $notifications = $notifications->filter(function ($notification) use ($query) {
                    return stripos($notification['title'], $query) !== false || 
                           stripos($notification['message'], $query) !== false;
                });
            }
            
            if ($type) {
                $notifications = $notifications->filter(function ($notification) use ($type) {
                    return $notification['type'] === $type;
                });
            }
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications->values(),
                'total' => $notifications->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur recherche notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * ============================================
     * MÉTHODES PRIVÉES - DONNÉES FACTICES
     * ============================================
     */

    /**
     * Obtenir des messages factices
     */
    private function getFakeMessages()
    {
        return collect([
            [
                'id' => 1,
                'sender_name' => 'Ministère de l\'Intérieur',
                'sender_email' => 'validation@pngdi.ga',
                'sender_color' => 'success',
                'subject' => 'Validation de votre dossier d\'association',
                'content' => 'Votre dossier de création d\'association "Protection de l\'Environnement" a été approuvé...',
                'created_at' => now()->subHours(2),
                'is_read' => false,
                'has_attachment' => true,
                'is_important' => false,
                'label' => 'Approuvé',
                'label_color' => 'success'
            ],
            [
                'id' => 2,
                'sender_name' => 'Service des Subventions',
                'sender_email' => 'subventions@pngdi.ga',
                'sender_color' => 'warning',
                'subject' => 'Demande de subvention - Documents manquants',
                'content' => 'Il manque le budget prévisionnel pour votre demande de subvention...',
                'created_at' => now()->subHours(4),
                'is_read' => false,
                'has_attachment' => false,
                'is_important' => true,
                'label' => 'En attente',
                'label_color' => 'warning'
            ],
            [
                'id' => 3,
                'sender_name' => 'Administration',
                'sender_email' => 'admin@pngdi.ga',
                'sender_color' => 'info',
                'subject' => 'Rappel - Déclaration annuelle',
                'content' => 'N\'oubliez pas de soumettre votre déclaration annuelle avant le 31 mars...',
                'created_at' => now()->subDay(),
                'is_read' => true,
                'has_attachment' => true,
                'is_important' => true,
                'label' => 'Urgent',
                'label_color' => 'danger'
            ]
        ]);
    }

    /**
     * Obtenir un message factice par ID
     */
    private function getFakeMessage($messageId)
    {
        $messages = $this->getFakeMessages();
        return $messages->firstWhere('id', $messageId);
    }

    /**
     * Obtenir des notifications factices
     */
    private function getFakeNotifications()
    {
        return collect([
            [
                'id' => 1,
                'title' => 'Dossier approuvé',
                'message' => 'Votre dossier de création d\'association a été approuvé par l\'administration.',
                'type' => 'dossier',
                'type_label' => 'Dossier',
                'type_color' => 'success',
                'priority' => 'haute',
                'priority_color' => 'success',
                'icon' => 'check-circle',
                'is_read' => false,
                'action_url' => '#',
                'action_text' => 'Télécharger le récépissé',
                'created_at' => now()->subHours(2)
            ],
            [
                'id' => 2,
                'title' => 'Documents manquants',
                'message' => 'Il manque des documents pour votre demande de subvention. Veuillez compléter votre dossier.',
                'type' => 'subvention',
                'type_label' => 'Subvention',
                'type_color' => 'warning',
                'priority' => 'haute',
                'priority_color' => 'warning',
                'icon' => 'exclamation-triangle',
                'is_read' => false,
                'action_url' => '#',
                'action_text' => 'Compléter le dossier',
                'created_at' => now()->subHours(4)
            ],
            [
                'id' => 3,
                'title' => 'Rappel - Déclaration annuelle',
                'message' => 'N\'oubliez pas de soumettre votre déclaration annuelle avant le 31 mars 2025.',
                'type' => 'declaration',
                'type_label' => 'Déclaration',
                'type_color' => 'info',
                'priority' => 'normale',
                'priority_color' => 'info',
                'icon' => 'calendar-alt',
                'is_read' => true,
                'action_url' => '#',
                'action_text' => 'Commencer la déclaration',
                'created_at' => now()->subDay()
            ]
        ]);
    }

    /**
     * Obtenir la liste des destinataires possibles
     */
    private function getDestinataires()
    {
        return [
            'admin' => 'Administration PNGDI',
            'subventions' => 'Service des Subventions',
            'validation' => 'Service de Validation',
            'technique' => 'Support Technique'
        ];
    }
}