<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\InviteToken;
use App\Mail\InvitoIscrizione;
use App\Models\File;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class PatientController extends Controller {
    public function patientCount() {
        // Calcolo del numero di pazienti.
        $count = Patient::count();

        return response()->json(
            [
                "success" => true,
                "patient_count" => $count
            ]);
    }

    public function createPatient(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "surname" => "required",
            "email" => "required|email|unique:patients,email",
            "birth" => "required|date",
            "telephone_number" => "nullable",
            "address" => "required",
            "gender" => "required|in:Male,Female",
            "doctor" => "required|exists:users,id"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Aggiunta del paziente.
        Patient::create([
            "name" => $request->name,
            "surname" => $request->surname,
            "email" => $request->email,
            "birth" => $request->birth,
            "telephone_number" => $request->telephone_number,
            "address" => $request->address,
            "gender" => $request->gender,
            "doctor_id" => $request->doctor
        ]);

        return response()->json(
            [
                "success" => true,
                "message" => "Patient created successfully."
            ]);
    }

    public function invitePatient(Request $request, int $id) {
        // Ricerca del paziente per ID.
        $patient = Patient::find($id);

        if ($patient) {
            // Controllo dell'esistenza di un utente con la stessa e-mail.
            $user = User::where("email", $patient->email)->first();

            if ($user) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "There's already a registered user with this e-mail."
                    ]);
            }
            else {
                // Generazione di un token univoco per l'invito.
                $token = Str::random(32);
                $expires_at = now()->addHours(48);

                // Aggiunta del token.
                InviteToken::create([
                    "email" => $patient->email,
                    "token" => $token,
                    "expires_at" => $expires_at
                ]);

                // Creazione del link di registrazione con il token.
                $link = route("register.token", ["token" => $token]);

                // Invio dell'e-mail con il link di registrazione.
                Mail::to($patient->email)->send(new InvitoIscrizione($patient, $request->user(), $link));

                return response()->json(
                    [
                        "success" => true,
                        "message" => "E-mail sent successfully."
                    ]);
            }
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Patient doesn't exist."
                ]);
        }
    }

    public function patients(Request $request) {
        // Recupero dei pazienti in base al ruolo.
        if ($request->user()->role->name === "Doctor") {
            // Il ruolo è quello del dottore.
            $patients = Patient::where("doctor_id", $request->user()->id)->get();
        }
        else {
            // Il ruolo è quello dell'amministratore.
            $patients = Patient::all();
        }

        return response()->json(
            [
                "success" => true,
                "patients" => $patients
            ]);
    }

    public function patient(int $id) {
        // Ricerca del paziente per ID.
        $patient = Patient::find($id);

        if ($patient) {
            return response()->json(
                [
                    "success" => true,
                    "message" => "Patient found successfully.",
                    "patient" => $patient
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Patient doesn't exist."
                ]);
        }
    }

    public function updatePatient(Request $request, int $id) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "surname" => "required",
            "email" => "required|email|unique:patients,email," . $id,
            "birth" => "required|date",
            "telephone_number" => "nullable",
            "address" => "required",
            "gender" => "required|in:Male,Female",
            "doctor" => "required|exists:users,id"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Ricerca del paziente per ID.
        $patient = Patient::find($id);

        if ($patient) {
            // Aggiornamento del paziente.
            $patient->update([
                "name" => $request->name,
                "surname" => $request->surname,
                "email" => $request->email,
                "birth" => $request->birth,
                "telephone_number" => $request->telephone_number,
                "address" => $request->address,
                "gender" => $request->gender,
                "doctor_id" => $request->doctor
            ]);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Patient updated successfully."
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Patient doesn't exist."
                ]);
        }
    }

    public function deletePatient(int $id) {
        // Ricerca del paziente per ID.
        $patient = Patient::find($id);

        if ($patient) {
            // Recupero dei file CSV associati al paziente da eliminare.
            $csvs = File::where("patient_id", $patient->id)->get();

            foreach ($csvs as $csv) {
                // Rimozione del file CSV.
                Storage::delete("patients_csv/" . $patient->id . "/" . $csv->csv_file_path);
                $csv->delete();
            }

            // Rimozione della directory (se vuota).
            Storage::deleteDirectory("patients_csv/" . $patient->id);

            // Rimozione del paziente.
            $patient->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Patient deleted successfully."
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Patient doesn't exist."
                ]);
        }
    }
}
