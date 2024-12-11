@extends('layouts.doc-page')
@section('title', "Developers API")
@section('sidebar')
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#whatsapp-api"><span
                    class="theme-icon-holder me-2"><i class="fa-solid fa-tower-broadcast"></i></span>Whatsapp API</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#whatsapp-1">Choose Default Gateway</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#whatsapp-2">Send Whatsapp</a></li>
    </ul>
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#sms-api"><span
                    class="theme-icon-holder me-2"><i class="fa-solid fa-tower-broadcast"></i></span>SMS API</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#sms-1">Set Default Gateway</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#sms-2">Send SMS</a></li>
    </ul>
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#email-api"><span
                    class="theme-icon-holder me-2"><i class="fa-solid fa-tower-broadcast"></i></span>Email API</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#email-1">Set Default Gateway</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#email-2">Send Email</a></li>
    </ul>
@endsection
@section('content')

    <article class="docs-article">
        <header class="docs-header">
            <h1 class="docs-heading">Introduction <span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
                <p>Integrate Sendeach's marketing or OTP messaging services into your apps or websites using our API. To
                    do this, developers will require a Sendeach Authentication Key (refer to our <a href="{{ route('docs.api-token') }}" target="_blank">
                        documentation on User
                    Authentication Key</a>) and can access our APIs.</p>
                <div class="alert alert-success">Kindly download our  <a href="{{ route('docs.api.postman') }}">
                        Postman Collection</a> which contains all the APIs mentioned below for your reference.
                </div>
            </section>
            <!--//docs-intro-->
        </header>
    </article>

    <article class="docs-article" id="whatsapp-api">
        <header class="docs-header">
            <h1 class="docs-heading">Whatsapp API <span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
                <div class="alert alert-info">To use our API, users need to register and log into the SendEach
                    dashboard, and set up their default sending gateway in the developer module they plan to use.
                </div>
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="whatsapp-1">
            <h2 class="section-heading">Set Default Whatsapp Gateway</h2>
            <div class="mb-5">
                <p class="h5">1. Set up Your Whatsapp Default Gateway.</p>
                <p>Please consult our Whatsapp Gateway documentation to learn how to set up your preferred gateway,
                    which will be utilized for sending messages through our API for developers.</p>
                <p><b>Note: </b>This API is only Applicable to Web and Desktop Gateway.</p>
                <img src="/assets/docs/images/doc-pages/developers-api/whatsapp-settings.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>

            <!--//docs-code-block-->
        </section>
        <!--//section-->

        <section class="docs-section" id="whatsapp-2">
            <h2 class="section-heading">Send Whatsapp Web/Desktop Message</h2>

            <div class="mb-5">
                <p class="h5">Request Syntax</p>
                <x-code id="whatsapp-send">
curl -X POST --location '{{ route('api.whatsapp.web.send') }}' \
     --header 'Accept: application/json' \
     --data '{
                 "recipients": {recipients},
                 "whatsapp_device": {whatsapp-device}, // Optional, if nothing givens then random device is chosen and sent
                 "message": {message}
             }'
                </x-code>
                <b>Parameters Placeholders</b>
                <p class="mt-2"><span class="text-success">{recipients}</span> - Array of recipient phone numbers with country code. Ex: ['12334566677', '12312312112']</p>
                <p class="mt-2"><span class="text-success">{whatsapp-device}</span> - Whatsapp Device ID [Integer]. (Optional)</p>
                <p class="mt-2"><span class="text-success">{message}</span> - Text Message to send.</p>
            </div>

            <div class="mb-5">
                <p class="h5">Sample Response</p>
                <x-code id="whatsapp-response">
{
    "status": "success",
    "data": {
        "log_ids": [
            {
                "whatsapp_id": 5,
                "to": "9876543210",
                "initiated_time": "2023-05-01T08:24:35.973291Z",
                "message": "Hello User",
                "status": 1,
                "id": 319
            },
            ...
        ]
    }
}
                </x-code>
            </div>
            <p class="h5">Status Value can be used to track message delivery status:</p>
            <p class="mt-2"><span class="text-success">status - {{ \App\Models\WhatsappLog::PENDING }}</span>  -> Pending</p>
            <p class="mt-2"><span class="text-success">status - {{ \App\Models\WhatsappLog::SCHEDULE }}</span>  -> Scheduled to send later.</p>
            <p class="mt-2"><span class="text-success">status - {{ \App\Models\WhatsappLog::FAILED }}</span>  -> Failed to send.</p>
            <p class="mt-2"><span class="text-success">status - {{ \App\Models\WhatsappLog::SUCCESS }}</span>  -> Successfully Sent</p>
            <p class="mt-2"><span class="text-success">status - {{ \App\Models\WhatsappLog::PROCESSING }}</span>  -> Processing</p>
            <!--//docs-code-block-->
        </section>
        <!--//section-->

    </article>

    <article class="docs-article" id="sms-api">
        <header class="docs-header">
            <h1 class="docs-heading">SMS API <span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
                <div class="alert alert-warning mt-2">
                    <p>The SendEach SMS API should only be used for transactional purposes or in OTP applications.</p>
                </div>
                <div class="alert alert-info mt-2">
                    <p>You need to have an API token ready, before you can start using our API.
                        If don't have a API Token ready, <a href="{{ route('docs.api-token') }}" target="_blank"> Click Here</a> to learn how to create one.</p>
                </div>
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="sms-1">
            <h2 class="section-heading">Set Default SMS Gateway</h2>
            <div class="mb-5">
                <p class="h5">1. Set up Your SMS Default Gateway.</p>
                <p>This will be the gateway all the messages will be sent from the SMS API.</p>
                <img src="/assets/docs/images/doc-pages/developers-api/sms-default-gateway.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>

        <section class="docs-section" id="sms-2">
            <h2 class="section-heading">Send Message</h2>

            <div class="mb-5">
                <p class="h5">Request Syntax</p>
                <x-code id="whatsapp-send">
