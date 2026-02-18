<?php

namespace App\Http\Controllers;


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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\InviteToken;


class PatientController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except("markModelReady");
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


    public function addPatient(Request $request)
    {
        // 1. Controllo manuale se l'email esiste già
        if (Patient::where('email', $request->email)->exists()) {
            return back()->withErrors(['error' => 'Email già presente']);
        }

        // 2. Validazione degli altri campi (ho rimosso 'unique' da email perché controllato sopra)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'birth' => ['required', 'date'],
            'gender' => ['required', 'in:Male,Female'],
            'telephone_number' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'doctor' => ['required', 'exists:users,id'],
        ]);

        DB::beginTransaction();

        try {
            Patient::create([
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

        } catch (\Exception $e) {
            DB::rollback();
            // Fallback di sicurezza nel caso il controllo manuale fallisse per concorrenza
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }

    public function updatePatient(Request $request)
    {
        // 1. Controllo manuale: esiste un ALTRO paziente con questa email?
        $emailExists = Patient::where('email', $request->email)
            ->where('id', '!=', $request->patient_id) // Escludi se stesso
            ->exists();

        if ($emailExists) {
            return back()->withErrors(['error' => 'Email già presente']);
        }

        // 2. Validazione (rimosso Rule::unique perché gestito sopra)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'birth' => ['required', 'date'],
            'gender' => ['required', 'in:Male,Female'],
            'telephone_number' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'doctor' => ['required', 'exists:users,id'],
        ]);

        DB::beginTransaction();

        try {
            $patient = Patient::findOrFail($request->patient_id);

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
                $filePath = 'patients_csv/' . $patientId . '/' . $file->csv_file_path;
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
        // Lista dei grafici
        $chartNames = [
            'glycemicSwingsChart',
            'tooLongChart',
            'tooFrequentChart',
            'tooFrequentTimeSwingsDurationChart',
            'tooFrequentTimeSwingsFrequencyChart',
            'timeSwingTooLongChart'
        ];

        $imagePaths = [];

        foreach ($chartNames as $chartName) {
            $imageData = $request->input($chartName);

            if (Str::startsWith($imageData, 'data:image/png;base64,')) {
                $image = str_replace('data:image/png;base64,', '', $imageData);
                $image = str_replace(' ', '+', $image);

                $imageName = $chartName . '_' . time() . '.png';
                $imagePath = 'charts/' . $imageName;
                $imagePathAbsolute = storage_path('app/public/' . $imagePath);

                Storage::disk('public')->put($imagePath, base64_decode($image));
                $imagePaths[$chartName] = $imagePathAbsolute;
            }
        }

        // Recupera i dati del paziente
        $client = Patient::findOrFail($patientId);
        $csv = File::find($id);

        // Prepara il percorso del file CSV
        $csvFilePath = storage_path('app/patients_csv/' . $patientId . '/' . $csv->csv_file_path);

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
        $time_swing_too_long = $request->time_swing_too_long;

        // Genera il PDF
        $pdf = PDF::loadView('pdf.patient-details', compact(
            'client',
            'data',
            'imagePaths',
            'detail',
            'summary',
            'time_swing',
            'too_long',
            'too_frequent',
            'too_frequent_time_swing',
            'time_swing_too_long'
        ));

        // Cancella le immagini temporanee dopo aver generato il PDF
        foreach ($imagePaths as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

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
        Log::debug("Inizio processo invio registrazione per Patient ID: " . $patientId);

        try {
            $patient = \App\Models\Patient::find($patientId);
            $user = Auth::user();

            if (!$patient) {
                Log::warning("Paziente non trovato nel database.");
                return back()->withErrors(['error' => 'Paziente non trovato.']);
            }

            Log::info("Paziente trovato: " . $patient->email);

            $existingUser = \App\Models\User::where('email', $patient->email)->first();
            if ($existingUser) {
                Log::warning("Email già registrata come utente: " . $patient->email);
                return back()->withErrors(['error' => "Esiste già un utente registrato con l'email {$patient->email}."]);
            }

            $token = Str::random(32);
            $expires_at = now()->addHours(48);

            Log::debug("Generazione Token: " . $token);

            // Debug creazione Token
            $invite = InviteToken::create([
                'email' => $patient->email,
                'token' => $token,
                'expires_at' => $expires_at,
            ]);

            if ($invite) {
                Log::info("Record InviteToken creato con successo nel database.");
            }

            $link = route('register.token', ['token' => $token]);
            Log::debug("Link generato: " . $link);

            // PROVA INVIO MAIL
            Log::info("Tentativo invio email via " . config('mail.mailers.smtp.host', 'default mailer'));

            Mail::to($patient->email)->send(new \App\Mail\InvitoIscrizione($patient, $user, $link));

            Log::info("Email inviata correttamente senza eccezioni.");

            return back()->with('success', "Email inviata con successo a {$patient->name} {$patient->surname}!");

        } catch (\Exception $e) {
            Log::error("ERRORE CRITICO INVIO REGISTRAZIONE: " . $e->getMessage());
            Log::error($e->getTraceAsString()); // Questo logga tutto il percorso dell'errore

            return back()->withErrors([
                'error' => 'Errore tecnico: ' . $e->getMessage(),
            ]);
        }
    }
}
