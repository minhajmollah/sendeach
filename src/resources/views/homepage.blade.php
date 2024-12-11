@extends('layouts.website')

{{--@section('title', 'Home')--}}

@section('content')
    <!-- ======= Hero Section ======= -->
    <section id="hero" class="d-flex align-items-center">
        <div class="container">
            <div class="row gy-4">
                <div class="order-2 col-lg-6 order-lg-1 d-flex flex-column justify-content-center">
                    <h1>Seamless Messaging & AI Customer Rep Solutions</h1>
                    <h2>
                        Empowering Communication: Send, Connect, Chat with Confidence
                    </h2>
                    <div>
                        <a href="{{ route('register') }}" class="btn-get-started scrollto">Connect</a>
                    </div>
                </div>
                <div class="order-1 col-lg-6 order-lg-2 hero-img">
                    <img src="../../../assets/landing_page/img/hero-img.svg" class="img-fluid animated" alt=""/>
                </div>
            </div>
        </div>
    </section>
    <!-- End Hero -->

    <main id="main">
        <!-- ======= About Section ======= -->
        <section id="about" class="about">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-5 d-flex align-items-center justify-content-center about-img">
                        <img src="../../../assets/landing_page/img/about-img.svg" class="img-fluid" alt=""
                             data-aos="zoom-in"/>
                    </div>
                    <div class="pt-5 col-lg-6 pt-lg-0 ">
                        <h3 data-aos="fade-up" class="section-title">SendEach?</h3>
                        <p class="fs-6">SendEach is your dynamic messaging solution. Seamlessly connect your device to the cloud, enabling hassle-free bulk messaging across WhatsApp, SMS, and email. Effortlessly manage group communications while maintaining a professional touch.</p>

                        <p class="fs-6">Our innovative AI Customer Rep lends a hand by interacting with customers through Facebook Messenger, WhatsApp, WordPress, Chat Link, or your website. </p>
                        <p class="fs-6  ">SendEach offers a distinctive advantage: utilize personal accounts for free messaging, OTP, and explore all features with no charges, while integrating seamlessly via API for app connections.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- End About Section -->

        <!-- ======= Services Section ======= -->
        <section id="services" class="services section-bg">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>Services</h2>
                    <p>Check out the great services we offer</p>
                </div>

                <div class="row">
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="100">
                        <div class="icon-box">
                            <div class="icon"><i class="bx bxl-whatsapp"></i></div>
                            <h4 class="title"><a href="{{ route('docs.whatsapp-gateway') }}">WhatsApp</a></h4>
                            <p class="description">
                                Send marketing and authentication messages via WhatsApp using your own account.
                            </p>
                            <a href="{{ route('docs.whatsapp-gateway') }}">Learn More</a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="200">
                        <div class="icon-box">
                            <div class="icon"><i class="bx bx-chat"></i></div>
                            <h4 class="title"><a href="https://sendeach.com/docs/ai-customer-representatives">AI Customer Rep</a></h4>
                            <p class="description">
                                Automate responses across WhatsApp, Messenger and your website.
                            </p>
                            <a href="https://sendeach.com/docs/ai-customer-representatives">Learn More</a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="300">
                        <div class="icon-box">
                            <div class="icon"><i class="bx bx-envelope"></i></div>
                            <h4 class="title"><a href="{{ route('docs.email.gateway') }}">SMS & Email</a></h4>
                            <p class="description">
                                Utilize your own email account or mobile plan to send marketing and OTP messages with
                                SendEach.
                            </p>
                            <a href="{{ route('docs.email.gateway') }}">Learn More</a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="400">
                        <div class="icon-box">
                            <div class="icon"><i class="bx bxs-dashboard"></i></div>
                            <h4 class="title"><a href="{{ route('docs.developers-api') }}">WordPress & API</a></h4>
                            <p class="description">
                                Seamless integration via WordPress plugin or developer-friendly API.
                            </p>
                            <a href="{{ route('docs.developers-api') }}">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Services Section -->

        <!-- ======= Packages Section ======= -->
        <section id="packages" class="services bg-white">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>Pricing</h2>
                    <p> Check out the exciting prices we offer</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="100">
                        <div class="icon-box">
                            <h4 class="title mt-3"><a
                                    href="{{ route('user.credits.create') }}">Free</a>
                            </h4>
                            <p class="description">Unlimited messages <span class="text-muted small"></span>
                            </p>
                            <p>Watermarked Messages</p>
                            <p class=""></p>
                            <h2 class="mt-3">
                                <strong><sup>{{ $general->currency_symbol }}</sup>{{ shortAmount(0) }}</strong>
                            </h2>
                            @guest
                                <div>
                                    <a href="{{ route('user.credits.create') }}"
                                       class="btn-get-started scrollto">Select</a>
                                </div>
                            @endguest
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="100">
                        <div class="icon-box">
                            <div class="badge">Recommended</div>
                            <h4 class="title mt-3"><a
                                    href="{{ route('user.credits.create') }}">Paid</a>
                            </h4>
                            <p class="description">Unlimited Messages</p>
                            <p class="description">No Watermarked Messages</p>
                            <p class="description"></p>
                            <h2 class="mt-3">
                                <strong><sup>{{ $general->currency_symbol }}</sup>5</strong><sub>Monthly</sub>
                            </h2>
                            @guest
                                <div>
                                    <a href="{{ route('user.credits.create') }}"
                                       class="btn-get-started scrollto">Select</a>
                                </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Services Section -->

        <!-- ======= Mobile App Section ======= -->
        <section id="Downloads" class="about section-bg">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-5 d-flex align-items-center justify-content-center about-img">
                        <img src="../../../assets/landing_page/img/about.png" class="img-fluid" alt=""
                             data-aos="zoom-in"/>
                    </div>
                    <div class="pt-5 col-lg-6 pt-lg-0">
                        <h3 data-aos="fade-up">Our client apps</h3>
                        <p data-aos="fade-up" data-aos-delay="100">
                            Get easy access to SendEach's messaging solutions with our Android and PC clients, plus a
                            WordPress plugin for seamless integration.
                        </p>
                        <div class="row">
                            <div class="col-md-4 col-lg-6 d-flex align-items-stretch" data-aos="zoom-in"
                                 data-aos-delay="100">
                                <div class="icon-box pb-2">
                                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                                        <a href="{{ asset('assets/android/v001.apk') }}" target="_blank"
                                           download="SendEach Mobile - Android App.apk"
                                           class="d-inline-block scale-hover">
                                            <img src="{{ asset('assets/landing_page/img/android_app.jpg') }}"
                                                 alt="Download Now"
                                                 class="img-fluid" style="width: 60px; border-radius: 1rem;">
                                        </a>
                                    </div>
                                    <br>
                                    <h4 class="title"><a href="{{ asset('assets/android/v001.apk') }}">Sms Gateway -
                                            Android App</a></h4>

                                    <p class="description">
                                        Easily send bulk SMS and OTP using your mobile plan with SendEach SMS Gateway Android app.
                                    </p>
                                    <a href="{{ route('docs.sms.gateway') }}#android" target="_blank">How to Install
                                        ?</a><br>
                                    {{--                                <a href="#" class="description">how to install ?</a>--}}
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 d-flex align-items-stretch" data-aos="zoom-in"
                                 data-aos-delay="200">
                                <div class="icon-box pb-2">
                                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                                        <a href="{{ asset('assets/desktop-app/SendEach.msi') }}" target="_blank"
                                           download="SendEach Desktop APP.msi"
                                           class="d-inline-block scale-hover">
                                            <img src="{{ asset('assets/landing_page/img/pc_whatsapp.jpg') }}"
                                                 alt="Download Now"
                                                 class="img-fluid" style="width: 60px; border-radius: 1rem;">
                                        </a>
                                    </div>
                                    <h4 class="title"><a href="{{ asset('assets/desktop-app/SendEach.msi') }}">Whatsapp
                                            Gateway - PC Software</a></h4>
                                    <p class="description">
                                        Utilize WhatsApp Gateway PC software to send bulk messages through your personal WhatsApp account.
                                    </p>
                                    {{--                                <a href="{{ route('docs.whatsapp-gateway') }}">Learn More</a><br>--}}
                                    <a href="{{ route('docs.whatsapp-gateway') }}#desktop" target="_blank">How to
                                        Install ?</a>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 d-flex align-items-stretch pt-4 " data-aos="zoom-in"
                                 data-aos-delay="300">
                                <div class="icon-box">
                                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                                        <a href="{{ asset('assets/wordpress/auth-plugin.zip') }}" target="_blank"
                                           download="Wordpress-OTP-Plugin.zip"
                                           class="d-inline-block scale-hover">
                                            <img src="{{ asset('assets/landing_page/img/wordpress_icon.jpg') }}"
                                                 alt="Download Now"
                                                 class="img-fluid" style="width: 60px; border-radius: 1rem;">
                                        </a>
                                    </div>
                                    <h4 class="title"><a href="{{ asset('assets/wordpress/sendeach.zip') }}">SendEach WordPress Plugin</a></h4>
                                    <p class="description">
                                        SendEach's WordPress plugin offers OTP authentication, login forms, AI customer rep, and your website regsitered user contact import.
                                    </p>
                                    <a href="{{ route('docs.wordpress') }}" target="_blank">how to Install
                                        ?</a><br>
                                    {{--                                <a href="#" class="description">how to install ?</a>--}}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- End Mobile App Section -->

        <!-- ======= Services Section ======= -->
        <!--//page-header-->
        <!--    <section id="Docs">-->
        <!--        <div class="section-title">-->
        <!--            <h2>Documentation</h2>-->
        <!--            <p> SendEach Documentation</p>-->
        <!--        </div>-->
        <!--        <div class="page-content">-->
        <!--            <div class="container">-->
        <!--                <div class="py-5 docs-overview">-->
        <!--                    <div class="row justify-content-center">-->
        <!--                        <div class="py-3 col-12 col-lg-4">-->
        <!--                            <div class="shadow-sm card">-->
        <!--                                <div class="card-body">-->
        <!--                                    <h4>Authentication</h4>-->
        <!--                                    <div class="card-text">-->
        <!--                                        You will be able to login/ signup-->
        <!--                                        using your WhatsApp number on which-->
        <!--                                        you will be able to send your OTP code.-->
        <!--                                    </div>-->
        <!--                                    <a class="card-link-mask" href="{{ route('docs.auth') }}"></a>-->
        <!--                                </div>-->
        <!--                                &lt;!&ndash;//card-body&ndash;&gt;-->
        <!--                            </div>-->
        <!--                            &lt;!&ndash;//card&ndash;&gt;-->
        <!--                        </div>-->

        <!--                        <div class="py-3 col-12 col-lg-4">-->
        <!--                            <div class="shadow-sm card">-->
        <!--                                <div class="card-body">-->
        <!--                                    <h5 class="mb-3 card-title">-->
        <!--                                <span class="theme-icon-holder card-icon-holder me-2">-->
        <!--                                    <i class="fa-solid fa-tower-broadcast"></i>-->
        <!--                                </span>-->
        <!--                                        &lt;!&ndash;//card-icon-holder&ndash;&gt;-->
        <!--                                        <span class="card-title-text">API Authentication Key</span>-->
        <!--                                    </h5>-->
        <!--                                    <div class="card-text">-->
        <!--                                        Using intuitive UI you will be to-->
        <!--                                        Generate UI token for the third party-->
        <!--                                        applications.-->
        <!--                                    </div>-->
        <!--                                    <a class="card-link-mask" href="{{ route('docs.api-token') }}"></a>-->
        <!--                                </div>-->
        <!--                                &lt;!&ndash;//card-body&ndash;&gt;-->
        <!--                            </div>-->
        <!--                            &lt;!&ndash;//card&ndash;&gt;-->
        <!--                        </div>-->


        <!--                        &lt;!&ndash;Developers API&ndash;&gt;-->
        <!--                        <div class="py-3 col-12 col-lg-4">-->
        <!--                            <div class="shadow-sm card">-->
        <!--                                <div class="card-body">-->
        <!--                                    <h5 class="mb-3 card-title">-->
        <!--                                <span class="theme-icon-holder card-icon-holder me-2">-->
        <!--                                    <i class="fa-solid fa-tower-broadcast"></i>-->
        <!--                                </span>-->
        <!--                                        &lt;!&ndash;//card-icon-holder&ndash;&gt;-->
        <!--                                        <span class="card-title-text">Developer API</span>-->
        <!--                                    </h5>-->
        <!--                                    <div class="card-text">-->
        <!--                                        Developers can integrate SendEach's marketing or OTP messaging services into-->
        <!--                                        their-->
        <!--                                        apps or websites using our API.-->
        <!--                                    </div>-->
        <!--                                    <a class="card-link-mask" href="{{ route('docs.developers-api') }}"></a>-->
        <!--                                </div>-->
        <!--                                &lt;!&ndash;//card-body&ndash;&gt;-->
        <!--                            </div>-->
        <!--                            &lt;!&ndash;//card&ndash;&gt;-->
        <!--                        </div>-->

        <!--                        &lt;!&ndash;//col&ndash;&gt;-->
        <!--                        <div class="py-3 col-12 col-lg-4">-->
        <!--                            <div class="shadow-sm card">-->
        <!--                                <div class="card-body">-->
        <!--                                    <h5 class="mb-3 card-title">-->
        <!--                                <span class="theme-icon-holder card-icon-holder me-2">-->
        <!--                                    <i class="fa-brands fa-whatsapp"></i>-->
        <!--                                </span>-->
        <!--                                        &lt;!&ndash;//card-icon-holder&ndash;&gt;-->
        <!--                                        <span class="card-title-text">WhatsApp Gateway</span>-->
        <!--                                    </h5>-->
        <!--                                    <div class="card-text">-->
        <!--                                        Create your own System User Access Token from Meta Business Manager And Send-->
        <!--                                        Messages through whatsapp business channel.-->
        <!--                                    </div>-->
        <!--                                    <a class="card-link-mask" href="{{ route('docs.whatsapp-gateway') }}"></a>-->
        <!--                                </div>-->
        <!--                                &lt;!&ndash;//card-body&ndash;&gt;-->
        <!--                            </div>-->
        <!--                            &lt;!&ndash;//card&ndash;&gt;-->
        <!--                        </div>-->

        <!--                        <div class="py-3 col-12 col-lg-4">-->
        <!--                            <div class="shadow-sm card">-->
        <!--                                <div class="card-body">-->
        <!--                                    <h5 class="mb-3 card-title">-->
        <!--                                <span class="theme-icon-holder card-icon-holder me-2">-->
        <!--                                     <img src="assets/frontend/img/sms.png" style="height: 32px;"/>-->
        <!--                                </span>-->
        <!--                                        &lt;!&ndash;//card-icon-holder&ndash;&gt;-->
        <!--                                        <span class="card-title-text">SMS Gateway</span>-->
        <!--                                    </h5>-->
        <!--                                    <div class="card-text">-->
        <!--                                        The SendEach SMS Gateway offers a streamlined solution for sending SMS messages-->
        <!--                                        for free or paid.-->
        <!--                                    </div>-->
        <!--                                    <a class="card-link-mask" href="{{ route('docs.sms.gateway') }}"></a>-->
        <!--                                </div>-->
        <!--                                &lt;!&ndash;//card-body&ndash;&gt;-->
        <!--                            </div>-->
        <!--                            &lt;!&ndash;//card&ndash;&gt;-->
        <!--                        </div>-->

        <!--                        <div class="py-3 col-12 col-lg-4">-->
        <!--                            <div class="shadow-sm card">-->
        <!--                                <div class="card-body">-->
        <!--                                    <h5 class="mb-3 card-title">-->
        <!--                                <span class="theme-icon-holder card-icon-holder me-2">-->
        <!--                                     <i class="fa fa-mail-bulk"></i>-->
        <!--                                </span>-->
        <!--                                        &lt;!&ndash;//card-icon-holder&ndash;&gt;-->
        <!--                                        <span class="card-title-text">Email Gateway</span>-->
        <!--                                    </h5>-->
        <!--                                    <div class="card-text">-->
        <!--                                        The SendEach Email Gateway enables users to send marketing and authentication-->
        <!--                                        emails through their own account for efficient communication.-->
        <!--                                    </div>-->
        <!--                                    <a class="card-link-mask" href="{{ route('docs.email.gateway') }}"></a>-->
        <!--                                </div>-->
        <!--                                &lt;!&ndash;//card-body&ndash;&gt;-->
        <!--                            </div>-->
        <!--                            &lt;!&ndash;//card&ndash;&gt;-->
        <!--                        </div>-->
        <!--                        <div class="py-3 col-12 col-lg-4">-->
        <!--                            <div class="shadow-sm card">-->
        <!--                                <div class="card-body">-->
        <!--                                    <h5 class="mb-3 card-title">-->
        <!--                                <span class="theme-icon-holder card-icon-holder me-2">-->
        <!--                                     <i class="fa fa-envelope"></i>-->
        <!--                                </span>-->
        <!--                                        &lt;!&ndash;//card-icon-holder&ndash;&gt;-->
        <!--                                        <span class="card-title-text">SendEach Support</span>-->
        <!--                                    </h5>-->
        <!--                                    <div class="card-text">-->
        <!--                                        SendEach offers excellent customer support, and welcomes feedback, issue-->
        <!--                                        reports, donations, and feature requests, which can be submitted via WhatsApp,-->
        <!--                                        website contact form, or user dashboard ticket.-->
        <!--                                    </div>-->
        <!--                                    <a class="card-link-mask" href="{{ route('docs.support') }}"></a>-->
        <!--                                </div>-->
        <!--                                &lt;!&ndash;//card-body&ndash;&gt;-->
        <!--                            </div>-->
        <!--                            &lt;!&ndash;//card&ndash;&gt;-->
        <!--                        </div>-->

        <!--                        &lt;!&ndash;//col&ndash;&gt;-->
        <!--                    </div>-->
        <!--                    &lt;!&ndash;//row&ndash;&gt;-->
        <!--                </div>-->
        <!--                &lt;!&ndash;//container&ndash;&gt;-->
        <!--            </div>-->
        <!--            &lt;!&ndash;//container&ndash;&gt;-->
        <!--        </div>-->
        <!--    </section>-->
        <!-- End Contact Us Section -->

        <!-- ======= Contact Us Section ======= -->
        <section id="contact" class="contact mt-2 section-bg">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>Contact Us</h2>
                    <p>Get Started with SendEach</p>
                </div>

                <div class="row">
                    <div class="col-lg-5 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100">
                        <div class="info">
                            <div class="address">
                                <i class="bi bi-geo-alt"></i>
                                <h4>Location:</h4>
                                <p>Ocho Rios, St. Ann, Jamaica</p>
                            </div>

                            <div class="email">
                                <i class="bi bi-envelope"></i>
                                <h4>Email:</h4>
                                <p>info@sendeach.com</p>
                            </div>

                            <div class="phone">
                                <i class="bi bi-whatsapp"></i>
                                <h4>WhatsApp us:</h4>
                                <p>+1 (876) 782-6551</p>
                            </div>

                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30286.590231082573!2d-77.09997296807852!3d18.400869670245893!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8edafdc91dfb81d5%3A0x24cebac16dce2c77!2sOcho%20Rios%2C%20Jamaica!5e0!3m2!1sen!2s!4v1676225860302!5m2!1sen!2s"
                                width="400" height="250" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>

                    <div class="mt-5 col-lg-7 mt-lg-0 d-flex align-items-stretch" data-aos="fade-up"
                         data-aos-delay="200">
                        <form action="{{ route('contact_support.send') }}" method="POST" role="form"
                              class="contact-form" id="contactForm">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name">Your Name</label>
                                    <input type="text" name="name" class="form-control" id="form_name"
                                           placeholder="Your Name" required/>
                                </div>
                                <div class="mt-3 form-group col-md-6 mt-md-0">
                                    <label for="name">Your Email</label>
                                    <input type="email" class="form-control" name="email" id="form_email"
                                           placeholder="Your Email" required/>
                                </div>
                            </div>
                            <div class="mt-3 form-group">
                                <label for="name">Subject</label>
                                <input type="text" class="form-control" name="subject" id="form_subject"
                                       placeholder="Subject" required/>
                            </div>
                            <div class="mt-3 form-group">
                                <label for="name">Message</label>
                                <textarea class="form-control" name="message" id="form_message" rows="10"
                                          required></textarea>
                            </div>
                            <div class="d-flex my-4 justify-content-center align-items-center flex-column"> {!! NoCaptcha::display() !!}
                                @if ($errors->has('g-recaptcha-response'))
                                    <span class="text-danger">
                                    {{ $errors->first('g-recaptcha-response') }}
                                </span>
                                @endif
                            </div>

                            <div class="text-center">
                                <button type="submit">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Contact Us Section -->
    </main>
    <!-- End #main -->
    {!! NoCaptcha::renderJs() !!}

    <script>

        const contactForm = document.getElementById('contactForm');
        contactForm.addEventListener('submit', (e) => {
            // e.preventDefault();
            const name = document.getElementById('form_name').value;
            const email = document.getElementById('form_email').value;
            const subject = document.getElementById('form_subject').value;
            const message = document.getElementById('form_message').value;
            if (name.length > 0 && email.length > 0 && subject.length > 0 && message.length > 0) {
                const whatsappMessage =
                    `Hi, This is ${name} with email: "${email}". I am contacting you in regards to ${subject}.\n${message}`;

                window.open(`https://wa.me/+18767826551?text=${whatsappMessage}`, "_blank");
            }
        })
    </script>
@endsection
