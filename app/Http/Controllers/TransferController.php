<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBranchHistory;
use App\Models\ShiftType;
use App\Mail\TransferSend;
use App\Models\Transfer;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TransferController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('Manage Transfer')) {
            if (Auth::user()->type == 'employee') {
                $emp       = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $transfers = Transfer::where('employee_id', '=', $emp->id)->orderby('transfer_date', 'desc')->get();
            } else {
                $transfers = Transfer::orderby('transfer_date', 'desc')->get();
            }

            return view('transfer.index', compact('transfers'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Transfer')) {
            $departments = Department::get()->pluck('name', 'id');
            $branches    = Branch::get()->pluck('name', 'id');
            $employees   = Employee::where('is_active', 1)->orderby('name', 'asc')->get();
            $direct_spv  = Employee::where('is_active', 1)->where('user_id', '!=', \Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');

            foreach ($employees  as $employee) {
                $employee->name = "{$employee->name} | {$employee->branch->name}";
            }

            $employees  = $employees->pluck('name', 'id');

            return view('transfer.create', compact('employees', 'departments', 'branches', 'direct_spv'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        // return $request;
        if (\Auth::user()->can('Create Transfer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'branch_id' => 'required',
                    'department_id' => 'required',
                    'designation_id' => 'required',
                    'transfer_date' => 'required',
                    'shift_type_id' => 'required'
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::find($request->employee_id);

            $document_path = null;
            if ($request->file('myDocument')) {
                $docs = $request->file('myDocument');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/transfers', $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }

            $transfer                   = new Transfer();
            $transfer->employee_id      = $request->employee_id;
            $transfer->branch_id        = $request->branch_id;
            $transfer->department_id    = $request->department_id;
            $transfer->designation_id   = $request->designation_id;
            $transfer->managed_by       = $request->managed_by ?? null;
            $transfer->shift_type_id    = $request->shift_type_id;
            $transfer->transfer_date    = $request->transfer_date;
            $transfer->document_path    = $document_path;
            $transfer->description      = $request->description;
            $transfer->created_by       = \Auth::user()->id;
            $transfer->save();

            EmployeeBranchHistory::ensureInitialPlacement($transfer->employee_id);

            EmployeeBranchHistory::record(
                $transfer->employee_id,
                $transfer->branch_id,
                $transfer->transfer_date
            );


            $setings = Utility::settings();
            if ($setings['employee_transfer'] == 1) {
                $branch  = Branch::find($transfer->branch_id);
                $department = Department::find($transfer->department_id);
                $employee = Employee::find($transfer->employee_id);
                $uArr = [
                    'transfer_name' => $employee->name,
                    'transfer_date' => $request->transfer_date,
                    'transfer_department' => $department->name,
                    'transfer_branch' => $branch->name,
                    'transfer_description' => $request->description,
                ];
                $resp = Utility::sendEmailTemplate('employee_transfer', [$employee->email], $uArr);
                return redirect()->route('transfer.index')->with('success', __('Transfer  successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('transfer.index')->with('success', __('Transfer  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Transfer $transfer)
    {
        return redirect()->route('transfer.index');
    }

    public function edit(Transfer $transfer)
    {
        if (\Auth::user()->can('Edit Transfer')) {
            $departments    = Department::get()->pluck('name', 'id');
            $branches       = Branch::get()->pluck('name', 'id');
            $employees      = Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            $designations   = Designation::where('department_id', $transfer->department_id)->orderBy('name', 'asc')->get()->pluck('name', 'id');
            $shifts         = ShiftType::where('branch_id', $transfer->branch_id)->orderby('name', 'asc')->select('id', 'name')->get()->pluck('name', 'id');
            if ($transfer->created_by == \Auth::user()->id || \Auth::user()->type == 'company') {
                return view('transfer.edit', compact('transfer', 'employees', 'departments', 'branches', 'designations', 'shifts'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, Transfer $transfer)
    {
        // return $request;
        if (\Auth::user()->can('Edit Transfer')) {
            if ($transfer->created_by == \Auth::user()->id || \Auth::user()->type == 'company') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'branch_id' => 'required',
                        'department_id' => 'required',
                        'transfer_date' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $employee = Employee::find($request->employee_id);
                $document_path = $transfer->document_path;
                if ($request->file('myDocument')) {
                    $docs = $request->file('myDocument');
                    $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/transfers', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;

                    // Check if the file exists before attempting to delete
                    if ($transfer->document_path) {
                        $filepath_array = explode('/', $transfer->document_path);
                        $filename = array_pop($filepath_array);

                        if (Storage::disk('public')->exists("uploads/transfers/" . $filename)) {
                            Storage::disk('public')->delete("uploads/transfers/" . $filename);
                        }
                    }
                }

                $transfer->employee_id      = $request->employee_id;
                $transfer->branch_id        = $request->branch_id;
                $transfer->department_id    = $request->department_id;
                $transfer->designation_id   = $request->designation_id;
                $transfer->managed_by       = $request->managed_by ?? null;
                $transfer->shift_type_id    = $request->shift_type_id;
                $transfer->transfer_date    = $request->transfer_date;
                $transfer->document_path    = $document_path;
                $transfer->description      = $request->description;
                $transfer->save();

                return redirect()->route('transfer.index')->with('success', __('Transfer successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Transfer $transfer)
    {
        if (\Auth::user()->can('Delete Transfer')) {
            if ($transfer->created_by == \Auth::user()->id || \Auth::user()->type == 'company') {
                $transfer->delete();

                // Check if the file exists before attempting to delete
                if ($transfer->document_path) {
                    $filepath_array = explode('/', $transfer->document_path);
                    $filename = array_pop($filepath_array);

                    if (Storage::disk('public')->exists("uploads/transfers/" . $filename)) {
                        Storage::disk('public')->delete("uploads/transfers/" . $filename);
                    }
                }

                return redirect()->route('transfer.index')->with('success', __('Transfer successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function action($id)
    {
        $transfer     = Transfer::find($id);
        $employee  = Employee::find($transfer->employee_id);

        // return $employee;
        return view('transfer.action', compact('employee', 'transfer'));
    }
}
