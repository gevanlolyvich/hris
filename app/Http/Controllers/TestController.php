<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class TestController extends Controller
{
    function get_attendances()
    {
        $nodejs_url = "http://172.16.0.176:3020";
        $date = date('Y-m-d');
        $responses = Http::withHeaders([
            'X-APP-KEY' => 'PTJAKTOURJXBPTJAKTOURJXBPTJAKTOURJXBACCESSDOOOR'
        ])->get($nodejs_url . '/transaction-attendances?date=' . $date);
        $attendances = $responses->json();
        return $attendances;
    }
}
