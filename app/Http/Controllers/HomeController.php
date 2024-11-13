<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PatientVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function pendingScreen () {
        $department = Department::query()->where('id', auth()->user()->department_id)->first();
        $patientVisits = PatientVisit::query()
        ->join('patients', 'patients.id', '=', 'patient_visits.patient_id')
        // ->select('patients.name, patient_visits.stt')
        ->where('patient_visits.department_id', $department->id)
        ->whereDate('patient_visits.arrival_time', Carbon::toDay())
        ->where('status', 0)
        ->orderBy('arrival_time', 'asc')
        ->take(10)
        ->get()
        ->toArray();
        
        
        
        return view('man-hinh-cho', compact('department', 'patientVisits'));
    }

}
