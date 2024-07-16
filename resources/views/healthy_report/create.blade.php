
{{ Form::open(['url' => 'healthy-targets', 'method' => 'post']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('activity_name', __('Activity'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::select('activity_name', $activities, null, ['class' => 'form-control select2','required' => 'required','placeholder'=>__('Select Activity')]) }}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('target', __('Target/Day'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::number('target', old('target'), ['class' => 'form-control', 'required' => 'required' ,'placeholder'=> __('Enter Target')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}



