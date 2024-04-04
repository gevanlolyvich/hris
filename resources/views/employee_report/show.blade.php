@extends('layouts.admin')

@section('page-title')
   {{ __('Create New Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee-report') }}">{{ __('Employee Report') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report Detail') }}</li>
@endsection

@push('script-page')
    <script>
        $(document).ready(function () {
            let imageSrc   = null;

            $('body').on('click', '.image-file', function() {
                imageSrc = $(this).data('image');
                let name = $(this).data('name');
                $('#attachmentImage').attr('src', imageSrc)
                document.getElementById('attachmentName').innerHTML = name;
            
                // Open the modal
                $('#photoModal').modal('show');
            });
        });
    </script>
@endpush

@section('content')
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('Attachment')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mt-0 pt-3">
                    <div class="mx-d-flex flex-column align-items-center text-center" id="photos">
                        <img id="attachmentImage" src="" alt="Attachment Image" style="max-width: 100%; border-radius: 5%" class="mb-3 mt-1">
                        <br>
                        <i id="attachmentName"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            {{-- Employee Detail --}}
            <div class="row text-center">
                <div class="col">
                    <h6 style="padding: 10px 0;margin-bottom: 0px">{{ $report->employee->name }}</h6> 
                </div>
                <div class="col">
                    <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getBranch($report->employee->branch_id)) ? \Auth::user()->getBranch($report->employee->branch_id)->name : '-' }}</h6>
                </div>
                <div class="col">
                    <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getDepartment($report->employee->department_id)) ? \Auth::user()->getDepartment($report->employee->department_id)->name : '-' }}</h6>
                </div>
                <div class="col">
                    <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getDesignation($report->employee->designation_id)) ? \Auth::user()->getDesignation($report->employee->designation_id)->name : '-' }}</h6>
                </div>
            </div>
            <hr>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('type', __('Type'),['class'=>'col-form-label'])}}
                        {{ Form::select('type', $type, $report->type, ['class' => 'form-control select2 type', 'id' => 'type', 'placeholder' => __('Select Report Type'), 'disabled' => 'disabled']) }}
                    </div>
                </div>
                <div class="col-md-4 date-form">
                    <div class="form-group">
                        {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
                        {{ Form::date('start_date', $report->start_date, ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required', 'id' => 'start_date', 'disabled' => 'disabled']) }}
                    </div>
                </div>
                <div class="col-md-4 date-form">
                    <div class="form-group">
                        {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                        {{ Form::date('end_date', $report->end_date, ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required', 'id' => 'end_date', 'disabled' => 'disabled']) }}
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
                </div>
                @foreach ($report->activities as $activity)
                    <div class="row activity">
                        <div class="col-md-2">
                            <div class="form-group">
                                {{ Form::date('activity_date[]', $activity->date, ['class' => 'form-control activity_date', 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="form-group">
                                {{ Form::textarea('activity[]', $activity->activity, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Report Activity'), 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="hidden-activity-div" style="display: none"></div>
            </div>
            <hr>

            {{-- Accomplishment --}}
            <div id="accomplishment-form">
                <div class="row my-2">
                    <div class="col-auto">
                        <h4>{{ __("Accomplishment") }}</h4>
                    </div>
                </div>
                @foreach ($report->accomplishments as $accomplishment)
                    <div class="row accomplishment">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::textarea('accomplishment[]', $accomplishment->accomplishment, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Accomplishment'), 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="hidden-accomplishment-div" style="display: none"></div>
            </div>
            <hr>

            {{-- Obstacle --}}
            <div id="obstacle-form">
                <div class="row my-2">
                    <div class="col-auto">
                        <h4>{{ __("Obstacle") }}</h4>
                    </div>
                </div>
                @foreach ($report->obstacles as $obstacle)
                    <div class="row obstacle">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::textarea('obstacle[]', $obstacle->obstacle, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Obstacle'), 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="hidden-obstacle-div" style="display: none"></div>
            </div>
            <hr>

            {{-- Work Plan --}}
            <div id="plan-form">
                <div class="row my-2">
                    <div class="col-auto">
                        <h4>{{ __("Work Plan") }}</h4>
                    </div>
                </div>
                @foreach ($report->plans as $plan)
                    <div class="row plan">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::textarea('plan[]', $plan->plan, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Work Plan'), 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="hidden-plan-div" style="display: none"></div>
            </div>
            <hr>
            
            {{-- Attachment --}}
            <div id="attachment-form">
                <div class="row my-2">
                    <div class="col-auto">
                        <h4>{{ __("Attachment") }}</h4>
                    </div>
                </div>
                @foreach ($report->attachments as $attachment)
                    @php
                        $temp_file      = explode('/', $attachment->attachment);
                        $filename       = array_pop($temp_file);
                        $temp_file_2    = explode('.', $attachment->attachment);
                        $file_type      = array_pop($temp_file_2);
                    @endphp
                    <div class="row attachment">
                        <div class="col-md-12">
                            <div class="form-group">
                                @if (in_array($file_type, ['jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG']))
                                    <a href="#" class="btn btn-md btn-primary image-file"
                                        data-image="{{ $attachment->attachment }}" data-name="{{ $filename }}">
                                        <i class="fas fa-file"></i> {{ $filename }}
                                    </a>
                                @else
                                    <a href="{{ asset($attachment->attachment) }}" target="blank" class="btn btn-md btn-info align-items-center text-start"
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('View') }}">
                                        <i class="fas fa-file"></i> {{ $filename }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <hr>
            
            {{-- Report Response --}}
            @if (\Auth::user()->employee?->id != $report->employee_id)
                {{ Form::open(['route' => ['report.response', $report->id], 'method' => 'post']) }}
                    <div id="response-form">
                        <div class="row my-2">
                            <div class="col-auto">
                                <h4>{{ __("Report Response") }}</h4>
                            </div>
                        </div>
                        <div class="row response">
                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    {{ Form::textarea('response', $report->response, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Report Response')]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($report->response_by)
                        <div class="row">
                            <h6 class="text-muted">{{ __('Response From') }} : {{ $report->responder?->employee->name ?? $report->responder?->name}}</h6>
                        </div>
                    @endif
                    <hr>
                    <div class="text-end">
                        <a class="btn btn-md btn-light btn-outline-dark" href="{{ url('employee-report') }}" style="color: black;">{{ __('Cancel') }}</a>
                        
                        <button type="button" class="btn btn-primary btn-outline-dark bs-pass-para">{{ __('Send') }}</button>
                    </div>
                {{ Form::close() }}
            @else
                <div id="response-form">
                    <div class="row my-2">
                        <div class="col-auto">
                            <h4>{{ __("Report Response") }}</h4>
                        </div>
                    </div>
                    <div class="row response">
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                {{ Form::textarea('response', $report->response ?? '-', ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Report Response'), 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                    </div>
                </div>
                @if ($report->response_by)
                    <div class="row">
                        <h6 class="text-muted">{{ __('Response From') }} : {{ $report->responder?->employee->name ?? $report->responder?->name}}</h6>
                    </div>
                @endif
                <hr>
            @endif
        </div>
    </div>
@endsection

