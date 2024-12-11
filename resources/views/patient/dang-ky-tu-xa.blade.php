@php use Illuminate\Support\Facades\DB; @endphp
@extends('layouts.patient')

@section('content')
    <div style="display: none" class="loading">
        <img src="{{ asset('/icons/spinner.svg') }}" alt="">
    </div>
    <!-- Modal -->
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
    <div class="container mt-2">
        <h3 class="text-center mb-4">Đăng Ký Khám Bệnh</h3>
        <form id="formSubmit">
            @csrf
            <div class="row">
                <div class="col mb-3">
                    <label for="fullName" class="form-label">Họ và tên</label>
                    <input type="text" name="fullname" class="form-control" id="fullname" placeholder="Nhập họ và tên"
                        required>
                </div>
                <div class="col mb-3">
                    <label for="age" class="form-label">Ngày sinh</label>
                    <input type="date" name="birthday" class="form-control" id="birthday" placeholder="dd-MM-YYYY"
                        required>
                </div>
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label for="gender" class="form-label">Giới tính</label>
                    <select name="gender" class="form-select" id="gender" required>
                        <option selected disabled value="">Chọn giới tính</option>
                        <option id value="Nam">Nam</option>
                        <option id value="Nữ">Nữ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div class="col mb-3">
                    <label for="appointmentType" class="form-label">Số CCCD</label>
                    <input type="text" name="cccd" id="cccd-number" class="form-control" placeholder="Nhập số CCCD">
                </div>

            </div>
            <div class="row">
                <div class="col mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Nhập email">
                </div>
                <div class="col mb-3">
                    <label for="appointmentDate" class="form-label">Chọn khoa khám</label>
                    <select name="department" class="form-select" id="appointmentDate" required>
                        @foreach (DB::table('departments')->where('status', 0)->get() as $item)
                            <option value="{{ $item->id }}">{{ $item->department_name }}</option>
                        @endforeach

                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-sm-12 mb-3">
                    <label for="appointmentType" class="form-label">Địa chỉ</label>
                    <input type="text" name="address" id="address" class="form-control" placeholder="Nhập địa chỉ">
                </div>

                <div class="col mb-3">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="tel" name="phone" class="form-control" id="phone"
                        placeholder="Nhập số điện thoại" required>
                </div>
                <div class="col mb-3">
                    <label for="phone" class="form-label">Ngày khám</label>
                    <input type="date" name="ngaykham" class="form-control" id="phone" required>
                </div>
            </div>
            <div class="mb-3">
                <textarea name="symptom" class="form-control" rows="3" placeholder="Triệu chứng"></textarea>
            </div>
            <div class="d-flex justify-content-between">

                <button style="font-size: 20px" type="submit" class="btn btn-primary">Đăng ký</button>
                <div class="d-flex">

                    <select
                        style="    
                            border: 1px solid #d4d4d4;
                            background: cadetblue;
                            color: white;
                            text-align: center;
                            border-radius: 4px;
                            height: 45px;
                            font-size: 18px;
                            width: 100px;
                            "
                        onchange="showMap(this)" name="" id="select-map">
                        <option selected value="">Bản Đồ</option>
                        @foreach (DB::table('departments')->where('status', 0)->get() as $item)
                            <option value="{{ $item->id }}">{{ $item->department_name }}</option>
                        @endforeach
                    </select>
                    <a style="font-size: 20px" href="{{ route('patient.login') }}" class="mx-2 btn btn-success">Đăng
                        nhập</a>
                </div>





            </div>
        </form>

    </div>
@endsection

@push('js')
    {{-- <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script> --}}
    <script>

        function scan() {
            $.ajax({
                type: "POST",
                url: "{{ route('patient.scan') }}",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    $(".loading").css('display', 'flex')
                },
                success: function(res) {
                    $("#fullname").val(res.bn_name)
                    $("#birthday").val(res.dob)
                    $("#address").val(res.birthplace)
                    $("#cccd-number").val(res.cccd)
                    $("#gender").val(`${res.gender == 'Nam' ? 'Nam' : 'Nữ'}`)
                    $(".loading").css('display', 'none')

                },
                error: function(xhr) {
                    console.log(xhr.responseJSON)
                }
            })
        }

        document.getElementById("formSubmit").addEventListener("submit", function(event) {
            event.preventDefault()
            register()
        });

        function showMap(department) {

            $.ajax({
                type: "GET",
                url: "/get-map/" + department.value,
                success: function(res) {
                    $(".modal-body").html(`
                        <video style="width: 75%" autoplay loop muted>
                            <source id="srcVideo" src="/assets/video/${res.video}" type="video/mp4">
                            </source>
                        </video>
                    `)
                    $("#img-map-modal").modal('toggle');
                },
                error: function(xhr) {

                }
            })
        }

        function register() {
            let cccd = document.getElementById("cccd-number").value;
            if (cccd.length < 12) {
                alert('Cần nhập đủ 12 số CCCD"')
                return;
            }
            let form = $("#formSubmit").serialize();

            $.ajax({
                type: 'POST',
                url: '{{ route('patient.remote.register') }}',
                data: form,
                beforeSend: function() {
                    $(".loading").css('display', 'flex')
                },
                success: function(res) {
                    alert('Đăng ký thành công !')
                    $(".loading").css('display', 'none')
                    $(".modal-body").html(`
                        <video style="width: 75%" autoplay loop muted>
                            <source id="srcVideo" src="/assets/video/${res.video}" type="video/mp4">
                            </source>
                        </video>
                    `)
                    $("#img-map-modal").modal('toggle');
                    // window.location.reload()
                },
                error: function(xhr) {
                    console.log(xhr.responseJSON)
                    $(".loading").css('display', 'none')
                    alert(xhr.responseJSON.message)

                }
            })
        }
    </script>
@endpush
