<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed $roles  String of roles, e.g., '1' or '1,2'
     * @return mixed
     */

    public function handle($request, Closure $next, $roles)
    {
        if (!Auth::check()) {
            return redirect('/login'); // Redirect se non autenticato
        }

        // Ottieni i ruoli consentiti come array di interi
        $allowedRoles = array_map('intval', explode('|', $roles));


        if (!in_array((int) Auth::user()->role_id, $allowedRoles)) {
            return redirect('/')->with('error', 'Accesso non autorizzato.');
        }

        return $next($request);
    }}
