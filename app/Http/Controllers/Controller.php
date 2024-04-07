<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\ActivityBook;
use App\Models\ActivityTicket;
use App\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function returnSuccess($massage, $data)
    {

        return response()->json([
            'code' => strval(200),
            'status' => true,
            'message' => $massage,
            'data' => $data,
        ], 200);
    }

    public function returnUpdate($massage)
    {
        return response()->json([
            'code' => strval(201),
            'status' => true,
            'message' => $massage,
            'data' => [],
        ], 201);
    }

    public function returnUpdateReturnData($massage, $data)
    {
        return response()->json([
            'code' => strval(201),
            'status' => true,
            'message' => $massage,
            'data' => $data,
        ], 201);
    }

    public function returnErrorData($massage, $code)
    {
        return response()->json([
            'code' => strval($code),
            'status' => false,
            'message' => $massage,
            'data' => [],
        ], 404);
    }

    public function returnError($massage)
    {
        return response()->json([
            'code' => strval(401),
            'status' => false,
            'message' => $massage,
            'data' => [],
        ], 401);
    }

    public function Log($userId, $description, $type)
    {
        $Log = new Log();
        $Log->user_id = $userId;
        $Log->description = $description;
        $Log->type = $type;
        $Log->save();
    }


    public function activity_book($booking_id, $status, $user)
    {
        $Log = new ActivityBook();
        $Log->booking_id = $booking_id;
        $Log->status = $status;
        $Log->create_by = $user;
        $Log->save();
    }

    public function activity_ticket($ticket_id, $status, $user)
    {
        $Log = new ActivityTicket();
        $Log->ticket_id = $ticket_id;
        $Log->status = $status;
        $Log->create_by = $user;
        $Log->save();
    }

    public function sendMail($email, $data, $title, $type)
    {

        $mail = new SendMail($email, $data, $title, $type);
        Mail::to($email)->send($mail);
    }

    public function sendLine($line_token, $text)
    {

        $sToken = $line_token;
        $sMessage = $text;

        $chOne = curl_init();
        curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($chOne, CURLOPT_POST, 1);
        curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=" . $sMessage);
        $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $sToken . '');
        curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($chOne);

        curl_close($chOne);
    }

    public function uploadImages(Request $request)
    {

        $image = $request->image;
        $path = $request->path;

        $input['imagename'] = md5(rand(0, 999999) . $image->getClientOriginalName()) . '.' . $image->extension();
        $destinationPath = public_path('/thumbnail');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $img = Image::make($image->path());
        $img->save($destinationPath . '/' . $input['imagename']);
        $destinationPath = public_path($path);
        $image->move($destinationPath, $input['imagename']);

        return $this->returnSuccess('ดำเนินการสำเร็จ', $path . $input['imagename']);
    }

    public function uploadImage($image, $path)
    {
        $input['imagename'] = md5(rand(0, 999999) . $image->getClientOriginalName()) . '.' . $image->extension();
        $destinationPath = public_path('/thumbnail');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $img = Image::make($image->path());
        $img->save($destinationPath . '/' . $input['imagename']);
        $destinationPath = public_path($path);
        $image->move($destinationPath, $input['imagename']);

        return $path . $input['imagename'];
    }

    public function uploadFile($file, $path)
    {
        $input['filename'] = time() . '.' . $file->extension();
        $destinationPath = public_path('/file_thumbnail');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $destinationPath = public_path($path);
        $file->move($destinationPath, $input['filename']);

        return $path . $input['filename'];
    }

    // public function uploadFile($file)
    // {
    // $input['filename'] = time() . '.' . $file->extension();

    // $destinationPath = public_path('/file_thumbnail');
    // if (!File::exists($destinationPath)) {
    //     File::makeDirectory($destinationPath, 0777, true);
    // }

    // $destinationPath = public_path($path);
    // $file->move($destinationPath, $input['filename']);

    // return $path . $input['filename'];

    // $file = $request->getClientOriginalName();
    // $path = $request->getPath();

    // $input['filename'] = time() . '.' . $request->extension();

    // $destinationPath = public_path('/file_thumbnail');
    // if (!File::exists($destinationPath)) {
    //     File::makeDirectory($destinationPath, 0777, true);
    // }

    // $destinationPath = public_path($path);
    // $file->move($destinationPath, $file);

    // return $path . $input['filename'];
    // }

    // public function uploadFile($file, $path)
    // {
    //     $input['filename'] = time() . '.' . $file->extension();
    //     $destinationPath = public_path('/file_thumbnail');
    //     if (!File::exists($destinationPath)) {
    //         File::makeDirectory($destinationPath, 0777, true);
    //     }

    //     $destinationPath = public_path($path);
    //     $file->move($destinationPath, $input['filename']);

    //     return $path . $input['filename'];
    // }

    public function getDropDownYear()
    {
        $Year = intval(((date('Y')) + 1) + 543);

        $data = [];

        for ($i = 0; $i < 10; $i++) {

            $Year = $Year - 1;
            $data[$i]['year'] = $Year;
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function getDropDownProvince()
    {

        $province = array("กระบี่", "กรุงเทพมหานคร", "กาญจนบุรี", "กาฬสินธุ์", "กำแพงเพชร", "ขอนแก่น", "จันทบุรี", "ฉะเชิงเทรา", "ชลบุรี", "ชัยนาท", "ชัยภูมิ", "ชุมพร", "เชียงราย", "เชียงใหม่", "ตรัง", "ตราด", "ตาก", "นครนายก", "นครปฐม", "นครพนม", "นครราชสีมา", "นครศรีธรรมราช", "นครสวรรค์", "นนทบุรี", "นราธิวาส", "น่าน", "บุรีรัมย์", "บึงกาฬ", "ปทุมธานี", "ประจวบคีรีขันธ์", "ปราจีนบุรี", "ปัตตานี", "พะเยา", "พังงา", "พัทลุง", "พิจิตร", "พิษณุโลก", "เพชรบุรี", "เพชรบูรณ์", "แพร่", "ภูเก็ต", "มหาสารคาม", "มุกดาหาร", "แม่ฮ่องสอน", "ยโสธร", "ยะลา", "ร้อยเอ็ด", "ระนอง", "ระยอง", "ราชบุรี", "ลพบุรี", "ลำปาง", "ลำพูน", "เลย", "ศรีสะเกษ", "สกลนคร", "สงขลา", "สตูล", "สมุทรปราการ", "สมุทรสงคราม", "สมุทรสาคร", "สระแก้ว", "สระบุรี", "สิงห์บุรี", "สุโขทัย", "สุพรรณบุรี", "สุราษฎร์ธานี", "สุรินทร์", "หนองคาย", "หนองบัวลำภู", "อยุธยา", "อ่างทอง", "อำนาจเจริญ", "อุดรธานี", "อุตรดิตถ์", "อุทัยธานี", "อุบลราชธานี");

        $data = [];

        for ($i = 0; $i < count($province); $i++) {

            $data[$i]['province'] = $province[$i];
        }

        return $this->returnSuccess('เรียกดูข้อมูลสำเร็จ', $data);
    }

    public function getDownloadFomatImport($params)
    {

        $file = $params;
        $destinationPath = public_path() . "/fomat_import/";

        return response()->download($destinationPath . $file);
    }

    public function checkDigitMemberId($memberId)
    {

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {

            $sum += (int) ($memberId[$i]) * (13 - $i);
        }

        if ((11 - ($sum % 11)) % 10 == (int) ($memberId[12])) {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function genCode(Model $model, $prefix, $number)
    {

        $countPrefix = strlen($prefix);
        $countRunNumber = strlen($number);

        //get last code
        $Property_type = $model::orderby('code', 'desc')->first();
        if ($Property_type) {
            $lastCode = $Property_type->code;
        } else {
            $lastCode = $prefix . $number;
        }

        $codelast = substr($lastCode, $countPrefix, $countRunNumber);

        $newNumber = intval($codelast) + 1;
        $Number = sprintf('%0' . strval($countRunNumber) . 'd', $newNumber);

        $runNumber = $prefix . $Number;

        return $runNumber;
    }


    // public function dateBetween($dateStart, $dateStop)
    // {
    //     $datediff = strtotime($dateStop) - strtotime($this->dateform($dateStart));
    //     return abs($datediff / (60 * 60 * 24));
    // }

    // public function log_noti($Title, $Description, $Url, $Pic, $Type)
    // {
    //     $log_noti = new Log_noti();
    //     $log_noti->title = $Title;
    //     $log_noti->description = $Description;
    //     $log_noti->url = $Url;
    //     $log_noti->pic = $Pic;
    //     $log_noti->log_noti_type = $Type;

    //     $log_noti->save();
    // }

    /////////////////////////////////////////// seach datatable  ///////////////////////////////////////////

    public function withPermission($query, $search)
    {

        $col = array('id', 'name', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('permission', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withMember($query, $search)
    {

        // $col = array('id', 'member_group_id','code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        // $query->orWhereHas('member', function ($query) use ($search, $col) {

        //     $query->Where(function ($query) use ($search, $col) {

        //         //search datatable
        //         $query->orwhere(function ($query) use ($search, $col) {
        //             foreach ($col as &$c) {
        //                 $query->orWhere($c, 'like', '%' . $search['value'] . '%');
        //             }
        //         });
        //     });

        // });

        // return $query;
    }


    public function withInquiryType($query, $search)
    {

        $col = array('id', 'code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('inquiry_type', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyType($query, $search)
    {

        $col = array('id', 'code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_type', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertySubType($query, $search)
    {

        $col = array('id', 'property_type_id', 'code', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_sub_type', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyAnnouncer($query, $search)
    {

        $col = array('id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_announcer', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyColorLand($query, $search)
    {

        $col = array('id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_color_land', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyOwnership($query, $search)
    {

        $col = array('id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_ownership', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertyFacility($query, $search)
    {

        $col = array('id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_facility', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });
            });
        });

        return $query;
    }

    public function withPropertySubFacility($query, $search)
    {

        $col = array('id', 'property_facility_id', 'name', 'icon', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_sub_facility', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                $query = $this->withPropertyFacility($query, $search);
            });
        });

        return $query;
    }

    public function withPropertySubFacilityExplend($query, $search)
    {

        $col = array('id', 'property_sub_facility_id', 'name', 'status', 'create_by', 'update_by', 'created_at', 'updated_at');

        $query->orWhereHas('property_sub_facility_explend', function ($query) use ($search, $col) {

            $query->Where(function ($query) use ($search, $col) {

                //search datatable
                $query->orwhere(function ($query) use ($search, $col) {
                    foreach ($col as &$c) {
                        $query->orWhere($c, 'like', '%' . $search['value'] . '%');
                    }
                });

                $query = $this->withPropertySubFacility($query, $search);
            });
        });

        return $query;
    }

    /////////////////////////////////////////// seach datatable  ///////////////////////////////////////////


    function sentMessage($encodeJson, $datas)
    {
        $datasReturn = [];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $datas['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $encodeJson,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer " . $datas['token'],
                "cache-control: no-cache",
                "content-type: application/json; charset=UTF-8",
            ),
        ));

        $response = curl_exec($curl);
        // dd($response);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $datasReturn['result'] = 'E';
            $datasReturn['message'] = $err;
        } else {
            if ($response == "{}") {
                $datasReturn['result'] = 'S';
                $datasReturn['message'] = 'Success';
            } else {
                $datasReturn['result'] = 'E';
                $datasReturn['message'] = $response;
            }
        }

        return $datasReturn;
    }

    public function sendOTP($tel, $open)
    {
        try {
            // เชค open otp
            if ($open == true) {

                $body = [
                    'key' => "1795120423986985",
                    'secret' => "a10fb7d5cbe4cce5682faafa6846948a",
                    'msisdn' => $tel
                ];
                // $body = [
                //     'key' => "1792559682800262",
                //     'secret' => "7eb74daa0d60779672dfd6684ec621d4",
                //     'msisdn' => $tel
                // ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                ])->post('https://otp.thaibulksms.com/v2/otp/request', $body);

                if ($response->status() === 200) {
                    $data = $response->json();

                    return $data;
                } elseif ($response->status() === 400) {
                    $data['status'] = 'failed';
                    $data['token'] = null;
                    $data['refno'] =  null;

                    return $data;
                } else {
                    $data['status'] = 'failed';
                    $data['token'] = null;
                    $data['refno'] =  null;

                    return $data;
                }
            } else {

                // random otp
                $otpKey = $this->randomOtp();

                $data['status'] = 'success';
                $data['token'] = $otpKey['otp_ref'];
                $data['refno'] = $otpKey['otp_ref'];

                return $data;
            }
        } catch (\Throwable $e) {

            $data['status'] = 'failed';
            $data['token'] = null;
            $data['refno'] =  null;

            return $data;
        }
    }

    public function verifyOTP($otpCode, $tokenOTP, $open)
    {
        try {
            // เชค open verifyOTP
            if ($open == true) {

                $body = [
                    'key' => "1795120423986985",
                    'secret' => "a10fb7d5cbe4cce5682faafa6846948a",
                    'token' => $tokenOTP,
                    'pin' => $otpCode,
                ];

                // $body = [
                //     'key' => "1774008287493544",
                //     'secret' => "6b17ac71d2dbdadef3845da4cc83f035",
                //     'msisdn' => $tel
                // ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                ])->post('https://otp.thaibulksms.com/v2/otp/verify', $body);

                $status = $response->status();
                $data = false;
                if ($status == 200) {
                    $data = true;
                }

                return $data;
            } else {
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    // function sendFlexMessage($type, $userid)
    // {
    //     $datasReturn = [];
    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => $datas['url'],
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 30,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => "POST",
    //         CURLOPT_POSTFIELDS => $encodeJson,
    //         CURLOPT_HTTPHEADER => array(
    //             "authorization: Bearer " . $datas['token'],
    //             "cache-control: no-cache",
    //             "content-type: application/json; charset=UTF-8",
    //         ),
    //     ));

    //     $response = curl_exec($curl);
    //     // dd($response);
    //     $err = curl_error($curl);

    //     curl_close($curl);

    //     if ($err) {
    //         $datasReturn['result'] = 'E';
    //         $datasReturn['message'] = $err;
    //     } else {
    //         if ($response == "{}") {
    //             $datasReturn['result'] = 'S';
    //             $datasReturn['message'] = 'Success';
    //         } else {
    //             $datasReturn['result'] = 'E';
    //             $datasReturn['message'] = $response;
    //         }
    //     }

    //     return $datasReturn;
    // }

    function sendFlexMessage($type, $userid, $license, $model, $image, $service_name, $date, $time, $reason, $booking_id)
    {
        // Create a DateTime object from the input date string
        $dateObject = Carbon::createFromFormat('Y-m-d', $date);

        $timeObject = Carbon::createFromFormat('H:i:s', $time);

        $formattedTime = $timeObject->format('H:i');

        // Format the date to 'd/m/Y' (e.g., '24/09/2023')
        $formattedDate = $dateObject->format('d/m/Y');

        if ($type == "New") {
            $type = "เปิดคำสั่งใหม่";
            $jsonArrayBtn = '
            {
                "type": "separator",
                "margin": "md"
              },   
            {
                "type": "button",
                "action": {
                  "type": "uri",
                  "label": "ยกเลิกการจอง",
                  "uri": "https://liff.line.me/2003760286-Mk9gQ2gG?cancel_id=' . $booking_id . '"
                },
                "color": "#0070A8",
                "margin": "md"
              }';
        } else if ($type == "Process") {
            $type = "การจองได้รับการยืนยันแล้ว";
            $jsonArrayBtn = '
            {
                "type": "separator",
                "margin": "md"
              },
            {
                "type": "button",
                "action": {
                  "type": "uri",
                  "label": "ยกเลิกการจอง",
                  "uri": "https://liff.line.me/2003760286-Mk9gQ2gG?cancel_id=' . $booking_id . '"
                },
                "color": "#0070A8",
                "margin": "md"
              }';
        } else if ($type == "Waiting") {
            $type = "กำลังดำเนินการ";
            $jsonArrayBtn = '
             {
                "type": "button",
                "action": {
                  "type": "uri",
                  "label": "ให้คะแนนการจองครั้งนี้",
                  "uri": "https://liff.line.me/2003760286-Mk9gQ2gG?template_id=' . $booking_id . '"
                },
                "style": "primary",
                "color": "#ff0000",
                "margin": "md"
              },
              {
                "type": "button",
                "action": {
                  "type": "uri",
                  "label": "เปลี่ยนแปลงวันนัดหมาย",
                  "uri": "https://liff.line.me/2003760286-Mk9gQ2gG?postpon_id=' . $booking_id . '"
                },
                "color": "#0070A8"
              },
              {
                "type": "separator",
                "margin": "md"
              },
              {
                "type": "button",
                "action": {
                  "type": "uri",
                  "label": "ยกเลิกการจอง",
                  "uri": "https://liff.line.me/2003760286-Mk9gQ2gG?cancel_id=' . $booking_id . '"
                },
                "color": "#0070A8"
              }';
        } else if ($type == "Finish") {
            $type = "รายการจองสิ้นสุดแล้ว";
            $jsonArrayBtn = '{
                "type": "button",
                "action": {
                  "type": "uri",
                  "label": "ดูรายละเอียด",
                  "uri": "https://liff.line.me/2003760286-Mk9gQ2gG?booking_id=' . $booking_id . '"
                },
                "color": "#0070A8",
                "margin": "md"
              }';
        } else if ($type == "Cancel") {
            $type = "การจองถูกยกเลิก";
            $jsonArrayBtn = '{
                "type": "button",
                "action": {
                  "type": "uri",
                  "label": "ดูรายละเอียด",
                  "uri": "https://liff.line.me/2003760286-Mk9gQ2gG?booking_id=' . $booking_id . '"
                },
                "color": "#0070A8",
                "margin": "md"
              }';

            $reason = '
              ,{
                "type": "text",
                "text": "เนื่องจากศูนย์บริการไม่รองรับเรื่องที่แจ้ง",
                "size": "md",
                "wrap": true,
                "weight": "bold",
                "color":"#FF595A"
            }';
        }



        $jsonArray = '{
            "type": "bubble",
            "header": {
              "type": "box",
              "layout": "vertical",
              "contents": [
                {
                  "type": "box",
                  "layout": "vertical",
                  "contents": [
                    {
                      "type": "text",
                      "text": "Car Service Booking",
                      "size": "lg",
                      "wrap": true,
                      "weight": "bold"
                    }
                  ],
                  "spacing": "sm"
                },
                {
                    "type": "box",
                    "layout": "vertical",
                    "contents": [
                        {
                            "type": "text",
                            "text": "ID: WN234",
                            "size": "md"
                          },
                        {
                            "type": "text",
                            "text": "' . $type . '",
                            "size": "lg",
                            "wrap": true,
                            "weight": "bold",
                            "color":"#FF595A"
                        }
                        ' . $reason . '
                    ],
                    "paddingTop": "10px"
                  }
              ]
            },
            "body": {
              "type": "box",
              "layout": "vertical",
              "margin": "sm",
              "paddingAll": "5px",
              "paddingTop": "1px",
              "contents": [
                {
                    "type": "box",
                    "layout": "horizontal",
                    "paddingAll": "5px",
                    "backgroundColor": "#F4F4F4",
                    "cornerRadius": "10px",
                    "margin": "sm",
                    "contents": [
                        {
                            "type": "image",
                            "url": "' . $image . '",
                            "flex": 0,
                            "size": "sm"
                          },
                          {
                            "type": "box",
                            "layout": "vertical",
                            "paddingAll": "3px",
                            "paddingTop": "10px",
                            "contents": [
                              {
                                  "type": "text",
                                  "text": "' . $license . '",
                                  "color": "#000000",
                                  "size": "lg",
                                  "weight": "bold"
                                },
                                {
                                  "type": "text",
                                  "text": "' . $model . '",
                                  "color": "#cccccc",
                                  "size": "md"
                                }
                            ]
                          }
                    ]
                }
            ],
            "backgroundColor": "#ffffff"
            },
            "footer": {
              "type": "box",
              "layout": "vertical",
              "contents": [
                {
                    "type": "box",
                    "layout": "vertical",
                    "contents": [
                      {
                        "type": "text",
                        "text": "' . $service_name . '",
                        "size": "md",
                        "wrap": true,
                        "weight": "bold"
                      }
                    ],
                    "spacing": "sm"
                  },
                  {
                    "type": "box",
                    "layout": "baseline",
                    "margin": "md",
                      "contents": [
                          {
                            "type": "icon",
                            "size": "sm",
                            "url": "https://asha-tech.co.th/asap/public/images/clock.png"
                          },
                          {
                              "type": "text",
                              "text":  " ' . $formattedDate . ' | ' . $formattedTime . '",
                              "size": "sm"
                           }
                      ],
                      "paddingTop": "10px"
                    },
                ' . $jsonArrayBtn . '
              ],
              "paddingAll": "20px"
            },
            "styles": {
              "header": {
                "backgroundColor": "#ffffff"
              },
              "body": {
                "backgroundColor": "#ffffff"
              },
              "footer": {
                "backgroundColor": "#ffffff"
              }
            }
          }';

        $array = json_decode($jsonArray, true);


        $dataPushMessages['url'] = "https://api.line.me/v2/bot/message/push";
        $dataPushMessages['token'] = "F8qIRhPLGgPpiRHB8IThbSNOzE757bbN3DHmNwAV2UBQOxcg8DVKNeNsUswpnVBPHkal6/YjIQjEjyzo8tM23mfJOevX0rPvXkfzxrm8q6zV6WpjJRtD645WrCLju9Xn8IRx7hVkjpr5ZU5DstHTGwdB04t89/1O/w1cDnyilFU=";

        $data = [
            'to' => $userid,
            'messages' => [
                [
                    'type' => 'flex',
                    'altText' => 'Asap Car Service Booking',
                    'contents' => $array // Your previously defined Flex Message structure
                ]
            ]
        ];



        $encodeJson = json_encode($data);
        $this->pushFlexMessage($encodeJson, $dataPushMessages);
    }

    function pushFlexMessage($encodeJson, $datas)
    {
        $datasReturn = [];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $datas['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $encodeJson,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer " . $datas['token'],
                "cache-control: no-cache",
                "content-type: application/json; charset=UTF-8",
            ),
        ));

        $response = curl_exec($curl);
        // dd($response);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $datasReturn['result'] = 'E';
            $datasReturn['message'] = $err;
        } else {
            if ($response == "{}") {
                $datasReturn['result'] = 'S';
                $datasReturn['message'] = 'Success';
            } else {
                $datasReturn['result'] = 'E';
                $datasReturn['message'] = $response;
            }
        }

        return $datasReturn;
    }
}
