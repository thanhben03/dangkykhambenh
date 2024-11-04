<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Models\Department;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function showPatient(Request $request)
    {
        $patients = Patient::query()
            ->when($request->name, function ($query) use ($request) {
                return $query->where('patients.name', 'LIKE', '%' . $request->name . '%');
            })
            ->when($request->cccd, function ($query) use ($request) {
                return $query->where('patients.nic', 'LIKE', '%' . $request->cccd . '%');
            })
            ->orderBy('created_at')
            ->get();

        $patients = PatientResource::make($patients)->resolve();

        return view('admin.quan-ly-benh-nhan', compact('patients'));
    }

    public function deletePatient($patient_id)
    {
        try {
            DB::beginTransaction();
            Patient::query()->where('id', $patient_id)->delete();
            PatientVisit::query()->where('patient_id', $patient_id)->delete();
            DB::commit();
            return response()->json([
                'msg' => 'ok'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 400);
        }
    }
    public function deleteDoctor($doctor_id)
    {
        try {
            DB::beginTransaction();
            User::query()->where('id', $doctor_id)->delete();
            DB::commit();
            return response()->json([
                'msg' => 'ok'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 400);
        }
    }

    public function showDoctor(Request $request)
    {
        $doctors = User::query()
            ->whereNot('user_type', 'admin')
            ->when($request->name, function ($query) use ($request) {
                return $query->where('patients.name', 'LIKE', '%' . $request->name . '%');
            })
            ->orderBy('created_at')
            ->get();
        $departments = Department::query()->get();

        return view('admin.quan-ly-bac-si', compact('doctors', 'departments'));
    }
    public function createDoctor (Request $request) {
        try {
            User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'department_id' => $request->department_id
            ]);

            return response()->json([
                'msg' => 'ok',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage(),
            ], 500);
        }
    }
}
