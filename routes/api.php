<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('talent.api')->group(function () {
    Route::get('ping', function (Request $request) {
        $principal = $request->attributes->get('api_principal');

        return response()->json(['data' => ['status' => 'ok', 'principal' => $principal->type]]);
    });
});
