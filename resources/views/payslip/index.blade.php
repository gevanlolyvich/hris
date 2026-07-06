@extends('layouts.admin')

@section('page-title')
    {{ __('Payslip') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('payslip') }}</li>
@endsection


@section('content')  
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="row justify-content-end">
                    <div class="col-3">
                        <div class="btn-box">
                            {{Form::label('branch',__('Branch'),['class'=>'form-label'])}}
                            {{Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : null, ['class'=>'month-btn form-control select2', 'placeholder' => __('Select Branch'), 'id' => 'branch-filter'])}}
                        </div>
                    </div>
                    <div class="col-3 month">
                        <div class="btn-box">
                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                            {{Form::month('month',isset($_GET['month'])?$_GET['month'] : date('Y-m', strtotime(date('Y-m') . ' -1 month')), ['class'=>'month-btn form-control month-btn', 'id'=>'month-filter'])}}
                        </div>
                    </div>
                    <div class="col-auto p-1 pt-1 mt-4">
                        {{ Form::open(['route' => ['payslip.index'], 'method' => 'GET', 'id' => 'payslip_filter']) }}
                            {{ Form::month('month', null, ['style' => 'display: none;', 'id'=>'filter_month'])}}
                            {{ Form::text('branch', null, ['style' => 'display: none;', 'id'=>'filter_branch'])}}
                            {{-- <input type="hidden" name="filter_month" id="filter_month"> --}}
                            <a href="#" class="btn  btn-primary"
                                onclick="document.getElementById('payslip_filter').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Search Payslip') }}"
                                data-original-title="{{ __('Search Payslip') }}">{{ __('Search') }}
                            </a>
                        {{ Form::close() }}
                    </div>
                    @if (\Auth::user()->type != 'employee')
                        <div class="col-auto p-1 pt-1 mt-4">
                            {{ Form::open(['route' => ['payslip.store'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                                {{ Form::month('month', null, ['style' => 'display: none;', 'id'=>'generate_month'])}}
                                {{ Form::text('branch', null, ['style' => 'display: none;', 'id'=>'generate_branch'])}}
                                {{-- <input type="hidden" name="generate_month" id="generate_month"> --}}
                                <a href="#" class="btn  btn-info"
                                    onclick="document.getElementById('payslip_form').submit(); return false;"
                                    data-bs-toggle="tooltip" title="{{ __('Generate Payslip') }}"
                                    data-original-title="{{ __('Generate Payslip') }}">{{ __('Generate') }}
                                </a>
                            {{ Form::close() }}
                        </div>
                    @endif
                    <div class="col-auto p-1 pt-1 mt-4">
                        {{ Form::open(['route' => ['payslip.bulkpayment', ['date'=>$month]], 'method' => 'POST', 'id' => 'payslip_bulkpay']) }}
                            {{ Form::text('branch', null, ['style' => 'display: none;', 'id'=>'bulk_branch'])}}
                            <button type="button" class="btn btn-success bs-pass-para"
                                data-bs-toggle="tooltip" title="{{ __('Bulk Payment Payslip') }}"
                                data-original-title="{{ __('Bulk Payment Payslip') }}">{{ __('Bulk Payment') }}
                            </button>
                        {{ Form::close() }}
                    </div>
                    <div class="col-auto p-1 pt-1 mt-4">
                        {{ Form::open(['route' => ['payslip.export'], 'method' => 'POST', 'id' => 'payslip_export']) }}
                            {{ Form::month('month', null, ['style' => 'display: none;', 'id'=>'export_month'])}}
                            {{ Form::text('branch', null, ['style' => 'display: none;', 'id'=>'export_branch'])}}
                            {{-- <input type="hidden" name="export_month" id="export_month"> --}}
                            <a href="#" class="btn btn-warning"
                                onclick="document.getElementById('payslip_export').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Export Payslip') }}"
                                data-original-title="{{ __('Export Payslip') }}">{{ __('Export') }}
                            </a>
                        {{ Form::close() }}
                    </div>
                </div>
                </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                {{--                <form> --}}
                {{-- <div class="d-flex justify-content-between w-100"> --}}
                <h5>{{ __('Employee Payslip') }}</h5>
                @if (\Auth::user()->type != 'employee')
                    {{ Form::open(['route' => ['payslip.delete-period'], 'method' => 'DELETE', 'id' => 'payslip_delete_period']) }}
                        {{ Form::month('month', null, ['style' => 'display: none;', 'id'=>'delete_month'])}}
                        {{ Form::text('branch', null, ['style' => 'display: none;', 'id'=>'delete_branch'])}}
                        <button type="button" class="btn btn-danger btn-sm bs-pass-para">{{ __('Delete Period') }}</button>
                    {{ Form::close() }}
                @endif
                {{-- <div class="row align-items-center justify-content-end mt-4">
                    <div class="col-4 month">
                        <div class="btn-box">
                            <select class="form-control month_date " name="year" tabindex="-1" aria-hidden="true">
                                <option value="--">--</option>
                                @foreach ($month as $k => $mon)
                                    @php
                                        $selected = date('m') - 1 == $k ? 'selected' : '';
                                    @endphp
                                    <option value="{{ $k }}" {{ $selected }}>{{ $mon }}</option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                    <div class="col-4 year">
                        <div class="btn-box">
                            {{ Form::select('year', $year, null, ['class' => 'form-control year_date ']) }}
                        </div>
                    </div>

                    <div class="col-auto float-end">
                        {{ Form::open(['route' => ['payslip.export'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                            <input type="hidden" name="filter_month" class="filter_month">
                            <input type="hidden" name="filter_year" class="filter_year">
                            <input type="submit" value="{{ __('Export') }}" class="btn btn-primary">
                        {{ Form::close() }}
                    </div>
                </div> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Payroll Type') }}</th>
                                <th>{{ __('Salary') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payslips as $payslip)
                                <tr>
                                    <td>{{ $payslip?->employees?->name ?? '-' }}</td>
                                    <td>{{ $payslip?->employees?->salaryType?->name ?? '-' }}</td>
                                    <td>{{ \Auth::user()->priceFormat($payslip?->basic_salary ?? '0') }}</td>
                                    <td>{{ \Auth::user()->priceFormat($payslip?->net_payble ?? '0') }}</td>
                                    <td>
                                        @if ($payslip?->status)
                                            <div class="badge bg-success p-2 px-3 rounded text-white">{{__('Paid')}}</div>
                                        @else
                                            <div class="badge bg-danger p-2 px-3 rounded text-white">{{__('UnPaid')}}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="#" data-url="{{ route('payslip.pdf', ['id' => $payslip->employee_id, 'm' => $month]) }}" data-size="lg"  data-ajax-popup="true" class="btn btn-sm m-1 btn-warning" data-title="{{ __('Employee Payslip') }}">{{ __('Payslip') }}</a>
                                    
                                            @if (\Auth::user()->type != 'employee')
                                                @if ($payslip->status == 0)
                                                    {!! Form::open(['method' => 'GET', 'route' => ['payslip.paysalary', ['id' => $payslip->employee_id, 'date' => $month]]]) !!}
                                                    <button type="button" class="btn-sm btn m-1 btn-primary bs-pass-para">{{ __('Click To Paid') }}</button>
                                                    </form>
                                                    
                                                    {!! Form::open(['method' => 'GET', 'route' => ['payslip.delete', $payslip->id]]) !!}
                                                    <button type="button" class="btn btn-danger m-1 btn-sm bs-pass-para">{{ __('Delete') }}</button>
                                                    </form>
                                                @endif
                                    
                                            @endif
                                        </div>
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
            callback();
            branchChange();

            function callback() {
                var month = $("#month-filter").val();

                let filterMonth = document.getElementById('filter_month');
                let exportMonth = document.getElementById('export_month');
                let generateMonth = document.getElementById('generate_month');
                let bulkpayMonth = document.getElementById('bulkpay_month');
                let deleteMonth = document.getElementById('delete_month');

                if (filterMonth) {
                    filterMonth.value = month;
                    filterMonth.val = month;
                }
                if (exportMonth) {
                    exportMonth.value = month;
                    exportMonth.val = month;
                }
                if (generateMonth) {
                    generateMonth.value = month;
                    generateMonth.val = month;
                }
                if (bulkpayMonth) {
                    bulkpayMonth.value = month;
                    bulkpayMonth.val = month;
                }
                if (deleteMonth) {
                    deleteMonth.value = month;
                    deleteMonth.val = month;
                }
            }

            function branchChange() {
                var branch = $("#branch-filter").val();

                let filterbranch = document.getElementById('filter_branch');
                let exportbranch = document.getElementById('export_branch');
                let generatebranch = document.getElementById('generate_branch');
                let bulkpaybranch = document.getElementById('bulk_branch');
                let deletebranch = document.getElementById('delete_branch');

                if (filterbranch) {
                    filterbranch.value = branch;
                    filterbranch.val = branch;
                }
                if (exportbranch) {
                    exportbranch.value = branch;
                    exportbranch.val = branch;
                }
                if (generatebranch) {
                    generatebranch.value = branch;
                    generatebranch.val = branch;
                }
                if (bulkpaybranch) {
                    bulkpaybranch.value = branch;
                    bulkpaybranch.val = branch;
                }
                if (deletebranch) {
                    deletebranch.value = branch;
                    deletebranch.val = branch;
                }
            }

            $(document).on("change", "#month-filter", callback);
            $(document).on("change", "#branch-filter", branchChange);

            //bulkpayment Click
            $(document).on("click", "#bulk_payment", function() {
                var month = $("#month-filter").val();
                var datePicker = month?.replace('-', '_');


            });
            $(document).on('click', '#bulk_payment',
                'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]',
                function() {
                    var month = $("#month-filter").val();
                    var datePicker = month?.replace('-', '_');

                    var title = 'Bulk Payment';
                    var size = 'md';
                    var url = 'payslip/bulk_pay_create/' + datePicker;

                    // return false;

                    $("#commonModal .modal-title").html(title);
                    $("#commonModal .modal-dialog").addClass('modal-' + size);
                    $.ajax({
                        url: url,
                        success: function(data) {

                            // alert(data);
                            // return false;
                            if (data.length) {
                                $('#commonModal .body').html(data);
                                $("#commonModal").modal('show');
                                // common_bind();
                            } else {
                                show_toastr('error', 'Permission denied.');
                                $("#commonModal").modal('hide');
                            }
                        },
                        error: function(data) {
                            data = data.responseJSON;
                            show_toastr('error', data.error);
                        }
                    });
                });

            $(document).on("click", ".payslip_delete", function() {
                var confirmation = confirm("are you sure you want to delete this payslip?");
                var url = $(this).data('url');


                if (confirmation) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "JSON",
                        success: function(data) {
                            // show_toastr(data.status, data.msg, 'data.status');
                            show_toastr('success', 'Payslip Deleted Successfully', 'success');


                            setTimeout(function() {
                                location.reload();
                            }, 800)
                        },
                    });

                }
            });
        });
    </script>
@endpush
