{{ Form::model($loan, ['route' => ['loan.update', $loan->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required','placeholder'=> __('Enter Title')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('loan_option', __('Loan Options*'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::select('loan_option', $loan_options, null, ['class' => 'form-control select2','required' => 'required']) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('is_recurring', __('Recurring'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::select('is_recurring', $recurringOptions, null , ['class' => 'form-control select2', 'required' => 'required']) }}
        </div>
        @if ($loan->is_recurring)
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
                {{ Form::select('type', $loans, null, ['class' => 'form-control select2 amount_type','required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('amount', __('Loan Amount'), ['class' => 'col-form-label amount_label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required','placeholder'=> __('Enter Amount')]) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('reason', __('Reason'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::textarea('reason', null, ['class' => 'form-control ', 'required' => 'required','rows' => 3, 'placeholder' => __('Enter Reason')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
