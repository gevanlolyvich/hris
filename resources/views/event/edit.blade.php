@php
    $setting = App\Models\Utility::settings();
@endphp

@if (Auth::user()->type == 'company')
    {{ Form::model($event, ['route' => ['event.update', $event->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body modal-lg" id="event-edit-modal">
        <div class="row" style="padding-left: 13px">
            <div class="col-4 choices__list choices__list--multiple" style="">
                <h6>{{__('Selected Department')}}</h6>
                @foreach ($selected_departments as $department)
                    <div class="choices__item disable">
                        {{ $department->name }}
                    </div>
                @endforeach
            </div>
            <div class="col-8 choices__list choices__list--multiple" style="">
                <h6>{{__('Selected Employee')}}</h6>
                @foreach ($selected_employees as $employee)
                    <div class="choices__item disable">
                        {{ $employee->name }}
                    </div>
                @endforeach
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label']) }}
                    <select class="form-control select" name="branch_id" id="branch_id"
                        placeholder="{{ __('Select Branch') }}">
                        <option value="">{{ __('Select Branch') }}</option>
                        <option value="0">{{ __('All Branch') }}</option>
                        @foreach ($branch as $branch)
                            @if ($event->branch_id === $branch->id)
                                <option selected value="{{ $branch->id }}">{{ $branch->name}}</option>
                            @else
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('department_id', __('Department'), ['class' => 'col-form-label']) }}
                    <div class="department_div">
                        <select class="form-control department_id" name="department_id[]"
                             placeholder="Select Department" >
                        <option value="">{{ __('Select Department') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                    <div class="employee_div">
                        <select class="form-control  employee_id" name="employee_id[]"
                            placeholder="Select Employee">
                        <option value="">{{ __('Select Employee') }}</option>
                        </select>
                    </div>
    
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('title', __('Event Title'), ['class' => 'col-form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Event Title')]) }}
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('start_date', __('Event start Date'), ['class' => 'col-form-label']) }}
                    {{ Form::text('start_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('end_date', __('Event End Date'), ['class' => 'col-form-label']) }}
                    {{ Form::text('end_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('location', __('Event Location'), ['class' => 'col-form-label']) }}
                {{ Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Enter Event Location')]) }}
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="form-group">
                    {{ Form::label('map', __('Map'), ['class' => 'form-label']) }}
                    <div id="openStreetMapContainer" style="height: 400px;"></div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ $latitude }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $longitude }}">
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('color', __('Event Select Color'), ['class' => 'col-form-label d-block mb-3']) }}
                <div class=" btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                    <label
                        class="btn bg-info p-3 {{ $event->color == 'event-info'
                            ? 'custom_color_radio_button
                                                                        '
                            : '' }} "><input
                            type="radio" name="color" class="d-none" value="event-info"
                            {{ $event->color == 'event-info' ? 'checked' : '' }}></label>

                    <label
                        class="btn bg-warning p-3 {{ $event->color == 'event-warning' ? 'custom_color_radio_button' : '' }}"><input
                            type="radio" class="d-none" name="color" value="event-warning"
                            {{ $event->color == 'event-warning' ? 'checked' : '' }}></label>

                    <label
                        class="btn bg-danger p-3 {{ $event->color == 'event-danger' ? 'custom_color_radio_button' : '' }}"><input
                            type="radio" name="color" class="d-none" value="event-danger"
                            {{ $event->color == 'event-danger' ? 'checked' : '' }}></label>


                    <label
                        class="btn bg-success p-3 {{ $event->color == 'event-success' ? 'custom_color_radio_button' : '' }}"><input
                            type="radio" class="d-none" name="color" value="event-success"
                            {{ $event->color == 'event-success' ? 'checked' : '' }}></label>

                    <label class="btn p-3 {{ $event->color == 'event-primary' ? 'custom_color_radio_button' : '' }}"
                        style="background-color: #51459d !important"><input type="radio" class="d-none" name="color"
                            value="event-primary" {{ $event->color == 'event-primary' ? 'checked' : '' }}></label>
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('description', __('Event Description'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '5', 'placeholder' => __('Enter Event Description')]) }}
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('myDocument', __('Document'), ['class' => 'col-form-label']) }}
                    <div>
                        <label for="myDocument">
                        <div class="btn btn-block btn-primary bg-primary document"> <i
                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                            </div>
                            <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="myDocument">
                        </label>
                        <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="uploadFile"><i
                            class="fa fa-regular fa-file"></i><p id="fileName"></p>
                        </div>
                    </div>
                </div>
            </div>
            @if (!empty($event->document))
                        <div class="col-md-6">
                            <a href="{{ $event->document }}" target="blank" class="btn btn-block btn-info btn-outline-dark bg-info"
                                data-bs-toggle="tooltip" style="margin-top: 40px"
                                data-bs-original-title="{{ __('View') }}">
                                <i class="ti ti-file text-white" style="font-size: 15px"></i>
                            </a>
                        </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

    </div>
    {{ Form::close() }}
@endif

