{{ Form::model($leave, ['route' => ['leave-office.approval', $leave->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ $leave->employee?->name ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Designation') }}</th>
                    <td>{{ $leave->employee?->designation?->name ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Department') }}</th>
                    <td>{{ $leave->employee?->department?->name ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Branch') }}</th>
                    <td>{{ $leave->employee?->branch?->name ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Time') }}</th>
                    <td>
                        <button class="btn btn-primary btn-sm" disabled="disabled">
                            {{ $leave->leave }}
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Location') }}</th>
                    <td>{{ $leave->location ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Need') }}</th>
                    <td>{{ $leave->need ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <td>
                        {{ Form::textarea('description', $leave->description, ['class' => 'form-control', 'disabled'=>'disabled','placeholder' => '-', 'rows' => '5']) }}
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($leave->status == 'Pending' || $leave->status == 'Waiting Superior Approval' || $leave->status == 'Waiting HR Approval')
                            <div class="badge bg-warning p-2 px-3 rounded">{{ __($leave->status) }}</div>
                        @elseif($leave->status == 'Approved')
                            <div class="badge bg-success p-2 px-3 rounded">{{ __($leave->status) }}</div>
                        @elseif($leave->status == "Rejected By HR" || $leave->status == "Rejected By Superior")
                            <div class="badge bg-danger p-2 px-3 rounded">{{ __($leave->status) }}</div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Status From Superior')}}</th>
                    <td>

                        @if ($leave->status == 'Rejected By Superior')
                            <button class="btn btn-danger btn-sm" disabled="disabled">
                                {{ $leave->superior->name }} |:| {{ __('Rejected') }}
                            </button>
                        @elseif ($leave->status == 'Waiting HR' || $leave->status == 'Approved')
                            <button class="btn btn-success btn-sm" disabled="disabled">
                                {{ $leave->superior->name }} |:| {{ __('Approved') }}
                            </button>
                        @else
                            -    
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Status From HR')}}</th>
                    <td>

                        @if ($leave->status == 'Rejected By HR')
                            <button class="btn btn-danger btn-sm" disabled="disabled">
                                {{ $leave->hr->name }} |:| {{ __('Rejected') }}
                            </button>
                        @elseif ($leave->status == 'Approved')
                            <button class="btn btn-success btn-sm" disabled="disabled">
                                {{ $leave->hr->name }} |:| {{ __($leave->status) }}
                            </button>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <input type="hidden" value="{{ $leave->id }}" name="leave_id">
            </table>
        </div>
    </div>
</div>

@if (
Auth::user()->employee?->id != $leave->employee_id &&
    (($leave->status == 'Waiting Superior' && in_array($leave->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray() ?? [])) ||
    ($leave->status == 'Waiting HR' && Auth::user()->type != 'employee')))
<div class="modal-footer">
    <button type="button" class="btn btn-success rounded bs-pass-para status" data-status="Approved" {{ $leave->status == 'Approved' ? 'disabled' : ''}}>{{ __('Approved') }}</button>
    @if (Auth::user()->type == 'employee')
        <button type="button" class="btn btn-danger rounded bs-pass-para status" data-status="Reject">{{ __('Reject') }}</button>
    @endif
    <input type="hidden" name="status" id="hiddenStatus">
</div>
@endif

{{ Form::close() }}
