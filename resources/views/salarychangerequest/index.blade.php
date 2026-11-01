@extends('layouts.admin')

@php
    use App\Models\SalaryChangeRequest;
@endphp

@section('page-title')
    {{ __('Approve Salary Change') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Approve Salary Change') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Pending Salary Changes') }}</h5>
                <p class="text-muted">
                    {{ __('Salary only changes once a request below is approved. An employee whose salary is still empty is updated immediately and never appears here.') }}
                </p>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Current Salary') }}</th>
                                <th>{{ __('Requested Salary') }}</th>
                                <th>{{ __('Difference') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Requested By') }}</th>
                                <th>{{ __('Requested At') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pending as $requestItem)
                                <tr>
                                    <td>{{ $requestItem->employee?->name }}</td>
                                    <td>{{ number_format($requestItem->old_salary, 0, ',', '.') }}</td>
                                    <td>{{ number_format($requestItem->new_salary, 0, ',', '.') }}</td>
                                    <td
                                        class="{{ $requestItem->new_salary > $requestItem->old_salary ? 'text-success' : 'text-danger' }}">
                                        {{ $requestItem->new_salary > $requestItem->old_salary ? '+' : '' }}{{ number_format($requestItem->new_salary - $requestItem->old_salary, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <div class="badge bg-info p-2 px-3 rounded">
                                            {{ $requestItem->source == SalaryChangeRequest::SOURCE_IMPORT ? __('Import') : __('Form') }}
                                        </div>
                                    </td>
                                    <td>{{ $requestItem->requester?->name }}</td>
                                    <td>{{ \Auth::user()->dateFormat($requestItem->created_at) }}</td>
                                    <td class="Action">
                                        <div class="d-flex gap-2">
                                            <form method="POST" action="{{ route('salary-change.approve', $requestItem->id) }}">
                                                @csrf
                                                <input type="hidden" name="note" value="">
                                                <button type="submit" class="btn btn-sm btn-success align-items-center">
                                                    {{ __('Approve') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('salary-change.reject', $requestItem->id) }}">
                                                @csrf
                                                <input type="hidden" name="note" value="">
                                                <button type="submit" class="btn btn-sm btn-danger align-items-center">
                                                    {{ __('Reject') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('No pending salary change.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Reviewed') }}</h5>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Current Salary') }}</th>
                                <th>{{ __('Requested Salary') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Reviewed By') }}</th>
                                <th>{{ __('Reviewed At') }}</th>
                                <th>{{ __('Note') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviewed as $requestItem)
                                <tr>
                                    <td>{{ $requestItem->employee?->name }}</td>
                                    <td>{{ number_format($requestItem->old_salary, 0, ',', '.') }}</td>
                                    <td>{{ number_format($requestItem->new_salary, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($requestItem->status == SalaryChangeRequest::STATUS_APPROVED)
                                            <div class="badge bg-success p-2 px-3 rounded">{{ __('Approved') }}</div>
                                        @else
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ __('Rejected') }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $requestItem->reviewer?->name }}</td>
                                    <td>{{ \Auth::user()->dateFormat($requestItem->reviewed_at) }}</td>
                                    <td>{{ $requestItem->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('Nothing reviewed yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
