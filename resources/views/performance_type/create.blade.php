
{{ Form::open(['url' => 'performanceType', 'method' => 'post']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('parent_id', __('Part Of'), ['class' => 'col-form-label']) }}
                {{ Form::select('parent_id', $parents, null, ['class' => 'form-control select2','placeholder'=>__('Select Perfomace Type')]) }}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', old('name'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=> __('Enter Name')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}



