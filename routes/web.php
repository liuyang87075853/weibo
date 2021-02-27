<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaticPagesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SessionController;
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

Route::get('/', [StaticPagesController::class,'home'])->name('home');
Route::get('/help',[StaticPagesController::class,'help'])->name('help');
Route::get('/about',[StaticPagesController::class,'about'])->name('about');
Route::get('signup',[UserController::class,'create'])->name('signup');

Route::resource('users',UserController::class);

Route::get('login',[SessionController::class,'create'])->name('login');
Route::post('login',[SessionController::class,'store'])->name('login');
Route::delete('logout',[SessionController::class,'destroy'])->name('logout');

