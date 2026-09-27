@extends('layouts.admin')

@section('page-title')
    {{ __('Isi Hasil Meeting') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('meeting-result.index') }}">{{ __('Hasil Meeting') }}</a></li>
    <li class="breadcrumb-item">{{ __('Isi Hasil') }}</li>
@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ $meeting->title }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <strong>{{ __('Meeting Type') }}:</strong>
                        <p>{{ $meeting->meeting_type }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>{{ __('Date') }}:</strong>
                        <p>{{ $meeting->meeting_date->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>{{ __('Time') }}:</strong>
                        <p>{{ \Carbon\Carbon::parse($meeting->meeting_time)->format('H:i') }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>{{ __('Deadline') }}:</strong>
                        <p>{{ $meeting->deadline->format('d M Y H:i') }}</p>
                    </div>
                </div>

                @if(!empty($documentUrls))
                <div class="row mb-4">
                    <div class="col-12">
                        <strong>{{ __('Document') }}:</strong>
                        <p>
                            @foreach($documentUrls as $docUrl)
                                <a href="{{ $docUrl }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1">
                                    <i class="ti ti-download"></i> {{ __('Download Document') }}
                                </a>
                            @endforeach
                        </p>
                    </div>
                </div>
                @endif

                <div class="row mb-4">
                    <div class="col-12">
                        <strong>{{ __('Attendees') }}:</strong>
                        <div class="mt-2">
                            @foreach($meeting->attendees as $attendee)
                                <span class="badge bg-info">{{ $attendee->employee->name }} ({{ $attendee->employee->department ? $attendee->employee->department->name : '-' }})</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('meeting-result.store', $meeting->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="content" class="form-label"><strong>{{ __('Hasil / Kesimpulan Meeting') }}</strong></label>
                                <textarea class="form-control editor" id="content" name="content" rows="15">{{ $result ? $result->content : '' }}</textarea>
                            </div>
                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-save"></i> {{ __('Save') }}
                                </button>
                                <a href="{{ route('meeting-result.index') }}" class="btn btn-light">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script src="{{ asset('assets/js/plugins/tinymce/tinymce.min.js') }}"></script>
<script>
    $(document).ready(function() {
        tinymce.init({
            selector: '#content',
            height: 400,
            menubar: true,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        });
    });
</script>
@endpush
