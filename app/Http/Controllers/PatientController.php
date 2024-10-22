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

        return response()->json($data);
    }
}
