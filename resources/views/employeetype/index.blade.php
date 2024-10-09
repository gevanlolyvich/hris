@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Employee Type') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Type') }}</li>
@endsection

@section('action-button')
    <a href="#" data-url="{{ route('employeetype.create') }}" data-ajax-popup="true"
        data-title="{{ __('Create Employee Type') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
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
                                <th width="10px">ID</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Salary Type') }}</th>
                                <th>{{ __('Period Type') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employee_types as $emp_type)
                                <tr>
                                    <td>{{ $emp_type->id }}</td>
                                    <td>{{ $emp_type->name }}</td>
                                    <td>{{ __($emp_type->type) }}</td>
                                    <td>{{ __($emp_type->period_type) }}</td>
                                    <td class="Action">
                                        <span>
                                            @can('Edit Designation')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                        {{-- data-url="{{  route('employeetype.edit', [$emp_type->id]) }}" --}}
                                                        data-url="{{  URL::to('employeetype/'.$emp_type->id."/edit") }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Designation') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete Designation')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['employeetype.destroy', $emp_type->id], 'id' => 'delete-form-' . $emp_type->id]) !!}
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
