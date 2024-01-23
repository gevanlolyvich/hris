@php
    $setting = App\Models\Utility::settings();
@endphp
{{ Form::open(['url' => ['attendancerequest'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    @if (\Auth::user()->type != 'employee')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                    {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'id' => 'employee_id', 'required' => 'required', 'placeholder' => __('Select Employee')]) }}
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
                {{ Form::date('date', null, ['class' => 'form-control', 'autocomplete' => 'off', 'required' => 'required', 'id' => 'date', 'placeholder' => __('Select Date')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('shift_id', __('Shift'), ['class' => 'col-form-label']) }}
                <div class="shift_div btn-box">
                    {{ Form::select('shift_id', [], null, ['class' => 'form-control select2 shift_id', 'placeholder' => __('Select Shift')]) }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_time', __('Start Time'), ['class' => 'col-form-label']) }}
                {{ Form::time('start_time', null, ['class' => 'form-control timepicker_format', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_time', __('End Time'), ['class' => 'col-form-label']) }}
                {{ Form::time('end_time', null, ['class' => 'form-control timepicker_format','required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('reason', __('Reason'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => __('Reason'), 'rows' => '3','required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('document', __('Document'), ['class' => 'col-form-label pb-1 pt-3']) }}
                <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
                <div>
                    <label for="myDocument">
                    <div class="btn btn-block btn-primary bg-primary document"> <i
                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="myDocument">
                    </label>
                    <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="uploadFile"><i
                        class="fa fa-regular fa-file"></i><p id="fileName"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
