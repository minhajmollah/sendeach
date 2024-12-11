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
                    </div>

                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <h6 class="card-title text-center text-light">{{ translate('Open AI Bot Configurations')}}</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route($routePrefix.'.ai_bots.system_settings.update') }}"
                                  class="row">
                                @csrf
                                @method('PUT')
                                <div class="mb-3 col-12">
                                    <label for="open_ai_api_key"
                                           class="form-label">{{ translate('OpenAI API Key')}}
                                    </label>
                                    <input class="form-control" id="openai_api_key" name="openai_api_key"
                                           placeholder="{{ translate('OpenAI API Key')}}"
                                           value="{{ old('openai_api_key', \Illuminate\Support\Arr::get($aiBot->data, 'openai.api_key') ?? '') }}">
                                </div>

                                <div class="col-12 shadow m-3 mb-4 p-2 row">
                                    <div class="col">
                                        <p class="text-muted">OpenAI Trials for users</p>
                                        <div class="form-check form-switch form-switch-lg">
                                            <input class="form-check-input"
                                                   name="is_user_trial_enabled" type="checkbox"
                                                   id="is_user_trial_enabled"
                                                   @if(\Illuminate\Support\Arr::get($aiBot->data ?? [], 'openai.is_user_trial_enabled')) checked @endif>
                                            <label class="form-check-label" for="is_user_trial_enabled">Enable
                                                Trials For users</label>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <label for="openai_trial_tokens_per_user"
                                               class="form-label">{{ translate('Trial Tokens Per User')}}
                                        </label>
                                        <input class="form-control" id="openai_trial_tokens_per_user"
                                               name="openai_trial_tokens_per_user" type="number"
                                               placeholder="{{ translate('OpenAI Tokens Per User')}}"
                                               value="{{ old('openai_trial_tokens_per_user', \Illuminate\Support\Arr::get($aiBot->data, 'openai.trial_tokens_per_user') ?? '') }}">
                                    </div>
                                </div>

                                <div class="col text-center">
                                    <button class="btn btn--primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
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

