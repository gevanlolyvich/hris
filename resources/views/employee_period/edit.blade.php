
{{ Form::model($employee_period, ['route' => ['employee-applications.update', $employee_period->id], 'method' => 'PUT']) }}
<div class="modal-body">

    <input type="hidden" name="id" value="{{ $employee_period->id }}">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <a class="btn btn-outline-primary"
                        href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee_period->employee->id)) }}">
                        {{  $employee_period->employee->name }}
                    </a>
                    <a class="btn btn-outline-primary"
                        href="{{ route('employeeattendancehistory.show', \Illuminate\Support\Facades\Crypt::encrypt($employee_period->employee->id)) }}">
                        {{ __("History") }}
                    </a>
                    <div class="action-btn bg-info ms-2">
                        <div class="action-btn bg-info ms-2">
                            <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee_period->employee->id)) }}"
                                class="mx-3 btn btn-sm  align-items-center"
                                data-bs-toggle="tooltip" title=""
                                data-bs-original-title="{{ __('Edit') }}">
                                <i class="ti ti-pencil text-white"></i>
                            </a>
                        </div>
                    </div>
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
                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('period_type', $employee_types, $employee_period->employee->type_id , ['class' => 'form-control select2 ','placeholder' => __('Select Period Type')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="row">
                <div class="form-group col-md-6"><span class="text-danger pl-1">*</span>
                    {!! Form::label('start_period', __('Start Period'), ['class' => 'form-label']) !!}
                    {{ Form::date('start_period', $employee_period->start_period, ['class' => 'form-control ', 'autocomplete' => 'off','placeholder'=>'Select Start Period']) }}
                </div>
                <div class="form-group col-md-6"><span class="text-danger pl-1">*</span>
                    {!! Form::label('end_period', __('End Period'), ['class' => 'form-label']) !!}
                    {{ Form::date('end_period', $employee_period->end_period, ['class' => 'form-control ', 'autocomplete' => 'off','placeholder'=>'Select End Period']) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
