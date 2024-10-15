@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Employee Application') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Application') }}</li>
@endsection

@section('action-button')
    <a href="#" data-url="{{ route('employee-applications.create') }}" data-ajax-popup="true"
        data-title="{{ __('Create Employee Application') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
        data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
@endsection



@section('content')
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">

                    <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th width="10px">{{ __('Employee ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employee_periods as $emp)
                                <tr>
                                    <td>
                                        @can('Show Employee')
                                            <a class="btn btn-outline-primary"
                                                href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($emp->employee->id)) }}">{{ $emp->employee->employee_id }}</a>
                                        @else
                                            <a href="#" class="btn btn-outline-primary">{{ $emp->employee->employee_id }}</a>
                                        @endcan
                                    </td>
                                    <td style="width: 150px; word-break: break-word; white-space: normal;">
                                        {{ $emp->employee->name }}
                                    </td>                                    
                                    <td>{{ $emp->employee->branch->name }}</td>
                                    <td>{{ $emp->start_period ." - ". $emp->end_period }}</td>
                                    <td>
                                        @if ($emp->status == 'Pending')
                                            <div class="badge bg-warning p-2 px-3 rounded">{{ $emp->status }}</div>
                                        @elseif($emp->status == 'Approved')
                                            <div class="badge bg-success p-2 px-3 rounded">{{ $emp->status }}</div>
                                        @elseif($emp->status == "Reject")
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ $emp->status }}</div>
                                        @endif
                                    </td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                    data-url="{{ URL::to('employee-applications/' . $emp->id . '/action') }}"
                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Action') }}"
                                                    data-bs-original-title="{{ __('Manage Employee Application') }}">
                                                    <i class="ti ti-caret-right text-white"></i>
                                                </a>
                                            </div>
                                            @if ($emp->status=="Pending"||$emp->status=="Rejected")
                                                @can('Edit Designation')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-url="{{  URL::to('employee-applications/'.$emp->id."/edit") }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                            data-title="{{ __('Edit Designation') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Designation')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['employee-applications.destroy', $emp->id], 'id' => 'delete-form-' . $emp->id]) !!}
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
        });

        $('body').on('click', '#renewalButton', function () {
            $('#renewal_form').show();
        });
    </script> 
@endpush