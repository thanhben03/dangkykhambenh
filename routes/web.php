<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('patient.dangkykhambenh');
});

Route::get('/dashboard', [DoctorController::class, 'index']);

Route::post('/scan-cccd', [PatientController::class, 'scan'])->name('patient.scan');
Route::post('/register', [PatientController::class, 'register'])->name('patient.register');


Route::get('/done/{stt}', [PatientController::class, 'done'])->name('doctor.done');
Route::get('/lich-hen', [PatientController::class, 'lichHen'])->name('doctor.lichHen');
Route::post('/next-department', [PatientController::class, 'nextDepartment'])->name('doctor.lichHen');
Route::get('/get-appointments', [PatientController::class, 'getAppointments'])->name('doctor.getAppointments');
