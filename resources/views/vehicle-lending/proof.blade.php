
{{ Form::model($vehicleLending, ['route' => ['vehicle-lending.proof', $vehicleLending->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data']) }}
    @php
        $access = !(!\Auth::user()->vehicleOfficer || \Auth::user()->id != $vehicleLending->request_by || \Auth::user()->type !== 'employee');
    @endphp
    <div class="modal-body" style="padding-top: 0.35rem">
        <div class="row">
            <div class="col-sm-6 col-md-6 col-xl-6 mx-auto" id="pickup-data">
                <h5 class="bg-primary btn-sm text-white mt-2 text-center " style="font-size: 15px">{{__('Pick Up')}}</h5>
                <hr>
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::label('pickup_km', __('Pick Up KM'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::number('pickup_km', $vehicleLending->pickup_km ?? $vehicle->km, ['class' => 'form-control ', 'step' => '1','placeholder' => __('Enter Pick Up KM'), 'disabled' => $access]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('pickup_emoney_balance', __('Pick Up Emoney Balance'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::number('pickup_emoney_balance', $vehicleLending->pickup_emoney_balance > 0 ? $vehicleLending->pickup_emoney_balance : $vehicle->emoney_balance, ['class' => 'form-control ', 'step' => '0.01','placeholder' => __('Enter Pick Up Emoney Balance'), 'disabled' => $access]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('pickup_time', __('Pick Up Time'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::datetimeLocal('pickup_time', null, ['class' => 'form-control datetime-local', 'disabled' => $access]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('pickup_file', __('Pick Up File'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        @if (\Auth::user()->id == $vehicleLending->request_by)
                            {{ Form::file('pickup_file_1', ['class' => 'form-control mb-2', 'disabled' => $access]) }}
                            {{ Form::file('pickup_file_2', ['class' => 'form-control mb-2', 'disabled' => $access]) }}
                            <span class="text-warning pl-1"><b>{{ __('Max Upload Size Per File: 10 MB')}}</b></span>
                        @endif

                        @if ($vehicleLending->pickup_file_1)
                            @php
                                $pickup_temp_file_1 = explode('/', $vehicleLending->pickup_file_1);
                                $pickup_filename_1  = array_pop($pickup_temp_file_1);
                            @endphp
                            <a href="{{ asset($vehicleLending->pickup_file_1) }}" target="blank" class="btn btn-md btn-success align-items-center text-start mt-2"
                                data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('View') }}">
                                <i class="fas fa-file"></i> {{ $pickup_filename_1 }}
                            </a>
                            <br>
                        @endif

                        @if ($vehicleLending->pickup_file_2)
                            @php
                                $pickup_temp_file_2 = explode('/', $vehicleLending->pickup_file_2);
                                $pickup_filename_2  = array_pop($pickup_temp_file_2);
                            @endphp
                            <a href="{{ asset($vehicleLending->pickup_file_2) }}" target="blank" class="btn btn-md btn-success align-items-center text-start mt-2"
                                data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('View') }}">
                                <i class="fas fa-file"></i> {{ $pickup_filename_2 }}
                            </a>
                            <br>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-xl-6 mx-auto" id="return-data">
                <h5 class="bg-info btn-sm text-white mt-2 text-center" style="font-size: 15px">{{__('Return')}}</h5>
                <hr>
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::label('return_km', __('Return KM'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::number('return_km', null, ['class' => 'form-control ', 'step' => '0.01','placeholder' => __('Enter Return KM'), 'disabled' => $access]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('return_emoney_balance', __('Return Emoney Balance'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::number('return_emoney_balance', null, ['class' => 'form-control ', 'step' => '0.01','placeholder' => __('Enter Return Emoney Balance'), 'disabled' => $access]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('return_time', __('Return Time'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::datetimeLocal('return_time', null, ['class' => 'form-control datetime-local', 'disabled' => $access]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('return_file', __('Return File'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        @if (\Auth::user()->id == $vehicleLending->request_by)
                            {{ Form::file('return_file_1', ['class' => 'form-control mb-2', 'disabled' => $access]) }}
                            {{ Form::file('return_file_2', ['class' => 'form-control mb-2', 'disabled' => $access]) }}
                            <span class="text-warning pl-1"><b>{{ __('Max Upload Size Per File: 10 MB')}}</b></span>
                        @endif

                        @if ($vehicleLending->return_file_1)
                            @php
                                $return_temp_file_1      = explode('/', $vehicleLending->return_file_1);
                                $return_filename_1       = array_pop($return_temp_file_1);
                            @endphp
                            <a href="{{ asset($vehicleLending->return_file_1) }}" target="blank" class="btn btn-md btn-success align-items-center text-start mt-2"
                                data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('View') }}">
                                <i class="fas fa-file"></i> {{ $return_filename_1 }}
                            </a>
                        @endif

                        @if ($vehicleLending->return_file_2)
                            @php
                                $return_temp_file_2      = explode('/', $vehicleLending->return_file_2);
                                $return_filename_2       = array_pop($return_temp_file_2);
                            @endphp
                            <a href="{{ asset($vehicleLending->return_file_2) }}" target="blank" class="btn btn-md btn-success align-items-center text-start mt-2"
                                data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('View') }}">
                                <i class="fas fa-file"></i> {{ $return_filename_2 }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{ $vehicleLending->id }}" name="lending_id">
    </div>
    @if (!$access)
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Send') }}" class="btn btn-primary">
        </div>
    @endif
{{ Form::close() }}