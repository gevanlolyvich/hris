<div class="modal-body">

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::date('date', $healthy_step->date, ['class' => 'form-control', 'required' => 'required' , 'disabled'=>'disabled','placeholder'=> __('Enter Date'), 'max' => date('Y-m-d')]) }}
            </div>
            
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('steps', __('Number Of Steps'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                {{ Form::number('steps', $healthy_step->steps, ['class' => 'form-control', 'required' => 'required' , 'disabled'=>'disabled', 'placeholder'=> __('Enter Number Of Steps')]) }}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="clock-images mx-d-flex flex-column align-items-center" id="photos">
                <div class="text-center mx-auto">
                    <strong>{{__('Attachment')}}</strong>
                    <br>
                    <img id="clockImage" src="{{ $healthy_step->attachment }}" alt="Clock In Out Image" style="max-width: 100%; max-height: 300px; border-radius: 5%" class="mb-3 mt-1">
                    <br>
                </div>
            </div>
        </div>
        <div class="clock-images mx-d-flex flex-column align-items-center" id="photos" style="display: none;">
            <div class="text-center mx-auto">
                <strong>{{__('Clock In / Out Image Capture')}}</strong>
                <br>
                <img id="clockImage" src="{{ $healthy_step->attachment }}" alt="Clock In Out Image" style="max-width: 100%; max-height: 300px; border-radius: 5%" class="mb-3 mt-1">
                <br>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Close') }}" class="btn btn-light" data-bs-dismiss="modal">
    {{-- <input type="submit" value="{{ __('Update') }}" class="btn btn-primary"> --}}
</div>