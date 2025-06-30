<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Notification;
use App\Models\Patient;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class CsvController extends Controller {
    public function csvCount() {
        // Calcolo del numero di file CSV.
        $count = File::count();

        return response()->json(
            [
                "success" => true,
                "csv_count" => $count
            ]);
    }

    public function createCsv(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "patient" => "required|exists:patients,id",
            "csv" => "required|file|mimes:csv,txt"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Recupero del file CSV 'vero e proprio'.
        $file = $request->file("csv");

        // Generazione di un nome file univoco per prevenire conflitti.
        $file_name = time() . "_" . $file->getClientOriginalName();

        // Recupero del percorso completo del file.
        $file_path = storage_path("app/patients_csv/" . $request->patient . "/" . $file_name);

        // Salvataggio del file nella directory.
        $file->storeAs("patients_csv/" . $request->patient . "/", $file_name);

        // Aggiunta del file CSV.
        $csv = File::create([
            "patient_id" => $request->patient,
            "csv_file_path" => $file_name,
            "start_time" => null,
            "end_time" => null
        ]);

        // Invio di una richiesta HTTP al servizio Python.
        $client = new Client();
        $response = $client->request("POST", "http://127.0.0.1:5000/process-date", [
            "multipart" => [
                [
                    "name" => "csv_file",
                    "contents" => fopen($file_path, "r")
                ]
            ]
        ]);

        // [200, 300) = La richiesta è andata a buon fine.
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            // Recupero della risposta.
            $data = json_decode($response->getBody()->getContents(), true);

            // Recupero delle date e del GMI.
            $start_date = str_replace("-","/", Carbon::parse($data["first_date"])->format("Y-m-d"));
            $end_date = str_replace("-","/", Carbon::parse($data["last_date"])->format("Y-m-d"));
            $gmi = $data["gmi"];

            // Aggiornamento del file CSV.
            $csv->update([
                "start_time" => $start_date,
                "end_time" => $end_date,
                "gmi" => $gmi
            ]);

            // Notifica al dottore in caso di caricamento effettuato dal paziente.
            if ($request->user()->role->name === "Patient") {
                $patient = Patient::find($request->patient);

                Notification::create([
                    "user_id" => $patient->doctor_id,
                    "title" => "New analysis file from patient {$patient->name} {$patient->surname}",
                    "message" => "A new analysis report is available for the file: {$file_name}, with GMI {$gmi}%.\nTime period: {$start_date} - {$end_date}.",
                    "file_id" => $csv->id
                ]);
            }

            return response()->json(
                [
                    "success" => true,
                    "message" => "CSV file uploaded and processed successfully."
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to process the CSV file."
                ]);
        }
    }

    public function csvs(Request $request) {
        // Validazione dei dati.
        $validator = Validator::make($request->all(), [
            "patient" => "required|exists:patients,id"
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $validator->errors()->first()
                ]);
        }

        // Recupero dei file CSV per ID del paziente.
        $csvs = File::where("patient_id", $request->patient)->orderBy("id")->get();

        return response()->json(
            [
                "success" => true,
                "csvs" => $csvs
            ]);
    }

    public function csv(int $id) {
        // Ricerca del file CSV per ID.
        $csv = File::find($id);

        if ($csv) {
            // Ricerca del file CSV 'vero e proprio'.
            $file_path = storage_path("app/patients_csv/" . $csv->patient_id . "/" . $csv->csv_file_path);

            if (file_exists($file_path)) {
                // Invio di una richiesta HTTP al servizio Python.
                $client = new Client();
                $response = $client->request("POST", "http://127.0.0.1:5000/process-csv", [
                    "multipart" => [
                        [
                            "name" => "csv_file",
                            "contents" => fopen($file_path, "r")
                        ]
                    ]
                ]);

                return response()->json(
                    [
                        "success" => true,
                        "message" => "CSV file found successfully.",
                        "csv" => json_decode($response->getBody(), true)
                    ]);
            }
            else {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "CSV file not found."
                    ]);
            }
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "CSV file doesn't exist."
                ]);
        }
    }

    public function deleteCsv(int $id) {
        // Ricerca del file CSV per ID.
        $csv = File::find($id);

        if ($csv) {
            // Rimozione del file CSV.
            Storage::delete("patients_csv/" . $csv->patient_id . "/" . $csv->csv_file_path);
            $csv->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "CSV file deleted successfully."
                ]);
        }
        else {
            return response()->json(
                [
                    "success" => false,
                    "message" => "CSV file doesn't exist."
                ]);
        }
    }
}
