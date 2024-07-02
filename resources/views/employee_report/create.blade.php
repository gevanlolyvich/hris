@extends('layouts.admin')

@section('page-title')
   {{ __('Create New Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee-report') }}">{{ __('Employee Report') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create New Report') }}</li>
@endsection

@push('script-page')
    <script>
        $(document).ready(function () {
            let dateForms  = document.getElementsByClassName('date-form');
            let startDate  = document.getElementById('start_date');
            let endDate    = document.getElementById('end_date');
            let hiddenDate = document.getElementById('end_date_hidden');
            let typeReport = document.getElementById('type');

            $('body').on('change', 'select[name=type]', function() {
                let type = $(this).val();

                if (type) {
                    for (let index = 0; index < dateForms.length; index++) {
                        dateForms[index].style.display = '';
                    }

                    startDate.value = null;
                    endDate.value   = null;
                } else {
                    for (let index = 0; index < dateForms.length; index++) {
                        dateForms[index].style.display = 'none';
                    }

                    startDate.value = null;
                    endDate.value   = null;
                }
            });

            $('body').on('change', '#start_date', function() {
                let s_date = new Date(startDate.value);
                switch (typeReport.value) {
                    case 'daily':
                        endDate.value = startDate.value;
                        hiddenDate.value = startDate.value;
                        break;

                    case 'weekly':
                        s_date.setDate(s_date.getDate() + 6);
                        endDate.value = s_date.toISOString().slice(0, 10);
                        hiddenDate.value = s_date.toISOString().slice(0, 10);
                        break;

                    case 'monthly':

                        s_date.setMonth(s_date.getMonth() + 1);
                        s_date.setDate(s_date.getDate() - 1);
                        endDate.value = s_date.toISOString().slice(0, 10);
                        hiddenDate.value = s_date.toISOString().slice(0, 10);
                        break;
                        
                    case 'yearly':
                        s_date.setFullYear(s_date.getFullYear() + 1);
                        s_date.setDate(s_date.getDate() - 1);
                        endDate.value = s_date.toISOString().slice(0, 10);
                        hiddenDate.value = s_date.toISOString().slice(0, 10);
                        break;

                    default:
                        break;
                }

                let dateActivity    = document.getElementsByClassName('activity_date');
                for (let index = 0; index < dateActivity.length; index++) {
                    dateActivity[index].min = startDate.value;
                    dateActivity[index].max = hiddenDate.value;
                }
            });

            $('body').on('click', '#addActivity', function() {
                let newActivity = $('div.activity').first().clone();
                
                // Clear input values
                newActivity.find('input[type="date"]').val(null);
                newActivity.find('input, textarea').val(null);

                // Show remove button only if it's not the first activity
                newActivity.find('.remove-row').show();
                newActivity.insertBefore(".hidden-activity-div");
            });

            $('body').on('click', '#addAccomplishment', function() {
                let newAccomplishment = $('div.accomplishment').first().clone();
                newAccomplishment.find('input, textarea').val(null);

                // Show remove button only if it's not the first accomplishment
                newAccomplishment.find('.remove-row').show();
                newAccomplishment.insertBefore(".hidden-accomplishment-div");
            });

            $('body').on('click', '#addObstacle', function() {
                let newObstacle = $('div.obstacle').first().clone();
                newObstacle.find('input, textarea').val(null);

                // Show remove button only if it's not the first Obstacle
                newObstacle.find('.remove-row').show();
                newObstacle.insertBefore(".hidden-obstacle-div");
            });

            $('body').on('click', '#addPlan', function() {
                let newPlan = $('div.plan').first().clone();
                newPlan.find('input, textarea').val(null);

                // Show remove button only if it's not the first Plan
                newPlan.find('.remove-row').show();
                newPlan.insertBefore(".hidden-plan-div");
            });

            $('body').on('click', '#addAttachment', function() {
                let newAttachment = $('div.attachment').first().clone();
                newAttachment.find('input, file').val(null);

                // Show remove button only if it's not the first FIle
                newAttachment.find('.remove-row').show();
                newAttachment.insertBefore(".hidden-attachment-div");
            });
            
            $("body").on("click", ".remove-row", function(){
                $(this).parent().parent().parent().remove();
            });

            $(window).on('load', function () {
                console.log(typeReport.value);
                if (typeReport.value) {
                    for (let index = 0; index < dateForms.length; index++) {
                        dateForms[index].style.display = '';
                    }
                }
            });
        });
    </script>
@endpush

