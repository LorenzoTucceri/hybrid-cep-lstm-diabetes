<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Retrieve notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Mark all unread notifications as read for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setReadNotification()
    {
        try {
            // Get the authenticated user
            $user = auth()->user();

            // Retrieve unread notifications for the user
            $notifications = $user->notifications()->where('status', 'unread')->get();

            // Check if there are unread notifications
            if ($notifications->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No unread notifications to mark as read.']);
            }

            // Update all unread notifications to "read"
            foreach ($notifications as $notification) {
                $notification->update(["status" => 'read']);
            }

            return response()->json(['success' => true, 'message' => 'Notifications have been marked as read.']);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error updating notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification by its ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNotification($id)
    {
        try {
            // Find the notification by ID
            $notification = Notification::findOrFail($id);

            // Delete the notification
            $notification->delete();

            return response()->json([
                'message' => 'Notification successfully deleted!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error deleting notification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAllNotification($id)
    {
        try {
            // Trova la notifica per ID
            Notification::where("user_id", $id)->delete();

            // Elimina la notifica

            // Rispondi con un messaggio di successo
            return response()->json([
                'message' => 'Notifiche eliminata con successo!'
            ], 200);
        } catch (\Exception $e) {
            // Gestione degli errori
            return response()->json([
                'error' => 'Errore durante l\'eliminazione delle notifiche: ' . $e->getMessage()
            ], 500);
        }
    }

}
