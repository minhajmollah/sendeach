@extends('layouts.doc-page')
@section('title', "SMS API")
@section('sidebar')
<ul class="section-items list-unstyled nav flex-column pb-3">
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#section-1"><span
                class="theme-icon-holder me-2"><i class="fa-brands fa-comment-o"></i></span>SMS API</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#item-1-1">Send Message</a></li>
</ul>
@endsection
@section('content')
<article class="docs-article" id="section-1">
    <header class="docs-header">
        <h1 class="docs-heading">SMS API <span class="docs-time">Last updated: 2023-02-17</span>
        </h1>
        <section class="docs-intro">
            <div class="alert alert-warning mt-2">
                <p>The SendEach SMS API should only be used for transactional purposes or in OTP applications.</p>
            </div>
            <div class="alert alert-info mt-2">
                <p>You need to have an API token ready, before you can start using our API.
                    If don't have a API Token ready, Click Here to learn how to create one.</p>
            </div>
      </section>
        <!--//docs-intro-->
    </header>
    <section class="docs-section" id="item-1-1">
        <h2 class="section-heading">Send Message</h2>

        <div class="mt-3 mb-5">
            <p class="h5">1. Generate an API POST request to the following url: https://sendeach.com/api/sms/send</p>
            <p class="h5">2. Add the API Token generated from dashboard as bearer token in the authorization header. See the below image for example in Postman application.</p>
            <img src="/assets/docs/images/doc-pages/sms-api-url-and-auth-token.jpg" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class=" mb-5">
            <p class="h5">3. Add the Accept and Content-Type header and set the value for both to "application/json". See the below image for example in Postman application.</p>
            <img src="/assets/docs/images/doc-pages/whatsapp-api-request-headers.jpg" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class=" mb-5">
            <p class="h5">4. The body of your request should consist of three attributes at minmum, number, message and schedule. See the below image for example in Postman application.</p>
            <ul>
                <li>Number(with country code) is the attribute that you want to send the SMS message to.</li>
                <li>Message is the content of your WhatsApp message.</li>
                <li>Schedule has two possible options [1, 2].</li>
                <li>Setting schedule to 1 will immediately send your SMS message.</li>
                <li>Setting schedule to 2 will send the message on the provided date in the schedule_date option.</li>
            </ul>
            <img src="/assets/docs/images/doc-pages/whatsapp-api-request-body.jpg" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class="">
            <p class="h5">5. You will receive the following response on successful request.  See the below image for example in Postman application.</p>
            <img src="/assets/docs/images/doc-pages/sms-api-response.jpg" alt="login" class="img-fluid mt-2 border">
        </div>
        <!--//docs-code-block-->
    </section>
    <!--//section-->
</article>
<!--//docs-article-->
@endsection
