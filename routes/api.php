<?php

use App\Exports\TicketExport;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandModelController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ClientCarsController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\ServiceCenterController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

//////////////////////////////////////////web no route group/////////////////////////////////////////////////////
//Login Admin
Route::post('/login', [LoginController::class, 'login']);

Route::post('/check_login', [LoginController::class, 'checkLogin']);

//user
Route::post('/create_admin', [UserController::class, 'createUserAdmin']);
Route::post('/forgot_password_user', [UserController::class, 'ForgotPasswordUser']);

// position
Route::resource('position', PositionController::class);
Route::post('/position_page', [PositionController::class, 'getPage']);
Route::get('/get_position', [PositionController::class, 'getList']);

// province
Route::resource('province', ProvinceController::class);
Route::post('/province_page', [ProvinceController::class, 'getPage']);
Route::get('/get_province', [ProvinceController::class, 'getList']);

// Car
Route::resource('car', CarController::class);
Route::post('/car_page', [CarController::class, 'getPage']);
Route::get('/get_car', [CarController::class, 'getList']);
Route::post('/get_car_by_license_plate', [CarController::class, 'get_car_by_license_plate']);
Route::post('/register', [CarController::class, 'register']);
Route::post('/update_car', [CarController::class, 'updateData']);
Route::post('/import_cars', [CarController::class, 'Import']);
Route::get('/get_car_by_key_search/{key}', [CarController::class, 'getListByKey']);
Route::get('/get_car_with_client', [CarController::class, 'getClientCars']);
Route::get('/get_car_by_key_search_all/{key}', [CarController::class, 'getListByKeyAll']);

// province
Route::resource('brand_model', BrandModelController::class);
Route::post('/brand_model_page', [BrandModelController::class, 'getPage']);
Route::get('/get_brand_model', [BrandModelController::class, 'getList']);


// Client
Route::resource('client', ClientsController::class);
Route::post('/client_page', [ClientsController::class, 'getPage']);
Route::get('/get_client', [ClientsController::class, 'getList']);
Route::post('/update_client', [ClientsController::class, 'updateData']);
Route::post('/import_client', [ClientsController::class, 'Import']);
Route::get('/get_client_by_key_search/{key}', [ClientsController::class, 'getListByKey']);

// Client Car
Route::resource('client_cars', ClientCarsController::class);

// Department
Route::resource('department', DepartmentController::class);
Route::post('/department_page', [DepartmentController::class, 'getPage']);
Route::get('/get_department', [DepartmentController::class, 'getList']);

// Services
Route::resource('services', ServicesController::class);
Route::post('/services_page', [ServicesController::class, 'getPage']);
Route::get('/get_services', [ServicesController::class, 'getList']);

// Booking
Route::resource('booking', BookingController::class);
Route::post('/booking_page', [BookingController::class, 'getPage']);
Route::get('/get_booking', [BookingController::class, 'getList']);
Route::put('/get_booking_by_dep/{id}', [BookingController::class, 'getListByDep']);
Route::post('/postpon_date_time', [BookingController::class, 'postpon_date_time']);
Route::post('/cancel_book', [BookingController::class, 'cancel_book']);
Route::post('/eva_book', [BookingController::class, 'booking_star']);

// Ticket
// Route::resource('ticket', TicketController::class);
Route::post('/ticket_page', [TicketController::class, 'getPage']);
Route::get('/get_ticket', [TicketController::class, 'getList']);
Route::post('/get_ticket_by_dep', [TicketController::class, 'getListByDep']);
Route::post('/ticket_history_page', [TicketController::class, 'getTicketPage']);

// Service Center
Route::resource('service_center', ServiceCenterController::class);
Route::post('/service_center_page', [ServiceCenterController::class, 'getPage']);
Route::post('/get_service_center_by_lat_lng', [ServiceCenterController::class, 'getListByLatLon']);
Route::post('/get_service_center_by_lat_lng_recommend', [ServiceCenterController::class, 'getListByLatLonRecommend']);
Route::get('/get_service_center', [ServiceCenterController::class, 'getList']);
Route::post('/import_service_centers', [ServiceCenterController::class, 'Import']);


