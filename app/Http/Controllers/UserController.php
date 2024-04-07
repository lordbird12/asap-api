<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserImport;

class UserController extends Controller
{

    public function getListByDep(Request $request)
    {
        $loginBy = $request->login_by;

        $User = User::where('department_id', $loginBy->department_id)->get()->toarray();

        if (!empty($User)) {

            for ($i = 0; $i < count($User); $i++) {
                $User[$i]['No'] = $i + 1;

                if (isset($User[$i]['image']) || $User[$i]['image'] != "" || $User[$i]['image'] != null) {
                    $User[$i]['image']  = url($User[$i]['image']);
                } else {
                    $User[$i]['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $User);
    }

    public function getList()
    {
        $Item = User::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $User[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getListByDepepartment($id)
    {
        if ($id) {
            $Item = User::where('department_id', $id)->get();
        } else {
            $Item = User::get()->toarray();
        }


        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['department'] = Department::find($Item[$i]['department_id']);
                $Item[$i]['position'] = Position::find($Item[$i]['position_id']);
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $Status = $request->status;

        $col = array('id', 'department_id', 'position_id', 'fname', 'lname', 'email', 'phone', 'image', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'department_id', 'position_id', 'image', 'fname', 'lname', 'email', 'phone', 'create_by', 'status');

        $D = User::select($col);

        if (isset($Status)) {
            $D->where('status', $Status);
        }

        if ($orderby[$order[0]['column']]) {
            $D->orderby($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if ($search['value'] != '' && $search['value'] != null) {

            $D->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orWhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                //search with
                // $query = $this->withPermission($query, $search);
            });
        }

        $d = $D->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {

            //run no
            $No = (($page - 1) * $length);

            for ($i = 0; $i < count($d); $i++) {

                $No = $No + 1;
                $d[$i]->No = $No;
                if ($d[$i]->image) {
                    $d[$i]->image = url($d[$i]->image);
                } else {
                    $d[$i]->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
                $d[$i]->position = Position::find($d[$i]->position_id);
                $d[$i]->department = Department::find($d[$i]->department_id);
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $loginBy = $request->login_by;

        if (!isset($request->fname)) {
            return $this->returnErrorData('กรุณาระบุชื่อ fname ให้เรียบร้อย', 404);
        } else if (!isset($request->lname)) {
            return $this->returnErrorData('กรุณาระบุชื่อ lname ให้เรียบร้อย', 404);
        } else if (!isset($request->email)) {
            return $this->returnErrorData('กรุณาระบุอีเมล์ให้เรียบร้อย', 404);
        } else if (!isset($request->password)) {
            return $this->returnErrorData('กรุณาระบุชื่อรหัสผ่านให้เรียบร้อย', 404);
        } else
            //

            if (strlen($request->password) < 6) {
                return $this->returnErrorData('กรุณาระบุรหัสผ่านอย่างน้อย 6 หลัก', 404);
            }

        $checkUserId = User::where('email', $request->email)->first();
        if ($checkUserId) {
            return $this->returnErrorData('มีชื่อบัญชีผู้ใช้งาน ' . $request->email . ' ในระบบแล้ว', 404);
        }

        $checkEmail = User::where('phone', $request->phone)->first();
        if ($checkEmail) {
            return $this->returnErrorData('มีเบอร์ ' . $request->phone . ' ในระบบแล้ว', 404);
        }

        DB::beginTransaction();

        try {
            $Item = new User();
            $Item->code = $request->code;
            $Item->department_id = $request->department_id;
            $Item->position_id = $request->position_id;
            if ($request->password) {
                $Item->password = md5($request->password);
            }
            $Item->fname = $request->fname;
            $Item->lname = $request->lname;
            $Item->email = $request->email;
            $Item->phone = $request->phone;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/users/');
            }

            $Item->status = "Request";
            $Item->create_by = "admin";

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ';
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $Item = User::where('id', $id)
            ->first();

        if ($Item) {
            if ($Item->image) {
                $Item->image = url($Item->image);
            } else {
                $Item->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $loginBy = $request->login_by;

        if (!isset($request->id)) {
            return $this->returnErrorData('ไม่พบข้อมูล id', 404);
        }
        if (!isset($request->fname)) {
            return $this->returnErrorData('กรุณาระบุชื่อผู้ใช้งานให้เรียบร้อย', 404);
        } else if (!isset($request->email)) {
            return $this->returnErrorData('กรุณาระบุอีเมล์ให้เรียบร้อย', 404);
        } else if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        } else
        //

        {
            DB::beginTransaction();
        }

        try {

            $id = $request->id;

            // $checkName = User::where('email', $request->email)
            //     ->where('id', '!=', $id)
            //     ->first();

            // if ($checkName) {
            //     return $this->returnErrorData('มีอีเมล์ ' . $request->email . ' ในระบบแล้ว', 404);
            // }

            $Item = User::find($id);
            $Item->department_id = $request->department_id;
            $Item->position_id = $request->position_id;
            $Item->password = md5($request->password);
            $Item->fname = $request->fname;
            $Item->lname = $request->lname;
            $Item->email = $request->email;
            $Item->phone = $request->phone;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/users/');
            }

            $Item->status = "Request";
            $Item->create_by = "admin";

            $Item->save();
            //log
            $userId = "admin";
            $type = 'แก้ไขผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
        }
    }

    public function updateData(Request $request)
    {

        $loginBy = $request->login_by;

        if (!isset($request->id)) {
            return $this->returnErrorData('ไม่พบข้อมูล id', 404);
        }
        if (!isset($request->fname)) {
            return $this->returnErrorData('กรุณาระบุชื่อผู้ใช้งานให้เรียบร้อย', 404);
        } else if (!isset($request->email)) {
            return $this->returnErrorData('กรุณาระบุอีเมล์ให้เรียบร้อย', 404);
        } else if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        } else
        //

        {
            DB::beginTransaction();
        }

        try {

            $id = $request->id;

            $Item = User::find($id);
            $Item->code = $request->code;
            $Item->department_id = $request->department_id;
            $Item->position_id = $request->position_id;
            if ($request->password) {
                $Item->password = md5($request->password);
            }
            $Item->fname = $request->fname;
            $Item->lname = $request->lname;
            $Item->email = $request->email;
            $Item->phone = $request->phone;

            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/users/');
            }

            $Item->status = "Yes";
            $Item->create_by = $loginBy->code;

            $Item->save();
            //log
            $userId = $loginBy->code;
            $type = 'แก้ไขผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function getProfileUser(Request $request)
    {

        $Item = User::where('id', $request->login_id)
            ->first();

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function updateProfileUser(Request $request)
    {
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        $check = Permission::find($request->permission_id)->first();
        if ($check) {
            return $this->returnErrorData('ไม่พบสิทธิ์นี้ในระบบอยู่แล้ว', 404);
        }

        DB::beginTransaction();

        try {

            $Item = User::find($loginBy->id);

            $Item->name = $request->name;
            $Item->email = $request->email;
            $Item->phone = $request->phone;
            $Item->permission_id = $request->permission_id;

            $Item->update_by = "admin";
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        DB::beginTransaction();

        try {

            $Item = User::find($id);

            $Item->email = $Item->email . '_del_' . date('YmdHis');
            $Item->save();

            //log
            $userId = $loginBy->code;
            $type = 'ลบผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $Item->email;
            $this->Log($userId, $description, $type);
            //

            $Item->delete();

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
        }
    }

    public function destroy_all(Request $request)
    {
        $loginBy = $request->login_by;
        $users = $request->users;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        DB::beginTransaction();

        try {

            for ($i = 0; $i < count($users); $i++) {

                $Item = User::find($users[$i]['user_id']);

                $Item->email = $Item->email . '_del_' . date('YmdHis');
                $Item->save();
            }



            //log
            $userId = $loginBy->code;
            $type = 'ลบผู้ใช้งาน';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $Item->email;
            $this->Log($userId, $description, $type);
            //

            $Item->delete();

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function createUserAdmin(Request $request)
    {
        if (!isset($request->email)) {
            return $this->returnErrorData('[email] ไม่มีข้อมูล', 404);
        } else if (!isset($request->fname)) {
            return $this->returnErrorData('[fname] ไม่มีข้อมูล', 404);
        } else if (!isset($request->password)) {
            return $this->returnErrorData('[password] ไม่มีข้อมูล', 404);
        }

        $checkName = User::where(function ($query) use ($request) {
            $query->orwhere('email', $request->email)
                ->orWhere('phone', $request->phone);
        })
            ->first();

        if ($checkName) {
            return $this->returnErrorData('มีผู้ใช้งานนี้ในระบบแล้ว', 404);
        } else {

            DB::beginTransaction();

            try {

                //
                $Item = new User();
                $Item->password = md5($request->password);
                $Item->name = $request->name;
                $Item->email = $request->email;
                $Item->phone = $request->phone;

                $Item->status = "Yes";
                $Item->create_by = "admin";


                $Item->save();

                //log
                $userId = "admin";
                $type = 'เพิ่ม admin';
                $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
                $this->Log($userId, $description, $type);
                //

                DB::commit();

                return $this->returnSuccess('ดำเนินการสำเร็จ', []);
            } catch (\Throwable $e) {

                DB::rollback();

                return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
            }
        }
    }

    public function ResetPasswordUser(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($id)) {
            return $this->returnErrorData('ไม่พบข้อมูล id', 404);
        } else if (!isset($request->password)) {
            return $this->returnErrorData('กรุณาระบุรหัสผ่านให้เรียบร้อย', 404);
        } else if (!isset($request->new_password)) {
            return $this->returnErrorData('กรุณาระบุรหัสผ่านใหม่ให้เรียบร้อย', 404);
        } else if (!isset($request->confirm_new_password)) {
            return $this->returnErrorData('กรุณาระบุรหัสผ่านใหม่อีกครั้ง', 404);
        } else if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        if (strlen($request->new_password) < 6) {
            return $this->returnErrorData('กรุณาระบุรหัสผ่านอย่างน้อย 6 หลัก', 404);
        }

        if ($request->new_password != $request->confirm_new_password) {
            return $this->returnErrorData('รหัสผ่านไม่ตรงกัน', 404);
        }

        DB::beginTransaction();

        try {

            $Item = User::find($id);

            if ($Item->password == md5($request->password)) {

                $Item->password = md5($request->new_password);
                $Item->updated_at = Carbon::now()->toDateTimeString();
                $Item->save();

                DB::commit();

                return $this->returnUpdate('ดำเนินการสำเร็จ');
            } else {

                return $this->returnErrorData('รหัสผ่านไม่ถูกต้อง', 404);
            }
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
        }
    }

    public function ForgotPasswordUser(Request $request)
    {

        $email = $request->email;

        $Item = User::where('email', $email)->where('status', 'Yes')->first();

        if (!empty($Item)) {

            //random string
            $length = 8;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            //

            $newPasword = md5($randomString);

            DB::beginTransaction();

            try {

                $Item->password = $newPasword;
                $Item->save();

                $title = 'รหัสผ่านใหม่';
                $text = 'รหัสผ่านใหม่ของคุณคือ  ' . $randomString;
                $type = 'Forgot Password';

                // //send line
                // if ($Item->line_token) {
                //     $this->sendLine($Item->line_token, $text);
                // }

                //send email
                if ($Item->email) {
                    $this->sendMail($Item->email, $text, $title, $type);
                }

                DB::commit();

                return $this->returnUpdate('ดำเนินการสำเร็จ');
            } catch (\Throwable $e) {

                DB::rollback();

                return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ', 404);
            }
        } else {
            return $this->returnErrorData('ไม่พบอีเมล์ในระบบ ', 404);
        }
    }

    public function Import(Request $request)
    {
        ini_set('memory_limit', '16048M');

        $file = request()->file('file');
        $fileName = $file->getClientOriginalName();

        $Data = Excel::toArray(new UserImport(), $file);

        $data = $Data[0];

        $insert_data = [];

        if (count($data) > 0) {

            for ($i = 0; $i < count($data) - 1; $i++) {

                $user = User::where('code', trim($data[$i][1]))->first();

                if (!isset($user)) {

                    if (trim($data[$i][3]) == "01") {
                        $position_id = 3;
                    } else if (trim($data[$i][3]) == "02") {
                        $position_id = 1;
                    } else if (trim($data[$i][3]) == "03") {
                        $position_id = 2;
                    }
                    // $position = Position::where('name', trim($data[$i][3]))->first();
                    // if (!isset($position)) {
                    //     $position = new Position();
                    //     $position->name =  trim($data[$i][3]);
                    //     $position->save();
                    // }

                    if (trim($data[$i][3]) == "Group1") {
                        $department_id = 1;
                    } else if (trim($data[$i][3]) == "Group2") {
                        $department_id = 2;
                    } else if (trim($data[$i][3]) == "Group3") {
                        $department_id = 3;
                    } else if (trim($data[$i][3]) == "Group4") {
                        $department_id = 4;
                    } else if (trim($data[$i][3]) == "Group5") {
                        $department_id = 5;
                    }

                    // $department =  Department::where('name', trim($data[$i][4]))->first();
                    // if (!isset($department)) {
                    //     $department = new Department();
                    //     $department->name =  trim($data[$i][4]);
                    //     $department->save();
                    // }

                    $datas = explode(" ", $data[$i][2]);

                    if ($datas) {
                        $lname = $datas[count($datas) - 1];
                        $fname = str_replace($lname, "", $data[$i][2]);
                    } else {
                        $lname = "";
                        $fname = "";
                    }

                    if (trim($data[$i][1]) != "" && trim($data[$i][2]) != "" && trim($data[$i][3]) != "" && trim($data[$i][4]) != "" && isset($position) && isset($department)) {
                        $insert_data[] = [
                            'code' => trim($data[$i][1]),
                            'fname' => $fname,
                            'lname' => $lname,
                            'position_id' => $position_id,
                            'department_id' => $department_id,
                            'phone' => trim($data[$i][5]),
                            'email' => trim($data[$i][6]),
                            'password' => md5(trim($data[$i][1])),
                        ];
                    }
                }
            }
        }

        if (!empty($insert_data)) {

            DB::beginTransaction();

            try {

                DB::table('users')->insert($insert_data);

                //log
                $type = 'นำเข้าข้อมูล';
                $description = 'ผู้ใช้งาน ได้ทำการ ' . $type;
                $this->Log("admin", $description, $type);
                //

                DB::commit();

                return $this->returnSuccess('นำเข้าข้อมูลสำเร็จ', $insert_data);
            } catch (\Throwable $e) {

                DB::rollback();

                return $this->returnErrorData('นำเข้าข้อมูลผิดพลาด ' . $e, 404);
            }
        }
    }

    // public function Import(Request $request)
    // {
    //     ini_set('memory_limit', '16048M');

    //     $file = request()->file('file');
    //     $fileName = $file->getClientOriginalName();

    //     $Data = Excel::toArray(new UserImport(), $file);
    //     $data = $Data[0];
    //     $this->addData($data);
    // }


    // function addData($data)
    // {
    //     ini_set('memory_limit', '16048M');

    //     $file = request()->file('file');
    //     $fileName = $file->getClientOriginalName();

    //     $Data = Excel::toArray(new UserImport(), $file);

    //     $data = $Data[0];

    //     $insert_data = [];


    //     if (count($data) > 0) {


    //         for ($i = 1; $i < count($data); $i++) {

    //             $user = User::where('code', $data[$i][1])->first();

    //             if (!$user) {
    //                 $position = Position::where('name', trim($data[$i][3]))->first();
    //                 if (!isset($position)) {
    //                     $position = new Position();
    //                     $position->name =  trim($data[$i][3]);
    //                     $position->save();
    //                 }
    //                 dd($position);
    //                 $department = Department::where('name', trim($data[$i][4]))->first();
    //                 if (!isset($department)) {
    //                     $department = new Department();
    //                     $department->name =  trim($data[$i][4]);
    //                     $department->save();
    //                 }

    //                 $datas = explode(" ", $data[$i][2]);

    //                 if ($datas) {
    //                     $lname = $datas[count($datas) - 1];
    //                     $fname = str_replace($lname, "", $datas);
    //                 } else {
    //                     $lname = "";
    //                     $fname = "";
    //                 }

    //                 if (trim($data[$i][1]) != "" && trim($data[$i][2]) != "" && trim($data[$i][3]) != "" && trim($data[$i][4]) != "") {
    //                     $insert_data[] = [

    //                         'code' => trim($data[$i][1]),
    //                         'fname' => $fname,
    //                         'lname' => $lname,
    //                         'position_id' => $position->id,
    //                         'department_id' => $department->id,
    //                         'phone' => trim($data[$i][5]),
    //                         'email' => trim($data[$i][6])

    //                     ];
    //                 }
    //             }
    //         }
    //     }
    //     if (!empty($insert_data)) {

    //         DB::beginTransaction();

    //         try {

    //             DB::table('users')->insert($insert_data);

    //             //log
    //             $type = 'นำเข้าข้อมูล';
    //             $description = 'ผู้ใช้งาน ได้ทำการ ' . $type;
    //             $this->Log("admin", $description, $type);
    //             //

    //             DB::commit();

    //             return $this->returnSuccess('นำเข้าข้อมูลสำเร็จ', $insert_data);
    //         } catch (\Throwable $e) {

    //             DB::rollback();

    //             return $this->returnErrorData('นำเข้าข้อมูลผิดพลาด ' . $e, 404);
    //         }
    //     }
    // }
}
