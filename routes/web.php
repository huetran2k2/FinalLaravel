<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// ----------------- TRANG CHU ---------------
Route::get('/', [PageController::class, 'getIndex']);
Route::get('/type/{id}', [PageController::class, 'getLoaiSp']);

Route::get('/detail/{id}', [PageController::class, 'getDetail']);


Route::get('/contact', [PageController::class, 'getContact']);
Route::get('/about', [PageController::class, 'getAbout']);
// ----------------- TRANG ADMIN ---------------
Route::get('/formAdd', [PageController::class, 'getAdminpage'])->name('admin');
Route::post('/formAdd', [PageController::class, 'postAdminAdd'])->name('add-product');
Route::get('/showadmin', [PageController::class, 'getIndexAdmin']);

Route::get('/admin-edit-form/{id}', [PageController::class, 'getAdminEdit']);
Route::post('/admin-edit', [PageController::class, 'postAdminEdit']);
Route::post('/admin-delete/{id}', [PageController::class, 'postAdminDelete']);

//---------------- CART ---------------
Route::get('add-to-cart/{id}', [PageController::class, 'getAddToCart'])->name('themgiohang');
Route::get('del-cart/{id}', [PageController::class, 'getDelItemCart'])->name('xoagiohang');

//------------------------- Login, Logout, Register ---------------------------------//
Route::get('/register', function () {
    return view('users.register');
});

Route::post('/register', [UserController::class, 'Register']);

Route::get('/login', function () {
    return view('users.login');
});

Route::get('/logout', [UserController::class, 'Logout']);
Route::post('/login', [UserController::class, 'Login']);


// ----------------- CHECKOUT ---------------														
Route::get('check-out', [PageController::class, 'getCheckout'])->name('dathang');
Route::post('check-out', [PageController::class, 'postCheckout'])->name('dathang');
// ---------------- Payment with VNPAY
// Route::get('/return-vnpay', function () {					
//  return view('vnpay.return-vnpay');
// });					

Route::get('/vnpay-index', function () {
    return view('/vnpay/vnpay-index');
});
//Route x??? l?? n??t X??c nh???n thanh to??n tr??n trang checkout.blade.php
// Route::get('/vnpay/create_payment', [PageController::class, 'createPayment'])->name('postCreatePayment');

Route::post('/vnpay/create_payment', [PageController::class, 'createPayment'])->name('postCreatePayment');
//Route ????? g??n cho key "vnp_ReturnUrl" ??? b?????c 6
Route::get('/vnpay/vnpay-return', [PageController::class, 'vnpayReturn'])->name('vnpayReturn');
    
Route::get('/input-email',[PageController::class,'getInputEmail'])->name('getInputEmail');
Route::post('/input-email',[PageController::class,'postInputEmail'])->name('postInputEmail');















// Route::get('/', function () 
// {
//     $data = DB::table('customers')->get();
//     print_r($data);
// });
// Route::get('/', function () 
// {
//     $data =DB::table('customers')->orderBy('name','desc')->get();
//     print_r($data);
// });
// Route::get('/', function () 
// {
//     $data =DB::table('customers')->find(3);
//     print_r($data);
// });
// Route::get('/', function () 
// {
//     $data =DB::table('customers')->select('name', 'email')->get();
//     print_r($data);
// });
// Route::get('/', function () 
// {
//     $data =DB::table('customers')->distinct()->get();
//     print_r($data);
// });
