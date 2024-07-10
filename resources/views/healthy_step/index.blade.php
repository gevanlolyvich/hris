@extends('layouts.admin')

@section('page-title')
   {{ __('Manage Healthy Steps') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Healthy Steps') }}</li>
@endsection

@section('action-button')

    @can('Create Healthy Steps')
        <a href="#" data-url="{{ route('healthy-targets.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Healthy Steps') }}" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan

@endsection


@section('content')
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">

                    <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                {{-- <th width="10px">ID</th> --}}
                                <th>{{ __('Activity') }}</th>
                                <th>{{ __('Target/Day') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($healthy_targets as $target)
                                <tr>
                                    {{-- <td>{{ $target->id }}</td> --}}
                                    <td>{{ __($target->activity_name) }}</td>
                                    <td>{{ number_format($target->target, 0, ',', '.') }}</td>
                                    <td class="Action">
                                        <span>
                                            @can('Edit Healthy Steps')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                        data-url="{{ URL::to('healthy-targets/' . $target->id . '/edit') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Healthy Steps') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete Healthy Steps')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['healthy-targets.destroy', $target->id], 'id' => 'delete-form-' . $target->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    </form>
                                                </div>
                                            @endcan
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
