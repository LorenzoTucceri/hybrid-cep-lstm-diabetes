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
        // Calcolo del numero di dottori.
        $count = User::where("role_id", "3")->count();

        return response()->json(
            [
                "success" => true,
                "user_count" => $count
            ]);
    }
}
