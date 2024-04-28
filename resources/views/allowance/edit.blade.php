{{ Form::model($allowance, ['route' => ['allowance.update', $allowance->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('allowance_option', __('Allowance Options*'), ['class' => 'col-form-label']) }}
            {{ Form::select('allowance_option', $allowance_options, null, ['class' => 'form-control select2','required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required','placeholder'=>'Enter Title']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('is_recurring', __('Recurring'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::select('is_recurring', $recurringOptions, null , ['class' => 'form-control select2', 'id' => 'is_recurring', 'required' => 'required']) }}
        </div>
        @if ($allowance->is_recurring)
            <div class="form-group col-md-6">
                {{ Form::label('period', __('Period'), ['class' => 'col-form-label']) }}
                {{Form::month('period', null ,['class'=>'month-btn form-control month-btn', 'id' => 'period', 'disabled' => 'disabled'])}}
            </div>
        @else
            <div class="form-group col-md-6">
                {{ Form::label('period', __('Period'), ['class' => 'col-form-label']) }}
                {{Form::month('period', null ,['class'=>'month-btn form-control month-btn', 'id' => 'period'])}}
            </div>
        @endif
        <div class="form-group">
            {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label amount_label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01','placeholder'=>'Enter Amount','autocomplete'=>'off']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
