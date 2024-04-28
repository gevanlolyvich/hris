{{ Form::model($otherpayment, ['route' => ['otherpayment.update', $otherpayment->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Title')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('is_recurring', __('Recurring'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::select('is_recurring', $recurringOptions, null , ['class' => 'form-control select2', 'id' => 'is_recurring', 'required' => 'required']) }}
        </div>
        @if ($otherpayment->is_recurring)
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
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('type', __('Type'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::select('type', $otherpaytypes, null, ['class' => 'form-control select2 amount_type', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label amount_label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01', 'placeholder' => __('Enter Amount')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
