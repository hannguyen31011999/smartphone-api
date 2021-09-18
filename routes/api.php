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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// login
Route::post('admin/login', 'Api\Backend\ApiLoginController@login');
Route::get('refresh/token', 'Api\Backend\ApiLoginController@refreshToken');

// api can token
Route::group(['middleware' => 'auth.jwt'], function () {
    // api admin
    Route::group(['prefix'=>'admin','namespace'=>'Api\Backend','middleware'=>'jwt.admin'],function(){
        // logout
        Route::get('logout', 'ApiLoginController@logout');
        // get info admin
        Route::get('info','ApiLoginController@getAdminInfo');
        // categories
        Route::group(['prefix'=>'categories'],function(){
            Route::get('/list','ApiCategoriesController@index');
            Route::post('/create','ApiCategoriesController@store');
            Route::get('/edit/{id}','ApiCategoriesController@edit');
            Route::put('/update/{id}','ApiCategoriesController@update');
            Route::delete('/delete/{id}','ApiCategoriesController@destroy');
            Route::get('/seach','ApiCategoriesController@seach');
        });

        // discount
        Route::group(['prefix'=>'discount'],function(){
            Route::get('/list','ApiDiscountController@index');
            Route::post('/create','ApiDiscountController@store');
            Route::get('/edit/{id}','ApiDiscountController@edit');
            Route::put('/update/{id}','ApiDiscountController@update');
            Route::delete('/delete/{id}','ApiDiscountController@destroy');
            Route::get('/seach','ApiDiscountController@seach');
        });

        // post
        Route::group(['prefix'=>'post'],function(){
            Route::get('/list','ApiPostController@index');
            Route::post('/create','ApiPostController@store');
            Route::get('/edit/{id}','ApiPostController@edit');
            Route::post('/update/{id}','ApiPostController@update');
            Route::delete('/delete/{id}','ApiPostController@destroy');
            Route::get('/seach','ApiPostController@seach');
            Route::post('/upload','ApiPostController@uploadFilePost');
        });

        // user
        Route::group(['prefix'=>'user'],function(){
            Route::get('/list','ApiUserController@index');
            Route::post('/create','ApiUserController@store');
            Route::get('/edit/{id}','ApiUserController@edit');
            Route::put('/update/{id}','ApiUserController@update');
            Route::delete('/delete/{id}','ApiUserController@destroy');
            Route::get('/seach','ApiUserController@seach');
            Route::patch('/status/{id}','ApiUserController@updateStatus');
        });

        // product
        Route::group(['prefix'=>'product'],function(){
            Route::get('/list','ApiProductController@index');
            Route::get('/parent','ApiProductController@getParentProduct');
            Route::post('/create','ApiProductController@store');
            Route::get('/edit/{id}','ApiProductController@edit');
            Route::put('/update/{id}','ApiProductController@update');
            Route::delete('/delete/{id}','ApiProductController@destroy');
            Route::get('/seach','ApiProductController@seach');
            Route::post('/variant/{product_id}','ApiProductController@createVariant');
            Route::put('/variant/{id}','ApiProductController@updateVariant');
            Route::delete('/variant/{id}','ApiProductController@deleteVariant');
        });
        // product sku
        Route::group(['prefix'=>'product/sku'],function(){
            Route::get('/list/{id}','ApiProductSkuController@index');
            Route::post('/create/{id}','ApiProductSkuController@store');
            Route::get('/edit/{id}','ApiProductSkuController@edit');
            Route::post('/update/{id}','ApiProductSkuController@update');
            Route::delete('/delete/{id}','ApiProductSkuController@destroy');
            Route::get('/seach','ApiProductSkuController@seach');
        });

        // inventory
        Route::group(['prefix'=>'inventory'],function(){
            Route::get('/list','ApiInventoryController@index');
            Route::get('/product','ApiInventoryController@getListProduct');
            Route::post('/create','ApiInventoryController@store');
            Route::get('/edit/{id}','ApiInventoryController@edit');
            Route::put('/update/{id}','ApiInventoryController@update');
            Route::patch('/status/{id}','ApiInventoryController@updateStatus');
            Route::get('/seach','ApiInventoryController@seach');
        });

        Route::group(['prefix'=>'review'],function(){
            Route::get('/list','ApiReviewController@index');
            Route::put('/update/{id}','ApiReviewController@update');
            Route::delete('/delete/{id}','ApiReviewController@destroy');
            Route::get('/seach','ApiReviewController@seach');
        });
    });
});

// api not token
Route::group(['namespace'=>'Api\Frontend'],function(){
    Route::get('/categories','ApiHomeController@getListCategories');
    Route::get('/product','ApiHomeController@getListProduct');
    Route::get('/product/promotion','ApiHomeController@getProductDiscount');
    Route::post('/visitor','ApiHomeController@createVisitor');
});

Route::group(['namespace'=>'Api\Frontend','prefix'=>'cart'],function(){
    Route::get('/list','ApiCartController@index');
    Route::post('/create','ApiCartController@store');
    Route::patch('/update/{id}','ApiCartController@update');
    Route::delete('/delete/{id}','ApiCartController@destroy');
});

Route::group(['namespace'=>'Api\Frontend','prefix'=>'register'],function(){
    Route::post('/create','ApiRegisterController@register');
    Route::put('/update/{id}','ApiRegisterController@update');
});

Route::group(['namespace'=>'Api\Frontend'],function(){
    Route::get('/redirect/{social}','ApiLoginClientController@redirect')->middleware('web');
    Route::get('/callback/{social}','ApiLoginClientController@callback')->middleware('web');
    Route::get('/social/remove','ApiLoginClientController@callback')->middleware('web');
    Route::post('/login','ApiLoginClientController@login');
    Route::get('/token/refresh','ApiLoginClientController@refreshToken');
    Route::get('/logout','ApiLoginClientController@logout')->middleware('auth.jwt');
});

Route::group(['namespace'=>'Api\Frontend','prefix'=>'detail'],function(){
    Route::get('/{slug}','ApiProductDetail@index');
    Route::post('/review/create','ApiProductDetail@createReview');
});

Route::group(['namespace'=>'Api\Frontend','prefix'=>'product'],function(){
    Route::get('/{slug}','ApiProductController@index');
});

Route::group(['namespace'=>'Api\Frontend','prefix'=>'categories'],function(){
    Route::get('/{id}','ApiProductController@getProductWithCategories');
});


