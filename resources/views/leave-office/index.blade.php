@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave Office') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave Office') }}</li>
@endsection

@section('action-button')
    {{-- @if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee')
        <a href="{{ route('leave-office.exportLending', ['url' => url()->full()]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Export') }}">
            <i class="ti ti-file-export"></i>
        </a>
    @endif --}}
    <a href="#" data-url="{{ route('leave-office.create') }}" data-ajax-popup="true" data-size="lg"
        data-title="{{ __('Create Leave Office') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
        data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
@endsection

@section('content')
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                {{ Form::open(array('route' => array('leave-office.index'),'method'=>'get','id'=>'filter')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-3">
                                    <label class="col-form-label">{{__('Type')}}</label>
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
                                        {{Form::label('month',__('Month'),['class'=>'col-form-label'])}}
                                        {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control month-btn'))}}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'),['class'=>'col-form-label'])}}
                                        {{ Form::date('date',isset($_GET['date'])?$_GET['date']:date('Y-m-d'), array('class' => 'form-control month-btn')) }}
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('branch', __('Branch'),['class'=>'col-form-label'])}}
                                        {{ Form::select('branch', $branch,isset($_GET['branch'])?$_GET['branch']:'', ['class' => 'form-control select2', 'placeholder' => __('Select Branch')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{route('leave-office.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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

    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Location') }}</th>
                            <th>{{ __('Purpose') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Proof') }}</th>
                            <th>{{ __('Approval') }}</th>
                            <th width="200px">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaves as $leave)
                            <tr>
                                <td>{{ $leave?->employee?->name ?? '-' }}</td>
                                <td>{{ $leave?->date ?? '-' }}</td>
                                <td>{{ Str::limit($leave?->location ?? '-', 20) }}</td>
                                <td>{{ Str::limit($leave?->purpose ?? '-', 20) }}</td>
                                <td>
                                    @if ($leave->status == 'Pending' || $leave->status == 'Waiting Superior Approval' || $leave->status == 'Waiting HR Approval')
                                        <div class="badge bg-warning p-2 px-3 rounded">{{ __($leave->status) }}</div>
                                    @elseif($leave->status == 'Approved')
                                        <div class="badge bg-success p-2 px-3 rounded">{{ __($leave->status) }}</div>
                                    @elseif($leave->status == "Rejected By HR" || $leave->status == "Rejected By Superior")
                                        <div class="badge bg-danger p-2 px-3 rounded">{{ __($leave->status) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    -
                                    {{-- @if (\Auth::user()->id != $leave->request_by)
                                            <a href="#" class="btn btn-{{ $leave->pickup_file || $leave->pickup_km || $leave->pickup_time || $leave->return_file || $leave->return_km || $leave->return_time ? 'info' : 'danger disabled'}} btn-sm text-center" data-size="xl"
                                                data-url="{{ route('leave-office.getProof', $leave->id) }}"
                                                data-ajax-popup="true" data-bs-toggle="tooltip"
                                                title="" data-title="{{ __('Leave Office Proof') }}"
                                                data-bs-original-title="{{ __('Proof') }}">
                                                <i class="ti ti-report"></i>
                                            </a>
                                    @elseif ($leave->status == 'Approved')
                                        <a href="#" class="btn btn-{{ $leave->pickup_file || $leave->pickup_km || $leave->pickup_time || $leave->return_file || $leave->return_km || $leave->return_time ? 'success' : 'warning'}} btn-sm text-center {{ $leave->status != 'Approved' ? 'disabled' : ''}}" data-size="xl"
                                            data-url="{{ route('leave-office.getProof', $leave->id) }}"
                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                            title="" data-title="{{ __('Leave Office Proof') }}"
                                            data-bs-original-title="{{ __('Proof') }}">
                                            <i class="ti ti-report"></i>
                                        </a>
                                    @else
                                    @endif --}}
                                </td>
                                <td class="text-center">
                                    <div class="action-btn bg-warning ms-2">
                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                            data-url="{{ route('leave-office.show', $leave->id) }}"
                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                            title="" data-title="{{ __('Leave Office Approval') }}"
                                            data-bs-original-title="{{ __('Approval') }}">
                                            <i class="ti ti-caret-right text-white"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="action text-center">
                                    <span>
                                        @if (\Auth::user()->employee?->id == $leave->employee_id || \Auth::user()->type != 'employee')
                                            @can('Edit Leave Office')
                                                @if ($leave->status != 'Approved')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" 
                                                            data-url="{{  route('leave-office.edit', $leave->id) }}"
                                                            data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Update Leave Office') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endcan
                                            @can('Delete Leave Office')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['leave-office.destroy', $leave->id], 'id' => 'delete-form-' . $leave->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title={{ __("Delete")}}
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
@endpush
