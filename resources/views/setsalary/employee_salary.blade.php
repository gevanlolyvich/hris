@extends('layouts.admin')

@section('page-title')
    {{ __('Employee Set Salary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('setsalary') }}">{{ __('Set Salary') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Set Salary') }}</li>
@endsection

@section('content')
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                {{ Form::open(array('route' => array('setsalary.show', $employee->id),'method'=>'get','id'=>'setsalary_filter')) }}
                    <div class="row align-items-center">
                        <div class="col-10 month">
                            <div class="btn-box">
                                {{Form::label('month',__('Month'),['class'=>'col-form-label'])}}
                                {{Form::month('month',isset($_GET['month']) ? $_GET['month'] : date('Y-m'),array('class'=>'month-btn form-control month-btn'))}}
                            </div>
                        </div>
                        <div class="col-2 mt-4 float-right text-right">
                            <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('setsalary_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{route('setsalary.show', $employee->id)}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                            </a>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body fulls-card p-3 align-items-center">
                <div class="row text-center">
                    <div class="col">
                        <h6 style="padding: 10px 0;margin-bottom: 0px">{{ $employee->name }}</h6> 
                    </div>
                    <div class="col">
                        <h6 style="padding: 10px 0;margin-bottom: 0px">{{ ucwords($employee?->employeeType?->name ?? '-') }}</h6>
                    </div>
                    <div class="col">
                        <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getBranch($employee->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : '-' }}</h6>
                    </div>
                    <div class="col">
                        <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '-' }}</h6>
                    </div>
                    <div class="col">
                        <h6 style="padding: 10px 0;margin-bottom: 0px">{{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '-' }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="row">
            <div class="col-xl-12">
                <div class="card set-card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ __('Employee Salary') }}</h5>
                            </div>
                            @can('Create Set Salary')
                                <div class="col-6 text-end">
                                    <a  data-url="{{ route('employee.basic.salary', $employee->id) }}"
                                        data-ajax-popup="true" data-title="{{ __('Set Basic Sallary') }}"
                                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                        data-bs-original-title="{{ __('Set Salary') }}">
                                        <i class="{{ $employee->salary_type() && $employee->salary ? "ti ti-pencil text-white" : "ti ti-plus" }}"></i>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="row mb-2">
                            <div class="project-info d-flex text-md">
                                <div class="project-info-inner col-6">
                                    <b class="m-0"> {{ __('Salary Type') }} </b>
                                    <div class="project-amnt">{{ $employee->salary_type() }}</div>
                                </div>
                                <div class="project-info-inner col-6">
                                    <b class="m-0"> {{ __('Main Salary') }} </b>
                                    <div class="project-amnt">{{ \Auth::user()->priceFormat($employee->salary) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <hr>
                            <div class="project-info d-flex text-md">
                                <div class="project-info-inner col-4">
                                    <b class="m-0"> {{ __('Required Days') }} </b>
                                    <div class="project-amnt ">{{ $employee->employeeType->type == 'Fixed' ? $total_work_days : '-' }}</div>
                                </div>
                                <div class="project-info-inner col-4">
                                    <b class="m-0"> {{ __('Valid Days') }} </b>
                                    <div class="project-amnt ">{{ $total_present_days }}</div>
                                </div>
                                <div class="project-info-inner col-4">
                                    <b class="m-0"> {{ __('Total Main Salary') }} </b>
                                    <div class="project-amnt">{{ \Auth::user()->priceFormat($employee->employeeType->type == 'Fixed' ? $employee->salary * ($total_present_days / $total_work_days) : $total_present_days * $employee->salary) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>

            <!-- allowance -->
            <div class="col-md-6">
                <div class="card set-card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ __('Allowance') . " (+)" }}</h5>
                            </div>
                            @can('Create Allowance')
                                <div class="col-6 text-end">
                                    <a  data-url="{{ route('allowances.create', $employee->id) }}"
                                        data-ajax-popup="true" data-title="{{ __('Create Allowance') }}"
                                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                        data-size="lg" data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class=" card-body table-border-style" style=" overflow:auto">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Allowance Option') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Recurring') }}</th>
                                        <th>{{ __('Period') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($allowances as $allowance)
                                        <tr>
                                            <td>{{ !empty($allowance->allowance_option()) ? $allowance->allowance_option()->name : '' }}</td>
                                            <td>{{ $allowance->title }}</td>
                                            <td>{{ $allowance->is_recurring ? __('Recurring') : __('No') }}</td>
                                            <td>{{ $allowance->period ?? '-' }}</td>
                                            <td>{{ \Auth::user()->priceFormat($allowance->amount) }}</td>
                                            <td class="Action">
                                                <span>
                                                    @can('Edit Allowance')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a  class="mx-3 btn btn-sm  align-items-center"
                                                                data-url="{{ URL::to('allowance/' . $allowance->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Edit Allowance') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Allowance')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['allowance.destroy', $allowance->id], 'id' => 'delete-form-' . $allowance->id]) !!}
                                                            <a 
                                                                class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
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

            <!-- Commission -->
            <div class="col-md-6">
                <div class="card set-card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ __('Commission') . " (+)" }}</h5>
                            </div>
                            @can('Create Commission')
                                <div class="col text-end">
                                    <a  data-url="{{ route('commissions.create', $employee->id) }}"
                                        data-ajax-popup="true" data-title="{{ __('Create Commission') }}"
                                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                        data-size="lg" data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>

                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class=" card-body table-border-style" style=" overflow:auto">

                        <div class="table-responsive">
                            <table class="table">
                                <thead>

                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Recurring') }}</th>
                                        <th>{{ __('Period') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($commissions as $commission)
                                        <tr>
                                            <td>{{ $commission->title }}</td>
                                            <td>{{ ucfirst($commission->type) }}</td>
                                            <td>{{ $commission->is_recurring ? __('Recurring') : __('No') }}</td>
                                            <td>{{ $commission->period ?? '-' }}</td>
                                            @if ($commission->type == 'fixed')
                                                <td>{{ \Auth::user()->priceFormat($commission->amount) }}</td>
                                            @else
                                                <td>{{ $commission->amount }}%
                                                    ({{ \Auth::user()->priceFormat($commission->tota_allow) }})
                                                </td>
                                            @endif

                                            <td class="Action">
                                                <span>
                                                    @can('Edit Commission')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a  class="mx-3 btn btn-sm  align-items-center"
                                                                data-url="{{ URL::to('commission/' . $commission->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Edit Commission') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Commission')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['commission.destroy', $commission->id], 'id' => 'delete-form-' . $commission->id]) !!}
                                                            <a 
                                                                class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
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

            <!-- other payment-->
            <div class="col-md-6">
                <div class="card set-card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ __('Other Payment') . " (+)" }}</h5>
                            </div>
                            @can('Create Other Payment')
                                <div class="col text-end">

                                    <a  data-url="{{ route('otherpayments.create', $employee->id) }}"
                                        data-ajax-popup="true" data-title="{{ __('Create Other Payment') }}"
                                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                        data-size="lg" data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class=" card-body table-border-style" style=" overflow:auto">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Recurring') }}</th>
                                        <th>{{ __('Period') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($otherpayments as $otherpayment)
                                        <tr>
                                            <td>{{ $otherpayment->title }}</td>
                                            <td>{{ $otherpayment->is_recurring ? __('Recurring') : __('No') }}</td>
                                            <td>{{ $otherpayment->period ?? '-' }}</td>
                                            <td>{{ ucfirst($otherpayment->type) }}</td>
                                            @if ($otherpayment->type == 'fixed')
                                                <td>{{ \Auth::user()->priceFormat($otherpayment->amount) }}</td>
                                            @else
                                                <td>{{ $otherpayment->amount }}%
                                                    ({{ \Auth::user()->priceFormat($otherpayment->tota_allow) }})
                                                </td>
                                            @endif

                                            <td class="Action">
                                                <span>
                                                    @can('Edit Other Payment')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a  class="mx-3 btn btn-sm  align-items-center"
                                                                data-url="{{ URL::to('otherpayment/' . $otherpayment->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Edit Other Payment') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Other Payment')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['otherpayment.destroy', $otherpayment->id], 'id' => 'delete-form-' . $otherpayment->id]) !!}
                                                            <a 
                                                                class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
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

            <!--overtime-->
            <div class="col-md-6">
                <div class="card set-card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ __('Overtime') . " (+)" }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class=" card-body table-border-style" style=" overflow:auto">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Work Days') }}</th>
                                        <th>{{ __('Start Time') }}</th>
                                        <th>{{ __('End Time') }}</th>
                                        <th>{{ __('Number of Hours') }}</th>
                                        <th>{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $overall_total_hours = 0;
                                        $maximum_hours       = $employee?->departments?->overtime_limit;
                                    @endphp
                                    @foreach ($overtimes as $overtime)
                                        @php
                                            $total_hours = 0;
                                            if ($overtime->type == 'daily') {
                                                $total_hours = 8;
                                            } else {
                                                if (date('Y-m-d', strtotime($overtime->clock_out)) != date('Y-m-d', strtotime($overtime->clock_in))) {
                                                    $end = date('Y-m-d', strtotime($overtime->clock_in . ' +1 day'));
                                                    $total_hours = max(0, round((strtotime($end) - strtotime($overtime->clock_in)) / 3600, 2));
                                                } else {
                                                    $total_hours = max(0, round((strtotime($overtime->clock_out) - strtotime($overtime->clock_in)) / 3600, 2));
                                                }
                                            }
                                        
                                            if(!empty($maximum_hours)) {
                                                if ($overall_total_hours >= $maximum_hours) {
                                                    continue;
                                                }
                                                if (($overall_total_hours + $total_hours) >= $maximum_hours) {
                                                    $total_hours =  $maximum_hours - $overall_total_hours;
                                                }
                                                $overall_total_hours += $total_hours;
                                            }
                                            $rate = $overtime->is_work_day ? $total_hours * ($overtime->employee->salary / $total_work_hours) : $total_hours * ($overtime->employee->salary / $total_work_hours) * 2;
                                        @endphp
                                        <tr>
                                            <td>{{ $overtime->title }}</td>
                                            <td>{{ $overtime->date }}</td>
                                            <td>{{ $overtime->type ?? 'hourly' }}</td>
                                            <td>{{ $overtime->is_work_day ? __('Work Days') : __('Holidays') }}</td>
                                            <td>{{ $overtime->clock_in ?? '-' }}</td>
                                            <td>{{ $overtime->clock_out ?? '-' }}</td>
                                            <td>{{ $total_hours }} {{  __('Hours')}}</td>
                                            <td>{{ \Auth::user()->priceFormat($rate) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- loan-->
            <div class="col-md-6">
                <div class="card set-card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ __('Loan') . " (-)" }}</h5>
                            </div>
                            @can('Create Loan')
                                <div class="col text-end">
                                    <a  data-url="{{ route('loans.create', $employee->id) }}"
                                        data-ajax-popup="true" data-title="{{ __('Create Loan') }}"
                                        data-bs-toggle="tooltip" title="" data-size="lg" class="btn btn-sm btn-primary"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class=" card-body table-border-style" style=" overflow:auto">

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Loan Options') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Recurring') }}</th>
                                        <th>{{ __('Period') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Loan Amount') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($loans as $loan)
                                        <tr>
                                            <td>{{ !empty($loan->loan_option()) ? $loan->loan_option()->name : '' }}
                                            </td>
                                            <td>{{ $loan->title }}</td>
                                            <td>{{ $loan->is_recurring ? __('Recurring') : __('No') }}</td>
                                            <td>{{ $loan->period ?? '-' }}</td>
                                            <td>{{ ucfirst($loan->type) }}</td>
                                            @if ($loan->type == 'fixed')
                                                <td>{{ \Auth::user()->priceFormat($loan->amount) }}</td>
                                            @else
                                                <td>{{ $loan->amount }}%
                                                    ({{ \Auth::user()->priceFormat($loan->tota_allow) }})
                                                </td>
                                            @endif
                                            <td class="Action">
                                                <span>
                                                    @can('Edit Loan')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a  class="mx-3 btn btn-sm  align-items-center"
                                                                data-url="{{ URL::to('loan/' . $loan->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Edit Loan') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Loan')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['loan.destroy', $loan->id], 'id' => 'delete-form-' . $loan->id]) !!}
                                                            <a 
                                                                class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
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

            <!-- Saturation -->
            <div class="col-md-6">
                <div class="card set-card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ __('Saturation Deduction') . " (-)" }}</h5>
                            </div>
                            @can('Create Saturation Deduction')
                                <div class="col text-end">
                                    <a  data-url="{{ route('saturationdeductions.create', $employee->id) }}"
                                        data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Saturation Deduction') }}"
                                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                        data-bs-original-title="{{ __('Create') }}">
                                        <i class="ti ti-plus"></i>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class=" card-body table-border-style" style=" overflow:auto">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Deduction Option') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Recurring') }}</th>
                                        <th>{{ __('Period') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($saturationdeductions as $saturationdeduction)
                                        <tr>
                                            <td>{{ $saturationdeduction->deduction?->name ?? '-' }}</td>
                                            <td>{{ $saturationdeduction->title }}</td>
                                            <td>{{ $saturationdeduction->is_recurring ? __('Recurring') : __('No') }}</td>
                                            <td>{{ $saturationdeduction->period ?? '-' }}</td>
                                            <td>{{ __("$saturationdeduction->type") }}</td>
                                            @if ($saturationdeduction->type == 'fixed')
                                                <td>{{ \Auth::user()->priceFormat($saturationdeduction->amount) }}
                                                </td>
                                            @else
                                                <td>{{ $saturationdeduction->amount }}%
                                                    ({{ \Auth::user()->priceFormat($saturationdeduction->tota_allow) }})
                                                </td>
                                            @endif

                                            <td class="Action">
                                                <span>
                                                    @can('Edit Saturation Deduction')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a  class="mx-3 btn btn-sm  align-items-center"
                                                                data-url="{{  URL::to('saturationdeduction/' . $saturationdeduction->id . '/edit')  }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Edit Saturation Deduction') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Saturation Deduction')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['saturationdeduction.destroy', $saturationdeduction->id], 'id' => 'delete-form-' . $saturationdeduction->id]) !!}
                                                            <a 
                                                                class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"><i
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
        </div>
    </div>
    
@endsection

@push('script-page')
    <script type="text/javascript">
        $(document).on('change', '.amount_type', function() {

            var val = $(this).val();
            var label_text = 'Amount';
            if (val == 'percentage') {
                var label_text = 'Percentage';
            }
            $('.amount_label').html(label_text);
        });


        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {
            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#designation_id').empty();
                    $('#designation_id').append(
                        '<option value="">{{ __('Select any Designation') }}</option>');
                    $.each(data, function(key, value) {
                        var select = '';
                        if (key == '{{ $employee->designation_id }}') {
                            select = 'selected';
                        }

                        $('#designation_id').append('<option value="' + key + '"  ' + select + '>' +
                            value + '</option>');
                    });
                }
            });
        }

        $(document).on('change', 'select[name=is_recurring]', function () {
            let recurring_choice = $(this).val();
            let periodHTML = document.getElementById('period');

            if (recurring_choice == 1) {
                periodHTML.disabled = true;
                periodHTML.value = null;
            } else if (recurring_choice == 0) {
                periodHTML.disabled = false;
            }
        })

        $(document).ready(function () {
            $('#commonModal').on('shown.bs.modal', function () {
                let recurringHTML = document.getElementById('is_recurring');

                if (recurringHTML) {
                    let periodHTML = document.getElementById('period');

                    if (recurringHTML.value == 1) {
                        periodHTML.disabled = true;
                    } else if (recurringHTML.value == 0) {
                        periodHTML.disabled = false;
                    }
                }
            });
        });
    </script>
@endpush
