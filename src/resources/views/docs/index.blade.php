@extends('layouts.docs')
@section('content')

    <div class="py-5 text-center page-header theme-bg-dark position-relative">
        <div class="theme-bg-shapes-right"></div>
        <div class="theme-bg-shapes-left"></div>
        <div class="container">
            <h1 class="mx-auto page-heading single-col-max">Documentation</h1>
            <div class="mx-auto page-intro single-col-max">SendEach dashboard and API</div>
        </div>
    </div>
    <!--//page-header-->
    <div class="page-content">
        <div class="container">
            <div class="py-5 docs-overview">
                <div class="row justify-content-center">
                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">Authentication</span>
                                </h5>
                                <div class="card-text">
                                    You will be able to login/ signup
                                    using your WhatsApp number on which
                                    you will be able to send your OTP code.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.auth') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>

                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                    <i class="fa-solid fa-tower-broadcast"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">API Authentication Key</span>
                                </h5>
                                <div class="card-text">
                                    Using intuitive UI you will be to
                                    Generate UI token for the third party
                                    applications.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.api-token') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>


                    <!--Developers API-->
                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                    <i class="fa-solid fa-tower-broadcast"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">Developer API</span>
                                </h5>
                                <div class="card-text">
                                    Developers can integrate SendEach's marketing or OTP messaging services into their
                                    apps or websites using our API.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.developers-api') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>

                    <!--//col-->
                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">WhatsApp Gateway</span>
                                </h5>
                                <div class="card-text">
                                    Create your own System User Access Token from Meta Business Manager And Send
                                    Messages through whatsapp business channel.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.whatsapp-gateway') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>

                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                     <img src="assets/frontend/img/sms.png" style="height: 32px;"/>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">SMS Gateway</span>
                                </h5>
                                <div class="card-text">
                                    The SendEach SMS Gateway offers a streamlined solution for sending SMS messages for free or paid.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.sms.gateway') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>

                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                     <i class="fa fa-mail-bulk"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">Email Gateway</span>
                                </h5>
                                <div class="card-text">
                                    The Sendeach Email Gateway enables users to send marketing and authentication emails through their own account for efficient communication.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.email.gateway') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>
                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                     <i class="fa fa-envelope"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">SendEach Support</span>
                                </h5>
                                <div class="card-text">
                                    Sendeach offers excellent customer support, and welcomes feedback, issue reports, donations, and feature requests, which can be submitted via WhatsApp, website contact form, or user dashboard ticket.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.support') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>
                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                     <i class="fa-brands fa-whatsapp"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">SendEach WhatsApp Tools</span>
                                </h5>
                                <div class="card-text">
                                    SendEach's WhatsApp tools help clients gather contacts, chat lists, active members, and other data.
                                    The tools also facilitate bulk adding of group members, chat list grabbing, group finding, and Google Maps data extraction.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.whatsapp_tools') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>
                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                     <i class="fa-brands fa-bots"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">AI Customer Representative</span>
                                </h5>
                                <div class="card-text">
                                    Sendeach AI Customer Rep seamlessly integrates with WhatsApp, websites, and Facebook, enabling businesses to engage customers across multiple platforms through intelligent chatbots for enhanced communication and support.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.ai_customer_reps') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>
                    <div class="py-3 col-12 col-lg-4">
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                <span class="theme-icon-holder card-icon-holder me-2">
                                     <i class="fa-brands fa-wordpress"></i>
                                </span>
                                    <!--//card-icon-holder-->
                                    <span class="card-title-text">WordPress</span>
                                </h5>
                                <div class="card-text">
                                    The Sendeach WordPress plugin empowers websites with advanced features such as AI chatbots, SMS OTP, and user data management, enhancing interactions and functionality seamlessly.
                                </div>
                                <a class="card-link-mask" href="{{ route('docs.wordpress') }}"></a>
                            </div>
                            <!--//card-body-->
                        </div>
                        <!--//card-->
                    </div>

                    <!--//col-->
                </div>
                <!--//row-->
            </div>
            <!--//container-->
        </div>
        <!--//container-->
    </div>
@endsection
