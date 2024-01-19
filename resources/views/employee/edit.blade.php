@extends('layouts.admin')

@section('page-title')
  {{ __('Edit Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Employee') }}</li>
@endsection

@section('content')

    <div class="">
        <div class="">

            {{ Form::model($employee, ['route' => ['employee.update', $employee->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
            <div class="row">
                <div class="col-md-6 ">
                    <div class="card" >
                        <div class="card-header">
                            <h5>{{ __('Personal Detail') }}</h5>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::date('dob', null, ['class' => 'form-control ', 'id' => 'data_picker1']) !!}
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <div class="form-group ">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input"
                                                    {{ $employee->gender == 'Male' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input"
                                                    {{ $employee->gender == 'Female' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="form-group col-md-12">
                                    {!! Form::label('nationality', __('Nationality'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('nationality', $nationalities, $employee->nationality, ['class' => 'form-control select2', 'id' => 'nationality', 'required' => 'required','placeholder' =>  __('Select Nationality')]) !!}
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('identity_type', __('Identity Type'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::select('identity_type', $identity_types, $employee->identity_type, ['class' => 'form-control select2', 'id' => 'identity_type', 'required' => 'required','placeholder' =>  __('Select Identity Type')]) !!}
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('identity_number', __('Identity Number'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    <input class="form-control" name="identity_number"
                                        type="text" id="identity_number" placeholder="{{ __('Enter Identity Number') }}"
                                        value="{{ $employee->identity_number }}" required autocomplete="identity_number">
                                </div>
                            </div>
                            <div class="form-group">
                                {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::textarea('address', null, ['class' => 'form-control', 'rows' => 2]) !!}
                            </div> --}}
                            <div class="form-group col-md-6">
                                {!! Form::label('emergency_contact_number', __('Emergency Contact Number'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::text('emergency_contact_number', old('emergency_contact_number'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>__('Enter Emergency Contact Number')]) !!}
                                {{-- {!! Form::text('emergency_contact_number', old('emergency_contact_number'), null, ['class' => 'form-control', 'id' => 'emergency_contact_number', 'required' => 'required','placeholder' =>  __('Enter Emergency Contact Number')]) !!} --}}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('emergency_contact_relation', __('Emergency Contact Relation'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::select('emergency_contact_relation', $emergency_contact_relations, old('emergency_contact_relation'), ['class' => 'form-control', 'id' => 'emergency_contact_relation', 'required' => 'required','placeholder' =>  __('Select Emergency Contact Relation')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('marital_status', __('Marital Status'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::select('marital_status', $marital_status, null, ['class' => 'form-control', 'id' => 'marital_status', 'required' => 'required','placeholder' =>  __('Select Marital Status')]) !!}
                            </div>
                            
                            
                            <div class="form-group col-md-6">
                                {!! Form::label('nationality', __('Nationality'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::select('nationality', $nationalities, old('nationality'), ['class' => 'form-control', 'id' => 'nationality', 'required' => 'required','placeholder' =>  __('Select Nationality')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('identity_type', __('Identity Type'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::select('identity_type', $identity_types, old('identity_type'), ['class' => 'form-control', 'id' => 'identity_type', 'required' => 'required','placeholder' =>  __('Select Identity Type')]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('identity_number', __('Identity Number'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::text('identity_number', old('identity_number'), ['class' => 'form-control' ,'required' => 'required','placeholder'=>__('Enter Identity Number')]) !!}
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
                            @if (\Auth::user()->type == 'employee')
                                {!! Form::submit('Update', ['class' => 'btn-create btn-xs badge-blue radius-10px float-right']) !!}
                            @endif
                        </div>
                    </div>
                </div>
                @if (\Auth::user()->type != 'employee')
                    <div class="col-md-6 ">
                        <div class="card " >
                            <div class="card-header">
                                <h5>{{ __('Company Detail') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @csrf
                                    <div class="form-group col-md-6">
                                        {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('employee_id', $employeesId, ['class' => 'form-control',  'required' => 'required','disabled' => (\Auth::user()->type == 'employee') ? 'disabled' : null ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('personel_id', "ID Personel (Access Door)", ['class' => 'form-label']) !!}
                                        {!! Form::text('personel_id', $employee->personel_id, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('type', __('Employee Type'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::select('type', $employeeTypes, $employee->type_id, ['class' => 'form-control select2', 'id' => 'type', 'required' => 'required','placeholder' =>  __('Select Employee Type')]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('company_doj', 'Company Date Of Joining', ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::date('company_doj', null, ['class' => 'form-control ', 'id' => 'data_picker2', 'required' => 'required']) !!}
                                    </div>
                                    <div class="form-group col-md-12">
                                        {!! Form::label('shift_type_id', __('Select Shift*'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {{ Form::select('shift_type_id', $shift_types, null, ['class' => 'form-control select2', 'id' => 'shift_type_id', 'required' => 'required' ,'placeholder' =>  __('Select Shift*')]) }}
                                    </div>
                                    
                                    <div class="form-group col-md-12">
                                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select2', 'required' => 'required','style'=>'font-weight:bold;', 'placeholder' => 'Select Branch']) }}
                                    </div>
                                    <div class="form-group col-md-12">
                                        {{ Form::label('department_id', __('Select Department'), ['class' => 'form-label']) }}
    
                                        <div class="form-icon-user">
                                            <div class="department_div">
                                                <select class="form-control select2  department_id" name="department_id"
                                                     placeholder="{{ __('Select Department') }}">
                                                     
                                                    @if ($employee->department_id)
                                                        <option value="{{ $employee->department_id }}">{{ $employee->department->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        {{ Form::label('designation_id', __('Select Designation'), ['class' => 'form-label']) }}

                                        <div class="form-icon-user">
                                            <div class="designation_div">
                                                <select class="form-control select2  designation_id" name="designation_id"
                                                    placeholder="Select Designation">
                                                    @if ($employee->designation_id)
                                                        <option value="{{ $employee->designation_id }}">{{ $employee->designation->name }}</option>
                                                    @endif
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
                                                     @if ($employee->managed_by)
                                                        <option value="{{ $employee->managed_by }}">{{ $employee->direct_spv->name }}</option>
                                                     @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap ">
                            <div class="card " >
                                <div class="card-header">
                                    <h5>{{ __('Company Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Branch') }}</strong>
                                                <span>{{ !empty($employee->branch) ? $employee->branch->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Department') }}</strong>
                                                <span>{{ !empty($employee->department) ? $employee->department->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Designation') }}</strong>
                                                <span>{{ !empty($employee->designation) ? $employee->designation->name : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Date Of Joining') }}</strong>
                                                <span>{{ \Auth::user()->dateFormat($employee->company_doj) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @if (\Auth::user()->type != 'employee')
                <div class="row">
                    <div class="col-md-6 ">
                        <div class="card " >
                            <div class="card-header" >
                                <h5>{{ __('Document') }}</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $employeedoc = $employee->documents()->pluck('document_value', __('document_id'));
                                @endphp

                                @foreach ($documents as $key => $document)
                                    <div class="row">
                                        <div class="form-group col-12 d-flex">
                                            <div class="float-left col-4">
                                                <label for="document" class=" form-label">{{ $document->name }}
                                                    @if ($document->is_required == 1)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                            </div>
                                            <div class="float-right col-8">
                                                <input type="hidden" name="emp_doc_id[{{ $document->id }}]" id=""
                                                    value="{{ $document->id }}">

                                                @php
                                                $employeedoc = !empty($employee->documents)?$employee->documents()->pluck('document_value',__('document_id')):[];
                                                $logo=\App\Models\Utility::get_file('uploads/document');

                                                @endphp
                                                <div class="choose-files ">
                                                    <label for="document[{{ $document->id }}]">
                                                        <div class=" bg-primary document "> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file"
                                                            class="form-control file  d-none @error('document') is-invalid @enderror"
                                                            @if ($document->is_required == 1)  @endif
                                                            name="document[{{ $document->id }}]" id="document[{{ $document->id }}]"
                                                            data-filename="{{ $document->id . '_filename' }}" onchange="document.getElementById('{{'blah'.$key}}').src = window.URL.createObjectURL(this.files[0])">
                                                    </label>
                                                    {{-- <a href="#"><p class="{{ $document->id . '_filename' }} "></p></a> --}}
                                                    <img id="{{'blah'.$key}}" src="{{ (isset($employeedoc[$document->id]) && !empty($employeedoc[$document->id])?$logo.'/'.$employeedoc[$document->id]:'') }}"  width="50%" />

                                                </div>

                                                @if (!empty($employeedoc[$document->id]))
                                                     <span class="text-xs"><a
                                                            href="{{ !empty($employeedoc[$document->id]) ? $logo . '/' . $employeedoc[$document->id] : '' }}"
                                                            target="_blank">
                                                            {{-- {{ !empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '' }} --}}
                                                        </a>
                                                    </span>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card " >
                            <div class="card-header">
                                <h5>{{ __('Bank Account Detail') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('bank_id', __('Bank Name'), ['class' => 'form-label']) !!}
                                        {!! Form::select('bank_id', $banks, $employee->bank_id, ['class' => 'form-control select2','placeholder' =>  __('Select Bank Name')]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                                        {!! Form::number('account_number', $employee->account_number, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                        {!! Form::text('account_holder_name', $employee->account_holder_name, ['class' => 'form-control']) !!}

                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                                        {!! Form::text('tax_payer_id', $employee->tax_payer_id, ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap">
                            <div class="card " >
                                <div class="card-header">
                                    <h5>{{ __('Document Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php
                                            $employeedoc = $employee->documents()->pluck('document_value', __('document_id'));
                                        @endphp
                                        @foreach ($documents as $key => $document)
                                            <div class="col-md-12">
                                                <div class="info">
                                                    <strong>{{ $document->name }}</strong>
                                                    <span><a href="{{ !empty($employeedoc[$document->id]) ? asset(Storage::url('uploads/document')) . '/' . $employeedoc[$document->id] : '' }}"
                                                            target="_blank">{{ !empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '' }}</a></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div class="employee-detail-wrap">
                            <div class="card " >
                                <div class="card-header">
                                    <h5>{{ __('Bank Account Detail') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Account Holder Name') }}</strong>
                                                <span>{{ $employee->account_holder_name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Account Number') }}</strong>
                                                <span>{{ $employee->account_number }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info font-style">
                                                <strong>{{ __('Bank Name') }}</strong>
                                                <span>{{ $employee->bank->name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Bank Identifier Code') }}</strong>
                                                <span>{{ $employee->bank->code }}</span>
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Branch Location') }}</strong>
                                                <span>{{ $employee->branch_location }}</span>
                                            </div>
                                        </div> --}}
                                        <div class="col-md-6">
                                            <div class="info">
                                                <strong>{{ __('Tax Payer Id') }}</strong>
                                                <span>{{ $employee->tax_payer_id }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (\Auth::user()->type != 'employee')
                <div class="float-end">
                    <button type="submit" class="btn  btn-primary">{{ 'Update' }}</button>
                </div>
            @endif
            <div class="col-12">
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@endsection

@push('script-page')

<script>
    $(document).on('change', 'select[name=department_id]', function() {
        var department_id = $(this).val();
        // console.log({department_id});
        getDesignation(department_id);
    });
    
    $(document).on('change', 'select[name=branch_id]', function() {
        var branch_id = $(this).val();
        // console.log({branch_id});
        getDepartment(branch_id);
        getEmployeeBranch(branch_id);
    });
    
    function getDepartment(branch_id) {
        $('.designation_id').empty();
        $.ajax({
            url: '{{ route('department.employee.json') }}',
            type: 'POST',
            data: {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                console.log(data);
                $('.department_id').empty();
                var emp_selct = ` <select class="form-control select2  department_id" name="department_id" id="choices-multiple"
                                        placeholder="Select Department" >
                                        </select>`;
                $('.department_div').html(emp_selct);

                $('.department_id').append('<option value="" disabled selected>{{ __('Select Designation') }}</option>');
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
        console.log({branch_id})
        $.ajax({
            url: '{{ route('branch.employee.json') }}',
            type: 'POST',
            data: {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                console.log(data);
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
@endpush
