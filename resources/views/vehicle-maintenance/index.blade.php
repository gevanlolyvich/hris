@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Vehicle Maintenance') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vehicle Maintenance') }}</li>
@endsection

@section('action-button')
    {{-- <a href="{{ route('vehicle-lending.exportLending', ['url' => url()->full()]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a> --}}
    @if ((\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee') && \Auth::user()->can('Create Vehicle Maintenance'))
        <a href="#" data-url="{{ route('vehicle-maintenance.create') }}" data-ajax-popup="true" data-size="lg"
            data-title="{{ __('Create Vehicle Maintenance') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                {{ Form::open(array('route' => array('vehicle-maintenance.index'),'method'=>'get','id'=>'filter')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-2">
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
                                <div class="col-xl-7 col-lg-7 col-md-12 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('vehicle', __('Vehicle'),['class'=>'col-form-label'])}}
                                        {{ Form::select('vehicle', $vehicles_choices,isset($_GET['vehicle'])?$_GET['vehicle']:'', ['class' => 'form-control select2', 'placeholder' => __('Select Vehicle')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-1">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{route('vehicle-maintenance.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
                            <th>{{ __('Vehicle') }}</th>
                            <th>{{ __('Police No') }}</th>
                            <th>{{ __('Maintenance Name') }}</th>
                            <th>{{ __('Maintenance Type') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Location') }}</th>
                            <th width="200px">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($maintenances as $maintenance)
                            <tr>
                                <td>{{ $maintenance?->vehicle?->name ?? '-' }}</td>
                                <td>{{ $maintenance?->vehicle?->police_no ?? '-' }}</td>
                                <td>{{ $maintenance?->name ?? '-' }}</td>
                                <td>{{ $maintenance?->maintenanceType?->name ?? '-' }}</td>
                                @if ($maintenance->end_date == $maintenance->start_date || !$maintenance->end_date)
                                    <td>{{ $maintenance?->start_date ?? '-' }}</td>
                                @else
                                    <td>{{ $maintenance?->start_date ?? '-' }}  >>  {{ $maintenance->end_date ?? '-' }}</td>
                                @endif
                                <td>{{ $maintenance?->location ?? '-' }}</td>
                                <td class="action">
                                    <span>
                                        <button class="btn btn-primary btn-sm leave-input"
                                            data-bs-toggle="tooltip" data-size="lg"
                                            data-url="{{ route('vehicle-maintenance.show', $maintenance->id) }}"
                                            data-ajax-popup="true" title="" data-title="{{ __('Detail Vehicle Maintenance') }}"
                                            data-bs-original-title="{{ __('Detail') }}">
                                            <i class="ti ti-eye text-white"></i>
                                        </button>
                                        @if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee')
                                            @can('Edit Vehicle Maintenance')
                                                <button class="btn btn-info btn-sm leave-input ms-2"
                                                    data-bs-toggle="tooltip" data-size="lg" data-ajax-popup="true"
                                                    data-url="{{  route('vehicle-maintenance.edit', $maintenance->id) }}"
                                                    title="" data-title="{{ __('Update Vehicle Lending') }}"
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </button>
                                            @endcan
                                            @can('Delete Vehicle Maintenance')
                                                <div class="action-btn ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['vehicle-maintenance.destroy', $maintenance->id], 'id' => 'delete-form-' . $maintenance->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-danger btn-sm  align-items-center bs-pass-para"
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

            $(document).on('change', '#date_input', function () {
                let dateInput = $(this).val();

                console.log(dateInput);

                const end_date = document.getElementById('end_date_input');
                end_date.disabled = false;
                end_date.min = dateInput;
                end_date.value = '';

                const next_date = document.getElementById('next_date_input');
                next_date.disabled = false;
                next_date.min = dateInput;
                next_date.value = '';
            })
        });

    </script>
@endpush
