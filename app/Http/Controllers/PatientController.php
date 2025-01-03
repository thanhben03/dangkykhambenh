<?php

namespace App\Http\Controllers;

use App\Events\PatientRegistered;
use App\Events\StandbyScreenEvent;
use App\Http\Resources\PatientPendingResource;
use App\Http\Resources\PatientResource;
use App\Models\Department;
use App\Models\MedicinePrescription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PatientVisit;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{

    public function index()
    {
        return view('patient.dashboard');
    }

    // Lấy hình ảnh bản đồ
    public function getMap($department_id)
    {
        $department = Department::query()->findOrFail($department_id);

        return response()->json([
            'video' => $department->video
        ]);
    }

    // tạo lịch hẹn
    public function createAppointment(Request $request)
    {
        try {

            $symptom = $request->symptom;
            $department_id = $request->department_id;
            $patient = Auth::guard('patient')->user();
            $ngaykham = $request->ngaykham;

            $patient_visit = PatientVisit::query()
                ->whereDate('arrival_time', $ngaykham)
                ->where('patient_id', $patient->id)
                ->latest()->first();
            if ($patient_visit && $patient_visit->status == 0) {
                return response()->json([
                    'message' => 'Bạn có một lịch khám ở khoa ' . $patient_visit->department->department_name,
                ], 500);
            }
            $this->registerPatientVisit($patient->id, $symptom, $department_id, null, $ngaykham);
            return response()->json([]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }


    // hủy lịch hẹn
    public function cancleAppointment($patient_visit_id)
    {
        try {
            PatientVisit::query()->where('id', $patient_visit_id)->delete();

            return response()->json([]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

    // hiển thị các thông tin của bệnh nhân
    public function showProfile()
    {
        $user = auth()->guard('patient')->user();
        return view('patient.profile', compact('user'));
    }

    // đổi password
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

    // Cập nhật thông tin cơ bản của bệnh nhân
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

    // Danh sách lịch hẹn của bệnh nhân
    public function appointmentPatient()
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

    // Lịch sử khám bệnh của bệnh nhân
    public function medicalExaminationHistory()
    {
        $patient = Patient::query()
            ->where('id', '=', auth()->guard('patient')->user()->id)
            ->get();
        $patient = PatientResource::make($patient)->resolve()[0];
        return view('patient.lich-su-kham-benh', compact('patient'));
    }

    // Quét CCCD
    public function scan()
    {
        // Gọi đến api của ras để lấy dữ liệu từ CCCD
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)->post(env('API_URL').'/command', [
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

    // Bỏ qua bệnh nhân đang tới lượt khám
    public function skip($patient_visit_id)
    {
        PatientVisit::query()->where('id', $patient_visit_id)->delete();
        broadcast(new StandbyScreenEvent())->toOthers();
    }

    // Chuyển chuỗi CCCD thành 1 mảng dữ liệu
    public function getDataFromCCCD(string $data)
    {
        $arrData = explode("|", $data); // chuyển chuỗi thành mảng phân cách bởi dấu |
        $strBirthday = $arrData[3];
        //$birthday = substr($strBirthday, 0, 2).'-'. substr($strBirthday, 2,2). '-'. substr($strBirthday, 4);
        $birthday = substr($strBirthday, 4) . '-' . substr($strBirthday, 2, 2) . '-' . substr($strBirthday, 0, 2);
        return [
            'bn_name' => $arrData[2],
            'dob' => $birthday,
            'gender' => $arrData[4],
            'birthplace' => $arrData[5],
            'cccd' => $arrData[0],
        ];
    }

    // Đăng ký khám bệnh tại trụ
    public function register(Request $request)
    {
        $data = $request->all();
        $department = null;




        // nếu người dùng không chọn khám tổng quát
        if ($data['department'] != 15) {
            $department = Department::query()->where('id', '=', $data['department'])->first();
        } else {
            $department = Department::query()->where('id', '=', 10)->first();
        }


        $arrival_time = $this->getArrivalTime($department)->toDateTimeString();

        // lấy dữ liệu của bệnh nhân theo cccd
        $patient = Patient::query()->where('nic', '=', $data['cccd'])->first();

        // nếu ko có thì đăng ký mới
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

        $patient_visit = PatientVisit::query()
            ->whereDate('arrival_time', Carbon::toDay())
            ->where('patient_id', $patient->id)
            ->latest()->first();
        if ($patient_visit && $patient_visit->status == 0) {
            return response()->json([
                'message' => 'Bạn có một lịch khám ở khoa ' . $patient_visit->department->department_name,
            ], 500);
        }


        $stt = $this->registerPatientVisit($patient->id, $data['symptom'], $data['department']);



        $response = Http::post(env('API_URL').'/print', [
            'stt' => $stt,
            'fullname' => $this->removeVietnameseAccents($data['fullname']),
            'cccd' => $this->removeVietnameseAccents($data['cccd']),
            'gender' => $this->removeVietnameseAccents($data['gender']),
            'birthday' => $this->removeVietnameseAccents($data['birthday']),
            'address' => $this->removeVietnameseAccents($data['address']),
            //            'email' => $this->removeVietnameseAccents($data['email']),
            'phone' => $this->removeVietnameseAccents($data['phone']),
            'arrival_time' => $arrival_time,
            'department' => $this->removeVietnameseAccents($department->department_name) . ' - ' . $department->room,
            'symptom' => $this->removeVietnameseAccents($data['symptom']),
        ]);


        if ($response->successful()) {
            // Khi đăng ký khám thành công sẽ trả về bản đồ đến khoa khám đó
            return response()->json([
                'img' => $department->img_map,
                'video' => $department->video
            ]);
        } else {
            return response()->json(['error' => 'API request failed'], 500);
        }
    }

    // Đăng ký khám bệnh từ xa cho bệnh nhân
    public function remoteRegister(Request $request)
    {
        $data = $request->all();
        $department = null;
        if ($data['department'] != 15) {
            $department = Department::query()->where('id', '=', $data['department'])->first();
        } else {
            $department = Department::query()->where('id', '=', 10)->first();
        }

        $ngaykham = $data['ngaykham'];



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
        $patient_visit = PatientVisit::query()
            ->whereDate('arrival_time', Carbon::toDay())
            ->where('patient_id', $patient->id)
            ->latest()->first();
        if ($patient_visit && $patient_visit->status == 0) {
            return response()->json([
                'message' => 'Bạn có một lịch khám ở khoa ' . $patient_visit->department->department_name,
            ], 500);
        }
        $stt = $this->registerPatientVisit($patient->id, $data['symptom'], $data['department'], null, $ngaykham);



        return response()->json([
            'img' => $department->img_map,
            'video' => $department->video
        ]);
    }

    // Hàm lấy STT hiện tại của một khoa
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

    // Lấy bệnh nhân đang khám hiện tại của một khoa
    public function getPatientVisitLatest(Department $department)
    {
        return PatientVisit::query()
            ->where('department_id', $department->id)
            ->whereDate('arrival_time', Carbon::today())
            ->orderBy('arrival_time', 'desc')
            ->first();
    }

    // Tính toán thời gian dự kiến đến khám
    public function getArrivalTime(Department $department, $ngaykham = null)
    {
        $patientVisit = null;
        if ($ngaykham) {
            // Lấy bệnh nhân mới nhất nếu có truyền vào ngày đến khám
            $patientVisit = PatientVisit::query()
                ->where('department_id', $department->id)
                ->whereDate('arrival_time', Carbon::parse($ngaykham))
                ->orderBy('arrival_time', 'desc')
                ->first();
        } else {
            // Lấy bệnh nhân mới nhất theo khoa của ngày hôm đó
            $patientVisit = $this->getPatientVisitLatest($department);
        }

        // Lấy thời gian hiện tại
        $currentNow = Carbon::now('Asia/Ho_Chi_Minh');

        // Nếu không có bệnh nhân đến khám hoặc thời gian hiện tại lớn hơn
        // thời gian dự kiến đến khám của bệnh nhân đó
        if (!$patientVisit || $currentNow > Carbon::parse($patientVisit->arrival_time)->addMinutes(10)) {
            if ($ngaykham && $currentNow < Carbon::parse($ngaykham)) {
                return Carbon::parse($ngaykham . ' 07:00:00');
            }
            return Carbon::now('Asia/Ho_Chi_Minh');
        }

        return Carbon::parse($patientVisit->arrival_time)->addMinutes(10);
    }

    // Chuyển đổi chuỗi sang không dấu
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

    // Chuyển khoa
    public function nextDepartment(Request $request)
    {
        $stt = $request->stt;
        $symptom = $request->symptom;
        $department_id = $request->department_id; // khoa kế tiếp

        // lấy dữ liệu khám của bệnh nhân
        $patientVisit = PatientVisit::query()
            ->where('stt', '=', $stt)
            ->whereDate('arrival_time', Carbon::today())
            ->orderBy('arrival_time', 'desc')
            ->first();

        // lấy thông bệnh nhân đến khám
        $patient = Patient::query()->where('id', '=', $patientVisit->patient_id)->first();

        $this->done($stt);
        $this->registerPatientVisit($patient->id, $symptom, $department_id, $stt);
    }

    // Khi bac si an nut hoan thanh
    public function done($stt)
    {
        PatientVisit::query()
            ->where('stt', '=', $stt)
            ->where('department_id', '=', auth()->user()->department_id)
            ->whereDate('arrival_time', Carbon::today())
            ->update(['status' => 1]);

        // gửi dữ liệu ra màn hình chờ khi khám xong để hiển thị stt tiếp theo
        broadcast(new StandbyScreenEvent())->toOthers();

        return response()->json([
            'msg' => 'Ok'
        ]);
    }

    // Xử lý đăng ký stt cho bệnh nhân
    public function registerPatientVisit($patient_id, $symptom, $department_id, $stt = null, $ngaykham = null)
    {
        $patientVisit = PatientVisit::query()
            // ->whereDate($ngaykham ? 'arrival_time' : 'created_at', $ngaykham ? $ngaykham : Carbon::toDay())
            ->whereDate('arrival_time', $ngaykham ? $ngaykham : Carbon::toDay())
            ->orderBy('arrival_time', 'desc')
            ->first();
        // Không có bệnh nhân mà có stt -> bác sĩ chuyển khoa hoặc khám sktq
        $department = Department::query()->where('id', '=', $department_id)->first();
        $kham_tq = 0;
        $newSTT = 0;
        $newPatientVisit = null;

        // nếu là khám tổng quát thì chuyển đến khoa chẩn đoán hình ảnh
        if ($department_id == 15) {
            $department_id = 10;
            $kham_tq = 1;
        }
        if ($stt) {
            $newPatientVisit = PatientVisit::query()->create([
                'patient_id' => $patient_id,
                'stt' => $stt,
                'department_id' => $department_id,
                'symptom' => $symptom,
                'kham_tq' => $kham_tq,
                'arrival_time' => $this->getArrivalTime($department, $ngaykham)->toDateTimeString(),
            ]);
            $newSTT = $stt;
        } else if (!$stt && !$patientVisit) {
            $newPatientVisit = PatientVisit::query()->create([
                'patient_id' => $patient_id,
                'stt' => 1,
                'department_id' => $department_id,
                'symptom' => $symptom,
                'kham_tq' => $kham_tq,
                'arrival_time' => $this->getArrivalTime($department, $ngaykham)->toDateTimeString(),
            ]);
            $newSTT = 1;
        } else {
            // lấy số tt mới nhất theo ngày hôm nay
            if (!$ngaykham) {
                $stt = PatientVisit::query()
                ->whereDate('arrival_time', Carbon::toDay())
                ->latest()->first()->stt;
            } else {
                $stt = PatientVisit::query()
                ->whereDate('arrival_time', $ngaykham)
                ->latest()->first()->stt;
            }
            $newSTT = $stt + 1;

            $newPatientVisit = PatientVisit::query()->create([
                'patient_id' => $patient_id,
                'stt' => $newSTT,
                'department_id' => $department_id,
                'symptom' => $symptom,
                'kham_tq' => $kham_tq,
                'arrival_time' => $this->getArrivalTime($department, $ngaykham)->toDateTimeString(),
            ]);
        }

        // thông báo cho bác sĩ khi có bệnh nhân đăng ký mới
        $result = PatientVisit::query()
            ->join('patients', 'patient_visits.patient_id', '=', 'patients.id')
            ->select('patients.*', 'patient_visits.*', 'patient_visits.stt as stt')
            ->where('patient_visits.id', $newPatientVisit->id)
            ->get();

        // format lại dữ liệu trả về
        $result = PatientPendingResource::make($result)->resolve();

        // gửi sự kiện đăng ký mới cho bác sĩ
        broadcast(new PatientRegistered($department_id, $result))->toOthers();
        // gửi sự kiện để màn hình tự load ở màn hình chờ
        broadcast(new StandbyScreenEvent())->toOthers();


        return $newSTT;
    }

    // Dành cho bệnh nhân khám tổng quát
    public function registerPatientGeneral(Request $request)
    {
        $symptom = $request->symptom;
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
                        'symptom' => $symptom
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
                'symptom' => $symptom,
                'arrival_time' => $this->getArrivalTime($department)->toDateTimeString(),
            ]);
        }
    }

    // Xem lịch hẹn của bệnh nhân
    public function appointment(Request $request)
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


    // Lưu chẩn đoán
    public function saveDiagnose(Request $request)
    {
        $data = $request->all();
        $medicineData = [];
        $patientVisit = PatientVisit::query()->find($data['current_patient_visit']);

        // kiểm tra nếu bác sĩ có thêm thuốc
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
            'symptom' => $data['symptoms'],
            'diagnosis' => $data['diagnosis']
        ]);

        return redirect()->route('dashboard');
    }
}
