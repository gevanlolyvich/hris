@extends('layouts.auth')
@section('page-title')
    {{ __('Login') }}
@endsection
@php
    $logos = \App\Models\Utility::get_file('uploads/logo/');

    $logo = Utility::get_superadmin_logo();
@endphp

@push('custom-scripts')
    @if (env('RECAPTCHA_MODULE') == 'yes')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush

@section('language-bar')
    <li class="nav-item">
        <select name="language" id="language" class="lang-dropdown btn btn-primary my-1 me-2"
            onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
            @foreach (App\Models\Utility::languages() as $language)
                <option @if ($lang == $language) selected @endif
                    value="{{ route('login', ['lang' => $language]) }}">
                    {{ Str::upper($language) }}</option>
            @endforeach
        </select>
    </li>
@endsection

@section('content')
    <div class="row row-flex">
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mx-auto">
            {{-- <img src="{{ asset('installer/img/crystal.png') }}" alt=""> --}}
            <div class="card card-login-head">
                <div class="card-body card-title">
                    {{-- <div>
                        <img src="{{ $logos . $logo }}" alt="{{ env('APP_NAME') }}" class="login-logo logo logo-lg" />
                    </div> --}}
                    <h2 class="mb-3 login-title">{{ env('APP_NAME') }}</h2>
                    <p class="login-subtitle">
                        {{ __('Login Subtitle') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 mx-auto">
            <div class="card card-login" style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate=""
                        id="form_data">
                        @csrf
                        <div>
                            <div class="form-group mb-3">
                                <label class="form-label login-label">{{ __('Email') }}</label>
                                <input class="form-control @error('email') is-invalid @enderror" id="email"
                                    type="email" name="email" value="{{ old('email') }}"
                                    placeholder="{{ __('Enter Your Email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="error invalid-email text-danger" role="alert">
                                        <small>{{ $message }}</small>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label login-label">{{ __('Password') }}</label>
                                <input class="form-control @error('password') is-invalid @enderror" id="password"
                                    type="password" name="password" placeholder="{{ __('Enter Your Password') }}" required
                                    autocomplete="current-password">
                                @error('password')
                                    <span class="error invalid-password text-danger" role="alert">
                                        <small>{{ $message }}</small>
                                    </span>
                                @enderror


                            </div>
                            @if (env('RECAPTCHA_MODULE') == 'yes')
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
                                <button type="submit" class="login-btn login-do-btn btn btn-primary btn-block mt-2"
                                    tabindex="4">{{ __('Login') }}</button>
                            </div>

                            <hr class="mt-4 mb-4 login-line " />

                            <div class="d-grid">
                                <button type="submit" class="btn btn-block btn-download" tabindex="4">
                                    <i class="fa-brands fa-android" style="float: left; font-size:1.5rem"></i>
                                    {{ __('Login Download') }}
                                </button>
                            </div>

                            @if (Utility::getValByName('disable_signup_button') == 'on')
                                <p class="my-4 text-center">{{ __("Don't have an account?") }}
                                    <a href="{{ route('register', $lang) }}"
                                        class="my-4 text-primary">{{ __('Register') }}</a>
                                </p>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#form_data").submit(function(e) {
            $("#login_button").attr("disabled", true);
            return true;
        });
    });
</script>
