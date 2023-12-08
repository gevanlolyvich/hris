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

    @if (\Auth::user()->type == 'employee')
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
                    @if ($yesterdayOfficeTime['is_cross_day'] && !empty($yesterdayEmployeeAttendance) && $yesterdayEmployeeAttendance->clock_out === $yesterdayOfficeTime['default_clock_out'])
                        <h5 class="text-danger pb-0-5">{{ __("Already Clock In At {$yesterdayEmployeeAttendance->date} {$yesterdayEmployeeAttendance->clock_in} WIB")}}</h5>
                    @elseif (empty($employeeAttendance))
                    @else
                        <h5 class="text-danger pb-0-5">{{ __("Already Clock In At {$employeeAttendance->date} {$employeeAttendance->clock_in} WIB")}}</h5>
                    @endif
                @else
                    <h6 class="text-muted pb-0-5">
                        {{ __('No Working Hour') }}
                    </h6>
                @endif
                <div class="row d-flex flex-column align-items-center">
                    {{-- Show form for attendance type and notes --}}                      
                    {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post', 'id' => 'clock-in-form', 'enctype' => 'multipart/form-data']) }}
                    {{ Form::label('picture', __('Picture'), ['class' => 'col-form-label']) }}
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
                        <input type="hidden" name="clockInData" id="clockInData" value="{{ $employeeAttendance }}">
                    </div>
                    <div class="col-md-6 text-center mx-auto mt-1">
                        {{-- @if (empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00') --}}
                        @if ($yesterdayOfficeTime['is_cross_day'] && !empty($yesterdayEmployeeAttendance) && $yesterdayEmployeeAttendance->clock_out === $yesterdayOfficeTime['default_clock_out'] && strtotime(date('Y-m-d H:i:s')) > (strtotime($officeTime['startTime']) - 3600))
                            <button type="submit" value="0" name="in" id="clock_in"
                                class="btn btn-primary btn-lg btn-block disabled" style="width: 150px" disabled>{{ __('CLOCK IN') }}</button>
                        @elseif ($yesterdayOfficeTime['is_cross_day'] && empty($yesterdayEmployeeAttendance) && strtotime(date('Y-m-d H:i:s')) > (strtotime(date('Y-m-d', strtotime('yesterday')) . ' ' . $yesterdayOfficeTime['startTime']) - 3600) && strtotime(date('Y-m-d H:i:s')) < (strtotime($yesterdayOfficeTime['absolute_out'])))
                            <button type="submit" value="0" name="in" id="clock_in" onclick="getLocation()"
                                class="btn btn-primary btn-lg btn-block" style="width: 150px" disabled>{{ __('CLOCK IN') }}</button>
                        @elseif (empty($employeeAttendance) && strtotime(date('Y-m-d H:i:s')) > (strtotime($officeTime['startTime']) - 3600))
                            <button type="submit" value="0" name="in" id="clock_in" onclick="getLocation()"
                                class="btn btn-primary btn-lg btn-block" style="width: 150px" disabled>{{ __('CLOCK IN') }}</button>
                        @else
                            <button type="submit" value="0" name="in" id="clock_in"
                                class="btn btn-primary btn-lg btn-block" style="width: 150px" disabled>{{ __('CLOCK IN') }}</button>
                        @endif
                        {{ Form::close() }}
                    </div>                                                    
                    <div class="col-md-6 text-center mx-auto mt-3">
                        {{-- @if (!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00') --}}
                        {{-- Tambahin validasi abs out terkait kalo dia sudah clock out masih dapat clock out lagi selagi masih dalam waktu AbsOut-nya --}}
                        @if ($yesterdayOfficeTime['is_cross_day'] && $yesterdayEmployeeAttendance && empty($employeeAttendance) && time() < $yesterdayOfficeTime['absolute_out'])
                            {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $yesterdayEmployeeAttendance->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                            <input type="hidden" name="latitude" id="latitude_out" value="0">
                            <input type="hidden" name="longitude" id="longitude_out" value="0">
                            <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                            <input type="hidden" name="picture_out" id="picture_out">
                            <button type="submit" value="1" name="out" id="clock_out" onclick="getLocation()"
                                class="btn btn-danger" style="width: 150px">{{ __('CLOCK OUT') }}</button>
                        @elseif ($employeeAttendance)
                            {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $employeeAttendance->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                            <input type="hidden" name="latitude" id="latitude_out" value="0">
                            <input type="hidden" name="longitude" id="longitude_out" value="0">
                            <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                            <input type="hidden" name="picture_out" id="picture_out">
                            <button type="submit" value="1" name="out" id="clock_out" onclick="getLocation()"
                                class="btn btn-danger" style="width: 150px">{{ __('CLOCK OUT') }}</button>
                        {{-- @elseif (!$officeTime['is_cross_day'] && !empty($employeeAttendance))
                            {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $employeeAttendance->id], 'method' => 'PUT']) }}
                            <input type="hidden" name="latitude" id="latitude_out" value="0">
                            <input type="hidden" name="longitude" id="longitude_out" value="0">
                            <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                            <button type="submit" value="1" name="out" id="clock_out" onclick="getLocation()"
                                class="btn btn-danger" style="width: 150px">{{ __('CLOCK OUT') }}</button> --}}
                        @else
                            <button type="submit" value="1" name="out" id="clock_out"
                                class="btn btn-danger disabled" style="width: 150px" disabled>{{ __('CLOCK OUT') }}</button>
                        @endif
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
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
                                <th>{{ __('Meeting Date') }}</th>
                                <th>{{ __('Meeting Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @foreach ($meetings as $meeting)
                                <tr>
                                    <td>{{ $meeting->title }}</td>
                                    <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                    <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
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
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($announcements as $announcement)
                                    <tr>
                                        <td>{{ $announcement->title }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                        <td>{{ $announcement->description }}</td>
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
                                            <h6 class="m-0">{{ __('Staff') }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-primary">{{ $countUser + $countEmployee }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-ticket"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0">{{ __('Ticket') }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-info"> {{ $countTicket }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-wallet"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0">{{ __('Account Balance') }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-warning">{{ \Auth::user()->priceFormat($accountBalance) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-primary">
                                    <i class="ti ti-cast"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Jobs') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0 text-primary">{{ $activeJob + $inActiveJOb }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-info">
                                    <i class="ti ti-cast"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Active Jobs') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0 text-info"> {{ $activeJob }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-warning">
                                    <i class="ti ti-cast"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Inactive Jobs') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0 text-warning">{{ $inActiveJOb }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- </div> --}}

        {{-- end --}}

        <div class="col-xxl-12">
            <div class="row">
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <h5>{{ __('Meeting schedule') }}</h5>
                        </div>
                        <div class="card-body" style="height: 324px; overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Time') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($meetings as $meeting)
                                            <tr>
                                                <td>{{ $meeting->title }}</td>
                                                <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                                <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <h5>{{ __("Today's Not Clock In") }}</h5>
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
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($announcements as $announcement)
                                    <tr>
                                        <td>{{ $announcement->title }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                        <td>{{ $announcement->description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
    </style>
@endpush

@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script>
        async function getLocation() {
          return new Promise((resolve, reject) => {
            if ("geolocation" in navigator) {
              navigator.geolocation.getCurrentPosition(
                (position) => {
                  const latitude = position.coords.latitude;
                  const longitude = position.coords.longitude;
                  const accuracy = position.coords.accuracy;
                  resolve({ latitude, longitude, accuracy });
                },
                (error) => {
                  if (error.code === 1) {
                    reject(new Error("User denied Geolocation"));
                  } else {
                    reject(error);
                  }
                }
              );
            } else {
              reject(new Error("Geolocation is not supported by your browser."));
            }
          });
        }

        // Automatically call getLocation when the page loads
        window.addEventListener("load", async () => {
          try {
            const { latitude, longitude, accuracy } = await getLocation();

            console.log(`${latitude}, ${longitude}, ${accuracy}`);
      
            const clockInButton = document.getElementById("clock_in");
            const clockOutButton = document.getElementById("clock_out");
            const clockInData = document.getElementById("clockInData");

            if (latitude !== 0 && longitude !== 0 && clockInButton && !clockInData.value) {
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
            const clockInButton = document.getElementById("clock_in");
            if (!clockInButton.disabled) {
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
    
            function startup() {
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
                    })
                    .catch(function(err) {
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
    
                    var data = canvas.toDataURL('image/png');
                    photo.setAttribute('src', data);
                    let pictureIn = document.getElementById('picture');
                    let pictureOut = document.getElementById('picture_out');

                    if (pictureIn) {
                        pictureIn.value = data;
                    }
                    if (pictureOut) {
                        pictureOut.value = data;
                    }
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
@endpush
