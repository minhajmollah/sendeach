@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix.'.layouts.app')

@section('panel')
    <x-facebook-login></x-facebook-login>
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                <div class="col-xl-12">
                    <div class="table_heading d-flex align--center justify--between">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a
                                        href="{{route($routePrefix.'.dashboard')}}">{{ translate('Dashboard')}}</a></li>
                                <li class="breadcrumb-item" aria-current="page"> {{ translate('Messenger Bots')}}</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <h6 class="card-title text-center text-light">{{ translate('Facebook Messenger Bot Configurations')}}</h6>
                        </div>
                        <div class="card-body row gap-2">
                            <a href="#" class="single_pinned_project shadow my-3 col-lg-4 col-md-6 ">
                                <div class="pinned_icon">
                                    <i class="lab la-facebook-messenger"></i>
                                </div>
                                <div class="pinned_text">
                                    <div>
                                        <h6>Total Conversations</h6>
                                        <p>{{ @$totalConversations }}</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="single_pinned_project shadow my-3 col-lg-4 col-md-6 ">
                                <div class="pinned_icon">
                                    <i class="lab la-facebook-messenger"></i>
                                </div>
                                <div class="pinned_text">
                                    <div>
                                        <h6>Facebook Messenger Bot Status</h6>
                                        <p class="fw-bold">{{ $facebookMessenger?->status ? 'Active' : 'In Active' }}</p>
                                    </div>
                                </div>
                            </a>

                            <p class="text-muted fs-6">
                                To use this platform, please set up your Facebook page by connecting it here. Ownership
                                of a Facebook page is required for this process. Once your page is connected, you will
                                be able to assign an OpenAI bot, which will handle all incoming messages for you.
                            </p>

                            @if($routePrefix == 'admin')
                                <div class="col-lg-12 alert alert-info">

                                    <p class="fs-6 text-muted">Facebook Page Messenger has not been Bot Not been
                                        Initialized. Please
                                        Initialize it to use it.</p>
                                    <form action="{{ route($routePrefix.'.facebook.messenger.initialize') }}"
                                          method="POST"
                                          class="row mt-3 gap-2 align-items-center">
                                        @csrf
                                        <div class="col-md-6">
                                            <label for="page_id" class="form-label">Your Facebook Page ID</label>
                                            <input type="text" class="form-control"
                                                   placeholder="Facebook Page ID (Optional)" name="page_id"
                                                   value="{{ $facebookMessenger?->page_id }}"
                                                   id="page_id">
                                            <p class="text-muted small mt-1">If Not given then first available page will
                                                be used.</p>
                                        </div>
                                        <div class="col-md-4 ">
                                            @if(!$facebookMessenger?->status)
                                                <button type="submit"
                                                        class="btn btn--primary text--light border-0 px-4 py-2 rounded">
                                                    Initialize
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="btn btn--success text--light border-0 px-4 py-2 rounded">
                                                    Re Initialize
                                                </button>
                                            @endif
                                            <button type="button" data-bs-target="#disconnect"
                                                    data-bs-toggle="modal"
                                                    class="btn--danger btn">Disconnect your page
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                {{--                                <div class="alert alert-warning">--}}
                                {{--                                    @if(!$facebookMessenger?->page_access_token || $facebookMessenger?->status)--}}
                                {{--                                        <p class="fs-6 text-muted col">Facebook Page Messenger has not been Bot Not--}}
                                {{--                                            been--}}
                                {{--                                            Initialized. Please--}}
                                {{--                                            Connect your page it to use it.</p>--}}
                                {{--                                    @endif--}}
                                {{--                                </div>--}}
                                <div
                                    class="col-lg-12 alert @if($facebookMessenger?->status) alert-success @else alert-info @endif">
                                    <div class="row gap-2 justify-content-between">
                                        @if($facebookMessenger?->status)
                                            <p class="col-12 fs-6 text-muted">
                                                Congratulations! Your Facebook Page has been successfully connected to
                                                sendEach, allowing messages to be handled by your default AI bot.
                                            </p>
                                        @else
                                            <p class="col-12 fs-6 text-muted">Connect your facebook page to initialize
                                                the
                                                messenger bot and start using it</p>
                                        @endif
                                        <div class="col">
                                            <button class="btn btn--primary"
                                                    onclick="login({{config('facebook_messenger.configuration_id')}})">
                                                Connect/Reconnect Your Page
                                            </button>
                                            @if($facebookMessenger?->status)
                                                <button type="button" data-bs-target="#disconnect"
                                                        data-bs-toggle="modal"
                                                        class="btn--danger btn">Disconnect Your Page
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($facebookMessenger?->status)
                                <div class="row col-12 mt-4">
                                    <h5 class="h5">Page Details</h5>
                                    <div class="col-4 p-3">
                                        <h6 class="text-dark fw-bold">Name</h6>
                                        <p class="text-muted small">{{ $facebookMessenger?->data['page']['name'] ?? '-' }}</p>
                                    </div>
                                    <div class="col-4 p-3">
                                        <h6 class="text-dark fw-bold">ID</h6>
                                        <a target="_blank"
                                           href="{{ "https://www.facebook.com/profile.php?id=$facebookMessenger?->page_id" }}"
                                           class="small link-primary">{{ $facebookMessenger?->page_id ?: '-' }}</a>
                                    </div>
                                    <div class="col-4 p-3">
                                        <h6 class="text-dark fw-bold">Category</h6>
                                        <p class="text-muted small">{{ $facebookMessenger?->data['page']['category'] ?? '-' }}</p>
                                    </div>
                                    <div class="col-6 p-3">
                                        <h6 class="text-dark fw-bold">Token Permissions</h6>
                                        <p class="text-muted small">{{ \Illuminate\Support\Arr::join($facebookMessenger?->data['page']['tasks'] ?? [], ', ') }}</p>
                                    </div>
                                </div>
                                @if($facebookMessenger?->data['app'] ?? null)
                                    <div class="row col-12 mt-4">
                                        <h5 class="h5">APP Details</h5>
                                        <div class="col-4 p-3">
                                            <h6 class="text-dark fw-bold">Name</h6>
                                            <p class="text-muted small">{{ $facebookMessenger?->data['app']['name'] ?? '---' }}</p>
                                        </div>
                                        <div class="col-4 p-3">
                                            <h6 class="text-dark fw-bold">ID</h6>
                                            <p class="text-muted small">{{ $facebookMessenger?->data['app']['id'] ?? '---' }}</p>
                                        </div>
                                        <div class="col-4 p-3">
                                            <h6 class="text-dark fw-bold">Link</h6>
                                            <p class="text-muted small">{{ $facebookMessenger?->data['app']['link'] ?? '---' }}</p>
                                        </div>
                                        <div class="col-6 p-3">
                                            <h6 class="text-dark fw-bold">Subscribed Fields</h6>
                                            <p class="text-muted small">
                                                {{ \Illuminate\Support\Arr::join($facebookMessenger?->data['app']['subscribed_fields'] ?? [], ', ') ?: '---' }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($routePrefix == 'admin')
                                    <div class="row col-12 mt-4">
                                        <h5 class="h5">Webhook Configurations</h5>
                                        <div class="col-6 p-3">
                                            <label class="text-dark fw-bold" for="webhook_url">URL</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control"
                                                       value="{{ route('facebook.messenger.webhooks') }}"
                                                       id="webhook_url"
                                                       aria-describedby="basic-addon2" readonly="">
                                                <div class="input-group-append pointer">
                                            <span class="input-group-text bg--success text--light" id="basic-addon2"
                                                  onclick="copyAccessToken('webhook_url')">Copy</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 p-3">
                                            <label class="text-dark fw-bold" for="verification_token">Verification
                                                Token</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control"
                                                       value="{{ $verificationToken }}"
                                                       id="verification_token"
                                                       aria-describedby="basic-addon2" readonly="">
                                                <div class="input-group-append pointer">
                                                <span class="input-group-text bg--success text--light" id="basic-addon2"
                                                      onclick="copyAccessToken('verification_token')">Copy</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4 p-3">
                                            <h6 class="text-dark fw-bold">Webhook Subscription Status</h6>
                                            <p class="text-muted small">{{ $facebookMessenger?->challenge ? 'Subscribed' : 'Not Subscribed' }}</p>
                                        </div>
                                        @endif
                                        {{--                                        <div class="col-12 p-3">--}}
                                        {{--                                            <h6 class="text-dark fw-bold">OpenAI Bot</h6>--}}
                                        {{--                                            <p class="text-muted">Any messages--}}
                                        {{--                                                received through this page will be processed and responded to by the--}}
                                        {{--                                                assigned OpenAI bot.--}}
                                        {{--                                            </p>--}}
                                        {{--                                            <form--}}
                                        {{--                                                action="{{ route($routePrefix.'.facebook.messenger.update.open_ai_bot') }}"--}}
                                        {{--                                                method="POST"--}}
                                        {{--                                                class="form-inline mt-2 col-12 float-sm-right text-end">--}}
                                        {{--                                                @csrf--}}
                                        {{--                                                @method('PUT')--}}
                                        {{--                                                <div class="col col-md-4">--}}
                                        {{--                                                    <input type="hidden" name="facebook_messenger_id"--}}
                                        {{--                                                           value="{{ $facebookMessenger->id }}">--}}
                                        {{--                                                    <div class="input-group mb-3">--}}
                                        {{--                                                        <select class="form-select" name="ai_bot_id" required="">--}}
                                        {{--                                                            <option disabled selected>Select AI Bot</option>--}}
                                        {{--                                                            @foreach($aiBots as $bot)--}}
                                        {{--                                                                <option--}}
                                        {{--                                                                    value="{{ $bot->id }}" @selected($facebookMessenger->ai_bot_id == $bot->id)--}}
                                        {{--                                                                >{{ \App\Models\AiBot::AI_NAME[$bot->name] }}</option>--}}
                                        {{--                                                            @endforeach--}}
                                        {{--                                                        </select>--}}
                                        {{--                                                        <button class="btn--primary input-group-text input-group-text"--}}
                                        {{--                                                                id="basic-addon2"--}}
                                        {{--                                                                type="submit">@lang('Update')</button>--}}
                                        {{--                                                    </div>--}}
                                        {{--                                                </div>--}}
                                        {{--                                            </form>--}}
                                        {{--                                            <div class="text-muted mt-2"><b class="fw-bold text-dark">Note: </b>Please--}}
                                        {{--                                                ensure that you have--}}
                                        {{--                                                properly configured your OpenAI Bot before utilizing this feature.--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}

{{--                                        <div class="col-12 mt-4 p-3">--}}
{{--                                            <h6 class="text-dark fw-bold">Greetings Text</h6>--}}
{{--                                            <p class="text-muted">Configure the Greetings text to welcome your users--}}
{{--                                                when they click on the "Get Started" button.--}}
{{--                                            </p>--}}
{{--                                            <form--}}
{{--                                                action="{{ route($routePrefix.'.facebook.messenger.update.greetings_text') }}"--}}
{{--                                                method="POST"--}}
{{--                                                class="form-inline mt-2 col-12 float-sm-right text-start row">--}}
{{--                                                @csrf--}}
{{--                                                @method('PUT')--}}
{{--                                                <input type="hidden" name="facebook_messenger_id"--}}
{{--                                                       value="{{ $facebookMessenger->id }}">--}}
{{--                                                <div class="col col-md-12">--}}
{{--                                                    <textarea id="greetings_text" name="greetings_text"--}}
{{--                                                              class="form-control">{{ $facebookMessenger->greetings_text }}</textarea>--}}
{{--                                                    <p class="small text-muted mt-2">Note: Below Text will be appended--}}
{{--                                                        at--}}
{{--                                                        your greeting text.</p>--}}
{{--                                                    <p class="text-muted fw-bold">Chatting will be assisted by SendEach--}}
{{--                                                        AI Chatbot.--}}
{{--                                                    </p>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-12 mt-2">--}}
{{--                                                    <button type="submit" class="btn--primary btn">Save</button>--}}
{{--                                                </div>--}}
{{--                                            </form>--}}
{{--                                        </div>--}}
                                    </div>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
        </div>
        </div>
    </section>

    <div class="modal fade" id="disconnect" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route($routePrefix.'.facebook.messenger.disconnect') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to disconnect your facebook page ?') }}</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="bg--danger">{{ translate('Delete') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @pushonce('scriptpush')

        <script>
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

