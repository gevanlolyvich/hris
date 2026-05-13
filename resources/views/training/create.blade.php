{{ Form::open(['url' => 'training', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('employee', __('Employee'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::select('employee', $employees, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Employee')]) }}
            </div>
        </div>
        <div class="form-group col-12">
            {{ Form::label('name', __('Training Name'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::text('name', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Training Name')]) }}
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('training_type', __('Training Type'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::select('training_type', $trainingTypes, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Training Option')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('organizer_type', __('Organizer Type'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::select('organizer_type', $organizerTypes, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Organizer Type')]) }}
            </div>
        </div>
        <div class="form-group col-md-6 col-12">
            {{ Form::label('organizer', __('Training Organizer'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::text('organizer', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Training Organizer')]) }}
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('training_cost', __('Training Cost'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::number('training_cost', null, ['class' => 'form-control', 'step' => '0.01', 'required' => 'required', 'placeholder' => __('Enter Cost')]) }}
            </div>
        </div>
        <div class="form-group col-6">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::date('start_date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'required' => 'required', 'id' => 'date_input', 'min' => date('Y-m-d')]) }}
        </div>
        <div class="form-group col-6">
            {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
            {{ Form::date('end_date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'required' => 'required', 'id' => 'end_date_input', 'min' => date('Y-m-d'), 'disabled' => true]) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('related_to', __('Related Project'), ['class' => 'col-form-label']) }}
            {{ Form::text('related_to', null, ['class' => 'form-control ', 'placeholder' => __('Enter Related Project')]) }}
        </div>
        <div class="form-group col-lg-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows'=>'7']) }}
        </div>
        <div class="form-group col-lg-12">
            {{ Form::label('file', __('File'), ['class' => 'col-form-label']) }}
            {{ Form::file('file', ['class' => 'form-control mb-2']) }}
            <span class="text-warning pl-1"><b>{{ __('Max Upload Size Per File: 10 MB')}}</b></span>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
