{{ Form::open(['url' => 'goal', 'method' => 'post']) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1"> *</span>
                    {{ Form::select('branch_id', $branch, null, ['class' => 'form-control select2', 'placeholder' => __('Select Branch')]) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('department_id', __('Department'), ['class' => 'col-form-label']) }}
                    <div class="department_div">
                        {{ Form::select('department_id', [], null, ['class' => 'form-control select2 department_id', 'placeholder' => __('Select Department'), 'id' => 'department_id']) }}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                    <div class='employee_div'>
                        {{ Form::select('employee_id', [], null, ['class' => 'form-control select2 employee_id', 'placeholder' => __('Select Employee'), 'id' => 'employee_id']) }}
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    {{ Form::label('name', __('Goal Name'), ['class' => 'form-label'])}}<span class="text-danger pl-1"> *</span>
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Name')]) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1"> *</span>
                    {{ Form::text('start_date', null, ['class' => 'form-control d_week','autocomplete'=>'off' ,'required' => 'required']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1"> *</span>
                    {{ Form::text('end_date', null, ['class' => 'form-control d_week','autocomplete'=>'off' ,'required' => 'required']) }}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('target', __('Target'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1"> *</span>
                    {{ Form::textarea('target', null, ['class' => 'form-control', 'rows'=>'3', 'required' => 'required', 'placeholder'=>__('Enter Target')]) }}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
                    {{ Form::textarea('description', null, ['class' => 'form-control' ,'rows'=>'4' ,'placeholder'=> __('Enter Description')]) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{{ Form::close() }}
