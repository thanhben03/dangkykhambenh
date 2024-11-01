<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('patient.dangkykhambenh');
});

Route::group(['middleware' => 'auth'], function () {
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


Route::post('/scan-cccd', [PatientController::class, 'scan'])->name('patient.scan');
Route::post('/patient-register', [PatientController::class, 'register'])->name('patient.register');

Route::get('/a', function () {
    dd(1);
});




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
