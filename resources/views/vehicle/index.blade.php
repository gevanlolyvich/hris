@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Vehicle') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vehicle') }}</li>
@endsection

@section('action-button')
    <a href="#" data-url="{{ route('vehicle.create') }}" data-ajax-popup="true" data-size="xl"
        data-title="{{ __('Create Vehicle') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
        data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
@endsection

@section('content')
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Police No') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('KM') }}</th>
                                <th>{{ __('Emoney Balance') }}</th>
                                <th>{{ __('Maintenance') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vehicles as $vehicle)
                                <tr>
                                    <td>{{ $vehicle->name }}</td>
                                    <td>
                                        @if ($vehicle->status == 'active')
                                            <div class="badge bg-success p-2 px-3 rounded">{{ __('Active') }}</div>
                                        @elseif ($vehicle->status == 'inactive')
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ __('Inactive') }}</div>
                                        @else
                                            <div class="badge bg-warning p-2 px-3 rounded">{{ __($vehicle->status) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $vehicle?->type?->name ?: '-' }}</td>
                                    <td>{{ $vehicle->police_no }}</td>
                                    <td>{{ $vehicle->branch?->name ?? '-' }}</td>
                                    <td>{{ $vehicle->km }}</td>
                                    <td>{{ \Auth::user()->priceFormat($vehicle->emoney_balance) }}</td>
                                    <td>
                                        <span>
                                            <button class="mx-3 btn btn-primary btn-sm align-items-center"
                                                data-bs-toggle="tooltip" 
                                                data-title="{{ __('Report Detail') }}"
                                                data-url="{{ route('vehicle.getMaintenanceHistory', $vehicle->id) }}"
                                                data-bs-original-title="{{ __('View') }}"
                                                onclick="window.location.href='{{ route('vehicle.getMaintenanceHistory', $vehicle->id) }}'">
                                                <i class="ti ti-tool text-white"></i>
                                            </button>
                                        </span>
                                    </td>
                                    <td class="action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" 
                                                    data-url="{{  route('vehicle.edit', $vehicle->id) }}"
                                                    data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Update Vehicle') }}"
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['vehicle.destroy', $vehicle->id], 'id' => 'delete-form-' . $vehicle->id]) !!}
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Delete')}}"
                                                    aria-label="{{__('Delete')}}"><i
                                                        class="ti ti-trash text-white text-white"></i></a>
                                                </form>
                                            </div>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                multiple: true,
            }); 
        });
    </script>
    <script>
        $(document).ready(function() {            
            $(document).on('change', 'select[name=is_resricted]', function () {
                let resricted_choice = $(this).val();
                let branch_div = document.getElementById('branch_div');

                if (resricted_choice == 1) {
                    document.getElementById("branch_div").style.display = '';
                } else if (resricted_choice == 0) {
                    document.getElementById("branch_div").style.display = 'none';
                }
            });

            $(document).on('change', '#type', function () {
                let type_choice = $(this).val();
                let newTypeDiv = document.getElementById("new_type_form");
                let newType = document.getElementById("new_type");

                if (type_choice == 0) {
                    newTypeDiv.style.display = '';
                    newType.required = true;
                } else {
                    document.getElementById("new_type_form").style.display = 'none';
                    newType.required = false;
                }
            });

            $('#commonModal').on('shown.bs.modal', function () {
                let resricted_choice = document.getElementById('is_resricted')?.value;

                if (resricted_choice == 1) {
                    document.getElementById("branch_div").style.display = '';
                } else if (resricted_choice == 0) {
                    document.getElementById("branch_div").style.display = 'none';
                }
            })
        });
    </script>
@endpush
