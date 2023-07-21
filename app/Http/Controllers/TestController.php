<?php

namespace App\Http\Controllers;

use App\Models\Employee;
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
        $attendances = $responses['attendances'];

        $employees = Employee::where('is_active', 1)->get();
        $match_data = [];
        for ($i = 0; $i < count($attendances); $i++) {
            for ($j = 0; $j < count($employees); $j++) {
                if ($employees[$j]->employee_id == $attendances[$i]['nrk']) {
                    array_push($match_data, $attendances[$i]);
                }
            }
        }
        return $match_data;
        return $attendances;
        return $employees;
    }
}
