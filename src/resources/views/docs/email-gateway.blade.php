@extends('layouts.doc-page')
@section('title', "Email Gateway")
@section('sidebar')
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#smtp"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>Custom SMTP</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#smtp-1">Configure SMTP</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#smtp-2">Test Configuration</a></li>
    </ul>

    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#sendgrid"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>SendGrid</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#sendgrid-1">Obtain SendGrid API Key</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#sendgrid-2">Configure SendGrid</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#sendgrid-3">Test Configuration</a></li>
    </ul>

    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#sparkpost"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>SparkPost</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#sparkpost-1">Obtain SparkPost Auth Key</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#sparkpost-2">Configure SparkPost</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#sparkpost-3">Test Configuration</a></li>
    </ul>
@endsection
@section('content')
    <article class="docs-article" id="section-1">
        <header class="docs-header">
            <h1 class="docs-heading">Email Gateway <span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
                <p>The Sendeach Email Gateway enables users to send marketing and authentication emails through their
                    own account for efficient communication.</p>
            </section>
            <!--//docs-intro-->
        </header>
        <!--//section-->
    </article>

    <article class="docs-article" id="smtp">
        <header class="docs-header">
            <h1 class="docs-heading">Custom SMTP<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="smtp-1">
            <h2 class="section-heading">Configure SMTP Settings</h2>
            <div class="mb-5">
                <p class="h5">1. Goto <a href="{{ route('user.mail.configuration') }}" target="_blank">Email
                        Configurations</a>.</p>
                <img src="/assets/docs/images/doc-pages/email-gateway/email-settings.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <div class="mb-">
                <p class="h5">2. Click Edit Action to configure SMTP Settings. </p>
                <p>Enter all of the SMTP credentials you obtained from your domain service provider or any other
                    source.</p>
                <img src="/assets/docs/images/doc-pages/email-gateway/smtp-settings.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>

        <section class="docs-section" id="smtp-2">
            <h2 class="section-heading">Test Your Configuration</h2>
            <div class="mb-5">
                <p class="h5">1. Scroll Down to bottom.</p>
                <p>Provide your email address to Receive the test mail.</p>
                <p>Upon submission, successful receipt of an error-free email indicates that the SMTP configuration is
                    functioning correctly
                    .
                </p>
                <img src="/assets/docs/images/doc-pages/email-gateway/smtp-test.png" alt="login"
                     class="img-fluid mt-2 border">
                <p>Please refer below image for Sample Test Mail</p>
                <img src="/assets/docs/images/doc-pages/email-gateway/smtp-test-1.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="sendgrid">
        <header class="docs-header">
            <h1 class="docs-heading">SendGrid API<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="sendgrid-1">
            <h2 class="section-heading">Obtain SendGrid API Key</h2>

            <div class="mb-">
                <p class="h5">1. Please register to SendGrid and goto developers docs on how to get SendGrid API Key.
                    <a href="https://docs.sendgrid.com/ui/account-and-settings/api-keys"
                       target="_blank">SendGrid Docs</a></p>
                <p></p>
                <img src="/assets/docs/images/doc-pages/email-gateway/smtp-settings.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>

        <section class="docs-section" id="sendgrid-2">
            <h2 class="section-heading">Configure SendGrid Settings</h2>

            <div class="mb-">
                <p class="h5">1. To configure the SendGrid Email Settings, locate SendGrid in the table and select the
                    edit action.</p>
                <p>To set up SendGrid in the Email Settings, provide the API Key acquired from SendGrid and specify the
                    From Address and From Name to be utilized as the sender's address.</p>
                <img src="/assets/docs/images/doc-pages/email-gateway/sendgrid-settings.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>

        <section class="docs-section" id="sendgrid-3">
            <h2 class="section-heading">Test Your Configuration</h2>
            <div class="mb-5">
                <p class="h5">1. Scroll Down to bottom.</p>
                <p>Provide your email address to Receive the test mail.</p>
                <p>Upon submission, successful receipt of an error-free email indicates that the SMTP configuration is
                    functioning
                    <correctly></correctly>
                    .
                </p>
                <img src="/assets/docs/images/doc-pages/email-gateway/send-grid-test.png" alt="login"
                     class="img-fluid mt-2 border">
                <p>Please refer below image for Sample Test Mail</p>
                <img src="/assets/docs/images/doc-pages/email-gateway/smtp-test-1.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>

        <!--//section-->
    </article>

    <article class="docs-article" id="sparkpost">
        <header class="docs-header">
            <h1 class="docs-heading">SparkPost API<span class="docs-time">Last updated: 2023-05-19</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="sparkpost-1">
            <h2 class="section-heading">Configure Sending Domain and Grab Your API/Auth Key</h2>

            <div class="mb-">
                <p class="h5">1. Please register to SparkPost and goto your dashboard.
                    <a href="https://app.sparkpost.com/"
                       target="_blank">SparkPost Login/Register</a></p>

                <p class="h5">2. Please configure your SparkPost Sending Domain. This domain will be used as a sender
                    address.</p>
                <img src="/assets/docs/images/doc-pages/email-gateway/sparkpost-domain.png" alt="sparkpost domain"
                     class="img-fluid mt-2 border">

                <p class="h5">3. Please create your API Key by clicking Create API Key.</p>
                <img src="/assets/docs/images/doc-pages/email-gateway/sparkpost-api-key.png" alt="sparkpostAPI Key"
                     class="img-fluid mt-2 border">
                <p>Make sure You leave IP address as blank and give check API Permissions to All.</p>

                <img src="/assets/docs/images/doc-pages/email-gateway/sparkpost-api-key_fill.png" alt="sparkpostAPI Key"
                     class="img-fluid mt-2 border">

                <p>Copy the generated API Key.</p>

            </div>
            <!--//docs-code-block-->
        </section>

        <section class="docs-section" id="sparkpost-2">
            <h2 class="section-heading">Configure SparkPost Settings</h2>

            <div class="mb-">
                <p class="h5">1. To configure the SparkPost Email Settings, locate SparkPost in the Gateway table and
                    select the edit action Icon.</p>
                <img src="/assets/docs/images/doc-pages/email-gateway/sparkpost-sparkpost.png" alt="login"
                     class="img-fluid mt-2 border">
                <p>To set up SparkPost in the Email Settings, provide the API Key in Authentication Token acquired from SparkPost and specify
                    the From Address and From Name to be utilized as the sender's address.</p>
            </div>
            <div class="alert alert-warning">
                Please ensure that the domain in the email address you are sending from matches the configured sending domain in SparkPost.</div>
            <!--//docs-code-block-->
        </section>

        <section class="docs-section" id="sparkpost-3">
            <h2 class="section-heading">Test Your Configuration</h2>
            <div class="mb-5">
                <p class="h5">The testing procedure for this email gateway is the same as the procedures for the other email gateways that have been mentioned.</p>
            </div>
            <!--//docs-code-block-->
        </section>

        <!--//section-->
    </article>

@endsection
