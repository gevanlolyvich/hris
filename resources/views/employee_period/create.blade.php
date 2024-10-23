
{{ Form::open(['url' => 'employee-applications', 'method' => 'post']) }}
<div class="modal-body">

    <div class="row">
        
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('request_type', __('Employee Application Type'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <a class="btn btn-outline-primary"
                        href="{{ route('employee.create') }}">
                        {{ __("New Employee") }}
                    </a>
                    <a class="btn btn-outline-primary"
                        href="#" id="renewalButton">
                        {{ __("Renewal Employee") }}
                    </a>
                </div>
            </div>
        </div>
        
        <div id="renewal_form" style="display: none">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Name'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2 ','placeholder' => __('Select Employee')]) }}
                    </div>
                </div>
            </div>
            
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::text('reason', null, ['class' => 'form-control', 'placeholder' => __('Enter Reason')]) }}
                    </div>
                    @error('reason')
                        <span class="invalid-reason" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('period_type', __('Type'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::select('period_type', $periodical_types, null , ['class' => 'form-control select2 ','placeholder' => __('Select Period Type')]) }}
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="row">
                    <div class="form-group col-md-6"><span class="text-danger pl-1">*</span>
                        {!! Form::label('start_period', __('Start Period'), ['class' => 'form-label']) !!}
                        {{ Form::date('start_period', null, ['class' => 'form-control ', 'autocomplete' => 'off','placeholder'=>'Select Start Period']) }}
                    </div>
                    <div class="form-group col-md-6"><span class="text-danger pl-1">*</span>
                        {!! Form::label('end_period', __('End Period'), ['class' => 'form-label']) !!}
                        {{ Form::date('end_period', null, ['class' => 'form-control ', 'autocomplete' => 'off','placeholder'=>'Select End Period']) }}
                    </div>
                </div>
            </div>
        </div>
        
        {{-- <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name')]) }}
                </div>
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div> --}}
        {{-- <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('type', __('Salary Type'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('type', $types, null, ['class' => 'form-control select2 ','placeholder' => __('Select Salary Type')]) }}
                </div>
            </div>
        </div> --}}
        
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
