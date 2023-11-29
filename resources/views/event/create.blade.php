@php
    $setting = App\Models\Utility::settings();
@endphp

{{ Form::open(['url' => ['event'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}
                <select class="form-control select" name="branch_id" id="branch_id"
                    placeholder="{{ __('Select Branch') }}">
                    <option value="">{{ __('Select Branch') }}</option>
                    <option value="0">{{ __('All Branch') }}</option>
                    @foreach ($branch as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('department_id', __('Department'), ['class' => 'col-form-label']) }}
                <div class="department_div">
                    <select class="form-control department_id" name="department_id[]"
                         placeholder="Select Designation" >
                    <option value="">{{ __('Select Designation') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                <div class="employee_div">
                    <select class="form-control  employee_id" name="employee_id[]"
                        placeholder="Select Employee">
                    <option value="">{{ __('Select Employee') }}</option>
                    </select>
                </div>

            </div>
        </div>

        <div class="col-md-12 col-sm-12 col-lg-12 col-xl-12">
            <div class="form-group">
                {{ Form::label('title', __('Event Title'), ['class' => 'col-form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control ', 'placeholder' => __('Enter Event Title')]) }}
            </div>
        </div>
        <div class="col-md-6 col-sm-12 col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Event start Date'), ['class' => 'col-form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control datetime-local ', 'autocomplete'=>'off']) }}
            </div>
        </div>
        <div class="col-md-6 col-sm-12 col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('end_date', __('Event End Date'), ['class' => 'col-form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control datetime-local ','autocomplete'=>'off' ]) }}
            </div>
        </div>
        <div class="col-md-6 col-sm-12 col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('location', __('Event Location'), ['class' => 'col-form-label']) }}
                {{ Form::text('location', null, ['class' => 'form-control ', 'id' => 'location-input', 'placeholder' => __('Enter Event Location')]) }}
            </div>
        </div>
        <div class="col-md-6 col-sm-12 col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('location action', __('Location Action'), ['class' => 'col-form-label']) }}
                <br>
                <button class="btn bg-primary btn-lg text-white" style="margin-right: 15px" type="button" id="get-location">{{__('Search Location')}}</button>
                <button class="btn bg-primary btn-lg text-white" type="button" id="show-map">{{__('Show Map')}}</button>
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12" style="display: none;" id="map-box">
            <div class="form-group">
                {{ Form::label('map', __('Map'), ['class' => 'form-label']) }}
                <div id="openStreetMapContainer" style="height: 400px;"></div>
                <input type="hidden" name="latitude" id="latitude" value="0">
                <input type="hidden" name="longitude" id="longitude" value="0">
            </div>
        </div>

        <div></div>
        <div class="col-md-12 col-sm-12 col-lg-12 col-xl-12">
            <div class="form-group">
                {{ Form::label('color', __('Event Select Color'), ['class' => 'col-form-label d-block mb-3']) }}
                <div class="btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                    <label class="btn bg-info active p-3"><input type="radio" name="color" value="event-info" checked class="d-none"></label>
                    <label class="btn bg-warning p-3"><input type="radio" name="color" value="event-warning" class="d-none"></label>
                    <label class="btn bg-danger p-3"><input type="radio" name="color" value="event-danger" class="d-none"></label>
                    <label class="btn bg-success p-3"><input type="radio" name="color" value="event-success" class="d-none"></label>
                    <label class="btn p-3" style="background-color: #51459d !important"><input type="radio" name="color" class="d-none" value="event-primary"></label>
                </div>
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('description', __('Event Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Event Description'),'rows'=>'5']) }}
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('myDocument', __('Document'), ['class' => 'col-form-label']) }}
                <div>
                    <label for="myDocument">
                    <div class="btn btn-block btn-primary bg-primary document"> <i
                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="myDocument">
                    </label>
                    <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="uploadFile"><i
                        class="fa fa-regular fa-file"></i><p id="fileName"></p>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($setting['is_enabled']) && $setting['is_enabled'] =='on')
        <div class="form-group col-md-6">
            {{ Form::label('synchronize_type', __('Synchroniz in Google Calendar ?'), ['class' => 'col-form-label']) }}
            <div class=" form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow"
                    value="google_calender">
                <label for="switch-shadow"></label>
            </div>
        </div>
        @endif
        
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">

</div>
{{ Form::close() }}

{{-- load cdn js leaflet --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
crossorigin=""></script>