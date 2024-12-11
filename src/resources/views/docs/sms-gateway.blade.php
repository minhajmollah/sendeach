@extends('layouts.doc-page')
@section('title', "SMS Gateway")
@section('sidebar')
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#android"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Android Client - Free</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#android-1">Download SendEach APK</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#android-2">Configure Android APP</a></li>
    </ul>

    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#nexmo"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Nexmo - Paid</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#nexmo-1">Configure SMS Gateway Settings</a></li>
    </ul>

    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#twillio"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Twillio - Paid</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#twillio-1">Configure SMS Gateway Settings</a></li>
    </ul>

    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#message-bird"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Message Bird - Paid</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#message-bird-1">Configure SMS Gateway Settings</a></li>
    </ul>
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#text-magic"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Text Magic - Paid</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#text-magic-1">Configure SMS Gateway Settings</a></li>
    </ul>
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#clickatell"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Clickatell - Paid</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#clickatell-1">Configure SMS Gateway Settings</a></li>
    </ul>
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#infobip"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>InfoBip - Paid</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#infobip-1">Configure SMS Gateway Settings</a></li>
    </ul>
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#smsbroadcast"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>SMS Broadcast - Paid</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#smsbroadcast-1">Configure SMS Gateway Settings</a></li>
    </ul>
