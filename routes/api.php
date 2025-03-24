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
/* ->withoutMiddleware(['auth:api']) */
Route::post('/login', 'App\Api\v1\Controllers\Auth\AuthController@login');

Route::group(['namespace' => 'App\Api\v1\Controllers'], function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('users', ['uses' => 'UserController@index']);
        Route::get('clients', ['uses' => 'ClientController@index']);

    
        /**
         * Users
         */
        /* Route::group(['prefix' => 'users'], function () {
            Route::get('/data', 'UsersController@anyData')->name('users.data');
        }); */
        
    });
}); 



    

/* Route::post('/loginAPI', 'App\Http\Controllers\Auth\LoginController@loginAPI'); */

/* Route::group(['namespace' => 'App\Api\v1\Controllers', 'middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/users', 'UserController@index');
});

Route::post('/loginAPI', 'Api\ApiAuthController@login');
// Dans routes/api.php
Route::post('/login', 'Auth\LoginController@loginAPI')->withoutMiddleware(['web']); */

/* -------------------------------
    Routes Publiques (Sans Auth)
---------------------------------- */
/* Route::group(['namespace' => 'App\Http\Controllers'], function () {
    Route::post('/login', 'Api\AuthController@login');
}); 
 */
/* -------------------------------
    Routes Protégées (Avec Token)
---------------------------------- */
/* Route::group([
    'namespace' => 'App\Api\v1\Controllers',
    'middleware' => ['auth:api']
], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/users', 'UserController@index');
}); */



/* ---------------------------
    Authentification API
--------------------------- */
/*  Route::post('/login', 'App\Http\Controllers\Api\AuthController@login');*/

// routes/api.php


/* ---------------------------
    Routes Protégées
--------------------------- */
/* Route::group([
    'middleware' => ['auth:api'], 
    'namespace' => 'App\Api\v1\Controllers'  
], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/users', 'UserController@index');
}); */