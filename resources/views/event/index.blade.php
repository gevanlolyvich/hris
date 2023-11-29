@extends('layouts.admin')

@section('page-title')
    {{ __('Event') }}
@endsection

@php
    $setting = App\Models\Utility::settings();
@endphp


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Event') }}</li>
@endsection

@section('action-button')
    @can('Create Event')
        <a href="#" data-url="{{ route('event.create') }}" data-ajax-popup="true" data-size="xl"
            data-title="{{ __('Create New Event') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
            class="btn btn-sm btn-primary" id="create-event">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection


@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-9">
                        <h5>{{ __('Calendar') }}</h5>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for=""></label>
                            @if(isset($setting['is_enabled']) && $setting['is_enabled'] =='on')
                                <select class="form-control" name="calender_type" id="calender_type" onchange="get_data()">
                                    <option value="google_calender">{{ __('Google Calender') }}</option>
                                    <option value="local_calender" selected="true">{{ __('Local Calender') }}</option>
                                </select>
                            @endif
                            <input type="hidden" id="path_admin" value="{{ url('/') }}">
                        </div>
                    </div>
                    <div class="card-body">
                        <div id='calendar' class='calendar'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-4">{{ __('Upcoming Events') }}</h4>
                <ul class="event-cards list-group list-group-flush mt-3 w-100">
                    <li class="list-group-item card mb-3">
                        <div class="row align-items-center justify-content-between">
                            <div class=" align-items-center">
                                @if (!$events->isEmpty())
                                    @foreach ($current_month_event as $event)
                                        <div class="card mb-3 border shadow-none">
                                            <div class="px-3">
                                                <div class="row align-items-center">
                                                    <div class="col ml-n2">
                                                        <h5 class="text-sm mb-0">
                                                            <a href="{{ route('event.show', $event->id)}}">
                                                                {{ $event->title }}
                                                            </a>
                                                        </h5>
                                                        <br>
                                                        <p class="card-text small text-dark mt-0">
                                                            {{ __('Start Date : ') }}
                                                            {{ \Auth::user()->dateFormat($event->start_date) }}<br>
                                                            {{ __('End Date : ') }}
                                                            {{ \Auth::user()->dateFormat($event->end_date) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <h5>{{__('All Events')}}</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Document') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                @if (str_contains($event->employee_id, \Auth::user()?->employee?->id) || \Auth::user()->type != 'employee' || array_intersect(json_decode($event->employee_id, true), \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray()))
                                    <tr>
                                        <td>{{ $event->title }}</td>
                                        <td>{{ $event->location ?? '-' }}</td>
                                        <td>{{ $event->start_date }}</td>
                                        <td>{{ $event->end_date }}</td>
                                        <td>
                                            @if ($event->document)
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ $event->document }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('View') }}">
                                                        <i class="ti ti-file text-white"></i>
                                                    </a>
                                                </div>
                                            @else
                                            -
                                            @endif
                                        </td>

                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="{{ route('event.show', $event->id)}}" class="mx-3 btn btn-sm  align-items-center"><i
                                                        class="fa fa-solid fa-info text-white"></i>
                                                    </a>
                                                </div>
                                                @if ($event->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
                                                    @can('Edit Event')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center edit-event" data-size="xl"
                                                                data-url="{{ URL::to('event/' . $event->id . '/edit') }}"
                                                                data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Event') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                @endif

                                                @if ($event->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
                                                    @can('Delete Event')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['event.destroy', $event->id], 'id' => 'delete-form-' . $event->id]) !!}
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
                                @endif
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
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calender_type = $('#calender_type :selected').val();
            $('#calendar').removeClass('local_calender');
            $('#calendar').removeClass('google_calender');
            if(calender_type==undefined){
                calender_type='local_calender';
            }
            $('#calendar').addClass(calender_type);

            $.ajax({
                url: $("#path_admin").val() + "/event/get_event_data",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'calender_type': calender_type
                },
                success: function(data) {
                    (function() {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                        });
                        calendar.render();
                    })();
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(bid) {
            $.ajax({
                url: '{{ route('event.getdepartment') }}',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('.department_id').empty();
                    var emp_selct = ` <select class="form-control  department_id" name="department_id[]" id="choices-multiple"
                                            placeholder="Select Department" multiple >
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value="0"> {{ __('All') }} </option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }

        $(document).on('change', '.department_id', function() {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(did) {
            $.ajax({
                url: '{{ route('event.getemployee') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('.employee_id').empty();
                    var emp_selct = ` <select class="form-control  employee_id" name="employee_id[]" id="choices-multiple1"
                                            placeholder="Select Employee" multiple >
                                            </select>`;
                    $('.employee_div').html(emp_selct);

                    $('.employee_id').append('<option value="0"> {{ __('All') }} </option>');
                    $.each(data, function(key, value) {
                        $('.employee_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple1', {
                        removeItemButton: true,
                    });
                }
            });
        }
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

    <script>
        let map = null;
        let layer = L.layerGroup();
        let mapOpened = false;

        function onMapClick(e, map) {
            const latitude = document.getElementById("latitude");
            const longitude = document.getElementById("longitude");
            latitude.value = e.latlng.lat;
            longitude.value = e.latlng.lng;
                
            if (layer !== null && layer.getLayers().length > 0) {
                layer.clearLayers();
            }
        
            let marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
            layer.addLayer(marker);
            map.addLayer(layer);
        }

        $(document).ready(function () {
            $('#create-event').click(function () {
            })

            $('.edit-event').click(function () {
                $('#commonModal').on('shown.bs.modal', function () {
                    var b_id = $('#branch_id').val();
                    getDepartment(b_id);
                })
            })

            // Remove map and layer when modal is closed
            $('#commonModal').on('hidden.bs.modal', function () {
                if (layer !== null) {
                    layer.clearLayers();
                }

                mapOpened = false;
            });

            $('body').on('click', '#get-location', function () {
                let query = document.getElementById('location-input');

                if (query.value) {
                    $.ajax({
                        url: `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query.value)}&format=json`,
                        type: 'GET',
                        success: function(data) {
                            if (data.length) {
                                let lat = document.getElementById("latitude");
                                let lon = document.getElementById("longitude");
                                lat.value = data[0].lat;
                                lon.value = data[0].lon;

                                if (map) {
                                    map.setView([lat.value, lon.value])
                                    let marker = L.marker([lat.value, lon.value]).addTo(map);
                                    layer.addLayer(marker);
                                    map.addLayer(layer);
                                }
                            } else {
                                alert('Location Not Found');
                            }
                        }
                    });
                }
            })

            $('body').on('click', '#show-map', function () {
                mapOpened = mapOpened ? false : true;
                if (mapOpened) {
                    document.getElementById('map-box').style.display = '';

                    if (map !== null) {
                        map?.remove();
                    }

                    let latitude = document.getElementById("latitude").value;
                    let longitude = document.getElementById("longitude").value;

                    map = L.map('openStreetMapContainer').setView([Number(latitude) != 0 ? latitude : -6.17436, Number(longitude) != 0 ? longitude : 106.82596], 15);
                        
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> ||' + 
                        ' <a href="https://www.openstreetmap.org/fixthemap">Report Missing / Broken Map Data To Open Street Map</a>',
                    }).addTo(map);
                        
                    if(map.hasLayer(layer)){
                        layer.clearLayers();
                    }

                    if (Number(latitude) != 0 && Number(longitude) != 0) {
                        let marker = L.marker([latitude, longitude]).addTo(map);
                        layer.addLayer(marker);
                        map.addLayer(layer);
                    }
                        
                    map.on('click', function (e) {
                        onMapClick(e, map)
                    });
                } else {
                    document.getElementById('map-box').style.display = 'none';
                }
            })
        });
    </script>
@endpush
