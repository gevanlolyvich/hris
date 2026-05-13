@extends('layouts.admin')
@section('page-title')
    {{ __('Employee History List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee History') }}</li>
@endsection

@push('css-page')
@endpush

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
                    $('.department').empty();
                    var emp_selct = ` <select class="form-control select2  department" name="department" id="choices-multiple"
                                            placeholder="Select Department" >
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department').append('<option value="" disabled selected>{{ __('Select Department') }}</option>');
                    $.each(data, function(key, value) {
                        $('.department').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });


                }
            });
        }

        $(document).on('change', 'select[name=branch]', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });
    </script>
@endpush

@section('action-button')
<!-- <a class="btn btn-sm btn-primary collapsed" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button"
        aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{ __('Filter') }}">
        <i class="ti ti-filter"></i>
    </a> -->
@endsection
@section('content')
<div class="col-sm-12">
    <div class=" mt-2 " id="multiCollapseExample1">
        <div class="card">
            <div class="card-body">
                {{ Form::open(array('route' => array('employeeattendancehistory.index'),'method'=>'get','id'=>'employeeattendancehistory_filter')) }}
                <div class="row align-items-center justify-content-end">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('branch', __('Branch'),['class'=>'form-label'])}}
                                    {{ Form::select('branch', $branch,isset($_GET['branch'])?$_GET['branch']:'', ['class' => 'form-control select2 branch', 'placeholder' => __('Select Branch')]) }}
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('department', __('Department'),['class'=>'form-label'])}}
                                    <div class="department_div btn-box">
                                        {{ Form::select('department', !empty($department) ? $department : [], isset($_GET['department'])?$_GET['department']:null, ['class' => 'form-control select2 department_id', 'placeholder' => __('Select Department')]) }}
                                    </div>
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
                                <a href="{{route('employeeattendancehistory.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
            <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('Department') }}</th>
                            <th>{{ __('Designation') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>
                                    <a class="btn" style="padding-left: 0px"
                                        href="{{ route('employeeattendancehistory.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ $employee->name }}
                                    </a>
                                </td>
                                <td>
                                    <a class="btn" style="padding-left: 0px"
                                        href="{{ route('employeeattendancehistory.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ $employee->email }}
                                    </a>
                                </td>
                                <td>
                                    <a class="btn" style="padding-left: 0px"
                                        href="{{ route('employeeattendancehistory.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ !empty(\Auth::user()->getBranch($employee->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : '-' }}
                                    </a>
                                </td>
                                <td>
                                    <a class="btn" style="padding-left: 0px"
                                        href="{{ route('employeeattendancehistory.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '-' }}
                                    </a>
                                </td>
                                <td>
                                    <a class="btn" style="padding-left: 0px"
                                        href="{{ route('employeeattendancehistory.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '-' }}
                                    </a>
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