@if (Auth::user()->type == 'employee')
    <div class="model-body">
        <div class="card">
            <div class="tab-content tab-bordered">
                <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="">
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4"><span class="h6 text-sm mb-0">{{ __('Title') }}</span>
                                        </dt>
                                        <dd class="col-sm-8"><span class="text-sm">{{ $event->title }}</span></dd>

                                        <dt class="col-sm-4"><span
                                                class="h6 text-sm mb-0">{{ __('Start Date') }}</span>
                                        </dt>
                                        <dd class="col-sm-8"><span
                                                class="text-sm">{{ \Auth::user()->dateFormat($event->start_date) }}</span>
                                        </dd>
                                        <dt class="col-sm-4"><span class="h6 text-sm mb-0">{{ __('End Date') }}</span>
                                        </dt>
                                        <dd class="col-sm-8"><span
                                                class="text-sm">{{ \Auth::user()->dateFormat($event->end_date) }}</span>
                                        </dd>
                                        <dt class="col-sm-4"><span
                                                class="h6 text-sm mb-0">{{ __('Description') }}</span></dt>
                                        <dd class="col-sm-8"><span class="text-sm">{{ $event->description }}</span>
                                        @if (!empty($event->document))
                                            <dt class="col-sm-4"><span
                                                class="h6 text-sm mb-0">{{ __('Document') }}</span></dt>
                                            {{-- <dd class="col-sm-8"><span class="text-sm">{{ $event->document }}</span> --}}
                                            <div class="col-md-8">
                                                <a href="{{ $event->document }}" target="blank" class="btn btn-outline-dark bg-info"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('View') }}"><i
                                                        class="ti ti-file text-white"></i>
                                                </a>
                                            </div>
                                        @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if (Auth::user()->type == 'hr')
    <div class="modal-body">
        <div class="row">
            <div class="col-form-label">
                {{ Form::model($event, ['route' => ['event.update', $event->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('title', __('Event Title'), ['class' => 'col-form-label']) }}
                            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Event Title')]) }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('start_date', __('Event start Date'), ['class' => 'col-form-label']) }}
                            {{ Form::text('start_date', null, ['class' => 'form-control ', 'id' => 'data_picker1']) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('end_date', __('Event End Date'), ['class' => 'col-form-label']) }}
                            {{ Form::text('end_date', null, ['class' => 'form-control ', 'id' => 'data_picker2']) }}
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="form-group">
                        {{ Form::label('map', __('Map'), ['class' => 'form-label']) }}
                        <div id="openStreetMapContainer" style="height: 400px;"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            {{ Form::label('color', __('Event Select Color'), ['class' => 'col-form-label d-block mb-3']) }}
                            <div class=" btn-group-toggle btn-group-colors event-tag" data-toggle="buttons">
                                <label
                                    class="btn bg-info p-3 {{ $event->color == 'event-info'
                                        ? 'custom_color_radio_button
                                                                                    '
                                        : '' }} "><input
                                        type="radio" name="color" class="d-none" value="event-info"
                                        {{ $event->color == 'event-info' ? 'checked' : '' }}></label>
            
                                <label
                                    class="btn bg-warning p-3 {{ $event->color == 'event-warning' ? 'custom_color_radio_button' : '' }}"><input
                                        type="radio" class="d-none" name="color" value="event-warning"
                                        {{ $event->color == 'event-warning' ? 'checked' : '' }}></label>
            
                                <label
                                    class="btn bg-danger p-3 {{ $event->color == 'event-danger' ? 'custom_color_radio_button' : '' }}"><input
                                        type="radio" name="color" class="d-none" value="event-danger"
                                        {{ $event->color == 'event-danger' ? 'checked' : '' }}></label>
            
            
                                <label
                                    class="btn bg-success p-3 {{ $event->color == 'event-success' ? 'custom_color_radio_button' : '' }}"><input
                                        type="radio" class="d-none" name="color" value="event-success"
                                        {{ $event->color == 'event-success' ? 'checked' : '' }}></label>
            
                                <label class="btn p-3 {{ $event->color == 'event-primary' ? 'custom_color_radio_button' : '' }}"
                                    style="background-color: #51459d !important"><input type="radio" class="d-none" name="color"
                                        value="event-primary" {{ $event->color == 'event-primary' ? 'checked' : '' }}></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('description', __('Event Description'), ['class' => 'col-form-label']) }}
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Event Description')]) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('myDocument', __('Document'), ['class' => 'col-form-label']) }}
                            <div>
                                <label for="myDocument">
                                <div class="btn btn-block btn-primary bg-primary document"> <i
                                            class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                    </div>
                                    <input style="margin-top: -50px" type="file" class="form-control col-md-12 mb-4 file" name="myDocument">
                                </label>
                                <div class="btn btn-block btn-success bg-success disabled" style="display: none;" id="uploadFile"><i
                                    class="fa fa-regular fa-file"></i><p id="fileName"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (!empty($event->document))
                        <div class="col-md-6">
                            <div class="btn btn-block btn-info btn-outline-dark bg-info" style="margin-top: 37px">
                                <a href="{{ $event->document }}" target="blank" class="btn btn-sm  align-items-center"
                                    data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('View') }}">
                                    <i class="ti ti-file text-white" style="font-size: 15px"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                    <div class="modal-footer">
                        <button type="button" class="btn  btn-light"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endif
