<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\File;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class FeedbackController extends Controller {
    public function feedbackCount(Request $request) {
        // Calcolo del numero di feedback.
        $count = Feedback::whereHas("file", function ($query) use ($request) {
            $query->where("patient_id", $request->user()->patient_id);
        })->count();

        return response()->json(
            [
                "success" => true,
                "feedback_count" => $count
            ]);
    }

    public function saveFeedback(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "message_doctor" => "nullable|string",
            "message_patient" => "nullable|string",
            "file_id" => "required|exists:files,id"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Ricerca del file CSV per ID.
        $csv = File::find($request->file_id);

        // Aggiornamento/creazione del feedback e notifica in base al ruolo.
        if ($request->user()->role->name === "Doctor") {
            Feedback::updateOrCreate([
                "file_id" => $csv->id,
                "doctor_id" => $request->user()->id
            ], [
                "message_doctor" => $request->message_doctor
            ]);

            $patient_user = User::where("patient_id", $csv->patient->id)->first();
            if ($patient_user) {
                Notification::create([
                    "user_id" => $patient_user->id,
                    "title" => "New Feedback from Dr. {$request->user()->name} {$request->user()->surname}",
                    "message" => "A new feedback report is available for the file: {$csv->csv_file_path}.\nTime period: {$csv->start_time} - {$csv->end_time}.",
                ]);
            }
        }
        else {
            Feedback::updateOrCreate([
                "file_id" => $csv->id,
                "doctor_id" => $request->user()->patient->doctor_id
            ], [
                "message_patient" => $request->message_patient
            ]);

            Notification::create([
                "user_id" => $request->user()->patient->doctor_id,
                "title" => "New Feedback from patient {$request->user()->patient->name} {$request->user()->patient->surname}",
                "message" => "A new feedback report is available for the file: {$csv->csv_file_path}.\nTime period: {$csv->start_time} - {$csv->end_time}.",
            ]);
        }

        return response()->json(
            [
                "success" => true,
                "message" => "Feedback saved successfully."
            ]);
    }

    public function feedback(int $id) {
        // Ricerca del feedback per ID del file.
        $feedback = Feedback::where("file_id", $id)->first();

        if ($feedback) {
            return response()->json(
                [
                    "success" => true,
                    "message" => "Feedback found successfully.",
                    "feedback" => $feedback
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Feedback not found."
                ]);
        }
    }
}
