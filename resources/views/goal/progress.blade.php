
{{ Form::model($goal, ['url' => "goal/{$goal->id}/progress", 'method' => 'PATCH']) }}
<div class="modal-body">
    <div class="row">
        <input type="hidden" name="goal_id" id="goal_id" value="{{ $goal->id }}">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('goal', __('Progress'), ['class' => 'form-label'])}}<span class="text-danger pl-1"> *</span>
                {{ Form::textarea('goal', null, ['class' => 'form-control', 'rows'=>'4', 'required' => 'required', 'placeholder'=>__('Enter Progress')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('progress', __('Progress Percentage'), ['class' => 'form-label'])}}<span class="text-danger pl-1"> *</span>
                <input type="range" class="slider w-100 mb-0 " name="progress" id="myRange"
                    value="{{ $goal->progress }}" min="1" max="100"
                    oninput="ageOutputId.value = myRange.value">
                <output name="ageOutputName" id="ageOutputId">{{ $goal->progress }}</output>
                %
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
