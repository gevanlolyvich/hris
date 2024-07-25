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
                    <th>{{ __('Transfer Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($transfer->transfer_date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Branch') }}</th>
                    <td>{{ !empty($transfer->branch->name) ? $transfer->branch->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Department') }}</th>
                    <td>{{ !empty($transfer->department->name) ? $transfer->department->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Designation') }}</th>
                    <td>{{ !empty($transfer->designation->name) ? $transfer->designation->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Shift') }}</th>
                    <td>{{ !empty($transfer->shift->name) ? $transfer->shift->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Supervisor') }}</th>
                    <td>{{ !empty($transfer->managed->name) ? $transfer->managed->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Document') }}</th>
                    <td>
                        @if ($transfer->document_path)
                            <div class="action-btn bg-info ms-2">
                                <a href="{{ asset($transfer->document_path) }}" target="blank" class="mx-3 btn btn-sm  align-items-center"
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
                    <th>{{ __('Note') }}</th>
                    <td>
                        {{ Form::textarea('note', $transfer->description, ['class' => 'form-control', 'disabled'=>'disabled','placeholder' => __('Note'), 'rows' => '3']) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{ Form::close() }}
