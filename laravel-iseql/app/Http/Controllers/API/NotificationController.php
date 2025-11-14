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
    public function notifications(Request $request) {
        // Recupero delle notifiche, ordinate in modo decrescente per data di creazione.
        $notifications = Notification::where("user_id", $request->user()->id)->orderBy("created_at", "desc")->get();
        /*
        foreach ($notifications as $notification) {
            $notification->file;
        }
        */

        return response()->json(
            [
                "success" => true,
                "notifications" => $notifications,
                "user" => $request->user()
            ]);
    }

    public function markNotificationAsRead(int $id) {
        // Ricerca della notifica per ID.
        $notification = Notification::find($id);

        if ($notification) {
            if ($notification->status === "unread") {
                // Aggiornamento dello stato della notifica.
                $notification->update(["status" => "read"]);

                return response()->json(
                    [
                        "success" => true,
                        "message" => "Notification marked as read."
                    ]);
            }
            else {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Notification already marked as read."
                    ]);
            }
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Notification doesn't exist."
                ]);
        }
    }

    public function deleteNotifications(Request $request) {
        // Rimozione delle notifiche.
        Notification::where("user_id", $request->user()->id)->delete();

        return response()->json(
            [
                "success" => true,
                "message" => "Notifications deleted successfully."
            ]);
    }

    public function deleteNotification(int $id) {
        // Ricerca della notifica per ID.
        $notification = Notification::find($id);

        if ($notification) {
            // Rimozione della notifica.
            $notification->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Notification deleted successfully."
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Notification doesn't exist."
                ]);
        }
    }
}
