@php
    $setting = App\Models\Utility::settings();
@endphp
{{ Form::open(['url' => 'permit', 'method' => 'post','enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    @if (\Auth::user()->type != 'employee')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                    {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('permit_type_id', __('Permit Type'), ['class' => 'col-form-label']) }}
                <select name="permit_type_id" id="permit_type_id" class="form-control select2">
                     <option value="" >{{ __('Select Permit Type') }}</option>
                    @foreach ($permittypes as $permit)
                        <option value="{{ $permit->id }}">{{ $permit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('start_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'placeholder' => __('Select Start Date')]) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('end_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'placeholder' => __('Select End Date')]) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('reason', __('Reason'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => __('Reason'), 'rows' => '3']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('document', __('Document'), ['class' => 'col-form-label pb-1 pt-3']) }}
                <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
                <div>
                    <label for="myDocument">
                        <div class="btn btn-block btn-primary bg-primary document"> 
                            <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="myDocument" required>
                    </label>
                    <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="uploadFile">
                        <i class="ti ti-file text-white"></i><p id="fileName"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            {{-- @if(isset($setting['is_enabled']) && $setting['is_enabled'] =='on')
            <div class="form-group">
                {{ Form::label('synchronize_type', __('Synchroniz in Google Calendar ?'), ['class' => 'form-label']) }}
                <div class=" form-switch">
                    <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow"
                        value="google_calender">
                    <label class="form-check-label" for="switch-shadow"></label>
                </div>
            </div>
            @endif --}}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
