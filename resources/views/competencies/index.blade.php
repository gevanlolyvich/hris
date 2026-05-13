@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Competencies') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Competencies') }}</li>
@endsection

@section('action-button')
    @can('Create Competencies')
        <a href="#" data-url="{{ route('competencies.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Competencies') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-size="lg" data-bs-original-title="{{ __('Create') }}">
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
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Performance Type') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($competencies as $competency)
                                <tr>
                                    {{-- <td>{{ $competency->id }}</td> --}}
                                    <td>{{ $competency->name }}</td>
                                    <td>{{ $competency->performance_type?->name ?? '-' }}</td>
                                    <td>{{ __($competency->type) }}</td>
                                    <td>{{ $competency->description }}</td>
                                    <td class="Action">
                                        <span>
                                            @can('Edit Competencies')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                        data-url="{{ URL::to('competencies/' . $competency->id . '/edit') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Competencies') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete Competencies')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['competencies.destroy', $competency->id], 'id' => 'delete-form-' . $competency->id]) !!}
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
