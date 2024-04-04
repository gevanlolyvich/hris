@extends('layouts.admin')

@section('page-title')
    {{ __('PPh 21') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('PPh 21') }}</li>
@endsection


@section('content')  
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="row justify-content-end">
                    @if (\Auth::user()->type != 'employee')
                        <div class="col-4">
                            <div class="btn-box">
                                {{Form::label('branch',__('Branch'),['class'=>'form-label'])}}
                                {{Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : null, ['class'=>'month-btn form-control select2', 'placeholder' => __('Select Branch'), 'id' => 'branch-filter'])}}
                            </div>
                        </div>
                    @endif
                    <div class="col-3 month">
                        <div class="btn-box">
                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                            {{Form::month('month',isset($_GET['month'])?$_GET['month'] : date('Y-m', strtotime(date('Y-m') . ' -1 month')), ['class'=>'month-btn form-control month-btn', 'id'=>'month-filter'])}}
                        </div>
                    </div>
                    <div class="col-auto p-1 pt-1 mt-4">
                        {{ Form::open(['route' => ['pph21.index'], 'method' => 'GET', 'id' => 'payslip_filter']) }}
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
                            {{ Form::open(['route' => ['pph21.store'], 'method' => 'POST', 'id' => 'payslip_form']) }}
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
                    {{-- <div class="col-auto p-1 pt-1 mt-4">
                        {{ Form::open(['route' => ['pph21.bulkpayment', ['date'=>$month]], 'method' => 'POST', 'id' => 'payslip_bulkpay']) }}
                            {{ Form::text('branch', null, ['style' => 'display: none;', 'id'=>'bulk_branch'])}}
                            <button type="button" class="btn btn-success bs-pass-para"
                                data-bs-toggle="tooltip" title="{{ __('Bulk Payment Payslip') }}"
                                data-original-title="{{ __('Bulk Payment Payslip') }}">{{ __('Bulk Payment') }}
                            </button>
                        {{ Form::close() }}
                    </div> --}}
                    <div class="col-auto p-1 pt-1 mt-4">
                        {{ Form::open(['route' => ['pph21.export'], 'method' => 'POST', 'id' => 'payslip_export']) }}
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
            <div class="card-header">
                <h5>{{ __('Employee Payslip') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('PTKP') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Bruto') }}</th>
                                <th>{{ __('PPh 21') }}</th>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pph21 as $pph)
                                <tr>
                                    <td>{{ $pph?->employee?->name ?? '-' }}</td>
                                    <td>{{ $pph?->employee?->branch?->name ?? '-' }}</td>
                                    <td>{{ $pph?->ptkp }}</td>
                                    <td>{{ $pph?->date }}</td>
                                    <td>{{ number_format($pph?->bruto ?? 0, 2) }}</td>
                                    <td>{{ number_format($pph?->pph21 ?? 0, 2) }}</td>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>
                                            {{  Form::open(['method' => 'DELETE', 'route' => ['pph21.destroy', $pph->id]]) }}
                                                <button type="button" class="btn btn-danger m-1 btn-sm bs-pass-para">{{ __('Delete') }}</button>
                                            {{ Form::close() }}
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
            }

            function branchChange() {
                var branch = $("#branch-filter").val();

                let filterbranch = document.getElementById('filter_branch');
                let exportbranch = document.getElementById('export_branch');
                let generatebranch = document.getElementById('generate_branch');
                let bulkpaybranch = document.getElementById('bulk_branch');

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
