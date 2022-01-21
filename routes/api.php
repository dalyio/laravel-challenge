<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/v1/zipcode/distance', 'V1\Zipcode\DistanceController@calculate')->name('api-zipcode-distance');