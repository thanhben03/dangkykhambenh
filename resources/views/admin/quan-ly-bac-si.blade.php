@extends('layouts.master')

@section('title')
Quản Lý Bác Sĩ
@endsection
@section('content')
    <!-- Modal -->
    <div class="modal fade" id="modal-create-doctor" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Tạo tài khoản bác sĩ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="">Khoa</label>
                        <select class="form-control" name="department_id" id="department_id">
                            @foreach ($departments as $item)
                                <option value="{{ $item->id }}">{{ $item->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input id="email" name="email" class="mb-3 form-control" type="text"
                        placeholder="Email đăng nhập">
                    <input id="password" name="password" class="mb-3 form-control" type="password" placeholder="Mật khẩu">

                    <!-- An element to toggle between password visibility -->
                    <input type="checkbox" onclick="showPass()">Hiển thị mật khẩu
                </div>
                <div class="modal-footer">
                    <button onclick="createDoctor()" type="button" class="btn btn-primary">Tạo tài khoản</button>
                </div>
            </div>
        </div>
    </div>
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Quản lý bác sĩ</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Tables</a></li>
                                    <li class="breadcrumb-item active">Quản lý bác sĩ</li>
                                </ol>
                            </div>

                        </div>
                        <button onclick="showModalCreateDoctor()" class="btn btn-primary mb-3">Tạo tài khoản</button>

                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-body">

                                <table id="" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr>
                                            {{-- <th>Họ tên</th> --}}
                                            <th>Email</th>
                                            <th>Khoa</th>
                                            <th></th>
                                        </tr>
                                    </thead>


                                    <tbody>
                                        @foreach ($doctors as $doctor)
                                            <tr>
                                                {{-- <td>{{ $doctor->name }}</td> --}}
                                                <td>{{ $doctor->email }}</td>
                                                <td>{{ $doctor->department->department_name }}</td>
                                                <td>
                                                    <button onclick="deleteDoctor({{ $doctor->id }})"
                                                        class="btn btn-danger">
                                                        Xóa
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->


            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->


    
    </div>
    <!-- end main content-->
@endsection

@push('js')
    <script>
        let table = new DataTable('#myTable');

        function showModalCreateDoctor() {
            $("#modal-create-doctor").modal('toggle');
        }

        function createDoctor() {
            $.ajax({
                type: "POST",
                url: "{{ route('crateDoctor') }}",
                data: {
                    'email': $("#email").val(),
                    'password': $("#password").val(),
                    'department_id': $("#department_id").val(),
                    '_token': "{{ csrf_token() }}",
                },
                success: function(res) {
                    alert('Tạo tài khoản thành công !')
                    window.location.reload();
                },
                error: function(xhr) {
                    alert("Đã xảy ra lỗi !")
                }
            })
        }

        function deleteDoctor(doctor_id) {
            if (!confirm('Bạn có chắc chắn muốn xóa ? ')) {
                return
            }

            $.ajax({
                type: "GET",
                url: "/delete-doctor/" + doctor_id,
                success: function(res) {
                    alert('Xóa thành công !')
                    window.location.reload()
                },
                error: function(xhr) {
                    alert('Đã xảy ra lỗi !')
                }
            })
        }

        function showPass() {
            let x = document.getElementById("password");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }
    </script>
@endpush
