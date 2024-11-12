<?php

namespace App\Http\Controllers;

use App\Events\PatientRegistered;
use App\Events\StandbyScreenEvent;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Models\PatientVisit;
use App\Models\CurrentPatient;
use App\Http\Resources\PatientPendingResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\PrintInfoResource;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{


    public function stt($department_id)
    {
        $patientVisit = PatientVisit::query()
            ->where('department_id', $department_id)
            ->where('status', 0)
            ->whereDate('created_at', Carbon::today())
            ->first();

        dd($patientVisit);
    }

    public function printInfoPatient($id)
    {
        $patient = Patient::find($id);
        return view('doctor.mau-phieu', compact('patient'));
    }

    public function index(Request $request)
    {
        $result = PatientVisit::query()
            ->join('patients', 'patient_visits.patient_id', '=', 'patients.id')
            ->select('patients.*', 'patient_visits.*', 'patient_visits.stt as stt')
            ->when($request->name, function ($query) use ($request) {
                return $query->where('patients.name', 'LIKE', '%' . $request->name . '%');
            })
            ->when($request->stt, function ($query) use ($request) {
                return $query->where('patient_visits.stt', $request->stt);
            })
            ->when($request->cccd, function ($query) use ($request) {
                return $query->where('patients.nic', 'LIKE', '%' . $request->cccd . '%');
            })
            ->where('department_id', '=', \auth()->user()->department_id ?? 1)
            ->where('status', 0)
            ->whereDate('patient_visits.arrival_time', Carbon::today())
            ->orderBy('patient_visits.arrival_time')
            ->get();

        $result = PatientPendingResource::make($result)->resolve();
        return view('doctor.dashboard', [
            'patients' => $result
        ]);
    }

    public function history(Request $request)
    {
        $result = Patient::query()
            ->when($request->name, function ($query) use ($request) {
                return $query->where('patients.name', 'LIKE', '%' . $request->name . '%');
            })
            ->when($request->stt, function ($query) use ($request) {
                return $query->where('patient_visits.stt', $request->stt);
            })
            ->when($request->cccd, function ($query) use ($request) {
                return $query->where('patients.nic', 'LIKE', '%' . $request->cccd . '%');
            })
            ->orderBy('created_at')
            ->get();

        $result = PatientResource::make($result)->resolve();
        return view('doctor.lich-su-kham-benh', [
            'patients' => $result
        ]);
    }

    public function getCurrentPatientVisit()
    {
        $currentPatient = CurrentPatient::query()
            ->where('department_id', \auth()->user()->department_id ?? 1)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$currentPatient) {
            $currentPatient = CurrentPatient::create([
                'department_id' => \auth()->user()->department_id ?? 1,
                'stt' => 1,
                'created_at' => now(),
                // Các giá trị khác cần thiết
            ]);
        }

        return $currentPatient;
    }

    // Khi bac si an nut hoan thanh
    public function done($stt)
    {
        PatientVisit::query()->where('stt', '=', $stt)->update(['status' => 1]);

        $currentPatient = CurrentPatient::query()
            ->where('department_id', \auth()->user()->department_id ?? 1)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();

        $currentSTT = intval($currentPatient->stt);
        $currentPatient->stt = $currentSTT + 1;
        $currentPatient->save();

        broadcast(new StandbyScreenEvent())->toOthers();



        return response()->json([
            'msg' => 'Ok'
        ]);
    }

    public function nextDepartment(Request $request)
    {
        $stt = $request->stt;
        $trieu_chung = $request->trieu_chung;
        $department_id = $request->department_id;

        $patientVisit = PatientVisit::query()
            ->where('stt', '=', $stt)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();

        $patient = Patient::query()->where('id', '=', $patientVisit->patient_id)->first();

        $this->done($stt);
        $this->registerPatientVisit($patient->id, $trieu_chung, $department_id);
    }

    public function registerPatientVisit($patient_id, $trieu_chung, $department_id, $stt = null)
    {
        PatientVisit::query()->create([
            'patient_id' => $patient_id,
            'stt' => $stt ?? PatientVisit::query()->orderBy('created_at', 'desc')->first()->stt + 1,
            'department_id' => $department_id,
            'trieu_chung' => $trieu_chung,
        ]);
    }

    public function lichHen()
    {
        $result = PatientVisit::query()
            ->join('patients', 'patient_visits.patient_id', '=', 'patients.id')
            ->select('patients.*', 'patient_visits.*', 'patient_visits.stt as stt')
            ->where('department_id', '=', \auth()->user()->department_id ?? 1)
            ->where('status', 0)
            ->whereDate('patient_visits.created_at', '=', Carbon::tomorrow())
            ->orderBy('patient_visits.created_at')
            ->get();
        return view('doctor.lich-hen', [
            'appointments' => $result
        ]);
    }

    public function getAppointments(Request $request)
    {
        $date = $request->query('date') ?? Carbon::tomorrow();


        $result = PatientVisit::query()
            ->join('patients', 'patient_visits.patient_id', '=', 'patients.id')
            ->select('patients.*', 'patient_visits.*', 'patient_visits.stt as stt')
            ->where('department_id', '=', \auth()->user()->department_id ?? 1)
            ->where('status', 0)
            ->whereDate('patient_visits.created_at', '=', $date)
            ->orderBy('patient_visits.created_at')
            ->get();

        return response()->json($result);
    }

    public function printMedicalRecord($id)
    {
        // dd(1);
        $patient_visit = PatientVisit::findOrFail($id);
        // $patient = Patient::query()->where('$patient_visit->patient_id);
        $patient = PrintInfoResource::make($patient_visit)->resolve();
        // Gửi dữ liệu sang view 'medical_record'
        $pdf = FacadePdf::loadView('doctor.mau-phieu', compact('patient'));

        // Đặt khổ giấy A5 và hướng dọc
        $pdf->setPaper('A5', 'portrait');

        // Xuất file PDF
        return $pdf->stream("medical_record.pdf"); // Hoặc dùng ->download() nếu muốn tải về
    }
}
