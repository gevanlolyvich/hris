@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Training') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Training List') }}</li>
@endsection


@section('action-button')
    {{-- <a href="{{ route('training.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a> --}}

    @can('Create Training')
        <a href="#" data-url="{{ route('training.create') }}" data-ajax-popup="true" data-size="lg"
            data-title="{{ __('Create New Training') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{-- <h5> </h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Training Type') }}</th>
                                <th>{{ __('Status')}}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Training Duration') }}</th>
                                <th>{{ __('Cost') }}</th>
                                @if (\Auth::user()->type == 'employee')
                                    <th>{{ __('Result') }}</th>
                                @endif
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Approval') }}</th>
                                @else
                                    <th>{{ __('Detail') }}</th>
                                @endif
                                @if (Gate::check('Edit Training') || Gate::check('Delete Training') || Gate::check('Show Training'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trainings as $training)
                                <tr>
                                    <td>{{ $training?->name }}</td>
                                    <td>{{ !empty($training->types) ? $training->types->name : '' }} <br></td>
                                    <td>
                                        @if ($training->status == 'Pending')
                                            <div class="badge bg-warning p-2 px-3 rounded">{{ __('Pending Approval') }}</div>
                                        @elseif($training->status == 'Approved')
                                            <div class="badge bg-success p-2 px-3 rounded">{{ __($training->status) }}</div>
                                        @elseif($training->status == "Reject")
                                            <div class="badge bg-danger p-2 px-3 rounded">{{ __($training->status) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ !empty($training->employees) ? $training->employees->name : '' }} </td>
                                    <td>{{ \Auth::user()->dateFormat($training->start_date) . ' to ' . \Auth::user()->dateFormat($training->end_date) }}
                                    </td>
                                    <td>{{ \Auth::user()->priceFormat($training->training_cost) }}</td>
                                    @if (\Auth::user()->type == 'employee' && $training->status == 'Approved')
                                        <td class="text-center">
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                    data-url="{{ route('training.getResult', $training->id) }}"
                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Training Result') }}"
                                                    data-bs-original-title="{{ __('Result') }}">
                                                    @if ($training->result_file)
                                                        <i class="fas fa-file text-white"></i>
                                                    @else
                                                        <i class="ti ti-file text-white"></i>
                                                    @endif
                                                </a>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                data-url="{{ route('training.show', $training->id) }}"
                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                title="" data-title="{{ \Auth::user()->type != 'employee' ? __('Training Approval') : __('Trainig Details')}}"
                                                data-bs-original-title="{{ \Auth::user()->type != 'employee' ? __('Approval') : __('Detail') }}">
                                                <i class="ti ti-eye text-white"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="Action">
                                        @if (Gate::check('Edit Training') || Gate::check('Delete Training') || Gate::check('Show Training'))
                                            <span>
                                                @can('Edit Training')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-size="lg"
                                                            data-url="{{ route('training.edit', $training->id) }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Training') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Training')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['training.destroy', $training->id], 'id' => 'delete-form-' . $training->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                            aria-label="Delete"><i
                                                                class="ti ti-trash text-white text-white"></i></a>
                                                        </form>
                                                    </div>
                                                @endcan
                                            </span>
                                        @endif
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
        $(document).ready(function () {
            $('#commonModal').on('shown.bs.modal', function () {
                $('.status').on('click', function () {
                    $('#commonModal').modal('hide');
                    
                    var buttonValue = $(this).data("status");
                    $("#hiddenStatus").val(buttonValue);
                })
            });

            $(document).on('change', '#date_input', function () {
                let dateInput = $(this).val();

                console.log(dateInput);

                const end_date = document.getElementById('end_date_input');

                end_date.disabled = false;
                end_date.min = dateInput;
                end_date.value = '';
            })
        });
    </script>
@endpush