@section('content')
    <div class="card">
        {{ Form::open(['route' => ['employee-report.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {{ Form::label('type', __('Type'),['class'=>'col-form-label'])}} <span class="text-danger pl-1"> * {{__('Required')}}</span>
                            {{ Form::select('type', $type, null, ['class' => 'form-control select2 type', 'id' => 'type', 'placeholder' => __('Select Report Type')]) }}
                        </div>
                    </div>
                    <div class="col-md-4 date-form" style="display: none">
                        <div class="form-group">
                            {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> * {{__('Required')}}</span>
                            {{ Form::date('start_date', '', ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required', 'id' => 'start_date']) }}
                        </div>
                    </div>
                    <div class="col-md-4 date-form" style="display: none">
                        <div class="form-group">
                            {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> * {{__('Required')}}</span>
                            {{ Form::date('end_date', '', ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required', 'id' => 'end_date', 'disabled' => 'disabled']) }}
                            {{ Form::hidden('end_date', '', ['id' => 'end_date_hidden']) }}
                        </div>
                    </div>
                </div>
                <hr>

                {{-- Activity --}}
                <div id="activity-form">
                    <div class="row my-2">
                        <div class="col-auto">
                            <h4>{{ __('Activity')}}</h4>
                        </div>
                        <div class="form-group col-2" style="margin: -5px">
                            <button type="button" class="btn btn-sm btn-info" id="addActivity"><i class="fas fa-plus-circle"></i></button>
                        </div>
                    </div>
                    <div class="row activity">
                        <div class="col-md-2">
                            <div class="form-group">
                                {{-- {{ Form::label('date', __('Type'),['class'=>'col-form-label'])}} --}}
                                {{ Form::date('activity_date[]', null, ['class' => 'form-control activity_date']) }}
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                {{-- {{ Form::label('activity', __('Type'),['class'=>'col-form-label'])}} --}}
                                {{ Form::textarea('activity[]', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Report Activity')]) }}
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <button type="button" class="btn btn-md btn-danger remove-row" style="display: none"><i class="fas fa-minus-circle"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="hidden-activity-div" style="display: none"></div>
                </div>
                <hr>

                {{-- Accomplishment --}}
                <div id="accomplishment-form">
                    <div class="row my-2">
                        <div class="col-auto">
                            <h4>{{ __("Accomplishment") }}</h4>
                        </div>
                        <div class="form-group col-2" style="margin: -5px">
                            <button type="button" class="btn btn-sm btn-info" id="addAccomplishment"><i class="fas fa-plus-circle"></i></button>
                        </div>
                    </div>
                    <div class="row accomplishment">
                        <div class="col-md-11">
                            <div class="form-group">
                                {{ Form::textarea('accomplishment[]', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Accomplishment')]) }}
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <button type="button" class="btn btn-md btn-danger remove-row" style="display: none"><i class="fas fa-minus-circle"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="hidden-accomplishment-div" style="display: none"></div>
                </div>
                <hr>

                {{-- Obstacle --}}
                <div id="obstacle-form">
                    <div class="row my-2">
                        <div class="col-auto">
                            <h4>{{ __("Obstacle") }}</h4>
                        </div>
                        <div class="form-group col-2" style="margin: -5px">
                            <button type="button" class="btn btn-sm btn-info" id="addObstacle"><i class="fas fa-plus-circle"></i></button>
                        </div>
                    </div>
                    <div class="row obstacle">
                        <div class="col-md-11">
                            <div class="form-group">
                                {{ Form::textarea('obstacle[]', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Obstacle')]) }}
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <button type="button" class="btn btn-md btn-danger remove-row" style="display: none"><i class="fas fa-minus-circle"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="hidden-obstacle-div" style="display: none"></div>
                </div>
                <hr>

                {{-- Work Plan --}}
                <div id="plan-form">
                    <div class="row my-2">
                        <div class="col-auto">
                            <h4>{{ __("Work Plan") }}</h4>
                        </div>
                        <div class="form-group col-2" style="margin: -5px">
                            <button type="button" class="btn btn-sm btn-info" id="addPlan"><i class="fas fa-plus-circle"></i></button>
                        </div>
                    </div>
                    <div class="row plan">
                        <div class="col-md-11">
                            <div class="form-group">
                                {{ Form::textarea('plan[]', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Work Plan')]) }}
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <button type="button" class="btn btn-md btn-danger remove-row" style="display: none"><i class="fas fa-minus-circle"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="hidden-plan-div" style="display: none"></div>
                </div>
                <hr>
                
                {{-- Attachment --}}
                <div id="attachment-form">
                    <div class="row my-2">
                        <div class="col-auto">
                            <h4>{{ __("Attachment") }}</h4>
                        </div>
                        <div class="form-group col-2" style="margin: -5px">
                            <button type="button" class="btn btn-sm btn-info" id="addAttachment"><i class="fas fa-plus-circle"></i></button>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    <div class="row attachment">
                        <div class="col-md-11">
                            <div class="form-group">
                                {{ Form::file('attachment[]', ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <button type="button" class="btn btn-md btn-danger remove-row" style="display: none"><i class="fas fa-minus-circle"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="hidden-attachment-div">
                        <span class="text-warning pl-1"><b>{{ __('Max Upload Size Per File: 10 MB')}}</b></span>
                    </div>
                </div>
                <hr>
            </div>
    
            <div class="card-footer text-end">
                <a class="btn btn-md btn-light btn-outline-dark" href="{{ url('employee-report') }}" style="color: black;">{{ __('Cancel') }}</a>
                
                <button type="button" class="btn btn-primary btn-outline-dark bs-pass-para">{{ __('Create') }}</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection

