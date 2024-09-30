<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th>{{ __('Vehicle') }}</th>
                    <td>{{ $vehicleMaintenance?->vehicle?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Police No') }}</th>
                    <td>{{ $vehicleMaintenance?->vehicle?->police_no ?? '-'}}</td>
                </tr>
                <tr>
                    <th>{{ __('Maintenance Name') }}</th>
                    <td>{{ $vehicleMaintenance->name }}</td>
                </tr>
                <tr>
                    <th>{{ __('Maintenance Type') }}</th>
                    <td>{{ $vehicleMaintenance->maintenanceType?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Date') }}</th>
                    @if ($vehicleMaintenance->end_date == $vehicleMaintenance->start_date || !$vehicleMaintenance->end_date)
                        <td>{{ $vehicleMaintenance?->start_date ?? '-' }}</td>
                    @else
                        <td>{{ $vehicleMaintenance?->start_date ?? '-' }}  >>  {{ $vehicleMaintenance->end_date ?? '-' }}</td>
                    @endif
                </tr>
                <tr>
                    <th>{{ __('Next Maintenance Date')}}</th>
                    <td>{{ $vehicleMaintenance?->next_date ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Location')}}</th>
                    <td>
                        @if ($vehicleMaintenance?->workshop)
                            {{ $vehicleMaintenance?->workshop?->name ?: '-'}}
                            <br>
                            {{ $vehicleMaintenance?->workshop?->address ?: '-'}}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Cost')}}</th>
                    <td>
                        @if ($vehicleMaintenance->cost)
                            {{ \Auth::user()->priceFormat($vehicleMaintenance->cost) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Receipt File')}}</th>
                    <td>
                        @if ($vehicleMaintenance->file)
                            @php
                                $return_temp_file      = explode('/', $vehicleMaintenance->file);
                                $return_filename       = array_pop($return_temp_file);
                            @endphp
                            <a href="{{ asset($vehicleMaintenance->file) }}" target="blank" class="btn btn-md btn-success align-items-center text-start mt-2"
                                data-bs-toggle="tooltip"
                                data-bs-original-title="{{ __('View') }}">
                                <i class="fas fa-file"></i> {{ $return_filename }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <td>
                        {{ Form::textarea('description', $vehicleMaintenance->description, ['class' => 'form-control', 'disabled'=>'disabled','placeholder' => '-', 'rows' => '7']) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
