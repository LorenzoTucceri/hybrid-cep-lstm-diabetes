<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    public function createUser(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "surname" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required|confirmed|min:6",
            "password_confirmation" => "required",
            "role" => "required|in:Admin,Doctor"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Ricerca del ruolo per nome.
        $role = Role::where("name", $request->role)->firstOrFail();

        // Aggiunta dell'utente.
        User::create([
            "name" => $request->name,
            "surname" => $request->surname,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role_id" => $role->id,
            "created_at" => now()
        ]);

        return response()->json(
            [
                "success" => true,
                "message" => "User created successfully."
            ]);
    }

    public function users(Request $request) {
        /* Recupero degli utenti, ad esclusione dell'utente autenticato, dell'amministratore
        principale e dei pazienti. */
        $users = User::where([
            ["id", "<>", $request->user()->id],
            ["id", "<>", "1"],
            ["role_id", "<>", "2"]
        ])->get();
        foreach ($users as $user) {
            $user->role;
        }

        return response()->json(
            [
                "success" => true,
                "users" => $users
            ]);
    }

    public function doctors(Request $request) {
        // Recupero dei dottori in base al ruolo.
        if ($request->user()->role->name === "Admin") {
            // Il ruolo è quello dell'amministratore.
            $users = User::where("role_id", "3")->get();
        }
        else {
            // Il ruolo è quello del dottore.
            $users = [$request->user()];
        }
        foreach ($users as $user) {
            $user->role;
        }

        return response()->json(
            [
                "success" => true,
                "users" => $users
            ]);
    }

    public function updateUser(Request $request, int $id) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "surname" => "required",
            "email" => "required|email|unique:users,email," . $id,
            "password" => "required|confirmed|min:6",
            "password_confirmation" => "required",
            "role" => "required|in:Admin,Doctor"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Ricerca dell'utente per ID.
        $user = User::find($id);

        if ($user) {
            // Ricerca del ruolo per nome.
            $role = Role::where("name", $request->role)->firstOrFail();

            // Aggiornamento dell'utente.
            $user->update([
                "name" => $request->name,
                "surname" => $request->surname,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "role_id" => $role->id
            ]);

            return response()->json(
                [
                    "success" => true,
                    "message" => "User updated successfully."
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "User doesn't exist."
                ]);
        }
    }

    public function deleteUser(int $id) {
        // Ricerca dell'utente per ID.
        $user = User::find($id);

        if ($user) {
            // Rimozione dell'utente.
            $user->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "User deleted successfully."
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "User doesn't exist."
                ]);
        }
    }

    public function updateProfile(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "surname" => "required",
            "email" => "required|email|unique:users,email," . $request->user()->id,
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Aggiornamento del profilo.
        $request->user()->update([
            "name" => $request->name,
            "surname" => $request->surname,
            "email" => $request->email
        ]);

        return response()->json(
            [
                "success" => true,
                "message" => "Profile updated successfully."
            ]);
    }

    public function updatePassword(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "password_current" => "required|current_password",
            "password" => "required|confirmed|min:6",
            "password_confirmation" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Aggiornamento della password.
        $request->user()->update([
            "password" => Hash::make($request->password)
        ]);

        return response()->json(
            [
                "success" => true,
                "message" => "Password updated successfully."
            ]);
    }
}