// Permission
Route::resource('permission', PermissionController::class);
Route::post('/permission_page', [PermissionController::class, 'getPage']);
Route::get('/get_permission', [PermissionController::class, 'getList']);
Route::post('/get_permisson_menu', [PermissionController::class, 'getPermissonMenu']);


//controller
Route::post('upload_images', [Controller::class, 'uploadImages']);
Route::post('upload_file', [Controller::class, 'uploadFile']);

//user
Route::resource('user', UserController::class);
Route::get('/get_user', [UserController::class, 'getList']);
Route::post('/user_page', [UserController::class, 'getPage']);
Route::get('/user_profile', [UserController::class, 'getProfileUser']);
Route::post('/import_employees', [UserController::class, 'Import']);
Route::get('/get_user_by_department/{id}', [UserController::class, 'getListByDepepartment']);

//  Route::post('/user_page', [UserController::class, 'UserPage']);
Route::put('/reset_password_user/{id}', [UserController::class, 'ResetPasswordUser']);
Route::post('/update_profile_user', [UserController::class, 'updateProfileUser']);
Route::get('/get_profile_user', [UserController::class, 'getProfileUser']);

Route::put('/update_password_user/{id}', [UserController::class, 'updatePasswordUser']);

// Route::post('/line/webhook', [LineController::class, 'handleWebhook']);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => 'checkjwt'], function () {

    Route::post('/line/webhook', [LineController::class, 'handleWebhook'])->middleware('verifyLineSignature');
    Route::post('/update_booking_status', [BookingController::class, 'update_status']);
    Route::post('/update_ticket_status', [TicketController::class, 'update_status']);


    Route::get('/get_user_by_dep', [UserController::class, 'getListByDep']);

    Route::post('/update_user', [UserController::class, 'updateData']);
    Route::post('/user_delete_all', [UserController::class, 'destroy_all']);

    Route::post('/client_delete_all', [ClientsController::class, 'destroy_all']);

    Route::post('/car_delete_all', [CarController::class, 'destroy_all']);

    Route::post('/service_center_delete_all', [ServiceCenterController::class, 'destroy_all']);


    Route::resource('ticket', TicketController::class);
});

//upload

Route::post('/upload_file', [UploadController::class, 'uploadFile']);

Route::get('/export_log', [LogController::class, 'ExportLog']);
Route::post('/log_page', [LogController::class, 'getPage']);

Route::post('/confirm_otp', [LoginController::class, 'requestOTP']);
Route::post('/verify_otp', [LoginController::class, 'confirmOtp']);

Route::get('/export_ticket', [TicketController::class, 'Export']);


Route::post('/get_dashboard_summary', [BookingController::class, 'get_dashboard_summary']);
Route::post('/get_dashboard_summary_service', [BookingController::class, 'get_dashboard_summary_service']);

Route::post('/dashboard_booking_page', [BookingController::class, 'getPageComList']);
Route::post('/get_dashboard_summary_by_comp', [BookingController::class, 'get_dashboard_summary_by_comp']);
Route::post('/car_comp_page', [BookingController::class, 'getPageComActivityList']);
Route::get('/export_book_activity/{id}', [BookingController::class, 'Export']);
Route::post('/service_center_book_page', [BookingController::class, 'getPageServiceCenter']);
Route::post('/get_dashboard_summary_by_service_center', [BookingController::class, 'get_dashboard_summary_by_service_center']);
Route::post('/car_service_center_page', [BookingController::class, 'getPageServiceCenterActivityList']);
Route::get('/export_book_activity_service_center/{id}', [BookingController::class, 'ExportServiceCenter']);


Route::post('/car_history_page', [BookingController::class, 'getPageHistory']);
Route::get('/booking_export_history/{license_plate}', [BookingController::class, 'ExportHistory']);

Route::post('/get_profile', [CarController::class, 'verifyLine']);
Route::post('/get_my_cars', [CarController::class, 'getMyCars']);

Route::get('/remove_car/{id}', [CarController::class, 'removeCar']);

Route::resource('profile', ProfileController::class);








