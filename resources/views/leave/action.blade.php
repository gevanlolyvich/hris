{{ Form::open(['url' => 'leave/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ !empty($employee->name) ? $employee->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Type ') }}</th>
                    <td>{{ !empty($leavetype->title) ? $leavetype->title : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Appplied On') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Reason') }}</th>
                    <td>{{ !empty($leave->leave_reason) ? $leave->leave_reason : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Location') }}</th>
                    <td>{{ !empty($leave->location) ? $leave->location : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Remark') }}</th>
                    <td>{{ !empty($leave->remark) ? $leave->remark : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Document') }}</th>
                    <td>
                        @if ($leave->document_path)
                            <div class="action-btn bg-info ms-2">
                                <a href="{{ asset($leave->document_path) }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
                                    data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('View') }}">
                                    <i class="ti ti-file text-white"></i>
                                </a>
                            </div>
                        @else
                        -
                        @endif 
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($leave->status == 'Pending')
                            <div class="badge bg-warning p-2 px-3 rounded">{{ $leave->status }}</div>
                        @elseif($leave->status == 'Approved')
                            <div class="badge bg-success p-2 px-3 rounded">{{ $leave->status }}</div>
                        @elseif($leave->status == "Reject")
                            <div class="badge bg-danger p-2 px-3 rounded">{{ $leave->status }}</div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Note') }}</th>
                    <td>
                        @if (\Auth::user()->type == 'employee')
                        {{ Form::textarea('note', $leave->note, ['class' => 'form-control', 'disabled'=>'disabled','placeholder' => __('Note'), 'rows' => '3']) }}
                        @else
                        {{ Form::textarea('note', $leave->note, ['class' => 'form-control', 'required'=>'required','placeholder' => __('Note'), 'rows' => '3']) }}
                        @endif
                    </td>
                </tr>
                <input type="hidden" value="{{ $leave->id }}" name="leave_id">
            </table>
        </div>
    </div>
</div>

@if ($leave->employee_id !== \Auth::user()->employee->id)
<div class="modal-footer">
    <button type="button" class="btn btn-success rounded bs-pass-para status" data-status="Approved" {{ $leave->status == 'Approved' ? 'disabled' : ''}}>{{ __('Approved') }}</button>
    <button type="button" class="btn btn-danger rounded bs-pass-para status" data-status="Reject" {{ $leave->status == 'Approved' ? 'disabled' : ''}}>{{ __('Reject') }}</button>
    <input type="hidden" name="status" id="hiddenStatus">
</div>
@endif


{{ Form::close() }}
