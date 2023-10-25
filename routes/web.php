<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\IsAdmin;
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
Route::get('/', function () {
    return redirect('login');
});
Route::get('/delete', function () {
    return view('auth.delete');
});
Route::post('user_delete', [App\Http\Controllers\HomeController::class,'destroy'])->name('user.delete');

Auth::routes();
Route::middleware([IsAdmin::class])->group(function(){

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('users', [App\Http\Controllers\HomeController::class, 'index_user'])->name('users');
Route::get('/logout', [App\Http\Controllers\HomeController::class, 'logout'])->name('logout');
Route::get('/activate-user/{user_id}', [App\Http\Controllers\HomeController::class, 'Activate'])->name('activate');

Route::get('/inactivate-user/{user_id}', [App\Http\Controllers\HomeController::class, 'Inactivate'])->name('inactivate');
    Route::resource('coupons', App\Http\Controllers\CouponController::class);
    Route::get('couponsuser', [App\Http\Controllers\CouponController::class,'getUsers'])->name('coupon.user');
    Route::get('deleteCopon/{id}', [App\Http\Controllers\CouponController::class,'destroy'])->name('coupon.delete');
    Route::post('couponsassign', [App\Http\Controllers\CouponController::class,'assign'])->name('coupon.assign');

});

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
