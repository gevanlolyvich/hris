@extends('layouts.admin')

@section('page-title')
   {{ __('Create Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Employee') }}</li>
@endsection


@section('content')
    <div class="">
        <div class="">
            <div class="row">

            </div>
            {{ Form::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
            <div class="row">
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Personal Detail') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('name', old('name'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter employee name']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('phone', old('phone'), ['class' => 'form-control' ,'placeholder'=>'Enter employee Phone']) !!}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {{ Form::date('dob', old('dob'), ['class' => 'form-control ', 'required' => 'required', 'autocomplete' => 'off' ,'placeholder'=>'Select Date of Birth']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input">
                                                <label class="form-check-label " for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input">
                                                <label class="form-check-label "
                                                    for="g_female">{{ __('Female') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::email('email', old('email'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter employee Email']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::password('password', ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>'Enter Password']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('emergency_contact_number', __('Emergency Contact Number'), ['class' => 'form-label']) !!}
                                    {!! Form::text('emergency_contact_number', old('emergency_contact_number'), ['class' => 'form-control' ,'placeholder'=>__('Enter Emergency Contact Number')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('emergency_contact_relation', __('Emergency Contact Relation'), ['class' => 'form-label']) !!}
                                    {!! Form::select('emergency_contact_relation', $emergency_contact_relations, old('emergency_contact_relation'), ['class' => 'form-control', 'id' => 'emergency_contact_relation','placeholder' =>  __('Select Emergency Contact Relation')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('marital_status', __('Marital Status'), ['class' => 'form-label']) !!}
                                    {!! Form::select('marital_status', $marital_statuses, old('marital_status'), ['class' => 'form-control', 'id' => 'marital_status','placeholder' =>  __('Select Marital Status')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('dependents', __('Dependents'), ['class' => 'form-label']) !!}
                                    {!! Form::number('dependents', old('dependents'), ['class' => 'form-control', 'id' => 'dependents','placeholder' =>  __('Enter Total Dependents'), 'step' => '1', 'min' => 0]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('nationality', __('Nationality'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('nationality', $nationalities, old('nationality'), ['class' => 'form-control', 'id' => 'nationality', 'required' => 'required','placeholder' =>  __('Select Nationality')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('identity_type', __('Identity Type'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('identity_type', $identity_types, old('identity_type'), ['class' => 'form-control', 'id' => 'identity_type', 'required' => 'required','placeholder' =>  __('Select Identity Type')]) !!}
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('identity_number', __('Identity Number'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('identity_number', old('identity_number'), ['class' => 'form-control', 'required' => 'required','placeholder'=>__('Enter Identity Number')]) !!}
                                </div>
                            </div>
                            <div class="form-group">
                                {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::textarea('address', old('address'), ['class' => 'form-control', 'rows' => 2 ,'placeholder'=>__('Enter Employee Address')]) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('domicile_address', __('Domicile Address'), ['class' => 'form-label']) !!}
                                {!! Form::textarea('domicile_address', old('domicile_address'), ['class' => 'form-control', 'rows' => 2 ,'placeholder'=>__('Enter Domicile Address')]) !!}
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('emergency_contact_photo', __('Emergency Contact Photo'), ['class' => 'form-label']) !!}
                                <div class="row">
                                    <div class="col-6">
                                        <div class="btn btn-block btn-primary bg-primary document"> <i
                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                        </div>
                                        <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="emergency_contact_photo">
                                    </div>
                                    <div class="col-6">
                                        <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="uploadFile"><i
                                            class="fa fa-regular fa-file"></i><p id="fileName"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card em-card">
                        <div class="card-header">
                            <h5>{{ __('Company Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                @csrf
                                <div class="form-group col-md-6">
                                    {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('employee_id', old('employee_id'), ['class' => 'form-control', 'required' => 'required', 'placeholder'=>"Enter Employee ID"]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('personel_id', "ID Personel (Access Door)", ['class' => 'form-label']) !!}
                                    {!! Form::text('personel_id', old('personel_id'), ['class' => 'form-control','placeholder'=>'Enter ID Personel']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('type', __('Employee Type'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('type', $employeeTypes, old('type'), ['class' => 'form-control select2 type-select', 'id' => 'type', 'required' => 'required','placeholder' =>  __('Select Employee Type')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('company_doj', __('Company Date Of Joining'), ['class' => 'form-label']) !!}
                                    {{ Form::date('company_doj', old('company_doj'), ['class' => 'form-control ', 'autocomplete' => 'off','placeholder'=>'Select Company Date Of Joining']) }}
                                </div>
                                <div id="period_field" style="display:none">
                                    <div class="form-group">
                                        {!! Form::label('reason', __('Reason'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::textarea('reason', old('reason'), ['class' => 'form-control', 'rows' => 2 ,'placeholder'=>__('Enter Reason')]) !!}
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6"><span class="text-danger pl-1">*</span>
                                            {!! Form::label('start_period', __('Start Period'), ['class' => 'form-label']) !!}
                                            {{ Form::date('start_period', old('start_period'), ['class' => 'form-control ', 'autocomplete' => 'off','placeholder'=>'Select Start Period']) }}
                                        </div>
                                        <div class="form-group col-md-6"><span class="text-danger pl-1">*</span>
                                            {!! Form::label('end_period', __('End Period'), ['class' => 'form-label']) !!}
                                            {{ Form::date('end_period', old('end_period'), ['class' => 'form-control ', 'autocomplete' => 'off','placeholder'=>'Select Start Period']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    {{ Form::label('branch_id', __('Select Branch'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    <div class="form-icon-user">
                                        {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Branch')]) }}
                                    </div>
                                </div>
                                
                                <div class="form-group col-md-12">
                                    {{ Form::label('department_id', __('Select Department'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>

                                    <div class="form-icon-user">
                                        <div class="department_div">
                                            <select class="form-control select2  department_id" name="department_id"
                                                 placeholder="{{ __('Select Department') }}">
                                                 <option value="" disabled selected>{{ __('Select Department') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-12">
                                    {{ Form::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>

                                    <div class="form-icon-user">
                                        <div class="designation_div">
                                            <select class="form-control select2  designation_id" name="designation_id"
                                                 placeholder="Select Designation">
                                                 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('managed_by', __('Select Direct Supervisor'), ['class' => 'form-label']) !!}
                                    {{-- {{ Form::select('managed_by', $employees, null, ['class' => 'form-control select2', 'id' => 'managed_by','placeholder' =>  __('Select Direct Supervisor')]) }} --}}
                                    {{-- {{ Form::select('company_doj', null, ['class' => 'form-control ', 'required' => 'required', 'autocomplete' => 'off','placeholder'=>'Select Company Date Of Joining']) }} --}}
                                    <div class="form-icon-user">
                                        <div class="managed_by_div">
                                            <select class="form-control select2  managed_by" name="managed_by"
                                                 placeholder="{{ __('Select Direct Supervisor') }}">
                                                 <option value="" disabled selected>{{ __('Select Direct Supervisor') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="form-group col-md-12">
                                    {!! Form::label('shift_type_id', __('Select Shift*'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {{ Form::select('shift_type_id', $shift_types, null, ['class' => 'form-control select2', 'id' => 'shift_type_id', 'required' => 'required' ,'placeholder' =>  __('Select Shift*')]) }}
                                </div> --}}
                                
                                <div class="form-group col-md-12">
                                    {{ Form::label('shift_type_id', __('Select Shift'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>

                                    <div class="form-icon-user">
                                        <div class="shift_type_id_div">
                                            <select class="form-control select2  shift_type_id" name="shift_type_id"
                                                 placeholder="Select Shift">
                                                 <option value="" disabled selected>{{ __('Select Shift') }}</option>
                                                 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 ">
                    <div class="card em-card" >
                        <div class="card-header">
                            <h5>{{ __('Document') }}</h6>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            @foreach ($documents as $key => $document)
                                <div class="row">
                                    <div class="form-group col-12 d-flex">
                                        <div class="float-left col-4">
                                            <label for="document"
                                                class="float-left pt-1 form-label">{{ $document->name }} @if ($document->is_required == 1)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                        </div>
                                        <div class="float-right col-8">
                                            <input type="hidden" name="emp_doc_id[{{ $document->id }}]" id=""
                                                value="{{ $document->id }}">

                                            <div class="choose-files ">
                                                <label for="document[{{ $document->id }}]">
                                                    <div class=" bg-primary document "> <i
                                                            class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                    </div>
                                                    <input type="file"
                                                        class="form-control file   @error('document') is-invalid @enderror "
                                                        @if ($document->is_required == 1) required @endif
                                                        name="document[{{ $document->id }}]" id="document[{{ $document->id }}]"
                                                        data-filename="{{ $document->id . '_filename' }}" onchange="document.getElementById('{{'blah'.$key}}').src = window.URL.createObjectURL(this.files[0])">
                                                </label>
                                                {{-- <a href="#"><p class="{{ $document->id . '_filename' }} "></p></a> --}}
                                                <img id="{{'blah'.$key}}" src=""  width="75%" />

                                            </div>

                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="card em-card ">
                        <div class="card-header">
                            <h5>{{ __('Bank Account Detail') }}</h5>
                        </div>
                        <div class="card-body employee-detail-create-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('bank_id', __('Bank Name'), ['class' => 'form-label']) !!}
                                    {!! Form::select('bank_id', $banks, old('bank_id'), ['class' => 'form-control select2','placeholder' =>  __('Select Bank Name')]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                    {!! Form::number('account_number', old('account_number'), ['class' => 'form-control']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                    {!! Form::text('account_holder_name', old('account_holder_name'), ['class' => 'form-control']) !!}

                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                                    {!! Form::text('tax_payer_id', old('tax_payer_id'), ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="float-end">
            <button type="submit"  class="btn  btn-primary">{{ 'Create' }}</button>
        </div>
        </form>
    </div>
@endsection

@push('script-page')
    <script>
        $('input[type="file"]').change(function(e) {
            var file = e.target.files[0].name;
            var file_name=$(this).attr('data-filename');
            $('.'+file_name).append(file);
        });
    </script>

<script>
    $(document).ready(() => {
        $(document).on('change', '[name="emergency_contact_photo"]', function () {
            const file = document.getElementById('uploadFile');
            file.style.display = '';
            file.style['max-width'] = '';
            document.getElementById('fileName').textContent = this.files[0].name;
        });
    })
</script>

    <script>
        $(document).ready(function() {
            var d_id = $('.department_id').val();
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });
        
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
            getEmployeeBranch(branch_id);
            getBranchShift(branch_id);
        });

        function getDepartment(branch_id) {
            $.ajax({
                url: '{{ route('department.employee.json') }}',
                type: 'POST',
                data: {
                    "branch_id": branch_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('.designation_id').empty();
                    $('.department_id').empty();
                    var emp_selct = ` <select class="form-control select2  department_id" name="department_id" id="choices-multiple"
                                            placeholder="Select Department" >
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value="" disabled selected>{{ __('Select Department') }}</option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });


                }
            });
        }

        function getEmployeeBranch(branch_id) {
            $.ajax({
                url: '{{ route('branch.employee.json') }}',
                type: 'POST',
                data: {
                    "branch_id": branch_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('.managed_by').empty();
                    var emp_selct = ` <select class="form-control select2  managed_by" name="managed_by" id="choices-multiple2"
                                            placeholder={{ __('Select Direct Supervisor') }} >
                                            </select>`;
                    $('.managed_by_div').html(emp_selct);

                    $('.managed_by').append('<option value="" disabled selected>{{ __('Select Direct Supervisor') }}</option>');
                    $.each(data, function(key, value) {
                        $('.managed_by').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple2', {
                        removeItemButton: true,
                    });


                }
            });
        }
        
        function getBranchShift(branch_id) {
            $.ajax({
                url: '{{ route('branch.shift.json') }}',
                type: 'POST',
                data: {
                    "branch_id": branch_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('.shift_type_id').empty();
                    var emp_selct = ` <select class="form-control select2  shift_type_id" name="shift_type_id" id="choices-multiple5"
                                            placeholder={{ __('Select Shift') }} >
                                            </select>`;
                    $('.shift_type_id_div').html(emp_selct);

                    $('.shift_type_id').append('<option value="" disabled selected>{{ __('Select Shift') }}</option>');
                    $.each(data, function(key, value) {
                        $('.shift_type_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple5', {
                        removeItemButton: true,
                    });


                }
            });
        }

        function getDesignation(did) {
            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('.designation_id').empty();
                    var emp_selct = ` <select class="form-control  designation_id" name="designation_id" id="choices-multiple3"
                                            placeholder="Select Designation" >
                                            </select>`;
                    $('.designation_div').html(emp_selct);

                    $('.designation_id').append('<option value="" disabled selected>{{ __('Select Designation') }}</option>');
                    $.each(data, function(key, value) {
                        $('.designation_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple3', {
                        removeItemButton: true,
                    });
                }
            });
        }
    </script>

    <script>

        function getEmployeeType(employee_type_id){
            $.ajax({
                url: '{{ route('employeetypes.json') }}',
                type: 'GET',
                data: {
                    "id": employee_type_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    if (data.period_type == "Fixed") {
                        $('#period_field').hide();
                    } else {
                        $('#period_field').show();
                    }
                }
            });
        }

        $('body').on('change', '.type-select', function () {
            let period_type = $(this).val();
            getEmployeeType(period_type)
        });
    </script>
@endpush
