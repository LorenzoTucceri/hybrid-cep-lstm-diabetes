<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class LoginController extends Controller {
    public function login(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Ricerca dell'utente per e-mail.
        $user = User::where("email", $request->email)->first();

        // Autenticazione non avvenuta con successo.
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Invalid credentials."
                ]);
        }

        // Autenticazione avvenuta con successo.
        $user->role; $user->patient;
        $token = $user->createToken("mobile_token")->plainTextToken;
        return response()->json(
            [
                "success" => true,
                "message" => "Login successful.",
                "token" => $token,
                "user" => $user
            ]);
    }

    public function logout(Request $request) {
        // Revoca di tutti i token.
        $request->user()->tokens()->delete();

        return response()->json(
            [
                "success" => true,
                "message" => "Logout successful."
            ]);
    }
}
