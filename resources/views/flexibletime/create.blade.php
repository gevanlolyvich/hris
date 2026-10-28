@extends('layouts.admin')

@section('page-title')
    {{ __('New Flexible Time Request') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('flexibletime.index') }}">{{ __('Flexible Time') }}</a></li>
    <li class="breadcrumb-item">{{ __('New Request') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
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
                <h5 class="card-title">{{ __('New Flexible Time Request') }}</h5>

                <form method="POST" action="{{ route('flexibletime.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">{{ __('Employee') }}</label>
                        <input type="text" class="form-control" value="{{ $employee->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="date" class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date" class="form-control"
                               min="{{ $minDate }}" value="{{ old('date') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="requested_start_time" class="form-label">
                            {{ __('Requested Start Time') }} <span class="text-danger">*</span>
                        </label>
                        <input type="time" name="requested_start_time" id="requested_start_time" class="form-control"
                               min="{{ $normal ? substr($normal['start_time'], 0, 5) : '00:00' }}"
                               max="{{ $maxStart }}"
                               value="{{ old('requested_start_time', $normal ? substr($normal['start_time'], 0, 5) : '') }}"
                               required>
                        <small class="text-muted">
                            {{ __('Allowed between :min and :max (inclusive). End time is fixed at :hours hours after the start.', [
                                'min' => $normal ? substr($normal['start_time'], 0, 5) : '-',
                                'max' => $maxStart,
                                'hours' => 9,
                            ]) }}
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">{{ __('Reason') }} <span class="text-danger">*</span></label>
                        <input type="text" name="reason" id="reason" class="form-control"
                               value="{{ old('reason') }}" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label for="document" class="form-label">{{ __('Document') }} <span class="text-danger">*</span></label>
                        <input type="file" name="document" id="document" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">
                            {{ __('PDF, JPG, JPEG, or PNG. Maximum size 2MB.') }}
                        </small>
                        @error('document')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    <a href="{{ route('flexibletime.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
