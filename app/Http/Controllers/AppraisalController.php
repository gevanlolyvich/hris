<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalRating;
use App\Models\Branch;
use App\Models\Competencies;
use App\Models\Employee;
use App\Models\Goal;
use App\Models\GoalEvaluation;
use App\Models\Indicator;
use App\Models\IndicatorWeight;
use App\Models\Performance_Type;
use App\Models\LevelDesignation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class AppraisalController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Appraisal')) {
            $user = \Auth::user();
            if ($user->type == 'employee') {
                $subordinate_ids = \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray();
                $employee_id = null;
                if (!empty($subordinate_ids)) {
                    $employee_id = $subordinate_ids;
                    $employee_id[] = \Auth::user()->employee->id;
                } else {
                    $employee_id[] = \Auth::user()->employee->id;
                }
                
                $appraisals = Appraisal::whereIn('employee_id', $employee_id)->get();
            } else {
                $appraisals = Appraisal::get();
            }

            return view('appraisal.index', compact('appraisals'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('Create Appraisal')) {
            if (\Auth::user()->type == 'employee') {
                $subordinate_ids = \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray();
                $employee_id = null;
                if (!empty($subordinate_ids)) {
                    $employee_id = $subordinate_ids;
                    $employee_id[] = \Auth::user()->employee->id;
                } else {
                    $employee_id[] = \Auth::user()->employee->id;
                }
                
                $employees = Employee::orderBy('name', 'ASC')->whereIn('id', $employee_id)->get();
            } else {
                $employees = Employee::orderBy('name', 'ASC')->get();
            }

            foreach ($employees as $key => $employee) {
                $department     = $employee?->department?->name ?? '-';
                $designation    = $employee?->designation?->name ?? '-';
                $employee->name = "$employee->name | $department | $designation";
            }

            $employees      = $employees->pluck('name', 'id');

            $main_goals     = Goal::whereNull('parent_id')->whereNull('employee_id')->select('name', 'id')->get()->pluck('name', 'id');

            return view('appraisal.create', compact('employees', 'main_goals'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        // return $request;
        if (\Auth::user()->can('Create Appraisal')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'start_month' => 'date|required',
                    'end_month' => 'date|required|after_or_equal:start_month',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $goal_rating   = $request->goal_rating ?? [];
            foreach($goal_rating as $rating) {
                if ($rating < 1 || $rating > 5) {
                    return redirect()->back()->with('error', 'Rating Must Be Between 1 and 5');
                }
            }
            $competency_rating   = $request->competency_rating ?? [];
            foreach($competency_rating as $rating) {
                if ($rating < 1 || $rating > 5) {
                    return redirect()->back()->with('error', 'Rating Must Be Between 1 and 5');
                }
            }

            $start_month            = date('Y-m-d', strtotime($request->start_month));
            $end_month              = date('Y-m-t', strtotime($request->end_month));

            $duplicate_appraisal    = Appraisal::where('employee_id', $request->employee_id)
                                        ->where(function ($query) use ($start_month, $end_month) {
                                            $query->orWhereBetween('start_month', [$start_month, $end_month])
                                                ->orWhereBetween('end_month', [$start_month, $end_month])
                                                ->orWhere(function ($query) use ($start_month, $end_month) {
                                                    $query->where('start_month', '<=', $start_month)
                                                            ->where('end_month', '>=', $end_month);
                                                });
                                        })->first();
            if ($duplicate_appraisal) {
                return redirect()->back()->with('error', __('Appraisal Already Exist'));
            }

            // Calculating Total Goal Weight Validity
            if ($request->goal_weight) {
                $total_goal_weight                      = array_reduce($request->goal_weight, function ($carry, $item) {
                                                                $carry += $item;
                                                                return $carry;
                                                            },
                                                        );
                if (!($total_goal_weight == 0 || $total_goal_weight == 100)) {
                    return redirect()->back()->with('error', __('Total Goal Weight Must Be 0 Or 100'));
                }
            }

            $appraisal                          = new Appraisal();
            $appraisal->employee_id             = $request->employee_id;
            $appraisal->indicator_id            = $request->indicator_id;
            $appraisal->start_month             = date('Y-m-d', strtotime($request->start_month));
            $appraisal->end_month               = date('Y-m-t', strtotime($request->end_month));
            $appraisal->created_by              = \Auth::user()->id;
            $appraisal->total_goal_score        = 0;
            $appraisal->total_goal_overall      = 0;
            $appraisal->total_competency_score  = 0;
            $appraisal->total_competency_overall= 0;
            $appraisal->total_apprisal          = 0;
            $appraisal->save();

            $total_goal_score           = 0;
            $total_goal_overall         = 0;
            $total_competency_score     = 0;    
            $total_competency_overall   = 0;        
            $total_apprisal             = 0;

            if ($request->goal_rating) {
                foreach ($request->goal_rating as $goal_id => $rating) 
                {
                    $score              = (int) $request->goal_weight[$goal_id] * (int) $rating;
                    $total_goal_score   += $score;
    
                    $goal_eva                           = new GoalEvaluation();
                    $goal_eva->apprisal_id              = $appraisal?->id ?? 0;
                    $goal_eva->goal_id                  = $goal_id;
                    $goal_eva->evaluation               = $request->goal_evaluation[$goal_id];
                    $goal_eva->weight                   = $request->goal_weight[$goal_id];
                    $goal_eva->rating                   = $rating;
                    $goal_eva->score                    = $score;
                    $goal_eva->save();
    
                    // saving the main goal of the evaluated goal
                    $goal               = Goal::find($goal_id);
                    $goal->parent_id    = $request->main_goal_id[$goal_id];
                    $goal->save();
                }
            }

            if ($request->competency_rating) {
                foreach ($request->competency_rating as $competency_id => $rating) {
                    $score_competency                   = (int) $request->competency_weight[$competency_id] * (int) $rating;
                    $total_competency_score             += $score_competency;
    
                    $appraisal_rate                     = new AppraisalRating();
                    $appraisal_rate->appraisal_id       = $appraisal?->id ?? 0;
                    $appraisal_rate->competency_id      = $competency_id;
                    $appraisal_rate->evaluation         = $request->competency_evaluation[$competency_id];
                    $appraisal_rate->rating             = $rating;
                    $appraisal_rate->score              = $score_competency;
                    $appraisal_rate->save();
                }
            }

            if ($request->competency_essay) {
                foreach ($request->competency_essay as $competency_id => $evaluation) {
                    $appraisal_essay                = new AppraisalRating();
                    $appraisal_essay->appraisal_id  = $appraisal?->id ?? 0;
                    $appraisal_essay->competency_id = $competency_id;
                    $appraisal_essay->evaluation    = $evaluation;
                    $appraisal_essay->save();
                }
            }

            // Calculating overall
            $indicator                      = Indicator::find($request->indicator_id);
            $level                          = $indicator?->level;

            if ($level?->goal_weight > 0 && $level?->competency_weight > 0) {
                $total_goal_overall         = ($total_goal_score / 100) * $level->goal_weight;
                $total_competency_overall   = ($total_competency_score / 100) * $level->competency_weight;
                $total_apprisal             = ($total_goal_overall + $total_competency_overall) / 100;
            } else {
                $total_goal_overall         = 0;
                $total_competency_overall   = $total_competency_score / 100;
                $total_apprisal             = $total_competency_overall;
            }
            
            $category   = null;
            if ($total_apprisal > 4.4) {
                $category   = 'Exceptional (O)';
            } else if ($total_apprisal >= 3.4 && $total_apprisal <= 4.4) {
                $category   = 'Commendable (VG)';
            } else if ($total_apprisal >= 3 && $total_apprisal <= 3.4) {
                $category   = 'Good (G+)';
            } else if ($total_apprisal >= 2.4 && $total_apprisal <= 3) {
                $category   = 'Good (G)';
            } else if ($total_apprisal >= 1.4 && $total_apprisal <= 2.4) {
                $category   = 'Sufficient (R)';
            } else if ($total_apprisal <= 1.4) {
                $category   = 'Underperformance (U)';
            } 

            $appraisal->total_goal_score            = $total_goal_score;
            $appraisal->total_competency_score      = $total_competency_score;
            $appraisal->total_goal_overall          = $total_goal_overall;
            $appraisal->total_competency_overall    = $total_competency_overall;
            $appraisal->total_apprisal              = $total_apprisal;
            $appraisal->category                    = $category;
            $appraisal->save();

            return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully created.'));
        }
    }

    public function show(Appraisal $appraisal)
    {
        if ($appraisal->created_by == \Auth::user()->id // the one who craete the appraisal
            || \Auth::user()->type == 'company' // admin
            || $appraisal->employee_id == \Auth::user()->employee?->id // the employee being assessed
            || (\Auth::user()->type == 'hr' && (\Auth::user()->branch_id == null || $appraisal?->employee?->branch_id)) // HR
            || in_array($appraisal->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray()) // the supervisor
        ) {
            $employee           = Employee::where('id', $appraisal->employee_id)->get();
            foreach ($employee as $key => $e) {
                $department     = $e?->department?->name ?? '-';
                $designation    = $e?->designation?->name ?? '-';
                $e->name        = "$e->name | $department | $designation";
            }
            $employee           = $employee->pluck('name', 'id');

            $main_goals         = Goal::whereNull('parent_id')->whereNull('employee_id')->select('name', 'id')->get()->pluck('name', 'id');
            $goals              = $appraisal->goal_evaluations;

            $competency_ratings = $appraisal->competency_ratings;
            $weighted_competency= $competency_ratings->whereNotNull('rating')->values();
            $essay_competency   = $competency_ratings->whereNull('rating')->values();
            $competency_ids     = $weighted_competency->pluck('competency_id');
            $indicators         = IndicatorWeight::where('indicator_id', $appraisal->indicator_id)->whereIn('competency_id', $competency_ids)->get()->pluck(null, 'competency_id');

            $performances       = [];
            foreach ($weighted_competency as $c_rating) {
                $id                                         = $c_rating?->competency?->performance_type?->id;
                if (isset($performances[$id])) {
                    array_push($performances[$id]['competencies'], [
                        'rating_id' => $c_rating?->id,
                        'name' => $c_rating?->competency?->name,
                        'description' => $c_rating?->competency?->description,
                        'weight' => $indicators[$c_rating->competency_id]?->weight,
                        'evaluation' => $c_rating?->evaluation,
                        'rating' => $c_rating?->rating,
                    ]);
                } else {
                    $temp_performance[$id]                  = [];
                    $temp_performance[$id]['id']            = $id;
                    $temp_performance[$id]['parent_id']     = $c_rating?->competency?->performance_type?->parent_id;
                    $temp_performance[$id]['name']          = $c_rating?->competency?->performance_type?->name;
                    $temp_performance[$id]['description']   = $c_rating?->competency?->performance_type?->description;

                    $temp_performance[$id]['competencies']  = [];

                    array_push($temp_performance[$id]['competencies'], [
                        'rating_id' => $c_rating?->id,
                        'name' => $c_rating?->competency?->name,
                        'description' => $c_rating?->competency?->description,
                        'weight' => $indicators[$c_rating->competency_id]?->weight,
                        'evaluation' => $c_rating?->evaluation,
                        'rating' => $c_rating?->rating,
                    ]);

                    $performances[$id]                      = $temp_performance[$id];
                }
            }

            return view('appraisal.show', compact('main_goals', 'employee', 'appraisal', 'goals', 'weighted_competency', 'essay_competency', 'performances'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(Appraisal $appraisal)
    {
        if (\Auth::user()->can('Edit Appraisal') &&
            (
                $appraisal->created_by == \Auth::user()->id // the one who craete the appraisal
                || \Auth::user()->type == 'company' // admin
                || (\Auth::user()->type == 'hr' && (\Auth::user()->branch_id == null || $appraisal?->employee?->branch_id)) // HR
                || in_array($appraisal->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray()) // the supervisor
            )
        ) {
            $employee           = Employee::where('id', $appraisal->employee_id)->get();
            foreach ($employee as $key => $e) {
                $department     = $e?->department?->name ?? '-';
                $designation    = $e?->designation?->name ?? '-';
                $e->name        = "$e->name | $department | $designation";
            }
            $employee           = $employee->pluck('name', 'id');

            $selected_employee  = Employee::find($appraisal->employee_id);

            $start_month        = $appraisal->start_month;
            $end_month          = $appraisal->end_month;

            $main_goals             = Goal::whereNull('parent_id')->whereNull('employee_id')->where('department_id', $selected_employee->department_id)
                                    ->where(function ($query) use ($start_month, $end_month) {
                                        $query->orWhereBetween('start_date', [$start_month, $end_month])
                                            ->orWhereBetween('end_date', [$start_month, $end_month])
                                            ->orWhere(function ($query) use ($start_month, $end_month) {
                                                $query->where('start_date', '<=', $start_month)
                                                        ->where('end_date', '>=', $end_month);
                                            });
                                    })->get()->pluck('name', 'id');
            $goals              = $appraisal->goal_evaluations;

            $competency_ratings = $appraisal->competency_ratings;
            $weighted_competency= $competency_ratings->whereNotNull('rating')->values();
            $essay_competency   = $competency_ratings->whereNull('rating')->values();
            $competency_ids     = $weighted_competency->pluck('competency_id');
            $indicators         = IndicatorWeight::where('indicator_id', $appraisal->indicator_id)->whereIn('competency_id', $competency_ids)->get()->pluck(null, 'competency_id');

            $performances       = [];
            foreach ($weighted_competency as $c_rating) {
                $id                                         = $c_rating?->competency?->performance_type?->id;
                if (isset($performances[$id])) {
                    array_push($performances[$id]['competencies'], [
                        'rating_id' => $c_rating?->id,
                        'name' => $c_rating?->competency?->name,
                        'description' => $c_rating?->competency?->description,
                        'weight' => $indicators[$c_rating->competency_id]?->weight,
                        'evaluation' => $c_rating?->evaluation,
                        'rating' => $c_rating?->rating,
                    ]);
                } else {
                    $temp_performance[$id]                  = [];
                    $temp_performance[$id]['id']            = $id;
                    $temp_performance[$id]['parent_id']     = $c_rating?->competency?->performance_type?->parent_id;
                    $temp_performance[$id]['name']          = $c_rating?->competency?->performance_type?->name;
                    $temp_performance[$id]['description']   = $c_rating?->competency?->performance_type?->description;

                    $temp_performance[$id]['competencies']  = [];

                    array_push($temp_performance[$id]['competencies'], [
                        'rating_id' => $c_rating?->id,
                        'name' => $c_rating?->competency?->name,
                        'description' => $c_rating?->competency?->description,
                        'weight' => $indicators[$c_rating->competency_id]?->weight,
                        'evaluation' => $c_rating?->evaluation,
                        'rating' => $c_rating?->rating,
                    ]);

                    $performances[$id]                      = $temp_performance[$id];
                }
            }

            return view('appraisal.edit', compact('main_goals', 'employee', 'appraisal', 'goals', 'weighted_competency', 'essay_competency', 'performances'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, Appraisal $appraisal)
    {
        if (\Auth::user()->can('Edit Appraisal') &&
            (
                $appraisal->created_by == \Auth::user()->id // the one who craete the appraisal
                || \Auth::user()->type == 'company' // admin
                || (\Auth::user()->type == 'hr' && (\Auth::user()->branch_id == null || $appraisal?->employee?->branch_id)) // HR
                || in_array($appraisal->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray()) // the supervisor
            )
        ) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'start_month' => 'date|required',
                    'end_month' => 'date|required|after_or_equal:start_month',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $goal_rating   = $request->goal_rating ?? [];
            foreach($goal_rating as $rating) {
                if ($rating < 0 || $rating > 5) {
                    return redirect()->back()->with('error', 'Rating Must Be Between 1 and 5');
                }
            }
            $competency_rating   = $request->competency_rating ?? [];
            foreach($competency_rating as $rating) {
                if ($rating < 0 || $rating > 5) {
                    return redirect()->back()->with('error', 'Rating Must Be Between 1 and 5');
                }
            }

            $start_month            = date('Y-m-d', strtotime($request->start_month));
            $end_month              = date('Y-m-t', strtotime($request->end_month));

            $duplicate_appraisal    = Appraisal::whereNot('id', $appraisal->id)->where('employee_id', $request->employee_id)
                                        ->where(function ($query) use ($start_month, $end_month) {
                                            $query->orWhereBetween('start_month', [$start_month, $end_month])
                                                ->orWhereBetween('end_month', [$start_month, $end_month])
                                                ->orWhere(function ($query) use ($start_month, $end_month) {
                                                    $query->where('start_month', '<=', $start_month)
                                                            ->where('end_month', '>=', $end_month);
                                                });
                                        })->first();
            if ($duplicate_appraisal) {
                return redirect()->back()->with('error', __('Appraisal Already Exist'));
            }

            // Calculating Total Goal Weight Validity
            if ($request->goal_weight) {
                $total_goal_weight  = array_reduce($request->goal_weight, function ($carry, $item) {
                                            $carry += $item;
                                            return $carry;
                                        },
                                    );
                if (!($total_goal_weight == 0 || $total_goal_weight == 100)) {
                    return redirect()->back()->with('error', __('Total Goal Weight Must Be 0 Or 100'));
                }
            }

            $total_goal_score           = 0;
            $total_goal_overall         = 0;
            $total_competency_score     = 0;    
            $total_competency_overall   = 0;        
            $total_apprisal             = 0;

            // Updating the goal evaluation
            if ($request->goal_rating) {
                foreach ($request->goal_rating as $goal_eval_id => $rating) 
                {
                    $score              = (int) $request->goal_weight[$goal_eval_id] * (int) $rating;
                    $total_goal_score   += $score;
    
                    $goal_eva                           = GoalEvaluation::find($goal_eval_id);
                    $goal_eva->evaluation               = $request->goal_evaluation[$goal_eval_id];
                    $goal_eva->weight                   = $request->goal_weight[$goal_eval_id];
                    $goal_eva->rating                   = $rating;
                    $goal_eva->score                    = $score;
                    $goal_eva->save();

                    // saving the main goal of the evaluated goal
                    $goal               = Goal::find($goal_eva->goal_id);
                    $goal->parent_id    = $request->main_goal_id[$goal_eval_id];
                    $goal->save();
                }
            }

            // Updating Appraisal Rating Compentency Grade / Weighted
            if ($request->competency_rating) {
                foreach ($request->competency_rating as $appraisal_rating_id => $rating) {
                    $score_competency                   = (int) $request->competency_weight[$appraisal_rating_id] * (int) $rating;
                    $total_competency_score             += $score_competency;
    
                    $appraisal_rate                     = AppraisalRating::find($appraisal_rating_id);
                    $appraisal_rate->evaluation         = $request->competency_evaluation[$appraisal_rating_id];
                    $appraisal_rate->rating             = $rating;
                    $appraisal_rate->score              = $score_competency;
                    $appraisal_rate->save();
                }
            }

            // Updating Appraisal Essay Evaluation
            if ($request->essay) {
                foreach ($request->essay as $appraisal_essay_id => $evaluation) {
                    $appraisal_essay                = AppraisalRating::find($appraisal_essay_id);
                    $appraisal_essay->evaluation    = $evaluation;
                    $appraisal_essay->save();
                }
            }

            // Calculating Overall
            $indicator                      = Indicator::find($request->indicator_id);
            $level                          = $indicator?->level;

            if ($level?->goal_weight > 0 && $level?->competency_weight > 0) {
                $total_goal_overall         = ($total_goal_score / 100) * $level->goal_weight;
                $total_competency_overall   = ($total_competency_score / 100) * $level->competency_weight;
                $total_apprisal             = ($total_goal_overall + $total_competency_overall) / 100;
            } else {
                $total_goal_overall         = 0;
                $total_competency_overall   = $total_competency_score / 100;
                $total_apprisal             = $total_competency_overall;
            }

            $category   = null;
            if ($total_apprisal > 4.4) {
                $category   = 'Exceptional (O)';
            } else if ($total_apprisal >= 3.4 && $total_apprisal <= 4.4) {
                $category   = 'Commendable (VG)';
            } else if ($total_apprisal >= 3 && $total_apprisal <= 3.4) {
                $category   = 'Good (G+)';
            } else if ($total_apprisal >= 2.4 && $total_apprisal <= 3) {
                $category   = 'Good (G)';
            } else if ($total_apprisal >= 1.4 && $total_apprisal <= 2.4) {
                $category   = 'Sufficient (R)';
            } else if ($total_apprisal <= 1.4) {
                $category   = 'Underperformance (U)';
            }

            $appraisal->start_month                 = date('Y-m-d', strtotime($request->start_month));
            $appraisal->end_month                   = date('Y-m-t', strtotime($request->end_month));
            $appraisal->total_goal_score            = $total_goal_score;
            $appraisal->total_competency_score      = $total_competency_score;
            $appraisal->total_goal_overall          = $total_goal_overall;
            $appraisal->total_competency_overall    = $total_competency_overall;
            $appraisal->total_apprisal              = $total_apprisal;
            $appraisal->category                    = $category;
            $appraisal->save();

            return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully updated.'));
        }
    }


    public function destroy(Appraisal $appraisal)
    {
        if (\Auth::user()->can('Delete Appraisal') && 
            (
                $appraisal->created_by == \Auth::user()->id // the one who craete the appraisal
                || \Auth::user()->type == 'company' // admin
                || (\Auth::user()->type == 'hr' && (\Auth::user()->branch_id == null || $appraisal?->employee?->branch_id)) // HR
                || in_array($appraisal->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray()) // the supervisor
            )
        ) {
            if ($appraisal->created_by == \Auth::user()->id || \Auth::user()->type == 'company') {
                $appraisal->delete();

                GoalEvaluation::where('apprisal_id', $appraisal->id)->delete();
                AppraisalRating::where('appraisal_id', $appraisal->id)->delete();

                return redirect()->route('appraisal.index')->with('success', __('Appraisal successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getGoals(Request $request) {
        $employee               = Employee::find($request->employee_id);
        $start_month            = date('Y-m-d', strtotime($request->start_month));
        $end_month              = date('Y-m-t', strtotime($request->end_month));
        $personal_goals         = Goal::where('employee_id', $request->employee_id)
                                    ->where(function ($query) use ($start_month, $end_month) {
                                        $query->orWhereBetween('start_date', [$start_month, $end_month])
                                            ->orWhereBetween('end_date', [$start_month, $end_month])
                                            ->orWhere(function ($query) use ($start_month, $end_month) {
                                                $query->where('start_date', '<=', $start_month)
                                                        ->where('end_date', '>=', $end_month);
                                            });
                                    })->get();
                                    
        $main_goals             = Goal::whereNull('parent_id')->whereNull('employee_id')->where('department_id', $employee->department_id)
                                    ->where(function ($query) use ($start_month, $end_month) {
                                        $query->orWhereBetween('start_date', [$start_month, $end_month])
                                            ->orWhereBetween('end_date', [$start_month, $end_month])
                                            ->orWhere(function ($query) use ($start_month, $end_month) {
                                                $query->where('start_date', '<=', $start_month)
                                                        ->where('end_date', '>=', $end_month);
                                            });
                                    })->get();
        return ['personal_goals' => $personal_goals, 'main_goals' => $main_goals];
    }

    public function getMainGoals() {
        $main_goals     = Goal::whereNull('parent_id')->whereNull('employee_id')->select('name', 'id')->get()->pluck('name', 'id');
        return $main_goals;
    }

    public function getWeightedCompetency(Request $request) {
        $employee               = Employee::find($request->employee_id);
        $designation_id         = $employee?->designation?->id ?? 0;
        $levels                 = LevelDesignation::whereRaw("FIND_IN_SET({$designation_id}, designation_ids)")->get();
        $employee_level         = null;
        foreach ($levels as $level) {
            $level_designations = explode(',', $level?->designation_ids);
            if (in_array($designation_id, $level_designations)) {
                $employee_level = $level;
            }
        }
        $indicator              = Indicator::where('level_id', $employee_level?->id)->first();
        $weights                = $indicator?->weights ?? [];
        $performances           = [];

        foreach($weights as $weight) {
            $id                                         = $weight?->competency?->performance_type?->id;
            if (isset($performances[$id])) {
                array_push($performances[$id]['competencies'], [
                    'id' => $weight?->competency_id,
                    'name' => $weight?->competency?->name,
                    'description' => $weight?->competency?->description,
                    'weight' => $weight?->weight,
                ]);
            } else {
                $temp_performance[$id]                  = [];
                $temp_performance[$id]['id']            = $id;
                $temp_performance[$id]['parent_id']     = $weight?->competency?->performance_type?->parent_id;
                $temp_performance[$id]['indicator_id']  = $indicator->id;
                $temp_performance[$id]['name']          = $weight?->competency?->performance_type?->name;
                $temp_performance[$id]['description']   = $weight?->competency?->performance_type?->description;

                $temp_performance[$id]['competencies']  = [];

                array_push($temp_performance[$id]['competencies'], [
                    'id' => $weight?->competency_id,
                    'name' => $weight?->competency?->name,
                    'description' => $weight?->competency?->description,
                    'weight' => $weight?->weight,
                ]);

                $performances[$id]                      = $temp_performance[$id];
            }
        }

        return response()->json($performances);
    }

    public function getEssayCompetency(Request $request) {
        $essays = Competencies::where('type', 'Essay')->select('id', 'name', 'description')->get();
        return $essays;
    }
}
