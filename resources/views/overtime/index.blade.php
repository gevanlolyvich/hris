@extends('layouts.admin')
@section('page-title')
    {{ __('Overtime') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Overtime') }}</li>
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
    #openStreetMapContainerIn {
        height: 200px;
        width: 100%;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    #openStreetMapContainerOut {
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
    </script>

    <!-- Add this script at the end of your Blade template -->
    <script>
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

      $(document).ready(function() {
          let map = null;
          let mapIn = null;
          let mapOut = null;
          let clockIn = null;
          let clockOut = null;
          let coordIn = null;
          let coordOut = null;
          let pictureIn = null;
          let pictureOut = null;
          let overtimeDate = null;

          $('body').on('click', '.clock-input', async function() {
              try {
                  let overtimeId = $(this).data('overtime-id');
                  document.getElementById('overtimeId').value = overtimeId;
                  document.getElementById('overtimeIdOut').value = overtimeId;

                  const { latitude, longitude, accuracy } = await getLocation();

                  const latElement = document.getElementById("latitude");
                  const longElement = document.getElementById("longitude");
                  const accElement = document.getElementById("accuracy");

                  const latOutElement = document.getElementById("latitude_out");
                  const longOutElement = document.getElementById("longitude_out");
                  const accOutElement = document.getElementById("accuracy_out");

                  if (latElement) {
                      latElement.value = latitude;
                  }
                  if (longElement) {
                      longElement.value = longitude
                  }
                  if (accElement) {
                      accElement.value = accuracy;
                  }

                  if (latOutElement) {
                      latOutElement.value = latitude;
                  }
                  if (longOutElement) {
                      longOutElement.value = longitude
                  }
                  if (accOutElement) {
                      accOutElement.value = accuracy;
                  }
                  
                  let clock_in = $(this).data('clock-in');
                  if (!clock_in){
                      document.getElementById("clock_in").disabled = false;
                      document.getElementById("clock_out").disabled = true;
                  }
              } catch (error) {
                  console.error(error);
                  if (error.message === "User denied Geolocation") {
                    // Handle the case where the user denied geolocation access
                    const clockInButton = document.getElementById("clock_in");
                    const clockOutButton = document.getElementById("clock_out");
                    if (clockInButton) {
                      clockInButton.disabled = true;
                    }
                    if (clockOutButton) {
                      clockOutButton.disabled = true;
                    }
                  }
              }
          
              // Open the modal
              $('#clockInOutInputModal').modal('show');
          });

          $('body').on('click', '.report-input', function() {
              // Open the modal
              $('#reportInputModal').modal('show');

              // Set the modal's data attributes
              // Get the values from the clicked button
              let overtimeId = $(this).data('overtime-id');
              overtimeDate = $(this).data('overtime-date');
              clockIn = $(this).data('clock-in');
              clockOut = $(this).data('clock-out');

              document.getElementById('overtimeIdReportInput').value = overtimeId;

              let documentFile = $(this).data('document');
              if (documentFile) {
                document.getElementById('exist-document-class').style.display = '';
                document.getElementById('exist-document-view').href = documentFile;
              }

              let notes = $(this).data('note');
              if (notes) {
                  document.getElementById('note').value = notes;
              }
          });

          $('body').on('click', '.report-data', function () {
              $('#reportDataModal').modal('show');

              let myDocument = $(this).data('document');
              let note = $(this).data('note');

              if (myDocument || note) {
                  document.getElementById('report-data-not-exist').style.display = 'none';
              }

              if (myDocument) {
                  document.getElementById('document-form-data').style.display = '';
                  document.getElementById('document-name').textContent = myDocument.split('/').pop();
                  document.getElementById('document-data').href = myDocument;
              }
              if (note) {
                  document.getElementById('note-form-data').style.display = '';
                  document.getElementById('note-data').textContent = note;
              }
          });

          $('body').on('click', '.clock-data', function () {
              $('#clockInOutDataModal').modal('show');

              clockIn = $(this).data('clock-in');
              clockOut = $(this).data('clock-out');
              coordIn = $(this).data('coord-in')?.split(', ');
              coordOut = $(this).data('coord-out')?.split(', ');
              pictureIn = $(this).data('picture-in');
              pictureOut = $(this).data('picture-out');

              if (clockIn?.length || clockOut?.length) {
                  document.getElementById('clock-data-not-exist').style.display = 'none';
              }

              if (clockIn?.length || coordIn?.length > 1 || pictureIn?.length) {
                  document.getElementById('clock-in-data').style.display = '';

                  if (clockIn?.length) {
                      document.getElementById('clock-in-hours').style.display = '';
                      document.getElementById('clock-in-hours').textContent = clockIn;
                  }
                  if (pictureIn?.length) {
                      document.getElementById('photosIn').style.display = '';
                      $('#clockImageIn').attr('src', pictureIn)
                  }
              }

              if (clockOut?.length || coordOut?.length > 1 || pictureOut?.length) {
                  document.getElementById('clock-out-data').style.display = '';

                  if (clockOut?.length) {
                      document.getElementById('clock-out-hours').style.display = '';
                      document.getElementById('clock-out-hours').textContent = clockOut;
                  }
                  if (pictureOut?.length) {
                      document.getElementById('photosOut').style.display = '';
                      $('#clockImageOut').attr('src', pictureOut)
                  }
              }

              $('#clockInOutDataModal').on('shown.bs.modal', function () {
                  if (mapIn !== null) {
                      mapIn?.remove();
                  }
                  if (mapOut !== null) {
                      mapOut?.remove();
                  }
              
                  if (coordIn?.length > 1) {
                      document.getElementById('mapIn').style.display = '';
                      
                      mapIn = L.map('openStreetMapContainerIn').setView([coordIn[0], coordIn[1]], 17);
                      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                          attribution: '© OpenStreetMap contributors'
                      }).addTo(mapIn);
                  
                      // Add a marker for the location
                      var marker = L.marker([coordIn[0], coordIn[1]]).addTo(mapIn);
                  
                      // Add a circle with the converted radius
                      var circle = L.circle([coordIn[0], coordIn[1]], {
                          color: 'blue',
                          fillColor: '#f0023',
                          fillOpacity: 0.2,
                          radius: coordIn[2],
                      }).addTo(mapIn);
                  }

                  if (coordOut?.length > 1) {
                      document.getElementById('mapOut').style.display = '';
                      
                      mapOut = L.map('openStreetMapContainerOut').setView([coordOut[0], coordOut[1]], 17);
                      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                          attribution: '© OpenStreetMap contributors'
                      }).addTo(mapOut);
                  
                      // Add a marker for the location
                      var marker = L.marker([coordOut[0], coordOut[1]]).addTo(mapOut);
                  
                      // Add a circle with the converted radius
                      var circle = L.circle([coordOut[0], coordOut[1]], {
                          color: 'blue',
                          fillColor: '#f0023',
                          fillOpacity: 0.2,
                          radius: coordOut[2],
                      }).addTo(mapOut);
                  }
              })
          })

          $('#reportInputModal').on('shown.bs.modal', function () {
              if (new Date(overtimeDate) < new Date() && (!clockIn || !clockOut)) {
                  document.getElementById('time-input').style.display = '';
              } else {
                  document.getElementById('time-input').style.display = 'none';
              }
          });
          
          $('#reportInputModal').on('hidden.bs.modal', function () {
              let file = document.getElementById('uploadFile');
              if (file) {
                  file.style.display = 'none';
              }

              document.getElementById('exist-document-class').style.display = 'none';
              document.getElementById('exist-document-view').href = '#';
          });

          $('#commonModal').on('hidden.bs.modal', function () {
              let file = document.getElementById('uploadFile');
              if (file) {
                  file.style.display = 'none';
              }
          });

          $('#clockInOutInputModal').on('hidden.bs.modal', function () {
              document.getElementById('load').style.display = '';
              document.getElementById('camera').style.display = 'none';
              document.getElementById('output').style.display = 'none';
          
              var tracks = video?.srcObject?.getTracks();
              tracks?.forEach(track => track.stop());
              video.srcObject = null;
          });

          $('#reportDataModal').on('hidden.bs.modal', function () {
              document.getElementById('document-form-data').style.display = 'none';
              document.getElementById('note-form-data').style.display = 'none';

              document.getElementById('report-data-not-exist').style.display = '';

              document.getElementById('document-name').textContent = '';
              document.getElementById('document-data').href = '#';
              document.getElementById('note-data').textContent = '';
          });

          $('#clockInOutDataModal').on('hidden.bs.modal', function () {
              document.getElementById('clock-in-data').style.display = 'none';
              document.getElementById('clock-out-data').style.display = 'none';

              document.getElementById('clock-in-hours').style.display = 'none';
              document.getElementById('clock-out-hours').style.display = 'none';

              document.getElementById('photosIn').style.display = 'none';
              document.getElementById('photosOut').style.display = 'none';

              document.getElementById('clock-data-not-exist').style.display = '';

              // Remove the map instances and their containers
              if (mapIn !== null && coordIn?.length > 1) {
                  mapIn.remove();
                  mapIn = null;
                  document.getElementById('mapIn').style.display = 'none';
              }
          
              if (mapOut !== null && coordOut?.length > 1) {
                  mapOut.remove();
                  mapOut = null;
                  document.getElementById('mapOut').style.display = 'none';
              }
              // document.getElementById('mapOut').style.display = 'none';

              clockIn = null;
              clockOut = null;
              coordIn = null;
              coordOut = null;
              pictureIn = null;
              pictureOut = null;
          });
      });
    </script>

    <script>
        $(document).ready(() => {
            $(document).on('change', '[name="overtimeDocument"]', function () {
                const overFile = document.getElementById('overtimeFile');
                overFile.style.display = '';
                overFile.style['max-width'] = '';
                document.getElementById('overtimeFileName').textContent = this.files[0].name;
            });

            $(document).on('change', '[name="myDocument"]', function () {
                const overFile = document.getElementById('uploadFile');
                overFile.style.display = '';
                overFile.style['max-width'] = '';
                document.getElementById('fileName').textContent = this.files[0].name;
            });

            // $(document).on('change', '[name="editDocument"]', function () {
            //     const overFile = document.getElementById('uploadFile');
            //     overFile.style.display = '';
            //     overFile.style['max-width'] = '';
            //     document.getElementById('fileName').textContent = this.files[0].name;
            // });
        })
    </script>

    <script>
        /* JS comes here */
        (function() {

            var width = 320; // We will scale the photo width to this
            var height = 0; // This will be computed based on the input stream

            var streaming = false;

            var video = null;
            var canvas = null;
            var photo = null;
            var takepic = null;
            var loadbutton = document.getElementById('load');

            loadbutton.addEventListener('click', startup, false);

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
                        alert("Please Allow Camera Access To Take Picture For Clock In / Out");
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
                    document.getElementById('picture_out').value = data;
                } else {
                    clearphoto();
                }
            }
        })();
    </script>
