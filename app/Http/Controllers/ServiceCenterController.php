<?php

namespace App\Http\Controllers;

use App\Models\ServiceCenter;
use App\Models\Services;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ServiceCenterImport;

class ServiceCenterController extends Controller
{
    public function getList()
    {
        $Item = ServiceCenter::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    public function getListByLatLonRecommend(Request $request)
    {

        $Item = ServiceCenter::where('recommend', 'Yes')->get()->toarray();


        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                try {
                    $Item[$i]['km'] = $this->haversineDistance($request->lat, $request->lon, $Item[$i]['lat'], $Item[$i]['lon']);
                } catch (\Throwable $e) {

                    $Item[$i]['km'] = 100;
                }
                if (isset($Item[$i]['km']) && $Item[$i]['km'] != 100) {
                    $Item[$i]['km'] = $Item[$i]['km'] / 1000;
                    $Item[$i]['km'] = number_format($Item[$i]['km'], 3);
                }
            }
        }


        $this->array_sort_by_column($Item, 'km');

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', array_slice($Item, 0, 10));
    }



    public function getListByLatLon(Request $request)
    {

        $Item = ServiceCenter::get()->toarray();


        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                try {
                    $Item[$i]['km'] = $this->haversineDistance($request->lat, $request->lon, $Item[$i]['lat'], $Item[$i]['lon']);
                } catch (\Throwable $e) {

                    $Item[$i]['km'] = 100;
                }
                if (isset($Item[$i]['km']) && $Item[$i]['km'] != 100) {
                    $Item[$i]['km'] = $Item[$i]['km'] / 1000;
                    $Item[$i]['km'] = number_format($Item[$i]['km'], 3);
                }
            }
        }


        $this->array_sort_by_column($Item, 'km');

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', array_slice($Item, 0, 5));


        // Example usage
        $lat1 = 37.7749;
        $lng1 = -122.4194;
        $lat2 = 34.0522;
        $lng2 = -118.2437;

        $distance = $this->haversineDistance($lat1, $lng1, $lat2, $lng2);

        // echo "Distance between the two points: " . $distance . " km\n";
        // $Item = ServiceCenter::get()->toarray();

        // if (!empty($Item)) {

        //     for ($i = 0; $i < count($Item); $i++) {
        //         $Item[$i]['No'] = $i + 1;
        //     }
        // }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', "Distance between the two points: " . $distance . " km\n");
    }



    public function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = array();
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }

    public function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        // Earth radius in kilometers (you can use 3959 for miles)
        $earthRadius = 6371;

        // Convert latitude and longitude from degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);

        // Calculate the change in coordinates
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        // Haversine formula
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) * sin($deltaLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Calculate the distance
        $distance = $earthRadius * $c;

        return $distance;
    }

    public function getPage(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $type = $request->type;

        $col = array('id', 'brand', 'name', 'code', 'recommend', 'email', 'phone', 'phone2', 'phone3', 'phone4', 'address', 'lat', 'lon', 'image', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'brand', 'name', 'code', 'recommend', 'email', 'phone', 'phone2', 'phone3', 'phone4', 'address', 'lat', 'lon', 'image', 'create_by');

        $D = ServiceCenter::select($col);

        // $D->orderby('name', 'asc');

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
                }
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

        if (!isset($request->name)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = new ServiceCenter();
            $Item->name = $request->name;
            $Item->address = $request->address;
            $Item->email = $request->email;
            $Item->phone = $request->phone;
            $Item->phone2 = $request->phone2;
            $Item->phone3 = $request->phone3;
            $Item->phone4 = $request->phone4;
            $Item->lat = $request->lat;
            $Item->lon = $request->lon;
            $Item->code = $request->code;
            $Item->brand = $request->brand;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/service_centers/');
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
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceCenter  $service_center
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = ServiceCenter::where('id', $id)
            ->first();

        if ($Item) {
            if ($Item->image) {
                $Item->image = url($Item->image);
            }
            // $Item->services = Services::where('service_center_id', $Item->id)->get();
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceCenter  $service_center
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceCenter $service_center)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceCenter  $service_center
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
            $Item = ServiceCenter::find($id);
            $Item->name = $request->name;
            $Item->address = $request->address;
            $Item->email = $request->email;
            $Item->phone = $request->phone;
            $Item->phone2 = $request->phone2;
            $Item->phone3 = $request->phone3;
            $Item->phone4 = $request->phone4;
            $Item->lat = $request->lat;
            $Item->lon = $request->lon;
            $Item->code = $request->code;
            $Item->brand = $request->brand;
            if ($request->image && $request->image != null && $request->image != 'null') {
                $Item->image = $this->uploadImage($request->image, '/images/service_centers/');
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
     * @param  \App\Models\ServiceCenter  $service_center
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = ServiceCenter::find($id);
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

        $Data = Excel::toArray(new ServiceCenterImport(), $file);
        $data = $Data[0];
        $this->addData($data);
    }


    function addData($items)
    {
        $itemsTemp = [];
        if (count($items) > 1000) {
            $itemsTemp = array_slice($items, 1000);
            $items = array_slice($items, 0, 1000);
        }
        $n = 0;
        $insert = [];
        foreach ($items as $item) {
            if ($n != 0) {
                $Service = ServiceCenter::where('name', $item[2] . '-' . $item[3])
                    ->where('code', $item[1])
                    ->first();
                   
                if (!$Service) {
                    $insert[] = [
                        'code' => trim($item[1]),
                        'name' => trim($item[2]),
                        'email' => trim($item[3]),
                        'phone' => trim($item[4]),
                        'phone2' => trim($item[5]),
                        'phone3' => trim($item[6]),
                        'phone4' => trim($item[7]),
                        'address' => trim($item[8]),
                        'lat' => trim($item[9]),
                        'lon' => trim($item[10]),
                        'brand' => trim($item[11])
                    ];
                }
               
            }
            $n++;
        }

        if (DB::table('service_centers')->insert($insert)) {
            if ($itemsTemp) {
                $this->addData($itemsTemp);
            }
        } else {
            return false;
        }
        return true;
    }

    public function destroy_all(Request $request)
    {
        $loginBy = $request->login_by;
        $service_centers = $request->service_centers;

        if (!isset($loginBy)) {
            return $this->returnErrorData('ไม่พบข้อมูลผู้ใช้งาน กรุณาเข้าสู่ระบบใหม่อีกครั้ง', 404);
        }

        DB::beginTransaction();

        try {

            for ($i = 0; $i < count($service_centers); $i++) {

                $Item = ServiceCenter::find($service_centers[$i]['service_center_id']);

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
