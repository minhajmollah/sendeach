@props(['templates', 'devices', 'groups'])

@php
    $user = auth()->user();
    $isAntiBlockEnabled = \Illuminate\Support\Arr::get($user->data, 'whatsapp.anti_block');
@endphp


@if(request()->routeIs('*desktop*'))
    <div class="row px-2">
        <div class="alert alert-success">
            You can shut down or disconnect your computer at any time during a sending campaign, and the sending will
            automatically resume once you reopen the SendEach PC app.
        </div>
    </div>

@endif
<div class="card mb-2">
    <h6 class="card-header">{{ translate('To recipient number collect in a different ways') }}
    </h6>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">
                    {{ translate('Send to') }}
                </label>
                <div class="input-group input-group-merge">
                    <select class="form-select" name="recipent_type" id="recipentType">
                        <option value="" disabled="">{{ translate('Select One') }}
                        </option>
                        @php
                            $sendingTypes = [
                                'toNumberInput' => translate('To Recipient Number'),
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
            <div class="col-md-6 mb-3">
                <label class="form-label">
                    {{ translate('Send Device') }}
                </label>
                <div class="input-group input-group-merge">
                    <select class="form-select" name="whatsapp_device" id="whatsappDevice">
                        <option value="">{{ translate('Send via random device') }}
                        </option>
                        @foreach ($devices as $device)
                            <option
                                value="{{ $device->id }}"
                                @if (old('whatsapp_device') == $device->id) selected @endif>
                                {{ $device->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 mb-3 d-none" id="toNumberInput">
                <label class="form-label">
                    {{ translate('To Recipient Number') }}
                </label>
                <div class="input-group input-group-merge">
                    <div class="input-group">

                        <input type="text" class="form-control" name="number" id="number"
                               placeholder="{{ translate('Enter with country code ') }}{{ $general->country_code }}{{ translate('XXXXXXXXX') }}"
                               aria-label="number" aria-describedby="basic-addon11">
                    </div>
                </div>
            </div>
            <div class="col-12 mb-4 d-none" id="toNumberGroupInput">
                <label class="form-label" for="group">
                    {{ translate('To Recipient Number From Group') }}
                    <div class="badge badge--primary"><span id="total-group-contacts">0</span> Contacts</div>
                </label>
                <div class="form-group">
                    <select class="form-select w-100" name="group_id[]" id="group"
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
            <div class="col-12 mb-4 d-none" id="toNumbersFromFileInput">
                <label class="form-label">
                    {{ translate('To Recipient Number From File Upload') }}
                </label>
                <div class="input-group input-group-merge">
                    <input class="form-control" type="file" name="file" id="file">
                </div>
                <div class="form-text">
                    {{ translate('Supported files: txt, csv, excel. Download all files from here: ') }}{{ translate('') }}
                    <a
                        href="{{ route('demo.file.downlode', 'txt') }}">{{ translate('txt') }}
                        ,</a>
                    <a href="{{ route('demo.file.downlode', 'csv') }}">{{ translate('csv') }}
                        ,
                    </a>
                    <a
                        href="{{ route('demo.file.downlode', 'xlsx') }}">{{ translate('xlsx') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-2">
    <h6 class="card-header">{{ translate('Message Body') }}</h6>
    <div class="card-body">
        <div class="row">
            <div class="md-12">
                <label class="form-label" for="message">
                    {{ translate('Write Message') }} <sup class="text-danger">*</sup>
                </label>
                <p class="py-2 text-dark">
                    To include an unsubscribe link in your <b class="fw-bold">group send</b>
                    message, just use the
                    variable text <b class="fw-bold"> @{{unsubscribe}} </b> wherever you
                    want it.
                    This variable will be
                    replaced with the actual unsubscribe link.
                    <br><b class="fw-bold">Note: </b> It works only when Send to method is
                    Group Send.
                </p>
                <div class="input-group input-group-merge speech-to-text" id="messageBox">
                                                <textarea class="form-control length-indicator" name="message"
                                                          id="message" rows="10"
                                                          placeholder="{{ translate('Enter Message Content') }}"
                                                          aria-describedby="text-to-speech-icon">{{ old('message') }}</textarea>
                    <span class="input-group-text" id="text-to-speech-icon">
                                                    <i class='fa fa-microphone pointer text-to-speech-toggle'></i>
                                                </span>
                </div>

                <div class="form-text d-flex justify-content-between">
                    <div class="text-start">
                        <a href="javascript:void(0)" data-bs-toggle="modal"
                           data-bs-target="#templatedata">{{ translate('Use Template') }}</a>
                    </div>
                    <div class="text-end message--word-count"></div>
                </div>

                <div class="mt-3">
                    <label class="form-label">
                        {{ translate('Choosen File') }} <sup class="text-danger">*</sup>
                    </label>
                    <select name="" class="form-select" id="selectTypeChange">
                        <option value="">{{ translate('Select One') }}</option>
                        <option value="file">{{ translate('File') }}</option>
                        <option value="image">{{ translate('Image') }}</option>
                        <option value="audio">{{ translate('Audio') }}</option>
                        <option value="video">{{ translate('Video') }}</option>
                    </select>
                </div>

                <div id="appendInputField">

                </div>
            </div>
            <div class="col my-4" id="spunMessages">
                <p class="fw-bold fs--10">The different versions of your messages are shown below.
                    You can edit them and click "Send" to send the generated messages to different recipients.</p>
            </div>
        </div>
    </div>
    <div class="card mb-2">
        <h6 class="card-header">{{ translate('Message Send Options') }}</h6>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="schedule" class="form-label">{{ translate('Message') }}<sup
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
    <div class="card mb-2">
        <h6 class="card-header">Disclaimer</h6>
        <div class="card-body">
            <label class="d-flex gap-2 align-items-center">
                <input type="checkbox" id="agree" onchange="agreeTermsAndConditions()" required>
                <span>I agree with Terms And Conditions</span>
            </label>
        </div>

    </div>


    <div class="row p-0 mx-3">
        @if($devices?->count() <= 1)
            <div class="alert alert-warning">
                ⚠️ Warning: Only one sending device detected.

                If you plan to send more than 100 messages, you may want to consider connecting more sending devices to
                avoid being blocked by WhatsApp.
            </div>
        @endif
        @if(!$isAntiBlockEnabled)
            <div class="alert alert-danger">
                Important: <b class="fw-bold">Your Anit Block System is disabled.</b> If you send more than 500 messages
                without anti-blocking enabled,
                your device may get blocked by WhatsApp. To avoid this, please enable anti-blocking before sending large
                numbers of messages.
                <a href="{{ route('user.gateway.whatsapp.create') }}" target="_blank">Enable Anti Block.</a>
            </div>
        @endif
    </div>
    <div class="row p-0 mx-3">
        @if($isAntiBlockEnabled)
            <div class="alert alert-primary">
                SendEach Anti-blocking Technology mimics the sending behavior of a human thus making message delivery
                slow and safe. For a Safe Faster delivery, we recommend using WhatsApp business Gateway.
            </div>
            <p>Please click below "Send Messages" to generate different versions of your messages for anti block system
                if recipients are more than 5.</p>
        @endif
        <div class="my-3 d-flex gap-2">
            @if($isAntiBlockEnabled)
                <button type="button" class="btn btn--primary" id="spinMessages" onclick="spinMessage(this)">Generate
                    Spin Messages Using AI
                </button>
            @endif

            <button type="submit" id="sendMessages" class="btn btn--primary me-sm-3 me-1">Send Messages</button>
        </div>
    </div>

    <div class="modal fade" id="terms-and-condition" tabindex="-1" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div
                                class="card-title text-center text--light">{{ translate('Please Agree to our Terms and Conditions before proceeding.') }}</div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                Your Whatsapp account will be blocked from <strong>WhatsApp</strong> in case you
                                are sending SPAM messages to users. SendEach doesn't hold any reponsibiliy on
                                how you use our service and will not be liallble to any damages you incur by
                                using our service.
                            </div>
                            {!!  Arr::get($general->data, 'whatsapp.terms_and_conditions')  !!}
                        </div>
                        <div class="card-footer">
                            <div class="text-end">
                                <button type="button" data-bs-dismiss="modal" class="btn btn--secondary">Close</button>
                                <button type="button" onclick="agreeTerms()" class="btn btn--success">@lang('Agree')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @pushonce('scriptpush')
        <script>

            function agreeTerms() {
                $("#agree").prop('checked', true)
                $("#terms-and-condition").modal('hide')
            }

            function agreeTermsAndConditions() {
                $("#terms-and-condition").modal('show')
                $("#agree").prop('checked', false)
            }

            let form = $("#compose-message-form")

            const $spunMessages = $("#spunMessages");
            $spunMessages.hide()

            let sendMessagesBtn = $("#sendMessages")
            let spinMessages = $("#spinMessages")
            @if(@$isAntiBlockEnabled) sendMessagesBtn.hide() @endif

            let isSpinAgain = false;

            function spinMessage(e) {
                let message = $("#message").val()

                $("#loader").css('display', 'flex');

                console.log(convertFormToJSON(form))

                $.ajax({
                    url: '{{ route((auth()->id() ? 'user' : 'admin').'.ai_bots.spin_message') }}',
                    method: 'POST',
                    data: {...convertFormToJSON(form), limit: isSpinAgain ? 5 : null},
                    success: function (data) {
                        console.log(data.messages)
                        if (data.messages.length > 0) {
                            $spunMessages.show()
                            data.messages.forEach((message) => {
                                $spunMessages.append(`<div class='my-4'><div class="input-group my-4 input-group-merge speech-to-text" id="messageBox">
                                                <textarea class="form-control" name="spinMessage[]"
                                                          id="message" rows="5"
                                                          placeholder="{{ translate('Enter Message Content') }}"
                                                          aria-describedby="text-to-speech-icon">${message}</textarea>
                                                    <span class="input-group-text" id="text-to-speech-icon">
                                                        <i class='fa fa-microphone pointer text-to-speech-toggle'></i>
                                                    </span>
                                                   </div>
                                                 <button type="button" onclick="$(this).parent().remove()" class="btn btn--danger fs-6 px-3 text--light">
                                                 <i class="las la-trash"></i>
                                                </button></div>`)
                            })

                            $("#message").focus()
                        }
                        sendMessagesBtn.attr('disabled', false)
                        $("#loader").css('display', 'none');
                        sendMessagesBtn.show()
                        spinMessages.text('Spin 5 more Messages')
                        isSpinAgain = true;
                    },
                    error: function (res) {
                        $("#loader").css('display', 'none');
                        $("#sendMessages").attr('disabled', false)
                        let errors = res.responseJSON.errors
                        errors = errors.message ?? errors
                        console.log(errors)
                        errors.forEach((error) => {
                            notify('error', error)
                        })
                    }
                })
            }

            function convertFormToJSON(form) {
                const array = form.serializeArray();
                const json = {};
                $.each(array, function () {
                    let value = this.value || "";
                    this.name = this.name.replace('[]', '')

                    if (!json[this.name]) {
                        json[this.name] = value;
                    } else {
                        if (Array.isArray(json[this.name])) {
                            json[this.name] = [...json[this.name], value];
                        } else {
                            json[this.name] = [json[this.name], value];
                        }
                    }

                    if (this.name == 'group_id') {
                        json[this.name] = Array.isArray(json[this.name]) ? json[this.name] : [json[this.name]];
                    }

                });

                return json;
            }


            function setScheduledDate() {
                $("#schedule_date_timezone").val((new Date($("#schedule_date").val())).toISOString())
            }

            function sendMessages(e) {
                form.submit();
            }


            (function ($) {
                "use strict";
                $('.keywords').select2({
                    tags: true,
                    tokenSeparators: [',']
                });

                $('input[type=radio][name=schedule]').on('change', function () {
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

                const message = $('textarea[name=message]');

                $('select[name=template]').on('change', function () {
                    let character = $(this).val();
                    message.val(character);
                    let word = character.split(" ");

                    if (character.length > 0) {
                        $(".message--word-count").html(`
                	<span class="text--success character">${character.length}</span> {{ translate('Character') }} |
					<span class="text--success word">${word.length}</span> {{ translate('Words') }}`);
                    } else {
                        $(".message--word-count").empty()
                    }
                    $('#templatedata').modal('toggle');
                });

                var t = window.SpeechRecognition || window.webkitSpeechRecognition,
                    e = document.querySelectorAll(".speech-to-text");
                if (null != t && null != e) {
                    var n = new t;
                    var e = !1;
                    $('#text-to-speech-icon').on('click', function () {
                        var messageBox = document.getElementById('messageBox');
                        messageBox.querySelector(".form-control").focus(), n.onspeechstart = function () {
                            e = !0
                        }, !1 === e && n.start(), n.onerror = function () {
                            e = !1
                        }, n.onresult = function (e) {
                            messageBox.querySelector(".form-control").value = e.results[0][0].transcript;
                        }, n.onspeechend = function () {
                            e = !1, n.stop()
                        }
                    });
                }


                $(document).on('change', '#selectTypeChange', function () {
                    if ($(this).val() == 'file') {
                        $('#appendInputField').html(' ')
                        $('#appendInputField').html(`
					<label for="">{{ translate('File') }}</label>
					<input type="file" name="document" class="form-control">
				`)
                    } else if ($(this).val() == 'audio') {
                        $('#appendInputField').html(' ')
                        $('#appendInputField').html(`
					<label for="">{{ translate('Audio') }}</label>
					<input type="file" name="audio" class="form-control">
				`)
                    } else if ($(this).val() == 'image') {
                        $('#appendInputField').html(' ')
                        $('#appendInputField').html(`
					<label for="">{{ translate('Image') }}</label>
					<input type="file" name="image" class="form-control">
				`)
                    } else if ($(this).val() == 'video') {
                        $('#appendInputField').html(' ')
                        $('#appendInputField').html(`
					<label for="">{{ translate('Video') }}</label>
					<input type="file" name="video" class="form-control">
				`)
                    }
                });

                const inputTypes = ['toNumberInput', 'toNumberGroupInput', 'toNumbersFromFileInput'];
                const recipentTypeHandler = function () {
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


                $('#group').on('change', function (e) {
                    let groupIds = $("#group").val()

                    if (!groupIds) return;

                    $.ajax({
                        url: '{{ route('user.phone.book.group.contacts_count') }}',
                        data: {
                            groupIds
                        },
                        success: function (data) {
                            $("#total-group-contacts").text(data.count)
                        },
                        error: {}
                    })
                })

            })(jQuery);
        </script>
@endpushonce