@endsection
@section('content')
    <article class="docs-article" id="section-1">
        <header class="docs-header">
            <h1 class="docs-heading">SMS Gateway <span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
                <p>The Sendeach SMS Gateway offers a streamlined solution for sending SMS messages for free(Also paid
                    options), with easy integration via API(See Api Section).</p>
            </section>
            <!--//docs-intro-->
        </header>
        <!--//section-->
    </article>

    <article class="docs-article" id="android">
        <header class="docs-header">
            <h1 class="docs-heading">Android Client - Free<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="android-1">
            <h2 class="section-heading">Download SendEach Android APK</h2>
            <div class="mb-5">
                <p class="h5">1. Goto SMS <a href="{{ route('user.gateway.sms.index') }}" target="_blank">Gateway
                        Settings</a>.</p>
                <p>Click Connect Android to know the steps on how to install the APP or follow the below steps.</p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/settings.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-">
                <p class="h5">2. Click Connect Android to follow the instructions to download the android APP or follow
                    the below instructions..</p>
                <div class="d-flex justify-content-between">
                    <ul class="my-3">
                        <li>Download the APK file by clicking this link, <a
                                href="{{ asset('assets/android/v001.apk') }}"
                                download="SendEach Mobile - Android App.apk" target="_blank">Download APK</a>.
                        </li>
                        <li>Install the downloaded application and give required permissions.</li>
                    </ul>
                    <img src="/assets/docs/images/doc-pages/sms%20gateway/android-app.png" alt="login"
                         class="mt-2 border" style="height: 500px">
                </div>
            </div>
            <!--//docs-code-block-->
        </section>

        <section class="docs-section" id="android-2">
            <h2 class="section-heading">Configure Android APP</h2>
            <div class="mb-5">
                <p class="h5">1. Open Installed SendEach APP and Login with your sendEach Account Phone Number.</p>
                <div class="alert alert-info">You need to have SendEach Account. So please <a href="{{ route('register') }}" target="_blank">Register</a> it before you Log in to SendEach APP.</div>
                <li>Login to the application using your account details.</li>
                <li>Click on the connect to complete the connection.</li>
                <li>Refresh the currently opened SendEach SMS Gateway Settings page, and you will see your connected devices in the bottom table.</li>
                <li>You can start Sending the Message through this gateway From Send SMS Option.</li>
                <div class="alert alert-warning">
                    Since SMS will be sent from your Android Phone. You will be charged by your Operator.</div>

            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="nexmo">
        <header class="docs-header">
            <h1 class="docs-heading">Nexmo Gateway - Paid<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="nexmo-1">
            <h2 class="section-heading">Configure SMS Settings</h2>
            <div class="mb-5">
                <p class="h5">1. Goto SMS <a href="{{ route('user.gateway.sms.index') }}" target="_blank">Gateway Settings </a> and scroll down to see list of available third
                party SMS Gateways.</p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/sms-gateways.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-5">
                <p class="h5">2. Click Edit Action and Fill out all the necessary fields obtained from Nexmo.</p>
                <p>Goto Nexmo Dashboard to get these credentials. Please refer these docs for more,
                    <a href="https://developer.vonage.com/en/messaging/sms/overview" target="_blank">Nexmo Docs</a>. </p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/nexmo settings.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="twillio">
        <header class="docs-header">
            <h1 class="docs-heading">Twillio Gateway - Paid<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="twillio-1">
            <h2 class="section-heading">Configure SMS Settings</h2>
            <div class="mb-5">
                <p class="h5">1. Goto SMS <a href="{{ route('user.gateway.sms.index') }}" target="_blank">Gateway Settings </a> and scroll down to see list of available third
                    party SMS Gateways.</p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/sms-gateways.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-5">
                <p class="h5">2. Click Edit Action and Fill out all the necessary fields obtained from Nexmo.</p>
                <p>Goto Nexmo Dashboard to get these credentials. Please refer these docs for more,
                    <a href="https://twilio.com/docs/sms" target="_blank">Twilio Docs</a>. </p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/twilio.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="message-bird">
        <header class="docs-header">
            <h1 class="docs-heading">Message Bird Gateway - Paid<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="message-bird-1">
            <h2 class="section-heading">Configure SMS Settings</h2>
            <div class="mb-5">
                <p class="h5">1. Goto SMS <a href="{{ route('user.gateway.sms.index') }}" target="_blank">Gateway Settings </a> and scroll down to see list of available third
                    party SMS Gateways.</p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/sms-gateways.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-5">
                <p class="h5">2. Click Edit Action and Fill out all the necessary fields obtained from Nexmo.</p>
                <p>Goto Message Bird Dashboard to get these credentials. Please refer these docs for more,
                    <a href="https://messagebird.com/connectivity/sms">Message Bird</a>. </p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/message-bird.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="text-magic">
        <header class="docs-header">
            <h1 class="docs-heading">Text Magic Gateway - Paid<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="text-magic-1">
            <h2 class="section-heading">Configure SMS Settings</h2>
            <div class="mb-5">
                <p class="h5">1. Goto SMS <a href="{{ route('user.gateway.sms.index') }}" target="_blank">Gateway Settings </a> and scroll down to see list of available third
                    party SMS Gateways.</p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/sms-gateways.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-5">
                <p class="h5">2. Click Edit Action and Fill out all the necessary fields obtained from Nexmo.</p>
                <p>Goto Text Magic Dashboard to get these credentials. Please refer these docs for more,
                    <a href="https://www.textmagic.com/docs/api/" target="_blank">Text Magic Docs</a>. </p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/text-magic.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="clickatell">
        <header class="docs-header">
            <h1 class="docs-heading">Clickatell Gateway - Paid<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="clickatell-1">
            <h2 class="section-heading">Configure SMS Settings</h2>
            <div class="mb-5">
                <p class="h5">1. Goto SMS <a href="{{ route('user.gateway.sms.index') }}" target="_blank">Gateway Settings </a> and scroll down to see list of available third
                    party SMS Gateways.</p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/sms-gateways.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-5">
                <p class="h5">2. Click Edit Action and Fill out all the necessary fields obtained from Nexmo.</p>
                <p>Goto Clickatell Dashboard to get these credentials. Please refer these docs for more,
                    <a href="https://www.clickatell.com/products/sms-api/" target="_blank">Clickatell Docs</a>. </p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/clickatell.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="infobip">
        <header class="docs-header">
            <h1 class="docs-heading">InfoBip Gateway - Paid<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="infobip-1">
            <h2 class="section-heading">Configure SMS Settings</h2>
            <div class="mb-5">
                <p class="h5">1. Goto SMS <a href="{{ route('user.gateway.sms.index') }}" target="_blank">Gateway Settings </a> and scroll down to see list of available third
                    party SMS Gateways.</p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/sms-gateways.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-5">
                <p class="h5">2. Click Edit Action and Fill out all the necessary fields obtained from Nexmo.</p>
                <p>Goto InfoBip Dashboard to get these credentials. Please refer these docs for more,
                    <a href="https://www.infobip.com/docs/sms/api" target="_blank">InfoBip Docs</a>. </p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/infobip.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="smsbroadcast">
        <header class="docs-header">
            <h1 class="docs-heading">SMS Broadcast Gateway - Paid<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="smsbroadcast-1">
            <h2 class="section-heading">Configure SMS Settings</h2>
            <div class="mb-5">
                <p class="h5">1. Goto SMS <a href="{{ route('user.gateway.sms.index') }}" target="_blank">Gateway Settings </a> and scroll down to see list of available third
                    party SMS Gateways.</p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/sms-gateways.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-5">
                <p class="h5">2. Click Edit Action and Fill out all the necessary fields obtained from Nexmo.</p>
                <p>Goto SMSBroadcast Dashboard to get these credentials. Please refer these docs for more,
                    <a href="https://smsbroadcast.com.au/developers/" target="_blank">SMSBroadcast Docs</a>. </p>
                <img src="/assets/docs/images/doc-pages/sms%20gateway/smsbroadcast.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <!--//docs-article-->
@endsection
