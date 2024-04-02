@extends('layouts.admin')
@section('page-title')
    {{ __('Employee Report List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Report') }}</li>
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
    <a href="{{ route('employee-report.create') }}" data-ajax-popup="true"
        data-title="{{ __('Create New Report') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
        data-bs-original-title="{{ __('Create New Report') }}">
        <i class="ti ti-plus"></i>
    </a>
@endsection

@section('content')
<div class="col-sm-12">
    <div class=" mt-2 " id="multiCollapseExample1">
        <div class="card">
            <div class="card-body">
                {{ Form::open(array('route' => array('employee-report.index'),'method'=>'get','id'=>'employeeattendancehistory_filter')) }}
                <div class="row align-items-center justify-content-end">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('branch', __('Branch'),['class'=>'form-label'])}}
                                    {{ Form::select('branch', $branch,isset($_GET['branch'])?$_GET['branch']:'', ['class' => 'form-control select2 branch', 'placeholder' => __('Select Branch')]) }}
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('department', __('Department'),['class'=>'form-label'])}}
                                    <div class="department_div btn-box">
                                        {{ Form::select('department', !empty($department) ? $department : [], isset($_GET['department'])?$_GET['department']:null, ['class' => 'form-control select2 department_id', 'placeholder' => __('Select Department')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('type', __('Type'),['class'=>'form-label'])}}
                                    <div class="type_div btn-box">
                                        {{ Form::select('type', !empty($type) ? $type : [], isset($_GET['type'])?$_GET['type']:null, ['class' => 'form-control select2 type_id', 'placeholder' => __('Select Report Type')]) }}
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
                                <a href="{{route('employee-report.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
                            <th>{{ __('Designation') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Time Period') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            @php
                                $type = null;
                                switch ($report->type) {
                                    case 'daily':
                                        $type = 'Daily';
                                        break;
                                    case 'weekly':
                                        $type = 'Weekly';
                                        break;
                                    case 'monthly':
                                        $type = 'Monthly';
                                        break;
                                    case 'yearly':
                                        $type = 'Yearly';
                                        break;
                                    
                                    default:
                                        break;
                                }
                            @endphp
                            <tr>
                                <td>{{ $report->employee->name }}</td>
                                <td>{{ $report->employee->designation->name }}</td>
                                <td>{{ $report->employee->branch->name }}</td>
                                <td>{{ __("$type") }}</td>
                                <td>{{ $report->start_date }} - {{ $report->end_date }}</td>
                                <td>
                                    <span>
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('employee-report.show', $report->id) }}" class="mx-3 btn btn-sm  align-items-center" data-size="lg" 
                                                data-bs-toggle="tooltip" data-ajax-popup="true"
                                                title="" data-title="{{ __('Appraisal Detail') }}"
                                                data-bs-original-title="{{ __('View') }}">
                                                <i class="ti ti-eye text-white"></i>
                                            </a>
                                        </div>
                                        @if (\Auth::user()->id == $report->created_by)
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['employee-report.destroy', $report->id], 'id' => 'delete-form-' . $report->id]) !!}
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                    aria-label="Delete"><i
                                                        class="ti ti-trash text-white text-white"></i></a>
                                                </form>
                                            </div>
                                        @endif
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