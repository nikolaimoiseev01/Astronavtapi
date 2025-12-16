<?php

namespace App\Http\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessKey;
use App\Models\AccessKeyStatistic;
use App\Models\CalculatorMeta\PbCity;
use App\Services\NatalCalculator\NatalService;
use Illuminate\Http\Request;

class NatalCalculatorController extends Controller
{
    public function __construct(
        protected NatalService $natalService,
    ) {
    }

    public function calculate(Request  $request)
    {
        $accessKey = AccessKey::where(
            'key',
            $request->header('Api-Access-Key')
        )->first();

        if (! $accessKey) {
            return response()->failWithMessage('Invalid access key');
        }

//        if ($accessKey->ip !== $request->ip()) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Invalid ip address',
//            ], 403);
//        }

//        if ($accessKey->expires_at->isPast()) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Access key has expired',
//            ], 403);
//        }


        $city = PbCity::find($request->city_id);

        if (! $city || empty($city->tz)) {
            return response()->json([
                'success' => false,
                'message' => 'No such city',
            ], 403);
        }

        $stat = AccessKeyStatistic::create([
            'access_key_id' => $accessKey['id'],
            'status' => 'pending',
        ]);

        $data = $this->natalService->calculate(
            $request->date,
            $request->time,
            $city->tz
        );

        $stat->update([
            'status' => 'success'
        ]);

        $data['additional_properties']['birth_location'] =
            $city->name . ', ' . $city->countryRelation?->name;

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