curl --location '{{ route('api.app.send_sms') }}' \
    --header 'Accept: application/json' \
    --data '{
        "device_id": {device_id},
        "recipients": {recipients},
        "message": {message},
    }'
                </x-code>
                <b>Parameters Placeholders</b>
                <p class="mt-2"><span class="text-success">{recipients}</span> - Array of recipient phone numbers with country code. Ex: ['12334566677', '12312312112']</p>
                <p class="mt-2"><span class="text-success">{device_id}</span> - Device ID [Integer] to use as a sender. (Optional)</p>
                <p class="mt-2"><span class="text-success">{message}</span> - Actual Message Text Content to send.</p>
            </div>

            <div class="mb-5">
                <p class="h5">Sample Response</p>
                <x-code id="whatsapp-response">
{
    'status': "success" ,
    'message': "New SMS request sent, please see in the SMS history for final status" ,
}
                </x-code>
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>

    <article class="docs-article" id="email-api">
        <header class="docs-header">
            <h1 class="docs-heading">Email API <span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="email-1">
            <h2 class="section-heading">Set Default Email Gateway</h2>
            <div class="mb-5">
                <p class="h5">1. Set up Your Email Default Gateway.</p>
                <p>This will be the gateway all the messages will be sent from the Email API.</p>
                <img src="/assets/docs/images/doc-pages/developers-api/email-default.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
            <!--//docs-code-block-->
        </section>

        <section class="docs-section" id="email-2">
            <h2 class="section-heading">Send Message</h2>

            <div class="mb-5">
                <p class="h5">Request Syntax</p>
                <x-code id="whatsapp-send">
curl -X POST --location '{{ route('api.email.send') }}' \
             --header 'Accept: application/json' \
             --data '{
                    "email": {emails},
                    "message": {message},
                    "subject": {subject},
                    "from_name": {from-name}, // optional
                    "reply_to_email": {reply-to-email} // optional
            }'
                </x-code>
                <b>Parameters Placeholders</b>
                <p class="mt-2"><span class="text-success">{emails}</span> - Array of recipient Emails. Ex: ['email1@gmail.com', 'email2@email.com']</p>
                <p class="mt-2"><span class="text-success">{message}</span> - Actual Message Text Content to send. It can be HTML.</p>
                <p class="mt-2"><span class="text-success">{subject}</span> - Subject of the email.</p>
                <p class="mt-2"><span class="text-success">{from_name}</span> - Sender From Name.</p>
                <p class="mt-2"><span class="text-success">{reply_to_email}</span> - Reply To email.</p>
            </div>

            <div class="mb-5">
                <p class="h5">Sample Response</p>
                <x-code id="whatsapp-response">
{
    'status': 'success' ,
    'message': "New Email request sent, please see in the Email history for final status"
}
                </x-code>
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>
    <!--//docs-article-->
@endsection
