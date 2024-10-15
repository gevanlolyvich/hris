{{ Form::open(['url' => 'employee-applications/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>
                        {{-- {{ !empty($employee->name) ? $employee->name : '' }} --}}
                        <a class="btn btn-outline-primary"
                            href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">
                            {{  $employee->name }}
                        </a>
                        <a class="btn btn-outline-primary"
                            href="{{ route('employeeattendancehistory.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">
                            {{ __("History") }}
                        </a>
                        @if ($employee_period->status =="Pending" || $employee_period->status =="Rejected")
                            <div class="action-btn bg-info ms-2">
                                <div class="action-btn bg-info ms-2">
                                    <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                        class="mx-3 btn btn-sm  align-items-center"
                                        data-bs-toggle="tooltip" title=""
                                        data-bs-original-title="{{ __('Edit') }}">
                                        <i class="ti ti-pencil text-white"></i>
                                    </a>
                                </div>
                            </div>
                        @endif
                        
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Branch') }}</th>
                    <td>{{ !empty($employee->branch->name) ? $employee->branch->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Email') }}</th>
                    <td>{{ !empty($employee->email) ? $employee->email : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Start Period') }}</th>
                    <td>{{ \Auth::user()->dateFormat($employee_period->start_period) }}</td>
                </tr>
                <tr>
                    <th>{{ __('End Period') }}</th>
                    <td>{{ \Auth::user()->dateFormat($employee_period->end_period) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Reason') }}</th>
                    <td>
                        {{ Form::textarea('reason', $employee_period->reason, ['class' => 'form-control', 'disabled'=>'disabled','placeholder' => __('Reason'), 'rows' => '3']) }}
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($employee_period->status == 'Pending')
                            <div class="badge bg-warning p-2 px-3 rounded">{{ $employee_period->status }}</div>
                        @elseif($employee_period->status == 'Approved')
                            <div class="badge bg-success p-2 px-3 rounded">{{ $employee_period->status }}</div>
                        @elseif($employee_period->status == "Reject")
                            <div class="badge bg-danger p-2 px-3 rounded">{{ $employee_period->status }}</div>
                        @endif
                    </td>
                </tr>
                <th>{{ __('Note') }}</th>
                    <td>
                        @if (\Auth::user()->type == 'employee')
                            {{ Form::textarea('response', $employee_period->response, ['class' => 'form-control', 'disabled'=>'disabled','placeholder' => __('Note'), 'rows' => '3']) }}
                        @else
                            {{ Form::textarea('response', $employee_period->response, ['class' => 'form-control', 'required'=>'required','placeholder' => __('Note'), 'rows' => '3']) }}
                        @endif
                    </td>
                <input type="hidden" value="{{ $employee_period->id }}" name="employee_period_id">
            </table>
        </div>
    </div>
</div>

@if (Auth::user()->type == 'company' || Auth::user()->type == 'hr' || in_array($employee_period->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray()))
    <div class="modal-footer">
        <button type="button" class="btn btn-success rounded bs-pass-para status" data-status="Approved" {{ $employee_period->status == 'Approved' ? 'disabled' : ''}}>{{ __('Approved') }}</button>
        <button type="button" class="btn btn-danger rounded bs-pass-para status" data-status="Reject" {{ $employee_period->status == 'Approved' ? 'disabled' : ''}}>{{ __('Reject') }}</button>
        <input type="hidden" name="status" id="hiddenStatus">
    </div>
@endif


{{ Form::close() }}
