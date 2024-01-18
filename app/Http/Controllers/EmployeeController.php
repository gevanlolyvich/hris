<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Mail\UserCreate;
use App\Models\ShiftHistory;
use App\Models\User;
use App\Models\Bank;
use App\Models\Utility;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\NOC;
use App\Models\Termination;
use App\Models\ExperienceCertificate;
use App\Models\JoiningLetter;
use App\Models\ShiftType;
use Illuminate\Support\Facades\Log;

//use Faker\Provider\File;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
    //  * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (\Auth::user()->can('Manage Employee')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', Auth::user()->id)->orderby('name', 'asc')->get();
            } else {
                $employees = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->orderby('name', 'asc')->get() : Employee::orderby('name', 'asc')->get();
            }

            return view('employee.index', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Employee')) {
            $company_settings = Utility::settings();
            $documents        = Document::get();
            $branches         = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $departments      = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Department::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $department_id    = $departments->pluck('id')->toArray();
            $designations     = !empty(\Auth::user()->branch_id) ? Designation::whereIn('department_id', $department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Designation::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $employees        = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $shift_types      = ShiftType::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $nationalities    = ['WNI' => __('WNI'), 'WNA' => __('WNA')];
            $identity_types   = ['KTP' => __('KTP'), 'Passport' => __('Passport'), 'SIM' => __('SIM')];
            $banks            = Bank::orderBy('name')->get()->pluck('name', 'id');
            $emergency_contact_relations = [
                'Parent' => __('Parent'),
                'Children' => __('Children'),
                'Sibling' => __('Sibling'),
                'Spouse' => __('Spouse'),
                'Friend' => __('Friend'),
            ];
            $marital_statuses = [
                'Single' => __('Single'),
                'Married' => __('Married'),
                'Widowed' => __('Widowed'),
            ];

            $employeeTypes = Employee::$employeeTypes;

            return view('employee.create', compact('employees', 'departments', 'designations', 'documents', 'branches', 'company_settings', 'shift_types', 'nationalities', 'banks', 'identity_types', 'emergency_contact_relations', 'marital_statuses', 'employeeTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        // return $request;
        if (\Auth::user()->can('Create Employee')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required|unique:employees',
                    'personel_id' => 'nullable|unique:employees',
                    'shift_type_id' => 'required',
                    'name' => 'required',
                    'type' => 'required',
                    'dob' => 'required',
                    'gender' => 'required',
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                    'address' => 'required',
                    'emergency_contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                    'emergency_contact_relation' => 'required',
                    'email' => 'required|unique:users,email,NULL,NULL,deleted_at,NULL',
                    'password' => 'required',
                    'department_id' => 'required',
                    'designation_id' => 'required',
                    'document.*' => 'required',
                    'nationality' => 'required',
                    'identity_type' => 'required',
                    'identity_number' => 'required'
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }
            // return $request;

            $user = User::create(
                [
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'type' => 'employee',
                    'lang' => 'en',
                    'created_by' => \Auth::user()->id,
                    'branch_id' => $request['branch_id'],
                ]
            );
            $user->save();
            $user->assignRole('Employee');


            if (!empty($request->document) && !is_null($request->document)) {
                $document_implode = implode(',', array_keys($request->document));
            } else {
                $document_implode = null;
            }


            $employee = Employee::create(
                [
                    'user_id' => $user->id,
                    'personel_id' => $request['personel_id'],
                    'shift_type_id' => $request['shift_type_id'],
                    'managed_by' => $request['managed_by'],
                    'name' => $request['name'],
                    'type' => $request['type'],
                    'dob' => $request['dob'],
                    'gender' => $request['gender'],
                    'phone' => $request['phone'],
                    'address' => $request['address'],
                    'domicile_address' => $request['domicile_address'] || null,
                    'emergency_contact_number' => $request['emergency_contact_number'],
                    'emergency_contact_relation' => $request['emergency_contact_relation'],
                    'marital_status' => $request['marital_status'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'employee_id' => $request['employee_id'],
                    'branch_id' => $request['branch_id'],
                    'department_id' => $request['department_id'],
                    'designation_id' => $request['designation_id'],
                    'company_doj' => $request['company_doj'],
                    'documents' => $document_implode,
                    'account_holder_name' => $request['account_holder_name'],
                    'account_number' => $request['account_number'],
                    'bank_id' => $request['bank_id'],
                    'tax_payer_id' => $request['tax_payer_id'],
                    'nationality' => $request['nationality'],
                    'identity_type' => $request['identity_type'],
                    'identity_number' => $request['identity_number'],
                    'created_by' => \Auth::user()->creatorId(),
                ]
            );

            ShiftHistory::create(
                [
                    'shift_type_id' => $request['shift_type_id'],
                    'employee_id' => $employee->id,
                ]
            );

            if ($request->hasFile('document')) {
                foreach ($request->document as $key => $document) {


                    $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('document')[$key]->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $dir             = 'app/public/uploads/document/';

                    $image_path      = $dir . $fileNameToStore;

                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }

                    $path = Utility::upload_coustom_file($request, 'document', $fileNameToStore, $dir, $key, []);

                    if ($path['flag'] == 1) {
                        $url = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                    $employee_document = EmployeeDocument::create(
                        [
                            'employee_id' => $employee['id'],
                            'document_id' => $key,
                            'document_value' => $fileNameToStore,
                            'created_by' => \Auth::user()->creatorId(),
                        ]
                    );
                    $employee_document->save();
                }
            }

            $setings = Utility::settings();
            if ($setings['new_employee'] == 1) {
                $department = Department::find($request['department_id']);
                $branch = Branch::find($request['branch_id']);
                $designation = Designation::find($request['designation_id']);
                $uArr = [
                    'employee_email' => $user->email,
                    'employee_password' => $request->password,
                    'employee_name' => $request['name'],
                    'employee_branch' => $branch->name,
                    'department_id' => $department->name,
                    'designation_id' => !empty($designation->name) ? $designation->name : '',
                ];
                $resp = Utility::sendEmailTemplate('new_employee', [$user->id => $user->email], $uArr);

                return redirect()->route('employee.index')->with('success', __('Employee successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('employee.index')->with('success', __('Employee  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        if (\Auth::user()->can('Edit Employee')) {
            $branches         = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $departments      = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Department::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $department_id    = $departments->pluck('id')->toArray();
            $designations     = !empty(\Auth::user()->branch_id) ? Designation::whereIn('department_id', $department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Designation::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $employees        = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderBy('name', 'ASC')->get()->pluck('name', 'id');

            $documents        = Document::where('created_by', \Auth::user()->creatorId())->get();
            $employee         = Employee::find($id);
            $employeesId      = ($employee->employee_id);
            $shift_types      = ShiftType::get()->pluck('name', 'id');
            $nationalities    = ['WNI' => __('WNI'), 'WNA' => __('WNA')];
            $identity_types   = ['KTP' => __('KTP'), 'Passport' => __('Passport'), 'SIM' => __('SIM')];
            $banks            = Bank::orderBy('name')->get()->pluck('name', 'id');
            $marital_status = [
                'Single'  => __('Single'),
                'Married' => __('Married'),
                'Widowed' => __('Widowed'),
            ];
            $emergency_contact_relations = [
                'Parent'  => __('Parent'),
                'Children' => __('Children'),
                'Sibling' => __('Sibling'),
                'Spouse'  => __('Spouse'),
                'Friend'  => __('Friend'),
            ];

            $employeeTypes = Employee::$employeeTypes;
            // return $employee->bank_id;

            // return $employee;
            return view('employee.edit', compact('shift_types', 'employee', 'employees', 'employeesId', 'branches', 'departments', 'designations', 'documents', 'banks', 'nationalities', 'identity_types', 'marital_status', 'emergency_contact_relations', 'employeeTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('Edit Employee')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required|unique:employees,employee_id,' . $id,
                    // 'personel_id' => 'required|unique:employees,personel_id,' . $id,
                    'name' => 'required',
                    'type' => 'required',
                    'dob' => 'required',
                    'gender' => 'required',
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                    'address' => 'required',
                    'document.*' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('is_active', 1)->find($id);
            $user     = User::find($employee->user_id);
            if (empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Inactive'));
            }

            // create shift history when employee changing it's shift
            if ($employee->shift_type_id !== (int)$request['shift_type_id']) {
                ShiftHistory::create(
                    [
                        'shift_type_id' => $request['shift_type_id'],
                        'employee_id' => $employee->id,
                    ]
                );
            }

            if ($request->document) {
                foreach ($request->document as $key => $document) {
                    if (!empty($document)) {


                        $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
                        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension       = $request->file('document')[$key]->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                        $dir             = 'app/public/uploads/document/';

                        $image_path      = $dir . $fileNameToStore;

                        if (File::exists($image_path)) {
                            File::delete($image_path);
                        }

                        $path = Utility::upload_coustom_file($request, 'document', $fileNameToStore, $dir, $key, []);

                        if ($path['flag'] == 1) {
                            $url = $path['url'];
                        } else {
                            return redirect()->back()->with('error', __($path['msg']));
                        }

                        $employee_document = EmployeeDocument::where('employee_id', $employee->employee_id)->where('document_id', $key)->first();

                        if (!empty($employee_document)) {
                            if ($employee_document->document_value) {
                                File::delete(storage_path('app/public/uploads/document/' . $employee_document->document_value));
                            }
                            $employee_document->document_value = $fileNameToStore;
                            $employee_document->save();
                        } else {
                            $employee_document                 = new EmployeeDocument();
                            $employee_document->employee_id    = $employee->id;
                            $employee_document->personel_id    = $employee->personel_id;
                            $employee_document->document_id    = $key;
                            $employee_document->document_value = $fileNameToStore;
                            $employee_document->save();
                        }
                    }
                }
            }

            $input    = $request->all();
            // return $input;
            $employee->fill($input)->save();
            $user->fill($request->except('type'))->save();
            if ($request->salary) {
                return redirect()->route('setsalary.index')->with('success', 'Employee successfully updated.');
            }

            if (\Auth::user()->type != 'employee') {
                return redirect()->route('employee.index')->with('success', 'Employee successfully updated.');
            } else {
                return redirect()->route('employee.show', Crypt::encrypt($employee->id))->with('success', 'Employee successfully updated.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {

        if (\Auth::user()->can('Delete Employee')) {
            $employee      = Employee::findOrFail($id);
            $user          = User::where('id', '=', $employee->user_id)->first();
            $emp_documents = EmployeeDocument::where('employee_id', $employee->employee_id)->get();
            $employee->delete();
            $user->delete();
            $dir = storage_path('app/public/uploads/document/');
            foreach ($emp_documents as $emp_document) {
                $emp_document->delete();
                File::delete(storage_path('app/public/uploads/document/' . $emp_document->document_value));
                if (!empty($emp_document->document_value)) {
                    // unlink($dir . $emp_document->document_value);
                }
            }

            return redirect()->route('employee.index')->with('success', 'Employee successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        if (\Auth::user()->can('Show Employee')) {
            $empId         = Crypt::decrypt($id);
            $documents     = Document::get();
            $branches      = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $departments   = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Department::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $department_id = $departments->pluck('id')->toArray();
            $designations  = !empty(\Auth::user()->branch_id) ? Designation::whereIn('department_id', $department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Designation::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $employee      = Employee::find($empId);
            $employeesId   = $employee->employee_id;

            return view('employee.show', compact('employee', 'employeesId', 'branches', 'departments', 'designations', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function json(Request $request)
    {
        // $department_id = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('id')->toArray() : Department::orderBy('name', 'ASC')->get()->pluck('id')->toArray();
        // $designations  = !empty(\Auth::user()->branch_id) ? Designation::where('department_id', $department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray() : Designation::where('department_id', $department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray();
        // $designations = !empty(\Auth::user()->branch_id) ? Designation::where('department_id', $request->department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray() : Designation::where('department_id', $request->department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray();
        // $designations = Designation::where('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
        $designations = Designation::where('department_id', $request->department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray();


        return response()->json($designations);
    }


    public function departmentJson(Request $request)
    {
        $departments = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray() : Department::where('branch_id', $request->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray();
        // $designations  = !empty(\Auth::user()->branch_id) ? Designation::whereIn('department_id', $department_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray() : Designation::orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray();
        // $designations = Designation::where('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();

        return response()->json($departments);
    }

    function employeeNumber()
    {
        $latest = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->latest('id')->first() : Employee::latest('id')->first();
        if (!$latest) {
            return 1;
        }

        return $latest->employee_id + 1;
    }

    public function profile(Request $request)
    {
        if (\Auth::user()->can('Manage Employee Profile')) {
            $employees = Employee::where('created_by', \Auth::user()->creatorId());
            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
            }
            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
            }
            if (!empty($request->designation)) {
                $employees->where('designation_id', $request->designation);
            }
            $employees = $employees->orderby('name', 'asc')->get();

            $brances = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $brances->prepend('All', '');

            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', '');

            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', '');
            $emergency_contact_relations = [
                'Parent' => __('Parent'),
                'Children' => __('Children'),
                'Sibling' => __('Sibling'),
                'Spouse' => __('Spouse'),
                'Friend' => __('Friend'),
            ];
            $marital_statuses = [
                'Single' => __('Single'),
                'Married' => __('Married'),
                'Widowed' => __('Widowed'),
            ];

            return view('employee.profile', compact('employees', 'departments', 'designations', 'brances', 'emergency_contact_relations', 'marital_statuses'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function profileShow($id)
    {
        if (\Auth::user()->can('Show Employee Profile')) {
            try {
                $empId        = Crypt::decrypt($id);
            } catch (\RuntimeException $e) {
                return redirect()->back()->with('error', __('Employee not avaliable'));
            }
            $documents    = Document::where('created_by', \Auth::user()->creatorId())->get();
            $branches     = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments  = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employee     = Employee::find($empId);
            if ($employee == null) {
                $employee     = Employee::where('user_id', $empId)->first();
            }
            $employeesId  = $employee->employee_id;
            $emergency_contact_relations = [
                'Parent' => __('Parent'),
                'Sibling' => __('Sibling'),
                'Spouse' => __('Spouse'),
                'Friend' => __('Friend'),
            ];
            $marital_statuses = [
                'Single' => __('Single'),
                'Married' => __('Married'),
                'Widowed' => __('Widowed'),
            ];

            return view('employee.show', compact('employee', 'employeesId', 'branches', 'departments', 'designations', 'documents', 'emergency_contact_relations', 'marital_statuses'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function lastLogin()
    {
        $users = !empty(\Auth::user()->branch_id) ? User::where('branch_id', \Auth::user()->branch_id)->get() : User::get();

        return view('employee.lastLogin', compact('users'));
    }

    public function employeeJson(Request $request)
    {
        $employees = !empty(\Auth::user()->branch_id) ? Employee::where('is_active', 1)->where('branch_id', $request->branch_id)->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray() : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray();

        return response()->json($employees);
    }
    public function directSpvJson(Request $request)
    {
        $employees = Employee::where('is_active', 1)
            ->where('id', '!=', $request->employee_id)
            ->orderby('name', 'asc')->get();
        for ($i = 0; $i < count($employees); $i++) {
            $employees[$i]['name'] = $employees[$i]['name'] . ' | ' . $employees[$i]->branch->name;
        }

        $employees = $employees->pluck('name', 'id')->toArray();
        // Log::info($employees);
        return response()->json($employees);
    }
    public function importFile()
    {
        return view('employee.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt,xlsx',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $employees = (new EmployeesImport())->toArray(request()->file('file'))[0];
        $totalCustomer = count($employees) - 1;
        $errorArray    = [];
        // return $employees;

        for ($i = 1; $i <= count($employees) - 1; $i++) {

            $employee = $employees[$i];

            $duplicatedEmployee = Employee::orWhere('email', $employee[5])
                ->orWhere('name', $employee[0])
                ->orWhere('employee_id', $employee[7])
                ->orWhere('phone', $employee[3])
                ->first();
            $userByEmail = User::orWhere('email', $employee[5])
                ->orWhere('name', $employee[0])
                ->first();


            if (!empty($duplicatedEmployee) && !empty($userByEmail)) {
                $employeeData = $duplicatedEmployee;
            } else {

                $user = new User();
                $user->name = $employee[0];
                $user->email = $employee[5];
                $user->password = Hash::make($employee[6]);
                $user->type = 'employee';
                $user->lang = 'id';
                $user->created_by = \Auth::user()->id;
                $user->branch_id = $employee[8];
                $user->save();
                $user->assignRole('Employee');

                $employeeData = new Employee();
                $employeeData->employee_id         = $employee[7];
                $employeeData->user_id             = $user->id;
                $employeeData->name                = $employee[0];
                $employeeData->dob                 = $employee[1];
                $employeeData->gender              = $employee[2];
                $employeeData->phone               = $employee[3];
                $employeeData->address             = $employee[4];
                $employeeData->email               = $employee[5];
                $employeeData->password            = Hash::make($employee[6]);
                $employeeData->employee_id         = $employee[7];
                $employeeData->branch_id           = $employee[8];
                $employeeData->company_doj         = $employee[9];
                $employeeData->nationality         = $employee[10];
                $employeeData->identity_type       = $employee[11];
                $employeeData->identity_number     = $employee[12];
                $employeeData->tax_payer_id        = $employee[13] ?? null;
                $employeeData->shift_type_id       = $employee[14];
                $employeeData->created_by          = \Auth::user()->id;
                $employeeData->save();
            }


            if (empty($employeeData)) {
                $errorArray[] = $employeeData;
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalCustomer . ' ' . 'record');


            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    public function export()
    {
        $name = 'employee_' . date('Y-m-d i:h:s');
        $data = Excel::download(new EmployeesExport(), $name . '.xlsx');

        return $data;
    }
    public function joiningletterPdf($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $joiningletter = JoiningLetter::where('lang', $currantLang)->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $obj = [
            'date' =>  \Auth::user()->dateFormat($date),
            'app_name' => env('APP_NAME'),
            'employee_name' => $employees->name,
            'address' => !empty($employees->address) ? $employees->address : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'start_date' => !empty($employees->company_doj) ? $employees->company_doj : '',
            'branch' => !empty($employees->Branch->name) ? $employees->Branch->name : '',
            'start_time' => !empty($settings['company_start_time']) ? $settings['company_start_time'] : '',
            'end_time' => !empty($settings['company_end_time']) ? $settings['company_end_time'] : '',
            'total_hours' => $result,
        ];

        $joiningletter->content = JoiningLetter::replaceVariable($joiningletter->content, $obj);
        return view('employee.template.joiningletterpdf', compact('joiningletter', 'employees'));
    }
    public function joiningletterDoc($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $joiningletter = JoiningLetter::where('lang', $currantLang)->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);



        $obj = [
            'date' =>  \Auth::user()->dateFormat($date),

            'app_name' => env('APP_NAME'),
            'employee_name' => $employees->name,
            'address' => !empty($employees->address) ? $employees->address : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'start_date' => !empty($employees->company_doj) ? $employees->company_doj : '',
            'branch' => !empty($employees->Branch->name) ? $employees->Branch->name : '',
            'start_time' => !empty($settings['company_start_time']) ? $settings['company_start_time'] : '',
            'end_time' => !empty($settings['company_end_time']) ? $settings['company_end_time'] : '',
            'total_hours' => $result,
            //

        ];
        // dd($obj);
        $joiningletter->content = JoiningLetter::replaceVariable($joiningletter->content, $obj);
        return view('employee.template.joiningletterdocx', compact('joiningletter', 'employees'));
    }

    public function ExpCertificatePdf($id)
    {
        $currantLang = \Cookie::get('LANGUAGE');
        if (!isset($currantLang)) {
            $currantLang = 'en';
        }
        $termination = Termination::where('employee_id', $id)->first();
        $experience_certificate = ExperienceCertificate::where('lang', $currantLang)->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        // dd($employees->salaryType->name);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $date1 = date_create($employees->company_doj);
        $date2 = date_create($employees->termination_date);
        $diff  = date_diff($date1, $date2);
        $duration = $diff->format("%a days");

        if (!empty($termination->termination_date)) {

            $obj = [
                'date' =>  \Auth::user()->dateFormat($date),
                'app_name' => env('APP_NAME'),
                'employee_name' => $employees->name,
                'payroll' => !empty($employees->salaryType->name) ? $employees->salaryType->name : '',
                'duration' => $duration,
                'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',

            ];
        } else {
            return redirect()->back()->with('error', __('Termination date is required.'));
        }


        $experience_certificate->content = ExperienceCertificate::replaceVariable($experience_certificate->content, $obj);
        return view('employee.template.ExpCertificatepdf', compact('experience_certificate', 'employees'));
    }
    public function ExpCertificateDoc($id)
    {
        $currantLang = \Cookie::get('LANGUAGE');
        if (!isset($currantLang)) {
            $currantLang = 'en';
        }
        $termination = Termination::where('employee_id', $id)->first();
        $experience_certificate = ExperienceCertificate::where('lang', $currantLang)->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $date1 = date_create($employees->company_doj);
        $date2 = date_create($employees->termination_date);
        $diff  = date_diff($date1, $date2);
        $duration = $diff->format("%a days");
        if (!empty($termination->termination_date)) {
            $obj = [
                'date' =>  \Auth::user()->dateFormat($date),
                'app_name' => env('APP_NAME'),
                'employee_name' => $employees->name,
                'payroll' => !empty($employees->salaryType->name) ? $employees->salaryType->name : '',
                'duration' => $duration,
                'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',

            ];
        } else {
            return redirect()->back()->with('error', __('Termination date is required.'));
        }

        $experience_certificate->content = ExperienceCertificate::replaceVariable($experience_certificate->content, $obj);
        return view('employee.template.ExpCertificatedocx', compact('experience_certificate', 'employees'));
    }
    public function NocPdf($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $noc_certificate = NOC::where('lang', $currantLang)->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);


        $obj = [
            'date' =>  \Auth::user()->dateFormat($date),
            'employee_name' => $employees->name,
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'app_name' => env('APP_NAME'),
        ];

        $noc_certificate->content = NOC::replaceVariable($noc_certificate->content, $obj);
        return view('employee.template.Nocpdf', compact('noc_certificate', 'employees'));
    }
    public function NocDoc($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $noc_certificate = NOC::where('lang', $currantLang)->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);


        $obj = [
            'date' =>  \Auth::user()->dateFormat($date),
            'employee_name' => $employees->name,
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'app_name' => env('APP_NAME'),
        ];

        $noc_certificate->content = NOC::replaceVariable($noc_certificate->content, $obj);
        return view('employee.template.Nocdocx', compact('noc_certificate', 'employees'));
    }
}
