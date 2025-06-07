<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
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
                // Effettuiamo la richiesta HTTP al servizio Python.
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
