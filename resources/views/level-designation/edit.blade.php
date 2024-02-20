
{{ Form::model($level, ['route' => ['level-designation.update', $level->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Level Name'), ['class' => 'col-form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Level Name')]) }}
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('designation_id', __('Designation'), ['class' => 'col-form-label']) }}
                {{ Form::select('designation_id', $designations, (!empty($level->designation_ids)) ? explode(",",$level->designation_ids) : [], ['class' => 'form-control select2 designation_id', 'data-placeholder' => __('Select Designation'), 'multiple' => true, 'name' => 'designation_id[]']) }}
            </div>
        </div>
        {{-- <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('department_id', __('Department'), ['class' => 'col-form-label']) }}

                <div class="department_div">
                    {{ Form::select('department_id[]', $departments, (!empty($announcement->department_id)) ? explode(",",$announcement->department_id) :null, ['class' => 'form-control select2 department_id','multiple','id'=>'department_id', 'placeholder' => __('Select Department')]) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}

                <div class="employee_div">
                    {{ Form::select('employee_id[]', $employees, (!empty($announcement->employee_id)) ? explode(",",$announcement->employee_id) :null, ['class' => 'form-control select2 employee_id','multiple','id'=>'employee_id', 'placeholder' => __('Select Employee')]) }}
                </div>
            </div>
        </div> --}}
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

    </div>
</div>
{{ Form::close() }}
