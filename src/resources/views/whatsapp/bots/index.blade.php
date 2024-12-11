@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix.'.layouts.app')

@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <h6 class="card-title text-center text-light">{{ translate('WhatsApp Bot')}}</h6>
                        </div>

                        <div class="card-body m-0 row align-items-center justify-content-center">

                            <form action="{{ route($routePrefix.'.whatsapp.bot.update') }}" class="row"
                                  method="POST">
                                @csrf
                                @method('PUT')

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="whatsappDevice">
                                        {{ translate('Whatsapp Device') }}
                                    </label>
                                    <select class="form-select" name="whatsapp_gateway_id" id="whatsappDevice">
                                        <option value="">Choose Whatsapp Device
                                        </option>
                                        @foreach ($whatsappGateways as $device)
                                            <option
                                                value="{{ $device->id }}"
                                                @if ($whatsappBot->whatsapp_gateway_id == $device->id) selected @endif>
                                                {{ $device->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3 pt-4">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input"
                                               name="is_enabled" type="checkbox"
                                               id="is_enabled" @if($whatsappBot->is_enabled) checked @endif>
                                        <label class="form-check-label" for="is_enabled">Enable Whatsapp Bot</label>
                                    </div>

                                    <p class="text-muted">
                                        Whatsapp Bot will only work if it is enabled and whatsapp web device is connected.
                                    </p>
                                </div>

{{--                                <div class="col-md-6 col mb-3">--}}
{{--                                    <label class="form-label" for="ai_bot_id">--}}
{{--                                        {{ translate('OpenAI Model') }}--}}
{{--                                    </label>--}}
{{--                                    <select class="form-select" name="ai_bot_id" id="ai_bot_id" required="">--}}
{{--                                        <option disabled selected>Select AI Bot</option>--}}
{{--                                        @foreach($aiBots as $bot)--}}
{{--                                            <option--}}
{{--                                                value="{{ $bot->id }}" @selected($whatsappBot->ai_bot_id == $bot->id)--}}
{{--                                            >{{ \App\Models\AiBot::AI_NAME[$bot->name] }}</option>--}}
{{--                                        @endforeach--}}
{{--                                    </select>--}}
{{--                                </div>--}}

                                <div class="row col-12 m-0 mt-4">

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch form-switch-lg">
                                            <input class="form-check-input"
                                                   name="handle_only_unknown_user" type="checkbox"
                                                   id="handle_only_unknown_user"
                                                   @if($whatsappBot->handle_only_unknown_user) checked @endif>
                                            <label class="form-check-label" for="handle_only_unknown_user">Only handle
                                                users
                                                who are not in your contact list.</label>
                                        </div>
                                        <p class="text-muted">
                                            Whatsapp Numbers not in your contact list will be ignored.
                                        </p>
                                    </div>

                                </div>

{{--                                <div class="col-12 mt-4">--}}
{{--                                    <h6 class="text-dark fw-bold">Greetings Text</h6>--}}
{{--                                    <p class="text-muted">Configure the Greetings text to welcome your new users.--}}
{{--                                    </p>--}}
{{--                                    <textarea id="greetings_text" name="greetings_text"--}}
{{--                                              class="form-control">{{ $whatsappBot->data['greetings_text'] ?? '' }}</textarea>--}}
{{--                                    <p class="small text-muted mt-2">Note: Below Text will be appended at--}}
{{--                                        your greeting text.</p>--}}
{{--                                    <p class="text-muted fw-bold">{{  config('requirements.greetings_text_postfix') }}--}}
{{--                                    </p>--}}
{{--                                </div>--}}

                                <div class="col-12 mt-4">
                                    <h6 class="h6">Ignore Whatsapp Numbers</h6>

                                    <p>The WhatsApp Bot will not respond to the WhatsApp numbers that
                                        are ignored.
                                    </p>

                                    <p class="text-muted mt-2">Please enter the WhatsApp number, including the country
                                        code but
                                        without the "+" symbol or any special characters. For example, use the format
                                        1XXXXXXXXXX.</p>

                                    <div class="row mt-3 add-whatsapp-numbers" id="ignored_numbers">
                                        @foreach($whatsappBot->data['ignored_numbers'] ?? [] as $ignoredNumber)
                                            <div class="col-md-4 col-lg-3 col-sm-6 col-12 mb-1">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="ignored_numbers[]"
                                                           value="{{ $ignoredNumber }}"
                                                           placeholder="Whatsapp Number">
                                                    <button type="button" onclick="removeIgnoredNumber(this)"
                                                            class="btn btn--danger fs-6 px-3 text--light"
                                                    ><i class="las la-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                        @endforeach
                                    </div>
                                    <button class="btn btn--primary mt-3 px-2"
                                            onclick="addWhatsappNumber(this, 'ignored_numbers[]')" type="button">
                                        <i class="las la-plus-circle fs-6"></i> Add
                                    </button>
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="h6">Allowed Whatsapp Numbers</h6>

                                    <p>The WhatsApp Bot will only respond to the WhatsApp numbers
                                        that
                                        are provided below.
                                        <b class="fw-bold">To enable all WhatsApp numbers,
                                            remove all the allowed numbers.</b>
                                    </p>

                                    <p class="text-muted mt-2">Please enter the WhatsApp number, including the country
                                        code but
                                        without the "+" symbol or any special characters. For example, use the format
                                        1XXXXXXXXXX.</p>

                                    <div class="row mt-3 add-whatsapp-numbers">
                                        @foreach($whatsappBot->data['allowed_numbers'] ?? [] as $allowedNumber)
                                            <div class="col-md-4 col-lg-3 col-sm-6 col-12 mb-1">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="allowed_numbers[]"
                                                           value="{{ $allowedNumber }}"
                                                           placeholder="Whatsapp Number">
                                                    <button type="button" onclick="removeIgnoredNumber(this)"
                                                            class="btn btn--danger fs-6 px-3 text--light"
                                                    ><i class="las la-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                        @endforeach
                                    </div>
                                    <button class="btn btn--primary mt-3 px-2"
                                            onclick="addWhatsappNumber(this, 'allowed_numbers[]')" type="button">
                                        <i class="las la-plus-circle fs-6"></i> Add
                                    </button>
                                </div>

                                <div class="col text-center">
                                    <button class="btn btn--success mt-4">Update</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@pushonce('scriptpush')

    <script>
        function removeIgnoredNumber(e) {
            $(e).parent().parent().remove();
        }


        function addWhatsappNumber(e, name) {
            $(e).siblings(".add-whatsapp-numbers").append(`
                                    <div class="col-md-4 col-lg-3 col-sm-6 col-12 mb-1">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="${name}"
                                                       placeholder="Whatsapp Number">
                                                <button type="button"
                                                        class="btn btn--danger fs-6 px-3 text--light" onclick="removeIgnoredNumber(this)"
                                                ><i class="las la-trash"></i>
                                                </button>
                                            </div>
                                        </div>`)
        }
    </script>

@endpushonce

