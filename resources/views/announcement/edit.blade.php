
 {{ Form::model($announcement, ['route' => ['announcement.update', $announcement->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Announcement Title'), ['class' => 'col-form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Announcement Title')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}
                
                {{ Form::select('branch_id', $branch, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Branch'), 'id' => 'branch_id']) }}
            </div>
        </div>

        <div class="col-md-6">
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
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Announcement start Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('start_date', null, ['class' => 'form-control d_week','autocomplete'=>'off']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('Announcement End Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('end_date', null, ['class' => 'form-control d_week','autocomplete'=>'off']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('description', __('Announcement Description'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control','placeholder' => __('Enter Announcement Title'),'rows'=>'3']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('myDocument', __('Document'), ['class' => 'col-form-label']) }}
                <div>
                    <label for="myDocument">
                    <div class="btn btn-block btn-primary bg-primary document"> <i
                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="myDocument">
                    </label>
                    <div class="btn btn-block btn-success btn-md bg-success disabled float-end mb-2" style="display: none;margin-top: -15px;" id="uploadFile"><i
                        class="ti ti-file text-white"></i><p id="fileName"></p>
                    </div>
                </div>
            </div>
        </div>
        @if (!empty($announcement->document))
            <div class="col-md-4 mb-3">
                <b>
                    {{ Form::label('old_file', __('Old File : '), ['class' => 'col-form-label']) }}
                </b>
                <a href="{{ asset($announcement->document) }}" target="blank" class="btn btn-block btn-info btn-outline-dark bg-info"
                    data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('View') }}">
                    <i class="ti ti-file text-white" style="font-size: 15px"></i>
                </a>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

    </div>
</div>
{{ Form::close() }}
