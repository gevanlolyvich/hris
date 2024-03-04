@extends('layouts.admin')

@section('page-title')
   {{ __('Create New Appraisal') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('appraisal') }}">{{ __('Appraisal') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create New Appraisal') }}</li>
@endsection

@push('script-page')
    <script>
        $(document).ready(function () {
            let main_goals      = [];
            let personal_goals  = [];
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

            function getGoal(employee_id, start_month, end_month) {
                $.ajax({
                    url: '{{ route('appraisal.getgoal') }}',
                    type: 'POST',
                    data: {
                        "employee_id": employee_id,
                        "start_month": start_month,
                        "end_month": end_month,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        $('#goals').empty();

                        main_goals = data.main_goals;
                        personal_goals = data.personal_goals;

                        if (personal_goals.length) {
                            roman_chapter.push('Goal');

                            let chapter_number = convertToRoman(roman_chapter.length);
                            $('#goals').append(`
                                <hr>
                                <hr>
                                <i><h5>${chapter_number}. {{ __("Goal")}} :</h5></i>
                                <span class='mx-1'><small># {{__('Note')}}</small></span>
                                <br>
                                <span class='mx-1'><small>- {{__('Weight Total Must Be 100')}}</small></span>
                                <br>
                                <span class='mx-1'><small>- {{__('Rating Must Be Between 1 And 5')}}</small></span>
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
                            personal_goals.forEach((element, index) => {
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
                                let goal_eval   = `{{ Form::textarea('goal_evaluation[${elementId}]', null, ['class' => 'form-control', 'placeholder' => __('Enter Evaluation'), 'required' => 'required', 'rows' => '5', 'style' => 'width: 250px;']) }}`;
                                let goal_weight = `{{ Form::number('goal_weight[${elementId}]', '', ['class' => 'form-control', 'placeholder' => __('Enter weight'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 100, 'style' => 'width: 100px;']) }}`;
                                let goal_rating = `
                                    {{ Form::number('goal_rating[${elementId}]', '', ['class' => 'form-control', 'placeholder' => __('Enter Rating'), 'required' => 'required', 'step' => '1', 'min' => 1, 'max' => 5, 'style' => 'width: 100px;']) }}
                                `;
    
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
                                    $(`.main_goal_id`).append('<option value="' + value.id + '">' + value.name +
                                        '</option>');
                                });
                                
                                new Choices(`#main_goal_id_${elementId}`, {
                                    removeItemButton: true,
                                });
                            });
                        } else {
                            $('#goals').empty();
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
                            <hr>
                            <hr>
                            <i><h5>${chapter_number}. {{ __("Essay")}} :</h5></i>
                            <span class='mx-4'><small># {{__('Note')}}</small></span>
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
                        let indicator_id    = null;

                        let keys = Object.keys(data);

                        if (keys.length) {
                            roman_chapter.push('Competency');
                            let chapter_number = convertToRoman(roman_chapter.length);
    
                            $('#weights').append(`
                                <hr>
                                <hr>
                                <i><h5>${chapter_number}. {{ __("Competency")}} :</h5></i>
                                <span class='mx-4'><small>- {{__('Weight Total Must Be 100')}}</small></span>
                                <br>
                                <span class='mx-4'><small>- {{__('Rating Must Be Between 1 And 5')}}</small></span>
                                <br>
                                <br>
                            `);
    
                            keys.forEach((element, index) => {
                                if (!indicator_id) {
                                    indicator_id        = data[element]['indicator_id'];
                                }
                                let competencies        = data[element]['competencies'];
                                let performance_html    = `
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

                            let indicator_html = `{{ Form::hidden('indicator_id', '${indicator_id}') }}`;
                            $('#weights').append(indicator_html);
                        }
                    }
                });
            }

            $('body').on('change', 'select[name=employee_id]', function() {
                let employee_id = $(this).val();
                let start_month = document.getElementById("start_month");
                let end_month   = document.getElementById("end_month");

                roman_chapter.length = 0;

                if (employee_id && start_month.value && end_month.value) {
                    getGoal(employee_id, start_month.value, end_month.value);
                    getWeightCompetenct(employee_id);
                    getEssay(employee_id);
                }
            });
            $('body').on('change', '#start_month', function() {
                let employee_id = document.getElementById("employee_id");
                let start_month = $(this).val();
                let end_month   = document.getElementById("end_month");

                roman_chapter.length = 0;

                if (employee_id.value && start_month && end_month.value) {
                    getGoal(employee_id.value, start_month, end_month.value);
                    getWeightCompetenct(employee_id.value);
                    getEssay(employee_id.value);
                }
            });
            $('body').on('change', '#end_month', function() {
                let employee_id = document.getElementById("employee_id");
                let start_month = document.getElementById("start_month");
                let end_month   = $(this).val();

                roman_chapter.length = 0;

                if (employee_id.value && start_month.value && end_month) {
                    getGoal(employee_id.value, start_month.value, end_month);
                    getWeightCompetenct(employee_id.value);
                    getEssay(employee_id.value);
                }
            });
        });
    </script>
@endpush

@section('content')
    <div class="card">
        {{ Form::open(['route' => ['appraisal.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Employee'), 'id' => 'employee_id']) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('start_month', __('Select Start Month'), ['class' => 'col-form-label']) }}
                            {{ Form::month('start_month', '', ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required', 'id' => 'start_month']) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('end_month', __('Select End Month'), ['class' => 'col-form-label']) }}
                            {{ Form::month('end_month', '', ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required', 'id' => 'end_month']) }}
                        </div>
                    </div>
                </div>
                <div id="goals">
                </div>
                <div id="weights">
                </div>
                <div id="essays">
                </div>
            </div>
    
            <div class="card-footer text-end">
                <a class="btn btn-md btn-light btn-outline-dark" href="{{ url('appraisal') }}" style="color: black;">{{ __('Cancel') }}</a>
                
                <button type="button" class="btn btn-primary btn-outline-dark bs-pass-para">{{ __('Create') }}</button>
            </div>
        {{ Form::close() }}
    </div>
@endsection

