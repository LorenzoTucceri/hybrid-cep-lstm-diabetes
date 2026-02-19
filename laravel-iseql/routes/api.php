<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\CsvController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Login.
Route::post("/login", [LoginController::class, "login"]);

Route::post('/internal/model-ready', [App\Http\Controllers\CsvController::class, 'markModelReady'])->name('internal.modelReady');
// Rotte da proteggere.
Route::middleware(["auth:sanctum"])->group(function () {
    // Logout.
    Route::post("/logout", [LoginController::class, "logout"]);

    // Dashboard e profilo.
    Route::get("/users/me/stats", [UserController::class, "stats"]);
    Route::put("/users/me/profile", [UserController::class, "updateProfile"]);
    Route::put("/users/me/profile/password", [UserController::class, "updatePassword"]);

    // Operatori.
    Route::post("/users", [UserController::class, "createUser"]);
    Route::get("/users", [UserController::class, "users"]);
    Route::get("/users/doctors", [UserController::class, "doctors"]);
    Route::put("/users/{id}", [UserController::class, "updateUser"]);
    Route::delete("/users/{id}", [UserController::class, "deleteUser"]);

    // Pazienti.
    Route::post("/patients", [PatientController::class, "createPatient"]);
    Route::post("/patients/{id}/invite", [PatientController::class, "invitePatient"]);
    Route::get("/patients", [PatientController::class, "patients"]);
    Route::put("/patients/{id}", [PatientController::class, "updatePatient"]);
    Route::delete("/patients/{id}", [PatientController::class, "deletePatient"]);

    // File CSV.
    Route::post("/csv", [CsvController::class, "createCsv"]);
    Route::get("/csv", [CsvController::class, "csvs"]);
    Route::get("/csv/{id}", [CsvController::class, "csv"]);
    Route::delete("/csv/{id}", [CsvController::class, "deleteCsv"]);

    // Feedback.
    Route::post("/feedbacks", [FeedbackController::class, "saveFeedback"]);
    Route::get("/feedbacks/by-file/{id}", [FeedbackController::class, "feedback"]);

    // Notifiche.
    Route::get("/notifications", [NotificationController::class, "notifications"]);
    Route::put("/notifications/{id}", [NotificationController::class, "markNotificationAsRead"]);
    Route::delete("/notifications", [NotificationController::class, "deleteNotifications"]);
    Route::delete("/notifications/{id}", [NotificationController::class, "deleteNotification"]);

    Route::get('/check-model-status/{id}', function($id) {
        $patient = \App\Models\Patient::find($id);
        return response()->json([
            'ready' => (bool)$patient->has_trained_model,
            'sensor_id' => $patient->sensor_id
        ]);
    });
    Route::get('/check-model-status/{id}', function($id) {
        // Qui $id è l'ID di Laravel (es. 1)
        $patient = \App\Models\Patient::find($id);
        return response()->json([
            'ready' => (bool)$patient->has_trained_model,
            'sensor_id' => $patient->sensor_id // utile per debug
        ]);
    });
});
