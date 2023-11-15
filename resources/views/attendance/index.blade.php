@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Attendance List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance List') }}</li>
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

    <!-- Add this script at the end of your Blade template -->
    <script>
        $(document).ready(function() {
            var map = null;

            $('.map-link').click(function() {
                var coordinates = $(this).data('coordinates').split(', ');

                $('#clockImage').attr('src', $(this).data('image'))
                console.log($(this).data('image'));
            
                // Convert the radius string to a number
                var radius = parseFloat(coordinates[2]);

            
                // Open the modal
                $('#openStreetMapModal').modal('show');
            
                // Initialize the map after the modal is fully shown
                $('#openStreetMapModal').on('shown.bs.modal', function () {
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
@endpush
@section('action-button')
<!-- <a class="btn btn-sm btn-primary collapsed" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button"
        aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{ __('Filter') }}">
        <i class="ti ti-filter"></i>
    </a> -->
@endsection
@section('content')
<!-- Update the modal structure in your Blade template -->
<div class="modal fade" id="openStreetMapModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Clock In / Out Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="clock-images mx-d-flex flex-column align-items-center">
                    <div class="text-center mx-auto">
                        <strong>Clock In / Out Image Capture:</strong>
                        <br>
                        <img id="clockImage" src="" alt="Clock In Out Image" style="max-width: 100%; max-height: 300px; border-radius: 5%" class="mb-2">
                    </div>
                </div>
                <div id="openStreetMapContainer" style="height: 400px; border-radius: 5%"></div>
            </div>
        </div>
    </div>
</div>


<div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('attendanceemployee.index'),'method'=>'get','id'=>'attendanceemployee_filter')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">

                                    <div class="col-3">
                                        <label class="form-label">{{__('Type')}}</label> <br>

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
                                    @if(\Auth::user()->type != 'employee')
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('branch', __('Branch'),['class'=>'form-label'])}}
                                                {{ Form::select('branch', $branch,isset($_GET['branch'])?$_GET['branch']:'', array('class' => 'form-control select')) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('department', __('Department'),['class'=>'form-label'])}}
                                                {{ Form::select('department', $department,isset($_GET['department'])?$_GET['department']:'', array('class' => 'form-control select')) }}
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">

                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('attendanceemployee_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        <a href="{{route('attendanceemployee.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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



    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                {{-- @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif --}}
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Shift') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Clock In') }}</th>
                                <th>{{ __('Clock Out') }}</th>
                                <th>{{ __('Late') }}</th>
                                <th>{{ __('Early Leaving') }}</th>
                                <th>{{ __('Overtime') }}</th>
                                <th>{{ __('Work Hours') }}</th>
                                {{-- @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif --}}
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($attendanceEmployee as $attendance)
                                <tr>
                                    {{-- @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}</td>
                                    @endif --}}
                                    <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}</td>
                                    <td>{{ $attendance->employee->shift_type->name }}</td>
                                    <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                    <td>{{ $attendance->status }}</td>
                                    <!-- Modify Clock In and Clock Out columns in your table -->
                                    <td>
                                        @if ($attendance->coord_in)
                                            <a href="#" class="btn btn-primary btn-sm map-link" data-coordinates="{{ $attendance->coord_in }}" data-image="{{ $attendance->picture_in }}">
                                                <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                            </a>
                                        @else
                                            {{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($attendance->coord_out)
                                        <a href="#" class="btn btn-info btn-sm map-link" data-coordinates="{{ $attendance->coord_out }}" data-image="{{ $attendance->picture_out }}">
                                            <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                        </a>
                                        @else
                                        <a href="#" class="btn btn-info btn-sm map-link text-center disabled">
                                            <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                        </a>
                                        @endif
                                    </td>
                                    <td>{{ $attendance->late }}</td>
                                    <td>{{ $attendance->early_leaving }}</td>
                                    <td>{{ $attendance->overtime }}</td>
                                    <td>{{ $attendance->work_hours }}</td>
                                    <td class="Action">
                                        @if ((Gate::check('Edit Attendance') || Gate::check('Delete Attendance')) && $emp !== $attendance->employee_id)
                                            <span>
                                                 {{-- @can('Validate Attendance') --}}
                                                @if (!$attendance->is_valid)
                                                <div class="action-btn bg-info ms-2">
                                                    {!! Form::open(['method' => 'PATCH', 'route' => ['attendanceemployee.validateAttendance', $attendance->id], 'id' => 'employee-form-' . $attendance->id]) !!}
                                                    <button type="button" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="Validate" aria-label="Validate">
                                                        <i class="ti ti-checks text-white text-white"></i>
                                                    </button>
                                                    {!! Form::close() !!}
                                                </div>
                                                @else
                                                <div class="action-btn bg-success ms-2">
                                                    <button type="submit" class="mx-3 btn btn-sm align-items-center"
                                                        data-bs-toggle="tooltip" title="Already Validated" aria-label="Already Validated" disabled>
                                                        <i class="ti ti-checks text-white text-white"></i>
                                                    </button>
                                                </div>
                                                @endif
                                                
                                            {{-- @endcan --}}

                                                @can('Edit Attendance')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                            data-url="{{ URL::to('attendanceemployee/' . $attendance->id . '/edit') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Attendance') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Attendance')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['attendanceemployee.destroy', $attendance->id], 'id' => 'delete-form-' . $attendance->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                            aria-label="Delete"><i
                                                                class="ti ti-trash text-white text-white"></i></a>
                                                        </form>
                                                    </div>
                                                @endcan
                                            </span>
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
@endsection