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

use Carbon\Carbon;


class DoctorController extends Controller
{

    // Danh sách chờ khám bệnh
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

    // Xem lịch sử khám bệnh của bệnh nhân bất kỳ
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

    // Lấy bệnh nhân đang khám theo tài khoản bác sĩ đã đăng nhập
    // public function getCurrentPatientVisit()
    // {
    //     $currentPatient = CurrentPatient::query()
    //         ->where('department_id', \auth()->user()->department_id ?? 1)
    //         ->whereDate('created_at', Carbon::today())
    //         ->orderBy('created_at', 'desc')
    //         ->first();

    //     if (!$currentPatient) {
    //         $currentPatient = CurrentPatient::create([
    //             'department_id' => \auth()->user()->department_id ?? 1,
    //             'stt' => 1,
    //             'created_at' => now(),
    //             // Các giá trị khác cần thiết
    //         ]);
    //     }

    //     return $currentPatient;
    // }

    // In phiếu khám bệnh
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
