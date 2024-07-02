{{ Form::model($permit, ['route' => ['permit.update', $permit->id], 'method' => 'PUT','enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'placeholder' => __('Select Employee')]) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('permit_type_id', __('Leave Type'), ['class' => 'col-form-label']) }}
                {{ Form::select('permit_type_id', $permittype, null, ['class' => 'form-control select2', 'placeholder' => __('Select Leave Type')]) }}
            </div>
        </div>
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
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('reason', __('Reason'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => __('Reason'),'rows'=>'3']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group row">
                {{ Form::label('document', __('Document'), ['class' => 'col-form-label pb-1 pt-3']) }}
                <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
                <div class="col-8">
                    <label for="myDocument">
                    <div class="btn btn-block btn-primary bg-primary document"> <i
                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="myDocument">
                    </label>
                    <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="uploadFile"><i
                        class="ti ti-file text-white"></i><p id="fileName"></p>
                    </div>
                </div>
                @if (!empty($permit->docs))
                    <div class="col-md-4">
                        <a href="{{ asset($permit->docs) }}" target="blank" class="btn btn-block btn-info btn-outline-dark bg-info"
                            data-bs-toggle="tooltip"
                            data-bs-original-title="{{ __('View') }}">
                            <i class="ti ti-file text-white" style="font-size: 15px"></i>
                        </a>
                    </div>
                @endif
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
                        {{-- <option value="pending" @if ($permit->status == 'Pending') selected="" @endif>{{ __('Pending') }}
                        </option>
                        <option value="approval" @if ($permit->status == 'Approval') selected="" @endif>{{ __('Approval') }}
                        </option>
                        <option value="reject" @if ($permit->status == 'Reject') selected="" @endif>{{ __('Reject') }}
                        </option> --}}
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
