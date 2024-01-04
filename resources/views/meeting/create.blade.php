@php
    $setting = App\Models\Utility::settings();
@endphp
{{ Form::open(['url' => 'meeting', 'method' => 'post']) }}
<div class="modal-body">

    <div class="row">

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <select class="form-control " name="branch_id" placeholder="Select Branch" id="branch_id">
                        <option value="">{{ __('Select Branch') }}</option>
                        @if (empty(\Auth::user()->branch_id))
                            <option value="0">{{ __('All Branch') }}</option>
                        @endif
                        @foreach ($branch as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <div class="department_div">
                        <select class="form-control select2 department_id" name="department_id[]" id="choices-multiple"
                            placeholder="Select Department" multiple>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <div class="employee_div">
                        <select class="form-control  employee_id" name="employee_id[]" id="choices-multiple1"
                            placeholder="Select Employee" required>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('title', __('Meeting Title'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => __('Enter Meeting Title')]) }}
                </div>
            </div>
        </div>


        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('meeting_type', __('Meeting Type'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('meeting_type', $meeting_types, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Meeting Type')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('url', __('Meeting URL'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('url', null, ['class' => 'form-control ', 'placeholder' => __('Enter Meeting URL')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('password', __('Meeting Password'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('password', null, ['class' => 'form-control ', 'placeholder' => __('Enter Meeting Password')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('location', null, ['class' => 'form-control ', 'placeholder' => __('Enter Location')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('start_time', __('Meeting Start Date'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::datetimeLocal('start_time', null, ['class' => 'form-control datetime-local', 'required' => 'required', 'placeholder' => __('Enter Location')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
                {{ Form::label('end_time', __('Meeting End Date'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::datetimeLocal('end_time', null, ['class' => 'form-control datetime-local', 'required' => 'required', 'placeholder' => __('Enter Location')]) }}
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('note', __('Meeting Note'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => '3']) }}
                </div>
            </div>
        </div>
        @if(isset($setting['is_enabled']) && $setting['is_enabled'] =='on')
        <div class="form-group col-md-6">
            {{ Form::label('synchronize_type', __('Synchroniz in Google Calendar ?'), ['class' => 'form-label']) }}
            <div class=" form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow"
                    value="google_calender">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
