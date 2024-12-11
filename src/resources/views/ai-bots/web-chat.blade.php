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
    <link href="/assets/landing_page/img/favicon-send.png" rel="icon" />
    <link href="/assets/landing_page/img/favicon-send.png" rel="apple-touch-icon" />

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

    <link href="{{ asset('/assets/landing_page/vendor/aos/aos.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/landing_page/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/landing_page/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/landing_page/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/landing_page/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/landing_page/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/landing_page/css/style.css') }}" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>


    <style>
        #chat2 .form-control {
            border-color: transparent;
        }

        #chat2 .form-control:focus {
            border-color: transparent;
            box-shadow: inset 0px 0px 0px 1px transparent;
        }

        .divider:after,
        .divider:before {
            content: "";
            flex: 1;
            height: 1px;
            background: #eee;
        }

        /* The popup chat - hidden by default */
        .form-popup {
            z-index: 9;
            width: 100%;
        }

        @keyframes float {
            0% {
                box-shadow: 0 5px 15px 0px rgba(0, 0, 0, 0.6);
                transform: translatey(0px);
            }

            50% {
                box-shadow: 0 25px 15px 0px rgba(0, 0, 0, 0.2);
                transform: translatey(-20px);
            }

            100% {
                box-shadow: 0 5px 15px 0px rgba(0, 0, 0, 0.6);
                transform: translatey(0px);
            }
        }

        .floating-button {
            transform: translatey(0px);
            animation: float 6s ease-in-out infinite;
        }
    </style>


</head>

<body>

    <div class="container-fluid p-4 vh-100">
        <div class="row gap-4 h-100 justify-content-center">
            <!-- <div class="col-lg-4 col-12 col h-100 row gap-4">
            <div class="card p-0">
                <div class="card-header row justify-content-between p-3">
                    <h5 class="col-lg-6 col">About {{ ucfirst(request('user')) }}</h5>
                    @if ($aiBot->data['website'] ?? null)
<h5 class="col-lg-6 col"><a href="{{ $aiBot->data['website'] }}" target="_blank">
                                {{ $aiBot->data['website'] }}
                            </a></h5>
@endif
                </div>
                <div class="card-body">
                    <p class="text-muted"> {{ $aiBot->data['business_text'] ?? 'No Information' }} </p>
                </div>
            </div>

            <div class="card p-0">
                <div class="card-header d-flex  justify-content-between p-3">
                    <h5 class="mb-2">About SendEach</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"> {{ @$adminBusinessInfo ?: 'No Information' }} </p>
                </div>
            </div>
        </div> -->
            <div class="col-lg-6 col-12 order-first order-lg-last col h-100">
                <div class="h-100 form-popup" id="sendeach-chat-popup">
                    <div class="h-100 w-100">
                        <div class="card w-100 h-100" id="chat2">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 d-flex"><span>Chat With {{ ucfirst(request('user')) }} </span>
                                </h5>
                                <div class="logo">
                                    <h1 class="text-light">
                                        <a target="__blank" href="{{ route('home') }}">
                                            <img src="/assets/landing_page/img/logo-navbar.png" height="45px"
                                                alt="logo">
                                        </a>
                                    </h1>
                                </div>
                            </div>
                            <div class="card-body" id="sendeach-chats"
                                style="position: relative; overflow-y: scroll; height: 400px"></div>
                            <div class="card-footer ">
                                <div class="d-flex align-items-center gap-2 justify-content-between">
                                    <div class="w-100">
                                        <input type="text" class="form-control" id="sendeach-chat-message"
                                            placeholder="Type message">
                                    </div>
                                    <div style="width: 60px">
                                        <button id="chat_send_message_button"
                                            class="btn m-auto p-2 bg-success rounded-circle text-muted  d-flex justify-content-center align-items-center"
                                            onclick="sendMessage()" style="width: 32px; height: 32px;">
                                            <i class="bi bi-send text-white"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        @include('partials.chat-js')

        sendeach_chat_popup = $("#sendeach-chat-popup");
        sendeach_chat_btn = $("#sendeach-chats-button");
        sendeach_chats = sendeach_chat_popup.find("#sendeach-chats");
        sendeach_messages = sendeach_chat_popup.find("#sendeach-chat-message");
        sendeach_send_message_button = sendeach_chat_popup.find("#chat_send_message_button");

        (async function() {

            if (!sendeach_chats.html()) {
                let messages = await getMessages();

                console.log(messages)

                messages.data.reverse()

                renderMessages(messages.data);
            }

            // setInterval(unreadMessages, 10000)
        })()

        sendeach_messages.on("keypress", function onEvent(event) {
            if (event.key === "Enter") {
                sendMessage()
            }
        });
    </script>

</body>

</html>
