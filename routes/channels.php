<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('department.{departmentId}', function ($user, $departmentId) {
    // Kiểm tra nếu user là bác sĩ của khoa cụ thể
    return $user->department_id == $departmentId;
});

Broadcast::channel('standby.1', function ($departmentId) {
    // Kiểm tra nếu user là bác sĩ của khoa cụ thể
    return true;
});