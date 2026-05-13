@extends('layouts.admin')

@section('page-title')
  {{ __("Manage Branch") }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __("Home") }}</a></li>
    <li class="breadcrumb-item">{{ __("Branch") }}</li>
@endsection

@section('action-button')
    @can('Create Branch')
        <a href="#" data-url="{{ route('branch.create') }}" data-ajax-popup="true" id="create-branch"
            data-title="{{ __('Create New Branch') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">

                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th width="10px">ID</th>
                                <th>{{__('Branch')}}</th>
                                <th>{{__('Main Branch')}}</th>
                                <th>{{__('Radius')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody >
                            @foreach ($branches as $branch)
                                <tr>
                                    <td>{{ $branch->id }}</td>
                                    <td>{{ $branch->name }}</td>
                                    <td>{{ $branch->parentBranch?->name ?? '-' }}</td>
                                    <td>{{ $branch->tolerance }} m</td>
                                    <td class="Action">
                                        <span>
                                            @can('Edit Branch')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center edit-branch"
                                                        data-url="{{ URL::to('branch/' . $branch->id . '/edit') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Branch') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete Branch')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['branch.destroy', $branch->id], 'id' => 'delete-form-' . $branch->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    </form>
                                                </div>
                                            @endcan
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
        let map = null;
        let layer = L.layerGroup();
        let circleLayer = L.layerGroup();

        function onMapDraw(e, map) {
            const latitude = document.getElementById("latitude");
            const longitude = document.getElementById("longitude");
            const tolerance = document.getElementById("tolerance");

            if (latitude.value && longitude.value) {
                if (circleLayer !== null && circleLayer.getLayers().length > 0) {
                    circleLayer.clearLayers();
                }
            
                var circle = L.circle([latitude.value, longitude.value], {
                        color: 'blue',
                        fillColor: '#f0023',
                        fillOpacity: 0.2,
                        radius: tolerance?.value || 0,
                    }).addTo(map);
                circleLayer.addLayer(circle);
                map.addLayer(circleLayer);
            }
        }

        function onMapClick(e, map) {
            const latitude = document.getElementById("latitude");
            const longitude = document.getElementById("longitude");
            latitude.value = e.latlng.lat;
            longitude.value = e.latlng.lng;
                
            if (layer !== null && layer.getLayers().length > 0) {
                layer.clearLayers();
            }
            if (circleLayer !== null && circleLayer.getLayers().length > 0) {
                circleLayer.clearLayers();
            }
        
            let marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
            layer.addLayer(marker);
            map.addLayer(layer);
            map.setView([e.latlng.lat, e.latlng.lng], map.getZoom());

            onMapDraw(e, map);
        }

        function onMapPin(e, map) {
            const latitude = document.getElementById("latitude");
            const longitude = document.getElementById("longitude");
                
            if (latitude.value && longitude.value) {
                if (layer !== null && layer.getLayers().length > 0) {
                    layer.clearLayers();
                }
                if (circleLayer !== null && circleLayer.getLayers().length > 0) {
                    circleLayer.clearLayers();
                }
            
                let marker = L.marker([latitude.value, longitude.value]).addTo(map);
                layer.addLayer(marker);
                map.addLayer(layer);
                map.setView([latitude.value, longitude.value], map.getZoom());

                onMapDraw(e, map);
            }
        }

        $(document).ready(function () {
            $('#create-branch').click(function () {
                $('#commonModal').on('shown.bs.modal', function () {
                    // If a map already exists, remove it
                    if (map !== null) {
                        map?.remove();
                    }
    
                    map = L.map('openStreetMapContainer').setView([-6.17436,106.82596], 15);
    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> ||' + 
                        ' <a href="https://www.openstreetmap.org/fixthemap">Report Missing / Broken Map Data To Open Street Map</a>',
                    }).addTo(map);

                    if(map.hasLayer(layer)){
                        layer.clearLayers();
                    }
                    if (map.hasLayer(circleLayer)) {
                        circleLayer.clearLayers();
                    }
                    

                    map.on('click', function (e) {
                        onMapClick(e, map)
                    });
                    
                    $('#latitude').on('change', function(e) {
                        onMapPin(e, map);
                    });

                    $('#longitude').on('change', function(e) {
                        onMapPin(e, map);
                    });

                    $('#tolerance').on('change', function(e) {
                        onMapDraw(e, map);
                    });
                })
            })

            $('.edit-branch').click(function () {
                $('#commonModal').on('shown.bs.modal', function () {
                    // If a map already exists, remove it
                    if (map !== null) {
                        map?.remove();
                    }
    
                    let latitude = document.getElementById("latitude").value;
                    let longitude = document.getElementById("longitude").value;
                    let tolerance = document.getElementById("longitude").value;

                    map = L.map('openStreetMapContainer').setView([latitude || '-6.17436', longitude || '106.82596'], 15);
    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> ||' + 
                        ' <a href="https://www.openstreetmap.org/fixthemap">Report Missing / Broken Map Data To Open Street Map</a>',
                    }).addTo(map);

                    // Pin marker
                    if(map.hasLayer(layer)){
                        layer.clearLayers();
                    }

                    let marker = L.marker([latitude, longitude]).addTo(map);
                    layer.addLayer(marker);
                    map.addLayer(layer);

                    // Circle Radius
                    if (map.hasLayer(circleLayer)) {
                        circleLayer.clearLayers();
                    }
            
                    var circle = L.circle([latitude, longitude], {
                            color: 'blue',
                            fillColor: '#f0023',
                            fillOpacity: 0.2,
                            radius: tolerance || 0,
                        }).addTo(map);
                    circleLayer.addLayer(circle);
                    map.addLayer(circleLayer);

                    map.on('click', function (e) {
                        onMapClick(e, map)
                    });

                    $('#latitude').on('change', function(e) {
                        onMapPin(e, map);
                    });

                    $('#longitude').on('change', function(e) {
                        onMapPin(e, map);
                    });

                    $('#tolerance').on('change', function(e) {
                        onMapDraw(e, map);
                    });
                })
            })

            // Remove map and layer when modal is closed
            $('#commonModal').on('hidden.bs.modal', function () {
                if (layer !== null) {
                    layer.clearLayers();
                }
                if (circleLayer !== null) {
                    circleLayer.clearLayers();
                }

            });
        });
    </script>
@endpush