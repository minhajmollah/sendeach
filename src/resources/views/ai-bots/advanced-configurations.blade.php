@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix.'.layouts.app')

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
                                            href="{{route($routePrefix.'.dashboard')}}">{{ translate('Dashboard')}}</a>
                                    </li>
                                    <li class="breadcrumb-item" aria-current="page"> {{ translate('AI Bots')}}</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="col-12 col-lg-3 col-xl-3 px-2 py-1 ">
                            <form method="GET"
                                  class="form-inline float-sm-right text-end">
                                <div class="input-group mb-3 w-100">
                                    <select class="form-select" name="model" required="">
                                        @foreach(\App\Models\AiBot::BOT_NAMES as $bot)
                                            <option value="{{ $bot }}" @selected($name == $bot)
                                            >{{ $bot }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn--primary input-group-text input-group-text" id="basic-addon2"
                                            type="submit">@lang('Select')</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <h6 class="card-title text-center text-light">{{ translate('Open AI Bot Configurations')}}</h6>
                        </div>

                        <div class="card-body row align-items-center justify-content-center">
                            <form
                                action="{{route($routePrefix.'.ai_bots.prompt_update', ['name' => $name] )}}"
                                method="POST">
                                @csrf
                                @method('PUT')
                                <div class="shadow-lg p-3 mb-3 bg-body rounded">
                                    <div class="row">
                                        @if($name == \App\Models\AiBot::CHAT)
                                            <div class="mb-3 col-12">
                                                <label for="system_text"
                                                       class="form-label">{{ translate('OpenAI System Text')}} <sup
                                                        class="text--danger">*</sup></label>
                                                <p class="small text-muted mb-2">Say how the AI should behave ?
                                                    Ex: You are a customer support representative.
                                                   </p>
                                                <textarea class="form-control" id="system_text" name="system_text"
                                                          placeholder="{{ translate('OpenAI System Message')}}"
                                                          rows="10"
                                                          required>{{ old('system_text', $aiBot->data['system_text'] ?? '') }}</textarea>
                                            </div>
                                            <div class="mb-3 col-12">
                                                <label for="assistant_text"
                                                       class="form-label">{{ translate('OpenAI Assistant Text')}}
                                                    (Optional)</label>
                                                <p class="small text-muted mb-2">Hint the AI for better accurate reply
                                                    ?</p>
                                                <input class="form-control" id="assistant_text" name="assistant_text"
                                                       placeholder="{{ translate('OpenAI Assistant Text')}}"
                                                       value="{{ old('assistant_text', $aiBot->data['assistant_text'] ?? '') }}">
                                            </div>
                                            <div class="mb-3 col-lg-12">
                                                <label for="user_text"
                                                       class="form-label">{{ translate('OpenAI User Text')}} (Optional)
                                                </label>
                                                <p class="text-muted small">Any extra information that to be sent with
                                                    users message.
                                                </p>
                                                <textarea class="form-control" id="user_text" name="user_text"
                                                          placeholder="{{ translate('User Text')}}"
                                                >{{ old('user_text',$aiBot->data['user_text'] ?? '') }}</textarea>
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
                                                    @if(\Illuminate\Support\Arr::get($aiBot->data, 'fine_tuned_model_status') == 'pending')
                                                        <a href="{{ route($routePrefix.'.ai_bots.cancel_fine_tune') }}"
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
                                                @if(isset($aiBot->data['train_data']))
                                                    @foreach($aiBot->data['train_data'] as $data)
                                                        <div class='col-12 my-2 row'>
                                                            <div class="col-6">
                                                                <input class="form-control" name="train_data[prompt][]"
                                                                       value="{{ $data['prompt'] }}"
                                                                       placeholder="{{ translate('Prompt Text')}}">
                                                            </div>
                                                            <div class="col-6">
                                                                <input class="form-control col-6"
                                                                       name="train_data[completion][]"
                                                                       value="{{ $data['completion'] }}"
                                                                       placeholder="{{ translate('Completion Text')}}">
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
                                        <div class="row mb-3 align-items-center">
                                            <div class="col-4">
                                                <label for="max_tokens"
                                                       class="form-label">{{ translate('Max Tokens')}}</label>
                                                <p class="small text-muted mb-2">Maximum No Of Tokens
                                                    to be used for each Message.
                                                </p>
                                                <input class="form-control" id="max_tokens" name="max_tokens"
                                                       placeholder="{{ translate('Max Tokens')}}"
                                                       value="{{ old('max_tokens', $aiBot->max_tokens) }}">
                                            </div>
                                            <div class="col-4">
                                                <label for="messages_per_minute"
                                                       class="form-label">{{ translate('Messages Per Minutes')}}</label>
                                                <p class="small text-muted mb-2">Set Maximum messages can user send per
                                                    minute to this Bot.
                                                </p>
                                                <input class="form-control" id="messages_per_minute"
                                                       name="messages_per_minute"
                                                       placeholder="{{ translate('Messages Per Minutes')}}"
                                                       value="{{ old('messages_per_minute', $aiBot->messages_per_minute) }}">
                                            </div>
                                            <div class="col-4">
                                                <label for="temperature"
                                                       class="form-label">{{ translate('Temperature')}}</label>
                                                <p class="small text-muted mb-2">Uniqueness of Reply [0 - 2]
                                                    0 for No uniqueness and 2 for most unique reply.
                                                </p>
                                                <input class="form-control" type="range" min="0" max="2" step="0.1"
                                                       id="temperature" name="temperature"
                                                       placeholder="{{ translate('Temperature')}}"
                                                       value="{{ old('temperature', $aiBot->temperature) }}">
                                            </div>
                                            @if($name == \App\Models\AiBot::CHAT)
                                                <div class="col-4">
                                                    <label for="stop"
                                                           class="form-label">{{ translate('Stop Sequence')}}
                                                        (Optional)</label>
                                                    <p class="small text-muted mb-2">When the AI should stop generating
                                                        reply. Ex: '\n' [New Line], '.' ? Leave it blank so that OpenAI
                                                        will reply full message.
                                                    </p>
                                                    <input class="form-control" id="stop" name="stop"
                                                           placeholder="{{ translate('Stop Sequence')}}"
                                                           value="{{ old('stop', $aiBot->data['stop'] ?? '') }}">
                                                </div>
                                            @endif
                                            <div class="col">
                                                <p class="small text-muted mb-2">Enhance the chat experience by enabling
                                                    memory.
                                                    The bot has the ability to recall past conversations with users,
                                                    which may require additional tokens.
                                                </p>
                                                <div class="form-check form-switch form-switch-lg">
                                                    <input class="form-check-input"
                                                           name="enable_memory" type="checkbox"
                                                           id="enable_memory" @if($aiBot->enable_memory) checked @endif>
                                                    <label class="form-check-label" for="enable_memory">Enable
                                                        Memory</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col text-center">
                                        <button type="submit"
                                                class="btn btn--primary px-4 text-light">{{ translate('Update')}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @pushonce('scriptpush')
        <script>
            $("#add-fine-tune-data").click(function () {
                $("#fine-tune-data").append(`<div class='col-12 my-2 row'><div class="col-6"><input class="form-control" name="train_data[prompt][]"
                                                       placeholder="{{ translate('Prompt Text')}}"
                                                       ></div><div class="col-6"><input class="form-control col-6" name="train_data[completion][]"
                                                       placeholder="{{ translate('Completion Text')}}"
                                                       ></div></div>`)
            })
        </script>
    @endpushonce
@endsection

