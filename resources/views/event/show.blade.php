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

    <script>
        $(document).ready(function() {
            var map = null;
            var imageSrc = null

            $('body').on('click', '.clock-input', function() {
                // var coordinates = $(this).data('coordinates').split(', ');

                // imageSrc = $(this).data('image');
                // if (imageSrc.length) {
                //     $('#clockImage').attr('src', imageSrc)
                //     document.getElementById('photos').style.display = '';
                // } else {
                //     document.getElementById('photos').style.display = 'none';
                // }
            
                // // Convert the radius string to a number
                // var radius = parseFloat(coordinates[2]);

            
                // Open the modal
                $('#clockInOutInputModal').modal('show');
            
                // Initialize the map after the modal is fully shown
                // $('#openStreetMapModal').on('shown.bs.modal', function () {
                //     // If a map already exists, remove it
                //     if (map !== null) {
                //         map.remove();
                //     }

                //     map = L.map('openStreetMapContainer').setView([coordinates[0], coordinates[1]], 17);
                //     L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                //         attribution: '© OpenStreetMap contributors'
                //     }).addTo(map);
                
                //     // Add a marker for the location
                //     var marker = L.marker([coordinates[0], coordinates[1]]).addTo(map);
                
                //     // Add a circle with the converted radius
                //     var circle = L.circle([coordinates[0], coordinates[1]], {
                //         color: 'blue',
                //         fillColor: '#f0023',
                //         fillOpacity: 0.2,
                //         radius: radius,
                //     }).addTo(map);
                // });
            });

            $('body').on('click', '.report-input', function() {
                // Open the modal
                $('#reportInputModal').modal('show');

                // Set the modal's data attributes
                // Get the values from the clicked button
                let eventPId = $(this).data('event-employee-id');
                document.getElementById('eventemployeeid').value = eventPId;

                let notes = $(this).data('note');
                if (notes) {
                    document.getElementById('note').value = notes;
                }


            });

            $('#reportInputModal').on('hidden.bs.modal', function () {
                let file = document.getElementById('uploadFile');
                if (file) {
                    file.style.display = 'none';
                }
            });
        });
    </script>

    <script>
        $(document).ready(() => {
            $(document).on('change', '[name="myDocument"]', function () {
                const file = document.getElementById('uploadFile');
                file.style.display = '';
                file.style['max-width'] = '';
                document.getElementById('fileName').textContent = this.files[0].name;
            });
        })
    </script>
@endpush

