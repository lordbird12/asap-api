<?php

namespace App\Http\Controllers;

use App\Exports\TicketExport;
use App\Models\ActivityTicket;
use App\Models\BrandModel;
use App\Models\Car;
use App\Models\ClientCars;
use App\Models\Clients;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketToppic;
use App\Models\User;
use Facade\FlareClient\Http\Client;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function getListByDep(Request $request)
    {
        $Users = $request->users;
        $Deps = $request->deps;
        $Clients = Clients::whereIn('department_id', $Deps)->get();

        $Item = [];
        foreach ($Clients as $key => $value) {
            $data = Ticket::where('client_id', $value['id'])->get();
            foreach ($data as $key => $value) {
                array_push($Item, $value);
            }
        }

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['ticket_topic'] = TicketToppic::where('ticket_id', $Item[$i]['id'])->get();
                $Item[$i]['activitys'] = ActivityTicket::where('ticket_id', $Item[$i]['id'])->get();
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
                $Item[$i]['car'] = Car::where('id', $Item[$i]['car_id'])->first();
                if ($Item[$i]['car']) {
                    $Item[$i]['car']['brand_model'] = BrandModel::find($Item[$i]['car']['brand_model_id']);
                    $Item[$i]['client'] = ClientCars::where('car_id', $Item[$i]['car']['id'])->first();
                    if ($Item[$i]['client']) {
                        $Item[$i]['client']['profile'] = Clients::find($Item[$i]['client']['client_id']);
                    }
                    if ($Item[$i]['car']['image']) {
                        $Item[$i]['car']['image'] = url($Item[$i]['car']['image']);
                    } else {
                        $Item[$i]['car']['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
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

        $ItemData = $ItemDataUsers;

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $ItemData);
    }


    public function getList()
    {
        $Item = Ticket::get()->toarray();

        if (!empty($Item)) {

            for ($i = 0; $i < count($Item); $i++) {
                $Item[$i]['No'] = $i + 1;
                $Item[$i]['ticket_topic'] = TicketToppic::where('ticket_id', $Item[$i]['id'])->get();
                $Item[$i]['activitys'] = ActivityTicket::where('ticket_id', $Item[$i]['id'])->get();
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
                $Item[$i]['car'] = Car::where('id', $Item[$i]['car_id'])->first();
                if ($Item[$i]['car']['image']) {
                    $Item[$i]['car']['image'] = url($Item[$i]['car']['image']);
                } else {
                    $Item[$i]['car']['image']  = "https://asha-tech.co.th/asap/public/images/default.jpg";
                }
                $Item[$i]['car']['brand_model'] = BrandModel::find($Item[$i]['car']['brand_model_id']);
                $Item[$i]['client'] = ClientCars::where('car_id', $Item[$i]['car']['id'])->first();
                if ($Item[$i]['client']) {
                    $Item[$i]['client']['profile'] = Clients::find($Item[$i]['client']['client_id']);
                }

                if ($Item[$i]['car']) {
                    if ($Item[$i]['car']['image']) {
                        $Item[$i]['car']['image'] = url($Item[$i]['car']['image']);
                    } else {
                        $Item[$i]['car']['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
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
        $status = $request->status;


        $col = array('id', 'name',  'code', 'client_id', 'note', 'car_id', 'phone', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('',  'name', 'code', 'client_id', 'note', 'car_id', 'phone', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $D = Ticket::select($col);

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
                $d[$i]->ticket_topics = TicketToppic::where('ticket_id', $d[$i]->id)->get();
                $d[$i]->car = Car::find($d[$i]->car_id);
                $d[$i]->car->brand_model = BrandModel::find($d[$i]->car->brand_model_id);

                $d[$i]->activitys = ActivityTicket::where('ticket_id', $d[$i]->id)->get();
                foreach ($d[$i]->activitys as $key => $value) {
                    $d[$i]->activitys[$key]['user'] = User::where('code', $value['create_by'])->first();
                    if ($d[$i]->activitys[$key]['user']) {
                        if ($d[$i]->activitys[$key]['user']['image']) {
                            $d[$i]->activitys[$key]['user']['image'] = url($d[$i]->activitys[$key]['user']['image']);
                        } else {
                            $d[$i]->activitys[$key]['user']['image'] = "https://asha-tech.co.th/asap/public/images/default.jpg";
                        }
                    }
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
        $ticket_topics = $request->ticket_topics;

        if (!isset($request->client_id)) {
            return $this->returnErrorData('[client_id] Data Not Found', 404);
        } else if (!isset($request->ticket_topics)) {
            return $this->returnErrorData('[ticket_topics] Data Not Found', 404);
        }


        DB::beginTransaction();

        try {

            $prefix = "2";
            $code = IdGenerator::generate(['table' => 'tickets', 'field' => 'code', 'length' => 4, 'prefix' => $prefix]);

            $Item = new Ticket();
            $Item->code = $code;
            $Item->client_id = $request->client_id;
            $Item->note = $request->note;
            $Item->car_id = $request->car_id;
            $Item->phone = $request->phone;
            $Item->name = $request->name;
            $Item->status = $request->status;
            $Item->create_by = $loginBy->code;
            $Item->save();

            foreach ($ticket_topics as $key => $value) {
                $ItemLists = new TicketToppic();
                $ItemLists->ticket_id = $Item->id;
                $ItemLists->status = $value['topic'];
                $ItemLists->save();
            }

            $userId = $loginBy->code;
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
            $this->activity_ticket($Item->id, "มีการเปลี่ยนสถานะเป็น " . $Item->status, $loginBy->code);



            //log
            $userId = $loginBy->code;
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
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Ticket::where('id', $id)
            ->first();

        if ($Item) {
            $Item->ticket_topic = TicketToppic::where('ticket_id', $id)->get();
            $Item->activitys = ActivityTicket::where('ticket_id', $id)->get();
            foreach ($Item->activitys as $key => $value) {
                $Item->activitys[$key]->user = User::where('fname', $value['create_by'])->first();
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function edit(Ticket $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //code   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $Item = Ticket::find($id);
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
        $id = $request->ticket_id;
        $topics = $request->topics;


        if (!$loginBy) {
            return $this->returnErrorData('กรุณาเข้าสู่ระบบ', 404);
        } else if (!isset($id)) {
            return $this->returnErrorData('[id] Data Not Found', 404);
        } else if (!isset($request->status)) {
            return $this->returnErrorData('[status] Data Not Found', 404);
        }

        $Item = Ticket::find($id);
        if (!$Item) {
            return $this->returnErrorData('ไม่พบข้อมูล Ticket ในระบบ', 404);
        }

        DB::beginTransaction();

        try {
            $act = "";
            $n = 1;
            // $Item = Booking::find($id);
            $Item->status = $request->status;
            $Item->reason = $request->reason;
            $Item->create_by = $loginBy->code;
            $Item->updated_at = Carbon::now()->toDateTimeString();

            
            // if ($request->phone != "") {
            //     $n++;
            //     $act .= $n . ".มีการเปลี่ยนแปลงเบอร์โทร จาก <b>" . $Item->phone . " เป็น " . $request->phone . "</b><br>";
            //     $Item->phone = $request->phone;
            // }

            
            if ($request->phone != "") {
                $n++;
                $act .= $n . ".มีการเปลี่ยนแปลงเบอร์โทร จาก <b>" . $Item->phone . " เป็น " . $request->phone . "</b><br>";
                $Item->phone = $request->phone;
            }

            $Item->save();


            $ItemDelete = TicketToppic::where('ticket_id', $id)->delete();
          
            if ($Item->status != "Cancel") {
                foreach ($topics as $key => $value) {

                    if ($value['status'] != "remove") {
                        $ItemLists = new TicketToppic();
                        $ItemLists->ticket_id = $Item->id;
                        $ItemLists->status = $value['topic'];
                        $ItemLists->save();

                        if ($value['status'] == "new") {
                            $n++;
                            $act .= $n . ".มีการเพิ่มรายละเอียดแจ้งซ่อม <b>" . $ItemLists->status . "</b><br>";
                        }
                    } else {
                        $n++;
                        $act .= $n . ".มีการลบรายละเอียดแจ้งซ่อม <b>" . $value['topic'] . "</b><br>";
                    }
                }
            }

            $status = "";
            if ($Item->status == "New") {
                $status = "งานใหม่";
            } else if ($Item->status == "Process") {
                $status = "กำลังดำเนินการ";
            } else if ($Item->status == "Cancel") {
                $status = "ยกเลิก";
            } else if ($Item->status == "Finish") {
                $status = "เสร็จสิ้น";
            }

            $userId = $loginBy->code;
            $type = 'เพิ่มรายการ';
            $description = 'ผู้ใช้งาน ' . $userId . ' ได้ทำการ ' . $type . ' ' . $request->name;
            $this->activity_ticket($Item->id, "1.มีการเปลี่ยนสถานะเป็น <b>" . $status . '</b><br> ' . $act, $loginBy->code);


            //

            //log
            $userId = $loginBy->code;
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

    public function Export(Request $request)
    {


        $log = Ticket::select('code', 'client_id', 'note', 'phone', 'status', 'created_at');

        $data = $log->get()->toArray();

        if (!empty($data)) {

            for ($i = 0; $i < count($data); $i++) {

                $export_data[] = array(
                    'code' => trim($data[$i]['code']),
                    'client_id' => trim($data[$i]['client_id']),
                    'phone' => trim($data[$i]['phone']),
                    'note' => trim($data[$i]['note']),
                    'status' => trim($data[$i]['status']),
                    'created_at' => date('Y-m-d H:i:s', strtotime($data[$i]['created_at'])),
                );
            }

            $result = new TicketExport($export_data);
            return Excel::download($result, 'ข้อมูล Ticket.xlsx');
        } else {

            $export_data[] = array(
                'code' => null,
                'client_id' => null,
                'phone' => null,
                'note' => null,
                'status' => null,
                'created_at' => null,
            );

            $result = new TicketExport($export_data);
            return Excel::download($result, 'Ticket.xlsx');
        }
    }

    public function getTicketPage(Request $request)
    {

        $columns = $request->columns;
        $length = $request->length;
        $order = $request->order;
        $search = $request->search;
        $start = $request->start;
        $page = $start / $length + 1;

        $search_license_plate = $request->search_license_plate;
        $date_start = $request->date_start;
        $date_stop = $request->date_stop;

        $Car = Car::where('license', $search_license_plate)->first();

        $col = array('id', 'code', 'client_id', 'note', 'car_id', 'phone', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $orderby = array('', 'code', 'client_id', 'note', 'car_id', 'phone', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $d = Ticket::select($col);


        if (isset($date_start) && isset($date_stop)) {
            $from = date($date_start);
            $to = date($date_stop);
            $d->whereRaw(
                "(created_at >= ? AND created_at <= ?)",
                [
                    $from . " 00:00:00",
                    $to . " 23:59:59"
                ]
            );
        }

        if ($Car) {
            $d->where('car_id', $Car->id);
        } else {
            $d->where('car_id', 0);
        }

        if ($orderby[$order[0]['column']]) {
            $d->orderby($orderby[$order[0]['column']], $order[0]['dir']);
        }

        if ($search['value'] != '' && $search['value'] != null) {

            //search datatable
            $d->where(function ($query) use ($search, $col) {
                foreach ($col as &$c) {
                    $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                }
            });
        }

        $d = $d->paginate($length, ['*'], 'page', $page);

        if ($d->isNotEmpty()) {

            //run no
            $No = (($page - 1) * $length);

            for ($i = 0; $i < count($d); $i++) {

                $No = $No + 1;
                $d[$i]->No = $No;
                $d[$i]->car = $Car;
                $d[$i]->client = Clients::find($d[$i]->client_id)->first();
                $d[$i]->user = User::where('code', $d[$i]->create_by)->first();
                $d[$i]->user->department = Department::find($d[$i]->user->department_id);
                $d[$i]->ticket_lines = TicketToppic::where('ticket_id', $d[$i]->id)->get();
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $d);
    }
}
