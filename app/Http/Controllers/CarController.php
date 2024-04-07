<?php

namespace App\Http\Controllers;

use App\Models\BrandModel;
use App\Models\Car;
use App\Models\Clients;
use App\Models\Province;
use Illuminate\Http\Request;
use Facade\FlareClient\Http\Client;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CarImport;
use App\Models\Booking;
use App\Models\ClientCars;
use App\Models\ServiceCenter;
use App\Models\Profile;
use Carbon\Carbon;

class CarController extends Controller
{
    public function getClientCars()
    {
        $Item = ClientCars::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['car'] = Car::find($Item[$i]['car_id']);
                if ($Item[$i]['car']) {
                    $Item[$i]['license_plate'] = $Item[$i]['car']['license'];
                }
                $Item[$i]['client'] = Clients::find($Item[$i]['client_id']);
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getList()
    {
        $Item = Car::limit(20)->get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['brand_model'] = BrandModel::find($Item[$i]['brand_model_id']);
                $Item[$i]['province'] = Province::find($Item[$i]['province_id']);
                $Item[$i]['image'] = url($Item[$i]['image']);
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getListByKey($key)
    {
        $Item = Car::where('license', 'like', "%{$key}%")
            ->where('status', 'Available')
            ->limit(20)
            ->get()
            ->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['brand_model'] = BrandModel::find($Item[$i]['brand_model_id']);
                $Item[$i]['province'] = Province::find($Item[$i]['province_id']);
                if ($Item[$i]['image']) {
                    $Item[$i]['image'] = url($Item[$i]['image']);
                } else {
                    $Item[$i]['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getListByKeyAll($key)
    {
        $Item = Car::where('license', 'like', "%{$key}%")
            ->limit(20)
            ->get()
            ->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['brand_model'] = BrandModel::find($Item[$i]['brand_model_id']);
                $Item[$i]['client'] = Clients::find($Item[$i]['client_id']);
                $Item[$i]['province'] = Province::find($Item[$i]['province_id']);
                if ($Item[$i]['image']) {
                    $Item[$i]['image'] = url($Item[$i]['image']);
                } else {
                    $Item[$i]['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
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

        $companys = $request->company;


        $col = array('id', 'brand', 'client_id', 'province_id', 'color', 'image', 'type', 'license', 'mile', 'brand_model_id', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'brand', 'client_id', 'province_id', 'color', 'image', 'type', 'license', 'mile', 'brand_model_id', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = Car::select($col);

        if ($companys) {
            $D->whereIn('client_id', $companys);
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
                // if ($d[$i]->image) {
                //     $d[$i]->image = url($d[$i]->image);
                // } else {
                //     $d[$i]->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                // }


                $d[$i]->brand_model = BrandModel::find($d[$i]->brand_model_id);
                if ($d[$i]->brand_model) {
                    $img = explode('-', $d[$i]->brand_model->name);
                    $d[$i]->image = "https://asha-tech.co.th/asap/public/images/cars/" . $img[0] . "/" . $d[$i]->brand_model->name . ".png";
                }

                $d[$i]->brand_model = BrandModel::find($d[$i]->brand_model_id);
                $d[$i]->province = Province::find($d[$i]->province_id);
                $d[$i]->client = Clients::find($d[$i]->client_id);
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

        if (!isset($request->license)) {
            return $this->returnErrorData('[license] Data Not Found', 404);
        } else if (!isset($request->brand_model_id)) {
            return $this->returnErrorData('[brand_model_id] Data Not Found', 404);
        }

        DB::beginTransaction();

        try {

            $Item = new Car();
            $Item->license = $request->license;
            $Item->mile = $request->mile;
            $Item->color = $request->color;
            $Item->brand_model_id = $request->brand_model_id;
            $Item->client_id = $request->client_id;
            $Item->province_id = $request->province_id;
            $Item->status = $request->status;
            $Item->brand = $request->brand;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/cars/');
            }

            $Item->save();

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
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
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Car::where('id', $id)
            ->first();

        if ($Item) {
            $Item->brand_model = BrandModel::find($Item->brand_model_id);

            if ($Item->brand_model) {
                $img = explode('-', $Item->brand_model->name);
                $Item->image = "https://asha-tech.co.th/asap/public/images/cars/" . $img[0] . "/" . $Item->brand_model->name . ".png";
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function edit(Car $car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = Car::find($id);
            $Item->license = $request->license;
            $Item->mile = $request->mile;
            $Item->color = $request->color;
            $Item->brand_model_id = $request->brand_model_id;
            $Item->client_id = $request->client_id;
            $Item->province_id = $request->province_id;
            $Item->status = $request->status;
            $Item->brand = $request->brand;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/cars/');
            }

            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Car::find($id);
            $Item->delete();

            //log
            $userId = "admin";
            $type = 'ลบผู้ใช้งาน';
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


    public function get_car_by_license_plate(Request $request)
    {
        $license = $request->license;
        $idToken = $request->id_token;
        $displayName = $request->display_name;
        $pictureUrl = $request->picture_url;
        $userId = $request->user_id;

        $Item = Car::where('license', $license)
            ->first();

        if (!$Item) {
            return $this->returnSuccess('ไม่พบข้อมูลรถ', null);
        } else

            DB::beginTransaction();

        try {

            $Item->idToken = $idToken;
            $Item->displayName = $displayName;
            $Item->pictureUrl = $pictureUrl;
            $Item->userId = $userId;
            $Item->save();


            if ($Item) {
                // if ($Item->image) {
                //     $Item->image = url($Item->image);
                // } else {
                //     $Item->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                // }

                $Item->brand_model = BrandModel::find($Item->brand_model_id);
                if ($Item->brand_model) {
                    $img = explode('-', $Item->brand_model->name);
                    $Item->image = "https://asha-tech.co.th/asap/public/images/cars/" . $img[0] . "/" . $Item->brand_model->name . ".png";
                }
            }

            $Item->brand_model = BrandModel::find($Item->brand_model_id);
            DB::commit();
            return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }


    public function register(Request $request)
    {
        $loginBy = $request->login_by;

        if (!isset($request->license)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = Car::where('license', $request->license)
                ->first();

            if (!$Item) {
                return $this->returnErrorData('ป้ายทะเบียนและเลขไมล์ไม่ตรงกับในระบบ', 404);
            } else {
                if (!isset($Item->client_id)) {
                    return $this->returnErrorData('รถคันนี้ยังไม่มีการลงทะเบียน', 404);
                }

                // if ($Item->status == "Unavailable") {
                //     return $this->returnErrorData('รถคันนี้ถูกใช้งานอยู่ปัจจุบัน', 404);
                // }

                $Item->mile = $request->mile;
                // $Item->status = "Unavailable";

                $Item->save();
            }


            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function updateData(Request $request)
    {
        if (!isset($request->id)) {
            return $this->returnErrorData('[id] Data Not Found', 404);
        }

        DB::beginTransaction();

        try {
            $Item = Car::find($request->id);

            if (!$Item) {
                return $this->returnErrorData('ไม่พบรายการ', 404);
            }

            $Item->license = $request->license;
            $Item->mile = $request->mile;
            $Item->color = $request->color;
            $Item->brand_model_id = $request->brand_model_id;
            if ($request->status == "Available") {
                $Item->client_id = "";
                $Item->displayName = "";
                $Item->pictureUrl = "";
                $Item->userId = "";
            } else {
                $Item->client_id = $request->client_id;
            }

            $Item->province_id = $request->province_id;
            $Item->status = $request->status;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/cars/');
            }


            $Item->save();
            //

            //log
            $userId = "admin";
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
            $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }


    public function Import(Request $request)
    {
        ini_set('memory_limit', '16048M');

        $file = request()->file('file');
        $fileName = $file->getClientOriginalName();

        $Data = Excel::toArray(new CarImport(), $file);

        $data = $Data[0];

        if (count($data) > 0) {

            DB::beginTransaction();

            $insert_data = [];

            try {
                for ($i = 1; $i < count($data); $i++) {

                    $brand = BrandModel::where('name', $data[$i][3] . '-' . $data[$i][4])->first();
                    if (!$brand) {
                        $brand = new BrandModel();
                        $brand->name = $data[$i][3] . '-' . $data[$i][4];

                        $brand->save();
                    }

                    if (trim($data[$i][9])) {
                        $client = Clients::where('code', trim($data[$i][9]))->first();
                    }

                    $ItemCar = Car::where('license', trim($data[$i][8]))->first();
                    if (!$ItemCar) {
                        $ItemCar = new Car();
                    }

                    if (trim($data[$i][8]) != "" && trim($data[$i][10]) != "" && $brand->id != "") {
                        $ItemCar->body_number = trim($data[$i][1]);
                        $ItemCar->motor_number = trim($data[$i][2]);
                        $ItemCar->brand_model_id = $brand->id;
                        $ItemCar->body_type = trim($data[$i][5]);
                        $ItemCar->color = trim($data[$i][6]);
                        $ItemCar->car_type = trim($data[$i][7]);
                        $ItemCar->license = trim($data[$i][8]);
                        $ItemCar->brand = trim($data[$i][10]);
                        $ItemCar->client_id = $client->id ? $client->id : null;
                        $ItemCar->save();

                        $ItemClientCar = new ClientCars();
                        $ItemClientCar->client_id = $client->id;
                        $ItemClientCar->car_id = $ItemCar->id;
                        $ItemClientCar->save();
                    }



                    // $insert_data[] = array(
                    //     'body_number' => trim($data[$i][1]),
                    //     'motor_number' => trim($data[$i][2]),
                    //     'brand_model_id' => $brand->id,
                    //     'body_type' => trim($data[$i][5]),
                    //     'color' => trim($data[$i][6]),
                    //     'car_type' => trim($data[$i][7]),
                    //     'license' => trim($data[$i][8]),
                    //     'client_id' => $client->id ? $client->id : null
                    // );
                }

                //log
                $userId = "admin";
                $type = 'นำเข้าข้อมูล';
                $description = 'ผู้ใช้งาน ได้ทำการ ' . $type;
                $this->Log("admin", $description, $type);
                //

                DB::commit();

                return $this->returnSuccess('ดำเนินการสำเร็จ', $ItemCar);
            } catch (\Throwable $e) {

                DB::rollback();

                return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
            }
        }

        // if (!empty($insert_data)) {

        //     DB::beginTransaction();

        //     try {

        //         DB::table('cars')->insert($insert_data);

        //         for ($i = 1; $i < count($data); $i++) {
        //             $car = Car::where('license', trim($data[$i][9]))->first();
        //         }

        //         //log
        //         $type = 'นำเข้าข้อมูล';
        //         $description = 'ผู้ใช้งาน ได้ทำการ ' . $type;
        //         $this->Log("admin", $description, $type);
        //         //

        //         DB::commit();

        //         return $this->returnSuccess('นำเข้าข้อมูลสำเร็จ', $insert_data);
        //     } catch (\Throwable $e) {

        //         DB::rollback();

        //         return $this->returnErrorData('นำเข้าข้อมูลผิดพลาด ' . $e, 404);
        //     }
        // }
    }

    public function verifyLine(Request $request)
    {
        $userId = $request->user_id;
        $arr = [];
        if ($userId) {

            $cars = Car::where('userId', $userId)->get();

            if (!$cars) {

                return $this->returnSuccess('ไม่พบรายการ', []);
            } else {

                if (count($cars) <= 0) {

                    return $this->returnSuccess('ไม่พบรายการ', []);
                }

                foreach ($cars as $key => $value) {

                    array_push($arr, $value['id']);

                    // if ($cars[$key]->image) {
                    //     $cars[$key]->image = url($value['image']);
                    // } else {
                    //     $cars[$key]->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                    // }

                    // $cars->brand_model = BrandModel::find($value['brand_model_id']);
                }
                $col = array('id', 'status', 'code', 'date', 'time', 'client_id', 'car_id', 'service_center_id', 'reason', 'phone', 'name', 'evaluate', 'create_by', 'update_by', 'created_at', 'updated_at');


                $books = Booking::select($col)
                    ->whereIn('car_id', $arr)
                    ->get();


                foreach ($books as $key => $value) {

                    if ($value['status'] == "New") {
                        $books[$key]->status = "สร้างรายการจองสำเร็จ";
                    } else if ($value['status'] == "Process") {
                        $books[$key]->status = "ยืนยันการจองสำเร็จ";
                    } else if ($value['status'] == "Waiting") {
                        $books[$key]->status = "กำลังดำเนินการ";
                    } else if ($value['status'] == "Finish") {
                        $books[$key]->status = "รายการจองสิ้นสุดแล้ว";
                    } else if ($value['status'] == "Cancel") {
                        $books[$key]->status = "รายการจองถูกยกเลิก";
                    }

                    $books[$key]->car = Car::find($books[$key]->car_id);
                    // if ($books[$key]->car) {
                    //     if ($books[$key]->car->image) {
                    //         $books[$key]->car->image = url($books[$key]->car->image);
                    //     } else {
                    //         $books[$key]->car->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                    //     }
                    // }

                    // $books[$key]->car->brand_model = BrandModel::find($books[$key]->car->brand_model_id);

                    $books[$key]->car->brand_model = BrandModel::find($books[$key]->car->brand_model_id);
                    if ($books[$key]->car->brand_model) {
                        $img = explode('-', $books[$key]->car->brand_model->name);
                        $books[$key]->car->image = "https://asha-tech.co.th/asap/public/images/cars/" . $img[0] . "/" . $books[$key]->car->brand_model->name . ".png";
                    }

                    $books[$key]->service_center = ServiceCenter::find($books[$key]->service_center_id);
                    $books[$key]->profile = Profile::where('user_id', $userId)->first();
                    $books[$key]->profile->cars = Car::where('userId', $userId)->get();
                }

                // dd($books->profile);
                return $this->returnSuccess('ดำเนินการสำเร็จ', $books);
            }
        } else {
            return $this->returnSuccess('ไม่พบรายการ', []);
        }
    }

    public function getMyCars(Request $request)
    {
        $userId = $request->user_id;
        $arr = [];
        if ($userId) {

            $cars = Car::where('userId', $userId)->get();

            if (!$cars) {

                return $this->returnSuccess('ไม่พบรายการ', []);
            } else {

                if (count($cars) <= 0) {

                    return $this->returnSuccess('ไม่พบรายการ', []);
                }

                foreach ($cars as $key => $value) {

                    // if ($cars[$key]->image) {
                    //     $cars[$key]->image = url($value['image']);
                    // } else {
                    //     $cars[$key]->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                    // }

                    $cars[$key]->brand_model = BrandModel::find($value['brand_model_id']);
                    if ($cars[$key]->brand_model) {
                        $img = explode('-', $cars[$key]->brand_model->name);
                        $cars[$key]->image = "https://asha-tech.co.th/asap/public/images/cars/" . $img[0] . "/" . $cars[$key]->brand_model->name . ".png";
                    }
                    $cars[$key]->expire = $this->expireDate($cars[$key]->expire_date);
                }


                return $this->returnSuccess('ดำเนินการสำเร็จ', $cars);
            }
        } else {
            return $this->returnSuccess('ไม่พบรายการ', []);
        }
    }


    public function destroy_all(Request $request)
    {
        $loginBy = $request->login_by;
        $cars = $request->cars;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        DB::beginTransaction();

        try {

            for ($i = 0; $i < count($cars); $i++) {

                $Item = Car::find($cars[$i]['car_id']);

                $Item->delete();
            }



            //log
            $userId = $loginBy->code;
            $type = 'ลบลูกค้า';
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

    public function expireDate($date)
    {
        // Your date string
        $dateString = '2024-05-01';

        // Convert string to Carbon instance
        $expireDate = Carbon::createFromFormat('Y-m-d', $date);

        // Get the current date (now)
        $now = Carbon::now();

        // Check if the expire date is before the current date
        if ($expireDate->isPast()) {
            return true;
        }

        // Alternatively, you can compare the expire date directly
        if ($expireDate->lessThan($now)) {
            return true;
        }

        return false;
    }

    public function removeCar($id)
    {
        DB::beginTransaction();

        try {

            $Item = Car::find($id);
            $Item->displayName = null;
            $Item->pictureUrl = null;
            $Item->userId = null;
            $Item->save();

            //log
            // $userId = "";
            // $type = 'ลบผู้ใช้งาน';
            // $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type;
            // $this->Log($userId, $description, $type);
            //

            DB::commit();

            return $this->returnUpdate('ดำเนินการสำเร็จ');
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
}
