<?php

namespace App\Http\Controllers;


use App\Models\Feedback;
use App\Models\File;
use App\Models\Notification;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class FeedbackController extends Controller
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


    public function saveFeedback(Request $request)
    {
        $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'file_id' => ['required', 'exists:files,id'],
            'feedbackDoctor' => ['nullable', 'string'],
            'feedbackPatient' => ['nullable', 'string'],
        ]);

        // Verifica che l'utente sia autenticato
        if (!auth()->check()) {
            return back()->withErrors(['error' => 'User not authenticated']);
        }

        $user = auth()->user();

        DB::beginTransaction();

        try {
            if ($user->role->name == 'Doctor') {
                Feedback::updateOrCreate(
                    [
                        'doctor_id' => $request->doctor_id,
                        'file_id' => $request->file_id,
                    ],
                    [
                        'message_doctor' => $request->feedbackDoctor,
                    ]
                );
            } else {
                Feedback::updateOrCreate(
                    [
                        'doctor_id' => $request->doctor_id,
                        'file_id' => $request->file_id,
                    ],
                    [
                        'message_patient' => $request->feedbackPatient,
                    ]
                );
            }

            // Recupera il file e gli utenti
            $csv = File::findOrFail($request->file_id);
            $patient = $csv->patient;
            $doctor = User::findOrFail($request->doctor_id);

            if (!$doctor) {
                return back()->withErrors(['error' => "Doctor with ID $request->doctor_id does not exist."]);
            }

            if (!$patient) {
                return back()->withErrors(['error' => "Patient linked to file ID $request->file_id does not exist."]);
            }


            if ($user->role->name == 'Patient') {
                Notification::create([
                    'user_id' => $doctor->id,
                    'title' => "New Feedback from patient $patient->name $patient->surname",
                    'message' => "A new feedback report is available for the file: $csv->csv_file_path.\nTime period: $csv->start_time - $csv->end_time.",
                ]);
            } else {
                $patient_user= User::where('patient_id', $patient->id)->first();
                Notification::create([
                    'user_id' => $patient_user->id,
                    'title' => "New Feedback from Dr. $doctor->name $doctor->surname",
                    'message' => "A new feedback report is available for the file: $csv->csv_file_path.\nTime period: $csv->start_time - $csv->end_time.",
                ]);
            }

            DB::commit();

            return back()->with('success', 'Feedback saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => "Error, feedback save failed! " . $e->getMessage()]);
        }
    }
    public function getFeedback($csvId, $doctorId)
    {
        $feedback = Feedback::where('file_id', $csvId)
            ->where('doctor_id', $doctorId)
            ->first();

        return response()->json([
            'message_doctor' => $feedback ? $feedback->message_doctor : null,
            'message_patient' => $feedback ? $feedback->message_patient : null
        ]);
    }


}
