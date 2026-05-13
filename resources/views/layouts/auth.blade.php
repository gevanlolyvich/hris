<!DOCTYPE html>
@php
    $logos = \App\Models\Utility::get_file('uploads/logo/');

    $logo = Utility::get_superadmin_logo();
    $company_favicon = Utility::getValByName('company_favicon');
    $company_name = Utility::getValByName('title_text');

    $dark_mode = Utility::getValByName('dark_mode');
    $theme_color = Utility::getValByName('theme_color');
    $SITE_RTL = env('SITE_RTL');

    $setting = App\Models\Utility::colorset();
    $mode_setting = App\Models\Utility::mode_layout();
    $color = 'theme-3';
    if (!empty($mode_setting['theme_color'])) {
        $color = $mode_setting['theme_color'];
    }
@endphp
<html lang="en">
<html dir="{{ env('SITE_RTL') == 'on' ? 'rtl' : '' }}">


<head>
    <title>
        {{ Utility::getValByName('title_text') ? Utility::getValByName('title_text') : config('app.name', 'Company') }}
        - @yield('page-title')</title>
    <!-- HTML5 Shim and Respond.js IE11 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 11]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />


    <meta name="description" content="Dashboard Template Description" />
    <meta name="keywords" content="Dashboard Template" />
    <meta name="author" content="SyafiqRM" />

    <!-- Favicon icon -->
    <link rel="icon" href="{{ asset(Storage::url('uploads/logo')) . '/favicon.png' }}" type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/stylesheet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <!-- vendor css -->

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">


    @if (env('SITE_RTL') == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif
    @if (isset($mode_setting['dark_mode']) && $mode_setting['dark_mode'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @endif
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/8eae36aafb.js" crossorigin="anonymous"></script>
</head>

<body class="{{ $color }}">
    <!-- [ auth-signup ] start -->
    <div class="auth-wrapper auth-v3 auth-pattern">
        <div class="auth-header">
            <img src="{{ $logos . $logo }}" alt="{{ env('APP_NAME') }}" class="logo logo-lg mb-4 login-logo"
                style="float: left" />
            <ul class="navbar-nav align-items-center ms-auto mb-4 mt-lg-4 mt-md-4 mt-2" style="float: right">
                <li class="nav-item">
                    @yield('language-bar')
                </li>
            </ul>
        </div>
        <div class="auth-content">

            @yield('content')

        </div>
        <div class="auth-footer-login">
            <h5 style="font-weight: 400" class="mb-4 mt-4">© Copyright <strong><a href="https://jxboard.co.id/"
                        target="_blank">Jakarta
                        Experience Board</a></strong>. All
                Rights Reserved</h5>
        </div>
    </div>
    <!-- [ auth-signup ] end -->

    <!-- Required Js -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor-all.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script>
        feather.replace();
    </script>

    <input type="checkbox" class="d-none" id="cust-theme-bg"
        {{ Utility::getValByName('cust_theme_bg') == 'on' ? 'checked' : '' }} />
    <input type="checkbox" class="d-none" id="cust-darklayout"
        {{ Utility::getValByName('cust_darklayout') == 'on' ? 'checked' : '' }} />

    <script src="{{ asset('js/custom.js') }}"></script>
    <script>
        var toster_pos = "{{ $SITE_RTL == 'on' ? 'left' : 'right' }}";
    </script>
    @stack('script')
    @stack('custom-scripts')

    @if ($message = Session::get('success'))
        <script>
            show_toastr('Success', '{!! $message !!}', 'success');
        </script>
    @endif
    @if ($message = Session::get('error'))
        <script>
            show_toastr('Error', '{!! $message !!}', 'error');
        </script>
    @endif
</body>

</html>
