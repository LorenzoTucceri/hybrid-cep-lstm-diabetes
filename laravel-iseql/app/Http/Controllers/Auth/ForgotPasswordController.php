<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Mostra il form per la richiesta del link di reset.
     */
    public function showLinkRequestForm()
    {
        // Assicurati che il file sia in resources/views/auth/passwords/email.blade.php
        return view('auth.passwords.email');
    }

    /**
     * Invia il link di reset all'utente.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // Rimosso il dd($message) che bloccava l'invio
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    protected function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => trans($response)], 200)
            : back()->with('status', "Ti abbiamo inviato il link per il reset della password via email!");
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($response)],
            ]);
        }

        // Se l'email non esiste nel DB, Laravel solitamente risponde con un errore standard
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => "Non riusciamo a trovare un utente con questo indirizzo email."]);
    }

    public function broker()
    {
        return Password::broker();
    }
}
