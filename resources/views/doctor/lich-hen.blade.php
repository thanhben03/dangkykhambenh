@extends('layouts.master')

@section('title')
Lịch Hẹn Bệnh Nhân
@endsection
@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid custom-content">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Lịch hẹn</h4>



                        </div>
                    </div>
                </div>
                <form action="">
                    <!-- Chọn lịch -->
                    <div class="form-group col-3">
                        <label for="appointment-date">Chọn ngày hẹn:</label>
                        <input name="date" value="{{$date}}" type="date" id="appointment-date" class="form-control">
                    </div>

                    <!-- Nút tìm kiếm -->
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">Tìm lịch hẹn</button>
                        <a href="/lich-hen" class="btn btn-warning">Xóa bộ lọc</a>
                    </div>
                </form>

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
                                    <td>{{ $item->bod }}</td>
                                    <td>{{ $item->trieu_chung }}</td>
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

@endpush
