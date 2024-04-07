<?php

namespace App\Http\Controllers;

use App\Imports\ClientImport;
use App\Models\Booking;
use App\Models\BrandModel;
use App\Models\Car;
use App\Models\ClientCars;
use App\Models\Clients;
use App\Models\Province;
use App\Models\Ticket;
use Facade\FlareClient\Http\Client;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ClientsController extends Controller
{
    public function getList()
    {
        $Item = Clients::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
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
        $status = $request->status;


        $col = array('id', 'company', 'name', 'phone', 'email', 'status', 'expire_date', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'company', 'name', 'phone', 'email', 'status', 'expire_date', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = Clients::select($col);

        if (isset($status)) {
            $D->where('status', $status);
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
                $d[$i]->cars = ClientCars::where('client_id', $d[$i]->id)->get();

                foreach ($d[$i]->cars as $key => $value) {
                    $d[$i]->cars[$key]->car = Car::find($value['car_id']);
                }

                $d[$i]->counts = count($d[$i]->cars);
            }
        }

        $sortedByCount = $d->sortBy('counts');

        $d->data = $sortedByCount;


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
        $cars = $request->cars;

        if (!isset($request->name)) {
            return $this->returnErrorData('[name] Data Not Found', 404);
        } else if (!isset($request->phone)) {
            return $this->returnErrorData('[phone] Data Not Found', 404);
        } else if (!isset($request->email)) {
            return $this->returnErrorData('[email] Data Not Found', 404);
        }

        $check = Clients::where('phone', $request->phone)->first();
        if ($check) {
            return $this->returnErrorData('มีข้อมูล phone ในระบบแล้ว', 404);
        }

        DB::beginTransaction();

        try {
            $prefix = "#C-";
            $id = IdGenerator::generate(['table' => 'clients', 'field' => 'code', 'length' => 9, 'prefix' => $prefix]);

            $Item = new Clients();
            $Item->code = $id;
            $Item->company = $request->company;
            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->status = $request->status;
            $Item->expire_date = $request->expire_date;
            $Item->department_id = $request->department_id;
            $Item->save();

            foreach ($cars as $key => $value) {
                $ItemCar = new ClientCars();
                $ItemCar->client_id = $Item->id;
                $ItemCar->car_id = $value['car_id'];
                $ItemCar->save();

                $ItemUpdateCar = Car::find($value['car_id']);
                if ($ItemUpdateCar) {
                    $ItemUpdateCar->client_id =  $Item->id;
                    $ItemUpdateCar->save();
                }
            }



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
     * @param  \App\Models\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Clients::where('id', $id)
            ->first();

        if ($Item) {

            $Item->cars = ClientCars::where('client_id', $id)->get();



            foreach ($Item->cars as $key => $value) {
                $Item->cars[$key]->car = Car::find($Item->cars[$key]->car_id);
                if ($Item->cars[$key]->car) {
                    if ($Item->cars[$key]->car->province_id) {
                        $Item->cars[$key]->car->province = Province::find($Item->cars[$key]->car->province_id);
                    }

                    if ($Item->cars[$key]->car->image) {
                        $Item->cars[$key]->car->image = url($Item->cars[$key]->car->image);
                    } else {
                        $Item->cars[$key]->car->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                    }

                    $Item->cars[$key]->car->brand_model = BrandModel::find($Item->cars[$key]->car->brand_model_id);
                    if ($Item->cars[$key]->image) {
                        $Item->cars[$key]->image = url($Item->cars[$key]->image);
                    } else {
                        $Item->cars[$key]->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                    }
                }
            }
            $Item->ticket = Ticket::where('client_id', $id)->get();
            $Item->books = Booking::where('client_id', $id)->get();
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function edit(Clients $clients)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;
        $cars = $request->cars;

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else if (!isset($request->name)) {
            return $this->returnErrorData('[name] Data Not Found', 404);
        } else if (!isset($request->phone)) {
            return $this->returnErrorData('[phone] Data Not Found', 404);
        } else if (!isset($request->email)) {
            return $this->returnErrorData('[email] Data Not Found', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = Clients::find($id);
            $Item->code = $id;
            $Item->company = $request->company;
            $Item->name = $request->name;
            $Item->phone = $request->phone;
            $Item->email = $request->email;
            $Item->status = $request->status;
            $Item->expire_date = $request->expire_date;
            $Item->department_id = $request->department_id;
            $Item->save();

            $ItemDelete = ClientCars::where('client_id', $id)->delete();

            foreach ($cars as $key => $value) {

                $ItemCar = new ClientCars();
                $ItemCar->client_id = $Item->id;
                $ItemCar->car_id = $value['car_id'];
                $ItemCar->save();


                $ItemUpdateCar = Car::find($value['car_id']);
                if ($ItemUpdateCar) {
                    $ItemUpdateCar->client_id =  $Item->id;
                    $ItemUpdateCar->save();
                }
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Clients::find($id);
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

    public function Import(Request $request)
    {
        ini_set('memory_limit', '16048M');

        $file = request()->file('file');
        $fileName = $file->getClientOriginalName();

        $Data = Excel::toArray(new ClientImport(), $file);

        $data = $Data[0];

        if (count($data) > 0) {



            $insert_data = [];

            for ($i = 1; $i < count($data); $i++) {

                if (trim($data[$i][5]) == "Group1") {
                    $department_id = 1;
                } else if (trim($data[$i][5]) == "Group2") {
                    $department_id = 2;
                } else if (trim($data[$i][5]) == "Group3") {
                    $department_id = 3;
                } else if (trim($data[$i][5]) == "Group4") {
                    $department_id = 4;
                } else if (trim($data[$i][5]) == "Group5") {
                    $department_id = 5;
                }

                $insert_data[] = array(
                    'code' => trim($data[$i][0]),
                    'company' => trim($data[$i][1]),
                    'name' => trim($data[$i][2]),
                    'phone' => trim($data[$i][3]),
                    'email' => trim($data[$i][4]),
                    'department_id' => $department_id,
                );
            }
        }

        if (!empty($insert_data)) {

            DB::beginTransaction();

            try {

                DB::table('clients')->insert($insert_data);

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

    public function getListByKey($key)
    {
        $Item = Clients::where('company', 'like', "%{$key}%")
            ->limit(20)
            ->get()
            ->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function destroy_all(Request $request)
    {
        $loginBy = $request->login_by;
        $clients = $request->clients;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        DB::beginTransaction();

        try {

            for ($i = 0; $i < count($clients); $i++) {

                $Item = Clients::find($clients[$i]['client_id']);

                if ($Item) {
                    ClientCars::where('client_id', $clients[$i]['client_id'])->delete();
                }

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
}
