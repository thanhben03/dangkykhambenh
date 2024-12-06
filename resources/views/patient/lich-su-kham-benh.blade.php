@extends('layouts.app2')
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



                <div class="row">
                    <div class="col-md-12">
                        <!-- Card for Patient Info -->
                        <div class="card">

                                

                                    <!-- Tab 2: Lịch sử khám bệnh -->
                                    <div role="tabpanel" class="tab-pane" id="history-{{ $patient['id'] }}">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Ngày Khám</th>
                                                    <th>Khoa</th>
                                                    <th>Chuẩn Đoán</th>
                                                    <th>Kê Đơn Thuốc</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($patient['history'] as $history)
                                                    <tr>
                                                        <td>{{ $history->arrival_time }}</td>
                                                        <td>
                                                            @if ($history->kham_tq)
                                                                Khám tổng quát({{ $history->department->department_name }})
                                                            @else
                                                                {{ $history->department->department_name }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $history->diagnosis ?? '' }}</td>
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
                success: function(res) {
                    alert('Thành công !')

                    window.location.reload()
                }
            })
        }

        function nextDepartment() {
            let stt = $("#current-stt")
            let trieu_chung = $("#trieu_chung")
            let department_id = $("#department_id")
            $.ajax({
                type: "POST",
                url: "/next-department",
                data: {
                    stt: stt.val(),
                    trieu_chung: trieu_chung.val(),
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
