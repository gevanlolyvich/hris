{{ Form::open(['url' => 'attendancerequest/changeaction', 'method' => 'post']) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-12">
                <table class="table modal-table" id="pc-dt-simple">
                    <tr role="row">
                        <th>{{ __('Employee') }}</th>
                        <td>{{ !empty($employee->name) ? $employee->name : '' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <td>{{ date('d M Y', strtotime($attendance_request->date)) }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Shift') }}</th>
                        <td>{{ !empty($shiftTimes) ? $shiftTimes . ' | ' . $attendance_request->shift->name : '-'  }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Start Time') }}</th>
                        <td>{{ $attendance_request->start_time }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('End Time') }}</th>
                        <td>{{ $attendance_request->end_time }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Reason') }}</th>
                        <td>{{ $attendance_request->reason }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Document') }}</th>
                        <td>
                            @if ($attendance_request->docs)
                                <div class="action-btn bg-info ms-2">
                                    <a href="{{ $attendance_request->docs }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
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
                            @if (is_null($attendance_request->is_approved))
                                <div class="badge bg-warning p-2 px-3 rounded">Waiting</div>
                            @endif
                            @if ($attendance_request->is_approved == 1)
                                <div class="badge bg-success p-2 px-3 rounded">Approved</div>
                            @endif
                            @if ($attendance_request->is_approved === 0)
                                <div class="badge bg-danger p-2 px-3 rounded">Rejected</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('Approved By') }}</th>
                        <td>{{ $attendance_request?->approvedBy?->employee?->name ?? $attendance_request?->approvedBy?->name ?? '-' }}</td>
                    </tr>
                    <input type="hidden" value="{{ $attendance_request->id }}" name="attendance_request_id">
                </table>
            </div>
        </div>
    </div>

    @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr' || in_array($attendance_request->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray()))
    <div class="modal-footer">
        <button type="button" class="btn btn-success rounded bs-pass-para status" data-status="Approved" {{ $attendance_request->is_approved == '1' ? 'disabled' : ''}}>{{ __('Approved') }}</button>
        <button type="button" class="btn btn-danger rounded bs-pass-para status" data-status="Reject" {{ $attendance_request->is_approved == '1' ? 'disabled' : ''}}>{{ __('Reject') }}</button>
        <input type="hidden" name="status" id="hiddenStatus">
    </div>
    @endif
{{ Form::close() }}
