<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>@yield('title', 'Welcome') - SendEach</title>
    <meta content="" name="description" />
    <meta content="" name="keywords" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicons -->
    <link href="{{ asset('assets/landing_page/img/favicon-send.png') }}" rel="icon" />
    <link href="{{ asset('assets/landing_page/img/favicon-send.png') }}" rel="apple-touch-icon" />

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-H01LM4PVRM"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-H01LM4PVRM');
    </script>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,600,600i,700,700i"
        rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/landing_page/vendor/aos/aos.css') }}?v={{ config('app.asset_version') }}"
        rel="stylesheet" />
    <link
        href="{{ asset('assets/landing_page/vendor/bootstrap/css/bootstrap.min.css') }}?v={{ config('app.asset_version') }}"
        rel="stylesheet" />
    <link
        href="{{ asset('assets/landing_page/vendor/bootstrap-icons/bootstrap-icons.css') }}?v={{ config('app.asset_version') }}"
        rel="stylesheet" />
    <link
        href="{{ asset('assets/landing_page/vendor/boxicons/css/boxicons.min.css') }}?v={{ config('app.asset_version') }}"
        rel="stylesheet" />
    <link
        href="{{ asset('assets/landing_page/vendor/glightbox/css/glightbox.min.css') }}?v={{ config('app.asset_version') }}"
        rel="stylesheet" />
    <link
        href="{{ asset('assets/landing_page/vendor/swiper/swiper-bundle.min.css') }}?v={{ config('app.asset_version') }}"
        rel="stylesheet" />

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/landing_page/css/style.css') }}?v={{ config('app.asset_version') }}"
        rel="stylesheet" />

    <style>
        .scale-hover {
            transform: scale(1);
            transition: transform .25s ease;
        }

        .scale-hover:hover {
            transform: scale(1.1);
        }
    </style>

</head>

<body>
    <!-- ======= Header ======= -->
    <header id="header" class="fixed-top d-flex align-items-center">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="logo">
                <h1 class="text-light">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('assets/landing_page/img/logo-navbar.png') }}" alt="logo">
                    </a>
                </h1>
            </div>

            <nav id="navbar" class="navbar">
                <ul>
                    <li><a class="nav-link scrollto" href="{{ route('home') }}">Home</a></li>
                    <li><a class="nav-link scrollto" href="#about">About Us</a></li>
                    <li><a class="nav-link scrollto" href="#services">Services</a></li>
                    <li><a class="nav-link scrollto" href="#packages">Pricing</a></li>
                    <li><a class="nav-link scrollto" href="#Downloads">Downloads</a></li>
                    <li><a class="nav-link" href="{{ route('docs.index') }}">Docs</a></li>
                    <li><a class="nav-link scrollto" href="/#contact">Contact</a></li>

                    @auth('web')
                        <li>
                            <a class="getstarted scrollto" href="{{ route('user.dashboard') }}">Dashboard</a>
                        </li>
                    @endauth
                    @guest
                        <li>
                            <a class="getstarted scrollto" href="{{ route('login') }}">Log In</a>
                        </li>
                        <li>
                            <a class="getstarted scrollto" href="{{ route('register') }}">Sign Up</a>
                        </li>
                    @endguest
                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav>
            <!-- .navbar -->
        </div>
    </header>
    <!-- End Header -->
    @yield('content')

    <!-- ======= Footer ======= -->
    <footer id="footer">
        <div class="footer-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6 footer-contact">
                        <img class="pb-3" src="/assets/landing_page/img/logo-navbar.png" alt="logo">
                        <p>
                            Ocho Rios, St. Ann, <br />
                            Jamaica,<br />
                        </p>
                    </div>

                    <div class="col-lg-3 col-md-6 footer-links">
                        <h4>Useful Links</h4>
                        <ul>
                            @guest
                                <li>
                                    <i class="bx bx-chevron-right"></i> <a href="{{ route('login') }}">Login</a>
                                </li>
                                <li>
                                    <i class="bx bx-chevron-right"></i> <a href="{{ route('register') }}">Signup</a>
                                </li>
                            @endguest

                            @auth('web')
                                <li>
                                    <i class="bx bx-chevron-right"></i> <a
                                        href="{{ route('user.dashboard') }}">Dashboard</a>
                                </li>
                            @endauth

                            <li>
                                <i class="bx bx-chevron-right"></i>
                                <a href="{{ route('terms-and-conditions') }}">Terms & Conditions</a>
                            </li>
                            <li>
                                <i class="bx bx-chevron-right"></i>
                                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 footer-links">
                        <h4>Our Services</h4>
                        <ul>
                            <li>
                                <i class="bx bx-chevron-right"></i>
                                <a class="scrollto" href="/#services">Bulk WhatsApp</a>
                            </li>
                            <li>
                                <i class="bx bx-chevron-right"></i>
                                <a class="scrollto" href="/#services">Bulk SMS</a>
                            </li>
                            <li>
                                <i class="bx bx-chevron-right"></i>
                                <a class="scrollto" href="/#services">Bulk Email</a>
                            </li>
                            <li>
                                <i class="bx bx-chevron-right"></i>
                                <a class="scrollto" href="/#services">
                                    Dashboard & APIs</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 footer-links">
                        <h4>Our Social Networks</h4>
                        <p>
                            Coming Soon...
                        </p>
                        <div class="mt-3 social-links">
                            <a href="JavaScript:Void(0);" class="twitter"><i class="bx bxl-twitter"></i></a>
                            <a href="JavaScript:Void(0);" class="facebook"><i class="bx bxl-facebook"></i></a>
                            <a href="JavaScript:Void(0);" class="instagram"><i class="bx bxl-instagram"></i></a>
                            <a href="JavaScript:Void(0);" class="google-plus"><i class="bx bxl-skype"></i></a>
                            <a href="JavaScript:Void(0);" class="linkedin"><i class="bx bxl-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container py-4">
            <div class="copyright ">
                &copy; Copyright <strong><span>SendEach</span></strong>. All Rights Reserved
            </div>

        </div>
    </footer>
    <!-- End Footer -->
    <button class="btn text-decoration-none whatsapp_float p-0 floating-button" id="chatButton"><i
            class="bi bi-chat-text" style="font-size: 30px"></i></button>

    {{-- <button class="btn text-decoration-none whatsapp_float p-0 floating-button" id="sendeach-chats-button"><i --}}
    {{--        class="bi bi-chat-text" style="font-size: 30px"></i></button> --}}

    {{-- <script src="{{ route('plugins.chat.js') }}"></script> --}}

    @include('partials.chat')

    @php session()->remove('public_user') @endphp

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>


    <!-- Vendor JS Files -->
    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/landing_page/vendor/aos/aos.js') }}?v={{ config('app.asset_version') }}"></script>
    <script
        src="{{ asset('assets/landing_page/vendor/bootstrap/js/bootstrap.bundle.min.js') }}?v={{ config('app.asset_version') }}">
    </script>
    <script
        src="{{ asset('assets/landing_page/vendor/glightbox/js/glightbox.min.js') }}?v={{ config('app.asset_version') }}">
    </script>
    <script
        src="{{ asset('assets/landing_page/vendor/isotope-layout/isotope.pkgd.min.js') }}?v={{ config('app.asset_version') }}">
    </script>
    <script
        src="{{ asset('assets/landing_page/vendor/swiper/swiper-bundle.min.js') }}?v={{ config('app.asset_version') }}">
    </script>
    <script
        src="{{ asset('assets/landing_page/vendor/php-email-form/validate.js') }}?v={{ config('app.asset_version') }}">
    </script>

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/landing_page/js/main.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>


</body>

</html>
