@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix . '.layouts.app')

@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                <div class="col-xl-12">
                    <div class="table_heading d-flex align--center justify--between">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a
                                        href="{{ route($routePrefix . '.dashboard') }}">{{ translate('Dashboard') }}</a></li>
                                <li class="breadcrumb-item" aria-current="page"> {{ translate('Web Bots') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" class="row align-items-center"
                                action="{{ route($routePrefix . '.ai_bots.generate.share_link') }}">
                                @CSRF
                                <div class="col-xl-6 col-sm-12">
                                    <label class="text-dark fw-bold" for="share_name">Create the URL for your Web
                                        Bot.</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <span class="input-group-text">https://sendeach.com/chat/</span>
                                        </div>
                                        <input type="text" name="share_name" class="form-control" placeholder="URL Name"
                                            id="share_name">
                                        <button type="submit" class="btn btn--primary input-group-append">
                                            @if (isset($shareLink))
                                                Generate New URL
                                            @else
                                                Generate
                                            @endif
                                        </button>
                                    </div>
                                </div>
                                @if (isset($shareLink))
                                    <div class="col-xl-6 col-sm-12 p-3">
                                        <label class="text-dark fw-bold" for="chatbot_url">ChatBot URL</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $shareLink }}"
                                                id="chatbot_url" aria-describedby="basic-addon2" readonly="">
                                            <div class="input-group-append pointer">
                                                <span class="input-group-text bg--success text--light" id="basic-addon2"
                                                    onclick="copyAccessToken('chatbot_url')">Copy</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <p class="text-muted">Generate Your New Chat Bot URL to be shared with anyone.</p>
                                <p class="text-muted">Share this link to anyone to initiate a chat with your bot.</p>
                            </form>
                        </div>
                    </div>

                    {{--                    <div class="col-12 mt-4 p-3"> --}}
                    {{--                        <label for="greetings_text" class="h6 text-dark fw-bold">Greetings Text</label> --}}
                    {{--                        <p class="text-muted">Set the Greetings text to welcome your users. --}}
                    {{--                        </p> --}}
                    {{--                        <form --}}
                    {{--                            action="{{ route($routePrefix.'.ai_bots.greetings_text.update') }}" --}}
                    {{--                            method="POST" --}}
                    {{--                            class="form-inline mt-2 col-12 float-sm-right text-start row"> --}}
                    {{--                            @csrf --}}
                    {{--                            @method('PUT') --}}
                    {{--                            <div class="col col-md-12"> --}}
                    {{--                                                    <textarea id="greetings_text" name="greetings_text" --}}
                    {{--                                                              class="form-control">{{ @$greetingsText }}</textarea> --}}
                    {{--                                <p class="small text-muted mt-2">Note: Below Text will be appended at --}}
                    {{--                                    your greeting text.</p> --}}
                    {{--                                <p class="text-muted fw-bold">Chatting will be assisted by SendEach --}}
                    {{--                                    AI Chatbot. --}}
                    {{--                                </p> --}}
                    {{--                            </div> --}}
                    {{--                            <div class="col-12 mt-2"> --}}
                    {{--                                <button type="submit" class="btn-primary btn">Save</button> --}}
                    {{--                            </div> --}}
                    {{--                        </form> --}}
                    {{--                    </div> --}}
                </div>

                <div class="col-12 p-3">
                    <div class="card">

                        <div class="card-body">
                            Incorporate our WordPress plugin into your WordPress website to seamlessly integrate your AI
                            web bot into your site.


                        </div>
                    </div>
                </div>

            </div>

            <div class="col-12 p-3">
                <div class="card">

                    @php
                        
                        if ($token !== '') {
                            $tokenCode = $token->plainTextToken;
                        } else {
                            $tokenCode = 'API_KEY';
                        }
                        
                    @endphp
                    <div class="card-body" id="generated-code">
                        <label class="text-dark fw-bold" for="chatbot_url">Web Chat Code</label>
                        <x-code id="webchat-code"
                            language="html">{{ implode(
                                "\n",
                                array_map(
                                    function ($line) {
                                        return preg_replace('/\s+/', ' ', trim($line));
                                    },
                                    explode(
                                        "\n",
                                        '
                                                                                                                                                                                                                                <button class="btn text-decoration-none whatsapp_float p-0 floating-button" id="sendeach-chats-button">
                                                                                                                                                                                                                                    <i class="bi bi-chat-text" style="font-size: 30px"></i>
                                                                                                                                                                                                                                </button>
                                                                                                                                                                                                                                <script src="' .
                                            route('plugins.chat.js', ['access_token' => $tokenCode]) .
                                            '"></script>
                                                                                                                                                                                                                            ',
                                    ),
                                ),
                            ) }}</x-code>




                    </div>
                    <div class="card-footer">
                        Insert the provided code into the body tag of your website's source code to enable and
                        utilize the ChatBot on your website.

                        <a href="{{ route('user.sanctum-token.index') }}" target="_blank" class="link-primary link">Generate
                            your API Token.</a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    @pushonce('scriptpush')
        <script>
            let originalWebCode;



            function generateCode() {
                if (!$("#api_key").val()) {
                    $("#generated-code").hide()
                    return;
                }

                $("#generated-code").show()
            }

            function setAPIToken(e) {
                if (!originalWebCode) {
                    originalWebCode = $("#webchat-code").html();
                }

                if ($(e).val()) {
                    $("#webchat-code").html(originalWebCode.replace('API_KEY', $(e).val()))
                } else {
                    $("#generated-code").hide()
                }

            }

            function copyAccessToken(e) {
                let copyText = document.getElementById(e);
                copyText.select();
                copyText.setSelectionRange(0, 99999)
                document.execCommand("copy");
                notify('success', "copied!");
            }
        </script>
    @endpushonce
@endsection
