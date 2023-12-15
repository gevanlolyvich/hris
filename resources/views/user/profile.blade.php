@extends('layouts.admin')
@php
$profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp

@push('script-page')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>

    {{-- <script>
        async function getLocation() {
          return new Promise((resolve, reject) => {
            if ("geolocation" in navigator) {
              navigator.geolocation.getCurrentPosition(
                (position) => {
                  const latitude = position.coords.latitude;
                  const longitude = position.coords.longitude;
                  const accuracy = position.coords.accuracy;
                  resolve({ latitude, longitude, accuracy });
                },
                (error) => {
                  if (error.code === 1) {
                    reject(new Error("User denied Geolocation"));
                  } else {
                    reject(error);
                  }
                }
              );
            } else {
              reject(new Error("Geolocation is not supported by your browser."));
            }
          });
        }

        $('#coordinate').on('click', async function () {
            try {
                const { latitude, longitude, accuracy } = await getLocation();
                console.log(`${latitude}, ${longitude}, ${accuracy}`);
    
                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
                document.getElementById('accuracy').value = accuracy;

                alert('Success Getting Current Location Coordinate');
            } catch (error) {
                console.log(error);
            }
        })
    </script> --}}

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

        function mapShow() {
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
                }
            }

        function mapOpenClose() {
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
        }

        $(document).ready(function () {
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

                                mapShow();

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

            $('body').on('click', '#show-map', mapOpenClose)
        });
    </script>
@endpush

@section('page-title')
    {{ __('Profile') }}
@endsection

@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0"> {{ __('Profile') }}</h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Profile') }}</li>
@endsection

@section('action-btn')
@endsection

