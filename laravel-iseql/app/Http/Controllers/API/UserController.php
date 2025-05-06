<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class UserController extends Controller {
    public function userCount() {
        // Ottenimento del numero di dottori (ID del ruolo pari a 3).
        $count = User::where("role_id", "3")->count();

        return response()->json(
            [
                "success" => true,
                "user_count" => $count
            ]);
    }
}
