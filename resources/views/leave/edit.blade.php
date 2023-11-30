{{ Form::model($leave, ['route' => ['leave.update', $leave->id], 'method' => 'PUT','enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'placeholder' => __('Select Employee'), 'disabled' => 'disabled']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_type_id', __('Leave Type'), ['class' => 'col-form-label']) }}
                <select name="leave_type_id" id="leave_type_id" class="form-control select">
                    @foreach ($leavetypes as $leavee)
                        @if ($leave->leave_type_id == $leavee->id)
                            <option value="{{ $leavee->id }}" selected>{{ $leavee->title }} (<p class="float-right pr-5">
                                    {{ $leavee->days }}</p>)</option>
                        @else
                            <option value="{{ $leavee->id }}">{{ $leavee->title }} (<p class="float-right pr-5">
                                    {{ $leavee->days }}</p>)</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('start_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'placeholder' => 'Select start date']) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('end_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'placeholder' => 'Select end date']) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('location', __('Location'), ['class' => 'col-form-label']) }}
                {{ Form::text('location', null, ['class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'Select Location']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'placeholder' => __('Leave Reason'),'rows'=>'3']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'placeholder' => __('Leave Remark'),'rows'=>'3']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('document', __('Document'), ['class' => 'form-label']) }}
                <div class="choose-files ">
                    <label for="document">
                        <div class=" bg-primary document "> <i
                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control file" name="document"  onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                        <img id="blah" class="mt-3"  width="100" src="" />
                    </label>
                </div>
            </div>
        </div>
    </div>
    @role('Company')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('status', __('Status'), ['class' => 'col-form-label']) }}
                    <select name="status" id="" class="form-control select2">
                        <option value="">{{ __('Select Status') }}</option>
                        <option value="pending" @if ($leave->status == 'Pending') selected="" @endif>{{ __('Pending') }}
                        </option>
                        <option value="approval" @if ($leave->status == 'Approval') selected="" @endif>{{ __('Approval') }}
                        </option>
                        <option value="reject" @if ($leave->status == 'Reject') selected="" @endif>{{ __('Reject') }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
    @endrole
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

</div>
{{ Form::close() }}
