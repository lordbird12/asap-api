<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Profile;
use App\Models\Car;
use Facade\FlareClient\Http\Client;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProfileController extends Controller
{
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
        $user_id = $request->user_id;
        $phone = $request->phone;
        $name = $request->name;
        $image = $request->image;

        if (!isset($request->name)) {
            return $this->returnErrorData('กรุณาระบุข้อมูลให้เรียบร้อย', 404);
        } else

            DB::beginTransaction();

        $Item = Profile::where('user_id', $user_id)->first();
        if (!$Item) {
            $Item = new Profile();
            $Item->user_id = $user_id;
        }

        try {


            $Item->phone = $phone;
            $Item->name = $name;

            if ($image != "" && $image != null) {
                $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
                $fileName = Str::random(10) . '.png';
                $filePath = public_path('images/clients/' . $fileName);
                file_put_contents($filePath, $fileData);

                // $Item->picture = $this->uploadImage($fileData, '/images/clients/');
                // Storage::disk('local')->put('uploads/'.$fileName, $fileData);
                $Item->picture = "images/clients/" . $fileName;
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
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Item = Profile::where('user_id', $id)
            ->first();

        if (!$Item) {
            $ItemCar = Car::where('userId', $id)->orderBy('updated_at', 'desc')->first();
            if($ItemCar){
                $ItemBooking = Booking::where('car_id', $ItemCar->id)->orderBy('updated_at', 'desc')->first();


                if ($ItemBooking) {
                    try {
    
                        $Item = new Profile();
                        $Item->user_id = $id;
                        $Item->phone = $ItemBooking->phone;
                        $Item->name = $ItemBooking->name;
                        $Item->pictureUrl = $ItemCar->pictureUrl;
                        $Item->displayName = $ItemCar->displayName;
                        $Item->save();
                        DB::commit();
    
                        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
                    } catch (\Throwable $e) {
    
                        DB::rollback();
    
                        return $this->returnErrorData('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง ' . $e, 404);
                    }
                }
            }
          
        }else{
            if($Item->picture){
                $Item->picture = url($Item->picture);
            }
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $Item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function edit(Profile $profile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Profile $profile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function destroy(Profile $profile)
    {
        //
    }
}
