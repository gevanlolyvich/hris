@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Vehicle Lending') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vehicle Lending') }}</li>
@endsection

@section('action-button')
    <a href="#" data-url="{{ route('vehicle-lending.create') }}" data-ajax-popup="true" data-size="xl"
        data-title="{{ __('Create Vehicle Lending') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
        data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
@endsection

@section('content')
    @if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee')
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                    {{ Form::open(array('route' => array('vehicle-lending.index'),'method'=>'get','id'=>'filter')) }}
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
                                            {{ Form::date('date',isset($_GET['date'])?$_GET['date']:'', array('class' => 'form-control month-btn')) }}
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
                                        <a href="{{route('vehicle-lending.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
    @endif

    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Vehicle') }}</th>
                            <th>{{ __('Police No') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Approved By') }}</th>
                            <th>{{ __('Report') }}</th>
                            <th width="200px">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lendings as $lending)
                            <tr>
                                <td>{{ $lending?->requester?->name ?? '-' }}</td>
                                <td>{{ $lending?->vehicle?->name ?? '-' }}</td>
                                <td>{{ $lending?->vehicle?->police_no ?? '-' }}</td>
                                <td>{{ $lending?->date ?? '-' }}</td>
                                <td>
                                    @if ($lending->status == 'Pending')
                                        <div class="badge bg-warning p-2 px-3 rounded">{{ __('Pending Approval') }}</div>
                                    @elseif($lending->status == 'Approved')
                                        <div class="badge bg-success p-2 px-3 rounded">{{ $lending->status }}</div>
                                    @elseif($lending->status == "Reject")
                                        <div class="badge bg-danger p-2 px-3 rounded">{{ $lending->status }}</div>
                                    @endif
                                </td>
                                <td>{{ $lending?->approver?->name ?? '-' }}</td>
                                <td>
                                    @if (\Auth::user()->type != 'employee')
                                        @if ($lending->pickup_file || $lending->return_file)
                                            <a href="#" class="btn btn-info btn-sm text-center">
                                                <i class="ti ti-breportus"></i>
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-info btn-sm text-center disabled">
                                                <i class="ti ti-report"></i>
                                            </a>
                                        @endif
                                    @else
                                        @if ($lending->pickup_file && $lending->return_file)
                                            <a href="#" class="btn btn-success btn-sm text-center">
                                                <i class="ti ti-report"></i>
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-warning btn-sm text-center">
                                                <i class="ti ti-report"></i>
                                            </a>
                                        @endif
                                    @endif
                                </td>
                                <td class="action">
                                    <span>
                                        {{-- <div class="action-btn bg-warning ms-2">
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg" 
                                                data-url="{{ route('vehicle-officer.show', $officer->id) }}"
                                                data-bs-toggle="tooltip" data-ajax-popup="true"
                                                title="" data-title="{{ __('Vehicle Officer Detail') }}"
                                                data-bs-original-title="{{ __('View') }}">
                                                <i class="ti ti-eye text-white"></i>
                                            </a>
                                        </div> --}}
                                        @if ($lending->request_by == \Auth::user()->id || \Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee')
                                            @if ($lending->status != 'Approved')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center" 
                                                        data-url="{{  route('vehicle-lending.edit', $lending->id) }}"
                                                        data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Update Vehicle Lending') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['vehicle-lending.destroy', $lending->id], 'id' => 'delete-form-' . $lending->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title={{ __("Delete")}}
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    </form>
                                                </div>
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
@endpush
