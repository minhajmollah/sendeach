@extends('layouts.doc-page')
@section('title', "Support")
@section('sidebar')
    <ul class="section-items list-unstyled nav flex-column pb-3">
        <li class="nav-item section-title"><a class="nav-link scrollto active" href="#stmp"><span
                    class="theme-icon-holder me-2"><i class="fa fa-sms"></i></span>SendEach Support</a>
        </li>
        <li class="nav-item"><a class="nav-link scrollto" href="#Contact">Contact Form</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#Whatsapp_Contact">WhatsApp Contact</a></li>
        <li class="nav-item"><a class="nav-link scrollto" href="#ticket">Open Ticket</a></li>
    </ul>
@endsection
@section('content')
    <article class="docs-article" id="section-1">
        <header class="docs-header">
            <h1 class="docs-heading">SendEach Support<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
                <p>Sendeach offers excellent customer support, and welcomes feedback, issue reports, donations, and
                    feature requests, which can be submitted via WhatsApp, website contact form, or user dashboard
                    ticket.</p>
            </section>
            <!--//docs-intro-->
        </header>
        <!--//section-->
    </article>

    <article class="docs-article">
        <header class="docs-header">
            <h1 class="docs-heading">Customer Support<span class="docs-time">Last updated: 2023-05-07</span>
            </h1>
            <section class="docs-intro">
            </section>
            <!--//docs-intro-->
        </header>
        <section class="docs-section" id="Contact">
            <h2 class="section-heading">1. Contact Form</h2>
            <div class="mb-5">
                <p>Our customers can submit their queries through our convenient contact form on the website.</p>
                <img src="/assets/docs/images/doc-pages/support-gateway/Contact.png" alt="login"
                     class="img-fluid mt-2 border">
            </div>
        </section>
        <section class="docs-section" id="Whatsapp_Contact">
            <div class="mb-">
                <h2 class="section-heading">2. WhatsApp Contact </h2>
                <p>At Sendeach, we understand the importance of providing multiple channels of communication for our
                    customers.
                    Therefore, we offer the option for our customers to easily reach out to us through WhatsApp,
                    allowing for quick and efficient communication with our support team.</p>
            </div>
        </section>
        <section class="docs-section" id="ticket">
            <h2 class="section-heading">3. Open Ticket</h2>
            <p>Our customers can raise a support ticket for their Queries.</p>
            <p class="h5">1. Login in to your Account navigate to the Support Ticket section.</p>
            <img src="/assets/docs/images/doc-pages/support-gateway/Navigation.png" alt="login"
                 class="img-fluid mt-2 border">
            <br>
            <p class="h5">2. Click on the Support Ticket Link in the bottom right corner to raise you ticket.</p>
            <img src="/assets/docs/images/doc-pages/support-gateway/support_add.png" alt="login"
                 class="img-fluid mt-2 border">
            <p class="h5">3. Fill the form and click on Submit</p>
            <img src="/assets/docs/images/doc-pages/support-gateway/support_ticket.png" alt="login"
                 class="img-fluid mt-2 border">
            <p>Kindly await our response to your ticket after we have investigated your issue. We will also provide you
                with assistance via email or WhatsApp.</p>
            <!--//docs-code-block-->
        </section>

    </article>
@endsection
