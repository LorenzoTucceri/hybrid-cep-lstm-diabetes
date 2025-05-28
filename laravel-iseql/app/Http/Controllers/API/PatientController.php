<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
}
