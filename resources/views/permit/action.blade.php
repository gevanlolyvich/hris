{{ Form::open(['url' => 'permit/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ !empty($employee->name) ? $employee->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Permit Type') }}</th>
                    <td>{{ $permit->permitType->name }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($permit->start_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($permit->end_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Reason') }}</th>
                    <td>{{ !empty($permit->reason) ? $permit->reason : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Document') }}</th>
                    <td>
                        @if ($permit->docs)
                            <div class="action-btn bg-info ms-2">
                                <a href="{{ $permit->docs }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
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
                        @if ($permit->status == 'Pending')
                            <div class="badge bg-warning p-2 px-3 rounded">{{ $permit->status }}</div>
                        @elseif($permit->status == 'Approved')
                            <div class="badge bg-success p-2 px-3 rounded">{{ $permit->status }}</div>
                        @elseif($permit->status == "Reject")
                            <div class="badge bg-danger p-2 px-3 rounded">{{ $permit->status }}</div>
                        @endif
                    </td>
                </tr>
                <input type="hidden" value="{{ $permit->id }}" name="permit_id">
            </table>
        </div>
    </div>
</div>

@if (Auth::user()->type == 'company' || Auth::user()->type == 'hr' || in_array($permit->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray()))
<div class="modal-footer">
    <button type="button" class="btn btn-success rounded bs-pass-para status" data-status="Approved" {{ $permit->status == 'Approved' ? 'disabled' : ''}}>{{ __('Approved') }}</button>
    <button type="button" class="btn btn-danger rounded bs-pass-para status" data-status="Reject" {{ $permit->status == 'Approved' ? 'disabled' : ''}}>{{ __('Reject') }}</button>
    <input type="hidden" name="status" id="hiddenStatus">
</div>
@endif


{{ Form::close() }}
