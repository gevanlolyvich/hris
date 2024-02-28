
{{ Form::model($level, ['route' => ['level-designation.update', $level->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Level Name'), ['class' => 'col-form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Level Name')]) }}
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('goal_weight', __('Goal Weight'), ['class' => 'col-form-label']) }}
                {{ Form::number('goal_weight', old('goal_weight'), ['class' => 'form-control', 'placeholder' => __('Enter Goal Weight'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 100]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('competency_weight', __('Competency Weight'), ['class' => 'col-form-label']) }}
                {{ Form::number('competency_weight', old('competency_weight'), ['class' => 'form-control', 'placeholder' => __('Enter Competency Weight'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 100]) }}
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('designation_id', __('Designation'), ['class' => 'col-form-label']) }}
                {{ Form::select('designation_id', $designations, (!empty($level->designation_ids)) ? explode(",",$level->designation_ids) : [], ['class' => 'form-control select2 designation_id', 'data-placeholder' => __('Select Designation'), 'multiple' => true, 'name' => 'designation_id[]']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

    </div>
</div>
{{ Form::close() }}
