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

    // Notifiche.
    Route::put("/notifications/{id}", [NotificationController::class, "markNotificationAsRead"]);
    Route::delete("/notifications/{id}", [NotificationController::class, "deleteNotification"]);
    Route::delete("/notifications", [NotificationController::class, "deleteNotifications"]);
    Route::get("/notifications", [NotificationController::class, "notifications"]);
});
