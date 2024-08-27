
{{ Form::model($training, ['route' => ['training.result', $training->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data']) }}
    @php
        $access = \Auth::user()?->employee?->id != $training->employee;
    @endphp
    <div class="modal-body" style="padding-top: 0.35rem;padding-bottom: 0.35rem">
        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('result_file', __('Result File'), ['class' => 'col-form-label']) }} <span class="text-danger pl-1"> *</span>
                @if (\Auth::user()?->employee?->id == $training->employee)
                    {{ Form::file('result_file', ['class' => 'form-control mb-2', 'disabled' => $access]) }}
                    <span class="text-warning pl-1"><b>{{ __('Max Upload Size Per File: 10 MB')}}</b></span>
                @endif

                @if ($training->result_file)
                    @php
                        $result_file = explode('/', $training->result_file);
                        $filename  = array_pop($result_file);
                    @endphp
                    <br>
                    <hr>
                    <div class="text-center align-items-center">
                        <a href="{{ asset($training->result_file) }}" target="blank" class="btn btn-md btn-success align-items-center text-start mt-2"
                            data-bs-toggle="tooltip"
                            data-bs-original-title="{{ __('View') }}">
                            <i class="fas fa-file"></i> {{ $filename }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <input type="hidden" value="{{ $training->id }}" name="training_id">
    </div>
    @if (!$access)
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Send') }}" class="btn btn-primary">
        </div>
    @endif
{{ Form::close() }}