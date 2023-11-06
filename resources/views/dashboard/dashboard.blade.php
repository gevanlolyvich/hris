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
        <div class="col-xxl-6">
            <div class="card" style="height: 947px;">
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
        <div class="col-xxl-6">
            <div class="card"style="height: 462px;">
                <div class="card-header">
                    <h5>{{ __('Mark Attandance') }}</h5>
                </div>
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
                            const accElement = document.getElementById("accuracy_out");
                            if (latOutElement) {
                                latOutElement.value = latitude;
                            }
                            if (longOutElement) {
                                longOutElement.value = longitude
                            }
                            if (accElement) {
                                accElement.value = accuracy;
                            }
                            clockOutButton.disabled = false;
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
                    });
                </script>                
                <div class="card-body">
                    @if ($officeTime['is_working'])
                        <h6>{{ __($officeTime['name'])}}</h6>
                        <p class="text-muted pb-0-5">
                            {{ __('Office Time: ' . $officeTime['startTime'] . ' to ' . $officeTime['endTime']) }}
                        </p>
                        {{-- Condition for showing employee already clock in or not --}}
                        @if ($yesterdayOfficeTime['is_cross_day'] && !empty($yesterdayEmployeeAttendance) && $yesterdayEmployeeAttendance->clock_out === $yesterdayOfficeTime['default_clock_out'])
                            <h5 class="text-danger pb-0-5">{{ __("Already Clock In At {$yesterdayEmployeeAttendance->date} {$yesterdayEmployeeAttendance->clock_in} ()")}}</h5>
                        @elseif (empty($employeeAttendance))
                        @else
                            <h5 class="text-danger pb-0-5">{{ __("Already Clock In At {$employeeAttendance->date} {$employeeAttendance->clock_in} ()")}}</h5>
                        @endif
                    @else
                        <h6 class="text-muted pb-0-5">
                            {{ __('No Working Hour') }}
                        </h6>
                    @endif
                    <div class="row">
                        {{-- Show form for attendance type and notes --}}                      
                        <div class="col-md-12">
                            {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post', 'id' => 'clock-in-form']) }}
                            <div class="form-group">
                                {!! Form::label('attendance_type', __('Attendance Type'), ['class' => 'col-form-label']) !!}
                                {{ Form::select('attendance_type', $attendance_type, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder'=>'Choose attendance type']) }}
                            </div>
                            <div class="form-group">
                                {{-- {!! Form::label('notes', __('Notes'), ['class' => 'col-form-label']) !!} --}}
                                {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => '2' ,'placeholder'=>'Enter notes for clock in']) !!}
                            </div>
                            <input type="hidden" name="latitude" id="latitude" value="0">
                            <input type="hidden" name="longitude" id="longitude" value="0">
                            <input type="hidden" name="accuracy" id="accuracy" value="0">
                        </div>
                        <div class="col-md-6 float-right border-right">
                            {{-- @if (empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00') --}}
                            @if ($yesterdayOfficeTime['is_cross_day'] && !empty($yesterdayEmployeeAttendance) && $yesterdayEmployeeAttendance->clock_out === $yesterdayOfficeTime['default_clock_out'])
                                <button type="submit" value="0" name="in" id="clock_in"
                                    class="btn btn-primary disabled" disabled>{{ __('CLOCK IN') }}</button>
                            @elseif (empty($employeeAttendance))
                                <button type="submit" value="0" name="in" id="clock_in" onclick="getLocation()"
                                    class="btn btn-primary" disabled>{{ __('CLOCK IN') }}</button>
                            @else
                                <button type="submit" value="0" name="in" id="clock_in"
                                    class="btn btn-primary disabled" disabled>{{ __('CLOCK IN') }}</button>
                            @endif
                            {{ Form::close() }}
                        </div>                                                    
                        <div class="col-md-6 float-left">
                            {{-- @if (!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00') --}}
                            @if (!empty($yesterdayEmployeeAttendance) && $yesterdayEmployeeAttendance->clock_out === $yesterdayOfficeTime['default_clock_out'])
                                {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $yesterdayEmployeeAttendance->id], 'method' => 'PUT']) }}
                                <input type="hidden" name="latitude" id="latitude_out" value="0">
                                <input type="hidden" name="longitude" id="longitude_out" value="0">
                                <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                                <button type="submit" value="1" name="out" id="clock_out" onclick="getLocation()"
                                    class="btn btn-danger">{{ __('CLOCK OUT') }}</button>
                            @elseif ($officeTime['is_cross_day'] && !empty($employeeAttendance) && $employeeAttendance->clock_out === $officeTime['default_clock_out'])
                                {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $employeeAttendance->id], 'method' => 'PUT']) }}
                                <input type="hidden" name="latitude" id="latitude_out" value="0">
                                <input type="hidden" name="longitude" id="longitude_out" value="0">
                                <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                                <button type="submit" value="1" name="out" id="clock_out" onclick="getLocation()"
                                    class="btn btn-danger">{{ __('CLOCK OUT') }}</button>
                            @elseif (!$officeTime['is_cross_day'] && !empty($employeeAttendance) && $employeeAttendance->clock_out === '00:00:00')
                                {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $employeeAttendance->id], 'method' => 'PUT']) }}
                                <input type="hidden" name="latitude" id="latitude_out" value="0">
                                <input type="hidden" name="longitude" id="longitude_out" value="0">
                                <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                                <button type="submit" value="1" name="out" id="clock_out" onclick="getLocation()"
                                    class="btn btn-danger">{{ __('CLOCK OUT') }}</button>
                            @else
                                <button type="submit" value="1" name="out" id="clock_out"
                                    class="btn btn-danger disabled" disabled>{{ __('CLOCK OUT') }}</button>
                            @endif
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" style="height: 462px;">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Meeting schedule') }}</h5>
                </div>
                <div class="card-body" style="height: 320px">
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



@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>

    @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr')
    <script type="text/javascript">
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calender_type = $('#calender_type :selected').val();
            console.log(calender_type);
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
                    'calender_type': calender_type
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
            console.log(calender_type);
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
                    'calender_type': calender_type
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
@endpush
