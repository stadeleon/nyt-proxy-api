<?php

use App\Http\Controllers\Api\V1\NytBestsellersController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('nyt-bestsellers', NytBestsellersController::class);
});
//Route::get('nyt-bestsellers', NytBestsellersController::class);
//Route::get('test-cache', function () {
//    \Illuminate\Support\Facades\Cache::put('test_key', 'test_value', 60);
//    return \Illuminate\Support\Facades\Cache::get('test_key');
//});
