
{{ Form::model($vehicleLending, ['route' => ['vehicle-lending.proof', $vehicleLending->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body" style="padding-top: 0.35rem">
        <div class="row">
            <div class="col-sm-6 col-md-6 col-xl-6 mx-auto" id="pickup-data">
                <h5 class="bg-primary btn-sm text-white mt-2 text-center " style="font-size: 15px">{{__('Pick Up')}}</h5>
                <hr>
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::label('pickup_km', __('Pick Up KM'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::number('pickup_km', null, ['class' => 'form-control ', 'step' => '0.01','placeholder' => __('Enter Pick Up KM'), 'disabled' => \Auth::user()->id != $vehicleLending->request_by]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('pickup_time', __('Pick Up Time'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::datetimeLocal('pickup_time', null, ['class' => 'form-control datetime-local', 'disabled' => \Auth::user()->id != $vehicleLending->request_by]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('pickup_file', __('Pick Up File'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        @if (\Auth::user()->id == $vehicleLending->request_by)
                            {{ Form::file('pickup_file', ['class' => 'form-control', 'disabled' => \Auth::user()->id != $vehicleLending->request_by]) }}
                            <span class="text-warning pl-1"><b>{{ __('Max Upload Size Per File: 10 MB')}}</b></span>
                        @endif

                        @if ($vehicleLending->pickup_file)
                            @php
                                $pickup_temp_file      = explode('/', $vehicleLending->pickup_file);
                                $pickup_filename       = array_pop($pickup_temp_file);
                            @endphp
                            <a href="{{ asset($vehicleLending->pickup_file) }}" target="blank" class="btn btn-md btn-success align-items-center text-start mt-2"
                                data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('View') }}">
                                <i class="fas fa-file"></i> {{ $pickup_filename }}
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
                        {{ Form::number('return_km', null, ['class' => 'form-control ', 'step' => '0.01','placeholder' => __('Enter Return KM'), 'disabled' => \Auth::user()->id != $vehicleLending->request_by]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('return_time', __('Return Time'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        {{ Form::datetimeLocal('return_time', null, ['class' => 'form-control datetime-local', 'disabled' => \Auth::user()->id != $vehicleLending->request_by]) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('return_file', __('Return File'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                        @if (\Auth::user()->id == $vehicleLending->request_by)
                            {{ Form::file('return_file', ['class' => 'form-control', 'disabled' => \Auth::user()->id != $vehicleLending->request_by]) }}
                            <span class="text-warning pl-1"><b>{{ __('Max Upload Size Per File: 10 MB')}}</b></span>
                        @endif

                        @if ($vehicleLending->return_file)
                            @php
                                $return_temp_file      = explode('/', $vehicleLending->return_file);
                                $return_filename       = array_pop($return_temp_file);
                            @endphp
                            <a href="{{ asset($vehicleLending->return_file) }}" target="blank" class="btn btn-md btn-success align-items-center text-start mt-2"
                                data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('View') }}">
                                <i class="fas fa-file"></i> {{ $return_filename }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{ $vehicleLending->id }}" name="lending_id">
    </div>
    @if ($vehicleLending->request_by == \Auth::user()->id)
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Send') }}" class="btn btn-primary">
        </div>
    @endif
{{ Form::close() }}