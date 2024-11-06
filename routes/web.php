<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Middleware\AuthenticatedPatient;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckDoctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('patient.dang-ky-kham-benh');
});

Route::get('/dang-ky-tu-xa', function () {
    return view('patient.dang-ky-tu-xa');
});

Route::middleware(CheckDoctor::class)->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'index'])->name('dashboard');
    Route::get('/lich-su-kham-benh', [DoctorController::class, 'history'])->name('history');
    Route::get('/done/{stt}', [PatientController::class, 'done'])->name('doctor.done');
    Route::get('/skip/{patient_visit_id}', [PatientController::class, 'skip'])->name('doctor.skip');
    Route::get('/lich-hen', [PatientController::class, 'lichHen'])->name('doctor.lichHen');
    Route::post('/next-department', [PatientController::class, 'nextDepartment'])->name('doctor.nextDepartment');
    Route::post('/next-department-general', [PatientController::class, 'registerPatientGeneral'])->name('doctor.registerPatientGeneral');
    Route::get('/get-appointments', [PatientController::class, 'getAppointments'])->name('doctor.getAppointments');
    Route::post('/luu-chuan-doan', [PatientController::class, 'luuChuanDoan'])->name('doctor.luuchuandoan');


    Route::get('/stt/{department_id}', [DoctorController::class, 'stt'])->name('doctor.stt');
    Route::get('/get-patient-by-stt/{stt}', [PatientController::class, 'getPatientByStt'])->name('doctor.getPatientByStt');
});

Route::middleware(CheckAdmin::class)->group(function () {
    Route::get('/quan-ly-benh-nhan', [AdminController::class, 'showPatient'])->name('showPatient');
    Route::get('/quan-ly-bac-si', [AdminController::class, 'showDoctor']);
    Route::post('/tao-tai-khoan', [AdminController::class, 'createDoctor'])->name('crateDoctor');
    Route::get('/delete-patient/{id}', [AdminController::class, 'deletePatient'])->name('deletePatient');;
    Route::get('/delete-doctor/{id}', [AdminController::class, 'deleteDoctor'])->name('deleteDoctor');;
});


Route::post('/scan-cccd', [PatientController::class, 'scan'])->name('patient.scan');
Route::post('/patient-register', [PatientController::class, 'register'])->name('patient.process.register');
Route::post('/patient-remote-register', [PatientController::class, 'remoteRegister'])->name('patient.remote.register');

Route::middleware(AuthenticatedPatient::class)->prefix('patients')->as('patient.')->group(function () {
    Route::get('/dashboard', [PatientController::class, 'lichHenPatient'])->name('dashboard');
    // Route::get('/lich-hen', [PatientController::class, 'lichHenPatient'])->name('dashboard');
    Route::get('/lich-su-kham-benh', [PatientController::class, 'khamBenh'])->name('history');
    Route::get('/profile', [PatientController::class, 'showProfile'])->name('show.profile');
    Route::post('/profile', [PatientController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/change-password', [PatientController::class, 'changePassword'])->name('profile.change.password');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/auth-patient.php';
