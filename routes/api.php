<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/api/v1/zipcode/distance', 'V1\Zipcode\DistanceController@calculate')->name('api-zipcode-distance');