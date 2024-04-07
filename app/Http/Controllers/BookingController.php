<?php

namespace App\Http\Controllers;

use App\Exports\ActivityBookExport;
use App\Exports\BookingHistoryExport;
use App\Models\ActivityBook;
use App\Models\Booking;
use App\Models\BookingEva;
use App\Models\BookingService;
use App\Models\BrandModel;
use App\Models\Car;
use App\Models\ClientCars;
use App\Models\Clients;
use App\Models\Department;
use App\Models\ServiceCenter;
use App\Models\Services;
use App\Models\User;
use Facade\FlareClient\Http\Client;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function getListByDep(Request $request, $id)
    {
        $Clients = Clients::where('department_id', $id)->get();
        $Users = $request->users;

        $Item = [];
        foreach ($Clients as $key => $value) {
            $data = Booking::where('client_id', $value['id'])
                ->where('status', '<>', 'New')->get();
            foreach ($data as $key => $value) {
                array_push($Item, $value);
            }
        }

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['service_center'] = ServiceCenter::find($Item[$i]['service_center_id']);
                $Item[$i]['services'] = BookingService::where('booking_id', $Item[$i]['id'])->get();
                $Item[$i]['car'] = Car::find($Item[$i]['car_id']);
                if ($Item[$i]['car']['image']) {
                    $Item[$i]['car']['image'] = url($Item[$i]['car']['image']);
                } else {
                    $Item[$i]['car']['image']  = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
                $Item[$i]['car']['brand_model'] = BrandModel::find($Item[$i]['car']['brand_model_id']);
                foreach ($Item[$i]['services'] as $key => $value) {
                    $Item[$i]['services'][$key]->service = Services::find($Item[$i]['services'][$key]->service_id);
                    $Item[$i]['services'][$key]->service->image = url($Item[$i]['services'][$key]->service->image);
                }
                $Item[$i]['activitys'] = ActivityBook::where('booking_id', $Item[$i]['id'])->get();
                foreach ($Item[$i]['activitys'] as $key => $value) {
                    $Item[$i]['activitys'][$key]['user'] = User::where('code', $value['create_by'])->first();
                    if ($Item[$i]['activitys'][$key]['user']) {
                        if ($Item[$i]['activitys'][$key]['user']['image']) {
                            $Item[$i]['activitys'][$key]['user']['image'] = url($Item[$i]['activitys'][$key]['user']['image']);
                        } else {
                            $Item[$i]['activitys'][$key]['user']['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
                        }
                    }
                }
            }
        }

        $ItemNew = [];

        foreach ($Clients as $key => $value) {
            $data = Booking::where('client_id', $value['id'])
                ->where('status', 'New')->get();
            foreach ($data as $key => $value) {
                array_push($ItemNew, $value);
            }
        }


        if (!empty($ItemNew)) {

            for ($i = 0; $i < count($ItemNew); $i++) {
                $ItemNew[$i]['No'] = $i + 1;
                $ItemNew[$i]['service_center'] = ServiceCenter::find($ItemNew[$i]['service_center_id']);
                $ItemNew[$i]['services'] = BookingService::where('booking_id', $ItemNew[$i]['id'])->get();
                $ItemNew[$i]['car'] = Car::find($ItemNew[$i]['car_id']);
                if ($ItemNew[$i]['car']['image']) {
                    $ItemNew[$i]['car']['image'] = url($ItemNew[$i]['car']['image']);
                } else {
                    $ItemNew[$i]['car']['image']  = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
                $ItemNew[$i]['car']['brand_model'] = BrandModel::find($ItemNew[$i]['car']['brand_model_id']);
                foreach ($ItemNew[$i]['services'] as $key => $value) {
                    $ItemNew[$i]['services'][$key]->service = Services::find($ItemNew[$i]['services'][$key]->service_id);
                    $ItemNew[$i]['services'][$key]->service->image = url($ItemNew[$i]['services'][$key]->service->image);
                }
                $ItemNew[$i]['activitys'] = ActivityBook::where('booking_id', $ItemNew[$i]['id'])->get();
                foreach ($ItemNew[$i]['activitys'] as $key => $value) {
                    $ItemNew[$i]['activitys'][$key]['user'] = User::where('code', $value['create_by'])->first();
                    if ($ItemNew[$i]['activitys'][$key]['user']) {
                        if ($ItemNew[$i]['activitys'][$key]['user']['image']) {
                            $ItemNew[$i]['activitys'][$key]['user']['image'] = url($ItemNew[$i]['activitys'][$key]['user']['image']);
                        } else {
                            $ItemNew[$i]['activitys'][$key]['user']['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
                        }
                    }
                }
            }
        }

        $ItemData = [];

        $ItemDataUsers = [];

        foreach ($Item as $key => $value) {

            if (count($value['activitys']) > 0) {

                foreach ($Users as $key2 => $value2) {
                    if ($value['activitys'][count($value['activitys']) - 1]['create_by'] == $value2['code']) {
                        array_push($ItemDataUsers, $value);
                    }
                }
            }
        }

        $ItemData['all'] = $ItemDataUsers;
        $ItemData['news'] = $ItemNew;

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $ItemData);
    }

    public function getList()
    {
        $Item = Booking::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['service_center'] = ServiceCenter::find($Item[$i]['service_center_id']);
                $Item[$i]['services'] = BookingService::where('booking_id', $Item[$i]['id'])->get();
                $Item[$i]['car'] = Car::find($Item[$i]['car_id']);
                if ($Item[$i]['car']['image']) {
                    $Item[$i]['car']['image'] = url($Item[$i]['car']['image']);
                } else {
                    $Item[$i]['car']['image']  = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
                $Item[$i]['car']['brand_model'] = BrandModel::find($Item[$i]['car']['brand_model_id']);
                foreach ($Item[$i]['services'] as $key => $value) {
                    $Item[$i]['services'][$key]->service = Services::find($Item[$i]['services'][$key]->service_id);
                    $Item[$i]['services'][$key]->service->image = url($Item[$i]['services'][$key]->service->image);
                }
                $Item[$i]['activitys'] = ActivityBook::where('booking_id', $Item[$i]['id'])->get();
                foreach ($Item[$i]['activitys'] as $key => $value) {
                    $Item[$i]['activitys'][$key]['user'] = User::where('code', $value['create_by'])->first();
                    if ($Item[$i]['activitys'][$key]['user']) {
                        if ($Item[$i]['activitys'][$key]['user']['image']) {
                            $Item[$i]['activitys'][$key]['user']['image'] = url($Item[$i]['activitys'][$key]['user']['image']);
                        } else {
                            $Item[$i]['activitys'][$key]['user']['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
                        }
                    }
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


        $col = array('id', 'code', 'date', 'time', 'client_id', 'car_id', 'service_center_id', 'reason', 'phone', 'name', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'code', 'date', 'time', 'client_id', 'car_id', 'service_center_id', 'reason', 'phone', 'name', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = Booking::select($col);


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
                $d[$i]->service_center = ServiceCenter::find($d[$i]->service_center_id);
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
        $services = $request->services;

        if (!isset($request->name)) {
            return $this->returnErrorData('[name] Data Not Found', 404);
        } else if (!isset($request->phone)) {
            return $this->returnErrorData('[phone] Data Not Found', 404);
        }


        DB::beginTransaction();

        try {
            $prefix = "2";
            $code = IdGenerator::generate(['table' => 'bookings', 'field' => 'code', 'length' => 4, 'prefix' => $prefix]);

            $Item = new Booking();
            $Item->code = $code;
            $Item->date = $request->date;
            $Item->time = $request->time;
            $Item->client_id = $request->client_id;
            $Item->car_id = $request->car_id;
            $Item->service_center_id = $request->service_center_id;
            $Item->reason = $request->reason;
            $Item->phone = $request->phone;
            $Item->name = $request->name;

            $Item->save();

            foreach ($services as $key => $value) {
                $ItemLists = new BookingService();
                $ItemLists->booking_id = $Item->id;
                $ItemLists->service_id = $value['service_id'];
                $ItemLists->note = $value['note'];
                $ItemLists->save();
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
     * @param  \App\Models\Booking  $clients
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Booking::where('id', $id)
            ->first();

        if ($Item) {
            $Item->services = BookingService::where('booking_id', $id)->get();
            $Item->car = Car::find($Item->car_id);
            if ($Item->car) {
                // if ($Item->car->image) {
                //     $Item->car->image = url($Item->car->image);
                // } else {
                //     $Item->car->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                // }

                $Item->car->brand_model = BrandModel::find($Item->car->brand_model_id);
                    if($Item->car->brand_model){
                        $img = explode('-',$Item->car->brand_model->name);
                        $Item->car->image = "https://asha-tech.co.th/asap/public/images/cars/".$img[0]."/".$Item->car->brand_model->name.".png";
                    }
            }
            $Item->service_center = ServiceCenter::find($Item->service_center_id)->first();
            foreach ($Item->services as $key => $value) {
                $Item->services[$key]->service = Services::find($Item->services[$key]->service_id);
                $Item->services[$key]->service->image = url($Item->services[$key]->service->image);
            }
            $Item->activitys = ActivityBook::where('booking_id', $id)->get();

            foreach ($Item->activitys as $key => $value) {
                $Item->activitys[$key]->user = User::where('code', $value['create_by'])->first();
                if ($Item->activitys[$key]->user) {
                    if ($Item->activitys[$key]->user->image) {
                        $Item->activitys[$key]->user->image = url($Item->activitys[$key]->user->image);
                    } else {
                        $Item->activitys[$key]->user->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                    }
                } else {
                    $Item->activitys[$key]->user->image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
            }

            $stop_date =  $Item->date . " " . $Item->time;
            $Item->date_change = date('d M Y H:i:s', strtotime($stop_date . ' -1 day'));

            $now = Carbon::now();
            $dateTime2 = Carbon::parse($stop_date);
            // if ($now < $dateTime2) {
            //     $diffInHours = 0;
            // } else {
            //     $diffInHours = $now->diffInHours($dateTime2);
            // }
            $diffInHours = $now->diffInHours($dateTime2);
            $Item->diff_date = $diffInHours;
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }


    function timeDifference($date1_pm_checked, $date1_format, $date2, $date2_format, $plus_minus = false, $return = 'all', $parseInt = false)
    {
        $strtotime1 = strtotime($date1_pm_checked);
        $strtotime2 = strtotime($date2);
        $date1 = new DateTime(date($date1_format, $strtotime1));
        $date2 = new DateTime(date($date2_format, $strtotime2));
        $interval = $date1->diff($date2);

        $plus_minus = (empty($plus_minus)) ? '' : (($strtotime1 > $strtotime2) ? '+' : '-'); # +/-/no_sign before value 

        switch ($return) {
            case 'y';
            case 'year';
            case 'years';
                $elapsed = $interval->format($plus_minus . '%y');
                break;

            case 'm';
            case 'month';
            case 'months';
                $elapsed = $interval->format($plus_minus . '%m');
                break;

            case 'a';
            case 'day';
            case 'days';
                $elapsed = $interval->format($plus_minus . '%a');
                break;

            case 'd';
                $elapsed = $interval->format($plus_minus . '%d');
                break;

            case 'h';
            case 'hour';
            case 'hours';
                $elapsed = $interval->format($plus_minus . '%h');
                break;

            case 'i';
            case 'minute';
            case 'minutes';
                $elapsed = $interval->format($plus_minus . '%i');
                break;

            case 's';
            case 'second';
            case 'seconds';
                $elapsed = $interval->format($plus_minus . '%s');
                break;

            case 'all':
                $parseInt = false;
                $elapsed = $plus_minus . $interval->format('%y years %m months %d days %h hours %i minutes %s seconds');
                break;

            default:
                $parseInt = false;
                $elapsed = $plus_minus . $interval->format($return);
        }

        if ($parseInt)
            return (int) $elapsed;
        else
            return $elapsed;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $clients
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $loginBy = $request->login_by;
        $services = $request->services;

        if (!isset($id)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        try {
            $Item = Booking::find($id);
            $Item->date = $request->date;
            $Item->time = $request->time;
            $Item->client_id = $request->client_id;
            $Item->car_id = $request->car_id;
            $Item->service_center_id = $request->service_center_id;
            $Item->reason = $request->reason;
            $Item->phone = $request->phone;
            $Item->name = $request->name;

            $Item->save();

            foreach ($services as $key => $value) {
                $ItemLists = new BookingService();
                $ItemLists->booking_id = $Item->id;
                $ItemLists->service_id = $value['service_id'];
                $ItemLists->save();
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
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Booking::find($id);
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

    public function update_status(Request $request)
    {
        $loginBy = $request->login_by;
        $id = $request->booking_id;

        $services = $request->services;

        if (!$loginBy) {
            return $this->returnErrorData('กรุณาเข้าสู่ระบบ', 404);
        } else if (!isset($id)) {
            return $this->returnErrorData('[id] Data Not Found', 404);
        } else if (!isset($request->status)) {
            return $this->returnErrorData('[status] Data Not Found', 404);
        }

        $Item = Booking::find($id);
        if (!$Item) {
            return $this->returnErrorData('ไม่พบข้อมูล Booking ในระบบ', 404);
        }

        DB::beginTransaction();

        try {
            $act = "";

            // $Item = Booking::find($id);
            $Item->status = $request->status;
            $Item->reason = $request->reason;
            $Item->updated_at = Carbon::now()->toDateTimeString();
            $Item->create_by = $loginBy->code;

            $n = 1;
            $servicename = "";
            if ($request->service_center_id != "") {
                $ItemServiceCenter1 = ServiceCenter::find($Item->service_center_id);
                $ItemServiceCenter2 = ServiceCenter::find($request->service_center_id);
                $n++;
                $act .= $n . ".มีการเปลี่ยนแปลงศูนย์บริการ จาก <b>" . $ItemServiceCenter1->name . " เป็น " . $ItemServiceCenter2->name . "</b><br>";
                $servicename  = "มีการเปลี่ยนแปลงศูนย์บริการ จาก " . $ItemServiceCenter1->name . " เป็น " . $ItemServiceCenter2->name . "";
                $Item->service_center_id = $request->service_center_id;
            }

            if ($request->phone != "") {
                $n++;
                $act .= $n . ".มีการเปลี่ยนแปลงเบอร์โทร จาก <b>" . $Item->phone . " เป็น " . $request->phone . "</b><br>";
                $Item->phone = $request->phone;
            }

            if ($request->date != "") {
                $n++;
                $act .= $n . ".มีการเปลี่ยนแปลงวันที่ จาก <b>" . $Item->date . " เป็น " . $request->date . "</b><br>";
                $Item->date = $request->date;
            }
            if ($request->time != "") {
                $n++;
                $act .= $n . ".มีการเปลี่ยนแปลงเวลา จาก <b>" . $Item->time . " เป็น " . $request->time . ":00</b><br>";
                $Item->time = $request->time;
            }


            $ItemOldService  = BookingService::where('booking_id', $id)
                ->where('service_id', 8)->first();


            $ItemDelete = BookingService::where('booking_id', $id)->delete();

            $noteCheck = false;
            if ($Item->status != "Cancel") {
                foreach ($services as $key => $value) {
                    $ItemService = Services::find($value['service_id']);
                    if ($value['status'] != "remove") {
                        $ItemLists = new BookingService();
                        $ItemLists->booking_id = $id;
                        $ItemLists->service_id = $value['service_id'];
                        if ($value['service_id'] == "8") {
                            if ($ItemOldService) {
                                if ($request->note != "" && $noteCheck == false) {
                                    $noteCheck = true;
                                    $n++;
                                    $act .= $n . ".มีการแก้ไขรายละเอียดบริการอื่นๆ จาก <b>" . $ItemOldService->note . " เป็น " . $request->note . "</b><br>";
                                    // $Item->note = $request->note;
                                }
                            }
                        }

                        $ItemLists->note = $request->note;


                        $ItemLists->save();
                        if ($value['status'] == "new") {
                            $n++;
                            if ($value['service_id'] == "8") {
                                $act .= $n . ".มีการเพิ่มบริการ <b>" . $ItemLists->note . "</b><br>";
                            } else {
                                $act .= $n . ".มีการเพิ่มบริการ <b>" . $ItemService->name . "</b><br>";
                            }
                        }
                    } else {
                        $n++;

                        $act .= $n . ".มีการลบบริการ <b>" . $ItemService->name . "</b><br>";
                    }
                }
            }





            $Item->save();

            $car = Car::find($Item->car_id);

            if ($car) {
                $brand = BrandModel::find($car->brand_model_id);

                $service_center = ServiceCenter::find($Item->service_center_id);


                // if ($car->image) {
                //     $image = url($car->image);
                // } else {
                //     $image = "https://asha-tech.co.th/asap/public/images/default.jpg";
                // }

                if($brand){
                    $img = explode('-',$brand->name);
                    $image = "https://asha-tech.co.th/asap/public/images/cars/".$img[0]."/".$brand->name.".png";
                }

                if ($car->color) {
                    $car->color = $car->color;
                } else {
                    $car->color = "";
                }

                if ($service_center->address) {
                    $service_center->address = $service_center->address;
                } else {
                    $service_center->address = "";
                }

                if (!$servicename) {
                    $servicename = $service_center->name;
                }


                if ($car->userId) {
                    $this->sendFlexMessage($request->status, $car->userId, $car->license, $brand->name . ' ' . $car->color, $image, $servicename, $Item->date, $Item->time, $Item->reason, $id);
                }
            }

            $userId = $loginBy->code;
            $type = 'เพิ่มรายการ';
            $status = "";
            if ($Item->status == "Waiting") {
                $status = "รอเข้ารับบริการ";
            } else if ($Item->status == "Process") {
                $status = "กำลังดำเนินการ";
            } else if ($Item->status == "Cancel") {
                $status = "ยกเลิก";
            } else if ($Item->status == "Finish") {
                $status = "เสร็จสิ้น";
            }
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
            $this->activity_book($Item->id, "1.มีการเปลี่ยนสถานะเป็น <b>" . $status . '</b><br> ' . $act, $loginBy->code);

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

            // return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);

            return $this->returnErrorData('พบข้อผิดพลาดในการบันทึกสถานะงาน'.$e, 404);
        }
    }

    public function get_dashboard_summary(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        $Item['summary_ticket']['all']['total'] = rand(10, 1000);
        $Item['summary_ticket']['all']['percent'] = rand(10, 100);
        $Item['summary_ticket']['process']['total'] = rand(10, 1000);
        $Item['summary_ticket']['process']['percent'] = rand(10, 100);
        $Item['summary_ticket']['waiting']['total'] = rand(10, 1000);
        $Item['summary_ticket']['waiting']['percent'] = rand(10, 100);
        $Item['summary_ticket']['finish']['total'] = rand(10, 1000);
        $Item['summary_ticket']['finish']['percent'] = rand(10, 100);


        $Item['graph_weeks']['mon']['new'] = rand(10, 10000);
        $Item['graph_weeks']['mon']['finish'] = rand(10, 10000);
        $Item['graph_weeks']['thu']['new'] = rand(10, 10000);
        $Item['graph_weeks']['thu']['finish'] = rand(10, 10000);
        $Item['graph_weeks']['wed']['new'] = rand(10, 10000);
        $Item['graph_weeks']['wed']['finish'] = rand(10, 10000);
        $Item['graph_weeks']['tue']['new'] = rand(10, 10000);
        $Item['graph_weeks']['tue']['finish'] = rand(10, 10000);
        $Item['graph_weeks']['fri']['new'] = rand(10, 10000);
        $Item['graph_weeks']['fri']['finish'] = rand(10, 10000);
        $Item['graph_weeks']['sat']['new'] = rand(10, 10000);
        $Item['graph_weeks']['sat']['finish'] = rand(10, 10000);
        $Item['graph_weeks']['son']['new'] = rand(10, 10000);
        $Item['graph_weeks']['son']['finish'] = rand(10, 10000);

        $Item['periods']['case'] = "2.5m";
        $Item['periods']['finish'] = "5 วัน";
        $Item['periods']['call'] = "5m:31s";

        $Item['work_ranking_persons'] = User::get(['id', 'code', 'fname', 'image', 'department_id'])->toarray();
        foreach ($Item['work_ranking_persons'] as $key => $value) {
            $Item['work_ranking_persons'][$key]['team'] = Department::where('id', $value['department_id'])->get(['name'])->first()['name'];
            $Item['work_ranking_persons'][$key]['case'] = rand(1000, 20000);
            $Item['work_ranking_persons'][$key]['period_open'] = rand(1000, 20000);
            $Item['work_ranking_persons'][$key]['period_finish'] = rand(1000, 20000);
        }

        $Item['client_smile']['point'] = "4.6";
        $Item['client_smile']['reviews'] = "698";

        $Item['client_smile']['client']['enjoy'] = "59%";
        $Item['client_smile']['client']['enjoy_detail'] = "ความรวดเร็วในการบริการ";
        $Item['client_smile']['client']['expect'] = "98%";
        $Item['client_smile']['client']['expect_detail'] = "งานด้านบริการ";


        return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
    }

    public function get_dashboard_summary_service(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        $department_id = $request->department_id;

        $deps = Department::get(['id', 'name'])->toarray();
        // $Item['deps_totals'] = Department::get(['name'])->toarray();
        $Item['deps_totals'][0]['id'] = "0";
        $Item['deps_totals'][0]['name'] = "All";
        $Item['deps_totals'][0]['total'] = 0;
        $sum = 0;
        foreach ($deps as $key => $value) {
            $key = $key + 1;
            $Item['deps_totals'][$key]['id'] = $value['id'];
            $Item['deps_totals'][$key]['name'] = $value['name'];
            $ra = rand(1000, 20000);
            $Item['deps_totals'][$key]['total'] = $ra;
            $sum += $ra;
        }
        $Item['deps_totals'][0]['total'] = $sum;

        $clients = Clients::where('department_id', $department_id)->get(['id', 'company'])->toarray();

        $booking = Booking::selectRaw('client_id,COUNT(*) as count')
            ->groupBy('client_id')
            ->limit(10) // Change this to your desired limit
            ->get();

        $Item['most_booking'] = $booking;
        foreach ($booking as $key => $value) {
            $Item['most_booking'][$key]['company'] = Clients::find($value['client_id'])['company'];
            $Item['most_booking'][$key]['total'] = rand(1000, 20000);
        }


        $services = Services::get()->toarray();

        foreach ($services as $key => $value) {
            $Item['top_services'][$key]['name'] = $value['name'];
            $Item['top_services'][$key]['percent'] = rand(0, 100);
            $total = BookingService::selectRaw('COUNT(*) as count')
                ->where('service_id', $value['id'])
                ->first();
            $Item['top_services'][$key]['total'] = $total['count'];
        }

        $Item['most_booking'] = $booking;
        foreach ($booking as $key => $value) {
            $Item['most_booking'][$key]['company'] = Clients::find($value['client_id'])['company'];
            $Item['most_booking'][$key]['total'] = $value['count'];
        }

        $service_centers = Booking::selectRaw('service_center_id,COUNT(*) as count')
            ->groupBy('service_center_id')
            ->limit(6) // Change this to your desired limit
            ->get();

        foreach ($service_centers as $key => $value) {
            $Item['most_service_centers'][$key]['name'] = ServiceCenter::find($value['service_center_id'])['name'];
            $Item['most_service_centers'][$key]['total'] = $value['count'];
        }

        return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
    }

    public function getPageComList(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        $department_id = $request->department_id;


        $col = array('id', 'company');

        $orderby = array('', 'company');

        $D = Clients::select($col);

        if (isset($department_id)) {
            $D->where('department_id', $department_id);
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
                $data = Booking::selectRaw('client_id,COUNT(*) as count')
                    ->where('client_id', $d[$i]->id)
                    ->groupBy('client_id')
                    ->first();

                if ($data) {
                    $d[$i]->total_book =  $data['count'];
                } else {
                    $d[$i]->total_book =  0;
                }

                $data2 = Booking::selectRaw('COUNT(*) as count')
                    ->where('client_id', $d[$i]->id)
                    ->groupBy('car_id')
                    ->first();


                if ($data2) {
                    $d[$i]->total_car =  $data2['count'];
                } else {
                    $d[$i]->total_car =  0;
                }

                $data3 = ClientCars::selectRaw('COUNT(*) as count')
                    ->where('client_id', $d[$i]->id)
                    ->groupBy('client_id')
                    ->first();


                if ($data3) {
                    $d[$i]->total_port =  $data3['count'];
                } else {
                    $d[$i]->total_port =  0;
                }



                $data4 = Booking::selectRaw('COUNT(*) as count')
                    ->first();


                if ($data4) {
                    if ($d[$i]->total_book > 0) {
                        $d[$i]->total_avg =  intval($data4['count']) / intval($d[$i]->total_book);
                    } else {
                        $d[$i]->total_avg =  0;
                    }
                } else {
                    $d[$i]->total_avg =  0;
                }

                $d[$i]->department = Department::find($department_id);

                // foreach ($d[$i]->cars as $key => $value) {
                //     $d[$i]->cars[$key]->car = Car::find($value['car_id']);
                // }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function get_dashboard_summary_by_comp(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        $client_id = $request->client_id;

        $service_centers = Booking::selectRaw('service_center_id,COUNT(*) as count')
            ->where('client_id', $client_id)
            ->groupBy('service_center_id')
            ->limit(6) // Change this to your desired limit
            ->get();

        foreach ($service_centers as $key => $value) {
            $Item['most_service_centers'][$key]['name'] = ServiceCenter::find($value['service_center_id'])['name'];
            $Item['most_service_centers'][$key]['total'] = $value['count'];
        }


        $services = Services::get()->toarray();

        $bookings = Booking::where('client_id', $client_id)
            ->get(['id']);

        $arr = [];
        foreach ($bookings as $key => $value) {
            array_push($arr, $value['id']);
        }

        foreach ($services as $key => $value) {
            $Item['top_services'][$key]['name'] = $value['name'];
            $Item['top_services'][$key]['percent'] = rand(0, 100);

            $total = BookingService::selectRaw('COUNT(*) as count')
                ->whereIn('id', $arr)
                ->where('service_id', $value['id'])
                ->first();

            $Item['top_services'][$key]['total'] = $total['count'];
        }



        return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
    }

    public function getPageComActivityList(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        $client_id = $request->client_id;


        $col = array('id', 'booking_id', 'status', 'created_at');

        $orderby = array('', 'booking_id', 'status', 'created_at');

        $bookings = Booking::where('client_id', $client_id)
            ->get(['id']);

        $arr = [];
        foreach ($bookings as $key => $value) {
            array_push($arr, $value['id']);
        }

        $D = ActivityBook::select($col)
            ->whereIn('booking_id', $arr);


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

                $booking = Booking::find($d[$i]->booking_id);
                if ($booking) {
                    $car =  Car::find($booking['car_id']);
                    if ($car) {
                        $d[$i]->license_plate = $car->license;
                        $d[$i]->mile = number_format($car->mile, 0);
                        $brand = BrandModel::find($car->brand_model_id);
                        $d[$i]->brand = $brand->name . ' ' . $car->color;
                    }
                } else {
                    $d[$i]->license_plate = null;
                    $d[$i]->mile = null;
                    $d[$i]->brand = null;
                }
                $d[$i]->booking = $booking;
                $d[$i]->booking->services = BookingService::where('booking_id', $booking->id)->get();
                foreach ($d[$i]->booking->services as $key => $value) {
                    $d[$i]->booking->services[$key]->service = Services::find($value['service_id']);
                }
                $d[$i]->service_center = ServiceCenter::find($booking['service_center_id'])['name'];
                $d[$i]->age = "1 ปี 0 เดือน";
                $d[$i]->client = Clients::find($client_id);
                if ($d[$i]->client) {
                    $d[$i]->department = Department::find($d[$i]->client->department_id);
                } else {
                    $d[$i]->department = null;
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function Export($id)
    {

        $client_id = $id;

        $bookings = Booking::where('client_id', $client_id)
            ->get(['id']);

        $arr = [];
        foreach ($bookings as $key => $value) {
            array_push($arr, $value['id']);
        }

        $col = array('id', 'booking_id', 'status', 'created_at');

        $data = ActivityBook::select($col)
            ->whereIn('booking_id', $arr)
            ->get();

        if (!empty($data)) {

            for ($i = 0; $i < count($data); $i++) {


                $booking = Booking::find($data[$i]['booking_id']);
                if ($booking) {
                    $car =  Car::find($booking['car_id']);
                    $service_center = ServiceCenter::find($booking['service_center_id'])['name'];
                }

                $export_data[] = array(
                    'license_plate' => trim($car->license),
                    'date' => trim($data[$i]['created_at']),
                    'time' => trim($data[$i]['created_at']),
                    'mile' => trim($car->mile),
                    'status' => trim($data[$i]['status']),
                    'service_center' => $service_center,
                    'age' => "1 ปี 0 เดือน",
                );
            }

            $result = new ActivityBookExport($export_data);
            return Excel::download($result, 'ประวัติกิจกรรมรถ.xlsx');
        } else {

            $export_data[] = array(
                'sub_agency_command_id' => null,
                'affiliation_id' => null,
                'name' => null,
                'sub_name' => null,
                'address' => null,
                'create_by' => null,
                'update_by' => null,
                'created_at' => null,
                'updated_at' => null,
            );

            $result = new ActivityBookExport($export_data);
            return Excel::download($result, 'ประวัติกิจกรรมรถ.xlsx');
        }
    }

    public function getPageServiceCenter(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $type = $request->type;

        $col = array('id', 'name');

        $orderby = array('', 'name');

        $D = ServiceCenter::select($col);

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
                $booking = Booking::selectRaw('COUNT(*) as count')
                    ->where('service_center_id', $d[$i]->id)
                    ->first();
                if ($booking) {
                    $d[$i]->total_book = $booking['count'];
                } else {
                    $d[$i]->total_book = 0;
                }

                $booking2 = Booking::selectRaw('COUNT(*) as count')
                    ->where('service_center_id', $d[$i]->id)
                    ->GroupBy('car_id')
                    ->first();

                if ($booking2) {
                    $d[$i]->total_car = $booking2['count'];
                } else {
                    $d[$i]->total_car = 0;
                }

                $d[$i]->total_port = "-";

                $data4 = Booking::selectRaw('COUNT(*) as count')
                    ->first();


                if ($data4) {
                    if ($d[$i]->total_book > 0) {
                        $d[$i]->total_avg =  intval($data4['count']) / intval($d[$i]->total_book);
                    } else {
                        $d[$i]->total_avg =  0;
                    }
                } else {
                    $d[$i]->total_avg =  0;
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function get_dashboard_summary_by_service_center(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        $service_center_id = $request->service_center_id;

        $service_centers = Booking::selectRaw('client_id,COUNT(*) as count')
            ->where('service_center_id', $service_center_id)
            ->groupBy('client_id')
            ->limit(6) // Change this to your desired limit
            ->get();

        foreach ($service_centers as $key => $value) {
            $Item['most_clients'][$key]['name'] = Clients::find($value['client_id'])['company'];
            $Item['most_clients'][$key]['total'] = $value['count'];
        }


        $services = Services::get()->toarray();

        $bookings = Booking::where('service_center_id', $service_center_id)
            ->get(['id']);

        $arr = [];
        foreach ($bookings as $key => $value) {
            array_push($arr, $value['id']);
        }

        foreach ($services as $key => $value) {
            $Item['top_services'][$key]['name'] = $value['name'];
            $Item['top_services'][$key]['percent'] = rand(0, 100);

            $total = BookingService::selectRaw('COUNT(*) as count')
                ->whereIn('id', $arr)
                ->where('service_id', $value['id'])
                ->first();

            $Item['top_services'][$key]['total'] = $total['count'];
        }



        return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
    }

    public function getPageServiceCenterActivityList(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        $service_center_id = $request->service_center_id;


        $col = array('id', 'booking_id', 'status', 'created_at');

        $orderby = array('', 'booking_id', 'status', 'created_at');

        $bookings = Booking::where('service_center_id', $service_center_id)
            ->get(['id']);

        $arr = [];
        foreach ($bookings as $key => $value) {
            array_push($arr, $value['id']);
        }

        $D = ActivityBook::select($col)
            ->whereIn('booking_id', $arr);


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

                $booking = Booking::find($d[$i]->booking_id);
                if ($booking) {
                    $car =  Car::find($booking['car_id']);
                    if ($car) {
                        $d[$i]->license_plate = $car->license;
                        $d[$i]->mile = number_format($car->mile, 0);
                        $brand = BrandModel::find($car->brand_model_id);
                        $d[$i]->brand = $brand->name . ' ' . $car->color;
                    }
                } else {
                    $d[$i]->license_plate = null;
                    $d[$i]->mile = null;
                }
                $d[$i]->service_center = ServiceCenter::find($booking['service_center_id'])['name'];
                $d[$i]->age = "1 ปี 0 เดือน";
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function ExportServiceCenter($id)
    {

        $service_center_id = $id;

        $bookings = Booking::where('service_center_id', $service_center_id)
            ->get(['id']);

        $arr = [];
        foreach ($bookings as $key => $value) {
            array_push($arr, $value['id']);
        }

        $col = array('id', 'booking_id', 'status', 'created_at');

        $data = ActivityBook::select($col)
            ->whereIn('booking_id', $arr)
            ->get();

        if (!empty($data)) {

            for ($i = 0; $i < count($data); $i++) {


                $booking = Booking::find($data[$i]['booking_id']);
                if ($booking) {
                    $car =  Car::find($booking['car_id']);
                    $service_center = ServiceCenter::find($booking['service_center_id'])['name'];
                }

                $export_data[] = array(
                    'license_plate' => trim($car->license),
                    'date' => trim($data[$i]['created_at']),
                    'time' => trim($data[$i]['created_at']),
                    'mile' => trim($car->mile),
                    'status' => trim($data[$i]['status']),
                    'service_center' => $service_center,
                    'age' => "1 ปี 0 เดือน",
                );
            }

            $result = new ActivityBookExport($export_data);
            return Excel::download($result, 'ประวัติกิจกรรมรถ.xlsx');
        } else {

            $export_data[] = array(
                'sub_agency_command_id' => null,
                'affiliation_id' => null,
                'name' => null,
                'sub_name' => null,
                'address' => null,
                'create_by' => null,
                'update_by' => null,
                'created_at' => null,
                'updated_at' => null,
            );

            $result = new ActivityBookExport($export_data);
            return Excel::download($result, 'ประวัติกิจกรรมรถ.xlsx');
        }
    }

    public function getPageHistory(Request $request)
    {
        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;
        $search_license_plate = $request->search_license_plate;


        $col = array('id', 'booking_id', 'service_id', 'note', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'booking_id', 'service_id', 'note', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = BookingService::select($col);

        if ($search_license_plate) {
            $car = Car::where('license', $search_license_plate)->first();

            if (isset($car)) {
                $bookings = Booking::where('car_id', $car->id)
                    ->get(['id']);


                $arr = [];
                foreach ($bookings as $key => $value) {
                    array_push($arr, $value['id']);
                }

                $D->whereIn('booking_id', $arr);
            } else {
                $D->where('booking_id', 0);
            }
        } else {
            $D->where('booking_id', 0);
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
                $d[$i]->service = Services::find($d[$i]->service_id)['name'];

                $booking = Booking::find($d[$i]->booking_id);
                $d[$i]->booking = $booking;

                if ($booking) {
                    $d[$i]->book_date = $booking['date'];
                    $d[$i]->book_time = $booking['time'];
                    $service_center = ServiceCenter::find($booking->service_center_id);
                    if ($service_center) {
                        $d[$i]->service_center = $service_center['name'];
                    }
                    $activity = ActivityBook::find($booking->id);
                    if ($activity) {
                        $d[$i]->user = User::where('create_by', $activity['create_by'])->first();
                        if ($d[$i]->user) {
                            if ($d[$i]->user->department_id) {
                                $d[$i]->user->department = Department::find($d[$i]->user->department_id);
                            } else {
                                $d[$i]->user->department = "-";
                            }
                        } else {
                            $d[$i]->user = null;
                        }
                    }

                    $car = Car::find($booking->car_id);
                    if ($car) {
                        $d[$i]->mile = $car->mile;
                    }
                } else {
                    $d[$i]->book_date = "-";
                    $d[$i]->book_time = "-";
                    $d[$i]->service_center = "-";
                    $d[$i]->user = "-";
                    $d[$i]->mile = "-";
                }
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }

    public function ExportHistory(Request $request)
    {
        $col = array('id', 'booking_id', 'service_id', 'note', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = BookingService::select($col);

        $search_license_plate = $request->license_plate;
        if ($search_license_plate) {
            $car = Car::where('license', $search_license_plate)->first();

            if (isset($car)) {
                $bookings = Booking::where('car_id', $car->id)
                    ->get(['id']);


                $arr = [];
                foreach ($bookings as $key => $value) {
                    array_push($arr, $value['id']);
                }

                $D->whereIn('booking_id', $arr);
            } else {
                $D->where('booking_id', 0);
            }
        }

        $data = $D->get()->toArray();

        if (!empty($data)) {

            for ($i = 0; $i < count($data); $i++) {

                $booking = Booking::find($data[$i]['booking_id']);

                if ($booking) {
                    $service_center = ServiceCenter::find($booking->service_center_id);
                    $activity = ActivityBook::find($booking->id);
                    $user = User::where('create_by', $activity['create_by'])->first();
                    if ($user) {
                        $department = Department::find($user->department_id);
                    } else {
                        $department = null;
                    }
                    $car = Car::find($booking->car_id);
                }

                if ($user && $department) {
                    $user_id = $user['fname'] . ' ' . $department['name'] != null ? $department['name'] : '-';
                } else {
                    $user_id = "-";
                }

                $export_data[] = array(
                    'date_time' => trim($booking['date'] . " " . $booking['time']),
                    'mile' => $car['mile'],
                    'activity' => Services::find($data[$i]['service_id'])['name'],
                    'service_center' => trim($service_center['name']),
                    'user' => $user_id,
                );
            }

            $result = new BookingHistoryExport($export_data);
            return Excel::download($result, 'ประวัติการใช้บริการรถ.xlsx');
        } else {

            $export_data[] = array(
                'date_time' => null,
                'mile' => null,
                'activity' => null,
                'service_center' => null,
                'user' => null,
            );

            $result = new BookingHistoryExport($export_data);
            return Excel::download($result, 'ประวัติการใช้บริการรถ.xlsx');
        }
    }

    public function postpon_date_time(Request $request)
    {
        $booking_id = $request->booking_id;
        $date = $request->date;
        $time = $request->time;

        if (!isset($booking_id)) {
            return $this->returnErrorData('[booking_id] Data Not Found', 404);
        } else if (!isset($request->date)) {
            return $this->returnErrorData('[date] Data Not Found', 404);
        } else if (!isset($request->time)) {
            return $this->returnErrorData('[time] Data Not Found', 404);
        }

        $Item = Booking::find($booking_id);
        if (!$Item) {
            return $this->returnErrorData('ไม่พบข้อมูล Booking ในระบบ', 404);
        }

        DB::beginTransaction();

        try {
            $Item->postpon = "Y";
            $Item->postpon_date = $Item->date;
            $Item->postpon_time = $Item->time;
            $Item->date = $date;
            $Item->time = $time;
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function cancel_book(Request $request)
    {
        $booking_id = $request->booking_id;
        $reason = $request->reason;

        if (!isset($booking_id)) {
            return $this->returnErrorData('[booking_id] Data Not Found', 404);
        } else if (!isset($request->reason)) {
            return $this->returnErrorData('[reason] Data Not Found', 404);
        }

        $Item = Booking::find($booking_id);
        if (!$Item) {
            return $this->returnErrorData('ไม่พบข้อมูล Booking ในระบบ', 404);
        }

        DB::beginTransaction();

        try {
            $Item->status = "Cancel";
            $Item->reason = $reason;
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();

            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }

    public function booking_star(Request $request)
    {
        $booking_id = $request->booking_id;
        $star = $request->star;
        $comment = $request->comment;
        $user_id = $request->user_id;
        if (!isset($booking_id)) {
            return $this->returnErrorData('[booking_id] Data Not Found', 404);
        } else if (!isset($request->star)) {
            return $this->returnErrorData('[star] Data Not Found', 404);
        }

        $Item = Booking::find($booking_id);
        if (!$Item) {
            return $this->returnErrorData('ไม่พบข้อมูล Booking ในระบบ', 404);
        }

        DB::beginTransaction();

        try {
            $Item->evaluate = "Y";
            $Item->updated_at = Carbon::now()->toDateTimeString();

            $Item->save();

            $ItemStar = new BookingEva();
            $ItemStar->booking_id = $booking_id;
            $ItemStar->star = $star;
            $ItemStar->comment = $comment;
            $ItemStar->user_id = $user_id;
            $ItemStar->save();

            $Item->eva = $ItemStar;
            DB::commit();

            return $this->returnSuccess('ดำเนินการสำเร็จ', $Item);
        } catch (\Throwable $e) {

            DB::rollback();

            return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
        }
    }
}
