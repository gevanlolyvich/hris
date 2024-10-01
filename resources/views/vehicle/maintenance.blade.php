@extends('layouts.admin')

@section('page-title')
   {{ __('Vehicle Maintenance History') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('vehicle') }}">{{ __('Manage Vehicle') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vehicle Maintenance History') }}</li>
@endsection

@push('script-page')
@endpush

@push('css-page')
    <style>
        ul.timeline-3 {
            list-style-type: none;
            position: relative;
        }
        ul.timeline-3:before {
            content: " ";
            background: #5387c7;
            display: inline-block;
            position: absolute;
            left: 29px;
            width: 2px;
            height: 100%;
            z-index: 400;
        }
        ul.timeline-3 > li {
            margin: 20px 0;
            padding-left: 20px;
        }
        ul.timeline-3 > li:before {
            content: " ";
            background: white;
            display: inline-block;
            position: absolute;
            border-radius: 50%;
            border: 3px solid #22c0e8;
            left: 20px;
            width: 20px;
            height: 20px;
            z-index: 400;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body fulls-card p-3 align-items-center">
                    <div class="row text-center">
                        <div class="col">
                            <h6 style="padding: 10px 0;margin-bottom: 0px">{{ $vehicle->name }}</h6> 
                        </div>
                        <div class="col">
                            <h6 style="padding: 10px 0;margin-bottom: 0px">{{ $vehicle?->police_no }}</h6>
                        </div>
                        <div class="col">
                            <h6 style="padding: 10px 0;margin-bottom: 0px">{{ $vehicle->branch?->name ?? '-' }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="timeline-3">
                                @foreach ($maintenances as $maintenance)
                                    <li>
                                        <i>{{ $maintenance->start_date }} @if ($maintenance->end_date) >> {{ $maintenance->end_date}} @endif</i> <br>
                                        {{-- <div style="color: #1058c4"> --}}
                                        <a href="#" data-size="xl"
                                            data-url="{{ route('vehicle-maintenance.show', $maintenance->id) }}"
                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                            title="" data-title="{{ __('Detail Vehicle Maintenance') }}"
                                            data-bs-original-title="{{ __('Detail') }}">
                                            <b>{{ $maintenance->name }} [{{ $maintenance?->maintenanceType?->name ?? '-' }}]</b>
                                            <br>
                                        </a>
                                        {{-- </div> --}}
                                        <i style="color: #c5480e" class="fa fa-solid fa-map-pin"></i> {{ $maintenance->workshop->name}} <br>
                                        <i style="color: #c5480e" class="fas fa-dollar-sign"></i> {{ \Auth::user()->priceFormat($maintenance->cost) }}
                                        <p class="mt-2">{{ $maintenance->description}}</p>
                                        <hr>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection