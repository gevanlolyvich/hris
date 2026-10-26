@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Budget') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Budget') }}</li>
@endsection

@section('action-button')
    @if (\Auth::user()->can('Create Budget'))
        <a href="#" data-url="{{ route('budget.create') }}" data-ajax-popup="true" data-size="md"
            data-title="{{ __('Create Budget') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Jenis') }}</th>
                                <th>{{ __('Nominal') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Document') }}</th>
                                <th width="150px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($budgets as $budget)
                                <tr>
                                    <td>{{ $budget->employee?->name ?? '-' }}</td>
                                    <td>{{ $budget->branch?->name ?? '-' }}</td>
                                    <td>{{ $budget->department?->name ?? '-' }}</td>
                                    <td>{{ $budget->jenis }}</td>
                                    <td>{{ number_format((float) $budget->nominal, 0, ',', '.') }}</td>
                                    <td>{{ \Auth::user()->dateFormat($budget->tanggal) }}</td>
                                    <td>
                                        @if ($budget->file)
                                            <a href="{{ $budget->file_url }}" target="_blank" rel="noopener">
                                                <i class="ti ti-file-text"></i> {{ __('View') }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('budget.show', $budget->id) }}" class="mx-3 btn btn-sm align-items-center"
                                                    data-bs-toggle="tooltip" title=""
                                                    data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            @if (\Auth::user()->can('Edit Budget'))
                                                <div class="action-btn bg-success ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('budget.edit', $budget->id) }}"
                                                        data-size="md" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Update Budget') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endif
                                            @if (\Auth::user()->can('Delete Budget'))
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['budget.destroy', $budget->id], 'id' => 'delete-form-' . $budget->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title=""
                                                        data-bs-original-title="{{ __('Delete') }}"
                                                        aria-label="{{ __('Delete') }}"><i
                                                            class="ti ti-trash text-white text-white"></i></a>
                                                    </form>
                                                </div>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('No Budget Found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
