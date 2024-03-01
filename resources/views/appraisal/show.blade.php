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
    <script>
        $(document).ready(function () {
            let main_goals      = @json($main_goals);
            let roman_chapter   = [];

            function convertToRoman(number) {
                // Define the Roman numeral symbols and their corresponding values
                var romanSymbols = {
                    'M': 1000,
                    'CM': 900,
                    'D': 500,
                    'CD': 400,
                    'C': 100,
                    'XC': 90,
                    'L': 50,
                    'XL': 40,
                    'X': 10,
                    'IX': 9,
                    'V': 5,
                    'IV': 4,
                    'I': 1
                };

                var romanNumeral = '';

                // Iterate through the symbols and subtract their values from the number
                for (var symbol in romanSymbols) {
                    while (number >= romanSymbols[symbol]) {
                        romanNumeral += symbol;
                        number -= romanSymbols[symbol];
                    }
                }

                return romanNumeral;
            }

            function getGoal(employee_id) {
                $.ajax({
                    url: '{{ route('appraisal.getgoal') }}',
                    type: 'POST',
                    data: {
                        "employee_id": employee_id,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        $('#goals').empty();

                        if (data.length) {
                            roman_chapter.push('Goal');

                            let chapter_number = convertToRoman(roman_chapter.length);
                            $('#goals').append(`
                                <i><h5>${chapter_number}. {{ __("Goal")}} :</h5></i>
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
                                        </tbody>
                                    </table>
                                </div>
                            `);
                            data.forEach((element, index) => {
                                let elementId   = element?.id;
                                // let goal_selct = `{{ Form::select('main_goal_id_${elementId}', [], null, ['class' => 'form-control select2 main_goal_id', 'id' => 'main_goal_id_${elementId}', 'style' => 'width: 600px;']) }}`;
                                let goal_selct  = `<select class="form-control select2 main_goal_id" name="main_goal_id[${elementId}]" id="main_goal_id_${elementId}" style="width: 600px;"></select>`;
                                let goal_detail = `<div class="action-btn bg-warning ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  goal-items-center" data-size="lg"
                                                            data-url="{{ route('goal.show', "TheElementId") }}"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Goal Detail') }} - ${index + 1}"
                                                            data-bs-original-title="{{ __('View') }}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>`;
                                let goal_eval   = `{{ Form::textarea('goal_evaluation[${elementId}]', null, ['class' => 'form-control', 'placeholder' => __('Enter Evaluation'), 'required' => 'required', 'rows' => '3', 'style' => 'width: 250px;']) }}`;
                                let goal_weight = `{{ Form::number('goal_weight[${elementId}]', '', ['class' => 'form-control', 'placeholder' => __('Enter weight'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 100, 'style' => 'width: 100px;']) }}`;
                                let goal_rating = `{{ Form::number('goal_rating[${elementId}]', '', ['class' => 'form-control', 'placeholder' => __('Enter Rating'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 5, 'style' => 'width: 100px;']) }}`;
    
                                // Replace /elementId placeholder with actual value
                                goal_detail = goal_detail.replace('TheElementId', elementId);
    
                                $('#goal_data').append(`
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td id='main_goal_select_${elementId}' style="width: 600px;"></td>
                                        <td id='goal_detail_${elementId}' class='text-center'>
                                        </td>
                                        <td id='goal_eval_${elementId}'>
                                        </td>
                                        <td id='goal_weight_${elementId}'>
                                        </td>
                                        <td id='goal_rating_${elementId}'>
                                        </td>
                                    </tr>
                                `)
                                $(`#main_goal_select_${elementId}`).html(goal_selct);
                                $(`#goal_detail_${elementId}`).html(goal_detail);
                                $(`#goal_eval_${elementId}`).html(goal_eval);
                                $(`#goal_weight_${elementId}`).html(goal_weight);
                                $(`#goal_rating_${elementId}`).html(goal_rating);
    
                                $(`.main_goal_id`).append('<option value="" disabled selected>{{ __('Select Main Goal') }}</option>');
                                $.each(main_goals, function(key, value) {
                                    $(`.main_goal_id`).append('<option value="' + key + '">' + value +
                                        '</option>');
                                });
                                
                                new Choices(`#main_goal_id_${elementId}`, {
                                    removeItemButton: true,
                                });
                            });
                        }
                    }
                });
            }

            function getEssay(employee_id) {
                $.ajax({
                    url: '{{ route('appraisal.getessay') }}',
                    type: 'POST',
                    data: {
                        "employee_id": employee_id,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        $('#essays').empty();

                        roman_chapter.push('Essay');
                        let chapter_number = convertToRoman(roman_chapter.length);

                        $('#essays').append(`
                            <i><h5>${chapter_number}. {{ __("Essay")}} :</h5></i>
                        `);
                        data.forEach((essay, index) => {
                            let essayId     = essay?.id;
                            let essayDesc   = essay?.description || '';
                            $('#essays').append(`
                                <div class='row'>
                                    <div class="col-12">
                                        <div class="form-group">
                                            {{ Form::label('essay[${essayId}]', '${essay?.name}', ['class' => 'col-form-label']) }}
                                            <p><small>${essayDesc}</small></p>
                                            {{ Form::textarea('competency_essay[${essayId}]', null, ['class' => 'form-control', 'placeholder' => __('Enter Evaluation'), 'rows' => '2']) }}
                                        </div>
                                    </div>
                                </div>
                            `);
                        });
                    }
                });
            }

            function getWeightCompetenct(employee_id) {
                $.ajax({
                    url: '{{ route('appraisal.getweightcompetency') }}',
                    type: 'POST',
                    data: {
                        "employee_id": employee_id,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        $('#weights').empty();

                        let keys = Object.keys(data);

                        if (keys.length) {
                            roman_chapter.push('Competency');
                            let chapter_number = convertToRoman(roman_chapter.length);
    
                            $('#weights').append(`
                                <i><h5>${chapter_number}. {{ __("Competency")}} :</h5></i>
                                <br>
                            `);
    
                            keys.forEach((element, index) => {
                                let competencies    = data[element]['competencies'];
                                let performance_html = `
                                    <div class='row'>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <h6>${index + 1}. <u>${data[element]['name']}</u></h6>
                                                <div id='performance_${element}' class='mx-4'>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                `;
    
                                $('#weights').append(performance_html);
    
                                competencies.forEach((competency, order) => {
                                    let compentency_html    = `
                                        {{ Form::label('compentecy[${competency.id}]', '${index + 1}.${order + 1}'. '. ${competency?.name}', ['class' => 'col-form-label']) }}
                                        <p><small>${competency.description}</small></p>
                                        <div class='row mb-3'>
                                            {{ Form::hidden('competency_weight[${competency.id}]', '${competency?.weight}') }}
                                            <div class='col-6'>
                                                {{ Form::textarea('competency_evaluation[${competency.id}]', null, ['class' => 'form-control', 'placeholder' => __('Enter Evaluation'), 'rows' => '2']) }}
                                            </div>
                                            <div class='col-3'>
                                                {{ Form::text('weight',  __('Weight') . ': ${competency?.weight}', ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                            </div>
                                            <div class='col-3'>
                                                {{ Form::number('competency_rating[${competency.id}]', null, ['class' => 'form-control', 'placeholder' => __('Enter Rating'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 5]) }}
                                            </div>
                                        </div>
                                    `;
    
                                    $(`#performance_${element}`).append(compentency_html);
                                })
                            });
                        }
                    }
                });
            }

            $('body').on('change', 'select[name=employee_id]', function() {
                var employee_id = $(this).val();

                roman_chapter.length = 0;

                if (employee_id) {
                    getGoal(employee_id);
                    getWeightCompetenct(employee_id);
                    getEssay(employee_id);
                }
            });
        });
    </script>
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
@endsection

