
{{ Form::open(['url' => 'leave-office', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-6">
                {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::date('date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'required' => 'required', 'id' => 'date_input', 'min' => date('Y-m-d')]) }}
            </div>
            <div class="form-group col-6">
                {{ Form::label('location', __('Location'), ['class' => 'col-form-label']) }}
                {{ Form::text('location', null, ['class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => __('Enter Location')]) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('purpose', __('Purpose'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::textarea('purpose', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Purposes Of Vehicle Lending Request'),'rows'=>'5']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}