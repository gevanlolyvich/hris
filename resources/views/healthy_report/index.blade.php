@extends('layouts.admin')

@section('page-title')
   {{ __('Physical Activity') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Physical Activity') }}</li>
@endsection


@section('content')
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(array('route' => array('healthy-reports.index'),'method'=>'get','id'=>'filter_of_healthy_report_employee')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-12">
                            <div class="row">
                                @if (Auth::user()->type=='employee')
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', 
                                                request('start_date') ? request('start_date') : Carbon\Carbon::now()->subWeek()->format('Y-m-d'), 
                                                ['class' => 'form-control start_date']) 
                                            }}
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            <div class="end_date_div btn-box">
                                                {{ Form::date('end_date', 
                                                    request('end_date') ? request('end_date') : Carbon\Carbon::now()->format('Y-m-d'), 
                                                    ['class' => 'form-control end_date_id']) 
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('branch_id', __('Branch'),['class'=>'form-label'])}}
                                            {{ Form::select('branch_id', $branches,isset($_GET['branch_id'])?$_GET['branch_id']:'', ['class' => 'form-control select2 branch', 'placeholder' => __('Select Branch')]) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', 
                                                request('start_date') ? request('start_date') : Carbon\Carbon::now()->subWeek()->format('Y-m-d'), 
                                                ['class' => 'form-control select2 start_date']) 
                                            }}
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            <div class="end_date_div btn-box">
                                                {{ Form::date('end_date', 
                                                    request('end_date') ? request('end_date') : Carbon\Carbon::now()->format('Y-m-d'), 
                                                    ['class' => 'form-control select2 end_date_id']) 
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                @endif      
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('filter_of_healthy_report_employee').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{route('healthy-reports.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
    <div class="col-xxl-12">
        {{-- start --}}
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary">
                                        <i class="ti ti-report"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Period') }}</small>
                                        <h6 class="m-0">{{ __('Report') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0 text-primary">
                                    <span class="text-primary">{{ date('d M Y', strtotime($start_date_str)) }} sd. {{ date('d M Y', strtotime($end_date_str)) }}</span>

                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-primary">
                                        <i class="ti ti-walk"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Healthy Steps') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0 text-primary">
                                    <span class=" {{ $total_steps >= $steps_target ? 'text-primary':'text-danger' }}">{{ number_format($total_steps, 0, ',', '.') }}</span> </span>
                                </h4>
                                <span class="text-primary" style="font-size: 0.8rem">/ {{ number_format($steps_target, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-route"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Distance Covered') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0 text-info">{{ number_format($distances, 1, ',', '.') }}</h4>
                                <span class="text-info" style="font-size: 0.8rem">KM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-warning">
                                        <i class="ti ti-calendar-off"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Calories Burned') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0 text-warning">{{ number_format($calories, 1, ',', '.') }}</h4>
                                <span class="text-warning" style="font-size: 0.8rem">Cal</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-12">
        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header card-body table-border-style">
                        <div class="row">
                            <div class="col-9">
                                <h5>{{ __("Your Healthy Step Performance") }}</h5>
                            </div>
                            <div class="col-2">
                                <a href="{{ route('attendanceemployee.exportNotClockIn', ['date' => date('Y-m-d')]) }}" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Export') }}">
                                    {{-- <button type="button" class="btn btn-info btn-lg btn-block">{{ count($notClockIns) }}</button> --}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="display: flex; justify-content: center;align-items: center;height: 324px;overflow: auto;">
                        
                        <canvas id="stepsChart" style="max-width: 100%; max-height: 100%;"></canvas>
                    </div>
                </div>
                
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header card-body table-border-style">
                        <div class="row">
                            <div class="col-9">
                                <h5>{{ __("Leaderboard") }}</h5>
                            </div>
                            <div class="col-2">
                                <a href="{{ route('attendanceemployee.exportNotClockIn', ['date' => date('Y-m-d')]) }}" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Export') }}">
                                    {{-- <button type="button" class="btn btn-info btn-lg btn-block">{{ count($notClockIns) }}</button> --}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="height: 324px; overflow:auto">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Name') }}</th>
                                        {{-- <th>{{ __('Branch') }}</th> --}}
                                        <th>{{ __('Daily Average') }}</th>
                                        <th>{{ __('Total Steps') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach ($leaderboard as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $item->employee->name }}  <br> 
                                                <span class="btn btn-primary btn-sm text-center disabled">
                                                    {{ $item->employee->branch->name ?? "" }}
                                                </span>
                                            </td>
                                            {{-- <td>{{ $item->employee->branch->name ?? "" }}</td> --}}
                                            <td>{{ number_format($item->avg_steps, 0, ',', '.').' '.__('Steps') }}</td>
                                            <td>{{ number_format($item->total_steps, 0, ',', '.').' '.__('Steps') }}</td>
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
        
@endsection


@push('script-page')
    <script>
        const ctx = document.getElementById('stepsChart').getContext('2d');
        const stepsChart = new Chart(ctx, {
            type: 'bar', // Bisa diganti menjadi 'bar', 'pie', dll.
            data: {
                labels: {!! json_encode($weekDates) !!}, // Array nama hari
                datasets: [{
                    label: 'Jumlah Langkah',
                    data: {!! json_encode($weeklySteps) !!}, // Array jumlah langkah per hari
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                },
            }
        });
    </script>
@endpush
