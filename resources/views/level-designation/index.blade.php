@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Level Designation') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Level Designation') }}</li>
@endsection

@section('action-button')
    @can('Create Level')
        <a href="#" data-url="{{ route('level-designation.create') }}" data-ajax-popup="true" data-size="xl"
            data-title="{{ __('Create New Level Designation') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
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
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Goal Weight') }}</th>
                                <th>{{ __('Competency Weight') }}</th>
                                <th>{{ __('Detail') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($levels as $level)
                                <tr>
                                    <td>{{ $level?->name ?? '-' }}</td>
                                    <td>{{ $level?->goal_weight ?? 0 }}</td>
                                    <td>{{ $level?->competency_weight ?? 0 }}</td>
                                    <td>
                                        <span>
                                            @can('Manage Level')
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="xl"
                                                        data-url="{{ route('level-designation.show', $level->id) }}"
                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Level Detail') }}"
                                                        data-bs-original-title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                        </span>
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @can('Edit Level')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                        data-url="{{  route('level-designation.edit', $level->id) }}"
                                                        data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Level') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete Level')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['level-designation.destroy', $level->id], 'id' => 'delete-form-' . $level->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Delete')}}"
                                                        aria-label="Delete" data-title="{{ __('Delete Level') }}"><i
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

@push('script-page')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                multiple: true,
            });
        });
    </script>
@endpush
