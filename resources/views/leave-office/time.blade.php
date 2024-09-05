{{ Form::model($leave, ['route' => ['leave-office.setTime', $leave->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data', 'id' => 'leave-form']) }} 
    <div class="modal-body" style="padding-top: 0.35rem">
        @if (empty($leave->leave) || empty($leave->return))
            <div class="row d-flex flex-column align-items-center">
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
            </div>
            <hr>

            <input type="hidden" name="latitude" id="latitude" value="0">
            <input type="hidden" name="longitude" id="longitude" value="0">
            <input type="hidden" name="accuracy" id="accuracy" value="0">
            <input type="hidden" value="{{ $leave->id }}" name="leave_id">
        @endif

        <div class="row">
            <div class="col-sm-6 col-md-6 col-xl-6 text-center mx-auto" id="clock-in-data">
                <h5 class="bg-primary btn-sm text-white mt-2" style="font-size: 15px">{{__('Leave Office')}}</h5>
                <hr>
                @if (!empty($leave->leave))
                    <div class="btn btn-primary btn-sm disabled" id="clock-in-hours">
                        {{ $leave->leave}}
                    </div>
                    <div class="clock-images mx-d-flex flex-column align-items-center mt-2" id="photosIn">
                        <div class="text-center mx-auto">
                            <strong>{{__('Leave Office Image Capture')}}</strong>
                            <br>
                            <img id="clockImageIn" src="{{ $leave->leave_pict}}" alt="Clock In Out Image" style="max-width: 100%; max-height: 300px; border-radius: 5%" class="mb-2 mt-1">
                            <br>
                        </div>
                    </div>
                    <div class="text-center mx-auto mt-2" id="mapLeave">
                        <strong>{{__('Leave Office Location')}}</strong>
                        <div id="openStreetMapContainerLeave" style="border-radius: 5%" class="mt-1"></div>
                    </div>
                @elseif(Auth::user()->employee?->id == $leave->employee_id)
                    <input type="hidden" name="type" id="accuracy" value="leave">
                    <button type="button" id="time-input" onclick="handleLocationAndSubmit()"
                        class="btn btn-primary btn-lg btn-block">
                            {{ __('Leave Office') }}
                    </button>
                @else
                    -
                @endif
            </div>
            <div class="col-sm-6 col-md-6 col-xl-6 text-center mx-auto" id="clock-out-data">
                <h5 class="bg-info btn-sm text-white mt-2" style="font-size: 15px">{{__('Return Office')}}</h5>
                <hr>
                @if (!empty($leave->return))
                    <div class="btn btn-info btn-sm disabled" id="clock-out-hours">
                        {{$leave->return}}
                    </div>
                    <div class="clock-images mx-d-flex flex-column align-items-center mt-2" id="photosOut">
                        <div class="text-center mx-auto">
                            <strong>{{__('Leave Office Image Capture')}}</strong>
                            <br>
                            <img id="clockImageOut" src="{{$leave->return_pict}}" alt="Clock In Out Image" style="max-width: 100%; max-height: 300px; border-radius: 5%" class="mb-2 mt-1">
                            <br>
                        </div>
                    </div>
                    <div class="text-center mx-auto mt-2" id="mapReturn">
                        <strong>{{__('Leave Office Location')}}</strong>
                        <div id="openStreetMapContainerReturn" style="border-radius: 5%" class="mt-1"></div>
                    </div>
                @elseif (!empty($leave->leave) && Auth::user()->employee?->id == $leave->employee_id)
                    <input type="hidden" name="type" id="accuracy" value="return">
                    <button type="button" id="time-input" onclick="handleLocationAndSubmit()"
                        class="btn btn-info btn-lg btn-block">
                            {{ __('Return Office') }}
                    </button>
                @else
                    -
                @endif
            </div>
        </div>
    </div>
    {{-- @if (!$access)
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Send') }}" class="btn btn-primary">
        </div>
    @endif --}}
{{ Form::close() }}

<script>
    $(document).ready(function () {
        let mapLeave = null;
        let mapReturn = null;
        let coordLeave = @json($leave->leave_coord)?.split(', ');
        let coordReturn = @json($leave->return_coord)?.split(', ');

        $('#commonModal').on('shown.bs.modal', function () {
            if (coordLeave?.length > 1) {
                // Destroy existing map instance if it's already initialized
                if (mapLeave !== null) {
                    mapLeave.off(); // Remove all event listeners
                    mapLeave.remove();  // Destroy the existing map instance
                    mapLeave = null;  // Set to null to ensure clean initialization
                }
                // Initialize the map
                mapLeave = L.map('openStreetMapContainerLeave').setView([coordLeave[0], coordLeave[1]], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(mapLeave);
            
                // Add a marker and circle to the map
                L.marker([coordLeave[0], coordLeave[1]]).addTo(mapLeave);
                L.circle([coordLeave[0], coordLeave[1]], {
                    color: 'blue',
                    fillColor: '#f0023',
                    fillOpacity: 0.2,
                    radius: coordLeave[2],
                }).addTo(mapLeave);

                mapLeave.invalidateSize();  // Corrects display issues if map is hidden initially
            }

            if (coordReturn?.length > 1) {
                // Destroy existing map instance if it's already initialized
                if (mapReturn !== null) {
                    mapReturn.off(); // Remove all event listeners
                    mapReturn.remove();  // Destroy the existing map instance
                    mapReturn = null;  // Set to null to ensure clean initialization
                }

                // Initialize the map
                mapReturn = L.map('openStreetMapContainerReturn').setView([coordReturn[0], coordReturn[1]], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(mapReturn);
            
                // Add a marker and circle to the map
                L.marker([coordReturn[0], coordReturn[1]]).addTo(mapReturn);
                L.circle([coordReturn[0], coordReturn[1]], {
                    color: 'blue',
                    fillColor: '#f0023',
                    fillOpacity: 0.2,
                    radius: coordReturn[2],
                }).addTo(mapReturn);

                mapReturn.invalidateSize();  // Corrects display issues if map is hidden initially
            }
        });

        $('#commonModal').on('hidden.bs.modal', function () {
            // Remove map instances when the modal is hidden to prevent reuse
            if (mapLeave !== null) {
                mapLeave.remove();
                mapLeave = null;  // Set to null to indicate no map instance exists
            }
            if (mapReturn !== null) {
                mapReturn.remove();
                mapReturn = null;  // Set to null to indicate no map instance exists
            }
        });
    });

</script>
