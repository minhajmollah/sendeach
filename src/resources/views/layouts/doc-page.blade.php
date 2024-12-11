<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title') - SendEach Documentation</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Favicons -->
    <link href="/assets/landing_page/img/favicon-send.png" rel="icon" />
    <link href="/assets/landing_page/img/favicon-send.png" rel="apple-touch-icon" />

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap" rel="stylesheet">
    <script src="{{asset('assets/global/js/jquery-3.6.0.min.js')}}?v={{ config('app.asset_version') }}"></script>

    <!-- FontAwesome JS-->
    <script defer src="/assets/docs/fontawesome/js/all.min.js?v={{ config('app.asset_version') }}"></script>

    <!-- Plugins CSS -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.2/styles/atom-one-dark.min.css?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="/assets/docs/plugins/simplelightbox/simple-lightbox.min.css?v={{ config('app.asset_version') }}">

    <!-- Theme CSS -->
    <link id="theme-style" rel="stylesheet" href="/assets/docs/css/theme.css?v={{ config('app.asset_version') }}">
    @stack('stylepush')
</head>

<body class="docs-page">

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
                    <a href="{{ route('docs.index') }}" class="mx-2 btn btn-secondary d-none d-lg-flex">All Docs</a>
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
    <div class="docs-wrapper">
        <div id="docs-sidebar" class="docs-sidebar">
            <nav id="docs-nav" class="docs-nav navbar">
                @yield('sidebar')
            </nav>
            <!--//docs-nav-->
        </div>
        <!--//docs-sidebar-->
        <div class="docs-content">
            <div class="container">
                @yield('content')
                <footer class="footer">
                    <div class="container text-center py-5">
                        <small class="copyright">&copy; Copyright <strong><span>SendEach</span></strong>. All Rights
                            Reserved.</small>
                    </div>
                </footer>
            </div>
        </div>
    </div>
    <!--//docs-wrapper-->


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
    @stack('scriptpush')
</body>

</html>
