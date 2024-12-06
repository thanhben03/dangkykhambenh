@extends('layouts.app2')

@section('title')
    Lịch Khám Bệnh
@endsection

@section('content')
    <input type="text" hidden id="current-stt">
    <input type="text" hidden id="current-patient-visit">

    <!-- The modal chuyen khoa -->
    <div class="modal fade" id="modal-create-appointment" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Đăng ký lịch khám bệnh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Chọn khoa: </label>
                        <select class="form-control" name="" id="department_id">
                            @foreach (DB::table('departments')->where('status', 0)->get() as $item)
                                <option value="{{ $item->id }}">{{ $item->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Chọn ngày khám: </label>
                        <input type="date" id="ngaykham" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Triệu chứng: </label>
                        <textarea class="form-control" id="symptom"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button onclick="createAppointment()" type="button" class="btn btn-success"
                        data-bs-dismiss="modal">Đăng ký</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="img-map-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 93% !important">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Bản đồ hướng dẫn đến khoa khám</h5>
                    <button onclick="window.location.reload()" class="my-btn">X</button>
                </div>
                <div class="modal-body d-flex justify-content-center">
                    {{-- <img style="
                        display: block;
                        margin-left: auto;
                        margin-right: auto;
                        width: 50%;"
                        src="" id="img_map" alt=""> --}}
                </div>
                <div class="modal-footer">
                    {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button> --}}
                </div>
            </div>
        </div>
    </div>


    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid custom-content">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Lịch khám bệnh</h4>

                            <button onclick="showModalCreateAppointment()" style="float: right" class="btn btn-success">Đăng
                                ký</button>
                        </div>
                    </div>
                </div>

                @foreach ($patients as $patient)
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Card for Patient Info -->
                            <div class="card">
                                <div class="card-header" {{-- @if ($patient == reset($patients)) style="background: #ffcccc;" @endif --}}>
                                    <h3 class="card-title">
                                        STT Khám Bệnh: {{ $patient['stt'] }} - {{ $patient['arrival_time'] }}

                                        {{-- @if ($patient == reset($patients))
                                            <button class="btn btn-warning">Đang tới lượt</button>
                                            <button onclick="skip({{ $patient['id'] }})" class="btn btn-danger">Bỏ
                                                qua</button>
                                            @if ($patient['kham_tq'] == 0)
                                                <button onclick="step1({{ $patient['stt'] }})" type="button"
                                                    class="btn btn-success"
                                                    style="float: right">{{ __('Hoàn Thành') }}</button>
                                            @elseif($patient['kham_tq'] == 1 && $patient['department_id'] == 5)
                                                <button onclick="step1General({{ $patient['stt'] }},{{ $patient['id'] }})"
                                                    type="button" class="btn btn-success"
                                                    style="float: right">{{ __('Hoàn thành') }}</button>
                                            @else
                                                <button
                                                    onclick="step1General({{ $patient['stt'] }},{{ $patient['id'] }})"
                                                    type="button" class="btn btn-success"
                                                    style="float: right">{{ __('Tiếp tục') }}</button>
                                            @endif
                                            @if ($patient['kham_tq'])
                                                <button style="float: right; font-weight: bold" class="btn">Khám tổng
                                                    quát</button>
                                            @endif
                                        @endif --}}

                                    </h3>
                                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#patientInfo-{{ $patient['id'] }}" aria-expanded="true"
                                        aria-controls="patientInfo-3">
                                        Xem chi tiết
                                    </button>
                                    <button onclick="showMap('{{$patient['department']->video}}')" class="btn btn-info" type="button"
                                        
                                        aria-controls="patientInfo-3">
                                        Bản đồ
                                    </button>
                                    <button onclick="huyLichHen({{ $patient['id'] }})" class="btn btn-danger"
                                        type="button">
                                        Hủy lịch
                                    </button>

                                </div>

                                <div class="collapse" id="patientInfo-{{ $patient['id'] }}" aria-expanded="true">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="#info-{{ $patient['id'] }}"
                                                aria-controls="info" role="tab" data-bs-toggle="tab">Thông Tin Khám</a>
                                        </li>
                                        {{-- <li class="nav-item">
                                            <a class="nav-link" href="#diagnosis-{{ $patient['stt'] }}"
                                                aria-controls="diagnosis" role="tab" data-bs-toggle="tab">Chuẩn
                                                Đoán</a>
                                        </li> --}}
                                        {{-- <li class="nav-item">
                                            <a class="nav-link" href="#history-{{ $patient['stt'] }}"
                                                aria-controls="history" role="tab" data-bs-toggle="tab">Lịch Sử Khám
                                                Bệnh</a>
                                        </li> --}}
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content p-3">
                                        <!-- Tab 1: Thông tin bệnh nhân -->
                                        <div role="tabpanel" class="tab-pane fade show active"
                                            id="info-{{ $patient['id'] }}">
                                            <input type="hidden" name="_token"
                                                value="N3aCZEQBBHtMdURn9NrkZvXMdfVtQUf9WKa0L0fQ">
                                            <div class="mb-3 row">
                                                <label for="patientID" class="col-sm-2 col-form-label">Khoa khám</label>
                                                <div class="col-sm-10">
                                                    <input readonly value="{{ $patient['department']->department_name }}"
                                                        type="text" class="form-control" id="patientID" name="reg_pname">
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label for="patientID" class="col-sm-2 col-form-label">Thời gian</label>
                                                <div class="col-sm-10">
                                                    <input readonly value="{{ $patient['arrival_time'] }}" type="text"
                                                        class="form-control" id="patientID" name="reg_pname">
                                                </div>
                                            </div>

                                            <div class="mb-3 row">
                                                <label for="fullName" class="col-sm-2 col-form-label">Địa chỉ</label>
                                                <div class="col-sm-10">
                                                    <input readonly value="527 Sư Vạn Hạnh, Phường 12, Quận 10, Thành phố Hồ Chí Minh" type="text"
                                                        class="form-control" id="fullName" name="reg_pname">
                                                </div>
                                            </div>

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

        function step1General(stt, id) {
            $("#modal-next-department-general").modal('toggle')
            $("#current-patient-visit").val(id);
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
                success: function(res) {
                    alert('Thành công !')

                    window.location.reload()
                }
            })
        }


        function huyLichHen(patientVisitId) {
            if (!confirm('Bạn có chắc chắn muốn hủy lịch ?')) {
                return;
            }
            $.ajax({
                type: "GET",
                url: "/patients/cancle-appointment/" + patientVisitId,
                success: function(res) {
                    alert('Hủy lịch hẹn thành công !');

                    window.location.reload();
                },
                error: function(xhr) {
                    alert('Đã xảy ra lỗi !')
                }
            })
        }

        function createAppointment() {
            $.ajax({
                type: "POST",
                url: "/patients/create-appointment",
                data: {
                    'department_id': $("#department_id").val(),
                    'symptom': $("#symptom").val(),
                    'ngaykham': $("#ngaykham").val(),
                    '_token': '{{ csrf_token() }}',
                },
                success: function(res) {
                    alert('Đăng ký thành công !');

                    window.location.reload();
                },
                error: function(xhr) {
                    console.log(xhr);

                }

            })
        }

        function showModalCreateAppointment() {
            $("#modal-create-appointment").modal('toggle');
        }

        function showMap(video) {
            console.log(video);
            
            $(".modal-body").html(`
                        <video class="video-map" style="width: 75%" autoplay loop muted>
                            <source id="srcVideo" src="/assets/video/${video}" type="video/mp4">
                            </source>
                        </video>
                    `)
            $("#img-map-modal").modal('toggle')
        }
    </script>
@endpush
