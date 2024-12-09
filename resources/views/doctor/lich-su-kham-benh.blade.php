@extends('layouts.master')

@section('title')
Lịch Sử Khám Bệnh
@endsection
@section('content')


    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid custom-content">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Lịch sử khám bệnh</h4>



                        </div>
                    </div>
                </div>

                <form id="search-form" class="mb-3" action="/medical-history" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchName" placeholder="Nhập tên bệnh nhân"
                                name="name">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchCCCD" placeholder="Nhập số CCCD"
                                name="cccd">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary" onclick="searchPatient()">Tìm kiếm</button>
                            <a href="/dashboard" class="btn btn-success">Quay lại</a>
                        </div>
                    </div>
                </form>

                @foreach ($patients as $patient)
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Card for Patient Info -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                       {{ $patient['name'] }} {{$patient['bod']}}

                                    </h3>
                                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#patientInfo-{{ $patient['id'] }}" aria-expanded="true"
                                    aria-controls="patientInfo-3">
                                    Xem chi tiết
                                </button>

                                </div>

                                <div class="collapse" id="patientInfo-{{ $patient['id'] }}" aria-expanded="true">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="#info-{{$patient['id']}}" aria-controls="info" role="tab"
                                                data-bs-toggle="tab">Thông Tin Bệnh Nhân</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#history-{{$patient['id']}}" aria-controls="history" role="tab"
                                                data-bs-toggle="tab">Lịch Sử Khám Bệnh</a>
                                        </li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content p-3">
                                        <!-- Tab 1: Thông tin bệnh nhân -->
                                        <div role="tabpanel" class="tab-pane fade show active" id="info-{{$patient['id']}}">
                                            <form action="/editpatient" method="POST">
                                                <input type="hidden" name="_token"
                                                    value="N3aCZEQBBHtMdURn9NrkZvXMdfVtQUf9WKa0L0fQ">
                                                
                                                <div class="mb-3 row">
                                                    <label for="fullName" class="col-sm-2 col-form-label">Full
                                                        Name</label>
                                                    <div class="col-sm-10">
                                                        <input readonly value="{{ $patient['name'] }}"
                                                            type="text" class="form-control" id="fullName"
                                                            name="reg_pname">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="nicNumber" class="col-sm-2 col-form-label">CCCD</label>
                                                    <div class="col-sm-10">
                                                        <input readonly value="{{ $patient['nic'] }}"
                                                            type="text" class="form-control" id="nicNumber"
                                                            name="reg_pnic">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="address" class="col-sm-2 col-form-label">Address</label>
                                                    <div class="col-sm-10">
                                                        <input readonly value="{{ $patient['address'] }}"
                                                            type="text" class="form-control" id="address"
                                                            name="reg_paddress">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="telephone"
                                                        class="col-sm-2 col-form-label">Telephone</label>
                                                    <div class="col-sm-10">
                                                        <input readonly value="{{ $patient['telephone'] }}"
                                                            type="tel" class="form-control" id="telephone"
                                                            name="reg_ptel">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="sex" class="col-sm-2 col-form-label">Sex</label>
                                                    <div class="col-sm-2">
                                                        <input readonly value="{{ $patient['sex'] }}"
                                                            type="text" class="form-control" id="sex"
                                                            name="reg_poccupation">
                                                    </div>
                                                    <label for="dob" class="col-sm-2 col-form-label">DOB</label>
                                                    <div class="col-sm-3">
                                                        <input readonly value="{{ $patient['bod'] }}"
                                                            type="text" class="form-control" id="dob"
                                                            name="reg_pbd">
                                                    </div>
                                                    <div class="col-sm-3 text-end">
                                                        {{-- <div class="btn-group">
                                                            <button type="button" onclick="go('9')"
                                                                class="btn btn-info"><i class="far fa-id-card"></i>
                                                                Profile</button>
                                                            <button type="button" class="btn btn-warning"><i
                                                                    class="fas fa-edit"></i> Edit</button>
                                                        </div> --}}
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Tab 2: Lịch sử khám bệnh -->
                                        <div role="tabpanel" class="tab-pane fade" id="history-{{$patient['id']}}">
                                            {{-- <h4>Lịch sử khám bệnh của bệnh nhân</h4> --}}
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Ngày Khám</th>
                                                        <th>Khoa</th>
                                                        <th>Chẩn Đoán</th>
                                                        <th>Kê Đơn Thuốc</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($patient['history'] as $history)
                                                        <tr>
                                                            <td>{{$history->created_at}}</td>
                                                            <td>
                                                                @if ($history->kham_tq)
                                                                    Khám tổng quát({{$history->department->department_name}})
                                                                @else
                                                                    {{$history->department->department_name}}
                                                                @endif
                                                            </td>
                                                            <td>{{$history->diagnosis ?? ''}}</td>
                                                            <td>
                                                                {{ $history->medicines->map(function ($item) {
                                                                    return $item->medicine_name . ' (' . $item->use . ')';
                                                                })->implode(', ') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- end page title -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->


        
    </div>
    <!-- end main content-->
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Thêm hàng mới vào bảng khi nút "Thêm Thuốc" được nhấn
            $('#addRow').click(function() {
                var newRow = `
            <tr>
                <td><input type="text" name="medicine_name[]" class="form-control" placeholder="Nhập tên thuốc..."></td>
                <td><input type="number" name="medicine_quantity[]" class="form-control" placeholder="Nhập số lượng..."></td>
                <td><input type="text" name="medicine_usage[]" class="form-control" placeholder="Nhập cách dùng..."></td>
                <td><button type="button" class="btn btn-danger removeRow">{{ __('Xóa') }}</button></td>
            </tr>`;
                $('#prescriptionTable tbody').append(newRow);
            });

            // Xóa hàng khi nhấn nút "Xóa"
            $(document).on('click', '.removeRow', function() {
                $(this).closest('tr').remove();
            });


        });

        function step1(stt) {
            $("#modal-next-department").modal('toggle')
            $("#current-stt").val(stt);

        }

        function done() {
            let stt = $("#current-stt").val();

            $.ajax({
                type: "GET",
                url: "{{ route('doctor.done', ':stt') }}".replace(':stt', stt),
                success: function(res) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200)
                }
            })
        }

        function skip(patient_visit_id) {
            if (!confirm('Bạn có chắc chắn muốn bỏ qua bệnh nhân này ?')) {
                return
            }

            $.ajax({
                type: "GET",
                url: "/skip/" + patient_visit_id,
                success: function (res) {
                    alert('Thành công !')

                    window.location.reload()
                }
            })
        }

        function nextDepartment() {
            let stt = $("#current-stt")
            let symptom = $("#symptom")
            let department_id = $("#department_id")
            $.ajax({
                type: "POST",
                url: "/next-department",
                data: {
                    stt: stt.val(),
                    symptom: symptom.val(),
                    department_id: department_id.val(),
                    "_token": "{{ csrf_token() }}"
                },
                success: function(res) {
                    alert('Chuyển khoa thành công !');

                    window.location.reload()
                }
            })
        }
    </script>
@endpush
