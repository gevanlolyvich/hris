<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\LeaveOffice;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LeaveOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Leave Office')) {
            $branch = Branch::find(\Auth::user()->branch_id);
            $branch_id = collect();
            if ($branch) {
                $branch_id->push($branch?->id);
            }

            $children = $branch?->childBranchFlatten();
            if ($children?->isNotEmpty()) {
                foreach ($children as $child) {
                    $branch_id->push($child->id);
                }
            }

            $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');

            $leaves       = LeaveOffice::orderby('date', 'DESC');

            // Filter result by user, superior can see it's subordinate data
            if(\Auth::user()->type == 'employee') {
                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $userId = \Auth::user()->employee->user_id;
                $subordinates = \Auth::user()->employee->subordinatesFlatten();

                // Check if employee managing other employee or not
                if ($subordinates->isNotEmpty()) {
                    $employees = collect();
                    foreach ($subordinates as $subordinate) {
                        $employees->push($subordinate->id);
                    }

                    $employees->push($emp);

                    $leaves = $leaves->whereIn('employee_id', $employees);
                } else {
                    $leaves = $leaves->where('employee_id', $emp);
                }
            }
            else
            {
                $employee = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->select('id') : Employee::select('id');
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                $employee = $employee?->orderby('name', 'asc')?->get()?->pluck('id');

                $leaves = $leaves->whereIn('employee_id', $employee);
            }

            // Filter by optional query
            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));
    
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                $leaves->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $leaves->where('date', $request->date);
            } else {
                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                $leaves->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            }
            $leaves   = $leaves->get();

            return view('leave-office.index', compact('leaves', 'branch'));
        } else  {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if(\Auth::user()->can('Create Leave Office'))
        {
            return view('leave-office.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Leave Office'))
        {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required|date|after_or_equal:today',
                    'purpose' => 'required',
                ]
            );
    
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
    
                return redirect()->back()->with('error', $messages->first());
            }

            // Create New Vehicle Officer
            $leave                  = new LeaveOffice();
            $leave->employee_id     = \Auth::user()?->employee?->id;
            $leave->date            = $request->date;
            $leave->purpose         = $request->purpose;
            $leave->status          = 'Waiting Superior Approval';
            $leave->save();
            

            // Send Notification To Superior
            // 1. Collect the reciever (subs) data that we need to send
            $superior_ids   = \Auth::user()?->employee?->managersFlatten()->pluck('id')->toArray();
            $superiors      = Employee::whereIn('id', $superior_ids)->whereNotIn('id', [97, 99, 98])->get();
            $subscriptions = [];
            foreach ($superiors as $superior) {
                foreach ($superior?->user?->pushNotifications ?? [] as $sub) {
                    array_push($subscriptions, ['data' => $sub->data, 'name' => $superior->user->name]);
                }
            }

            // 2. Send push notification to list of reciever (subs)
            \Auth::user()->sendNotifications(
                $subscriptions,
                json_encode([
                    'title' => __('New Leave Office Request'),
                    'body' => \Auth::user()->name . '  ' . __('Make Leave Office Request') . ' ' . __('On Date') . ' ' . $request->date,
                    'url' => "/leave-office?type=daily&month=&date={$request->date}&branch="
                ]),
                'normal'
            );

            return redirect()->back()->with('success', __('Leave Office Successfully Created'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (\Auth::user()->can('Manage Leave Office')) {
            $leave = LeaveOffice::find($id);
            return view('leave-office.show', compact('leave'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (\Auth::user()->can('Edit Leave Office')) {
            $leave = LeaveOffice::find($id);
            return view('leave-office.edit', compact('leave'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $leave = LeaveOffice::find($id);
        if (\Auth::user()->can('Delete Leave Office')) {
            $leave->delete();

            return redirect()->back()->with('success', __('Leave Office Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function approval(Request $request)
    {
        // return $request;
        if (\Auth::user()->can('Approval Leave Office')) {
            $leave                    = LeaveOffice::find($request->leave_id);

            // Update leave Status Data
            if (\Auth::user()->type == 'employee') {
                $leave->status                  = $request->status == 'Reject' ? 'Rejected By Superior' : 'Waiting HR Approval';
                $leave->superior_approval_by    = \Auth::user()->employee->id;
                $leave->save();
            } else  {
                $leave->status          = $request->status == 'Reject' ? 'Rejected By HR' : 'Approved';
                $leave->hr_approval_by  = \Auth::user()->id;
                $leave->save();                
            }

            // Send push notification to requester
            // $subscriptions = [];
            // if ($leave->requester->pushNotifications) {
            //     foreach ($leave->requester->pushNotifications ?? [] as $sub) {
            //         array_push($subscriptions, ['data' => $sub->data, 'name' => $leave->requester->name]);
            //     }
            // }
            // $status = $request->status == 'Approved' ? 'Approved' : 'Rejected';
            // \Auth::user()->sendNotifications(
            //     $subscriptions,
            //     json_encode([
            //         'title' => __('Vehicle leave Request') . ' ' . __($status),
            //         'body' => __('Vehicle leave Request') . ' ' . $leave->vehicle->name . ' '. __('For Date') . ' ' . $leave->date . ' '. __($status),
            //         'url' => '/vehicle-leave'
            //     ]),
            //     'normal'
            // );

            return redirect()->back()->with('success', __('Leave Office Status Successfully Updated'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
