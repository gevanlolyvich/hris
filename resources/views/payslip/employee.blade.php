@extends('layouts.admin')

@section('page-title')
    {{ __('Payslip') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('payslip') }}</li>
@endsection


@section('content')
    {{-- Password Modal --}}
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('Enter Password To Access Payslip')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    {!! Form::open(['method' => 'POST', 'route' => ['payslip.auth'], 'id'=>'payslipForm']) !!}
                        <div class="modal-body" style="padding-top: 0.35rem">
                                <div class="form-group">
                                    {{ Form::label('password', __('Password'), ['class' => 'col-form-label']) }}
                                    {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Password'), 'type'=>'password', 'required'=>'required', 'id'=>'password_input', 'autocomplete' => 'new-password']) }}
                                </div>
                                <input type="hidden" name="payslip_id" id="payslip_id" value="0">
                        </div>
                        <div class="modal-footer">
                            <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
                            <input type="submit" value="{{__('Submit')}}" id="submitPassword" class="btn btn-primary" data-size="xl" data-ajax-popup="true" data-title="{{ __('Employee Payslip') }}">
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    {{-- PDF Modal --}}
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">{{ __('Employee Payslip') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- PDF content will be loaded here -->
                </div>
                <!-- You can add a footer if needed -->
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
                                <th>{{ __('Salary Month') }}</th>
                                <th>{{ __('Payroll Type') }}</th>
                                <th>{{ __('Created Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payslips as $payslip)
                                <tr>
                                    <td>{{ $payslip?->salary_month }}</td>
                                    <td>{{ $payslip?->employees?->salaryType?->name ?? '-' }}</td>
                                    <td>{{ $payslip?->created_at }}</td>
                                    <td>
                                        @if ($payslip?->status)
                                            <div class="badge bg-success p-2 px-3 rounded text-white">{{__('Paid')}}</div>
                                        @else
                                            <div class="badge bg-danger p-2 px-3 rounded text-white">{{__('UnPaid')}}</div>
                                        @endif
                                    </td>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm m-1 btn-warning" data-payslip="{{ $payslip->id }}" id="payslipPDF">{{ __('Payslip') }}</button>
                                            </div>
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
            let payslip_id;
            $(document).on("click", "#payslipPDF", function() {
                $('#passwordModal').modal('show');

                payslip_id = $(this).data('payslip');
                document.getElementById('payslip_id').value = payslip_id;
            });
            
            $(document).on("submit", "#payslipForm", function(e) {
                e.preventDefault(); // Prevent default form submission

                let formData = $(this).serialize(); // Serialize form data

                // Make an AJAX POST request
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'html', // Expect HTML response for PDF content
                    success: function(response) {
                        let isError = false, errMsg = '';
                        try {
                            let parsed = JSON.parse(response);
                            isError = true;
                            errMsg = parsed.error || '';
                        } catch (e) {
                            isError = false;
                        }

                        if (isError) {
                            $('#pdfModal .modal-body').html('<div class="alert alert-danger">' + errMsg + '</div>');
                        } else {
                            $('#pdfModal .modal-body').html(response);
                        }

                        $('#pdfModal').modal('show'); // Show modal
                    },
                    error: function(error) {
                        show_toastr('error', '{{ __('Something went wrong.') }}');
                    }
                });

                $('#passwordModal').modal('hide');
            });

            $('#passwordModal').on('hidden.bs.modal', function () {
                document.getElementById('password_input').value = '';
            });
        });
    </script>
@endpush
