<?php

namespace Dalyio\Challenge\Http\Controllers\Api\V1\Zipcode;

use Dalyio\Challenge\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DistanceController extends ApiController
{
    /**
     * @var \Dalyio\Challenge\Services\CalcDistance
     */
    private $calcDistance;
    
    /**
     * @return void
     */
    public function __construct(
        \Dalyio\Challenge\Services\CalcDistance $calcDistance
    ) {
        $this->calcDistance = $calcDistance;
    }
    
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function calculate(Request $request)
    {
        try {
            timer('start');
            return response()->json([
                'success' => true,
                'distances' => $this->calcDistance->byZipcodes($this->serialize($request->input('zipcodes'))),
                'timer' => timer('result'),
            ]);
        } catch (HttpException $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }
}
