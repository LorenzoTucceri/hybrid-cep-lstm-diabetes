<?php

namespace App\Http\Controllers;


use App\Models\File;
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


    public function storeCsv(Request $request)
    {
        $request->validate([
            'csv.*' => 'required|file|mimes:csv,txt'
        ]);

        try {
            if ($request->hasFile('csv')) {
                $patient = Patient::find($request->input('patient_id'));

                foreach ($request->file('csv') as $csvFile) {
                    // Generate a unique file name to avoid conflicts
                    $csvFileName = time() . '_' . $csvFile->getClientOriginalName();

                    // Save the file in the directory
                    $csvFile->storeAs('patients_csv/' . $request->get('patient_id') . '/', $csvFileName);

                    // Complete file path
                    $filePath = storage_path('app/patients_csv/' . $request->get('patient_id') . '/' . $csvFileName);

                    // Create a record in the File table
                    $fileRecord = File::create([
                        'patient_id' => $request->get('patient_id'), // Associate the CSV with a patient
                        'csv_file_path' => $csvFileName,
                        'start_time' => null, // To be updated
                        'end_time' => null,   // To be updated
                    ]);

                    // Call the Flask API to process the dates
                    $response = Http::attach(
                        'csv_file', fopen($filePath, 'r'), $csvFileName
                    )->post('http://127.0.0.1:5000/process-date');

                    if ($response->successful()) {
                        $data = $response->json();
                        $startDate = Carbon::parse($data['first_date'])->format('Y-m-d');
                        $endDate = Carbon::parse($data['last_date'])->format('Y-m-d');

                        // Update the File record with the start_time and end_time
                        $startDate = str_replace("-","/",$startDate);
                        $endDate = str_replace("-","/",$endDate);


                        $fileRecord->update([
                            'start_time' => $startDate,
                            'end_time' => $endDate,
                        ]);
                    } else {
                        // Handle the error if Flask API request fails
                        throw new \Exception('Failed to process the CSV file. Flask API response: ' . $response->body());
                    }
                }
            }

            return redirect()->back()->with(['success' => 'CSV file(s) uploaded and processed successfully.', 'patient' => $patient]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error uploading or processing CSV: ' . $e->getMessage()]);
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
            $csvFilePath = storage_path('app/patients_csv/'.$patient->id.'/'. $csv->csv_file_path);

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
                            'name'     => 'csv_file',
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
            $csvFilePath = storage_path('app/patients_csv/'.$patient->id.'/'. $csv->csv_file_path);

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
                            'name'     => 'csv_file',
                            'contents' => fopen($csvFilePath, 'r'),
                        ],
                        [
                            'name'     => 'daterange',
                            'contents' => $daterange
                        ],
                        [
                            'name'     => 'start_date',
                            'contents' => $startDate
                        ],
                        [
                            'name'     => 'end_date',
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

            Storage::delete('patients_csv/'.$file->patient_id.'/'. $file->csv_file_path);
            $file->delete();

            return redirect()->back()->with('success', 'CSV file deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error deleting CSV: ' . $e->getMessage()]);
        }
    }




}
