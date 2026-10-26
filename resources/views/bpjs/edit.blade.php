{{ Form::model($bpjs, ['route' => ['bpjs.update', $bpjs->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('bpjs_option', __('Bpjs Options'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::select('bpjs_option', $bpjs_options, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Bpjs Option')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('is_recurring', __('Recurring'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::select('is_recurring', $recurringOptions, $selectedRecurring ?? null, ['class' => 'form-control select2', 'id' => 'is_recurring', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::select('type', $bpjsTypes, null, ['class' => 'form-control select2 amount_type', 'id' => 'type', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount (%)'), ['class' => 'col-form-label amount_label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01', 'placeholder' => __('Enter Amount')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
