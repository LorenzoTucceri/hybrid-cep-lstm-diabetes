<?php

namespace App\Http\Controllers;


use App\Models\File;
use App\Models\Notification;
use App\Models\Patient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class CsvController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['markModelReady']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }

    public function root()
    {
        return view('index');
    }


    public function storeCsv(Request $request)
    {
        $request->validate([
            'csv.*' => 'required|file|mimes:csv,txt'
        ]);

        try {
            if ($request->hasFile('csv')) {
                $patient = Patient::find($request->input('patient_id'));

                // FIX: Assicuriamoci che doctor esista (se l'utente è un dottore, l'ID è auth id)
                $doctorId = $request->role == 'Doctor' ? auth()->id() : $request->doctor;

                foreach ($request->file('csv') as $csvFile) {
                    $csvFileName = time() . '_' . $csvFile->getClientOriginalName();
                    $csvFile->storeAs('patients_csv/' . $request->get('patient_id') . '/', $csvFileName);
                    $filePath = storage_path('app/patients_csv/' . $request->get('patient_id') . '/' . $csvFileName);

                    $fileRecord = File::create([
                        'patient_id' => $request->get('patient_id'),
                        'csv_file_path' => $csvFileName,
                        'start_time' => null,
                        'end_time' => null,
                    ]);

                    // --- MODIFICA CRITICA 1: Usiamo 'process-csv' ---
                    // Solo questa rotta avvia il training su Celery e restituisce l'ID sensore
                    $response = Http::attach(
                        'csv_file', fopen($filePath, 'r'), $csvFileName
                    )->post('http://127.0.0.1:5000/process-csv', [
                        // Passiamo parametri opzionali vuoti per evitare errori Python
                        'start_date' => '',
                        'end_date' => '',
                        'patient_id' => $patient->id
                    ]);

                    if ($response->successful()) {
                        $data = $response->json(); // Qui i dati sono in $data

                        // --- MODIFICA CRITICA 2: Chiavi corrette per 'process-csv' ---
                        // process-csv restituisce 'start_time' e 'end_time', non 'first_date'
                        $startDate = Carbon::parse($data['start_time'])->format('Y-m-d');
                        $endDate = Carbon::parse($data['end_time'])->format('Y-m-d');
                        $gmi = $data['gmi'];

                        // Aggiorna record File
                        $fileRecord->update([
                            'start_time' => $startDate,
                            'end_time' => $endDate,
                            'gmi' => $gmi,
                        ]);

                        // Creazione Notifica
                        if ($request->role == "Patient" && $doctorId) {
                            Notification::create([
                                'user_id' => $doctorId,
                                'title' => "New Analysis file from patient $patient->name $patient->surname",
                                'message' => "Report available: $csvFileName (GMI $gmi%).\nPeriod: $startDate - $endDate.",
                                'file_id' => $fileRecord->id,
                            ]);
                        }

                        // --- MODIFICA CRITICA 3: Salvataggio Sensor ID ---
                        // Usiamo $data (non $body) e controlliamo se l'ID è valido
                        if (isset($data['patient_id']) && $data['patient_id'] != 'guest_unknown') {
                            // Salviamo l'ID solo se non ce l'abbiamo già, o se vogliamo sovrascriverlo
                            if (!$patient->sensor_id) {
                                $patient->sensor_id = $data['patient_id'];
                                $patient->save();
                                // Log opzionale per debug
                                Log::info("Sensor ID {$data['patient_id']} collegato al paziente {$patient->id}");
                            }
                        }

                    } else {
                        // Log dell'errore per capire cosa non va su Python
                        Log::error("Flask API Error: " . $response->body());
                        throw new \Exception('Failed to process CSV via Flask.');
                    }
                }
            }

            return redirect()->back()->with(['success' => 'CSV uploaded, processed and Training started!', 'patient' => $patient]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function viewCsv($csvId, $patientId)
    {
        try {
            // Fetch patient data from the database
            $patient = Patient::find($patientId);

            $csv = File::find($csvId);

            // Check if the patient record exists
            if (!$patient) {
                return redirect()->back()->withErrors(['error' => 'Patient not found']);
            }

            // Prepare the CSV file path
            $csvFilePath = storage_path('app/patients_csv/' . $patient->id . '/' . $csv->csv_file_path);

            // Check if the CSV file exists
            if (!file_exists($csvFilePath)) {
                return redirect()->back()->withErrors(['error' => 'CSV file not found']);
            }

            // Send request to Flask application
            $httpClient = new Client();
            try {
                $response = $httpClient->request('POST', 'http://127.0.0.1:5000/process-csv', [
                    'multipart' => [
                        [
                            'name' => 'csv_file',
                            'contents' => fopen($csvFilePath, 'r'),
                        ],
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                // Check if JSON decoding was successful
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()->withErrors(['error' => 'Failed to parse JSON response from the Flask application']);
                }


                $startDate = null;
                $endDate = null;

                return view('patientDetails', compact('patient', 'data', 'csv', 'startDate', 'endDate'));

            } catch (RequestException $e) {
                Log::error('HTTP Request Exception: ' . $e->getMessage());
                return redirect()->back()->withErrors(['error' => 'Failed file CSV probably has not the correct format.']);
            } catch (TransferException $e) {
                Log::error('HTTP Transfer Exception: ' . $e->getMessage());
                return redirect()->back()->withErrors(['error' => 'Network error while communicating with the Flask application']);
            }
        } catch (\Exception $e) {
            Log::error('Unexpected Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred']);
        }
    }


    public function viewCsvRange(Request $request, $csvId, $patientId)
    {
        try {
            // Fetch patient data from the database
            $patient = Patient::find($patientId);
            $csv = File::find($csvId);

            // Check if the patient record exists
            if (!$patient) {
                return redirect()->back()->withErrors(['error' => 'Patient not found']);
            }

            // Prepare the CSV file path
            $csvFilePath = storage_path('app/patients_csv/' . $patient->id . '/' . $csv->csv_file_path);

            // Check if the CSV file exists
            if (!file_exists($csvFilePath)) {
                return redirect()->back()->withErrors(['error' => 'CSV file not found']);
            }

            // Retrieve daterange and parse it if available
            $daterange = $request->query('daterange');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            // Convert dates to the correct format if they are present
            $startDate = $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : null;
            $endDate = $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : null;

            // Send request to Flask application
            $httpClient = new Client();

            try {
                $response = $httpClient->request('POST', 'http://127.0.0.1:5000/process-csv', [
                    'multipart' => [
                        [
                            'name' => 'csv_file',
                            'contents' => fopen($csvFilePath, 'r'),
                        ],
                        [
                            'name' => 'daterange',
                            'contents' => $daterange
                        ],
                        [
                            'name' => 'start_date',
                            'contents' => $startDate
                        ],
                        [
                            'name' => 'end_date',
                            'contents' => $endDate
                        ],
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                // Check if JSON decoding was successful
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()->withErrors(['error' => 'Failed to parse JSON response from the Flask application']);
                }

                return view('patientDetails', compact('patient', 'data', 'csv', 'startDate', 'endDate'));

            } catch (RequestException $e) {
                Log::error('HTTP Request Exception: ' . $e->getMessage());
                return redirect()->back()->withErrors(['error' => 'Failed file CSV probably has not the correct format.']);
            } catch (TransferException $e) {
                Log::error('HTTP Transfer Exception: ' . $e->getMessage());
                return redirect()->back()->withErrors(['error' => 'Network error while communicating with the Flask application']);
            }
        } catch (\Exception $e) {
            Log::error('Unexpected Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred']);
        }
    }

    public function deleteCsv(Request $request)
    {
        try {
            $file = File::find($request->csv_id);

            if (!$file) {
                return redirect()->back()->withErrors(['error' => 'File not found.']);
            }

            Storage::delete('patients_csv/' . $file->patient_id . '/' . $file->csv_file_path);
            $file->delete();

            return redirect()->back()->with('success', 'CSV file deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error deleting CSV: ' . $e->getMessage()]);
        }
    }


    public function markModelReady(Request $request)
    {
        file_put_contents(public_path('debug_python.txt'), "Richiesta arrivata: " . date('H:i:s') . "\nPayload: " . json_encode($request->all()) . "\n", FILE_APPEND);
        // ---------------------

        Log::info("Ci siamo - Inizio funzione markModelReady");


        // 2. TENTATIVO A: Ricerca esatta
        $patient = \App\Models\Patient::findOrFail($request->patient_id);


        // 4. Aggiorniamo se trovato
        if ($patient) {
            $patient->has_trained_model = true;
            $patient->save();

            Log::info("✅ SUCCESSO! Modello attivato per paziente: " . $patient->name . " (ID DB: " . $patient->id . ")");
            return response()->json(['status' => 'success'], 200);
        }

        // 5. Errore se ancora non trovato
        Log::error("❌ FALLIMENTO TOTALE. Nessun paziente trovato per ID: " . $incomingId);
        return response()->json(['status' => 'error', 'message' => 'Patient not found'], 404);
    }


}
