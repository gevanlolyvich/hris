
{{ Form::model($competencies, ['route' => ['competencies.update', $competencies->id], 'method' => 'PUT']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('performance_type_id', __('Performance Type'), ['class' => 'col-form-label'])}}
                {{ Form::select('performance_type_id', $performance_types, null, ['class' => 'form-control select2', 'placeholder' => __('Select Performance Type')]) }}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label'])}}<span class="text-danger pl-1"> *</span>
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Competencies Name')]) }}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('type', __('Type'), ['class' => 'col-form-label'])}}<span class="text-danger pl-1"> *</span>
                {{ Form::select('type', $types, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Competencies Type')]) }}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows'=>'3']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
