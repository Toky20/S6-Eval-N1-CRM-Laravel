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
        Route::get('projects', ['uses' => 'ProjectController@index']); 
        Route::get('tasks', ['uses' => 'TaskController@index']);

        Route::get('offers', ['uses' => 'OfferController@index']);
        Route::get('offers/stat', ['uses' => 'OfferController@getOffersStatistics']);
        Route::get('offers/stat/clients', ['uses' => 'OfferController@getClientOffersStatistics']);
        Route::get('offers/stat/users', ['uses' => 'OfferController@getUserOffersStatistics']);

        Route::get('settings/remise', ['uses' => 'SettingController@getDiscountRate']);
        Route::put('settings/remise', ['uses' => 'SettingController@updateDiscountRate']);

        Route::get('invoices', ['uses' => 'InvoiceController@index']); 
        Route::get('invoices/stat/product', ['uses' => 'InvoiceController@getProductQty']); 
        Route::get('invoices/stat/status', ['uses' => 'InvoiceController@getInvoiceStatusDistribution']); 

        Route::get('invoices/remise/apply/{external_id}', ['uses' => 'InvoiceController@applyDiscount']); 
        Route::get('invoices/remise/remove/{external_id}', ['uses' => 'InvoiceController@removeDiscount']); 

        Route::get('payments/clients', ['uses' => 'PaymentController@getTotalPaymentsByClient']);
        Route::get('payments/clients/{clientId}', ['uses' => 'PaymentController@getPaymentsByClient']);

        Route::get('payments/days', ['uses' => 'PaymentController@getTotalPaymentsByDay']);
        Route::get('payments/days/{date}', ['uses' => 'PaymentController@getPaymentsByDay']);

        Route::get('payments/sources', ['uses' => 'PaymentController@getTotalByPaymentSource']);
        Route::get('payments/sources/{paymentSource}', ['uses' => 'PaymentController@getPaymentsBySource']);
        Route::get('payments/{external_id}', ['uses' => 'PaymentController@show']);
        Route::put('payments/{external_id}', ['uses' => 'PaymentController@update']);

    
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