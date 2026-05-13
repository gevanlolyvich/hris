{{ Form::model($overtime, ['route' => ['overtime.update', $overtime->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1"> *</span>
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'placeholder' => __('Select Employee')]) }}
        </div>
        <div class="form-group col-md-6">
                {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1"> *</span>
                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required','placeholder'=>'Enter Title']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::date('date', null, ['class' => 'form-control datetime-local ', 'required' => 'required', 'autocomplete'=>'off']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Overtime Type'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::select('type', $types, null, ['class' => 'form-control select2', 'placeholder' => __('Select Overtime Type')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows'=>'3']) }}
        </div>
        <div class="col-md-12">
            <div class="form-group row">
                {{ Form::label('document', __('Document'), ['class' => 'col-form-label pb-1 pt-3']) }}
                <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
                <div class="col-8">
                    <label for="myDocument">
                    <div class="btn btn-block btn-primary bg-primary document"> <i
                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="overtimeDocument">
                    </label>
                    <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="overtimeFile"><i
                        class="fa fa-regular fa-file"></i><p id="overtimeFileName"></p>
                    </div>
                </div>
                @if (!empty($overtime->document))
                    <div class="col-md-4">
                        {{ Form::label('old_file', __('Old File : '), ['class' => 'col-form-label']) }}
                        <a href="{{ asset($overtime->document) }}" target="blank" class="btn btn-block btn-info btn-outline-dark bg-info"
                            data-bs-toggle="tooltip"
                            data-bs-original-title="{{ __('View') }}">
                            <i class="ti ti-file text-white" style="font-size: 15px"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
