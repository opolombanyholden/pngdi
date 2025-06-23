<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Liste des messages
     */
    public function index()
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module messagerie en cours de développement');
    }

    /**
     * Nouveau message
     */
    public function create()
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module nouveau message en cours de développement');
    }

    /**
     * Envoyer un message
     */
    public function store(Request $request)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module envoi message en cours de développement');
    }

    /**
     * Afficher un message
     */
    public function show($message)
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module lecture message en cours de développement');
    }

    /**
     * Répondre à un message
     */
    public function reply(Request $request, $message)
    {
        return redirect()->back()
            ->with('info', 'Module réponse message en cours de développement');
    }

    /**
     * Marquer comme lu
     */
    public function markAsRead($message)
    {
        return redirect()->back()
            ->with('info', 'Module marquer comme lu en cours de développement');
    }

    /**
     * Supprimer un message
     */
    public function destroy($message)
    {
        return redirect()->back()
            ->with('info', 'Module suppression message en cours de développement');
    }

    /**
     * Liste des notifications
     */
    public function notifications()
    {
        return redirect()->route('operator.dashboard')
            ->with('info', 'Module notifications en cours de développement');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        return redirect()->back()
            ->with('info', 'Module marquer tout comme lu en cours de développement');
    }

    /**
     * Compteur de notifications non lues
     */
    public function unreadCount()
    {
        return response()->json(['count' => 0]);
    }
}