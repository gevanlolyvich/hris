{{ Form::model($leave, ['route' => ['leave-office.update', $leave->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-6">
                {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::date('date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'required' => 'required', 'id' => 'date_input', 'min' => $leave->date]) }}
            </div>
            <div class="form-group col-6">
                {{ Form::label('leave', __('Leave Office Time'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::time('leave', null, ['class' => 'form-control', 'required' => 'required', 'id'=>'clock_in']) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('location', __('Location') . ' ' . __('Purpose'), ['class' => 'col-form-label']) }}
                {{ Form::text('location', null, ['class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => __('Enter Location')]) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('need', __('Need'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('need', null, ['class' => 'form-control', 'autocomplete' => 'off', 'required' => 'required', 'placeholder' => __('Enter Need')]) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows'=>'5']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}