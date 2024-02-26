{{ Form::open(['url' => 'indicator', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('level', __('Level Designation'), ['class' => 'col-form-label']) }}
                {{ Form::select('level', $levels, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Level Designation')]) }}
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
                                    <div class="col-3">
                                        {{ Form::number($comptency_2->id, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Value Weight'), 'name' => "competencies[{$comptency_2->id}]", 'min' => '0']) }}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    @foreach ($type_1->competencies as $comptency_1)
                        <div class="row mt-2">
                            <div class="col-9 align-items-center" >
                                <p>{{ $comptency_1->name }}</p>
                            </div>
                            <div class="col-3">
                                {{ Form::number($comptency_1->id, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Value Weight'), 'name' => "competencies[{$comptency_1->id}]", 'min' => '0']) }}
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        @endforeach
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
{{-- </div> --}}
