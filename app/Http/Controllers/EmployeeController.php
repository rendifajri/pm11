<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader;
use App\Models\Employee;
use App\Models\User;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();
        $page = $request->query('page');
        $limit = $request->query('limit');
        $employee_pg = 0;
        if($page != null && $limit != null){
            $page--;
            $employee_pg = ceil($query->count() / $limit);
            $query->offset(($page * $limit));
            $query->limit($limit);
        }
        $employee = $query->orderBy("created_at", "desc")->get();
        foreach ($employee as $val) {
            $val->department = $val->department;
        }
        $res = [
            "status" => "success",
            "message" => "Get Employee list success",
            "response" => $employee,
            "response_total_page" => $employee_pg
        ];

        return response($res);
    }
    public function detail($id)
    {
        $employee = Employee::find($id);
        if($employee == null)
            throw new \ModelNotFoundException("Employee not found.");
        else {
            $res = [
                "status" => "success",
                "message" => "Get Employee success",
                "response" => $employee 
            ];
        }

        return response($res);
    }
    public function create(Request $request)
    {
        try{
            \DB::beginTransaction();
            $valid_arr = [
                "nik" => "required|unique:App\Models\Employee,nik",
                "department" => "required",
                "password" => "required",
                "name" => "required",
                "shift" => "required|integer|min:1|max:3"
            ];
            $valid = Validator::make($request->all(), $valid_arr);
            if ($valid->fails())
                throw new \ValidationException($valid);
            
            $employee = Employee::create([
                "nik" => $request->nik,
                "department" => $request->department,
                "name" => $request->name,
                "shift" => $request->shift
            ]);
            $res = [
                "status" => "success",
                "message" => "Create Employee success",
                "response" => $employee 
            ];
            \DB::commit();
            return response($res);
        } catch (\Throwable $e) {
            \DB::rollback();
            throw $e;
        }
    }
    public function update(Request $request, $id)
    {
        try{
            \DB::beginTransaction();
            $valid_arr = [
                "nik" => "required|unique:App\Models\Employee,nik,{$id},id",
                "department" => "required",
                "name" => "required",
                "shift" => "required|integer|min:1|max:3"
            ];
            $valid = Validator::make($request->all(), $valid_arr);
            if ($valid->fails())
                throw new \ValidationException($valid);

            $employee = Employee::find($id);
            $employee ->update([
                "nik" => $request->nik,
                "department" => $request->department,
                "name" => $request->name,
                "shift" => $request->shift
            ]);
            $res = [
                "status" => "success",
                "message" => "Update Employee success",
                "response" => $employee 
            ];
            \DB::commit();
            return response($res);
        } catch (\Throwable $e) {
            \DB::rollback();
            throw $e;
        }
    }
    public function delete($id)
    {
        $employee = Employee::find($id);
        if($employee == null)
            throw new \ModelNotFoundException("Employee not found.");
        else {
            $employee->delete();
            $res = [
                "status" => "success",
                "message" => "Delete Employee success",
                "response" => $employee 
            ];
        }

        return response($res);
    }
    public function import(Request $request)
    {
        try {
            \DB::beginTransaction();
            set_time_limit(0);
            $file = $request->file;
            $file_type = IOFactory::identify($file);

            if($file_type == 'Csv')
                $reader = new Reader\Csv();
            else if($file_type == 'Xlsx')
                $reader = new Reader\Xlsx();
            else if($file_type == 'Xls')
                $reader = new Reader\Xls();
            else
                throw \ValidationException::withMessages(['file' => "File invalid."]);
            
            $spreadsheet = $reader->load($file);
            $results = $spreadsheet->getActiveSheet()->toArray();
            $headers = $results[0];
            $rows = array_splice($results, 1);

            //echo "<pre>";
            //var_dump($rows);
            //echo "</pre>";
            $employee_arr = [];
            foreach ($rows as $row) {
                //$row[1] = nik
                $request = [
                    "department" => $row[3],
                    "nik" => $row[1],
                    "password" => $row[5],
                    "name" => $row[2],
                    "shift" => $row[4],
                ];
                $valid_arr = [
                    "nik" => "required|unique:App\Models\Employee,nik",
                    "department" => "required",
                    "password" => "required",
                    "name" => "required",
                    "shift" => "required|integer|min:1|max:3"
                ];
                $valid = Validator::make($request, $valid_arr);
                if ($valid->fails())
                    throw new \ValidationException($valid);
                $user_check = User::where("username", $request['nik'])->first();
                if($user_check != null)
                    throw \ValidationException::withMessages(['username' => "The username has already been taken."]);
                else{
                    $employee = Employee::create([
                        "nik" => $request['nik'],
                        "department" => $request['department'],
                        "name" => $request['name'],
                        "shift" => $request['shift']
                    ]);
                    array_push($employee_arr, $employee);
                }
            }
            $res = [
                "status" => "success",
                "message" => "Import Employee success",
                "response" => $employee_arr 
            ];

            \DB::commit();
            return response($res);
        } catch (\Throwable $e) {
            \DB::rollback();
            throw $e;
        }
    }
}
