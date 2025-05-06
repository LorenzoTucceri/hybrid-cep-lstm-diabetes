<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class NotificationController extends Controller {
    // Funziona alla perfezione!
    public function deleteNotification(int $id) {
        // Ricerca della notifica tramite ID.
        $notification = Notification::find($id);

        if ($notification) {
            // Rimozione della notifica.
            $notification->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Notification deleted successfully."
                ]);
        } else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Notification doesn't exist."
                ]);
        }
    }

    // Funziona alla perfezione!
    public function deleteNotifications(Request $request) {
        // Ottenimento delle notifiche di un determinato utente (utente autenticato).
        $notifications = Notification::where('user_id', $request->user()->id);

        // Rimozione delle notifiche.
        $notifications->delete();

        return response()->json(
            [
                "success" => true,
                "message" => "Notifications deleted successfully."
            ]);
    }

    // Funziona alla perfezione!
    public function notifications(Request $request) {
        /* Ottenimento delle notifiche di un determinato utente (utente autenticato),
        ordinate in modo decrescente per data di creazione. */
        $notifications = Notification::where('user_id', $request->user()->id)->orderBy("created_at", "desc")->get();

        return response()->json(
            [
                "success" => true,
                "notifications" => $notifications,
                "user" => $request->user()
            ]);
    }
}
