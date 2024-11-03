@extends('layouts.app2')

@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Thông tin cá nhân</h4>

                        </div>
                    </div>
                </div>

                <!-- end page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                                @endif
                                @if (session()->has('msg'))
                                <div class="alert alert-success">{{session()->get('msg')}}</div>
                                    
                                @endif
                                <form action="{{ route('patient.profile.update') }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col mb-3">
                                                <label for="fullname" class="form-label">Họ tên</label>
                                                <input type="text" class="form-control" id="fullname" name="name"
                                                    value="{{ $user->name }}">
                                            </div>
                                            <div class="col mb-3">
                                                <label for="address" class="form-label">Địa chỉ</label>
                                                <input type="text" class="form-control" id="address" name="address"
                                                    value="{{ $user->address }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col mb-3">
                                                <label for="phone" class="form-label">Số điện thoại</label>
                                                <input type="text" class="form-control" id="phone" name="telephone"
                                                    value="{{ $user->telephone }}">
                                            </div>
                                            <div class="col mb-3">
                                                <label for="phone" class="form-label">Ngày sinh</label>
                                                <input type="date" class="form-control" id="phone" name="bod"
                                                    value="{{ $user->bod }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col mb-3">
                                                <label for="phone" class="form-label">Giới tính</label>
                                                <select class="form-control" name="sex" id="">
                                                    <option @selected($user->sex == 'Male') value="Male">Nam</option>
                                                    <option @selected($user->sex == 'Female') value="Female">Nữ</option>
                                                </select>
                                            </div>
                                            <div class="col mb-3">
                                                <label for="phone" class="form-label">Số CCCD</label>
                                                <input type="text" class="form-control" id="phone" name="nic"
                                                    value="{{ $user->nic }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Lưu</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                {{-- @if ($errors->has('password'))
                                    <div class="alert alert-danger">
                                        {{ $errors->first() }}

                                    </div>
                                @endif --}}
                                <form action="{{ route('patient.profile.change.password') }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col mb-3">
                                                <label for="phone" class="form-label">Mật khẩu hiện tại</label>
                                                <input type="password" class="form-control" id="phone"
                                                    name="current_password">
                                            </div>
                                            <div class="col mb-3">
                                                <label for="phone" class="form-label">Mật khẩu mới</label>
                                                <input type="password" class="form-control" id="phone" name="new_password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Lưu</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->


        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © Minia.
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Design & Develop by <a href="#!" class="text-decoration-underline">Themesbrand</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection
