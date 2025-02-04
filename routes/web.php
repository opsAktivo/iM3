<?php

use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\HL7Controller;
use App\Models\VitalSignIm3Raw;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',
    [ExaminationController::class, 'index']
);

