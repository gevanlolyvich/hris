{{ Form::model($attendanceEmployee, ['route' => ['attendanceemployee.updateAttendance', $attendanceEmployee->id], 'method' => 'PUT']) }}
<div class="modal-body">
<div class="row">
    <div class="form-group col-12">
        {{ Form::label('shift_type_id', __('Shift'), ['class' => 'col-form-label']) }}
        {{ Form::select('shift_type_id', $shift_types, null, ['class' => 'form-control select2']) }}
    </div>
    <div class="form-group col-lg-6 col-md-6">
        {{ Form::label('clock_in', __('Clock In'), ['class' => 'col-form-label']) }}
        {{ Form::time('clock_in', null, ['class' => 'form-control', 'id'=>'clock_in']) }}
    </div>

    <div class="form-group col-lg-6 col-md-6">
        {{ Form::label('clock_out', __('Clock Out'), ['class' => 'col-form-label']) }}
        {{ Form::time('clock_out', null, ['class' => 'form-control', 'id'=>'clock_out']) }}
    </div>
    <input type="hidden" name="date" value="{{ $attendanceEmployee->date }}">
    <input type="hidden" name="employee_id" value="{{ $attendanceEmployee->employee_id }}">
    <input type="hidden" name="coord_out" value="{{ $attendanceEmployee->coord_out }}">
    <input type="hidden" name="type" value="edit-attendance">
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}


