@extends('admin.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row d-flex align--center rounded">
                <div class="col-xl-12">
                    <div class="table_heading d-flex align--center justify--between">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a
                                        href="{{route('admin.dashboard')}}">{{translate('Dashboard')}}</a></li>
                                <li class="breadcrumb-item" aria-current="page"> {{translate('General Setting')}}</li>
                                <li class="breadcrumb-item" aria-current="page">{{translate('Last time cron job run')}}
                                    <i class="las la-arrow-right"></i><span
                                        class="text--success"> {{getDateTime($general->cron_job_run)}}</span></li>
                            </ol>
                        </nav>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#cronjob"
                           class="btn--dark text--light border-0 px-3 py-1 rounded ms-3"><i
                                class="las la-key"></i> {{translate('Setup Cron Jobs')}}</a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form action="{{route('admin.general.setting.store')}}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="mb-3 col-lg-12 col-md-12">
                                        <label for="site_name" class="form-label">{{translate('Site Name')}} <sup
                                                class="text--danger">*</sup></label>
                                        <input type="text" name="site_name" id="site_name" class="form-control"
                                               value="{{$general->site_name}}"
                                               placeholder="{{translate('Enter Site Name')}}" required>
                                    </div>

                                    <div class="mb-3 col-lg-12 col-md-12">
                                        <label for="free_watermark"
                                               class="form-label">{{translate('Watermark for Free plan users')}}</label>
                                        <input type="text" name="free_watermark" id="free_watermark"
                                               class="form-control" value="{{$general->free_watermark}}"
                                               placeholder="{{translate('Enter Watermark for free plan users')}}">
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12 d-none">
                                        <label for="plan_id" class="form-label">{{translate('Sign Up Bonus')}} <sup
                                                class="text--danger">*</sup></label>
                                        <select class="form-select" id="plan_id" name="plan_id" required="">
                                            @foreach($plans as $plan)
                                                <option value="{{$plan->id}}"
                                                        @if($plan->id == $general->plan_id) selected @endif>{{__($plan->name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12 d-none">
                                        <label for="sign_up_bonus"
                                               class="form-label">{{translate('Sign Up Bonus Status')}} <sup
                                                class="text--danger">*</sup></label>
                                        <select class="form-select" id="sign_up_bonus" name="sign_up_bonus" required="">
                                            <option value="1"
                                                    @if($general->sign_up_bonus == 1) selected @endif>{{translate('ON')}}</option>
                                            <option value="2"
                                                    @if($general->sign_up_bonus == 2) selected @endif>{{translate('OFF')}}</option>
                                        </select>
                                    </div>


                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="sms_gateway" class="form-label">{{translate('SMS Gateway')}} <sup
                                                class="text--danger">*</sup></label>
                                        <select class="form-select" id="sms_gateway" name="sms_gateway" required="">
                                            <option value="1"
                                                    @if($general->sms_gateway == 1) selected @endif>{{translate('Api Gateway')}}</option>
                                            <option value="2"
                                                    @if($general->sms_gateway == 2) selected @endif>{{translate('Android Gateway')}}</option>
                                        </select>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="whatsapp_gateway"
                                               class="form-label">{{translate('Default Whatsapp Gateway')}} <sup
                                                class="text--danger">*</sup></label>
                                        <p class="text-secondary py-2">This gateway will be used to send login OTP and
                                            any user alerts.</p>
                                        <select class="form-select" id="whatsapp_gateway" name="whatsapp_gateway"
                                                required="">
                                            <option disabled selected>{{translate('Select Gateway')}}</option>
                                            @foreach(\App\Models\WhatsappLog::ADMIN_GATEWAYS as $gateway)
                                                <option value="{{ $gateway }}"
                                                        @if($general->default_whatsapp_gateway == $gateway) selected @endif>{{translate(ucfirst($gateway).' Gateway')}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{--                                <div class="mb-3 col-lg-6 col-md-12">--}}
                                    {{--                                    <label for="whatsapp_business_otp_template" class="form-label">{{translate('Whatsapp Business OTP Template')}} <sup class="text--danger">*</sup></label>--}}
                                    {{--                                    <p class="text-secondary py-2">This OTP Template will be used to send login OTP.</p>--}}
                                    {{--                                    <select class="form-select" id="whatsapp_business_otp_template" name="whatsapp_business_otp_template" required="">--}}
                                    {{--                                        <option disabled selected>{{translate('Select OTP Tem')}}</option>--}}
                                    {{--                                        @foreach(\App\Models\WhatsappTemplate::query()->tem as $gateway)--}}
                                    {{--                                            <option value="{{ $gateway }}" @if($general->default_whatsapp_gateway == $gateway) selected @endif>{{translate(ucfirst($gateway).' Gateway')}}</option>--}}
                                    {{--                                        @endforeach--}}
                                    {{--                                    </select>--}}
                                    {{--                                </div>--}}

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="registration_status"
                                               class="form-label">{{translate('User Registration')}} <sup
                                                class="text--danger">*</sup></label>
                                        <select class="form-select" id="registration_status" name="registration_status"
                                                required="">
                                            <option value="1"
                                                    @if($general->registration_status == 1) selected @endif>{{translate('ON')}}</option>
                                            <option value="2"
                                                    @if($general->registration_status == 2) selected @endif>{{translate('OFF')}}</option>
                                        </select>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="currency_name" class="form-label">{{translate('Currency')}} <sup
                                                class="text--danger">*</sup></label>
                                        <input type="text" name="currency_name" id="currency_name" class="form-control"
                                               value="{{$general->currency_name}}"
                                               placeholder="{{translate('Enter Currency Name')}}" required>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="currency_symbol" class="form-label">{{translate('Currency Symbol')}}
                                            <sup class="text--danger">*</sup></label>
                                        <input type="text" name="currency_symbol" id="currency_symbol"
                                               class="form-control" value="{{$general->currency_symbol}}"
                                               placeholder="{{translate('Enter Currency Symbol')}}" required>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="currency_symbol"
                                               class="form-label">{{translate('Country Code For Contact')}} <sup
                                                class="text--danger">*</sup></label>
                                        <div class="input-group mb-3">
                                            <label class="input-group-text" for="country_code"
                                                   id="country--dial--code">{{$general->country_code}}</label>
                                            <select name="country_code" class="form-select" id="country_code">
                                                <@foreach($countries as $countryData)
                                                    <option value="{{$countryData->dial_code}}"
                                                            @if($general->country_code == $countryData->dial_code) selected="" @endif>{{$countryData->country}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="timelocation" class="form-label">{{translate('Time Zone')}} <sup
                                                class="text--danger">*</sup></label>
                                        <select class="form-select" id="timelocation" name="timelocation" required="">
                                            @foreach($timeLocations as $timeLocation)
                                                <option value='{{ @$timeLocation}}'
                                                        @if($general->timezone == $timeLocation) selected @endif>{{__($timeLocation)}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="site_logo" class="form-label">{{translate('Site Logo')}}</label>
                                        <input type="file" name="site_logo" id="site_logo" class="form-control">
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label for="site_favicon"
                                               class="form-label">{{translate('Site Favicon')}}</label>
                                        <input type="file" name="site_favicon" id="site_favicon" class="form-control">
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" name="debug_mode" type="checkbox"
                                                   role="switch" id="debug_mode"
                                                   value="true" {{$general->debug_mode=="true" ? "checked" : ""}}>
                                            <label class="form-check-label"
                                                   for="debug_mode">{{translate('Debug Mode For Developing Purpose')}}</label>
                                        </div>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" name="maintenance_mode" type="checkbox"
                                                   role="switch" id="maintenance_mode"
                                                   value="true" {{$general->maintenance_mode=="true" ? "checked" : ""}}>
                                            <label class="form-check-label"
                                                   for="maintenance_mode">{{translate('Maintenance Mode For Site Maintenance')}}</label>
                                        </div>
                                    </div>
                                    @if($general->maintenance_mode=="true")
                                        <div class="mb-3 col-lg-12 col-md-12" id="maintenance_mode_div">
                                            <label for="maintenance_mode_message"
                                                   class="form-label">{{translate('Maintenance Mode Message')}}
                                                <sup class="text--danger">*</sup></label>
                                            <input type="text" name="maintenance_mode_message"
                                                   id="maintenance_mode_message" class="form-control"
                                                   value="{{$general->maintenance_mode_message}}"
                                                   placeholder="{{translate('Write some message for maintenance mode page')}}">
                                        </div>
                                    @endif


                                    {{--                                <div class="mb-3 col-lg-6 col-md-12">--}}
                                    {{--                                    <label for="whatsapp_credit_count" class="form-label">{{translate('Per Credit For WhatsApp')}} <sup class="text--danger">*</sup></label>--}}
                                    {{--                                    <div class="input-group">--}}
                                    {{--                                        <span class="input-group-text" id="basic-addon1">{{translate('1 Credit')}} </span>--}}
                                    {{--                                        <input type="text" id="rate" name="whatsapp_word_count" value="{{$general->whatsapp_word_count}}" class="form-control" placeholder="{{translate('Enter number of words')}}" aria-label="Username" aria-describedby="basic-addon1">--}}
                                    {{--                                    </div>--}}
                                    {{--                                </div>--}}


                                    {{--                                <div class="mb-3 col-lg-6 col-md-12">--}}
                                    {{--                                    <label for="whatsapp_credit" class="form-label">{{translate('Per Credit For SMS')}} <sup class="text--danger">*</sup></label>--}}
                                    {{--                                    <div class="input-group mb-3">--}}
                                    {{--                                        <input type="text" class="form-control" value="{{$general->sms_word_text_count}}" name="sms_word_text_count" placeholder="Number for Text" aria-label="Username">--}}
                                    {{--                                        <span class="input-group-text">{{translate('1 Credit')}}</span>--}}
                                    {{--                                        <input type="text" class="form-control" value="{{$general->sms_word_unicode_count}}" name="sms_word_unicode_count" placeholder="Number for Unicode" aria-label="Server">--}}
                                    {{--                                    </div>--}}
                                    {{--                                </div>--}}
                                    {{--							</div>--}}

                                    <div class="row col-12 mt-4">
                                        <div class="mb-3 col-md-6">
                                            <label for="site_logo"
                                                   class="form-label">{{translate('Upload Updated Desktop Version as ZIP')}}</label>
                                            <input type="file" name="desktop_app" id="desktop_app" class="form-control"
                                                   accept="application/zip">
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <label for="site_name"
                                                   class="form-label">{{translate('Desktop APP Version')}} <sup
                                                    class="text--danger">*</sup></label>
                                            <input type="number" name="desktop_app_version" id="desktop_app_version"
                                                   class="form-control"
                                                   value="{{ old('desktop_app_version', $general->desktop_app_version) }}"
                                                   placeholder="{{translate('Enter Desktop APP Latest Version')}}"
                                                   required>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <label class="form-label mt-3">
                                            Whatsapp Terms and Conditions
                                            <textarea class="form-control summernote"
                                                      name="whatsapp-terms-and-conditions"
                                                      id="whatsapp-terms-and-conditions" rows="2">{{ Arr::get($general->data, 'whatsapp.terms_and_conditions') }}</textarea>
                                        </label>
                                        <label class="form-label mt-3">
                                            Email Terms and Conditions
                                        <textarea class="form-control summernote" name="email-terms-and-conditions"
                                                  id="whatsapp-terms-and-conditions" rows="2">{{ Arr::get($general->data, 'email.terms_and_conditions') }}</textarea>
                                        </label>
                                        <label class="form-label mt-3">
                                            SMS Terms and Conditions
                                        <textarea class="form-control summernote" name="sms-terms-and-conditions"
                                                  id="whatsapp-terms-and-conditions" rows="2">{{ Arr::get($general->data, 'sms.terms_and_conditions') }}</textarea>
                                        </label>
                                    </div>

                                    <button type="submit"
                                            class="btn btn--primary w-100 text-light">{{translate('Submit')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="cronjob" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div class="card-title text-center text--light">
                                <h6>{{translate('Cron Job Setting')}}</h6>
                                <p>{{translate('Set the cron once every minute this is the ideal time')}}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="timelocation" class="form-label">{{translate('Cron Job Run')}} <sup
                                        class="text--danger">* {{translate('Set time for 2 minutes')}}</sup></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="curl -s {{route('cron.run')}}"
                                           id="cron--run" aria-describedby="basic-addon2" readonly="">
                                    <div class="input-group-append pointer">
                                        <span class="input-group-text bg--success text--light" id="basic-addon2"
                                              onclick="cronJobRun()">{{translate('Copy')}}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="timelocation" class="form-label">{{translate('Queue Job In Other Hosting')}}
                                    <sup class="text--danger">* {{translate('Set time for 1 minute')}}</sup></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="curl -s {{route('queue.work')}}"
                                           id="queue_url" aria-describedby="basic-addon2" readonly="">
                                    <div class="input-group-append pointer">
                                        <span class="input-group-text bg--success text--light" id="basic-addon2"
                                              onclick="queue()">{{translate('Copy')}}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="timelocation" class="form-label">{{translate('Queue Job In cPanel')}} <sup
                                        class="text--danger">* {{translate('Set time for 1 minute')}}</sup></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control"
                                           value="/usr/local/bin/php /home/you_project_location/src/artisan queue:work --stop-when-empty > /dev/null 2>&1"
                                           id="queue_cpanel" aria-describedby="basic-addon2" readonly="">
                                    <div class="input-group-append pointer">
                                        <span class="input-group-text bg--success text--light" id="basic-addon2"
                                              onclick="queue_cpanel()">{{translate('Copy')}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal_button2">
                    <button type="button" class="w-100" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passportKeyGen" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('admin.general.setting.passport.key')}}" method="GET">
                    @csrf
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{translate('You are trying to generate a new passport api key!')}}</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                        <button type="submit" class="bg--success">{{translate('Continue')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('scriptpush')
    <script>
        "use strict";
        (function ($) {
            $('select[name=country_code]').on('change', function () {
                var value = $(this).val();
                $("#country--dial--code").text(value);
            });

            $('#maintenance_mode').on('click', function (e) {
                var status = $(this).val();
                if ($(this).prop("checked") == true) {
                    $("#maintenance_mode_div").fadeIn();
                } else if ($(this).prop("checked") == false) {
                    $("#maintenance_mode_div").fadeOut();
                }
            })
        })(jQuery);

        function cronJobRun() {
            var copyText = document.getElementById("cron--run");
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
            notify('success', 'Copied the text : ' + copyText.value);
        }

        function queue() {
            var copyText = document.getElementById("queue_url");
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
            notify('success', 'Copied the text : ' + copyText.value);
        }

        function queue_cpanel() {
            var copyText = document.getElementById("queue_cpanel");
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
            notify('success', 'Copied the text : ' + copyText.value);
        }


        $(".summernote").summernote({
            placeholder: 'Terms and Conditions',
            tabsize: 2,
            width: '100%',
            height: 200,
            toolbar: [
                ['fontname', ['fontname']],
                ['style', ['style']],
                ['fontsize', ['fontsizeunit']],
                ['font', ['bold', 'underline', 'clear']],
                ['height', ['height']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['codeview']],
            ],
            codeviewFilterRegex: 'custom-regex'
        });
    </script>
@endpush
