@extends('layouts.admin')

@php
    use App\Models\FlexibleTime;
@endphp

@section('page-title')
    {{ __('Flexible Time') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Flexible Time') }}</li>
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">{{ __('Flexible Time Requests') }}</h5>
                    @if ($canCreate)
                        <a href="{{ route('flexibletime.create') }}" class="btn btn-primary btn-sm">
                            {{ __('New Request') }}
                        </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Normal Schedule') }}</th>
                                <th>{{ __('Requested Start') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Requested By') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $item)
                                <tr>
                                    <td>{{ $item->employee?->name }}</td>
                                    <td>{{ \Auth::user()->dateFormat($item->date) }}</td>
                                    <td>
                                        {{ $item->normal_start_time ? substr($item->normal_start_time, 0, 5) : '-' }}
                                        -
                                        {{ $item->normal_end_time ? substr($item->normal_end_time, 0, 5) : '-' }}
                                    </td>
                                    <td>{{ substr($item->requested_start_time, 0, 5) }}</td>
                                    <td>
                                        @if ($item->status == FlexibleTime::STATUS_APPROVED)
                                            <div class="badge bg-success p-2 px-3 rounded">{{ __('Approved') }}</div>
                                        @elseif ($item->status == FlexibleTime::STATUS_PENDING)
                                            <div class="badge bg-warning p-2 px-3 rounded">{{ __('Pending') }}</div>
                                        @elseif ($item->status == FlexibleTime::STATUS_REJECTED)
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ __('Rejected') }}</div>
                                        @else
                                            <div class="badge bg-secondary p-2 px-3 rounded">{{ __('Cancelled') }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $item->createdBy?->name ?? '-' }}</td>
                                    <td class="Action">
                                        <a href="{{ route('flexibletime.show', $item->id) }}" class="btn btn-sm btn-info">
                                            {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No Flexible Time requests.') }}</td>
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
