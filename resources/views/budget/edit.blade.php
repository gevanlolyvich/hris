{{ Form::open(['url' => 'budget/' . $budget->id, 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::select('employee_id', $employees, $budget->employee_id, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Employee')]) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('jenis', __('Jenis'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('jenis', old('jenis', $budget->jenis), ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Jenis')]) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('nominal', __('Nominal'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::number('nominal', old('nominal', $budget->nominal), ['class' => 'form-control', 'required' => 'required', 'min' => '0', 'step' => '100', 'placeholder' => __('Enter Nominal')]) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('tanggal', __('Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::date('tanggal', old('tanggal', \Carbon\Carbon::parse($budget->tanggal)->format('Y-m-d')), ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('file', __('Document'), ['class' => 'col-form-label']) }}
                {{ Form::file('file', ['class' => 'form-control']) }}
                <small class="text-muted d-block">{{ __('Optional. PDF, JPG, PNG or Office file, max 10 MB.') }}</small>
                @if ($budget->file)
                    <small class="text-muted d-block">{{ __('Current Document:') }}
                        <a href="{{ $budget->file_url }}" target="_blank" rel="noopener">{{ basename($budget->file) }}</a>
                    </small>
                    <small class="text-muted d-block">{{ __('Leave empty to keep the current document.') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}
