<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        $response = Http::post('crow-wondrous-asp.ngrok-free.app/print', [
            'stt' => '123',
            'fullname' => $this->removeVietnameseAccents($data['fullname']),
            'cccd' => $this->removeVietnameseAccents($data['cccd']),
            'gender' => $this->removeVietnameseAccents($data['gender']),
            'birthday' => $this->removeVietnameseAccents($data['birthday']),
            'address' => $this->removeVietnameseAccents($data['address']),
            'email' => $this->removeVietnameseAccents($data['email']),
            'phone' => $this->removeVietnameseAccents($data['phone']),
            'arrival_time' => Carbon::now('Asia/Ho_Chi_Minh')->toDateTimeString(),
            'department' => $this->removeVietnameseAccents($data['department']),
            'trieu_chung' => $this->removeVietnameseAccents($data['trieu_chung']),
        ]);

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
}
