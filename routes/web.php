<?php

use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('patient.dangkykhambenh');
});


Route::post('/scan-cccd', [PatientController::class, 'scan'])->name('patient.scan');
Route::post('/register', [PatientController::class, 'register'])->name('patient.register');
