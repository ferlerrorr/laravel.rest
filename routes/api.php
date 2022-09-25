<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::group(['prefix'=>'/v1'],function(){

    Route::resource('meeting','App\Http\Controllers\MeetingController',['except' =>['edit','create']]);


    Route::resource('registration','App\Http\Controllers\RegistrationController',['only' =>['store','destroy']]);
    
    
    Route::post('user',[
        'uses' => 'App\Http\Controllers\AuthController@store'
    ]);
    
    
    Route::post('user/signin',[
        'uses' => 'App\Http\Controllers\AuthController@signin'
    ]);

});
