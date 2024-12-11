@extends('layouts.doc-page')
@section('title', "Whatsapp Business Channel")
@section('sidebar')
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#section-1"><span
                    class="theme-icon-holder me-2"><i class="fa-brands fa-whatsapp"></i></span>Whatsapp Business Channel</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#item-1-2">Create Business App</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#item-1-3">Create Whatsapp Account</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#item-1-4">Create System User Access Token</a></li>
    </ul>
@endsection
@section('content')
    <article class="docs-article" id="section-1">
        <header class="docs-header">
            <h1 class="docs-heading">WhatsApp Business Channel <span class="docs-time">Last updated: 2023-04-23</span>
            </h1>
            <section class="docs-intro">
                <div class="alert alert-warning mt-2">
                    <p>We are not responsible for any messages sent through this channel. You may be banned by meta if
                        they found this
                        business channel is used for illegitimate purpose.</p>
                </div>
                <div class="alert alert-info mt-2">
                    <p>Please create your meta business account first to start the process. Goto <a target="__blank"
                                                                                                    href="https://business.facebook.com/">
                            Create Meta Business Account</a>
                        and use your personal facebook account to create your business account.
                    </p>
                </div>
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="item-1-1">
            <h2 class="section-heading" id="item-1-2">Creating Business App from Facebook developers</h2>

            <div class="mt-3 mb-5">
                <p class="h5">1. Goto <a href="https://developers.facebook.com/apps/" target="__blank">Facebook
                        Developer</a>
                    and Login with your facebook developer account if not.</p>
                <p class="text-secondary">If there is no developer account, Please create it during login.</p>
                <p class="h5">2. Goto All Apps if not. Click Create App Button.</p>
                <img src="/assets/docs/images/doc-pages/business/create-app.png" alt="Click Create App"
                     class="img-fluid mt-2 border">
            </div>
            <div class=" mb-5">
                <p class="h5">3. Please choose the App Type as Business.</p>
                <img src="/assets/docs/images/doc-pages/business/app-type.png" alt="App Type"
                     class="img-fluid mt-2 border">
            </div>
            <div class=" mb-5">
                <p class="h5">4. Please fill out all the necessary fields carefully.</p>
                <p>Please select the business account you created during start of process. Click Create App to finish
                    the process.</p>
                <p>After you successfully added the whatsapp product app. you can access it products left side menu.</p>
                <img src="/assets/docs/images/doc-pages/business/app-details.png" alt="App Details"
                     class="img-fluid mt-2 border">
            </div>
            <div class=" mb-5">
                <p class="h5">5. You will be redirected to App Dashboard. If not goto My Apps and click your App you
                    created.</p>
                <p>You will See your dashboard similar to below.</p>
                <p>Please scroll to bottom and Add Whatsapp Product by clicking Set up button on whatsapp product
                    card.</p>
                <p>Please refer to screenshots below.</p>
                <img src="/assets/docs/images/doc-pages/business/app-dashboard.png" alt="App Dashboard"
                     class="img-fluid mt-2 border">
            </div>
            <div class=" mb-5">
                <p class="h5">6. Verify your business.</p>
                <li>Please goto Basic settings from side menu dropdown.</li>
                <li>Fill out all the fields accurately.</li>
                <img src="/assets/docs/images/doc-pages/business/business-verification.png" alt="Verify your business"
                     class="img-fluid mt-2 border">
                <li>Scroll down and start a verification process by clicking verify button. It may take upto 1 day or
                    less.
                </li>
                <p>Once verified you will see similar below status.</p>
                <img src="/assets/docs/images/doc-pages/business/business-verification1.png" alt="Verify your business"
                     class="img-fluid mt-2 border">
            </div>
            <div class=" mb-5" id="item-1-3">
                <p class="h5">6. Create Whatsapp Business Profile and Create Whatsapp Business Account.</p>
                <img src="/assets/docs/images/doc-pages/business/whatsapp-getting-started.png"
                     alt="Whatsapp Getting Started"
                     class="img-fluid mt-2 border">
                <ul>
                    <li>Click Add Phone Number</li>
                    <li>Create your Whatsapp Business Profile by filling out necessary fields carefully.</li>
                    <li>Please click next and add your new phone number that is not used in whatsapp and verify it.</li>
                    <img src="/assets/docs/images/doc-pages/business/whatsapp-business-profile.png"
                         alt="Whatsapp Add Phone Number"
                         class="img-fluid mt-2 border">
                    <li>Finally Add Payment Method to start sending your message.</li>
                    <li>You will be redirected business settings on clicking Add phone number.</li>
                    <li>Click the Account and goto settings tab and click payment settings to add payment.</li>
                </ul>
                <img src="/assets/docs/images/doc-pages/business/payment-settings.png" alt="Whatsapp Add Phone Number"
                     class="img-fluid mt-2 border">
                <div class="alert alert-warning mt-2">
                    <p>Payment method is required to start sending messages through business channel.</p>
                </div>
            </div>
            <div class=" mb-5" id="item-1-4">
                <p class="h5">7. Create System User Access Token and Add whatsapp business account to your SendEach
                    Account.</p>
                <img src="/assets/docs/images/doc-pages/business/whatsapp-getting-started.png"
                     alt="Whatsapp Getting Started"
                     class="img-fluid mt-2 border">
                <ul>
                    <li>Goto <a href="https://business.facebook.com/settings/system-users">System users</a> under Users
                        Menu.
                    </li>
                    <li>Click Add button.</li>
                    <li>set any appropriate system user name.</li>
                    <li>Set Role of your wish.</li>
                    <li>After creating it. Click Add Asset to add your app to this system user.</li>
                    <img src="/assets/docs/images/doc-pages/business/sytem-user.png" alt="Whatsapp Add Phone Number"
                         class="img-fluid mt-2 border">
                    <li>Assign the created app previously to this system user.</li>
                    <img src="/assets/docs/images/doc-pages/business/assign%20app.png" alt="Whatsapp Add Phone Number"
                         class="img-fluid mt-2 border">
                    <li>Click Generate new Access Token.</li>
                    <li>Select the app and set token expiration to permanent.</li>
                    <li>Select these permissions: <i>business_management, whatsapp_business_messaging,
                            whatsapp_business_management</i>.
                    </li>
                    <li>After clicking Generate Token. Please copy the token and goto
                        <a href="{{ route('user.business.whatsapp.account.create') }}">SendEach Whatsapp Business
                            Account</a>
                    </li>
                    <li>Paste it in System User Access Token field and click submit to add the token to your SendEach
                        token.
                    </li>
                    <img src="/assets/docs/images/doc-pages/business/sendeach-user-access-token.png"
                         alt="Whatsapp Add Phone Number"
                         class="img-fluid mt-2 border">
                    <li>Your SendEach account will be synced with Meta, and You will see your phone numbers added from
                        meta.
                    </li>
                    <li>You can goto <a href="{{ route('user.business.whatsapp.template.index') }}">Whatsapp
                            Templates</a> to create and manage your templates.
                    </li>
                    <li>You can start sending messages immediately only using Message templates by going to this link
                        <a href="{{ route('user.business.whatsapp.create') }}">Start Sending Business Whatsapp
                            Messages</a>or navigating to side menu
                    </li>
                </ul>
                <p>You will get sample templates to test sending messages. <b>Note:</b> You will be charged for each
                    message you send.</p>
                <p>The Access tokens are securely saved in SendEach Server and No one will be able to access it except
                    you.</p>
            </div>
            <!--//docs-code-block-->
        </section>
        <!--//section-->
    </article>
    <!--//docs-article-->
@endsection
