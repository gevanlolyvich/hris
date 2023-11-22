@extends('layouts.admin')

@section('page-title')
    {{__('Employee')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('event.index') }}">{{ __('All Events') }}</a></li>
    <li class="breadcrumb-item">{{ __('Event') }}</li>
@endsection

@section('action-button')
@endsection

@push('css-page')
    <style>
        #openStreetMapContainer {
            height: 150px;
            width: 100%;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
            border-bottom-left-radius: 10px;
        }
    </style>
@endpush

@push('script-page')
    <script>
        $(document).ready(function() {
            var map = null;
            var imageSrc = null;

            var locationLink = document.getElementById('gMapLink');
            var coordinate = document.getElementById('coordinate');

            if (locationLink && coordinate) {
                locationLink.href = `https://www.google.co.id/maps/search/${coordinate.textContent?.replace(' ', '')}`;

                // If a map already exists, remove it
                if (map !== null) {
                        map.remove();
                    }

                    let coordinates = coordinate.textContent.split(', ');
                    map = L.map('openStreetMapContainer').setView([coordinates[0], coordinates[1]], 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                
                    // Add a marker for the location
                    var marker = L.marker([coordinates[0], coordinates[1]]).addTo(map);
            }
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-6 col-xl-6">
            <div class="card">
                <div class="tab-content tab-bordered">
                    <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
                        <div class="card-body">
                            <dl class="row" style="font-size: 15px !important;">
                                <dt class="col-4"><span class="h6 mb-0">{{ __('Title') }}</span>
                                </dt>
                                <dd class="col-8"><span class="">{{ $event->title }}</span></dd>
                                <dt class="col-4"><span
                                        class="h6 mb-0">{{ __('Start Date') }}</span>
                                </dt>
                                <dd class="col-8"><span
                                        class="">{{ \Auth::user()->dateFormat($event->start_date) }}</span>
                                </dd>
                                <dt class="col-4"><span class="h6 mb-0">{{ __('End Date') }}</span>
                                </dt>
                                <dd class="col-8"><span
                                        class="">{{ \Auth::user()->dateFormat($event->end_date) }}</span>
                                </dd>
                                <dt class="col-4"><span
                                        class="h6  mb-0">{{ __('Description') }}</span></dt>
                                <dd class="col-8"><span class="">{{ $event->description }}</span>
                                @if (!empty($event->document))
                                    <dt class="col-4"><span
                                        class="h6  mb-0">{{ __('Document') }}</span></dt>
                                    <div class="col-8">
                                        <a href="{{ $event->document }}" target="blank" class="btn btn-outline-dark bg-info"
                                            data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('View') }}"><i
                                                class="ti ti-file text-white"></i>
                                        </a>
                                    </div>
                                @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6 col-xl-6">
            <div class="card">
                <div class="card-body" style="padding-bottom: 10px">
                    <dl class="row" style="font-size: 15px !important;">
                        @if (!empty($event->location_coord))
                            <dt class="col-4"><span
                                    class="h6 mb-0">{{ __('Location') }}</span></dt>
                            <dd class="col-8"><a href="https://www.google.co.id/maps/search/-6.175851,106.827197" id="gMapLink"><span class="">{{ $event->location }}</span></a>
                            <dt class="col-4"><span style="display: none;" class="h6 mb-0">Coordinate</span></dt>
                            <dd class="col-8"><span style="display: none;" id="coordinate">{{ $event->location_coord }}</span></dd>
                            <div id="openStreetMapContainer" class="mt-1"></div>
                        @else
                            <dt class="col-4"><span
                                class="h6 mb-0">{{ __('Location') }}</span></dt>
                            <dd class="col-8"><a href="https://www.google.co.id/maps/search/-6.175851,106.827197" id="gMapLink"><span class="">{{ $event->location }}</span></a>
                        @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{__('Employees')}}</h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->name }}</td>
                                        <td>
                                        {{ !empty(\Auth::user()->getBranch($employee->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : '' }}
                                        </td>
                                        <td>
                                            {{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '' }}
                                        </td>
                                        <td>
                                            {{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '' }}
                                        </td>
                                        @if ((\Auth::user()->employee->id == $employee->id) || \Auth::user()->type != 'employee')
                                            <td>there will be lot a button here</td>
                                        @else
                                            <td></td>
                                        @endif
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

