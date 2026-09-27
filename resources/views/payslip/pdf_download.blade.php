@php
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = Utility::getValByName('company_logo');
    $companyLogoFile = (isset($company_logo) && !empty($company_logo)) ? $company_logo : 'logo.png';
    $logoPath = public_path('uploads/logo/' . $companyLogoFile);
    $logoSrc = '';
    if (file_exists($logoPath)) {
        $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    } else {
        $logoSrc = $logo . '/' . $companyLogoFile;
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip</title>
    <style>
        body { font-family: DejaVuSans, sans-serif; font-size: 10px; color: #333; }
        h4 { margin: 0 0 5px 0; }
        .invoice-number { text-align: right; }
        .company-address { text-align: right; }
        .info-block { padding: 4px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th { background: #f4f4f4; text-align: left; }
        .table th, .table td { border: 1px solid #ddd; padding: 4px 6px; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .mt-4 { margin-top: 14px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .pb-2 { padding-bottom: 8px; }
        .total-block { width: 45%; margin-left: auto; }
        .total-row { display: block; margin-top: 4px; }
        .hr { border-top: 1px solid #ccc; margin: 6px 0; }
    </style>
</head>
<body>
    <div class="invoice">
        <div style="width:100%; overflow:hidden;">
            <div style="width:35%; float:left;">
                <img src="{{ $logoSrc }}" width="75px;">
            </div>
            <div style="width:60%; float:right;">
                <div class="invoice-number"><h4>{{ __('Payslip') }}</h4></div>
            </div>
        </div>

        <div style="width:100%; overflow:hidden; margin-top:8px;">
            <div style="width:50%; float:left;">
                <div class="info-block">
                    <strong>{{ __('Name') }} :</strong> {{ $employee->name }}<br>
                    <strong>{{ __('Position') }} :</strong> {{ __('Employee') }}<br>
                    <strong>{{ __('Salary Date') }} :</strong> {{ \Auth::user()->dateFormat($payslip->created_at) }}<br>
                </div>
            </div>
            <div style="width:50%; float:right;">
                <div class="company-address">
                    <strong>{{ \Utility::getValByName('company_name') }} </strong><br>
                    {{ \Utility::getValByName('company_address') }} , {{ \Utility::getValByName('company_city') }},<br>
                    {{ \Utility::getValByName('company_state') }}-{{ \Utility::getValByName('company_zipcode') }}<br>
                    <strong>{{ __('Salary Slip') }} :</strong> {{ $payslip->salary_month }}<br>
                </div>
            </div>
        </div>

        <table class="table">
            <tbody>
                <tr class="font-weight-bold">
                    <th>{{ __('Earning') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th class="text-right">{{ __('Amount') }}</th>
                </tr>
                <tr>
                    <td>{{ __('Basic Salary') }}</td>
                    <td>{{ !empty($salaryType) ? $salaryType : '-' }}</td>
                    <td>-</td>
                    <td class="text-right">{{ \Auth::user()->priceFormat($payslip->basic_salary) }}</td>
                </tr>

                @foreach ($payslipDetail['earning']['allowance'] as $allowance)
                    @php
                        $employess = \App\Models\Employee::find($allowance->employee_id);
                        $empdallow = ($allowance->amount * $employess->salary) / 100;
                        $allowanceAmount = $allowance->prorated_amount ?? $allowance->amount;
                    @endphp
                    <tr>
                        <td>{{ __('Allowance') }}</td>
                        <td>{{ $allowance->title }}</td>
                        <td>{{ ucfirst($allowance->type) }}</td>
                        @if ($allowance->type != 'percentage')
                            <td class="text-right">{{ \Auth::user()->priceFormat($allowanceAmount) }}</td>
                        @else
                            <td class="text-right">{{ $allowance->amount }}% ({{ \Auth::user()->priceFormat($empdallow) }})</td>
                        @endif
                    </tr>
                @endforeach
                @foreach ($payslipDetail['earning']['commission'] as $commission)
                    @php
                        $employess = \App\Models\Employee::find($commission->employee_id);
                        $empcomm = ($commission->amount * $employess->salary) / 100;
                    @endphp
                    <tr>
                        <td>{{ __('Commission') }}</td>
                        <td>{{ $commission->title }}</td>
                        <td>{{ ucfirst($commission->type) }}</td>
                        @if ($commission->type != 'percentage')
                            <td class="text-right">{{ \Auth::user()->priceFormat($commission->amount) }}</td>
                        @else
                            <td class="text-right">{{ $commission->amount }}% ({{ \Auth::user()->priceFormat($empcomm) }})</td>
                        @endif
                    </tr>
                @endforeach
                @foreach ($payslipDetail['earning']['otherPayment'] as $otherPayment)
                    @php
                        $employess = \App\Models\Employee::find($otherPayment->employee_id);
                        $emppayment = ($otherPayment->amount * $employess->salary) / 100;
                    @endphp
                    <tr>
                        <td>{{ __('Other Payment') }}</td>
                        <td>{{ $otherPayment->title }}</td>
                        <td>{{ ucfirst($otherPayment->type) }}</td>
                        @if ($otherPayment->type != 'percentage')
                            <td class="text-right">{{ \Auth::user()->priceFormat($otherPayment->amount) }}</td>
                        @else
                            <td class="text-right">{{ $otherPayment->amount }}% ({{ \Auth::user()->priceFormat($emppayment) }})</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="table">
            <tbody>
                <tr class="font-weight-bold">
                    <th>{{ __('Deduction') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th class="text-right">{{ __('Amount') }}</th>
                </tr>
                @if (count($payslipDetail['deduction']['loan']) || count($payslipDetail['deduction']['deduction']) || count($payslipDetail['deduction']['bpjs'] ?? []))
                    @foreach ($payslipDetail['deduction']['loan'] as $loan)
                        @php
                            $employess = \App\Models\Employee::find($loan->employee_id);
                            $emploan = ($loan->amount * $employess->salary) / 100;
                        @endphp
                        <tr>
                            <td>{{ __('Loan') }}</td>
                            <td>{{ $loan->title }}</td>
                            <td>{{ ucfirst($loan->type) }}</td>
                            @if ($loan->type != 'percentage')
                                <td class="text-right">{{ \Auth::user()->priceFormat($loan->amount) }}</td>
                            @else
                                <td class="text-right">{{ $loan->amount }}% ({{ \Auth::user()->priceFormat($emploan) }})</td>
                            @endif
                        </tr>
                    @endforeach
                    @foreach ($payslipDetail['deduction']['deduction'] as $deduction)
                        @php
                            $employess = \App\Models\Employee::find($deduction->employee_id);
                            $empdeduction = ($deduction->amount * $employess->salary) / 100;
                        @endphp
                        <tr>
                            <td>{{ __('Saturation Deduction') }}</td>
                            <td>{{ $deduction->title }}</td>
                            <td>{{ ucfirst($deduction->type) }}</td>
                            @if ($deduction->type != 'percentage')
                                <td class="text-right">{{ \Auth::user()->priceFormat($deduction->amount) }}</td>
                            @else
                                <td class="text-right">{{ $deduction->amount }}% ({{ \Auth::user()->priceFormat($empdeduction) }})</td>
                            @endif
                        </tr>
                    @endforeach
                    @foreach ($payslipDetail['deduction']['bpjs'] ?? [] as $item)
                        @php
                            $empbpjs = $item->prorated_amount ?? (($item->amount * \App\Models\Employee::find($item->employee_id)?->salary) / 100);
                        @endphp
                        <tr>
                            <td>{{ __('BPJS') }}</td>
                            <td>{{ !empty($item->bpjs_option()) ? $item->bpjs_option()->name : '' }}</td>
                            <td>{{ ucfirst($item->type) }}</td>
                            @if ($item->type != 'percentage')
                                <td class="text-right">{{ \Auth::user()->priceFormat($item->amount) }}</td>
                            @else
                                <td class="text-right">{{ $item->amount }}% ({{ \Auth::user()->priceFormat($empbpjs) }})</td>
                            @endif
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td> - </td>
                        <td> - </td>
                        <td> - </td>
                        <td class="text-right"> - </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="mt-4">
            <div class="total-block">
                <div class="total-row">
                    <strong>{{ __('Total Earning') }} :</strong>
                    {{ \Auth::user()->priceFormat($payslipDetail['totalEarning']) }}
                </div>
                <div class="total-row">
                    <strong>{{ __('Total Deduction') }} :</strong>
                    {{ \Auth::user()->priceFormat($payslipDetail['totalDeduction']) }}
                </div>
                <div class="hr"></div>
                <div class="total-row">
                    <strong>{{ __('Net Salary') }} :</strong>
                    {{ \Auth::user()->priceFormat($payslip->net_payble) }}
                </div>
            </div>
        </div>

        <div style="width:100%; overflow:hidden; margin-top:24px;">
            <div style="width:50%; float:left;">
                <p class="mt-2">{{ __('Employee Signature') }}</p>
            </div>
            <div style="width:50%; float:right; text-align:right;">
                <p class="mt-2">{{ __('Paid By') }} {{ $payslip->status ? $company_name?->value : '-' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
