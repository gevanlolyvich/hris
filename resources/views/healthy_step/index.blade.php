@extends('layouts.admin')

@section('page-title')
   {{ __('Manage Healthy Steps') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Healthy Steps') }}</li>
@endsection

@section('action-button')

    @can('Create Healthy Steps')
        <a href="#" data-url="{{ route('healthy-steps.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Healthy Steps') }}" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan

@endsection


@section('content')
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">

                    <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                @if (empty(Auth::user()->employee_id))
                                    <th>{{ __('Name') }}</th>
                                @endif
                                <th>{{ __('Number Of Steps') }}</th>
                                <th>{{ __('Target/Day') }}</th>
                                <th>{{ __('Attachment') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($healthy_steps as $step)
                                <tr>
                                    <td>{{ $step->date }}</td>
                                    @if (empty(Auth::user()->employee_id))
                                        <td>{{ $step->employee->name  }}</td>
                                    @endif
                                    <td>
                                        <span @if($step->steps <= $step->healthy_target->target) class="btn btn-danger btn-sm text-center disabled" @endif>
                                            {{ number_format($step->steps, 0, ',', '.').' '.__("Steps") }} 
                                        </span>
                                    </td>
                                    <td>{{ number_format($step->healthy_target->target, 0, ',', '.').' '.__("Steps") }} </td>
                                    <td>
                                        <div class="info">
                                            <a href="#" class="btn btn-primary btn-sm  align-items-center"
                                                data-url="{{ route('healthy-steps.show', $step->id) }}"
                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                data-title="{{ __('Show Healthy Steps') }}"
                                                data-bs-original-title="{{ __('Show') }}">
                                                <i class="ti ti-eye"></i> {{ __('Show File') }}</a>
                                            </a>
                                        </div>
                                        
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @can('Edit Healthy Steps')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                        data-url="{{ URL::to('healthy-steps/' . $step->id . '/edit') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Healthy Steps') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete Healthy Steps')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['healthy-steps.destroy', $step->id], 'id' => 'delete-form-' . $step->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><i
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
@endsection

@push('script-page')
    <script>
        $(document).ready(() => {
            $(document).on('change', '[name="attachment"]', function () {
                const file = document.getElementById('uploadFile');
                file.style.display = '';
                file.style['max-width'] = '';
                document.getElementById('fileName').textContent = this.files[0].name;
            });
        })
    </script>
@endpush