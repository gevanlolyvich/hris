@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave Office Permit') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave Office') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('leave-office.export', ['url' => url()->full()]) }}" class="btn btn-sm btn-success mx-2" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="#" data-url="{{ route('leave-office.create') }}" data-ajax-popup="true" data-size="lg"
        data-title="{{ __('Create Leave Office Permit') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
        data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
@endsection

@section('content')
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                {{ Form::open(array('route' => array('leave-office.index'),'method'=>'get','id'=>'filter')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-3">
                                    <label class="col-form-label">{{__('Type')}}</label>
                                    <br>
                                    <div class="form-check form-check-inline form-group">
                                        <input type="radio" id="monthly" value="monthly" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='monthly' ?'checked':'checked'}}>
                                        <label class="form-check-label" for="monthly">{{__('Monthly')}}</label>
                                    </div>
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="daily" value="daily" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='daily' ?'checked':''}}>
                                            <label class="form-check-label" for="daily">{{__('Daily')}}</label>
                                        </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                    <div class="btn-box">
                                        {{Form::label('month',__('Month'),['class'=>'col-form-label'])}}
                                        {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control month-btn'))}}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'),['class'=>'col-form-label'])}}
                                        {{ Form::date('date',isset($_GET['date'])?$_GET['date']:date('Y-m-d'), array('class' => 'form-control month-btn')) }}
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('branch', __('Branch'),['class'=>'col-form-label'])}}
                                        {{ Form::select('branch', $branch,isset($_GET['branch'])?$_GET['branch']:'', ['class' => 'form-control select2', 'placeholder' => __('Select Branch')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{route('leave-office.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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

    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Location') }}</th>
                            <th>{{ __('Need') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Leave Time') }}</th>
                            <th>{{ __('Return Time') }}</th>
                            <th>{{ __('Approval') }}</th>
                            <th width="200px">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaves as $leave)
                            <tr>
                                <td>{{ $leave?->employee?->name ?? '-' }}</td>
                                <td>{{ $leave?->date ?? '-' }}</td>
                                <td>{{ Str::limit($leave?->location ?? '-', 20) }}</td>
                                <td>{{ Str::limit($leave?->need ?? '-', 20) }}</td>
                                <td>
                                    @if ($leave->status == 'Pending' || $leave->status == 'Waiting Superior' || $leave->status == 'Waiting HR')
                                        <div class="badge bg-warning p-2 px-3 rounded">{{ __($leave->status) }}</div>
                                    @elseif($leave->status == 'Approved')
                                        <div class="badge bg-success p-2 px-3 rounded">{{ __($leave->status) }}</div>
                                    @elseif($leave->status == "Rejected By HR" || $leave->status == "Rejected By Superior")
                                        <div class="badge bg-danger p-2 px-3 rounded">{{ __($leave->status) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($leave->leave)
                                        <button type="button" class="btn btn-primary btn-sm" disabled="disabled">
                                            {{ $leave->leave}}
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($leave->return)
                                    @php
                                        $leaveReturnParts = explode(' ', $leave->return);
                                        $return_time = array_pop($leaveReturnParts);
                                    @endphp
                                        <button type="button" class="btn btn-info btn-sm" disabled="disabled">
                                            {{ $return_time }}
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="action-btn bg-warning ms-2">
                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                            data-url="{{ route('leave-office.show', $leave->id) }}"
                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                            title="" data-title="{{ __('Leave Office Approval') }}"
                                            data-bs-original-title="{{ __('Approval') }}">
                                            <i class="ti ti-caret-right text-white"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="action">
                                    <span>
                                        @if ($leave->status == 'Approved' || $leave->status == 'Waiting HR')
                                            <div class="action-btn bg-warning ms-2">
                                                <button class="btn @if ($leave->return) btn-success @else btn-primary @endif btn-sm leave-input" data-bs-toggle="tooltip" data-size="xl"
                                                    data-url="{{ route('leave-office.getTime', $leave->id) }}"
                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Return Office Time') }}"
                                                    data-bs-original-title="{{ __('Return Office Time') }}">
                                                    <i class="fa fa-solid fa-clock"></i>
                                                </button>
                                            </div>
                                        @else
                                            <div class="action-btn ms-2">
                                                <button class="btn btn-secondary btn-sm leave-input" disabled="disabled" data-bs-toggle="tooltip" data-size="xl"
                                                    data-ajax-popup="true"
                                                    data-bs-original-title="{{ __('Return Office Time') }}">
                                                    <i class="fa fa-solid fa-clock"></i>
                                                </button>
                                            </div>
                                        @endif
                                        @if (\Auth::user()->employee?->id == $leave->employee_id || \Auth::user()->type != 'employee')
                                            @can('Edit Leave Office')
                                                @if ($leave->status != 'Approved')
                                                    <div class="action-btn ms-2">
                                                        <a href="#" class="mx-3 btn btn-info btn-sm align-items-center" 
                                                            data-url="{{  route('leave-office.edit', $leave->id) }}"
                                                            data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Update Leave Office Permit') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endcan
                                            @can('Delete Leave Office')
                                                <div class="action-btn ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['leave-office.destroy', $leave->id], 'id' => 'delete-form-' . $leave->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-danger btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title={{ __("Delete")}}
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    </form>
                                                </div>
                                            @endcan
                                        @endif
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
        $('input[name="type"]:radio').on('change', function(e) {
            var type = $(this).val();

            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');
                $('.date').addClass('d-none');
                $('.date').removeClass('d-block');
            } else {
                $('.date').addClass('d-block');
                $('.date').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });

        $('input[name="type"]:radio:checked').trigger('change');

        async function getLocation() {
            return new Promise((resolve, reject) => {
                if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    const latElement = document.getElementById("latitude");
                    const longElement = document.getElementById("longitude");
                    const accElement = document.getElementById("accuracy");

                    if (latElement) {
                        latElement.value = latitude;
                    }
                    if (longElement) {
                        longElement.value = longitude
                    }
                    if (accElement) {
                        accElement.value = accuracy;
                    }

                    console.log('--------------------------------');
                    console.log(latElement.value);
                    console.log(longElement.value);
                    console.log(accElement.value);
                    console.log('--------------------------------');

                    resolve({ latitude, longitude, accuracy });
                    },
                    (error) => {
                    if (error.code === 1) {
                        alert("User denied Geolocation");
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

        async function handleLocationAndSubmit() {
            try {
                await getLocation(); // Wait until location is retrieved
                document.getElementById("leave-form").submit(); // Submit the form after location is set
            } catch (error) {
                console.error("Error retrieving location:", error);
            }
        }

        $(document).ready(function () {
            var width = 320; // We will scale the photo width to this
            var height = 0; // This will be computed based on the input stream

            var streaming = false;

            var video = null;
            var canvas = null;
            var photo = null;
            var takepic = null;


            function startup() {
                video = document.getElementById('video');
                canvas = document.getElementById('canvas');
                photo = document.getElementById('photo');
                takepic = document.getElementById('takepic');

                navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    })
                    .then(function(stream) {
                        document.getElementById('load').style.display = 'none';
                        document.getElementById('camera').style.display = 'block';
                        document.getElementById('output').style.display = 'block';

                        takepic.style.display = '';
                        video.srcObject = stream;
                        video.play();
                    })
                    .catch(function(err) {
                        alert("Please Allow Camera Access To Take Picture");
                        console.log("An error occurred: " + err);
                    });

                video.addEventListener('canplay', function(ev) {
                    if (!streaming) {
                        height = video.videoHeight / (video.videoWidth / width);

                        if (isNaN(height)) {
                            height = width / (4 / 3);
                        }

                        video.setAttribute('width', width);
                        video.setAttribute('height', height);
                        document.getElementById('camera').style.width = width;
                        document.getElementById('camera').style.height = height;
                        canvas.setAttribute('width', width);
                        canvas.setAttribute('height', height);
                        photo.setAttribute('width', width);
                        photo.setAttribute('height', height);
                        streaming = true;
                    }
                }, false);

                takepic.addEventListener('click', function(ev) {
                    takepicture();
                    ev.preventDefault();
                }, false);

                clearphoto();
            }

            function clearphoto() {
                var context = canvas.getContext('2d');
                context.fillStyle = "#AAA";
                context.fillRect(0, 0, canvas.width, canvas.height);

                var data = canvas.toDataURL('image/png');
                photo.setAttribute('src', data);
            }

            function takepicture() {
                var context = canvas.getContext('2d');
                if (width && height) {
                    canvas.width = width;
                    canvas.height = height;
                    context.drawImage(video, 0, 0, width, height);

                    var data = canvas.toDataURL('image/png');
                    photo.setAttribute('src', data);
                    document.getElementById('picture').value = data;
                } else {
                    clearphoto();
                }
            }

            $('#commonModal').on('shown.bs.modal', function () {
                $('.status').on('click', function () {
                    $('#commonModal').modal('hide');
                    
                    var buttonValue = $(this).data("status");
                    $("#hiddenStatus").val(buttonValue);
                })

                var loadbutton = document.getElementById('load');

                if (loadbutton) {
                    loadbutton.addEventListener('click', startup, false);
                }

            });

            $('#commonModal').on('hidden.bs.modal', function () {
                let loadElement = document.getElementById('load');
                let cameraElement = document.getElementById('camera');
                let outputElement = document.getElementById('output');

                if (loadElement) {
                    loadElement.style.display = '';
                }
                if (cameraElement) {
                    cameraElement.style.display = 'none';
                }
                if (outputElement) {
                    outputElement.style.display = 'none';
                }
            
                var tracks = video?.srcObject?.getTracks();
                tracks?.forEach(track => track.stop());
                streaming = false;
                if (video) {
                    video.srcObject = null;
                }
            });
        });

    </script>

    <script>
        /* JS comes here */
        (function() {
        })();
    </script>

    <script>
        // $(document).ready(function() {
        //     let map = null;
        //     let mapReturn = null;
        //     let mapReturn = null;
        //     let clockIn = null;
        //     let clockOut = null;
        //     let coordIn = null;
        //     let coordReturn = null;
        //     let pictureIn = null;
        //     let pictureReturn = null;
        //     let overtimeDate = null;

        //     $('body').on('click', '#time-input', async function() {
        //         try {
        //             const { latitude, longitude, accuracy } = await getLocation();

        //             const latElement = document.getElementById("latitude");
        //             const longElement = document.getElementById("longitude");
        //             const accElement = document.getElementById("accuracy");

        //             if (latElement) {
        //                 latElement.value = latitude;
        //             }
        //             if (longElement) {
        //                 longElement.value = longitude
        //             }
        //             if (accElement) {
        //                 accElement.value = accuracy;
        //             }
        //         } catch (error) {
        //             // console.log(error);
        //             if (error.message === "User denied Geolocation") {
        //             // Handle the case where the user denied geolocation access
        //             const clockInButton = document.getElementById("clock_in");
        //             const returnButton = document.getElementById("return");
        //             if (clockInButton) {
        //                 clockInButton.disabled = true;
        //             }
        //             if (returnButton) {
        //                 returnButton.disabled = true;
        //             }
        //             }
        //         }
            
        //         // Open the modal
        //         $('#leaveReturnInputModal').modal('show');
        //     });

        //     $('body').on('click', '.clock-data', function () {
        //         $('#leaveReturnDataModal').modal('show');

        //         clockIn = $(this).data('leave');
        //         clockOut = $(this).data('clock-out');
        //         coordIn = $(this).data('coord-in')?.split(', ');
        //         coordOut = $(this).data('coord-out')?.split(', ');
        //         pictureIn = $(this).data('picture-in');
        //         pictureOut = $(this).data('picture-out');

        //         if (clockIn?.length || clockOut?.length) {
        //             document.getElementById('clock-data-not-exist').style.display = 'none';
        //         }

        //         if (clockIn?.length || coordIn?.length > 1 || pictureIn?.length) {
        //             document.getElementById('leave-data').style.display = '';

        //             if (clockIn?.length) {
        //                 document.getElementById('leave-hours').style.display = '';
        //                 document.getElementById('leave-hours').textContent = clockIn;
        //             }
        //             if (pictureIn?.length) {
        //                 document.getElementById('photosIn').style.display = '';
        //                 $('#clockImageIn').attr('src', pictureIn)
        //             }
        //         }

        //         if (clockOut?.length || coordOut?.length > 1 || pictureOut?.length) {
        //             document.getElementById('return-data').style.display = '';

        //             if (clockOut?.length) {
        //                 document.getElementById('clock-out-hours').style.display = '';
        //                 document.getElementById('clock-out-hours').textContent = clockOut;
        //             }
        //             if (pictureOut?.length) {
        //                 document.getElementById('photosOut').style.display = '';
        //                 $('#clockImageOut').attr('src', pictureOut)
        //             }
        //         }

        //         $('#leaveReturnDataModal').on('shown.bs.modal', function () {
        //             if (mapIn !== null) {
        //                 mapIn?.remove();
        //             }
        //             if (mapOut !== null) {
        //                 mapOut?.remove();
        //             }
                
        //             if (coordIn?.length > 1) {
        //                 document.getElementById('mapIn').style.display = '';
                        
        //                 mapIn = L.map('openStreetMapContainerIn').setView([coordIn[0], coordIn[1]], 17);
        //                 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        //                     attribution: '© OpenStreetMap contributors'
        //                 }).addTo(mapIn);
                    
        //                 // Add a marker for the location
        //                 var marker = L.marker([coordIn[0], coordIn[1]]).addTo(mapIn);
                    
        //                 // Add a circle with the converted radius
        //                 var circle = L.circle([coordIn[0], coordIn[1]], {
        //                     color: 'blue',
        //                     fillColor: '#f0023',
        //                     fillOpacity: 0.2,
        //                     radius: coordIn[2],
        //                 }).addTo(mapIn);
        //             }

        //             if (coordOut?.length > 1) {
        //                 document.getElementById('mapOut').style.display = '';
                        
        //                 mapOut = L.map('openStreetMapContainerOut').setView([coordOut[0], coordOut[1]], 17);
        //                 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        //                     attribution: '© OpenStreetMap contributors'
        //                 }).addTo(mapOut);
                    
        //                 // Add a marker for the location
        //                 var marker = L.marker([coordOut[0], coordOut[1]]).addTo(mapOut);
                    
        //                 // Add a circle with the converted radius
        //                 var circle = L.circle([coordOut[0], coordOut[1]], {
        //                     color: 'blue',
        //                     fillColor: '#f0023',
        //                     fillOpacity: 0.2,
        //                     radius: coordOut[2],
        //                 }).addTo(mapOut);
        //             }
        //         })
        //     })

        //     $('#leaveReturnInputModal').on('hidden.bs.modal', function () {
        //         document.getElementById('load').style.display = '';
        //         document.getElementById('camera').style.display = 'none';
        //         document.getElementById('output').style.display = 'none';
            
        //         var tracks = video?.srcObject?.getTracks();
        //         tracks?.forEach(track => track.stop());
        //         video.srcObject = null;
        //     });

        //     $('#leaveReturnDataModal').on('hidden.bs.modal', function () {
        //         document.getElementById('leave-data').style.display = 'none';
        //         document.getElementById('clock-out-data').style.display = 'none';

        //         document.getElementById('leave-hours').style.display = 'none';
        //         document.getElementById('clock-out-hours').style.display = 'none';

        //         document.getElementById('photosIn').style.display = 'none';
        //         document.getElementById('photosOut').style.display = 'none';

        //         document.getElementById('clock-data-not-exist').style.display = '';

        //         // Remove the map instances and their containers
        //         if (mapIn !== null && coordIn?.length > 1) {
        //             mapIn.remove();
        //             mapIn = null;
        //             document.getElementById('mapIn').style.display = 'none';
        //         }
            
        //         if (mapOut !== null && coordOut?.length > 1) {
        //             mapOut.remove();
        //             mapOut = null;
        //             document.getElementById('mapOut').style.display = 'none';
        //         }
        //         // document.getElementById('mapOut').style.display = 'none';

        //         clockIn = null;
        //         clockOut = null;
        //         coordIn = null;
        //         coordOut = null;
        //         pictureIn = null;
        //         pictureOut = null;
        //     });
        // });
    </script>
@endpush

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
        #openStreetMapContainerLeave {
            height: 200px;
            width: 100%;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
            border-bottom-left-radius: 10px;
        }
        #openStreetMapContainerReturn {
            height: 200px;
            width: 100%;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .custBtn{
            position: absolute;
            top: 83%;
            left: 50%;
            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            border: none;
            cursor: pointer;
        }
    </style>
@endpush
