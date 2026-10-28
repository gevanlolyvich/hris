@extends('layouts.admin')

@php
    use App\Models\FlexibleTime;
@endphp

@section('page-title')
    {{ __('Flexible Time Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('flexibletime.index') }}">{{ __('Flexible Time') }}</a></li>
    <li class="breadcrumb-item">{{ __('Detail') }}</li>
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
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Flexible Time Detail') }}</h5>

                <table class="table table-borderless">
                    <tr>
                        <th style="width: 35%;">{{ __('Employee') }}</th>
                        <td>{{ $flexible->employee?->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <td>{{ \Auth::user()->dateFormat($flexible->date) }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Normal Schedule') }}</th>
                        <td>
                            {{ $flexible->normal_start_time ? substr($flexible->normal_start_time, 0, 5) : '-' }}
                            -
                            {{ $flexible->normal_end_time ? substr($flexible->normal_end_time, 0, 5) : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('Requested Start') }}</th>
                        <td>{{ substr($flexible->requested_start_time, 0, 5) }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Requested End') }}</th>
                        <td>{{ $flexible->requested_end_time ? substr($flexible->requested_end_time, 0, 5) : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Reason') }}</th>
                        <td>{{ $flexible->reason }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Document') }}</th>
                        <td>
                            @if ($flexible->document)
                                <a href="{{ $flexible->document }}" target="_blank">{{ __('View Document') }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('Status') }}</th>
                        <td>
                            @if ($flexible->status == FlexibleTime::STATUS_APPROVED)
                                <div class="badge bg-success p-2 px-3 rounded">{{ __('Approved') }}</div>
                            @elseif ($flexible->status == FlexibleTime::STATUS_PENDING)
                                <div class="badge bg-warning p-2 px-3 rounded">{{ __('Pending') }}</div>
                            @elseif ($flexible->status == FlexibleTime::STATUS_REJECTED)
                                <div class="badge bg-danger p-2 px-3 rounded">{{ __('Rejected') }}</div>
                            @else
                                <div class="badge bg-secondary p-2 px-3 rounded">{{ __('Cancelled') }}</div>
                            @endif
                        </td>
                    </tr>
                    @if ($flexible->approved_at)
                        <tr>
                            <th>{{ __('Approved By') }}</th>
                            <td>{{ $flexible->approvedBy?->name }} ({{ \Auth::user()->dateFormat($flexible->approved_at) }})</td>
                        </tr>
                        <tr>
                            <th>{{ __('Approved Start') }}</th>
                            <td>{{ $flexible->approved_start_time ? substr($flexible->approved_start_time, 0, 5) : '-' }}</td>
                        </tr>
                    @endif
                    @if ($flexible->rejected_at)
                        <tr>
                            <th>{{ __('Rejected By') }}</th>
                            <td>{{ $flexible->rejectedBy?->name }} ({{ \Auth::user()->dateFormat($flexible->rejected_at) }})</td>
                        </tr>
                        <tr>
                            <th>{{ __('Rejection Reason') }}</th>
                            <td>{{ $flexible->rejection_reason ?? '-' }}</td>
                        </tr>
                    @endif
                    @if ($flexible->cancelled_at)
                        <tr>
                            <th>{{ __('Cancelled By') }}</th>
                            <td>{{ $flexible->cancelledBy?->name }} ({{ \Auth::user()->dateFormat($flexible->cancelled_at) }})</td>
                        </tr>
                    @endif
                </table>

                <div class="d-flex gap-2">
                    @if ($canApprove)
                        <form method="POST" action="{{ route('flexibletime.approve', $flexible->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">{{ __('Approve') }}</button>
                        </form>

                        <form method="POST" action="{{ route('flexibletime.reject', $flexible->id) }}" class="d-flex gap-2">
                            @csrf
                            <input type="text" name="rejection_reason" class="form-control"
                                   placeholder="{{ __('Rejection reason (optional)') }}" maxlength="255">
                            <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                        </form>
                    @endif

                    @if ($canCancel)
                        <form method="POST" action="{{ route('flexibletime.cancel', $flexible->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary"
                                    onclick="return confirm('{{ __('Cancel this request?') }}')">
                                {{ __('Cancel Request') }}
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('flexibletime.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('History') }}</h5>

                <ul class="list-group list-group-flush">
                    @forelse ($flexible->logs as $log)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong>
                                    {{ $log->from_status ? __(':from → :to', ['from' => $log->from_status, 'to' => $log->to_status]) : $log->to_status }}
                                </strong>
                                <small>{{ \Auth::user()->dateFormat($log->created_at) }}</small>
                            </div>
                            <small class="text-muted">
                                {{ $log->changedBy?->name }} — {{ $log->note }}
                            </small>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">{{ __('No history.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
