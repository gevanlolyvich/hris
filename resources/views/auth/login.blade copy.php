

@extends('layouts.auth')
@section('page-title')
    {{__('Login')}}
@endsection
@php
    // $logo=asset(Storage::url('uploads/logo/'));
$logo=\App\Models\Utility::get_file('uploads/logo/');

@endphp

@push('custom-scripts')
@if(env('RECAPTCHA_MODULE') == 'yes')
        {!! NoCaptcha::renderJs() !!}
@endif
@endpush

@section('language-bar')
<li class="nav-item">
    <select name="language" id="language" class="lang-dropdown btn btn-primary my-1 me-2" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
        @foreach(App\Models\Utility::languages() as $language)
            <option @if($lang == $language) selected @endif value="{{ route('login', ['lang'=>$language]) }}">{{Str::upper($language)}}</option>
        @endforeach
    </select>
</li>

@endsection

@section('content')
<div class="card">
    <div class="row align-items-center text-start">
        <div class="col-xl-6">
            <div class="card-body">
                <div class="">
                    <h2 class="mb-3 f-w-600">Login</h2>
                </div>
                <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate="" id="form_data">
                    @csrf
                    <div>
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter Your Email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="error invalid-email text-danger" role="alert">
                                    <small>{{ $message }}</small>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('Password') }}</label>
                            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" placeholder="{{ __('Enter Your Password') }}" required autocomplete="current-password">
                            @error('password')
                            <span class="error invalid-password text-danger" role="alert">
                                <small>{{ $message }}</small>
                            </span>
                            @enderror

                            @if (Route::has('password.request'))
                            <div class="mb-2 mt-2">
                                <a href="{{ route('password.request') }}" class="small text-muted text-underline--dashed border-primar">{{ __('Forgot Your Password?') }}</a>
                            </div>
                            @endif
                        </div>
                        @if(env('RECAPTCHA_MODULE') == 'yes')
                        <div class="form-group col-lg-12 col-md-12 mt-3">
                            {!! NoCaptcha::display() !!}
                            @error('g-recaptcha-response')
                            <span class="error small text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        @endif

                        <div class="d-grid">
                            <button type="submit" class="login-do-btn btn btn-primary btn-block mt-2" tabindex="4">{{ __('Login') }}</button>
                            <span class="mt-3 text-muted">{{ __('Get our Android app by clicking on the icon below.') }}</span>
                            <a href="{{ asset('storage/uploads/apk/HRIS.apk') }}" class="mt-2" target="_blank" rel="noopener noreferrer">
                                <img src="{{ asset('assets/images/android-download.png') }}" height="40" width="120">
                            </a>
                        </div>

                        @if(Utility::getValByName('disable_signup_button')=='on')
                            <p class="my-4 text-center">{{ __("Don't have an account?") }}
                                <a href="{{route('register',$lang)}}" class="my-4 text-primary">{{__('Register')}}</a>
                            </p>
                        @endif
                    </div>
                </form>

        </div>
    </div>
        <div class="col-xl-6 img-card-side">
            <div class="auth-img-content">
                <img src="{{ asset('assets/images/auth/img-auth-3.svg') }}" alt="" class="img-fluid">
                <h4 class="text-white mb-4 mt-5"> {{ "“".__('Success is not achieved alone')."”" }}</h4>
                <p class="text-white"> {{__('But through strong team collaboration. Every small step brings us closer to our common goal.')}}</p>
            </div>
        </div>
    </div>
</div>
@endsection
<script src="{{asset('js/jquery.min.js')}}"></script>
<script>
    $(document).ready(function () {
    $("#form_data").submit(function (e) {
        $("#login_button").attr("disabled", true);
        return true;
         });
    });
</script>
