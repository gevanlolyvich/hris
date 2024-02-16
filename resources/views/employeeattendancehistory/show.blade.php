@extends('layouts.admin')

@section('page-title')
    {{__('Employee')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employeeattendancehistory.index') }}">{{ __('Employee History List') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee History') }}</li>
@endsection

@section('action-button')
@endsection

@push('css-page')
    <style>
        #openStreetMapContainer {
            height: 400px; /* You can adjust the height as needed */
            width: 100%;
        }
    </style>
@endpush

@push('script-page')
    <script>
        $('input[name="type"]:radio').on('change', function(e) {
            var type = $(this).val();

            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');
                $('.date').addClass('d-none');
                $('.date').removeClass('d-block');
            } else {
                $('.date').addClass('d-block');
                $('.date').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });

        $('input[name="type"]:radio:checked').trigger('change');
    </script>

    <script>
        $(document).ready(function() {
            var map = null;
            var mapHome = null;
            var imageSrc = null;
            var notes = null;

            let customIcon = L.icon({
                iconUrl: 'https://cdn4.iconfinder.com/data/icons/leto-most-searched-mix-8/64/__business_office_building-256.png',
                // shadowUrl: 'http://leafletjs.com/examples/custom-icons/leaf-shadow.png',
                
                iconSize:     [40, 40], // size of the icon
                // shadowSize:   [50, 64], // size of the shadow
                iconAnchor:   [36, 17], // point of the icon which will correspond to marker's location
                // shadowAnchor: [4, 62],  // the same for the shadow
                // popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
            });

            $('body').on('click', '.map-link', function() {
                var coordinates = $(this).data('coordinates').split(', ');
                var nearCoordinate = $(this).data('near-coordinate').split(', ');
                var nearName = $(this).data('near-name');
                var nearRadius = $(this).data('near-radius');
                var employeeName = $(this).data('employee');
                notes = $(this).data('note');
                var attendanceType = $(this).data('type');

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

                    if (attendanceType) {
                        document.getElementById('modal-type').style.display = '';
                        document.getElementById('type-value').value = attendanceType;
                    } else {
                        document.getElementById('modal-type').style.display = 'none';
                        document.getElementById('type-value').value = '';
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
                    marker.bindTooltip(employeeName, { permanent: true, direction: 'top', offset: [-15, -15] }).openTooltip();
                
                    // Add a circle with the converted radius
                    var circle = L.circle([coordinates[0], coordinates[1]], {
                        color: 'blue',
                        fillColor: '#f0023',
                        fillOpacity: 0.2,
                        radius: radius,
                    }).addTo(map);
                    
                    if (nearCoordinate.length > 1) {
                        var marker2 = L.marker([nearCoordinate[0], nearCoordinate[1]], {icon: customIcon}).addTo(map);
                        marker2.bindTooltip(nearName, { permanent: true, direction: 'top', offset: [-15, -15] }).openTooltip();
                        var circle2 = L.circle([nearCoordinate[0], nearCoordinate[1]], {
                            color: 'red',
                            fillColor: '#f0023',
                            fillOpacity: 0.5,
                            radius: nearRadius,
                        }).addTo(map);
                    }
                });
            });
            
            $('body').on('click', '.home-coordinate', function() {
                var coordinates = $(this).data('coordinates').split(', ');

                // Convert the radius string to a number
                var radius = parseFloat(coordinates[2]);

            
                // Open the modal
                $('#openStreetMapHomeModal').modal('show');
            
                // Initialize the map after the modal is fully shown
                $('#openStreetMapHomeModal').on('shown.bs.modal', function () {
                    // If a map already exists, remove it
                    if (mapHome !== null) {
                        mapHome.remove();
                    }

                    mapHome = L.map('openStreetMapHomeContainer').setView([coordinates[0], coordinates[1]], 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(mapHome);
                
                    // Add a marker for the location
                    var marker = L.marker([coordinates[0], coordinates[1]]).addTo(mapHome);
                
                    // Add a circle with the converted radius
                    var circle = L.circle([coordinates[0], coordinates[1]], {
                        color: 'blue',
                        fillColor: '#f0023',
                        fillOpacity: 0.2,
                        radius: radius,
                    }).addTo(mapHome); 
                });
            });
            
        });
    </script>
@endpush

