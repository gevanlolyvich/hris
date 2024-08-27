{{ Form::model($training, ['route' => ['training.approval', $training->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ $training->employee_ref->name ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Training Name') }}</th>
                    <td>{{ $training->name }}</td>
                </tr>
                <tr>
                    <th>{{ __('Training Organizer') }}</th>
                    <td>{{ $training->organizer }}</td>
                </tr>
                <tr>
                    <th>{{ __('Training Type') }}</th>
                    <td>{{ $training?->type?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($training->start_date) . ' To ' . \Auth::user()->dateFormat($training->end_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <td>
                        {{ Form::textarea('description', $training->description, ['class' => 'form-control', 'disabled'=>'disabled','placeholder' => '-', 'rows' => '7']) }}
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($training->status == 'Pending')
                            <div class="badge bg-warning p-2 px-3 rounded">{{ __('Pending Approval') }}</div>
                        @elseif($training->status == 'Approved')
                            <div class="badge bg-success p-2 px-3 rounded">{{ __($training->status) }}</div>
                        @elseif($training->status == "Reject")
                            <div class="badge bg-danger p-2 px-3 rounded">{{ __($training->status) }}</div>
                        @endif
                    </td>
                </tr>
                @if ($training->status == 'Approved')
                    <tr>
                        <th>{{ __('Result File') }}</th>
                        <td>
                            @if ($training->result_file)
                                <div class="action-btn bg-info ms-2">
                                    <a href="{{ asset($training->result_file) }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('View') }}">
                                        <i class="fas fa-file text-white"></i>
                                    </a>
                                </div>
                            @else
                            -
                            @endif 
                        </td>
                    </tr>
                @endif
                <input type="hidden" value="{{ $training->id }}" name="training_id">
            </table>
        </div>
    </div>
</div>

@if (Auth::user()->type != 'employee')
<div class="modal-footer">
    <button type="button" class="btn btn-success rounded bs-pass-para status" data-status="Approved" {{ $training->status == 'Approved' ? 'disabled' : ''}}>{{ __('Approved') }}</button>
    <button type="button" class="btn btn-danger rounded bs-pass-para status" data-status="Reject" {{ $training->status == 'Approved' ? 'disabled' : ''}}>{{ __('Reject') }}</button>
    <input type="hidden" name="status" id="hiddenStatus">
</div>
@endif

{{ Form::close() }}
