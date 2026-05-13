{{ Form::open(['url' => 'transfer', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => 'required','placeholder'=>__('Select Employee')]) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('transfer_date', __('Transfer Date'), ['class' => 'col-form-label']) }}
            {{ Form::date('transfer_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off' , 'required' => 'required']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}
            {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select2' , 'required' => 'required','placeholder'=>__('Select Branch')]) }}
        </div>

        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('department_id', __('Select Department'), ['class' => 'col-form-label']) }}

            <div class="form-icon-user">
                <div class="department_div">
                    <select class="form-control select2  department_id" name="department_id"
                         placeholder="{{ __('Select Department') }}">
                         <option value="" disabled selected>{{ __('Select Department') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('designation_id', __('Select Designation'), ['class' => 'col-form-label']) }}

            <div class="form-icon-user">
                <div class="designation_div">
                    <select class="form-control select2  designation_id" name="designation_id"
                         placeholder="Select Designation">
                         <option value="" disabled selected>{{ __('Select Designation') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('shift_type_id', __('Select Shift'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>

            <div class="form-icon-user">
                <div class="shift_type_id_div">
                    <select class="form-control select2  shift_type_id" name="shift_type_id"
                         placeholder="Select Shift">
                         <option value="" disabled selected>{{ __('Select Shift') }}</option>
                         
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group col-lg-12 col-md-12">
            {!! Form::label('managed_by', __('Select Direct Supervisor'), ['class' => 'col-form-label']) !!}
            <div class="form-icon-user">
                <div class="managed_by_div">
                    <select class="form-control select2  managed_by" name="managed_by"
                         placeholder="{{ __('Select Direct Supervisor') }}">
                         <option value="" disabled selected>{{ __('Select Direct Supervisor') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group col-lg-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'),'rows'=>'3' , 'required' => 'required']) }}
        </div>
        <div class="form-group col-lg-12 col-md-12">
            {{ Form::label('document', __('Document'), ['class' => 'col-form-label pb-1 pt-3']) }}
            <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
            <div>
                <label for="myDocument">
                <div class="btn btn-block btn-primary bg-primary document"> <i
                            class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                    </div>
                    <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="myDocument" required>
                </label>
                <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="uploadFile"><i
                    class="fa fa-regular fa-file"></i><p id="fileName"></p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}

