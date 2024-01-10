@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Transfer') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Transfer') }}</li>
@endsection


@section('action-button')
    @can('Create Transfer')
        <a href="#" data-url="{{ route('transfer.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Transfer') }}" data-size="lg" data-bs-toggle="tooltip" title=""
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
                                @role('company')
                                    <th>{{ __('Employee Name') }}</th>
                                @endrole
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Transfer Date') }}</th>
                                <th>{{ __('Document') }}</th>
                                @if (Gate::check('Edit Transfer') || Gate::check('Delete Transfer'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="">

                            @foreach ($transfers as $transfer)
                                <tr>
                                    @role('company')
                                        <td>{{ !empty($transfer->employee()) ? $transfer->employee()->name : '' }}</td>
                                    @endrole
                                    <td>{{ !empty($transfer->branch()) ? $transfer->branch()->name : '' }}</td>
                                    <td>{{ $transfer->department->name }}</td>
                                    <td>{{ \Auth::user()->dateFormat($transfer->transfer_date) }}</td>
                                    <td>
                                        @if ($transfer->document_path)
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ asset($transfer->document_path )}}" target="blank" class="mx-3 btn btn-sm  align-items-center"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-file text-white"></i>
                                                </a>
                                            </div>
                                        @else
                                        -
                                        @endif 
                                    </td>
                                    <td class="Action">
                                        @if (Gate::check('Edit Transfer') || Gate::check('Delete Transfer'))
                                            <span>
                                                @can('Edit Transfer')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                            data-url="{{ URL::to('transfer/' . $transfer->id . '/edit') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Transfer') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Transfer')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['transfer.destroy', $transfer->id], 'id' => 'delete-form-' . $transfer->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                            aria-label="Delete"><i
                                                                class="ti ti-trash text-white text-white"></i></a>
                                                        </form>
                                                    </div>
                                                @endcan
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

@push('script-page')
    <script>
        $('input[type="file"]').change(function(e) {
            var file = e.target.files[0].name;
            var file_name=$(this).attr('data-filename');
            $('.'+file_name).append(file);
        });
    </script>
    <script>
        var employee_id = null;

        // $('#commonModal').on('show.bs.modal', function () {
        //     employee_id= $('.branch_id').
        //     console.log({employee_id})
            
        // });
        $(document).ready(function() {
            var d_id = $('.department_id').val();
            var branch_id = $('.branch_id').val();
            var employee_id = $('.employee_id').val();
            // console.log(d_id);
            getEmployeeBranch(branch_id);
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=employee_id]', function() {
            var employee_id = $(this).val();
            console.log({employee_id});
            getEmployeeBranch(employee_id);
            // getDesignation(department_id);
        });
        $(document).on('change', 'select[name=department_id]', function() {
            department_id = $(this).val();
            // console.log({department_id})
            getDesignation(department_id);
        });
        
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();
            // console.log({branch_id})
            // $('.designation_id').empty();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id) {
            console.log({loc:'departement'})
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

        function getEmployeeBranch(employee_id) {
            // console.log({employee_id})
            console.log({loc:'empbranch'})
            $.ajax({
                url: '{{ route('direct.employee.json') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    // console.log(data);
                    $('.managed_by').empty();
                    var emp_selct = ` <select class="form-control select2  managed_by" name="managed_by" id="choices-multiple2"
                                            placeholder={{ __('Select Direct Supervisor') }} >
                                            </select>`;
                    $('.managed_by_div').html(emp_selct);

                    $('.managed_by').append('<option value="" disabled selected>{{ __('Select Direct Supervisor') }}</option>');
                    $.each(data, function(key, value) {
                        $('.managed_by').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple2', {
                        removeItemButton: true,
                    });


                }
            });
        }

        function getDesignation(did) {
            console.log({loc:'designation'})

            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('.designation_id').empty();
                    var emp_selct = ` <select class="form-control  designation_id" name="designation_id" id="choices-multiple3"
                                            placeholder="Select Designation" >
                                            </select>`;
                    $('.designation_div').html(emp_selct);

                    $('.designation_id').append('<option value="" disabled selected>{{ __('Select Designation') }}</option>');
                    $.each(data, function(key, value) {
                        $('.designation_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple3', {
                        removeItemButton: true,
                    });
                }
            });
        }
    </script>
@endpush
