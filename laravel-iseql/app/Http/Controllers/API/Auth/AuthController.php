<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class AuthController extends Controller {
    public function login(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
            "device_name" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validator->errors()->first()
            ]);
        }

        // Ricerca dell'utente per e-mail.
        $user = User::where("email", $request->email)->first();

        // Autenticazione fallita.
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "success" => false,
                "message" => "Invalid credentials."
            ]);
        }

        // Autenticazione riuscita.
        $user->role; $user->patient;
        $token = $user->createToken($request->device_name)->plainTextToken;
        return response()->json([
            "success" => true,
            "message" => "Login successful.",
            "token" => $token,
            "user" => $user
        ]);
    }

    public function forgotPassword(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "email" => "required|email"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validator->errors()->first()
            ]);
        }

        // Invio dell'e-mail con il link di reset della password.
        $response = Password::broker()->sendResetLink([
            "email" => $request->email
        ]);

        if ($response == Password::RESET_LINK_SENT) {
            return response()->json([
                "success" => true,
                "message" => "E-mail sent successfully."
            ]);
        }
        else {
            return response()->json([
                "success" => false,
                "message" => "User doesn't exist."
            ]);
        }
    }

    public function register(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "surname" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required|confirmed|min:6",
            "password_confirmation" => "required",
            "device_name" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validator->errors()->first()
            ]);
        }

        // Aggiunta dell'utente.
        User::create([
            "name" => $request->name,
            "surname" => $request->surname,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role_id" => 3, // Dottore.
            "created_at" => now()
        ]);

        // Login automatico.
        return $this->login($request);
    }

    public function logout(Request $request) {
        // Revoca del token di accesso corrente.
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "success" => true,
            "message" => "Logout successful."
        ]);
    }
}
