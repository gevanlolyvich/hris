@extends('layouts.admin')

@section('page-title')
    {{ __('Budget') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('budget.index') }}">{{ __('Budget') }}</a></li>
    <li class="breadcrumb-item">{{ $budget->employee?->name ?? '-' }}</li>
@endsection

@section('action-button')
    <a href="{{ route('budget.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-arrow-left"></i>
    </a>
@endsection

@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <table class="table">
                    <tbody>
                        <tr>
                            <th style="width: 200px">{{ __('Employee') }}</th>
                            <td>{{ $budget->employee?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Branch') }}</th>
                            <td>{{ $budget->branch?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Department') }}</th>
                            <td>{{ $budget->department?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Jenis') }}</th>
                            <td>{{ $budget->jenis }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Nominal') }}</th>
                            <td>{{ number_format((float) $budget->nominal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <td>{{ \Auth::user()->dateFormat($budget->tanggal) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Document') }}</th>
                            <td>
                                @if ($budget->file)
                                    <a href="{{ $budget->file_url }}" target="_blank" rel="noopener">
                                        <i class="ti ti-file-text"></i> {{ basename($budget->file) }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Created By') }}</th>
                            <td>{{ $budget->creator?->name ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
