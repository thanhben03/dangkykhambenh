<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\PatientVisit;
use App\Models\CurrentPatient;
use App\Models\Patient;

class PatientController extends Controller
{
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

    public function getDataFromCCCD(string $data)
    {
        $arrData = explode("|", $data);
        $strBirthday = $arrData[3];
//        $birthday = substr($strBirthday, 0, 2).'-'. substr($strBirthday, 2,2). '-'. substr($strBirthday, 4);
        $birthday = substr($strBirthday, 4).'-'. substr($strBirthday, 2,2). '-'. substr($strBirthday, 0,2);
        return [
            'stt' => rand(0,1000),
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
        $department = DB::table('departments')->where('id', '=', $data['department'])->first();
        $response = Http::post('crow-wondrous-asp.ngrok-free.app/print', [
            'stt' => '123',
            'fullname' => $this->removeVietnameseAccents($data['fullname']),
            'cccd' => $this->removeVietnameseAccents($data['cccd']),
            'gender' => $this->removeVietnameseAccents($data['gender']),
            'birthday' => $this->removeVietnameseAccents($data['birthday']),
            'address' => $this->removeVietnameseAccents($data['address']),
//            'email' => $this->removeVietnameseAccents($data['email']),
            'phone' => $this->removeVietnameseAccents($data['phone']),
            'arrival_time' => Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString(),
            'department' => $this->removeVietnameseAccents($data['department']),
            'trieu_chung' => $this->removeVietnameseAccents($data['trieu_chung']),
        ]);

        $id = Patient::query()->orderBy('id', 'desc')->first()->id;
        $patient = Patient::query()->where('nic', '=', $data['cccd'])->first();

        if (!$patient) {
            $patient = Patient::query()->create([
                'id' => $id + 1,
                'name' => $data['fullname'],
                'address' => $data['address'],
                'sex' => $data['gender'] == 'Nam' ? 'Male' : 'Female',
                'bod' => $data['birthday'],
                'telephone' => $data['phone'],
                'nic' => $data['cccd'],
            ]);
        }
        $this->registerPatientVisit($patient->id > 0 ? $patient->id : $id+1, $data['trieu_chung'], $department->id);

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json(['error' => 'API request failed'], 500);
        }

    }

    function removeVietnameseAccents($str) {
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
        PatientVisit::query()->where('stt','=', $stt)->update(['status' => 1]);

        $currentPatient = CurrentPatient::query()
            ->where('department_id', \auth()->user()->department_id ?? 1)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();

        $currentSTT = intval($currentPatient->stt);
        $currentPatient->stt = $currentSTT + 1;
        $currentPatient->save();

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
            ->where('stt','=', $stt)
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
            'stt' => $stt ?? PatientVisit::query()->whereDate('created_at', Carbon::toDay())->orderBy('created_at', 'desc')->first()->stt + 1,
            'department_id' => $department_id,
            'trieu_chung' => $trieu_chung,
        ]);
    }

    public function lichHen()
    {
        $result = PatientVisit::query()
            ->join('patients', 'patient_visits.patient_id', '=', 'patients.id')
            ->select('patients.*','patient_visits.*','patient_visits.stt as stt')
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
                ->select('patients.*','patient_visits.*','patient_visits.stt as stt')
                ->where('department_id', '=', \auth()->user()->department_id ?? 1)
                ->where('status', 0)
                ->whereDate('patient_visits.created_at', '=', $date)
                ->orderBy('patient_visits.created_at')
                ->get();

        return response()->json($result);
    }
}
