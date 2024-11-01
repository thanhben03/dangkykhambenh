@extends('layouts.master')

@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Lịch hẹn</h4>

                         

                        </div>
                    </div>
                </div>
                <!-- Chọn lịch -->
                <div class="form-group col-3">
                    <label for="appointment-date">Chọn ngày hẹn:</label>
                    <input type="date" id="appointment-date" class="form-control">
                </div>

                <!-- Nút tìm kiếm -->
                <div class="form-group mt-3">
                    <button id="search-appointments" class="btn btn-primary">Tìm lịch hẹn</button>
                    <a href="/lich-hen" class="btn btn-warning">Xóa bộ lọc</a>
                </div>

                <!-- Hiển thị danh sách bệnh nhân -->
                <div class="mt-4">
                    <h5>Danh sách bệnh nhân có lịch hẹn</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên bệnh nhân</th>
                                <th>Năm sinh</th>
                                <th>Triệu chứng</th>
                            </tr>
                        </thead>
                        <tbody id="appointment-list">
                            @foreach ($appointments as $item)
                                <tr>
                                    <td>{{ $item->stt }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->nic }}</td>
                                    <td>{{ $item->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.getElementById('search-appointments').addEventListener('click', function() {
            var date = document.getElementById('appointment-date').value;

            if (date) {
                // Gọi AJAX để lấy danh sách bệnh nhân
                fetch('/get-appointments?date=' + date)
                    .then(response => response.json())
                    .then(data => {
                        // Xóa danh sách cũ
                        var appointmentList = document.getElementById('appointment-list');
                        appointmentList.innerHTML = '';

                        // Hiển thị danh sách mới
                        data.forEach((appointment, index) => {
                            console.log(appointment);
                            appointmentList.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${appointment.name}</td>
                            <td>${appointment.nic}</td>
                            <td>${appointment.created_at}</td>
                        </tr>
                    `;
                        });
                    });
            } else {
                alert('Vui lòng chọn ngày hẹn!');
            }
        });
    </script>
@endpush
