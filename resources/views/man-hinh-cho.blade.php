<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Màn hình chờ khám bệnh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite('resources/js/bootstrap.js')

    <style>
        table>* {
            font-size: 30px
        }
    </style>

</head>

<body>

    <div class="container my-5">
        <table class="table table-bordered">
            <thead>
                <tr class="table-primary">
                    <th scope="col">LOGO</th>
                    <th style="text-align: center" colspan="3">Khoa {{ $department->department_name }}</th>
                </tr>
                <tr class="table-primary">
                    <th scope="col">STT</th>
                    <th scope="col">Họ Và Tên</th>
                    <th scope="col">Năm sinh</th>
                    <th scope="col">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @if ($patientVisits)
                    @foreach ($patientVisits as $patient)
                        <tr @if ($patient == reset($patientVisits)) class="table-success" @endif>
                            <th scope="row">{{ $patient['stt'] }}</th>
                            <td>{{ $patient['name'] }}</td>
                            <td>{{ $patient['bod'] }}</td>
                            <td>
                                @if ($patient == reset($patientVisits))
                                    Đang tới lượt
                                @else
                                    Chờ khám
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td></td>
                    </tr>
                @endif

            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {

            

            // window.Echo.private(`department.{{ $department->id }}`)
            //     .listen('PatientRegistered', (event) => {

            //         window.location.reload();
            //         console.log('Thông tin đăng ký mới:', event.patientInfo);
            //         // Thực hiện logic hiển thị thông báo hoặc cập nhật giao diện
            //     });

            window.Echo.channel('standby-screen')
                .listen('StandbyScreenEvent', (e) => {
                    console.log('New message');
                    window.location.reload();
                });

        });
    </script>
</body>



</html>
