<!DOCTYPE html>
<html lang="en">

<head>
    <title>SendEach - Documentation</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="/assets/landing_page/img/favicon-send.png">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap" rel="stylesheet">

    <!-- FontAwesome JS-->
    <script defer src="/assets/docs/fontawesome/js/all.min.js?v={{ config('app.asset_version') }}"></script>

    <!-- Theme CSS -->
    <link id="theme-style" rel="stylesheet" href="/assets/docs/css/theme.css?v={{ config('app.asset_version') }}">

</head>

<body>
    <header class="header fixed-top">

        <div class="branding docs-branding">
            <div class="py-2 container-fluid position-relative">
                <div class="docs-logo-wrapper">
                    <div class="site-logo"><a class="navbar-brand" href="{{ route('home') }}"><img
                                style="max-width: 120px;" class="logo-icon me-2"
                                src="/assets/landing_page/img/logo-navbar.png" alt="logo"></a></div>
                </div>
                <!--//docs-logo-wrapper-->
                <div class="docs-top-utilities d-flex justify-content-end align-items-center">
                    @auth("web")
                    <a href="{{ route('user.dashboard') }}" class="mx-2 btn btn-primary d-none d-lg-flex">Dashboard</a>
                    @endauth
                    @guest
                    <a href="{{ route('login') }}" class="mx-2 btn btn-primary d-none d-lg-flex">Log In</a>
                    <a href="{{ route('register') }}" class="mx-2 btn btn-primary d-none d-lg-flex">Sign Up</a>
                    @endguest
                </div>
                <!--//docs-top-utilities-->
            </div>
            <!--//container-->
        </div>
        <!--//branding-->
    </header>
    <!--//header-->
    @yield('content')

    <footer class="footer">

        <div class="py-3 text-center footer-bottom footer-bg">
            <!--/* This template is free as long as you keep the footer attribution link. If you'd like to use the template without the attribution link, you can buy the commercial license via our website: themes.3rdwavemedia.com Thank you for your support. :) */-->
            <small class="copyright">&copy; Copyright <strong><span>SendEach</span></strong>. All Rights
                Reserved.</small>
        </div>

    </footer>

    <!-- Javascript -->
    <script src="/assets/docs/plugins/popper.min.js?v={{ config('app.asset_version') }}"></script>
    <script src="/assets/docs/plugins/bootstrap/js/bootstrap.min.js?v={{ config('app.asset_version') }}"></script>

    <!-- Page Specific JS -->
    <script src="/assets/docs/plugins/smoothscroll.min.js?v={{ config('app.asset_version') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.8/highlight.min.js"></script>
    <script src="/assets/docs/js/highlight-custom.js?v={{ config('app.asset_version') }}"></script>
    <script src="/assets/docs/plugins/simplelightbox/simple-lightbox.min.js?v={{ config('app.asset_version') }}"></script>
    <script src="/assets/docs/plugins/gumshoe/gumshoe.polyfills.min.js?v={{ config('app.asset_version') }}"></script>
    <script src="/assets/docs/js/docs.js?v={{ config('app.asset_version') }}"></script>

</body>

</html>
