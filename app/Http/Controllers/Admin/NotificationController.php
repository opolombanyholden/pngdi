<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Liste des notifications
     */
    public function index()
    {
        return view('admin.notifications.index', [
            'notifications' => $this->getNotifications(),
            'unread_count' => 3
        ]);
    }

    /**
     * API: Notifications récentes pour le header
     */
    public function recent()
    {
        return response()->json([
            'count' => 3,
            'notifications' => [
                [
                    'id' => 1,
                    'title' => 'Nouveau dossier en attente',
                    'message' => 'Association "Jeunesse Gabonaise" a soumis un dossier',
                    'time' => '5 minutes',
                    'read' => false
                ],
                [
                    'id' => 2,
                    'title' => 'Dossier approuvé',
                    'message' => 'ONG "Développement Durable" approuvée',
                    'time' => '1 heure',
                    'read' => false
                ],
                [
                    'id' => 3,
                    'title' => 'Nouvel utilisateur',
                    'message' => 'Opérateur "Marie NZAMBA" s\'est inscrit',
                    'time' => '2 heures',
                    'read' => true
                ]
            ]
        ]);
    }

    /**
     * Marquer comme lu
     */
    public function markAsRead($id)
    {
        return response()->json(['success' => true, 'message' => 'Notification marquée comme lue']);
    }

    /**
     * Marquer toutes comme lues
     */
    public function markAllAsRead()
    {
        return response()->json(['success' => true, 'message' => 'Toutes les notifications marquées comme lues']);
    }

    /**
     * Obtenir les notifications (simulation)
     */
    private function getNotifications()
    {
        return collect([
            [
                'id' => 1,
                'title' => 'Nouveau dossier en attente',
                'message' => 'Association "Jeunesse Gabonaise" a soumis un dossier',
                'type' => 'dossier',
                'time' => '5 minutes',
                'read' => false,
                'icon' => 'fas fa-folder',
                'color' => 'warning'
            ],
            [
                'id' => 2,
                'title' => 'Dossier approuvé',
                'message' => 'ONG "Développement Durable" approuvée par Agent Martin',
                'type' => 'validation',
                'time' => '1 heure',
                'read' => false,
                'icon' => 'fas fa-check-circle',
                'color' => 'success'
            ],
            [
                'id' => 3,
                'title' => 'Nouvel utilisateur',
                'message' => 'Opérateur "Marie NZAMBA" s\'est inscrit',
                'type' => 'user',
                'time' => '2 heures',
                'read' => true,
                'icon' => 'fas fa-user-plus',
                'color' => 'info'
            ]
        ]);
    }
}
