
@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Attendance Permit') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance Permit') }}</li>
@endsection

@section('action-button')
    {{-- <a href="{{ route('leave.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a> --}}

    {{-- <a href="{{ route('leave.calender') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a> --}}

    @can('Create Leave')
        <a href="#" data-url="{{ route('permit.create') }}" data-ajax-popup="true" data-title="{{ __('Create New Attendance Permit') }}"
            data-size="lg" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{-- <h5> </h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Permit Type') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Total Days') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Attachment') }}</th>
                                <th>{{ __('status') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permits as $permit)
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty(\Auth::user()->getEmployee($permit->employee_id)) ? \Auth::user()->getEmployee($permit->employee_id)->name : '' }}
                                        </td>
                                    @endif
                                    <td>{{ $permit->permitType->name }}
                                    </td>
                                    <td>{{ \Auth::user()->dateFormat($permit->start_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($permit->end_date) }}</td>
                                    <td>{{ $permit->total_permit_days }}</td>
                                    <td>{{ $permit->reason }}</td>
                                    <td>
                                        @if ($permit->docs)
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ $permit->docs }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
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
                                        @if ($permit->status == 'Pending')
                                            <div class="badge bg-warning p-2 px-3 rounded">{{ $permit->status }}</div>
                                        @elseif($permit->status == 'Approved')
                                            <div class="badge bg-success p-2 px-3 rounded">{{ $permit->status }}</div>
                                        @elseif($permit->status == "Reject")
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ $permit->status }}</div>
                                        @endif
                                    </td>

                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                    data-url="{{ URL::to('permit/' . $permit->id . '/action') }}"
                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Permit Action') }}"
                                                    data-bs-original-title="{{ __('Manage Permit') }}">
                                                    <i class="ti ti-caret-right text-white"></i>
                                                </a>
                                            </div>
                                            @if (\Auth::user()->type == 'employee')
                                                @if (($permit->created_by == Auth::user()->id || $permit->employee_id == Auth::user()->employee->id || Auth::user()->type != 'employee') && $permit->status != 'Approved')
                                                    @can('Edit Permit')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                                data-url="{{ URL::to('permit/' . $permit->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Attendance Permit') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Permit')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['permit.destroy', $permit->id], 'id' => 'delete-form-' . $permit->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                                aria-label="Delete"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            </form>
                                                        </div>
                                                    @endcan
                                                @endif
                                            @else
                                                @if ($permit->status != 'Approved')
                                                    @can('Edit Permit')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                                data-url="{{ URL::to('permit/' . $permit->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Attendance Permit') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Permit')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['permit.destroy', $permit->id], 'id' => 'delete-form-' . $permit->id]) !!}
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
        });
    </script>

    <script>
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
                        '<option value="">{{ __('Select Leave Type') }}</option>');

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
@endpush

