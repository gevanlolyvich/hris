
{{ Form::model($healthy_step, ['route' => ['healthy-steps.update', $healthy_step->id],'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::date('date', $healthy_step->date, ['class' => 'form-control', 'required' => 'required' , 'disabled'=>'disabled', 'placeholder'=> __('Enter Date'), 'max' => date('Y-m-d')]) }}
            </div>
            <div class="form-group">
                {{ Form::label('steps', __('Number Of Steps'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::number('steps', $healthy_step->steps, ['class' => 'form-control', 'required' => 'required' ,'placeholder'=> __('Enter Number Of Steps')]) }}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('document', __('Document'), ['class' => 'col-form-label pb-1 pt-3']) }}
                <p style="color: rgba(218, 71, 71, 0.788)" class="mb-2">* {{__('Required')}}</p>
                <div>
                    <label for="attachment">
                        <div class="btn btn-block btn-primary bg-primary document">
                            <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                        </div>
                        <input style="margin-top: -50px" type="file" class="form-control mb-4 file" name="attachment">
                    </label>
                    <div class="btn btn-block btn-info btn-sm bg-info disabled" style="display: none;" id="uploadFile">
                        <i class="ti ti-file text-white"></i><p id="fileName"></p>
                    </div>
                    @if ($healthy_step->attachment)
                        <div class="mt-2">
                            <a href="{{ url($healthy_step->attachment) }}" target="_blank" class="btn btn-block btn-secondary">
                                <i class="ti ti-file px-1"></i>{{ __('View Existing File') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}