@section('content')
    <div class="modal fade" id="clockInOutInputModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('Clock In / Clock Out')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding-top: 0.35rem">
                    {{-- <div class="row d-flex flex-column align-items-center">
                        {{ Form::open(['url' => 'event/attendance', 'method' => 'post', 'id' => 'clock-in-form', 'enctype' => 'multipart/form-data']) }}
                        {{ Form::label('picture', __('Picture'), ['class' => 'col-form-label']) }}
                        <div class="col-md-6 col-lg-12 text-center mx-auto">
                            <button type="button" class="btn btn-info btn-lg btn-block " id="load"><i
                                class="fa fa-solid fa-camera"></i> {{ __('Load Webcam') }}
                            </button>
                            <div id="camera" style="display: none; position: relative" class="col-12">
                                <video id="video" style="border-radius: 5%" class="mb-2">Video stream not available.</video>
                                <button type="button" class="btn btn-info btn-lg btn-block custBtn" id="takepic" style="display: none;"><i
                                    class="fa fa-solid fa-camera"></i> {{ __('Take A Picture') }}
                                </button>
                            </div>
                            <canvas id="canvas" style="display: none;"></canvas>
                            <div id="output" style="display: none;">
                                <img id="photo" style="border-radius: 5%" alt="The screen capture will appear in this box.">
                            </div>
                            <label for="picture">
                                <input type="hidden" name="picture" id="picture">
                            </label>      
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('attendance_type', __('Attendance Type'), ['class' => 'col-form-label']) !!}
                                {{ Form::select('attendance_type', $attendance_type, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder'=>'Choose attendance type']) }}
                            </div>
                            <div class="form-group">
                                {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => '2' ,'placeholder'=>'Enter notes for clock in']) !!}
                            </div>
                            <input type="hidden" name="latitude" id="latitude" value="0">
                            <input type="hidden" name="longitude" id="longitude" value="0">
                            <input type="hidden" name="accuracy" id="accuracy" value="0">
                        </div>
                        <div class="col-md-6 text-center mx-auto mt-1">
                            <button type="submit" value="0" name="in" id="clock_in" onclick="getLocation()"
                                class="btn btn-primary btn-lg btn-block" style="width: 150px" disabled>{{ __('CLOCK IN') }}</button>
                            {{ Form::close() }}
                        </div>                                                    
                        <div class="col-md-6 text-center mx-auto mt-3">
                            {{ Form::model($eventP, ['route' => ['event.attendance', $eventP->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                                <input type="hidden" name="latitude" id="latitude_out" value="0">
                                <input type="hidden" name="longitude" id="longitude_out" value="0">
                                <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                                <input type="hidden" name="picture_out" id="picture_out">
                                <button type="submit" value="1" name="out" id="clock_out" onclick="getLocation()"
                                    class="btn btn-danger" style="width: 150px">{{ __('CLOCK OUT') }}</button>
                            {{ Form::close() }}
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reportInputModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('Report')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding-top: 0.35rem">
                    {{ Form::open(['route' => ['eventemployee.report'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="form-group" style="margin-bottom: 0px">
                            {{ Form::label('myDocument', __('Document'), ['class' => 'col-form-label']) }}
                            <div class="row">
                                <label for="myDocument" class="col-6">
                                <div class="btn btn-block btn-primary bg-primary document"> <i
                                            class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                    </div>
                                    <input style="margin-top: -50px" type="file" class="btn btn-block btn-primary bg-primary document form-control mb-4 file" name="myDocument">
                                </label>
                                <div class="btn btn-block btn-success bg-success disabled col-6" style="display: none;" id="uploadFile"><i
                                    class="fa fa-regular fa-file"></i><p id="fileName"></p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {{ Form::label('note', __('Note'), ['class' => 'col-form-label']) }}
                            {{ Form::textarea('note', null, ['class' => 'form-control', 'id' => 'note', 'placeholder' => __('Add Notes'),'rows'=>'3']) }}
                        </div>
                        <input type="hidden" name="eventemployeeid" id="eventemployeeid" value="0">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <input type="submit" value="{{ __('Submit') }}" class="btn  btn-primary">
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-6 col-xl-6">
            <div class="card">
                <div class="tab-content tab-bordered">
                    <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
                        <div class="card-body" style="padding-bottom: 10px">
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
                                        <a href="{{ $event->document }}" target="blank" class="btn btn-outline-dark bg-info btn-sm"
                                            data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Document') }}"><i
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
                                @foreach ($event_employees as $eventP)
                                    <tr>
                                        <td>{{ $eventP->employee->name }}</td>
                                        <td>
                                            {{ !empty(\Auth::user()->getBranch($eventP->employee->branch_id)) ? \Auth::user()->getBranch($eventP->employee->branch_id)->name : '' }}
                                        </td>
                                        <td>
                                            {{ !empty(\Auth::user()->getDepartment($eventP->employee->department_id)) ? \Auth::user()->getDepartment($eventP->employee->department_id)->name : '' }}
                                        </td>
                                        <td>
                                            {{ !empty(\Auth::user()->getDesignation($eventP->employee->designation_id)) ? \Auth::user()->getDesignation($eventP->employee->designation_id)->name : '' }}
                                        </td>
                                        @if ((\Auth::user()?->employee?->id == $eventP->employee->id) || \Auth::user()->type != 'employee')
                                            <td>
                                                <button class="btn btn-primary btn-sm clock-input" data-bs-toggle="tooltip"
                                                    data-event_employee-id="{{ $eventP->id }}"
                                                    data-clock-in="{{ $eventP->clock_in }}"
                                                    data-bs-original-title="{{ __('Clock In / Clock Out') }}">
                                                    <i class="fa fa-solid fa-clock"></i>
                                                </button>
                                                <button class="btn btn-primary btn-sm report-input" data-bs-toggle="tooltip"
                                                    data-event-employee-id="{{ $eventP->id }}"
                                                    data-document="{{ $eventP->report_document }}"
                                                    data-note="{{ $eventP->report_note }}"
                                                    data-bs-original-title="{{ __('Report Document') }}">
                                                    <i class="fa fa-solid fa-file-import"></i>
                                                </button>
                                            </td>
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

