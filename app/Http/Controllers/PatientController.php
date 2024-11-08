<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientPendingResource;
use App\Http\Resources\PatientResource;
use App\Models\Department;
use App\Models\MedicinePrescription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PatientVisit;
use App\Models\Patient;
use Closure;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{

    public function index()
    {
        return view('patient.dashboard');
    }

    public function createAppointment(Request $request)
    {
        try {

            $trieu_chung = $request->trieu_chung;
            $department_id = $request->department_id;
            $patient = Auth::guard('patient')->user();
            $ngaykham = $request->ngaykham;
            $this->registerPatientVisit($patient->id, $trieu_chung, $department_id, null, $ngaykham);
            return response()->json([]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

    public function cancleAppointment($patient_visit_id)
    {
        try {
            PatientVisit::query()->where('id', $patient_visit_id)->delete();

            return response()->json([]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

    public function showProfile()
    {
        $user = auth()->guard('patient')->user();
        return view('patient.profile', compact('user'));
    }

    public function changePassword(Request $request)
    {
        $password = $request->current_password;
        $newPassword = $request->new_password;
        $user = auth()->guard('patient')->user();

        if (!Hash::check($password, $user->password)) {
            return redirect()->back()->withErrors(['password' => 'Mật khẩu hiện tại không đúng !']);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return redirect()->back()->with('msg', 'Cập nhật mật khẩu thành công !');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'telephone' => 'required|digits_between:10,15',
            'bod' => 'required|date',
            'sex' => 'required|in:Male,Female,other', // Adjust the options based on your requirements
            'nic' => 'required|string|max:20',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string',
            'email' => 'nullable|string',
        ]);
        $data = $request->all();



        try {
            DB::beginTransaction();

            $user = auth()->guard('patient')->user();
            $user->update($data);
            $user->save();

            DB::commit();


            return redirect()->back()->with('msg', 'Cập nhật thành công !');
        } catch (\Throwable $th) {


            DB::rollBack();

            return redirect()->back()->withErrors(['msg' => $th->getMessage()]);
        }
    }

    public function lichHenPatient()
    {
        $patients = PatientVisit::query()
            ->join('patients', 'patient_visits.patient_id', '=', 'patients.id')
            ->select('patients.*', 'patient_visits.*', 'patient_visits.stt as stt')
            ->where('patient_id', '=', \auth()->guard('patient')->user()->id)
            ->where('status', 0)
            ->whereDate('patient_visits.arrival_time', '>=', Carbon::toDay())
            ->orderBy('patient_visits.arrival_time')
            ->get();

        $patients = PatientPendingResource::make($patients)->resolve();
        return view('patient.dashboard', [
            'patients' => $patients
        ]);
    }

    public function khamBenh()
    {
        $patient = Patient::query()
            ->where('id', '=', auth()->guard('patient')->user()->id)
            ->get();
        $patient = PatientResource::make($patient)->resolve()[0];
        return view('patient.lich-su-kham-benh', compact('patient'));
    }

    public function scan()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(0)->post('crow-wondrous-asp.ngrok-free.app/command', [
            'command' => 'scan_qr',
        ]);
        //        return response()->json($this->getDataFromCCCD('089202017098|352576714|Lê Văn Lương|23052002|Nam|Tổ 10 Ấp An Thái, Hòa Bình, Chợ Mới, An Giang|31122021'));

        // Kiểm tra phản hồi
        if ($response->successful()) {
            // Xử lý phản hồi thành công
            // Du lieu chua duoc xu ly
            // 089202017098|352576714|Lê Văn Lương|23052002|Nam|
            $data = $response->json();
            $data = $this->getDataFromCCCD($data['data']); // du lieu da duoc xu ly

            return response()->json($data);
        } else {
            // Xử lý lỗi nếu yêu cầu không thành công
            return 'Request failed with status: ' . $response->status();
        }
    }

    public function getPatientByStt($stt)
    {
        $patientVisit = PatientVisit::query()
            ->where('stt', '=', $stt)
            ->whereDate('created_at', Carbon::today())
            ->first();

        return response()->json($patientVisit);
    }

    public function skip($patient_visit_id)
    {
        PatientVisit::query()->where('id', $patient_visit_id)->delete();
    }

    public function getDataFromCCCD(string $data)
    {
        $arrData = explode("|", $data);
        $strBirthday = $arrData[3];
        //        $birthday = substr($strBirthday, 0, 2).'-'. substr($strBirthday, 2,2). '-'. substr($strBirthday, 4);
        $birthday = substr($strBirthday, 4) . '-' . substr($strBirthday, 2, 2) . '-' . substr($strBirthday, 0, 2);
        return [
            'stt' => rand(0, 1000),
            'bn_name' => $arrData[2],
            'dob' => $birthday,
            'gender' => $arrData[4],
            'birthplace' => $arrData[5],
            'arrival_time' => Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString(),
            'department' => 'Khoa CNTT',
            'cccd' => $arrData[0],
        ];
    }

    public function register(Request $request)
    {
        $data = $request->all();
        if ($data['department'] != 15) {
            $department = Department::query()->where('id', '=', $data['department'])->first();
        } else {
            $department = Department::query()->where('id', '=', 10)->first();
        }

        // $stt = $this->getSTTOfDepartment($department);

        $patientLatest = $this->getPatientLatest();
        $arrival_time = $this->getArrivalTime($department)->toDateTimeString();

        $patient = Patient::query()->where('nic', '=', $data['cccd'])->first();
        if (!$patient) {
            $patient = Patient::query()->create([
                'name' => $data['fullname'],
                'address' => $data['address'],
                'sex' => $data['gender'] == 'Nam' ? 'Male' : 'Female',
                'bod' => $data['birthday'],
                'telephone' => $data['phone'],
                'nic' => $data['cccd'],
                'password' => Hash::make($data['cccd']),

            ]);
        }
        $stt = $this->registerPatientVisit($patient->id > 0 ? $patient->id : $patientLatest->id + 1, $data['trieu_chung'], $data['department']);




        $response = Http::post('crow-wondrous-asp.ngrok-free.app/print', [
            'stt' => $stt,
            'fullname' => $this->removeVietnameseAccents($data['fullname']),
            'cccd' => $this->removeVietnameseAccents($data['cccd']),
            'gender' => $this->removeVietnameseAccents($data['gender']),
            'birthday' => $this->removeVietnameseAccents($data['birthday']),
            'address' => $this->removeVietnameseAccents($data['address']),
            //            'email' => $this->removeVietnameseAccents($data['email']),
            'phone' => $this->removeVietnameseAccents($data['phone']),
            'arrival_time' => $arrival_time,
            'department' => $this->removeVietnameseAccents($department->department_name),
            'trieu_chung' => $this->removeVietnameseAccents($data['trieu_chung']),
        ]);


        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json(['error' => 'API request failed'], 500);
        }
    }

    public function remoteRegister(Request $request)
    {
        $data = $request->all();

        if ($data['department'] != 15) {
            $department = Department::query()->where('id', '=', $data['department'])->first();
        } else {
            $department = Department::query()->where('id', '=', 10)->first();
        }

        $ngaykham = $data['ngaykham'];
        $patientLatest = $this->getPatientLatest();

        $patient = Patient::query()->where('nic', '=', $data['cccd'])->first();
        if (!$patient) {
            $patient = Patient::query()->create([
                'name' => $data['fullname'],
                'address' => $data['address'],
                'sex' => $data['gender'] == 'Nam' ? 'Male' : 'Female',
                'bod' => $data['birthday'],
                'telephone' => $data['phone'],
                'nic' => $data['cccd'],
                'password' => Hash::make($data['cccd']),
            ]);
        }
        $stt = $this->registerPatientVisit($patient->id, $data['trieu_chung'], $data['department'], null, $ngaykham);
    }

    public function getSTTOfDepartment(Department $department)
    {
        $patientVisit = PatientVisit::query()
            ->where('department_id', $department->id)
            ->whereDate('created_at', Carbon::toDay())
            ->orderBy('created_at', 'desc')
            ->first();
        $stt = optional($patientVisit)->stt ? optional($patientVisit)->stt + 1 : 1;

        return $stt;
    }

    public function getPatientLatest()
    {

        return Patient::query()->orderBy('id', 'desc')->first();
    }

    public function getPatientVisitLatest(Department $department)
    {
        return PatientVisit::query()
            ->where('department_id', $department->id)
            ->whereDate('arrival_time', Carbon::today())
            ->orderBy('arrival_time', 'desc')
            ->first();
    }

    public function getArrivalTime(Department $department, $ngaykham = null)
    {
        $patientVisit = null;
        if ($ngaykham) {
            $patientVisit = PatientVisit::query()
                ->where('department_id', $department->id)
                ->whereDate('arrival_time', Carbon::parse($ngaykham))
                ->orderBy('arrival_time', 'desc')
                ->first();
        } else {
            $patientVisit = $this->getPatientVisitLatest($department);
        }
        $currentNow = Carbon::now('Asia/Ho_Chi_Minh');

        if (!$patientVisit || $currentNow > Carbon::parse($patientVisit->arrival_time)->addMinutes(10)) {
            if ($ngaykham && $currentNow < Carbon::parse($ngaykham)) {
                return Carbon::parse($ngaykham . ' 07:00:00');
            }
            return Carbon::now('Asia/Ho_Chi_Minh');
        }

        return Carbon::parse($patientVisit->arrival_time)->addMinutes(10);
    }

    function removeVietnameseAccents($str)
    {
        $accents = [
            'a' => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'],
            'e' => ['è', 'é', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'],
            'i' => ['ì', 'í', 'ỉ', 'ĩ', 'ị'],
            'o' => ['ò', 'ó', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'],
            'u' => ['ù', 'ú', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'],
            'y' => ['ỳ', 'ý', 'ỷ', 'ỹ', 'ỵ'],
            'd' => ['đ'],
            'A' => ['À', 'Á', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ'],
            'E' => ['È', 'É', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ'],
            'I' => ['Ì', 'Í', 'Ỉ', 'Ĩ', 'Ị'],
            'O' => ['Ò', 'Ó', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ'],
            'U' => ['Ù', 'Ú', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự'],
            'Y' => ['Ỳ', 'Ý', 'Ỷ', 'Ỹ', 'Ỵ'],
            'D' => ['Đ'],
        ];

        foreach ($accents as $nonAccent => $accentedChars) {
            $str = str_replace($accentedChars, $nonAccent, $str);
        }

        return $str;
    }

    // Khi bac si an nut hoan thanh
    public function done($stt)
    {
        PatientVisit::query()
            ->where('stt', '=', $stt)
            ->where('department_id', '=', auth()->user()->department_id)
            ->whereDate('arrival_time', Carbon::today())
            ->update(['status' => 1]);

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
            ->whereDate('arrival_time', Carbon::today())
            ->orderBy('arrival_time', 'desc')
            ->first();

        $patient = Patient::query()->where('id', '=', $patientVisit->patient_id)->first();

        $this->done($stt);
        $this->registerPatientVisit($patient->id, $trieu_chung, $department_id, $stt);
    }

    public function registerPatientVisit($patient_id, $trieu_chung, $department_id, $stt = null, $ngaykham = null)
    {
        $patientVisit = PatientVisit::query()
            ->whereDate($ngaykham ? 'arrival_time' : 'created_at', $ngaykham ? $ngaykham : Carbon::toDay())
            ->orderBy('created_at', 'desc')
            ->first();
        // Không có bệnh nhân mà có stt -> bác sĩ chuyển khoa hoặc khám sktq
        $department = Department::query()->where('id', '=', $department_id)->first();
        $kham_tq = 0;
        if ($department_id == 15) {
            $department_id = 10;
            $kham_tq = 1;
        }
        if ($stt) {
            PatientVisit::query()->create([
                'patient_id' => $patient_id,
                'stt' => $stt,
                'department_id' => $department_id,
                'trieu_chung' => $trieu_chung,
                'kham_tq' => $kham_tq,
                'arrival_time' => $this->getArrivalTime($department, $ngaykham)->toDateTimeString(),
            ]);
            return $stt;
        } else if (!$stt && !$patientVisit) {
            PatientVisit::query()->create([
                'patient_id' => $patient_id,
                'stt' => 1,
                'department_id' => $department_id,
                'trieu_chung' => $trieu_chung,
                'kham_tq' => $kham_tq,
                'arrival_time' => $this->getArrivalTime($department, $ngaykham)->toDateTimeString(),
            ]);
            return 1;
        } else {
            $stt = PatientVisit::query()
                ->whereDate('created_at', Carbon::toDay())
                ->latest()->first()->stt;
            PatientVisit::query()->create([
                'patient_id' => $patient_id,
                'stt' => $stt + 1,
                'department_id' => $department_id,
                'trieu_chung' => $trieu_chung,
                'kham_tq' => $kham_tq,
                'arrival_time' => $this->getArrivalTime($department, $ngaykham)->toDateTimeString(),
            ]);
            return $stt + 1;
        }
    }

    // Dành cho bệnh nhân khám tổng quát
    public function registerPatientGeneral(Request $request)
    {
        $trieu_chung = $request->trieu_chung;
        $patient_visit = PatientVisit::query()
            ->where('id', '=', $request->id)
            ->first();
        $stt = $request->stt;
        $department_id = 0;
        switch ($patient_visit->department_id) {
            case 10:
                $department_id = 4;
                break;
            case 4:
                $department_id = 5;
                break;
            case 5:
                PatientVisit::query()
                    ->where('id', '=', $request->id)
                    ->update([
                        'status' => 1,
                        'trieu_chung' => $trieu_chung
                    ]);
                break;
            default:
                # code...
                break;
        }
        $this->done($stt);

        if ($patient_visit->department_id != 5) {
            // $department = Departments::find
            $department = Department::query()->where('id', '=', $department_id)->first();
            PatientVisit::query()->create([
                'patient_id' => $patient_visit->patient_id,
                'stt' => $stt,
                'department_id' => $department_id,
                'kham_tq' => 1,
                'trieu_chung' => $trieu_chung,
                'arrival_time' => $this->getArrivalTime($department)->toDateTimeString(),
            ]);
        }
    }

    public function lichHen(Request $request)
    {
        $date = $request->has('date') ? $request->get('date') : null;

        $result = PatientVisit::query()
            ->join('patients', 'patient_visits.patient_id', '=', 'patients.id')
            ->select('patients.*', 'patient_visits.*', 'patient_visits.stt as stt')
            ->where('department_id', '=', \auth()->user()->department_id)
            ->where('status', 0)
            ->whereDate('patient_visits.arrival_time', $date ? $date : Carbon::tomorrow())
            ->orderBy('patient_visits.arrival_time')
            ->get();
        return view('doctor.lich-hen', [
            'appointments' => $result,
            'date' => $date
        ]);
    }

    public function getAppointments(Request $request)
    {
        $date = Carbon::parse($request->query('date')) ?? Carbon::tomorrow();

        $result = PatientVisit::query()
            ->join('patients', 'patient_visits.patient_id', '=', 'patients.id')
            ->select('patients.*', 'patient_visits.*', 'patient_visits.stt as stt')
            ->where('department_id', '=', \auth()->user()->department_id ?? 1)
            ->where('status', 0)
            ->whereDate('patient_visits.created_at', $date)
            // ->orderBy('patient_visits.created_at')
            ->get();

        return response()->json($result);
    }

    public function luuChuanDoan(Request $request)
    {
        $data = $request->all();
        $medicineData = [];
        $patientVisit = PatientVisit::query()->find($data['current_patient_visit']);

        if (isset($data['medicine_name']) && count($data['medicine_name']) > 0) {
            for ($i = 0; $i < count($data['medicine_name']); $i++) {
                $medicineData[] = [
                    'medicine_name' => $data['medicine_name'][$i],
                    'current_patient_visit' => $data['current_patient_visit'],
                    'qty' => $data['medicine_quantity'][$i],
                    'use' => $data['medicine_usage'][$i],
                ];
            }

            MedicinePrescription::query()->where('current_patient_visit', '=', $data['current_patient_visit'])->delete();
            MedicinePrescription::query()->insert($medicineData);
        }

        $patientVisit->update([
            'trieu_chung' => $data['symptoms'],
            'chuan_doan' => $data['diagnosis']
        ]);

        return redirect()->route('dashboard');
    }
}
