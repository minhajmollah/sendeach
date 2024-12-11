@extends('admin.layouts.app')
@push('scriptpush')
    <style>
        .select2-container {
            min-width: 100%;
        }

        #select2-group-results {
            min-width: 100%;
        }
    </style>
@endpush
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="p-0 pb-2 mb-3 container-fluid">
            <div class="rounded row d-flex align--center">
                <div class="col-xl-12">
                    <div class="col-xl">
                        <form action="{{ route('admin.sms.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2 card">
                                <h6 class="card-header">{{ translate('To recipient number collect in a different ways') }}
                                </h6>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3 col-md-4" id="recipentTypeContianer">
                                            <label class="form-label">
                                                {{ translate('Send to') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select" name="recipent_type" id="recipentType">
                                                    <option value="" disabled="">{{ translate('Select One') }}
                                                    </option>
                                                    @php
                                                        $sendingTypes = [
                                                            'toNumberInput' => translate('To Recipient Numbers'),
                                                            'toNumberGroupInput' => translate('To Recipient Number From Group'),
                                                            'toNumbersFromFileInput' => translate('To Recipient Number From File Upload'),
                                                        ];
                                                    @endphp
                                                    @foreach ($sendingTypes as $key => $value)
                                                        <option value="{{ $key }}"
                                                            @if (old('recipent_type') == $key) selected @endif>
                                                            {{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-4" id="sendDeviceTypeContainer">
                                            <label class="form-label">
                                                {{ translate('Send Via') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select" name="gateway_or_device_type"
                                                    id="sendDeviceType" required>
                                                    <option value="mobile"
                                                        @if (old('gateway_or_device_type') == 'mobile') selected @endif>Mobile Device -
                                                        {{ count($devices) }}</option>
                                                    <option value="gateway"
                                                        @if (old('gateway_or_device_type') == 'gateway') selected @endif>SMS Gateway (3rd
                                                        Party Service) - {{ count($gateways) }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-4 d-none" id="mobileDeviceTypeContainer">
                                            <label class="form-label">
                                                {{ translate('Mobile Device') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select" name="mobile_device_id" id="mobileDevice">
                                                    <option value="">
                                                        {{ translate('Select a mobile device to send message with...') }}
                                                    </option>
                                                    @foreach ($devices as $device)
                                                        <option
                                                            value="{{ $device->id }}"@if (old('mobile_device_id') == $device->id) selected @endif>
                                                            {{ $device->device_id }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-4 d-none" id="gatewayTypeContainer">
                                            <label class="form-label">
                                                {{ translate('SMS Gateway') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select" name="gateway_id" id="smsGateway">
                                                    <option value="">
                                                        {{ translate('Select a gateway to send message with...') }}
                                                    </option>
                                                    @foreach ($gateways as $gateway)
                                                        <option
                                                            value="{{ $gateway->id }}"@if (old('gateway_id') == $gateway->id) selected @endif>
                                                            {{ $gateway->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-12 d-none"id="toNumberInput">
                                            <label class="form-label">
                                                {{ translate('To Recipient Numbers') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <div class="input-group">
                                                    <span class="input-group-text" id="basic-addon11">(Phone #)</span>
                                                    <input type="text" class="form-control" name="number" id="number"
                                                        placeholder="{{ translate('Enter mobile numbers seperated by commas') }}"
                                                        aria-label="number" aria-describedby="basic-addon11">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4 col-12 d-none" id="toNumberGroupInput">
                                            <label class="form-label">
                                                {{ translate('To Recipient Number From Group') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select keywords" name="group_id[]" id="group"
                                                    multiple="multiple">
                                                    <option value="" disabled="">{{ translate('Select One') }}
                                                    </option>
                                                    @foreach ($groups as $group)
                                                        <option value="{{ $group->id }}">{{ __($group->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-text">
                                                {{ translate('Can be select single or multiple group') }}
                                            </div>
                                        </div>
                                        <div class="mb-4 col-12 d-none" id="toNumbersFromFileInput">
                                            <label class="form-label">
                                                {{ translate('To Recipient Number From File Upload') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <input class="form-control" type="file" name="file" id="file">
                                            </div>
                                            <div class="form-text">
                                                {{ translate('Supported files: txt, csv, excel. Download all files from here: ') }}
                                                @lang('')
                                                <a
                                                    href="{{ route('demo.file.downlode', 'txt') }}">{{ translate('txt') }},</a>
                                                <a href="{{ route('demo.file.downlode', 'csv') }}">{{ translate('csv') }},
                                                </a>
                                                <a
                                                    href="{{ route('demo.file.downlode', 'xlsx') }}">{{ translate('xlsx') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2 card">
                                <h6 class="card-header">{{ translate('SMS Body') }}</h6>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                {{ translate('Select SMS Type') }} <sup class="text-danger">*</sup>
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <div class="form-check form-check-inline">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="smsType"
                                                            id="smsTypeText" value="plain" checked="">
                                                        <label class="form-check-label"
                                                            for="smsTypeText">{{ translate('Text') }}</label>
                                                    </div>

                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="smsType"
                                                            id="smsTypeUnicode" value="unicode">
                                                        <label class="form-check-label"
                                                            for="smsTypeUnicode">{{ translate('Unicode') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="md-12">
                                            <label class="form-label">
                                                {{ translate('Write Message') }} <sup class="text-danger">*</sup>
                                            </label>
                                            <div class="input-group input-group-merge speech-to-text" id="messageBox">
                                                <textarea class="form-control" name="message" id="message"
                                                    placeholder="{{ translate('Enter SMS Content &  For Mention Name Use ') }}@php echo "{{ ". 'name' ." }}" @endphp"
                                                    aria-describedby="text-to-speech-icon"></textarea>
                                                <span class="input-group-text" id="text-to-speech-icon">
                                                    <i class='fa fa-microphone pointer text-to-speech-toggle'></i>
                                                </span>
                                            </div>
                                            <div class="input-group">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#templatedata">{{ translate('Use Template') }}</a>
                                            </div>
                                            <div class="text-end message--word-count"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 card">
                                <h6 class="card-header">{{ translate('SMS Send Options') }}</h6>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-4 col-md-6">
                                            <label for="schedule" class="form-label">{{ translate('SMS') }} <sup
                                                    class="text-danger">*</sup></label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="schedule"
                                                        id="schedule" value="1" checked="">
                                                    <label class="form-check-label"
                                                        for="schedule">{{ translate('Send Now') }}</label>
                                                </div>

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="schedule"
                                                        id="schedule2" value="2">
                                                    <label class="form-check-label"
                                                        for="schedule2">{{ translate('Send Later') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 scheduledate"></div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="modal fade" id="templatedata" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div class="text-center card-title text--light">{{ translate('SMS Template') }}</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="template" class="form-label">{{ translate('Select Template') }} <sup
                                        class="text--danger">*</sup></label>
                                <select class="form-select" name="template" id="template" required>
                                    <option value="" disabled="" selected="">{{ translate('Select One') }}
                                    </option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->message }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scriptpush')
    <script>

        function setScheduledDate (){
            $("#schedule_date_timezone").val((new Date($("#schedule_date").val())).toISOString())
        }

        (function($) {
            "use strict";
            $('.keywords').select2({
                tags: true,
                tokenSeparators: [',']
            });

            var wordLength = {{ $general->sms_word_text_count }};
            $('input[type=radio][name=smsType]').on('change', function() {
                if (this.value == "unicode") {
                    wordLength = {{ $general->sms_word_unicode_count }};
                } else {
                    wordLength = {{ $general->sms_word_text_count }};
                }
            });

            $('input[type=radio][name=schedule]').on('change', function() {
                if (this.value == 2) {
                    var html = `
	        		<label for="schedule_date" class="form-label">{{ translate('Schedule') }}<sup class="text-danger">*</sup></label>
	        		<input type="hidden" name="schedule_date" id="schedule_date_timezone">
					<input type="datetime-local" onchange="setScheduledDate()" id="schedule_date" class="form-control" required="">`;
                    $('.scheduledate').append(html);
                } else {
                    $('.scheduledate').empty();
                }
            });

            $('select[name=template]').on('change', function() {
                var character = $(this).val();
                $('textarea[name=message]').val(character);
                $('#templatedata').modal('toggle');
            });

            $(`textarea[name=message]`).on('keyup', function(event) {
                var credit = 160;
                var character = $(this).val();
                var characterleft = credit - character.length;
                var word = character.split(" ");
                var sms = 1;
                if (character.length > wordLength) {
                    sms = Math.ceil(character.length / wordLength);
                }
                if (character.length > 0) {
                    $(".message--word-count").html(`
                	<span class="text--success character">${character.length}</span> {{ translate('Character') }} |
					<span class="text--success word">${word.length}</span> {{ translate('Words') }} |
					<span class="text--success word">${sms}</span> {{ translate('SMS') }} (${wordLength} Char./SMS)`);
                } else {
                    $(".message--word-count").empty()
                }
            });

            var t = window.SpeechRecognition || window.webkitSpeechRecognition,
                e = document.querySelectorAll(".speech-to-text");
            if (null != t && null != e) {
                var n = new t;
                var e = !1;
                $('#text-to-speech-icon').on('click', function() {
                    var messageBox = document.getElementById('messageBox');
                    messageBox.querySelector(".form-control").focus(), n.onspeechstart = function() {
                        e = !0
                    }, !1 === e && n.start(), n.onerror = function() {
                        e = !1
                    }, n.onresult = function(e) {
                        messageBox.querySelector(".form-control").value = e.results[0][0].transcript
                    }, n.onspeechend = function() {
                        e = !1, n.stop()
                    }
                });
            }

            const inputTypes = ['toNumberInput', 'toNumberGroupInput', 'toNumbersFromFileInput'];
            const recipentTypeHandler = function() {
                const selected = $('#recipentType').val();
                inputTypes.forEach(id => {
                    document.getElementById(id).classList.add('d-none');
                });
                if (selected == "toNumberGroupInput") {
                    $('#group').select2({
                        tags: true,
                        tokenSeparators: [',']
                    });
                }

                document.getElementById(selected).classList.remove('d-none');
            };
            recipentTypeHandler();
            $(document).on('change', '#recipentType', recipentTypeHandler);

            const sendDeviceType = [
                ['gatewayTypeContainer', 'smsGateway'],
                ['mobileDeviceTypeContainer', 'mobileDevice']
            ];
            const sendingDeviceTypeHandler = function() {
                const selected = $('#sendDeviceType').val();
                sendDeviceType.forEach(type => {
                    document.getElementById(type[0]).classList.add('d-none');
                    document.getElementById(type[1]).removeAttribute('required');
                });

                switch (selected) {
                    case 'mobile':
                        document.getElementById('mobileDeviceTypeContainer').classList.remove('d-none');
                        document.getElementById('mobileDevice').setAttribute('required', true);
                        break;
                    case 'gateway':
                        document.getElementById('gatewayTypeContainer').classList.remove('d-none');
                        document.getElementById('smsGateway').setAttribute('required', true);
                        break;
                    default:
                        console.error('Unknown selection type - ' + selected)
                }
            };
            sendingDeviceTypeHandler();
            $(document).on('change', '#sendDeviceType', sendingDeviceTypeHandler);

        })(jQuery);
    </script>
@endpush
