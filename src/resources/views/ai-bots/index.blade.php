@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix . '.layouts.app')

@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                <div class="col-xl-12">
                    <div class="row">
                        <div class="col table_heading d-flex align--center justify--between">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a
                                            href="{{ route($routePrefix . '.dashboard') }}">{{ translate('Dashboard') }}</a>
                                    </li>
                                    <li class="breadcrumb-item" aria-current="page"> {{ translate('AI Bots') }}</li>
                                </ol>
                            </nav>
                        </div>

                        {{--                        <div class="col-12 col-lg-3 col-xl-3 px-2 py-1 "> --}}
                        {{--                            <form method="GET" --}}
                        {{--                                  class="form-inline float-sm-right text-end"> --}}
                        {{--                                <div class="input-group mb-3 w-100"> --}}
                        {{--                                    <select class="form-select" name="model" required=""> --}}
                        {{--                                        @foreach (\App\Models\AiBot::BOT_NAMES as $bot) --}}
                        {{--                                            <option value="{{ $bot }}" @selected($name == $bot) --}}
                        {{--                                            >{{ $bot }}</option> --}}
                        {{--                                        @endforeach --}}
                        {{--                                    </select> --}}
                        {{--                                    <button class="btn--primary input-group-text input-group-text" id="basic-addon2" --}}
                        {{--                                            type="submit">@lang('Select')</button> --}}
                        {{--                                </div> --}}
                        {{--                            </form> --}}
                        {{--                        </div> --}}
                    </div>

                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <h6 class="card-title text-center text-light">{{ translate('Open AI Bot Configurations') }}</h6>
                        </div>

                        <div class="card-body row align-items-center justify-content-center">
                            <div class="row col-12 align-items-center my-2 gap-2">
                                <a href="#" class="single_pinned_project shadow col-lg-3">
                                    <div class="pinned_icon">
                                        <i class="lab la-telegram-plane"></i>
                                    </div>
                                    <div class="pinned_text">
                                        <div>
                                            <h6>Total Open Ai Tokens Used</h6>
                                            <p>{{ @$aiBot->total_tokens_used ?: 0 }}</p>
                                        </div>
                                    </div>
                                </a>


                                <a href="#" class="single_pinned_project shadow col-lg-3">
                                    <div class="pinned_icon">
                                        <i class="lab la-facebook-messenger"></i>
                                    </div>
                                    <div class="pinned_text">
                                        <div>
                                            <h6>Total Credits Usage</h6>
                                            <p>{{ @$aiBot->getCreditsUsage() }}</p>
                                        </div>
                                    </div>
                                </a>

                                <a href="#" class="single_pinned_project shadow col-lg-3">
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

                            </div>
                            <div class="row my-3 gap-2">
                                @if ($availableTrailTokens)
                                    <x-report-card title="Remaining Trial OpenAI Tokens" class="col-4" :value="$availableTrailTokens"
                                        icon_class="bg-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                            fill="currentColor" class="bi bi-coin" viewBox="0 0 16 16">
                                            <path
                                                d="M5.5 9.511c.076.954.83 1.697 2.182 1.785V12h.6v-.709c1.4-.098 2.218-.846 2.218-1.932 0-.987-.626-1.496-1.745-1.76l-.473-.112V5.57c.6.068.982.396 1.074.85h1.052c-.076-.919-.864-1.638-2.126-1.716V4h-.6v.719c-1.195.117-2.01.836-2.01 1.853 0 .9.606 1.472 1.613 1.707l.397.098v2.034c-.615-.093-1.022-.43-1.114-.9H5.5zm2.177-2.166c-.59-.137-.91-.416-.91-.836 0-.47.345-.822.915-.925v1.76h-.005zm.692 1.193c.717.166 1.048.435 1.048.91 0 .542-.412.914-1.135.982V8.518l.087.02z" />
                                            <path
                                                d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                            <path
                                                d="M8 13.5a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11zm0 .5A6 6 0 1 0 8 2a6 6 0 0 0 0 12z" />
                                        </svg>
                                    </x-report-card>
                                @endif
                            </div>
                            <form action="{{ route($routePrefix . '.ai_bots.business.update', ['name' => $name]) }}"
                                method="POST">
                                @csrf
                                @method('PUT')
                                <div class="shadow-lg p-3 mb-3 bg-body rounded">
                                    <div class="row">
                                        @if ($availableTrailTokens)
                                            <div class="col-12 alert alert-success">
                                                Congratulations! Your trial has been activated, and you still have <b
                                                    class="fw-bold">{{ $availableTrailTokens }}</b> Tokens remaining for
                                                use.
                                            </div>
                                        @endif
                                        {{--                                        <div class="col-12 alert alert-info"> --}}
                                        {{--                                            <b class="fw-bold">Note:</b> When Multiple Models are enabled, the models --}}
                                        {{--                                            will be utilized in the following hierarchy: --}}
                                        {{--                                            <b class="fw-bold">Inflection AI > Google's PaLM > OpenAI</b>. If a model in --}}
                                        {{--                                            the higher hierarchy is unavailable, a model from the lower hierarchy will --}}
                                        {{--                                            serve as a backup. --}}
                                        {{--                                        </div> --}}
                                        {{--                                        <div class="alert alert-warning col-12"> --}}
                                        {{--                                            <b class="fw-bold">Note: </b>Please take note that only the OpenAI Model --}}
                                        {{--                                            incurs charges, while the other models are entirely free to use.<b --}}
                                        {{--                                                class="fw-bold">1 Credit = 25,000 --}}
                                        {{--                                                Tokens</b> --}}
                                        {{--                                        </div> --}}
                                        <div class="col shadow m-3 mb-4 p-3">
                                            <p class="small text-muted mb-2">If When the OpenAI Model is deactivated,
                                                messages will be managed solely through custom set replies and other
                                                enabled models.
                                                No Tokens or Credits will be consumed if OpenAi Model is disabled.
                                            </p>
                                            <div class="form-check form-switch form-switch-lg">
                                                <input class="form-check-input" name="is_enabled" type="checkbox"
                                                    id="is_enabled" @if ($aiBot->is_enabled) checked @endif>
                                                <label class="form-check-label" for="is_enabled">Enable
                                                    OpenAI Model</label>
                                            </div>
                                        </div>

                                        {{--                                        <div class="col shadow m-3 mb-4 p-3"> --}}
                                        {{--                                            <p class="small text-muted mb-2">If When the PaLM API Model is deactivated, --}}
                                        {{--                                                messages will be managed solely through OpenAI and Custom Auto Replies. --}}
                                        {{--                                            </p> --}}
                                        {{--                                            <div class="form-check form-switch form-switch-lg"> --}}
                                        {{--                                                <input class="form-check-input" --}}
                                        {{--                                                       name="is_palm_enabled" type="checkbox" --}}
                                        {{--                                                       id="is_palm_enabled" --}}
                                        {{--                                                       @if (\Illuminate\Support\Arr::get($aiBot->data ?? [], 'palm_api.is_enabled')) checked @endif> --}}
                                        {{--                                                <label class="form-check-label" for="is_palm_enabled">Enable --}}
                                        {{--                                                    Google's PaLM</label> --}}
                                        {{--                                            </div> --}}
                                        {{--                                        </div> --}}

                                        {{--                                        <div class="col shadow m-3 mb-4 p-3"> --}}
                                        {{--                                            <p class="small text-muted mb-2">If When the PI AI Model is activated, --}}
                                        {{--                                                messages will be managed by PI AI through Whatsapp and other Models will --}}
                                        {{--                                                be used if this doesn't work. --}}
                                        {{--                                            </p> --}}
                                        {{--                                            <div class="form-check form-switch form-switch-lg"> --}}
                                        {{--                                                <input class="form-check-input" --}}
                                        {{--                                                       name="is_inflection_enabled" type="checkbox" --}}
                                        {{--                                                       id="is_inflection_enabled" --}}
                                        {{--                                                       @if (\Illuminate\Support\Arr::get($aiBot->data ?? [], 'pi_ai.is_enabled')) checked @endif> --}}
                                        {{--                                                <label class="form-check-label" for="is_inflection_enabled">Enable --}}
                                        {{--                                                    inflection AI</label> --}}
                                        {{--                                            </div> --}}
                                        {{--                                        </div> --}}

                                        <div class="mb-3 col-12">
                                            <label for="website"
                                                class="form-label">{{ translate('Fetch data from Your Website') }}
                                                (Optional)</label>
                                            <p class="small text-muted mb-2">You can fetch data by entering a single
                                                page url at a time</p>
                                            <div class="input-group">
                                                <input class="form-control" id="website" name="website"
                                                    placeholder="{{ translate('Website URL') }}"
                                                    value="{{ old('website', $aiBot->data['website'] ?? '') }}">
                                                <button type="button" id="parseBusinessDetails"
                                                    class="btn btn--primary px-4">Fetch info
                                                </button>
                                            </div>
                                        </div>

                                        @if ($name == \App\Models\AiBot::CHAT)
                                            <div class="mb-3 col-12">
                                                <label for="business_text"
                                                    class="form-label">{{ translate('Business Information') }} <sup
                                                        class="text--danger">*</sup></label>
                                                <p class="small text-muted mb-2">Please furnish your business details
                                                    for integration with AI to support user chat support.</p>
                                                <div class="input-group input-group-merge speech-to-text d-flex flex-column w-100"
                                                    id="messageBox">
                                                    <textarea class="form-control length-indicator w-100" name="business_text" id="business_text"
                                                        data-max-length="{{ config('openai.max_business_text_length') }}"
                                                        placeholder="{{ translate('Business Information') }}" rows="10" aria-describedby="text-to-speech-icon">{{ old('business_text', $aiBot->data['business_text'] ?? '') }}</textarea>
                                                    <div class="text-end message--word-count"></div>
                                                    {{--                                                    <span class="input-group-text" id="text-to-speech-icon"> --}}
                                                    {{--                                                    <i class='fa fa-microphone pointer text-to-speech-toggle'></i> --}}
                                                    {{--                                                </span> --}}
                                                </div>
                                                <p class="text-muted">Please add as much business info as possible
                                                    below</p>

                                                <button type="button" class="btn btn--primary mt-2" id="summarize">
                                                    Summarize
                                                </button>
                                            </div>
                                        @elseif($name == \App\Models\AiBot::FINE_TUNE)
                                            <label class="form-label">Train Your AI Bot</label>

                                            <div class="col-12">
                                                <div class="alert alert-info py-2 row">
                                                    <div class="col-lg-2 py-2"><b class="fw-bold">Train Status: </b>
                                                        <span
                                                            class="text-muted">{{ $aiBot->data['fine_tuned_model_status'] ?? ' - ' }}</span>
                                                    </div>
                                                    <div class="col-lg-5 py-2"><b class="fw-bold">Fine Tune ID: </b>
                                                        <span
                                                            class="text-muted">{{ $aiBot->data['fine_tune_id'] ?? ' - ' }}</span>
                                                    </div>
                                                    <div class="col-lg-5 py-2"><b class="fw-bold">Fine Tune Trained
                                                            Model Name: </b>
                                                        <span
                                                            class="text-muted">{{ $aiBot->data['fine_tuned_model'] ?? ' - ' }}</span>
                                                    </div>
                                                    <div class="col-lg-12 py-2"><b class="fw-bold">Train Message: </b>
                                                        <span
                                                            class="text-muted">{{ $aiBot->data['fine_tuned_model_stream_message'] ?? ' - ' }}</span>
                                                    </div>
                                                    @if (\Illuminate\Support\Arr::get($aiBot->data, 'fine_tuned_model_status') == 'pending')
                                                        <a href="{{ route($routePrefix . '.ai_bots.cancel_fine_tune') }}"
                                                            class="link link-danger col-6">Cancel Training</a>
                                                        <p>Refresh the page to see live updates on your model train.</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <div id="fine-tune-data" class="col-12 row">
                                                <p class="text-secondary fs-6 col-12 mt-3">
                                                    Please provide example data to train the bot. You can give multiple
                                                    prompts and completions as examples.
                                                </p>
                                                @if (isset($aiBot->data['train_data']))
                                                    @foreach ($aiBot->data['train_data'] as $data)
                                                        <div class='col-12 my-2 row'>
                                                            <div class="col-6">
                                                                <input class="form-control" name="train_data[prompt][]"
                                                                    value="{{ $data['prompt'] }}"
                                                                    placeholder="{{ translate('Prompt Text') }}">
                                                            </div>
                                                            <div class="col-6">
                                                                <input class="form-control col-6"
                                                                    name="train_data[completion][]"
                                                                    value="{{ $data['completion'] }}"
                                                                    placeholder="{{ translate('Completion Text') }}">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                            <div class="mb-3 col-12">
                                                <button class="btn btn-primary" type="button" id="add-fine-tune-data">
                                                    Add Train Data
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col col-md-12 mt-4">
                                        <label for="greetings_text" class="h6 text-dark fw-bold">Greetings Text</label>
                                        <p class="text-muted">Set the Greetings text to welcome your users.
                                        </p>
                                        <textarea id="greetings_text" name="greetings_text" data-max-length="100" class="form-control length-indicator">{{ @$aiBot->data['greetings_text'] }}</textarea>
                                        <div class="text-end message--word-count"></div>
                                        <p class="small text-muted mt-2">Note: Below Text will be appended at
                                            your greeting text.</p>
                                        <p class="text-muted fw-bold">Chatting will be assisted by SendEach
                                            AI Chatbot.
                                        </p>
                                    </div>

                                    <div class="col text-center">
                                        <button type="submit"
                                            class="btn btn--primary px-4 text-light">{{ translate('Create AI Bot') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col mb-4 text-center">
                            <a href="{{ route($routePrefix . '.ai_bots.advanced_configurations') }}"
                                class="btn btn--coral px-4 text-light">{{ translate('View Advanced Settings') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="enable_pi_ai" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="enable_pi_ai_form">
                    @csrf
                    <div class="modal_body2">
                        <div class="mb-3">
                            <label class="form-label" for="whatsappDevice">
                                {{ translate('Whatsapp Device') }}
                            </label>
                            <select class="form-select" name="whatsapp_gateway_id" id="whatsappDevice" required>
                                <option value="">Choose Whatsapp Device
                                </option>
                                @foreach ($whatsappGateways as $device)
                                    <option value="{{ $device->id }}">
                                        {{ $device->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="modal_text2 mt-3">
                            <h6>To utilize this AI Model, it is necessary to connect your WhatsApp device as Inflection
                                AI operates via the WhatsApp gateway.</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="bg--primary">{{ translate('Enable') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @pushonce('scriptpush')
        <script>
            $("#add-fine-tune-data").click(function() {
                $("#fine-tune-data").append(`<div class='col-12 my-2 row'><div class="col-6"><input class="form-control" name="train_data[prompt][]"
                                                       placeholder="{{ translate('Prompt Text') }}"
                                                       ></div><div class="col-6"><input class="form-control col-6" name="train_data[completion][]"
                                                       placeholder="{{ translate('Completion Text') }}"
                                                       ></div></div>`)
            })

            $("#parseBusinessDetails").click(function() {

                let website = $("#website").val()

                if (!website) return;

                $("#loader").css('display', 'flex');

                $.ajax({
                    url: '{{ route($routePrefix . '.ai_bots.parse_business_details') }}',
                    method: 'POST',
                    data: {
                        website
                    },
                    success: function(data) {
                        let text = $("#business_text").val()

                        if (text) {
                            text = text + "\n\n" + data.content
                        } else {
                            text = data.content
                        }

                        $("#business_text").val(text)
                        $("#loader").css('display', 'none');
                        calculateLength($("#business_text"))
                        $("#website").val('')
                    },
                    error: function(res) {
                        $("#loader").css('display', 'none');
                    }
                })

            })

            $("#summarize").click(function() {

                let business_text = $("#business_text").val()

                if (!business_text) return;

                $("#loader").css('display', 'flex');

                $.ajax({
                    url: '{{ route($routePrefix . '.ai_bots.summarize_business_details') }}',
                    method: 'POST',
                    data: {
                        business_text
                    },
                    success: function(data) {
                        console.log(data)
                        $("#business_text").val(data.content)
                        $("#loader").css('display', 'none');
                        calculateLength($("#business_text"))
                    },
                    error: function(res) {
                        $("#loader").css('display', 'none');
                    }
                })

            })

            $("#is_inflection_enabled").change(function() {
                if ($(this).is(':checked')) {
                    $("#enable_pi_ai").modal('show')

                    $("#is_inflection_enabled").prop('checked', false);
                } else {
                    $("#loader").css('display', 'flex');

                    $.ajax({
                        url: '{{ route($routePrefix . '.ai_bots.disablePIAI') }}',
                        method: 'PUT',
                        accept: 'application/json',
                        success: function(data) {
                            if (data.success) {
                                notify('success', data.message)
                            } else {
                                notify('error', data.message)
                            }
                            $("#loader").css('display', 'none');
                        },
                        error: function(data) {
                            notify('error', data.message)
                            $("#loader").css('display', 'none');
                        }
                    })
                }
            });

            $("#enable_pi_ai_form").submit(function(e) {
                let form = $(this)
                e.preventDefault()

                $.ajax({
                    url: '{{ route($routePrefix . '.ai_bots.enablePIAI') }}',
                    method: 'PUT',
                    accept: 'application/json',
                    data: {
                        'whatsapp_gateway_id': form.find("#whatsappDevice").val()
                    },
                    success: function(data) {
                        if (data.success) {
                            notify('success', data.message)
                            $("#enable_pi_ai").modal('hide')
                            $("#is_inflection_enabled").prop('checked', true);

                        } else {
                            notify('error', data.message)
                        }
                        $("#loader").css('display', 'none');
                    },
                    error: function(data) {
                        notify('error', data.message)
                        $("#loader").css('display', 'none');
                    }
                })

            })
        </script>
    @endpushonce
@endsection
