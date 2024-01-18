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

    {{-- <script>
        $(document).ready(function () {
            let employeeData = @json(session('employee'));

            if (employeeData) {
                // do something here with employeeData.name
                let searchTable = document.getElementsByClassName('dataTable-input');

                if (searchTable?.length) {
                    searchTable[0].value = employeeData.name;
                    var event = new KeyboardEvent('keydown', {
                        key: 'Enter',
                        bubbles: true,
                        cancelable: true,
                        keyCode: 13,
                        code: 'Enter',
                    });
                    searchTable[0].dispatchEvent(event);
                    $(searchTable[0]).trigger('input');
                    searchTable[0].trigger('input');
                }
            }
        })
    </script> --}}
@endpush

@section('action-button')
    <a href="{{ route('attendanceemployee.export', ['url' => url()->full()]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>
@endsection

@section('content')
<!-- Update the modal structure in your Blade template -->
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

<div class="col-sm-12">
    <div class=" mt-2 " id="multiCollapseExample1">
        <div class="card">
            <div class="card-body">
            {{ Form::open(array('route' => array('attendanceemployee.index'),'method'=>'get','id'=>'attendanceemployee_filter')) }}
                <div class="row align-items-center justify-content-end">
                    <div class="col-xl-10">
                        <div class="row">
                            <div class="col-3">
                                <label class="form-label">{{__('Type')}}</label>
                                <br>
                                <div class="form-check form-check-inline form-group">
                                    <input type="radio" id="monthly" value="monthly" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='monthly' ?'checked':''}}>
                                    <label class="form-check-label" for="monthly">{{__('Monthly')}}</label>
                                </div>
                                    <div class="form-check form-check-inline form-group">
                                        <input type="radio" id="daily" value="daily" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='daily' ?'checked':'checked'}}>
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
                                    {{ Form::date('date',isset($_GET['date'])?$_GET['date']:date('Y-m-d'), array('class' => 'form-control month-btn')) }}
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
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Shift') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Clock In') }}</th>
                            <th>{{ __('Clock Out') }}</th>
                            <th>{{ __('Late') }}</th>
                            <th>{{ __('Early Leaving') }}</th>
                            <th>{{ __('Work Hours') }}</th>
                            <th>{{ __('Validation') }}</th>
                            @if ((Gate::check('Edit Attendance') || Gate::check('Delete Attendance')))
                                <th width="200px">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendanceEmployee as $attendance)
                            @if ((session('employee') && session('employee')->name == (!empty($attendance->employee) ? $attendance->employee->name : '')) || empty(session('employee')))
                                <tr>
                                    <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}</td>
                                    <td>{{ $attendance->shift_type?->name ?? $attendance->employee->shift_type->name }}</td>
                                    <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                    <td>{{ $attendance->status }}</td>
                                    <!-- Modify Clock In and Clock Out columns in your table -->
                                    <td>
                                        @if ($attendance->coord_in)
                                            <a href="#" class="btn btn-primary btn-sm map-link" data-coordinates="{{ $attendance->coord_in }}" data-image="{{ $attendance->picture_in }}" data-note="{{ $attendance->note }}">
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
                                            <a href="#" class="btn btn-info btn-sm map-link" data-coordinates="{{ $attendance->coord_out }}" data-image="{{ $attendance->picture_out }}">
                                                <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-info btn-sm map-link text-center disabled">
                                                <i class="fa fa-solid fa-map-pin"></i> {{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span @if($attendance->late != '00:00:00' && strpos($attendance->early_leaving, '-') === false) class="btn btn-danger btn-sm text-center disabled" @endif>
                                            {{ $attendance->late }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span @if($attendance->early_leaving != '00:00:00' && strpos($attendance->early_leaving, '-') === false) class="btn btn-danger btn-sm text-center disabled" @endif>
                                            {{ $attendance->early_leaving }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span @if(strtotime('08:00:00') > strtotime($attendance->work_hours) && $attendance->status == 'Present')) class="btn btn-danger btn-sm text-center disabled" @endif>
                                            {{ $attendance?->work_hours ?? '00:00:00' }}
                                        </span>
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @if (!$attendance->is_valid && \Auth::user()?->employee?->id != $attendance->employee_id)
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'PATCH', 'route' => ['attendanceemployee.validateAttendance', $attendance->id], 'id' => 'employee-form-' . $attendance->id]) !!}
                                                    <button type="button" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-original-title="{{__('Invalid / Required Validation')}}"
                                                        title="{{__('Invalid / Required Validation')}}"
                                                        style="pointer-events: auto">
                                                        <i class="ti ti-alert-triangle text-white text-white"></i>
                                                    </button>
                                                    {!! Form::close() !!}
                                                </div>
                                            @elseif ($attendance->is_valid)
                                                <div class="action-btn bg-success ms-2">
                                                    <button type="submit" class="mx-3 btn btn-sm align-items-center"
                                                        data-bs-toggle="tooltip" disabled
                                                        data-bs-original-title="{{__('Valid')}}"
                                                        title="{{__('Valid')}}"
                                                        style="pointer-events: auto">
                                                        <i class="ti ti-checks text-white text-white"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="action-btn bg-danger ms-2">
                                                    <button type="button" class="mx-3 btn btn-sm align-items-center disabled" disabled
                                                        data-bs-toggle="tooltip" title="{{__('Invalid / Required Validation')}}"
                                                        data-bs-original-title="{{__('Invalid / Required Validation')}}"
                                                        style="pointer-events: auto">
                                                        <i class="ti ti-alert-triangle text-white text-white"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </span>
                                    </td>
                                    @if ((Gate::check('Edit Attendance') || Gate::check('Delete Attendance')) && $emp !== $attendance->employee_id)
                                        <td class="Action">
                                            <span>
                                                @if ((Gate::check('Edit Attendance') || Gate::check('Delete Attendance')) && $emp !== $attendance->employee_id)
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
                                                @endif
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection