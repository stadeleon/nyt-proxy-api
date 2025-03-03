<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/api/docs');
});
//Route::get('/', function () {
//    return view('welcome');
//});
