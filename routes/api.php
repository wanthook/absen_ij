<?php

use Illuminate\Http\Request;

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
Route::group(['midleware' => 'api,cors'], function($router)
{
    Route::post('register', 'Api\UserController@register');
    Route::post('login', 'Api\UserController@login');
//    Route::get('profile', 'Api\UserController@getAuthenticateUser');
});

Route::group(['midleware' => 'auth:api'],function()
{   
    Route::post('profile', 'Api\UserController@getAuthenticatedUser');
});