@section('content')
    <div class="col-sm-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card sticky-top" style="top:30px">
                    <div class="list-group list-group-flush" id="useradd-sidenav">
                        <a href="#useradd-1"
                            class="list-group-item list-group-item-action border-0">{{ __('Personal Info') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-2"
                            class="list-group-item list-group-item-action border-0">{{ __('Change Password') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                    </div>
                </div>
            </div>


            <div class="col-xl-9">
                <div id="useradd-1">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Personal Information') }}</h5>
                            <small> {{ __('Details about your personal information') }}</small>
                        </div>
                        <div class="card-body">
                            {{ Form::model($userDetail, ['route' => ['update.account'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                            @csrf
                            <input type="hidden" name="latitude" id="latitude" value="{{ explode(', ', $userDetail?->employee?->coordinate)[0] ?? 0 }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ explode(', ', $userDetail?->employee?->coordinate)[1] ?? 0 }}">
                            <input type="hidden" name="accuracy" id="accuracy" value="{{ explode(', ', $userDetail?->employee?->coordinate)[2] ?? 0 }}">
                            <div class="row">
                                <div class="col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label class="col-form-label text-dark">{{ __('Name') }}</label>
                                        <input class="form-control @error('name') is-invalid @enderror" name="name"
                                            type="text" id="name" placeholder="{{ __('Enter Your Name') }}"
                                            value="{{ $userDetail->name }}" required autocomplete="name">
                                        @error('name')
                                            <span class="invalid-feedback text-danger text-xs"
                                                role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label for="email" class="col-form-label text-dark">{{ __('Email') }}</label>
                                        <input class="form-control @error('email') is-invalid @enderror" name="email"
                                            type="text" id="email" placeholder="{{ __('Enter Your Email Address') }}"
                                            value="{{ $userDetail->email }}" required autocomplete="email">
                                        @error('email')
                                            <span class="invalid-feedback text-danger text-xs"
                                                role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label class="col-form-label text-dark">{{ __('Birthdate') }}</label>
                                        {{ Form::date('birthdate', $userDetail?->employee?->dob, [
                                                'class' => 'form-control d_week', 'required' => 'required', 'autocomplete'=>'birthdate',
                                                'id'=>'birthdate', 'required'=>'required', 'name'=>'birthdate'
                                            ])
                                        }}
                                        {{-- <input class="form-control @error('birthday') is-invalid @enderror" name="name"
                                            type="text" id="name" placeholder="{{ __('Enter Your Name') }}"
                                            value="{{ $userDetail->name }}" required autocomplete="name">
                                        @error('name')
                                            <span class="invalid-feedback text-danger text-xs"
                                                role="alert">{{ $message }}</span>
                                        @enderror --}}
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label for="phone" class="col-form-label text-dark">{{ __('Phone') }}</label>
                                        <input class="form-control" name="phone"
                                            type="text" id="phone" placeholder="{{ __('Enter Phone') }}"
                                            value="{{ $userDetail?->employee?->phone }}" required autocomplete="phone">
                                    </div>
                                </div>
                                <div class="col-lg-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="address" class="col-form-label text-dark">{{ __('Address') }}</label>
                                        {{ Form::textarea('address', $userDetail?->employee?->address, [
                                                'class' => "form-control", 'rows' => '3', 'placeholder'=>__('Enter Your Address'),
                                                'name' => 'address', 'required'=>'required', 'id'=>'location-input', 'autocomplete'=>'address'
                                            ])
                                        }}
                                        {{-- <textarea rows="3" class="form-control @error('address') is-invalid @enderror" name="address"
                                            id="address" placeholder="{{ __('Enter Your Address') }}"
                                            value="{{ $userDetail?->employee?->address }}" required autocomplete="address">
                                        </textarea> --}}
                                        @error('address')
                                            <span class="invalid-feedback text-danger text-xs"
                                                role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        <label for="coordinate" class="col-form-label text-dark">{{ __('Coordinate') }}</label>
                                        {{-- <button type="button" class="btn bg-primary form-control text-white" id="coordinate">{{__("Get Current Location Coordinate")}}</button> --}}
                                        <button class="btn bg-primary form-control text-white" style="margin-right: 15px" type="button" id="get-location">{{__('Search Location')}}</button>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="form-group">
                                        {{ Form::label('location action', __('Location'), ['class' => 'col-form-label text-dark']) }}
                                        <button class="btn bg-primary form-control text-white" type="button" id="show-map">{{__('Show Map')}}</button>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12" style="display: none;" id="map-box">
                                    <div class="form-group">
                                        {{ Form::label('map', __('Map'), ['class' => 'form-label']) }}
                                        <div id="openStreetMapContainer" style="height: 300px;"></div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('profile', __('Avatar'), ['class' => 'col-form-label']) }}
                                        <div class="choose-files ">
                                            <label for="profile">
                                                <div class=" bg-primary profile "> <i
                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                </div>
                                                <input type="file" class="form-control file" name="profile" id="profile" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                                <span class="theme-avtar" style="width: 150px; height: 150px; overflow: hidden; border-radius: 50%;">
                                                    <img alt="#" id="blah"
                                                        src="{{ !empty($userDetail->avatar) ? $profile . $userDetail->avatar : $profile . '/avatar.png' }}"
                                                        class="header-avtar" style="width: 100%; height: 100%; object-fit: cover;">
                                                    {{-- <img id="blah"  width="100" src="{{ !empty($userDetail->avatar) ? $profile . $userDetail->avatar : $profile . '/avatar.png' }}" /> --}}
                                                </span>   
                                            </label>
                                        </div>
                                        <span
                                        class="text-xs text-muted">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.') }}</span>
                                    @error('profile')
                                        <span class="invalid-feedback text-danger text-xs"
                                            role="alert">{{ $message }}</span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12 text-end">
                                    <input type="submit" value="{{ __('Save Changes') }}"
                                        class="btn btn-print-invoice  btn-primary m-r-10">
                                </div>
                            </div>
                            </form>
                        </div>

                    </div>
                </div>

                <div id="useradd-2">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Change Password') }}</h5>
                            <small> {{ __('Details about your account password change') }}</small>
                        </div>
                        <div class="card-body">
                            {{ Form::model($userDetail, ['route' => ['update.password', $userDetail->id], 'method' => 'post']) }}

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('current_password', __('Current Password'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Form::password('current_password', ['class' => 'form-control', 'placeholder' => __('Enter Current Password')]) }}
                                        @error('current_password')
                                            <span class="invalid-current_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('new_password', __('New Password'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Form::password('new_password', ['class' => 'form-control', 'placeholder' => __('Enter New Password')]) }}
                                        @error('new_password')
                                            <span class="invalid-new_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('confirm_password', __('Re-type New Password'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Form::password('confirm_password', ['class' => 'form-control', 'placeholder' => __('Enter Re-type New Password')]) }}
                                        @error('confirm_password')
                                            <span class="invalid-confirm_password" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer pr-0">
                                {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>
@endsection
