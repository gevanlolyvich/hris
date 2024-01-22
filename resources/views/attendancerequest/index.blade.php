
@extends('layouts.admin')

@section('page-title')
    {{ __('Request Attendance') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Request Attendance') }}</li>
@endsection

@section('action-button')
    {{-- <a href="{{ route('attendancerequest.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a> --}}

    {{-- <a href="{{ route('leave.calender') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a> --}}

    @can('Create Request Attendance')
        <a href="#" data-url="{{ route('attendancerequest.create') }}" data-ajax-popup="true" data-title="{{ __('Create New Request Attendance') }}"
            data-size="lg" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(array('route' => array('attendancerequest.index'),'method'=>'get','id'=>'employeeattendancehistory_filter')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-12">
                            <div class="row">
                                <div class="form-group col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                    {{ Form::label('branch_id', __('Select Branch'), ['class' => 'form-label']) }}
                                    {{ Form::select('branch_id', $branch, isset($_GET['branch_id'])?$_GET['branch_id']:null, ['class' => 'form-control select2', 'placeholder' => __('Select Branch')]) }}
                                </div>
                                <div class="form-group col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                    {{ Form::label('department_id', __('Select Department'), ['class' => 'form-label']) }}
                                    <div class="department_div btn-box">
                                        {{ Form::select('department_id', !empty($department) ? $department : [], isset($_GET['department_id'])?$_GET['department_id']:null, ['class' => 'form-control select2 department_id', 'placeholder' => __('Select Department')]) }}
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
                                    <a href="{{route('attendancerequest.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
                {{-- <h5> </h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Start Time') }}</th>
                                <th>{{ __('End Time') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Document') }}</th>
                                {{-- <th>{{ __('Leave Reason') }}</th> --}}
                                <th>{{ __('status') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendance_requests as $attendance_request)
                                <tr>
                                    <td>{{ !empty(\Auth::user()->getEmployee($attendance_request->employee_id)) ? \Auth::user()->getEmployee($attendance_request->employee_id)->name : '' }}</td>
                                    <td>{{ date('d M Y', strtotime($attendance_request->date)) }}</td>
                                    <td>{{ $attendance_request->start_time }}</td>
                                    <td>{{ $attendance_request->end_time }}</td>
                                    <td>{{ $attendance_request->reason }}</td>                                   
                                    <td>
                                        @if ($attendance_request->docs)
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ $attendance_request->docs }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-file text-white"></i>
                                                </a>
                                            </div>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if (is_null($attendance_request->is_approved))
                                            <div class="badge bg-warning p-2 px-3 rounded">Waiting</div>
                                        @endif
                                        @if ($attendance_request->is_approved == 1)
                                            <div class="badge bg-success p-2 px-3 rounded">Approved</div>
                                        @endif
                                        @if ($attendance_request->is_approved === 0)
                                            <div class="badge bg-danger p-2 px-3 rounded">Rejected</div>
                                        @endif
                                    </td>

                                    <td class="Action">
                                        <span>
                                            @if (\Auth::user()->type == 'employee')
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                        data-url="{{ URL::to('attendancerequest/' . $attendance_request->id . '/action') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Attendance Request Action') }}"
                                                        data-bs-original-title="{{ __('Manage Attendance Request') }}">
                                                        <i class="ti ti-caret-right text-white"></i>
                                                    </a>
                                                </div>
                                                @if (($attendance_request->created_by == Auth::user()->id || $attendance_request->employee_id == Auth::user()->employee->id || Auth::user()->type != 'employee') && $attendance_request->is_approved != 1)
                                                    @can('Edit Request Attendance')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('attendancerequest/' . $attendance_request->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Attendance Request') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Request Attendance')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['attendancerequest.destroy', $attendance_request->id], 'id' => 'delete-form-' . $attendance_request->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                                aria-label="Delete"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            </form>
                                                        </div>
                                                    @endcan
                                                @endif
                                            @else
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                        data-url="{{ URL::to('attendancerequest/' . $attendance_request->id . '/action') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Attendance Request Action') }}"
                                                        data-bs-original-title="{{ __('Manage Attendance Request') }}">
                                                        <i class="ti ti-caret-right text-white"></i>
                                                    </a>
                                                </div>
                                                @if ($attendance_request->is_approved != 1)
                                                    @can('Edit Request Attendance')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                                data-url="{{ URL::to('attendancerequest/' . $attendance_request->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Attendance Request') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Request Attendance')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['attendancerequest.destroy', $attendance_request->id], 'id' => 'delete-form-' . $attendance_request->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                                aria-label="Delete"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            </form>
                                                        </div>
                                                    @endcan
                                                @endif
                                            @endif
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
@endsection

@push('script-page')
    <script>
        $(document).ready(function () {
            $('#commonModal').on('shown.bs.modal', function () {
                $('.status').on('click', function () {
                    $('#commonModal').modal('hide');

                    var buttonValue = $(this).data("status");
                    $("#hiddenStatus").val(buttonValue);
                })
            });
        })
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value="">{{ __('Select Request Attendance Type') }}</option>');

                    $.each(data, function(key, value) {

                        if (value.total_leave == value.days) {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
                        }
                    });

                }
            });
        });
    </script>

    <script>
        $(document).ready(() => {
            $(document).on('change', '[name="myDocument"]', function () {
                const file = document.getElementById('uploadFile');
                file.style.display = '';
                file.style['max-width'] = '';
                document.getElementById('fileName').textContent = this.files[0].name;
            });
        })
    </script>

    <script>
        function getDepartment(branch_id) {
            $.ajax({
                url: '{{ route('department.employee.json') }}',
                type: 'POST',
                data: {
                    "branch_id": branch_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('.designation_id').empty();
                    $('.department_id').empty();
                    var emp_selct = ` <select class="form-control select2  department_id" name="department_id" id="choices-multiple"
                                            placeholder="Select Department" >
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value="" disabled selected>{{ __('Select Department') }}</option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });


                }
            });
        }

        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();
            // console.log({branch_id})
            // $('.designation_id').empty();
            getDepartment(branch_id);
        });
    </script>
@endpush

