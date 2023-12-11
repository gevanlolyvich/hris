{{ Form::open(['url' => 'overtime', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
{{-- {{ Form::hidden('employee_id', $employee->id, []) }} --}}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Overtime Title'), ['class' => 'col-form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required','placeholder'=>'Enter Title']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
            {{ Form::date('date', null, ['class' => 'form-control datetime-local ', 'required' => 'required', 'autocomplete'=>'off']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows'=>'3']) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('document', __('Document'), ['class' => 'col-form-label pb-1 pt-3']) }}
            <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
            <div>
                <label for="overtimeDocument">
                <div class="btn btn-block btn-primary bg-primary document"> <i
                            class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                    </div>
                    <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="overtimeDocument">
                </label>
                <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="overtimeFile"><i
                    class="fa fa-regular fa-file"></i><p id="overtimeFileName"></p>
                </div>
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::checkbox('is_work_day', 'yes', false, ['class' => 'form-check-input', 'autocomplete'=>'off']) }}
            {{ Form::label('is_work_day', __('Is Work Day'), ['class' => 'form-check-label']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">

</div>
{{ Form::close() }}
