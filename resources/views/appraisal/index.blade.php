@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Appraisal') }}
@endsection

@push('script-page')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Appraisal') }}</li>
@endsection

@section('action-button')
    @can('Create Appraisal')
        <a href="{{ route('appraisal.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Appraisal') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create New Appraisal') }}">
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
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Designation  ') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Overall Rating') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Time Period') }}</th>
                                <th>{{ __('Appraisal Date') }}</th>
                                @if (Gate::check('Edit Appraisal') || Gate::check('Delete Appraisal') || Gate::check('Show Appraisal'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appraisals as $appraisal)
                                <tr>
                                    <td>{{ $appraisal->employee->name }}</td>
                                    <td>{{ $appraisal->employee->designation->name }}</td>
                                    <td>{{ $appraisal->created_by_user->name }}</td>
                                    <td>{{ $appraisal->total_apprisal }}</td>
                                    <td>{{ __($appraisal->category) }}</td>
                                    <td>{{ $appraisal->start_month }} - {{ $appraisal->end_month }}</td>
                                    <td>{{ $appraisal->created_at }}</td>
                                    <td class="Action">
                                        @if (Gate::check('Edit Appraisal') || Gate::check('Delete Appraisal') || Gate::check('Show Appraisal'))
                                            <span>
                                                @can('Show Appraisal')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="{{ route('appraisal.show', $appraisal->id) }}" class="mx-3 btn btn-sm  align-items-center" data-size="lg" 
                                                            data-bs-toggle="tooltip" data-ajax-popup="true"
                                                            title="" data-title="{{ __('Appraisal Detail') }}"
                                                            data-bs-original-title="{{ __('View') }}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @if (in_array($appraisal->employee_id, \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray() ?? []) 
                                                    || \Auth::user()->type == 'company' 
                                                    || $appraisal->employee->branch_id == \Auth::user()->branch_id 
                                                    || (\Auth::user()->type == 'hr' && !\Auth::user()->branch_id)
                                                    || $appraisal->created_by == \Auth::user()->id)
                                                    @can('Edit Appraisal')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('appraisal.edit', $appraisal->id) }}" class="mx-3 btn btn-sm align-items-center" 
                                                                data-size="lg" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Appraisal') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('Delete Appraisal')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['appraisal.destroy', $appraisal->id], 'id' => 'delete-form-' . $appraisal->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                                aria-label="Delete"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            </form>
                                                        </div>
                                                    @endcan
                                                @endif
                                            </span>
                                        @endif
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
