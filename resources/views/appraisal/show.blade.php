@extends('layouts.admin')

@section('page-title')
   {{ __('Appraisal Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('appraisal') }}">{{ __('Appraisal') }}</a></li>
    <li class="breadcrumb-item">{{ __('Appraisal Detail') }}</li>
@endsection

@push('script-page')
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                        {{ Form::select('employee_id', $employee, $appraisal->employee_id, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Employee'), 'id' => 'employee_id', 'disabled' => 'disabled']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('start_month', __('Select Start Month'), ['class' => 'col-form-label']) }}
                        {{ Form::month('start_month', date('Y-m', strtotime($appraisal->start_month)), ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required', 'disabled' => 'disabled']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('end_month', __('Select End Month'), ['class' => 'col-form-label']) }}
                        {{ Form::month('end_month', date('Y-m', strtotime($appraisal->end_month)), ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required', 'disabled' => 'disabled']) }}
                    </div>
                </div>
            </div>
            <hr>
            <hr>
            @php
                $sub_title = 0;
            @endphp
            @if (count($goals) > 0)
                @php
                    $sub_title += 1;
                @endphp

                <div id="goals">
                    <i><h5>{{\Auth::user()->romanize($sub_title)}}. {{ __("Goal")}} :</h5></i>
                    <div class="table-responsive mt-3">
                        <table class="table" id="goal-table">
                            <thead>
                                <tr>
                                    <th>{{ __('No') }}</th>
                                    <th>{{ __('Main Goal') }}</th>
                                    <th>{{ __('Detail') }}</th>
                                    <th>{{ __('Evaluation') }}</th>
                                    <th>{{ __('Weight') }}</th>
                                    <th>{{ __('Rating') }}</th>
                                </tr>
                            </thead>
                            <tbody id="goal_data">
                                @foreach ($goals as $index => $goal)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td id='main_goal_select_{{$goal->id}}' style="width: 600px;">
                                            {{ Form::select("main_goal_id[$goal->id]", $main_goals, $goal?->goal?->parent?->id, ['class' => 'form-control select2 main_goal_id', 'id' => "main_goal_id_$goal->id", 'required' => 'required', 'disabled' => 'disabled']) }}
                                        </td>
                                        @php
                                            echo $goal?->goal?->parent?->id ? 'yes' : 'no';
                                        @endphp
                                        <td id="goal_detail_{{$goal->id}}" class='text-center'>
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  goal-items-center" data-size="lg"
                                                    data-url="{{ route('goal.show', $goal->goal_id) }}"
                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Goal Detail') }} - {{$index + 1}}"
                                                    data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td id="goal_eval_{{$goal->id}}">
                                            {{ Form::textarea("goal_evaluation[$goal->id]", $goal->evaluation, ['class' => 'form-control', 'placeholder' => __('Enter Evaluation'), 'required' => 'required', 'rows' => '5', 'style' => 'width: 250px;', 'disabled' => 'disabled']) }}
                                        </td>
                                        <td id="goal_weight_{{$goal->id}}">
                                            {{ Form::number("goal_weight[$goal->id]", $goal->weight, ['class' => 'form-control', 'placeholder' => __('Enter weight'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 100, 'style' => 'width: 100px;', 'disabled' => 'disabled']) }}
                                        </td>
                                        <td id="goal_rating_{{$goal->id}}">
                                            {{ Form::number("goal_rating[$goal->id]", $goal->rating, ['class' => 'form-control', 'placeholder' => __('Enter Rating'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 5, 'style' => 'width: 100px;', 'disabled' => 'disabled']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            <hr>
            <hr>
            @if (count($performances) > 0)
                <div id="weights">
                    @php
                        $sub_title += 1;
                    @endphp

                    <i><h5>{{\Auth::user()->romanize($sub_title)}}. {{ __('Competencies')}} :</h5></i>
                    <br>
                    @php
                        $performance_index = 0;
                    @endphp
                    @foreach ($performances as $performance_id => $performance)
                        @php
                            $performance_index += 1;
                        @endphp
                        <div class='row'>
                            <div class="col-12">
                                <div class="form-group">
                                    <h6>{{$performance_index}}. <u>{{$performance['name']}}</u></h6>
                                    <div id='performance_{{$performance_id}}' class='mx-4'>
                                        @php
                                            $order = 0;
                                        @endphp
                                        @foreach ($performance['competencies'] as $competency)
                                        @php
                                            $rating_id  = $competency['rating_id'];
                                            $order      += 1;
                                        @endphp
                                        {{ Form::label("compentecy[$rating_id]", "$performance_index.$order. {$competency['name']}", ['class' => 'col-form-label']) }}
                                            <p><small>{{$competency['description']}}</small></p>
                                            <div class='row mb-3'>
                                                {{ Form::hidden("competency_weight[$rating_id]", "{$competency['weight']}") }}
                                                <div class='col-8'>
                                                    {{ Form::textarea("competency_evaluation[$rating_id]", "{$competency['evaluation']}", ['class' => 'form-control', 'placeholder' => __('Enter Evaluation'), 'rows' => '2', 'disabled' => 'disabled']) }}
                                                </div>
                                                <div class='col-2'>
                                                    {{ Form::text('weight',  __('Weight') . ": {$competency['weight']}", ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                                </div>
                                                <div class='col-2'>
                                                    {{ Form::number("competency_rating[$rating_id]", "{$competency['rating']}", ['class' => 'form-control', 'placeholder' => __('Enter Rating'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 5, 'disabled' => 'disabled']) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    @endforeach
                </div>
            @endif
            <hr>
            <hr>
            <div id="essays">
                @php
                    $sub_title += 1;
                @endphp

                <i><h5>{{\Auth::user()->romanize($sub_title)}}. {{ __("Essay")}} :</h5></i>
                @foreach ($essay_competency as $index => $essay)
                    @php
                        $index += 1;
                    @endphp
                    <div class='row'>
                        <div class="col-12">
                            <div class="form-group">
                                {{ Form::label("essay[$essay->id]", "$index. {$essay->competency->name}", ['class' => 'col-form-label']) }}
                                <p><small>{{$essay->competency->description}}</small></p>
                                {{ Form::textarea("essay[$essay->id]", $essay->evaluation, ['class' => 'form-control', 'rows' => '2', 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        {{ Form::model($appraisal, ['route' => ['appraisal.comment', $appraisal->id], 'method' => 'PATCH']) }}
            <div class="card-body">
                <div class="form-group">
                    <h5>
                        {{ Form::label("comment", __('Comment From The Assessee'), ['class' => 'col-form-label']) }}
                    </h5>
                    @if ($appraisal->employee_id == \Auth::user()->employee?->id)
                        {{ Form::textarea("comment", $appraisal->comment, ['class' => 'form-control', 'rows' => '5', 'placeholder' => __('Enter Comment')]) }}
                    @else
                        {{ Form::textarea("comment", $appraisal->comment, ['class' => 'form-control', 'rows' => '5', 'disabled' => 'disabled']) }}
                    @endif
                </div>
            </div>
            @if ($appraisal->employee_id == \Auth::user()->employee?->id)
                <div class="card-footer text-end">
                    <a class="btn btn-md btn-light btn-outline-dark" href="{{ url('appraisal') }}" style="color: black;">{{ __('Cancel') }}</a>
                    
                    <button type="button" class="btn btn-primary btn-outline-dark bs-pass-para">{{ __('Send') }}</button>
                </div>
            @endif
        {{ Form::close() }}
    </div>
@endsection

