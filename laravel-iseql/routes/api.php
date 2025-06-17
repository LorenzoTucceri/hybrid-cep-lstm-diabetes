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

// Rotte da proteggere.
Route::middleware(["auth:sanctum"])->group(function () {
    // Logout.
    Route::post("/logout", [LoginController::class, "logout"]);

    // Contatori (Dashboard).
    Route::get("/users/count", [UserController::class, "userCount"]);
    Route::get("/patients/count", [PatientController::class, "patientCount"]);
    Route::get("/csv/count", [CsvController::class, "csvCount"]);
    Route::get("/feedbacks/count", [FeedbackController::class, "feedbackCount"]);

    // Operatori.
    Route::post("/users", [UserController::class, "createUser"]);
    Route::get("/users", [UserController::class, "users"]);
    Route::get("/users/role/{id}", [UserController::class, "usersByRole"]);
    Route::put("/users/{id}", [UserController::class, "updateUser"]);
    Route::delete("/users/{id}", [UserController::class, "deleteUser"]);

    // Pazienti.
    Route::post("/patients", [PatientController::class, "createPatient"]);
    Route::post("/patients/{id}/invite", [PatientController::class, "invitePatient"]);
    Route::get("/patients", [PatientController::class, "patients"]);
    Route::get("/patients/{id}", [PatientController::class, "patient"]);
    Route::put("/patients/{id}", [PatientController::class, "updatePatient"]);
    Route::delete("/patients/{id}", [PatientController::class, "deletePatient"]);

    // File CSV.
    Route::post("/csv", [CsvController::class, "createCsv"]);
    Route::get("/csv", [CsvController::class, "csvs"]);
    Route::get("/csv/{id}", [CsvController::class, "csv"]);
    Route::delete("/csv/{id}", [CsvController::class, "deleteCsv"]);

    // Notifiche.
    Route::get("/notifications", [NotificationController::class, "notifications"]);
    Route::put("/notifications/{id}", [NotificationController::class, "markNotificationAsRead"]);
    Route::delete("/notifications", [NotificationController::class, "deleteNotifications"]);
    Route::delete("/notifications/{id}", [NotificationController::class, "deleteNotification"]);

    // Profilo.
    Route::put("/users/{id}/profile", [UserController::class, "updateProfile"]);
    Route::put("/users/{id}/profile/password", [UserController::class, "updatePassword"]);
});
