<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Permit;
use App\Models\PermitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PermitController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Leave')) {
            $permits = Permit::where('created_by', '=', Auth::user()->creatorId())->get();
            if (Auth::user()->type == 'employee') {
                $user     = Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $permits   = Permit::where('employee_id', '=', $employee->id)->get();
            } else {
                $permits = Permit::where('created_by', '=', Auth::user()->creatorId())->get();
            }

            return view('permit.index', compact('permits'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('Create Leave')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', Auth::user()->id)->get()->pluck('name', 'id');
            } else {
                $employees = Employee::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
            }
            $permittypes   = PermitType::get();

            return view('permit.create', compact('employees', 'permittypes'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Leave')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'permit_type_id'    => 'required',
                    'start_date'        => 'required',
                    'end_date'          => 'required',
                    'reason'            => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $total_permit_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
            $permit_type = PermitType::find($request->permit_type_id);

            $permit = new Permit();

            if (Auth::user()->type == "employee") {
                $permit->employee_id = $employee->id;
            } else {
                $permit->employee_id = $request->employee_id;
            }

            $employee = Employee::find($permit->employee_id);
            $document_path = null;
            if ($request->file('document')) {
                $docs = $request->file('document');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/permits', $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }

            $permit->permit_type_id     = $permit_type->id;
            $permit->start_date         = $request->start_date;
            $permit->end_date           = $request->end_date;
            $permit->total_permit_days  = $total_permit_days + 1;
            $permit->reason             = $request->reason;
            $permit->docs               = $document_path;
            $permit->status             = 'Pending';
            $permit->created_by         = Auth::user()->creatorId();

            $permit->save();
            return redirect()->route('permit.index')->with('success', __('Attendance Permit Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Permit $permit)
    {
        return redirect()->route('permit.index');
    }

    public function edit($id)
    {
        $permit = Permit::find($id);

        if (Auth::user()->can('Edit Leave')) {
            if ($permit->created_by == Auth::user()->creatorId()) {
                $employees  = Employee::get()->pluck('name', 'id');
                $permittype = PermitType::get()->pluck('name', 'id');

                return view('permit.edit', compact('permit', 'employees', 'permittype'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $permit_id)
    {
        $permit = Permit::find($permit_id);
        if (Auth::user()->can('Edit Leave')) {
            if ($permit->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'permit_type_id'    => 'required',
                        'start_date'        => 'required',
                        'end_date'          => 'required',
                        'reason'            => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                // return $request;

                //* Custom Form
                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $total_permit_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

                $date = date_create($request->date);
                $document_path = null;
                if ($request->file('document')) {
                    $docs = $request->file('document');
                    $docName = time() . "_" . date_format($date, "Y-m-d") . "_" . preg_replace('/\s+/', '', $permit->employee->name) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/permits', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }

                //* Input Data
                $form = [
                    'employee_id'   => $request->employee_id,
                    'start_date'    => $request->start_date,
                    'end_date'      => $request->end_date,
                    'total_permit_days' => $total_permit_days + 1,
                    'reason'        => $request->reason,
                    'docs'          => $document_path ? $document_path : $permit->docs,
                ];

                //* Update Data
                Permit::where('id', $permit->id)->update($form);
                return redirect()->route('permit.index')->with('success', __('Attendance Permit Successfully Updated'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
