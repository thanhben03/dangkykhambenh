<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('patient.dangkykhambenh');
});

Route::get('/dashboard', [DoctorController::class, 'index'])->name('dashboard');

Route::post('/scan-cccd', [PatientController::class, 'scan'])->name('patient.scan');
Route::post('/register', [PatientController::class, 'register'])->name('patient.register');


Route::get('/done/{stt}', [PatientController::class, 'done'])->name('doctor.done');
Route::get('/lich-hen', [PatientController::class, 'lichHen'])->name('doctor.lichHen');
Route::post('/next-department', [PatientController::class, 'nextDepartment'])->name('doctor.nextDepartment');
Route::get('/get-appointments', [PatientController::class, 'getAppointments'])->name('doctor.getAppointments');
Route::post('/luu-chuan-doan', [PatientController::class, 'luuChuanDoan'])->name('doctor.luuchuandoan');
