<?php

namespace App\Http\Controllers;

use App\Mail\InviteToken;
use App\Mail\InvitoIscrizione;
use App\Models\File;
use App\Models\Patient;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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

    public function updatePatient(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:patients,email,' . $request->client_id],  // Validazione email unica, ignorando il paziente corrente
            'birth' => ['required', 'date'],  // Validazione per una data corretta
            'gender' => ['required', 'in:Male,Female'],
            'telephone_number' => ['nullable', 'string', 'max:255'],  // Opzionale
            'address' => ['required', 'string', 'max:255'],  // Obbligatorio
            'doctor' => ['required', 'exists:users,id'],  // Validazione per il doctor
        ]);

        DB::beginTransaction();

        try {
            // Recupera il paziente tramite l'ID (client_id) dalla richiesta
            $patient = Patient::findOrFail($request->client_id);

            // Aggiorna il paziente con i dati dalla richiesta
            $patient->update([
                'name' => $request->get('name'),
                'surname' => $request->get('surname'),
                'email' => $request->get('email'),
                'birth' => $request->get('birth'),
                'gender' => $request->get('gender'),
                'telephone_number' => $request->get('telephone_number'),
                'address' => $request->get('address'),
                'doctor_id' => $request->get('doctor'),
            ]);

            DB::commit();

            return back()->with('success', 'Patient information updated successfully!');
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Patient not found.']);
        } catch (QueryException $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error updating patient: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }

    public function addPatient(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:patients,email'],  // Validazione email unica
            'birth' => ['required', 'date'],  // Validazione per una data corretta
            'gender' => ['required', 'in:Male,Female'],
            'telephone_number' => ['nullable', 'string', 'max:255'],  // Opzionale
            'address' => ['required', 'string', 'max:255'],  // Obbligatorio
            'doctor' => ['required', 'exists:users,id'],  // Validazione per il doctor
        ]);

        DB::beginTransaction();

        try {
            // Creazione del paziente
            $patient = Patient::create([
                'name' => $request->get('name'),
                'surname' => $request->get('surname'),
                'email' => $request->get('email'),
                'doctor_id' => $request->get('doctor'),
                'birth' => $request->get('birth'),
                'telephone_number' => $request->get('telephone_number'),
                'address' => $request->get('address'),
                'gender' => $request->get('gender'),
            ]);

            DB::commit();

            return back()->with('success', 'The patient has been successfully added!');
        } catch (QueryException $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error adding patient: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }



    public function deletePatient(Request $request)
    {
        $request->validate([
            'patient' => ['required', 'exists:patients,id'],
        ]);

        $patientId = $request->patient;

        try {
            // Recupera il paziente
            $patient = Patient::findOrFail($patientId);

            // Recupera i file associati al paziente
            $files = File::where('patient_id', $patientId)->get();
            foreach ($files as $file) {
                // Elimina il file fisico dal sistema
                $filePath = 'patients_csv/'.$patientId.'/'. $file->csv_file_path;
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
                // Elimina il record del file dal database
                $file->delete();
            }


            // Elimina la directory se vuota
            $directory = 'patients_csv/' . $patientId;
            Storage::deleteDirectory($directory);


            $patient->delete();

            return back()->with('success', 'Patient deleted successfully!');
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Patient not found.']);
        } catch (QueryException $e) {
            return back()->withErrors(['error' => 'Error deleting patient: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }
    public function downloadPDF(Request $request, $id, $patientId)
    {
        // Recupera i dati del paziente
        $client = Patient::findOrFail($patientId);
        $csv = File::find($id);

        // Prepara il percorso del file CSV
        $csvFilePath = storage_path('app/patients_csv/'.$patientId.'/'. $csv->csv_file_path);

        // Controlla se il file CSV esiste
        if (!file_exists($csvFilePath)) {
            abort(404, 'CSV file not found');
        }

        // Recupera i parametri opzionali start_date e end_date dalla richiesta
        $startDate = request('start_date');
        $endDate = request('end_date');

        // Crea l'array per inviare i parametri all'API Flask
        $multipart = [
            [
                'name' => 'csv_file',
                'contents' => fopen($csvFilePath, 'r'),
            ]
        ];

        // Aggiungi start_date e end_date se sono presenti
        if ($startDate) {
            $multipart[] = [
                'name' => 'start_date',
                'contents' => $startDate,
            ];
        }

        if ($endDate) {
            $multipart[] = [
                'name' => 'end_date',
                'contents' => $endDate,
            ];
        }

        // Invia la richiesta all'applicazione Flask
        $httpClient = new Client();
        $response = $httpClient->request('POST', 'http://127.0.0.1:5000/process-csv', [
            'multipart' => $multipart,
        ]);

        // Decodifica la risposta JSON dall'API Flask
        $data = json_decode($response->getBody(), true);

        $detail = $request->detail;
        $summary = $request->summary;
        $time_swing = $request->time_swing;
        $too_long = $request->too_long;
        $too_frequent = $request->too_frequent;
        $too_frequent_time_swing = $request->too_frequent_time_swing;
        $time_swing_too_long= $request->time_swing_too_long;

        // GESTIONE IMMAGINI - INIZIO
        $images = [];
        $chartNames = [
            'glycemicSwingsChart',
            'tooLongGlucoseAnomaliesChart',
            'tooFrequentGlucoseAnomaliesChart',
            'tooFrequentTimeSwingsDurationChart',
            'tooFrequentTimeSwingsFrequencyChart',
            'timeSwingTooLongGlucoseAnomaliesChart'
        ];

        // Crea una directory temporanea unica per questo PDF
        $tempDir = 'pdf_charts/'.time();
        Storage::disk('public')->makeDirectory($tempDir);

        foreach ($chartNames as $chartName) {
            if ($request->has($chartName)) {
                try {
                    $imageData = $request->input($chartName);

                    // Estrai solo la parte base64
                    $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);

                    // Decodifica l'immagine
                    $decodedImage = base64_decode($base64Image);

                    if ($decodedImage === false) {
                        throw new \Exception("Base64 decoding failed for $chartName");
                    }

                    // Salva l'immagine
                    $imagePath = "$tempDir/$chartName.png";
                    Storage::disk('public')->put($imagePath, $decodedImage);

                    // Verifica che l'immagine sia stata salvata
                    if (!Storage::disk('public')->exists($imagePath)) {
                        throw new \Exception("Failed to save image $chartName");
                    }

                    // Aggiungi il percorso assoluto all'array images
                    $images[$chartName] = storage_path("app/public/$imagePath");

                } catch (\Exception $e) {
                    // Log dell'errore ma continua con le altre immagini
                    \Log::error("Error processing $chartName: ".$e->getMessage());
                    continue;
                }
            }
        }
        // GESTIONE IMMAGINI - FINE

        $pdf = PDF::loadView('pdf.patient-details', compact('client', 'data','images', 'detail', 'summary', 'time_swing','too_long', 'too_frequent', 'too_frequent_time_swing', 'time_swing_too_long'));

        // Cancella le immagini temporanee dopo aver generato il PDF
        Storage::disk('public')->deleteDirectory($tempDir);

        return $pdf->download('patient-details-' . $patientId . '.pdf');
    }

    public function showCsvPatient($patientId)
    {
        try {
            #dd($patientId);
            $patient = Patient::find($patientId);
            $files = File::where("patient_id", $patientId)->orderBy("id")->get();

            return view('csvPatient', compact('files', 'patient'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Errore durante il recupero dei file CSV: ' . $e->getMessage()]);
        }
    }

    public function sendRegistration($patientId)
    {
        try {
            // Recupera i dati del cliente e dell'utente attualmente loggato
            $patient = \App\Models\Patient::find($patientId);
            $user = Auth::user();

            // Verifica se il cliente esiste
            if (!$patient) {
                return back()->withErrors([
                    'error' => 'Paziente non trovato.',
                ]);
            }

            // Controlla se esiste già un utente con la stessa email
            $existingUser = User::where('email', $patient->email)->first();
            if ($existingUser) {
                return back()->withErrors([
                    'error' => "Esiste già un utente registrato con l'email {$patient->email}.",
                ]);
            }

            // Genera un token univoco per l'invito
            $token = Str::random(32);
            $expires_at = now()->addHours(48); // Il link di registrazione scade dopo 48 ore

            // Salva il token nel database
            InviteToken::create([
                'email' => $patient->email,
                'token' => $token,
                'expires_at' => $expires_at,
            ]);

            // Crea il link di registrazione con il token
            $link = route('register.token', ['token' => $token]);

            // Invia l'email con il link di registrazione
            Mail::to($patient->email)->send(new InvitoIscrizione($patient, $user, $link));

            // Aggiungi nome e cognome del cliente nel messaggio di successo
            return back()->with([
                'success' => "Email inviata con successo a {$patient->name} {$patient->surname}!",
            ]);

        } catch (\Exception $e) {
            // Registra l'errore per il debug

            // Ritorna alla pagina precedente con un messaggio di errore
            return back()->withErrors([
                'error' => 'Errore durante l\'invio dell\'email. Riprova più tardi.',
            ]);
        }
    }



}