@section('content')
    <div class="modal fade" id="openStreetMapModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('Clock In / Out Data')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding-top: 0.35rem">
                    <div class="row text-center mx-auto">
                        <div class="col" style="display: none;" id="modal-note">
                            <div class="text-center mx-auto">
                                <strong>{{__('Notes')}}</strong>
                                <textarea class="form-control mb-3 mt-1" name="note-value" id="note-value" rows="2" disabled></textarea>
                            </div>
                        </div>
                        <div class="col" style="display: none;" id="modal-type">
                            <div class="text-center mx-auto">
                                <strong>{{__('Type')}}</strong>
                                <textarea class="form-control mb-3 mt-1" name="note-value" id="type-value" rows="2" disabled></textarea>
                            </div>
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

    <div class="modal fade" id="openStreetMapHomeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('Coordinate')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding-top: 0.35rem">
                    <div class="text-center mx-auto">
                        <div id="openStreetMapHomeContainer" style="height: 400px; border-radius: 5%" class="mt-1"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body fulls-card p-3 align-items-center">
                    <div class="row text-center">
                        <div class="col">
                            <h6 style="padding: 10px 0;margin-bottom: 0px">{{ $employee->name }}</h6> 
                        </div>
                        <div class="col">
                            <h6 style="padding: 10px 0;margin-bottom: 0px">{{ ucwords($employee?->employeeType?->name ?? '-') }}</h6>
                        </div>
                        <div class="col">
                            <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getBranch($employee->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : '-' }}</h6>
                        </div>
                        <div class="col">
                            <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '-' }}</h6>
                        </div>
                        <div class="col">
                            <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '-' }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(array('route' => array('employeeattendancehistory.show', $id),'method'=>'get','id'=>'employeeattendancehistory_filter')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-3">
                                    <label class="form-label">{{__('Type')}}</label>
                                    <br>
                                    <div class="form-check form-check-inline form-group">
                                        <input type="radio" id="monthly" value="monthly" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='monthly' ?'checked':'checked'}}>
                                        <label class="form-check-label" for="monthly">{{__('Monthly')}}</label>
                                    </div>
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="daily" value="daily" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='daily' ?'checked':''}}>
                                            <label class="form-check-label" for="daily">{{__('Daily')}}</label>
                                        </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                    <div class="btn-box">
                                        {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                        {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control month-btn'))}}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'),['class'=>'form-label'])}}
                                        {{ Form::date('date',isset($_GET['date'])?$_GET['date']:'', array('class' => 'form-control month-btn')) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('employeeattendancehistory_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{route('employeeattendancehistory.show', $id)}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            {{-- Attendance --}}
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="card">
                        <div class="card-header card-body employee-detail-body fulls-card table-border-style">
                            <div class="row">
                                <div class="col-10">
                                    <h5>{{__('Attendance')}}</h5>
                                </div>
                                <div class="col-2">
                                    <a href="{{ route('report.monthlyAttendace.export', ['url' => url()->full()]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Export Employee Attendance Data') }} Excel">
                                        <i class="ti ti-file-export"></i>
                                    </a>
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Clock In') }}</th>
                                            <th>{{ __('Clock Out') }}</th>
                                            <th>{{ __('Late') }}</th>
                                            <th>{{ __('Early Leaving') }}</th>
                                            <th>{{ __('Work Hours') }}</th>
                                            <th>{{ __('Valid Attendance') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attendanceEmployee as $attendance)
                                            <tr>
                                                <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                                <td>{{ $attendance->status }}</td>
                                                <!-- Modify Clock In and Clock Out columns in your table -->
                                                <td>
                                                    @if ($attendance->coord_in)
                                                        <a href="#" class="btn btn-primary btn-sm map-link"
                                                            data-employee="{{ $attendance->employee->name }}"
                                                            data-coordinates="{{ $attendance->coord_in }}"
                                                            data-image="{{ $attendance->picture_in }}"
                                                            data-type="{{ $attendance->attendance_type?->name ?? '-' }}"
                                                            data-near-coordinate="{{ $attendance->location_in_coordinate }}"
                                                            data-near-name="{{ $attendance->location_in_address }}"
                                                            data-near-radius="{{ $attendance->location_in_radius }}"
                                                            data-note="{{ $attendance->note }}">
                                                            <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                                        </a>
                                                    @else
                                                        <a href="#" class="btn btn-primary btn-sm map-link disabled" data-coordinates="{{ $attendance->coord_in }}" data-image="{{ $attendance->picture_in }}">
                                                            <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($attendance->coord_out)
                                                        <a href="#" class="btn btn-info btn-sm map-link"
                                                            data-employee="{{ $attendance->employee->name }}"
                                                            data-coordinates="{{ $attendance->coord_out }}"
                                                            data-near-coordinate="{{ $attendance->location_out_coordinate }}"
                                                            data-near-name="{{ $attendance->location_out_address }}"
                                                            data-near-radius="{{ $attendance->location_out_radius }}"
                                                            data-image="{{ $attendance->picture_out }}">
                                                            <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                                        </a>
                                                    @else
                                                        <a href="#" class="btn btn-info btn-sm map-link text-center disabled">
                                                            <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $attendance->late }}</td>
                                                <td class="text-center">{{ $attendance->early_leaving }}</td>
                                                <td class="text-center">{{ $attendance->work_hours }}</td>
                                                <td class="text-center">
                                                    @if ($attendance->is_valid)
                                                        <div class="action-btn bg-success ms-2">
                                                            <i class="ti ti-checks text-white text-white"></i>
                                                        </div>
                                                    @else
                                                        <div class="action-btn bg-danger ms-2">
                                                            <i class="ti ti-alert-triangle text-white text-white"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <hr>
                            <div class="text-center mt-4">
                                <h6>Total {{ __('Attendance') }} : {{ count($attendanceEmployee) }} {{ __('Attendance') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Work Hours And Overtime --}}
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card ">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{__('Work Hours')}}</h5>
                            <hr>
                            <div class="table-responsive">
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Work Hours') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendanceEmployee as $attendance)
                                        @if ($attendance->work_hours !== '00:00:00')
                                            <tr>
                                                <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                                <!-- Modify Clock In and Clock Out columns in your table -->
                                                <td>{{ $attendance->work_hours }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                            <div class="text-center mt-4">
                            <h6>Total: {{ $total_workhours['hours'] }} {{__('Hours')}}  {{ $total_workhours['minutes'] }} {{__(' Minute')}}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card ">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{__('Overtime')}}</h5>
                            <hr>
                            <div class="table-responsive">
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Overtime') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($overtimes as $overtime)
                                        <tr>
                                            <td>{{ \Auth::user()->dateFormat($overtime->date) }}</td>
                                            <!-- Modify Clock In and Clock Out columns in your table -->
                                            <td>{{ $overtime->type ?? 'hourly' }}</td>
                                            <td>{{ $overtime->total }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                            <div class="text-center mt-4">
                                @if ($overtime_exceed_limit)
                                    <h6>Total: {{ $max_overtime }} {{__('Hours')}} 0 {{__(' Minute')}} | {{ __('Maximum Overtime')}}</h6>
                                @else
                                    <h6>Total: {{ $total_overtime['hours'] }} {{__('Hours')}}  {{ $total_overtime['minutes'] }} {{__(' Minute')}}</h6>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Late And Early Leaving --}}
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header card-body employee-detail-body fulls-card table-border-style">
                            <h5>{{__('Late')}}</h5>
                            <hr>
                            <br>
                            <div class="table-responsive">
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Late') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendanceEmployee as $attendance)
                                        <tr>
                                            <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                            <!-- Modify Clock In and Clock Out columns in your table -->
                                            <td>{{ $attendance->late }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                            <div class="text-center mt-4">
                                <h6>Total: {{ $total_late['hours'] }} {{__('Hours')}}  {{ $total_late['minutes'] }} {{__(' Minute')}}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{__('Early Leaving')}}</h5>
                            <hr>
                            <br>
                            <div class="table-responsive">
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Early Leaving') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendanceEmployee as $attendance)
                                        <tr>
                                            <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                            <!-- Modify Clock In and Clock Out columns in your table -->
                                            <td>{{ $attendance->early_leaving }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                            <div class="text-center mt-4">
                            <h6>Total: {{ $total_early['hours'] }} {{__('Hours')}}  {{ $total_early['minutes'] }} {{__(' Minute')}}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shift Changes And Address Changes --}}
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header card-body employee-detail-body fulls-card table-border-style">
                            <h5>{{__('Shift Changes')}}</h5>
                            <hr>
                            <br>
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Shift') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($shift_changes as $shiftChange)
                                            <tr>
                                                <td>{{ \Auth::user()->dateFormat($shiftChange->created_at) }}</td>
                                                <td>{{ $shiftChange?->shiftType?->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header card-body employee-detail-body fulls-card table-border-style">
                            <h5>{{__('Home Address Changes')}}</h5>
                            <hr>
                            <br>
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Address') }}</th>
                                            <th>{{ __('Coordinate') }}</th>
                                        </tr>
                                    </thead> 
                                    <tbody>
                                        @foreach ($home_changes as $home)
                                            <tr>
                                                <td>{{ \Auth::user()->dateFormat($home->created_at) }}</td>
                                                <td>{{ $home->address }}</td>
                                                <td>
                                                    @if(!empty($home->coordinate))
                                                        <a href="#" class="btn btn-primary btn-sm home-coordinate" data-coordinates="{{ $home->coordinate }}">
                                                            <i class="fa fa-solid fa-map-pin"></i>
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
                </div>
            </div>

            {{-- Employee Transfer And Training --}}
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header card-body employee-detail-body fulls-card table-border-style">
                            <h5>{{__('Transfer')}}</h5>
                            <hr>
                            <br>
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Branch') }}</th>
                                            <th>{{ __('Department') }}</th>
                                            <th>{{ __('Designation') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transfers as $transfer)
                                            <tr>
                                                <td>{{ $transfer->transfer_date }}</td>
                                                <td>{{ $transfer?->branch?->name ?? '-' }}</td>
                                                <td>{{ $transfer?->department?->name ?? '-' }}</td>
                                                <td>{{ $transfer?->designation?->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header card-body employee-detail-body fulls-card table-border-style">
                            <h5>{{__('Training')}}</h5>
                            <hr>
                            <br>
                            <div class="table-responsive">
                                <table class="table" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Cost') }}</th>
                                            <th>{{ __('Description') }}</th>
                                        </tr>
                                    </thead> 
                                    <tbody>
                                        @foreach ($trainings as $training)
                                            <tr>
                                                <td>{{ $training->start_date }}</td>
                                                <td>{{ $training->end_date }}</td>
                                                <td>{{ $training->training_cost }}</td>
                                                <td>{{ $training->description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
