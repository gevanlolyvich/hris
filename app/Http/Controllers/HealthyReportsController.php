<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\HealthyStep;
use App\Models\HealthyTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HealthyReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return $request;
        if (Auth::user()->can('Manage Healthy Report')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'start_date' => 'nullable|date|before_or_equal:end_date',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $branches = Branch::find(Auth::user()->branch_id);
            $branch_id = collect();
            if ($branches) {
                $branch_id->push($branches?->id);
            }

            $children = $branches?->childBranchFlatten();
            if ($children?->isNotEmpty()) {
                foreach ($children as $child) {
                    $branch_id->push($child->id);
                }
            }

            $branches = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');

            $calories_per_step = 0.04;
            $step_length = 0.75;

            $startDate = !empty($request?->start_date) ? Carbon::createFromFormat('Y-m-d', $request->start_date, 'Asia/Jakarta')->midDay() : Carbon::now()->subWeek()->startOfDay(); // 7 hari yang lalu
            // $startDate = Carbon::now()->previous(Carbon::MONDAY)->startOfDay(); // Hari Senin yang lalu
            $endDate = !empty($request?->end_date) ? Carbon::createFromFormat('Y-m-d', $request->end_date, 'Asia/Jakarta')->endOfDay() : Carbon::now()->endOfDay(); // Hari ini
            $daysInPeriod = $endDate->diffInDays($startDate) + 1; // Menghitung jumlah hari dalam periode
            // return [$request->start_date, $request->end_date, $daysInPeriod, $startDate, $endDate];

            $leaderboard = HealthyStep::selectRaw('employee_id, SUM(steps) as total_steps, SUM(steps) / ? as avg_steps', [$daysInPeriod])
                ->groupBy('employee_id')
                ->orderBy('total_steps', 'desc');

            $steps_report = HealthyStep::orderBy('date', 'desc');

            if (Auth::user()->type == 'employee') {
                $steps_report->where('employee_id', Auth::user()->employee->id);
            } else {
                if (!empty($request->branch_id)) {

                    $steps_report->whereHas('employee', function ($query) use ($request) {
                        $query->where('branch_id', $request->branch_id);
                    });
                    $leaderboard->whereHas('employee', function ($query) use ($request) {
                        $query->where('branch_id', $request->branch_id);
                    });
                }
            }

            $start_date_str = $startDate->format('Y-m-d');
            $end_date_str = $endDate->format('Y-m-d');

            if ($request->start_date && $request->end_date) {
                if ($request->start_date != $request->end_date) {
                    $leaderboard->whereBetween('date', [$startDate, $endDate]);
                    $steps_report->whereBetween('date', [$startDate, $endDate]);
                } else {
                    $leaderboard->where('date', $start_date_str);
                    $steps_report->where('date', $start_date_str);
                }
            } else {
                $leaderboard->whereBetween('date', [$startDate, $endDate]);
                $steps_report->whereBetween('date', [$startDate, $endDate]);
            }

            $steps_report = $steps_report->get();
            $leaderboard = $leaderboard->get();

            $total_steps = $steps_report->sum('steps');
            $calories = $total_steps * $calories_per_step;
            $distances = $total_steps * $step_length / 1000;
            $healthy_target = HealthyTarget::where('activity_name', 'Healthy Steps')->first();
            $steps_target = $healthy_target->target * $daysInPeriod;

            $weekDays = [];
            $weeklySteps = [];
            $weekDates = [];

            $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);

            foreach ($period as $date) {
                $formattedDate = $date->format('Y-m-d');
                $weekDays[$formattedDate] = Carbon::parse($formattedDate)->format('D');
                $weeklySteps[$formattedDate] = 0;
            }

            foreach ($period as $date) {
                $formattedDate = $date->format('Y-m-d');
                $weekDates[$formattedDate] = Carbon::parse($formattedDate)->format('d');
            }

            foreach ($steps_report as $data) {
                $formattedDate = Carbon::parse($data->date)->format('Y-m-d');
                $weeklySteps[$formattedDate] = $data->steps ? $data->steps : 0;
            }

            $weekDays = array_values($weekDays);
            $weeklySteps = array_values($weeklySteps);
            $weekDates = array_values($weekDates);



            return view('healthy_report.index', compact(
                'steps_report',
                'total_steps',
                'distances',
                'calories',
                'steps_target',
                'leaderboard',
                'weekDays',
                'weeklySteps',
                'weekDates',
                'start_date_str',
                'end_date_str',
                'branches'
            ));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->can('Manage Healthy Report')) {
            $activities = [
                'Healthy Steps' => __('Healthy Steps')
            ];
            return view('healthy_report.create', compact('activities'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'activity_name' => 'required',
                'target' => 'required'
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $availabilityCheck = HealthyTarget::where('activity_name', $request->activity_name)->first();
        if ($availabilityCheck) {
            return redirect()->back()->with('error', __('Healthy Report Already Exist'));
        }

        $healthy_target                   = new HealthyTarget();
        $healthy_target->activity_name    = $request->activity_name;
        $healthy_target->target           = $request->target;
        $healthy_target->save();

        return redirect()->back()->with('success', __('Healthy Report Successfully Created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HealthyTarget  $healthy_target
     * @return \Illuminate\Http\Response
     */
    public function show(HealthyTarget $healthy_target)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HealthyTarget  $healthy_target
     * @return \Illuminate\Http\Response
     */
    public function edit(HealthyTarget $healthy_target)
    {
        if (Auth::user()->can('Edit Healthy Report')) {
            $activities = [
                'Healthy Steps' => __('Healthy Steps')
            ];
            return view('healthy_report.edit', compact('healthy_target', 'activities'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HealthyTarget  $healthy_target
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HealthyTarget $healthy_target)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'activity_name' => 'required',
                'target' => 'required'
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $availabilityCheck = HealthyTarget::where('activity_name', $request->activity_name)
            ->whereNot('id', $healthy_target->id)->first();
        if ($availabilityCheck) {
            return redirect()->back()->with('error', __('Healthy Report Already Exist'));
        }

        $healthy_target->activity_name    = $request->activity_name;
        $healthy_target->target           = $request->target;
        $healthy_target->save();

        return redirect()->back()->with('success', __('Healthy Report Successfully Updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HealthyTarget  $healthy_target
     * @return \Illuminate\Http\Response
     */
    public function destroy(HealthyTarget $healthy_target)
    {
        if (Auth::user()->can('Delete Healthy Report')) {
            if (Auth::user()->type != 'employee') {
                if (count($healthy_target->healthy_steps) > 0) {
                    return redirect()->back()->with('error', __('Healthy data still exists, target cannot be deleted'));
                }
                $healthy_target->delete();
                return redirect()->back()->with('success', __('Healthy Report Successfully Deleted'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }
}
