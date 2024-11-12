<!-- resources/views/medical_record.blade.php -->
<?php use Carbon\Carbon;?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Phiếu Khám Bệnh</title>
    <style>
        /* CSS tùy chỉnh để in khổ A5 */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            /* padding: 20px; */
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .patient-info,
        .medical-info {
            margin-bottom: 15px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
        }

        .ngay-in, .signature {
            text-align: right;
            padding: 0 17px 5px 0;
            margin: 0
        }
        p {
            margin: 0 !important;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="">
            <p>Đại học Sư Phạm Kỹ Thuật</p>
            <p style="margin-left: 5px !important">Thành phố Hồ Chí Minh</p>
            <p class="ngay-in" style="text-align: right"><em>Ngày in phiếu: {{Carbon::toDay()->toDateString()}}</em></p>
        </div>

        <div class="header">
            <h2 style="margin: 5px">Phiếu Khám Bệnh</h2>
            <p style="font-size: 18px">Khoa: Nội Tổng Quát</p>
        </div>

        <div class="patient-info">
            <p>Họ tên: {{ $patient['name'] }}</p>
            <p>Giới tính: Nam</p>
            <p>Ngày sinh: {{ $patient['bod'] }}</p>
            <p>Nơi sinh: {{ $patient['address'] }}</p>
            <p>CCCD: {{ $patient['cccd'] }}</p>
            <p>Triệu chứng: {{$patient['trieu_chung']}}</p>
            <p>Chẩn đoán: {{$patient['chuan_doan']}}</p>
            <p>Đơn thuốc:<br></p>
            <p>{{$patient['history_medicine']}}</p>
            <p class="signature" style="text-align: right;margin: 20px 35px 0 0 !important;"><strong>Ký tên</strong></p>
            
        </div>

    </div>
</body>

</html>
