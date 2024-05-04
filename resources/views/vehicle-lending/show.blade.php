{{ Form::model($vehicleLending, ['route' => ['vehicle-lending.approval', $vehicleLending->id], 'method' => 'PATCH', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ $vehicleLending->requester->name ?? '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Vehicle') }}</th>
                    <td>{{ $vehicleLending->vehicle->name }}</td>
                </tr>
                <tr>
                    <th>{{ __('Police No') }}</th>
                    <td>{{ $vehicleLending->vehicle->police_no }}</td>
                </tr>
                <tr>
                    <th>{{ __('Vehicle Type') }}</th>
                    <td>{{ $vehicleLending->vehicle->type }}</td>
                </tr>
                <tr>
                    <th>{{ __('Branch') }}</th>
                    <td>{{ $vehicleLending->vehicle->branch?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($vehicleLending->date) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Purpose') }}</th>
                    <td>
                        {{ Form::textarea('purpose', $vehicleLending->purpose, ['class' => 'form-control', 'disabled'=>'disabled','placeholder' => '-', 'rows' => '5']) }}
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($vehicleLending->status == 'Pending')
                            <div class="badge bg-warning p-2 px-3 rounded">{{ __('Pending Approval') }}</div>
                        @elseif($vehicleLending->status == 'Approved')
                            <div class="badge bg-success p-2 px-3 rounded">{{ __($vehicleLending->status) }}</div>
                        @elseif($vehicleLending->status == "Reject")
                            <div class="badge bg-danger p-2 px-3 rounded">{{ __($vehicleLending->status) }}</div>
                        @endif
                    </td>
                </tr>
                <input type="hidden" value="{{ $vehicleLending->id }}" name="lending_id">
            </table>
        </div>
    </div>
</div>

@if (Auth::user()->type != 'employee' || Auth::user()->vehicleOfficer)
<div class="modal-footer">
    <button type="button" class="btn btn-success rounded bs-pass-para status" data-status="Approved" {{ $vehicleLending->status == 'Approved' ? 'disabled' : ''}}>{{ __('Approved') }}</button>
    <button type="button" class="btn btn-danger rounded bs-pass-para status" data-status="Reject" {{ $vehicleLending->status == 'Approved' ? 'disabled' : ''}}>{{ __('Reject') }}</button>
    <input type="hidden" name="status" id="hiddenStatus">
</div>
@endif

{{ Form::close() }}
