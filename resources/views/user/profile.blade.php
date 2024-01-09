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
                        @if (\Auth::user()->type == 'employee')
                        <a href="#useradd-3"
                                class="list-group-item list-group-item-action border-0">{{ __('Bank') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-4"
                                class="list-group-item list-group-item-action border-0">{{ __('Nationality') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-5"
                                class="list-group-item list-group-item-action border-0">{{ __('Document') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            
                        @endif
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
                                            value="{{ $userDetail->email }}" required autocomplete="email" disabled>
                                        @error('email')
                                            <span class="invalid-feedback text-danger text-xs"
                                                role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                @if (\Auth::user()->type == 'employee')
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="col-form-label text-dark">{{ __('Birthdate') }}</label>
                                            {{ Form::date('birthdate', $userDetail?->employee?->dob, [
                                                    'class' => 'form-control d_week', 'required' => 'required', 'autocomplete'=>'birthdate',
                                                    'id'=>'birthdate', 'required'=>'required', 'name'=>'birthdate'
                                                ])
                                            }}
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="phone" class="col-form-label text-dark">{{ __('Phone Number') }}</label>
                                            <input class="form-control" name="phone"
                                                type="text" id="phone" placeholder="{{ __('Enter Phone') }}"
                                                value="{{ $userDetail?->employee?->phone }}" required autocomplete="phone">
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('emergency_contact_number', __('Emergency Contact Number'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('emergency_contact_number', $userDetail?->employee?->emergency_contact_number, ['class' => 'form-control', 'required' => 'required' ,'placeholder'=>__('Enter Emergency Contact Number')]) !!}
                                        {{-- {!! Form::text('emergency_contact_number', old('emergency_contact_number'), null, ['class' => 'form-control', 'id' => 'emergency_contact_number', 'required' => 'required','placeholder' =>  __('Enter Emergency Contact Number')]) !!} --}}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('emergency_contact_relation', __('Emergency Contact Relation'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::select('emergency_contact_relation', $emergency_contact_relations, $userDetail?->employee?->emergency_contact_relation, ['class' => 'form-control', 'id' => 'emergency_contact_relation', 'required' => 'required','placeholder' =>  __('Select Emergency Contact Relation')]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('marital_status', __('Marital Status'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::select('marital_status', $marital_status, $userDetail?->employee?->marital_status, ['class' => 'form-control', 'id' => 'marital_status', 'required' => 'required','placeholder' =>  __('Select Marital Status')]) !!}
                                    </div>
                                    
                                    <div class="col-lg-12 col-sm-12">
                                        <div class="form-group">
                                            <label for="address" class="col-form-label text-dark">{{ __('Address') }}</label>
                                            {{ Form::textarea('address', $userDetail?->employee?->address, [
                                                    'class' => "form-control", 'rows' => '3', 'placeholder'=>__('Enter Your Address'),
                                                    'name' => 'address', 'required'=>'required', 'id'=>'location-input', 'autocomplete'=>'address'
                                                ])
                                            }}
                                            @error('address')
                                                <span class="invalid-feedback text-danger text-xs"
                                                    role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-sm-12">
                                        <div class="form-group">
                                            <label for="domicile_address" class="col-form-label text-dark">{{ __('Domicile Address') }}</label>
                                            {{ Form::textarea('domicile_address', $userDetail?->employee?->domicile_address, [
                                                    'class' => "form-control", 'rows' => '3', 'placeholder'=>__('Enter Your Domicile Address'),
                                                    'name' => 'domicile_address', 'required'=>'required', 'id'=>'location-input', 'autocomplete'=>'domicile_address'
                                                ])
                                            }}
                                            @error('domicile_address')
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
                                @endif
                                
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

                

                @if (\Auth::user()->type == 'employee')
                    <div id="useradd-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Bank') }}</h5>
                                <small> {{ __('Details about your bank information') }}</small>
                            </div>
                            <div class="card-body">
                                {{ Form::model($userDetail, ['route' => ['update.bank', $userDetail->id], 'method' => 'post']) }}

                                <div class="row">
                                    {{-- Banks --}}
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="bank_id" class="col-form-label text-dark">{{ __('Bank Name') }}</label>
                                            {{ Form::select('bank_id', $banks, $userDetail->employee?->bank_id, ['class' => 'form-control select2', 'id' => 'bank','placeholder' =>  __('Select Bank Name')]) }}
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="account_number" class="col-form-label text-dark">{{ __('Account Number') }}</label>
                                            <input class="form-control" name="account_number"
                                                type="text" id="account_number" placeholder="{{ __('Enter Account Number') }}"
                                                value="{{ $userDetail?->employee?->account_number }}" required autocomplete="account_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="account_holder_name" class="col-form-label text-dark">{{ __('Account Holder Name') }}</label>
                                            <input class="form-control" name="account_holder_name"
                                                type="text" id="account_holder_name" placeholder="{{ __('Enter Account Holder Name') }}"
                                                value="{{ $userDetail?->employee?->account_holder_name }}" required autocomplete="account_holder_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="tax_payer_id" class="col-form-label text-dark">{{ __('Tax Payer Id') }}</label>
                                            <input class="form-control" name="tax_payer_id"
                                                type="text" id="tax_payer_id" placeholder="{{ __('Enter Tax Payer Id') }}"
                                                value="{{ $userDetail?->employee?->tax_payer_id }}" required autocomplete="tax_payer_id">
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

                    <div id="useradd-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Nationality') }}</h5>
                                <small> {{ __('Details about your nationality information') }}</small>
                            </div>
                            <div class="card-body">
                                {{ Form::model($userDetail, ['route' => ['update.nationality', $userDetail->id], 'method' => 'post']) }}

                                <div class="row">
                                    {{-- Nationality --}}
                                    <div class="col-lg-4 col-sm-4">
                                        <div class="form-group">
                                            <label for="phone" class="col-form-label text-dark">{{ __('Nationality') }}</label>
                                            {{ Form::select('nationality', $nationalities, $userDetail->employee?->nationality, ['class' => 'form-control ', 'id' => 'nationality','placeholder' =>  __('Select Nationality')]) }}
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-4">
                                        <div class="form-group">
                                            <label for="phone" class="col-form-label text-dark">{{ __('Identity Type') }}</label>
                                            {{ Form::select('identity_type', $identity_types, $userDetail->employee?->identity_type, ['class' => 'form-control ', 'id' => 'identity_type','placeholder' =>  __('Select Identity Type')]) }}
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-4">
                                        <div class="form-group">
                                            <label for="identity_number" class="col-form-label text-dark">{{ __('Identity Number') }}</label>
                                            <input class="form-control" name="identity_number"
                                                type="text" id="identity_number" placeholder="{{ __('Enter Identity Number') }}"
                                                value="{{ $userDetail?->employee?->identity_number }}" required autocomplete="identity_number">
    
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
                    
                    <div id="useradd-5">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Document') }}</h5>
                                <small> {{ __('Details about your document information') }}</small>
                            </div>
                            <div class="card-body">
                                {{ Form::model($userDetail, ['route' => ['update.documents', $userDetail->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}

                                @php
                                    $employeedoc = $userDetail->employee?->documents()->pluck('document_value', __('document_id'));
                                @endphp
                                @foreach ($documents as $key => $document)
                                <div class="row">
                                    <div class="form-group col-12 d-flex">
                                        <div class="float-left col-4">
                                            <label for="document"
                                                class="float-left pt-1 form-label">{{ $document->name }} @if ($document->is_required == 1)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            <div class="info">
                                                <span>
                                                    <a href="{{ !empty($employeedoc[$document->id]) ? asset(Storage::url('uploads/document')) . '/' . $employeedoc[$document->id] : '' }}"
                                                       class="btn btn-primary btn-sm" target="_blank" data-bs-toggle="tooltip" disabled
                                                       data-bs-original-title="{{ !empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '' }}"
                                                       title="{{ !empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '' }}">
                                                       <i class="ti ti-eye"></i> Show File
                                                    </a>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="float-right col-8">
                                            
                                            <input type="hidden" name="emp_doc_id[{{ $document->id }}]" id=""
                                                value="{{ $document->id }}">

                                            <div class="choose-files ">
                                                <label for="document[{{ $document->id }}]">
                                                    <div class=" bg-primary document "> <i
                                                            class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                    </div>
                                                    <input type="file"
                                                        class="form-control file   @error('document') is-invalid @enderror "
                                                        {{-- @if ($document->is_required == 1) required @endif --}}
                                                        name="document[{{ $document->id }}]" id="document[{{ $document->id }}]"
                                                        data-filename="{{ $document->id . '_filename' }}" onchange="document.getElementById('{{'blah'.$key}}').src = window.URL.createObjectURL(this.files[0])">
                                                </label>
                                                {{-- <a href="#"><p class="{{ $document->id . '_filename' }} "></p></a> --}}
                                                <img id="{{'blah'.$key}}" src=""  width="75%" />

                                            </div>

                                            
                                            {{-- @foreach ($documents as $key => $document)
                                                <div class="col-md-12">
                                                    <div class="info">
                                                        <strong>{{ $document->name }}</strong>
                                                        <span><a href="{{ !empty($employeedoc[$document->id]) ? asset(Storage::url('uploads/document')) . '/' . $employeedoc[$document->id] : '' }}"
                                                                target="_blank">{{ !empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '' }}</a></span>
                                                    </div>
                                                </div>
                                            @endforeach --}}
                                        </div>

                                    </div>
                                </div>
                                <hr>

                            @endforeach

                                <div class="modal-footer pr-0">
                                    {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                @endif
                
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
