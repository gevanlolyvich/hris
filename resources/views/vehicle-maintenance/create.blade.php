
{{ Form::open(['url' => 'vehicle-maintenance', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-6">
                {{ Form::label('start_date', __('Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::date('start_date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'required' => 'required', 'id' => 'date_input']) }}
            </div>
            <div class="form-group col-6">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'id' => 'end_date_input', 'disabled' => true]) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('vehicle_id', __('Vehicle'), ['class' => 'col-form-label']) }}
                {{ Form::select('vehicle_id', $vehicles, null, ['class' => 'form-control select2 vehicle_id', 'placeholder' => __('Select Vehicle'), 'id' => 'vehicle_id']) }}
            </div>
            <div class="form-group col-6">
                {{ Form::label('maintenance_type_id', __('Maintenance Type'), ['class' => 'col-form-label']) }}
                {{ Form::select('maintenance_type_id', $types, null, ['class' => 'form-control select2 maintenance_type_id', 'placeholder' => __('Select Maintenance Type'), 'id' => 'maintenance_type_id']) }}
            </div>
            <div class="form-group col-6">
                {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('name', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Name')]) }}
            </div>
            <div class="form-group col-6">
                {{ Form::label('location', __('Location'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('location', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Location')]) }}
            </div>
            <div class="form-group col-6">
                {{ Form::label('cost', __('Cost'), ['class' => 'col-form-label']) }}
                {{ Form::number('cost', null, ['class' => 'form-control', 'step' => '0.01', 'placeholder' => __('Enter Cost')]) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('file', __('Receipt File'), ['class' => 'col-form-label']) }}
                {{ Form::file('file', ['class' => 'form-control mb-2']) }}
                <span class="text-warning pl-1"><b>{{ __('Max Upload Size Per File: 10 MB')}}</b></span>
            </div>
            <div class="form-group col-12">
                {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows'=>'7']) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('next_date', __('Next Maintenance Date'), ['class' => 'col-form-label']) }}
                {{ Form::date('next_date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'id' => 'next_date_input', 'disabled' => true]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}