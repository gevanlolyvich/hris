{{ Form::model($attendance_request, ['route' => ['attendancerequest.update', $attendance_request->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
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
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'required' => 'required',  'placeholder' => __('Select Date')]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('start_time', __('Start Time'), ['class' => 'col-form-label']) }}
                {{ Form::time('start_time', null, ['class' => 'form-control timepicker_format', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-4">
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
                {{ Form::label('document', __('Document'), ['class' => 'col-form-label']) }}
                <div class="choose-files ">
                    <label for="document">
                        <div class=" bg-primary document "> <i
                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control file" name="document"  onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                        <img id="blah" class="mt-3"  width="100" src="" />
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

</div>
{{ Form::close() }}
