

<div class="modal-body">
    <div class="row py-4">
        <div class="col-md-12 ">
            <div class="info text-sm">
                <strong>{{ __('Level Designation') }} : </strong>
                <span>{{ $indicator->level->name ?? '-' }}</span>
            </div>
        </div>

    </div>
    <div class="row">
        @foreach ($performance_types as $performance_type)
            <div class="col-md-12 mt-3">
                <h5>{{ $performance_type->name }}</h5>
                <hr class="mt-0">
            </div>
            @foreach ($performance_type->competencies as $comptency_0)
                <div class="row mt-2 mx-1">
                    <div class="col-9 align-items-center">
                        <p>{{ $comptency_0->name }}</p>
                    </div>
                    <div class="col-3">
                        {{ Form::number($comptency_0->id, array_key_exists("competency_{$comptency_0->id}", $processed_weights) ? $processed_weights["competency_{$comptency_0->id}"] : null, ['class' => 'form-control', 'name' => "competencies[{$comptency_0->id}]", 'min' => '0', 'disabled' => 'disabled']) }}
                    </div>
                </div>
            @endforeach
            @foreach ($performance_type->child as $type_1)
                <div class="col-12 mt-4 mx-1">
                    <h6><i>{{ $type_1->name }}</i></h6>
                    <hr class="mt-0">
                </div>
                <br>
                @if (count($type_1->child) > 0)
                    @foreach ($type_1->child as $type_2)
                        <div class="col-12 mt-4 mx-2">
                            <h6><i>{{ $type_2->name }}</i></h6>
                            <hr class="mt-0">
                        </div>
                        @if (count($type_2->child) > 0)
                        @else
                            @foreach ($type_2->competencies as $comptency_2)
                                <div class="row mt-2 mx-2">
                                    <div class="col-9 align-items-center">
                                        <p>{{ $comptency_2->name }}</p>
                                    </div>
                                    <div class="col-2">
                                        {{ Form::number("competency_{$comptency_2->id}", array_key_exists("competency_{$comptency_2->id}", $processed_weights) ? $processed_weights["competency_{$comptency_2->id}"] : null, ['id' => "competency_{$comptency_2->id}", 'class' => 'form-control', 'name' => "competencies[{$comptency_2->id}]", 'disabled' => 'disabled']) }}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    @foreach ($type_1->competencies as $comptency_1)
                        <div class="row mt-2 mx-1">
                            <div class="col-9 align-items-center">
                                <p>{{ $comptency_1->name }}</p>
                            </div>
                            <div class="col-2">
                                {{ Form::number("competency_{$comptency_1->id}", array_key_exists("competency_{$comptency_1->id}", $processed_weights) ? $processed_weights["competency_{$comptency_1->id}"] : null, ['id' => "competency_{$comptency_1->id}", 'class' => 'form-control', 'name' => "competencies[{$comptency_1->id}]", 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        @endforeach
    </div>
</div>
