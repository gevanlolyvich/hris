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
                            <a href="#useradd-6"
                                class="list-group-item list-group-item-action border-0">{{ __('Certificates') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-7"
                                class="list-group-item list-group-item-action border-0">{{ __('CV') }} <div
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
                                                    'name' => 'address', 'required'=>'required', 'autocomplete'=>'address'
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
                                        <div class="">
                                            <label for="profile">
                                                <div class="btn btn-md bg-primary profile text-white"> <i
                                                        class="ti ti-upload px-1"></i>{{ __('Upload Profile Photo') }}
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
                                        <span class="text-xs text-muted">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.') }}</span>
                                    @error('profile')
                                        <span class="invalid-feedback text-danger text-xs"
                                            role="alert">{{ $message }}</span>
                                    @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('emergency_contact_photo', __('Emergency Contact Photo'), ['class' => 'col-form-label']) }}
                                        <div class="">
                                            <label for="emergency_contact_photo">
                                                <div class="btn btn-md bg-primary emergency_contact_photo text-white"> <i
                                                        class="ti ti-upload px-1"></i>{{ __('Upload Emergency Contact Photo') }}
                                                </div>
                                                <input type="file" class="form-control file" name="emergency_contact_photo" id="emergency_contact_photo" onchange="document.getElementById('blah2').src = window.URL.createObjectURL(this.files[0])">
                                                <span class="theme-avtar" style="width: 150px; height: 150px; overflow: hidden; border-radius: 50%;">
                                                    <img alt="#" id="blah2"
                                                        src="{{ !empty($userDetail->employee->emergency_contact_photo) ? $userDetail->employee->emergency_contact_photo : $profile . '/avatar.png' }}"
                                                        class="header-avtar" style="width: 100%; height: 100%; object-fit: cover;">
                                                    {{-- <img id="blah"  width="100" src="{{ !empty($userDetail->avatar) ? $profile . $userDetail->avatar : $profile . '/avatar.png' }}" /> --}}
                                                </span>   
                                            </label>
                                        </div>
                                        <span class="text-xs text-muted">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.') }}</span>
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
                                    $employeedoc = $userDetail->employee?->documents()->pluck('document_value','document_id');
                                    // echo $employeedoc;
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
                                                    @if (!empty($employeedoc[$document->id]))
                                                        <a href="{{ !empty($employeedoc[$document->id]) ? asset(Storage::url('uploads/document')) . '/' . $employeedoc[$document->id] : '' }}"
                                                        class="btn btn-primary btn-sm" target="_blank" data-bs-toggle="tooltip" disabled
                                                        data-bs-original-title="{{ !empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '' }}"
                                                        title="{{ !empty($employeedoc[$document->id]) ? $employeedoc[$document->id] : '' }}">
                                                        <i class="ti ti-eye"></i> Show File
                                                        </a>
                                                    @endif
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
                                                {{-- <img id="{{'blah'.$key}}" src=""  width="75%" /> --}}

                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <hr>

                            @endforeach

                            
                            @if (count($documents))
                        
                            <div class="modal-footer pr-0">
                                {{ Form::submit(__('Save Changes'), ['class' => 'btn  btn-primary']) }}
                            </div>
                            @endif
                            {{ Form::close() }}
                            </div>
                        </div>
                    </div>

                    <div id="useradd-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Certificates') }}</h5>
                                <small> {{ __('Upload your certificates (you can upload more than one)') }}</small>
                            </div>
                            <div class="card-body">
                                @if (count($certificates))
                                    <div class="table-responsive">
                                        <table class="table table-border-style">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Issuer') }}</th>
                                                    <th>{{ __('Issue Date') }}</th>
                                                    <th>{{ __('Expiry Date') }}</th>
                                                    <th>{{ __('File') }}</th>
                                                    <th class="text-end">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($certificates as $certificate)
                                                    <tr>
                                                        <td>{{ $certificate->name }}</td>
                                                        <td>{{ $certificate->issuer ?? '-' }}</td>
                                                        <td>{{ !empty($certificate->issue_date) ? \Auth::user()->dateFormat($certificate->issue_date) : '-' }}</td>
                                                        <td>{{ !empty($certificate->expiry_date) ? \Auth::user()->dateFormat($certificate->expiry_date) : '-' }}</td>
                                                        <td>
                                                            <a href="{{ $certificate->file }}" target="_blank" class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['delete.certificate', $certificate->id], 'id' => 'delete-certificate-' . $certificate->id]) !!}
                                                            <a href="#" class="bs-pass-para btn btn-sm btn-danger" data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-certificate-{{ $certificate->id }}" title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted">
                                        {{ __('No certificate uploaded yet.') }}
                                    </div>
                                @endif

                                <hr>

                                <h6 class="mb-3">{{ __('Upload New Certificate') }}</h6>
                                {{ Form::open(['route' => ['store.certificate'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {{ Form::label('name', __('Certificate Name'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Certificate Name')]) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('issuer', __('Issuer / Institution'), ['class' => 'form-label']) }}
                                        {{ Form::text('issuer', null, ['class' => 'form-control', 'placeholder' => __('Enter Issuer / Institution')]) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('issue_date', null, ['class' => 'form-control']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('expiry_date', __('Expiry Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('expiry_date', null, ['class' => 'form-control']) }}
                                    </div>
                                    <div class="form-group col-md-12">
                                        {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                        {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Description (optional)')]) }}
                                    </div>
                                    <div class="form-group col-md-12">
                                        {{ Form::label('file', __('Certificate File'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        <div class="choose-files">
                                            <label for="certificate_file">
                                                <div class="bg-primary document text-white px-3 py-2 rounded"><i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                                <input type="file" class="form-control file" name="file" id="certificate_file" required>
                                            </label>
                                        </div>
                                        <small class="text-muted">{{ __('Allowed: jpeg, png, jpg, gif, svg, pdf, doc, zip, docx, xls, xlsx, ppt, pptx (max 10MB)') }}</small>
                                    </div>
                                </div>
                                <div class="modal-footer pr-0">
                                    {{ Form::submit(__('Upload Certificate'), ['class' => 'btn btn-primary']) }}
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>

                    <div id="useradd-7">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">{{ __('CV') }}</h5>
                                    <small> {{ __('Fill in your CV information. Personal data is taken from your profile') }}</small>
                                </div>
                                <div>
                                    <a href="{{ route('cv.show') }}" target="_blank" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View CV') }}">
                                        <i class="ti ti-file-text"></i> {{ __('View CV') }}
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @php
                                    $cv                = $userDetail->employee?->cv;
                                    $cvExperiences     = $userDetail->employee?->cvExperiences;
                                    $cvEducations      = $userDetail->employee?->cvEducations;
                                    $cvSkills          = $userDetail->employee?->cvSkills;
                                    $cvLanguages       = $userDetail->employee?->cvLanguages;
                                    $skillProficiencies = ['Beginner' => __('Beginner'), 'Intermediate' => __('Intermediate'), 'Advanced' => __('Advanced'), 'Expert' => __('Expert')];
                                    $languageProficiencies = ['Basic' => __('Basic'), 'Conversational' => __('Conversational'), 'Fluent' => __('Fluent'), 'Native' => __('Native')];
                                @endphp

                                {{ Form::open(['route' => ['update.cv'], 'method' => 'post', 'id' => 'cv-form']) }}
                                @csrf

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        {{ Form::label('summary', __('Professional Summary'), ['class' => 'form-label']) }}
                                        {{ Form::textarea('summary', $cv?->summary, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Write a short professional summary about yourself')]) }}
                                    </div>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ __('Work Experience') }}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-experience"><i class="ti ti-plus"></i> {{ __('Add Experience') }}</button>
                                </div>
                                <div id="experience-wrapper">
                                    @forelse ($cvExperiences as $exp)
                                        <div class="row cv-row experience-row mt-2">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('experience_company[]', __('Company'), ['class' => 'form-label']) }}
                                                {{ Form::text('experience_company[]', $exp->company, ['class' => 'form-control', 'placeholder' => __('Enter Company')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('experience_position[]', __('Position'), ['class' => 'form-label']) }}
                                                {{ Form::text('experience_position[]', $exp->position, ['class' => 'form-control', 'placeholder' => __('Enter Position')]) }}
                                            </div>
                                            <div class="form-group col-md-4">
                                                {{ Form::label('experience_start_date[]', __('Start Date'), ['class' => 'form-label']) }}
                                                {{ Form::date('experience_start_date[]', $exp->start_date, ['class' => 'form-control']) }}
                                            </div>
                                            <div class="form-group col-md-4">
                                                {{ Form::label('experience_end_date[]', __('End Date'), ['class' => 'form-label']) }}
                                                {{ Form::date('experience_end_date[]', $exp->end_date, ['class' => 'form-control']) }}
                                            </div>
                                            <div class="form-group col-md-4 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-danger remove-cv-row mb-2"><i class="ti ti-trash"></i></button>
                                            </div>
                                            <div class="form-group col-md-12">
                                                {{ Form::label('experience_description[]', __('Description'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('experience_description[]', $exp->description, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Describe your responsibilities and achievements')]) }}
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted mt-2">{{ __('No work experience added yet.') }}</div>
                                    @endforelse
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ __('Education') }}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-education"><i class="ti ti-plus"></i> {{ __('Add Education') }}</button>
                                </div>
                                <div id="education-wrapper">
                                    @forelse ($cvEducations as $edu)
                                        <div class="row cv-row education-row mt-2">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('education_institution[]', __('Institution'), ['class' => 'form-label']) }}
                                                {{ Form::text('education_institution[]', $edu->institution, ['class' => 'form-control', 'placeholder' => __('Enter Institution')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('education_degree[]', __('Degree'), ['class' => 'form-label']) }}
                                                {{ Form::text('education_degree[]', $edu->degree, ['class' => 'form-control', 'placeholder' => __('e.g. S1 / Bachelor')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('education_field_of_study[]', __('Field of Study'), ['class' => 'form-label']) }}
                                                {{ Form::text('education_field_of_study[]', $edu->field_of_study, ['class' => 'form-control', 'placeholder' => __('e.g. Computer Science')]) }}
                                            </div>
                                            <div class="form-group col-md-3">
                                                {{ Form::label('education_start_year[]', __('Start Year'), ['class' => 'form-label']) }}
                                                {{ Form::number('education_start_year[]', $edu->start_year, ['class' => 'form-control', 'min' => '1900', 'max' => '2100']) }}
                                            </div>
                                            <div class="form-group col-md-3">
                                                {{ Form::label('education_end_year[]', __('End Year'), ['class' => 'form-label']) }}
                                                {{ Form::number('education_end_year[]', $edu->end_year, ['class' => 'form-control', 'min' => '1900', 'max' => '2100']) }}
                                            </div>
                                            <div class="form-group col-md-3">
                                                {{ Form::label('education_gpa[]', __('GPA'), ['class' => 'form-label']) }}
                                                {{ Form::text('education_gpa[]', $edu->gpa, ['class' => 'form-control', 'placeholder' => __('e.g. 3.50')]) }}
                                            </div>
                                            <div class="form-group col-md-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-danger remove-cv-row mb-2"><i class="ti ti-trash"></i></button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted mt-2">{{ __('No education added yet.') }}</div>
                                    @endforelse
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ __('Skills') }}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-skill"><i class="ti ti-plus"></i> {{ __('Add Skill') }}</button>
                                </div>
                                <div id="skill-wrapper">
                                    @forelse ($cvSkills as $skill)
                                        <div class="row cv-row skill-row mt-2">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('skill[]', __('Skill'), ['class' => 'form-label']) }}
                                                {{ Form::text('skill[]', $skill->skill, ['class' => 'form-control', 'placeholder' => __('e.g. PHP, Laravel, Excel')]) }}
                                            </div>
                                            <div class="form-group col-md-4">
                                                {{ Form::label('skill_proficiency[]', __('Proficiency'), ['class' => 'form-label']) }}
                                                {{ Form::select('skill_proficiency[]', $skillProficiencies, $skill->proficiency, ['class' => 'form-control', 'placeholder' => __('Select Proficiency')]) }}
                                            </div>
                                            <div class="form-group col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-danger remove-cv-row mb-2"><i class="ti ti-trash"></i></button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted mt-2">{{ __('No skills added yet.') }}</div>
                                    @endforelse
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ __('Languages') }}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-language"><i class="ti ti-plus"></i> {{ __('Add Language') }}</button>
                                </div>
                                <div id="language-wrapper">
                                    @forelse ($cvLanguages as $lang)
                                        <div class="row cv-row language-row mt-2">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('language[]', __('Language'), ['class' => 'form-label']) }}
                                                {{ Form::text('language[]', $lang->language, ['class' => 'form-control', 'placeholder' => __('e.g. Indonesian, English')]) }}
                                            </div>
                                            <div class="form-group col-md-4">
                                                {{ Form::label('language_proficiency[]', __('Proficiency'), ['class' => 'form-label']) }}
                                                {{ Form::select('language_proficiency[]', $languageProficiencies, $lang->proficiency, ['class' => 'form-control', 'placeholder' => __('Select Proficiency')]) }}
                                            </div>
                                            <div class="form-group col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-danger remove-cv-row mb-2"><i class="ti ti-trash"></i></button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted mt-2">{{ __('No languages added yet.') }}</div>
                                    @endforelse
                                </div>

                                <div class="modal-footer pr-0">
                                    {{ Form::submit(__('Save CV'), ['class' => 'btn btn-primary']) }}
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

@push('script-page')
    <script>
        function cvRowHtml(type) {
            var skillOptions = '<option value="">{{ __('Select Proficiency') }}</option>' +
                '<option value="Beginner">{{ __('Beginner') }}</option>' +
                '<option value="Intermediate">{{ __('Intermediate') }}</option>' +
                '<option value="Advanced">{{ __('Advanced') }}</option>' +
                '<option value="Expert">{{ __('Expert') }}</option>';
            var languageOptions = '<option value="">{{ __('Select Proficiency') }}</option>' +
                '<option value="Basic">{{ __('Basic') }}</option>' +
                '<option value="Conversational">{{ __('Conversational') }}</option>' +
                '<option value="Fluent">{{ __('Fluent') }}</option>' +
                '<option value="Native">{{ __('Native') }}</option>';

            if (type === 'experience') {
                return '<div class="row cv-row experience-row mt-2">' +
                    '<div class="form-group col-md-6">' +
                    '<label class="form-label">{{ __('Company') }}</label>' +
                    '<input type="text" name="experience_company[]" class="form-control" placeholder="{{ __('Enter Company') }}">' +
                    '</div>' +
                    '<div class="form-group col-md-6">' +
                    '<label class="form-label">{{ __('Position') }}</label>' +
                    '<input type="text" name="experience_position[]" class="form-control" placeholder="{{ __('Enter Position') }}">' +
                    '</div>' +
                    '<div class="form-group col-md-4">' +
                    '<label class="form-label">{{ __('Start Date') }}</label>' +
                    '<input type="date" name="experience_start_date[]" class="form-control">' +
                    '</div>' +
                    '<div class="form-group col-md-4">' +
                    '<label class="form-label">{{ __('End Date') }}</label>' +
                    '<input type="date" name="experience_end_date[]" class="form-control">' +
                    '</div>' +
                    '<div class="form-group col-md-4 d-flex align-items-end">' +
                    '<button type="button" class="btn btn-sm btn-danger remove-cv-row mb-2"><i class="ti ti-trash"></i></button>' +
                    '</div>' +
                    '<div class="form-group col-md-12">' +
                    '<label class="form-label">{{ __('Description') }}</label>' +
                    '<textarea name="experience_description[]" class="form-control" rows="2" placeholder="{{ __('Describe your responsibilities and achievements') }}"></textarea>' +
                    '</div>' +
                    '</div>';
            }

            if (type === 'education') {
                return '<div class="row cv-row education-row mt-2">' +
                    '<div class="form-group col-md-6">' +
                    '<label class="form-label">{{ __('Institution') }}</label>' +
                    '<input type="text" name="education_institution[]" class="form-control" placeholder="{{ __('Enter Institution') }}">' +
                    '</div>' +
                    '<div class="form-group col-md-6">' +
                    '<label class="form-label">{{ __('Degree') }}</label>' +
                    '<input type="text" name="education_degree[]" class="form-control" placeholder="{{ __('e.g. S1 / Bachelor') }}">' +
                    '</div>' +
                    '<div class="form-group col-md-6">' +
                    '<label class="form-label">{{ __('Field of Study') }}</label>' +
                    '<input type="text" name="education_field_of_study[]" class="form-control" placeholder="{{ __('e.g. Computer Science') }}">' +
                    '</div>' +
                    '<div class="form-group col-md-3">' +
                    '<label class="form-label">{{ __('Start Year') }}</label>' +
                    '<input type="number" name="education_start_year[]" class="form-control" min="1900" max="2100">' +
                    '</div>' +
                    '<div class="form-group col-md-3">' +
                    '<label class="form-label">{{ __('End Year') }}</label>' +
                    '<input type="number" name="education_end_year[]" class="form-control" min="1900" max="2100">' +
                    '</div>' +
                    '<div class="form-group col-md-3">' +
                    '<label class="form-label">{{ __('GPA') }}</label>' +
                    '<input type="text" name="education_gpa[]" class="form-control" placeholder="{{ __('e.g. 3.50') }}">' +
                    '</div>' +
                    '<div class="form-group col-md-3 d-flex align-items-end">' +
                    '<button type="button" class="btn btn-sm btn-danger remove-cv-row mb-2"><i class="ti ti-trash"></i></button>' +
                    '</div>' +
                    '</div>';
            }

            if (type === 'skill') {
                return '<div class="row cv-row skill-row mt-2">' +
                    '<div class="form-group col-md-6">' +
                    '<label class="form-label">{{ __('Skill') }}</label>' +
                    '<input type="text" name="skill[]" class="form-control" placeholder="{{ __('e.g. PHP, Laravel, Excel') }}">' +
                    '</div>' +
                    '<div class="form-group col-md-4">' +
                    '<label class="form-label">{{ __('Proficiency') }}</label>' +
                    '<select name="skill_proficiency[]" class="form-control">' + skillOptions + '</select>' +
                    '</div>' +
                    '<div class="form-group col-md-2 d-flex align-items-end">' +
                    '<button type="button" class="btn btn-sm btn-danger remove-cv-row mb-2"><i class="ti ti-trash"></i></button>' +
                    '</div>' +
                    '</div>';
            }

            if (type === 'language') {
                return '<div class="row cv-row language-row mt-2">' +
                    '<div class="form-group col-md-6">' +
                    '<label class="form-label">{{ __('Language') }}</label>' +
                    '<input type="text" name="language[]" class="form-control" placeholder="{{ __('e.g. Indonesian, English') }}">' +
                    '</div>' +
                    '<div class="form-group col-md-4">' +
                    '<label class="form-label">{{ __('Proficiency') }}</label>' +
                    '<select name="language_proficiency[]" class="form-control">' + languageOptions + '</select>' +
                    '</div>' +
                    '<div class="form-group col-md-2 d-flex align-items-end">' +
                    '<button type="button" class="btn btn-sm btn-danger remove-cv-row mb-2"><i class="ti ti-trash"></i></button>' +
                    '</div>' +
                    '</div>';
            }

            return '';
        }

        $(document).ready(function () {
            $('#add-experience').on('click', function () {
                $('#experience-wrapper').append(cvRowHtml('experience'));
                $('#experience-wrapper .text-center.text-muted').remove();
            });

            $('#add-education').on('click', function () {
                $('#education-wrapper').append(cvRowHtml('education'));
                $('#education-wrapper .text-center.text-muted').remove();
            });

            $('#add-skill').on('click', function () {
                $('#skill-wrapper').append(cvRowHtml('skill'));
                $('#skill-wrapper .text-center.text-muted').remove();
            });

            $('#add-language').on('click', function () {
                $('#language-wrapper').append(cvRowHtml('language'));
                $('#language-wrapper .text-center.text-muted').remove();
            });

            $(document).on('click', '.remove-cv-row', function () {
                $(this).closest('.cv-row').remove();
            });
        });
    </script>
@endpush
