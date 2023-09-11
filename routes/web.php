<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/payment/verify' , function(Request $request){
    $response = Http::post('http://localhost:8000/api/payment/verify' , [
        'trackId' => $request->trackId,
        'success' => $request->success,
        'status' => $request->status,
    ]);

    dd($response->json());

});
