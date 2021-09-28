<?php

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
    return view('welcome');
});

// Route::get('/message', function () {
//     $message['user'] = "Juan Perez";
//     $message['message'] =  "Prueba mensaje desde Pusher";
//     $success = event(new App\Events\MessagesEvent($message));
//     return $success;
// });

