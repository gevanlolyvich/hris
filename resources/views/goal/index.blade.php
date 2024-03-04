@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Goal') }}
@endsection

@section('action-button')
    @can('Create Goal')
        <a href="#" data-url="{{ route('goal.create') }}" data-ajax-popup="true" data-size="xl"
            data-title="{{ __('Create New Goal') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Goal') }}</li>
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
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Name') }}</th>
                                {{-- <th>{{ __('Target') }}</th> --}}
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th width="20%">{{ __('Progress Percentage') }}</th>
                                @if (Gate::check('Edit Goal') || Gate::check('Delete Goal'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($goals as $goal)
                                <tr>
                                    <td>{{ $goal?->branch?->name ?? '-' }}</td>
                                    <td>{{ $goal?->department?->name ?? '-' }}</td>
                                    <td>{{ $goal?->employee?->name ?? '-' }}</td>
                                    <td>{{ $goal->name }}</td>
                                    {{-- <td>{{ $goal->target }}</td> --}}
                                    <td>{{ \Auth::user()->dateFormat($goal->start_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($goal->end_date) }}</td>
                                    <td>
                                        <div class="progress-wrapper">
                                            <span class="progress-percentage"><small
                                                    class="font-weight-bold"></small>{{ $goal->progress }}%</span>
                                            <div class="progress progress-xs mt-2 w-100">
                                                <div class="progress-bar bg-{{ Utility::getProgressColor($goal->progress) }}"
                                                    role="progressbar" aria-valuenow="{{ $goal->progress }}"
                                                    aria-valuemin="0" aria-valuemax="100"
                                                    style="width: {{ $goal->progress }}%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @can('Manage Goal')
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  goal-items-center" data-size="xl"
                                                        data-url="{{ route('goal.show', $goal->id) }}"
                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Goal Detail') }}"
                                                        data-bs-original-title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @if (($goal->employee_id == \Auth::user()->employee?->id || \Auth::user()->type == 'company') || !$goal->employee_id)
                                                @can('Progress Goal')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  goal-items-center" data-size="xl"
                                                        data-url="{{ URL::to('goal/' . $goal->id . '/progress') }}"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Goal Progress') }}"
                                                            data-bs-original-title="{{ __('Progress') }}">
                                                            <i class="ti ti-percentage text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                            @endif
                                            @if ((Gate::check('Edit Goal') || Gate::check('Delete Goal')) && (($goal->employee_id == \Auth::user()->employee?->id || \Auth::user()->type == 'company') || !$goal->employee_id))
                                                @can('Edit Goal')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="xl"
                                                            data-url="{{ route('goal.edit', $goal->id) }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Goal') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Goal')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['goal.destroy', $goal->id], 'id' => 'delete-form-' . $goal->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Delete')}}"
                                                            aria-label="{{ __('Delete')}}"><i
                                                                class="ti ti-trash text-white text-white"></i></a>
                                                        </form>
                                                    </div>
                                                @endcan
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

@push('script-page')
    <script>
        $(document).ready(function () {
            function getDepartment(branch_id) {
                $.ajax({
                    url: '{{ route('department.employee.json') }}',
                    type: 'POST',
                    data: {
                        "branch_id": branch_id,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        // dept
                        $('.department_id').empty();
                        var dept_select = ` <select class="form-control select2  department_id" name="department_id" id="department_id"
                                                placeholder="Select Department" >
                                                </select>`;
                        $('.department_div').html(dept_select);

                        $('.department_id').append('<option value="" disabled selected>{{ __('Select Department') }}</option>');
                        $.each(data, function(key, value) {
                            $('.department_id').append('<option value="' + key + '">' + value +
                                '</option>');
                        });
                        new Choices('#department_id', {
                            removeItemButton: true,
                        });

                        // employee
                        $('.employee_id').empty();
                        var emp_selct = ` <select class="form-control select2  employee_id" name="employee_id" id="employee_id"
                                                placeholder="Select Employee" >
                                                </select>`;
                        $('.employee_div').html(emp_selct);

                        $('.employee_id').append('<option value="" disabled selected>{{ __('Select Employee') }}</option>');
                        new Choices('#employee_id', {
                            removeItemButton: true,
                        });
                    }
                });
            }

            function getEmployee(department_id) {
                $.ajax({
                    url: '{{ route('employee.department.json') }}',
                    type: 'POST',
                    data: {
                        "department_id": department_id,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        $('.employee_id').empty();
                        var emp_selct = ` <select class="form-control select2  employee_id" name="employee_id" id="employee_id"
                                                placeholder="Select Employee" >
                                                </select>`;
                        $('.employee_div').html(emp_selct);

                        $('.employee_id').append('<option value="" disabled selected>{{ __('Select Employee') }}</option>');
                        $.each(data, function(key, value) {
                            $('.employee_id').append('<option value="' + key + '">' + value +
                                '</option>');
                        });
                        new Choices('#employee_id', {
                            removeItemButton: true,
                        });
                    }
                });
            }

            $('body').on('change', 'select[name=branch_id]', function() {
                let branch_id = $(this).val();

                if (branch_id) {
                    getDepartment(branch_id);
                    $('.employee_id').empty();
                }
            });
            $('body').on('change', 'select[name=department_id]', function() {
                let department_id = $(this).val();

                if (department_id) {
                    getEmployee(department_id);
                }
            });
        });
    </script>
@endpush
