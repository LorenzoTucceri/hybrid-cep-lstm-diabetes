<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\InviteToken;
use App\Models\Client;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showRegistrationForm($token)
    {
        // Recupera il token di invito
        $inviteToken = InviteToken::where('token', $token)->first();

        // Verifica se il token esiste e non è scaduto
        if (!$inviteToken || $inviteToken->expires_at < now()) {
            return redirect()->route('login')->withErrors(['token' => 'Il link di registrazione è scaduto o non valido.']);
        }

        // Trova il cliente tramite l'email del token
        $patient = Patient::where('email', $inviteToken->email)->first();

        if (!$patient) {
            return redirect()->route('login')->withErrors(['email' => 'Paziente non trovato.']);
        }

        // Passa i dati alla vista, inclusi nome, cognome e email
        return view('auth.register', [
            'token' => $token,
            'email' => $inviteToken->email,
            'name' => $patient->name,
            'surname' => $patient->surname,
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'newPassword' => ['required', 'string', 'min:6'],
            'confirmPassword' => ['required', 'string', 'min:6', 'same:newPassword'],
        ]);

        DB::beginTransaction();

        try {
            // Recupera il cliente tramite l'email fornita
            $patient = Patient::where('email', $request->get('email'))->first();

            if (!$patient) {
                return back()->withErrors(["error" => "Nessun paziente trovato con questa email."]);
            }

            // Crea un nuovo utente associato al cliente
            $user = User::create([
                'name' => $patient->name,
                'surname' => $patient->surname,
                'email' => $request->get('email'),
                'role_id' => 2, // Ruolo utente predefinito
                'password' => Hash::make($request->get('newPassword')),
                'patient_id' => $patient->id, // Associa l'utente al cliente trovato
                'created_at' => now(),
            ]);

            $doctor = User::findOrFail($patient->doctor_id);

            Notification::create([
                'user_id' => $doctor->id, // Notifica inviata al dottore
                'title' => "New Patient Registration: $patient->name $patient->surname",
                'message' => "A new patient, $patient->name $patient->surname, has just registered.
                  You can review their details in your dashboard.",
            ]);

            // Rimuove tutti i token associati a questa email
            InviteToken::where('email', $request->get('email'))->delete();

            // Conferma la transazione
            DB::commit();

            // Effettua il login dell'utente appena creato
            $this->guard()->login($user);

            // Redireziona alla rotta 'detailsClient' con l'ID del cliente
            return redirect()->route('index')
                ->with("success", "Registrazione effettuata.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(["error" => "Errore durante la registrazione. Riprova più tardi."]);
        }
    }



    /**
     * Custom guard for registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }
}
