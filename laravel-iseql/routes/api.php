<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CsvController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\UserController;
use App\Models\Patient;
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

// Login (Davide).
Route::post("/login", [AuthController::class, "login"]);

// Recupero password (Davide).
Route::post("/forgot-password", [AuthController::class, "forgotPassword"]);

// Registrazione (Davide).
Route::post("/register", [AuthController::class, "register"]);

// Machine Learning (Lorenzo).
Route::post("/internal/model-ready", [App\Http\Controllers\CsvController::class, "markModelReady"])->name("internal.modelReady");

// Rotte da proteggere.
Route::middleware(["auth:sanctum"])->group(function () {
    // Logout (Davide).
    Route::post("/logout", [AuthController::class, "logout"]);

    // Dashboard e profilo (Davide).
    Route::get("/users/me/stats", [UserController::class, "stats"]);
    Route::put("/users/me/profile", [UserController::class, "updateProfile"]);
    Route::put("/users/me/profile/password", [UserController::class, "updatePassword"]);

    // Operatori (Davide).
    Route::post("/users", [UserController::class, "createUser"]);
    Route::get("/users", [UserController::class, "users"]);
    Route::get("/users/doctors", [UserController::class, "doctors"]);
    Route::put("/users/{id}", [UserController::class, "updateUser"]);
    Route::delete("/users/{id}", [UserController::class, "deleteUser"]);

    // Pazienti (Davide).
    Route::post("/patients", [PatientController::class, "createPatient"]);
    Route::post("/patients/{id}/invite", [PatientController::class, "invitePatient"]);
    Route::get("/patients", [PatientController::class, "patients"]);
    Route::put("/patients/{id}", [PatientController::class, "updatePatient"]);
    Route::delete("/patients/{id}", [PatientController::class, "deletePatient"]);

    // File CSV (Davide).
    Route::post("/csv", [CsvController::class, "createCsv"]);
    Route::get("/csv", [CsvController::class, "csvs"]);
    Route::get("/csv/{id}", [CsvController::class, "csv"]);
    Route::delete("/csv/{id}", [CsvController::class, "deleteCsv"]);

    // Feedback (Davide).
    Route::post("/feedbacks", [FeedbackController::class, "saveFeedback"]);
    Route::get("/feedbacks/by-file/{id}", [FeedbackController::class, "feedback"]);

    // Notifiche (Davide).
    Route::get("/notifications", [NotificationController::class, "notifications"]);
    Route::put("/notifications/{id}", [NotificationController::class, "markNotificationAsRead"]);
    Route::delete("/notifications", [NotificationController::class, "deleteNotifications"]);
    Route::delete("/notifications/{id}", [NotificationController::class, "deleteNotification"]);


 
});
