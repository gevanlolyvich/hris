@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Vehicle Workshop') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vehicle Workshop') }}</li>
@endsection

@section('action-button')
    <a href="#" data-url="{{ route('vehicle-workshop.create') }}" data-ajax-popup="true" data-size="md"
        data-title="{{ __('Create Vehicle Workshop') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
        data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
@endsection

@section('content')
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Address') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($workshops as $workshop)
                                <tr>
                                    <td>{{ $workshop->name }}</td>
                                    <td>{{ Str::limit($workshop?->address ?? '-', 50) }}</td>
                                    <td class="action">
                                        <span>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" 
                                                    data-url="{{  route('vehicle-workshop.edit', $workshop->id) }}"
                                                    data-size="md" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('Update Vehicle Workshop') }}"
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['vehicle-workshop.destroy', $workshop->id], 'id' => 'delete-form-' . $workshop->id]) !!}
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Delete')}}"
                                                    aria-label="{{__('Delete')}}"><i
                                                        class="ti ti-trash text-white text-white"></i></a>
                                                </form>
                                            </div>
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
        $(document).ready(function() {
            $('.select2').select2({
                multiple: true,
            }); 
        });
    </script>
    <script>
        $(document).ready(function() {            
            $(document).on('change', 'select[name=is_resricted]', function () {
                let resricted_choice = $(this).val();
                let branch_div = document.getElementById('branch_div');

                if (resricted_choice == 1) {
                    document.getElementById("branch_div").style.display = '';
                } else if (resricted_choice == 0) {
                    document.getElementById("branch_div").style.display = 'none';
                }
            });

            $('#commonModal').on('shown.bs.modal', function () {
                let resricted_choice = document.getElementById('is_resricted').value;

                if (resricted_choice == 1) {
                    document.getElementById("branch_div").style.display = '';
                } else if (resricted_choice == 0) {
                    document.getElementById("branch_div").style.display = 'none';
                }
            })
        });
    </script>
@endpush
