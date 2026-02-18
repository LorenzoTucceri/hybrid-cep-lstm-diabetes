<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CsvController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();










Route::get('/', [App\Http\Controllers\UserController::class, 'root'])->name('root');
Route::get('/register/{token}', [RegisterController::class, 'showRegistrationForm'])->name('register.token');
Route::post('/register/{token}', [RegisterController::class, 'register'])->name('register.token.submit');
Route::post('/resetPassword', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('resetPassword');
Route::view('/register/doctor/view', "auth/register_doctor")->name('register_doctor');
Route::post('/register/doctor/upload', [App\Http\Controllers\UserController::class, 'registerDoctor'])->name('registerDoctor');
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

// 2. Gestisce il POST del form e invia l'email
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// 3. Mostra il form per inserire la NUOVA password (dopo aver cliccato il link nella mail)
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');

// 4. Gestisce il cambio effettivo della password
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware(['auth'])->group(function () {

    Route::view('/userProfile', "userProfile")->name("userProfile");

    Route::post('/update-profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('updatePassword');
    Route::post('/csv/upload', [CsvController::class, 'storeCsv'])->name('uploadCsv');

    Route::get('/csv/view/{csvId}/{patientId}', [CsvController::class, 'viewCsv'])->name('viewCsv');
    Route::get('/csv/view/range/{csvId}/{patientId}', [CsvController::class, 'viewCsvRange'])->name('viewCsvRange');
    Route::post('/csv/delete', [CsvController::class, 'deleteCsv'])->name('deleteCsv');
    Route::post('/patient/{csvId}/{patientId}/download-pdf', [App\Http\Controllers\PatientController::class, 'downloadPDF'])->name('download.pdf');
    Route::get('patient/csv/{patientId}', [App\Http\Controllers\PatientController::class, 'showCsvPatient'])->name('showCsvPatient');
    Route::get('/get-feedback/{csvId}/{doctorId}', [App\Http\Controllers\FeedbackController::class, 'getFeedback']);

    Route::delete('/notifications/delete/{notificationId}', [App\Http\Controllers\NotificationController::class, 'deleteNotification'])->name('deleteNotification');
    Route::delete('/notifications/delete/all/{userId}', [App\Http\Controllers\NotificationController::class, 'deleteAllNotification'])->name('deleteAllNotification');

    Route::post("/saveFeedback", [App\Http\Controllers\FeedbackController::class, 'saveFeedback'])->name("saveFeedback");

    Route::post('/notifications/set-read', [App\Http\Controllers\NotificationController::class, 'setReadNotification'])->name('setReadNotification');
});


Route::middleware(['auth', 'role:1'])->group(function () {
    Route::post('/new-profile', [App\Http\Controllers\UserController::class, 'newProfile'])->name('newProfile');
    Route::get('/searchUser/{id}', [App\Http\Controllers\UserController::class, 'searchUser'])->name('searchUser');
    Route::post('/deleteUser', [App\Http\Controllers\UserController::class, 'deleteUser'])->name('deleteUser');
    Route::post('/updateUser', [App\Http\Controllers\UserController::class, 'updateUser'])->name('updateUser');
    Route::view('/userManagement', "userManagement")->name("userManagement");
});


Route::middleware(['auth', 'role:1|3'])->group(function () {
  Route::view("/patientManagement", "patientManagement")->name("patientManagement");
    Route::post('/deletePatient', [App\Http\Controllers\PatientController::class, 'deletePatient'])->name('deletePatient');
    Route::view("/newPatient", "newPatient")->name("newPatient");
    Route::post("/addPatient", [App\Http\Controllers\PatientController::class, 'addPatient'])->name("addPatient");
    Route::get("/searchPatient/{id}", [App\Http\Controllers\PatientController::class, 'searchPatient'])->name("searchPatient");

    Route::post("/updatePatient", [App\Http\Controllers\PatientController::class, 'updatePatient'])->name("updatePatient");
    Route::get('/send/registration/{patientId}', [\App\Http\Controllers\PatientController::class, 'sendRegistration'])->name('sendRegistration');


    Route::any('/monitor', function () {
        return view('monitor');
    })->name('monitor.view');
});

Route::middleware(['auth', 'role:3'])->group(function () {


});

Route::get('{any}', [App\Http\Controllers\UserController::class, 'index'])->name('index');




