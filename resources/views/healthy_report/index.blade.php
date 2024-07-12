@extends('layouts.admin')

@section('page-title')
   {{ __('Healthy Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Healthy Report') }}</li>
@endsection



@section('content')
@if (Auth::user()->type=='employee')
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
                                        <small class="text-muted">{{ __('Date') }}</small>
                                        <h6 class="m-0">{{ __('Last Report') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0 text-primary">
                                    <span class="text-primary">{{ date('d M Y', strtotime($last_step->date)) }}</span>

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
                                    <span class=" {{ $last_step->steps >= $last_step->healthy_target->target ? 'text-primary':'text-danger' }}">{{ $last_step->steps }}</span> </span>
                                </h4>
                                <span class="text-primary" style="font-size: 0.8rem">/ {{ $last_step->healthy_target->target }}</span>
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
                                <h4 class="m-0 text-info">{{ number_format($last_step->distances, 1, ',', '.') }}</h4>
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
                                <h4 class="m-0 text-warning">{{ number_format($last_step->calories, 1, ',', '.') }}</h4>
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
                                <h5>{{ __("Weekly Leaderboard") }}</h5>
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
                                        <th>{{ __('Daily Average') }}</th>
                                        <th>{{ __('Total Steps') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach ($leaderboard as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->employee->name }}</td>
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
@else
    
@endif  
    
@endsection


@push('script-page')
    <script>
        const ctx = document.getElementById('stepsChart').getContext('2d');
        const stepsChart = new Chart(ctx, {
            type: 'bar', // Bisa diganti menjadi 'bar', 'pie', dll.
            data: {
                labels: {!! json_encode($weekDays) !!}, // Array nama hari
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
