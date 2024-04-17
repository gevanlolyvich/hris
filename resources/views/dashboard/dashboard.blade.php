@extends('layouts.admin')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@php
    $setting = App\Models\Utility::settings();
    
@endphp

{{-- @section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
@endsection --}}

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    {{-- Modal --}}

    @if (Auth::user()->type == 'employee')
        <div class="modal fade" id="clockInOutInputModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{__('Clock In / Clock Out')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding-top: 0.35rem">
                        <div class="row d-flex flex-column align-items-center">
                            {{ Form::open(['route' => ['overtime.attendance'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                            <div class="col-md-6 col-lg-12 text-center mx-auto mt-2">
                                <button type="button" class="btn btn-info btn-lg btn-block" id="load-overtime"><i
                                    class="fa fa-solid fa-camera"></i> {{ __('Load Webcam') }}
                                </button>
                                <div id="camera-overtime" style="display: none; position: relative" class="col-12">
                                    <video id="video-overtime" style="border-radius: 5%" class="mb-2">Video stream not available.</video>
                                    <button type="button" class="btn btn-info btn-sm custBtn3" id="takepic-overtime" style="display: none;"><i
                                        class="fa fa-solid fa-camera"></i> {{ __('Take A Picture') }}
                                    </button>
                                </div>
                                <canvas id="canvas-overtime" style="display: none;"></canvas>
                                <div id="output-overtime" style="display: none;">
                                    <img id="photo-overtime" style="border-radius: 5%" alt="The screen capture will appear in this box.">
                                </div>
                                <label for="picture-overtime">
                                    <input type="hidden" name="picture" id="picture-overtime">
                                </label>      
                            </div>
                            <hr>
                            <input type="hidden" name="latitude" id="latitude-overtime" value="0">
                            <input type="hidden" name="longitude" id="longitude-overtime" value="0">
                            <input type="hidden" name="accuracy" id="accuracy-overtime" value="0">
                            <input type="hidden" name="overtimeId" id="overtimeId" value="">
                            <div class="col-md-6 text-center mx-auto mt-3">
                                <button type="submit" value="0" name="in" id="clock_in-overtime" onclick="getLocation()"
                                    class="btn btn-primary btn-lg btn-block" style="width: 150px" disabled>{{ __('In') }}</button>
                                {{ Form::close() }}
                            </div>                                                    
                            <div class="col-md-6 text-center mx-auto mt-3">
                                {{ Form::open(['route' => ['overtime.attendance'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                    <input type="hidden" name="latitude" id="latitude_out-overtime" value="0">
                                    <input type="hidden" name="longitude" id="longitude_out-overtime" value="0">
                                    <input type="hidden" name="accuracy" id="accuracy_out-overtime" value="0">
                                    <input type="hidden" name="picture_out" id="picture_out-overtime">
                                    <input type="hidden" name="overtimeIdOut" id="overtimeIdOut" value="">
                                    <button type="submit" value="1" name="out" id="clock_out-overtime" onclick="getLocation()"
                                        class="btn btn-danger" style="width: 150px">{{ __('Out') }}</button>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="reportInputModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{__('Report')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding-top: 0.35rem">
                        {{ Form::open(['route' => ['overtime.report'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                            <div class="form-group" style="margin-bottom: 0px">
                                {{ Form::label('myDocument', __('Document'), ['class' => 'col-form-label']) }}
                                <div class="row">
                                    <label for="myDocument" class="col-6">
                                    <div class="btn btn-block btn-primary bg-primary document"> <i
                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                        </div>
                                        <input style="margin-top: -50px" type="file" class="btn btn-block btn-primary bg-primary document form-control mb-4 file" name="myDocument">
                                    </label>
                                    <div class="btn btn-block btn-success bg-success disabled col-6" style="display: none;" id="uploadFile"><i
                                        class="fa fa-regular fa-file"></i><p id="fileName"></p>
                                    </div>
                                </div>
                                <div class="col-md-6" id="exist-document-class" style="display: none;">
                                    {{ Form::label('old_file', __('Old File : '), ['class' => 'col-form-label']) }}
                                    <a href="#" target="blank" class="btn btn-block btn-info btn-outline-dark bg-info"
                                        data-bs-toggle="tooltip" id="exist-document-view"
                                        data-bs-original-title="{{ __('View') }}">
                                        <i class="ti ti-file text-white" style="font-size: 15px"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="form-group">
                                {{ Form::label('note', __('Note'), ['class' => 'col-form-label']) }}
                                {{ Form::textarea('note', null, ['class' => 'form-control', 'id' => 'note', 'placeholder' => __('Add Notes'),'rows'=>'3']) }}
                            </div>
                            <input type="hidden" name="overtimeId" id="overtimeIdReportInput" value="0">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <input type="submit" value="{{ __('Submit') }}" class="btn  btn-primary">
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="openStreetMapModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{__('Clock In / Out Data')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding-top: 0.35rem">
                        <div style="display: none;" id="modal-note">
                            <div class="text-center mx-auto">
                                <strong>{{__('Notes')}}</strong>
                                <textarea class="form-control mb-3 mt-1" name="note-value" id="note-value" rows="2" disabled></textarea>
                            </div>
                        </div>
                        <div class="clock-images mx-d-flex flex-column align-items-center" id="photos" style="display: none;">
                            <div class="text-center mx-auto">
                                <strong>{{__('Clock In / Out Image Capture')}}</strong>
                                <br>
                                <img id="clockImage" src="" alt="Clock In Out Image" style="max-width: 100%; max-height: 300px; border-radius: 5%" class="mb-3 mt-1">
                                <br>
                            </div>
                        </div>
                        <div class="text-center mx-auto">
                            <strong>{{__('Clock In / Out Location')}}</strong>
                            <div id="openStreetMapContainer" style="height: 400px; border-radius: 5%" class="mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-5">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Mark Attandance') }}</h5>
                </div>               
                <div class="card-body">
                    @if ($officeTime['is_working'])
                        <h6>{{ __($officeTime['name'])}}</h6>
                        <p class="text-muted pb-0-5">
                            {{ __('Office Time:') }} {{ $officeTime['startTime'] }} {{ __(' to ')}} {{ $officeTime['endTime'] }} WIB
                        </p>
                        {{-- Condition for showing employee already clock in or not --}}
                        @if ($yesterdayOfficeTime['is_cross_day'] && !empty($yesterdayEmployeeAttendance) && $yesterdayEmployeeAttendance->clock_out === $yesterdayEmployeeAttendance->clock_in)
                            <h5 class="text-danger pb-0-5">{{ __("Already Clock In At")}} | {{$yesterdayEmployeeAttendance->date}} {{$yesterdayEmployeeAttendance->clock_in}} WIB</h5>
                            {!! Form::hidden('source', $yesterdayEmployeeAttendance->source_out) !!}
                            @elseif (empty($employeeAttendance))
                            {{-- DO Nothing --}}
                            @else
                            <h5 class="text-danger pb-0-5">{{ __("Already Clock In At")}} | {{$employeeAttendance->date}} {{$employeeAttendance->clock_in}} WIB</h5>
                            {!! Form::hidden('source', $employeeAttendance->source_out) !!}
                        @endif
                    @else
                        <h6 class="text-muted pb-0-5">
                            {{ __('No Working Hour') }}
                        </h6>
                    @endif
                    <div class="row d-flex flex-column align-items-center">
                        {{-- Show form for attendance type and notes --}}                      
                        {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post', 'id' => 'clock-in-form', 'enctype' => 'multipart/form-data']) }}
                        {{ Form::label('picture', __('Picture'), ['class' => 'col-form-label pb-1 pt-3']) }}
                        @if ($settings['photo_on_clock'] == 'Required')
                            <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
                        @endif
                        <div class="col-md-6 col-lg-12 text-center mx-auto">
                            <button type="button" class="btn btn-info btn-lg btn-block mb-3" id="load"><i
                                class="fa fa-solid fa-camera"></i> {{ __('Load Webcam') }}
                            </button>
                            <div id="camera" style="display: none; position: relative" class="col-12">
                                <video id="video" style="border-radius: 5%" class="mb-2">Video stream not available.</video>
                                <div class="row allign-center text-center">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-info btn-md custBtn1" id="takepic" style="display: none;">
                                            <i class="fa fa-solid fa-camera"></i>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-danger btn-md custBtn2" id="closecamera" style="display: none;">
                                            <i class="fa fa-solid fa-window-close"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <canvas id="canvas" style="display: none;"></canvas>
                            <div id="output" style="display: none;">
                                <img id="photo" style="border-radius: 5%" alt="The screen capture will appear in this box.">
                            </div>
                            <label for="picture">
                                <input type="hidden" name="picture" id="picture">
                            </label>      
                        </div>
                        <div class="col-md-12" id="other-form" style="display: none;">
                            <div class="form-group mb-1">
                                {!! Form::label('shift_type_id', __('Shift'), ['class' => 'col-form-label pb-1 pt-3']) !!}
                                <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p> 
                                {{ Form::select('shift_type_id', $shift_types, \Auth::user()->employee->shift_type_id, ['class' => 'form-control select2', 'id' => 'id', 'placeholder'=>'Choose Shift']) }}
                            </div>
                            <div class="form-group mb-1">
                                {!! Form::label('attendance_type', __('Attendance Type'), ['class' => 'col-form-label pb-1 pt-3']) !!}
                                <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
                                {{ Form::select('attendance_type', $attendance_type, 1, ['class' => 'form-control select2', 'id' => 'id', 'placeholder'=>'Choose attendance type']) }}
                            </div>
                            <div class="form-group">
                                {!! Form::label('notes', __('Notes'), ['class' => 'col-form-label']) !!}
                                {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => '2' ,'placeholder'=> __('Enter notes for clock in') ]) !!}
                            </div>
                            <input type="hidden" name="latitude" id="latitude" value="0">
                            <input type="hidden" name="longitude" id="longitude" value="0">
                            <input type="hidden" name="accuracy" id="accuracy" value="0">
                            {{-- <input type="hidden" name="clockInData" id="clockInData" value="{{ $employeeAttendance }}"> --}}
                        </div>
                        <div class="col-md-6 text-center mx-auto mt-1">
                            @if ($yesterdayOfficeTime['is_cross_day'] && !empty($yesterdayEmployeeAttendance) && ($yesterdayEmployeeAttendance->clock_out === $yesterdayOfficeTime['default_clock_out'] || $yesterdayEmployeeAttendance->clock_out === $yesterdayEmployeeAttendance->clock_in || $yesterdayEmployeeAttendance->source_out !== 'Application') && strtotime(date('Y-m-d H:i:s')) > (strtotime($officeTime['startTime']) - 3600))
                                <button type="button" value="0" name="in" id="clock_in"
                                    class="btn btn-primary btn-lg btn-block disabled" style="width: 150px" disabled>{{ __('CLOCK IN') }}</button>
                            @elseif ($yesterdayOfficeTime['is_cross_day'] && empty($yesterdayEmployeeAttendance) && strtotime(date('Y-m-d H:i:s')) > (strtotime(date('Y-m-d', strtotime('yesterday')) . ' ' . $yesterdayOfficeTime['startTime']) - 3600))
                                <button type="button" value="0" name="in" id="clock_in" onclick="getLocation()"
                                    class="btn btn-primary btn-lg btn-block" style="width: 150px" >{{ __('CLOCK IN') }}</button>
                            @elseif (empty($employeeAttendance) && strtotime(date('Y-m-d H:i:s')) > (strtotime($officeTime['startTime']) - 3600))
                                <button type="button" value="0" name="in" id="clock_in" onclick="getLocation()"
                                    class="btn btn-primary btn-lg btn-block" style="width: 150px" >{{ __('CLOCK IN') }}</button>
                            @elseif (!empty($employeeAttendance) && ($employeeAttendance->clock_out == '00:00:00' || $employeeAttendance->clock_out == $employeeAttendance->clock_in || $employeeAttendance->source_out !== 'Application'))
                                <button type="button" value="0" name="in" id="clock_in" onclick="getLocation()"
                                    class="btn btn-primary btn-lg btn-block disabled" style="width: 150px" disabled>{{ __('CLOCK IN') }}</button>
                            @else
                                <button type="button" value="0" name="in" id="clock_in"
                                    class="btn btn-primary btn-lg btn-block" style="width: 150px" >{{ __('CLOCK IN') }}</button>
                            @endif
                            {{ Form::close() }}
                        </div>                                                    
                        <div class="col-md-6 text-center mx-auto mt-3">
                            @if ($yesterdayOfficeTime['is_cross_day'] && $yesterdayEmployeeAttendance && empty($employeeAttendance) && ($yesterdayEmployeeAttendance->clock_out === $yesterdayOfficeTime['default_clock_out'] || $yesterdayEmployeeAttendance->clock_out === $yesterdayEmployeeAttendance->clock_in || $yesterdayEmployeeAttendance->source_out !== 'Application'))
                                {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $yesterdayEmployeeAttendance->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'clock-out-form']) }}
                                <input type="hidden" name="latitude" id="latitude_out" value="0">
                                <input type="hidden" name="longitude" id="longitude_out" value="0">
                                <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                                <input type="hidden" name="picture_out" id="picture_out">
                                <input type="hidden" name="shift_type_id" value="{{ $employeeAttendance?->shift_type_id ?? $yesterdayEmployeeAttendance?->shift_type_id}}">
                                <button type="button" value="1" name="out" id="clock_out" onclick="getLocation()"
                                    class="btn btn-danger" style="width: 150px">{{ __('CLOCK OUT') }}</button>
                            @elseif ($employeeAttendance && ($employeeAttendance->clock_out == '00:00:00' || $employeeAttendance->clock_out == $employeeAttendance->clock_in || $employeeAttendance->source_out !== 'Application'))
                                {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $employeeAttendance->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'clock-out-form']) }}
                                <input type="hidden" name="latitude" id="latitude_out" value="0">
                                <input type="hidden" name="longitude" id="longitude_out" value="0">
                                <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                                <input type="hidden" name="picture_out" id="picture_out">
                                <input type="hidden" name="shift_type_id" value="{{ $employeeAttendance->shift_type_id ?? $yesterdayEmployeeAttendance->shift_type_id}}">
                                <button type="button" value="1" name="out" id="clock_out" onclick="getLocation()"
                                    class="btn btn-danger" style="width: 150px">{{ __('CLOCK OUT') }}</button>
                            @else
                                <button type="button" value="0" name="out" id="clock_out"
                                    class="btn btn-danger disabled" style="width: 150px" disabled>{{ __('CLOCK OUT') }}</button>
                            @endif
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
            @if (!$attendances->isEmpty())
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __("Today Attendance History") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center">
                                <thead>
                                    <tr>
                                        <th>{{ __('Shift') }}</th>
                                        <th>{{ __('Clock In') }}</th>
                                        <th>{{ __('Clock Out') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach ($attendances as $attendanceData)
                                        <tr>
                                            <td>{{ $attendanceData->shift_type?->name ?? '-' }}</td>
                                            <td>
                                                @if ($attendanceData->coord_in)
                                                    <a href="#" class="btn btn-primary btn-sm map-link" data-coordinates="{{ $attendanceData->coord_in }}" data-image="{{ $attendanceData->picture_in }}" data-note="{{ $attendanceData->note }}">
                                                        <i class="fa fa-solid fa-map-pin"></i> {{ $attendanceData->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendanceData->clock_in) : '00:00' }}
                                                    </a>
                                                @else
                                                    <a href="#" class="btn btn-primary btn-sm map-link disabled" data-coordinates="{{ $attendanceData->coord_in }}" data-image="{{ $attendanceData->picture_in }}">
                                                        <i class="fa fa-solid fa-map-pin"></i> {{ $attendanceData->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendanceData->clock_in) : '00:00' }}
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($attendanceData->coord_out)
                                                    <a href="#" class="btn btn-info btn-sm map-link" data-coordinates="{{ $attendanceData->coord_out }}" data-image="{{ $attendanceData->picture_out }}">
                                                        <i class="fa fa-solid fa-map-pin"></i> {{ $attendanceData->clock_out != $attendanceData->clock_in ? \Auth::user()->timeFormat($attendanceData->clock_out) : ' - ' }}
                                                    </a>
                                                @else
                                                    <a href="#" class="btn btn-info btn-sm map-link text-center disabled">
                                                        <i class="fa fa-solid fa-map-pin"></i> {{ $attendanceData->clock_out != $attendanceData->clock_in ? \Auth::user()->timeFormat($attendanceData->clock_out) : ' - ' }}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            @if (!empty($overtime))
                <div class="card">
                    <div class="card-header card-body table-border-style">
                        <h5>{{ __('Overtime') }}</h5>
                    </div>
                    <div class="card-body">
                        <h6>{{ __('Title')}} :</h6>
                        <p class="text-muted pb-0-5">{{ $overtime->title}}</p>
                        <h6>{{ __('Description')}} :</h6>
                        <p class="text-muted pb-0-5">{{ $overtime->description}}</p>
                        <hr>
                        <hr>
                        <div class="text-center">
                            @if ($overtime->type != 'daily')
                                <button class="btn @if ($overtime->clock_out) btn-success @else btn-primary @endif btn-xl clock-input mx-3" data-bs-toggle="tooltip"
                                    data-overtime-id="{{ $overtime->id }}"
                                    data-clock-in="{{ $overtime->clock_in }}"
                                    data-bs-original-title="{{ __('Clock In / Clock Out') }}">
                                    <i class="fa fa-solid fa-clock"></i>
                                </button>
                            @endif
                            <button class="btn @if ($overtime->report_document) btn-success @else btn-primary @endif btn-xxl report-input" data-bs-toggle="tooltip"
                                data-overtime-id="{{ $overtime->id }}"
                                data-document="{{ $overtime->report_document }}"
                                data-note="{{ $overtime->report_note }}"
                                data-bs-original-title="{{ __('Report Document') }}">
                                <i class="fa fa-solid fa-file-import"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Meeting schedule') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Meeting title') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __("Start Date") }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Detail') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($meetings as $meeting)
                                    <tr>
                                        <td>{{ $meeting->title }}</td>
                                        <td>{{ $meeting->meeting_type }}</td>
                                        <td>{{ $meeting->start_time }}</td>
                                        <td>{{ $meeting->end_time }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                        data-url="{{ URL::to('meeting/' . $meeting->id) }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Meeting') }}"
                                                        data-bs-original-title="{{ __('Meeting') }}">
                                                        <i class="ti ti-caret-right text-white"></i>
                                                    </a>
                                                </div>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-7">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-9">
                            <h5>{{ __('Calendar') }}</h5>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for=""></label>
                                @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
                                    <select class="form-control" name="calender_type" id="calender_type"
                                        onchange="get_data()">
                                        <option value="google_calender">{{ __('Google Calender') }}</option>
                                        <option value="local_calender" selected="true">
                                            {{ __('Local Calender') }}</option>
                                    </select>
                                @endif
                                <input type="hidden" id="path_admin" value="{{ url('/') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id='event_calendar' class='calendar'></div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Announcement List') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Document') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($announcements as $announcement)
                                    <tr>
                                        <td>{{ $announcement->title }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                        <td>{{ $announcement->description }}</td>
                                        <td>
                                            @if ($announcement->document)
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ $announcement->document }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('View Document') }}">
                                                        <i class="ti ti-file text-white"></i>
                                                    </a>
                                                </div>
                                            @else
                                            -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-xxl-12">
            {{-- start --}}
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('employee.index') }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-users"></i>
                                            </div>
                                            <div class="ms-3">
                                                <small class="text-muted">{{ __('Total') }}</small>
                                                <h6 class="m-0">{{ __('Employee') }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto text-end">
                                        <h4 class="m-0 text-primary">{{ $countEmployee }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('attendanceemployee.index', ['is_valid' => 1]) }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-calendar"></i>
                                            </div>
                                            <div class="ms-3">
                                                <small class="text-muted">{{ __('Total') }}</small>
                                                <h6 class="m-0">{{ __('Valid Attendance') }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto text-end">
                                        <h4 class="m-0 text-info"> {{ $validAttendance }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('attendanceemployee.index', ['is_valid' => 0]) }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="theme-avtar bg-warning">
                                                <i class="ti ti-calendar-off"></i>
                                            </div>
                                            <div class="ms-3">
                                                <small class="text-muted">{{ __('Total') }}</small>
                                                <h6 class="m-0">{{ __('Invalid Attendance') }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto text-end">
                                        <h4 class="m-0 text-warning">{{ $invalidAttendance }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="{{ route('attendancerequest.index', ['is_approved' => 0]) }}">      
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary">
                                        <i class="ti ti-zoom-question"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Request Attendance') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0 text-primary">{{ $requestAttendanceCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="{{ route('permit.index', ['status' => 'Pending']) }}">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-license"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Permit Attendance') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0 text-info"> {{ $permitCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6">
            <a href="{{ route('leave.index', ['status' => 'Pending']) }}">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-warning">
                                        <i class="ti ti-plane"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Leave') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0 text-warning">{{ $leaveCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- </div> --}}

        {{-- end --}}

        <div class="col-xxl-12">
            <div class="row">
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <div class="row">
                                <div class="col-9">
                                    <h5>{{ __("Today's Not Clock In") }}</h5>
                                </div>
                                <div class="col-2">
                                    <a href="{{ route('attendanceemployee.exportNotClockIn', ['date' => date('Y-m-d')]) }}" data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Export') }}">
                                        <button type="button" class="btn btn-info btn-lg btn-block">{{ count($notClockIns) }}</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="height: 324px; overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($notClockIns as $notClockIn)
                                            <tr>
                                                <td>{{ $notClockIn->name }}</td>
                                                <td><span class="absent-btn">{{ __('Absent') }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <h5>{{ __('Meeting schedule') }}</h5>
                        </div>
                        <div class="card-body" style="height: 324px; overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Meeting title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __("Start Date") }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Detail') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($meetings as $meeting)
                                            <tr>
                                                <td>{{ $meeting->title }}</td>
                                                <td>{{ $meeting->meeting_type }}</td>
                                                <td>{{ $meeting->start_time }}</td>
                                                <td>{{ $meeting->end_time }}</td>
                                                <td class="Action">
                                                    <span>
                                                        <div class="action-btn bg-success ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                                data-url="{{ URL::to('meeting/' . $meeting->id) }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Meeting') }}"
                                                                data-bs-original-title="{{ __('Meeting') }}">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        </div>
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-9">
                                    <h5>{{ __('Calendar') }}</h5>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label for=""></label>
                                        @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
                                            <select class="form-control" name="calender_type" id="calender_type"
                                                onchange="get_data()">
                                                <option value="google_calender">{{ __('Google Calender') }}</option>
                                                <option value="local_calender" selected="true">
                                                    {{ __('Local Calender') }}</option>
                                            </select>
                                        @endif
                                        <input type="hidden" id="path_admin" value="{{ url('/') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body card-635">
                            <div id='calendar' class='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Announcement List') }}</h5>
                </div>
                <div class="card-body" style="height: 270px; overflow:auto">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Document') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($announcements as $announcement)
                                    <tr>
                                        <td>{{ $announcement->title }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                        <td>{{ $announcement->description }}</td>
                                        <td>
                                            @if ($announcement->document)
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ $announcement->document }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('View Document') }}">
                                                        <i class="ti ti-file text-white"></i>
                                                    </a>
                                                </div>
                                            @else
                                            -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
{{-- {{ dd($arrEvents) }} --}}

@push('css-page')
    <style>
        @media (max-width: 768px) {
            #event_calendar {
                height: 750px; /* Adjust for smaller screens */
            }
        }

        .custBtn1{
            position: absolute;
            top: 83%;
            left: 40%;
            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            border: none;
            cursor: pointer;
        }

        .custBtn2{
            position: absolute;
            top: 83%;
            left: 60%;
            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            border: none;
            cursor: pointer;
        }

        .custBtn3{
            position: absolute;
            top: 83%;
            left: 50%;
            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            border: none;
            cursor: pointer;
        }
    </style>
@endpush

@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script>
        async function getLocation() {
          let locationIcon = document.getElementById('location-permission');
          return new Promise((resolve, reject) => {
            if ("geolocation" in navigator) {
              navigator.geolocation.getCurrentPosition(
                (position) => {
                  const latitude = position.coords.latitude;
                  const longitude = position.coords.longitude;
                  const accuracy = position.coords.accuracy;
                  locationIcon.style.color = "Green";
                  resolve({ latitude, longitude, accuracy });
                },
                (error) => {
                  if (error.code === 1) {
                    locationIcon.style.color = "Red";
                    reject(new Error("User denied Geolocation"));
                  } else {
                    locationIcon.style.color = "Red";
                    reject(error);
                  }
                }
              );
            } else {
                locationIcon.style.color = "Red";
                reject(new Error("Geolocation is not supported by your browser."));
            }
          });
        }

        // Automatically call getLocation when the page loads
        window.addEventListener("load", async () => {
          try {
            const { latitude, longitude, accuracy } = await getLocation();

            const clockInButton = document.getElementById("clock_in");
            const clockOutButton = document.getElementById("clock_out");
            const sourceHidden = document.getElementById("source");
            // const clockInData = document.getElementById("clockInData");

            if (latitude !== 0 && longitude !== 0 && clockInButton) {
                const latElement = document.getElementById("latitude");
                const longElement = document.getElementById("longitude");
                const accElement = document.getElementById("accuracy");
                if (latElement) {
                    latElement.value = latitude;
                }
                if (longElement) {
                    longElement.value = longitude
                }
                if (accElement) {
                    accElement.value = accuracy;
                }
                clockInButton.disabled = false;
            }
            if (latitude !== 0 && longitude !== 0 && clockOutButton) {
                const latOutElement = document.getElementById("latitude_out");
                const longOutElement = document.getElementById("longitude_out");
                const accOutElement = document.getElementById("accuracy_out");
                if (latOutElement) {
                    latOutElement.value = latitude;
                }
                if (longOutElement) {
                    longOutElement.value = longitude
                }
                if (accOutElement) {
                    accOutElement.value = accuracy;
                }
                clockOutButton.disabled = false;
            }
          } catch (error) {
            console.error(error);
            if (error.message === "User denied Geolocation") {
              // Handle the case where the user denied geolocation access
              const clockInButton = document.getElementById("clock_in");
              const clockOutButton = document.getElementById("clock_out");
              const otherForm = document.getElementById('other-form');
              if (clockInButton) {
                clockInButton.disabled = true;
              }
              if (clockOutButton) {
                clockOutButton.disabled = true;
              }
              if (otherForm) {
                  otherForm.style.display = ''
              }
            }
          } finally  {
            const otherForm = document.getElementById('other-form');
            const clockOutButton = document.getElementById("clock_out");
            const clockInButton = document.getElementById("clock_in");

            if (!Boolean(Number(clockOutButton.value))) {
                if (otherForm) {
                    otherForm.style.display = ''
                }
            } else {
                otherForm.style.display = 'none';
            }
          }
        });
    </script> 

    @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr')
        <script type="text/javascript">
            $(document).ready(function() {
                get_data();
            });

            function get_data() {
                var calender_type = $('#calender_type :selected').val();
                $('#calendar').removeClass('local_calender');
                $('#calendar').removeClass('google_calender');
                if (calender_type == undefined) {
                    calender_type = 'local_calender';
                }
                $('#calendar').addClass(calender_type);

                $.ajax({
                    url: $("#path_admin").val() + "/event/get_event_data",
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        'calender_type': calender_type,
                        'user_type': "{{ Auth::user()->type }}",
                    },
                    success: function(data) {
                        (function() {
                            var etitle;
                            var etype;
                            var etypeclass;
                            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                                headerToolbar: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                                },
                                buttonText: {
                                    timeGridDay: "{{ __('Day') }}",
                                    timeGridWeek: "{{ __('Week') }}",
                                    dayGridMonth: "{{ __('Month') }}"
                                },
                                themeSystem: 'bootstrap',
                                slotDuration: '00:10:00',
                                navLinks: true,
                                droppable: true,
                                selectable: true,
                                selectMirror: true,
                                editable: true,
                                dayMaxEvents: true,
                                handleWindowResize: true,
                                events: data,
                            });
                            calendar.render();
                        })();
                    }
                });

            }
        </script>
    @else
    <script>
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calender_type = $('#calender_type :selected').val();
            $('#event_calendar').removeClass('local_calender');
            $('#event_calendar').removeClass('google_calender');
            if (calender_type == undefined) {
                calender_type = 'local_calender';
            }
            $('#event_calendar').addClass(calender_type);

            $.ajax({
                url: $("#path_admin").val() + "/event/get_event_data",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'calender_type': calender_type,
                    'user_type': "{{ Auth::user()->type }}",
                    'empId': "{{ Auth::user()->employee->id }}",
                },
                success: function(data) {
                    (function() {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById(
                        'event_calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                        });
                        calendar.render();
                    })();
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            var map = null;
            var imageSrc = null;
            var notes = null;

            $('body').on('click', '.map-link', function() {
                var coordinates = $(this).data('coordinates').split(', ');
                notes = $(this).data('note');

                imageSrc = $(this).data('image');
                if (imageSrc.length) {
                    $('#clockImage').attr('src', imageSrc)
                    document.getElementById('photos').style.display = '';
                } else {
                    document.getElementById('photos').style.display = 'none';
                }
            
                // Convert the radius string to a number
                var radius = parseFloat(coordinates[2]);

            
                // Open the modal
                $('#openStreetMapModal').modal('show');
            
                // Initialize the map after the modal is fully shown
                $('#openStreetMapModal').on('shown.bs.modal', function () {
                    if (notes) {
                        document.getElementById('modal-note').style.display = '';
                        document.getElementById('note-value').value = notes;
                    } else {
                        document.getElementById('modal-note').style.display = 'none';
                        document.getElementById('note-value').value = '';
                    }
                    // If a map already exists, remove it
                    if (map !== null) {
                        map.remove();
                    }

                    map = L.map('openStreetMapContainer').setView([coordinates[0], coordinates[1]], 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                
                    // Add a marker for the location
                    var marker = L.marker([coordinates[0], coordinates[1]]).addTo(map);
                
                    // Add a circle with the converted radius
                    var circle = L.circle([coordinates[0], coordinates[1]], {
                        color: 'blue',
                        fillColor: '#f0023',
                        fillOpacity: 0.2,
                        radius: radius,
                    }).addTo(map);
                });
            });
        });
    </script>
    @endif

    <script>
        /* JS comes here */
        (function() {
    
            var width = 320; // We will scale the photo width to this
            var height = 0; // This will be computed based on the input stream
    
            var streaming = false;
    
            var video = null;
            var canvas = null;
            var photo = null;
            var takepic = null;
            var loadbutton = document.getElementById('load');
    
            loadbutton.addEventListener('click', startup, false);

            function compressAndSetPicture(canvas, quality) {
                canvas.toBlob(
                    function (blob) {
                        var reader = new FileReader();
                        reader.onloadend = function () {
                            var compressedDataUrl = reader.result;
                            // Set the compressed image as the source of the photo element
                            document.getElementById('photo').src = compressedDataUrl;

                            // Set the compressed image data as the value of the hidden input field
                            let pictureIn = document.getElementById('picture');
                            let pictureOut = document.getElementById('picture_out');

                            if (pictureIn) {
                                pictureIn.value = compressedDataUrl;
                            }
                            if (pictureOut) {
                                pictureOut.value = compressedDataUrl;
                            }
                        };
                        reader.readAsDataURL(blob);
                    },
                    'image/jpeg', // Change the MIME type as needed (e.g., 'image/png')
                    quality // Adjust the image quality (0 to 1)
                );
            }
    
            function startup() {
                let cameraIcon = document.getElementById('camera-permission');
                video = document.getElementById('video');
                canvas = document.getElementById('canvas');
                photo = document.getElementById('photo');
                takepic = document.getElementById('takepic');
                closecamera = document.getElementById('closecamera');
    
                navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    })
                    .then(function(stream) {
                        document.getElementById('load').style.display = 'none';
                        document.getElementById('camera').style.display = 'block';
                        document.getElementById('output').style.display = 'block';

                        takepic.style.display = '';
                        closecamera.style.display = '';
                        video.srcObject = stream;
                        video.play();
                        cameraIcon.style.color = 'Green';
                    })
                    .catch(function(err) {
                        cameraIcon.style.color = 'Red';
                        alert("Please Allow Camera Access To Take Picture For Clock In / Out");
                        console.log("An error occurred: " + err);
                    });
    
                video.addEventListener('canplay', function(ev) {
                    if (!streaming) {
                        height = video.videoHeight / (video.videoWidth / width);
    
                        if (isNaN(height)) {
                            height = width / (4 / 3);
                        }
    
                        video.setAttribute('width', width);
                        video.setAttribute('height', height);
                        document.getElementById('camera').style.width = width;
                        document.getElementById('camera').style.height = height;
                        canvas.setAttribute('width', width);
                        canvas.setAttribute('height', height);
                        photo.setAttribute('width', width);
                        photo.setAttribute('height', height);
                        streaming = true;
                    }
                }, false);
    
                takepic.addEventListener('click', function(ev) {
                    takepicture();
                    ev.preventDefault();
                }, false);

                closecamera.addEventListener('click', function(ev) {
                    console.log('Camera Must Be Close');
                    closeCamera();
                    ev.preventDefault();
                }, false)
    
                clearphoto();
            }
    
            function clearphoto() {
                var context = canvas.getContext('2d');
                context.fillStyle = "#AAA";
                context.fillRect(0, 0, canvas.width, canvas.height);
    
                var data = canvas.toDataURL('image/png');
                photo.setAttribute('src', data);
            }
    
            function takepicture() {
                var context = canvas.getContext('2d');
                if (width && height) {
                    canvas.width = width;
                    canvas.height = height;
                    context.drawImage(video, 0, 0, width, height);

                    // Compress the captured image with a specified quality
                    compressAndSetPicture(canvas, 0.8); // Adjust quality as needed
                } else {
                    clearphoto();
                }
            }

            function closeCamera() {
                document.getElementById('load').style.display = '';
                document.getElementById('camera').style.display = 'none';
                document.getElementById('output').style.display = 'none';
            
                var tracks = video.srcObject?.getTracks();
                tracks?.forEach(track => track.stop());
                video.srcObject = null;
            }
        })();
    </script>

    @if (Auth::user()->type == 'employee')
        <script>
            $(document).ready(() => {
                $(document).on('change', '[name="overtimeDocument"]', function () {
                    const overFile = document.getElementById('overtimeFile');
                    overFile.style.display = '';
                    overFile.style['max-width'] = '';
                    document.getElementById('overtimeFileName').textContent = this.files[0].name;
                });

                $(document).on('change', '[name="myDocument"]', function () {
                    const overFile = document.getElementById('uploadFile');
                    overFile.style.display = '';
                    overFile.style['max-width'] = '';
                    document.getElementById('fileName').textContent = this.files[0].name;
                });
            })
        </script>
        <script>
            /* JS comes here */
            (function() {

                var width_overtime = 320; // We will scale the photo width to this
                var height_overtime = 0; // This will be computed based on the input stream

                var streaming_overtime = false;

                var video_overtime = null;
                var canvas_overtime = null;
                var photo_overtime = null;
                var takepic_overtime = null;
                var loadbutton_overtime = document.getElementById('load-overtime');

                loadbutton_overtime.addEventListener('click', startupOvertime, false);

                function startupOvertime() {
                    video_overtime = document.getElementById('video-overtime');
                    canvas_overtime = document.getElementById('canvas-overtime');
                    photo_overtime = document.getElementById('photo-overtime');
                    takepic_overtime = document.getElementById('takepic-overtime');

                    navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: false
                        })
                        .then(function(stream) {
                            document.getElementById('load-overtime').style.display = 'none';
                            document.getElementById('camera-overtime').style.display = 'block';
                            document.getElementById('output-overtime').style.display = 'block';

                            takepic_overtime.style.display = '';
                            video_overtime.srcObject = stream;
                            video_overtime.play();
                        })
                        .catch(function(err) {
                            alert("Please Allow Camera Access To Take Picture For Clock In / Out");
                            console.log("An error occurred: " + err);
                        });

                    video_overtime.addEventListener('canplay', function(ev) {
                        if (!streaming_overtime) {
                            height_overtime = video_overtime.videoHeight / (video_overtime.videoWidth / width_overtime);

                            if (isNaN(height_overtime)) {
                                height_overtime = width / (4 / 3);
                            }

                            video_overtime.setAttribute('width', width_overtime);
                            video_overtime.setAttribute('height', height_overtime);
                            document.getElementById('camera-overtime').style.width = width_overtime;
                            document.getElementById('camera-overtime').style.height = height_overtime;
                            canvas_overtime.setAttribute('width', width_overtime);
                            canvas_overtime.setAttribute('height', height_overtime);
                            photo_overtime.setAttribute('width', width_overtime);
                            photo_overtime.setAttribute('height', height_overtime);
                            streaming_overtime = true;
                        }
                    }, false);

                    takepic_overtime.addEventListener('click', function(ev) {
                        takepictureOvertime();
                        ev.preventDefault();
                    }, false);

                    clearphotoOvertime();
                }

                function clearphotoOvertime() {
                    var context_overtime = canvas_overtime.getContext('2d');
                    context_overtime.fillStyle = "#AAA";
                    context_overtime.fillRect(0, 0, canvas_overtime.width, canvas_overtime.height);

                    var data_overtime = canvas_overtime.toDataURL('image/png');
                    photo_overtime.setAttribute('src', data_overtime);
                }

                function takepictureOvertime() {
                    var context_overtime = canvas_overtime.getContext('2d');
                    if (width_overtime && height_overtime) {
                        canvas_overtime.width = width_overtime;
                        canvas_overtime.height = height_overtime;
                        context_overtime.drawImage(video_overtime, 0, 0, width_overtime, height_overtime);

                        var data_overtime = canvas_overtime.toDataURL('image/png');
                        photo_overtime.setAttribute('src', data_overtime);
                        document.getElementById('picture').value = data_overtime;
                        document.getElementById('picture_out').value = data_overtime;
                    } else {
                        clearphotoOvertime();
                    }
                }
            })();
        </script>
        <script>
            $(document).ready(function() {
                let map = null;
                let mapIn = null;
                let mapOut = null;
                let clockIn = null;
                let clockOut = null;
                let coordIn = null;
                let coordOut = null;
                let pictureIn = null;
                let pictureOut = null;
    
                $('body').on('click', '.clock-input', async function() {
                    try {
                        let overtimeId = $(this).data('overtime-id');
                        document.getElementById('overtimeId').value = overtimeId;
                        document.getElementById('overtimeIdOut').value = overtimeId;
    
                        const { latitude, longitude, accuracy } = await getLocation();

                        const latElement = document.getElementById("latitude-overtime");
                        const longElement = document.getElementById("longitude-overtime");
                        const accElement = document.getElementById("accuracy-overtime");
    
                        const latOutElement = document.getElementById("latitude_out-overtime");
                        const longOutElement = document.getElementById("longitude_out-overtime");
                        const accOutElement = document.getElementById("accuracy_out-overtime");
    
                        if (latElement) {
                            latElement.value = latitude;
                        }
                        if (longElement) {
                            longElement.value = longitude
                        }
                        if (accElement) {
                            accElement.value = accuracy;
                        }
    
                        if (latOutElement) {
                            latOutElement.value = latitude;
                        }
                        if (longOutElement) {
                            longOutElement.value = longitude
                        }
                        if (accOutElement) {
                            accOutElement.value = accuracy;
                        }
                        
                        let clock_in = $(this).data('clock-in');
                        if (!clock_in){
                            document.getElementById("clock_in-overtime").disabled = false;
                            document.getElementById("clock_out-overtime").disabled = true;
                        }
                    } catch (error) {
                        console.error(error);
                        if (error.message === "User denied Geolocation") {
                        // Handle the case where the user denied geolocation access
                        const clockInButton = document.getElementById("clock_in");
                        const clockOutButton = document.getElementById("clock_out");
                        if (clockInButton) {
                            clockInButton.disabled = true;
                        }
                        if (clockOutButton) {
                            clockOutButton.disabled = true;
                        }
                        }
                    }
                
                    // Open the modal
                    $('#clockInOutInputModal').modal('show');
                });
    
                $('body').on('click', '.report-input', function() {
                    // Open the modal
                    $('#reportInputModal').modal('show');
    
                    // Set the modal's data attributes
                    // Get the values from the clicked button
                    let overtimeId = $(this).data('overtime-id');
                    document.getElementById('overtimeIdReportInput').value = overtimeId;
    
                    let documentFile = $(this).data('document');
                    if (documentFile) {
                    document.getElementById('exist-document-class').style.display = '';
                    document.getElementById('exist-document-view').href = documentFile;
                    }
    
                    let notes = $(this).data('note');
                    if (notes) {
                        document.getElementById('note').value = notes;
                    }
                });
    
                $('#reportInputModal').on('hidden.bs.modal', function () {
                    let file = document.getElementById('uploadFile');
                    if (file) {
                        file.style.display = 'none';
                    }
    
                    document.getElementById('exist-document-class').style.display = 'none';
                    document.getElementById('exist-document-view').href = '#';
                });
    
                $('#commonModal').on('hidden.bs.modal', function () {
                    let file = document.getElementById('uploadFile');
                    if (file) {
                        file.style.display = 'none';
                    }
                });
    
                $('#clockInOutInputModal').on('hidden.bs.modal', function () {
                    document.getElementById('load-overtime').style.display = '';
                    document.getElementById('camera-overtime').style.display = 'none';
                    document.getElementById('output-overtime').style.display = 'none';
                
                    video_overtime = document.getElementById('video-overtime');
                    var tracks = video_overtime?.srcObject?.getTracks();
                    tracks?.forEach(track => track.stop());
                    video_overtime.srcObject = null;
                });
            });
        </script>

        <script>
            $('body').on('click', '#clock_in', async function() {
                const { latitude, longitude, accuracy } = await getLocation();


                const latElement = document.getElementById("latitude");
                const longElement = document.getElementById("longitude");
                const accElement = document.getElementById("accuracy");

                if (latElement) {
                    latElement.value = latitude;
                }
                if (longElement) {
                    longElement.value = longitude
                }
                if (accElement) {
                    accElement.value = accuracy;
                }

                // Submit the form
                $('#clock-in-form').submit();
            });

            $('body').on('click', '#clock_out', async function() {
                const { latitude, longitude, accuracy } = await getLocation();

                const latOutElement = document.getElementById("latitude_out");
                const longOutElement = document.getElementById("longitude_out");
                const accOutElement = document.getElementById("accuracy_out");
                if (latOutElement) {
                    latOutElement.value = latitude;
                }
                if (longOutElement) {
                    longOutElement.value = longitude
                }
                if (accOutElement) {
                    accOutElement.value = accuracy;
                }

                // Submit the form
                $('#clock-out-form').submit();
            });
        </script>
    @endif
@endpush
