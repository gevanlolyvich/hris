@extends('layouts.admin')

@section('page-title')
    {{ __('Hasil Meeting') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Hasil Meeting') }}</li>
@endsection

@section('action-button')
    @php
        $exportEmp = \App\Models\Employee::where('user_id', Auth::id())->first();
        $canExport = Auth::user()->type == 'company' || ($exportEmp && in_array($exportEmp->department_id, [11, 12, 16]));
    @endphp
    @if($canExport)
        <a href="{{ route('meeting-result.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Export') }}">
            <i class="ti ti-file-export mx-1"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Deadline') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Filled By') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resultRows as $row)
                                <tr>
                                    <td>{{ $row->meeting->title }}</td>
                                    <td>{{ $row->meeting->meeting_type }}</td>
                                    <td>{{ $row->meeting->meeting_date->format('d M Y') }}</td>
                                    <td>{{ $row->meeting->deadline->format('d M Y H:i') }}</td>
                                    <td>
                                        @if ($row->meeting->isLocked())
                                            <span class="badge bg-danger">{{ __('Di Tutup') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Open') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row->is_filled)
                                            <span class="badge bg-success">{{ $row->employee->name }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('Belum diisi') }} ({{ $row->employee->name }})</span>
                                        @endif
                                    </td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg"
                                                    data-url="{{ route('meeting-result.show', [$row->meeting->id, 'employee_id' => $row->employee->id]) }}"
                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                    data-title="{{ __('View Meeting Result') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            @if ($row->is_my_result && !$row->meeting->isLocked())
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('meeting-result.fill', $row->meeting->id) }}"
                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                        data-title="{{ $row->is_filled ? __('Edit Hasil') : __('Isi Hasil') }}">
                                                        <i class="ti ti-edit text-white"></i>
                                                    </a>
                                                </div>
                                            @endif
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
@endsection