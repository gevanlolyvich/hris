

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
            @foreach ($performance_type->child as $type_1)
                <div class="col-12 mt-4">
                    <h6><i>{{ $type_1->name }}</i></h6>
                    <hr class="mt-0">
                </div>
                <br>
                @if (count($type_1->child) > 0)
                    @foreach ($type_1->child as $type_2)
                        <div class="col-12 mt-4">
                            <h6><i>{{ $type_2->name }}</i></h6>
                            <hr class="mt-0">
                        </div>
                        @if (count($type_2->child) > 0)
                        @else
                            @foreach ($type_2->competencies as $comptency_2)
                                <div class="row mt-2">
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
                        <div class="row mt-2">
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
        {{-- @foreach ($performance_types as $performances)
            <div class="col-md-12 mt-3">
                <h6>{{ $performances->name }}</h6>
                <hr class="mt-0">
            </div>
            @foreach ($performances->types as $types)
                <div class="col-6">
                    {{ $types->name }}
                </div>
                <div class="col-6">
                    <fieldset id='demo1' class="rate">
                        <input class="stars" type="radio" id="technical-5-{{ $types->id }}"
                            name="rating[{{ $types->id }}]" value="5"
                            {{ isset($ratings[$types->id]) && $ratings[$types->id] == 5 ? 'checked' : '' }} disabled>
                        <label class="full" for="technical-5-{{ $types->id }}"
                            title="Awesome - 5 stars"></label>
                        <input class="stars" type="radio" id="technical-4-{{ $types->id }}"
                            name="rating[{{ $types->id }}]" value="4"
                            {{ isset($ratings[$types->id]) && $ratings[$types->id] == 4 ? 'checked' : '' }} disabled>
                        <label class="full" for="technical-4-{{ $types->id }}"
                            title="Pretty good - 4 stars"></label>
                        <input class="stars" type="radio" id="technical-3-{{ $types->id }}"
                            name="rating[{{ $types->id }}]" value="3"
                            {{ isset($ratings[$types->id]) && $ratings[$types->id] == 3 ? 'checked' : '' }} disabled>
                        <label class="full" for="technical-3-{{ $types->id }}"
                            title="Meh - 3 stars"></label>
                        <input class="stars" type="radio" id="technical-2-{{ $types->id }}"
                            name="rating[{{ $types->id }}]" value="2"
                            {{ isset($ratings[$types->id]) && $ratings[$types->id] == 2 ? 'checked' : '' }} disabled>
                        <label class="full" for="technical-2-{{ $types->id }}"
                            title="Kinda bad - 2 stars"></label>
                        <input class="stars" type="radio" id="technical-1-{{ $types->id }}"
                            name="rating[{{ $types->id }}]" value="1"
                            {{ isset($ratings[$types->id]) && $ratings[$types->id] == 1 ? 'checked' : '' }} disabled>
                        <label class="full" for="technical-1-{{ $types->id }}"
                            title="Sucks big time - 1 star"></label>
                    </fieldset>
                </div>
            @endforeach
        @endforeach --}}
    </div>
</div>
