<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
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

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $user = Auth::user();
            $user->update($request->only(['name', 'surname', 'email']));
        } catch (QueryException $e) {
            return back()->withErrors(['error' => 'Error updating profile information.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An unexpected error occurred.']);
        }

        return back()->with('success', 'Profile information updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'oldPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:6'],
            'confirmPassword' => ['required', 'string', 'min:6'],
        ]);

        if (!Hash::check($request->input('oldPassword'), Auth::user()->password)) {
            return back()->withErrors(['errorPasswordUpdate' => 'Current password does not match.']);
        }
        if($request->input('newPassword') !== $request->input('confirmPassword')) {
            return back()->withErrors(['errorPasswordUpdate' => 'New password does not match with Confrim password.']);

        }

        try {
            $user = Auth::user();
            $user->password = Hash::make($request->input('newPassword'));
            $user->save();
        } catch (QueryException $e) {
            return back()->withErrors(['errorPasswordUpdate' => 'Error updating password.']);
        } catch (\Exception $e) {
            return back()->withErrors(['errorPasswordUpdate' => 'An unexpected error occurred.']);
        }

        return back()->with('successPasswordUpdate', 'Password updated successfully!');
    }

    public function newProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'in:Admin,Doctor'],
            'newPassword' => ['required', 'string', 'min:6'],
        ]);

        try {
            $role = Role::where('name', $request->input('role'))->firstOrFail();

            if ($request->filled('newPassword')) {
                if ($request->input('newPassword') != $request->input('confirmPassword')) {
                    return back()->withErrors(['error' => 'Password confirmation does not match.']);
                }
            }

            User::create([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'email' => $request->input('email'),
                'role_id' => $role->id,
                'password' => Hash::make($request->input('newPassword')),
                'created_at' => now(),
            ]);
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Role not found.']);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'SQLSTATE[23000]')) {
                return back()->withErrors(['error' => 'Email already in use.']);
            }
            return back()->withErrors(['error' => 'Error adding new user.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An unexpected error occurred.']);
        }

        return back()->with('success', 'User added successfully!');
    }
    public function searchUser($id)
    {
        try {
            $user = User::findOrFail($id);
            return back()->with('updateUser', $user);
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'User not found.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function deleteUser(Request $request)
    {
        $request->validate(['user' => ['required', 'exists:users,id']]);

        try {
            User::findOrFail($request->input('user'))->delete();
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'User not found.']);
        } catch (QueryException $e) {
            return back()->withErrors(['error' => 'Error deleting user.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An unexpected error occurred.']);
        }

        return back()->with('success', 'User deleted successfully!');
    }

    public function updateUser(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'in:Admin,Doctor'],
            'newPassword' => ['nullable', 'string', 'min:6'],
        ]);

        try {
            $user = User::findOrFail($request->input('id'));

            if ($request->filled('newPassword')) {
                if ($request->input('newPassword') != $request->input('confirmPassword')) {
                    return back()->withErrors(['error' => 'Password confirmation does not match.']);
                }
                $user->password = Hash::make($request->input('newPassword'));
            }

            $user->fill($request->only(['name', 'surname', 'email']));

            // Aggiorna il ruolo dell'utente
            $role = Role::where('name', $request->input('role'))->first();
            if ($role) {
                $user->role()->associate($role);
            }

            $user->save();
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'User not found.']);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'SQLSTATE[23000]')) {
                return back()->withErrors(['error' => 'Email already in use.']);
            }
            return back()->withErrors(['error' => 'Error updating user.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An unexpected error occurred.']);
        }

        return back()->with('success', 'User information updated successfully!');
    }
}
