
@extends('layouts.admin')

@section('page-title')
   {{ __('Manage Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('employee.export') }}" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-original-title="{{ __('Export') }}" class="btn btn-sm btn-primary">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="#" data-url="{{ route('employee.file.import') }}" data-ajax-popup="true"
        data-title="{{ __('Import  employee CSV file') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
        data-bs-original-title="{{ __('Import') }}">
        <i class="ti ti-file"></i>
    </a>

    @can('Create Employee')
        @if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id)))
            <a href="{{ route('employee.create') }}" 
                data-title="{{ __('Create New Employee') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endif
    @endcan
        
@endsection

@section('content')
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(array('route' => array('employee.index'),'method'=>'get','id'=>'employeeattendancehistory_filter')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-12">
                            <div class="row">
                                <div class="form-group col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                    {{ Form::label('branch_id', __('Select Branch'), ['class' => 'form-label']) }}
                                    {{ Form::select('branch_id', $branch, isset($_GET['branch_id'])?$_GET['branch_id']:null, ['class' => 'form-control select2', 'placeholder' => __('Select Branch')]) }}
                                </div>
                                <div class="form-group col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                    {{ Form::label('department_id', __('Select Department'), ['class' => 'form-label']) }}
                                    <div class="department_div btn-box">
                                        {{ Form::select('department_id', !empty($department) ? $department : [], isset($_GET['department_id'])?$_GET['department_id']:null, ['class' => 'form-control select2 department_id', 'placeholder' => __('Select Department')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('employeeattendancehistory_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{route('employee.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{-- <h5></h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Designation') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Shift') }}</th>
                                @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td>
                                        @can('Show Employee')
                                            <a class="btn btn-outline-primary"
                                                href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ $employee->employee_id }}</a>
                                        @else
                                            <a href="#" class="btn btn-outline-primary">{{ $employee->employee_id }}</a>
                                        @endcan
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>
                                        @if ($employee->is_active)
                                            <button type="button" class="btn btn-sm btn-success disabled">{{ __('Active')}}</button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-danger disabled">{{ __('Inactive')}}</button>
                                        @endif
                                    </td>
                                    <td>{{ $employee->email }}</td>
                                    <td>
                                        {{ !empty(\Auth::user()->getBranch($employee->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : '-' }}
                                    </td>
                                    <td>
                                        {{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '-' }}
                                    </td>
                                    <td>
                                        {{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '-' }}
                                    </td>
                                    <td>{{ ucwords( $employee?->employeeType?->name ?? '-') }}</td>
                                    <td>{{ $employee->shift_type?->name ?? '' }}</td>
                                    @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                        <td class="Action">
                                            @if ($employee->is_active == 1)
                                                <span>
                                                    @can('Edit Employee')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                                class="mx-3 btn btn-sm  align-items-center"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('Delete Employee')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['employee.destroy', $employee->id], 'id' => 'delete-form-' . $employee->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            </form>
                                                        </div>
                                                    @endcan
                                                </span>
                                            @else
                                                <i class="ti ti-lock"></i>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row"> --}}
        {{-- <div class="col-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee ID') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Date Of Joining') }}</th>
                                    @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    <tr>
                                        <td>
                                            @can('Show Employee')
                                                <a
                                                    href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ $employee->employee_id }}</a>
                                            @else
                                                <a href="#">{{ $employee->employee_id }}</a>
                                            @endcan
                                        </td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $employee->email }}</td>
                                        <td>
                                            {{ !empty(\Auth::user()->getBranch($employee->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : '' }}
                                        </td>
                                        <td>
                                            {{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '' }}
                                        </td>
                                        <td>
                                            {{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '' }}
                                        </td>
                                        <td>
                                            {{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                                        @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                            <td class="d-flex">
                                                @if ($employee->is_active == 1)
                                                    @can('Edit Employee')
                                                        <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                            class="action-btn btn-primary me-1 btn btn-sm d-inline-flex align-items-center"
                                                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                            title="{{ __('Edit') }}"><i class="ti ti-pencil"></i></a>
                                                    @endcan
                                                    @can('Delete Employee')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['employee.destroy', $employee->id], 'id' => 'delete-form-' . $employee->id]) !!}
                                                        <a href="#!"
                                                            class="action-btn btn-danger me-1 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                            data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                            title="{{ __('Delete') }}">
                                                            <i class="ti ti-trash"></i></a>
                                                        {!! Form::close() !!}
                                                    @endcan
                                                @else
                                                    <i class="fas fa-lock"></i>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

@push('script-page')
<script>
    function getDepartment(branch_id) {
        $.ajax({
            url: '{{ route('department.employee.json') }}',
            type: 'POST',
            data: {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                $('.designation_id').empty();
                $('.department_id').empty();
                var emp_selct = ` <select class="form-control select2  department_id" name="department_id" id="choices-multiple"
                                        placeholder="Select Department" >
                                        </select>`;
                $('.department_div').html(emp_selct);

                $('.department_id').append('<option value="" disabled selected>{{ __('Select Department') }}</option>');
                $.each(data, function(key, value) {
                    $('.department_id').append('<option value="' + key + '">' + value +
                        '</option>');
                });
                new Choices('#choices-multiple', {
                    removeItemButton: true,
                });


            }
        });
    }

    $(document).on('change', 'select[name=branch_id]', function() {
        var branch_id = $(this).val();
        // console.log({branch_id})
        // $('.designation_id').empty();
        getDepartment(branch_id);
    });
</script>
@endpush