@endpush

@section('action-button')
    @can('Create Overtime')
        <a href="#" data-url="{{ route('overtime.create') }}" data-ajax-popup="true" data-size="xl"
        data-title="{{ __('Create Overtime') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
        class="btn btn-sm btn-primary" id="create-event">
        <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
<!-- Update the modal structure in your Blade template -->
<div class="modal fade" id="clockInOutInputModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">{{__('Clock In / Clock Out')}}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" style="padding-top: 0.35rem">
              <div class="row d-flex flex-column align-items-center">
                  {{ Form::open(['route' => ['overtime.attendance'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                  <div class="col-md-6 col-lg-12 text-center mx-auto mt-2">
                      <button type="button" class="btn btn-info btn-lg btn-block" id="load"><i
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
                  <hr>
                  <input type="hidden" name="latitude" id="latitude" value="0">
                  <input type="hidden" name="longitude" id="longitude" value="0">
                  <input type="hidden" name="accuracy" id="accuracy" value="0">
                  <input type="hidden" name="overtimeId" id="overtimeId" value="">
                  <div class="col-md-6 text-center mx-auto mt-3">
                      <button type="submit" value="0" name="in" id="clock_in" onclick="getLocation()"
                          class="btn btn-primary btn-lg btn-block" style="width: 150px" disabled>{{ __('CLOCK IN') }}</button>
                      {{ Form::close() }}
                  </div>                                                    
                  <div class="col-md-6 text-center mx-auto mt-3">
                      {{ Form::open(['route' => ['overtime.attendance'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                          <input type="hidden" name="latitude" id="latitude_out" value="0">
                          <input type="hidden" name="longitude" id="longitude_out" value="0">
                          <input type="hidden" name="accuracy" id="accuracy_out" value="0">
                          <input type="hidden" name="picture_out" id="picture_out">
                          <input type="hidden" name="overtimeIdOut" id="overtimeIdOut" value="">
                          <button type="submit" value="1" name="out" id="clock_out" onclick="getLocation()"
                              class="btn btn-danger" style="width: 150px">{{ __('CLOCK OUT') }}</button>
                      {{ Form::close() }}
                  </div>
              </div>
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
              {{ Form::open(['route' => ['overtime.report'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
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
                      <div class="col-md-6" id="exist-document-class" style="display: none;">
                          {{ Form::label('old_file', __('Old File : '), ['class' => 'col-form-label']) }}
                          <a href="#" target="blank" class="btn btn-block btn-info btn-outline-dark bg-info"
                              data-bs-toggle="tooltip" id="exist-document-view"
                              data-bs-original-title="{{ __('View') }}">
                              <i class="ti ti-file text-white" style="font-size: 15px"></i>
                          </a>
                      </div>
                  </div>
                  <div class="row" id="time-input" style="display: none;">
                    <div class="form-group col-6">
                        {{ Form::label('start_time', __('Start Time'), ['class' => 'col-form-label']) }}
                        {{ Form::time('start_time', null, ['class' => 'form-control timepicker_format']) }}
                    </div>
                    <div class="form-group col-6">
                        {{ Form::label('end_time', __('End Time'), ['class' => 'col-form-label']) }}
                        {{ Form::time('end_time', null, ['class' => 'form-control timepicker_format']) }}
                    </div>
                </div>
                  <div class="form-group">
                      {{ Form::label('note', __('Note'), ['class' => 'col-form-label']) }}
                      {{ Form::textarea('note', null, ['class' => 'form-control', 'id' => 'note', 'placeholder' => __('Add Notes'),'rows'=>'3']) }}
                  </div>
                  {{-- {{ Form::text('overtimeId', null, ['class' => 'form-control ', 'required' => 'required','placeholder'=>'Enter Title', 'id' => 'overtimeIdReportInput', 'name' => 'overtimeId']) }} --}}
                  <input type="hidden" name="overtimeId" id="overtimeIdReportInput" value="0">
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                  <input type="submit" value="{{ __('Submit') }}" class="btn  btn-primary">
              </div>
          {{ Form::close() }}
      </div>
  </div>
</div>

<div class="modal fade" id="reportDataModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">{{__('Report Data')}}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" style="padding-top: 0.35rem">
              <div class="form-group" id="document-form-data" style="margin-bottom: 0px; display: none;">
                  {{ Form::label('myDocument', __('Document'), ['class' => 'col-form-label']) }}
                  <div class="row">
                      <label for="myDocument" class="col-6">
                      <a class="btn btn-block btn-primary bg-primary" id="document-data"><i
                          class="fa fa-regular fa-file"></i> <span id="document-name"></span>
                      </a>
                      </label>
                      <div class="btn btn-block btn-success bg-success disabled col-6" style="display: none;" id="uploadFile"><i
                          class="fa fa-regular fa-file"></i><p id="fileName"></p>
                      </div>
                  </div>
              </div>
              <div class="form-group" id="note-form-data" style="display: none;">
                  {{ Form::label('note', __('Note'), ['class' => 'col-form-label']) }}
                  <textarea name="note-data" class="form-control" id="note-data" rows="3"></textarea>
              </div>
          </div>
          <div class="form-group text-center" id="report-data-not-exist">
              <h5>{{ __("Data Doesn't Exist") }}</h5>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="clockInOutDataModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">{{__('Clock In / Out Data')}}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" style="padding-top: 0.35rem">
              <div class="row">
                  <div class="col-sm-6 col-md-6 col-xl-6 text-center mx-auto" id="clock-in-data" style="display: none;">
                      <h5 class="bg-primary btn-sm text-white mt-2" style="font-size: 15px">{{__('Clock In')}}</h5>
                      <hr>
                      <div class="btn btn-primary btn-sm disabled" id="clock-in-hours" style="display: none;">
                      </div>
                      <div class="clock-images mx-d-flex flex-column align-items-center mt-2" id="photosIn" style="display: none;">
                          <div class="text-center mx-auto">
                              <strong>{{__('Clock In Image Capture')}}</strong>
                              <br>
                              <img id="clockImageIn" src="" alt="Clock In Out Image" style="max-width: 100%; max-height: 300px; border-radius: 5%" class="mb-2 mt-1">
                              <br>
                          </div>
                      </div>
                      <div class="text-center mx-auto mt-2" id="mapIn" style="display: none;">
                          <strong>{{__('Clock In Location')}}</strong>
                          <div id="openStreetMapContainerIn" style="border-radius: 5%" class="mt-1"></div>
                      </div>
                  </div>
                  <div class="col-sm-6 col-md-6 col-xl-6 text-center mx-auto" id="clock-out-data" style="display: none;">
                      <h5 class="bg-info btn-sm text-white mt-2" style="font-size: 15px">{{__('Clock Out')}}</h5>
                      <hr>
                      <div class="btn btn-info btn-sm disabled" id="clock-out-hours" style="display: none;">
                      </div>
                      <div class="clock-images mx-d-flex flex-column align-items-center mt-2" id="photosOut" style="display: none;">
                          <div class="text-center mx-auto">
                              <strong>{{__('Clock Out Image Capture')}}</strong>
                              <br>
                              <img id="clockImageOut" src="" alt="Clock In Out Image" style="max-width: 100%; max-height: 300px; border-radius: 5%" class="mb-2 mt-1">
                              <br>
                          </div>
                      </div>
                      <div class="text-center mx-auto mt-2" id="mapOut" style="display: none;">
                          <strong>{{__('Clock Out Location')}}</strong>
                          <div id="openStreetMapContainerOut" style="border-radius: 5%" class="mt-1"></div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="form-group text-center" id="clock-data-not-exist">
              <h5>{{ __("Data Doesn't Exist") }}</h5>
          </div>
      </div>
  </div>
</div>

<div class="col-sm-12">
    <div class=" mt-2 " id="multiCollapseExample1">
        <div class="card">
            <div class="card-body">
            {{ Form::open(array('route' => array('overtime.index'),'method'=>'get','id'=>'overtime_filter')) }}
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
                                    {{ Form::date('date',isset($_GET['date'])?$_GET['date']:'', array('class' => 'form-control month-btn')) }}
                                </div>
                            </div>
                            @if(\Auth::user()->type != 'employee')
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('branch', __('Branch'),['class'=>'col-form-label'])}}
                                        {{ Form::select('branch', $branch,isset($_GET['branch'])?$_GET['branch']:'', array('class' => 'form-control select')) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('department', __('Department'),['class'=>'col-form-label'])}}
                                        {{ Form::select('department', $department,isset($_GET['department'])?$_GET['department']:'', array('class' => 'form-control select')) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-auto mt-4">
                        <div class="row">
                            <div class="col-auto">
                                <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('overtime_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{route('overtime.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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

<div class="col-md-12">
    <div class="card">
        <div class="card-header card-body table-border-style">
            <h5>{{__('Overtime List')}}</h5>
            <hr>
            <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Document') }}</th>
                            <th>{{ __('Report') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($overtimes as $overtime)
                            <tr>
                                <td>{{ !empty($overtime->employee) ? $overtime->employee->name : '' }}</td>
                                <td>{{ $overtime->title }}</td>
                                <td>{{ $overtime->date }}</td>
                                <td>{{ $overtime->type ?? 'hourly' }}</td>
                                <td>{{ $overtime->description }}</td>
                                <td>
                                    @if ($overtime->document)
                                        <div class="action-btn bg-info ms-2">
                                            <a href="{{ asset($overtime->document) }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
                                                data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('View') }}">
                                                <i class="ti ti-file text-white"></i>
                                            </a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($overtime->employee_id == \Auth::user()?->employee?->id)
                                        @if ($overtime->type != 'daily')
                                            <button class="btn @if ($overtime->clock_out) btn-success @else btn-primary @endif btn-sm clock-input" data-bs-toggle="tooltip"
                                                data-overtime-id="{{ $overtime->id }}"
                                                data-clock-in="{{ $overtime->clock_in }}"
                                                data-bs-original-title="{{ __('Clock In / Clock Out') }}" @if (strtotime(date('Y-m-d')) > strtotime($overtime->date)) disabled @endif>
                                                <i class="fa fa-solid fa-clock"></i>
                                            </button>
                                        @endif
                                        <button class="btn @if ($overtime->report_document) btn-success @else btn-primary @endif btn-sm report-input" data-bs-toggle="tooltip"
                                            data-overtime-id="{{ $overtime->id }}"
                                            data-overtime-date="{{ $overtime->date }}"
                                            data-document="{{ $overtime->report_document }}"
                                            data-note="{{ $overtime->report_note }}"
                                            data-clock-in="{{ $overtime->clock_in }}"
                                            data-clock-out="{{ $overtime->clock_out }}"
                                            data-bs-original-title="{{ __('Report Document') }}">
                                            <i class="fa fa-solid fa-file-import"></i>
                                        </button>
                                    @elseif (\Auth::user()?->employee ? in_array($overtime->employee_id, \Auth::user()?->employee?->subordinatesFlatten()?->pluck('id')?->toArray()) : false || Auth::user()->type != 'employee')
                                        @if ($overtime->type != 'daily')
                                            <button class="btn btn-success btn-sm clock-data" data-bs-toggle="tooltip"
                                                data-overtime-id="{{ $overtime->id }}"
                                                data-clock-in="{{ $overtime->clock_in }}"
                                                data-clock-out="{{ $overtime->clock_out }}"
                                                data-coord-in="{{ $overtime->coord_in }}"
                                                data-coord-out="{{ $overtime->coord_out }}"
                                                data-picture-in="{{ $overtime->picture_in }}"
                                                data-picture-out="{{ $overtime->picture_out }}"
                                                data-bs-original-title="{{ __('Clock In / Clock Out') }}">
                                                <i class="fa fa-solid fa-clock"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-success btn-sm report-data" data-bs-toggle="tooltip"
                                            data-overtime-id="{{ $overtime->id }}"
                                            data-document="{{ $overtime->report_document }}"
                                            data-note="{{ $overtime->report_note }}"
                                            data-bs-original-title="{{ __('Report Document') }}">
                                            <i class="fa fa-solid fa-file-import"></i>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="Action">
                                    <span>
                                        @if (((Gate::check('Edit Overtime') && $overtime->created_by == \Auth::user()->id) || \Auth::user()->type != 'employee') && (empty($overtime->report_document) && empty($overtime->report_note)))
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center edit-event" data-size="xl"
                                                    data-url="{{ URL::to('overtime/' . $overtime->id . '/edit') }}"
                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Edit Overtime') }}"
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                        @endif
                                        @if (((Gate::check('Delete Overtime') && $overtime->created_by !== \Auth::user()?->employee?->id) || \Auth::user()->type != 'employee') && (empty($overtime->report_document) && empty($overtime->report_note)))
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['overtime.destroy', $overtime->id], 'id' => 'delete-form-' . $overtime->id]) !!}
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                    aria-label="Delete"><i
                                                        class="ti ti-trash text-white text-white"></i></a>
                                                </form>
                                            </div>
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