
{{ Form::open(['url' => 'vehicle-maintenance-type', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                {{ Form::text('name', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Name')]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}