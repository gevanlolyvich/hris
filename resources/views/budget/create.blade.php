{{ Form::open(['url' => 'budget', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Employee')]) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('jenis', __('Jenis'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('jenis', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Jenis')]) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('nominal', __('Nominal'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::number('nominal', null, ['class' => 'form-control', 'required' => 'required', 'min' => '0', 'step' => '100', 'placeholder' => __('Enter Nominal')]) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('tanggal', __('Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::date('tanggal', old('tanggal', date('Y-m-d')), ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('file', __('Document'), ['class' => 'col-form-label']) }}
                {{ Form::file('file', ['class' => 'form-control']) }}
                <small class="text-muted d-block">{{ __('Optional. PDF, JPG, PNG or Office file, max 10 MB.') }}</small>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}
