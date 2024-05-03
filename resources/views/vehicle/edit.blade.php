
{{ Form::model($vehicle, ['route' => ['vehicle.update', $vehicle->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-md-6 col-12">
                {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('name', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Name')]) }}
            </div>
            <div class="form-group col-md-6 col-12">
                {{ Form::label('type', __('Type'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('type', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Type')]) }}
            </div>
            <div class="form-group col-md-6 col-12">
                {{ Form::label('police_no', __('Police No'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('police_no', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Police No')]) }}
            </div>
            <div class="form-group col-md-6 col-12">
                {{ Form::label('km', __('KM'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::number('km', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01', 'placeholder' => __('Enter KM')]) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select2', 'placeholder' => __('Select Branch')]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}