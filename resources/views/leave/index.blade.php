@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave ') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('leave.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="{{ route('leave.calender') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a>

    @can('Create Leave')
        <a href="#" data-url="{{ route('leave.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Leave') }}" data-size="lg" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['leave.index'], 'method' => 'get', 'id' => 'employeeattendancehistory_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-12">
                            <div class="row">
                                <div class="form-group col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                    {{ Form::label('branch_id', __('Select Branch'), ['class' => 'form-label']) }}
                                    {{ Form::select('branch_id', $branch, isset($_GET['branch_id']) ? $_GET['branch_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('Select Branch')]) }}
                                </div>
                                <div class="form-group col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                    {{ Form::label('department_id', __('Select Department'), ['class' => 'form-label']) }}
                                    <div class="department_div btn-box">
                                        {{ Form::select('department_id', !empty($department) ? $department : [], isset($_GET['department_id']) ? $_GET['department_id'] : null, ['class' => 'form-control select2 department_id', 'placeholder' => __('Select Department')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('employeeattendancehistory_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                        data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('leave.index') }}" class="btn btn-sm btn-danger "
                                        data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                        data-original-title="{{ __('Reset') }}">
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
                                <th>{{ __('Leave Type') }}</th>
                                {{-- <th>{{ __('Applied On') }}</th> --}}
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Total Days') }}</th>
                                <th>{{ __('Leave Reason') }}</th>
                                <th>{{ __('Attachment') }}</th>
                                <th>{{ __('status') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaves as $leave)
                                <tr>
                                    <td>{{ !empty(\Auth::user()->getEmployee($leave->employee_id)) ? \Auth::user()->getEmployee($leave->employee_id)->name : '' }}
                                    </td>
                                    <td>{{ !empty(\Auth::user()->getLeaveType($leave->leave_type_id)) ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '' }}
                                    </td>
                                    {{-- <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td> --}}
                                    <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>

                                    {{-- @php
                                        $attendanceCount = \App\Models\AttendanceEmployee::where(
                                            'employee_id',
                                            $leave->employee_id,
                                        )
                                            ->whereBetween('date', [$leave->start_date, $leave->end_date])
                                            ->where('source_in', 'Application')
                                            ->count();

                                        $totalDays = in_array($leave->status, ['Waiting Confirmation', 'Approved'])
                                            ? $attendanceCount
                                            : $leave->total_leave_days;
                                    @endphp --}}
                                    <td>{{ $leave->total_leave_days }}</td>

                                    <td>{{ $leave->leave_reason }}</td>
                                    <td>
                                        @if ($leave->document_path)
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ asset($leave->document_path) }}" target="blank"
                                                    class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-file text-white"></i>
                                                </a>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($leave->status == 'Pending')
                                            <div class="badge bg-warning p-2 px-3 rounded">{{ $leave->status }}</div>
                                        @elseif($leave->status == 'Approved')
                                            <div class="badge bg-success p-2 px-3 rounded">{{ $leave->status }}</div>
                                        @elseif ($leave->status == 'Waiting Confirmation')
                                            <span class="badge bg-info p-2 px-3 rounded">{{ $leave->status }}</span>
                                        @elseif ($leave->status == 'Confirmed')
                                            <span class="badge bg-success p-2 px-3 rounded">{{ $leave->status }}</span>
                                        @elseif($leave->status == 'Reject')
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ $leave->status }}</div>
                                        @elseif ($leave->status == 'Cancel')
                                            <div class="badge bg-secondary p-2 px-3 rounded">{{ $leave->status }}</div>
                                        @endif
                                    </td>

                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                    data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Leave Action') }}"
                                                    data-bs-original-title="{{ __('Manage Leave') }}">
                                                    <i class="ti ti-caret-right text-white"></i>
                                                </a>
                                            </div>
                                            @if (\Auth::user()->type == 'employee')
                                                @if (
                                                    ($leave->created_by == Auth::user()->id ||
                                                        $leave->employee_id == Auth::user()->employee->id ||
                                                        Auth::user()->type != 'employee') &&
                                                        $leave->status != 'Approved' &&
                                                        $leave->status != 'Waiting Confirmation' &&
                                                        $leave->status != 'Confirmed')
                                                    @can('Edit Leave')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Leave') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Leave')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['leave.destroy', $leave->id],
                                                                'id' => 'delete-form-' . $leave->id,
                                                            ]) !!}
                                                            <a href="#"
                                                                class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            </form>
                                                        </div>
                                                    @endcan
                                                @endif
                                            @else
                                                @can('Edit Leave')
                                                    @if ($leave->status != 'Approved' && $leave->status != 'Waiting Confirmation' && $leave->status != 'Confirmed')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Edit Leave') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan

                                                @can('Delete Leave')
                                                    {{-- @if ($leave->status != 'Approved') --}}
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['leave.destroy', $leave->id],
                                                            'id' => 'delete-form-' . $leave->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete"><i
                                                                class="ti ti-trash text-white text-white"></i></a>
                                                        </form>
                                                    </div>
                                                    {{-- @endif --}}
                                                @endcan
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
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            $('#commonModal').on('shown.bs.modal', function() {
                $('.status').on('click', function() {
                    $('#commonModal').modal('hide');

                    var buttonValue = $(this).data("status");
                    $("#hiddenStatus").val(buttonValue);
                })
            });
        });

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
                    var leave_selct = ` <select class="form-control select2  leave_type_id" name="leave_type_id" id="choices-multiple"
                                            placeholder="Select Leave Type" >
                                            </select>`;
                    $('.leave_type_div').html(leave_selct);

                    $('.leave_type_id').append(
                        '<option value="" disabled selected>{{ __('Select Leave Type') }}</option>'
                    );
                    $.each(data, function(key, value) {
                        if (value.total_leave == value.days) {
                            $('.leave_type_id').append('<option value="' + value.id +
                                '" disabled>' +
                                `( ${value.total_leave} / ${value.days} ) | ${value.title}` +
                                '</option>');
                        } else {
                            $('.leave_type_id').append('<option value="' + value.id + '">' +
                                `( ${value.total_leave} / ${value.days} ) | ${value.title}` +
                                '</option>');
                        }
                    });

                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        });
    </script>

    <script>
        $(document).ready(() => {
            $(document).on('change', '[name="myDocument"]', function() {
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

                    $('.department_id').append(
                        '<option value="" disabled selected>{{ __('Select Department') }}</option>');
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
            getDepartment(branch_id);
        });
    </script>
@endpush
