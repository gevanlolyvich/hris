{{ Form::model($vehicleLending, ['route' => ['vehicle-lending.update', $vehicleLending->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-6">
                {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::date('date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'required' => 'required', 'id' => 'date_input', 'min' => date('Y-m-d')]) }}
            </div>
            <div class="form-group col-6">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::date('end_date', null, ['class' => 'form-control month-btn', 'autocomplete' => 'on', 'required' => 'required', 'id' => 'end_date_input', 'min' =>$vehicleLending->date ]) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('vehicle_id', __('Vehicle'), ['class' => 'col-form-label']) }}
                <div class='vehicle_div'>
                    {{ Form::select('vehicle_id', $vehicles, null, ['class' => 'form-control select2 vehicle_id', 'placeholder' => __('Select Vehicle'), 'id' => 'vehicle_id']) }}
                </div>
            </div>
            <div class="form-group col-12">
                {{ Form::label('purpose', __('Purpose'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::textarea('purpose', null, ['class' => 'form-control', 'placeholder' => __('Enter Purposes Of Vehicle Lending Request'),'rows'=>'5']) }}
            </div>
            {!! Form::hidden('chosen_vehicle', $vehicleLending->vehicle_id, ['id' => 'chosen_vehicle']) !!}
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}