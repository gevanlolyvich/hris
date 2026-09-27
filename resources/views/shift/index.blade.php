@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Shift') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Shift') }}</li>
@endsection

@section('action-button')
    @can('Create Shift')
        <a href="#" data-url="{{ route('shift.create') }}" data-ajax-popup="true"
            data-title={{ __('Create New Shift') }} data-size="lg" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{-- <h5> </h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Shift Name') }}</th>
                                <th>{{ __('Tipe') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Work Days') }}</th>
                                <th>{{ __('Start Time') }}</th>
                                <th>{{ __('End Time') }}</th>
                                {{-- <th>{{ __('Description') }}</th> --}}
                                @if (Gate::check('Edit Warning') || Gate::check('Delete Warning'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->name }}
                                    <td>
                                        @if (!empty($shift->is_shift))
                                            <span class="badge bg-warning text-dark">{{ __('Shift-shiftan') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Normal') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $shift->branch?->name  }}
                                    </td>
                                    <td>
                                        @foreach ($shift->shiftTimes as $shiftTimes)
                                            @if ($shiftTimes->is_working== true)
                                                {{ __($shiftTimes->days) }} <br>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($shift->shiftTimes as $shiftTimes)
                                            @if ($shiftTimes->is_working == true)
                                                {{ $shiftTimes->start_time }} <br>
                                            @endif
                                        @endforeach    
                                    </td>
                                    <td>
                                        @foreach ($shift->shiftTimes as $shiftTimes)
                                            @if ($shiftTimes->is_working == true)
                                                {{ $shiftTimes->end_time }} <br>
                                            @endif
                                        @endforeach    
                                    </td>
                                    <td class="Action">
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                data-url="{{ URL::to('shift/' . $shift->id . '/edit') }}"
                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                title="" data-title="{{ __('Edit Shift') }}"
                                                data-bs-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['shift.destroy', $shift->id], 'id' => 'delete-form-' . $shift->id]) !!}
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                aria-label="Delete"><i
                                                    class="ti ti-trash text-white text-white"></i></a>
                                            </form>
                                        </div>
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
