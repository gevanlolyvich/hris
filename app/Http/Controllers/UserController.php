<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Invoice;
use App\Mail\UserCreate;
use App\Models\Notification;
use App\Models\Bank;
use App\Models\User;
use App\Models\Utility;
use App\Models\Branch;
use App\Models\Document;
use App\Models\Training;
use App\Models\EmployeeDocument;
use App\Models\EmployeeHomeHistory;
use App\Models\EmployeeCertificate;
use App\Models\EmployeeCv;
use App\Models\EmployeeCvExperience;
use App\Models\EmployeeCvEducation;
use App\Models\EmployeeCvSkill;
use App\Models\EmployeeCvLanguage;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage User')) {
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

            $users = $branch_id?->isNotEmpty() ? User::whereIn('branch_id', $branch_id)->withAggregate('employee', 'name')->orderBy('employee_name', 'asc')->get() : User::withAggregate('employee', 'name')->orderBy('employee_name', 'asc')->get();

            return view('user.index', compact('users'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create User')) {
            $user  = \Auth::user();
            $roles = Role::where('created_by', '=', $user->creatorId())->where('name', '!=', 'employee')->get()->pluck('name', 'id');

            $branches = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('user.create', compact('roles', 'branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create User')) {
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            $validator        = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|unique:users',
                    'password' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $role_r = Role::findById($request->role);
            $date = date("Y-m-d H:i:s");

            $user   = User::create(
                [
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'type' => $role_r->name,
                    'lang' => !empty($default_language) ? $default_language->value : '',
                    'created_by' => \Auth::user()->id,
                    'email_verified_at' => $date,
                    'branch_id' => $request->branch_id,
                ]
            );
            $user->assignRole($role_r);
            $user->userDefaultData();

            $setings = Utility::settings();
            if ($setings['new_user'] == 1) {

                $uArr = [
                    'email' => $user->email,
                    'password' => $request->password,
                ];

                $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr);
                return redirect()->route('user.index')->with('success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            return redirect()->route('user.index')->with('success', __('User successfully created.'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function show(User $user)
    {
        return view('profile.index');
    }

    public function edit($id)
    {
        if (\Auth::user()->can('Edit User')) {
            $user  = User::find($id);
            $roles = Role::where('created_by', '=', $user->creatorId())->get()->pluck('name', 'id');
            
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

            $branches = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('user.edit', compact('user', 'roles', 'branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'unique:users,email,' . $id,
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if (\Auth::user()->can('Edit User')) {
            $user = User::findOrFail($id);

            $role          = Role::findById($request->role);
            $input         = $request->all();
            $input['type'] = $role->name;
            $user->fill($input)->save();

            $user->assignRole($role);

            return redirect()->route('user.index')->with('success', 'User successfully updated.');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('Delete User')) {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('user.index')->with('success', 'User successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function userPassword($id)
    {
        $eId      = \Crypt::decrypt($id);

        $user     = User::find($eId);

        $employee = User::where('id', $eId)->first();

        return view('user.reset', compact('user', 'employee'));
    }

    public function userPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'password' => 'required|confirmed|same:password_confirmation',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $eId                  = \Crypt::decrypt($id);
        $user                 = User::where('id', $eId)->first();
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return redirect()->route('user.index')->with(
            'success',
            'User Password successfully updated.'
        );
    }

    public function profile()
    {
        $userDetail = \Auth::user();
        // $employee   = Employee::where('user_id', $userDetail->id)->first();
        $nationalities = ['WNI' => __('WNI'), 'WNA' => __('WNA')];
        $identity_types = ['KTP' => __('KTP'), 'Passport' => __('Passport'), 'SIM' => __('SIM')];
        $emergency_contact_relations = [
            'Parent' => __('Parent'),
            'Children' => __('Children'),
            'Sibling' => __('Sibling'),
            'Spouse' => __('Spouse'),
            'Friend' => __('Friend'),
        ];
        $marital_status = [
            'Single' => __('Single'),
            'Married' => __('Married'),
            'Widowed' => __('Widowed'),
        ];
        $banks = Bank::orderBy('name')->get()->pluck('name', 'id');
        $documents        = Document::get();

        $certificates = collect();
        if (\Auth::user()->type == 'employee' && \Auth::user()->employee) {
            $certificates = \Auth::user()->employee->certificates()->orderBy('id', 'desc')->get();
        }

        return view('user.profile', compact('userDetail', 'nationalities', 'identity_types', 'banks', 'emergency_contact_relations', 'marital_status', 'documents', 'certificates'));
    }

    public function editprofile(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = User::findOrFail($userDetail['id']);

        $request['email'] = !$request['email'] ? $user['email'] : $request['email'];

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|max:120',
                'email' => 'required|email|unique:users,email,' . $userDetail['id'],
                'profile' => 'nullable|mimes:jpg,png,jpeg,JPG,PNG,JPEG|image|max:2048',
                'emergency_contact_photo' => 'nullable|mimes:jpg,png,jpeg,JPG,PNG,JPEG|image|max:2048',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->hasFile('profile')) {

            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;


            $dir        = '../storage/app/public/uploads/avatar/';

            $image_path = $dir . $userDetail['avatar'];
            if (File::exists($image_path)) {
                File::delete($image_path);
            }
            $url = '';
            $path = Utility::upload_file($request, 'profile', $fileNameToStore, $dir, []);

            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->route('profile', \Auth::user()->id)->with('error', __($path['msg']));
            }
        }

        if (!empty($request->profile)) {
            $user['avatar'] = $fileNameToStore;
        }
        $user['name']  = $request['name'];
        $user['email'] = $request['email'];
        $user->save();

        if (\Auth::user()->type == 'employee') {
            $coordinate     = "$request->latitude, $request->longitude, 50";

            $employee       = Employee::where('user_id', $user->id)->first();

            $document_path  = $employee->emergency_contact_photo;
            if ($request->file('emergency_contact_photo')) {
                $docs = $request->file('emergency_contact_photo');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/employees/'. preg_replace('/\s+/', '', $employee->name), $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
    
                // Check if the file exists before attempting to delete
                if ($employee->emergency_contact_photo) {
                    $filepath_array = explode('/', $employee->emergency_contact_photo);
                    $filename = array_pop($filepath_array);
    
                    if (Storage::disk('public')->exists("uploads/employees/" . preg_replace('/\s+/', '', $employee->name). '/' . $filename)) {
                        Storage::disk('public')->delete("uploads/employees/" . preg_replace('/\s+/', '', $employee->name). '/' . $filename);
                    }
                }
            }

            if ($employee->coordinate != $coordinate || $employee->address != $request->address) {
                EmployeeHomeHistory::create([
                    'employee_id' => $employee->id,
                    'coordinate' => $coordinate,
                    'address' => $request->address,
                ]);
            }
            $employee->name                         = $request->name;
            $employee->email                        = $request->email;
            $employee->coordinate                   = $coordinate;
            $employee->address                      = $request->address;
            $employee->dob                          = $request->birthdate;
            $employee->phone                        = $request->phone;
            $employee->marital_status               = $request->marital_status;
            $employee->domicile_address             = $request->domicile_address;
            $employee->emergency_contact_number     = $request->emergency_contact_number;
            $employee->emergency_contact_relation   = $request->emergency_contact_relation;
            $employee->emergency_contact_photo      = $document_path;
            $employee->save();
        }

        return redirect()->back()->with(
            'success',
            'Profile successfully updated.'
        );
    }

    public function updatePassword(Request $request)
    {
        if (\Auth::Check()) {
            $request->validate(
                [
                    'current_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            $objUser          = Auth::user();
            $request_data     = $request->All();
            $current_password = $objUser->password;
            if (Hash::check($request_data['current_password'], $current_password)) {
                $user_id            = Auth::User()->id;
                $obj_user           = User::find($user_id);
                $obj_user->password = Hash::make($request_data['new_password']);;
                $obj_user->save();

                return redirect()->route('profile', $objUser->id)->with('success', __('Password successfully updated.'));
            } else {
                return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
            }
        } else {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }

    public function updateBank(Request $request)
    {
        if (\Auth::Check()) {
            $request->validate(
                [
                    'bank_id'               => 'required',
                    'account_number'        => 'required',
                    'account_holder_name'   => 'required',
                    'tax_payer_id'          => 'required'
                ]
            );
            // return $request;
            $objUser          = Auth::user();

            $objEmployee = Employee::where('user_id', Auth::user()->id)->first();
            $objEmployee->bank_id = $request->bank_id;
            $objEmployee->account_number = $request->account_number;
            $objEmployee->account_holder_name = $request->account_holder_name;
            $objEmployee->tax_payer_id = $request->tax_payer_id;
            $objEmployee->save();

            // return $objEmployee;
            return redirect()->route('profile', $objUser->id)->with('success', __('Bank successfully updated.'));
        } else {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }

    public function updateNationality(Request $request)
    {
        if (\Auth::Check()) {
            $request->validate(
                [
                    'nationality'       => 'required',
                    'identity_type'     => 'required',
                    'identity_number'   => 'required',
                ]
            );
            // return $request;
            $objUser          = Auth::user();

            $objEmployee = Employee::where('user_id', Auth::user()->id)->first();
            $objEmployee->nationality = $request->nationality;
            $objEmployee->identity_type = $request->identity_type;
            $objEmployee->identity_number = $request->identity_number;
            $objEmployee->save();

            // return $objEmployee;
            return redirect()->route('profile', $objUser->id)->with('success', __('Nationality successfully updated.'));
        } else {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }
    public function updateDocuments(Request $request)
    {
        $employee          = Auth::user()->employee;
        // return $employee['employee_id'];
        // return $request->file('document')[1]->getClientOriginalName();
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

                $form = [
                    'employee_id' => $employee['id'],
                    'document_id' => $key,
                    'document_value' => $fileNameToStore,
                    'created_by' => \Auth::user()->creatorId(),
                ];
                $employee_document = EmployeeDocument::create($form);
                $employee_document->save();
            }
        }
        // return $employee;
        return redirect()->route('profile', Auth::user()->id)->with('success', __('Document successfully updated.'));
    }

    public function storeCertificate(Request $request)
    {
        if (!\Auth::Check()) {
            return redirect()->back()->with('error', __('Something is wrong.'));
        }

        $employee = \Auth::user()->employee;
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'issuer' => 'nullable|string|max:255',
                'issue_date' => 'nullable|date',
                'expiry_date' => 'nullable|date|after_or_equal:issue_date',
                'description' => 'nullable|string|max:1000',
                'file' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip,docx,xls,xlsx,ppt,pptx|max:10480',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $emp_name = preg_replace('/\s+/', '', $employee->name);
        $file     = $request->file('file');
        $filename = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_certificate' . "." . $file->getClientOriginalExtension();
        $filepath = $file->storeAs("uploads/certificates/{$emp_name}", $filename, 'public');

        $certificate = EmployeeCertificate::create(
            [
                'employee_id' => $employee->id,
                'name' => $request->name,
                'issuer' => $request->issuer,
                'issue_date' => $request->issue_date,
                'expiry_date' => $request->expiry_date,
                'description' => $request->description,
                'file' => env('APP_URL') . '/storage/' . $filepath,
                'created_by' => \Auth::user()->id,
            ]
        );

        return redirect()->route('profile', \Auth::user()->id)->with('success', __('Certificate successfully uploaded.'));
    }

    public function destroyCertificate($id)
    {
        if (!\Auth::Check()) {
            return redirect()->back()->with('error', __('Something is wrong.'));
        }

        $certificate = EmployeeCertificate::find($id);
        if (!$certificate) {
            return redirect()->back()->with('error', __('Certificate not found.'));
        }

        $employee = \Auth::user()->employee;
        if (!$employee || $certificate->employee_id != $employee->id) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $filepath_array = explode('/', $certificate->file);
        $filename       = array_pop($filepath_array);
        if (!empty($filename) && Storage::disk('public')->exists('uploads/certificates/' . preg_replace('/\s+/', '', $employee->name) . '/' . $filename)) {
            Storage::disk('public')->delete('uploads/certificates/' . preg_replace('/\s+/', '', $employee->name) . '/' . $filename);
        }

        $certificate->delete();

        return redirect()->route('profile', \Auth::user()->id)->with('success', __('Certificate successfully deleted.'));
    }

    public function updateCv(Request $request)
    {
        if (!\Auth::Check()) {
            return redirect()->back()->with('error', __('Something is wrong.'));
        }

        $employee = \Auth::user()->employee;
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        EmployeeCv::updateOrCreate(
            ['employee_id' => $employee->id],
            ['summary' => $request->summary]
        );

        $experiences = [];
        if ($request->has('experience_company')) {
            foreach ($request->experience_company as $index => $company) {
                if (empty($company) && empty($request->experience_position[$index] ?? null)) {
                    continue;
                }
                $experiences[] = [
                    'employee_id' => $employee->id,
                    'company' => $company,
                    'position' => $request->experience_position[$index] ?? null,
                    'start_date' => !empty($request->experience_start_date[$index]) ? date('Y-m-d', strtotime($request->experience_start_date[$index])) : null,
                    'end_date' => !empty($request->experience_end_date[$index]) ? date('Y-m-d', strtotime($request->experience_end_date[$index])) : null,
                    'description' => $request->experience_description[$index] ?? null,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        EmployeeCvExperience::where('employee_id', $employee->id)->delete();
        if (count($experiences)) {
            EmployeeCvExperience::insert($experiences);
        }

        $educations = [];
        if ($request->has('education_institution')) {
            foreach ($request->education_institution as $index => $institution) {
                if (empty($institution)) {
                    continue;
                }
                $educations[] = [
                    'employee_id' => $employee->id,
                    'institution' => $institution,
                    'degree' => $request->education_degree[$index] ?? null,
                    'field_of_study' => $request->education_field_of_study[$index] ?? null,
                    'start_year' => !empty($request->education_start_year[$index]) ? $request->education_start_year[$index] : null,
                    'end_year' => !empty($request->education_end_year[$index]) ? $request->education_end_year[$index] : null,
                    'gpa' => $request->education_gpa[$index] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        EmployeeCvEducation::where('employee_id', $employee->id)->delete();
        if (count($educations)) {
            EmployeeCvEducation::insert($educations);
        }

        $skills = [];
        if ($request->has('skill')) {
            foreach ($request->skill as $index => $skill) {
                if (empty($skill)) {
                    continue;
                }
                $skills[] = [
                    'employee_id' => $employee->id,
                    'skill' => $skill,
                    'proficiency' => $request->skill_proficiency[$index] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        EmployeeCvSkill::where('employee_id', $employee->id)->delete();
        if (count($skills)) {
            EmployeeCvSkill::insert($skills);
        }

        $languages = [];
        if ($request->has('language')) {
            foreach ($request->language as $index => $language) {
                if (empty($language)) {
                    continue;
                }
                $languages[] = [
                    'employee_id' => $employee->id,
                    'language' => $language,
                    'proficiency' => $request->language_proficiency[$index] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        EmployeeCvLanguage::where('employee_id', $employee->id)->delete();
        if (count($languages)) {
            EmployeeCvLanguage::insert($languages);
        }

        return redirect()->route('profile', \Auth::user()->id)->with('success', __('CV successfully updated.'));
    }

    public function cv()
    {
        if (!\Auth::Check()) {
            return redirect()->route('login')->with('error', __('Please Login.'));
        }

        $employee = \Auth::user()->employee;
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        $profile = \App\Models\Utility::get_file('uploads/avatar/');

        return view('cv.show', compact('employee', 'profile'));
    }

    public function notificationSeen($user_id)
    {
        \Notification::where('user_id', '=', $user_id)->update(['is_read' => 1]);

        return response()->json(['is_success' => true], 200);
    }
}
