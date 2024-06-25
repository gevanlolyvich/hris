<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Name') }}</th>
                    <td>{{ !empty($vehicleOfficer->user) ? $vehicleOfficer->user->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Access') }}</th>
                    <td>{{ $vehicleOfficer->is_resricted ? __('Resricted Access') : __('Full Access') }}</td>
                </tr>
                @if ($vehicleOfficer->is_resricted)
                    <tr>
                        <th>{{ __('Branch') }}</th>
                        <td>
                            @foreach ($vehicleOfficer->accesses as $access)
                                <span class="btn btn-sm btn-primary mb-2">- {{$access?->branch?->name}}</span>
                                <br>
                            @endforeach
                        </td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
</div>