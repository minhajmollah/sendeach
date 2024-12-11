@extends('layouts.doc-page')
@section('title', "Authentication")
@section('sidebar')
<ul class="section-items list-unstyled nav flex-column pb-3">
    <li class="nav-item section-title"><a class="nav-link scrollto active" href="#section-1"><span
                class="theme-icon-holder me-2"><i class="fa-solid fa-lock"></i></span>Authentication</a>
    </li>
    <li class="nav-item"><a class="nav-link scrollto" href="#item-1-1">Login</a></li>
    <li class="nav-item"><a class="nav-link scrollto" href="#item-1-2">Register</a></li>
</ul>
@endsection
@section('content')
<article class="docs-article" id="section-1">
    <header class="docs-header">
        <h1 class="docs-heading">Authentication <span class="docs-time">Last updated: 2023-02-17</span>
        </h1>
        <section class="docs-intro">
            <p>SendEach application allows for the generation of API token to connect your third-party applicationw with our server and consume API resources.</p>
        </section>
        <!--//docs-intro-->




    </header>
    <section class="docs-section" id="item-1-1">
        <h2 class="section-heading">Login</h2>

        <div class="mt-3 mb-5">
            <p class="h5">1. Click on the Login button in the header</p>
            <img src="/assets/docs/images/doc-pages/1-login.png" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class="mb-5">
            <p class="h5">2. Enter your active WhatsApp number to login</p>
            <p><em>You'll receive an OTP code on your provided number.</em></p>
            <img src="/assets/docs/images/doc-pages/2-enter-whtsapp-num.png" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class="mb-5">
            <p class="h5">3. Enter the received OTP code</p>
            <img src="/assets/docs/images/doc-pages/3-varify-otp-code.png" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class="mb-5">
            <p class="h5">4. If OTP is correct, you will be successfully logged In and see the below screen.</p>
            <img src="/assets/docs/images/doc-pages/4-user-dashboard.png" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class="">
            <p class="h5">5. To Logout, Click on your profile Image(In the top right corner) and select the Logout
                option</p>
            <img src="/assets/docs/images/doc-pages/5-logout or apitoken.png" alt="login" class="img-fluid mt-2 border">
        </div>


        <!--//docs-code-block-->

    </section>
    <!--//section-->

    <section class="docs-section" id="item-1-2">
        <h2 class="section-heading">Register</h2>

        <div class="mt-3 mb-5">
            <p class="h5">1. Click on the Signup button in the header</p>
            <img src="/assets/docs/images/doc-pages/1-login.png" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class=" mb-5">
            <p class="h5">2. Enter your name, your active WhatsApp account number, select your prefered package respectively and click the button to register</p>
            <img src="/assets/docs/images/doc-pages/1.signup-reg.png" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class="mb-5">
            <p class="h5">3. Enter the received OTP code</p>
            <img src="/assets/docs/images/doc-pages/3-varify-otp-code.png" alt="login" class="img-fluid mt-2 border">
        </div>
        <div class="mb-5">
            <p class="h5">4. If OTP is correct, you will be successfully registered and see the below screen.</p>
            <img src="/assets/docs/images/doc-pages/4-user-dashboard.png" alt="login" class="img-fluid mt-2 border">
        </div>
    </section>
    <!--//section-->

</article>
<!--//docs-article-->
@endsection
